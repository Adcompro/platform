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
        Schema::create('monthly_intercompany_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('from_company_id')->constrained('companies');
            $table->foreignId('to_company_id')->constrained('companies');
            $table->integer('year');
            $table->integer('month');
            $table->enum('billing_method', ['hourly_rate', 'fixed_monthly']);
            $table->decimal('agreed_amount', 12, 2);
            $table->decimal('actual_hours_worked', 8, 2)->default(0);
            $table->decimal('actual_hours_value', 12, 2)->default(0);
            $table->decimal('amount_to_charge', 12, 2);
            $table->enum('status', ['draft', 'approved', 'invoiced', 'paid'])->default('draft');
            $table->string('invoice_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['project_id', 'from_company_id', 'to_company_id', 'year', 'month'], 'unique_monthly_charge');
            
            // Indexes
            $table->index(['project_id', 'year', 'month']);
            $table->index(['from_company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_intercompany_charges');
    }
};