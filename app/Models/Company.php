<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'vat_number',
        'address',
        'street',
        'house_number',
        'addition',
        'postal_code',
        'city',
        'country',
        'email',
        'phone',
        'website',
        'default_hourly_rate',
        'is_main_invoicing',
        'bank_details',
        'invoice_settings',
        'is_active',
    ];

    protected $casts = [
        'default_hourly_rate' => 'decimal:2',
        'is_main_invoicing' => 'boolean',
        'is_active' => 'boolean',
        'bank_details' => 'json',
        'invoice_settings' => 'json',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // FIXED: Customers relationship via company_id foreign key
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'company_id');
    }
    
    /**
     * Many-to-many relationship with customers (managed customers)
     */
    public function managedCustomers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_companies')
            ->withPivot(['is_primary', 'role', 'notes'])
            ->withTimestamps()
            ->orderByPivot('is_primary', 'desc');
    }

    // Projects relationship
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'company_id');
    }

    /**
     * Get all activities for this company
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CompanyActivity::class);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    public function canBeDeletedBy($user)
    {
        return true;
    }

    public function getMonthlyRevenueAttribute()
    {
        // Bereken revenue via customers en hun invoices
        return $this->customers->sum(function($customer) {
            return $customer->total_revenue ?? 0;
        });
    }

    public function getStatusAttribute()
    {
        return $this->is_active ? 'active' : 'inactive';
    }
    
    /**
     * Get formatted full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [];
        
        // Street line
        if ($this->street) {
            $streetLine = $this->street;
            if ($this->house_number) {
                $streetLine .= ' ' . $this->house_number;
                if ($this->addition) {
                    $streetLine .= '-' . $this->addition;
                }
            }
            $parts[] = $streetLine;
        }
        
        // Postal code and city line
        if ($this->postal_code || $this->city) {
            $cityLine = '';
            if ($this->postal_code) {
                $cityLine .= $this->postal_code;
            }
            if ($this->city) {
                $cityLine .= ($cityLine ? ' ' : '') . $this->city;
            }
            $parts[] = $cityLine;
        }
        
        // Country (only if not Netherlands)
        if ($this->country && $this->country !== 'Netherlands') {
            $parts[] = $this->country;
        }
        
        return implode("\n", $parts);
    }

    /**
     * NIEUW: Get active customers count
     */
    public function getActiveCustomersCountAttribute(): int
    {
        return $this->customers()->active()->count();
    }

    /**
     * NIEUW: Get total customers count
     */
    public function getTotalCustomersCountAttribute(): int
    {
        return $this->customers()->count();
    }
}