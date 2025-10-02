<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'template_type',
        'color_scheme',
        'primary_color',
        'secondary_color',
        'accent_color',
        'logo_position',
        'logo_path',
        'show_logo',
        'show_header',
        'show_payment_terms',
        'show_bank_details',
        'show_budget_overview',
        'show_additional_costs_section',
        'show_project_details',
        'show_time_entry_details',
        'show_page_numbers',
        'show_footer',
        'show_subtotals',
        'show_tax_details',
        'show_discount_section',
        'show_notes_section',
        'header_content',
        'footer_content',
        'payment_terms_text',
        'font_family',
        'font_size',
        'line_spacing',
        'blade_template',
        'block_positions',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_header' => 'boolean',
        'show_payment_terms' => 'boolean',
        'show_bank_details' => 'boolean',
        'show_budget_overview' => 'boolean',
        'show_additional_costs_section' => 'boolean',
        'show_project_details' => 'boolean',
        'show_time_entry_details' => 'boolean',
        'show_page_numbers' => 'boolean',
        'show_footer' => 'boolean',
        'show_subtotals' => 'boolean',
        'show_tax_details' => 'boolean',
        'show_discount_section' => 'boolean',
        'show_notes_section' => 'boolean',
        'block_positions' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Company that owns this template
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Projects using this template
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Customers using this template
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Invoices using this template
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default template
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get template type display name
     */
    public function getTemplateTypeDisplayAttribute(): string
    {
        return match($this->template_type) {
            'standard' => 'Standard',
            'modern' => 'Modern',
            'classic' => 'Classic',
            'minimal' => 'Minimal',
            default => ucfirst($this->template_type)
        };
    }

    /**
     * Get color scheme class
     */
    public function getColorSchemeClassAttribute(): string
    {
        return match($this->color_scheme) {
            'blue' => 'border-blue-500 bg-blue-50 text-blue-900',
            'green' => 'border-green-500 bg-green-50 text-green-900',
            'red' => 'border-red-500 bg-red-50 text-red-900',
            'purple' => 'border-purple-500 bg-purple-50 text-purple-900',
            'gray' => 'border-gray-500 bg-gray-50 text-gray-900',
            default => 'border-gray-500 bg-gray-50 text-gray-900'
        };
    }

    /**
     * Get font size CSS class
     */
    public function getFontSizeClassAttribute(): string
    {
        return match($this->font_size) {
            'small' => 'text-xs',
            'normal' => 'text-sm',
            'large' => 'text-base',
            default => 'text-sm'
        };
    }

    /**
     * Get line spacing CSS class
     */
    public function getLineSpacingClassAttribute(): string
    {
        return match($this->line_spacing) {
            'compact' => 'leading-tight',
            'normal' => 'leading-normal',
            'relaxed' => 'leading-relaxed',
            default => 'leading-normal'
        };
    }

    /**
     * Generate CSS for this template
     */
    public function generateCss(): string
    {
        $css = '';
        
        // Font family
        if ($this->font_family !== 'Inter') {
            $css .= "body { font-family: '{$this->font_family}', sans-serif; }\n";
        }
        
        return $css;
    }

    /**
     * Set as default template
     */
    public function setAsDefault(): void
    {
        // Remove default from other templates
        self::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Clone template
     */
    public function duplicate(string $newName = null): self
    {
        $clone = $this->replicate();
        $clone->name = $newName ?: $this->name . ' (Copy)';
        $clone->slug = \Str::slug($clone->name) . '-' . uniqid();
        $clone->is_default = false;
        $clone->save();
        
        return $clone;
    }
}