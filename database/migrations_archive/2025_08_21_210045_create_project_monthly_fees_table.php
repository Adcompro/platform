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
        Schema::create('project_monthly_fees', function (Blueprint $table) {
            $table->id();
            
            // Project relationship
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Period tracking
            $table->integer('year');
            $table->integer('month');
            $table->date('period_start');
            $table->date('period_end');
            
            // Budget amounts
            $table->decimal('monthly_budget', 10, 2)->default(0); // Base monthly budget
            $table->decimal('rollover_from_previous', 10, 2)->default(0); // Rollover from previous month
            $table->decimal('total_budget', 10, 2)->default(0); // Total available (monthly + rollover)
            
            // Costs breakdown
            $table->decimal('time_entry_costs', 10, 2)->default(0); // Costs from approved time entries
            $table->decimal('time_entry_hours', 8, 2)->default(0); // Total approved hours
            $table->decimal('additional_costs_onetime', 10, 2)->default(0); // One-time additional costs
            $table->decimal('additional_costs_recurring', 10, 2)->default(0); // Monthly recurring costs
            $table->decimal('total_costs', 10, 2)->default(0); // Total of all costs
            
            // Budget status
            $table->decimal('budget_used', 10, 2)->default(0); // Amount used from budget
            $table->decimal('budget_remaining', 10, 2)->default(0); // Remaining budget
            $table->decimal('budget_exceeded', 10, 2)->default(0); // Amount over budget (if applicable)
            $table->decimal('rollover_to_next', 10, 2)->default(0); // Amount to rollover to next month
            
            // Counts for statistics
            $table->integer('time_entries_count')->default(0);
            $table->integer('additional_costs_count')->default(0);
            
            // Status and processing
            $table->enum('status', ['draft', 'calculated', 'approved', 'invoiced'])->default('draft');
            $table->boolean('is_locked')->default(false); // Locked after invoicing
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            $table->json('calculation_details')->nullable(); // Store detailed breakdown
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->unique(['project_id', 'year', 'month']);
            $table->index(['year', 'month']);
            $table->index('status');
            
            // Foreign keys
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_monthly_fees');
    }
};