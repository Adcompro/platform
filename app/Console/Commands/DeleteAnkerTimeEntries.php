<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TimeEntry;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteAnkerTimeEntries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'timeentries:delete-anker {--dry-run : Run without actually deleting anything}';

    /**
     * The console command description.
     */
    protected $description = 'Delete all time entries for Anker projects (for testing import deduplication)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No entries will be deleted');
        } else {
            $this->warn('⚠️  LIVE MODE - Time entries will be permanently deleted!');
            if (!$this->confirm('Are you sure you want to delete ALL Anker time entries?')) {
                $this->info('Deletion cancelled.');
                return 0;
            }
        }

        $this->info('Finding Anker projects...');

        // Find Anker projects via customer name
        $ankerProjects = Project::whereHas('customer', function($q) {
            $q->where('name', 'LIKE', '%Anker%');
        })->with('customer')->get();

        if ($ankerProjects->isEmpty()) {
            $this->error('No Anker projects found!');
            return 1;
        }

        $this->info(sprintf('Found %d Anker projects:', $ankerProjects->count()));

        $projectIds = [];
        foreach ($ankerProjects as $project) {
            $timeEntriesCount = TimeEntry::where('project_id', $project->id)->count();
            $this->line(sprintf('  - [%d] %s (%s) - %d time entries',
                $project->id,
                $project->name,
                $project->customer->name,
                $timeEntriesCount
            ));
            $projectIds[] = $project->id;
        }

        // Count total time entries
        $totalEntries = TimeEntry::whereIn('project_id', $projectIds)->count();

        if ($totalEntries === 0) {
            $this->info('No time entries found for Anker projects. Nothing to delete.');
            return 0;
        }

        $this->newLine();
        $this->info(sprintf('Total time entries to delete: %d', $totalEntries));

        if (!$isDryRun) {
            $this->warn('Deleting in 3 seconds... Press Ctrl+C to cancel!');
            sleep(3);

            DB::beginTransaction();
            try {
                // Get all entry IDs for logging
                $entryIds = TimeEntry::whereIn('project_id', $projectIds)
                    ->pluck('id')
                    ->toArray();

                // Delete all time entries for Anker projects
                $deleted = TimeEntry::whereIn('project_id', $projectIds)->delete();

                DB::commit();

                Log::info('Deleted Anker time entries for testing', [
                    'project_ids' => $projectIds,
                    'entry_ids' => $entryIds,
                    'count_deleted' => $deleted,
                    'reason' => 'Testing import deduplication'
                ]);

                $this->newLine();
                $this->info(sprintf('✅ Successfully deleted %d time entries from Anker projects', $deleted));

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Error deleting time entries: ' . $e->getMessage());
                Log::error('Failed to delete Anker time entries', [
                    'error' => $e->getMessage(),
                    'project_ids' => $projectIds
                ]);
                return 1;
            }
        } else {
            $this->newLine();
            $this->info(sprintf('🔍 DRY RUN: Would delete %d time entries', $totalEntries));
        }

        // Show final statistics
        $remaining = TimeEntry::whereIn('project_id', $projectIds)->count();

        $this->newLine();
        $this->info('📊 Final Statistics:');
        $this->line(sprintf('  - Anker time entries remaining: %d', $remaining));
        $this->line(sprintf('  - Total time entries in database: %d', TimeEntry::count()));

        $this->newLine();
        $this->info('💡 Next steps:');
        $this->line('  1. Go to Teamleader dashboard: https://progress.adcompro.app/teamleader');
        $this->line('  2. Find "Anker Solix" customer');
        $this->line('  3. Click "Import" and select the company with checkboxes:');
        $this->line('     ☑ Import Contacts');
        $this->line('     ☑ Import Projects');
        $this->line('     ☑ Import Time Entries');
        $this->line('  4. Verify that only NEW entries are imported (no duplicates)');

        return 0;
    }
}
