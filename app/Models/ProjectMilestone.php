<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasStatusManagement;

class ProjectMilestone extends Model
{
    use HasFactory, HasStatusManagement, SoftDeletes;

    protected $fillable = [
        'project_id',
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
        'invoicing_trigger',
        'source_type',
        'source_id',
        'deliverables',
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
     * Project this milestone belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Tasks in this milestone
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->orderBy('sort_order');
    }

    /**
     * Time entries for this milestone
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'project_milestone_id');
    }

    /**
     * Template milestone this was created from (if from template)
     */
    public function sourceTemplate(): BelongsTo
    {
        return $this->belongsTo(TemplateMilestone::class, 'source_id')
                    ->where('source_type', 'template');
    }

    /**
     * Service milestone this was created from (if from service)
     */
    public function sourceService(): BelongsTo
    {
        return $this->belongsTo(ServiceMilestone::class, 'source_id')
                    ->where('source_type', 'service');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for completed milestones
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending milestones
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in-progress milestones
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for milestones ready for invoicing
     */
    public function scopeReadyForInvoicing($query)
    {
        return $query->where('status', 'completed')
                    ->where('invoicing_trigger', 'completion');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get milestone progress percentage based on tasks
     */
    public function getProgressPercentageAttribute(): float
    {
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return $this->status === 'completed' ? 100 : 0;
        }
        
        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        
        return round(($completedTasks / $totalTasks) * 100, 1);
    }

    /**
     * Get total hours worked on this milestone
     */
    public function getTotalHoursWorkedAttribute(): float
    {
        return $this->timeEntries()
            ->where('status', 'approved')
            ->sum('hours');
    }

    /**
     * Get total billable hours for this milestone
     */
    public function getTotalBillableHoursAttribute(): float
    {
        return $this->timeEntries()
            ->where('status', 'approved')
            ->where('is_billable', 'billable')
            ->sum('hours');
    }

    /**
     * Calculate total cost for this milestone
     */
    public function getTotalCostAttribute(): float
    {
        if ($this->pricing_type === 'fixed_price') {
            return $this->fixed_price ?? 0;
        }
        
        // For hourly rate, sum all task costs
        return $this->tasks->sum('total_cost');
    }

    /**
     * Get effective hourly rate for this milestone
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        if ($this->hourly_rate_override) {
            return $this->hourly_rate_override;
        }
        
        if ($this->project->default_hourly_rate) {
            return $this->project->default_hourly_rate;
        }
        
        return $this->project->mainInvoicingCompany->default_hourly_rate ?? 75.00;
    }

    /**
     * Check if milestone is ready for invoicing
     */
    public function isReadyForInvoicing(): bool
    {
        return $this->status === 'completed' && 
               $this->invoicing_trigger === 'completion';
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
     * Get fee type badge color
     */
    public function getFeeTypeColorAttribute(): string
    {
        return $this->fee_type === 'in_fee' ? 'green' : 'orange';
    }

    /**
     * Get milestone duration in days
     */
    public function getDurationDaysAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}