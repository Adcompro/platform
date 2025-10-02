<?php

namespace App\Http\Controllers;

use App\Models\AiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiSettingsController extends Controller
{
    /**
     * Display AI Settings page
     */
    public function index()
    {
        // Check permissions - alleen admins
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage AI settings.');
        }

        $settings = AiSetting::current();
        $availableModels = AiSetting::getAvailableModels();
        
        // Get usage statistics from ai_usage_logs table if it exists
        $usageStats = null;
        if (Schema::hasTable('ai_usage_logs')) {
            $usageStats = DB::table('ai_usage_logs')
                ->selectRaw('
                    COUNT(*) as total_requests,
                    SUM(total_tokens) as total_tokens,
                    SUM(estimated_cost) as total_cost,
                    DATE(created_at) as date
                ')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();
        }

        return view('ai-settings.index', compact('settings', 'availableModels', 'usageStats'));
    }

    /**
     * Update AI Settings
     */
    public function update(Request $request)
    {
        // Check permissions
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage AI settings.');
        }

        // First, handle checkboxes before validation
        // This ensures unchecked boxes are treated as false
        $input = $request->all();
        $input['ai_enabled'] = $request->has('ai_enabled');
        $input['log_ai_usage'] = $request->has('log_ai_usage');
        $input['show_ai_costs'] = $request->has('show_ai_costs');
        $input['ai_chat_enabled'] = $request->has('ai_chat_enabled');
        $input['ai_task_generator_enabled'] = $request->has('ai_task_generator_enabled');
        $input['ai_time_predictions_enabled'] = $request->has('ai_time_predictions_enabled');
        $input['ai_invoice_generation_enabled'] = $request->has('ai_invoice_generation_enabled');
        $input['ai_digest_enabled'] = $request->has('ai_digest_enabled');
        $input['ai_learning_enabled'] = $request->has('ai_learning_enabled');
        $input['ai_time_entry_enabled'] = $request->has('ai_time_entry_enabled');
        $input['ai_time_entry_auto_improve'] = $request->has('ai_time_entry_auto_improve');
        $input['ai_time_entry_learn_from_history'] = $request->has('ai_time_entry_learn_from_history');
        $input['ai_chat_show_context'] = $request->has('ai_chat_show_context');
        $input['ai_chat_allow_file_analysis'] = $request->has('ai_chat_allow_file_analysis');
        $input['ai_invoice_enabled'] = $request->has('ai_invoice_enabled');
        $input['ai_invoice_include_technical_details'] = $request->has('ai_invoice_include_technical_details');
        $input['ai_invoice_bundle_press_releases'] = $request->has('ai_invoice_bundle_press_releases');
        $input['ai_invoice_list_all_media'] = $request->has('ai_invoice_list_all_media');
        $input['ai_invoice_group_by_activity_type'] = $request->has('ai_invoice_group_by_activity_type');
        
        // Process array fields for time entry settings
        if (isset($input['ai_time_entry_default_categories']) && is_string($input['ai_time_entry_default_categories'])) {
            $input['ai_time_entry_default_categories'] = array_map('trim', explode(',', $input['ai_time_entry_default_categories']));
        }
        if (isset($input['ai_time_entry_example_patterns']) && is_string($input['ai_time_entry_example_patterns'])) {
            $input['ai_time_entry_example_patterns'] = array_filter(array_map('trim', explode("\n", $input['ai_time_entry_example_patterns'])));
        }

        $validated = validator($input, [
            // OpenAI Settings
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'required|string',
            'openai_temperature' => 'required|numeric|between:0,2',
            'openai_max_tokens' => 'required|integer|min:1|max:128000',
            
            // Anthropic Settings
            'anthropic_api_key' => 'nullable|string',
            'anthropic_model' => 'required|string',
            'anthropic_temperature' => 'required|numeric|between:0,2',
            'anthropic_max_tokens' => 'required|integer|min:1|max:200000',
            
            // General Settings
            'default_provider' => 'required|in:openai,anthropic',
            'ai_enabled' => 'required|boolean',
            'log_ai_usage' => 'required|boolean',
            'show_ai_costs' => 'required|boolean',
            
            // Feature Toggles
            'ai_chat_enabled' => 'required|boolean',
            'ai_task_generator_enabled' => 'required|boolean',
            'ai_time_predictions_enabled' => 'required|boolean',
            'ai_invoice_generation_enabled' => 'required|boolean',
            'ai_digest_enabled' => 'required|boolean',
            'ai_learning_enabled' => 'required|boolean',
            
            // AI Chat Settings
            'ai_chat_system_prompt' => 'nullable|string',
            'ai_chat_max_tokens' => 'required|integer|min:100|max:4000',
            'ai_chat_temperature' => 'required|numeric|between:0,1',
            'ai_chat_history_limit' => 'required|integer|min:5|max:50',
            'ai_chat_show_context' => 'required|boolean',
            'ai_chat_allow_file_analysis' => 'required|boolean',
            'ai_chat_welcome_message' => 'nullable|string|max:500',
            
            // AI Time Entry Settings
            'ai_time_entry_enabled' => 'required|boolean',
            'ai_time_entry_default_rules' => 'nullable|string',
            'ai_time_entry_default_categories' => 'nullable|array',
            'ai_time_entry_example_patterns' => 'nullable|array',
            'ai_time_entry_prompt_template' => 'nullable|string',
            'ai_time_entry_max_length' => 'required|integer|min:50|max:500',
            'ai_time_entry_auto_improve' => 'required|boolean',
            'ai_time_entry_learn_from_history' => 'required|boolean',
            'ai_time_entry_history_days' => 'required|integer|min:7|max:365',
            
            // AI Invoice Settings
            'ai_invoice_enabled' => 'required|boolean',
            'ai_invoice_system_prompt' => 'nullable|string',
            'ai_invoice_consolidation_instructions' => 'nullable|string',
            'ai_invoice_description_prompt' => 'nullable|string',
            'ai_invoice_output_language' => 'required|string|in:nl,en,auto',
            'ai_invoice_max_description_words' => 'required|integer|min:50|max:500',
            'ai_invoice_include_technical_details' => 'required|boolean',
            'ai_invoice_group_similar_threshold' => 'required|numeric|between:0,1',
            'ai_invoice_bundle_press_releases' => 'required|boolean',
            'ai_invoice_list_all_media' => 'required|boolean',
            'ai_invoice_group_by_activity_type' => 'required|boolean',
            
            // Cost Settings
            'openai_input_cost_per_1k' => 'required|numeric|min:0',
            'openai_output_cost_per_1k' => 'required|numeric|min:0',
            'anthropic_input_cost_per_1k' => 'required|numeric|min:0',
            'anthropic_output_cost_per_1k' => 'required|numeric|min:0',
            
            // Rate Limiting
            'max_requests_per_minute' => 'required|integer|min:1',
            'max_tokens_per_day' => 'required|integer|min:1000',
            'max_cost_per_month' => 'required|integer|min:1',
            
            // Advanced Settings
            'proxy_url' => 'nullable|url',
            'timeout_seconds' => 'required|integer|min:5|max:300'
        ])->validate();

        // Don't overwrite API keys if not provided (security)
        $settings = AiSetting::current();
        if (empty($validated['openai_api_key']) || $validated['openai_api_key'] === '••••••••') {
            unset($validated['openai_api_key']);
        }
        if (empty($validated['anthropic_api_key']) || $validated['anthropic_api_key'] === '••••••••') {
            unset($validated['anthropic_api_key']);
        }

        $settings->update($validated);

        // Clear cache
        Cache::forget('ai_settings');

        return redirect()->route('ai-settings.index')
            ->with('success', 'AI settings updated successfully.');
    }

    /**
     * Test AI connection
     */
    public function testConnection(Request $request)
    {
        // Check permissions
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $provider = $request->input('provider', 'openai');
        $settings = AiSetting::current();

        if (!$settings->getApiKey($provider)) {
            return response()->json([
                'success' => false,
                'message' => 'API key not configured for ' . ucfirst($provider)
            ]);
        }

        try {
            if ($provider === 'openai') {
                $response = $this->testOpenAI($settings);
            } else {
                $response = $this->testAnthropic($settings);
            }

            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
                'details' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reset usage statistics
     */
    public function resetUsage(Request $request)
    {
        // Check permissions
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        $settings = AiSetting::current();
        $settings->update([
            'total_requests_today' => 0,
            'total_tokens_today' => 0,
            'total_cost_this_month' => 0,
            'last_reset_date' => now()
        ]);

        return redirect()->route('ai-settings.index')
            ->with('success', 'Usage statistics reset successfully.');
    }

    /**
     * Export AI usage logs
     */
    public function exportUsage(Request $request)
    {
        // Check permissions
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        $logs = DB::table('ai_usage_logs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "Date,Service,Feature,Model,Tokens,Cost,User\n";
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%d,%.6f,%s\n",
                $log->created_at,
                $log->service,
                $log->feature,
                $log->model,
                $log->total_tokens,
                $log->estimated_cost,
                $log->user_id
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="ai_usage_' . date('Y-m-d') . '.csv"');
    }

    /**
     * Test OpenAI connection
     */
    private function testOpenAI($settings)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $settings->openai_api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $settings->openai_model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "Connection test successful" in exactly 3 words.']
                ],
                'max_tokens' => 10,
                'temperature' => 0
            ],
            'timeout' => $settings->timeout_seconds
        ]);

        $data = json_decode($response->getBody(), true);
        return [
            'model' => $data['model'],
            'response' => $data['choices'][0]['message']['content']
        ];
    }

    /**
     * Test Anthropic connection
     */
    private function testAnthropic($settings)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $settings->anthropic_api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $settings->anthropic_model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "Connection test successful" in exactly 3 words.']
                ],
                'max_tokens' => 10
            ],
            'timeout' => $settings->timeout_seconds
        ]);

        $data = json_decode($response->getBody(), true);
        return [
            'model' => $settings->anthropic_model,
            'response' => $data['content'][0]['text']
        ];
    }
}