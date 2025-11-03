<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->json('block_positions')->nullable()->after('blade_template');
            $table->string('primary_color', 7)->nullable()->after('color_scheme');
            $table->string('secondary_color', 7)->nullable()->after('primary_color');
            $table->string('accent_color', 7)->nullable()->after('secondary_color');
            $table->string('logo_path')->nullable()->after('logo_position');
            $table->boolean('show_header')->default(true)->after('show_logo');
            $table->boolean('show_subtotals')->default(true)->after('show_footer');
            $table->boolean('show_tax_details')->default(true)->after('show_subtotals');
            $table->boolean('show_discount_section')->default(false)->after('show_tax_details');
            $table->boolean('show_notes_section')->default(true)->after('show_discount_section');
        });
        
        // Set default block positions for existing templates
        $defaultBlocks = [
            ['type' => 'header', 'enabled' => true],
            ['type' => 'logo', 'enabled' => true],
            ['type' => 'company_info', 'enabled' => true],
            ['type' => 'customer_info', 'enabled' => true],
            ['type' => 'invoice_details', 'enabled' => true],
            ['type' => 'invoice_lines', 'enabled' => true],
            ['type' => 'totals', 'enabled' => true],
            ['type' => 'payment_info', 'enabled' => true],
            ['type' => 'bank_details', 'enabled' => true],
            ['type' => 'notes', 'enabled' => true],
            ['type' => 'footer', 'enabled' => true]
        ];
        
        DB::table('invoice_templates')->update([
            'block_positions' => json_encode($defaultBlocks)
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropColumn([
                'block_positions',
                'primary_color',
                'secondary_color', 
                'accent_color',
                'logo_path',
                'show_header',
                'show_subtotals',
                'show_tax_details',
                'show_discount_section',
                'show_notes_section'
            ]);
        });
    }
};