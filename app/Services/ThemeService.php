<?php

namespace App\Services;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\View;

class ThemeService
{
    protected $theme;

    public function __construct()
    {
        $this->theme = ThemeSetting::active();
    }

    /**
     * Share theme data met alle views
     */
    public function shareWithViews()
    {
        View::share('theme', $this->theme);
        View::share('themeStyles', $this->generateStyles());
    }

    /**
     * Generate inline styles voor theme
     */
    public function generateStyles()
    {
        // If no theme exists, create default
        if (!$this->theme) {
            $this->theme = ThemeSetting::createDefault();
        }
        
        if (!$this->theme) {
            return '';
        }

        $styles = $this->theme->getCssVariables();

        // Voeg custom CSS toe als het bestaat
        if ($this->theme->custom_css) {
            $styles .= "\n" . $this->theme->custom_css;
        }

        // Generate component-specific styles
        $styles .= $this->generateComponentStyles();

        return $styles;
    }

    /**
     * Generate component-specific styles based on theme settings
     */
    protected function generateComponentStyles()
    {
        $styles = "
            /* Primary Buttons */
            .btn-primary {
                background-color: var(--primary-color);
                color: var(--primary-text);
                transition: all var(--transition-speed);
            }
            .btn-primary:hover {
                background-color: var(--primary-hover);
            }

            /* Secondary Buttons */
            .btn-secondary {
                background-color: var(--secondary-color);
                border-color: var(--secondary-border);
                color: var(--text-primary);
                transition: all var(--transition-speed);
            }
            .btn-secondary:hover {
                background-color: var(--secondary-hover);
                border-color: var(--accent-color);
            }

            /* Links */
            .link-primary {
                color: var(--accent-color);
            }
            .link-primary:hover {
                color: var(--accent-hover);
            }

            /* Focus states */
            .form-input:focus {
                border-color: var(--accent-color);
                outline-color: var(--accent-color);
            }

            /* Badges */
            .badge-primary {
                background-color: var(--badge-bg);
                color: var(--badge-text);
            }

            /* Text colors */
            .text-primary-theme {
                color: var(--text-primary);
            }
            .text-secondary-theme {
                color: var(--text-secondary);
            }
            .text-muted-theme {
                color: var(--text-muted);
            }

            /* Font sizes */
            .text-base-theme {
                font-size: var(--font-size-base);
            }
            .text-small-theme {
                font-size: var(--font-size-small);
            }
            .text-large-theme {
                font-size: var(--font-size-large);
            }
            .text-xl-theme {
                font-size: var(--font-size-xl);
            }
        ";

        // Add shadow styles if enabled
        if (!$this->theme->use_shadows) {
            $styles .= "
                .shadow-sm, .shadow, .shadow-md, .shadow-lg, .shadow-xl {
                    box-shadow: none !important;
                }
            ";
        }

        // Add animation styles if disabled
        if (!$this->theme->use_animations) {
            $styles .= "
                * {
                    transition: none !important;
                    animation: none !important;
                }
            ";
        }

        return $styles;
    }

    /**
     * Get button classes based on theme
     */
    public function getButtonClasses($type = 'primary')
    {
        $baseClasses = "inline-flex items-center {$this->theme->button_padding} text-[{$this->theme->font_size_base}] font-normal rounded-lg";
        
        if ($type === 'primary') {
            return $baseClasses . " btn-primary";
        } elseif ($type === 'secondary') {
            return $baseClasses . " btn-secondary";
        }
        
        return $baseClasses;
    }

    /**
     * Get header classes based on theme
     */
    public function getHeaderClasses()
    {
        return "text-[{$this->theme->font_size_xl}] font-semibold text-primary-theme";
    }

    /**
     * Get card classes based on theme
     */
    public function getCardClasses()
    {
        $classes = "bg-white border border-gray-200 rounded-lg overflow-hidden";
        
        if ($this->theme->use_shadows) {
            $classes .= " shadow-sm";
        }
        
        return $classes;
    }

    /**
     * Check if theme customization is enabled (plugin check)
     */
    public function isCustomizationEnabled()
    {
        // Check if theme customization plugin is active
        try {
            $plugin = \App\Models\Plugin::where('name', 'theme_customization')->first();
            return $plugin && $plugin->is_active;
        } catch (\Exception $e) {
            // Als plugins tabel niet bestaat, return false
            return false;
        }
    }
}