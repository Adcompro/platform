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
        Schema::create('project_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->enum('role', ['main_invoicing', 'subcontractor'])->default('subcontractor');
            $table->enum('billing_method', ['hourly_rate', 'fixed_monthly'])->default('hourly_rate');
            $table->decimal('hourly_rate_override', 8, 2)->nullable();
            $table->decimal('monthly_fixed_amount', 10, 2)->nullable();
            $table->date('billing_start_date');
            $table->date('billing_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['project_id', 'company_id']);
            
            // Indexes
            $table->index(['project_id', 'role']);
            $table->index(['project_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_companies');
    }
};