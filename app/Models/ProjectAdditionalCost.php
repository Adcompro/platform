<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectAdditionalCost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'created_by',
        'name',
        'description',
        'cost_type',
        'fee_type',
        'amount',
        'hours',
        'hourly_rate',
        'quantity',
        'unit',
        'calculation_type',
        'start_date',
        'end_date',
        'recurring_day_of_month',
        'monthly_variations',
        'is_active',
        'category',
        'vendor',
        'reference',
        'auto_invoice',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
        'auto_invoice' => 'boolean',
        'monthly_variations' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Cost type opties
     */
    const COST_TYPES = [
        'one_time' => 'One-time Cost',
        'monthly_recurring' => 'Monthly Recurring',
    ];

    /**
     * Calculation type opties
     */
    const CALCULATION_TYPES = [
        'fixed_amount' => 'Fixed Amount',
        'hourly_rate' => 'Hourly Rate (Hours × Rate)',
        'quantity_based' => 'Quantity Based (Qty × Unit Price)',
    ];

    /**
     * Unit opties
     */
    const UNITS = [
        'hours' => 'Hours',
        'pieces' => 'Pieces',
        'euros' => 'Euros',
        'clicks' => 'Clicks',
        'impressions' => 'Impressions (per 1000)',
        'users' => 'Users',
        'other' => 'Other',
    ];

    /**
     * Category opties
     */
    const CATEGORIES = [
        'hosting' => 'Hosting',
        'software' => 'Software',
        'licenses' => 'Licenses',
        'services' => 'Services',
        'other' => 'Other',
    ];

    /**
     * Fee type opties
     */
    const FEE_TYPES = [
        'in_fee' => 'Within Budget',
        'additional' => 'Additional Cost',
    ];

    /**
     * Relationships
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOneTime($query)
    {
        return $query->where('cost_type', 'one_time');
    }

    public function scopeRecurring($query)
    {
        return $query->where('cost_type', 'monthly_recurring');
    }

    public function scopeInFee($query)
    {
        return $query->where('fee_type', 'in_fee');
    }

    public function scopeAdditional($query)
    {
        return $query->where('fee_type', 'additional');
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('start_date', $year)
                    ->whereMonth('start_date', $month);
    }

    /**
     * Bereken het bedrag voor een specifieke maand
     */
    public function getAmountForMonth($year, $month)
    {
        $monthKey = sprintf('%04d-%02d', $year, $month);

        // Check of er een monthly variation is voor deze maand
        if ($this->monthly_variations && isset($this->monthly_variations[$monthKey])) {
            $variation = $this->monthly_variations[$monthKey];

            // Als er een specific amount is, gebruik die
            if (isset($variation['amount'])) {
                return (float) $variation['amount'];
            }

            // Anders bereken op basis van variation data
            if (isset($variation['hours']) && $this->hourly_rate) {
                return $variation['hours'] * $this->hourly_rate;
            }

            if (isset($variation['quantity']) && $this->amount) {
                return $variation['quantity'] * $this->amount;
            }
        }

        // Geen variation? Gebruik standaard berekening
        return $this->calculateAmount();
    }

    /**
     * Bereken standaard bedrag op basis van calculation_type
     */
    public function calculateAmount()
    {
        switch ($this->calculation_type) {
            case 'hourly_rate':
                return ($this->hours ?? 0) * ($this->hourly_rate ?? 0);

            case 'quantity_based':
                return ($this->quantity ?? 0) * ($this->amount ?? 0);

            case 'fixed_amount':
            default:
                return $this->amount ?? 0;
        }
    }

    /**
     * Check of deze cost actief is in een specifieke maand
     */
    public function isActiveInMonth($year, $month)
    {
        if (!$this->is_active) {
            return false;
        }

        $checkDate = Carbon::create($year, $month, 1);

        // Start date check
        if ($this->start_date && $checkDate->lt($this->start_date)) {
            return false;
        }

        // End date check (voor recurring costs)
        if ($this->end_date && $checkDate->gt($this->end_date)) {
            return false;
        }

        // One-time costs: alleen in start maand
        if ($this->cost_type === 'one_time') {
            return $checkDate->isSameMonth($this->start_date);
        }

        return true;
    }

    /**
     * Get description voor factuur
     */
    public function getInvoiceDescription($year = null, $month = null)
    {
        $description = $this->name;

        if ($this->description) {
            $description .= "\n" . $this->description;
        }

        // Voeg calculation details toe
        switch ($this->calculation_type) {
            case 'hourly_rate':
                $hours = $this->hours;
                $rate = $this->hourly_rate;

                // Check monthly variation
                if ($year && $month && $this->monthly_variations) {
                    $monthKey = sprintf('%04d-%02d', $year, $month);
                    if (isset($this->monthly_variations[$monthKey]['hours'])) {
                        $hours = $this->monthly_variations[$monthKey]['hours'];
                    }
                }

                $description .= "\n({$hours} hours × €" . number_format($rate, 2) . ")";
                break;

            case 'quantity_based':
                $qty = $this->quantity;

                // Check monthly variation
                if ($year && $month && $this->monthly_variations) {
                    $monthKey = sprintf('%04d-%02d', $year, $month);
                    if (isset($this->monthly_variations[$monthKey]['quantity'])) {
                        $qty = $this->monthly_variations[$monthKey]['quantity'];
                    }
                }

                $description .= "\n({$qty} {$this->unit} × €" . number_format($this->amount, 2) . ")";
                break;
        }

        return $description;
    }

    /**
     * Get alle maanden met variations
     */
    public function getMonthsWithVariations()
    {
        if (!$this->monthly_variations) {
            return collect([]);
        }

        $months = [];
        foreach ($this->monthly_variations as $monthKey => $data) {
            [$year, $month] = explode('-', $monthKey);
            $months[] = [
                'year' => (int) $year,
                'month' => (int) $month,
                'month_key' => $monthKey,
                'data' => $data,
                'calculated_amount' => $data['amount'] ?? $this->calculateVariationAmount($data),
            ];
        }

        return collect($months)->sortBy('month_key');
    }

    /**
     * Set variation voor een specifieke maand
     */
    public function setMonthlyVariation($year, $month, array $data)
    {
        $monthKey = sprintf('%04d-%02d', $year, $month);
        $variations = $this->monthly_variations ?? [];
        $variations[$monthKey] = $data;

        $this->update(['monthly_variations' => $variations]);
    }

    /**
     * Remove variation voor een specifieke maand
     */
    public function removeMonthlyVariation($year, $month)
    {
        $monthKey = sprintf('%04d-%02d', $year, $month);
        $variations = $this->monthly_variations ?? [];

        if (isset($variations[$monthKey])) {
            unset($variations[$monthKey]);
            $this->update(['monthly_variations' => $variations]);
        }
    }

    /**
     * Bereken variation amount
     */
    private function calculateVariationAmount(array $variation)
    {
        if (isset($variation['amount'])) {
            return (float) $variation['amount'];
        }

        if (isset($variation['hours']) && $this->hourly_rate) {
            return $variation['hours'] * $this->hourly_rate;
        }

        if (isset($variation['quantity']) && $this->amount) {
            return $variation['quantity'] * $this->amount;
        }

        return $this->calculateAmount();
    }

    /**
     * Attributes
     */
    public function getStatusBadgeClassAttribute()
    {
        if (!$this->is_active) {
            return 'bg-gray-100 text-gray-800';
        }
        return 'bg-green-100 text-green-800';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getFeeTypeBadgeClassAttribute()
    {
        return $this->fee_type === 'in_fee'
            ? 'bg-green-100 text-green-800'
            : 'bg-orange-100 text-orange-800';
    }

    public function getCostTypeBadgeClassAttribute()
    {
        return $this->cost_type === 'one_time'
            ? 'bg-purple-100 text-purple-800'
            : 'bg-indigo-100 text-indigo-800';
    }

    public function getCalculationTypeLabelAttribute()
    {
        return self::CALCULATION_TYPES[$this->calculation_type] ?? 'Unknown';
    }

    /**
     * Methods
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function canBeDeleted(): bool
    {
        // Check of deze cost al gefactureerd is
        // Als er invoice_lines zijn die naar deze cost verwijzen, kan het niet verwijderd worden
        $isInvoiced = \DB::table('invoice_lines')
            ->where('source_type', 'additional_cost')
            ->where('source_id', $this->id)
            ->exists();

        return !$isInvoiced;
    }

    public function canBeEdited(): bool
    {
        // Check of deze cost al gefactureerd is
        // Als er invoice_lines zijn die naar deze cost verwijzen, kan het niet bewerkt worden
        $isInvoiced = \DB::table('invoice_lines')
            ->where('source_type', 'additional_cost')
            ->where('source_id', $this->id)
            ->exists();

        return !$isInvoiced;
    }
}
