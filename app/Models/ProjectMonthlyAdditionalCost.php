<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectMonthlyAdditionalCost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'monthly_amount',
        'start_date',
        'end_date',
        'fee_type',
        'cost_type',
        'supplier',
        'is_billable',
        'is_active',
        'billing_day',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_amount' => 'decimal:2',
        'is_billable' => 'boolean',
        'is_active' => 'boolean',
        'billing_day' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Cost type opties voor terugkerende kosten
     */
    const COST_TYPES = [
        'hosting' => 'Hosting',
        'license' => 'Software License',
        'subscription' => 'Subscription',
        'maintenance' => 'Maintenance',
        'other' => 'Other',
    ];

    /**
     * Fee type opties
     */
    const FEE_TYPES = [
        'in_fee' => 'Within Budget',
        'extended' => 'Additional Cost',
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

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    public function scopeInFee($query)
    {
        return $query->where('fee_type', 'in_fee');
    }

    public function scopeExtended($query)
    {
        return $query->where('fee_type', 'extended');
    }

    public function scopeActiveInMonth($query, $year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
        
        return $query->where('is_active', true)
                    ->where('start_date', '<=', $endOfMonth)
                    ->where(function($q) use ($startOfMonth) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $startOfMonth);
                    });
    }

    /**
     * Attributes
     */
    public function getStatusBadgeClassAttribute()
    {
        if (!$this->is_active) {
            return 'bg-gray-100 text-gray-800';
        }
        if ($this->end_date && $this->end_date->isPast()) {
            return 'bg-red-100 text-red-800';
        }
        return 'bg-green-100 text-green-800';
    }

    public function getStatusTextAttribute()
    {
        if (!$this->is_active) {
            return 'Inactive';
        }
        if ($this->end_date && $this->end_date->isPast()) {
            return 'Expired';
        }
        return 'Active';
    }

    public function getFeeTypeBadgeClassAttribute()
    {
        return $this->fee_type === 'in_fee' 
            ? 'bg-blue-100 text-blue-800' 
            : 'bg-orange-100 text-orange-800';
    }

    public function getDurationTextAttribute()
    {
        if (!$this->end_date) {
            return 'Ongoing since ' . $this->start_date->format('M Y');
        }
        
        $months = $this->start_date->diffInMonths($this->end_date) + 1;
        return $months . ' month' . ($months > 1 ? 's' : '');
    }

    /**
     * Methods
     */
    public function isActiveInMonth($year, $month): bool
    {
        $date = Carbon::create($year, $month, 1);
        
        if (!$this->is_active) {
            return false;
        }
        
        if ($this->start_date->greaterThan($date->endOfMonth())) {
            return false;
        }
        
        if ($this->end_date && $this->end_date->lessThan($date->startOfMonth())) {
            return false;
        }
        
        return true;
    }

    public function getAmountForMonth($year, $month): float
    {
        if (!$this->isActiveInMonth($year, $month)) {
            return 0;
        }
        
        return $this->monthly_amount;
    }

    public function canBeDeleted(): bool
    {
        // Implementeer logica voor wanneer een recurring cost verwijderd mag worden
        return true;
    }

    public function canBeEdited(): bool
    {
        // Implementeer logica voor wanneer een recurring cost bewerkt mag worden
        return $this->is_active;
    }
}