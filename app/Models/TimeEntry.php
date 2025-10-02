<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TimeEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'project_id',
        'customer_id',
        'project_milestone_id',
        'project_task_id',
        // 'project_subtask_id', // Disabled - subtasks no longer used
        'date',
        'entry_date',
        'start_time',
        'end_time',
        'hours',
        'minutes',
        'description',
        'notes',
        'hourly_rate_used',
        'status',
        'is_billable',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'invoice_id',
        'invoice_line_id',
        'is_invoiced',
        'invoiced_at',
        'invoiced_hours',
        'invoiced_rate',
        'invoiced_description',
        'invoiced_modified_at',
        'invoiced_modified_by',
        'is_finalized',
        'finalized_at',
        'final_invoice_number',
        'was_deferred',
        'deferred_at',
        'deferred_by',
        'defer_reason',
        'was_previously_deferred',
        'previous_deferred_at',
        'previous_deferred_by',
        'previous_defer_reason',
        'is_service_item',
        'original_service_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hours' => 'decimal:2',
        'minutes' => 'integer',
        'hourly_rate_used' => 'decimal:2',
        'is_invoiced' => 'boolean',
        'is_finalized' => 'boolean',
        'is_service_item' => 'boolean',
        'approved_at' => 'datetime',
        'invoiced_at' => 'datetime',
        'finalized_at' => 'datetime',
        'invoiced_hours' => 'decimal:2',
        'invoiced_rate' => 'decimal:2',
        'invoiced_modified_at' => 'datetime',
        'was_deferred' => 'boolean',
        'deferred_at' => 'datetime',
        'was_previously_deferred' => 'boolean',
        'previous_deferred_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * User who logged this time entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Company the user belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Project this time was logged for
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Customer this time was logged for
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Milestone this time was logged for (optional)
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'project_milestone_id');
    }

    /**
     * Task this time was logged for (optional)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    /**
     * Subtask this time was logged for (optional)
     */
    // Subtask relationship disabled - subtasks no longer used

    /**
     * User who approved this time entry
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * User who last modified the invoiced data
     */
    public function invoicedModifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invoiced_modified_by');
    }

    /**
     * User who deferred this time entry
     */
    public function deferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deferred_by');
    }

    /**
     * Invoice this time entry is linked to
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Invoice line this time entry is linked to
     */
    public function invoiceLine(): BelongsTo
    {
        return $this->belongsTo(InvoiceLine::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for pending time entries
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved time entries
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected time entries
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for billable time entries
     */
    public function scopeBillable($query)
    {
        return $query->where('is_billable', 'billable');
    }

    /**
     * Scope for non-billable time entries
     */
    public function scopeNonBillable($query)
    {
        return $query->where('is_billable', 'non_billable');
    }

    /**
     * Scope for time entries ready for invoicing
     */
    public function scopeForInvoicing($query)
    {
        return $query->where('status', 'approved')
                    ->where('is_billable', 'billable')
                    ->where('is_invoiced', false);
    }

    /**
     * Scope for monthly report (all time regardless of status)
     */
    public function scopeForMonthlyReport($query, int $month, int $year)
    {
        return $query->whereYear('entry_date', $year)
                    ->whereMonth('entry_date', $month);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get total cost of this time entry
     */
    public function getTotalCostAttribute(): float
    {
        return $this->hours * $this->hourly_rate_used;
    }

    /**
     * Check if time entry is editable
     */
    public function getIsEditableAttribute(): bool
    {
        // Time entry is editable if not invoiced, not finalized, and not in final states
        return !$this->is_invoiced && !$this->is_finalized && !in_array($this->status, ['paid']);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get billable status badge color
     */
    public function getBillableColorAttribute(): string
    {
        return match($this->is_billable) {
            'billable' => 'green',
            'non_billable' => 'red',
            'pending' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Get work item description (milestone/task/subtask)
     */
    public function getWorkItemAttribute(): string
    {
        // Subtasks disabled - start with task level
        if ($this->task) {
            return $this->task->name;
        }
        
        if ($this->milestone) {
            return $this->milestone->name;
        }
        
        return 'General project work';
    }

    /**
     * Get formatted time duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->minutes) {
            $hours = floor($this->minutes / 60);
            $minutes = $this->minutes % 60;
            
            if ($minutes > 0) {
                return "{$hours}h {$minutes}m";
            }
            
            return "{$hours}h";
        }
        
        // Fallback voor oude entries zonder minutes
        $hours = floor($this->hours);
        $minutes = ($this->hours - $hours) * 60;
        
        if ($minutes > 0) {
            return "{$hours}h {$minutes}m";
        }
        
        return "{$hours}h";
    }
    
    /**
     * Get hierarchical work item path
     */
    public function getWorkItemPathAttribute(): string
    {
        $path = [];
        
        if ($this->milestone) {
            $path[] = $this->milestone->name;
        }
        
        if ($this->task) {
            $path[] = $this->task->name;
        }
        
        // Subtasks disabled
        
        return implode(' → ', $path) ?: 'General project work';
    }
    
    /**
     * Scope voor entries van huidige user
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', Auth::id());
    }
    
    /**
     * Scope voor actieve work items (niet completed en deadline niet verstreken)
     */
    public function scopeWithActiveWorkItems($query)
    {
        return $query->whereHas('milestone', function($q) {
            $q->where('status', '!=', 'completed')
              ->where(function($q2) {
                  $q2->whereNull('end_date')
                     ->orWhere('end_date', '>=', now());
              });
        })->orWhereHas('task', function($q) {
            $q->where('status', '!=', 'completed')
              ->where(function($q2) {
                  $q2->whereNull('end_date')
                     ->orWhere('end_date', '>=', now());
              });
        }); // Removed subtask scope - subtasks disabled
    }
    
    /**
     * Boot method voor automatic fields
     */
    protected static function boot()
    {
        parent::boot();
        
        // Bij aanmaken
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->user_id = $model->user_id ?? Auth::id();
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
                
                // Bereken hours van minutes
                if ($model->minutes) {
                    $model->hours = round($model->minutes / 60, 2);
                }
                
                // Zet company_id van project
                if ($model->project_id && !$model->company_id) {
                    $project = Project::find($model->project_id);
                    if ($project) {
                        $model->company_id = $project->company_id;
                        $model->customer_id = $project->customer_id;
                    }
                }
            }
        });
        
        // Bij updaten
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
                
                // Herbereken hours van minutes
                if ($model->minutes) {
                    $model->hours = round($model->minutes / 60, 2);
                }
            }
        });
    }

    // ========================================
    // INVOICED DATA HELPERS
    // ========================================

    /**
     * Get the hours that will be/were invoiced (falls back to actual hours)
     */
    public function getInvoicedHoursAttribute(): float
    {
        return $this->attributes['invoiced_hours'] ?? $this->hours;
    }

    /**
     * Get the rate that will be/was invoiced (falls back to hourly_rate_used)
     */
    public function getInvoicedRateAttribute(): float
    {
        return $this->attributes['invoiced_rate'] ?? $this->hourly_rate_used;
    }

    /**
     * Get the description that will be/was invoiced (falls back to description)
     */
    public function getInvoicedDescriptionDisplayAttribute(): string
    {
        return $this->attributes['invoiced_description'] ?? $this->description;
    }

    /**
     * Check if invoiced data differs from original data
     */
    public function getHasInvoiceModificationsAttribute(): bool
    {
        // Only return true if there are actual differences
        return $this->hours_difference != 0 ||
               $this->rate_difference != 0 ||
               (!is_null($this->attributes['invoiced_description']) &&
                $this->attributes['invoiced_description'] !== $this->description);
    }

    /**
     * Get difference in hours (invoiced - actual)
     */
    public function getHoursDifferenceAttribute(): float
    {
        if (is_null($this->attributes['invoiced_hours'])) {
            return 0;
        }
        return $this->attributes['invoiced_hours'] - $this->hours;
    }

    /**
     * Get difference in rate (invoiced - actual)
     */
    public function getRateDifferenceAttribute(): float
    {
        if (is_null($this->attributes['invoiced_rate'])) {
            return 0;
        }
        return $this->attributes['invoiced_rate'] - $this->hourly_rate_used;
    }

    /**
     * Get total amount difference (invoiced total - actual total)
     */
    public function getAmountDifferenceAttribute(): float
    {
        $actualTotal = $this->hours * $this->hourly_rate_used;
        $invoicedTotal = $this->invoiced_hours * $this->invoiced_rate;
        return $invoicedTotal - $actualTotal;
    }
}