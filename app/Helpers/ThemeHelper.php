<?php

namespace App\Helpers;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;

class ThemeHelper
{
    /**
     * Get theme value with fallback
     */
    public static function get($key, $default = null)
    {
        // Check if theme plugin is active
        $pluginActive = Cache::remember('theme_plugin_active', 300, function () {
            try {
                $plugin = \App\Models\Plugin::where('name', 'theme_customization')->first();
                return $plugin && $plugin->is_active;
            } catch (\Exception $e) {
                return false;
            }
        });

        if (!$pluginActive) {
            return self::getDefault($key, $default);
        }

        // Get active theme
        $theme = ThemeSetting::active();
        
        if (!$theme) {
            return self::getDefault($key, $default);
        }

        return $theme->$key ?? self::getDefault($key, $default);
    }

    /**
     * Get default theme values
     */
    public static function getDefault($key, $fallback = null)
    {
        $defaults = [
            // Colors
            'primary_color' => '#68757a',
            'primary_hover' => '#576165',
            'primary_text' => '#ffffff',
            'secondary_color' => '#ffffff',
            'secondary_hover' => '#f9fafb',
            'secondary_border' => '#d1d5db',
            'accent_color' => '#ea580c',
            'accent_hover' => '#c2410c',
            'text_primary' => '#4b5563',
            'text_secondary' => '#6b7280',
            'text_muted' => '#9ca3af',
            
            // Button colors
            'button_primary_bg' => '#68757a',
            'button_primary_hover' => '#576165',
            'button_primary_text' => '#ffffff',
            'button_secondary_bg' => '#ffffff',
            'button_secondary_hover' => '#f9fafb',
            'button_secondary_text' => '#4b5563',
            'button_secondary_border' => '#d1d5db',
            'button_danger_bg' => '#ef4444',
            'button_danger_hover' => '#dc2626',
            'button_success_bg' => '#10b981',
            'button_success_hover' => '#059669',
            
            // Link colors
            'link_color' => '#ea580c',
            'link_hover' => '#c2410c',
            'link_visited' => '#dc2626',
            
            // Status colors
            'success_color' => '#10b981',
            'warning_color' => '#f59e0b',
            'danger_color' => '#ef4444',
            'info_color' => '#3b82f6',
            
            // Table colors
            'table_header_bg' => '#f9fafb',
            'table_row_hover' => '#f3f4f6',
            'table_border_color' => '#e5e7eb',
            
            // Badge colors
            'badge_bg_color' => '#68757a',
            'badge_text_color' => '#ffffff',
            
            // Typography
            'font_family' => 'Inter, system-ui, sans-serif',
            'font_size_base' => '13px',
            'font_size_small' => '11px',
            'font_size_large' => '15px',
            'font_size_xl' => '24px',
            // Font weights
            'font_weight_normal' => '400',
            'font_weight_medium' => '500',
            'font_weight_semibold' => '600',
            'font_weight_bold' => '700',
            // Spacing
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
            
            // Effects
            'use_shadows' => true,
            'use_gradients' => false,
            'use_animations' => true,
            'transition_speed' => '200ms',
            'border_radius' => '0.5rem',
        ];

        return $defaults[$key] ?? $fallback;
    }

    /**
     * Get button classes based on theme
     */
    public static function buttonClasses($type = 'primary', $size = 'normal')
    {
        $baseClasses = 'inline-flex items-center font-semibold rounded-lg transition-all duration-200';
        
        // Size classes
        $sizeClasses = match($size) {
            'small' => 'px-3 py-1.5 text-xs',
            'large' => 'px-6 py-3 text-base',
            default => 'px-4 py-2 text-sm'
        };
        
        // Type-specific classes with theme colors
        $typeClasses = match($type) {
            'primary' => 'bg-[' . self::get('primary_color') . '] hover:bg-[' . self::get('primary_hover') . '] text-white',
            'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
            'accent' => 'bg-[' . self::get('accent_color') . '] hover:bg-[' . self::get('accent_hover') . '] text-white',
            'danger' => 'bg-red-600 hover:bg-red-700 text-white',
            'success' => 'bg-green-600 hover:bg-green-700 text-white',
            default => 'bg-gray-600 hover:bg-gray-700 text-white'
        };
        
        // Voor fallback naar Tailwind classes als custom colors niet werken
        if ($type === 'primary' && !self::isThemeActive()) {
            $typeClasses = 'bg-slate-600 hover:bg-slate-700 text-white';
        } elseif ($type === 'accent' && !self::isThemeActive()) {
            $typeClasses = 'bg-orange-600 hover:bg-orange-700 text-white';
        }
        
        return "$baseClasses $sizeClasses $typeClasses";
    }

    /**
     * Get badge classes based on status
     */
    public static function badgeClasses($status)
    {
        return match($status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check if theme plugin is active
     */
    public static function isThemeActive()
    {
        return Cache::remember('theme_plugin_active', 300, function () {
            try {
                $plugin = \App\Models\Plugin::where('name', 'theme_customization')->first();
                return $plugin && $plugin->is_active;
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    /**
     * Get inline styles for dynamic colors
     */
    public static function getInlineStyles()
    {
        if (!self::isThemeActive()) {
            return self::getDefaultStyles();
        }

        $theme = ThemeSetting::active();
        if (!$theme) {
            return self::getDefaultStyles();
        }

        return "
        <style>
            :root {
                --theme-primary: {$theme->primary_color};
                --theme-primary-hover: {$theme->primary_hover};
                --theme-accent: {$theme->accent_color};
                --theme-accent-hover: {$theme->accent_hover};
                --theme-text-primary: {$theme->text_primary};
                --theme-text-secondary: {$theme->text_secondary};
                --theme-text-muted: {$theme->text_muted};
                --theme-border: {$theme->table_border_color};
                --theme-bg-hover: {$theme->table_row_hover};
                
                /* Button colors */
                --button-primary-bg: {$theme->button_primary_bg};
                --button-primary-hover: {$theme->button_primary_hover};
                --button-primary-text: {$theme->button_primary_text};
                --button-secondary-bg: {$theme->button_secondary_bg};
                --button-secondary-hover: {$theme->button_secondary_hover};
                --button-secondary-text: {$theme->button_secondary_text};
                --button-secondary-border: {$theme->button_secondary_border};
                --button-danger-bg: {$theme->button_danger_bg};
                --button-danger-hover: {$theme->button_danger_hover};
                --button-success-bg: {$theme->button_success_bg};
                --button-success-hover: {$theme->button_success_hover};
                
                /* Link colors */
                --link-color: {$theme->link_color};
                --link-hover: {$theme->link_hover};
                --link-visited: {$theme->link_visited};
                
                /* Typography */
                --font-family: {$theme->font_family};
                --font-size-base: {$theme->font_size_base};
                --font-size-small: {$theme->font_size_small};
                --font-size-large: {$theme->font_size_large};
                --font-size-xl: {$theme->font_size_xl};
                --font-weight-normal: {$theme->font_weight_normal};
                --font-weight-medium: {$theme->font_weight_medium};
                --font-weight-semibold: {$theme->font_weight_semibold};
                --font-weight-bold: {$theme->font_weight_bold};
                
                /* Spacing */
                --letter-spacing-tight: {$theme->letter_spacing_tight};
                --letter-spacing-normal: {$theme->letter_spacing_normal};
                --letter-spacing-wide: {$theme->letter_spacing_wide};
                --line-height-tight: {$theme->line_height_tight};
                --line-height-normal: {$theme->line_height_normal};
                --line-height-loose: {$theme->line_height_loose};
                --spacing-xs: {$theme->spacing_xs};
                --spacing-sm: {$theme->spacing_sm};
                --spacing-md: {$theme->spacing_md};
                --spacing-lg: {$theme->spacing_lg};
                --spacing-xl: {$theme->spacing_xl};
                
                /* Button styling */
                --theme-border-radius: {$theme->border_radius};
                --theme-transition-speed: {$theme->transition_speed};
                --theme-button-padding: {$theme->button_padding};
                --theme-header-padding: {$theme->header_padding};
                --theme-card-padding: {$theme->card_padding};
                
                /* Company text color - uses accent color */
                --company-text-color: {$theme->accent_color};
            }
            
            /* Company text styling */
            .text-theme-company {
                color: var(--company-text-color) !important;
            }
            
            /* Button classes */
            .btn-theme-primary {
                background-color: var(--button-primary-bg);
                color: var(--button-primary-text);
            }
            .btn-theme-primary:hover {
                background-color: var(--button-primary-hover);
            }
            
            .btn-theme-secondary {
                background-color: var(--button-secondary-bg);
                color: var(--button-secondary-text);
                border: 1px solid var(--button-secondary-border);
            }
            .btn-theme-secondary:hover {
                background-color: var(--button-secondary-hover);
            }
            
            .btn-theme-danger {
                background-color: var(--button-danger-bg);
                color: white;
            }
            .btn-theme-danger:hover {
                background-color: var(--button-danger-hover);
            }
            
            .btn-theme-success {
                background-color: var(--button-success-bg);
                color: white;
            }
            .btn-theme-success:hover {
                background-color: var(--button-success-hover);
            }
            
            /* Link classes */
            .link-theme {
                color: var(--link-color);
            }
            .link-theme:hover {
                color: var(--link-hover);
            }
            .link-theme:visited {
                color: var(--link-visited);
            }
            
            /* Text classes */
            .text-theme-primary {
                color: var(--theme-text-primary);
            }
            .text-theme-secondary {
                color: var(--theme-text-secondary);
            }
            .text-theme-muted {
                color: var(--theme-text-muted);
            }
            
            /* Utility classes */
            .border-theme {
                border-color: var(--theme-border);
            }
            
            .hover\\:bg-theme-hover:hover {
                background-color: var(--theme-bg-hover);
            }
            
            /* Typography utility classes */
            .font-theme-family {
                font-family: var(--font-family);
            }
            .text-theme-base {
                font-size: var(--font-size-base);
            }
            .text-theme-small {
                font-size: var(--font-size-small);
            }
            .text-theme-large {
                font-size: var(--font-size-large);
            }
            .text-theme-xl {
                font-size: var(--font-size-xl);
            }
            .font-theme-normal {
                font-weight: var(--font-weight-normal);
            }
            .font-theme-medium {
                font-weight: var(--font-weight-medium);
            }
            .font-theme-semibold {
                font-weight: var(--font-weight-semibold);
            }
            .font-theme-bold {
                font-weight: var(--font-weight-bold);
            }
            
            /* Spacing utility classes */
            .tracking-theme-tight {
                letter-spacing: var(--letter-spacing-tight);
            }
            .tracking-theme-normal {
                letter-spacing: var(--letter-spacing-normal);
            }
            .tracking-theme-wide {
                letter-spacing: var(--letter-spacing-wide);
            }
            .leading-theme-tight {
                line-height: var(--line-height-tight);
            }
            .leading-theme-normal {
                line-height: var(--line-height-normal);
            }
            .leading-theme-loose {
                line-height: var(--line-height-loose);
            }
            
            /* Spacing presets */
            .space-theme-xs {
                margin: var(--spacing-xs);
            }
            .space-theme-sm {
                margin: var(--spacing-sm);
            }
            .space-theme-md {
                margin: var(--spacing-md);
            }
            .space-theme-lg {
                margin: var(--spacing-lg);
            }
            .space-theme-xl {
                margin: var(--spacing-xl);
            }
            .p-theme-xs {
                padding: var(--spacing-xs);
            }
            .p-theme-sm {
                padding: var(--spacing-sm);
            }
            .p-theme-md {
                padding: var(--spacing-md);
            }
            .p-theme-lg {
                padding: var(--spacing-lg);
            }
            .p-theme-xl {
                padding: var(--spacing-xl);
            }
            
            /* Form Control Classes */
            .input-theme, .select-theme {
                background-color: #ffffff;
                border: 1px solid var(--theme-border);
                color: var(--theme-text-primary);
                font-size: var(--font-size-base);
                font-family: var(--font-family);
                padding: var(--spacing-sm);
                border-radius: var(--theme-border-radius);
                transition: border-color var(--theme-transition-speed), box-shadow var(--theme-transition-speed);
            }
            .input-theme:focus, .select-theme:focus {
                outline: none;
                border-color: var(--theme-accent);
                box-shadow: 0 0 0 1px var(--theme-accent);
            }
            .input-theme::placeholder {
                color: var(--theme-text-muted);
            }
            
            /* Search Button Styling */
            .btn-search-theme {
                background-color: var(--button-secondary-bg);
                color: var(--button-secondary-text);
                border: 1px solid var(--button-secondary-border);
                font-size: var(--font-size-base);
                font-family: var(--font-family);
                padding: var(--spacing-sm);
                border-radius: var(--theme-border-radius);
                transition: all var(--theme-transition-speed);
            }
            .btn-search-theme:hover {
                background-color: var(--button-secondary-hover);
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            /* Clear Button Styling */  
            .btn-clear-theme {
                background-color: transparent;
                color: var(--theme-text-secondary);
                border: 1px solid var(--theme-border);
                font-size: var(--font-size-base);
                font-family: var(--font-family);
                padding: var(--spacing-sm);
                border-radius: var(--theme-border-radius);
                transition: all var(--theme-transition-speed);
            }
            .btn-clear-theme:hover {
                background-color: var(--theme-bg-hover);
                color: var(--theme-text-primary);
                border-color: var(--theme-text-secondary);
            }
            
            /* Card Styling */
            .card-theme {
                background-color: #ffffff;
                border: 1px solid var(--theme-border);
                border-radius: var(--theme-border-radius);
                padding: var(--spacing-md);
                transition: all var(--theme-transition-speed);
            }
            .card-theme:hover {
                background-color: var(--theme-bg-hover);
                transform: translateY(-1px);
            }
            
            /* Icon Container Styling */
            .icon-container-primary {
                background-color: var(--theme-primary);
                color: var(--button-primary-text);
                width: 2.5rem;
                height: 2.5rem;
                border-radius: var(--theme-border-radius);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-success {
                background-color: var(--success-color);
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: var(--theme-border-radius);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-danger {
                background-color: var(--danger-color);
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: var(--theme-border-radius);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-warning {
                background-color: var(--warning-color);
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: var(--theme-border-radius);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-info {
                background-color: var(--info-color);
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: var(--theme-border-radius);
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
        ";
    }

    /**
     * Get default styles when plugin is not active
     */
    public static function getDefaultStyles()
    {
        return "
        <style>
            :root {
                --theme-primary: #68757a;
                --theme-primary-hover: #576165;
                --theme-accent: #ea580c;
                --theme-accent-hover: #c2410c;
                --theme-text-primary: #4b5563;
                --theme-text-secondary: #6b7280;
                --theme-text-muted: #9ca3af;
                --theme-border: #e5e7eb;
                --theme-bg-hover: #f3f4f6;
                
                /* Default button colors */
                --button-primary-bg: #68757a;
                --button-primary-hover: #576165;
                --button-primary-text: #ffffff;
                --button-secondary-bg: #ffffff;
                --button-secondary-hover: #f9fafb;
                --button-secondary-text: #4b5563;
                --button-secondary-border: #d1d5db;
                --button-danger-bg: #ef4444;
                --button-danger-hover: #dc2626;
                --button-success-bg: #10b981;
                --button-success-hover: #059669;
                
                /* Default link colors */
                --link-color: #ea580c;
                --link-hover: #c2410c;
                --link-visited: #dc2626;
                
                /* Default Typography */
                --font-family: 'Inter, system-ui, sans-serif';
                --font-size-base: 13px;
                --font-size-small: 11px;
                --font-size-large: 15px;
                --font-size-xl: 24px;
                --font-weight-normal: 400;
                --font-weight-medium: 500;
                --font-weight-semibold: 600;
                --font-weight-bold: 700;
                
                /* Default Spacing */
                --letter-spacing-tight: -0.025em;
                --letter-spacing-normal: 0;
                --letter-spacing-wide: 0.025em;
                --line-height-tight: 1.25;
                --line-height-normal: 1.5;
                --line-height-loose: 1.75;
                --spacing-xs: 0.25rem;
                --spacing-sm: 0.5rem;
                --spacing-md: 1rem;
                --spacing-lg: 1.5rem;
                --spacing-xl: 2rem;
            }
            
            /* Button classes */
            .btn-theme-primary {
                background-color: var(--button-primary-bg);
                color: var(--button-primary-text);
            }
            .btn-theme-primary:hover {
                background-color: var(--button-primary-hover);
            }
            
            .btn-theme-secondary {
                background-color: var(--button-secondary-bg);
                color: var(--button-secondary-text);
                border: 1px solid var(--button-secondary-border);
            }
            .btn-theme-secondary:hover {
                background-color: var(--button-secondary-hover);
            }
            
            .btn-theme-danger {
                background-color: var(--button-danger-bg);
                color: white;
            }
            .btn-theme-danger:hover {
                background-color: var(--button-danger-hover);
            }
            
            .btn-theme-success {
                background-color: var(--button-success-bg);
                color: white;
            }
            .btn-theme-success:hover {
                background-color: var(--button-success-hover);
            }
            
            /* Link classes */
            .link-theme {
                color: var(--link-color);
            }
            .link-theme:hover {
                color: var(--link-hover);
            }
            .link-theme:visited {
                color: var(--link-visited);
            }
            
            /* Text classes */
            .text-theme-primary {
                color: var(--theme-text-primary);
            }
            .text-theme-secondary {
                color: var(--theme-text-secondary);
            }
            .text-theme-muted {
                color: var(--theme-text-muted);
            }
            
            /* Utility classes */
            .border-theme {
                border-color: var(--theme-border);
            }
            
            .hover\\:bg-theme-hover:hover {
                background-color: var(--theme-bg-hover);
            }
            
            /* Typography utility classes */
            .font-theme-family {
                font-family: var(--font-family);
            }
            .text-theme-base {
                font-size: var(--font-size-base);
            }
            .text-theme-small {
                font-size: var(--font-size-small);
            }
            .text-theme-large {
                font-size: var(--font-size-large);
            }
            .text-theme-xl {
                font-size: var(--font-size-xl);
            }
            .font-theme-normal {
                font-weight: var(--font-weight-normal);
            }
            .font-theme-medium {
                font-weight: var(--font-weight-medium);
            }
            .font-theme-semibold {
                font-weight: var(--font-weight-semibold);
            }
            .font-theme-bold {
                font-weight: var(--font-weight-bold);
            }
            
            /* Spacing utility classes */
            .tracking-theme-tight {
                letter-spacing: var(--letter-spacing-tight);
            }
            .tracking-theme-normal {
                letter-spacing: var(--letter-spacing-normal);
            }
            .tracking-theme-wide {
                letter-spacing: var(--letter-spacing-wide);
            }
            .leading-theme-tight {
                line-height: var(--line-height-tight);
            }
            .leading-theme-normal {
                line-height: var(--line-height-normal);
            }
            .leading-theme-loose {
                line-height: var(--line-height-loose);
            }
            
            /* Spacing presets */
            .space-theme-xs {
                margin: var(--spacing-xs);
            }
            .space-theme-sm {
                margin: var(--spacing-sm);
            }
            .space-theme-md {
                margin: var(--spacing-md);
            }
            .space-theme-lg {
                margin: var(--spacing-lg);
            }
            .space-theme-xl {
                margin: var(--spacing-xl);
            }
            .p-theme-xs {
                padding: var(--spacing-xs);
            }
            .p-theme-sm {
                padding: var(--spacing-sm);
            }
            .p-theme-md {
                padding: var(--spacing-md);
            }
            .p-theme-lg {
                padding: var(--spacing-lg);
            }
            .p-theme-xl {
                padding: var(--spacing-xl);
            }
            
            /* Form Control Classes */
            .input-theme, .select-theme {
                background-color: #ffffff;
                border: 1px solid #e5e7eb;
                color: #4b5563;
                font-size: 13px;
                font-family: 'Inter, system-ui, sans-serif';
                padding: 0.5rem;
                border-radius: 0.5rem;
                transition: border-color 200ms, box-shadow 200ms;
            }
            .input-theme:focus, .select-theme:focus {
                outline: none;
                border-color: #ea580c;
                box-shadow: 0 0 0 1px #ea580c;
            }
            .input-theme::placeholder {
                color: #9ca3af;
            }
            
            /* Search Button Styling */
            .btn-search-theme {
                background-color: #ffffff;
                color: #4b5563;
                border: 1px solid #d1d5db;
                font-size: 13px;
                font-family: 'Inter, system-ui, sans-serif';
                padding: 0.5rem;
                border-radius: 0.5rem;
                transition: all 200ms;
            }
            .btn-search-theme:hover {
                background-color: #f9fafb;
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            /* Clear Button Styling */  
            .btn-clear-theme {
                background-color: transparent;
                color: #6b7280;
                border: 1px solid #e5e7eb;
                font-size: 13px;
                font-family: 'Inter, system-ui, sans-serif';
                padding: 0.5rem;
                border-radius: 0.5rem;
                transition: all 200ms;
            }
            .btn-clear-theme:hover {
                background-color: #f3f4f6;
                color: #4b5563;
                border-color: #6b7280;
            }
            
            /* Card Styling - Default */
            .card-theme {
                background-color: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                padding: 1rem;
                transition: all 200ms;
            }
            .card-theme:hover {
                background-color: #f3f4f6;
                transform: translateY(-1px);
            }
            
            /* Icon Container Styling - Default */
            .icon-container-primary {
                background-color: #68757a;
                color: #ffffff;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-success {
                background-color: #10b981;
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-danger {
                background-color: #ef4444;
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-warning {
                background-color: #f59e0b;
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .icon-container-info {
                background-color: #3b82f6;
                color: white;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
        ";
    }
}