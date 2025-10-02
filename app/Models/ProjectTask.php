<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasStatusManagement;

class ProjectTask extends Model
{
    use HasFactory, HasStatusManagement, SoftDeletes;

    protected $fillable = [
        'project_milestone_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'sort_order',
        'fee_type',
        'pricing_type',
        'fixed_price',
        'hourly_rate_override',
        'estimated_hours',
        'source_type',
        'source_id',
        'is_service_item',
        'service_name',
        'service_color',
        'original_service_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fixed_price' => 'decimal:2',
        'hourly_rate_override' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'sort_order' => 'integer',
        'is_service_item' => 'boolean',
        'original_service_id' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Milestone this task belongs to
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'project_milestone_id');
    }

    /**
     * Project this task belongs to (through milestone)
     */
    public function project(): BelongsTo
    {
        return $this->milestone()->getRelated()->project();
    }


    /**
     * Subtasks for this task
     * Note: Subtasks table doesn't exist in this system yet
     */
    // public function subtasks(): HasMany
    // {
    //     return $this->hasMany(ProjectSubtask::class, 'project_task_id');
    // }

    /**
     * Time entries for this task
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'project_task_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get task progress percentage based on status
     */
    public function getProgressPercentageAttribute(): float
    {
        return $this->status === 'completed' ? 100 : 0;
    }

    /**
     * Get total hours worked on this task
     */
    public function getTotalHoursWorkedAttribute(): float
    {
        return $this->timeEntries()
            ->where('status', 'approved')
            ->sum('hours');
    }

    /**
     * Calculate total cost for this task
     */
    public function getTotalCostAttribute(): float
    {
        if ($this->pricing_type === 'fixed_price') {
            return $this->fixed_price ?? 0;
        }
        
        // Calculate from worked hours
        return $this->total_hours_worked * $this->effective_hourly_rate;
    }

    /**
     * Get effective hourly rate for this task
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        if ($this->hourly_rate_override) {
            return $this->hourly_rate_override;
        }
        
        return $this->milestone->effective_hourly_rate;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'on_hold' => 'yellow',
            default => 'gray'
        };
    }
}