<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TeamleaderMasterSyncService;

class TeamleaderMasterSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teamleader:master-sync
                            {--force : Forceer sync zelfs als recent uitgevoerd}
                            {--stats : Toon alleen statistics zonder sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniseer ALLE data van Teamleader Focus naar lokale cache database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('🚀 TEAMLEADER MASTER SYNC');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Toon huidige statistics als --stats flag gebruikt wordt
        if ($this->option('stats')) {
            $this->showCurrentStats();
            return 0;
        }

        // Bevestiging vragen (tenzij --force)
        if (!$this->option('force')) {
            $this->warn('⚠️  Dit gaat ALLE data van Teamleader synchroniseren naar de lokale cache.');
            $this->warn('   Dit kan enkele minuten duren en veel API calls maken.');
            $this->newLine();

            if (!$this->confirm('Wil je doorgaan?', true)) {
                $this->info('Sync geannuleerd.');
                return 0;
            }
        }

        $this->newLine();
        $this->info('Starting Master Sync...');
        $this->newLine();

        try {
            $syncService = new TeamleaderMasterSyncService();
            $results = $syncService->syncAll();

            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════');
            $this->info('✅ MASTER SYNC COMPLETED SUCCESSFULLY!');
            $this->info('═══════════════════════════════════════════════════════');
            $this->newLine();

            // Toon resultaten in tabel format
            $this->table(
                ['Entity', 'New', 'Updated', 'Skipped', 'Failed'],
                [
                    [
                        'Companies',
                        $results['companies']['synced'] ?? 0,
                        $results['companies']['updated'] ?? 0,
                        $results['companies']['skipped'] ?? 0,
                        $results['companies']['failed'] ?? 0,
                    ],
                    [
                        'Contacts',
                        $results['contacts']['synced'] ?? 0,
                        $results['contacts']['updated'] ?? 0,
                        $results['contacts']['skipped'] ?? 0,
                        $results['contacts']['failed'] ?? 0,
                    ],
                    [
                        'Projects',
                        $results['projects']['synced'] ?? 0,
                        $results['projects']['updated'] ?? 0,
                        $results['projects']['skipped'] ?? 0,
                        $results['projects']['failed'] ?? 0,
                    ],
                    [
                        'Milestones',
                        $results['milestones']['synced'] ?? 0,
                        $results['milestones']['updated'] ?? 0,
                        $results['milestones']['skipped'] ?? 0,
                        $results['milestones']['failed'] ?? 0,
                    ],
                    [
                        'Tasks',
                        $results['tasks']['synced'] ?? 0,
                        $results['tasks']['updated'] ?? 0,
                        $results['tasks']['skipped'] ?? 0,
                        $results['tasks']['failed'] ?? 0,
                    ],
                    [
                        'Time Entries',
                        $results['time_entries']['synced'] ?? 0,
                        $results['time_entries']['updated'] ?? 0,
                        $results['time_entries']['skipped'] ?? 0,
                        $results['time_entries']['failed'] ?? 0,
                    ],
                ]
            );

            $this->newLine();
            $this->info("⏱️  Duration: {$results['duration']} seconds");
            $this->newLine();

            // Toon finale statistics
            $this->showCurrentStats();

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('═══════════════════════════════════════════════════════');
            $this->error('❌ MASTER SYNC FAILED');
            $this->error('═══════════════════════════════════════════════════════');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Toon huidige cache statistics
     */
    protected function showCurrentStats()
    {
        $this->info('📊 Current Cache Statistics:');
        $this->newLine();

        $stats = [
            ['Companies', \App\Models\TeamleaderCompany::count()],
            ['Contacts', \App\Models\TeamleaderContact::count()],
            ['Projects', \App\Models\TeamleaderProject::count()],
            ['Milestones', \App\Models\TeamleaderMilestone::count()],
            ['Tasks', \App\Models\TeamleaderTask::count()],
            ['Time Entries', \App\Models\TeamleaderTimeEntry::count()],
        ];

        $this->table(['Entity', 'Total in Cache'], $stats);

        $this->newLine();
        $lastSync = \App\Models\TeamleaderCompany::orderBy('synced_at', 'desc')->first();
        if ($lastSync && $lastSync->synced_at) {
            $this->info("Last sync: {$lastSync->synced_at->diffForHumans()}");
        } else {
            $this->warn('No sync performed yet.');
        }
        $this->newLine();
    }
}
