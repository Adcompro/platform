<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class InvoiceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Invoice timing settings
        $invoiceSettings = [
            [
                'key' => 'invoice_monthly_day',
                'value' => 'last',
                'type' => 'string',
                'description' => 'When to generate monthly invoices (last, first_next, or day number)'
            ],
            [
                'key' => 'invoice_quarterly_timing',
                'value' => 'quarter_end',
                'type' => 'string',
                'description' => 'When to generate quarterly invoices (quarter_end, quarter_start, quarter_after_15)'
            ],
            [
                'key' => 'invoice_milestone_days',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Days after milestone completion to generate invoice'
            ],
            [
                'key' => 'invoice_project_completion_days',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Days after project completion to generate final invoice'
            ],
            [
                'key' => 'invoice_due_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Default payment terms in days for generated invoices'
            ],
            [
                'key' => 'invoice_auto_generate',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Whether to automatically generate draft invoices when due dates are reached'
            ],
            [
                'key' => 'invoice_number_prefix',
                'value' => 'INV-',
                'type' => 'string',
                'description' => 'Prefix for invoice numbers'
            ],
            [
                'key' => 'invoice_number_start',
                'value' => '1000',
                'type' => 'integer',
                'description' => 'Starting number for invoices'
            ],
            [
                'key' => 'invoice_number_format',
                'value' => '{prefix}{year}{number}',
                'type' => 'string',
                'description' => 'Format for invoice numbers. Available: {prefix}, {year}, {month}, {number}'
            ],
        ];

        foreach ($invoiceSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type']
                ]
            );
        }

        echo "Invoice settings seeded successfully.\n";
    }
}