<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CalendarEvent;
use App\Models\CalendarSyncLog;
use App\Models\CalendarActivity;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncCalendarEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:sync {--user=all : User ID or "all" for all users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync calendar events from Microsoft 365 for all connected users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting calendar synchronization...');
        
        // Determine which users to sync
        $userOption = $this->option('user');
        
        if ($userOption === 'all') {
            // Get all users with Microsoft connected
            $users = User::where('is_active', true)->get();
        } else {
            $users = User::where('id', $userOption)->get();
        }
        
        $totalSynced = 0;
        $totalFailed = 0;
        
        foreach ($users as $user) {
            $this->info("Checking user: {$user->name} ({$user->email})");
            
            // Set the user context for MsGraph
            auth()->loginUsingId($user->id);
            
            // Check if Microsoft is connected for this user
            if (!MsGraph::isConnected()) {
                $this->warn("  - Skipped: Microsoft not connected");
                continue;
            }
            
            try {
                $syncLog = CalendarSyncLog::create([
                    'user_id' => $user->id,
                    'sync_type' => 'automatic',
                    'status' => 'started',
                    'sync_started_at' => now(),
                    'sync_from' => Carbon::now()->startOfDay(),
                    'sync_to' => Carbon::now()->addMonths(3)->endOfDay(),
                ]);
                
                // Get events for the next 3 months
                $startDateTime = Carbon::now()->startOfDay();
                $endDateTime = Carbon::now()->addMonths(3)->endOfDay();
                
                $events = MsGraph::get('/me/calendar/events?$filter=start/dateTime ge \'' . 
                    $startDateTime->toIso8601String() . '\' and start/dateTime le \'' . 
                    $endDateTime->toIso8601String() . '\'&$orderby=start/dateTime&$top=100');
                
                if (!isset($events['value'])) {
                    throw new \Exception('No events data received from Microsoft');
                }
                
                $syncedCount = 0;
                $failedCount = 0;
                
                foreach ($events['value'] as $msEvent) {
                    try {
                        DB::beginTransaction();
                        
                        // Check if event already exists
                        $existingEvent = CalendarEvent::where('user_id', $user->id)
                            ->where('ms_event_id', $msEvent['id'])
                            ->first();
                        
                        $startDateTime = Carbon::parse($msEvent['start']['dateTime']);
                        $endDateTime = Carbon::parse($msEvent['end']['dateTime']);
                        
                        if ($existingEvent) {
                            // Update existing event
                            $oldSubject = $existingEvent->subject;
                            $oldStart = $existingEvent->start_datetime;
                            
                            $existingEvent->update([
                                'subject' => $msEvent['subject'] ?? 'No Subject',
                                'body' => isset($msEvent['body']['content']) ? strip_tags($msEvent['body']['content']) : null,
                                'start_datetime' => $startDateTime,
                                'end_datetime' => $endDateTime,
                                'timezone' => $msEvent['start']['timeZone'] ?? config('app.timezone'),
                                'is_all_day' => $msEvent['isAllDay'] ?? false,
                                'location' => isset($msEvent['location']['displayName']) ? $msEvent['location']['displayName'] : null,
                                'attendees' => $msEvent['attendees'] ?? [],
                                'categories' => $msEvent['categories'] ?? [],
                                'ms_raw_data' => $msEvent,
                            ]);
                            
                            // Log update if significant changes
                            if ($oldSubject != $existingEvent->subject || !$oldStart->equalTo($startDateTime)) {
                                CalendarActivity::log($existingEvent->id, 'synced', 
                                    'Updated event from Microsoft 365',
                                    [
                                        'subject' => ['old' => $oldSubject, 'new' => $existingEvent->subject],
                                        'start_datetime' => ['old' => $oldStart->format('d-m-Y H:i'), 'new' => $startDateTime->format('d-m-Y H:i')]
                                    ],
                                    ['source' => 'auto_sync']
                                );
                            }
                        } else {
                            // Create new event
                            $newEvent = CalendarEvent::create([
                                'user_id' => $user->id,
                                'ms_event_id' => $msEvent['id'],
                                'subject' => $msEvent['subject'] ?? 'No Subject',
                                'body' => isset($msEvent['body']['content']) ? strip_tags($msEvent['body']['content']) : null,
                                'start_datetime' => $startDateTime,
                                'end_datetime' => $endDateTime,
                                'timezone' => $msEvent['start']['timeZone'] ?? config('app.timezone'),
                                'is_all_day' => $msEvent['isAllDay'] ?? false,
                                'location' => isset($msEvent['location']['displayName']) ? $msEvent['location']['displayName'] : null,
                                'attendees' => $msEvent['attendees'] ?? [],
                                'categories' => $msEvent['categories'] ?? [],
                                'organizer_email' => $msEvent['organizer']['emailAddress']['address'] ?? null,
                                'organizer_name' => $msEvent['organizer']['emailAddress']['name'] ?? null,
                                'ms_raw_data' => $msEvent,
                                'is_converted' => false,
                            ]);
                            
                            // Log creation
                            CalendarActivity::log($newEvent->id, 'synced', 
                                'Imported event from Microsoft 365',
                                null,
                                [
                                    'event_date' => $startDateTime->format('d-m-Y'),
                                    'event_time' => $startDateTime->format('H:i') . ' - ' . $endDateTime->format('H:i'),
                                    'source' => 'auto_sync'
                                ]
                            );
                        }
                        
                        DB::commit();
                        $syncedCount++;
                        
                    } catch (\Exception $e) {
                        DB::rollback();
                        $failedCount++;
                        Log::error('Failed to sync individual event', [
                            'user_id' => $user->id,
                            'ms_event_id' => $msEvent['id'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Update sync log
                $syncLog->update([
                    'status' => 'completed',
                    'events_synced' => $syncedCount,
                    'events_failed' => $failedCount,
                    'sync_completed_at' => now(),
                ]);
                
                $totalSynced += $syncedCount;
                $totalFailed += $failedCount;
                
                $this->info("  - Synced: {$syncedCount} events, Failed: {$failedCount}");
                
            } catch (\Exception $e) {
                if (isset($syncLog)) {
                    $syncLog->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sync_completed_at' => now(),
                    ]);
                }
                
                $this->error("  - Error: " . $e->getMessage());
                Log::error('Calendar sync failed for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info('');
        $this->info("Synchronization complete!");
        $this->info("Total synced: {$totalSynced} events");
        $this->info("Total failed: {$totalFailed} events");
        
        return Command::SUCCESS;
    }
}