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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled', 'on_hold'])
                  ->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('monthly_fee', 12, 2)->nullable(); // Was monthly_budget
            $table->date('fee_start_date')->nullable(); // Was budget_start_date
            $table->boolean('fee_rollover_enabled')->default(true); // Was budget_rollover_enabled
            $table->decimal('default_hourly_rate', 8, 2)->nullable();
            $table->foreignId('main_invoicing_company_id')->nullable()->constrained('companies');
            $table->decimal('vat_rate', 5, 2)->default(21.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['customer_id', 'status']);
            $table->index(['main_invoicing_company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};