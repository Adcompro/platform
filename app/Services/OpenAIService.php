<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\AIUsageLog;
use App\Models\AiSetting;

class OpenAIService
{
    protected string $apiKey;
    protected string $model;
    protected float $temperature;
    protected int $maxTokens;
    protected AiSetting $aiSettings;
    
    public function __construct()
    {
        // Get AI Settings from database
        $this->aiSettings = AiSetting::current();
        
        // Use settings from database, fallback to config/env
        $this->apiKey = $this->aiSettings->openai_api_key ?: config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = $this->aiSettings->openai_model ?: config('services.openai.model', 'gpt-4o-mini');
        $this->temperature = $this->aiSettings->openai_temperature ?: config('services.openai.temperature', 0.3);
        $this->maxTokens = $this->aiSettings->openai_max_tokens ?: 2000;
    }
    
    /**
     * Summarize time entry descriptions for invoice
     * Groups similar activities and creates concise summaries
     */
    public function summarizeTimeEntriesForInvoice(array $timeEntries, ?string $projectContext = null): array
    {
        // Check if AI is enabled
        if (!$this->aiSettings->ai_enabled || !$this->aiSettings->ai_invoice_generation_enabled) {
            return [
                'success' => false,
                'error' => 'AI features are currently disabled',
                'summaries' => []
            ];
        }
        
        // Check rate limits
        if ($limitError = $this->aiSettings->isRateLimitExceeded()) {
            return [
                'success' => false,
                'error' => $limitError,
                'summaries' => []
            ];
        }
        
        try {
            // Prepare the descriptions for analysis
            $descriptions = [];
            foreach ($timeEntries as $entry) {
                $descriptions[] = [
                    'date' => $entry['entry_date'] ?? '',
                    'hours' => $entry['hours'] ?? 0,
                    'description' => $entry['description'] ?? '',
                    'user' => $entry['user']['name'] ?? 'Unknown'
                ];
            }
            
            $systemPrompt = "You are an expert at summarizing time entries for professional invoices. 
                            Your task is to analyze time entry descriptions and create clear, concise, and professional summaries 
                            that group similar activities together. Focus on deliverables and value provided.
                            Always respond in the same language as the input descriptions.";
            
            $userPrompt = "Analyze these time entries and create a professional invoice summary:\n\n";
            
            if ($projectContext) {
                $userPrompt .= "Project Context: $projectContext\n\n";
            }
            
            $userPrompt .= "Time Entries:\n";
            foreach ($descriptions as $desc) {
                $userPrompt .= "- [{$desc['date']}] {$desc['hours']}h by {$desc['user']}: {$desc['description']}\n";
            }
            
            $userPrompt .= "\nProvide:
1. A main summary (max 100 words) describing the key work performed
2. Group similar activities into 3-5 categories with bullet points
3. Identify any significant deliverables or milestones achieved
4. Suggest a concise invoice line description (max 50 words)

Format your response as JSON with keys: main_summary, grouped_activities, deliverables, invoice_description";
            
            // Make API call
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'temperature' => $this->temperature,
                'response_format' => ['type' => 'json_object']
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $content = json_decode($result['choices'][0]['message']['content'], true);
                
                // Log AI usage
                $this->logUsage(
                    'summarize_time_entries',
                    $result['usage']['prompt_tokens'] ?? 0,
                    $result['usage']['completion_tokens'] ?? 0,
                    $result['usage']['total_tokens'] ?? 0
                );
                
                return [
                    'success' => true,
                    'data' => $content,
                    'tokens_used' => $result['usage']['total_tokens'] ?? 0
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to get response from OpenAI',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('OpenAI Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Consolidate invoice line descriptions
     * Takes multiple invoice lines and creates smart groupings
     */
    public function consolidateInvoiceLines(array $invoiceLines): array
    {
        try {
            $systemPrompt = "You are an expert at consolidating invoice line items. 
                            Group similar work items together and create clear, professional descriptions.
                            Maintain the same language as the input.";
            
            $userPrompt = "Consolidate these invoice line items into logical groups:\n\n";
            
            foreach ($invoiceLines as $line) {
                $userPrompt .= "- {$line['description']} ({$line['quantity']} {$line['unit']} @ {$line['unit_price']})\n";
            }
            
            $userPrompt .= "\nProvide consolidated groups with:
1. Group name
2. Combined description
3. Total hours/quantity
4. Suggested pricing structure

Format as JSON with key: consolidated_groups (array of objects)";
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'temperature' => $this->temperature,
                'response_format' => ['type' => 'json_object']
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $content = json_decode($result['choices'][0]['message']['content'], true);
                
                // Log AI usage
                $this->logUsage(
                    'consolidate_invoice_lines',
                    $result['usage']['prompt_tokens'] ?? 0,
                    $result['usage']['completion_tokens'] ?? 0,
                    $result['usage']['total_tokens'] ?? 0
                );
                
                return [
                    'success' => true,
                    'data' => $content,
                    'tokens_used' => $result['usage']['total_tokens'] ?? 0
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to consolidate invoice lines',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('OpenAI Consolidation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Generate smart invoice description based on work performed
     */
    public function generateInvoiceDescription(string $projectName, array $workSummary, string $period): array
    {
        try {
            $systemPrompt = "Create professional invoice descriptions that clearly communicate value delivered.";
            
            $userPrompt = "Generate a professional invoice description for:
Project: $projectName
Period: $period
Work Summary: " . json_encode($workSummary) . "

Provide a clear, concise description (max 100 words) that highlights the value delivered.";
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'temperature' => $this->temperature,
                'max_tokens' => 200
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $description = $result['choices'][0]['message']['content'];
                
                // Log AI usage
                $this->logUsage(
                    'generate_invoice_description',
                    $result['usage']['prompt_tokens'] ?? 0,
                    $result['usage']['completion_tokens'] ?? 0,
                    $result['usage']['total_tokens'] ?? 0
                );
                
                return [
                    'success' => true,
                    'description' => $description,
                    'tokens_used' => $result['usage']['total_tokens'] ?? 0
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to generate description',
                'description' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('OpenAI Description Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'description' => null
            ];
        }
    }
    
    /**
     * Log AI usage for tracking and billing
     */
    protected function logUsage(string $feature, int $promptTokens, int $completionTokens, int $totalTokens): void
    {
        try {
            // Check if usage logging is enabled
            if (!$this->aiSettings->log_ai_usage) {
                return;
            }
            
            // Calculate costs based on model pricing
            $promptCost = $this->calculateCost($promptTokens, 'input');
            $completionCost = $this->calculateCost($completionTokens, 'output');
            $totalCost = $promptCost + $completionCost;
            
            // Update usage statistics in AI Settings
            $this->aiSettings->incrementUsage($totalTokens, $totalCost);
            
            AIUsageLog::create([
                'user_id' => auth()->id(),
                'service' => 'openai',
                'feature' => $feature,
                'model' => $this->model,
                'prompt_tokens' => $promptTokens,
                'response_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'estimated_cost' => $totalCost,
                'currency' => 'USD',
                'status' => 'success',
                'metadata' => json_encode([
                    'prompt_cost' => $promptCost,
                    'completion_cost' => $completionCost,
                    'timestamp' => now()->toIso8601String()
                ])
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log AI usage: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate cost based on OpenAI pricing
     */
    protected function calculateCost(int $tokens, string $type): float
    {
        // Get pricing from AI Settings (per 1K tokens)
        if ($type === 'input') {
            $costPer1K = $this->aiSettings->openai_input_cost_per_1k;
        } else {
            $costPer1K = $this->aiSettings->openai_output_cost_per_1k;
        }
        
        // Calculate cost: (tokens / 1000) * costPer1K
        return ($tokens / 1000) * $costPer1K;
    }
}