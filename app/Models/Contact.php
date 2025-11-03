<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'company_id',
        'teamleader_id',
        'name',
        'email',
        'phone',
        'position',
        'notes',
        'is_primary',
        'is_active'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Legacy single company relationship (backwards compatibility)
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Many-to-many relationship with companies
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'contact_companies')
            ->withPivot(['is_primary', 'role', 'notes'])
            ->withTimestamps()
            ->orderByPivot('is_primary', 'desc');
    }

    /**
     * Get the primary company for this contact
     */
    public function primaryCompany()
    {
        return $this->companies()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get all activities for this contact
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ContactActivity::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Attributes
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active 
            ? 'bg-green-100 text-green-800' 
            : 'bg-gray-100 text-gray-800';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get full contact info for display
     */
    public function getFullContactInfoAttribute()
    {
        $info = $this->name;
        if ($this->position) {
            $info .= ' - ' . $this->position;
        }
        if ($this->company) {
            $info .= ' (' . $this->company->name . ')';
        }
        return $info;
    }

    /**
     * Check if this contact is the primary contact for the customer
     */
    public function isPrimaryForCustomer(): bool
    {
        return $this->is_primary;
    }

    /**
     * Make this contact the primary contact
     */
    public function makePrimary()
    {
        // Verwijder primary status van andere contacten van deze customer
        self::where('customer_id', $this->customer_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
        
        // Maak deze contact primary
        $this->update(['is_primary' => true]);
    }
}