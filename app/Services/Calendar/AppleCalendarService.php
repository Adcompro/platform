<?php

namespace App\Services\Calendar;

use App\Contracts\CalendarProviderInterface;
use App\Models\CalendarEvent;
use App\Models\CalendarSyncLog;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AppleCalendarService implements CalendarProviderInterface
{
    /**
     * Get provider type identifier
     */
    public function getProviderType(): string
    {
        return 'apple';
    }

    /**
     * Get authorization URL for OAuth flow
     * Apple iCloud gebruikt app-specific wachtwoorden, geen OAuth
     */
    public function getAuthorizationUrl(int $userId): string
    {
        // Apple iCloud CalDAV heeft geen OAuth flow
        // Gebruiker moet app-specific password aanmaken in Apple ID settings
        return route('calendar.providers.apple.setup');
    }

    /**
     * Handle OAuth callback and store access token
     * Voor Apple gebruiken we app-specific passwords
     */
    public function handleCallback(string $code, int $userId): bool
    {
        // Apple iCloud gebruikt geen OAuth callback
        // Credentials worden direct opgeslagen via setup form
        return false;
    }

    /**
     * Store Apple iCloud credentials (username + app-specific password)
     */
    public function storeCredentials(int $userId, string $username, string $password): bool
    {
        try {
            // Test connection with CalDAV discovery
            $connectionTest = $this->testConnectionDetailed($username, $password);

            if (!$connectionTest['success']) {
                Log::warning("Apple CalDAV connection test failed", [
                    'username' => $username,
                    'user_id' => $userId,
                    'error' => $connectionTest['message']
                ]);
                return false;
            }

            // Store credentials (encrypted)
            Setting::set("apple_calendar_username_{$userId}", $username);
            Setting::set("apple_calendar_password_{$userId}", encrypt($password));
            Setting::set("apple_calendar_connected_at_{$userId}", Carbon::now()->toDateTimeString());

            Log::info("Apple iCloud credentials stored successfully", [
                'username' => $username,
                'user_id' => $userId,
                'principal_url' => $connectionTest['principal_url'] ?? null,
                'calendar_home_url' => $connectionTest['calendar_home_url'] ?? null
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Apple Calendar store credentials error", [
                'username' => $username,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Test CalDAV connection with detailed discovery
     */
    private function testConnectionDetailed(string $username, string $password): array
    {
        try {
            // Step 1: Principal discovery
            $principalUrl = $this->discoverPrincipalUrl($username, $password);
            if (!$principalUrl) {
                return ['success' => false, 'message' => 'Principal discovery failed'];
            }

            // Step 2: Calendar home discovery
            $calendarHomeUrl = $this->discoverCalendarHomeUrl($username, $password, $principalUrl);
            if (!$calendarHomeUrl) {
                return ['success' => false, 'message' => 'Calendar home discovery failed'];
            }

            return [
                'success' => true,
                'message' => 'CalDAV connection successful',
                'principal_url' => $principalUrl,
                'calendar_home_url' => $calendarHomeUrl
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Discover principal URL via PROPFIND
     */
    private function discoverPrincipalUrl(string $username, string $password): ?string
    {
        $propfindBody = '<?xml version="1.0" encoding="utf-8" ?>
            <d:propfind xmlns:d="DAV:">
                <d:prop>
                    <d:current-user-principal/>
                    <d:principal-URL/>
                </d:prop>
            </d:propfind>';

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders([
                'Depth' => '0',
                'Content-Type' => 'application/xml; charset=utf-8'
            ])
            ->withOptions(['verify' => false])
            ->timeout(15)
            ->withBody($propfindBody, 'application/xml')
            ->send('PROPFIND', 'https://caldav.icloud.com/');

        if (!$response->successful()) {
            return null;
        }

        $xml = simplexml_load_string($response->body());
        if (!$xml) {
            return null;
        }

        $xml->registerXPathNamespace('d', 'DAV:');
        $principalNodes = $xml->xpath('//d:current-user-principal/d:href');

        if (!empty($principalNodes)) {
            return (string) $principalNodes[0];
        }

        return null;
    }

    /**
     * Discover calendar home URL via principal
     */
    private function discoverCalendarHomeUrl(string $username, string $password, string $principalUrl): ?string
    {
        $propfindBody = '<?xml version="1.0" encoding="utf-8" ?>
            <d:propfind xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
                <d:prop>
                    <c:calendar-home-set/>
                </d:prop>
            </d:propfind>';

        $response = Http::withBasicAuth($username, $password)
            ->withHeaders([
                'Depth' => '0',
                'Content-Type' => 'application/xml; charset=utf-8'
            ])
            ->withOptions(['verify' => false])
            ->timeout(15)
            ->withBody($propfindBody, 'application/xml')
            ->send('PROPFIND', 'https://caldav.icloud.com' . $principalUrl);

        if (!$response->successful()) {
            return null;
        }

        $xml = simplexml_load_string($response->body());
        if (!$xml) {
            return null;
        }

        $xml->registerXPathNamespace('d', 'DAV:');
        $xml->registerXPathNamespace('c', 'urn:ietf:params:xml:ns:caldav');
        $homeNodes = $xml->xpath('//c:calendar-home-set/d:href');

        if (!empty($homeNodes)) {
            return (string) $homeNodes[0];
        }

        return null;
    }

    /**
     * Check if user is authenticated with this provider
     */
    public function isAuthenticated(int $userId): bool
    {
        $username = Setting::get("apple_calendar_username_{$userId}");
        $password = Setting::get("apple_calendar_password_{$userId}");

        return $username && $password;
    }

    /**
     * Get credentials voor user
     */
    private function getCredentials(int $userId): ?array
    {
        $username = Setting::get("apple_calendar_username_{$userId}");
        $encryptedPassword = Setting::get("apple_calendar_password_{$userId}");

        if (!$username || !$encryptedPassword) {
            return null;
        }

        try {
            $password = decrypt($encryptedPassword);
            return ['username' => $username, 'password' => $password];
        } catch (\Exception $e) {
            Log::error("Apple Calendar decrypt credentials error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync calendar events from provider
     */
    public function syncEvents(int $userId, array $options = []): array
    {
        try {
            $credentials = $this->getCredentials($userId);
            if (!$credentials) {
                return [
                    'success' => false,
                    'message' => 'Apple iCloud credentials not configured',
                    'events_synced' => 0
                ];
            }

            // Use sync range setting if no specific dates provided
            $syncRange = (int) Setting::get('calendar_sync_range', 90);
            $startDate = $options['start_date'] ?? Carbon::now()->subDays(7); // Keep 7 days history
            $endDate = $options['end_date'] ?? Carbon::now()->addDays($syncRange);

            if (is_string($startDate)) $startDate = Carbon::parse($startDate);
            if (is_string($endDate)) $endDate = Carbon::parse($endDate);

            // Get calendars and events via CalDAV
            $calendars = $this->getCalendarsFromCalDAV($credentials);
            $allEvents = [];

            foreach ($calendars as $calendar) {
                $events = $this->getEventsFromCalDAV($credentials, $calendar['href'], $startDate, $endDate);
                $allEvents = array_merge($allEvents, $events);
            }

            $syncedCount = 0;
            foreach ($allEvents as $event) {
                $standardizedEvent = $this->transformEvent($event);

                // Store event in database
                CalendarEvent::updateOrCreate([
                    'user_id' => $userId,
                    'provider_type' => 'apple',
                    'external_event_id' => $event['uid']
                ], array_merge($standardizedEvent, [
                    'provider_raw_data' => $event
                ]));

                $syncedCount++;
            }

            // Log sync
            CalendarSyncLog::create([
                'user_id' => $userId,
                'provider_type' => 'apple',
                'sync_type' => 'full',
                'events_synced' => $syncedCount,
                'status' => 'success',
                'sync_started_at' => Carbon::now(),
                'sync_completed_at' => Carbon::now()
            ]);

            return [
                'success' => true,
                'message' => "Synced {$syncedCount} Apple iCloud events",
                'events_synced' => $syncedCount
            ];

        } catch (\Exception $e) {
            Log::error("Apple Calendar sync error: " . $e->getMessage());

            CalendarSyncLog::create([
                'user_id' => $userId,
                'provider_type' => 'apple',
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
     * Get calendars from CalDAV server
     */
    private function getCalendarsFromCalDAV(array $credentials): array
    {
        try {
            // Discover principal and calendar home URLs
            $principalUrl = $this->discoverPrincipalUrl($credentials['username'], $credentials['password']);
            if (!$principalUrl) {
                Log::warning("Could not find principal URL for Apple CalDAV");
                return [];
            }

            $calendarHomeUrl = $this->discoverCalendarHomeUrl($credentials['username'], $credentials['password'], $principalUrl);
            if (!$calendarHomeUrl) {
                Log::warning("Could not find calendar home URL for Apple CalDAV");
                return [];
            }

            // Get list of calendars
            $calendars = $this->getCalendarList($credentials, $calendarHomeUrl);

            Log::info("Apple CalDAV calendar discovery completed", [
                'principal_url' => $principalUrl,
                'calendar_home_url' => $calendarHomeUrl,
                'calendars_found' => count($calendars)
            ]);

            return $calendars;

        } catch (\Exception $e) {
            Log::error("Apple CalDAV calendar discovery error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get calendar list from calendar home URL
     */
    private function getCalendarList(array $credentials, string $calendarHomeUrl): array
    {
        $propfindBody = '<?xml version="1.0" encoding="utf-8" ?>
            <d:propfind xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
                <d:prop>
                    <d:resourcetype/>
                    <d:displayname/>
                    <c:supported-calendar-component-set/>
                </d:prop>
            </d:propfind>';

        $response = Http::withBasicAuth($credentials['username'], $credentials['password'])
            ->withHeaders([
                'Depth' => '1',
                'Content-Type' => 'application/xml; charset=utf-8'
            ])
            ->withOptions(['verify' => false])
            ->timeout(15)
            ->withBody($propfindBody, 'application/xml')
            ->send('PROPFIND', $calendarHomeUrl);

        if (!$response->successful()) {
            return [];
        }

        $xml = simplexml_load_string($response->body());
        if (!$xml) {
            return [];
        }

        $calendars = [];
        foreach ($xml->response as $response) {
            // Register namespaces for each response element
            $response->registerXPathNamespace('d', 'DAV:');
            $response->registerXPathNamespace('c', 'urn:ietf:params:xml:ns:caldav');

            $href = (string) $response->href;
            $resourceType = $response->xpath('.//c:calendar');

            // Get displayname with safe array access
            $displayNameNodes = $response->xpath('.//d:displayname');
            $displayName = !empty($displayNameNodes) ? (string) $displayNameNodes[0] : '';

            $components = $response->xpath('.//c:comp[@name="VEVENT"]');

            // Only include calendars that support VEVENT (calendar events)
            if (!empty($resourceType) && !empty($components)) {
                $calendars[] = [
                    'href' => $href,
                    'name' => $displayName ?: basename($href, '/'),
                    'supports_events' => true
                ];
            }
        }

        return $calendars;
    }

    /**
     * Get events from CalDAV calendar
     */
    private function getEventsFromCalDAV(array $credentials, string $calendarHref, Carbon $startDate, Carbon $endDate): array
    {
        // Convert to full URL if relative
        $calendarUrl = $calendarHref;
        if (strpos($calendarHref, 'http') !== 0) {
            $calendarUrl = 'https://p150-caldav.icloud.com:443' . $calendarHref;
        }

        $reportBody = '<?xml version="1.0" encoding="utf-8" ?>
            <c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
                <d:prop>
                    <d:getetag/>
                    <c:calendar-data/>
                </d:prop>
                <c:filter>
                    <c:comp-filter name="VCALENDAR">
                        <c:comp-filter name="VEVENT">
                            <c:time-range start="' . $startDate->format('Ymd\THis\Z') . '" end="' . $endDate->format('Ymd\THis\Z') . '"/>
                        </c:comp-filter>
                    </c:comp-filter>
                </c:filter>
            </c:calendar-query>';

        $response = Http::withBasicAuth($credentials['username'], $credentials['password'])
            ->withHeaders([
                'Depth' => '1',
                'Content-Type' => 'application/xml; charset=utf-8'
            ])
            ->withOptions(['verify' => false])
            ->timeout(15)
            ->withBody($reportBody, 'application/xml')
            ->send('REPORT', $calendarUrl);

        if (!$response->successful()) {
            Log::warning("Failed to get events from calendar", [
                'calendar_url' => $calendarUrl,
                'status' => $response->status()
            ]);
            return [];
        }

        return $this->parseCalendarData($response->body());
    }

    /**
     * Parse calendar data from CalDAV response
     */
    private function parseCalendarData(string $xmlBody): array
    {
        $xml = simplexml_load_string($xmlBody);
        if (!$xml) {
            return [];
        }

        $events = [];
        foreach ($xml->response as $response) {
            // Register namespaces for each response element
            $response->registerXPathNamespace('d', 'DAV:');
            $response->registerXPathNamespace('c', 'urn:ietf:params:xml:ns:caldav');

            $calendarDataNodes = $response->xpath('.//c:calendar-data');
            $calendarData = !empty($calendarDataNodes) ? (string) $calendarDataNodes[0] : '';

            if (!$calendarData) {
                continue;
            }

            $event = $this->parseICalEvent($calendarData);
            if ($event) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Parse iCal VEVENT data
     */
    private function parseICalEvent(string $icalData): ?array
    {
        $lines = explode("\n", $icalData);
        $event = [];
        $inEvent = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === 'BEGIN:VEVENT') {
                $inEvent = true;
                continue;
            }

            if ($line === 'END:VEVENT') {
                break;
            }

            if (!$inEvent) {
                continue;
            }

            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);

                // Remove parameters from key (like DTSTART;TZID=...)
                $key = explode(';', $key)[0];

                $event[strtolower($key)] = $value;
            }
        }

        return !empty($event) ? $event : null;
    }

    /**
     * Get events from provider for date range
     */
    public function getEvents(int $userId, \DateTime $startDate, \DateTime $endDate): array
    {
        return CalendarEvent::where('user_id', $userId)
            ->where('provider_type', 'apple')
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
        // TODO: Implement event creation via CalDAV PUT
        return null;
    }

    /**
     * Update event in provider calendar
     */
    public function updateEvent(int $userId, string $eventId, array $eventData): bool
    {
        // TODO: Implement event update via CalDAV PUT
        return false;
    }

    /**
     * Delete event from provider calendar
     */
    public function deleteEvent(int $userId, string $eventId): bool
    {
        // TODO: Implement event deletion via CalDAV DELETE
        return false;
    }

    /**
     * Revoke authentication for this provider
     */
    public function revokeAuthentication(int $userId): bool
    {
        try {
            // Clear stored credentials
            Setting::set("apple_calendar_username_{$userId}", '');
            Setting::set("apple_calendar_password_{$userId}", '');
            Setting::set("apple_calendar_connected_at_{$userId}", '');

            // Delete related calendar events
            CalendarEvent::where('user_id', $userId)
                ->where('provider_type', 'apple')
                ->delete();

            return true;
        } catch (\Exception $e) {
            Log::error("Apple Calendar revoke authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's calendar list from provider
     */
    public function getCalendars(int $userId): array
    {
        try {
            $credentials = $this->getCredentials($userId);
            if (!$credentials) {
                return [];
            }

            return $this->getCalendarsFromCalDAV($credentials);
        } catch (\Exception $e) {
            Log::error("Apple Calendar get calendars error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Transform provider event data to standardized format
     */
    public function transformEvent(array $providerEvent): array
    {
        $startDateTime = $this->parseICalDateTime($providerEvent['dtstart'] ?? '');
        $endDateTime = $this->parseICalDateTime($providerEvent['dtend'] ?? '');

        return [
            'external_event_id' => $providerEvent['uid'] ?? '',
            'subject' => $providerEvent['summary'] ?? 'Untitled Event',
            'body' => $providerEvent['description'] ?? null,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'timezone' => 'Europe/Amsterdam', // Default timezone
            'is_all_day' => $this->isAllDayEvent($providerEvent['dtstart'] ?? ''),
            'location' => $providerEvent['location'] ?? null,
            'attendees' => null, // TODO: Parse attendees from iCal
            'categories' => null,
            'organizer_email' => null,
            'organizer_name' => null,
        ];
    }

    /**
     * Parse iCal datetime format
     */
    private function parseICalDateTime(string $dtStr): Carbon
    {
        if (empty($dtStr)) {
            return Carbon::now();
        }

        // Handle date-only format (YYYYMMDD)
        if (preg_match('/^\d{8}$/', $dtStr)) {
            return Carbon::createFromFormat('Ymd', $dtStr)->startOfDay();
        }

        // Handle datetime format (YYYYMMDDTHHMMSSZ or YYYYMMDDTHHMMSS)
        if (preg_match('/^(\d{8})T(\d{6})Z?$/', $dtStr, $matches)) {
            return Carbon::createFromFormat('YmdHis', $matches[1] . $matches[2]);
        }

        // Fallback to Carbon parsing
        try {
            return Carbon::parse($dtStr);
        } catch (\Exception $e) {
            Log::warning("Could not parse iCal datetime: " . $dtStr);
            return Carbon::now();
        }
    }

    /**
     * Check if event is all-day
     */
    private function isAllDayEvent(string $dtStart): bool
    {
        // All-day events use VALUE=DATE format (YYYYMMDD)
        return preg_match('/^\d{8}$/', $dtStart) === 1;
    }

    /**
     * Get provider-specific settings for user
     */
    public function getSettings(int $userId): array
    {
        $username = Setting::get("apple_calendar_username_{$userId}");
        $connectedAt = Setting::get("apple_calendar_connected_at_{$userId}");

        return [
            'connected' => $username !== null,
            'username' => $username,
            'connected_at' => $connectedAt,
            'last_sync' => CalendarSyncLog::where('user_id', $userId)
                ->where('provider_type', 'apple')
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
        // Apple Calendar heeft momenteel geen specifieke user settings
        return true;
    }
}