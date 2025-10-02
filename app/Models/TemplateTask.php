<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_milestone_id',
        'name',
        'description',
        'estimated_hours',
        'default_hourly_rate',
        'fee_type',
        'pricing_type',
        'default_fixed_price',
        'duration_days',
        'start_date',
        'end_date',
        'sort_order',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'default_hourly_rate' => 'decimal:2',
        'default_fixed_price' => 'decimal:2',
        'duration_days' => 'integer',
        'sort_order' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the milestone that owns this task
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(TemplateMilestone::class, 'template_milestone_id');
    }

    /**
     * Get the total estimated hours for this task
     */
    public function getTotalEstimatedHoursAttribute(): float
    {
        return $this->estimated_hours ?? 0;
    }

    /**
     * Get the effective hourly rate (from milestone or template)
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        return $this->milestone->effective_hourly_rate ?? 75;
    }

    /**
     * Get the estimated value of this task
     */
    public function getEstimatedValueAttribute(): float
    {
        return $this->total_estimated_hours * $this->effective_hourly_rate;
    }

    /**
     * Scope to get tasks ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}