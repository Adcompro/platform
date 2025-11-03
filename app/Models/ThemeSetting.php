<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ThemeSetting extends Model
{
    protected $fillable = [
        'brand_name',
        'logo_path',
        'favicon_path',
        'primary_color',
        'primary_hover',
        'primary_text',
        'secondary_color',
        'secondary_hover',
        'secondary_border',
        'accent_color',
        'accent_hover',
        // Button colors
        'button_primary_bg',
        'button_primary_hover',
        'button_primary_text',
        'button_secondary_bg',
        'button_secondary_hover',
        'button_secondary_text',
        'button_secondary_border',
        'button_danger_bg',
        'button_danger_hover',
        'button_success_bg',
        'button_success_hover',
        // Link colors
        'link_color',
        'link_hover',
        'link_visited',
        // Other fields
        'text_primary',
        'text_secondary',
        'text_muted',
        'font_family',
        'font_size_base',
        'font_size_small',
        'font_size_large',
        'font_size_xl',
        // Font weight settings
        'font_weight_normal',
        'font_weight_medium',
        'font_weight_semibold',
        'font_weight_bold',
        // Spacing settings
        'letter_spacing_tight',
        'letter_spacing_normal',
        'letter_spacing_wide',
        'line_height_tight',
        'line_height_normal',
        'line_height_loose',
        'spacing_xs',
        'spacing_sm',
        'spacing_md',
        'spacing_lg',
        'spacing_xl',
        'header_padding',
        'card_padding',
        'button_padding',
        'border_radius',
        'use_shadows',
        'use_gradients',
        'use_animations',
        'transition_speed',
        'badge_bg_color',
        'badge_text_color',
        'success_color',
        'warning_color',
        'danger_color',
        'info_color',
        'table_header_bg',
        'table_row_hover',
        'table_border_color',
        'custom_css',
        'theme_preset',
        'is_active',
    ];

    protected $casts = [
        'use_shadows' => 'boolean',
        'use_gradients' => 'boolean',
        'use_animations' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get de actieve theme settings
     */
    public static function active()
    {
        return Cache::remember('active_theme', 3600, function () {
            return self::where('is_active', true)->first() ?? self::first() ?? self::createDefault();
        });
    }

    /**
     * Create default theme als er geen bestaat
     */
    public static function createDefault()
    {
        return self::create([
            'brand_name' => 'Progress Communications',
            'theme_preset' => 'progress',
            'is_active' => true,
            // Modern Progress Communications styling based on website
            'primary_color' => '#2563eb',      // Modern blue primary
            'primary_hover' => '#1d4ed8',      // Darker blue hover
            'primary_text' => '#ffffff',       // White text on blue
            'secondary_color' => '#f8fafc',    // Light gray background
            'secondary_hover' => '#f1f5f9',    // Slightly darker hover
            'secondary_border' => '#e2e8f0',   // Subtle border
            'accent_color' => '#059669',       // Green accent
            'accent_hover' => '#047857',       // Darker green hover
            // Button colors - modern Progress Communications style
            'button_primary_bg' => '#2563eb',      // Modern blue
            'button_primary_hover' => '#1d4ed8',   // Darker blue hover
            'button_primary_text' => '#ffffff',    // White text
            'button_secondary_bg' => '#f8fafc',    // Light background
            'button_secondary_hover' => '#e2e8f0', // Light hover
            'button_secondary_text' => '#475569',  // Dark gray text
            'button_secondary_border' => '#cbd5e1', // Subtle border
            'button_danger_bg' => '#dc2626',       // Modern red
            'button_danger_hover' => '#b91c1c',    // Darker red hover
            'button_success_bg' => '#059669',      // Green success
            'button_success_hover' => '#047857',   // Darker green hover
            // Link colors - modern Progress Communications style
            'link_color' => '#2563eb',         // Blue links matching primary
            'link_hover' => '#1d4ed8',         // Darker blue hover
            'link_visited' => '#7c3aed',       // Purple for visited links
            // Modern text colors with better contrast
            'text_primary' => '#1e293b',       // Darker primary text
            'text_secondary' => '#475569',     // Medium gray
            'text_muted' => '#64748b',         // Muted but readable
            'font_family' => 'Inter, system-ui, sans-serif',
            'font_size_base' => '13px',
            'font_size_small' => '11px',
            'font_size_large' => '15px',
            'font_size_xl' => '24px',
            // Font weight defaults
            'font_weight_normal' => '400',
            'font_weight_medium' => '500',
            'font_weight_semibold' => '600',
            'font_weight_bold' => '700',
            // Spacing defaults
            'letter_spacing_tight' => '-0.025em',
            'letter_spacing_normal' => '0',
            'letter_spacing_wide' => '0.025em',
            'line_height_tight' => '1.25',
            'line_height_normal' => '1.5',
            'line_height_loose' => '1.75',
            'spacing_xs' => '0.25rem',
            'spacing_sm' => '0.5rem',
            'spacing_md' => '1rem',
            'spacing_lg' => '1.5rem',
            'spacing_xl' => '2rem',
            'badge_bg_color' => '#68757a',
            'badge_text_color' => '#ffffff',
            'success_color' => '#10b981',
            'warning_color' => '#f59e0b',
            'danger_color' => '#ef4444',
            'info_color' => '#3b82f6',
            'table_header_bg' => '#f9fafb',
            'table_row_hover' => '#f3f4f6',
            'table_border_color' => '#e5e7eb',
            // Modern Progress Communications styling preferences
            'use_shadows' => true,             // Subtle shadows for depth
            'use_gradients' => false,          // Clean, flat design
            'use_animations' => true,          // Smooth transitions
            'transition_speed' => '200ms',     // Quick, professional transitions
            'border_radius' => '0.5rem',       // Modern rounded corners
            'header_padding' => 'py-6',        // More spacious headers
            'card_padding' => 'p-6',           // More generous card padding
            'button_padding' => 'px-6 py-3',   // Larger, more accessible buttons
        ]);
    }

    /**
     * Clear theme cache wanneer settings worden geupdate
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::forget('active_theme');
        });

        static::deleted(function ($model) {
            Cache::forget('active_theme');
        });
    }

    /**
     * Theme presets
     */
    public static function getPresets()
    {
        return [
            'progress' => [
                'name' => 'Progress Communications',
                'primary_color' => '#2563eb',
                'primary_hover' => '#1d4ed8',
                'accent_color' => '#059669',
                'description' => 'Modern blue with green accents - authentic Progress Communications styling',
                // Complete color scheme
                'button_primary_bg' => '#2563eb',
                'button_primary_hover' => '#1d4ed8',
                'button_primary_text' => '#ffffff',
                'button_secondary_bg' => '#f8fafc',
                'button_secondary_hover' => '#e2e8f0',
                'button_secondary_text' => '#475569',
                'button_secondary_border' => '#cbd5e1',
                'link_color' => '#2563eb',
                'link_hover' => '#1d4ed8',
                'text_primary' => '#1e293b',
                'text_secondary' => '#475569',
                'text_muted' => '#64748b',
            ],
            'progress_classic' => [
                'name' => 'Progress Classic',
                'primary_color' => '#68757a',
                'primary_hover' => '#576165',
                'accent_color' => '#ea580c',
                'description' => 'Complete classic Progress Communications styling with authentic layout settings',
                
                // Color Scheme - Classic Progress
                'primary_text' => '#ffffff',
                'secondary_color' => '#ffffff',
                'secondary_hover' => '#f9fafb',
                'secondary_border' => '#d1d5db',
                'accent_hover' => '#c2410c',
                
                // Button Colors - Classic Corporate Style
                'button_primary_bg' => '#68757a',
                'button_primary_hover' => '#576165', 
                'button_primary_text' => '#ffffff',
                'button_secondary_bg' => '#ffffff',
                'button_secondary_hover' => '#f9fafb',
                'button_secondary_text' => '#4b5563',
                'button_secondary_border' => '#d1d5db',
                'button_danger_bg' => '#dc2626',
                'button_danger_hover' => '#b91c1c',
                'button_success_bg' => '#16a34a',
                'button_success_hover' => '#15803d',
                
                // Link Colors - Classic Orange
                'link_color' => '#ea580c',
                'link_hover' => '#c2410c',
                'link_visited' => '#9a3412',
                
                // Typography - Professional & Readable (beter leesbaar)
                'text_primary' => '#374151',     // Darker for better readability
                'text_secondary' => '#6b7280',   // Medium gray
                'text_muted' => '#9ca3af',       // Light gray
                'font_family' => 'system-ui, -apple-system, sans-serif',
                'font_size_base' => '13px',      // Beter leesbaar maar nog compact
                'font_size_small' => '12px',     // Goed leesbaar voor labels
                'font_size_large' => '15px',     // Voor headings
                'font_size_xl' => '18px',        // Voor grote titels
                
                // Font Weights - Corporate Standards
                'font_weight_normal' => '400',
                'font_weight_medium' => '500',
                'font_weight_semibold' => '600',
                'font_weight_bold' => '700',
                
                // Spacing - Readable Corporate Layout
                'letter_spacing_tight' => '-0.025em',
                'letter_spacing_normal' => '0',
                'letter_spacing_wide' => '0.025em',  // Standaard spacing
                'line_height_tight' => '1.3',        // Beter leesbaar
                'line_height_normal' => '1.5',       // Comfortabele regelhoogte
                'line_height_loose' => '1.7',        // Ruime regelhoogte
                'spacing_xs' => '0.125rem',           // 2px - zeer compact
                'spacing_sm' => '0.25rem',            // 4px - kleiner
                'spacing_md' => '0.5rem',             // 8px - veel compacter
                'spacing_lg' => '0.75rem',            // 12px - tighter
                'spacing_xl' => '1rem',               // 16px - veel kleiner
                
                // Layout - Balanced Corporate Standards  
                'border_radius' => '0.375rem',       // 6px - Professionele afronding
                'button_padding' => 'px-4 py-2',     // Goed klikbare buttons
                'transition_speed' => '150ms',       // Vloeiende transitions
                
                // Component Colors - Professional
                'badge_bg_color' => '#68757a',
                'badge_text_color' => '#ffffff',
                'success_color' => '#16a34a',        // Professional green
                'warning_color' => '#d97706',        // Warm amber
                'danger_color' => '#dc2626',         // Clear red
                'info_color' => '#2563eb',           // Professional blue
                
                // Table Styling - Corporate Clean
                'table_header_bg' => '#f9fafb',      // Light gray header
                'table_row_hover' => '#f3f4f6',      // Subtle hover
                'table_border_color' => '#e5e7eb',   // Clean borders
                
                // Effects - Conservative Corporate
                'use_shadows' => true,              // Subtle depth
                'use_gradients' => false,           // Clean flat design
                'use_animations' => true,           // Professional transitions
            ],
            'modern' => [
                'name' => 'Modern Blue',
                'primary_color' => '#3b82f6',
                'primary_hover' => '#2563eb',
                'accent_color' => '#10b981',
                'description' => 'Clean blue with green accents'
            ],
            'corporate' => [
                'name' => 'Corporate Dark',
                'primary_color' => '#1e293b',
                'primary_hover' => '#0f172a',
                'accent_color' => '#0ea5e9',
                'description' => 'Dark professional with cyan accents'
            ],
            'minimal' => [
                'name' => 'Minimal Light',
                'primary_color' => '#000000',
                'primary_hover' => '#171717',
                'accent_color' => '#737373',
                'description' => 'Black and white minimalist design',
                
                // Complete minimal color scheme
                'primary_text' => '#ffffff',
                'secondary_color' => '#ffffff',
                'secondary_hover' => '#f5f5f5',
                'secondary_border' => '#e5e5e5',
                'accent_hover' => '#525252',
                
                // Button Colors - Minimal Black & White
                'button_primary_bg' => '#000000',
                'button_primary_hover' => '#171717',
                'button_primary_text' => '#ffffff',
                'button_secondary_bg' => '#ffffff',
                'button_secondary_hover' => '#f5f5f5',
                'button_secondary_text' => '#171717',
                'button_secondary_border' => '#e5e5e5',
                'button_danger_bg' => '#ef4444',
                'button_danger_hover' => '#dc2626',
                'button_success_bg' => '#10b981',
                'button_success_hover' => '#059669',
                
                // Link Colors - Minimal Gray
                'link_color' => '#737373',
                'link_hover' => '#525252',
                'link_visited' => '#404040',
                
                // Typography - Clean Minimal
                'text_primary' => '#171717',
                'text_secondary' => '#525252',
                'text_muted' => '#737373',
                'font_family' => 'system-ui, -apple-system, sans-serif',
                'font_size_base' => '14px',
                'font_size_small' => '12px',
                'font_size_large' => '16px',
                'font_size_xl' => '20px',
                
                // Font Weights - Minimal
                'font_weight_normal' => '400',
                'font_weight_medium' => '500',
                'font_weight_semibold' => '600',
                'font_weight_bold' => '700',
                
                // Spacing - Clean Minimal
                'letter_spacing_tight' => '-0.025em',
                'letter_spacing_normal' => '0',
                'letter_spacing_wide' => '0.025em',
                'line_height_tight' => '1.3',
                'line_height_normal' => '1.5',
                'line_height_loose' => '1.7',
                'spacing_xs' => '0.25rem',
                'spacing_sm' => '0.5rem',
                'spacing_md' => '1rem',
                'spacing_lg' => '1.5rem',
                'spacing_xl' => '2rem',
                
                // Layout - Minimal Clean
                'border_radius' => '0.25rem',
                'button_padding' => 'px-4 py-2',
                'transition_speed' => '200ms',
                
                // Component Colors - Minimal
                'badge_bg_color' => '#000000',
                'badge_text_color' => '#ffffff',
                'success_color' => '#10b981',
                'warning_color' => '#f59e0b',
                'danger_color' => '#ef4444',
                'info_color' => '#737373',
                
                // Table Styling - Clean Minimal
                'table_header_bg' => '#f9f9f9',
                'table_row_hover' => '#f5f5f5',
                'table_border_color' => '#e5e5e5',
                
                // Effects - Minimal
                'use_shadows' => false,
                'use_gradients' => false,
                'use_animations' => true,
            ],
            'vibrant' => [
                'name' => 'Vibrant Purple',
                'primary_color' => '#9333ea',
                'primary_hover' => '#7c3aed',
                'accent_color' => '#ec4899',
                'description' => 'Bold purple with pink accents'
            ],
            'tech_green' => [
                'name' => 'Tech Green',
                'primary_color' => '#059669',  // Emerald green
                'primary_hover' => '#047857',
                'accent_color' => '#0891b2',   // Cyan blue
                'description' => 'Modern tech-inspired green with cyan accents'
            ],
        ];
    }

    /**
     * Apply een preset met alle kleuren
     */
    public function applyPreset($presetKey)
    {
        $presets = self::getPresets();
        
        if (!isset($presets[$presetKey])) {
            return false;
        }

        $preset = $presets[$presetKey];
        
        // Prepare update data with all available preset fields
        $updateData = [
            'theme_preset' => $presetKey,
            'primary_color' => $preset['primary_color'],
            'primary_hover' => $preset['primary_hover'],
            'accent_color' => $preset['accent_color'],
        ];
        
        // Add all extended fields if they exist in preset
        $extendedFields = [
            // Base colors
            'primary_text', 'secondary_color', 'secondary_hover', 'secondary_border', 'accent_hover',
            // Button colors
            'button_primary_bg', 'button_primary_hover', 'button_primary_text',
            'button_secondary_bg', 'button_secondary_hover', 'button_secondary_text', 'button_secondary_border',
            'button_danger_bg', 'button_danger_hover', 'button_success_bg', 'button_success_hover',
            // Link colors
            'link_color', 'link_hover', 'link_visited',
            // Text colors
            'text_primary', 'text_secondary', 'text_muted',
            // Typography
            'font_family', 'font_size_base', 'font_size_small', 'font_size_large', 'font_size_xl',
            'font_weight_normal', 'font_weight_medium', 'font_weight_semibold', 'font_weight_bold',
            // Spacing
            'letter_spacing_tight', 'letter_spacing_normal', 'letter_spacing_wide',
            'line_height_tight', 'line_height_normal', 'line_height_loose',
            'spacing_xs', 'spacing_sm', 'spacing_md', 'spacing_lg', 'spacing_xl',
            // Layout
            'border_radius', 'header_padding', 'card_padding', 'button_padding', 'transition_speed',
            // Component colors
            'badge_bg_color', 'badge_text_color', 'success_color', 'warning_color', 'danger_color', 'info_color',
            // Table colors
            'table_header_bg', 'table_row_hover', 'table_border_color',
            // Effects
            'use_shadows', 'use_gradients', 'use_animations'
        ];
        
        foreach ($extendedFields as $field) {
            if (isset($preset[$field])) {
                $updateData[$field] = $preset[$field];
            }
        }
        
        $this->update($updateData);

        return true;
    }

    /**
     * Generate CSS variables voor gebruik in views
     */
    public function getCssVariables()
    {
        return "
            :root {
                --primary-color: {$this->primary_color};
                --primary-hover: {$this->primary_hover};
                --primary-text: {$this->primary_text};
                --secondary-color: {$this->secondary_color};
                --secondary-hover: {$this->secondary_hover};
                --secondary-border: {$this->secondary_border};
                --accent-color: {$this->accent_color};
                --accent-hover: {$this->accent_hover};
                
                /* Button colors */
                --button-primary-bg: {$this->button_primary_bg};
                --button-primary-hover: {$this->button_primary_hover};
                --button-primary-text: {$this->button_primary_text};
                --button-secondary-bg: {$this->button_secondary_bg};
                --button-secondary-hover: {$this->button_secondary_hover};
                --button-secondary-text: {$this->button_secondary_text};
                --button-secondary-border: {$this->button_secondary_border};
                --button-danger-bg: {$this->button_danger_bg};
                --button-danger-hover: {$this->button_danger_hover};
                --button-success-bg: {$this->button_success_bg};
                --button-success-hover: {$this->button_success_hover};
                
                /* Link colors */
                --link-color: {$this->link_color};
                --link-hover: {$this->link_hover};
                --link-visited: {$this->link_visited};
                
                --text-primary: {$this->text_primary};
                --text-secondary: {$this->text_secondary};
                --text-muted: {$this->text_muted};
                --font-family: {$this->font_family};
                --font-size-base: {$this->font_size_base};
                --font-size-small: {$this->font_size_small};
                --font-size-large: {$this->font_size_large};
                --font-size-xl: {$this->font_size_xl};
                --border-radius: {$this->border_radius};
                --transition-speed: {$this->transition_speed};
                --badge-bg: {$this->badge_bg_color};
                --badge-text: {$this->badge_text_color};
                --success-color: {$this->success_color};
                --warning-color: {$this->warning_color};
                --danger-color: {$this->danger_color};
                --info-color: {$this->info_color};
                --table-header-bg: {$this->table_header_bg};
                --table-row-hover: {$this->table_row_hover};
                --table-border: {$this->table_border_color};
            }
        ";
    }
}