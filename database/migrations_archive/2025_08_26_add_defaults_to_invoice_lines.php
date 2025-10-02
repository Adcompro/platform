<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set defaults for existing columns that don't have them
        DB::statement('ALTER TABLE invoice_lines MODIFY unit_price_ex_vat DECIMAL(12,2) DEFAULT 0.00');
        DB::statement('ALTER TABLE invoice_lines MODIFY fee_capped_amount DECIMAL(12,2) DEFAULT NULL');
        DB::statement('ALTER TABLE invoice_lines MODIFY original_amount DECIMAL(12,2) DEFAULT NULL');
        DB::statement('ALTER TABLE invoice_lines MODIFY line_total_ex_vat DECIMAL(12,2) DEFAULT 0.00');
        DB::statement('ALTER TABLE invoice_lines MODIFY line_vat_amount DECIMAL(12,2) DEFAULT 0.00');
        DB::statement('ALTER TABLE invoice_lines MODIFY vat_rate DECIMAL(5,2) DEFAULT 21.00');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove defaults
        DB::statement('ALTER TABLE invoice_lines MODIFY unit_price_ex_vat DECIMAL(12,2)');
        DB::statement('ALTER TABLE invoice_lines MODIFY fee_capped_amount DECIMAL(12,2)');
        DB::statement('ALTER TABLE invoice_lines MODIFY original_amount DECIMAL(12,2)');
        DB::statement('ALTER TABLE invoice_lines MODIFY line_total_ex_vat DECIMAL(12,2)');
        DB::statement('ALTER TABLE invoice_lines MODIFY line_vat_amount DECIMAL(12,2)');
        DB::statement('ALTER TABLE invoice_lines MODIFY vat_rate DECIMAL(5,2)');
    }
};