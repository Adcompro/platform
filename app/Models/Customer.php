<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;  // ← UITGESCHAKELD
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasFactory;
    // use SoftDeletes;  // ← UITGESCHAKELD

    protected $fillable = [
        'teamleader_id',
        'company_id',
        'invoice_template_id',
        'name',
        'email',
        'phone',
        'address',
        'street',
        'addition',
        'zip_code',
        'city',
        'country',
        'language',
        'contact_person',
        'company',
        'notes',
        'status',
        'start_date',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // 'deleted_at' => 'datetime'  // ← UITGESCHAKELD
    ];

    /**
     * Relationships
     */
    public function companyRelation(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    
    /**
     * Many-to-many relationship with companies (managing companies)
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'customer_companies')
            ->withPivot(['is_primary', 'role', 'notes'])
            ->withTimestamps()
            ->orderByPivot('is_primary', 'desc');
    }
    
    /**
     * Get the primary managing company
     */
    public function primaryCompany()
    {
        return $this->companies()->wherePivot('is_primary', true)->first();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all invoices for this customer
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Invoice template preference for this customer
     */
    public function invoiceTemplate(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    /**
     * Get all contacts for this customer
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get primary contact for this customer
     */
    public function primaryContact(): HasOne
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    /**
     * Get all activities for this customer
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CustomerActivity::class);
    }

    /**
     * Scopes voor herbruikbare queries
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%')
              ->orWhere('company', 'like', '%' . $search . '%')
              ->orWhere('contact_person', 'like', '%' . $search . '%');
        });
    }

    /**
     * Calculated properties
     */
    public function getTotalProjectsAttribute()
    {
        return $this->projects()->count();
    }

    public function getActiveProjectsAttribute()
    {
        return $this->projects()->where('status', 'active')->count();
    }

    public function getTotalProjectValueAttribute()
    {
        return $this->projects()->sum('total_value') ?? 0;
    }

    public function getMonthlyRecurringValueAttribute()
    {
        return $this->projects()->whereNotNull('monthly_fee')->sum('monthly_fee') ?? 0;
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'active' 
            ? 'bg-green-100 text-green-800' 
            : 'bg-red-100 text-red-800';
    }

    public function getStatusIconAttribute()
    {
        return $this->status === 'active' ? '✓' : '✗';
    }

    /**
     * Get formatted full address
     */
    public function getFormattedAddressAttribute(): string
    {
        // Build street with addition if present
        $streetPart = $this->street;
        if ($this->addition) {
            $streetPart .= ' ' . $this->addition;
        }
        
        $parts = array_filter([
            $streetPart,
            trim($this->zip_code . ' ' . $this->city),
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get short address (street + city)
     */
    public function getShortAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->city
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Business logic methods
     */
    public function canBeDeleted(): bool
    {
        return $this->projects()->count() === 0;
    }

    public function hasActiveProjects(): bool
    {
        return $this->projects()->where('status', 'active')->exists();
    }

    public function getRecentActivity($limit = 5)
    {
        return $this->projects()
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages(): array
    {
        return [
            'nl' => 'Nederlands',
            'en' => 'English',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'es' => 'Español',
            'it' => 'Italiano'
        ];
    }

    /**
     * Get language display name
     */
    public function getLanguageNameAttribute(): string
    {
        $languages = self::getAvailableLanguages();
        return $languages[$this->language] ?? 'Nederlands';
    }

    /**
     * Get language flag emoji
     */
    public function getLanguageFlagAttribute(): string
    {
        return match($this->language) {
            'nl' => '🇳🇱',
            'en' => '🇬🇧',
            'fr' => '🇫🇷',
            'de' => '🇩🇪',
            'es' => '🇪🇸',
            'it' => '🇮🇹',
            default => '🇳🇱'
        };
    }
}