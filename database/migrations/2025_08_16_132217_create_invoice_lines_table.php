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
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('project_monthly_fee_id')->nullable()->constrained('project_monthly_fees');
            $table->enum('line_type', ['hours', 'milestone', 'service', 'adjustment', 'custom', 'budget_adjustment']);
            $table->text('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price_ex_vat', 10, 2);
            $table->decimal('fee_capped_amount', 12, 2)->nullable();
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('line_total_ex_vat', 12, 2);
            $table->decimal('line_vat_amount', 12, 2)->default(0);
            $table->boolean('is_merged_line')->default(false);
            $table->json('source_data')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'sort_order']);
            $table->index(['line_type', 'project_monthly_fee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};