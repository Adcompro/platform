<?php

namespace App\Services;

use App\Models\TeamleaderProject;
use App\Models\TeamleaderMilestone;
use App\Models\TeamleaderTask;
use App\Models\TeamleaderTimeEntry;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeamleaderProjectSyncService
{
    /**
     * Sync alle projecten voor een specifieke customer
     */
    public function syncProjectsForCustomer(Customer $customer, int $maxPages = 10): array
    {
        if (!$customer->teamleader_id) {
            throw new \Exception('Customer has no Teamleader ID');
        }

        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total_fetched' => 0
        ];

        $page = 1;
        $hasMore = true;

        Log::info('Starting Teamleader project sync for customer', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'teamleader_id' => $customer->teamleader_id
        ]);

        try {
            DB::beginTransaction();

            while ($hasMore && $page <= $maxPages) {
                $response = TeamleaderService::listProjectsForCompany($customer->teamleader_id, $page, 100);

                if (empty($response['data'])) {
                    Log::info('No more projects found', ['page' => $page]);
                    break;
                }

                $projectsOnPage = count($response['data']);
                $stats['total_fetched'] += $projectsOnPage;

                Log::info('Processing Teamleader projects page', [
                    'page' => $page,
                    'count' => $projectsOnPage
                ]);

                foreach ($response['data'] as $tlProject) {
                    try {
                        $result = $this->syncSingleProject($tlProject, $customer);

                        if ($result === 'created') {
                            $stats['synced']++;
                        } elseif ($result === 'updated') {
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Error syncing single project', [
                            'teamleader_id' => $tlProject['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Check of er meer paginas zijn
                if ($projectsOnPage < 100) {
                    $hasMore = false;
                } else {
                    $page++;
                    // Kleine pauze om API rate limiting te respecteren
                    usleep(500000); // 0.5 seconden
                }
            }

            DB::commit();

            Log::info('Teamleader project sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Teamleader project sync failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customer->id
            ]);
            throw $e;
        }
    }

    /**
     * Sync een enkel project
     */
    protected function syncSingleProject(array $tlProject, Customer $customer): string
    {
        $teamleaderId = $tlProject['id'];

        // Extract Teamleader customer ID from project
        $tlCustomerId = $tlProject['customer']['id'] ?? null;

        if (!$tlCustomerId) {
            Log::warning('Project has no customer ID, skipping', [
                'project_id' => $teamleaderId,
                'title' => $tlProject['title'] ?? 'Unknown'
            ]);
            return 'skipped';
        }

        // Find the correct customer in Progress based on Teamleader customer ID
        $progressCustomer = Customer::where('teamleader_id', $tlCustomerId)->first();

        if (!$progressCustomer) {
            Log::warning('Project customer not found in Progress, skipping', [
                'project_id' => $teamleaderId,
                'title' => $tlProject['title'] ?? 'Unknown',
                'teamleader_customer_id' => $tlCustomerId
            ]);
            return 'skipped';
        }

        // Extract budget informatie
        $budgetAmount = null;
        $budgetCurrency = 'EUR';

        if (isset($tlProject['budget']['provided']['amount'])) {
            $budgetAmount = (float) $tlProject['budget']['provided']['amount'];
            $budgetCurrency = $tlProject['budget']['provided']['currency'] ?? 'EUR';
        }

        // Parse dates
        $startsOn = isset($tlProject['starts_on'])
            ? Carbon::parse($tlProject['starts_on'])
            : null;

        $dueOn = isset($tlProject['due_on'])
            ? Carbon::parse($tlProject['due_on'])
            : null;

        // Data voor upsert - gebruik de GEVONDEN customer, niet de input customer
        $projectData = [
            'teamleader_company_id' => $tlCustomerId,
            'customer_id' => $progressCustomer->id,
            'title' => $tlProject['title'] ?? 'Unnamed Project',
            'description' => $tlProject['description'] ?? null,
            'status' => $this->mapTeamleaderStatus($tlProject['status'] ?? 'unknown'),
            'starts_on' => $startsOn,
            'due_on' => $dueOn,
            'budget_amount' => $budgetAmount,
            'budget_currency' => $budgetCurrency,
            'raw_data' => $tlProject,
            'synced_at' => now(),
        ];

        // Check of project al bestaat
        $existing = TeamleaderProject::where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update bestaand project (alleen als niet geïmporteerd)
            if (!$existing->is_imported) {
                $existing->update($projectData);
                return 'updated';
            }
            return 'skipped'; // Al geïmporteerd, niet updaten
        }

        // Nieuw project aanmaken
        TeamleaderProject::create(array_merge(
            ['teamleader_id' => $teamleaderId],
            $projectData
        ));

        return 'created';
    }

    /**
     * Map Teamleader status naar Progress status
     */
    protected function mapTeamleaderStatus(string $tlStatus): string
    {
        return match(strtolower($tlStatus)) {
            'active' => 'active',
            'on_hold' => 'on_hold',
            'done', 'completed' => 'done',
            'cancelled' => 'cancelled',
            default => 'active'
        };
    }

    /**
     * Sync projecten voor ALLE customers met Teamleader ID
     */
    public function syncAllCustomers(int $maxPagesPerCustomer = 5): array
    {
        $customers = Customer::whereNotNull('teamleader_id')->get();

        $totalStats = [
            'customers_processed' => 0,
            'total_synced' => 0,
            'total_updated' => 0,
            'total_errors' => 0
        ];

        foreach ($customers as $customer) {
            try {
                $stats = $this->syncProjectsForCustomer($customer, $maxPagesPerCustomer);

                $totalStats['customers_processed']++;
                $totalStats['total_synced'] += $stats['synced'];
                $totalStats['total_updated'] += $stats['updated'];
                $totalStats['total_errors'] += $stats['errors'];

            } catch (\Exception $e) {
                $totalStats['total_errors']++;
                Log::error('Failed to sync customer projects', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Kleine pauze tussen customers
            sleep(1);
        }

        return $totalStats;
    }

    /**
     * Sync ALLE projecten uit Teamleader naar database
     * Dit haalt ALLE projecten op zonder company filter
     * en slaat ze op met of zonder customer koppeling
     */
    public function syncAllProjectsFromTeamleader(int $maxPages = 50): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'no_customer_found' => 0,
            'errors' => 0,
            'total_fetched' => 0
        ];

        $page = 1;
        $hasMore = true;

        Log::info('Starting GLOBAL Teamleader project sync (all projects)');

        try {
            DB::beginTransaction();

            while ($hasMore && $page <= $maxPages) {
                // Haal ALLE projecten op (geen company filter!)
                $response = TeamleaderService::listProjects($page, 100);

                if (empty($response['data'])) {
                    Log::info('No more projects found', ['page' => $page]);
                    break;
                }

                $projectsOnPage = count($response['data']);
                $stats['total_fetched'] += $projectsOnPage;

                Log::info('Processing Teamleader projects page (GLOBAL)', [
                    'page' => $page,
                    'count' => $projectsOnPage
                ]);

                foreach ($response['data'] as $tlProject) {
                    try {
                        $result = $this->syncSingleProjectGlobal($tlProject);

                        if ($result === 'created') {
                            $stats['synced']++;
                        } elseif ($result === 'updated') {
                            $stats['updated']++;
                        } elseif ($result === 'no_customer') {
                            $stats['no_customer_found']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Error syncing single project (GLOBAL)', [
                            'teamleader_id' => $tlProject['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Check of er meer paginas zijn
                if ($projectsOnPage < 100) {
                    $hasMore = false;
                } else {
                    $page++;
                    // Kleine pauze om API rate limiting te respecteren
                    usleep(500000); // 0.5 seconden
                }
            }

            DB::commit();

            Log::info('GLOBAL Teamleader project sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GLOBAL Teamleader project sync failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Sync een enkel project (GLOBAL - slaat alles op, ook zonder customer match)
     */
    protected function syncSingleProjectGlobal(array $tlProject): string
    {
        $teamleaderId = $tlProject['id'];

        // Extract Teamleader customer ID from project
        $tlCustomerId = $tlProject['customer']['id'] ?? null;

        // Zoek de customer in Progress (maar skip NIET als niet gevonden!)
        $progressCustomer = null;
        if ($tlCustomerId) {
            $progressCustomer = Customer::where('teamleader_id', $tlCustomerId)->first();
        }

        // Log als customer niet gevonden, maar ga wel door met opslaan
        if ($tlCustomerId && !$progressCustomer) {
            Log::debug('Project customer not found in Progress, saving with NULL customer_id', [
                'project_id' => $teamleaderId,
                'title' => $tlProject['title'] ?? 'Unknown',
                'teamleader_customer_id' => $tlCustomerId
            ]);
        }

        // Extract budget informatie
        $budgetAmount = null;
        $budgetCurrency = 'EUR';

        if (isset($tlProject['budget']['provided']['amount'])) {
            $budgetAmount = (float) $tlProject['budget']['provided']['amount'];
            $budgetCurrency = $tlProject['budget']['provided']['currency'] ?? 'EUR';
        }

        // Parse dates
        $startsOn = isset($tlProject['starts_on'])
            ? Carbon::parse($tlProject['starts_on'])
            : null;

        $dueOn = isset($tlProject['due_on'])
            ? Carbon::parse($tlProject['due_on'])
            : null;

        // Data voor upsert - customer_id kan NULL zijn!
        $projectData = [
            'teamleader_company_id' => $tlCustomerId,
            'customer_id' => $progressCustomer ? $progressCustomer->id : null,
            'title' => $tlProject['title'] ?? 'Unnamed Project',
            'description' => $tlProject['description'] ?? null,
            'status' => $this->mapTeamleaderStatus($tlProject['status'] ?? 'unknown'),
            'starts_on' => $startsOn,
            'due_on' => $dueOn,
            'budget_amount' => $budgetAmount,
            'budget_currency' => $budgetCurrency,
            'raw_data' => $tlProject,
            'synced_at' => now(),
        ];

        // Check of project al bestaat
        $existing = TeamleaderProject::where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update bestaand project (alleen als niet geïmporteerd)
            if (!$existing->is_imported) {
                $existing->update($projectData);
                return 'updated';
            }
            return 'skipped'; // Al geïmporteerd, niet updaten
        }

        // Nieuw project aanmaken
        TeamleaderProject::create(array_merge(
            ['teamleader_id' => $teamleaderId],
            $projectData
        ));

        return $progressCustomer ? 'created' : 'no_customer';
    }

    /**
     * Sync ALLE companies uit Teamleader naar database
     */
    public function syncAllCompaniesFromTeamleader(int $maxPages = 50): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total_fetched' => 0
        ];

        $page = 1;
        $hasMore = true;

        Log::info('Starting GLOBAL Teamleader companies sync (all companies)');

        try {
            DB::beginTransaction();

            while ($hasMore && $page <= $maxPages) {
                // Haal ALLE companies op
                $response = TeamleaderService::listCompanies($page, 100);

                if (empty($response['data'])) {
                    Log::info('No more companies found', ['page' => $page]);
                    break;
                }

                $companiesOnPage = count($response['data']);
                $stats['total_fetched'] += $companiesOnPage;

                Log::info('Processing Teamleader companies page (GLOBAL)', [
                    'page' => $page,
                    'count' => $companiesOnPage
                ]);

                foreach ($response['data'] as $tlCompany) {
                    try {
                        $result = $this->syncSingleCompanyGlobal($tlCompany);

                        if ($result === 'created') {
                            $stats['synced']++;
                        } elseif ($result === 'updated') {
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Error syncing single company (GLOBAL)', [
                            'teamleader_id' => $tlCompany['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Check of er meer paginas zijn
                if ($companiesOnPage < 100) {
                    $hasMore = false;
                } else {
                    $page++;
                    usleep(500000); // 0.5 seconden pauze
                }
            }

            DB::commit();

            Log::info('GLOBAL Teamleader companies sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GLOBAL Teamleader companies sync failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Sync een enkele company (GLOBAL)
     */
    protected function syncSingleCompanyGlobal(array $tlCompany): string
    {
        $teamleaderId = $tlCompany['id'];

        // Extract company data
        $name = $tlCompany['name'] ?? 'Unnamed Company';
        $vatNumber = $tlCompany['vat_number'] ?? null;
        $status = $tlCompany['status'] ?? 'active';

        // Extract email
        $email = null;
        if (!empty($tlCompany['emails']) && is_array($tlCompany['emails'])) {
            $email = $tlCompany['emails'][0]['email'] ?? null;
        }

        // Extract phone
        $phone = null;
        if (!empty($tlCompany['telephones']) && is_array($tlCompany['telephones'])) {
            $phone = $tlCompany['telephones'][0]['number'] ?? null;
        }

        // Extract address
        $addressLine1 = null;
        $addressLine2 = null;
        $postalCode = null;
        $city = null;
        $country = null;

        if (!empty($tlCompany['addresses']) && is_array($tlCompany['addresses'])) {
            $addressWrapper = $tlCompany['addresses'][0];
            if (isset($addressWrapper['address'])) {
                $address = $addressWrapper['address'];
                $addressLine1 = $address['line_1'] ?? null;
                $addressLine2 = $address['line_2'] ?? null;
                $postalCode = $address['postal_code'] ?? null;
                $city = $address['city'] ?? null;
                $country = $address['country'] ?? null;
            }
        }

        // Data voor insert/update
        $companyData = [
            'name' => $name,
            'vat_number' => $vatNumber,
            'national_identification_number' => $tlCompany['national_identification_number'] ?? null,
            'email' => $email,
            'website' => $tlCompany['website'] ?? null,
            'phone' => $phone,
            'mobile' => null, // Companies meestal geen mobile
            'address_line_1' => $addressLine1,
            'address_line_2' => $addressLine2,
            'postal_code' => $postalCode,
            'city' => $city,
            'country' => $country,
            'business_type' => $tlCompany['business_type']['type'] ?? null,
            'status' => $status,
            'raw_data' => json_encode($tlCompany),
            'synced_at' => now(),
        ];

        // Check of company al bestaat
        $existing = DB::table('teamleader_companies')->where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update als niet geïmporteerd
            if (!$existing->is_imported) {
                DB::table('teamleader_companies')
                    ->where('teamleader_id', $teamleaderId)
                    ->update(array_merge($companyData, ['updated_at' => now()]));
                return 'updated';
            }
            return 'skipped';
        }

        // Nieuwe company aanmaken
        DB::table('teamleader_companies')->insert(array_merge(
            ['teamleader_id' => $teamleaderId],
            $companyData,
            [
                'is_imported' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ));

        return 'created';
    }

    /**
     * Sync ALLE contacts uit Teamleader naar database
     */
    public function syncAllContactsFromTeamleader(int $maxPages = 50): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total_fetched' => 0
        ];

        $page = 1;
        $hasMore = true;

        Log::info('Starting GLOBAL Teamleader contacts sync (all contacts)');

        try {
            DB::beginTransaction();

            while ($hasMore && $page <= $maxPages) {
                // Haal ALLE contacts op
                $response = TeamleaderService::listContacts($page, 100);

                if (empty($response['data'])) {
                    Log::info('No more contacts found', ['page' => $page]);
                    break;
                }

                $contactsOnPage = count($response['data']);
                $stats['total_fetched'] += $contactsOnPage;

                Log::info('Processing Teamleader contacts page (GLOBAL)', [
                    'page' => $page,
                    'count' => $contactsOnPage
                ]);

                foreach ($response['data'] as $tlContact) {
                    try {
                        $result = $this->syncSingleContactGlobal($tlContact);

                        if ($result === 'created') {
                            $stats['synced']++;
                        } elseif ($result === 'updated') {
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Error syncing single contact (GLOBAL)', [
                            'teamleader_id' => $tlContact['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Check of er meer paginas zijn
                if ($contactsOnPage < 100) {
                    $hasMore = false;
                } else {
                    $page++;
                    usleep(500000); // 0.5 seconden pauze
                }
            }

            DB::commit();

            Log::info('GLOBAL Teamleader contacts sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GLOBAL Teamleader contacts sync failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Sync een enkel contact (GLOBAL)
     */
    protected function syncSingleContactGlobal(array $tlContact): string
    {
        $teamleaderId = $tlContact['id'];

        // Extract contact data
        $firstName = $tlContact['first_name'] ?? null;
        $lastName = $tlContact['last_name'] ?? null;
        $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

        // Extract email
        $email = null;
        if (!empty($tlContact['emails']) && is_array($tlContact['emails'])) {
            $email = $tlContact['emails'][0]['email'] ?? null;
        }

        // Extract phone numbers
        $phone = null;
        $mobile = null;
        if (!empty($tlContact['telephones']) && is_array($tlContact['telephones'])) {
            foreach ($tlContact['telephones'] as $telephone) {
                if ($telephone['type'] === 'phone') {
                    $phone = $telephone['number'];
                } elseif ($telephone['type'] === 'mobile') {
                    $mobile = $telephone['number'];
                }
            }
        }

        // Extract companies
        $companies = [];
        if (!empty($tlContact['companies']) && is_array($tlContact['companies'])) {
            foreach ($tlContact['companies'] as $company) {
                $companies[] = $company['company']['id'] ?? null;
            }
        }

        // Extract address
        $addressLine1 = null;
        $addressLine2 = null;
        $postalCode = null;
        $city = null;
        $country = null;

        if (!empty($tlContact['addresses']) && is_array($tlContact['addresses'])) {
            $addressWrapper = $tlContact['addresses'][0];
            if (isset($addressWrapper['address'])) {
                $address = $addressWrapper['address'];
                $addressLine1 = $address['line_1'] ?? null;
                $addressLine2 = $address['line_2'] ?? null;
                $postalCode = $address['postal_code'] ?? null;
                $city = $address['city'] ?? null;
                $country = $address['country'] ?? null;
            }
        }

        // Data voor insert/update
        $contactData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'mobile' => $mobile,
            'position' => $tlContact['position'] ?? null,
            'language' => $tlContact['language'] ?? null,
            'companies' => json_encode(array_filter($companies)),
            'address_line_1' => $addressLine1,
            'address_line_2' => $addressLine2,
            'postal_code' => $postalCode,
            'city' => $city,
            'country' => $country,
            'raw_data' => json_encode($tlContact),
            'synced_at' => now(),
        ];

        // Check of contact al bestaat
        $existing = DB::table('teamleader_contacts')->where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update als niet geïmporteerd
            if (!$existing->is_imported) {
                DB::table('teamleader_contacts')
                    ->where('teamleader_id', $teamleaderId)
                    ->update(array_merge($contactData, ['updated_at' => now()]));
                return 'updated';
            }
            return 'skipped';
        }

        // Nieuw contact aanmaken
        DB::table('teamleader_contacts')->insert(array_merge(
            ['teamleader_id' => $teamleaderId],
            $contactData,
            [
                'is_imported' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ));

        return 'created';
    }

    /**
     * Sync company-contact relationships (REVERSE SYNC)
     *
     * Dit lost het probleem op dat /contacts.list geen companies retourneert.
     * We doen een reverse sync: voor elke company halen we de contacten op.
     */
    public function syncContactCompanyRelationships(?int $syncJobId = null): array
    {
        $stats = [
            'companies_processed' => 0,
            'companies_total' => 0,
            'contacts_updated' => 0,
            'contacts_created' => 0,
            'relationships_added' => 0,
            'errors' => 0,
            'sync_job_id' => $syncJobId
        ];

        Log::info('Starting REVERSE sync of contact-company relationships');

        // Haal sync job op als opgegeven
        $syncJob = $syncJobId ? \App\Models\SyncJob::find($syncJobId) : null;

        try {
            // Haal ALLE companies op uit onze cache (geen limiet!)
            $companies = DB::table('teamleader_companies')
                ->whereNotNull('teamleader_id')
                ->orderBy('name')
                ->get();

            $stats['companies_total'] = $companies->count();

            // Update sync job met totaal
            if ($syncJob) {
                $syncJob->update(['total_items' => $stats['companies_total']]);
            }

            Log::info('Processing companies for contact relationships', [
                'total_companies' => $companies->count()
            ]);

            foreach ($companies as $company) {
                try {
                    $stats['companies_processed']++;

                    Log::info('Fetching contacts for company', [
                        'company_name' => $company->name,
                        'teamleader_id' => $company->teamleader_id
                    ]);

                    // Update progress elke 5 companies
                    if ($syncJob && $stats['companies_processed'] % 5 === 0) {
                        $syncJob->updateProgress(
                            $stats['companies_processed'],
                            $stats['relationships_added'],
                            $stats['errors'],
                            "Processing: {$company->name}"
                        );
                    }

                    // Haal alle contacten voor deze company op (kan meerdere pagina's zijn)
                    $page = 1;
                    $hasMore = true;

                    while ($hasMore && $page <= 10) { // Max 10 pagina's = 1000 contacten per company
                        $response = TeamleaderService::listContactsForCompany($company->teamleader_id, $page, 100);

                        if (empty($response['data'])) {
                            break;
                        }

                        $contactsOnPage = count($response['data']);

                        foreach ($response['data'] as $tlContact) {
                            try {
                                $contactTeamleaderId = $tlContact['id'];

                                // Check of contact al bestaat in onze cache
                                $existingContact = DB::table('teamleader_contacts')
                                    ->where('teamleader_id', $contactTeamleaderId)
                                    ->first();

                                if ($existingContact) {
                                    // Update: voeg company toe aan companies array
                                    $currentCompanies = json_decode($existingContact->companies ?? '[]', true);

                                    if (!in_array($company->teamleader_id, $currentCompanies)) {
                                        $currentCompanies[] = $company->teamleader_id;

                                        DB::table('teamleader_contacts')
                                            ->where('teamleader_id', $contactTeamleaderId)
                                            ->update([
                                                'companies' => json_encode($currentCompanies),
                                                'updated_at' => now()
                                            ]);

                                        $stats['contacts_updated']++;
                                        $stats['relationships_added']++;
                                    }
                                } else {
                                    // Nieuw contact aanmaken met deze company
                                    $firstName = $tlContact['first_name'] ?? null;
                                    $lastName = $tlContact['last_name'] ?? null;
                                    $fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

                                    $email = null;
                                    if (!empty($tlContact['emails']) && is_array($tlContact['emails'])) {
                                        $email = $tlContact['emails'][0]['email'] ?? null;
                                    }

                                    $phone = null;
                                    $mobile = null;
                                    if (!empty($tlContact['telephones']) && is_array($tlContact['telephones'])) {
                                        foreach ($tlContact['telephones'] as $telephone) {
                                            if ($telephone['type'] === 'phone') {
                                                $phone = $telephone['number'];
                                            } elseif ($telephone['type'] === 'mobile') {
                                                $mobile = $telephone['number'];
                                            }
                                        }
                                    }

                                    DB::table('teamleader_contacts')->insert([
                                        'teamleader_id' => $contactTeamleaderId,
                                        'first_name' => $firstName,
                                        'last_name' => $lastName,
                                        'full_name' => $fullName,
                                        'email' => $email,
                                        'phone' => $phone,
                                        'mobile' => $mobile,
                                        'position' => $tlContact['position'] ?? null,
                                        'language' => $tlContact['language'] ?? null,
                                        'companies' => json_encode([$company->teamleader_id]),
                                        'raw_data' => json_encode($tlContact),
                                        'synced_at' => now(),
                                        'is_imported' => false,
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);

                                    $stats['contacts_created']++;
                                    $stats['relationships_added']++;
                                }

                            } catch (\Exception $e) {
                                $stats['errors']++;
                                Log::error('Error processing contact in reverse sync', [
                                    'contact_id' => $tlContact['id'] ?? 'unknown',
                                    'company_id' => $company->teamleader_id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // Check voor meer pagina's
                        if ($contactsOnPage < 100) {
                            $hasMore = false;
                        } else {
                            $page++;
                            usleep(200000); // 0.2 seconden pauze
                        }
                    }

                    // Pauze tussen companies
                    usleep(300000); // 0.3 seconden

                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Error processing company in reverse sync', [
                        'company_id' => $company->teamleader_id,
                        'company_name' => $company->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('REVERSE sync of contact-company relationships completed', $stats);

            // Mark sync job als compleet
            if ($syncJob) {
                $syncJob->updateProgress(
                    $stats['companies_processed'],
                    $stats['relationships_added'],
                    $stats['errors'],
                    "Completed!"
                );
                $syncJob->markCompleted();
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('REVERSE sync of contact-company relationships failed', [
                'error' => $e->getMessage()
            ]);

            // Mark sync job als gefaald
            if ($syncJob) {
                $syncJob->markFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Sync ALLE milestones uit Teamleader naar database
     */
    public function syncAllMilestonesFromTeamleader(int $maxPages = 100): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total_fetched' => 0
        ];

        Log::info('Starting GLOBAL Teamleader milestones sync');

        try {
            DB::beginTransaction();

            // Haal ALLE projecten op uit onze cache
            $projects = TeamleaderProject::whereNotNull('teamleader_id')->get();

            Log::info('Syncing milestones for projects', ['project_count' => $projects->count()]);

            foreach ($projects as $project) {
                try {
                    // Haal milestones op voor dit project
                    $page = 1;
                    $hasMore = true;

                    while ($hasMore && $page <= 10) { // Max 10 pagina's per project
                        $response = TeamleaderService::listMilestonesForProject($project->teamleader_id, $page, 100);

                        if (empty($response['data'])) {
                            break;
                        }

                        $milestonesOnPage = count($response['data']);
                        $stats['total_fetched'] += $milestonesOnPage;

                        foreach ($response['data'] as $tlMilestone) {
                            try {
                                $result = $this->syncSingleMilestoneGlobal($tlMilestone, $project->teamleader_id);

                                if ($result === 'created') {
                                    $stats['synced']++;
                                } elseif ($result === 'updated') {
                                    $stats['updated']++;
                                } else {
                                    $stats['skipped']++;
                                }
                            } catch (\Exception $e) {
                                $stats['errors']++;
                                Log::error('Error syncing single milestone', [
                                    'teamleader_id' => $tlMilestone['id'] ?? 'unknown',
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        if ($milestonesOnPage < 100) {
                            $hasMore = false;
                        } else {
                            $page++;
                            usleep(200000); // 0.2 seconden
                        }
                    }

                    // Pauze tussen projecten
                    usleep(300000); // 0.3 seconden

                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Error syncing milestones for project', [
                        'project_id' => $project->teamleader_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            Log::info('GLOBAL Teamleader milestones sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GLOBAL Teamleader milestones sync failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Sync een enkele milestone (GLOBAL)
     */
    protected function syncSingleMilestoneGlobal(array $tlMilestone, string $projectId): string
    {
        $teamleaderId = $tlMilestone['id'];

        // Extract milestone data
        $name = $tlMilestone['name'] ?? 'Unnamed Milestone';
        $status = $tlMilestone['status'] ?? 'open';

        // Parse dates
        $startsOn = isset($tlMilestone['starts_on'])
            ? Carbon::parse($tlMilestone['starts_on'])
            : null;

        $dueOn = isset($tlMilestone['due_on'])
            ? Carbon::parse($tlMilestone['due_on'])
            : null;

        // Extract budget
        $budgetAmount = null;
        if (isset($tlMilestone['budget']['provided']['amount'])) {
            $budgetAmount = (float) $tlMilestone['budget']['provided']['amount'];
        }

        // Extract allocated time (in seconds)
        $allocatedTimeSeconds = null;
        if (isset($tlMilestone['allocated_time']['value'])) {
            $allocatedTimeSeconds = (int) $tlMilestone['allocated_time']['value'];
        }

        // Data voor insert/update
        $milestoneData = [
            'teamleader_project_id' => $projectId,
            'name' => $name,
            'status' => $status,
            'starts_on' => $startsOn,
            'due_on' => $dueOn,
            'invoicing_method' => $tlMilestone['invoicing_method'] ?? null,
            'budget_amount' => $budgetAmount,
            'allocated_time_seconds' => $allocatedTimeSeconds,
            'raw_data' => $tlMilestone,
            'synced_at' => now(),
        ];

        // Check of milestone al bestaat
        $existing = TeamleaderMilestone::where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update als niet geïmporteerd
            if (!$existing->is_imported) {
                $existing->update($milestoneData);
                return 'updated';
            }
            return 'skipped';
        }

        // Nieuwe milestone aanmaken
        TeamleaderMilestone::create(array_merge(
            ['teamleader_id' => $teamleaderId],
            $milestoneData
        ));

        return 'created';
    }

    /**
     * Sync ALLE tasks uit Teamleader naar database
     */
    public function syncAllTasksFromTeamleader(int $maxPages = 100): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total_fetched' => 0
        ];

        Log::info('Starting GLOBAL Teamleader tasks sync');

        try {
            DB::beginTransaction();

            // Haal ALLE milestones op uit onze cache
            $milestones = TeamleaderMilestone::whereNotNull('teamleader_id')->get();

            Log::info('Syncing tasks for milestones', ['milestone_count' => $milestones->count()]);

            foreach ($milestones as $milestone) {
                try {
                    // Haal tasks op voor deze milestone
                    $page = 1;
                    $hasMore = true;

                    while ($hasMore && $page <= 10) { // Max 10 pagina's per milestone
                        $response = TeamleaderService::listTasksForMilestone($milestone->teamleader_id, $page, 100);

                        if (empty($response['data'])) {
                            break;
                        }

                        $tasksOnPage = count($response['data']);
                        $stats['total_fetched'] += $tasksOnPage;

                        foreach ($response['data'] as $tlTask) {
                            try {
                                $result = $this->syncSingleTaskGlobal($tlTask, $milestone->teamleader_project_id, $milestone->teamleader_id);

                                if ($result === 'created') {
                                    $stats['synced']++;
                                } elseif ($result === 'updated') {
                                    $stats['updated']++;
                                } else {
                                    $stats['skipped']++;
                                }
                            } catch (\Exception $e) {
                                $stats['errors']++;
                                Log::error('Error syncing single task', [
                                    'teamleader_id' => $tlTask['id'] ?? 'unknown',
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        if ($tasksOnPage < 100) {
                            $hasMore = false;
                        } else {
                            $page++;
                            usleep(200000); // 0.2 seconden
                        }
                    }

                    // Pauze tussen milestones
                    usleep(100000); // 0.1 seconden

                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Error syncing tasks for milestone', [
                        'milestone_id' => $milestone->teamleader_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            Log::info('GLOBAL Teamleader tasks sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GLOBAL Teamleader tasks sync failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Sync een single task (GLOBAL)
     */
    protected function syncSingleTaskGlobal(array $tlTask, string $projectId, string $milestoneId): string
    {
        $teamleaderId = $tlTask['id'];

        // Extract task data
        $title = $tlTask['title'] ?? 'Unnamed Task';
        $description = $tlTask['description'] ?? null;
        $completed = $tlTask['completed'] ?? false;

        // Parse due date
        $dueOn = isset($tlTask['due_on'])
            ? Carbon::parse($tlTask['due_on'])
            : null;

        // Extract estimated duration (in minutes)
        $estimatedDurationMinutes = null;
        if (isset($tlTask['estimated_duration']['value'])) {
            $estimatedDurationMinutes = (int) $tlTask['estimated_duration']['value'];
        }

        // Data voor insert/update
        $taskData = [
            'teamleader_project_id' => $projectId,
            'teamleader_milestone_id' => $milestoneId,
            'title' => $title,
            'description' => $description,
            'completed' => $completed,
            'due_on' => $dueOn,
            'estimated_duration_minutes' => $estimatedDurationMinutes,
            'raw_data' => $tlTask,
            'synced_at' => now(),
        ];

        // Check of task al bestaat
        $existing = TeamleaderTask::where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update als niet geïmporteerd
            if (!$existing->is_imported) {
                $existing->update($taskData);
                return 'updated';
            }
            return 'skipped';
        }

        // Nieuwe task aanmaken
        TeamleaderTask::create(array_merge(
            ['teamleader_id' => $teamleaderId],
            $taskData
        ));

        return 'created';
    }

    /**
     * Sync ALLE time entries uit Teamleader naar database
     * FIXED: API filter werkt niet, we halen ALLES op en filteren via milestone IDs
     */
    public function syncAllTimeEntriesFromTeamleader(int $maxPages = 100): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'no_project_match' => 0,
            'errors' => 0,
            'total_fetched' => 0
        ];

        Log::info('Starting GLOBAL Teamleader time entries sync');

        try {
            DB::beginTransaction();

            // Haal ALLE time entries op ZONDER project filter (filter werkt toch niet!)
            $page = 1;
            $hasMore = true;

            Log::info('Fetching ALL time entries from Teamleader (API filter does not work)');

            while ($hasMore && $page <= $maxPages) {
                $response = TeamleaderService::listTimeTracking($page, 100);

                if (empty($response['data'])) {
                    break;
                }

                $entriesOnPage = count($response['data']);
                $stats['total_fetched'] += $entriesOnPage;

                foreach ($response['data'] as $tlEntry) {
                    try {
                        // Bepaal project ID via milestone/task lookup
                        $projectId = $this->findProjectIdForTimeEntry($tlEntry);

                        if (!$projectId) {
                            $stats['no_project_match']++;
                            Log::debug('Time entry has no matching project in cache', [
                                'time_entry_id' => $tlEntry['id'],
                                'subject' => $tlEntry['subject'] ?? 'none'
                            ]);
                            continue;
                        }

                        $result = $this->syncSingleTimeEntryGlobal($tlEntry, $projectId);

                        if ($result === 'created') {
                            $stats['synced']++;
                        } elseif ($result === 'updated') {
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Error syncing single time entry', [
                            'teamleader_id' => $tlEntry['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                if ($entriesOnPage < 100) {
                    $hasMore = false;
                } else {
                    $page++;
                    usleep(200000); // 0.2 seconden tussen pagina's
                }
            }

            DB::commit();

            Log::info('GLOBAL Teamleader time entries sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GLOBAL Teamleader time entries sync failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Find project ID for time entry via milestone or task lookup
     */
    protected function findProjectIdForTimeEntry(array $tlEntry): ?string
    {
        // Check if time entry has a subject (milestone or task)
        if (!isset($tlEntry['subject']) || !isset($tlEntry['subject']['id'])) {
            return null;
        }

        $subjectType = $tlEntry['subject']['type'] ?? null;
        $subjectId = $tlEntry['subject']['id'] ?? null;

        if (!$subjectId) {
            return null;
        }

        // Als subject een milestone is, zoek direct de project_id
        if ($subjectType === 'milestone') {
            $milestone = TeamleaderMilestone::where('teamleader_id', $subjectId)->first();
            if ($milestone) {
                return $milestone->teamleader_project_id;
            }
        }

        // Als subject een task is, zoek via milestone naar project
        if ($subjectType === 'task') {
            $task = TeamleaderTask::where('teamleader_id', $subjectId)->first();
            if ($task && $task->teamleader_milestone_id) {
                $milestone = TeamleaderMilestone::where('teamleader_id', $task->teamleader_milestone_id)->first();
                if ($milestone) {
                    return $milestone->teamleader_project_id;
                }
            }
        }

        return null;
    }

    /**
     * Sync een single time entry (GLOBAL)
     */
    protected function syncSingleTimeEntryGlobal(array $tlEntry, string $projectId): string
    {
        $teamleaderId = $tlEntry['id'];

        // Extract time entry data
        // Teamleader API heeft geen 'date' veld, gebruik started_at voor de datum
        $date = now(); // Default
        if (isset($tlEntry['started_at'])) {
            $date = Carbon::parse($tlEntry['started_at'])->startOfDay();
        } elseif (isset($tlEntry['date'])) {
            $date = Carbon::parse($tlEntry['date']);
        }

        // Duration kan direct een getal zijn OF een object met 'value' key
        $durationSeconds = 0;
        if (isset($tlEntry['duration'])) {
            if (is_array($tlEntry['duration']) && isset($tlEntry['duration']['value'])) {
                $durationSeconds = (int) $tlEntry['duration']['value'];
            } elseif (is_numeric($tlEntry['duration'])) {
                $durationSeconds = (int) $tlEntry['duration'];
            }
        }

        $description = $tlEntry['description'] ?? null;

        // Extract hourly rate
        $hourlyRate = null;
        $currency = 'EUR';
        if (isset($tlEntry['hourly_rate']['amount'])) {
            $hourlyRate = (float) $tlEntry['hourly_rate']['amount'];
            $currency = $tlEntry['hourly_rate']['currency'] ?? 'EUR';
        }

        // Extract user ID
        $userId = $tlEntry['user']['id'] ?? null;

        // Data voor insert/update
        $entryData = [
            'teamleader_project_id' => $projectId,
            'teamleader_user_id' => $userId,
            'date' => $date,
            'duration_seconds' => $durationSeconds,
            'description' => $description,
            'hourly_rate' => $hourlyRate,
            'currency' => $currency,
            'raw_data' => $tlEntry,
            'synced_at' => now(),
        ];

        // Check of time entry al bestaat
        $existing = TeamleaderTimeEntry::where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update als niet geïmporteerd
            if (!$existing->is_imported) {
                $existing->update($entryData);
                return 'updated';
            }
            return 'skipped';
        }

        // Nieuwe time entry aanmaken
        TeamleaderTimeEntry::create(array_merge(
            ['teamleader_id' => $teamleaderId],
            $entryData
        ));

        return 'created';
    }

    /**
     * Sync een enkele time entry ZONDER project check (voor global sync)
     * Slaat ALLE entries op, ook zonder project koppeling
     *
     * @param array $tlEntry Time entry van Teamleader API
     * @param string|null $projectId Project ID als gevonden, anders NULL
     * @return string 'created', 'updated', of 'skipped'
     */
    protected function syncSingleTimeEntryWithoutProjectCheck(array $tlEntry, ?string $projectId): string
    {
        $teamleaderId = $tlEntry['id'];

        // Parse datum
        $date = isset($tlEntry['started_at']) ? Carbon::parse($tlEntry['started_at'])->format('Y-m-d') : null;

        if (!$date) {
            Log::warning('Time entry zonder datum geskipt', ['teamleader_id' => $teamleaderId]);
            return 'skipped';
        }

        // Parse duration (in seconden)
        $durationSeconds = (int) ($tlEntry['duration'] ?? 0);

        // Parse description
        $description = $tlEntry['description'] ?? null;

        // Parse hourly rate
        $hourlyRate = 0;
        $currency = 'EUR';

        if (isset($tlEntry['hourly_rate']) && isset($tlEntry['hourly_rate']['amount'])) {
            $hourlyRate = (float) $tlEntry['hourly_rate']['amount'];
            $currency = $tlEntry['hourly_rate']['currency'] ?? 'EUR';
        }

        // Extract user ID
        $userId = $tlEntry['user']['id'] ?? null;

        // Data voor insert/update
        $entryData = [
            'teamleader_project_id' => $projectId, // Mag NULL zijn!
            'teamleader_user_id' => $userId,
            'date' => $date,
            'duration_seconds' => $durationSeconds,
            'description' => $description,
            'hourly_rate' => $hourlyRate,
            'currency' => $currency,
            'raw_data' => $tlEntry,
            'synced_at' => now(),
        ];

        // Check of time entry al bestaat
        $existing = TeamleaderTimeEntry::where('teamleader_id', $teamleaderId)->first();

        if ($existing) {
            // Update als niet geïmporteerd
            if (!$existing->is_imported) {
                $existing->update($entryData);
                return 'updated';
            }
            return 'skipped';
        }

        // Nieuwe time entry aanmaken (ZONDER project requirement!)
        TeamleaderTimeEntry::create(array_merge(
            ['teamleader_id' => $teamleaderId],
            $entryData
        ));

        return 'created';
    }

    /**
     * Sync ALLE time entries van Teamleader naar cache (global sync)
     * Dit haalt alle time entries op zonder project filtering
     *
     * @param int $maxPages Maximum aantal pagina's om op te halen (0 = unlimited)
     * @return array Statistics
     */
    public function syncTimeEntries(int $maxPages = 0): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'total_fetched' => 0
        ];

        $page = 1;
        $hasMore = true;

        Log::info('Starting GLOBAL Teamleader time entries sync');

        try {
            while ($hasMore && ($maxPages === 0 || $page <= $maxPages)) {
                echo "Fetching page {$page}..." . PHP_EOL;

                // Haal time entries op via API (100 per page)
                $response = TeamleaderService::listTimeTracking($page, 100);

                if (empty($response['data'])) {
                    Log::info('No more time entries found', ['page' => $page]);
                    break;
                }

                $entriesOnPage = count($response['data']);
                $stats['total_fetched'] += $entriesOnPage;

                Log::info('Processing time entries page', [
                    'page' => $page,
                    'count' => $entriesOnPage
                ]);

                echo "Processing {$entriesOnPage} entries from page {$page}..." . PHP_EOL;

                // Proces elke time entry
                foreach ($response['data'] as $tlEntry) {
                    try {
                        // Probeer project ID te vinden (mag NULL zijn voor global sync)
                        $projectId = $this->findProjectIdForTimeEntry($tlEntry);

                        // GLOBAL SYNC: Sla ALTIJD op, ook zonder project ID
                        // Later kunnen we entries koppelen via milestone/task cache
                        $result = $this->syncSingleTimeEntryWithoutProjectCheck($tlEntry, $projectId);

                        if ($result === 'created') {
                            $stats['synced']++;
                        } elseif ($result === 'updated') {
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }

                    } catch (\Exception $e) {
                        $stats['failed']++;
                        Log::error('Error syncing time entry', [
                            'teamleader_id' => $tlEntry['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                echo "Page {$page} complete. Synced: {$stats['synced']}, Updated: {$stats['updated']}, Skipped: {$stats['skipped']}, Failed: {$stats['failed']}" . PHP_EOL;

                // Check of er meer paginas zijn
                if ($entriesOnPage < 100) {
                    echo "Last page reached (only {$entriesOnPage} entries)" . PHP_EOL;
                    $hasMore = false;
                } else {
                    $page++;
                    // Kleine pauze om API rate limiting te respecteren (200 calls/min = 3.33 calls/sec)
                    usleep(350000); // 0.35 seconden tussen pages
                }
            }

            Log::info('GLOBAL time entries sync completed', $stats);

            return $stats;

        } catch (\Exception $e) {
            Log::error('GLOBAL time entries sync failed', [
                'error' => $e->getMessage(),
                'stats' => $stats
            ]);

            throw $e;
        }
    }

    /**
     * Sync milestones voor een specifieke customer
     * Haalt milestones op voor alle projecten van deze customer
     */
    public function syncMilestonesForCustomer(Customer $customer, int $maxPages = 10): array
    {
        $stats = [
            'synced' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        Log::info('Starting milestone sync for customer', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name
        ]);

        try {
            // Haal alle Teamleader projecten op voor deze customer uit cache
            $teamleaderProjects = TeamleaderProject::where('teamleader_company_id', $customer->teamleader_id)->get();

            if ($teamleaderProjects->isEmpty()) {
                Log::info('No projects found in cache for customer', [
                    'customer_id' => $customer->id
                ]);
                return $stats;
            }

            echo "Found {$teamleaderProjects->count()} projects for {$customer->name}" . PHP_EOL;

            // Voor elk project, haal milestones op via API
            foreach ($teamleaderProjects as $tlProject) {
                echo "Syncing milestones for project: {$tlProject->title}..." . PHP_EOL;

                $page = 1;
                $hasMore = true;

                while ($hasMore && $page <= $maxPages) {
                    try {
                        // Haal milestones op voor dit project
                        $response = TeamleaderService::listMilestonesForProject($tlProject->teamleader_id, $page, 100);

                        if (empty($response['data'])) {
                            break;
                        }

                        $milestonesOnPage = count($response['data']);

                        foreach ($response['data'] as $tlMilestone) {
                            try {
                                $result = $this->syncSingleMilestoneGlobal($tlMilestone, $tlProject->teamleader_id);

                                if ($result === 'created') {
                                    $stats['synced']++;
                                } elseif ($result === 'updated') {
                                    $stats['updated']++;
                                } else {
                                    $stats['skipped']++;
                                }

                            } catch (\Exception $e) {
                                $stats['errors']++;
                                Log::error('Error syncing milestone', [
                                    'milestone_id' => $tlMilestone['id'] ?? 'unknown',
                                    'project_id' => $tlProject->teamleader_id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // Check of er meer paginas zijn
                        if ($milestonesOnPage < 100) {
                            $hasMore = false;
                        } else {
                            $page++;
                            usleep(300000); // 0.3 sec pauze tussen API calls
                        }

                    } catch (\Exception $e) {
                        Log::error('Error fetching milestones page for project', [
                            'project_id' => $tlProject->teamleader_id,
                            'page' => $page,
                            'error' => $e->getMessage()
                        ]);
                        break;
                    }
                }

                echo "  → Synced {$stats['synced']} milestones" . PHP_EOL;
            }

            Log::info('Milestone sync for customer completed', array_merge(['customer_id' => $customer->id], $stats));

            return $stats;

        } catch (\Exception $e) {
            Log::error('Milestone sync for customer failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'stats' => $stats
            ]);

            throw $e;
        }
    }
}
