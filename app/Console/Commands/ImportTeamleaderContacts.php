<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Contact;
use App\Services\TeamleaderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportTeamleaderContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teamleader:import-contacts {--customer-id= : Import contacts for specific customer only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all contacts from Teamleader Focus and match them to customers based on company relations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('Teamleader Contact Import - Started');
        $this->info('===========================================');
        $this->newLine();

        $startTime = now();
        $customerId = $this->option('customer-id');

        // Stats
        $stats = [
            'total_processed' => 0,
            'imported' => 0,
            'skipped_no_email' => 0,
            'skipped_existing' => 0,
            'skipped_no_match' => 0,
            'errors' => 0
        ];

        try {
            // Haal alle customers op met teamleader_id
            $customers = Customer::whereNotNull('teamleader_id')->get();

            if ($customerId) {
                $customers = $customers->where('id', $customerId);
            }

            $this->info("Found {$customers->count()} customers with Teamleader ID");
            $this->newLine();

            // Maak een mapping van teamleader company ID -> customer
            $customerMap = [];
            foreach ($customers as $customer) {
                $customerMap[$customer->teamleader_id] = $customer;
            }

            // Haal alle contacten op uit Teamleader (paginated)
            $page = 1;
            $hasMore = true;
            $pageSize = 100;
            $maxPages = 50; // Max 5000 contacten

            $this->info("Fetching contacts from Teamleader...");
            $progressBar = $this->output->createProgressBar();

            while ($hasMore && $page <= $maxPages) {
                try {
                    // Haal contacten op
                    $response = TeamleaderService::listContacts($page, $pageSize);

                    if (empty($response['data'])) {
                        $hasMore = false;
                        break;
                    }

                    $contactsOnPage = count($response['data']);
                    $this->newLine();
                    $this->info("Processing page {$page} ({$contactsOnPage} contacts)...");

                    foreach ($response['data'] as $tlContactBasic) {
                        $stats['total_processed']++;
                        $progressBar->advance();

                        // Rate limiting: 500ms tussen API calls voor volledige contact info
                        usleep(500000);

                        try {
                            // Haal volledige contact info op (inclusief companies relaties)
                            $fullContactResponse = TeamleaderService::getContact($tlContactBasic['id']);
                            $tlContact = $fullContactResponse['data'] ?? $fullContactResponse;

                            // Extract email
                            $email = $tlContact['emails'][0]['email'] ?? null;
                            if (empty($email)) {
                                $stats['skipped_no_email']++;
                                continue;
                            }

                            // Check of contact al bestaat
                            $existingContact = Contact::where('teamleader_id', $tlContact['id'])
                                ->orWhere('email', $email)
                                ->first();

                            if ($existingContact) {
                                $stats['skipped_existing']++;
                                continue;
                            }

                            // Zoek matching customer op basis van company relaties
                            $matchedCustomer = null;

                            // DEBUG: Log eerste 3 contacten om structuur te zien
                            if ($stats['total_processed'] <= 3) {
                                Log::info('DEBUG Contact Structure', [
                                    'contact_name' => ($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? ''),
                                    'companies' => $tlContact['companies'] ?? 'NO COMPANIES',
                                    'customer_map_keys' => array_keys($customerMap)
                                ]);
                            }

                            if (isset($tlContact['companies']) && is_array($tlContact['companies'])) {
                                foreach ($tlContact['companies'] as $linkedCompany) {
                                    // Haal company ID op uit de correcte veldnaam ('company', niet 'customer')
                                    $companyId = $linkedCompany['company']['id'] ?? $linkedCompany['customer']['id'] ?? $linkedCompany['id'] ?? null;

                                    // DEBUG: Log company ID checking
                                    if ($stats['total_processed'] <= 3) {
                                        Log::info('DEBUG Checking Company', [
                                            'extracted_company_id' => $companyId,
                                            'in_customer_map' => isset($customerMap[$companyId]) ? 'YES' : 'NO',
                                            'linked_company_structure' => $linkedCompany
                                        ]);
                                    }

                                    if ($companyId && isset($customerMap[$companyId])) {
                                        $matchedCustomer = $customerMap[$companyId];

                                        // LOG: Match gevonden!
                                        Log::info('✓ MATCH FOUND!', [
                                            'contact_name' => ($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? ''),
                                            'contact_email' => $email,
                                            'matched_customer' => $matchedCustomer->name,
                                            'company_id' => $companyId
                                        ]);

                                        break; // Neem eerste match
                                    }
                                }
                            }

                            if (!$matchedCustomer) {
                                $stats['skipped_no_match']++;
                                continue;
                            }

                            // Extract phone
                            $phone = $tlContact['telephones'][0]['number'] ?? null;

                            // Bepaal of primary (eerste contact voor deze customer = primary)
                            $isPrimary = Contact::where('customer_id', $matchedCustomer->id)->count() === 0;

                            // Maak contact aan
                            Contact::create([
                                'customer_id' => $matchedCustomer->id,
                                'teamleader_id' => $tlContact['id'],
                                'name' => trim(($tlContact['first_name'] ?? '') . ' ' . ($tlContact['last_name'] ?? '')),
                                'email' => $email,
                                'phone' => $phone,
                                'position' => null,
                                'notes' => null,
                                'is_primary' => $isPrimary,
                                'is_active' => true,
                                'created_at' => isset($tlContact['added_at']) ? Carbon::parse($tlContact['added_at']) : now(),
                                'updated_at' => isset($tlContact['updated_at']) ? Carbon::parse($tlContact['updated_at']) : now(),
                            ]);

                            $stats['imported']++;

                            // Log elke 10 imports
                            if ($stats['imported'] % 10 === 0) {
                                $this->newLine();
                                $this->info("✓ Imported {$stats['imported']} contacts so far...");
                            }

                        } catch (\Exception $e) {
                            $stats['errors']++;
                            Log::error('Error processing contact', [
                                'contact_id' => $tlContactBasic['id'] ?? 'unknown',
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // Check of er meer pagina's zijn
                    if ($contactsOnPage < $pageSize) {
                        $hasMore = false;
                    } else {
                        $page++;
                        // Pauze tussen pagina's
                        sleep(2);
                    }

                } catch (\Exception $e) {
                    $this->error("Error fetching page {$page}: " . $e->getMessage());
                    Log::error('Error fetching contacts page', [
                        'page' => $page,
                        'error' => $e->getMessage()
                    ]);
                    $hasMore = false;
                }
            }

            $progressBar->finish();
            $this->newLine(2);

        } catch (\Exception $e) {
            $this->error('Fatal error: ' . $e->getMessage());
            Log::error('Fatal error in contact import', ['error' => $e->getMessage()]);
            return 1;
        }

        // Output statistics
        $duration = $startTime->diffInMinutes(now());

        $this->info('===========================================');
        $this->info('Import Completed!');
        $this->info('===========================================');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $stats['total_processed']],
                ['Successfully Imported', $stats['imported']],
                ['Skipped (No Email)', $stats['skipped_no_email']],
                ['Skipped (Already Exists)', $stats['skipped_existing']],
                ['Skipped (No Customer Match)', $stats['skipped_no_match']],
                ['Errors', $stats['errors']],
                ['Duration (minutes)', $duration],
            ]
        );

        Log::info('Teamleader contact import completed', $stats);

        return 0;
    }
}
