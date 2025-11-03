<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateTimeEntries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'timeentries:cleanup-duplicates {--dry-run : Run without actually deleting anything}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup duplicate time entries that were imported multiple times (keeps oldest entry per group)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No entries will be deleted');
        } else {
            $this->warn('⚠️  LIVE MODE - Duplicates will be permanently deleted!');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        $this->info('Starting duplicate cleanup...');

        $totalDeleted = 0;
        $groupsProcessed = 0;

        // Find duplicates: same date, project, description, hours, and minutes
        // AND created within 1 hour of each other (likely from same import batch)
        $duplicateGroups = DB::select("
            SELECT
                entry_date,
                project_id,
                description,
                hours,
                minutes,
                COUNT(*) as duplicate_count,
                MIN(id) as keep_id,
                GROUP_CONCAT(id ORDER BY id) as all_ids
            FROM time_entries
            WHERE teamleader_id IS NULL
            GROUP BY entry_date, project_id, description, hours, minutes
            HAVING duplicate_count > 1
        ");

        $this->info(sprintf('Found %d duplicate groups to process', count($duplicateGroups)));

        foreach ($duplicateGroups as $group) {
            try {
                // Parse alle IDs
                $allIds = explode(',', $group->all_ids);
                $keepId = $group->keep_id;
                $deleteIds = array_filter($allIds, fn($id) => $id != $keepId);

                // Haal entries op om timestamps te checken
                $entries = TimeEntry::whereIn('id', $allIds)
                    ->orderBy('created_at')
                    ->get();

                // Check of ze binnen 1 uur gemaakt zijn (waarschijnlijk bulk import)
                $firstCreated = $entries->first()->created_at;
                $lastCreated = $entries->last()->created_at;
                $timeDiff = $lastCreated->diffInMinutes($firstCreated);

                // Skip als entries meer dan 1 uur uit elkaar liggen (mogelijk legitiem)
                if ($timeDiff > 60) {
                    $this->line(sprintf(
                        'Skipping group (time spread: %d min) - Date: %s, Project: %s, Desc: %s',
                        $timeDiff,
                        $group->entry_date,
                        $group->project_id,
                        substr($group->description, 0, 40)
                    ));
                    continue;
                }

                if (!$isDryRun) {
                    // Delete duplicates (keep oldest)
                    $deleted = TimeEntry::whereIn('id', $deleteIds)->delete();
                    $totalDeleted += $deleted;

                    Log::info('Deleted duplicate time entries', [
                        'kept_id' => $keepId,
                        'deleted_ids' => $deleteIds,
                        'date' => $group->entry_date,
                        'project_id' => $group->project_id,
                        'description' => substr($group->description, 0, 100),
                        'count_deleted' => $deleted
                    ]);
                } else {
                    $totalDeleted += count($deleteIds);
                }

                $this->line(sprintf(
                    '%s [%d/%d] Date: %s | Project: %s | Kept ID: %d | Deleted: %s',
                    $isDryRun ? '🔍' : '✅',
                    ++$groupsProcessed,
                    count($duplicateGroups),
                    $group->entry_date,
                    $group->project_id,
                    $keepId,
                    implode(',', $deleteIds)
                ));

            } catch (\Exception $e) {
                $this->error(sprintf('Error processing group: %s', $e->getMessage()));
                Log::error('Duplicate cleanup error', [
                    'group' => $group,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info(sprintf('🔍 DRY RUN: Would delete %d duplicate entries from %d groups',
                $totalDeleted, $groupsProcessed));
            $this->info('Run without --dry-run to actually delete duplicates');
        } else {
            $this->info(sprintf('✅ Successfully deleted %d duplicate entries from %d groups',
                $totalDeleted, $groupsProcessed));
        }

        // Show remaining stats
        $remaining = TimeEntry::whereNull('teamleader_id')->count();
        $withTeamleaderId = TimeEntry::whereNotNull('teamleader_id')->count();

        $this->newLine();
        $this->info('📊 Current Statistics:');
        $this->line(sprintf('  - Entries WITHOUT teamleader_id: %d', $remaining));
        $this->line(sprintf('  - Entries WITH teamleader_id: %d', $withTeamleaderId));
        $this->line(sprintf('  - Total entries: %d', $remaining + $withTeamleaderId));

        return 0;
    }
}
