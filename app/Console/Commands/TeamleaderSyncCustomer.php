<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Services\TeamleaderProjectSyncService;

class TeamleaderSyncCustomer extends Command
{
    protected $signature = 'teamleader:sync-customer
                            {customer_id : Customer ID om te syncen}
                            {--force : Forceer sync zelfs als recent uitgevoerd}';

    protected $description = 'Synchroniseer ALLEEN projecten en milestones voor een specifieke customer (snel en gericht)';

    public function handle()
    {
        $customerId = $this->argument('customer_id');

        $customer = Customer::find($customerId);

        if (!$customer) {
            $this->error("Customer #{$customerId} not found!");
            return 1;
        }

        if (!$customer->teamleader_id) {
            $this->error("Customer '{$customer->name}' has no Teamleader ID!");
            return 1;
        }

        $this->info('═══════════════════════════════════════════════════════');
        $this->info("🔄 SYNCING CUSTOMER: {$customer->name}");
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $this->info("Customer ID: {$customer->id}");
        $this->info("Teamleader ID: {$customer->teamleader_id}");
        $this->newLine();

        try {
            $startTime = microtime(true);

            $syncService = new TeamleaderProjectSyncService();

            // Sync projects (max 10 pages = 1000 projects, meer dan genoeg voor 1 customer)
            $this->info('📋 Step 1: Syncing Projects...');
            $projectResults = $syncService->syncProjectsForCustomer($customer, 10);

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Projects synced', $projectResults['synced'] ?? 0],
                    ['Projects updated', $projectResults['updated'] ?? 0],
                    ['Projects skipped', $projectResults['skipped'] ?? 0],
                ]
            );

            $this->newLine();

            // Sync milestones voor deze projects
            $this->info('🎯 Step 2: Syncing Milestones...');
            $milestoneResults = $syncService->syncMilestonesForCustomer($customer, 10);

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Milestones synced', $milestoneResults['synced'] ?? 0],
                    ['Milestones updated', $milestoneResults['updated'] ?? 0],
                    ['Milestones skipped', $milestoneResults['skipped'] ?? 0],
                ]
            );

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════');
            $this->info("✅ SYNC COMPLETED in {$duration} seconds!");
            $this->info('═══════════════════════════════════════════════════════');

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('═══════════════════════════════════════════════════════');
            $this->error('❌ SYNC FAILED');
            $this->error('═══════════════════════════════════════════════════════');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            $this->newLine();

            return 1;
        }
    }
}
