<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'project_monthly_fee_id',
        'line_type',
        'source_type',
        'source_id',
        'group_milestone_id',
        'group_task_id',
        'group_subtask_id',
        'description',
        'detailed_description',
        'quantity',
        'unit',
        'unit_price',
        'amount',
        'category',
        'is_billable',
        'defer_to_next_month',
        'is_service_package',
        'service_id',
        'service_color',
        'metadata',
        'unit_price_ex_vat',
        'fee_capped_amount',
        'original_amount',
        'vat_rate',
        'line_total_ex_vat',
        'line_vat_amount',
        'is_merged_line',
        'source_data',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'unit_price_ex_vat' => 'decimal:2',
        'fee_capped_amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'line_total_ex_vat' => 'decimal:2',
        'line_vat_amount' => 'decimal:2',
        'is_billable' => 'boolean',
        'defer_to_next_month' => 'boolean',
        'is_service_package' => 'boolean',
        'is_merged_line' => 'boolean',
        'source_data' => 'array',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Invoice this line belongs to
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Monthly fee this line is related to
     */
    public function monthlyFee(): BelongsTo
    {
        return $this->belongsTo(ProjectMonthlyFee::class, 'project_monthly_fee_id');
    }

    /**
     * Milestone this line is grouped under
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'group_milestone_id');
    }

    /**
     * Task this line is grouped under
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'group_task_id');
    }

    /**
     * Time entries linked to this line
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Monthly additional costs linked to this line
     */
    public function monthlyAdditionalCosts(): HasMany
    {
        return $this->hasMany(ProjectMonthlyAdditionalCost::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for hours lines
     */
    public function scopeHours($query)
    {
        return $query->where('line_type', 'hours');
    }

    /**
     * Scope for service lines
     */
    public function scopeServices($query)
    {
        return $query->where('line_type', 'service');
    }

    /**
     * Scope for milestone lines
     */
    public function scopeMilestones($query)
    {
        return $query->where('line_type', 'milestone');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get line type display name
     */
    public function getLineTypeDisplayAttribute(): string
    {
        return match($this->line_type) {
            'hours' => 'Hours',
            'milestone' => 'Milestone',
            'service' => 'Service',
            'adjustment' => 'Adjustment',
            'custom' => 'Custom',
            'budget_adjustment' => 'Budget Adjustment',
            default => ucfirst($this->line_type)
        };
    }

    /**
     * Calculate VAT amount
     */
    public function calculateVatAmount(): float
    {
        return $this->line_total_ex_vat * ($this->vat_rate / 100);
    }

    /**
     * Get line total (alias for line_total_ex_vat for backwards compatibility)
     */
    public function getLineTotalAttribute(): float
    {
        return $this->line_total_ex_vat ?? 0;
    }

    /**
     * Get total including VAT
     */
    public function getTotalIncVatAttribute(): float
    {
        return $this->line_total_ex_vat + $this->line_vat_amount;
    }

    /**
     * Check if line has fee cap applied
     */
    public function hasFeeCap(): bool
    {
        return $this->fee_capped_amount !== null && 
               $this->original_amount !== null && 
               abs($this->fee_capped_amount - $this->original_amount) > 0.01;
    }

    /**
     * Get fee cap savings
     */
    public function getFeeCappedSavingsAttribute(): float
    {
        if (!$this->hasFeeCap()) {
            return 0;
        }

        return $this->original_amount - $this->fee_capped_amount;
    }

    /**
     * Update VAT amount based on current rate and total
     */
    public function updateVatAmount(): void
    {
        $this->line_vat_amount = $this->calculateVatAmount();
        $this->save();
    }
}

// ============================================================================
// LAATSTE MODELS (inline)
// ============================================================================

// MonthlyIntercompanyCharge Model
class MonthlyIntercompanyCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'from_company_id', 'to_company_id',
        'year', 'month', 'billing_method', 'agreed_amount',
        'actual_hours_worked', 'actual_hours_value', 'amount_to_charge',
        'status', 'invoice_number', 'notes'
    ];

    protected $casts = [
        'year' => 'integer', 'month' => 'integer',
        'agreed_amount' => 'decimal:2', 'actual_hours_worked' => 'decimal:2',
        'actual_hours_value' => 'decimal:2', 'amount_to_charge' => 'decimal:2'
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function fromCompany(): BelongsTo { return $this->belongsTo(Company::class, 'from_company_id'); }
    public function toCompany(): BelongsTo { return $this->belongsTo(Company::class, 'to_company_id'); }
    
    public function getPeriodDisplayAttribute(): string
    {
        return date('M Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}

// InvoiceDraftAction Model  
class InvoiceDraftAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'user_id', 'action',
        'details', 'old_value', 'new_value'
    ];

    protected $casts = [
        'details' => 'array',
        'old_value' => 'array', 
        'new_value' => 'array'
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    
    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            'created' => 'Created',
            'line_added' => 'Line Added',
            'line_removed' => 'Line Removed', 
            'line_merged' => 'Lines Merged',
            'description_changed' => 'Description Changed',
            'amount_adjusted' => 'Amount Adjusted',
            'finalized' => 'Finalized',
            default => ucfirst(str_replace('_', ' ', $this->action))
        };
    }
}