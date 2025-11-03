<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_type',
        'sync_type',
        'status',
        'events_synced',
        'events_created',
        'events_updated',
        'events_deleted',
        'events_failed',
        'sync_from',
        'sync_to',
        'sync_started_at',
        'sync_completed_at',
        'error_message',
        'details',
    ];

    protected $casts = [
        'sync_from' => 'datetime',
        'sync_to' => 'datetime',
        'sync_started_at' => 'datetime',
        'sync_completed_at' => 'datetime',
        'events_synced' => 'integer',
        'events_created' => 'integer',
        'events_updated' => 'integer',
        'events_deleted' => 'integer',
        'events_failed' => 'integer',
        'details' => 'array',
    ];

    /**
     * Get the user that owns the sync log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'started' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get sync type label
     */
    public function getSyncTypeLabelAttribute(): string
    {
        return match($this->sync_type) {
            'manual' => 'Manual Sync',
            'automatic' => 'Automatic Sync',
            'webhook' => 'Webhook Sync',
            default => 'Unknown'
        };
    }

    /**
     * Get duration in seconds
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->status === 'started') {
            return null;
        }

        return $this->created_at->diffInSeconds($this->updated_at);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        if ($this->duration < 60) {
            return $this->duration . 's';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return "{$minutes}m {$seconds}s";
    }

    /**
     * Scope for successful syncs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for recent syncs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific provider
     */
    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider_type', $provider);
    }

    /**
     * Get provider label
     */
    public function getProviderLabelAttribute(): string
    {
        return match($this->provider_type) {
            'microsoft' => 'Microsoft 365',
            'google' => 'Google Calendar',
            'apple' => 'Apple iCloud',
            default => ucfirst($this->provider_type ?? 'Unknown')
        };
    }

    /**
     * Get provider badge class
     */
    public function getProviderBadgeClassAttribute(): string
    {
        return match($this->provider_type) {
            'microsoft' => 'bg-blue-100 text-blue-800',
            'google' => 'bg-red-100 text-red-800',
            'apple' => 'bg-gray-100 text-gray-800',
            default => 'bg-slate-100 text-slate-800'
        };
    }
}