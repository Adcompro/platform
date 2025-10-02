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
        Schema::table('services', function (Blueprint $table) {
            // Add soft delete column if it doesn't exist
            if (!Schema::hasColumn('services', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Also add soft deletes to related tables
        Schema::table('service_milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('service_milestones', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        Schema::table('service_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('service_tasks', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        Schema::table('service_subtasks', function (Blueprint $table) {
            if (!Schema::hasColumn('service_subtasks', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('service_milestones', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('service_tasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        Schema::table('service_subtasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
