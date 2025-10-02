<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            
            // OpenAI Settings
            $table->string('openai_api_key')->nullable();
            $table->string('openai_model')->default('gpt-4o-mini');
            $table->decimal('openai_temperature', 3, 2)->default(0.7);
            $table->integer('openai_max_tokens')->default(2000);
            
            // Claude/Anthropic Settings
            $table->string('anthropic_api_key')->nullable();
            $table->string('anthropic_model')->default('claude-3-haiku-20240307');
            $table->decimal('anthropic_temperature', 3, 2)->default(0.7);
            $table->integer('anthropic_max_tokens')->default(2000);
            
            // General AI Settings
            $table->enum('default_provider', ['openai', 'anthropic'])->default('openai');
            $table->boolean('ai_enabled')->default(true);
            $table->boolean('log_ai_usage')->default(true);
            $table->boolean('show_ai_costs')->default(true);
            
            // Feature Toggles
            $table->boolean('ai_chat_enabled')->default(true);
            $table->boolean('ai_task_generator_enabled')->default(true);
            $table->boolean('ai_time_predictions_enabled')->default(true);
            $table->boolean('ai_invoice_generation_enabled')->default(true);
            $table->boolean('ai_digest_enabled')->default(true);
            $table->boolean('ai_learning_enabled')->default(true);
            
            // Cost Settings (per 1K tokens)
            $table->decimal('openai_input_cost_per_1k', 10, 6)->default(0.00015); // GPT-4o-mini input
            $table->decimal('openai_output_cost_per_1k', 10, 6)->default(0.00060); // GPT-4o-mini output
            $table->decimal('anthropic_input_cost_per_1k', 10, 6)->default(0.003); // Claude 3.5 Sonnet input
            $table->decimal('anthropic_output_cost_per_1k', 10, 6)->default(0.015); // Claude 3.5 Sonnet output
            
            // Rate Limiting
            $table->integer('max_requests_per_minute')->default(60);
            $table->integer('max_tokens_per_day')->default(100000);
            $table->integer('max_cost_per_month')->default(100); // in dollars
            
            // Advanced Settings
            $table->json('custom_prompts')->nullable(); // Voor custom system prompts per feature
            $table->json('model_overrides')->nullable(); // Voor specifieke models per feature
            $table->string('proxy_url')->nullable();
            $table->integer('timeout_seconds')->default(30);
            
            // Usage Tracking
            $table->integer('total_requests_today')->default(0);
            $table->integer('total_tokens_today')->default(0);
            $table->decimal('total_cost_this_month', 10, 4)->default(0);
            $table->date('last_reset_date')->nullable();
            
            $table->timestamps();
        });
        
        // Migrate existing settings from settings table
        $existingSettings = DB::table('settings')->whereIn('key', [
            'openai_api_key',
            'openai_model',
            'anthropic_api_key',
            'claude_api_key',
            'ai_enabled'
        ])->get();
        
        $aiSettings = [];
        foreach ($existingSettings as $setting) {
            $aiSettings[$setting->key] = $setting->value;
        }
        
        if (!empty($aiSettings)) {
            DB::table('ai_settings')->insert([
                'openai_api_key' => $aiSettings['openai_api_key'] ?? null,
                'openai_model' => $aiSettings['openai_model'] ?? 'gpt-4o-mini',
                'anthropic_api_key' => $aiSettings['anthropic_api_key'] ?? $aiSettings['claude_api_key'] ?? null,
                'ai_enabled' => isset($aiSettings['ai_enabled']) ? (bool)$aiSettings['ai_enabled'] : true,
                'default_provider' => isset($aiSettings['claude_api_key']) ? 'anthropic' : 'openai',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            // Create default record
            DB::table('ai_settings')->insert([
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('ai_settings');
    }
};