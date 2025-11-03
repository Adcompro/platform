<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamleaderTimeEntry extends Model
{
    protected $fillable = [
        'teamleader_id',
        'teamleader_project_id',
        'teamleader_user_id',
        'date',
        'duration_seconds',
        'description',
        'hourly_rate',
        'currency',
        'is_imported',
        'imported_at',
        'synced_at',
        'raw_data',
    ];

    protected $casts = [
        'is_imported' => 'boolean',
        'date' => 'date',
        'duration_seconds' => 'integer',
        'hourly_rate' => 'decimal:2',
        'imported_at' => 'datetime',
        'synced_at' => 'datetime',
        'raw_data' => 'json',
    ];

    /**
     * Get the Teamleader project this time entry belongs to
     */
    public function teamleaderProject()
    {
        return $this->belongsTo(TeamleaderProject::class, 'teamleader_project_id', 'teamleader_id');
    }

    /**
     * Calculate hours from duration in seconds
     */
    public function getHoursAttribute()
    {
        return floor($this->duration_seconds / 3600);
    }

    /**
     * Calculate minutes from duration in seconds (remaining after hours)
     */
    public function getMinutesAttribute()
    {
        return floor(($this->duration_seconds % 3600) / 60);
    }

    /**
     * Calculate total hours as decimal
     */
    public function getDecimalHoursAttribute()
    {
        return round($this->duration_seconds / 3600, 2);
    }
}
