<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceTemplate;

class InvoiceTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Standard Template
        InvoiceTemplate::create([
            'name' => 'Standard Invoice',
            'slug' => 'standard-invoice',
            'description' => 'Clean and professional standard invoice template',
            'template_type' => 'standard',
            'color_scheme' => 'blue',
            'logo_position' => 'left',
            'show_logo' => true,
            'show_payment_terms' => true,
            'show_bank_details' => true,
            'show_budget_overview' => true,
            'show_additional_costs_section' => true,
            'show_project_details' => true,
            'show_time_entry_details' => false,
            'show_page_numbers' => true,
            'show_footer' => true,
            'header_content' => null,
            'footer_content' => 'Thank you for your business!',
            'payment_terms_text' => 'Payment is due within 30 days of invoice date.',
            'font_family' => 'Inter',
            'font_size' => 'normal',
            'line_spacing' => 'normal',
            'blade_template' => 'invoices.templates.standard',
            'is_active' => true,
            'is_default' => true,
        ]);

        // Modern Template
        InvoiceTemplate::create([
            'name' => 'Modern Invoice',
            'slug' => 'modern-invoice',
            'description' => 'Modern design with gradient accents and clean typography',
            'template_type' => 'modern',
            'color_scheme' => 'purple',
            'logo_position' => 'center',
            'show_logo' => true,
            'show_payment_terms' => true,
            'show_bank_details' => true,
            'show_budget_overview' => true,
            'show_additional_costs_section' => true,
            'show_project_details' => true,
            'show_time_entry_details' => false,
            'show_page_numbers' => false,
            'show_footer' => true,
            'header_content' => null,
            'footer_content' => 'We appreciate your continued partnership.',
            'payment_terms_text' => 'Net 30 - Payment due within 30 days.',
            'font_family' => 'Inter',
            'font_size' => 'normal',
            'line_spacing' => 'relaxed',
            'blade_template' => 'invoices.templates.modern',
            'is_active' => true,
            'is_default' => false,
        ]);

        // Classic Template
        InvoiceTemplate::create([
            'name' => 'Classic Invoice',
            'slug' => 'classic-invoice',
            'description' => 'Traditional invoice layout with formal styling',
            'template_type' => 'classic',
            'color_scheme' => 'gray',
            'logo_position' => 'right',
            'show_logo' => true,
            'show_payment_terms' => true,
            'show_bank_details' => true,
            'show_budget_overview' => false,
            'show_additional_costs_section' => true,
            'show_project_details' => false,
            'show_time_entry_details' => false,
            'show_page_numbers' => true,
            'show_footer' => true,
            'header_content' => 'INVOICE',
            'footer_content' => 'Thank you for your business. Please remit payment at your earliest convenience.',
            'payment_terms_text' => 'Terms: Net 30 days. A 1.5% monthly interest charge will be applied to overdue accounts.',
            'font_family' => 'Georgia',
            'font_size' => 'normal',
            'line_spacing' => 'normal',
            'blade_template' => 'invoices.templates.classic',
            'is_active' => true,
            'is_default' => false,
        ]);

        // Minimal Template
        InvoiceTemplate::create([
            'name' => 'Minimal Invoice',
            'slug' => 'minimal-invoice',
            'description' => 'Clean minimal design focusing on content',
            'template_type' => 'minimal',
            'color_scheme' => 'gray',
            'logo_position' => 'none',
            'show_logo' => false,
            'show_payment_terms' => true,
            'show_bank_details' => true,
            'show_budget_overview' => false,
            'show_additional_costs_section' => false,
            'show_project_details' => false,
            'show_time_entry_details' => false,
            'show_page_numbers' => false,
            'show_footer' => false,
            'header_content' => null,
            'footer_content' => null,
            'payment_terms_text' => 'Due upon receipt.',
            'font_family' => 'Inter',
            'font_size' => 'small',
            'line_spacing' => 'compact',
            'blade_template' => 'invoices.templates.minimal',
            'is_active' => true,
            'is_default' => false,
        ]);

        // Detailed Template (with time entries)
        InvoiceTemplate::create([
            'name' => 'Detailed Invoice',
            'slug' => 'detailed-invoice',
            'description' => 'Comprehensive invoice with all details including time entries',
            'template_type' => 'standard',
            'color_scheme' => 'green',
            'logo_position' => 'left',
            'show_logo' => true,
            'show_payment_terms' => true,
            'show_bank_details' => true,
            'show_budget_overview' => true,
            'show_additional_costs_section' => true,
            'show_project_details' => true,
            'show_time_entry_details' => true,
            'show_page_numbers' => true,
            'show_footer' => true,
            'header_content' => null,
            'footer_content' => 'Thank you for choosing our services. We look forward to working with you again.',
            'payment_terms_text' => 'Payment terms: 30 days net. Please include invoice number with payment.',
            'font_family' => 'Inter',
            'font_size' => 'normal',
            'line_spacing' => 'normal',
            'blade_template' => 'invoices.templates.detailed',
            'is_active' => true,
            'is_default' => false,
        ]);
    }
}