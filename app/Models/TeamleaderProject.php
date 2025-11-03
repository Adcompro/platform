<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamleaderProject extends Model
{
    protected $fillable = [
        'teamleader_id',
        'teamleader_company_id',
        'customer_id',
        'imported_as_project_id',
        'title',
        'description',
        'status',
        'starts_on',
        'due_on',
        'budget_amount',
        'budget_currency',
        'raw_data',
        'is_imported',
        'synced_at',
        'imported_at',
    ];

    protected $casts = [
        'raw_data' => 'json',
        'is_imported' => 'boolean',
        'starts_on' => 'date',
        'due_on' => 'date',
        'synced_at' => 'datetime',
        'imported_at' => 'datetime',
        'budget_amount' => 'decimal:2',
    ];

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Imported project relationship
     */
    public function importedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'imported_as_project_id');
    }

    /**
     * Scope: Only not imported yet
     */
    public function scopeNotImported($query)
    {
        return $query->where('is_imported', false);
    }

    /**
     * Scope: Only imported
     */
    public function scopeImported($query)
    {
        return $query->where('is_imported', true);
    }

    /**
     * Scope: By customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: By Teamleader company
     */
    public function scopeByTeamleaderCompany($query, $companyId)
    {
        return $query->where('teamleader_company_id', $companyId);
    }

    /**
     * Scope: Active status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'on_hold' => 'bg-yellow-100 text-yellow-800',
            'done' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get formatted budget
     */
    public function getFormattedBudgetAttribute(): string
    {
        if (!$this->budget_amount) {
            return 'N/A';
        }

        return $this->budget_currency . ' ' . number_format($this->budget_amount, 2, ',', '.');
    }

    /**
     * Check if project can be imported
     */
    public function canBeImported(): bool
    {
        return !$this->is_imported && $this->customer_id !== null;
    }
}
