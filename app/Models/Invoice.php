<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'invoicing_company_id',
        'invoice_template_id',
        'customer_id',
        'invoice_number',
        'status',
        'is_editable',
        'draft_name',
        'notes',
        'invoice_date',
        'due_date',
        'period_start',
        'period_end',
        'billing_type',
        'created_by',
        'vat_rate',
        'previous_month_remaining',
        'monthly_budget',
        'total_budget',
        'next_month_rollover',
        'work_amount',
        'service_amount',
        'additional_costs',
        'subtotal',
        'total_amount',
        'subtotal_ex_vat',
        'vat_amount',
        'total_inc_vat',
        'finalized_by',
        'finalized_at',
        'sent_at',
        'paid_at',
        'paid_amount',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'vat_rate' => 'decimal:2',
        'previous_month_remaining' => 'decimal:2',
        'monthly_budget' => 'decimal:2',
        'total_budget' => 'decimal:2',
        'next_month_rollover' => 'decimal:2',
        'work_amount' => 'decimal:2',
        'service_amount' => 'decimal:2',
        'additional_costs' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'subtotal_ex_vat' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_inc_vat' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'finalized_at' => 'datetime',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Project this invoice belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Company that generated this invoice
     */
    public function invoicingCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'invoicing_company_id');
    }

    /**
     * Customer this invoice is for
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Template used for this invoice
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    /**
     * User who created this invoice
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who finalized this invoice
     */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * User who approved this invoice (alias for finalizer)
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * Lines on this invoice
     */
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort_order');
    }

    /**
     * Time entries linked to this invoice
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Monthly additional costs linked to this invoice
     */
    public function monthlyAdditionalCosts(): HasMany
    {
        return $this->hasMany(ProjectMonthlyAdditionalCost::class);
    }

    /**
     * Draft actions performed on this invoice
     */
    public function draftActions(): HasMany
    {
        return $this->hasMany(InvoiceDraftAction::class)->orderBy('created_at');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for draft invoices
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for finalized invoices
     */
    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }

    /**
     * Scope for sent invoices
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'finalized' => 'Finalized',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'finalized' => 'blue',
            'sent' => 'yellow',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get period display
     */
    public function getPeriodDisplayAttribute(): string
    {
        if ($this->period_start->format('Y-m') === $this->period_end->format('Y-m')) {
            return $this->period_start->format('M Y');
        }
        
        return $this->period_start->format('M j') . ' - ' . $this->period_end->format('M j, Y');
    }

    /**
     * Get display name for draft invoices
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->invoice_number) {
            return $this->invoice_number;
        }
        
        return $this->draft_name ?? "Draft Invoice #{$this->id}";
    }

    /**
     * Check if invoice can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft' && $this->is_editable;
    }

    /**
     * Check if invoice can be finalized
     */
    public function canBeFinalized(): bool
    {
        return $this->status === 'draft' && 
               $this->lines()->count() > 0 &&
               $this->subtotal_ex_vat > 0;
    }

    /**
     * Recalculate totals from lines
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->lines()->sum('line_total_ex_vat');
        $vatAmount = $this->lines()->sum('line_vat_amount');
        $total = $subtotal + $vatAmount;

        $this->update([
            'subtotal_ex_vat' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_inc_vat' => $total,
        ]);
    }

    /**
     * Get total hours invoiced
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->lines()
            ->where('line_type', 'hours')
            ->sum('quantity');
    }

    /**
     * Get average hourly rate
     */
    public function getAverageHourlyRateAttribute(): float
    {
        $totalHours = $this->total_hours;
        
        if ($totalHours <= 0) {
            return 0;
        }

        $hoursTotal = $this->lines()
            ->where('line_type', 'hours')
            ->sum('line_total_ex_vat');

        return $hoursTotal / $totalHours;
    }
}