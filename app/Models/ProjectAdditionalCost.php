<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'start_date',
        'end_date',
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
        'is_active' => 'boolean',
        'auto_invoice' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Cost type opties - based on actual database enum
     */
    const COST_TYPES = [
        'one_time' => 'One-time Cost',
        'monthly_recurring' => 'Monthly Recurring',
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
     * Fee type opties - based on actual database enum
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
        // TODO: Enable when Invoice model is created
        // return $this->belongsTo(Invoice::class);
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
            ? 'bg-blue-100 text-blue-800' 
            : 'bg-orange-100 text-orange-800';
    }

    public function getCostTypeBadgeClassAttribute()
    {
        return $this->cost_type === 'one_time' 
            ? 'bg-purple-100 text-purple-800' 
            : 'bg-indigo-100 text-indigo-800';
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
        // Can delete if not active
        return !$this->is_active;
    }

    public function canBeEdited(): bool
    {
        // Can edit if active
        return $this->is_active;
    }
}