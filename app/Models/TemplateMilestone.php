<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_template_id',
        'name',
        'description',
        'estimated_hours',
        'default_hourly_rate',
        'days_from_start',
        'duration_days',
        'start_date',
        'end_date',
        'fee_type',
        'pricing_type',
        'default_fixed_price',
        'sort_order',
        'is_required',
        'status'
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'default_hourly_rate' => 'decimal:2',
        'default_fixed_price' => 'decimal:2',
        'days_from_start' => 'integer',
        'duration_days' => 'integer',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the project template that owns this milestone
     */
    public function projectTemplate(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class);
    }

    /**
     * Get all tasks for this milestone
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TemplateTask::class)->orderBy('sort_order');
    }

    // Scopes voor herbruikbare queries

    /**
     * Scope voor ordered milestones
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope voor required milestones
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope voor active milestones
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Calculated Properties & Helper Methods

    /**
     * Get the total estimated hours including tasks
     */
    public function getTotalEstimatedHoursAttribute(): float
    {
        $milestoneHours = $this->estimated_hours ?? 0;
        $taskHours = $this->tasks->sum('estimated_hours') ?? 0;
        
        return $milestoneHours + $taskHours;
    }

    /**
     * Get the effective hourly rate (milestone rate or template default)
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        return $this->default_hourly_rate ?? $this->projectTemplate->default_hourly_rate ?? 75.00;
    }

    /**
     * Get the estimated value for this milestone
     */
    public function getEstimatedValueAttribute(): float
    {
        return $this->total_estimated_hours * $this->effective_hourly_rate;
    }

    /**
     * Formatteer estimated hours voor weergave
     */
    public function getFormattedHoursAttribute(): string
    {
        return number_format($this->total_estimated_hours, 1) . ' hrs';
    }

    /**
     * Formatteer estimated value voor weergave
     */
    public function getFormattedValueAttribute(): string
    {
        return '€' . number_format($this->estimated_value, 2, ',', '.');
    }

    /**
     * CSS class voor status badge
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'draft' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check of milestone required is
     */
    public function getRequiredTextAttribute(): string
    {
        return $this->is_required ? 'Required' : 'Optional';
    }

    /**
     * Aantal tasks in deze milestone
     */
    public function getTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }


    /**
     * Get start date relative to project start
     */
    public function getStartDateFromProjectAttribute()
    {
        if (!$this->days_from_start) {
            return null;
        }
        
        // This would be calculated based on project start date when used in real projects
        return "Day " . $this->days_from_start;
    }

    /**
     * Get end date relative to project start
     */
    public function getEndDateFromProjectAttribute()
    {
        if (!$this->days_from_start || !$this->duration_days) {
            return null;
        }
        
        $endDay = $this->days_from_start + $this->duration_days;
        return "Day " . $endDay;
    }

    /**
     * Check of milestone can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Check if milestone has tasks
        return $this->tasks()->count() === 0;
    }

    /**
     * Get next sort order for new milestones in the same template
     */
    public static function getNextSortOrder($templateId): int
    {
        return self::where('project_template_id', $templateId)->max('sort_order') + 1;
    }

    // Boot method voor automatic field filling

    protected static function boot()
    {
        parent::boot();

        // Automatisch sort_order instellen
        static::creating(function ($milestone) {
            if (!$milestone->sort_order) {
                $milestone->sort_order = self::getNextSortOrder($milestone->project_template_id);
            }
        });
    }
}