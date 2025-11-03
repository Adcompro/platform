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
        Schema::table('project_templates', function (Blueprint $table) {
            // Add columns without specifying position since we don't know exact structure
            if (!Schema::hasColumn('project_templates', 'total_estimated_hours')) {
                $table->decimal('total_estimated_hours', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('project_templates', 'estimated_total_value')) {
                $table->decimal('estimated_total_value', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('project_templates', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
            if (!Schema::hasColumn('project_templates', 'estimated_duration_days')) {
                $table->integer('estimated_duration_days')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_templates', function (Blueprint $table) {
            if (Schema::hasColumn('project_templates', 'total_estimated_hours')) {
                $table->dropColumn('total_estimated_hours');
            }
            if (Schema::hasColumn('project_templates', 'estimated_total_value')) {
                $table->dropColumn('estimated_total_value');
            }
            if (Schema::hasColumn('project_templates', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('project_templates', 'estimated_duration_days')) {
                $table->dropColumn('estimated_duration_days');
            }
        });
    }
};