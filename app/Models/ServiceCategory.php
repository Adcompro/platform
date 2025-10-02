<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'status',
        'sort_order',
        'icon',
        'color',
        'is_active',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    // Relationships

    /**
     * Een service categorie behoort tot een company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Een service categorie heeft meerdere services
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_category_id');
    }

    /**
     * Gebruiker die de categorie heeft aangemaakt
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Gebruiker die de categorie laatst heeft bijgewerkt
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes voor herbruikbare queries

    /**
     * Scope voor actieve categorieën
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * Scope voor categorieën van een specifieke company
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope voor gesorteerde categorieën
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope voor search functionaliteit
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    // Calculated Properties & Helper Methods

    /**
     * CSS class voor status badge
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Aantal services in deze categorie
     */
    public function getServicesCountAttribute(): int
    {
        return $this->services()->count();
    }

    /**
     * Check of categorie verwijderd kan worden
     */
    public function canBeDeleted(): bool
    {
        // Kan alleen verwijderd worden als er geen services aan gekoppeld zijn
        return $this->services()->count() === 0;
    }

    /**
     * Formatteer de creation date
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M j, Y');
    }

    /**
     * Volgende sort order voor nieuwe categorieën
     */
    public static function getNextSortOrder($companyId): int
    {
        return self::where('company_id', $companyId)->max('sort_order') + 1;
    }

    // Boot method voor automatic field filling

    protected static function boot()
    {
        parent::boot();

        // Automatisch company_id, created_by en updated_by instellen
        static::creating(function ($category) {
            if (auth()->check()) {
                $category->company_id = $category->company_id ?? auth()->user()->company_id;
                $category->created_by = auth()->id();
                $category->updated_by = auth()->id();
                
                // Automatisch sort_order instellen als niet opgegeven
                if (!$category->sort_order) {
                    $category->sort_order = self::getNextSortOrder($category->company_id);
                }
            }
        });

        static::updating(function ($category) {
            if (auth()->check()) {
                $category->updated_by = auth()->id();
            }
        });
    }
}