<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TeamleaderProjectSyncService;

class TeamleaderSyncTimeEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teamleader:sync-time-entries
                            {--force : Forceer sync zelfs als recent uitgevoerd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniseer ALLEEN time entries van Teamleader Focus naar cache (snel - 2499 entries)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('⏱️  TEAMLEADER TIME ENTRIES SYNC');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Toon huidige count
        $currentCount = \App\Models\TeamleaderTimeEntry::count();
        $this->info("Current time entries in cache: {$currentCount}");
        $this->newLine();

        // Bevestiging vragen (tenzij --force)
        if (!$this->option('force')) {
            if (!$this->confirm('Wil je alle 2,499 time entries synchroniseren?', true)) {
                $this->info('Sync geannuleerd.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Starting Time Entries sync (expected: ~20 seconds)...');
        $this->newLine();

        try {
            $startTime = microtime(true);

            $syncService = new TeamleaderProjectSyncService();
            $results = $syncService->syncTimeEntries();

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════');
            $this->info('✅ TIME ENTRIES SYNC COMPLETED!');
            $this->info('═══════════════════════════════════════════════════════');
            $this->newLine();

            $this->table(
                ['Metric', 'Count'],
                [
                    ['New synced', $results['synced'] ?? 0],
                    ['Updated', $results['updated'] ?? 0],
                    ['Skipped', $results['skipped'] ?? 0],
                    ['Failed', $results['failed'] ?? 0],
                    ['Total fetched', $results['total_fetched'] ?? 0],
                ]
            );

            $this->newLine();
            $this->info("⏱️  Duration: {$duration} seconds");
            $this->newLine();

            $finalCount = \App\Models\TeamleaderTimeEntry::count();
            $this->info("📊 Total in cache: {$finalCount} time entries");
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('═══════════════════════════════════════════════════════');
            $this->error('❌ TIME ENTRIES SYNC FAILED');
            $this->error('═══════════════════════════════════════════════════════');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();

            return 1;
        }
    }
}
