<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'default_hourly_rate',
        'estimated_duration_days',
        'status',
        'company_id',
        'created_by',
        'total_estimated_hours',
        'estimated_total_value',
    ];

    protected $casts = [
        'default_hourly_rate' => 'decimal:2',
        'estimated_duration_days' => 'integer',
        'total_estimated_hours' => 'decimal:2',
        'estimated_total_value' => 'decimal:2',
    ];

    /**
     * Get all milestones for this template
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(TemplateMilestone::class)->orderBy('sort_order');
    }

    /**
     * Get the company that owns this template (if column exists)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created this template (if column exists)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate total estimated hours from all milestones, tasks, and subtasks
     */
    public function calculateTotalHours(): float
    {
        return $this->milestones->sum('total_estimated_hours');
    }

    /**
     * Calculate estimated total value
     */
    public function calculateTotalValue(): float
    {
        return $this->milestones->sum('estimated_value');
    }

    /**
     * Update the calculated totals
     */
    public function updateTotals(): void
    {
        $this->update([
            'total_estimated_hours' => $this->calculateTotalHours(),
            'estimated_total_value' => $this->calculateTotalValue(),
        ]);
    }

    /**
     * Scope to get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get templates by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}