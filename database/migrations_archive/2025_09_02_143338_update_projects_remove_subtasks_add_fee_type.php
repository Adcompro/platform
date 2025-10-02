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
        // First, create an archive table for existing subtasks data
        Schema::create('archived_project_subtasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id');
            $table->unsignedBigInteger('project_task_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee');
            $table->enum('pricing_type', ['hourly_rate', 'fixed_price'])->default('hourly_rate');
            $table->decimal('fixed_price', 10, 2)->nullable();
            $table->decimal('hourly_rate_override', 8, 2)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->boolean('is_service_item')->default(false);
            $table->string('service_name')->nullable();
            $table->string('service_color', 7)->nullable();
            $table->unsignedBigInteger('original_service_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('archived_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
            
            $table->index(['project_task_id']);
            $table->index(['original_id']);
        });

        // Copy existing subtasks to archive table
        if (Schema::hasTable('project_subtasks')) {
            DB::statement('
                INSERT INTO archived_project_subtasks (
                    original_id, project_task_id, name, description, status, 
                    start_date, end_date, sort_order, fee_type, pricing_type,
                    fixed_price, hourly_rate_override, estimated_hours,
                    source_type, source_id, is_service_item, service_name,
                    service_color, original_service_id, deleted_at,
                    created_at, updated_at
                )
                SELECT 
                    id, project_task_id, name, description, status,
                    start_date, end_date, sort_order, fee_type, pricing_type,
                    fixed_price, hourly_rate_override, estimated_hours,
                    source_type, source_id, is_service_item, service_name,
                    service_color, original_service_id, deleted_at,
                    created_at, updated_at
                FROM project_subtasks
            ');
        }

        // Add fee_type to project_tasks if it doesn't exist
        Schema::table('project_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('project_tasks', 'fee_type')) {
                $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee')->after('sort_order');
            }
        });

        // Add fee_type to project_milestones if it doesn't exist
        Schema::table('project_milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('project_milestones', 'fee_type')) {
                $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee')->after('sort_order');
            }
        });

        // Drop foreign key constraints first
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        // Now drop the project_subtasks table
        Schema::dropIfExists('project_subtasks');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate project_subtasks table
        Schema::create('project_subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('fee_type', ['in_fee', 'extended'])->default('in_fee');
            $table->enum('pricing_type', ['hourly_rate', 'fixed_price'])->default('hourly_rate');
            $table->decimal('fixed_price', 10, 2)->nullable();
            $table->decimal('hourly_rate_override', 8, 2)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->boolean('is_service_item')->default(false);
            $table->string('service_name')->nullable();
            $table->string('service_color', 7)->nullable();
            $table->unsignedBigInteger('original_service_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['project_task_id', 'sort_order']);
            $table->index(['status']);
        });

        // Restore subtasks from archive
        DB::statement('
            INSERT INTO project_subtasks (
                id, project_task_id, name, description, status, 
                start_date, end_date, sort_order, fee_type, pricing_type,
                fixed_price, hourly_rate_override, estimated_hours,
                source_type, source_id, is_service_item, service_name,
                service_color, original_service_id, deleted_at,
                created_at, updated_at
            )
            SELECT 
                original_id, project_task_id, name, description, status,
                start_date, end_date, sort_order, fee_type, pricing_type,
                fixed_price, hourly_rate_override, estimated_hours,
                source_type, source_id, is_service_item, service_name,
                service_color, original_service_id, deleted_at,
                created_at, updated_at
            FROM archived_project_subtasks
        ');

        // Remove fee_type from project_tasks
        Schema::table('project_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('project_tasks', 'fee_type')) {
                $table->dropColumn('fee_type');
            }
        });

        // Remove fee_type from project_milestones
        Schema::table('project_milestones', function (Blueprint $table) {
            if (Schema::hasColumn('project_milestones', 'fee_type')) {
                $table->dropColumn('fee_type');
            }
        });

        // Drop archive table
        Schema::dropIfExists('archived_project_subtasks');
    }
};