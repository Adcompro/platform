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
        // Add service fields to project_milestones
        Schema::table('project_milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('project_milestones', 'is_service_item')) {
                $table->boolean('is_service_item')->default(false)->after('source_id')
                    ->comment('Indicates if this milestone was imported from a service');
            }
            if (!Schema::hasColumn('project_milestones', 'service_name')) {
                $table->string('service_name')->nullable()->after('is_service_item')
                    ->comment('Custom service name (e.g. Webdesign example.com)');
            }
            if (!Schema::hasColumn('project_milestones', 'service_color')) {
                $table->string('service_color', 7)->nullable()->default('#3B82F6')->after('service_name')
                    ->comment('Color for visual identification of service items');
            }
            if (!Schema::hasColumn('project_milestones', 'original_service_id')) {
                $table->unsignedBigInteger('original_service_id')->nullable()->after('service_color')
                    ->comment('Reference to original service');
            }
        });

        // Add service fields to project_tasks
        Schema::table('project_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('project_tasks', 'is_service_item')) {
                $table->boolean('is_service_item')->default(false)->after('source_id')
                    ->comment('Indicates if this task was imported from a service');
            }
            if (!Schema::hasColumn('project_tasks', 'service_name')) {
                $table->string('service_name')->nullable()->after('is_service_item')
                    ->comment('Custom service name');
            }
            if (!Schema::hasColumn('project_tasks', 'service_color')) {
                $table->string('service_color', 7)->nullable()->default('#3B82F6')->after('service_name')
                    ->comment('Color for visual identification');
            }
            if (!Schema::hasColumn('project_tasks', 'original_service_id')) {
                $table->unsignedBigInteger('original_service_id')->nullable()->after('service_color')
                    ->comment('Reference to original service');
            }
        });

        // Add service fields to project_subtasks
        Schema::table('project_subtasks', function (Blueprint $table) {
            if (!Schema::hasColumn('project_subtasks', 'is_service_item')) {
                $table->boolean('is_service_item')->default(false)->after('source_id')
                    ->comment('Indicates if this subtask was imported from a service');
            }
            if (!Schema::hasColumn('project_subtasks', 'service_name')) {
                $table->string('service_name')->nullable()->after('is_service_item')
                    ->comment('Custom service name');
            }
            if (!Schema::hasColumn('project_subtasks', 'service_color')) {
                $table->string('service_color', 7)->nullable()->default('#3B82F6')->after('service_name')
                    ->comment('Color for visual identification');
            }
            if (!Schema::hasColumn('project_subtasks', 'original_service_id')) {
                $table->unsignedBigInteger('original_service_id')->nullable()->after('service_color')
                    ->comment('Reference to original service');
            }
        });

        // Update project_services table to store custom name
        Schema::table('project_services', function (Blueprint $table) {
            if (!Schema::hasColumn('project_services', 'custom_name')) {
                $table->string('custom_name')->nullable()->after('service_id')
                    ->comment('Custom name for this service instance (e.g. Webdesign example.com)');
            }
            if (!Schema::hasColumn('project_services', 'import_status')) {
                $table->enum('import_status', ['pending', 'imported', 'failed'])->default('pending')->after('custom_name')
                    ->comment('Status of service structure import');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn(['is_service_item', 'service_name', 'service_color', 'original_service_id']);
        });

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['is_service_item', 'service_name', 'service_color', 'original_service_id']);
        });

        Schema::table('project_subtasks', function (Blueprint $table) {
            $table->dropColumn(['is_service_item', 'service_name', 'service_color', 'original_service_id']);
        });

        Schema::table('project_services', function (Blueprint $table) {
            $table->dropColumn(['custom_name', 'import_status']);
        });
    }
};