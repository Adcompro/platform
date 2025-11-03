<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SimplifiedThemeSetting extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        // Colors
        'primary_color',
        'secondary_color',
        'accent_color',
        'danger_color',
        'text_color',
        'muted_text_color',
        'background_color',
        'gradient_start',
        'gradient_end',
        'gradient_direction',
        // Typography
        'font_family',
        'font_size_base',
        'header_font_size',
        'line_height',
        // Header customization
        'header_title_size',
        'header_title_weight',
        'header_spacing',
        // Buttons
        'button_size',
        'button_text_color',
        'button_radius',
        'button_style',
        // Tables
        'table_row_padding',
        'table_header_style',
        'table_style',
        'table_striped',
        'table_hover_effect',
        // Layout
        'header_height',
        'header_style',
        'header_padding',
        'sidebar_width',
        'sidebar_style',
        'card_padding',
        'card_shadow',
        'card_style',
        'border_radius',
        // Menu styling
        'menu_style',
        'sidebar_background_color',
        'sidebar_text_color',
        'sidebar_active_color',
        'top_nav_style',
        'top_nav_active_color',
        'sidebar_icon_size',
        'sidebar_text_size',
        'topbar_background_color',
        // View header styling
        'view_header_title_size',
        'view_header_padding',
        'view_header_auto_scale',
        // Background
        'background_style',
        // Animation
        'animation_speed',
        'enable_animations',
        // Meta
        'preset_name',
        'is_active',
    ];

    protected $casts = [
        'table_striped' => 'boolean',
        'enable_animations' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get de actieve theme settings voor een company (of default)
     */
    public static function active($companyId = null)
    {
        $cacheKey = $companyId ? "theme_company_{$companyId}" : 'theme_default';
        
        // Verminder cache tijd naar 60 seconden voor snellere updates
        return Cache::remember($cacheKey, 60, function () use ($companyId) {
            $query = self::where('is_active', true);
            
            if ($companyId) {
                $query->where('company_id', $companyId);
            } else {
                $query->whereNull('company_id');
            }
            
            return $query->first() ?? self::createDefault($companyId);
        });
    }

    /**
     * Create default theme
     */
    public static function createDefault($companyId = null)
    {
        return self::create([
            'company_id' => $companyId,
            'name' => 'Modern Theme',
            'preset_name' => 'modern',
            'is_active' => true,
            // Modern default colors
            'primary_color' => '#2563eb',
            'accent_color' => '#059669',
            'danger_color' => '#dc2626',
            'text_color' => '#1e293b',
            'muted_text_color' => '#64748b',
            'background_color' => '#f8fafc',
            // Typography defaults
            'font_family' => 'inter',
            'font_size_base' => '14px',
            'header_font_size' => 'normal',
            'line_height' => 'normal',
            // Header defaults
            'header_title_size' => 'xl',
            'header_title_weight' => 'bold',
            'header_spacing' => 'normal',
            // Button defaults
            'button_size' => 'normal',
            'button_text_color' => 'white',
            'button_radius' => 'medium',
            'button_style' => 'solid',
            // Table defaults
            'table_row_padding' => 'normal',
            'table_header_style' => 'light',
            'table_striped' => false,
            'table_hover_effect' => 'light',
            // Layout defaults
            'header_height' => 'normal',
            'sidebar_width' => 'normal',
            'card_padding' => 'normal',
            'card_shadow' => 'small',
            'border_radius' => 'medium',
        ]);
    }

    /**
     * Theme presets
     */
    public static function getPresets()
    {
        return [
            'progress' => [
                'name' => 'Progress Communications',
                'description' => 'Official Progress Communications corporate branding',
                'preview_colors' => ['#FF6600', '#2B3E50', '#FFFFFF'],
                'settings' => [
                    // Progress Communications corporate kleuren
                    'primary_color' => '#FF6600',      // Progress helder oranje (zoals website)
                    'accent_color' => '#00A6E0',       // Progress blauw accent
                    'danger_color' => '#DC3545',       // Standaard Bootstrap rood
                    'text_color' => '#2B3E50',         // Donker grijs-blauw voor leesbaarheid
                    'muted_text_color' => '#6C757D',   // Standaard muted grijs
                    'background_color' => '#FAFAFA',   // Licht grijs achtergrond
                    // Typography - Clean en modern
                    'font_family' => 'inter',          // Modern font zoals op hun website
                    'font_size_base' => '12px',        // Kleinere font size voor meer content
                    'header_font_size' => 'normal',    
                    'line_height' => 'normal',
                    'header_title_size' => 'xl',
                    'header_title_weight' => 'bold',
                    'header_spacing' => 'normal',         
                    // Buttons - Corporate style
                    'button_size' => 'normal',
                    'button_text_color' => 'white',
                    'button_radius' => 'small',        // Subtiele afronding
                    'button_style' => 'solid',
                    // Tables - Professional
                    'table_row_padding' => 'normal',
                    'table_header_style' => 'light',
                    'table_striped' => false,
                    'table_hover_effect' => 'light',
                    // Layout - Corporate standards
                    'header_height' => 'normal',
                    'sidebar_width' => 'normal',
                    'card_padding' => 'normal',
                    'card_shadow' => 'small',
                    'border_radius' => 'small',        // Professionele, subtiele rondingen
                ],
            ],
            'modern' => [
                'name' => 'Modern',
                'description' => 'Clean and modern with blue accents',
                'preview_colors' => ['#2563eb', '#059669', '#f8fafc'],
                'settings' => [
                    'primary_color' => '#2563eb',
                    'accent_color' => '#059669',
                    'danger_color' => '#dc2626',
                    'text_color' => '#1e293b',
                    'muted_text_color' => '#64748b',
                    'background_color' => '#f8fafc',
                    'font_family' => 'inter',
                    'font_size_base' => '14px',
                    'header_font_size' => 'normal',
                    'line_height' => 'normal',
                    'header_title_size' => 'xl',
                    'header_title_weight' => 'bold',
                    'header_spacing' => 'normal',
                    'button_size' => 'normal',
                    'button_radius' => 'medium',
                    'table_row_padding' => 'normal',
                    'card_padding' => 'normal',
                    'card_shadow' => 'small',
                ],
            ],
            'classic' => [
                'name' => 'Classic',
                'description' => 'Traditional business style with borders',
                'preview_colors' => ['#4b5563', '#1f2937', '#ffffff'],
                'settings' => [
                    'primary_color' => '#4b5563',
                    'accent_color' => '#059669',
                    'danger_color' => '#b91c1c',
                    'text_color' => '#111827',
                    'muted_text_color' => '#6b7280',
                    'background_color' => '#ffffff',
                    'font_family' => 'system',
                    'font_size_base' => '14px',
                    'header_font_size' => 'normal',
                    'line_height' => 'normal',
                    'header_title_size' => 'lg',
                    'header_title_weight' => 'semibold',
                    'header_spacing' => 'normal',
                    'button_size' => 'normal',
                    'button_radius' => 'small',
                    'button_style' => 'solid',
                    'table_row_padding' => 'normal',
                    'table_header_style' => 'bold',
                    'table_striped' => true,
                    'card_padding' => 'normal',
                    'card_shadow' => 'none',
                    'border_radius' => 'small',
                ],
            ],
            'compact' => [
                'name' => 'Compact',
                'description' => 'Maximum data density for power users',
                'preview_colors' => ['#3b82f6', '#e5e7eb', '#ffffff'],
                'settings' => [
                    'primary_color' => '#3b82f6',
                    'accent_color' => '#10b981',
                    'danger_color' => '#ef4444',
                    'text_color' => '#1f2937',
                    'muted_text_color' => '#9ca3af',
                    'background_color' => '#ffffff',
                    'font_family' => 'inter',
                    'font_size_base' => '12px',
                    'header_font_size' => 'small',
                    'line_height' => 'compact',
                    'header_title_size' => 'base',
                    'header_title_weight' => 'medium',
                    'header_spacing' => 'tight',
                    'button_size' => 'small',
                    'button_radius' => 'small',
                    'table_row_padding' => 'compact',
                    'table_header_style' => 'light',
                    'header_height' => 'compact',
                    'sidebar_width' => 'narrow',
                    'card_padding' => 'small',
                    'card_shadow' => 'none',
                ],
            ],
            'spacious' => [
                'name' => 'Spacious',
                'description' => 'Comfortable reading with extra spacing',
                'preview_colors' => ['#7c3aed', '#f3f4f6', '#faf5ff'],
                'settings' => [
                    'primary_color' => '#7c3aed',
                    'accent_color' => '#ec4899',
                    'danger_color' => '#dc2626',
                    'text_color' => '#1e293b',
                    'muted_text_color' => '#64748b',
                    'background_color' => '#faf5ff',
                    'font_family' => 'poppins',
                    'font_size_base' => '16px',
                    'header_font_size' => 'large',
                    'line_height' => 'relaxed',
                    'header_title_size' => '3xl',
                    'header_title_weight' => 'bold',
                    'header_spacing' => 'relaxed',
                    'button_size' => 'large',
                    'button_radius' => 'large',
                    'table_row_padding' => 'spacious',
                    'header_height' => 'tall',
                    'sidebar_width' => 'wide',
                    'card_padding' => 'large',
                    'card_shadow' => 'medium',
                    'border_radius' => 'large',
                ],
            ],
            'dark' => [
                'name' => 'Dark Mode',
                'description' => 'Easy on the eyes dark theme',
                'preview_colors' => ['#3b82f6', '#1f2937', '#111827'],
                'settings' => [
                    'primary_color' => '#3b82f6',
                    'accent_color' => '#10b981',
                    'danger_color' => '#ef4444',
                    'text_color' => '#f3f4f6',
                    'muted_text_color' => '#9ca3af',
                    'background_color' => '#111827',
                    'font_family' => 'inter',
                    'font_size_base' => '14px',
                    'header_title_size' => 'xl',
                    'header_title_weight' => 'bold',
                    'header_spacing' => 'normal',
                    'button_text_color' => 'white',
                    'table_header_style' => 'dark',
                    'table_hover_effect' => 'dark',
                    'card_shadow' => 'large',
                ],
            ],
            'highcontrast' => [
                'name' => 'High Contrast',
                'description' => 'Maximum readability and accessibility',
                'preview_colors' => ['#000000', '#ffff00', '#ffffff'],
                'settings' => [
                    'primary_color' => '#000000',
                    'accent_color' => '#0000ff',
                    'danger_color' => '#ff0000',
                    'text_color' => '#000000',
                    'muted_text_color' => '#404040',
                    'background_color' => '#ffffff',
                    'font_family' => 'system',
                    'font_size_base' => '16px',
                    'header_font_size' => 'large',
                    'line_height' => 'relaxed',
                    'header_title_size' => '2xl',
                    'header_title_weight' => 'extrabold',
                    'header_spacing' => 'relaxed',
                    'button_text_color' => 'white',
                    'button_style' => 'solid',
                    'table_header_style' => 'bold',
                    'table_striped' => true,
                    'card_shadow' => 'none',
                    'border_radius' => 'none',
                ],
            ],
        ];
    }

    /**
     * Apply een preset
     */
    public function applyPreset($presetKey)
    {
        $presets = self::getPresets();
        
        if (!isset($presets[$presetKey])) {
            \Log::error('Preset not found: ' . $presetKey);
            return false;
        }

        $settings = $presets[$presetKey]['settings'];
        $settings['preset_name'] = $presetKey;
        
        \Log::info('Applying preset: ' . $presetKey, [
            'settings' => $settings,
            'current_preset' => $this->preset_name
        ]);
        
        $this->update($settings);
        $this->clearCache();
        
        \Log::info('Preset applied successfully', [
            'new_preset' => $this->fresh()->preset_name
        ]);
        
        return true;
    }

    /**
     * Get CSS variables voor gebruik in views
     */
    public function getCssVariables()
    {
        // Set defaults for empty values - ALLE velden die we gebruiken
        $fontFamily = $this->font_family ?: 'inter';
        $fontSize = $this->font_size_base ?: '14px';
        $headerFontSize = $this->header_font_size ?: 'normal';
        $lineHeight = $this->line_height ?: 'normal';
        
        // Header customization defaults
        $headerTitleSize = $this->header_title_size ?: 'xl';
        $headerTitleWeight = $this->header_title_weight ?: 'bold';
        $headerCustomPaddingValue = $this->header_padding ?: 'normal';
        $headerSpacing = $this->header_spacing ?: 'normal';
        
        $buttonSize = $this->button_size ?: 'normal';
        $buttonRadius = $this->button_radius ?: 'medium';
        $buttonTextColor = $this->button_text_color ?: '#ffffff';
        $tableRowPadding = $this->table_row_padding ?: 'normal';
        $cardPaddingValue = $this->card_padding ?: 'normal';
        $headerHeightValue = $this->header_height ?: 'normal';
        $sidebarWidthValue = $this->sidebar_width ?: 'normal';
        
        // Fix voor border_radius - als het een directe waarde is, map naar de juiste key
        $borderRadiusValue = $this->border_radius;
        if ($borderRadiusValue == '0.5rem') {
            $borderRadiusValue = 'medium';
        } elseif (!$borderRadiusValue) {
            $borderRadiusValue = 'medium';
        }
        
        // Card shadow heeft mogelijk geen kolom, dus altijd default
        $cardShadowValue = $this->card_shadow ?? 'medium';
        
        // Map font families naar echte font stacks
        $fontStacks = [
            'system' => '-apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif',
            'inter' => '"Inter", system-ui, sans-serif',
            'Inter' => '"Inter", system-ui, sans-serif', // Support both capitalizations
            'roboto' => '"Roboto", system-ui, sans-serif',
            'poppins' => '"Poppins", system-ui, sans-serif',
            'opensans' => '"Open Sans", system-ui, sans-serif',
        ];

        // Map sizes naar pixels
        $headerSizes = [
            'small' => '20px',
            'normal' => '24px',
            'large' => '32px',
        ];

        $lineHeights = [
            'compact' => '1.25',
            'normal' => '1.5',
            'relaxed' => '1.75',
        ];

        // Map layout sizes
        $sidebarWidths = [
            'narrow' => '200px',
            'normal' => '250px',
            'wide' => '300px',
        ];

        $borderRadii = [
            'none' => '0',
            'small' => '0.25rem',
            'medium' => '0.5rem',
            'large' => '0.75rem',
            'full' => '9999px',
        ];

        $shadows = [
            'none' => 'none',
            'small' => '0 1px 3px 0 rgb(0 0 0 / 0.1)',
            'medium' => '0 4px 6px -1px rgb(0 0 0 / 0.1)',
            'large' => '0 10px 15px -3px rgb(0 0 0 / 0.1)',
        ];

        // Sidebar sizing options
        $sidebarIconSizes = [
            'small' => '0.75rem',   // text-xs
            'medium' => '0.875rem', // text-sm
            'large' => '1rem',      // text-base
        ];

        $sidebarTextSizes = [
            'small' => '0.625rem',  // text-xs/10px
            'medium' => '0.75rem',  // text-xs
            'large' => '0.875rem',  // text-sm
        ];

        // Button sizes
        $buttonPaddingX = [
            'small' => '0.5rem',   // px-2
            'normal' => '1rem',    // px-4
            'medium' => '1rem',    // px-4 (alias for normal)
            'large' => '1.5rem',   // px-6
        ];
        
        $buttonPaddingY = [
            'small' => '0.25rem',  // py-1
            'normal' => '0.5rem',  // py-2
            'medium' => '0.5rem',  // py-2 (alias for normal)
            'large' => '0.75rem',  // py-3
        ];
        
        $buttonFontSizes = [
            'small' => '0.75rem',  // text-xs
            'normal' => '0.875rem', // text-sm
            'medium' => '0.875rem', // text-sm (alias for normal)
            'large' => '1rem',     // text-base
        ];

        // Table settings
        $tablePaddingX = [
            'compact' => '0.75rem',  // px-3
            'normal' => '1rem',      // px-4
            'spacious' => '1.5rem',  // px-6
        ];
        
        $tablePaddingY = [
            'compact' => '0.25rem',  // py-1
            'normal' => '0.5rem',    // py-2
            'spacious' => '1rem',    // py-4
        ];

        // Card settings
        $cardPadding = [
            'small' => '0.75rem',   // p-3
            'normal' => '1rem',     // p-4
            'large' => '1.5rem',    // p-6
        ];

        // Header height settings
        $headerHeights = [
            'compact' => '3rem',    // 48px
            'normal' => '4rem',     // 64px
            'tall' => '5rem',       // 80px
        ];
        
        $headerPadding = [
            'compact' => '0.5rem',  // py-2
            'normal' => '1rem',     // py-4
            'tall' => '1.5rem',     // py-6
        ];

        // Header title customization settings
        $headerTitleSizes = [
            'sm' => '0.875rem',     // text-sm
            'base' => '1rem',       // text-base  
            'lg' => '1.125rem',     // text-lg
            'xl' => '1.25rem',      // text-xl
            '2xl' => '1.5rem',      // text-2xl
            '3xl' => '1.875rem',    // text-3xl
            '4xl' => '2.25rem',     // text-4xl
        ];

        $headerTitleWeights = [
            'normal' => '400',      // font-normal
            'medium' => '500',      // font-medium
            'semibold' => '600',    // font-semibold
            'bold' => '700',        // font-bold
            'extrabold' => '800',   // font-extrabold
        ];

        $headerCustomPadding = [
            'compact' => '0.5rem',  // py-2
            'normal' => '1rem',     // py-4
            'relaxed' => '1.25rem', // py-5
            'spacious' => '1.5rem', // py-6
        ];

        $headerSpacings = [
            'tight' => '0.25rem',   // space-y-1
            'normal' => '0.5rem',   // space-y-2
            'relaxed' => '0.75rem', // space-y-3
        ];

        // Helper function to convert hex to RGB
        $hexToRgb = function($hex) {
            $hex = ltrim($hex, '#');
            return implode(', ', [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            ]);
        };

        return "<style>
            :root {
                /* Colors */
                --theme-primary: {$this->primary_color};
                --theme-primary-rgb: {$hexToRgb($this->primary_color)};
                --theme-accent: {$this->accent_color};
                --theme-accent-rgb: {$hexToRgb($this->accent_color)};
                --theme-danger: {$this->danger_color};
                --theme-danger-rgb: {$hexToRgb($this->danger_color)};
                --theme-text: {$this->text_color};
                --theme-text-rgb: {$hexToRgb($this->text_color)};
                --theme-text-muted: {$this->muted_text_color};
                --theme-text-muted-rgb: {$hexToRgb($this->muted_text_color)};
                --theme-bg: {$this->background_color};
                --theme-bg-rgb: {$hexToRgb($this->background_color)};
                --theme-border-rgb: {$hexToRgb('#e2e8f0')};

                /* Typography */
                --theme-font-family: {$fontStacks[$fontFamily]};
                --theme-font-size: {$fontSize};
                --theme-header-size: {$headerSizes[$headerFontSize]};
                --theme-line-height: {$lineHeights[$lineHeight]};

                /* Header Customization */
                --theme-header-title-size: {$headerTitleSizes[$headerTitleSize]};
                --theme-header-title-weight: {$headerTitleWeights[$headerTitleWeight]};
                --theme-header-custom-padding: {$headerCustomPadding[$headerCustomPaddingValue]};
                --theme-header-spacing: {$headerSpacings[$headerSpacing]};

                /* Buttons */
                --theme-button-padding-x: {$buttonPaddingX[$buttonSize]};
                --theme-button-padding-y: {$buttonPaddingY[$buttonSize]};
                --theme-button-font-size: {$buttonFontSizes[$buttonSize]};
                --theme-button-radius: {$borderRadii[$buttonRadius]};
                --theme-button-text-color: {$buttonTextColor};

                /* Tables */
                --theme-table-padding-x: {$tablePaddingX[$tableRowPadding]};
                --theme-table-padding-y: {$tablePaddingY[$tableRowPadding]};
                --theme-table-striped: {$this->table_striped};
                --theme-table-header-style: {$this->table_header_style};
                --theme-table-hover-effect: {$this->table_hover_effect};

                /* Cards */
                --theme-card-padding: {$cardPadding[$cardPaddingValue]};

                /* Layout */
                --theme-header-height: {$headerHeights[$headerHeightValue]};
                --theme-header-padding: {$headerPadding[$headerHeightValue]};
                --theme-sidebar-width: {$sidebarWidths[$sidebarWidthValue]};
                --theme-border-radius: {$borderRadii[$borderRadiusValue]};
                --theme-card-shadow: {$shadows[$cardShadowValue]};

                /* Menu Styling */
                --theme-sidebar-bg: " . ($this->sidebar_background_color ?: '#1e293b') . ";
                --theme-sidebar-text: " . ($this->sidebar_text_color ?: '#94a3b8') . ";
                --theme-sidebar-active: " . ($this->sidebar_active_color ?: '#14b8a6') . ";
                --theme-nav-active: " . ($this->top_nav_active_color ?: '#14b8a6') . ";
                --theme-nav-active-rgb: " . $hexToRgb($this->top_nav_active_color ?: '#14b8a6') . ";
                --theme-sidebar-icon-size: " . ($sidebarIconSizes[$this->sidebar_icon_size ?: 'medium']) . ";
                --theme-sidebar-text-size: " . ($sidebarTextSizes[$this->sidebar_text_size ?: 'small']) . ";
                --theme-topbar-bg: " . ($this->topbar_background_color ?: $this->background_color) . ";
                --theme-topbar-bg-rgb: " . $hexToRgb($this->topbar_background_color ?: $this->background_color) . ";
                --theme-nav-style: " . ($this->top_nav_style ?: 'tabs') . ";
                /* View Header Styling */
                --theme-view-header-title-size: " . ($this->getViewHeaderTitleSize()) . ";
                --theme-view-header-padding: " . ($this->getViewHeaderPadding()) . ";
                --theme-view-header-auto-scale: " . ($this->view_header_auto_scale ? 'true' : 'false') . ";
                --theme-view-header-description-size: " . ($this->getViewHeaderDescriptionSize()) . ";
                --theme-view-header-button-size: " . ($this->getViewHeaderButtonSize()) . ";
            }
        </style>";
    }

    /**
     * Get view header title size
     */
    public function getViewHeaderTitleSize()
    {
        $sizes = [
            'small' => '1.25rem',   // 20px
            'medium' => '1.5rem',   // 24px
            'large' => '1.875rem',  // 30px
        ];

        return $sizes[$this->view_header_title_size ?: 'medium'];
    }

    /**
     * Get view header padding
     */
    public function getViewHeaderPadding()
    {
        $paddings = [
            'compact' => '0.5rem',  // 8px
            'normal' => '1rem',     // 16px
            'spacious' => '1.5rem', // 24px
        ];

        return $paddings[$this->view_header_padding ?: 'normal'];
    }

    /**
     * Get view header description size
     */
    public function getViewHeaderDescriptionSize()
    {
        $titleSize = $this->getViewHeaderTitleSize();

        // Convert rem to numeric value, calculate 75% of title size
        $numeric = floatval(str_replace('rem', '', $titleSize));
        $descriptionSize = $numeric * 0.75;

        return $descriptionSize . 'rem';
    }

    /**
     * Get view header button size
     */
    public function getViewHeaderButtonSize()
    {
        $titleSize = $this->getViewHeaderTitleSize();

        // Convert rem to numeric value, calculate 60% of title size
        $numeric = floatval(str_replace('rem', '', $titleSize));
        $buttonSize = $numeric * 0.6;

        return $buttonSize . 'rem';
    }

    /**
     * Get Tailwind classes voor componenten
     */
    public function getComponentClasses($component)
    {
        $classes = [
            'button' => $this->getButtonClasses(),
            'card' => $this->getCardClasses(),
            'table' => $this->getTableClasses(),
            'header' => $this->getHeaderClasses(),
        ];

        return $classes[$component] ?? '';
    }

    private function getButtonClasses()
    {
        $size = [
            'small' => 'px-2 py-1 text-xs',
            'normal' => 'px-4 py-2 text-sm',
            'large' => 'px-6 py-3 text-base',
        ][$this->button_size];

        $radius = [
            'none' => 'rounded-none',
            'small' => 'rounded',
            'medium' => 'rounded-md',
            'large' => 'rounded-lg',
            'full' => 'rounded-full',
        ][$this->button_radius];

        return "{$size} {$radius}";
    }

    private function getCardClasses()
    {
        $padding = [
            'small' => 'p-3',
            'normal' => 'p-4',
            'large' => 'p-6',
        ][$this->card_padding];

        $shadow = [
            'none' => '',
            'small' => 'shadow-sm',
            'medium' => 'shadow',
            'large' => 'shadow-lg',
        ][$this->card_shadow];

        $radius = [
            'none' => 'rounded-none',
            'small' => 'rounded',
            'medium' => 'rounded-lg',
            'large' => 'rounded-xl',
        ][$this->border_radius];

        return "{$padding} {$shadow} {$radius}";
    }

    private function getTableClasses()
    {
        $padding = [
            'compact' => 'px-3 py-1',
            'normal' => 'px-4 py-2',
            'spacious' => 'px-6 py-4',
        ][$this->table_row_padding];

        return $padding;
    }

    private function getHeaderClasses()
    {
        $height = [
            'compact' => 'py-2',
            'normal' => 'py-4',
            'tall' => 'py-6',
        ][$this->header_height];

        return $height;
    }

    /**
     * Clear theme cache
     */
    public function clearCache()
    {
        $cacheKey = $this->company_id ? "theme_company_{$this->company_id}" : 'theme_default';
        Cache::forget($cacheKey);
    }
    
    /**
     * Clear all theme caches (for all companies)
     */
    public static function clearAllCaches()
    {
        // Clear default theme cache
        Cache::forget('theme_default');
        
        // Clear all company theme caches
        $companies = \App\Models\Company::pluck('id');
        foreach ($companies as $companyId) {
            Cache::forget("theme_company_{$companyId}");
        }
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->clearCache();
        });

        static::deleted(function ($model) {
            $model->clearCache();
        });
    }
}