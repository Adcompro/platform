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
        // Check if table exists, if not create it
        if (!Schema::hasTable('project_ai_settings')) {
            Schema::create('project_ai_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->unique()->constrained()->onDelete('cascade');
                $table->boolean('use_global_settings')->default(true);
                $table->boolean('is_active')->default(true);
                $table->text('ai_naming_rules')->nullable();
                $table->text('ai_prompt_template')->nullable();
                $table->json('ai_task_categories')->nullable();
                $table->json('ai_keywords')->nullable();
                $table->json('ai_example_patterns')->nullable();
                $table->timestamps();
            });
        } else {
            // Table exists, add missing columns
            Schema::table('project_ai_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('project_ai_settings', 'ai_naming_rules')) {
                    $table->text('ai_naming_rules')->nullable();
                }
                if (!Schema::hasColumn('project_ai_settings', 'ai_prompt_template')) {
                    $table->text('ai_prompt_template')->nullable();
                }
                if (!Schema::hasColumn('project_ai_settings', 'ai_task_categories')) {
                    $table->json('ai_task_categories')->nullable();
                }
                if (!Schema::hasColumn('project_ai_settings', 'ai_keywords')) {
                    $table->json('ai_keywords')->nullable();
                }
                if (!Schema::hasColumn('project_ai_settings', 'ai_example_patterns')) {
                    $table->json('ai_example_patterns')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table, just remove the new columns
        if (Schema::hasTable('project_ai_settings')) {
            Schema::table('project_ai_settings', function (Blueprint $table) {
                $columns = ['ai_naming_rules', 'ai_prompt_template', 'ai_task_categories', 'ai_keywords', 'ai_example_patterns'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('project_ai_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};