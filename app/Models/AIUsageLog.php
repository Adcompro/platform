<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIUsageLog extends Model
{
    protected $table = 'ai_usage_logs';
    
    protected $fillable = [
        'service',
        'model',
        'feature',
        'prompt',
        'prompt_tokens',
        'response_tokens',
        'total_tokens',
        'estimated_cost',
        'currency',
        'cached_response',
        'response_time_ms',
        'status',
        'error_message',
        'user_id',
        'project_id',
        'ip_address',
        'metadata'
    ];
    
    protected $casts = [
        'metadata' => 'json',
        'cached_response' => 'boolean',
        'estimated_cost' => 'decimal:6',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the user who made the request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the project associated with the request
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    
    /**
     * Scope for specific service
     */
    public function scopeForService($query, $service)
    {
        return $query->where('service', $service);
    }
    
    /**
     * Scope for specific feature
     */
    public function scopeForFeature($query, $feature)
    {
        return $query->where('feature', $feature);
    }
    
    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    /**
     * Get formatted cost
     */
    public function getFormattedCostAttribute()
    {
        return $this->currency . ' ' . number_format($this->estimated_cost, 4);
    }
    
    /**
     * Get cost in euros (converted from USD)
     */
    public function getCostInEurosAttribute()
    {
        // Simple conversion, you might want to use real exchange rates
        $exchangeRate = 0.92; // USD to EUR
        return $this->currency === 'USD' ? $this->estimated_cost * $exchangeRate : $this->estimated_cost;
    }
    
    /**
     * Calculate estimated cost based on model and tokens
     * Prices as of January 2025 (per 1M tokens)
     */
    public static function calculateCost($model, $promptTokens, $responseTokens)
    {
        // Claude pricing (per 1M tokens)
        $pricing = [
            'claude-3-opus-20240229' => [
                'input' => 15.00,
                'output' => 75.00
            ],
            'claude-3-sonnet-20240229' => [
                'input' => 3.00,
                'output' => 15.00
            ],
            'claude-3-haiku-20240307' => [
                'input' => 0.25,
                'output' => 1.25
            ],
            'claude-3-5-sonnet-20241022' => [
                'input' => 3.00,
                'output' => 15.00
            ],
            // OpenAI pricing for comparison
            'gpt-4-turbo' => [
                'input' => 10.00,
                'output' => 30.00
            ],
            'gpt-3.5-turbo' => [
                'input' => 0.50,
                'output' => 1.50
            ]
        ];
        
        // Get pricing for model, default to Haiku if not found
        $modelPricing = $pricing[$model] ?? $pricing['claude-3-haiku-20240307'];
        
        // Calculate cost (pricing is per 1M tokens, so divide by 1,000,000)
        $inputCost = ($promptTokens / 1000000) * $modelPricing['input'];
        $outputCost = ($responseTokens / 1000000) * $modelPricing['output'];
        
        return $inputCost + $outputCost;
    }
    
    /**
     * Log an AI API call
     */
    public static function logUsage($data)
    {
        // Calculate estimated cost if tokens are provided
        if (isset($data['prompt_tokens']) && isset($data['response_tokens']) && isset($data['model'])) {
            $data['estimated_cost'] = self::calculateCost(
                $data['model'],
                $data['prompt_tokens'],
                $data['response_tokens']
            );
            $data['total_tokens'] = $data['prompt_tokens'] + $data['response_tokens'];
        }
        
        // Add user and IP if not provided
        if (!isset($data['user_id']) && auth()->check()) {
            $data['user_id'] = auth()->id();
        }
        
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = request()->ip();
        }
        
        return self::create($data);
    }
}