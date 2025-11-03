<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\TeamleaderContact;
use App\Models\TeamleaderMilestone;
use App\Models\TeamleaderProject;
use App\Models\TeamleaderTask;
use App\Models\TeamleaderTimeEntry;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TeamleaderImportService
{
    /**
     * Import companies from Teamleader Focus as Customers
     */
    public function importCompanies(): array
    {
        $imported = 0;
        $skipped = 0;
        $page = 1;
        $hasMore = true;

        DB::beginTransaction();
        try {
            while ($hasMore) {
                $response = TeamleaderService::listCompanies($page, 100);

                if (empty($response['data'])) {
                    $hasMore = false;
                    break;
                }

                foreach ($response['data'] as $tlCompany) {
                    try {
                        // Check of customer al bestaat op basis van teamleader_id
                        $existingCustomer = Customer::where('teamleader_id', $tlCompany['id'])->first();

                        if ($existingCustomer) {
                            $skipped++;
                            continue;
                        }

                        // Extract address data from Teamleader (addresses is een array)
                        $addresses = $tlCompany['addresses'] ?? [];
                        $street = null;
                        $addition = null;
                        $zipCode = null;
                        $city = null;
                        $country = null;

                        // Debug: Log complete addresses array
                        Log::info('Teamleader addresses debug', [
                            'company_name' => $tlCompany['name'],
                            'addresses_count' => count($addresses),
                            'addresses_data' => $addresses
                        ]);

                        // Pak het eerste adres uit de addresses array
                        if (!empty($addresses) && is_array($addresses)) {
                            $addressWrapper = $addresses[0] ?? null;

                            if ($addressWrapper && isset($addressWrapper['address'])) {
                                // Teamleader heeft een extra nesting: addresses[0]['address'] bevat de data!
                                $address = $addressWrapper['address'];

                                // Teamleader address structuur: line_1, postal_code, city, country
                                $street = $address['line_1'] ?? null;
                                $addition = $address['line_2'] ?? null;
                                $zipCode = $address['postal_code'] ?? null;
                                $city = $address['city'] ?? null;
                                $country = $address['country'] ?? null;
                            }
                        }

                        // Maak nieuwe customer aan (Teamleader Company → Progress Customer)
                        $customer = Customer::create([
                            'company_id' => null, // Laat leeg zodat het handmatig toegewezen kan worden
                            'teamleader_id' => $tlCompany['id'],
                            'name' => $tlCompany['name'] ?? 'Unnamed Customer',
                            'company' => $tlCompany['name'] ?? null, // Company naam in customer record
                            'vat_number' => $tlCompany['vat_number'] ?? null,
                            'email' => $tlCompany['emails'][0]['email'] ?? null,
                            'phone' => $tlCompany['telephones'][0]['number'] ?? null,
                            'website' => $tlCompany['website'] ?? null,
                            'street' => $street,
                            'addition' => $addition,
                            'zip_code' => $zipCode,
                            'city' => $city,
                            'country' => $country,
                            'language' => $tlCompany['language'] ?? 'nl',
                            'status' => $tlCompany['status'] === 'active' ? 'active' : 'inactive',
                            'is_active' => true,
                            'created_at' => isset($tlCompany['added_at']) ? Carbon::parse($tlCompany['added_at']) : now(),
                            'updated_at' => isset($tlCompany['updated_at']) ? Carbon::parse($tlCompany['updated_at']) : now(),
                        ]);

                        $imported++;

                        // TIJDELIJK UITGESCHAKELD: Importeer contactpersonen voor deze customer
                        // De contact import is te traag vanwege API rate limiting
                        // TODO: Implementeer dit als achtergrond job met queue
                        // $this->importContactsForCustomer($customer, $tlCompany['id']);

                    } catch (\Exception $e) {
                        Log::warning('Failed to import Teamleader company as customer', [
                            'company_id' => $tlCompany['id'],
                            'error' => $e->getMessage()
                        ]);
                        $skipped++;
                    }
                }

                // Check for next page - Teamleader API heeft meer data als data array niet leeg is
                // En we gaan door tot we minder dan 100 results krijgen
                if (count($response['data']) >= 100) {
                    $page++;
                    Log::info('Fetching next page', ['page' => $page, 'current_count' => count($response['data'])]);
                } else {
                    $hasMore = false;
                    Log::info('Last page reached', ['final_count' => count($response['data'])]);
                }
            }

            DB::commit();
            Log::info('Teamleader companies imported as customers', [
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing Teamleader companies as customers', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import selected companies from Teamleader Focus as Customers
     */
    public function importSelectedCompanies(array $companyIds, array $options = []): array
    {
        $imported = 0;
        $skipped = 0;
        $contactsImported = 0;
        $projectsImported = 0;
        $timeEntriesImported = 0;

        // Parse options met defaults
        $importContacts = $options['import_contacts'] ?? false;
        $importProjects = $options['import_projects'] ?? false;
        $importTimeEntries = $options['import_time_entries'] ?? false;

        DB::beginTransaction();
        try {
            foreach ($companyIds as $companyId) {
                try {
                    // Haal company details op van Teamleader
                    $response = TeamleaderService::getCompany($companyId);
                    $tlCompany = $response['data'] ?? $response; // Support both wrapped and unwrapped responses

                    // Extract address data from Teamleader (addresses is een array)
                    $addresses = $tlCompany['addresses'] ?? [];
                    $street = null;
                    $addition = null;
                    $zipCode = null;
                    $city = null;
                    $country = null;

                    // Debug: Log complete addresses array
                    Log::info('Teamleader addresses debug (selected)', [
                        'company_name' => $tlCompany['name'],
                        'addresses_count' => count($addresses),
                        'addresses_data' => $addresses
                    ]);

                    // Pak het eerste adres uit de addresses array
                    if (!empty($addresses) && is_array($addresses)) {
                        $addressWrapper = $addresses[0] ?? null;

                        if ($addressWrapper && isset($addressWrapper['address'])) {
                            // Teamleader heeft een extra nesting: addresses[0]['address'] bevat de data!
                            $address = $addressWrapper['address'];

                            // Teamleader address structuur: line_1, postal_code, city, country
                            $street = $address['line_1'] ?? null;
                            $addition = $address['line_2'] ?? null;
                            $zipCode = $address['postal_code'] ?? null;
                            $city = $address['city'] ?? null;
                            $country = $address['country'] ?? null;
                        }
                    }

                    // Prepared customer data
                    $customerData = [
                        'teamleader_id' => $tlCompany['id'],
                        'name' => $tlCompany['name'] ?? 'Unnamed Customer',
                        'company' => $tlCompany['name'] ?? null,
                        'vat_number' => $tlCompany['vat_number'] ?? null,
                        'email' => $tlCompany['emails'][0]['email'] ?? null,
                        'phone' => $tlCompany['telephones'][0]['number'] ?? null,
                        'website' => $tlCompany['website'] ?? null,
                        'street' => $street,
                        'addition' => $addition,
                        'zip_code' => $zipCode,
                        'city' => $city,
                        'country' => $country,
                        'language' => $tlCompany['language'] ?? 'nl',
                        'status' => $tlCompany['status'] === 'active' ? 'active' : 'inactive',
                        'is_active' => true,
                        'updated_at' => isset($tlCompany['updated_at']) ? Carbon::parse($tlCompany['updated_at']) : now(),
                    ];

                    // Check of customer al bestaat - UPDATE of CREATE
                    $existingCustomer = Customer::where('teamleader_id', $companyId)->first();

                    if ($existingCustomer) {
                        // UPDATE bestaande customer met nieuwe data uit Teamleader
                        $existingCustomer->update($customerData);
                        $customer = $existingCustomer;

                        Log::info('Updated existing customer from Teamleader', [
                            'customer_id' => $customer->id,
                            'customer_name' => $customer->name,
                            'teamleader_id' => $companyId
                        ]);

                        $imported++; // Tel ook updates mee als "imported"
                    } else {
                        // CREATE nieuwe customer
                        $customerData['company_id'] = null; // Laat leeg zodat het handmatig toegewezen kan worden
                        $customerData['created_at'] = isset($tlCompany['added_at']) ? Carbon::parse($tlCompany['added_at']) : now();

                        $customer = Customer::create($customerData);

                        Log::info('Created new customer from Teamleader', [
                            'customer_id' => $customer->id,
                            'customer_name' => $customer->name,
                            'teamleader_id' => $companyId
                        ]);

                        $imported++;
                    }

                    // 🔄 CASCADE IMPORTS - One-Click Complete Import!
                    Log::info('Starting cascade imports for customer', [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'options' => compact('importContacts', 'importProjects', 'importTimeEntries')
                    ]);

                    // STEP 1: Import Contacts (if enabled)
                    if ($importContacts) {
                        try {
                            $contactResult = $this->importContactsForCustomer($customer, $companyId);
                            $contactsImported += $contactResult['imported'];

                            Log::info('Contacts imported for customer', [
                                'customer_id' => $customer->id,
                                'contacts_imported' => $contactResult['imported'],
                                'contacts_skipped' => $contactResult['skipped']
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to import contacts for customer', [
                                'customer_id' => $customer->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // STEP 2: Import Projects (if enabled)
                    if ($importProjects) {
                        try {
                            $projectResult = $this->importProjectsForCustomer($customer, $companyId, $importTimeEntries);
                            $projectsImported += $projectResult['imported'];
                            $timeEntriesImported += $projectResult['time_entries_imported'] ?? 0;

                            Log::info('Projects imported for customer', [
                                'customer_id' => $customer->id,
                                'projects_imported' => $projectResult['imported'],
                                'time_entries_imported' => $projectResult['time_entries_imported'] ?? 0
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to import projects for customer', [
                                'customer_id' => $customer->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    Log::warning('Failed to import selected company', [
                        'company_id' => $companyId,
                        'error' => $e->getMessage()
                    ]);
                    $skipped++;
                }
            }

            DB::commit();
            Log::info('Selected Teamleader companies imported with cascade', [
                'imported' => $imported,
                'skipped' => $skipped,
                'contacts_imported' => $contactsImported,
                'projects_imported' => $projectsImported,
                'time_entries_imported' => $timeEntriesImported,
                'total_selected' => count($companyIds)
            ]);

            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'contacts_imported' => $contactsImported,
                'projects_imported' => $projectsImported,
                'time_entries_imported' => $timeEntriesImported,
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing selected companies', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import contacts from Teamleader as Customers
     */
    public function importContacts(): array
    {
        $imported = 0;
        $skipped = 0;
        $page = 1;
        $hasMore = true;

        DB::beginTransaction();
        try {
            while ($hasMore) {
                $response = TeamleaderService::listContacts($page, 100);

                if (empty($response['data'])) {
                    $hasMore = false;
                    break;
                }

                foreach ($response['data'] as $tlContact) {
                    try {
                        // Check of contact al bestaat
                        $existingCustomer = Customer::where('teamleader_id', $tlContact['id'])->first();

                        if ($existingCustomer) {
                            $skipped++;
                            continue;
                        }

                        // Vind gekoppelde company (als er een is)
                        $companyId = null;
                        if (!empty($tlContact['companies']) && isset($tlContact['companies'][0]['id'])) {
                            $company = Company::where('teamleader_id', $tlContact['companies'][0]['id'])->first();
                            if ($company) {
                                $companyId = $company->id;
                            }
                        }

                        // Default company voor deze user
                        if (!$companyId) {
                            $companyId = Auth::user()->company_id;
                        }

                        // Maak customer aan
                        Customer::create([
                            'company_id' => $companyId,
                            'teamleader_id' => $tlContact['id'],
                            'name' => trim(($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? '')),
                            'email' => $tlContact['emails'][0]['email'] ?? null,
                            'phone' => $tlContact['telephones'][0]['number'] ?? null,
                            'contact_person' => trim(($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? '')),
                            'status' => 'active',
                            'is_active' => true,
                            'created_at' => isset($tlContact['added_at']) ? Carbon::parse($tlContact['added_at']) : now(),
                            'updated_at' => isset($tlContact['updated_at']) ? Carbon::parse($tlContact['updated_at']) : now(),
                        ]);

                        $imported++;

                    } catch (\Exception $e) {
                        Log::warning('Failed to import Teamleader contact', [
                            'contact_id' => $tlContact['id'],
                            'error' => $e->getMessage()
                        ]);
                        $skipped++;
                    }
                }

                // Check for next page - Teamleader API heeft meer data als data array niet leeg is
                // En we gaan door tot we minder dan 100 results krijgen
                if (count($response['data']) >= 100) {
                    $page++;
                    Log::info('Fetching next page', ['page' => $page, 'current_count' => count($response['data'])]);
                } else {
                    $hasMore = false;
                    Log::info('Last page reached', ['final_count' => count($response['data'])]);
                }
            }

            DB::commit();
            Log::info('Teamleader contacts import completed', [
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing Teamleader contacts', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import projects from Teamleader Focus
     */
    public function importProjects(): array
    {
        $imported = 0;
        $skipped = 0;
        $page = 1;
        $hasMore = true;

        DB::beginTransaction();
        try {
            while ($hasMore) {
                $response = TeamleaderService::listProjects($page, 100);

                if (empty($response['data'])) {
                    $hasMore = false;
                    break;
                }

                foreach ($response['data'] as $tlProject) {
                    try {
                        // Check of project al bestaat
                        $existingProject = Project::where('teamleader_id', $tlProject['id'])->first();

                        if ($existingProject) {
                            $skipped++;
                            continue;
                        }

                        // Vind gekoppelde customer
                        $customerId = null;
                        if (isset($tlProject['customer']['id'])) {
                            $customer = Customer::where('teamleader_id', $tlProject['customer']['id'])->first();
                            if ($customer) {
                                $customerId = $customer->id;
                            }
                        }

                        // Skip als geen customer gevonden
                        if (!$customerId) {
                            Log::warning('Skipping Teamleader project: no customer found', [
                                'project_id' => $tlProject['id'],
                                'project_name' => $tlProject['title'] ?? 'Unknown'
                            ]);
                            $skipped++;
                            continue;
                        }

                        // Extract budget informatie van Teamleader (alleen totale waarde)
                        $totalBudget = isset($tlProject['budget']['provided']['amount'])
                            ? (float) $tlProject['budget']['provided']['amount']
                            : null;

                        // Maak project aan
                        $project = Project::create([
                            'company_id' => Auth::user()->company_id,
                            'customer_id' => $customerId,
                            'teamleader_id' => $tlProject['id'],
                            'name' => $tlProject['title'] ?? 'Unnamed Project',
                            'description' => $tlProject['description'] ?? null,
                            'status' => $this->mapProjectStatus($tlProject['status'] ?? 'active'),
                            'start_date' => isset($tlProject['starts_on']) ? Carbon::parse($tlProject['starts_on']) : null,
                            'end_date' => isset($tlProject['due_on']) ? Carbon::parse($tlProject['due_on']) : null,
                            'total_value' => $totalBudget,
                            // monthly_fee moet handmatig ingesteld worden door gebruiker
                            'created_by' => Auth::id(),
                            'created_at' => isset($tlProject['created_at']) ? Carbon::parse($tlProject['created_at']) : now(),
                            'updated_at' => isset($tlProject['updated_at']) ? Carbon::parse($tlProject['updated_at']) : now(),
                        ]);

                        $imported++;

                    } catch (\Exception $e) {
                        Log::warning('Failed to import Teamleader project', [
                            'project_id' => $tlProject['id'],
                            'error' => $e->getMessage()
                        ]);
                        $skipped++;
                    }
                }

                // Check for next page - Teamleader API heeft meer data als data array niet leeg is
                // En we gaan door tot we minder dan 100 results krijgen
                if (count($response['data']) >= 100) {
                    $page++;
                    Log::info('Fetching next page', ['page' => $page, 'current_count' => count($response['data'])]);
                } else {
                    $hasMore = false;
                    Log::info('Last page reached', ['final_count' => count($response['data'])]);
                }
            }

            DB::commit();
            Log::info('Teamleader projects import completed', [
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing Teamleader projects', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import selected projects from Teamleader Focus
     */
    public function importSelectedProjects(array $projectIds, int $customerId): array
    {
        $imported = 0;
        $skipped = 0;

        // Haal customer op
        $customer = Customer::findOrFail($customerId);

        DB::beginTransaction();
        try {
            foreach ($projectIds as $projectId) {
                try {
                    // Check of project al bestaat
                    $existingProject = Project::where('teamleader_id', $projectId)->first();

                    if ($existingProject) {
                        Log::info('Project already imported', ['project_id' => $projectId]);
                        $skipped++;
                        continue;
                    }

                    // Haal project details op uit DATABASE (niet API!)
                    $tlProject = TeamleaderProject::where('teamleader_id', $projectId)->first();

                    if (!$tlProject) {
                        Log::warning('Project not found in database cache', ['project_id' => $projectId]);
                        $skipped++;
                        continue;
                    }

                    // Check of customer_id klopt
                    if ($tlProject->customer_id !== $customerId) {
                        Log::warning('Project customer mismatch', [
                            'project_id' => $projectId,
                            'expected_customer' => $customerId,
                            'actual_customer' => $tlProject->customer_id
                        ]);
                        $skipped++;
                        continue;
                    }

                    // Map Teamleader status naar Progress status
                    $status = match($tlProject->status) {
                        'done' => 'completed',  // Teamleader "done" → Progress "completed"
                        'active' => 'active',
                        'on_hold' => 'on_hold',
                        'cancelled' => 'cancelled',
                        default => 'active'
                    };

                    // Maak project aan in Progress
                    $project = Project::create([
                        'company_id' => Auth::user()->company_id,
                        'customer_id' => $tlProject->customer_id,
                        'teamleader_id' => $tlProject->teamleader_id,
                        'name' => $tlProject->title ?? 'Unnamed Project',
                        'description' => $tlProject->description ?? null,
                        'status' => $status,
                        'start_date' => $tlProject->starts_on,
                        'end_date' => $tlProject->due_on,
                        'total_value' => $tlProject->budget_amount ?? 0,
                        'monthly_fee' => $tlProject->budget_amount ?? null, // Gebruik budget als monthly_fee
                        'created_by' => Auth::id(),
                        'created_at' => $tlProject->created_at ?? now(),
                        'updated_at' => $tlProject->updated_at ?? now(),
                    ]);

                    // Update teamleader_projects table: mark als imported
                    $tlProject->update([
                        'is_imported' => true,
                        'imported_as_project_id' => $project->id,
                        'imported_at' => now()
                    ]);

                    Log::info('Project imported successfully from database', [
                        'project_id' => $project->id,
                        'teamleader_id' => $projectId,
                        'customer' => $customer->name,
                        'budget' => $tlProject->budget_amount
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    Log::warning('Failed to import Teamleader project', [
                        'project_id' => $projectId,
                        'error' => $e->getMessage()
                    ]);
                    $skipped++;
                }
            }

            DB::commit();

            Log::info('Selected projects import completed', [
                'customer_id' => $customerId,
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing selected Teamleader projects', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import time tracking from Teamleader Focus
     */
    public function importTimeTracking(): array
    {
        $imported = 0;
        $skipped = 0;
        $page = 1;
        $hasMore = true;

        DB::beginTransaction();
        try {
            while ($hasMore) {
                $response = TeamleaderService::listTimeTracking($page, 100);

                if (empty($response['data'])) {
                    $hasMore = false;
                    break;
                }

                foreach ($response['data'] as $tlTime) {
                    try {
                        // Check of time entry al bestaat
                        $existingEntry = TimeEntry::where('teamleader_id', $tlTime['id'])->first();

                        if ($existingEntry) {
                            $skipped++;
                            continue;
                        }

                        // Vind gekoppeld project
                        $projectId = null;
                        if (isset($tlTime['work_type']['id'])) {
                            $project = Project::where('teamleader_id', $tlTime['work_type']['id'])->first();
                            if ($project) {
                                $projectId = $project->id;
                            }
                        }

                        // Skip als geen project gevonden
                        if (!$projectId) {
                            $skipped++;
                            continue;
                        }

                        // Vind user (of gebruik current user)
                        $userId = Auth::id();
                        if (isset($tlTime['user']['id'])) {
                            $user = User::where('teamleader_id', $tlTime['user']['id'])->first();
                            if ($user) {
                                $userId = $user->id;
                            }
                        }

                        // Bereken uren en minuten uit duration (in seconds)
                        $totalMinutes = isset($tlTime['duration']) ? round($tlTime['duration'] / 60) : 0;
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;

                        // Maak time entry aan
                        $timeEntry = TimeEntry::create([
                            'user_id' => $userId,
                            'project_id' => $projectId,
                            'teamleader_id' => $tlTime['id'],
                            'entry_date' => isset($tlTime['started_on']) ? Carbon::parse($tlTime['started_on']) : now(),
                            'hours' => $hours,
                            'minutes' => $minutes,
                            'description' => $tlTime['description'] ?? 'Imported from Teamleader',
                            'is_billable' => isset($tlTime['invoiceable']) && $tlTime['invoiceable'] ? 'billable' : 'non_billable',
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => now(),
                            'created_at' => isset($tlTime['created_at']) ? Carbon::parse($tlTime['created_at']) : now(),
                            'updated_at' => isset($tlTime['updated_at']) ? Carbon::parse($tlTime['updated_at']) : now(),
                        ]);

                        // Log activity voor geïmporteerde time entry
                        $totalHours = round($totalMinutes / 60, 2);
                        ProjectActivity::log(
                            $projectId,
                            'time_entry_added',
                            'imported ' . $totalHours . ' hours from Teamleader',
                            null,
                            'time_entry',
                            $timeEntry->id
                        );

                        $imported++;

                    } catch (\Exception $e) {
                        Log::warning('Failed to import Teamleader time tracking', [
                            'time_id' => $tlTime['id'],
                            'error' => $e->getMessage()
                        ]);
                        $skipped++;
                    }
                }

                // Check for next page - Teamleader API heeft meer data als data array niet leeg is
                // En we gaan door tot we minder dan 100 results krijgen
                if (count($response['data']) >= 100) {
                    $page++;
                    Log::info('Fetching next page', ['page' => $page, 'current_count' => count($response['data'])]);
                } else {
                    $hasMore = false;
                    Log::info('Last page reached', ['final_count' => count($response['data'])]);
                }
            }

            DB::commit();
            Log::info('Teamleader time tracking import completed', [
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing Teamleader time tracking', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Map Teamleader project status to our status
     */
    private function mapProjectStatus(string $teamleaderStatus): string
    {
        return match($teamleaderStatus) {
            'ongoing' => 'active',
            'on_hold' => 'on_hold',
            'done' => 'completed',
            'cancelled' => 'cancelled',
            default => 'draft'
        };
    }

    /**
     * Import users from Teamleader Focus (zonder emails te versturen)
     */
    public function importUsers(): array
    {
        $imported = 0;
        $skipped = 0;
        $page = 1;
        $hasMore = true;

        DB::beginTransaction();
        try {
            while ($hasMore) {
                $response = TeamleaderService::listContacts($page, 100);

                if (empty($response['data'])) {
                    $hasMore = false;
                    break;
                }

                foreach ($response['data'] as $tlContact) {
                    try {
                        // Skip als geen email (verplicht voor users)
                        if (empty($tlContact['emails'][0]['email'])) {
                            $skipped++;
                            continue;
                        }

                        $email = $tlContact['emails'][0]['email'];

                        // Check of user al bestaat op basis van email of teamleader_id
                        $existingUser = User::where('email', $email)
                            ->orWhere('teamleader_id', $tlContact['id'])
                            ->first();

                        if ($existingUser) {
                            $skipped++;
                            continue;
                        }

                        // Maak nieuwe user aan (ZONDER email notificatie)
                        User::create([
                            'company_id' => null, // Laat leeg zodat het handmatig toegewezen kan worden
                            'teamleader_id' => $tlContact['id'],
                            'name' => trim(($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? '')),
                            'email' => $email,
                            'email_verified_at' => now(), // Skip email verificatie
                            'password' => bcrypt(\Illuminate\Support\Str::random(32)), // Random wachtwoord
                            'role' => 'user', // Default role
                            'is_active' => true,
                            'created_at' => isset($tlContact['added_at']) ? Carbon::parse($tlContact['added_at']) : now(),
                            'updated_at' => isset($tlContact['updated_at']) ? Carbon::parse($tlContact['updated_at']) : now(),
                        ]);

                        $imported++;

                    } catch (\Exception $e) {
                        Log::warning('Failed to import Teamleader user', [
                            'contact_id' => $tlContact['id'],
                            'error' => $e->getMessage()
                        ]);
                        $skipped++;
                    }
                }

                // Check for next page
                if (count($response['data']) >= 100) {
                    $page++;
                    Log::info('Fetching next page of users', ['page' => $page, 'current_count' => count($response['data'])]);
                } else {
                    $hasMore = false;
                    Log::info('Last page of users reached', ['final_count' => count($response['data'])]);
                }
            }

            DB::commit();
            Log::info('Teamleader users import completed', [
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing Teamleader users', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import selected users from Teamleader Focus (zonder emails te versturen)
     */
    public function importSelectedUsers(array $userIds): array
    {
        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($userIds as $userId) {
                try {
                    // Haal contact details op uit DATABASE (niet API!)
                    $tlContact = TeamleaderContact::where('teamleader_id', $userId)->first();

                    if (!$tlContact) {
                        Log::warning('Contact not found in database cache', ['user_id' => $userId]);
                        $skipped++;
                        continue;
                    }

                    // Skip als geen email
                    if (empty($tlContact->email)) {
                        $skipped++;
                        continue;
                    }

                    // Check of user al bestaat
                    $existingUser = User::where('email', $tlContact->email)
                        ->orWhere('teamleader_id', $userId)
                        ->first();

                    if ($existingUser) {
                        $skipped++;
                        continue;
                    }

                    // Maak nieuwe user aan (ZONDER email notificatie)
                    User::create([
                        'company_id' => null, // Laat leeg zodat het handmatig toegewezen kan worden
                        'teamleader_id' => $tlContact->teamleader_id,
                        'name' => $tlContact->full_name ?? trim(($tlContact->first_name ?? '') . ' ' . ($tlContact->last_name ?? '')),
                        'email' => $tlContact->email,
                        'email_verified_at' => now(), // Skip email verificatie
                        'password' => bcrypt(\Illuminate\Support\Str::random(32)), // Random wachtwoord
                        'role' => 'user', // Default role
                        'is_active' => true,
                        'created_at' => $tlContact->created_at ?? now(),
                        'updated_at' => $tlContact->updated_at ?? now(),
                    ]);

                    // Update teamleader_contacts table: mark als imported
                    $tlContact->update([
                        'is_imported' => true,
                        'imported_at' => now()
                    ]);

                    Log::info('User imported successfully from database', [
                        'teamleader_id' => $userId,
                        'email' => $tlContact->email,
                        'name' => $tlContact->full_name
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    Log::warning('Failed to import selected user', [
                        'user_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                    $skipped++;
                }
            }

            DB::commit();
            Log::info('Selected Teamleader users imported', [
                'imported' => $imported,
                'skipped' => $skipped,
                'total_selected' => count($userIds)
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error importing selected users', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import contactpersonen voor een specifieke customer uit Teamleader
     *
     * @param Customer $customer De customer waarvoor contacten geïmporteerd worden
     * @param string $teamleaderCompanyId Het Teamleader company ID
     * @return array Aantal geïmporteerde en overgeslagen contacten
     */
    protected function importContactsForCustomer(Customer $customer, string $teamleaderCompanyId): array
    {
        $imported = 0;
        $skipped = 0;
        $page = 1;
        $hasMore = true;
        $maxPages = 3; // Limiteer tot 3 pagina's (300 contacten) om rate limit en timeout te voorkomen
        $maxContactsPerCompany = 50; // Maximum 50 contacten per company om performance te waarborgen

        try {
            while ($hasMore && $page <= $maxPages) {
                // Haal ALLE contacten op uit Teamleader (paginated)
                // We moeten zelf filteren omdat de Teamleader filter niet betrouwbaar werkt
                $response = TeamleaderService::listContacts($page, 100);

                if (empty($response['data'])) {
                    $hasMore = false;
                    break;
                }

                foreach ($response['data'] as $tlContactBasic) {
                    try {
                        // Rate limiting: sleep 100ms tussen API calls om rate limit te voorkomen
                        usleep(100000); // 100ms = 10 calls per seconde

                        // Haal volledige contact info op om te zien welke companies eraan gekoppeld zijn
                        $fullContactResponse = TeamleaderService::getContact($tlContactBasic['id']);
                        $tlContact = $fullContactResponse['data'] ?? $fullContactResponse;

                        // Check of dit contact aan deze company gekoppeld is
                        $linkedToThisCompany = false;
                        if (isset($tlContact['companies']) && is_array($tlContact['companies'])) {
                            foreach ($tlContact['companies'] as $linkedCompany) {
                                // Check zowel 'id' als nested 'customer.id' structuur
                                $companyId = $linkedCompany['customer']['id'] ?? $linkedCompany['id'] ?? null;
                                if ($companyId === $teamleaderCompanyId) {
                                    $linkedToThisCompany = true;
                                    break;
                                }
                            }
                        }

                        // Skip als niet aan deze company gekoppeld
                        if (!$linkedToThisCompany) {
                            continue;
                        }

                        // Debug: Log eerste match om te verifiëren
                        if ($imported === 0) {
                            Log::info('Teamleader contact matched to company', [
                                'customer_name' => $customer->name,
                                'teamleader_company_id' => $teamleaderCompanyId,
                                'contact_name' => ($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? ''),
                                'contact_email' => $tlContact['emails'][0]['email'] ?? 'no email',
                                'companies_count' => count($tlContact['companies'] ?? [])
                            ]);
                        }

                        // Skip als geen email
                        $email = $tlContact['emails'][0]['email'] ?? null;
                        if (empty($email)) {
                            $skipped++;
                            continue;
                        }

                        // Check of contact AL BESTAAT (globaal - niet alleen voor deze customer)
                        // Een contact kan maar 1x geïmporteerd worden en hoort bij 1 customer
                        $existingContact = Contact::where('teamleader_id', $tlContact['id'])
                            ->orWhere('email', $email)
                            ->first();

                        if ($existingContact) {
                            $skipped++;
                            continue;
                        }

                        // Extract phone number
                        $phone = null;
                        if (!empty($tlContact['telephones'])) {
                            $phone = $tlContact['telephones'][0]['number'] ?? null;
                        }

                        // Bepaal of dit de primary contact is (eerste contact = primary)
                        $isPrimary = Contact::where('customer_id', $customer->id)->count() === 0;

                        // Maak nieuwe contact aan
                        Contact::create([
                            'customer_id' => $customer->id,
                            'teamleader_id' => $tlContact['id'],
                            'name' => trim(($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? '')),
                            'email' => $email,
                            'phone' => $phone,
                            'position' => null, // Teamleader heeft geen position field in basic response
                            'notes' => null,
                            'is_primary' => $isPrimary,
                            'is_active' => true,
                            'created_at' => isset($tlContact['added_at']) ? Carbon::parse($tlContact['added_at']) : now(),
                            'updated_at' => isset($tlContact['updated_at']) ? Carbon::parse($tlContact['updated_at']) : now(),
                        ]);

                        $imported++;

                        // Stop als we maximum aantal contacten bereikt hebben voor deze company
                        if ($imported >= $maxContactsPerCompany) {
                            Log::info('Maximum contacts per company reached', [
                                'customer_id' => $customer->id,
                                'max_contacts' => $maxContactsPerCompany
                            ]);
                            $hasMore = false;
                            break 2; // Break uit beide loops (foreach en while)
                        }

                    } catch (\Exception $e) {
                        Log::warning('Failed to import contact for customer', [
                            'customer_id' => $customer->id,
                            'contact_id' => $tlContact['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        $skipped++;
                    }
                }

                // Check for next page
                if (count($response['data']) >= 100) {
                    $page++;
                } else {
                    $hasMore = false;
                }
            }

            Log::info('Contacts imported for customer', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            Log::error('Error importing contacts for customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            // Check of het een rate limit error is
            if (str_contains($e->getMessage(), 'rate limit')) {
                Log::warning('Teamleader API rate limit reached, stopping contact import', [
                    'customer_id' => $customer->id,
                    'imported_so_far' => $imported
                ]);
            }

            // Don't throw - we want to continue with other imports
            return ['imported' => $imported, 'skipped' => $skipped];
        }
    }

    /**
     * Eenvoudige methode om contacten te importeren voor een specifieke customer
     * Deze methode gebruikt de gefilterde API zonder alle contacten door te lopen
     *
     * @param Customer $customer
     * @return array
     */
    public function importContactsForCustomerSimple(Customer $customer): array
    {
        if (!$customer->teamleader_id) {
            return ['imported' => 0, 'skipped' => 0, 'message' => 'Customer has no Teamleader ID'];
        }

        $imported = 0;
        $skipped = 0;
        $maxPages = 10; // Maximum 10 pages (1000 contacts) om timeout te voorkomen
        $pageSize = 100;

        try {
            Log::info('Starting contact import for customer', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'teamleader_id' => $customer->teamleader_id
            ]);

            // Fetch contacts page by page (listContactsForCompany is BROKEN, use listContacts instead)
            for ($page = 1; $page <= $maxPages; $page++) {
                $response = TeamleaderService::listContacts($page, $pageSize);

                if (empty($response['data'])) {
                    break; // No more contacts
                }

                foreach ($response['data'] as $tlContactBasic) {
                    try {
                        // Skip als geen email
                        $email = $tlContactBasic['emails'][0]['email'] ?? null;
                        if (empty($email)) {
                            $skipped++;
                            continue;
                        }

                        // Fetch FULL contact details (includes company relationships)
                        usleep(200000); // 200ms delay to prevent API rate limiting
                        $fullContactResponse = TeamleaderService::getContact($tlContactBasic['id']);
                        $tlContact = $fullContactResponse['data'] ?? $fullContactResponse;

                        // Check if this contact is linked to OUR customer's company
                        $isLinkedToCustomer = false;
                        if (isset($tlContact['companies']) && is_array($tlContact['companies'])) {
                            foreach ($tlContact['companies'] as $linkedCompany) {
                                $companyId = $linkedCompany['company']['id'] ?? $linkedCompany['customer']['id'] ?? $linkedCompany['id'] ?? null;

                                if ($companyId === $customer->teamleader_id) {
                                    $isLinkedToCustomer = true;
                                    break;
                                }
                            }
                        }

                        // Skip if not linked to our customer
                        if (!$isLinkedToCustomer) {
                            continue;
                        }

                        // Check of contact al bestaat
                        $existingContact = Contact::where('teamleader_id', $tlContact['id'])
                            ->orWhere('email', $email)
                            ->first();

                        if ($existingContact) {
                            $skipped++;
                            continue;
                        }

                        // Extract data
                        $phone = $tlContact['telephones'][0]['number'] ?? null;
                        $position = null;

                        // Try to get position from company relationship
                        if (isset($tlContact['companies']) && is_array($tlContact['companies'])) {
                            foreach ($tlContact['companies'] as $linkedCompany) {
                                $companyId = $linkedCompany['company']['id'] ?? null;
                                if ($companyId === $customer->teamleader_id) {
                                    $position = $linkedCompany['position'] ?? null;
                                    break;
                                }
                            }
                        }

                        // Bepaal of primary
                        $isPrimary = Contact::where('customer_id', $customer->id)->count() === 0;

                        // Maak contact aan
                        Contact::create([
                            'customer_id' => $customer->id,
                            'teamleader_id' => $tlContact['id'],
                            'name' => trim(($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? '')),
                            'email' => $email,
                            'phone' => $phone,
                            'position' => $position,
                            'notes' => null,
                            'is_primary' => $isPrimary,
                            'is_active' => true,
                            'created_at' => isset($tlContact['added_at']) ? Carbon::parse($tlContact['added_at']) : now(),
                            'updated_at' => isset($tlContact['updated_at']) ? Carbon::parse($tlContact['updated_at']) : now(),
                        ]);

                        $imported++;

                    } catch (\Exception $e) {
                        Log::warning('Failed to import contact', [
                            'customer_id' => $customer->id,
                            'contact_id' => $tlContactBasic['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        $skipped++;
                    }
                }

                // Stop als we minder dan pageSize hebben (laatste pagina)
                if (count($response['data']) < $pageSize) {
                    break;
                }

                // 2 second delay between pages
                sleep(2);
            }

            Log::info('Contact import completed', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            Log::error('Error in contact import', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
            return ['imported' => 0, 'skipped' => 0, 'message' => $e->getMessage()];
        }
    }

    /**
     * Import selected companies WITH their contacts
     * Dit importeert companies en direct de contacten die aan deze companies gekoppeld zijn
     */
    public function importCompaniesWithContacts(array $companyIds = []): array
    {
        $stats = [
            'companies_imported' => 0,
            'companies_skipped' => 0,
            'contacts_imported' => 0,
            'contacts_skipped' => 0,
            'errors' => []
        ];

        try {
            // Eerst companies importeren
            if (empty($companyIds)) {
                // Import ALLE companies
                $companyResult = $this->importCompanies();
            } else {
                // Import geselecteerde companies
                $companyResult = $this->importSelectedCompanies($companyIds);
            }

            $stats['companies_imported'] = $companyResult['imported'];
            $stats['companies_skipped'] = $companyResult['skipped'];

            Log::info('Companies import completed, starting contacts import', [
                'companies_imported' => $stats['companies_imported']
            ]);

            // Nu voor elke customer met teamleader_id de contacten importeren
            $customers = Customer::whereNotNull('teamleader_id')->get();

            Log::info('Found customers with Teamleader ID', ['count' => $customers->count()]);

            foreach ($customers as $customer) {
                try {
                    $contactResult = $this->importContactsForCustomerSimple($customer);

                    $stats['contacts_imported'] += $contactResult['imported'] ?? 0;
                    $stats['contacts_skipped'] += $contactResult['skipped'] ?? 0;

                    if (($contactResult['imported'] ?? 0) > 0) {
                        Log::info('Contacts imported for customer', [
                            'customer_name' => $customer->name,
                            'imported' => $contactResult['imported']
                        ]);
                    }

                } catch (\Exception $e) {
                    $stats['errors'][] = "Error importing contacts for {$customer->name}: " . $e->getMessage();
                    Log::error('Error importing contacts for customer', [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Complete import finished', $stats);

            return $stats;

        } catch (\Exception $e) {
            Log::error('Fatal error in combined import', ['error' => $e->getMessage()]);
            $stats['errors'][] = 'Fatal error: ' . $e->getMessage();
            return $stats;
        }
    }

    /**
     * Import projects for a specific customer (CASCADE IMPORT HELPER)
     */
    protected function importProjectsForCustomer(Customer $customer, string $teamleaderCompanyId, bool $importTimeEntries = false): array
    {
        $imported = 0;
        $skipped = 0;
        $timeEntriesImported = 0;

        try {
            // Fetch projects for this company from database cache
            $tlProjects = TeamleaderProject::where('teamleader_company_id', $teamleaderCompanyId)
                ->get();

            Log::info('Importing projects for customer', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'teamleader_company_id' => $teamleaderCompanyId,
                'total_projects' => $tlProjects->count()
            ]);

            foreach ($tlProjects as $tlProject) {
                try {
                    // Check if project already exists
                    $existingProject = Project::where('teamleader_id', $tlProject->teamleader_id)->first();

                    if ($existingProject) {
                        $skipped++;
                        continue;
                    }

                    // Map Teamleader status to Progress status
                    $status = match($tlProject->status) {
                        'done' => 'completed',
                        'active' => 'active',
                        'on_hold' => 'on_hold',
                        'cancelled' => 'cancelled',
                        default => 'active'
                    };

                    // Create project
                    $project = Project::create([
                        'company_id' => Auth::user()->company_id,
                        'customer_id' => $customer->id,
                        'teamleader_id' => $tlProject->teamleader_id,
                        'name' => $tlProject->title ?? 'Unnamed Project',
                        'description' => $tlProject->description,
                        'status' => $status,
                        'start_date' => $tlProject->starts_on,
                        'end_date' => $tlProject->due_on,
                        'total_value' => $tlProject->budget_amount ?? 0,
                        'monthly_fee' => $tlProject->budget_amount ?? null,
                        'created_by' => Auth::id(),
                    ]);

                    $imported++;

                    // Import milestones and tasks for this project (ALWAYS)
                    try {
                        $this->importMilestonesAndTasksForProject($project, $tlProject->teamleader_id);
                    } catch (\Exception $e) {
                        Log::warning('Failed to import milestones/tasks for project', [
                            'project_id' => $project->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Import time entries for this project (if enabled)
                    if ($importTimeEntries) {
                        try {
                            $timeResult = $this->importTimeEntriesForProject($project, $tlProject->teamleader_id);
                            $timeEntriesImported += $timeResult['imported'];
                        } catch (\Exception $e) {
                            Log::warning('Failed to import time entries for project', [
                                'project_id' => $project->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    Log::warning('Failed to import project for customer', [
                        'customer_id' => $customer->id,
                        'project_teamleader_id' => $tlProject->teamleader_id,
                        'error' => $e->getMessage()
                    ]);
                    $skipped++;
                }
            }

            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'time_entries_imported' => $timeEntriesImported
            ];

        } catch (\Exception $e) {
            Log::error('Error importing projects for customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            return [
                'imported' => 0,
                'skipped' => 0,
                'time_entries_imported' => 0
            ];
        }
    }

    /**
     * Import time entries for a specific project (CASCADE IMPORT HELPER)
     */
    protected function importTimeEntriesForProject(Project $project, string $teamleaderProjectId): array
    {
        $imported = 0;
        $skipped = 0;

        try {
            // KRITIEKE CHANGE: Read from DATABASE CACHE ipv API call!
            $timeEntries = TeamleaderTimeEntry::where('teamleader_project_id', $teamleaderProjectId)
                ->whereNotNull('teamleader_id')
                ->orderBy('date')
                ->get();

            Log::info('Importing time entries for project (from cache)', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'total_entries' => $timeEntries->count()
            ]);

            foreach ($timeEntries as $tlEntry) {
                try {
                    // KRITIEKE FIX: Check duplicate via teamleader_id in time_entries table
                    // (NIET alleen via is_imported flag in cache!)
                    $existingEntry = TimeEntry::where('teamleader_id', $tlEntry->teamleader_id)->first();

                    if ($existingEntry) {
                        $skipped++;
                        continue;
                    }

                    // Skip if already imported (checked via cache table is_imported flag)
                    if ($tlEntry->is_imported) {
                        $skipped++;
                        continue;
                    }

                    // Get hourly rate from cache (fallback to project default)
                    $hourlyRate = $tlEntry->hourly_rate ?? ($project->default_hourly_rate ?? 0.00);

                    // Bereken TOTAL minutes (niet remainder!)
                    // TimeEntry boot() method berekent hours automatisch van minutes
                    $totalMinutes = round($tlEntry->duration_seconds / 60);

                    // KRITIEKE FIX: Voeg teamleader_id toe om duplicates te voorkomen!
                    // Create time entry
                    $timeEntry = TimeEntry::create([
                        'teamleader_id' => $tlEntry->teamleader_id, // KRITIEKE FIX: was missing!
                        'user_id' => Auth::id(), // Assign to current user
                        'project_id' => $project->id,
                        'entry_date' => $tlEntry->date,
                        'minutes' => $totalMinutes, // Total minutes - hours wordt auto-berekend
                        'description' => $tlEntry->description ?? 'Imported from Teamleader',
                        'is_billable' => 'billable',
                        'status' => 'approved', // Auto-approve imported entries
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'hourly_rate_used' => $hourlyRate,
                    ]);

                    // Log activity voor geïmporteerde time entry
                    $totalHours = round($totalMinutes / 60, 2);
                    ProjectActivity::log(
                        $project->id,
                        'time_entry_added',
                        'imported ' . $totalHours . ' hours from Teamleader',
                        null,
                        'time_entry',
                        $timeEntry->id
                    );

                    $imported++;

                    // Mark time entry as imported in cache
                    $tlEntry->update([
                        'is_imported' => true,
                        'imported_at' => now()
                    ]);

                } catch (\Exception $e) {
                    Log::warning('Failed to import time entry from cache', [
                        'project_id' => $project->id,
                        'time_entry_id' => $tlEntry->teamleader_id,
                        'error' => $e->getMessage()
                    ]);
                    $skipped++;
                }
            }

            Log::info('Time entries import completed (from cache)', [
                'project_id' => $project->id,
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return compact('imported', 'skipped');

        } catch (\Exception $e) {
            Log::error('Error importing time entries for project', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return ['imported' => 0, 'skipped' => 0];
        }
    }

    /**
     * Import milestones and tasks for a specific project (DATABASE-FIRST)
     * Reads from teamleader_milestones and teamleader_tasks cache tables
     */
    protected function importMilestonesAndTasksForProject(Project $project, string $teamleaderProjectId): array
    {
        $milestonesImported = 0;
        $tasksImported = 0;

        try {
            Log::info('Importing milestones and tasks for project (from cache)', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'teamleader_project_id' => $teamleaderProjectId
            ]);

            // KRITIEKE CHANGE: Read from DATABASE CACHE ipv API call!
            $milestones = TeamleaderMilestone::where('teamleader_project_id', $teamleaderProjectId)
                ->whereNotNull('teamleader_id')
                ->orderBy('created_at')
                ->get();

            Log::info('Found milestones in cache for project', [
                'project_id' => $project->id,
                'milestones_count' => $milestones->count()
            ]);

            // Sort order counter
            $sortOrder = 1;

            foreach ($milestones as $tlMilestone) {
                try {
                    // Map Teamleader status to Progress status
                    $status = match($tlMilestone->status) {
                        'open' => 'pending',
                        'in_progress' => 'in_progress',
                        'done' => 'completed',
                        'on_hold' => 'on_hold',
                        default => 'pending'
                    };

                    // Create milestone in Progress
                    $milestone = ProjectMilestone::create([
                        'project_id' => $project->id,
                        'name' => $tlMilestone->name,
                        'description' => null,
                        'status' => $status,
                        'start_date' => $tlMilestone->starts_on,
                        'end_date' => $tlMilestone->due_on,
                        'sort_order' => $sortOrder++,
                        'fee_type' => 'in_fee',
                        'pricing_type' => $tlMilestone->invoicing_method === 'time_and_materials' ? 'hourly_rate' : 'fixed_price',
                        'fixed_price' => $tlMilestone->budget_amount,
                        'estimated_hours' => $tlMilestone->estimated_hours, // Uses accessor from model
                        'source_type' => 'manual',
                    ]);

                    $milestonesImported++;

                    // Mark milestone as imported in cache
                    $tlMilestone->update([
                        'is_imported' => true,
                        'imported_at' => now()
                    ]);

                    Log::info('Milestone imported from cache', [
                        'milestone_id' => $milestone->id,
                        'milestone_name' => $milestone->name,
                        'teamleader_milestone_id' => $tlMilestone->teamleader_id
                    ]);

                    // Now import tasks for this milestone (ALSO FROM CACHE!)
                    $tasks = TeamleaderTask::where('teamleader_milestone_id', $tlMilestone->teamleader_id)
                        ->whereNotNull('teamleader_id')
                        ->orderBy('created_at')
                        ->get();

                    Log::info('Found tasks in cache for milestone', [
                        'milestone_id' => $milestone->id,
                        'tasks_count' => $tasks->count()
                    ]);

                    $taskSortOrder = 1;

                    foreach ($tasks as $tlTask) {
                        try {
                            // Map task status
                            $taskStatus = $tlTask->completed ? 'completed' : 'pending';

                            // Create task in Progress
                            $task = ProjectTask::create([
                                'project_milestone_id' => $milestone->id,
                                'name' => $tlTask->title,
                                'description' => $tlTask->description,
                                'status' => $taskStatus,
                                'start_date' => null,
                                'end_date' => $tlTask->due_on,
                                'sort_order' => $taskSortOrder++,
                                'fee_type' => 'in_fee',
                                'pricing_type' => 'hourly_rate',
                                'estimated_hours' => $tlTask->estimated_hours, // Uses accessor from model
                                'source_type' => 'manual',
                            ]);

                            $tasksImported++;

                            // Mark task as imported in cache
                            $tlTask->update([
                                'is_imported' => true,
                                'imported_at' => now()
                            ]);

                        } catch (\Exception $e) {
                            Log::warning('Failed to import task from cache', [
                                'milestone_id' => $milestone->id,
                                'task_title' => $tlTask->title,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    Log::warning('Failed to import milestone from cache', [
                        'project_id' => $project->id,
                        'milestone_name' => $tlMilestone->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Milestones and tasks import completed (from cache)', [
                'project_id' => $project->id,
                'milestones_imported' => $milestonesImported,
                'tasks_imported' => $tasksImported
            ]);

            return [
                'milestones_imported' => $milestonesImported,
                'tasks_imported' => $tasksImported
            ];

        } catch (\Exception $e) {
            Log::error('Error importing milestones and tasks for project', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return [
                'milestones_imported' => 0,
                'tasks_imported' => 0
            ];
        }
    }

    /**
     * Import selected time entries FROM CACHE (database-first)
     *
     * @param array $timeEntryIds Array van teamleader_id's
     * @return array Statistics
     */
    public function importSelectedTimeEntries(array $timeEntryIds): array
    {
        $imported = 0;
        $skipped = 0;

        Log::info('Starting database-first time entries import', [
            'count' => count($timeEntryIds),
            'ids' => $timeEntryIds
        ]);

        DB::beginTransaction();
        try {
            foreach ($timeEntryIds as $tlEntryId) {
                // FIXED: Haal time entry uit CACHE ipv API (database-first!)
                $tlEntry = TeamleaderTimeEntry::where('teamleader_id', $tlEntryId)->first();

                if (!$tlEntry) {
                    Log::warning('Time entry not found in database cache', ['time_entry_id' => $tlEntryId]);
                    $skipped++;
                    continue;
                }

                // Check of al geïmporteerd
                $existing = TimeEntry::where('teamleader_id', $tlEntry->teamleader_id)->first();
                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Vind Progress project via teamleader_id
                $project = Project::where('teamleader_id', $tlEntry->teamleader_project_id)->first();

                if (!$project) {
                    Log::warning('Progress project not found for time entry', [
                        'time_entry_id' => $tlEntry->teamleader_id,
                        'teamleader_project_id' => $tlEntry->teamleader_project_id
                    ]);
                    $skipped++;
                    continue;
                }

                // Vind milestone/task via cache lookup
                $milestoneId = null;
                $taskId = null;

                if ($tlEntry->raw_data && isset($tlEntry->raw_data['subject'])) {
                    $subject = $tlEntry->raw_data['subject'];

                    if ($subject['type'] === 'milestone') {
                        // Zoek in cache welke Progress milestone dit is
                        $cacheMilestone = \App\Models\TeamleaderMilestone::where('teamleader_id', $subject['id'])->first();
                        if ($cacheMilestone && $cacheMilestone->progress_milestone_id) {
                            $milestoneId = $cacheMilestone->progress_milestone_id;
                        }
                    } elseif ($subject['type'] === 'task') {
                        // Zoek in cache welke Progress task dit is
                        $cacheTask = \App\Models\TeamleaderTask::where('teamleader_id', $subject['id'])->first();
                        if ($cacheTask && $cacheTask->progress_task_id) {
                            $taskId = $cacheTask->progress_task_id;

                            // Haal milestone ID op via task
                            $progressTask = ProjectTask::find($taskId);
                            if ($progressTask) {
                                $milestoneId = $progressTask->project_milestone_id;
                            }
                        }
                    }
                }

                // Gebruik Auth user of zoek Teamleader user
                $userId = Auth::id();

                // Als niet ingelogd, zoek via Teamleader user ID
                if (!$userId && $tlEntry->teamleader_user_id) {
                    $user = User::where('teamleader_id', $tlEntry->teamleader_user_id)->first();
                    if ($user) {
                        $userId = $user->id;
                    }
                }

                // Fallback: Gebruik eerste admin user van het project's company
                if (!$userId && $project) {
                    $fallbackUser = User::where('company_id', $project->company_id)
                        ->whereIn('role', ['super_admin', 'admin'])
                        ->first();
                    if ($fallbackUser) {
                        $userId = $fallbackUser->id;
                    }
                }

                // Bepaal hourly rate (5-level hierarchy)
                $hourlyRate = $tlEntry->hourly_rate ?: 0;
                if (!$hourlyRate && $project) {
                    $hourlyRate = $project->default_hourly_rate ?: 0;
                    if (!$hourlyRate && $project->companyRelation) {
                        $hourlyRate = $project->companyRelation->default_hourly_rate ?: 0;
                    }
                }

                // FIXED: Bereken BOTH minutes en hours expliciet
                // TimeEntry boot() zou dit auto-berekenen maar werkt niet altijd in create()
                $totalMinutes = round($tlEntry->duration_seconds / 60);
                $decimalHours = round($totalMinutes / 60, 2);

                // Maak time entry aan
                TimeEntry::create([
                    'teamleader_id' => $tlEntry->teamleader_id,
                    'user_id' => $userId,
                    'company_id' => Auth::check() ? Auth::user()->company_id : $project->company_id,
                    'project_id' => $project->id,
                    'customer_id' => $project->customer_id,
                    'project_milestone_id' => $milestoneId,
                    'project_task_id' => $taskId,
                    'entry_date' => $tlEntry->date,
                    'hours' => $decimalHours, // Expliciet meegevenfor database constraint
                    'minutes' => $totalMinutes,
                    'description' => $tlEntry->description ?? 'Imported from Teamleader',
                    'is_billable' => 'billable',
                    'status' => 'approved',
                    'approved_by' => Auth::check() ? Auth::id() : $userId,
                    'approved_at' => now(),
                    'hourly_rate_used' => $hourlyRate,
                    'created_by' => Auth::check() ? Auth::id() : $userId,
                    'updated_by' => Auth::check() ? Auth::id() : $userId,
                ]);

                // Mark als imported in cache
                $tlEntry->update([
                    'is_imported' => true,
                    'imported_at' => now()
                ]);

                $imported++;
            }

            DB::commit();

            Log::info('Database-first time entries import completed', [
                'imported' => $imported,
                'skipped' => $skipped
            ]);

            return [
                'imported' => $imported,
                'skipped' => $skipped
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing time entries from cache', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
