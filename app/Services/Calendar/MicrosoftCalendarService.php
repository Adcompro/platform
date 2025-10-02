<?php

namespace App\Services\Calendar;

use App\Contracts\CalendarProviderInterface;
use App\Models\UserMsGraphToken;
use App\Models\CalendarEvent;
use App\Models\CalendarSyncLog;
use App\Models\Setting;
use App\Services\MicrosoftGraphService;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MicrosoftCalendarService implements CalendarProviderInterface
{
    /**
     * Get provider type identifier
     */
    public function getProviderType(): string
    {
        return 'microsoft';
    }

    /**
     * Get authorization URL for OAuth flow
     */
    public function getAuthorizationUrl(int $userId): string
    {
        // Gebruik bestaande MicrosoftGraphService logica
        return MicrosoftGraphService::getConnectUrl(true);
    }

    /**
     * Handle OAuth callback and store access token
     */
    public function handleCallback(string $code, int $userId): bool
    {
        try {
            // Gebruik bestaande MsGraph package voor token handling
            $tokenData = MsGraph::getAccessToken($code);

            if (!$tokenData) {
                return false;
            }

            // Get account info
            $accountInfo = MsGraph::get('me');
            $accountData = [
                'email' => $accountInfo['mail'] ?? $accountInfo['userPrincipalName'] ?? 'Unknown',
                'name' => $accountInfo['displayName'] ?? 'Unknown User',
                'id' => $accountInfo['id'] ?? null
            ];

            // Store tokens via existing service
            $userToken = MicrosoftGraphService::storeTokens($tokenData, $accountData);

            return $userToken !== null;
        } catch (\Exception $e) {
            Log::error("Microsoft OAuth callback error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is authenticated with this provider
     */
    public function isAuthenticated(int $userId): bool
    {
        // Check if user has valid Microsoft Graph tokens directly from database
        $token = \App\Models\UserMsGraphToken::where('user_id', $userId)
            ->whereNotNull('access_token')
            ->where('expires_at', '>', now())
            ->first();

        return $token !== null;
    }

    /**
     * Sync calendar events from provider
     */
    public function syncEvents(int $userId, array $options = []): array
    {
        try {
            // Temporarily login as the user for Microsoft Graph API access
            $originalUser = auth()->id();
            auth()->loginUsingId($userId);

            // Initialize connection
            if (!MicrosoftGraphService::initializeConnection()) {
                // Restore original auth state
                if ($originalUser) {
                    auth()->loginUsingId($originalUser);
                } else {
                    auth()->logout();
                }

                return [
                    'success' => false,
                    'message' => 'Authentication failed',
                    'events_synced' => 0
                ];
            }

            // Use sync range setting if no specific dates provided
            $syncRange = (int) Setting::get('calendar_sync_range', 90);
            $startDate = $options['start_date'] ?? Carbon::now()->subDays(7); // Keep 7 days history
            $endDate = $options['end_date'] ?? Carbon::now()->addDays($syncRange);

            if (is_string($startDate)) $startDate = Carbon::parse($startDate);
            if (is_string($endDate)) $endDate = Carbon::parse($endDate);

            // Get events from Microsoft Graph
            $events = $this->getEventsFromGraph($startDate, $endDate);

            $syncedCount = 0;
            foreach ($events as $event) {
                $standardizedEvent = $this->transformEvent($event);

                // Store event in database met provider_type
                CalendarEvent::updateOrCreate([
                    'user_id' => $userId,
                    'provider_type' => 'microsoft',
                    'external_event_id' => $event['id']
                ], array_merge($standardizedEvent, [
                    'ms_event_id' => $event['id'], // Backward compatibility
                    'provider_raw_data' => $event,
                    'ms_raw_data' => $event // Backward compatibility
                ]));

                $syncedCount++;
            }

            // Log sync
            CalendarSyncLog::create([
                'user_id' => $userId,
                'provider_type' => 'microsoft',
                'sync_type' => 'full',
                'events_synced' => $syncedCount,
                'status' => 'success',
                'sync_started_at' => Carbon::now(),
                'sync_completed_at' => Carbon::now()
            ]);

            // Restore original auth state
            if ($originalUser) {
                auth()->loginUsingId($originalUser);
            } else {
                auth()->logout();
            }

            return [
                'success' => true,
                'message' => "Synced {$syncedCount} events",
                'events_synced' => $syncedCount
            ];

        } catch (\Exception $e) {
            // Restore original auth state in case of error
            if (isset($originalUser)) {
                if ($originalUser) {
                    auth()->loginUsingId($originalUser);
                } else {
                    auth()->logout();
                }
            }

            Log::error("Microsoft calendar sync error: " . $e->getMessage());

            CalendarSyncLog::create([
                'user_id' => $userId,
                'provider_type' => 'microsoft',
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
     * Get events from Microsoft Graph API
     */
    private function getEventsFromGraph(\DateTime $startDate, \DateTime $endDate): array
    {
        $startIso = $startDate->format('Y-m-d\TH:i:s.000\Z');
        $endIso = $endDate->format('Y-m-d\TH:i:s.000\Z');

        $response = MsGraph::get("me/calendar/events?\$filter=start/dateTime ge '{$startIso}' and end/dateTime le '{$endIso}'&\$top=999");

        return $response['value'] ?? [];
    }

    /**
     * Get events from provider for date range
     */
    public function getEvents(int $userId, \DateTime $startDate, \DateTime $endDate): array
    {
        return CalendarEvent::where('user_id', $userId)
            ->where('provider_type', 'microsoft')
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
            if (!MicrosoftGraphService::initializeConnection()) {
                return null;
            }

            $msEventData = $this->transformToMicrosoftFormat($eventData);
            $response = MsGraph::post('me/calendar/events', $msEventData);

            return $response['id'] ?? null;
        } catch (\Exception $e) {
            Log::error("Microsoft create event error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update event in provider calendar
     */
    public function updateEvent(int $userId, string $eventId, array $eventData): bool
    {
        try {
            if (!MicrosoftGraphService::initializeConnection()) {
                return false;
            }

            $msEventData = $this->transformToMicrosoftFormat($eventData);
            MsGraph::patch("me/calendar/events/{$eventId}", $msEventData);

            return true;
        } catch (\Exception $e) {
            Log::error("Microsoft update event error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete event from provider calendar
     */
    public function deleteEvent(int $userId, string $eventId): bool
    {
        try {
            if (!MicrosoftGraphService::initializeConnection()) {
                return false;
            }

            MsGraph::delete("me/calendar/events/{$eventId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Microsoft delete event error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revoke authentication for this provider
     */
    public function revokeAuthentication(int $userId): bool
    {
        try {
            // Only disconnect Microsoft calendar tokens, not the entire user session
            // Clear Microsoft calendar tokens from Settings
            Setting::where('key', 'like', 'microsoft_graph_%')->delete();

            // Clear user-specific MS Graph tokens
            \App\Models\UserMsGraphToken::where('user_id', $userId)->delete();

            // Clear standard MsGraph tokens (but this might affect the session)
            // Note: We avoid MicrosoftGraphService::disconnect() as it logs out the user

            // Delete related calendar events
            CalendarEvent::where('user_id', $userId)
                ->where('provider_type', 'microsoft')
                ->delete();

            Log::info("Microsoft Calendar disconnected for user {$userId} without logging out");
            return true;
        } catch (\Exception $e) {
            Log::error("Microsoft revoke authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's calendar list from provider
     */
    public function getCalendars(int $userId): array
    {
        try {
            if (!MicrosoftGraphService::initializeConnection()) {
                return [];
            }

            $response = MsGraph::get('me/calendars');
            return $response['value'] ?? [];
        } catch (\Exception $e) {
            Log::error("Microsoft get calendars error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Transform provider event data to standardized format
     */
    public function transformEvent(array $providerEvent): array
    {
        // Microsoft Graph event naar standaard formaat
        $start = Carbon::parse($providerEvent['start']['dateTime'] ?? $providerEvent['start']['date']);
        $end = Carbon::parse($providerEvent['end']['dateTime'] ?? $providerEvent['end']['date']);

        // Extract body content and clean it up
        $body = null;
        if (isset($providerEvent['body']['content']) && !empty($providerEvent['body']['content'])) {
            $body = $providerEvent['body']['content'];
            // Strip HTML tags but preserve line breaks
            $body = strip_tags($body);
            $body = html_entity_decode($body);
            $body = trim($body);
            // If still empty after cleanup, set to null
            if (empty($body)) {
                $body = null;
            }
        }

        return [
            'external_event_id' => $providerEvent['id'],
            'subject' => $providerEvent['subject'] ?? 'Untitled Event',
            'body' => $body,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'timezone' => $providerEvent['start']['timeZone'] ?? 'Europe/Amsterdam',
            'is_all_day' => isset($providerEvent['isAllDay']) ? $providerEvent['isAllDay'] : false,
            'location' => $providerEvent['location']['displayName'] ?? null,
            'attendees' => $this->transformAttendees($providerEvent['attendees'] ?? []),
            'categories' => $providerEvent['categories'] ?? null,
            'organizer_email' => $providerEvent['organizer']['emailAddress']['address'] ?? null,
            'organizer_name' => $providerEvent['organizer']['emailAddress']['name'] ?? null,
        ];
    }

    /**
     * Transform attendees array
     */
    private function transformAttendees(array $attendees): ?array
    {
        if (empty($attendees)) return null;

        return array_map(function($attendee) {
            return [
                'name' => $attendee['emailAddress']['name'] ?? '',
                'email' => $attendee['emailAddress']['address'] ?? '',
                'status' => $attendee['status']['response'] ?? 'none'
            ];
        }, $attendees);
    }

    /**
     * Transform standard event data to Microsoft format
     */
    private function transformToMicrosoftFormat(array $eventData): array
    {
        $microsoftEvent = [
            'subject' => $eventData['subject'] ?? 'Untitled Event',
            'body' => [
                'contentType' => 'text',
                'content' => $eventData['body'] ?? ''
            ],
            'start' => [
                'dateTime' => $eventData['start_datetime']->format('Y-m-d\TH:i:s.000'),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Amsterdam'
            ],
            'end' => [
                'dateTime' => $eventData['end_datetime']->format('Y-m-d\TH:i:s.000'),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Amsterdam'
            ],
            'location' => [
                'displayName' => $eventData['location'] ?? ''
            ],
            'isAllDay' => $eventData['is_all_day'] ?? false
        ];

        // Add attendees if provided
        if (!empty($eventData['attendees'])) {
            $microsoftEvent['attendees'] = array_map(function($attendee) {
                return [
                    'emailAddress' => [
                        'name' => $attendee['name'] ?? '',
                        'address' => $attendee['email']
                    ],
                    'type' => 'required'
                ];
            }, $eventData['attendees']);
        }

        return $microsoftEvent;
    }

    /**
     * Get provider-specific settings for user
     */
    public function getSettings(int $userId): array
    {
        $userToken = UserMsGraphToken::where('user_id', $userId)->first();

        return [
            'connected' => $userToken !== null && !$userToken->isExpired(),
            'email' => $userToken->email ?? null,
            'last_sync' => CalendarSyncLog::where('user_id', $userId)
                ->where('provider_type', 'microsoft')
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
        // Microsoft provider heeft momenteel geen specifieke settings
        // die door gebruiker gewijzigd kunnen worden
        return true;
    }
}