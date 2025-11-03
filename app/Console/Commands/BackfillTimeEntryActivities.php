<?php

namespace App\Console\Commands;

use App\Models\TimeEntry;
use App\Models\ProjectActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTimeEntryActivities extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'activities:backfill-time-entries {--project_id=}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill activity logs for existing time entries that were created before activity logging was implemented';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectId = $this->option('project_id');

        $this->info('Starting backfill of time entry activities...');

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

        $timeEntries = $query->with(['user', 'project'])->get();

        $this->info("Found {$timeEntries->count()} time entries without activity logs");

        if ($timeEntries->isEmpty()) {
            $this->warn('No time entries found to backfill');
            return 0;
        }

        $bar = $this->output->createProgressBar($timeEntries->count());
        $bar->start();

        $created = 0;
        $skipped = 0;

        foreach ($timeEntries as $timeEntry) {
            try {
                // Bereken totale uren
                $totalMinutes = ($timeEntry->hours * 60) + $timeEntry->minutes;
                $totalHours = round($totalMinutes / 60, 2);

                // Bepaal beschrijving op basis van import status
                $description = $timeEntry->teamleader_id
                    ? "imported {$totalHours} hours from Teamleader"
                    : "logged {$totalHours} hours on project";

                // Maak activity log aan met originele timestamp
                ProjectActivity::create([
                    'project_id' => $timeEntry->project_id,
                    'user_id' => $timeEntry->user_id,
                    'activity_type' => 'time_entry_added',
                    'entity_type' => 'time_entry',
                    'entity_id' => $timeEntry->id,
                    'description' => $description,
                    'old_values' => null,
                    'new_values' => null,
                    'ip_address' => '127.0.0.1', // Placeholder voor backfill
                    'created_at' => $timeEntry->created_at, // Gebruik originele timestamp
                    'updated_at' => $timeEntry->created_at,
                ]);

                $created++;
            } catch (\Exception $e) {
                $this->error("Failed to create activity for time entry {$timeEntry->id}: {$e->getMessage()}");
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Skipped', $skipped],
                ['Total', $timeEntries->count()],
            ]
        );

        return 0;
    }
}
