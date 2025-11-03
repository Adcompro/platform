<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectMonthlyFee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'year',
        'month',
        'period_start',
        'period_end',
        'base_monthly_fee',
        'rollover_from_previous',
        'total_available_fee',
        'hours_worked',
        'hours_value',
        'amount_invoiced_from_fee',
        'additional_costs_in_fee',
        'additional_costs_outside_fee',
        'total_invoiced',
        'rollover_to_next',
        'is_finalized',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'base_monthly_fee' => 'decimal:2',
        'rollover_from_previous' => 'decimal:2',
        'total_available_fee' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'hours_value' => 'decimal:2',
        'amount_invoiced_from_fee' => 'decimal:2',
        'additional_costs_in_fee' => 'decimal:2',
        'additional_costs_outside_fee' => 'decimal:2',
        'total_invoiced' => 'decimal:2',
        'rollover_to_next' => 'decimal:2',
        'is_finalized' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Status wordt nu met is_finalized boolean bijgehouden

    /**
     * Relationships
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Geen user tracking in deze tabel structuur

    /**
     * Scopes
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_finalized', true);
    }

    public function scopeNotFinalized($query)
    {
        return $query->where('is_finalized', false);
    }

    public function scopeOverBudget($query)
    {
        return $query->whereRaw('total_invoiced > total_available_fee');
    }

    /**
     * Attributes
     */
    public function getMonthNameAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F');
    }

    public function getPeriodLabelAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    public function getBudgetPercentageUsedAttribute()
    {
        if ($this->total_available_fee <= 0) {
            return 0;
        }
        return min(round(($this->amount_invoiced_from_fee / $this->total_available_fee) * 100, 1), 100);
    }

    public function getIsOverBudgetAttribute()
    {
        return $this->total_invoiced > $this->total_available_fee;
    }
    
    public function getBudgetExceededAttribute()
    {
        return max(0, $this->total_invoiced - $this->total_available_fee);
    }
    
    public function getBudgetRemainingAttribute()
    {
        return max(0, $this->total_available_fee - $this->total_invoiced);
    }

    public function getStatusBadgeClassAttribute()
    {
        if ($this->is_finalized) {
            return 'bg-purple-100 text-purple-800';
        }
        return 'bg-gray-100 text-gray-800';
    }

    public function getBudgetStatusBadgeClassAttribute()
    {
        if ($this->budget_exceeded > 0) {
            return 'bg-red-100 text-red-800';
        } elseif ($this->getBudgetPercentageUsedAttribute() > 80) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }

    /**
     * Methods
     */
    public function canBeEdited(): bool
    {
        return !$this->is_finalized;
    }

    public function canBeFinalized(): bool
    {
        return !$this->is_finalized;
    }

    /**
     * Finalize this record (after invoicing)
     */
    public function finalize(): void
    {
        $this->is_finalized = true;
        $this->save();
    }

    /**
     * Get or create monthly fee for a specific period
     */
    public static function getOrCreateForPeriod(Project $project, int $year, int $month): self
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return self::firstOrCreate(
            [
                'project_id' => $project->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'period_start' => $startDate,
                'period_end' => $endDate,
                'base_monthly_fee' => $project->monthly_fee ?? 0,
                'total_available_fee' => $project->monthly_fee ?? 0,
                'is_finalized' => false,
            ]
        );
    }

    /**
     * Get the previous month's record
     */
    public function getPreviousMonth(): ?self
    {
        $previousDate = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        
        return self::where('project_id', $this->project_id)
            ->where('year', $previousDate->year)
            ->where('month', $previousDate->month)
            ->first();
    }

    /**
     * Get the next month's record
     */
    public function getNextMonth(): ?self
    {
        $nextDate = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        
        return self::where('project_id', $this->project_id)
            ->where('year', $nextDate->year)
            ->where('month', $nextDate->month)
            ->first();
    }
}