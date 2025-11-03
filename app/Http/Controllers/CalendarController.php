<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\CalendarSyncLog;
use App\Models\CalendarActivity;
use App\Models\TimeEntry;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\ProjectSubtask;
use App\Models\User;
use App\Mail\CalendarInvitation;
use App\Services\AICalendarPredictionService;
use App\Services\MicrosoftGraphService;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Display the calendar view
     */
    public function index(Request $request)
    {
        // Try to initialize connection with stored tokens first
        $connected = MicrosoftGraphService::initializeConnection() || MsGraph::isConnected();

        if (!$connected) {
            return redirect()->route('msgraph.connect');
        }

        // Get the current Microsoft account info
        $accountInfo = MicrosoftGraphService::getCurrentAccount();
        if ($accountInfo) {
            session(['ms_user_email' => $accountInfo['email']]);
        }

        // Auto-sync if enabled and last sync was more than 1 hour ago
        $autoSyncEnabled = \App\Models\Setting::get('calendar_auto_sync', 'true') === 'true';
        $autoSyncInterval = (int)\App\Models\Setting::get('calendar_auto_sync_interval', '60'); // minutes
        
        if ($autoSyncEnabled && !$request->has('skip_sync')) {
            $lastSync = CalendarSyncLog::where('user_id', Auth::id())
                ->where('status', 'completed')
                ->latest('created_at')
                ->first();

            $shouldSync = false;
            if (!$lastSync) {
                $shouldSync = true; // No previous sync
            } else {
                // Use sync_completed_at if available, otherwise fall back to created_at
                $lastSyncTime = $lastSync->sync_completed_at ?? $lastSync->created_at;
                if ($lastSyncTime) {
                    // Ensure both times are in the same timezone for proper comparison
                    $lastSyncTimeUtc = $lastSyncTime->utc();
                    $nowUtc = now()->utc();
                    $minutesSinceLastSync = $lastSyncTimeUtc->diffInMinutes($nowUtc);
                    $shouldSync = $minutesSinceLastSync >= $autoSyncInterval;

                }
            }
            
            if ($shouldSync) {
                try {
                    // Log auto-sync attempt
                    Log::info('Auto-sync triggered for user', [
                        'user_id' => Auth::id(),
                        'last_sync_time' => $lastSync ? ($lastSync->sync_completed_at ?? $lastSync->created_at) : 'never',
                        'minutes_since_last' => $lastSync ? ($lastSync->sync_completed_at ?? $lastSync->created_at)->diffInMinutes(now()) : 'n/a',
                        'interval' => $autoSyncInterval
                    ]);

                    // Run sync directly using the same logic as manual sync
                    $this->performAutoSync();

                } catch (\Exception $e) {
                    Log::error('Auto-sync failed', [
                        'user_id' => Auth::id(),
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        // Skip the calendar test for now - let's try to work with it
        // Some accounts need different endpoints

        // Get date range for calendar view
        $startDate = $request->get('start', Carbon::now()->startOfMonth());
        $endDate = $request->get('end', Carbon::now()->endOfMonth());
        
        // Get view type (month, week, day)
        $viewType = $request->get('view', 'month');
        
        // Get cached events from database - use wider range if no dates provided to show more events
        $startDate = $request->get('start') ? Carbon::parse($request->get('start')) : Carbon::now()->subDays(30);
        $endDate = $request->get('end') ? Carbon::parse($request->get('end')) : Carbon::now()->addDays(60);
        
        $events = CalendarEvent::where('user_id', Auth::id())
            ->whereBetween('start_datetime', [$startDate, $endDate])
            ->orderBy('start_datetime')
            ->get();
        
        // Get sync status and calculate next sync time (use same logic as auto-sync)
        $lastSyncForDisplay = CalendarSyncLog::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest('created_at')
            ->first();

        // Calculate next sync time
        $nextSyncMinutes = null;
        if ($autoSyncEnabled && $lastSyncForDisplay) {
            // Use sync_completed_at if available, otherwise fall back to created_at
            $lastSyncTime = $lastSyncForDisplay->sync_completed_at ?? $lastSyncForDisplay->created_at;
            if ($lastSyncTime) {
                // Ensure both times are in the same timezone for proper comparison
                $lastSyncTimeUtc = $lastSyncTime->utc();
                $nowUtc = now()->utc();
                $minutesSinceLastSync = $lastSyncTimeUtc->diffInMinutes($nowUtc);
                $nextSyncMinutes = max(0, $autoSyncInterval - $minutesSinceLastSync);
                $nextSyncMinutes = (int) round($nextSyncMinutes);
            } else {
                $nextSyncMinutes = 0;
            }
        } elseif ($autoSyncEnabled) {
            // Auto-sync is enabled but no previous sync - next sync is immediate
            $nextSyncMinutes = 0;
        }

        // Get user's projects for quick conversion
        $projects = Project::when(Auth::user()->role !== 'super_admin', function($q) {
            $q->where('company_id', Auth::user()->company_id);
        })->where('status', 'active')->get();
        
        // Get date/time format from settings
        $dateFormat = \App\Models\Setting::get('date_format', 'd-m-Y');
        $timeFormat = \App\Models\Setting::get('time_format', 'H:i');
        
        // Get recent activities
        $activities = CalendarActivity::with(['user', 'calendarEvent'])
            ->whereHas('calendarEvent', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->orWhere('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get();
        
        // Rename for view compatibility
        $lastSync = $lastSyncForDisplay;

        return view('calendar.index', compact('events', 'lastSync', 'nextSyncMinutes', 'projects', 'viewType', 'startDate', 'endDate', 'dateFormat', 'timeFormat', 'activities'));
    }
    
    /**
     * Show Microsoft connection page
     */
    public function connect()
    {
        // Check if already connected
        if (MsGraph::isConnected()) {
            // Check which account is connected
            try {
                $me = MsGraph::get('me');
                $currentEmail = $me['mail'] ?? $me['userPrincipalName'] ?? 'Unknown';
                
                return view('calendar.connect', [
                    'isConnected' => true,
                    'currentAccount' => $currentEmail
                ]);
            } catch (\Exception $e) {
                // Error getting account info
                MsGraph::disconnect();
            }
        }
        
        return view('calendar.connect', [
            'isConnected' => false,
            'currentAccount' => null
        ]);
    }
    
    /**
     * AJAX sync endpoint for JavaScript interval sync
     */
    public function ajaxSync(Request $request)
    {
        try {
            // Check last sync to prevent too frequent syncs
            $lastSync = CalendarSyncLog::where('user_id', Auth::id())
                ->where('status', 'completed')
                ->whereNotNull('sync_completed_at')
                ->latest('sync_completed_at')
                ->first();
            
            // Don't sync if last sync was less than 5 minutes ago (for AJAX calls)
            if ($lastSync && $lastSync->sync_completed_at && $lastSync->sync_completed_at->diffInMinutes(now()) < 5) {
                return response()->json([
                    'status' => 'skipped',
                    'message' => 'Recently synced',
                    'last_sync' => $lastSync->sync_completed_at->format('Y-m-d H:i:s'),
                    'next_sync' => $lastSync->sync_completed_at->addMinutes(5)->format('Y-m-d H:i:s')
                ]);
            }
            
            // Run sync command
            \Artisan::call('calendar:sync', [
                '--user' => Auth::id()
            ]);
            
            // Get updated sync log
            $newSync = CalendarSyncLog::where('user_id', Auth::id())
                ->whereNotNull('sync_completed_at')
                ->latest('sync_completed_at')
                ->first();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Calendar synced successfully',
                'events_synced' => $newSync ? $newSync->events_synced : 0,
                'last_sync' => $newSync && $newSync->sync_completed_at ? $newSync->sync_completed_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            Log::error('AJAX sync failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync calendar events from Microsoft 365
     */
    public function sync(Request $request)
    {
        try {
            // Create sync log entry
            $syncLog = CalendarSyncLog::create([
                'user_id' => Auth::id(),
                'sync_type' => $request->get('type', 'manual'),
                'status' => 'started',
                'sync_from' => Carbon::now()->subDays(30),
                'sync_to' => Carbon::now()->addDays(90),
                'sync_started_at' => now(),
            ]);
            
            // Get date range for sync with proper timezone format
            $startDateTime = Carbon::now()->subDays(30)->toISOString();
            $endDateTime = Carbon::now()->addDays(90)->toISOString();
            
            // Try different approaches to get calendar events
            $response = null;
            $msEvents = [];
            
            // Method 1: Try calendar view endpoint
            try {
                $response = MsGraph::get('me/calendar/calendarView', [
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                    '$orderby' => 'start/dateTime',
                    '$top' => 100
                ]);
                $msEvents = $response['value'] ?? [];
            } catch (\Exception $e1) {
                // Check for mailbox not enabled error
                if (strpos($e1->getMessage(), 'MailboxNotEnabledForRESTAPI') !== false) {
                    // Store the connected email for display
                    try {
                        $me = MsGraph::get('me');
                        session(['ms_user_email' => $me['mail'] ?? $me['userPrincipalName'] ?? 'Unknown']);
                    } catch (\Exception $e) {
                        session(['ms_user_email' => 'Unknown']);
                    }
                    
                    return redirect()->route('calendar.switch-account')
                        ->with('error', 'This Microsoft account does not have a valid Exchange Online mailbox. Please disconnect and login with an account that has calendar access.');
                }
                // Method 2: Try events endpoint
                try {
                    $response = MsGraph::get('me/events', [
                        '$filter' => "start/dateTime ge '$startDateTime' and start/dateTime le '$endDateTime'",
                        '$orderby' => 'start/dateTime',
                        '$top' => 100
                    ]);
                    $msEvents = $response['value'] ?? [];
                } catch (\Exception $e2) {
                    // Method 3: Try calendar events
                    try {
                        $response = MsGraph::get('me/calendar/events', [
                            '$orderby' => 'start/dateTime',
                            '$top' => 100
                        ]);
                        $msEvents = $response['value'] ?? [];
                    } catch (\Exception $e3) {
                        throw new \Exception('Unable to access calendar. Error: ' . $e1->getMessage());
                    }
                }
            }

            $eventsCreated = 0;
            $eventsUpdated = 0;
            $eventsDeleted = 0;

            DB::beginTransaction();

            // Get all current MS event IDs from the sync
            $msEventIds = collect($msEvents)->pluck('id')->filter()->all();
            
            foreach ($msEvents as $msEvent) {
                try {
                    // Normalize attendees from Microsoft Graph format to our local format
                    $normalizedAttendees = [];
                    if (isset($msEvent['attendees']) && is_array($msEvent['attendees'])) {
                        foreach ($msEvent['attendees'] as $msAttendee) {
                            $normalizedAttendees[] = [
                                'name' => $msAttendee['emailAddress']['name'] ?? $msAttendee['emailAddress']['address'] ?? 'Unknown',
                                'email' => $msAttendee['emailAddress']['address'] ?? '',
                                'type' => $msAttendee['type'] ?? 'required',
                                'status' => $msAttendee['status']['response'] ?? 'none',
                                'user_id' => null
                            ];
                        }
                    }
                    
                    // First check if this event already exists for ANY user
                    $existingEvent = CalendarEvent::where('ms_event_id', $msEvent['id'])->first();
                    
                    if ($existingEvent) {
                        // If it exists but for a different user, update it to current user
                        if ($existingEvent->user_id != Auth::id()) {
                            $existingEvent->user_id = Auth::id();
                            $existingEvent->save();
                            \Log::info('Reassigned event to correct user', [
                                'event_id' => $existingEvent->id,
                                'old_user' => $existingEvent->user_id,
                                'new_user' => Auth::id()
                            ]);
                        }
                        
                        // Update the existing event
                        $existingEvent->update([
                            'subject' => $msEvent['subject'] ?? 'No Subject',
                            'body' => strip_tags($msEvent['body']['content'] ?? ''),
                            'start_datetime' => Carbon::parse($msEvent['start']['dateTime'])->setTimezone($msEvent['start']['timeZone'] ?? 'UTC'),
                            'end_datetime' => Carbon::parse($msEvent['end']['dateTime'])->setTimezone($msEvent['end']['timeZone'] ?? 'UTC'),
                            'timezone' => $msEvent['start']['timeZone'] ?? 'UTC',
                            'is_all_day' => $msEvent['isAllDay'] ?? false,
                            'location' => $msEvent['location']['displayName'] ?? null,
                            'attendees' => json_encode($normalizedAttendees),
                            'categories' => json_encode($msEvent['categories'] ?? []),
                            'organizer_email' => $msEvent['organizer']['emailAddress']['address'] ?? null,
                            'organizer_name' => $msEvent['organizer']['emailAddress']['name'] ?? null,
                            'ms_raw_data' => json_encode($msEvent),
                        ]);
                        $eventsUpdated++;
                    } else {
                        // Create new event
                        CalendarEvent::create([
                            'user_id' => Auth::id(),
                            'ms_event_id' => $msEvent['id'],
                            'subject' => $msEvent['subject'] ?? 'No Subject',
                            'body' => strip_tags($msEvent['body']['content'] ?? ''),
                            'start_datetime' => Carbon::parse($msEvent['start']['dateTime'])->setTimezone($msEvent['start']['timeZone'] ?? 'UTC'),
                            'end_datetime' => Carbon::parse($msEvent['end']['dateTime'])->setTimezone($msEvent['end']['timeZone'] ?? 'UTC'),
                            'timezone' => $msEvent['start']['timeZone'] ?? 'UTC',
                            'is_all_day' => $msEvent['isAllDay'] ?? false,
                            'location' => $msEvent['location']['displayName'] ?? null,
                            'attendees' => json_encode($normalizedAttendees),
                            'categories' => json_encode($msEvent['categories'] ?? []),
                            'organizer_email' => $msEvent['organizer']['emailAddress']['address'] ?? null,
                            'organizer_name' => $msEvent['organizer']['emailAddress']['name'] ?? null,
                            'ms_raw_data' => json_encode($msEvent),
                        ]);
                        $eventsCreated++;
                    }
                } catch (\Exception $eventError) {
                    \Log::warning('Failed to sync individual event', [
                        'event_id' => $msEvent['id'] ?? 'unknown',
                        'subject' => $msEvent['subject'] ?? 'unknown',
                        'error' => $eventError->getMessage()
                    ]);
                    // Continue with next event
                }
            }

            // Delete events that are no longer in Microsoft 365 (within the sync date range)
            if (!empty($msEventIds)) {
                $deletedEvents = CalendarEvent::where('user_id', Auth::id())
                    ->whereBetween('start_datetime', [
                        Carbon::now()->subDays(30),
                        Carbon::now()->addDays(90)
                    ])
                    ->whereNotIn('ms_event_id', $msEventIds)
                    ->get();

                foreach ($deletedEvents as $deletedEvent) {
                    Log::info('Deleting event no longer in Microsoft 365', [
                        'event_id' => $deletedEvent->id,
                        'ms_event_id' => $deletedEvent->ms_event_id,
                        'subject' => $deletedEvent->subject,
                        'user_id' => Auth::id()
                    ]);
                    $deletedEvent->delete();
                    $eventsDeleted++;
                }
            }

            // Update sync log
            $syncLog->update([
                'status' => 'completed',
                'events_synced' => count($msEvents),
                'events_created' => $eventsCreated,
                'events_updated' => $eventsUpdated,
                'sync_completed_at' => now(),
                'details' => json_encode([
                    'total_fetched' => count($msEvents),
                    'events_deleted' => $eventsDeleted
                ])
            ]);
            
            DB::commit();
            
            return redirect()->route('calendar.index')
                ->with('success', sprintf('Calendar synced successfully! %d events synced (%d new, %d updated, %d deleted)',
                    count($msEvents), $eventsCreated, $eventsUpdated, $eventsDeleted));
                    
        } catch (\Exception $e) {
            DB::rollback();
            
            // Update sync log with error (truncate if needed)
            if (isset($syncLog)) {
                $errorMessage = $e->getMessage();
                // Truncate error message if it's too long
                if (strlen($errorMessage) > 1000) {
                    $errorMessage = substr($errorMessage, 0, 997) . '...';
                }
                
                $syncLog->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'sync_completed_at' => now()
                ]);
            }
            
            Log::error('Calendar sync failed', ['error' => $e->getMessage()]);
            
            return back()->with('error', 'Failed to sync calendar: ' . $e->getMessage());
        }
    }

    /**
     * Perform auto sync without redirect (used for background sync)
     */
    private function performAutoSync()
    {
        // Create sync log entry
        $syncLog = CalendarSyncLog::create([
            'user_id' => Auth::id(),
            'sync_type' => 'automatic',
            'status' => 'started',
            'sync_from' => Carbon::now()->subDays(30),
            'sync_to' => Carbon::now()->addDays(90),
            'sync_started_at' => now(),
        ]);

        // Get date range for sync with proper timezone format
        $startDateTime = Carbon::now()->subDays(30)->toISOString();
        $endDateTime = Carbon::now()->addDays(90)->toISOString();

        // Try different approaches to get calendar events
        $response = null;
        $msEvents = [];

        // Method 1: Try calendar view endpoint
        try {
            $response = MsGraph::get('me/calendar/calendarView', [
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime,
                '$orderby' => 'start/dateTime',
                '$top' => 100
            ]);
            $msEvents = $response['value'] ?? [];
        } catch (\Exception $e1) {
            // Method 2: Try events endpoint
            try {
                $response = MsGraph::get('me/events', [
                    '$filter' => "start/dateTime ge '$startDateTime' and start/dateTime le '$endDateTime'",
                    '$orderby' => 'start/dateTime',
                    '$top' => 100
                ]);
                $msEvents = $response['value'] ?? [];
            } catch (\Exception $e2) {
                // Method 3: Try calendar events
                try {
                    $response = MsGraph::get('me/calendar/events', [
                        '$orderby' => 'start/dateTime',
                        '$top' => 100
                    ]);
                    $msEvents = $response['value'] ?? [];
                } catch (\Exception $e3) {
                    throw new \Exception('Unable to access calendar during auto-sync');
                }
            }
        }

        $eventsCreated = 0;
        $eventsUpdated = 0;
        $eventsDeleted = 0;

        DB::beginTransaction();

        // Get all current MS event IDs from the sync
        $msEventIds = collect($msEvents)->pluck('id')->filter()->all();

        foreach ($msEvents as $msEvent) {
            try {
                // Normalize attendees from Microsoft Graph format to our local format
                $normalizedAttendees = [];
                if (isset($msEvent['attendees']) && is_array($msEvent['attendees'])) {
                    foreach ($msEvent['attendees'] as $msAttendee) {
                        $normalizedAttendees[] = [
                            'name' => $msAttendee['emailAddress']['name'] ?? $msAttendee['emailAddress']['address'] ?? 'Unknown',
                            'email' => $msAttendee['emailAddress']['address'] ?? '',
                            'type' => $msAttendee['type'] ?? 'required',
                            'status' => $msAttendee['status']['response'] ?? 'none',
                            'user_id' => null
                        ];
                    }
                }

                // First check if this event already exists for this user
                $existingEvent = CalendarEvent::where('ms_event_id', $msEvent['id'])
                    ->where('user_id', Auth::id())
                    ->first();

                if ($existingEvent) {
                    // Update the existing event
                    $existingEvent->update([
                        'subject' => $msEvent['subject'] ?? 'No Subject',
                        'body' => strip_tags($msEvent['body']['content'] ?? ''),
                        'start_datetime' => Carbon::parse($msEvent['start']['dateTime'])->setTimezone($msEvent['start']['timeZone'] ?? 'UTC'),
                        'end_datetime' => Carbon::parse($msEvent['end']['dateTime'])->setTimezone($msEvent['end']['timeZone'] ?? 'UTC'),
                        'timezone' => $msEvent['start']['timeZone'] ?? 'UTC',
                        'is_all_day' => $msEvent['isAllDay'] ?? false,
                        'location' => $msEvent['location']['displayName'] ?? null,
                        'attendees' => json_encode($normalizedAttendees),
                        'categories' => json_encode($msEvent['categories'] ?? []),
                        'organizer_email' => $msEvent['organizer']['emailAddress']['address'] ?? null,
                        'organizer_name' => $msEvent['organizer']['emailAddress']['name'] ?? null,
                        'ms_raw_data' => json_encode($msEvent),
                    ]);
                    $eventsUpdated++;
                } else {
                    // Create new event
                    CalendarEvent::create([
                        'user_id' => Auth::id(),
                        'ms_event_id' => $msEvent['id'],
                        'subject' => $msEvent['subject'] ?? 'No Subject',
                        'body' => strip_tags($msEvent['body']['content'] ?? ''),
                        'start_datetime' => Carbon::parse($msEvent['start']['dateTime'])->setTimezone($msEvent['start']['timeZone'] ?? 'UTC'),
                        'end_datetime' => Carbon::parse($msEvent['end']['dateTime'])->setTimezone($msEvent['end']['timeZone'] ?? 'UTC'),
                        'timezone' => $msEvent['start']['timeZone'] ?? 'UTC',
                        'is_all_day' => $msEvent['isAllDay'] ?? false,
                        'location' => $msEvent['location']['displayName'] ?? null,
                        'attendees' => json_encode($normalizedAttendees),
                        'categories' => json_encode($msEvent['categories'] ?? []),
                        'organizer_email' => $msEvent['organizer']['emailAddress']['address'] ?? null,
                        'organizer_name' => $msEvent['organizer']['emailAddress']['name'] ?? null,
                        'ms_raw_data' => json_encode($msEvent),
                    ]);
                    $eventsCreated++;
                }
            } catch (\Exception $eventError) {
                Log::warning('Failed to sync individual event during auto-sync', [
                    'event_id' => $msEvent['id'] ?? 'unknown',
                    'subject' => $msEvent['subject'] ?? 'unknown',
                    'error' => $eventError->getMessage()
                ]);
            }
        }

        // Delete events that are no longer in Microsoft 365 (within the sync date range)
        if (!empty($msEventIds)) {
            $deletedEvents = CalendarEvent::where('user_id', Auth::id())
                ->whereBetween('start_datetime', [
                    Carbon::now()->subDays(30),
                    Carbon::now()->addDays(90)
                ])
                ->whereNotIn('ms_event_id', $msEventIds)
                ->get();

            foreach ($deletedEvents as $deletedEvent) {
                $deletedEvent->delete();
                $eventsDeleted++;
            }
        }

        // Update sync log
        $syncLog->update([
            'status' => 'completed',
            'events_synced' => count($msEvents),
            'events_created' => $eventsCreated,
            'events_updated' => $eventsUpdated,
            'sync_completed_at' => now(),
            'details' => json_encode([
                'total_fetched' => count($msEvents),
                'events_deleted' => $eventsDeleted
            ])
        ]);

        DB::commit();

        Log::info('Auto-sync completed successfully', [
            'user_id' => Auth::id(),
            'events_synced' => count($msEvents),
            'events_created' => $eventsCreated,
            'events_updated' => $eventsUpdated,
            'events_deleted' => $eventsDeleted
        ]);
    }

    /**
     * Convert calendar event to time entry
     */
    public function convertToTimeEntry(Request $request, CalendarEvent $calendarEvent)
    {
        // Check ownership
        if ($calendarEvent->user_id !== Auth::id()) {
            // Voor AJAX requests, return JSON
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You can only convert your own calendar events.'], 403);
            }
            abort(403, 'You can only convert your own calendar events.');
        }
        
        // Check if already converted
        if ($calendarEvent->is_converted) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This event has already been converted to a time entry.'], 400);
            }
            return back()->with('warning', 'This event has already been converted to a time entry.');
        }
        
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_milestone_id' => 'nullable|exists:project_milestones,id',
            'project_task_id' => 'nullable|exists:project_tasks,id',
            'project_subtask_id' => 'nullable|exists:project_subtasks,id',
            'description' => 'nullable|string',
            'is_billable' => 'required|in:billable,non_billable',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Calculate duration
            $startTime = Carbon::parse($calendarEvent->start_datetime);
            $endTime = Carbon::parse($calendarEvent->end_datetime);
            $totalMinutes = $startTime->diffInMinutes($endTime);
            $hours = floor($totalMinutes / 60);
            $remainingMinutes = $totalMinutes % 60;
            
            // Bepaal de hourly rate volgens de hiërarchie
            $project = Project::find($request->project_id);
            $hourlyRate = $this->determineHourlyRate(
                $project,
                $request->project_milestone_id,
                $request->project_task_id,
                $request->project_subtask_id
            );
            
            // Create time entry
            $timeEntry = TimeEntry::create([
                'user_id' => Auth::id(),
                'project_id' => $request->project_id,
                'project_milestone_id' => $request->project_milestone_id,
                'project_task_id' => $request->project_task_id,
                'project_subtask_id' => $request->project_subtask_id,
                'entry_date' => $startTime->format('Y-m-d'),
                'hours' => $hours,
                'minutes' => $totalMinutes, // Gebruik het totale aantal minuten voor de dropdown
                'description' => $request->description ?: $calendarEvent->subject . ($calendarEvent->body ? "\n\n" . $calendarEvent->body : ''),
                'is_billable' => $request->is_billable,
                'status' => 'pending',
                'hourly_rate_used' => $hourlyRate,
            ]);
            
            // Mark calendar event as converted
            $calendarEvent->update([
                'is_converted' => true,
                'time_entry_id' => $timeEntry->id
            ]);
            
            DB::commit();
            
            // Log the activity
            CalendarActivity::log($calendarEvent->id, 'converted', 
                'Converted event "' . $calendarEvent->subject . '" (' . $calendarEvent->start_datetime->format('d-m-Y H:i') . ') to time entry',
                null,
                [
                    'event_date' => $calendarEvent->start_datetime->format('d-m-Y'),
                    'event_time' => $calendarEvent->start_datetime->format('H:i') . ' - ' . $calendarEvent->end_datetime->format('H:i'),
                    'time_entry_id' => $timeEntry->id,
                    'project_id' => $request->project_id,
                    'hours' => $hours,
                    'minutes' => $totalMinutes,
                    'is_billable' => $request->is_billable
                ]
            );
            
            // Voor AJAX requests, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Calendar event successfully converted to time entry.',
                    'redirect' => route('time-entries.edit', $timeEntry)
                ]);
            }
            
            return redirect()->route('time-entries.edit', $timeEntry)
                ->with('success', 'Calendar event successfully converted to time entry. Please review and submit.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to convert calendar event', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to convert event: ' . $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Failed to convert event: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk convert calendar events to time entries
     */
    public function bulkConvert(Request $request)
    {
        $request->validate([
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:calendar_events,id',
            'project_id' => 'required|exists:projects,id',
            'is_billable' => 'required|in:billable,non_billable',
        ]);
        
        try {
            DB::beginTransaction();
            
            $converted = 0;
            $skipped = 0;
            
            foreach ($request->event_ids as $eventId) {
                $calendarEvent = CalendarEvent::find($eventId);
                
                // Check ownership
                if ($calendarEvent->user_id !== Auth::id()) {
                    $skipped++;
                    continue;
                }
                
                // Skip if already converted
                if ($calendarEvent->is_converted) {
                    $skipped++;
                    continue;
                }
                
                // Calculate duration
                $startTime = Carbon::parse($calendarEvent->start_datetime);
                $endTime = Carbon::parse($calendarEvent->end_datetime);
                $totalMinutes = $startTime->diffInMinutes($endTime);
                $hours = floor($totalMinutes / 60);
                
                // Bepaal de hourly rate
                $project = Project::find($request->project_id);
                $hourlyRate = $this->determineHourlyRate(
                    $project,
                    null, // milestone_id
                    null, // task_id
                    null  // subtask_id
                );
                
                // Create time entry
                $timeEntry = TimeEntry::create([
                    'user_id' => Auth::id(),
                    'project_id' => $request->project_id,
                    'entry_date' => $startTime->format('Y-m-d'),
                    'hours' => $hours,
                    'minutes' => $totalMinutes, // Gebruik totale minuten voor dropdown
                    'description' => $calendarEvent->subject . ($calendarEvent->body ? "\n\n" . $calendarEvent->body : ''),
                    'is_billable' => $request->is_billable,
                    'status' => 'pending',
                    'hourly_rate_used' => $hourlyRate,
                ]);
                
                // Mark as converted
                $calendarEvent->update([
                    'is_converted' => true,
                    'time_entry_id' => $timeEntry->id
                ]);
                
                $converted++;
            }
            
            DB::commit();
            
            return redirect()->route('time-entries.index')
                ->with('success', sprintf('%d events converted to time entries. %d skipped.', $converted, $skipped));
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk conversion failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Bulk conversion failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get calendar events for API/AJAX
     */
    public function getEvents(Request $request)
    {
        try {
            // Debug logging
            \Log::info('Calendar getEvents called', [
                'user_id' => Auth::id(),
                'start' => $request->get('start'),
                'end' => $request->get('end')
            ]);
            
            // Parse dates with fallback
            $start = $request->get('start') ? Carbon::parse($request->get('start')) : Carbon::now()->startOfMonth();
            $end = $request->get('end') ? Carbon::parse($request->get('end')) : Carbon::now()->endOfMonth();
            
            // Get events from database
            $events = CalendarEvent::where('user_id', Auth::id())
                ->whereBetween('start_datetime', [$start, $end])
                ->get();
            
            \Log::info('Found events', [
                'count' => $events->count(),
                'date_range' => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d')
            ]);
            
            // If no events in current range, get all events for debugging
            if ($events->count() === 0) {
                $allEvents = CalendarEvent::where('user_id', Auth::id())->get();
                \Log::info('All events for user', [
                    'total_count' => $allEvents->count(),
                    'dates' => $allEvents->pluck('start_datetime')->map(function($date) {
                        return $date->format('Y-m-d H:i');
                    })
                ]);
            }
            
            $formattedEvents = $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->subject,
                    'start' => $event->start_datetime->toIso8601String(),
                    'end' => $event->end_datetime->toIso8601String(),
                    'allDay' => $event->is_all_day,
                    'backgroundColor' => $event->is_converted ? '#10b981' : '#3b82f6',
                    'borderColor' => $event->is_converted ? '#059669' : '#2563eb',
                    'converted' => $event->is_converted,
                    'location' => $event->location,
                    'extendedProps' => [
                        'body' => $event->body,
                        'attendees' => is_string($event->attendees) ? json_decode($event->attendees) : $event->attendees,
                        'categories' => is_string($event->categories) ? json_decode($event->categories) : $event->categories,
                        'timeEntryId' => $event->time_entry_id
                    ]
                ];
            });
            
            return response()->json($formattedEvents);
            
        } catch (\Exception $e) {
            \Log::error('Error in getEvents', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get project milestones for AJAX dropdown
     */
    public function getProjectMilestones(Request $request, $project)
    {
        try {
            // Debug logging
            \Log::info('getProjectMilestones called with project: ' . json_encode($project));
            
            // Handle both ID and model binding
            if (!($project instanceof Project)) {
                \Log::info('Project is not instance, trying to find by ID: ' . $project);
                $project = Project::findOrFail($project);
            }
            
            \Log::info('Project loaded: ' . $project->id . ' - ' . $project->name);
            
            // Check if user has access to this project
            if (!Auth::check()) {
                \Log::error('User not authenticated');
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            
            \Log::info('User authenticated: ' . Auth::user()->id . ' - ' . Auth::user()->name);
            
            if (Auth::user()->role !== 'super_admin' && $project->company_id !== Auth::user()->company_id) {
                \Log::warning('Access denied for user to project');
                return response()->json(['error' => 'Access denied'], 403);
            }
            
            // Fix: gebruik $project->id ipv $projectId
            $milestones = ProjectMilestone::where('project_id', $project->id)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($milestone) {
                    return [
                        'id' => $milestone->id,
                        'name' => $milestone->name,
                        'status' => $milestone->status
                    ];
                });
            
            \Log::info('Milestones found: ' . $milestones->count());
            
            return response()->json($milestones);
        } catch (\Exception $e) {
            \Log::error('Error in getProjectMilestones: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }
    
    /**
     * Get milestone tasks for AJAX dropdown
     */
    public function getMilestoneTasks(Request $request, $milestone)
    {
        try {
            // Handle both ID and model binding
            if (!($milestone instanceof ProjectMilestone)) {
                $milestone = ProjectMilestone::findOrFail($milestone);
            }
            
            // Check if user has access
            if (Auth::user()->role !== 'super_admin' && $milestone->project->company_id !== Auth::user()->company_id) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            
            // Fix: gebruik $milestone->id ipv $milestoneId
            $tasks = ProjectTask::where('project_milestone_id', $milestone->id)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->name,
                        'status' => $task->status
                    ];
                });
            
            return response()->json($tasks);
        } catch (\Exception $e) {
            \Log::error('Error in getMilestoneTasks: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get task subtasks for AJAX dropdown
     */
    public function getTaskSubtasks(Request $request, $task)
    {
        try {
            // Handle both ID and model binding
            if (!($task instanceof ProjectTask)) {
                $task = ProjectTask::findOrFail($task);
            }
            
            // Check if user has access
            if (Auth::user()->role !== 'super_admin' && $task->milestone->project->company_id !== Auth::user()->company_id) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            
            // Fix: gebruik $task->id ipv $taskId
            $subtasks = ProjectSubtask::where('project_task_id', $task->id)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($subtask) {
                    return [
                        'id' => $subtask->id,
                        'name' => $subtask->name,
                        'status' => $subtask->status
                    ];
                });
            
            return response()->json($subtasks);
        } catch (\Exception $e) {
            \Log::error('Error in getTaskSubtasks: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle OAuth callback from Microsoft
     */
    public function callback()
    {
        // The MsGraph package handles the callback automatically
        // This method is here for documentation purposes
        return redirect()->route('calendar.index')
            ->with('success', 'Successfully connected to Microsoft 365!');
    }
    
    /**
     * Disconnect from Microsoft 365
     */
    public function disconnect(Request $request)
    {
        try {
            // Clear Microsoft Graph tokens
            MsGraph::disconnect();
            
            // Clear session data
            session()->forget('ms_user_email');
            
            // Optionally delete cached calendar events
            CalendarEvent::where('user_id', Auth::id())->delete();
            CalendarSyncLog::where('user_id', Auth::id())->delete();
            
            // Check if we should redirect to connect page
            if ($request->input('redirect_to_connect')) {
                return redirect()->route('calendar.connect')
                    ->with('info', 'Disconnected. Please login with the correct Microsoft 365 account.');
            }
            
            return redirect()->route('calendar.index')
                ->with('success', 'Disconnected from Microsoft 365. Calendar data has been removed.');
        } catch (\Exception $e) {
            Log::error('Failed to disconnect from Microsoft', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to disconnect: ' . $e->getMessage());
        }
    }
    
    /**
     * Store a new calendar event
     */
    public function store(Request $request)
    {
        // Convert datetime-local format to proper datetime if needed
        if ($request->has('start_datetime') && !str_contains($request->start_datetime, ' ')) {
            $request->merge(['start_datetime' => str_replace('T', ' ', $request->start_datetime) . ':00']);
        }
        if ($request->has('end_datetime') && !str_contains($request->end_datetime, ' ')) {
            $request->merge(['end_datetime' => str_replace('T', ' ', $request->end_datetime) . ':00']);
        }
        
        $request->validate([
            'subject' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'location' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'is_all_day' => 'nullable',
            'attendee_ids' => 'nullable|array',
            'attendee_ids.*' => 'exists:users,id',
            'external_attendees' => 'nullable|string',
            'send_invitations' => 'nullable',
            'project_id' => 'nullable|exists:projects,id',
            'project_milestone_id' => 'nullable|exists:project_milestones,id',
            'project_task_id' => 'nullable|exists:project_tasks,id',
            'project_subtask_id' => 'nullable|exists:project_subtasks,id',
            'is_billable' => 'nullable|in:billable,non_billable',
            'auto_create_time_entry' => 'nullable'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Parse dates flexibly
            $startDateTime = Carbon::parse($request->start_datetime);
            $endDateTime = Carbon::parse($request->end_datetime);
            
            // For all-day events, set times to start and end of day
            if ($request->boolean('is_all_day')) {
                $startDateTime = $startDateTime->startOfDay();
                $endDateTime = $endDateTime->endOfDay();
            }
            
            // Process attendees
            $attendees = [];
            
            // Add internal users
            if ($request->has('attendee_ids')) {
                $users = User::whereIn('id', $request->attendee_ids)
                    ->where('role', '!=', 'super_admin') // Verberg super_admin users
                    ->get();
                foreach ($users as $user) {
                    $attendees[] = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => 'required',
                        'status' => 'pending',
                        'user_id' => $user->id
                    ];
                }
            }
            
            // Add external attendees
            if ($request->filled('external_attendees')) {
                $externalEmails = array_map('trim', explode(',', $request->external_attendees));
                foreach ($externalEmails as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $attendees[] = [
                            'name' => $email,
                            'email' => $email,
                            'type' => 'required',
                            'status' => 'pending',
                            'user_id' => null
                        ];
                    }
                }
            }
            
            // Create calendar event
            $calendarEvent = CalendarEvent::create([
                'user_id' => Auth::id(),
                'ms_event_id' => 'local_' . uniqid(),
                'subject' => $request->subject,
                'body' => $request->body,
                'start_datetime' => $startDateTime,
                'end_datetime' => $endDateTime,
                'timezone' => config('app.timezone'),
                'is_all_day' => $request->boolean('is_all_day'),
                'location' => $request->location,
                'attendees' => $attendees,
                'categories' => ['Local Event'],
                'organizer_email' => Auth::user()->email,
                'organizer_name' => Auth::user()->name,
                'ms_raw_data' => [
                    'manual' => true,
                    'project_id' => $request->project_id,
                    'project_milestone_id' => $request->project_milestone_id,
                    'project_task_id' => $request->project_task_id,
                    'project_subtask_id' => $request->project_subtask_id
                ]
            ]);
            
            // Optionally create time entry immediately
            if ($request->boolean('auto_create_time_entry') && $request->project_id) {
                $startTime = Carbon::parse($request->start_datetime);
                $endTime = Carbon::parse($request->end_datetime);
                $totalMinutes = $startTime->diffInMinutes($endTime);
                
                // Bepaal de hourly rate
                $project = Project::find($request->project_id);
                $hourlyRate = $this->determineHourlyRate(
                    $project,
                    $request->project_milestone_id,
                    $request->project_task_id,
                    $request->project_subtask_id
                );
                
                $timeEntry = TimeEntry::create([
                    'user_id' => Auth::id(),
                    'project_id' => $request->project_id,
                    'project_milestone_id' => $request->project_milestone_id,
                    'project_task_id' => $request->project_task_id,
                    'project_subtask_id' => $request->project_subtask_id,
                    'entry_date' => $startTime->format('Y-m-d'),
                    'hours' => floor($totalMinutes / 60),
                    'minutes' => $totalMinutes, // Gebruik totale minuten voor dropdown
                    'description' => $request->subject . ($request->body ? "\n\n" . $request->body : ''),
                    'is_billable' => $request->is_billable ?? 'billable',
                    'status' => 'pending',
                    'hourly_rate_used' => $hourlyRate,
                ]);
                
                // Mark calendar event as converted
                $calendarEvent->update([
                    'is_converted' => true,
                    'time_entry_id' => $timeEntry->id
                ]);
            }
            
            // Try to sync with Microsoft if connected
            if (MsGraph::isConnected()) {
                try {
                    // Prepare attendees for MS Graph
                    $msAttendees = [];
                    foreach ($attendees as $attendee) {
                        $msAttendees[] = [
                            'emailAddress' => [
                                'address' => $attendee['email'],
                                'name' => $attendee['name']
                            ],
                            'type' => $attendee['type']
                        ];
                    }
                    
                    $msEvent = MsGraph::post('me/calendar/events', [
                        'subject' => $request->subject,
                        'body' => [
                            'contentType' => 'HTML',
                            'content' => $request->body ?? ''
                        ],
                        'start' => [
                            'dateTime' => $startDateTime->toIso8601String(),
                            'timeZone' => config('app.timezone')
                        ],
                        'end' => [
                            'dateTime' => $endDateTime->toIso8601String(),
                            'timeZone' => config('app.timezone')
                        ],
                        'location' => [
                            'displayName' => $request->location ?? ''
                        ],
                        'isAllDay' => $request->boolean('is_all_day'),
                        'attendees' => $msAttendees
                    ]);
                    
                    // Update with real MS event ID
                    if (isset($msEvent['id'])) {
                        $calendarEvent->update(['ms_event_id' => $msEvent['id']]);
                    }
                } catch (\Exception $e) {
                    // Log but don't fail if MS sync fails
                    Log::warning('Could not sync event to Microsoft', ['error' => $e->getMessage()]);
                }
            }
            
            // Send email invitations if requested
            if ($request->boolean('send_invitations') && count($attendees) > 0) {
                $this->sendEventInvitations($calendarEvent, $attendees);
            }
            
            DB::commit();
            
            // Log the activity
            CalendarActivity::log($calendarEvent->id, 'created', 
                'Created event "' . $request->subject . '" for ' . $calendarEvent->start_datetime->format('d-m-Y H:i'),
                null,
                [
                    'event_date' => $calendarEvent->start_datetime->format('d-m-Y'),
                    'event_time' => $calendarEvent->start_datetime->format('H:i') . ' - ' . $calendarEvent->end_datetime->format('H:i'),
                    'location' => $calendarEvent->location,
                    'has_attendees' => !empty($attendees),
                    'attendee_count' => count($attendees),
                    'auto_created_time_entry' => $request->boolean('auto_create_time_entry'),
                    'project_linked' => !is_null($request->project_id)
                ]
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'event' => [
                        'id' => $calendarEvent->id,
                        'title' => $calendarEvent->subject,
                        'start' => $calendarEvent->start_datetime->toIso8601String(),
                        'end' => $calendarEvent->end_datetime->toIso8601String()
                    ]
                ]);
            }
            
            return redirect()->route('calendar.index')
                ->with('success', 'Event created successfully' . 
                    ($calendarEvent->time_entry_id ? ' and time entry created' : ''));
                    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create event', ['error' => $e->getMessage()]);
            
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Failed to create event: ' . $e->getMessage());
        }
    }
    
    /**
     * Manually store a calendar event (legacy support)
     */
    public function manualStore(Request $request)
    {
        return $this->store($request);
    }
    
    /**
     * Import ICS file
     */
    public function import(Request $request)
    {
        $request->validate([
            'ics_file' => 'required|file|mimes:ics,txt|max:10240'
        ]);
        
        // This is a placeholder for ICS import functionality
        // You would need to parse the ICS file and create events
        
        return redirect()->route('calendar.manual')
            ->with('info', 'ICS import functionality will be available soon');
    }
    
    /**
     * Send email invitations to event attendees
     */
    protected function sendEventInvitations(CalendarEvent $event, array $attendees)
    {
        try {
            foreach ($attendees as $attendee) {
                // Send email using Laravel Mail
                \Mail::to($attendee['email'])->send(new CalendarInvitation($event, $attendee));
                
                Log::info('Event invitation sent', [
                    'event_id' => $event->id,
                    'to' => $attendee['email']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send event invitations', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle invitation response (accept/decline/tentative)
     */
    public function respondToInvitation(Request $request, CalendarEvent $event)
    {
        $response = $request->input('response');
        $email = $request->input('email');
        
        if (!in_array($response, ['accept', 'decline', 'tentative'])) {
            return redirect()->route('calendar.index')
                ->with('error', 'Invalid response type');
        }
        
        // Update attendee status
        $attendees = $event->attendees;
        if (is_string($attendees)) {
            $attendees = json_decode($attendees, true);
        }
        if (!is_array($attendees)) {
            $attendees = [];
        }
        
        $updated = false;
        
        foreach ($attendees as &$attendee) {
            if ($attendee['email'] === $email) {
                $attendee['status'] = $response;
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $event->update(['attendees' => $attendees]);
            
            // Log the response
            Log::info('Calendar invitation response', [
                'event_id' => $event->id,
                'email' => $email,
                'response' => $response
            ]);
            
            // If user is logged in and is the attendee, copy event to their calendar
            if (Auth::check() && Auth::user()->email === $email && $response === 'accept') {
                $this->copyEventToAttendee($event, Auth::user());
            }
            
            $message = match($response) {
                'accept' => 'You have accepted the meeting invitation',
                'decline' => 'You have declined the meeting invitation',
                'tentative' => 'You have tentatively accepted the meeting invitation'
            };
            
            return redirect()->route('calendar.index')
                ->with('success', $message);
        }
        
        return redirect()->route('calendar.index')
            ->with('error', 'Could not update your response');
    }
    
    /**
     * Copy event to attendee's calendar
     */
    protected function copyEventToAttendee(CalendarEvent $event, User $attendee)
    {
        try {
            CalendarEvent::create([
                'user_id' => $attendee->id,
                'ms_event_id' => 'copy_' . $event->id . '_' . uniqid(),
                'subject' => $event->subject,
                'body' => $event->body,
                'start_datetime' => $event->start_datetime,
                'end_datetime' => $event->end_datetime,
                'timezone' => $event->timezone,
                'is_all_day' => $event->is_all_day,
                'location' => $event->location,
                'attendees' => $event->attendees,
                'categories' => ['Meeting'],
                'organizer_email' => $event->organizer_email,
                'organizer_name' => $event->organizer_name,
                'ms_raw_data' => [
                    'copied_from' => $event->id,
                    'response' => 'accepted'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to copy event to attendee calendar', [
                'event_id' => $event->id,
                'attendee_id' => $attendee->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Cancel an event
     */
    public function cancelEvent(Request $request, CalendarEvent $event)
    {
        // Check if user owns the event
        if ($event->user_id !== Auth::id()) {
            abort(403, 'You can only cancel your own events.');
        }
        
        // Check if event is in the future
        if ($event->start_datetime->isPast()) {
            return back()->with('error', 'Cannot cancel events that have already started.');
        }
        
        // Check if event is already converted to time entry
        if ($event->is_converted) {
            return back()->with('error', 'Cannot cancel events that have been converted to time entries.');
        }
        
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
            'notify_attendees' => 'nullable'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Send cancellation notifications if requested
            if ($request->boolean('notify_attendees')) {
                $attendees = $event->attendees;
                if (is_string($attendees)) {
                    $attendees = json_decode($attendees, true);
                }
                
                if (is_array($attendees) && count($attendees) > 0) {
                    $this->sendCancellationEmails($event, $attendees, $request->cancellation_reason);
                }
            }
            
            // If connected to Microsoft, try to delete from Outlook
            if (MsGraph::isConnected() && !str_starts_with($event->ms_event_id, 'local_')) {
                try {
                    MsGraph::delete('me/events/' . $event->ms_event_id);
                } catch (\Exception $e) {
                    Log::warning('Could not delete event from Microsoft', ['error' => $e->getMessage()]);
                }
            }
            
            // Delete the event
            $event->delete();
            
            // Also delete any copied events for attendees
            CalendarEvent::where('ms_raw_data->copied_from', $event->id)->delete();
            
            DB::commit();
            
            return redirect()->route('calendar.index')
                ->with('success', 'Event has been cancelled' . 
                    ($request->boolean('notify_attendees') ? ' and attendees have been notified.' : '.'));
                    
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to cancel event', ['error' => $e->getMessage()]);
            
            return back()->with('error', 'Failed to cancel event: ' . $e->getMessage());
        }
    }
    
    /**
     * Send cancellation emails to attendees
     */
    protected function sendCancellationEmails(CalendarEvent $event, array $attendees, ?string $reason = null)
    {
        try {
            foreach ($attendees as $attendee) {
                \Mail::to($attendee['email'])->send(new \App\Mail\EventCancellation($event, $attendee, $reason));
                
                Log::info('Cancellation email sent', [
                    'event_id' => $event->id,
                    'to' => $attendee['email']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send cancellation emails', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Delete a calendar event and its linked time entry
     */
    public function destroy(Request $request, CalendarEvent $event)
    {
        // Check ownership
        if ($event->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You can only delete your own events.'], 403);
            }
            abort(403, 'You can only delete your own events.');
        }
        
        try {
            DB::beginTransaction();
            
            // Delete linked time entry if exists
            if ($event->time_entry_id) {
                $timeEntry = TimeEntry::find($event->time_entry_id);
                if ($timeEntry) {
                    // Only delete if not already invoiced
                    if (!$timeEntry->is_invoiced) {
                        $timeEntry->delete();
                        Log::info('Deleted linked time entry', ['time_entry_id' => $timeEntry->id]);
                    } else {
                        Log::warning('Cannot delete invoiced time entry', ['time_entry_id' => $timeEntry->id]);
                        
                        if ($request->expectsJson()) {
                            return response()->json(['error' => 'Cannot delete event with invoiced time entry.'], 400);
                        }
                        return back()->with('error', 'Cannot delete event with invoiced time entry.');
                    }
                }
            }
            
            // Delete from Microsoft 365 if synced
            if ($event->ms_event_id && !str_starts_with($event->ms_event_id, 'local_')) {
                try {
                    if (MsGraph::isConnected()) {
                        MsGraph::delete('/me/events/' . $event->ms_event_id);
                        Log::info('Deleted event from Microsoft 365', ['ms_event_id' => $event->ms_event_id]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete from Microsoft 365', [
                        'ms_event_id' => $event->ms_event_id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with local deletion even if MS delete fails
                }
            }
            
            // Store event details before deletion for logging
            $eventDetails = [
                'event_subject' => $event->subject,
                'event_date' => $event->start_datetime->format('d-m-Y'),
                'event_time' => $event->start_datetime->format('H:i') . ' - ' . $event->end_datetime->format('H:i'),
                'had_time_entry' => !is_null($event->time_entry_id),
                'was_synced' => !is_null($event->ms_event_id) && !str_starts_with($event->ms_event_id, 'local_'),
                'location' => $event->location
            ];
            
            // Log the activity before deleting (with null event_id since it will be deleted)
            CalendarActivity::create([
                'user_id' => Auth::id(),
                'calendar_event_id' => null, // Set to null since event will be deleted
                'action' => 'deleted',
                'description' => 'Deleted event "' . $event->subject . '" scheduled for ' . $event->start_datetime->format('d-m-Y H:i'),
                'changes' => null,
                'metadata' => $eventDetails,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Delete the calendar event
            $event->delete();
            
            DB::commit();
            
            Log::info('Calendar event deleted successfully', ['event_id' => $event->id]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event deleted successfully.'
                ]);
            }
            
            return redirect()->route('calendar.index')
                ->with('success', 'Event deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete calendar event', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to delete event: ' . $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Failed to delete event: ' . $e->getMessage());
        }
    }
    
    /**
     * Determine hourly rate based on hierarchy
     * Subtask > Task > Milestone > Project > Default
     */
    private function determineHourlyRate($project, $milestoneId, $taskId, $subtaskId)
    {
        // Check subtask level
        if ($subtaskId) {
            $subtask = ProjectSubtask::find($subtaskId);
            if ($subtask && $subtask->hourly_rate_override) {
                return $subtask->hourly_rate_override;
            }
        }

        // Check task level
        if ($taskId) {
            $task = ProjectTask::find($taskId);
            if ($task && $task->hourly_rate_override) {
                return $task->hourly_rate_override;
            }
        }

        // Check milestone level
        if ($milestoneId) {
            $milestone = ProjectMilestone::find($milestoneId);
            if ($milestone && $milestone->hourly_rate_override) {
                return $milestone->hourly_rate_override;
            }
        }

        // Use project default
        return $project->default_hourly_rate ?? 0;
    }
    
    /**
     * Get project work items for dropdowns
     */
    private function getProjectWorkItems(Project $project)
    {
        $items = [];
        
        foreach ($project->milestones->sortBy('sort_order') as $milestone) {
            // Add milestone as header
            $items[] = [
                'id' => 'milestone_' . $milestone->id,
                'label' => $milestone->name,
                'type' => 'milestone',
                'is_header' => true,
                'selectable' => false,
                'indent' => 0
            ];
            
            foreach ($milestone->tasks->sortBy('sort_order') as $task) {
                // Add task
                $items[] = [
                    'id' => 'task_' . $task->id,
                    'label' => '  📋 ' . $task->name,
                    'type' => 'task',
                    'is_header' => false,
                    'selectable' => true,
                    'indent' => 1
                ];
                
                foreach ($task->subtasks->sortBy('sort_order') as $subtask) {
                    // Add subtask
                    $items[] = [
                        'id' => 'subtask_' . $subtask->id,
                        'label' => '    ⚪ ' . $subtask->name,
                        'type' => 'subtask',
                        'is_header' => false,
                        'selectable' => true,
                        'indent' => 2
                    ];
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Get AI predictions for calendar event conversion
     */
    public function getAIPredictions(Request $request, CalendarEvent $calendarEvent)
    {
        // Check ownership
        if ($calendarEvent->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            $predictionService = app(AICalendarPredictionService::class);
            $predictions = $predictionService->predictProjectAndWorkItems($calendarEvent);
            
            // Get the actual objects if IDs are predicted
            $result = [
                'predictions' => $predictions,
                'project' => null,
                'milestone' => null,
                'task' => null,
                'subtask' => null,
                'work_items' => []
            ];
            
            if ($predictions['project_id']) {
                $project = Project::with(['milestones.tasks.subtasks'])->find($predictions['project_id']);
                if ($project) {
                    $result['project'] = [
                        'id' => $project->id,
                        'name' => $project->name
                    ];
                    
                    // Get work items for the predicted project
                    $workItems = $this->getProjectWorkItems($project);
                    $result['work_items'] = $workItems;
                    
                    // Get predicted milestone
                    if ($predictions['milestone_id']) {
                        $milestone = ProjectMilestone::find($predictions['milestone_id']);
                        if ($milestone && $milestone->project_id == $project->id) {
                            $result['milestone'] = [
                                'id' => $milestone->id,
                                'name' => $milestone->name
                            ];
                        }
                    }
                    
                    // Get predicted task
                    if ($predictions['task_id']) {
                        $task = ProjectTask::find($predictions['task_id']);
                        if ($task && $predictions['milestone_id'] && $task->project_milestone_id == $predictions['milestone_id']) {
                            $result['task'] = [
                                'id' => $task->id,
                                'name' => $task->name
                            ];
                        }
                    }
                    
                    // Get predicted subtask
                    if ($predictions['subtask_id']) {
                        $subtask = ProjectSubtask::find($predictions['subtask_id']);
                        if ($subtask && $predictions['task_id'] && $subtask->project_task_id == $predictions['task_id']) {
                            $result['subtask'] = [
                                'id' => $subtask->id,
                                'name' => $subtask->name
                            ];
                        }
                    }
                }
            }
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Failed to get AI predictions for calendar event', [
                'event_id' => $calendarEvent->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'predictions' => [
                    'project_id' => null,
                    'milestone_id' => null,
                    'task_id' => null,
                    'subtask_id' => null,
                    'confidence' => 0,
                    'description' => '',
                    'source' => 'none'
                ],
                'error' => 'Failed to generate predictions'
            ]);
        }
    }
}