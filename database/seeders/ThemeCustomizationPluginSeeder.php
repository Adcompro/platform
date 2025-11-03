<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plugin;

class ThemeCustomizationPluginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Zoek of plugin al bestaat
        $plugin = Plugin::where('name', 'theme_customization')->first();
        
        if (!$plugin) {
            Plugin::create([
                'name' => 'theme_customization',
                'display_name' => 'Theme Customization',
                'description' => 'Advanced theme customization per installation. Allows complete control over colors, typography, branding, and visual styles. Perfect for white-label deployments and custom client branding.',
                'icon' => 'fa-palette',
                'category' => 'general',
                'is_active' => false, // Standaard uit, moet per klant geactiveerd worden
                'is_core' => false,
                'dependencies' => [],
                'routes' => [
                    'theme.settings',
                    'theme.update', 
                    'theme.preset',
                    'theme.reset',
                    'theme.upload.logo',
                    'theme.upload.favicon',
                    'theme.preview'
                ],
                'permissions' => ['super_admin', 'admin'], // Alleen admins kunnen theme aanpassen
                'settings' => [
                    'allow_custom_css' => true,
                    'allow_logo_upload' => true,
                    'allow_favicon_upload' => true,
                    'max_logo_size_mb' => 2,
                    'max_favicon_size_kb' => 512,
                    'available_presets' => ['progress', 'modern', 'corporate', 'minimal', 'vibrant']
                ],
                'sort_order' => 100,
                'version' => '1.0.0',
                'author' => 'AdCompro',
                'url' => 'https://adcompro.app'
            ]);
            
            $this->command->info('Theme Customization plugin added successfully.');
        } else {
            // Update existing plugin to ensure all fields are correct
            $plugin->update([
                'display_name' => 'Theme Customization',
                'description' => 'Advanced theme customization per installation. Allows complete control over colors, typography, branding, and visual styles. Perfect for white-label deployments and custom client branding.',
                'icon' => 'fa-palette',
                'category' => 'general',
                'is_core' => false,
                'dependencies' => [],
                'routes' => [
                    'theme.settings',
                    'theme.update',
                    'theme.preset', 
                    'theme.reset',
                    'theme.upload.logo',
                    'theme.upload.favicon',
                    'theme.preview'
                ],
                'permissions' => ['super_admin', 'admin'],
                'settings' => [
                    'allow_custom_css' => true,
                    'allow_logo_upload' => true,
                    'allow_favicon_upload' => true,
                    'max_logo_size_mb' => 2,
                    'max_favicon_size_kb' => 512,
                    'available_presets' => ['progress', 'modern', 'corporate', 'minimal', 'vibrant']
                ],
                'sort_order' => 100,
                'version' => '1.0.0',
                'author' => 'AdCompro',
                'url' => 'https://adcompro.app'
            ]);
            
            $this->command->info('Theme Customization plugin updated successfully.');
        }
    }
}