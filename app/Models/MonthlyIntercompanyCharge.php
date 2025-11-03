<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyIntercompanyCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'from_company_id',
        'to_company_id',
        'year',
        'month',
        'billing_method',
        'agreed_amount',
        'actual_hours_worked',
        'actual_hours_value',
        'amount_to_charge',
        'status',
        'invoice_number',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'agreed_amount' => 'decimal:2',
        'actual_hours_worked' => 'decimal:2',
        'actual_hours_value' => 'decimal:2',
        'amount_to_charge' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fromCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    public function toCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    public function getPeriodDisplayAttribute(): string
    {
        return date('M Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }

    public function getEfficiencyPercentageAttribute(): float
    {
        if ($this->actual_hours_value <= 0) {
            return 100;
        }

        return round(($this->amount_to_charge / $this->actual_hours_value) * 100, 1);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'approved' => 'blue',
            'invoiced' => 'yellow',
            'paid' => 'green',
            default => 'gray'
        };
    }
}