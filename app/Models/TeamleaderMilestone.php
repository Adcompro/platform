<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamleaderMilestone extends Model
{
    protected $fillable = [
        'teamleader_id',
        'teamleader_project_id',
        'name',
        'status',
        'starts_on',
        'due_on',
        'invoicing_method',
        'budget_amount',
        'allocated_time_seconds',
        'is_imported',
        'imported_at',
        'synced_at',
        'raw_data',
    ];

    protected $casts = [
        'is_imported' => 'boolean',
        'starts_on' => 'date',
        'due_on' => 'date',
        'budget_amount' => 'decimal:2',
        'allocated_time_seconds' => 'integer',
        'imported_at' => 'datetime',
        'synced_at' => 'datetime',
        'raw_data' => 'json',
    ];

    /**
     * Get the Teamleader project this milestone belongs to
     */
    public function teamleaderProject()
    {
        return $this->belongsTo(TeamleaderProject::class, 'teamleader_project_id', 'teamleader_id');
    }

    /**
     * Get tasks for this milestone
     */
    public function tasks()
    {
        return $this->hasMany(TeamleaderTask::class, 'teamleader_milestone_id', 'teamleader_id');
    }

    /**
     * Calculate estimated hours from allocated time in seconds
     */
    public function getEstimatedHoursAttribute()
    {
        if (!$this->allocated_time_seconds) {
            return null;
        }
        return round($this->allocated_time_seconds / 3600, 2);
    }
}
