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
        // Add start_date and end_date to template_milestones
        Schema::table('template_milestones', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('template_milestones', 'start_date')) {
                $table->date('start_date')->nullable()->after('duration_days');
            }
            if (!Schema::hasColumn('template_milestones', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        // Add end_date to template_tasks (they already have hourly_rate and estimated_hours)
        Schema::table('template_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('template_tasks', 'start_date')) {
                $table->date('start_date')->nullable()->after('duration_days');
            }
            if (!Schema::hasColumn('template_tasks', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
        
        // Drop the template_subtasks table if it exists
        Schema::dropIfExists('template_subtasks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from template_milestones
        Schema::table('template_milestones', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });

        // Remove added columns from template_tasks
        Schema::table('template_tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });

        // Recreate template_subtasks table
        Schema::create('template_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_task_id')->constrained('template_tasks')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['template_task_id', 'sort_order']);
        });
    }
};