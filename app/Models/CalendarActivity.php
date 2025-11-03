<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CalendarActivity extends Model
{
    protected $fillable = [
        'user_id',
        'calendar_event_id',
        'action',
        'description',
        'changes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'json',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the calendar event
     */
    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    /**
     * Get action badge class
     */
    public function getActionBadgeClassAttribute()
    {
        return match($this->action) {
            'created' => 'bg-green-100 text-green-800',
            'updated' => 'bg-blue-100 text-blue-800',
            'deleted' => 'bg-red-100 text-red-800',
            'converted' => 'bg-purple-100 text-purple-800',
            'cancelled' => 'bg-yellow-100 text-yellow-800',
            'synced' => 'bg-indigo-100 text-indigo-800',
            'attendee_added' => 'bg-teal-100 text-teal-800',
            'attendee_removed' => 'bg-orange-100 text-orange-800',
            'attendee_responded' => 'bg-pink-100 text-pink-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get formatted changes for display
     */
    public function getFormattedChangesAttribute()
    {
        if (!$this->changes) {
            return [];
        }

        $formatted = [];
        foreach ($this->changes as $field => $values) {
            if (isset($values['old']) && isset($values['new'])) {
                $formatted[] = [
                    'field' => ucfirst(str_replace('_', ' ', $field)),
                    'old' => $this->formatValue($field, $values['old']),
                    'new' => $this->formatValue($field, $values['new']),
                ];
            }
        }

        return $formatted;
    }

    /**
     * Format field values for display
     */
    private function formatValue($field, $value)
    {
        if (is_null($value) || $value === '') {
            return '(empty)';
        }

        // Format datetime fields
        if (in_array($field, ['start_datetime', 'end_datetime'])) {
            try {
                return \Carbon\Carbon::parse($value)->format('d-m-Y H:i');
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Format boolean fields
        if (in_array($field, ['is_all_day', 'is_converted'])) {
            return $value ? 'Yes' : 'No';
        }

        // Format arrays/JSON
        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }

    /**
     * Log a calendar activity
     */
    public static function log($eventId, $action, $description, $changes = null, $metadata = null)
    {
        return static::create([
            'user_id' => Auth::id(),
            'calendar_event_id' => $eventId,
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get time difference for humans
     */
    public function getTimeDifferenceAttribute()
    {
        $now = now();
        $created = $this->created_at;
        
        // If today
        if ($created->isToday()) {
            if ($created->diffInMinutes($now) < 1) {
                return 'just now';
            }
            if ($created->diffInMinutes($now) < 60) {
                return $created->diffInMinutes($now) . ' minutes ago';
            }
            return $created->diffInHours($now) . ' hours ago';
        }
        
        // If yesterday
        if ($created->isYesterday()) {
            return 'Yesterday at ' . $created->format('H:i');
        }
        
        // If this week
        if ($created->diffInDays($now) < 7) {
            return $created->diffInDays($now) . ' days ago';
        }
        
        // Older
        return $created->format('d-m-Y H:i');
    }
}