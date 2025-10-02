<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\ClaudeAIService;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Setting;
use App\Models\AiSetting;
use Carbon\Carbon;

class AIChatController extends Controller
{
    protected $claudeService;
    
    public function __construct(ClaudeAIService $claudeService)
    {
        $this->claudeService = $claudeService;
    }
    
    /**
     * Process chat message
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|string|max:500',
            'project_id' => 'nullable|exists:projects,id'
        ]);
        
        try {
            // Get user context
            $userContext = $this->getUserContext($request);
            
            // Build enhanced prompt with context
            $prompt = $this->buildChatPrompt($validated['message'], $userContext, $validated['project_id'] ?? null);
            
            // Send to Claude
            $response = $this->sendToClaude($prompt);
            
            // Store in chat history
            $this->storeChatHistory($validated['message'], $response);
            
            return response()->json([
                'success' => true,
                'response' => $response,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('AI Chat error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'response' => 'Sorry, I encountered an error processing your request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Get chat history
     */
    public function history(Request $request)
    {
        $aiSettings = AiSetting::current();
        $history = session('ai_chat_history', []);
        
        return response()->json([
            'success' => true,
            'history' => array_slice($history, -$aiSettings->ai_chat_history_limit), // Use configured limit
            'welcome_message' => $aiSettings->ai_chat_welcome_message
        ]);
    }
    
    /**
     * Clear chat history
     */
    public function clearHistory(Request $request)
    {
        session()->forget('ai_chat_history');
        
        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared'
        ]);
    }
    
    /**
     * Get suggested questions based on context
     */
    public function suggestions(Request $request)
    {
        $context = $request->get('context', 'dashboard');
        $projectId = $request->get('project_id');
        
        $suggestions = $this->getContextualSuggestions($context, $projectId);
        
        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }
    
    /**
     * Build chat prompt with context
     */
    protected function buildChatPrompt($message, $userContext, $projectId = null)
    {
        $prompt = "You are an AI assistant for a project management system. ";
        $prompt .= "The user is {$userContext['user_name']} with role {$userContext['user_role']}. ";
        $prompt .= "Current date/time: " . now()->format('Y-m-d H:i:s') . ". ";
        
        // Add company context
        if ($userContext['company']) {
            $prompt .= "Company: {$userContext['company']}. ";
        }
        
        // Add project-specific context if provided
        if ($projectId) {
            $project = Project::with(['customer', 'milestones'])->find($projectId);
            if ($project) {
                $prompt .= "\n\nProject Context:\n";
                $prompt .= "- Project: {$project->name}\n";
                $prompt .= "- Customer: " . ($project->customer->name ?? 'N/A') . "\n";
                $prompt .= "- Status: {$project->status}\n";
                $prompt .= "- Start: {$project->start_date}\n";
                $prompt .= "- Deadline: {$project->end_date}\n";
                
                // Add milestone info
                $totalMilestones = $project->milestones->count();
                $completedMilestones = $project->milestones->where('status', 'completed')->count();
                $prompt .= "- Progress: {$completedMilestones}/{$totalMilestones} milestones completed\n";
                
                // Add budget info if available
                if ($project->monthly_fee) {
                    $prompt .= "- Monthly Budget: €" . number_format($project->monthly_fee, 2) . "\n";
                }
            }
        }
        
        // Add overall statistics
        $prompt .= "\n\nSystem Overview:\n";
        $prompt .= "- Total Active Projects: {$userContext['total_projects']}\n";
        $prompt .= "- Projects at Risk: {$userContext['projects_at_risk']}\n";
        $prompt .= "- Overdue Projects: {$userContext['overdue_projects']}\n";
        
        // Add projects with hours
        if (!empty($userContext['projects_with_hours'])) {
            $prompt .= "\nProjects with logged hours (top 5):\n";
            foreach ($userContext['projects_with_hours'] as $project) {
                $prompt .= "- {$project['name']}: {$project['hours']} hours\n";
            }
        }
        
        $prompt .= "\n\nUser Question: {$message}\n\n";
        $prompt .= "Provide a helpful, concise response. If asked about specific data, provide accurate information based on the context. ";
        $prompt .= "Format your response in clear, easy-to-read text. Use bullet points where appropriate. ";
        $prompt .= "If you need more information to answer accurately, ask for clarification.";
        
        return $prompt;
    }
    
    /**
     * Get user context for chat
     */
    protected function getUserContext($request)
    {
        $user = Auth::user();
        
        // Get project statistics
        $projectsQuery = Project::where('status', 'active');
        
        if ($user->role !== 'super_admin') {
            $projectsQuery->whereHas('companies', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }
        
        $totalProjects = $projectsQuery->count();
        $overdueProjects = $projectsQuery->where('end_date', '<', now())->count();
        
        // Count at-risk projects (simplified)
        $projectsAtRisk = $projectsQuery->whereHas('milestones', function($q) {
            $q->where('end_date', '<', now())
              ->where('status', '!=', 'completed');
        })->count();
        
        // Get projects with most logged hours
        $projectsWithHours = Project::withSum('timeEntries', 'minutes')
            ->where('status', 'active')
            ->when($user->role !== 'super_admin', function($q) use ($user) {
                $q->whereHas('companies', function($q2) use ($user) {
                    $q2->where('company_id', $user->company_id);
                });
            })
            ->orderBy('time_entries_sum_minutes', 'desc')
            ->take(5)
            ->get()
            ->map(function($project) {
                return [
                    'name' => $project->name,
                    'hours' => round(($project->time_entries_sum_minutes ?? 0) / 60, 1)
                ];
            });
        
        return [
            'user_name' => $user->name,
            'user_role' => $user->role,
            'company' => $user->company->name ?? null,
            'total_projects' => $totalProjects,
            'projects_at_risk' => $projectsAtRisk,
            'overdue_projects' => $overdueProjects,
            'projects_with_hours' => $projectsWithHours
        ];
    }
    
    /**
     * Send message to Claude
     */
    protected function sendToClaude($prompt)
    {
        // Get AI Settings
        $aiSettings = AiSetting::current();
        
        // Check if AI is enabled
        if (!$aiSettings->ai_enabled || !$aiSettings->ai_chat_enabled) {
            return "AI Chat is currently disabled. Please contact your administrator.";
        }
        
        // Get API key based on provider
        $apiKey = $aiSettings->getApiKey();
        
        if (!$apiKey) {
            return "AI assistant is not configured. Please add your API key in AI Settings.";
        }
        
        try {
            if ($aiSettings->default_provider === 'anthropic') {
                // Anthropic API - system prompt is a top-level parameter
                $requestData = [
                    'model' => $aiSettings->anthropic_model,
                    'max_tokens' => $aiSettings->ai_chat_max_tokens,
                    'temperature' => $aiSettings->ai_chat_temperature,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ];
                
                // Add system prompt as top-level parameter if configured
                if ($aiSettings->ai_chat_system_prompt) {
                    $requestData['system'] = $aiSettings->ai_chat_system_prompt;
                }
                
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json'
                ])->post('https://api.anthropic.com/v1/messages', $requestData);
            } else {
                // OpenAI API - system prompt is part of messages array
                $messages = [];
                if ($aiSettings->ai_chat_system_prompt) {
                    $messages[] = [
                        'role' => 'system',
                        'content' => $aiSettings->ai_chat_system_prompt
                    ];
                }
                $messages[] = [
                    'role' => 'user',
                    'content' => $prompt
                ];
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $aiSettings->openai_model,
                    'max_tokens' => $aiSettings->ai_chat_max_tokens,
                    'temperature' => $aiSettings->ai_chat_temperature,
                    'messages' => $messages
                ]);
            }
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Handle different response formats
                if ($aiSettings->default_provider === 'anthropic') {
                    return $data['content'][0]['text'] ?? 'No response generated';
                } else {
                    // OpenAI format
                    return $data['choices'][0]['message']['content'] ?? 'No response generated';
                }
            }
            
            Log::error('Claude API error', ['response' => $response->body()]);
            return "I'm having trouble connecting to the AI service. Please try again later.";
            
        } catch (\Exception $e) {
            Log::error('Claude API exception', ['error' => $e->getMessage()]);
            return "An error occurred while processing your request. Please try again.";
        }
    }
    
    /**
     * Store chat in session history
     */
    protected function storeChatHistory($message, $response)
    {
        $history = session('ai_chat_history', []);
        
        $history[] = [
            'id' => uniqid(),
            'message' => $message,
            'response' => $response,
            'timestamp' => now()->toIso8601String(),
            'user' => Auth::user()->name
        ];
        
        // Keep only last 50 messages
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }
        
        session(['ai_chat_history' => $history]);
    }
    
    /**
     * Get contextual suggestions
     */
    protected function getContextualSuggestions($context, $projectId = null)
    {
        $suggestions = [];
        
        switch ($context) {
            case 'dashboard':
                $suggestions = [
                    "What projects need my attention today?",
                    "Show me the project health summary",
                    "Which projects are at risk of missing deadlines?",
                    "What's the total budget utilization this month?",
                    "Generate a weekly status report"
                ];
                break;
                
            case 'project':
                $suggestions = [
                    "What's the current status of this project?",
                    "Are there any risks I should be aware of?",
                    "Generate a client update for this project",
                    "What tasks are overdue?",
                    "How many hours were logged this week?",
                    "What's the budget situation?"
                ];
                break;
                
            case 'intelligence':
                $suggestions = [
                    "Which project has the lowest health score?",
                    "What are the main risks across all projects?",
                    "Give me optimization recommendations",
                    "Predict completion dates for active projects",
                    "What patterns do you see in our delays?"
                ];
                break;
                
            case 'invoices':
                $suggestions = [
                    "What's the total outstanding amount?",
                    "Which invoices are overdue?",
                    "Calculate revenue for this month",
                    "Show me unpaid invoices older than 30 days",
                    "What's our average payment time?"
                ];
                break;
                
            default:
                $suggestions = [
                    "What can you help me with?",
                    "Show me today's priorities",
                    "Generate a status summary",
                    "What needs my attention?",
                    "Help me understand the AI features"
                ];
        }
        
        return $suggestions;
    }
}