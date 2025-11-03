<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CalendarManager;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class CalendarSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:sync
                            {--user= : Sync for specific user ID only}
                            {--provider= : Sync specific provider only (microsoft, google, apple)}
                            {--force : Force sync even if disabled in settings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync calendar events from all connected providers for all users';

    private CalendarManager $calendarManager;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->calendarManager = app(CalendarManager::class);

        // Check if auto-sync is enabled (unless forced)
        if (!$this->option('force') && !Setting::get('calendar_auto_sync', true)) {
            $this->info('Calendar auto-sync is disabled in settings. Use --force to sync anyway.');
            return 0;
        }

        $userFilter = $this->option('user');
        $providerFilter = $this->option('provider');

        // Get users to sync
        $query = User::query();
        if ($userFilter) {
            $query->where('id', $userFilter);
        }
        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error('No users found to sync.');
            return 1;
        }

        $this->info("Starting calendar sync for {$users->count()} user(s)...");

        $totalSynced = 0;
        $totalErrors = 0;

        foreach ($users as $user) {
            $this->line("Syncing calendars for user: {$user->name} ({$user->email})");

            try {
                // Get available providers
                $providers = $providerFilter ? [$providerFilter] : $this->calendarManager->getAvailableProviders();

                foreach ($providers as $providerType) {
                    try {
                        $provider = $this->calendarManager->provider($providerType);

                        if (!$provider->isAuthenticated($user->id)) {
                            $this->line("  - {$providerType}: Not authenticated, skipping");
                            continue;
                        }

                        $this->line("  - Syncing {$providerType}...");
                        $result = $provider->syncEvents($user->id);

                        if ($result['success']) {
                            $this->info("    ✓ {$providerType}: {$result['events_synced']} events synced");
                            $totalSynced += $result['events_synced'];
                        } else {
                            $this->error("    ✗ {$providerType}: {$result['message']}");
                            $totalErrors++;
                        }

                    } catch (\Exception $e) {
                        $this->error("    ✗ {$providerType}: " . $e->getMessage());
                        $totalErrors++;
                        Log::error("Calendar sync error for user {$user->id}, provider {$providerType}: " . $e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error syncing user {$user->id}: " . $e->getMessage());
                $totalErrors++;
                Log::error("Calendar sync error for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Calendar sync completed!");
        $this->table(['Metric', 'Count'], [
            ['Total Events Synced', $totalSynced],
            ['Total Errors', $totalErrors],
            ['Users Processed', $users->count()]
        ]);

        Log::info("Calendar sync command completed", [
            'events_synced' => $totalSynced,
            'errors' => $totalErrors,
            'users_processed' => $users->count()
        ]);

        return $totalErrors > 0 ? 1 : 0;
    }
}
