<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\SimplifiedThemeSetting;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for fresh installation.
     */
    public function run(): void
    {
        // 1. Create default company
        $company = Company::create([
            'name' => 'AdCompro BV',
            'email' => 'info@adcompro.app',
            'phone' => '+31 (0)20 123 4567',
            'address' => 'Hoofdstraat 1',
            'city' => 'Amsterdam',
            'postal_code' => '1000 AA',
            'country' => 'Nederland',
            'default_hourly_rate' => 85.00,
            'is_main_invoicing' => true,
            'is_active' => true,
        ]);

        // 2. Create super admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@adcompro.app',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'company_id' => $company->id,
            'is_active' => true,
        ]);

        // 3. Create default theme (global)
        SimplifiedThemeSetting::create([
            'company_id' => null, // Global theme
            'name' => 'Default Theme',
            'is_active' => true,
            'primary_color' => '#3b82f6',
            'secondary_color' => '#64748b',
            'accent_color' => '#f59e0b',
            'font_size_base' => '14px',
            'table_header_style' => 'light',
            'menu_style' => 'teamleader',
            'sidebar_style' => 'dark',
        ]);

        // 4. Basic system settings
        $settings = [
            ['key' => 'app_timezone', 'value' => 'Europe/Amsterdam', 'type' => 'string'],
            ['key' => 'currency', 'value' => 'EUR', 'type' => 'string'],
            ['key' => 'currency_symbol', 'value' => '€', 'type' => 'string'],
            ['key' => 'vat_rate', 'value' => '21', 'type' => 'number'],
            ['key' => 'date_format', 'value' => 'd-m-Y', 'type' => 'string'],
            ['key' => 'time_format', 'value' => 'H:i', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Email: admin@adcompro.app');
        $this->command->info('Password: admin123');
        $this->command->info('');
        $this->command->warn('⚠️  CHANGE THE PASSWORD IMMEDIATELY AFTER FIRST LOGIN!');
    }
}
