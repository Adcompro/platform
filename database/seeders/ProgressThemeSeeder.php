<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SimplifiedThemeSetting;

class ProgressThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if theme already exists
        $existingTheme = SimplifiedThemeSetting::where('is_active', true)
            ->whereNull('company_id')
            ->first();
            
        if ($existingTheme) {
            // Update existing theme to Progress preset
            $existingTheme->update([
                'name' => 'Progress Communications Theme',
                'preset_name' => 'progress',
                // Progress Communications corporate colors
                'primary_color' => '#FF6600',      // Progress helder oranje (website kleur)
                'accent_color' => '#00A6E0',       // Progress blauw accent
                'danger_color' => '#DC3545',       // Professioneel rood
                'text_color' => '#2B3E50',         // Donker grijs-blauw voor optimale leesbaarheid
                'muted_text_color' => '#6C757D',   // Standaard muted grijs
                'background_color' => '#FAFAFA',   // Licht grijs voor subtiele achtergrond
                // Typography settings
                'font_family' => 'inter',          // Modern, clean font zoals op website
                'font_size_base' => '12px',        
                'header_font_size' => 'normal',    
                'line_height' => 'normal',         
                // Button styling
                'button_size' => 'normal',
                'button_text_color' => 'white',
                'button_radius' => 'small',       
                'button_style' => 'solid',
                // Table settings
                'table_row_padding' => 'normal',
                'table_header_style' => 'light',
                'table_striped' => false,
                'table_hover_effect' => 'light',
                // Layout
                'header_height' => 'normal',
                'sidebar_width' => 'normal',
                'card_padding' => 'normal',
                'card_shadow' => 'small',
                'border_radius' => 'small',
            ]);
            
            echo "Progress Communications theme updated successfully!\n";
        } else {
            // Create new theme
            SimplifiedThemeSetting::create([
                'company_id' => null,
                'name' => 'Progress Communications Theme',
                'preset_name' => 'progress',
                'is_active' => true,
                // Progress Communications brand colors
                'primary_color' => '#FF6B35',      
                'accent_color' => '#F39C12',       
                'danger_color' => '#E74C3C',       
                'text_color' => '#2C3E50',         
                'muted_text_color' => '#7F8C8D',   
                'background_color' => '#FFFFFF',   
                // Typography
                'font_family' => 'inter',
                'font_size_base' => '12px',
                'header_font_size' => 'normal',
                'line_height' => 'normal',
                // Buttons
                'button_size' => 'normal',
                'button_text_color' => 'white',
                'button_radius' => 'small',
                'button_style' => 'solid',
                // Tables
                'table_row_padding' => 'normal',
                'table_header_style' => 'light',
                'table_striped' => false,
                'table_hover_effect' => 'light',
                // Layout
                'header_height' => 'normal',
                'sidebar_width' => 'normal',
                'card_padding' => 'normal',
                'card_shadow' => 'small',
                'border_radius' => 'small',
            ]);
            
            echo "Progress Communications theme created successfully!\n";
        }
    }
}