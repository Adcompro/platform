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
        if (Schema::hasTable('project_ai_settings')) {
            Schema::table('project_ai_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('project_ai_settings', 'ai_task_categories')) {
                    $table->json('ai_task_categories')->nullable()->after('ai_prompt_template');
                }
                if (!Schema::hasColumn('project_ai_settings', 'ai_keywords')) {
                    $table->json('ai_keywords')->nullable()->after('ai_task_categories');
                }
                if (!Schema::hasColumn('project_ai_settings', 'ai_example_patterns')) {
                    $table->json('ai_example_patterns')->nullable()->after('ai_keywords');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_ai_settings')) {
            Schema::table('project_ai_settings', function (Blueprint $table) {
                if (Schema::hasColumn('project_ai_settings', 'ai_task_categories')) {
                    $table->dropColumn('ai_task_categories');
                }
                if (Schema::hasColumn('project_ai_settings', 'ai_keywords')) {
                    $table->dropColumn('ai_keywords');
                }
                if (Schema::hasColumn('project_ai_settings', 'ai_example_patterns')) {
                    $table->dropColumn('ai_example_patterns');
                }
            });
        }
    }
};