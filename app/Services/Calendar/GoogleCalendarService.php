<?php

namespace App\Services\Calendar;

use App\Contracts\CalendarProviderInterface;
use App\Models\CalendarEvent;
use App\Models\CalendarSyncLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GoogleCalendarService implements CalendarProviderInterface
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private array $scopes = [
        'https://www.googleapis.com/auth/calendar.readonly',
        'https://www.googleapis.com/auth/calendar.events'
    ];

    public function __construct()
    {
        // Google Calendar API credentials uit settings of config
        $this->clientId = config('services.google.client_id') ?? Setting::get('google_calendar_client_id', '');
        $this->clientSecret = config('services.google.client_secret') ?? Setting::get('google_calendar_client_secret', '');
        $this->redirectUri = config('services.google.redirect_uri') ?? route('calendar.providers.google.callback');
    }

    /**
     * Get provider type identifier
     */
    public function getProviderType(): string
    {
        return 'google';
    }

    /**
     * Get authorization URL for OAuth flow
     */
    public function getAuthorizationUrl(int $userId): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => base64_encode(json_encode(['user_id' => $userId, 'provider' => 'google']))
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Handle OAuth callback and store access token
     */
    public function handleCallback(string $code, int $userId): bool
    {
        try {
            // Exchange code for tokens
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
            ]);

            if (!$response->successful()) {
                Log::error('Google OAuth token exchange failed', ['response' => $response->body()]);
                return false;
            }

            $tokenData = $response->json();

            // Store tokens in settings table (per user)
            $this->storeTokens($userId, $tokenData);

            return true;
        } catch (\Exception $e) {
            Log::error("Google Calendar OAuth callback error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store Google tokens voor user
     */
    private function storeTokens(int $userId, array $tokenData): void
    {
        $expiresAt = Carbon::now()->addSeconds($tokenData['expires_in'] ?? 3600);

        Setting::set("google_calendar_access_token_{$userId}", $tokenData['access_token']);
        Setting::set("google_calendar_refresh_token_{$userId}", $tokenData['refresh_token'] ?? '');
        Setting::set("google_calendar_expires_at_{$userId}", $expiresAt->toDateTimeString());
        Setting::set("google_calendar_token_type_{$userId}", $tokenData['token_type'] ?? 'Bearer');
    }

    /**
     * Get stored access token voor user
     */
    private function getAccessToken(int $userId): ?string
    {
        $token = Setting::get("google_calendar_access_token_{$userId}");
        $expiresAt = Setting::get("google_calendar_expires_at_{$userId}");

        if (!$token || !$expiresAt) {
            return null;
        }

        // Check if token is expired
        if (Carbon::parse($expiresAt)->isPast()) {
            // Try to refresh token
            if ($this->refreshToken($userId)) {
                return Setting::get("google_calendar_access_token_{$userId}");
            }
            return null;
        }

        return $token;
    }

    /**
     * Refresh access token
     */
    private function refreshToken(int $userId): bool
    {
        $refreshToken = Setting::get("google_calendar_refresh_token_{$userId}");
        if (!$refreshToken) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $this->storeTokens($userId, $tokenData);
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Google Calendar token refresh error: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Check if user is authenticated with this provider
     */
    public function isAuthenticated(int $userId): bool
    {
        return $this->getAccessToken($userId) !== null;
    }

    /**
     * Sync calendar events from provider
     */
    public function syncEvents(int $userId, array $options = []): array
    {
        try {
            $accessToken = $this->getAccessToken($userId);
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Not authenticated with Google Calendar',
                    'events_synced' => 0
                ];
            }

            // Use sync range setting if no specific dates provided
            $syncRange = (int) Setting::get('calendar_sync_range', 90);
            $startDate = $options['start_date'] ?? Carbon::now()->subDays(7); // Keep 7 days history
            $endDate = $options['end_date'] ?? Carbon::now()->addDays($syncRange);

            if (is_string($startDate)) $startDate = Carbon::parse($startDate);
            if (is_string($endDate)) $endDate = Carbon::parse($endDate);

            // Get events from Google Calendar API
            $events = $this->getEventsFromGoogle($accessToken, $startDate, $endDate);

            $syncedCount = 0;
            foreach ($events as $event) {
                $standardizedEvent = $this->transformEvent($event);

                // Store event in database
                CalendarEvent::updateOrCreate([
                    'user_id' => $userId,
                    'provider_type' => 'google',
                    'external_event_id' => $event['id']
                ], array_merge($standardizedEvent, [
                    'provider_raw_data' => $event
                ]));

                $syncedCount++;
            }

            // Log sync
            CalendarSyncLog::create([
                'user_id' => $userId,
                'provider_type' => 'google',
                'sync_type' => 'full',
                'events_synced' => $syncedCount,
                'status' => 'success',
                'sync_started_at' => Carbon::now(),
                'sync_completed_at' => Carbon::now()
            ]);

            return [
                'success' => true,
                'message' => "Synced {$syncedCount} Google Calendar events",
                'events_synced' => $syncedCount
            ];

        } catch (\Exception $e) {
            Log::error("Google Calendar sync error: " . $e->getMessage());

            CalendarSyncLog::create([
                'user_id' => $userId,
                'provider_type' => 'google',
                'sync_type' => 'full',
                'events_synced' => 0,
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'sync_started_at' => Carbon::now(),
                'sync_completed_at' => Carbon::now()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'events_synced' => 0
            ];
        }
    }

    /**
     * Get events from Google Calendar API
     */
    private function getEventsFromGoogle(string $accessToken, \DateTime $startDate, \DateTime $endDate): array
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/calendar/v3/calendars/primary/events', [
                'timeMin' => $startDate->format('c'),
                'timeMax' => $endDate->format('c'),
                'singleEvents' => 'true',
                'orderBy' => 'startTime',
                'maxResults' => 2500
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch Google Calendar events: ' . $response->body());
        }

        $data = $response->json();
        return $data['items'] ?? [];
    }

    /**
     * Get events from provider for date range
     */
    public function getEvents(int $userId, \DateTime $startDate, \DateTime $endDate): array
    {
        return CalendarEvent::where('user_id', $userId)
            ->where('provider_type', 'google')
            ->whereBetween('start_datetime', [$startDate, $endDate])
            ->orderBy('start_datetime')
            ->get()
            ->toArray();
    }

    /**
     * Create event in provider calendar
     */
    public function createEvent(int $userId, array $eventData): ?string
    {
        try {
            $accessToken = $this->getAccessToken($userId);
            if (!$accessToken) {
                return null;
            }

            $googleEventData = $this->transformToGoogleFormat($eventData);

            $response = Http::withToken($accessToken)
                ->post('https://www.googleapis.com/calendar/v3/calendars/primary/events', $googleEventData);

            if ($response->successful()) {
                $event = $response->json();
                return $event['id'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Google Calendar create event error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update event in provider calendar
     */
    public function updateEvent(int $userId, string $eventId, array $eventData): bool
    {
        try {
            $accessToken = $this->getAccessToken($userId);
            if (!$accessToken) {
                return false;
            }

            $googleEventData = $this->transformToGoogleFormat($eventData);

            $response = Http::withToken($accessToken)
                ->put("https://www.googleapis.com/calendar/v3/calendars/primary/events/{$eventId}", $googleEventData);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Google Calendar update event error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete event from provider calendar
     */
    public function deleteEvent(int $userId, string $eventId): bool
    {
        try {
            $accessToken = $this->getAccessToken($userId);
            if (!$accessToken) {
                return false;
            }

            $response = Http::withToken($accessToken)
                ->delete("https://www.googleapis.com/calendar/v3/calendars/primary/events/{$eventId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Google Calendar delete event error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revoke authentication for this provider
     */
    public function revokeAuthentication(int $userId): bool
    {
        try {
            // Revoke tokens at Google
            $accessToken = $this->getAccessToken($userId);
            if ($accessToken) {
                Http::post('https://oauth2.googleapis.com/revoke', [
                    'token' => $accessToken
                ]);
            }

            // Clear stored tokens - Setting model doesn't have delete method, set to null
            Setting::set("google_calendar_access_token_{$userId}", '');
            Setting::set("google_calendar_refresh_token_{$userId}", '');
            Setting::set("google_calendar_expires_at_{$userId}", '');
            Setting::set("google_calendar_token_type_{$userId}", '');

            // Delete related calendar events
            CalendarEvent::where('user_id', $userId)
                ->where('provider_type', 'google')
                ->delete();

            return true;
        } catch (\Exception $e) {
            Log::error("Google Calendar revoke authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's calendar list from provider
     */
    public function getCalendars(int $userId): array
    {
        try {
            $accessToken = $this->getAccessToken($userId);
            if (!$accessToken) {
                return [];
            }

            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/calendar/v3/users/me/calendarList');

            if ($response->successful()) {
                $data = $response->json();
                return $data['items'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error("Google Calendar get calendars error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Transform provider event data to standardized format
     */
    public function transformEvent(array $providerEvent): array
    {
        // Handle different Google Calendar event time formats
        $start = $this->parseGoogleDateTime($providerEvent['start'] ?? []);
        $end = $this->parseGoogleDateTime($providerEvent['end'] ?? []);

        // Extract and clean description
        $body = null;
        if (isset($providerEvent['description']) && !empty($providerEvent['description'])) {
            $body = trim($providerEvent['description']);
            // If still empty after trim, set to null
            if (empty($body)) {
                $body = null;
            }
        }

        return [
            'external_event_id' => $providerEvent['id'],
            'subject' => $providerEvent['summary'] ?? 'Untitled Event',
            'body' => $body,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'timezone' => $providerEvent['start']['timeZone'] ?? 'Europe/Amsterdam',
            'is_all_day' => isset($providerEvent['start']['date']),
            'location' => $providerEvent['location'] ?? null,
            'attendees' => $this->transformGoogleAttendees($providerEvent['attendees'] ?? []),
            'categories' => null, // Google doesn't have categories like Microsoft
            'organizer_email' => $providerEvent['organizer']['email'] ?? null,
            'organizer_name' => $providerEvent['organizer']['displayName'] ?? null,
        ];
    }

    /**
     * Parse Google Calendar datetime format
     */
    private function parseGoogleDateTime(array $dateTime): Carbon
    {
        if (isset($dateTime['dateTime'])) {
            // Timed event
            return Carbon::parse($dateTime['dateTime']);
        } elseif (isset($dateTime['date'])) {
            // All-day event
            return Carbon::parse($dateTime['date'])->startOfDay();
        }

        return Carbon::now();
    }

    /**
     * Transform Google attendees format
     */
    private function transformGoogleAttendees(array $attendees): ?array
    {
        if (empty($attendees)) return null;

        return array_map(function($attendee) {
            return [
                'name' => $attendee['displayName'] ?? '',
                'email' => $attendee['email'] ?? '',
                'status' => $attendee['responseStatus'] ?? 'needsAction'
            ];
        }, $attendees);
    }

    /**
     * Transform standard event data to Google format
     */
    private function transformToGoogleFormat(array $eventData): array
    {
        $event = [
            'summary' => $eventData['subject'] ?? 'Untitled Event',
            'description' => $eventData['body'] ?? '',
            'location' => $eventData['location'] ?? '',
        ];

        // Handle start/end times
        if ($eventData['is_all_day'] ?? false) {
            $event['start'] = ['date' => $eventData['start_datetime']->format('Y-m-d')];
            $event['end'] = ['date' => $eventData['end_datetime']->format('Y-m-d')];
        } else {
            $event['start'] = [
                'dateTime' => $eventData['start_datetime']->format('c'),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Amsterdam'
            ];
            $event['end'] = [
                'dateTime' => $eventData['end_datetime']->format('c'),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Amsterdam'
            ];
        }

        // Add attendees if provided
        if (!empty($eventData['attendees'])) {
            $event['attendees'] = array_map(function($attendee) {
                return [
                    'displayName' => $attendee['name'] ?? '',
                    'email' => $attendee['email']
                ];
            }, $eventData['attendees']);
        }

        return $event;
    }

    /**
     * Get provider-specific settings for user
     */
    public function getSettings(int $userId): array
    {
        $accessToken = Setting::get("google_calendar_access_token_{$userId}");
        $expiresAt = Setting::get("google_calendar_expires_at_{$userId}");

        return [
            'connected' => $accessToken !== null,
            'expires_at' => $expiresAt,
            'last_sync' => CalendarSyncLog::where('user_id', $userId)
                ->where('provider_type', 'google')
                ->where('status', 'success')
                ->latest()
                ->value('sync_completed_at')
        ];
    }

    /**
     * Update provider-specific settings for user
     */
    public function updateSettings(int $userId, array $settings): bool
    {
        // Google Calendar heeft momenteel geen specifieke user settings
        return true;
    }
}