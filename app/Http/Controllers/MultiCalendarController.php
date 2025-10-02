<?php

namespace App\Http\Controllers;

use App\Services\CalendarManager;
use App\Services\Calendar\AppleCalendarService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MultiCalendarController extends Controller
{
    private CalendarManager $calendarManager;

    public function __construct(CalendarManager $calendarManager)
    {
        $this->calendarManager = $calendarManager;
    }

    /**
     * Show multi-provider calendar settings dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user || !$user->id) {
            return redirect()->route('login')->with('error', 'Please log in to access calendar providers.');
        }

        $providers = $this->calendarManager->getAuthenticationStatus($user->id);

        return view('calendar.providers.index', [
            'providers' => $providers,
            'availableProviders' => $this->calendarManager->getAvailableProviders()
        ]);
    }

    /**
     * Connect to Microsoft Calendar
     */
    public function connectMicrosoft()
    {
        if (!Auth::check() || !Auth::id()) {
            return redirect()->route('login')->with('error', 'Please log in to connect calendar providers.');
        }

        $authUrl = $this->calendarManager->getAuthorizationUrl('microsoft', Auth::id());
        return redirect()->away($authUrl);
    }

    /**
     * Handle Microsoft OAuth callback
     */
    public function callbackMicrosoft(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('calendar.providers.index')
                ->with('error', 'Microsoft authorization was cancelled or failed.');
        }

        $success = $this->calendarManager->handleCallback('microsoft', $code, Auth::id());

        if ($success) {
            return redirect()->route('calendar.providers.index')
                ->with('success', 'Microsoft Calendar connected successfully!');
        }

        return redirect()->route('calendar.providers.index')
            ->with('error', 'Failed to connect Microsoft Calendar. Please try again.');
    }

    /**
     * Disconnect Microsoft Calendar
     */
    public function disconnectMicrosoft()
    {
        if (!Auth::check() || !Auth::id()) {
            return redirect()->route('login')->with('error', 'Please log in to manage calendar providers.');
        }

        $success = $this->calendarManager->revokeProvider(Auth::id(), 'microsoft');

        if ($success) {
            return redirect()->route('calendar.providers.index')
                ->with('success', 'Microsoft Calendar disconnected successfully.');
        }

        return redirect()->route('calendar.providers.index')
            ->with('error', 'Failed to disconnect Microsoft Calendar.');
    }

    /**
     * Connect to Google Calendar
     */
    public function connectGoogle()
    {
        if (!Auth::check() || !Auth::id()) {
            return redirect()->route('login')->with('error', 'Please log in to connect calendar providers.');
        }

        $authUrl = $this->calendarManager->getAuthorizationUrl('google', Auth::id());
        return redirect()->away($authUrl);
    }

    /**
     * Handle Google OAuth callback
     */
    public function callbackGoogle(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (!$code) {
            return redirect()->route('calendar.providers.index')
                ->with('error', 'Google authorization was cancelled or failed.');
        }

        // Decode state to get user ID
        $stateData = json_decode(base64_decode($state), true);
        $userId = $stateData['user_id'] ?? Auth::id();

        $success = $this->calendarManager->handleCallback('google', $code, $userId);

        if ($success) {
            return redirect()->route('calendar.providers.index')
                ->with('success', 'Google Calendar connected successfully!');
        }

        return redirect()->route('calendar.providers.index')
            ->with('error', 'Failed to connect Google Calendar. Please try again.');
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnectGoogle()
    {
        if (!Auth::check() || !Auth::id()) {
            return redirect()->route('login')->with('error', 'Please log in to manage calendar providers.');
        }

        $success = $this->calendarManager->revokeProvider(Auth::id(), 'google');

        if ($success) {
            return redirect()->route('calendar.providers.index')
                ->with('success', 'Google Calendar disconnected successfully.');
        }

        return redirect()->route('calendar.providers.index')
            ->with('error', 'Failed to disconnect Google Calendar.');
    }

    /**
     * Show Google Calendar setup form
     */
    public function setupGoogle()
    {
        if (!Auth::check() || !Auth::id()) {
            return redirect()->route('login')->with('error', 'Please log in to setup Google Calendar.');
        }

        return view('calendar.providers.google-setup');
    }

    /**
     * Store Google Calendar credentials
     */
    public function storeGoogle(Request $request)
    {
        if (!Auth::check() || !Auth::id()) {
            return redirect()->route('login')->with('error', 'Please log in to setup Google Calendar.');
        }

        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string'
        ]);

        try {
            Log::info('Attempting Google OAuth credentials setup', [
                'client_id' => $request->client_id,
                'user_id' => Auth::id()
            ]);

            // Store Google OAuth credentials in settings
            Setting::set('google_calendar_client_id', $request->client_id);
            Setting::set('google_calendar_client_secret', $request->client_secret);
            Setting::set('google_calendar_setup_user_id', Auth::id());
            Setting::set('google_calendar_setup_at', now());

            Log::info('Google OAuth credentials stored successfully', [
                'user_id' => Auth::id()
            ]);

            // Test the connection by generating an auth URL
            /** @var \App\Services\Calendar\GoogleCalendarService $googleService */
            $googleService = $this->calendarManager->provider('google');

            try {
                $authUrl = $googleService->getAuthorizationUrl(Auth::id());

                // If we got here, the credentials are valid enough to generate auth URL
                return redirect()->route('calendar.providers.index')
                    ->with('success', 'Google Calendar credentials saved successfully! You can now connect your Google account.');

            } catch (\Exception $e) {
                Log::warning('Google credentials test failed', [
                    'error' => $e->getMessage(),
                    'user_id' => Auth::id()
                ]);

                // Still save the credentials but warn user
                return redirect()->route('calendar.providers.index')
                    ->with('success', 'Google Calendar credentials saved, but please verify they are correct before connecting.');
            }

        } catch (\Exception $e) {
            Log::error('Google OAuth setup error', [
                'client_id' => $request->client_id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('calendar.providers.google.setup')
                ->with('error', 'Setup error: ' . $e->getMessage())
                ->withInput(['client_id']);
        }
    }

    /**
     * Show Apple iCloud setup form
     */
    public function setupApple()
    {
        return view('calendar.providers.apple-setup');
    }

    /**
     * Store Apple iCloud credentials
     */
    public function storeApple(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required|min:6'
        ]);

        try {
            /** @var AppleCalendarService $appleService */
            $appleService = $this->calendarManager->provider('apple');

            Log::info('Attempting Apple iCloud connection', [
                'username' => $request->username,
                'user_id' => Auth::id()
            ]);

            $success = $appleService->storeCredentials(
                Auth::id(),
                $request->username,
                $request->password
            );

            if ($success) {
                return redirect()->route('calendar.providers.index')
                    ->with('success', 'Apple iCloud Calendar connected successfully!');
            }

            // Check logs for more specific error details
            $errorMessage = 'Connection test failed. Please verify:
                • You are using your full Apple ID email address
                • You are using an app-specific password (not your regular Apple ID password)
                • Two-factor authentication is enabled on your Apple ID
                • The app-specific password was generated recently';

            return redirect()->route('calendar.providers.apple.setup')
                ->with('error', $errorMessage)
                ->withInput(['username']);

        } catch (\Exception $e) {
            Log::error('Apple iCloud setup error', [
                'username' => $request->username,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('calendar.providers.apple.setup')
                ->with('error', 'Connection error: ' . $e->getMessage())
                ->withInput(['username']);
        }
    }

    /**
     * Disconnect Apple Calendar
     */
    public function disconnectApple()
    {
        if (!Auth::check() || !Auth::id()) {
            return redirect()->route('login')->with('error', 'Please log in to manage calendar providers.');
        }

        $success = $this->calendarManager->revokeProvider(Auth::id(), 'apple');

        if ($success) {
            return redirect()->route('calendar.providers.index')
                ->with('success', 'Apple iCloud Calendar disconnected successfully.');
        }

        return redirect()->route('calendar.providers.index')
            ->with('error', 'Failed to disconnect Apple iCloud Calendar.');
    }

    /**
     * Sync specific provider
     */
    public function syncProvider(Request $request, string $provider)
    {
        if (!Auth::check() || !Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            $calendarProvider = $this->calendarManager->provider($provider);
            $result = $calendarProvider->syncEvents(Auth::id());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'events_synced' => $result['events_synced']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);

        } catch (\Exception $e) {
            Log::error("Sync error for provider {$provider}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all connected providers
     */
    public function syncAll(Request $request)
    {
        if (!Auth::check() || !Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            $results = $this->calendarManager->syncAllProviders(Auth::id());

            $totalSynced = 0;
            $messages = [];
            $hasErrors = false;

            foreach ($results as $provider => $result) {
                if ($result['success']) {
                    $totalSynced += $result['events_synced'];
                    $messages[] = ucfirst($provider) . ': ' . $result['events_synced'] . ' events';
                } else {
                    $hasErrors = true;
                    $messages[] = ucfirst($provider) . ': ' . $result['message'];
                }
            }

            $message = 'Sync completed. ' . implode(', ', $messages);

            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => !$hasErrors || $totalSynced > 0,
                    'message' => $message,
                    'total_events_synced' => $totalSynced,
                    'provider_results' => $results
                ]);
            }

            // For regular form submission, redirect with flash message
            if (!$hasErrors || $totalSynced > 0) {
                return redirect()->route('calendar.index')->with('success', $message);
            } else {
                return redirect()->route('calendar.index')->with('error', $message);
            }

        } catch (\Exception $e) {
            Log::error("Sync all providers error: " . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('calendar.index')->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Show main multi-calendar view
     */
    public function calendarIndex()
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user || !$user->id) {
            Log::warning('Calendar access attempt without authentication', [
                'user' => $user,
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id()
            ]);
            return redirect()->route('login')->with('error', 'Please log in to access the calendar.');
        }

        $providers = $this->calendarManager->getAuthenticationStatus($user->id);

        // Get last sync information first
        $lastSync = \App\Models\CalendarSyncLog::where('user_id', $user->id)
            ->where('status', 'success')
            ->latest('sync_completed_at')
            ->first();

        // Get calendar sync settings
        $calendarSyncSettings = [
            'auto_sync' => Setting::get('calendar_auto_sync', true),
            'page_load_interval' => Setting::get('calendar_auto_sync_interval', 15), // minutes
            'background_interval' => Setting::get('calendar_background_sync_interval', 15), // minutes
            'sync_range' => Setting::get('calendar_sync_range', 90), // days
        ];

        // Check if we should auto-sync on page load
        $shouldAutoSync = false;
        if ($calendarSyncSettings['auto_sync']) {
            $lastSyncMinutes = null;
            if ($lastSync && $lastSync->sync_completed_at) {
                $lastSyncMinutes = $lastSync->sync_completed_at->diffInMinutes(now());
            }

            if (!$lastSync || $lastSyncMinutes >= $calendarSyncSettings['page_load_interval']) {
                $shouldAutoSync = true;
            }
        }

        // Get all events from all connected providers
        $allEvents = collect();
        $connectedProviders = [];

        foreach ($providers as $providerName => $status) {
            if ($status['authenticated']) {
                try {
                    $calendarProvider = $this->calendarManager->provider($providerName);

                    // Use wider date range to include historical events
                    $startDate = now()->subYears(2);
                    $endDate = now()->addYears(1);

                    // Get events from database for this provider
                    $events = \App\Models\CalendarEvent::where('user_id', $user->id)
                        ->where('provider_type', $providerName)
                        ->whereBetween('start_datetime', [$startDate, $endDate])
                        ->orderBy('start_datetime')
                        ->get();

                    $allEvents = $allEvents->merge($events);
                    $connectedProviders[] = $providerName;
                } catch (\Exception $e) {
                    Log::warning("Failed to get events from {$providerName}: " . $e->getMessage());
                }
            }
        }

        // Sort all events by start time
        $events = $allEvents->sortBy('start_datetime');

        // Calculate next sync time based on settings
        $nextSyncMinutes = null;
        if ($lastSync && $lastSync->sync_completed_at) {
            $minutesSinceLastSync = $lastSync->sync_completed_at->diffInMinutes(now());
            $nextSyncMinutes = max(0, $calendarSyncSettings['page_load_interval'] - $minutesSinceLastSync);
            // Round to whole minutes for display
            $nextSyncMinutes = (int) round($nextSyncMinutes);
        }

        return view('calendar.multi-index', [
            'events' => $events,
            'providers' => $providers,
            'connectedProviders' => $connectedProviders,
            'lastSync' => $lastSync,
            'nextSyncMinutes' => $nextSyncMinutes,
            'calendarSyncSettings' => $calendarSyncSettings,
            'shouldAutoSync' => $shouldAutoSync
        ]);
    }

    /**
     * Create a new event in the specified provider calendar
     */
    public function createEvent(Request $request)
    {
        if (!Auth::check() || !Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:500',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'provider' => 'required|string|in:microsoft,google,apple',
            'attendees' => 'nullable|array',
            'attendees.*.email' => 'required|email',
            'attendees.*.name' => 'nullable|string|max:255'
        ]);

        try {
            $provider = $request->provider;
            $calendarProvider = $this->calendarManager->provider($provider);

            // Check if user is authenticated with this provider
            if (!$calendarProvider->isAuthenticated(Auth::id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated with ' . ucfirst($provider) . ' calendar'
                ], 400);
            }

            // Create event data
            $eventData = [
                'subject' => $request->title,
                'body' => $request->description ?? '',
                'start_datetime' => \Carbon\Carbon::parse($request->start),
                'end_datetime' => \Carbon\Carbon::parse($request->end),
                'timezone' => \App\Models\Setting::get('app_timezone', 'Europe/Amsterdam'),
                'is_all_day' => false
            ];

            // Create event in provider calendar
            $externalEventId = $calendarProvider->createEvent(Auth::id(), $eventData);

            if (!$externalEventId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create event in ' . ucfirst($provider) . ' calendar'
                ], 500);
            }

            // Store event in local database
            $calendarEvent = \App\Models\CalendarEvent::create([
                'user_id' => Auth::id(),
                'provider_type' => $provider,
                'external_event_id' => $externalEventId,
                'subject' => $eventData['subject'],
                'body' => $eventData['body'],
                'start_datetime' => $eventData['start_datetime'],
                'end_datetime' => $eventData['end_datetime'],
                'timezone' => $eventData['timezone'],
                'is_all_day' => $eventData['is_all_day'],
                'provider_raw_data' => ['created_via' => 'progress_calendar']
            ]);

            Log::info("Event created successfully", [
                'user_id' => Auth::id(),
                'provider' => $provider,
                'event_id' => $calendarEvent->id,
                'external_event_id' => $externalEventId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully in ' . ucfirst($provider) . ' calendar',
                'event_id' => $calendarEvent->id,
                'external_event_id' => $externalEventId
            ]);

        } catch (\Exception $e) {
            Log::error("Event creation error: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'provider' => $request->provider ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing event in the provider calendar
     */
    public function updateEvent(Request $request, $eventId)
    {
        if (!Auth::check() || !Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'required|date|after:start'
        ]);

        try {
            // Find the event
            $calendarEvent = \App\Models\CalendarEvent::where('user_id', Auth::id())
                ->where('id', $eventId)
                ->first();

            if (!$calendarEvent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            $provider = $calendarEvent->provider_type;
            $calendarProvider = $this->calendarManager->provider($provider);

            // Check if user is authenticated with this provider
            if (!$calendarProvider->isAuthenticated(Auth::id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated with ' . ucfirst($provider) . ' calendar'
                ], 400);
            }

            // Update event data
            $eventData = [
                'subject' => $request->title,
                'body' => $request->description ?? '',
                'start_datetime' => \Carbon\Carbon::parse($request->start),
                'end_datetime' => \Carbon\Carbon::parse($request->end),
                'timezone' => \App\Models\Setting::get('app_timezone', 'Europe/Amsterdam'),
                'is_all_day' => false
            ];

            // Update event in provider calendar
            $success = $calendarProvider->updateEvent(Auth::id(), $calendarEvent->external_event_id, $eventData);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update event in ' . ucfirst($provider) . ' calendar'
                ], 500);
            }

            // Update event in local database
            $calendarEvent->update([
                'subject' => $eventData['subject'],
                'body' => $eventData['body'],
                'start_datetime' => $eventData['start_datetime'],
                'end_datetime' => $eventData['end_datetime'],
                'timezone' => $eventData['timezone']
            ]);

            Log::info("Event updated successfully", [
                'user_id' => Auth::id(),
                'provider' => $provider,
                'event_id' => $calendarEvent->id,
                'external_event_id' => $calendarEvent->external_event_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully in ' . ucfirst($provider) . ' calendar'
            ]);

        } catch (\Exception $e) {
            Log::error("Event update error: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'event_id' => $eventId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an event from the provider calendar
     */
    public function deleteEvent(Request $request, $eventId)
    {
        if (!Auth::check() || !Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            // Find the event
            $calendarEvent = \App\Models\CalendarEvent::where('user_id', Auth::id())
                ->where('id', $eventId)
                ->first();

            if (!$calendarEvent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            $provider = $calendarEvent->provider_type;
            $calendarProvider = $this->calendarManager->provider($provider);

            // Check if user is authenticated with this provider
            if (!$calendarProvider->isAuthenticated(Auth::id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated with ' . ucfirst($provider) . ' calendar'
                ], 400);
            }

            // Delete event from provider calendar
            $success = $calendarProvider->deleteEvent(Auth::id(), $calendarEvent->external_event_id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete event from ' . ucfirst($provider) . ' calendar'
                ], 500);
            }

            // Delete event from local database
            $calendarEvent->delete();

            Log::info("Event deleted successfully", [
                'user_id' => Auth::id(),
                'provider' => $provider,
                'event_id' => $eventId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully from ' . ucfirst($provider) . ' calendar'
            ]);

        } catch (\Exception $e) {
            Log::error("Event delete error: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'event_id' => $eventId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed event information
     */
    public function getEventDetails($eventId)
    {
        if (!Auth::check() || !Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            // Find the event - use ID directly from calendar data
            $calendarEvent = \App\Models\CalendarEvent::where('user_id', Auth::id())
                ->where('id', $eventId)
                ->first();

            if (!$calendarEvent) {
                // If not found by ID, try to find by external_event_id
                $calendarEvent = \App\Models\CalendarEvent::where('user_id', Auth::id())
                    ->where('external_event_id', $eventId)
                    ->first();
            }

            if (!$calendarEvent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            // Parse attendees JSON if it exists
            $attendees = [];
            if ($calendarEvent->attendees) {
                $attendeesData = is_string($calendarEvent->attendees)
                    ? json_decode($calendarEvent->attendees, true)
                    : $calendarEvent->attendees;

                if (is_array($attendeesData)) {
                    $attendees = $attendeesData;
                }
            }

            // Try to get description from multiple sources
            $description = $calendarEvent->body;

            // If no description in main field, try to extract from provider raw data
            if (empty($description) && $calendarEvent->provider_raw_data) {
                $rawData = is_string($calendarEvent->provider_raw_data)
                    ? json_decode($calendarEvent->provider_raw_data, true)
                    : $calendarEvent->provider_raw_data;

                if (is_array($rawData)) {
                    // Microsoft Calendar
                    if ($calendarEvent->provider_type === 'microsoft' && isset($rawData['body']['content'])) {
                        $description = $rawData['body']['content'];
                        // Strip HTML if present
                        if (!empty($description)) {
                            $description = strip_tags($description);
                        }
                    }
                    // Google Calendar
                    elseif ($calendarEvent->provider_type === 'google' && isset($rawData['description'])) {
                        $description = $rawData['description'];
                    }
                    // Apple Calendar (description might be in different field)
                    elseif ($calendarEvent->provider_type === 'apple') {
                        // Apple iCal events might have description in 'description' or other fields
                        if (isset($rawData['description'])) {
                            $description = $rawData['description'];
                        }
                    }
                }
            }

            $eventData = [
                'id' => $calendarEvent->id,
                'external_event_id' => $calendarEvent->external_event_id,
                'title' => $calendarEvent->subject,
                'body' => $description, // Use the enhanced description
                'location' => $calendarEvent->location,
                'start_datetime' => $calendarEvent->start_datetime,
                'end_datetime' => $calendarEvent->end_datetime,
                'timezone' => $calendarEvent->timezone,
                'is_all_day' => $calendarEvent->is_all_day,
                'attendees' => $attendees,
                'organizer_name' => $calendarEvent->organizer_name,
                'organizer_email' => $calendarEvent->organizer_email,
                'provider_type' => $calendarEvent->provider_type,
                'categories' => $calendarEvent->categories,
                'created_at' => $calendarEvent->created_at,
                'updated_at' => $calendarEvent->updated_at,
            ];

            Log::info("Event details retrieved", [
                'user_id' => Auth::id(),
                'event_id' => $eventId,
                'provider' => $calendarEvent->provider_type
            ]);

            return response()->json([
                'success' => true,
                'event' => $eventData
            ]);

        } catch (\Exception $e) {
            Log::error("Event details retrieval error: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'event_id' => $eventId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event details: ' . $e->getMessage()
            ], 500);
        }
    }
}