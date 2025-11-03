<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TeamleaderProjectSyncService;
use App\Models\SyncJob;

class SyncContactCompanies extends Command
{
    protected $signature = 'teamleader:sync-contact-companies {sync_job_id}';
    protected $description = 'Sync contact-company relationships in background';

    public function handle()
    {
        $syncJobId = $this->argument('sync_job_id');
        
        $this->info("Starting contact-company sync (Job ID: {$syncJobId})");
        
        $syncService = new TeamleaderProjectSyncService();
        
        try {
            $stats = $syncService->syncContactCompanyRelationships($syncJobId);
            
            $this->info("Sync completed!");
            $this->info("Companies processed: {$stats['companies_processed']} / {$stats['companies_total']}");
            $this->info("Relationships added: {$stats['relationships_added']}");
            $this->info("Errors: {$stats['errors']}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            return 1;
        }
    }
}
