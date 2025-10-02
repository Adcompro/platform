<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateSubtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_task_id',
        'name',
        'estimated_hours',
        'sort_order',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the task that owns this subtask
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(TemplateTask::class, 'template_task_id');
    }

    /**
     * Get the effective hourly rate (from task's milestone or template)
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        return $this->task->effective_hourly_rate ?? 75;
    }

    /**
     * Get the estimated value of this subtask
     */
    public function getEstimatedValueAttribute(): float
    {
        return ($this->estimated_hours ?? 0) * $this->effective_hourly_rate;
    }

    /**
     * Scope to get subtasks ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}