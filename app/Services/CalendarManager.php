<?php

namespace App\Services;

use App\Contracts\CalendarProviderInterface;
use App\Services\Calendar\MicrosoftCalendarService;
use App\Services\Calendar\GoogleCalendarService;
use App\Services\Calendar\AppleCalendarService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CalendarManager
{
    private array $providers = [];

    public function __construct()
    {
        // Register alle beschikbare providers
        $this->registerProvider('microsoft', MicrosoftCalendarService::class);
        $this->registerProvider('google', GoogleCalendarService::class);
        $this->registerProvider('apple', AppleCalendarService::class);
    }

    /**
     * Register een calendar provider
     */
    public function registerProvider(string $type, string $serviceClass): void
    {
        $this->providers[$type] = $serviceClass;
    }

    /**
     * Get provider instance voor gegeven type
     */
    public function provider(string $type): CalendarProviderInterface
    {
        if (!isset($this->providers[$type])) {
            throw new \InvalidArgumentException("Unknown calendar provider: {$type}");
        }

        $serviceClass = $this->providers[$type];
        return app($serviceClass);
    }

    /**
     * Get alle beschikbare provider types
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Sync events voor alle authenticated providers van een user
     */
    public function syncAllProviders(int $userId): array
    {
        $results = [];
        $user = User::findOrFail($userId);

        foreach ($this->getAvailableProviders() as $providerType) {
            try {
                $provider = $this->provider($providerType);

                if ($provider->isAuthenticated($userId)) {
                    Log::info("Syncing {$providerType} calendar for user {$userId}");
                    $results[$providerType] = $provider->syncEvents($userId);
                } else {
                    $results[$providerType] = [
                        'success' => false,
                        'message' => 'Provider not authenticated',
                        'events_synced' => 0
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Error syncing {$providerType} for user {$userId}: " . $e->getMessage());
                $results[$providerType] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'events_synced' => 0
                ];
            }
        }

        return $results;
    }

    /**
     * Get events van alle providers voor date range
     */
    public function getAllEvents(int $userId, \DateTime $startDate, \DateTime $endDate): array
    {
        $allEvents = [];

        foreach ($this->getAvailableProviders() as $providerType) {
            try {
                $provider = $this->provider($providerType);

                if ($provider->isAuthenticated($userId)) {
                    $events = $provider->getEvents($userId, $startDate, $endDate);
                    foreach ($events as $event) {
                        $event['provider_type'] = $providerType;
                        $allEvents[] = $event;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error getting events from {$providerType} for user {$userId}: " . $e->getMessage());
            }
        }

        // Sort events by start time
        usort($allEvents, function($a, $b) {
            return strtotime($a['start_datetime']) <=> strtotime($b['start_datetime']);
        });

        return $allEvents;
    }

    /**
     * Get authentication status voor alle providers
     */
    public function getAuthenticationStatus(int $userId): array
    {
        $status = [];

        foreach ($this->getAvailableProviders() as $providerType) {
            try {
                $provider = $this->provider($providerType);
                $status[$providerType] = [
                    'authenticated' => $provider->isAuthenticated($userId),
                    'settings' => $provider->getSettings($userId)
                ];
            } catch (\Exception $e) {
                $status[$providerType] = [
                    'authenticated' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $status;
    }

    /**
     * Revoke authentication voor specifieke provider
     */
    public function revokeProvider(int $userId, string $providerType): bool
    {
        try {
            $provider = $this->provider($providerType);
            return $provider->revokeAuthentication($userId);
        } catch (\Exception $e) {
            Log::error("Error revoking {$providerType} for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get authorization URL voor specifieke provider
     */
    public function getAuthorizationUrl(string $providerType, int $userId): string
    {
        $provider = $this->provider($providerType);
        return $provider->getAuthorizationUrl($userId);
    }

    /**
     * Handle OAuth callback voor specifieke provider
     */
    public function handleCallback(string $providerType, string $code, int $userId): bool
    {
        try {
            $provider = $this->provider($providerType);
            return $provider->handleCallback($code, $userId);
        } catch (\Exception $e) {
            Log::error("Error handling callback for {$providerType}, user {$userId}: " . $e->getMessage());
            return false;
        }
    }
}