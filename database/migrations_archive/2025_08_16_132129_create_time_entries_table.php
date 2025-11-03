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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('project_milestone_id')->nullable()->constrained('project_milestones');
            $table->foreignId('project_task_id')->nullable()->constrained('project_tasks');
            $table->foreignId('project_subtask_id')->nullable()->constrained('project_subtasks');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('hours', 8, 2);
            $table->text('description');
            $table->text('notes')->nullable();
            $table->decimal('hourly_rate_used', 8, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('is_billable', ['pending', 'billable', 'non_billable'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('invoice_line_id')->nullable();
            $table->boolean('is_invoiced')->default(false);
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'date']);
            $table->index(['project_id', 'status']);
            $table->index(['date', 'status', 'is_billable']);
            $table->index(['company_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};