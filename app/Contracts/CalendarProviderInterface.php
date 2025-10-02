<?php

namespace App\Contracts;

interface CalendarProviderInterface
{
    /**
     * Get provider type identifier
     */
    public function getProviderType(): string;

    /**
     * Get authorization URL for OAuth flow
     */
    public function getAuthorizationUrl(int $userId): string;

    /**
     * Handle OAuth callback and store access token
     */
    public function handleCallback(string $code, int $userId): bool;

    /**
     * Check if user is authenticated with this provider
     */
    public function isAuthenticated(int $userId): bool;

    /**
     * Sync calendar events from provider
     */
    public function syncEvents(int $userId, array $options = []): array;

    /**
     * Get events from provider for date range
     */
    public function getEvents(int $userId, \DateTime $startDate, \DateTime $endDate): array;

    /**
     * Create event in provider calendar
     */
    public function createEvent(int $userId, array $eventData): ?string;

    /**
     * Update event in provider calendar
     */
    public function updateEvent(int $userId, string $eventId, array $eventData): bool;

    /**
     * Delete event from provider calendar
     */
    public function deleteEvent(int $userId, string $eventId): bool;

    /**
     * Revoke authentication for this provider
     */
    public function revokeAuthentication(int $userId): bool;

    /**
     * Get user's calendar list from provider
     */
    public function getCalendars(int $userId): array;

    /**
     * Transform provider event data to standardized format
     */
    public function transformEvent(array $providerEvent): array;

    /**
     * Get provider-specific settings for user
     */
    public function getSettings(int $userId): array;

    /**
     * Update provider-specific settings for user
     */
    public function updateSettings(int $userId, array $settings): bool;
}