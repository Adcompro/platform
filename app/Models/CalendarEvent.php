<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_type',
        'external_event_id',
        'ms_event_id', // Backward compatibility
        'subject',
        'body',
        'start_datetime',
        'end_datetime',
        'timezone',
        'is_all_day',
        'location',
        'attendees',
        'categories',
        'organizer_email',
        'organizer_name',
        'is_converted',
        'time_entry_id',
        'provider_raw_data',
        'ms_raw_data', // Backward compatibility
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_all_day' => 'boolean',
        'is_converted' => 'boolean',
        'attendees' => 'array',
        'categories' => 'array',
        'provider_raw_data' => 'array',
        'ms_raw_data' => 'array', // Backward compatibility
    ];

    /**
     * Get the user that owns the calendar event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the time entry if converted
     */
    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(TimeEntry::class);
    }

    /**
     * Get duration in minutes
     */
    public function getDurationInMinutesAttribute(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_in_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->is_converted) {
            return 'bg-green-100 text-green-800';
        }
        
        if ($this->start_datetime->isPast()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        return 'bg-blue-100 text-blue-800';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_converted) {
            return 'Converted';
        }
        
        if ($this->start_datetime->isFuture()) {
            return 'Upcoming';
        }
        
        if ($this->end_datetime->isPast()) {
            return 'Past';
        }
        
        return 'In Progress';
    }

    /**
     * Scope for unconverted events
     */
    public function scopeUnconverted($query)
    {
        return $query->where('is_converted', false);
    }

    /**
     * Scope for converted events
     */
    public function scopeConverted($query)
    {
        return $query->where('is_converted', true);
    }

    /**
     * Scope for today's events
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_datetime', today());
    }

    /**
     * Scope for this week's events
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_datetime', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for this month's events
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('start_datetime', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Scope for specific provider
     */
    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider_type', $provider);
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
     * Get provider icon
     */
    public function getProviderIconAttribute(): string
    {
        return match($this->provider_type) {
            'microsoft' => 'fab fa-microsoft',
            'google' => 'fab fa-google',
            'apple' => 'fab fa-apple',
            default => 'fas fa-calendar'
        };
    }

    /**
     * Get actual event ID (uses external_event_id with fallback to ms_event_id)
     */
    public function getActualEventIdAttribute(): ?string
    {
        return $this->external_event_id ?? $this->ms_event_id;
    }
}