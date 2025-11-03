<?php

namespace App\Console\Commands;

use App\Models\TimeEntry;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackfillTimeEntryActivitiesImproved extends Command
{
    protected $signature = 'activities:backfill-time-entries-improved {--project_id=}';
    protected $description = 'Backfill activity logs with correct timestamps and Teamleader detection';

    public function handle()
    {
        $projectId = $this->option('project_id');
        $this->info('Starting improved backfill of time entry activities...');

        // Query voor time entries zonder activity log
        $query = TimeEntry::query()
            ->whereNotExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('project_activities')
                  ->whereColumn('project_activities.entity_id', 'time_entries.id')
                  ->where('project_activities.entity_type', 'time_entry');
            });

        if ($projectId) {
            $query->where('project_id', $projectId);
            $this->info("Filtering for project ID: {$projectId}");
        }

        $timeEntries = $query->with(['user', 'project'])
            ->orderBy('project_id')
            ->orderBy('created_at')
            ->get();

        $this->info("Found {$timeEntries->count()} time entries without activity logs");

        if ($timeEntries->isEmpty()) {
            $this->warn('No time entries found to backfill');
            return 0;
        }

        // Detecteer batch imports per project
        $batchImports = $this->detectBatchImports($timeEntries);

        $bar = $this->output->createProgressBar($timeEntries->count());
        $bar->start();

        $created = 0;
        $skipped = 0;
        $activitiesToInsert = [];

        foreach ($timeEntries as $timeEntry) {
            try {
                // Bereken totale uren
                $totalMinutes = ($timeEntry->hours * 60) + $timeEntry->minutes;
                $totalHours = round($totalMinutes / 60, 2);

                // Bepaal of dit een batch import is
                $isBatchImport = isset($batchImports[$timeEntry->project_id]);

                // Bepaal beschrijving
                $description = ($timeEntry->teamleader_id || $isBatchImport)
                    ? "imported {$totalHours} hours from Teamleader"
                    : "logged {$totalHours} hours on project";

                // Verzamel activities voor batch insert (met originele timestamps!)
                $activitiesToInsert[] = [
                    'project_id' => $timeEntry->project_id,
                    'user_id' => $timeEntry->user_id,
                    'activity_type' => 'time_entry_added',
                    'entity_type' => 'time_entry',
                    'entity_id' => $timeEntry->id,
                    'description' => $description,
                    'old_values' => null,
                    'new_values' => null,
                    'ip_address' => '127.0.0.1',
                    'created_at' => $timeEntry->created_at, // Originele timestamp!
                    'updated_at' => $timeEntry->created_at, // Originele timestamp!
                ];

                $created++;

                // Insert in batches van 100 voor betere performance
                if (count($activitiesToInsert) >= 100) {
                    DB::table('project_activities')->insert($activitiesToInsert);
                    $activitiesToInsert = [];
                }

            } catch (\Exception $e) {
                $this->error("Failed for time entry {$timeEntry->id}: {$e->getMessage()}");
                $skipped++;
            }

            $bar->advance();
        }

        // Insert resterende activities
        if (!empty($activitiesToInsert)) {
            DB::table('project_activities')->insert($activitiesToInsert);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Skipped', $skipped],
                ['Batch imports detected', count($batchImports)],
                ['Total', $timeEntries->count()],
            ]
        );

        return 0;
    }

    /**
     * Detecteer batch imports: entries binnen 5 seconden van elkaar voor hetzelfde project
     */
    protected function detectBatchImports($timeEntries)
    {
        $batchImports = [];
        $grouped = $timeEntries->groupBy('project_id');

        foreach ($grouped as $projectId => $entries) {
            if ($entries->count() < 5) {
                continue; // Te weinig entries voor batch import
            }

            // Sorteer op created_at
            $sorted = $entries->sortBy('created_at');
            $first = $sorted->first()->created_at;
            $last = $sorted->last()->created_at;

            // Als alle entries binnen 5 seconden zijn aangemaakt = batch import
            $diffSeconds = $first->diffInSeconds($last);

            if ($diffSeconds <= 5) {
                $batchImports[$projectId] = [
                    'count' => $entries->count(),
                    'duration_seconds' => $diffSeconds,
                    'timestamp' => $first->format('Y-m-d H:i:s'),
                ];

                $project = Project::find($projectId);
                $this->line("  - Detected batch import: Project #{$projectId} ({$project->name}): {$entries->count()} entries in {$diffSeconds}s");
            }
        }

        return $batchImports;
    }
}
