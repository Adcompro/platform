<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\AiSetting;
use App\Models\AIUsageLog;

class ClaudeAIService
{
    protected $apiKey;
    protected $model;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';
    protected $maxTokens = 4000;
    protected $temperature = 0.7;
    protected $cacheHours;

    public function __construct()
    {
        $aiSettings = AiSetting::current();
        $this->apiKey = $aiSettings->getApiKey();
        $this->model = $aiSettings->getModel();
        $this->cacheHours = 24; // Default cache duration
        $this->maxTokens = $aiSettings->anthropic_max_tokens ?? 4000;
        $this->temperature = $aiSettings->anthropic_temperature ?? 0.7;
    }

    /**
     * Analyseer project gezondheid met AI
     */
    public function analyzeProjectHealth($project, $metrics = [])
    {
        // Cache key voor configureerbare uren
        $cacheKey = "project-health-{$project->id}-" . md5(json_encode($metrics));
        
        return Cache::remember($cacheKey, $this->cacheHours * 3600, function() use ($project, $metrics) {
            $prompt = $this->buildProjectHealthPrompt($project, $metrics);
            
            $response = $this->sendRequest($prompt, 'project_health');
            
            if ($response) {
                return $this->parseHealthAnalysis($response);
            }
            
            // Fallback naar basis analyse
            return $this->fallbackHealthAnalysis($project, $metrics);
        });
    }

    /**
     * Detecteer project risico's
     */
    public function detectProjectRisks($project, $timeEntries, $milestones)
    {
        $cacheKey = "project-risks-{$project->id}-" . date('Y-m-d');
        
        return Cache::remember($cacheKey, $this->cacheHours * 3600, function() use ($project, $timeEntries, $milestones) {
            $prompt = $this->buildRiskDetectionPrompt($project, $timeEntries, $milestones);
            
            $response = $this->sendRequest($prompt);
            
            if ($response) {
                return $this->parseRiskAnalysis($response);
            }
            
            return $this->fallbackRiskDetection($project, $milestones);
        });
    }

    /**
     * Genereer AI aanbevelingen
     */
    public function generateRecommendations($project, $issues = [])
    {
        $prompt = $this->buildRecommendationsPrompt($project, $issues);
        
        $response = $this->sendRequest($prompt);
        
        if ($response) {
            return $this->parseRecommendations($response);
        }
        
        return $this->fallbackRecommendations($issues);
    }

    /**
     * Voorspel project completion date
     */
    public function predictCompletion($project, $velocity, $remainingWork)
    {
        $cacheKey = "project-completion-{$project->id}-" . date('Y-m-d');
        
        return Cache::remember($cacheKey, $this->cacheHours * 3600, function() use ($project, $velocity, $remainingWork) {
            $prompt = $this->buildCompletionPredictionPrompt($project, $velocity, $remainingWork);
            
            $response = $this->sendRequest($prompt);
            
            if ($response) {
                return $this->parseCompletionPrediction($response);
            }
            
            // Simple fallback calculation
            if ($velocity > 0) {
                $daysNeeded = ceil($remainingWork / $velocity);
                return [
                    'predicted_date' => now()->addDays($daysNeeded)->format('Y-m-d'),
                    'confidence' => 'low',
                    'factors' => ['Based on simple velocity calculation']
                ];
            }
            
            return null;
        });
    }

    /**
     * Stuur request naar Claude API met usage tracking
     */
    public function sendRequest($prompt, $feature = 'general')
    {
        if (!$this->apiKey) {
            Log::warning('Claude API key niet geconfigureerd');
            return null;
        }

        $startTime = microtime(true);
        
        // Handle both string prompts and message arrays
        $promptText = '';
        $messages = [];
        
        if (is_array($prompt)) {
            // If prompt is already a messages array, use it directly
            $messages = $prompt;
            // Extract text for logging
            foreach ($prompt as $msg) {
                if (isset($msg['content'])) {
                    $promptText .= $msg['content'] . ' ';
                }
            }
            $promptText = trim($promptText);
        } else {
            // Convert string prompt to messages format
            $messages = [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];
            $promptText = $prompt;
        }
        
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json'
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'messages' => $messages,
                'system' => "You are an expert project management AI assistant. Analyze project data and provide insights in JSON format. Be concise and actionable. Respond in the same language as the input."
            ]);

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Log successful usage
                AIUsageLog::logUsage([
                    'service' => 'claude',
                    'model' => $this->model,
                    'feature' => $feature,
                    'prompt' => substr($promptText, 0, 500), // Store first 500 chars for privacy
                    'prompt_tokens' => $data['usage']['input_tokens'] ?? strlen($promptText) / 4, // Estimate if not provided
                    'response_tokens' => $data['usage']['output_tokens'] ?? strlen($data['content'][0]['text'] ?? '') / 4,
                    'response_time_ms' => $responseTime,
                    'status' => 'success',
                    'cached_response' => false,
                    'metadata' => [
                        'model_version' => $this->model,
                        'temperature' => $this->temperature,
                        'max_tokens' => $this->maxTokens
                    ]
                ]);
                
                // Return full response data for new methods
                return [
                    'success' => true,
                    'data' => $data['content'][0]['text'] ?? null,
                    'usage' => [
                        'input_tokens' => $data['usage']['input_tokens'] ?? 0,
                        'output_tokens' => $data['usage']['output_tokens'] ?? 0,
                        'total_tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0)
                    ]
                ];
            }

            // Log failed request
            AIUsageLog::logUsage([
                'service' => 'claude',
                'model' => $this->model,
                'feature' => $feature,
                'prompt' => substr($promptText, 0, 500),
                'prompt_tokens' => strlen($promptText) / 4, // Estimate
                'response_tokens' => 0,
                'response_time_ms' => $responseTime,
                'status' => 'failed',
                'error_message' => $response->body(),
                'cached_response' => false
            ]);
            
            Log::error('Claude API error', ['response' => $response->body()]);
            return [
                'success' => false,
                'error' => 'API request failed',
                'data' => null
            ];

        } catch (\Exception $e) {
            // Log exception
            AIUsageLog::logUsage([
                'service' => 'claude',
                'model' => $this->model,
                'feature' => $feature,
                'prompt' => substr($promptText, 0, 500),
                'prompt_tokens' => strlen($promptText) / 4,
                'response_tokens' => 0,
                'response_time_ms' => (microtime(true) - $startTime) * 1000,
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'cached_response' => false
            ]);
            
            Log::error('Claude API exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Build project health analysis prompt
     */
    protected function buildProjectHealthPrompt($project, $metrics)
    {
        $prompt = "Analyze this project's health and return JSON with structure: {score: 0-100, status: 'healthy'|'warning'|'critical', issues: [], strengths: []}\n\n";
        $prompt .= "Project: {$project->name}\n";
        $prompt .= "Customer: " . ($project->customer->name ?? 'N/A') . "\n";
        $prompt .= "Status: {$project->status}\n";
        $prompt .= "Start: {$project->start_date}\n";
        $prompt .= "Deadline: {$project->end_date}\n";
        $prompt .= "Budget: €" . number_format($project->monthly_fee ?? 0, 2) . "\n\n";
        
        if (!empty($metrics)) {
            $prompt .= "Metrics:\n";
            foreach ($metrics as $key => $value) {
                $prompt .= "- {$key}: {$value}\n";
            }
        }
        
        $prompt .= "\nProvide health score and actionable insights.";
        
        return $prompt;
    }

    /**
     * Build risk detection prompt
     */
    protected function buildRiskDetectionPrompt($project, $timeEntries, $milestones)
    {
        $prompt = "Identify project risks and return JSON: {risks: [{type: string, severity: 'low'|'medium'|'high'|'critical', description: string, mitigation: string}]}\n\n";
        $prompt .= "Project: {$project->name}\n";
        $prompt .= "Deadline: {$project->end_date}\n";
        $prompt .= "Days remaining: " . now()->diffInDays($project->end_date, false) . "\n";
        
        // Milestone info
        $overdueMilestones = $milestones->filter(function($m) {
            return $m->end_date && $m->end_date < now() && $m->status != 'completed';
        });
        
        $prompt .= "Total milestones: " . $milestones->count() . "\n";
        $prompt .= "Completed: " . $milestones->where('status', 'completed')->count() . "\n";
        $prompt .= "Overdue: " . $overdueMilestones->count() . "\n";
        
        // Time tracking info
        $hoursThisWeek = $timeEntries->where('entry_date', '>=', now()->startOfWeek())->sum('minutes') / 60;
        $hoursLastWeek = $timeEntries->whereBetween('entry_date', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])->sum('minutes') / 60;
        
        $prompt .= "Hours this week: " . round($hoursThisWeek, 1) . "\n";
        $prompt .= "Hours last week: " . round($hoursLastWeek, 1) . "\n";
        
        $prompt .= "\nIdentify risks based on deadline, progress, and velocity.";
        
        return $prompt;
    }

    /**
     * Build recommendations prompt
     */
    protected function buildRecommendationsPrompt($project, $issues)
    {
        $prompt = "Generate actionable recommendations and return JSON: {recommendations: [{priority: 'high'|'medium'|'low', action: string, reason: string, impact: string}]}\n\n";
        $prompt .= "Project: {$project->name}\n";
        $prompt .= "Status: {$project->status}\n";
        
        if (!empty($issues)) {
            $prompt .= "\nIdentified issues:\n";
            foreach ($issues as $issue) {
                $prompt .= "- {$issue}\n";
            }
        }
        
        $prompt .= "\nProvide 3-5 specific, actionable recommendations to improve project success.";
        
        return $prompt;
    }

    /**
     * Build completion prediction prompt
     */
    protected function buildCompletionPredictionPrompt($project, $velocity, $remainingWork)
    {
        $prompt = "Predict project completion and return JSON: {predicted_date: 'YYYY-MM-DD', confidence: 'high'|'medium'|'low', factors: [], risks: []}\n\n";
        $prompt .= "Project: {$project->name}\n";
        $prompt .= "Original deadline: {$project->end_date}\n";
        $prompt .= "Current date: " . now()->format('Y-m-d') . "\n";
        $prompt .= "Team velocity (hours/day): " . round($velocity, 1) . "\n";
        $prompt .= "Remaining work (hours): " . round($remainingWork, 1) . "\n";
        $prompt .= "Working days/week: 5\n";
        
        $prompt .= "\nPredict realistic completion date considering velocity trends and typical project delays.";
        
        return $prompt;
    }

    /**
     * Parse health analysis response
     */
    protected function parseHealthAnalysis($response)
    {
        try {
            $data = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($data['score'])) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse Claude health response', ['error' => $e->getMessage()]);
        }
        
        // Try to extract info from text response
        return $this->extractHealthFromText($response);
    }

    /**
     * Parse risk analysis response
     */
    protected function parseRiskAnalysis($response)
    {
        try {
            $data = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($data['risks'])) {
                return $data['risks'];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse Claude risk response', ['error' => $e->getMessage()]);
        }
        
        return [];
    }

    /**
     * Analyze and consolidate time entries hierarchically by milestone/task
     * Groups similar descriptions and calculates totals
     */
    public function consolidateHierarchicalTimeEntries(array $timeEntries, ?string $projectContext = null): array
    {
        try {
            // Group entries by hierarchy
            $hierarchicalData = $this->groupEntriesHierarchically($timeEntries);
            
            // Get prompts from settings
            $systemPrompt = \App\Models\Setting::get('ai_invoice_system_prompt', 
                "You are an expert at analyzing time entries and creating comprehensive invoice descriptions for clients. 
                Your task is to intelligently consolidate similar activities while preserving ALL important details.
                IMPORTANT: Always create descriptions in ENGLISH for international business compatibility.
                Focus on deliverables and value provided to the client.
                Keep all essential information - it's better to have multiple detailed lines than to lose important context.");
            
            $consolidationInstructions = \App\Models\Setting::get('ai_invoice_consolidation_instructions',
                "1. Analyze ALL time entry descriptions and group truly similar activities
2. Create comprehensive descriptions that include ALL important work performed
3. Use multiple bullet points or lines when different types of work were done
4. Keep specific technical details, feature names, bug fixes, and deliverables
5. For repetitive tasks (like daily meetings), combine them but mention frequency
6. NEVER lose important information - when in doubt, keep it as separate items
7. Format descriptions professionally for client invoices");
            
            $userPrompt = "Analyze these hierarchically grouped time entries and create detailed invoice descriptions:\n\n";
            
            if ($projectContext) {
                $userPrompt .= "Project Context: $projectContext\n\n";
            }
            
            $userPrompt .= "Hierarchical Time Entries:\n" . json_encode($hierarchicalData, JSON_PRETTY_PRINT);
            
            $userPrompt .= "\n\nFor each task level, please:\n" . $consolidationInstructions;

            $userPrompt .= "\n\nReturn JSON with structure:
{
    \"milestones\": [
        {
            \"name\": \"milestone name\",
            \"total_hours\": 0,
            \"total_amount\": 0,
            \"tasks\": [
                {
                    \"name\": \"task name\",
                    \"consolidated_descriptions\": [
                        \"First important work item or feature\",
                        \"Second distinct activity or deliverable\",
                        \"Third item if significantly different\"
                    ],
                    \"total_hours\": 0,
                    \"total_amount\": 0
                }
            ]
        }
    ]
}

IMPORTANT: consolidated_descriptions is an ARRAY of strings, not a single string. Include all important distinct activities.";
            
            // Prepare messages with system prompt
            $messages = [
                ['role' => 'user', 'content' => $systemPrompt . "\n\n" . $userPrompt]
            ];
            
            $response = $this->sendRequest($messages, 'consolidate_hierarchical_entries');
            
            if ($response['success']) {
                $content = json_decode($response['data'], true);
                
                // Usage is already logged in sendRequest method
                // No need to log again here
                
                return [
                    'success' => true,
                    'data' => $content,
                    'tokens_used' => $response['usage']['total_tokens'] ?? 0
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to consolidate entries',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('Claude Hierarchical Consolidation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Group time entries hierarchically by milestone/task
     */
    protected function groupEntriesHierarchically(array $timeEntries): array
    {
        $grouped = [];
        
        foreach ($timeEntries as $entry) {
            $milestoneKey = $entry['milestone'] ?? 'General Work';
            $taskKey = $entry['task'] ?? 'General Tasks';
            
            if (!isset($grouped[$milestoneKey])) {
                $grouped[$milestoneKey] = [
                    'name' => $milestoneKey,
                    'tasks' => [],
                    'total_hours' => 0,
                    'total_amount' => 0
                ];
            }
            
            if (!isset($grouped[$milestoneKey]['tasks'][$taskKey])) {
                $grouped[$milestoneKey]['tasks'][$taskKey] = [
                    'name' => $taskKey,
                    'entries' => [],
                    // Subtasks removed - using improved descriptions instead
                    'total_hours' => 0,
                    'total_amount' => 0
                ];
            }
            
            $hours = $entry['hours'] ?? 0;
            $rate = $entry['hourly_rate'] ?? 0;
            $amount = $hours * $rate;
            
            // Add all entries directly to the task level
            $grouped[$milestoneKey]['tasks'][$taskKey]['entries'][] = [
                'description' => $entry['description'],
                'hours' => $hours,
                'amount' => $amount,
                'date' => $entry['entry_date']
            ];
            
            $grouped[$milestoneKey]['tasks'][$taskKey]['total_hours'] += $hours;
            $grouped[$milestoneKey]['tasks'][$taskKey]['total_amount'] += $amount;
            $grouped[$milestoneKey]['total_hours'] += $hours;
            $grouped[$milestoneKey]['total_amount'] += $amount;
        }
        
        return array_values($grouped);
    }

    /**
     * Parse recommendations response
     */
    protected function parseRecommendations($response)
    {
        try {
            $data = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($data['recommendations'])) {
                return $data['recommendations'];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse Claude recommendations response', ['error' => $e->getMessage()]);
        }
        
        return [];
    }

    /**
     * Parse completion prediction response
     */
    protected function parseCompletionPrediction($response)
    {
        try {
            $data = json_decode($response, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($data['predicted_date'])) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse Claude completion response', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Extract health info from text response
     */
    protected function extractHealthFromText($text)
    {
        // Simple extraction logic as fallback
        $score = 70; // default
        
        if (stripos($text, 'critical') !== false || stripos($text, 'severe') !== false) {
            $score = 30;
            $status = 'critical';
        } elseif (stripos($text, 'warning') !== false || stripos($text, 'concern') !== false) {
            $score = 60;
            $status = 'warning';
        } else {
            $score = 85;
            $status = 'healthy';
        }
        
        return [
            'score' => $score,
            'status' => $status,
            'issues' => [],
            'strengths' => [],
            'summary' => $text
        ];
    }

    /**
     * Fallback health analysis zonder AI
     */
    protected function fallbackHealthAnalysis($project, $metrics)
    {
        $score = 100;
        $issues = [];
        $strengths = [];
        
        // Check deadline
        $daysRemaining = now()->diffInDays($project->end_date, false);
        if ($daysRemaining < 0) {
            $score -= 30;
            $issues[] = "Project is " . abs($daysRemaining) . " days overdue";
        } elseif ($daysRemaining < 7) {
            $score -= 15;
            $issues[] = "Deadline approaching in {$daysRemaining} days";
        } else {
            $strengths[] = "Good time buffer with {$daysRemaining} days remaining";
        }
        
        // Check budget usage
        if (isset($metrics['budget_used_percentage'])) {
            if ($metrics['budget_used_percentage'] > 90) {
                $score -= 20;
                $issues[] = "Budget nearly exhausted ({$metrics['budget_used_percentage']}% used)";
            } elseif ($metrics['budget_used_percentage'] > 75) {
                $score -= 10;
                $issues[] = "High budget usage ({$metrics['budget_used_percentage']}% used)";
            } else {
                $strengths[] = "Budget under control ({$metrics['budget_used_percentage']}% used)";
            }
        }
        
        // Check progress
        if (isset($metrics['completion_percentage'])) {
            if ($metrics['completion_percentage'] < 50 && $daysRemaining < 14) {
                $score -= 25;
                $issues[] = "Low progress ({$metrics['completion_percentage']}%) with deadline approaching";
            } elseif ($metrics['completion_percentage'] > 75) {
                $strengths[] = "Good progress at {$metrics['completion_percentage']}% complete";
            }
        }
        
        $status = $score >= 70 ? 'healthy' : ($score >= 40 ? 'warning' : 'critical');
        
        return [
            'score' => max(0, min(100, $score)),
            'status' => $status,
            'issues' => $issues,
            'strengths' => $strengths
        ];
    }

    /**
     * Fallback risk detection zonder AI
     */
    protected function fallbackRiskDetection($project, $milestones)
    {
        $risks = [];
        
        // Check overdue milestones
        $overdueMilestones = $milestones->filter(function($m) {
            return $m->end_date && $m->end_date < now() && $m->status != 'completed';
        })->count();
        
        if ($overdueMilestones > 0) {
            $risks[] = [
                'type' => 'schedule',
                'severity' => $overdueMilestones > 2 ? 'high' : 'medium',
                'description' => "{$overdueMilestones} milestone(s) are overdue",
                'mitigation' => 'Review milestone priorities and reassign resources'
            ];
        }
        
        // Check project deadline
        $daysRemaining = now()->diffInDays($project->end_date, false);
        if ($daysRemaining < 0) {
            $risks[] = [
                'type' => 'deadline',
                'severity' => 'critical',
                'description' => "Project is " . abs($daysRemaining) . " days past deadline",
                'mitigation' => 'Immediate escalation and deadline renegotiation required'
            ];
        } elseif ($daysRemaining < 7) {
            $risks[] = [
                'type' => 'deadline',
                'severity' => 'high',
                'description' => "Only {$daysRemaining} days until deadline",
                'mitigation' => 'Focus all resources on critical path items'
            ];
        }
        
        return $risks;
    }

    /**
     * Fallback recommendations zonder AI
     */
    protected function fallbackRecommendations($issues)
    {
        $recommendations = [];
        
        foreach ($issues as $issue) {
            if (stripos($issue, 'overdue') !== false) {
                $recommendations[] = [
                    'priority' => 'high',
                    'action' => 'Schedule immediate team meeting to address delays',
                    'reason' => 'Multiple items are behind schedule',
                    'impact' => 'Prevent further delays and get project back on track'
                ];
            }
            
            if (stripos($issue, 'budget') !== false) {
                $recommendations[] = [
                    'priority' => 'high',
                    'action' => 'Review and optimize resource allocation',
                    'reason' => 'Budget consumption is higher than expected',
                    'impact' => 'Reduce costs and improve efficiency'
                ];
            }
        }
        
        // Add general recommendations if none specific
        if (empty($recommendations)) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Conduct weekly progress reviews',
                'reason' => 'Regular monitoring prevents issues',
                'impact' => 'Early detection of potential problems'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Generate structured response for task generation
     */
    public function generateStructuredResponse($prompt, $options = [])
    {
        if (!$this->apiKey) {
            Log::warning('Claude API key not configured');
            return null;
        }
        
        $model = $options['model'] ?? $this->model;
        $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
        $temperature = $options['temperature'] ?? 0.3; // Lower for structured output
        
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json'
            ])->post($this->apiUrl, [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => "You are an expert project management AI that generates structured project plans. Always respond with valid JSON matching the requested format. Be thorough and realistic with time estimates."
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? null;
            }

            Log::error('Claude API error for structured generation', ['response' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Claude API exception in structured generation', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Summarize time entry descriptions for invoice
     * Groups similar activities and creates concise summaries
     */
    public function summarizeTimeEntriesForInvoice(array $timeEntries, ?string $projectContext = null): array
    {
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
            
            $prompt = "Analyze these time entries and create a professional invoice summary:\n\n";
            
            if ($projectContext) {
                $prompt .= "Project Context: $projectContext\n\n";
            }
            
            $prompt .= "Time Entries:\n";
            foreach ($descriptions as $desc) {
                $prompt .= "- [{$desc['date']}] {$desc['hours']}h by {$desc['user']}: {$desc['description']}\n";
            }
            
            $prompt .= "\nProvide:
1. A main summary (max 100 words) describing the key work performed
2. Group similar activities into 3-5 categories with bullet points
3. Identify any significant deliverables or milestones achieved
4. Suggest a concise invoice line description (max 50 words)

Format your response as JSON with keys: main_summary, grouped_activities, deliverables, invoice_description

Important: Respond in the same language as the input descriptions.";
            
            // Send request with JSON response format
            $response = $this->sendRequest($prompt, 'invoice_summarization');
            
            if ($response) {
                // Try to parse as JSON
                $content = $response;
                if (is_string($content)) {
                    // Extract JSON from the response if wrapped in text
                    if (preg_match('/\{.*\}/s', $content, $matches)) {
                        $content = json_decode($matches[0], true);
                    } else {
                        $content = json_decode($content, true);
                    }
                }
                
                return [
                    'success' => true,
                    'data' => $content,
                    'tokens_used' => 0 // Claude doesn't return token count in same way
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to get response from Claude',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('Claude Service Error: ' . $e->getMessage());
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
            $prompt = "Consolidate these invoice line items into logical groups:\n\n";
            
            foreach ($invoiceLines as $line) {
                $prompt .= "- {$line['description']} ({$line['quantity']} {$line['unit']} @ {$line['unit_price']})\n";
            }
            
            $prompt .= "\nProvide consolidated groups with:
1. Group name
2. Combined description
3. Total hours/quantity
4. Suggested pricing structure

Format as JSON with key: consolidated_groups (array of objects)

Maintain the same language as the input.";
            
            $response = $this->sendRequest($prompt, 'invoice_consolidation');
            
            if ($response) {
                // Parse response
                $content = $response;
                if (is_string($content)) {
                    if (preg_match('/\{.*\}/s', $content, $matches)) {
                        $content = json_decode($matches[0], true);
                    } else {
                        $content = json_decode($content, true);
                    }
                }
                
                return [
                    'success' => true,
                    'data' => $content,
                    'tokens_used' => 0
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to consolidate invoice lines',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('Claude Consolidation Error: ' . $e->getMessage());
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
            // Get prompt template from settings
            $promptTemplate = \App\Models\Setting::get('ai_invoice_description_prompt',
                "Generate a professional invoice description for:
Project: {PROJECT_NAME}
Period: {PERIOD}
Work Summary: {WORK_SUMMARY}

Provide a clear, concise description (max 100 words) that highlights the value delivered.
IMPORTANT: Always respond in ENGLISH, regardless of the project name or input language.
Format: Return a JSON object with key 'invoice_description' containing the English description.");
            
            // Replace placeholders
            $prompt = str_replace(
                ['{PROJECT_NAME}', '{PERIOD}', '{WORK_SUMMARY}'],
                [$projectName, $period, json_encode($workSummary)],
                $promptTemplate
            );
            
            $response = $this->sendRequest($prompt, 'invoice_description');
            
            if ($response && $response['success']) {
                // Parse the JSON response to get just the description
                $data = json_decode($response['data'], true);
                $description = $data['invoice_description'] ?? $response['data'];
                
                return [
                    'success' => true,
                    'description' => $description,
                    'tokens_used' => $response['usage']['total_tokens'] ?? 0
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to generate description',
                'description' => null
            ];
            
        } catch (\Exception $e) {
            Log::error('Claude Description Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'description' => null
            ];
        }
    }
    
    /**
     * Analyze time entry description and generate subtask suggestion
     */
    public function analyzeTimeEntryDescription($description, $projectName, $taskName = null, $recentEntries = [], $existingSubtasks = [], $projectAiSettings = null)
    {
        // Create a cache key based on normalized description
        $normalizedDesc = strtolower(trim(preg_replace('/\s+/', ' ', $description)));
        $cacheKey = 'ai_time_entry_' . md5($normalizedDesc . $projectName . $taskName);
        
        // Check cache first for consistency
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }
        
        try {
            $context = "Project: $projectName";
            if ($taskName) {
                $context .= "\nTask: $taskName";
            }
            
            // Add recent entries context
            $historyContext = "";
            if (!empty($recentEntries)) {
                $historyContext = "\n\nRecent time entries from this project (for context):\n";
                $sampleEntries = array_slice($recentEntries, 0, 10); // Show max 10 examples
                foreach ($sampleEntries as $entry) {
                    if ($entry['subtask_name']) {
                        $historyContext .= "- Description: \"{$entry['description']}\" → Subtask: \"{$entry['subtask_name']}\"\n";
                    }
                }
            }
            
            // Add existing subtasks context
            $subtasksContext = "";
            if (!empty($existingSubtasks)) {
                $subtasksContext = "\n\nExisting subtasks in this project (prefer these if applicable):\n";
                $subtasksContext .= implode(", ", array_slice($existingSubtasks, 0, 20));
            }
            
            // Check for project-specific AI settings
            $projectRules = "";
            $categories = "development|bugfix|meeting|documentation|testing|design|other";
            
            if ($projectAiSettings && is_object($projectAiSettings) && !$projectAiSettings->use_global_settings) {
                // Add project-specific naming rules
                if (!empty($projectAiSettings->ai_naming_rules)) {
                    $projectRules = "\n\nPROJECT-SPECIFIC NAMING RULES:\n" . $projectAiSettings->ai_naming_rules;
                }
                
                // Add project examples
                if (!empty($projectAiSettings->ai_example_patterns) && is_array($projectAiSettings->ai_example_patterns)) {
                    $projectRules .= "\n\nEXAMPLES OF GOOD NAMING IN THIS PROJECT:\n";
                    foreach ($projectAiSettings->ai_example_patterns as $example) {
                        $projectRules .= "- " . $example . "\n";
                    }
                }
                
                // Add project keywords
                if (!empty($projectAiSettings->ai_keywords) && is_array($projectAiSettings->ai_keywords)) {
                    $projectRules .= "\n\nIMPORTANT PROJECT TERMS: " . implode(', ', $projectAiSettings->ai_keywords);
                }
                
                // Use project-specific categories if defined
                if (!empty($projectAiSettings->ai_task_categories) && is_array($projectAiSettings->ai_task_categories)) {
                    $categories = implode('|', $projectAiSettings->ai_task_categories);
                }
                
                // Use custom prompt if provided
                if (!empty($projectAiSettings->ai_prompt_template)) {
                    $prompt = str_replace(
                        ['{PROJECT_NAME}', '{TASK_NAME}', '{DESCRIPTION}'],
                        [$projectName, $taskName ?? 'N/A', $description],
                        $projectAiSettings->ai_prompt_template
                    );
                    $prompt .= "\n\nReturn JSON with structure:
{
    \"suggested_name\": \"Brief subtask name\",
    \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"],
    \"category\": \"$categories\",
    \"main_action\": \"Core action performed\",
    \"confidence\": 0.0 to 1.0,
    \"matched_existing\": true/false,
    \"similar_to\": \"name of similar existing subtask if found\"
}";
                } else {
                    // Use default prompt with project rules
                    $prompt = "You are a task naming assistant for a specific project. Learn from the project's history and maintain consistency with existing naming patterns.

$context$historyContext$subtasksContext$projectRules

New Time Entry Description: \"$description\"

IMPORTANT RULES:
1. FIRST CHECK: Does this match any existing subtask names? If yes, suggest that exact name
2. LEARN FROM HISTORY: Follow the naming patterns seen in recent entries
3. BE CONSISTENT: Similar work should get similar names as in the history
4. If no match found, create a new name following the project's naming style
5. Focus on the MAIN ACTION and OBJECT
6. Keep it under 50 characters
7. Use the same terminology as the project (e.g., if they say 'bug' not 'issue', use 'bug')
8. FOLLOW PROJECT-SPECIFIC RULES if provided above

Analyze and return JSON:
{
    \"suggested_name\": \"Brief subtask name (prefer existing or follow project patterns)\",
    \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"],
    \"category\": \"$categories\",
    \"main_action\": \"Core action performed\",
    \"confidence\": 0.0 to 1.0,
    \"matched_existing\": true/false,
    \"similar_to\": \"name of similar existing subtask if found\"
}";
                }
            } else {
                // Use default prompt (no project-specific settings)
                $prompt = "You are a task naming assistant for a specific project. Learn from the project's history and maintain consistency with existing naming patterns.

$context$historyContext$subtasksContext

New Time Entry Description: \"$description\"

IMPORTANT RULES:
1. FIRST CHECK: Does this match any existing subtask names? If yes, suggest that exact name
2. LEARN FROM HISTORY: Follow the naming patterns seen in recent entries
3. BE CONSISTENT: Similar work should get similar names as in the history
4. If no match found, create a new name following the project's naming style
5. Focus on the MAIN ACTION and OBJECT
6. Keep it under 50 characters
7. Use the same terminology as the project (e.g., if they say 'bug' not 'issue', use 'bug')

Analyze and return JSON:
{
    \"suggested_name\": \"Brief subtask name (prefer existing or follow project patterns)\",
    \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"],
    \"category\": \"$categories\",
    \"main_action\": \"Core action performed\",
    \"confidence\": 0.0 to 1.0,
    \"matched_existing\": true/false,
    \"similar_to\": \"name of similar existing subtask if found\"
}";
            }

            $response = $this->sendRequest($prompt, 'time_entry_analysis');
            
            if ($response && $response['success']) {
                $data = json_decode($response['data'], true);
                
                // Cache for 24 hours for consistency
                \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 86400);
                
                return $data;
            }
            
            // Fallback response
            $fallback = [
                'suggested_name' => $this->extractSimpleName($description),
                'keywords' => $this->extractKeywords($description),
                'category' => 'other',
                'main_action' => substr($description, 0, 100),
                'confidence' => 0.5
            ];
            
            // Cache fallback too
            \Illuminate\Support\Facades\Cache::put($cacheKey, $fallback, 86400);
            
            return $fallback;
            
        } catch (\Exception $e) {
            Log::error('Time entry analysis failed: ' . $e->getMessage());
            
            // Return basic analysis on error
            $fallback = [
                'suggested_name' => $this->extractSimpleName($description),
                'keywords' => $this->extractKeywords($description),
                'category' => 'other',
                'main_action' => substr($description, 0, 100),
                'confidence' => 0.3
            ];
            
            // Cache even error fallback for consistency
            \Illuminate\Support\Facades\Cache::put($cacheKey, $fallback, 3600); // Only 1 hour for errors
            
            return $fallback;
        }
    }
    
    /**
     * Calculate text similarity between two strings
     */
    public function calculateTextSimilarity($text1, $text2)
    {
        // Normalize texts for better comparison
        $normalized1 = strtolower(trim(preg_replace('/\s+/', ' ', $text1)));
        $normalized2 = strtolower(trim(preg_replace('/\s+/', ' ', $text2)));
        
        // Quick exact match check
        if ($normalized1 === $normalized2) {
            return 1.0;
        }
        
        // Calculate Levenshtein distance for very similar strings
        $levenshtein = levenshtein($normalized1, $normalized2);
        $maxLen = max(strlen($normalized1), strlen($normalized2));
        
        if ($maxLen > 0) {
            $levenshteinSimilarity = 1 - ($levenshtein / $maxLen);
            
            // If strings are very similar (>80%), use Levenshtein
            if ($levenshteinSimilarity > 0.8) {
                return $levenshteinSimilarity;
            }
        }
        
        // Word-based similarity for longer texts
        $words1 = array_map('strtolower', str_word_count($normalized1, 1));
        $words2 = array_map('strtolower', str_word_count($normalized2, 1));
        
        if (empty($words1) || empty($words2)) {
            return 0;
        }
        
        // Remove stop words for better matching
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $words1 = array_diff($words1, $stopWords);
        $words2 = array_diff($words2, $stopWords);
        
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));
        
        // Jaccard similarity
        $similarity = count($union) > 0 ? count($intersection) / count($union) : 0;
        
        // Boost score if key technical terms match
        $technicalTerms = ['api', 'bug', 'fix', 'update', 'implement', 'feature', 'test', 
                          'deploy', 'database', 'ui', 'ux', 'login', 'form', 'validation',
                          'template', 'invoice', 'payment', 'email', 'dashboard'];
        
        $techMatch1 = array_intersect($words1, $technicalTerms);
        $techMatch2 = array_intersect($words2, $technicalTerms);
        
        $commonTechTerms = array_intersect($techMatch1, $techMatch2);
        if (!empty($commonTechTerms)) {
            // Boost based on number of matching technical terms
            $boost = 1 + (count($commonTechTerms) * 0.15);
            $similarity = min(1.0, $similarity * $boost);
        }
        
        return round($similarity, 3);
    }
    
    /**
     * Extract simple name from description (fallback method)
     */
    private function extractSimpleName($description)
    {
        // Remove common words and extract key terms
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before', 'after'];
        
        $words = str_word_count(strtolower($description), 1);
        $filtered = array_diff($words, $stopWords);
        
        // Take first 3-5 meaningful words
        $nameWords = array_slice($filtered, 0, 5);
        $name = implode(' ', $nameWords);
        
        // Capitalize first letter of each word
        $name = ucwords($name);
        
        // Truncate to 50 characters
        if (strlen($name) > 50) {
            $name = substr($name, 0, 47) . '...';
        }
        
        return $name ?: 'General Task';
    }
    
    /**
     * Extract keywords from text (fallback method)
     */
    private function extractKeywords($text)
    {
        // Common technical and business terms to look for
        $importantTerms = [
            'api', 'database', 'ui', 'ux', 'bug', 'fix', 'feature', 'update', 'implement',
            'design', 'test', 'deploy', 'review', 'meeting', 'documentation', 'refactor',
            'optimize', 'security', 'performance', 'integration', 'migration', 'backup',
            'configuration', 'setup', 'install', 'upgrade', 'maintenance'
        ];
        
        $words = array_map('strtolower', str_word_count($text, 1));
        $keywords = [];
        
        // Find important terms
        foreach ($importantTerms as $term) {
            if (in_array($term, $words)) {
                $keywords[] = $term;
            }
        }
        
        // Add unique words that are longer than 4 characters
        foreach ($words as $word) {
            if (strlen($word) > 4 && !in_array($word, $keywords)) {
                $keywords[] = $word;
            }
            
            if (count($keywords) >= 5) {
                break;
            }
        }
        
        return array_slice($keywords, 0, 5);
    }
}