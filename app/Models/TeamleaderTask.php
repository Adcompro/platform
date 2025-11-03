<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamleaderTask extends Model
{
    protected $fillable = [
        'teamleader_id',
        'teamleader_project_id',
        'teamleader_milestone_id',
        'title',
        'description',
        'completed',
        'due_on',
        'estimated_duration_minutes',
        'is_imported',
        'imported_at',
        'synced_at',
        'raw_data',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'is_imported' => 'boolean',
        'due_on' => 'date',
        'estimated_duration_minutes' => 'integer',
        'imported_at' => 'datetime',
        'synced_at' => 'datetime',
        'raw_data' => 'json',
    ];

    /**
     * Get the Teamleader project this task belongs to
     */
    public function teamleaderProject()
    {
        return $this->belongsTo(TeamleaderProject::class, 'teamleader_project_id', 'teamleader_id');
    }

    /**
     * Get the Teamleader milestone this task belongs to
     */
    public function teamleaderMilestone()
    {
        return $this->belongsTo(TeamleaderMilestone::class, 'teamleader_milestone_id', 'teamleader_id');
    }

    /**
     * Calculate estimated hours from duration in minutes
     */
    public function getEstimatedHoursAttribute()
    {
        if (!$this->estimated_duration_minutes) {
            return null;
        }
        return round($this->estimated_duration_minutes / 60, 2);
    }
}
