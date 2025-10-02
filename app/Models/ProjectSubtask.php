<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasStatusManagement;

class ProjectSubtask extends Model
{
    use HasFactory, HasStatusManagement, SoftDeletes;

    protected $fillable = [
        'project_task_id',
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
     * Task this subtask belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    /**
     * Milestone this subtask belongs to (through task)
     */
    public function milestone(): BelongsTo
    {
        return $this->task()->getRelated()->milestone();
    }

    /**
     * Project this subtask belongs to (through task -> milestone)
     */
    public function project(): BelongsTo
    {
        return $this->task()->getRelated()->milestone()->getRelated()->project();
    }

    /**
     * Time entries for this subtask
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'project_subtask_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for completed subtasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending subtasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get total hours worked on this subtask
     */
    public function getTotalHoursWorkedAttribute(): float
    {
        return $this->timeEntries()
            ->where('status', 'approved')
            ->sum('hours');
    }

    /**
     * Calculate total cost for this subtask
     */
    public function getTotalCostAttribute(): float
    {
        if ($this->pricing_type === 'fixed_price') {
            return $this->fixed_price ?? 0;
        }
        
        // For hourly rate, calculate from worked hours
        return $this->total_hours_worked * $this->effective_hourly_rate;
    }

    /**
     * Get effective hourly rate for this subtask
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        if ($this->hourly_rate_override) {
            return $this->hourly_rate_override;
        }
        
        return $this->task->effective_hourly_rate;
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

    /**
     * Check if subtask is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get completion percentage (always 0 or 100 for subtasks)
     */
    public function getProgressPercentageAttribute(): int
    {
        return $this->isCompleted() ? 100 : 0;
    }
}