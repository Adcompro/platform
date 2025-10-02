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
        Schema::table('ai_settings', function (Blueprint $table) {
            // AI Time Entry Settings - General/Default settings
            $table->boolean('ai_time_entry_enabled')->default(true)->after('ai_learning_enabled');
            $table->text('ai_time_entry_default_rules')->nullable()->comment('Default naming rules for all projects');
            $table->json('ai_time_entry_default_categories')->nullable()->comment('Default task categories');
            $table->json('ai_time_entry_example_patterns')->nullable()->comment('Default good naming examples');
            $table->text('ai_time_entry_prompt_template')->nullable()->comment('Global prompt template for time entry improvement');
            $table->integer('ai_time_entry_max_length')->default(100)->comment('Maximum character length for descriptions');
            $table->boolean('ai_time_entry_auto_improve')->default(false)->comment('Automatically improve descriptions on save');
            $table->boolean('ai_time_entry_learn_from_history')->default(true)->comment('Learn from recent time entries');
            $table->integer('ai_time_entry_history_days')->default(30)->comment('Days of history to consider for learning');
        });
        
        // Set default values for new fields
        \App\Models\AiSetting::query()->update([
            'ai_time_entry_default_rules' => "- Focus on creating CONSISTENT and CLEAR descriptions\n- Keep descriptions concise but informative\n- Use consistent terminology throughout projects\n- Standardize similar activities into consistent descriptions\n- Be specific about what was done but avoid unnecessary details\n- Use action verbs in past tense (Fixed, Updated, Implemented, etc.)",
            'ai_time_entry_default_categories' => json_encode([
                'development',
                'bugfix',
                'meeting',
                'documentation',
                'testing',
                'design',
                'support',
                'deployment',
                'review',
                'planning',
                'research',
                'optimization'
            ]),
            'ai_time_entry_example_patterns' => json_encode([
                'Fixed authentication bug in login API',
                'Optimized database queries for dashboard',
                'Updated user interface layout for mobile view',
                'Attended sprint planning meeting',
                'Updated API documentation for v2 endpoints',
                'Resolved merge conflicts in feature branch',
                'Implemented unit tests for payment module',
                'Configured CI/CD pipeline for staging environment'
            ]),
            'ai_time_entry_prompt_template' => "You are an AI assistant helping to improve time entry descriptions. Your goal is to make descriptions consistent, clear, and professional while maintaining the original meaning.\n\nRules:\n{rules}\n\nCategories available: {categories}\n\nExamples of good descriptions:\n{examples}\n\nPlease improve the following description while keeping it under {max_length} characters:"
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ai_time_entry_enabled',
                'ai_time_entry_default_rules',
                'ai_time_entry_default_categories',
                'ai_time_entry_example_patterns',
                'ai_time_entry_prompt_template',
                'ai_time_entry_max_length',
                'ai_time_entry_auto_improve',
                'ai_time_entry_learn_from_history',
                'ai_time_entry_history_days'
            ]);
        });
    }
};