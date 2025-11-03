<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvoiceTemplate;

class UpdateInvoiceTemplateBlocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default blocks for standard invoice template
        $defaultBlocks = [
            [
                'id' => 'header_1',
                'type' => 'header',
                'name' => 'Header',
                'order' => 0,
                'config' => [
                    'show_logo' => true,
                    'header_style' => 'standard',
                    'show_invoice_number' => true
                ]
            ],
            [
                'id' => 'company_info_1',
                'type' => 'company_info',
                'name' => 'Company Information',
                'order' => 1,
                'config' => [
                    'show_vat' => true,
                    'show_coc' => true,
                    'show_email' => true,
                    'show_phone' => true
                ]
            ],
            [
                'id' => 'customer_info_1',
                'type' => 'customer_info',
                'name' => 'Customer Information',
                'order' => 2,
                'config' => [
                    'show_customer_vat' => false,
                    'show_contact_person' => true,
                    'address_format' => 'block'
                ]
            ],
            [
                'id' => 'invoice_details_1',
                'type' => 'invoice_details',
                'name' => 'Invoice Details',
                'order' => 3,
                'config' => [
                    'show_invoice_date' => true,
                    'show_due_date' => true,
                    'show_payment_terms' => true
                ]
            ],
            [
                'id' => 'line_items_1',
                'type' => 'line_items',
                'name' => 'Invoice Lines',
                'order' => 4,
                'config' => [
                    'group_by_milestone' => true,
                    'show_task_descriptions' => true,
                    'show_hours' => true,
                    'show_rate' => true
                ]
            ],
            [
                'id' => 'additional_costs_1',
                'type' => 'additional_costs',
                'name' => 'Additional Costs',
                'order' => 5,
                'config' => [
                    'show_recurring' => true,
                    'show_one_time' => true,
                    'show_category' => true
                ]
            ],
            [
                'id' => 'subtotal_1',
                'type' => 'subtotal',
                'name' => 'Subtotal',
                'order' => 6,
                'config' => [
                    'show_line_items_subtotal' => true,
                    'show_additional_costs_subtotal' => true
                ]
            ],
            [
                'id' => 'tax_section_1',
                'type' => 'tax_section',
                'name' => 'Tax Details',
                'order' => 7,
                'config' => [
                    'tax_rate' => 21,
                    'show_tax_breakdown' => true
                ]
            ],
            [
                'id' => 'total_section_1',
                'type' => 'total_section',
                'name' => 'Total Amount',
                'order' => 8,
                'config' => [
                    'show_currency_symbol' => true,
                    'highlight_total' => true
                ]
            ],
            [
                'id' => 'payment_terms_1',
                'type' => 'payment_terms',
                'name' => 'Payment Terms',
                'order' => 9,
                'config' => [
                    'payment_days' => 30
                ]
            ],
            [
                'id' => 'bank_details_1',
                'type' => 'bank_details',
                'name' => 'Bank Details',
                'order' => 10,
                'config' => [
                    'show_bank_name' => true,
                    'show_iban' => true,
                    'show_bic' => true
                ]
            ],
            [
                'id' => 'footer_1',
                'type' => 'footer',
                'name' => 'Footer',
                'order' => 11,
                'config' => [
                    'show_page_numbers' => true
                ]
            ]
        ];

        // Update all existing templates without block_positions
        $templates = InvoiceTemplate::whereNull('block_positions')->get();
        
        foreach ($templates as $template) {
            $template->block_positions = json_encode($defaultBlocks);
            $template->save();
            
            $this->command->info("Updated template: {$template->name}");
        }

        // Create some variations for different template types
        $modernBlocks = [
            [
                'id' => 'header_1',
                'type' => 'header',
                'name' => 'Header',
                'order' => 0,
                'config' => [
                    'show_logo' => true,
                    'header_style' => 'modern',
                    'show_invoice_number' => true
                ]
            ],
            [
                'id' => 'invoice_details_1',
                'type' => 'invoice_details', 
                'name' => 'Invoice Details',
                'order' => 1,
                'config' => [
                    'show_invoice_date' => true,
                    'show_due_date' => true
                ]
            ],
            [
                'id' => 'customer_info_1',
                'type' => 'customer_info',
                'name' => 'Customer Information',
                'order' => 2,
                'config' => [
                    'address_format' => 'inline'
                ]
            ],
            [
                'id' => 'line_items_1',
                'type' => 'line_items',
                'name' => 'Invoice Lines',
                'order' => 3,
                'config' => [
                    'group_by_milestone' => false,
                    'show_line_numbers' => true
                ]
            ],
            [
                'id' => 'total_section_1',
                'type' => 'total_section',
                'name' => 'Total Amount',
                'order' => 4,
                'config' => [
                    'show_in_words' => true
                ]
            ],
            [
                'id' => 'qr_code_1',
                'type' => 'qr_code',
                'name' => 'QR Code',
                'order' => 5,
                'config' => [
                    'qr_type' => 'payment',
                    'qr_size' => 'small'
                ]
            ]
        ];

        // Update Modern Invoice template if exists
        $modernTemplate = InvoiceTemplate::where('name', 'Modern Invoice')->first();
        if ($modernTemplate && !$modernTemplate->block_positions) {
            $modernTemplate->block_positions = json_encode($modernBlocks);
            $modernTemplate->save();
            $this->command->info("Updated Modern Invoice template with custom blocks");
        }
    }
}