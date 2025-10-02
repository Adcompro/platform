<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AiSetting extends Model
{
    protected $fillable = [
        // OpenAI Settings
        'openai_api_key',
        'openai_model',
        'openai_temperature',
        'openai_max_tokens',
        
        // Anthropic Settings
        'anthropic_api_key',
        'anthropic_model',
        'anthropic_temperature',
        'anthropic_max_tokens',
        
        // General Settings
        'default_provider',
        'ai_enabled',
        'log_ai_usage',
        'show_ai_costs',
        
        // Feature Toggles
        'ai_chat_enabled',
        'ai_task_generator_enabled',
        'ai_time_predictions_enabled',
        'ai_invoice_generation_enabled',
        'ai_digest_enabled',
        'ai_learning_enabled',
        'ai_time_entry_enabled',
        
        // AI Chat Settings
        'ai_chat_system_prompt',
        'ai_chat_max_tokens',
        'ai_chat_temperature',
        'ai_chat_history_limit',
        'ai_chat_show_context',
        'ai_chat_allow_file_analysis',
        'ai_chat_quick_actions',
        'ai_chat_welcome_message',
        
        // AI Time Entry Settings
        'ai_time_entry_default_rules',
        'ai_time_entry_default_categories',
        'ai_time_entry_example_patterns',
        'ai_time_entry_prompt_template',
        'ai_time_entry_max_length',
        'ai_time_entry_auto_improve',
        'ai_time_entry_learn_from_history',
        'ai_time_entry_history_days',
        
        // AI Invoice Settings
        'ai_invoice_enabled',
        'ai_invoice_system_prompt',
        'ai_invoice_consolidation_instructions',
        'ai_invoice_description_prompt',
        'ai_invoice_output_language',
        'ai_invoice_max_description_words',
        'ai_invoice_include_technical_details',
        'ai_invoice_group_similar_threshold',
        'ai_invoice_bundle_press_releases',
        'ai_invoice_list_all_media',
        'ai_invoice_group_by_activity_type',
        
        // Cost Settings
        'openai_input_cost_per_1k',
        'openai_output_cost_per_1k',
        'anthropic_input_cost_per_1k',
        'anthropic_output_cost_per_1k',
        
        // Rate Limiting
        'max_requests_per_minute',
        'max_tokens_per_day',
        'max_cost_per_month',
        
        // Advanced Settings
        'custom_prompts',
        'model_overrides',
        'proxy_url',
        'timeout_seconds',
        
        // Usage Tracking
        'total_requests_today',
        'total_tokens_today',
        'total_cost_this_month',
        'last_reset_date'
    ];

    protected $casts = [
        'ai_enabled' => 'boolean',
        'log_ai_usage' => 'boolean',
        'show_ai_costs' => 'boolean',
        'ai_chat_enabled' => 'boolean',
        'ai_task_generator_enabled' => 'boolean',
        'ai_time_predictions_enabled' => 'boolean',
        'ai_invoice_generation_enabled' => 'boolean',
        'ai_digest_enabled' => 'boolean',
        'ai_learning_enabled' => 'boolean',
        'ai_time_entry_enabled' => 'boolean',
        'ai_chat_show_context' => 'boolean',
        'ai_chat_allow_file_analysis' => 'boolean',
        'ai_time_entry_auto_improve' => 'boolean',
        'ai_time_entry_learn_from_history' => 'boolean',
        'ai_invoice_enabled' => 'boolean',
        'ai_invoice_include_technical_details' => 'boolean',
        'ai_invoice_bundle_press_releases' => 'boolean',
        'ai_invoice_list_all_media' => 'boolean',
        'ai_invoice_group_by_activity_type' => 'boolean',
        'ai_invoice_group_similar_threshold' => 'float',
        'ai_chat_temperature' => 'float',
        'ai_chat_quick_actions' => 'array',
        'ai_time_entry_default_categories' => 'array',
        'ai_time_entry_example_patterns' => 'array',
        'openai_temperature' => 'float',
        'anthropic_temperature' => 'float',
        'openai_input_cost_per_1k' => 'float',
        'openai_output_cost_per_1k' => 'float',
        'anthropic_input_cost_per_1k' => 'float',
        'anthropic_output_cost_per_1k' => 'float',
        'total_cost_this_month' => 'float',
        'custom_prompts' => 'array',
        'model_overrides' => 'array',
        'last_reset_date' => 'date'
    ];

    /**
     * Get the current AI settings (cached)
     */
    public static function current()
    {
        return Cache::remember('ai_settings', 60, function () {
            return self::first() ?? self::create([]);
        });
    }

    /**
     * Clear the cache when settings are updated
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('ai_settings');
        });

        static::deleted(function () {
            Cache::forget('ai_settings');
        });
    }

    /**
     * Check if a specific AI feature is enabled
     */
    public function isFeatureEnabled($feature)
    {
        $featureKey = 'ai_' . $feature . '_enabled';
        return $this->ai_enabled && $this->$featureKey;
    }

    /**
     * Get the API key for the specified provider
     */
    public function getApiKey($provider = null)
    {
        $provider = $provider ?? $this->default_provider;
        
        return match($provider) {
            'openai' => $this->openai_api_key,
            'anthropic' => $this->anthropic_api_key,
            default => null
        };
    }

    /**
     * Get the model for the specified provider
     */
    public function getModel($provider = null, $feature = null)
    {
        $provider = $provider ?? $this->default_provider;
        
        // Check for feature-specific overrides
        if ($feature && $this->model_overrides && isset($this->model_overrides[$feature])) {
            return $this->model_overrides[$feature];
        }
        
        return match($provider) {
            'openai' => $this->openai_model,
            'anthropic' => $this->anthropic_model,
            default => 'gpt-4o-mini'
        };
    }

    /**
     * Update usage statistics
     */
    public function incrementUsage($tokens, $cost)
    {
        // Reset counters if it's a new day
        if (!$this->last_reset_date || $this->last_reset_date->format('Y-m-d') !== now()->format('Y-m-d')) {
            $this->total_requests_today = 0;
            $this->total_tokens_today = 0;
            $this->last_reset_date = now();
        }
        
        // Reset monthly cost if it's a new month
        if (!$this->last_reset_date || $this->last_reset_date->format('Y-m') !== now()->format('Y-m')) {
            $this->total_cost_this_month = 0;
        }
        
        $this->increment('total_requests_today');
        $this->increment('total_tokens_today', $tokens);
        $this->increment('total_cost_this_month', $cost);
        $this->save();
    }

    /**
     * Check if rate limits are exceeded
     */
    public function isRateLimitExceeded()
    {
        if ($this->total_tokens_today >= $this->max_tokens_per_day) {
            return 'Daily token limit exceeded';
        }
        
        if ($this->total_cost_this_month >= $this->max_cost_per_month) {
            return 'Monthly cost limit exceeded';
        }
        
        return false;
    }

    /**
     * Get available AI models per provider
     */
    public static function getAvailableModels()
    {
        return [
            'openai' => [
                'gpt-4o' => 'GPT-4 Omni (Latest, Most Capable)',
                'gpt-4o-mini' => 'GPT-4 Omni Mini (Fast, Cost-effective)',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'gpt-4' => 'GPT-4 (Original)',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Legacy)'
            ],
            'anthropic' => [
                'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (Fast, Latest)',
                'claude-3-haiku-20240307' => 'Claude 3 Haiku (Fast)',
                'claude-3-opus-20240229' => 'Claude 3 Opus (Most Capable)',
                'claude-3-sonnet-20240229' => 'Claude 3 Sonnet (Balanced)',
                'claude-2.1' => 'Claude 2.1 (Legacy)',
                'claude-2.0' => 'Claude 2.0 (Legacy)',
                'claude-instant-1.2' => 'Claude Instant 1.2 (Fast, Legacy)'
            ]
        ];
    }
}