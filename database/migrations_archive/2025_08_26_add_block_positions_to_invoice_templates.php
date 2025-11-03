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
            // Add columns for custom colors
            $table->string('primary_color', 7)->nullable()->after('color_scheme');
            $table->string('secondary_color', 7)->nullable()->after('primary_color');
            $table->string('accent_color', 7)->nullable()->after('secondary_color');
            
            // Add block positions for drag-and-drop builder
            $table->json('block_positions')->nullable()->after('blade_template');
            
            // Add more visibility flags
            $table->boolean('show_header')->default(true)->after('show_logo');
            $table->boolean('show_subtotals')->default(true)->after('show_footer');
            $table->boolean('show_tax_details')->default(true)->after('show_subtotals');
            $table->boolean('show_discount_section')->default(false)->after('show_tax_details');
            $table->boolean('show_notes_section')->default(true)->after('show_discount_section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color',
                'secondary_color',
                'accent_color',
                'block_positions',
                'show_header',
                'show_subtotals',
                'show_tax_details',
                'show_discount_section',
                'show_notes_section'
            ]);
        });
    }
};