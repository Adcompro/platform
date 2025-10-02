<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\AIUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTimeZone;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        // Only super_admin and admin can access settings
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage settings.');
        }

        // Get current settings
        $settings = [
            'timezone' => Setting::get('app_timezone', 'Europe/Amsterdam'),
            'date_format' => Setting::get('date_format', 'd-m-Y'),
            'time_format' => Setting::get('time_format', 'H:i'),
            // Calendar sync settings
            'calendar_auto_sync' => Setting::get('calendar_auto_sync', 'true'),
            'calendar_auto_sync_interval' => Setting::get('calendar_auto_sync_interval', '60'),
            'calendar_js_sync_interval' => Setting::get('calendar_js_sync_interval', '15'),
            'calendar_cron_sync_interval' => Setting::get('calendar_cron_sync_interval', '60'),
            'calendar_sync_range' => Setting::get('calendar_sync_range', '90'),
            // Microsoft Graph settings
            'msgraph_client_id' => Setting::get('msgraph_client_id', ''),
            'msgraph_client_secret' => Setting::get('msgraph_client_secret', ''),
            'msgraph_tenant_id' => Setting::get('msgraph_tenant_id', 'common'),
            'msgraph_redirect_uri' => Setting::get('msgraph_redirect_uri', url('/msgraph/oauth')),
            'msgraph_landing_url' => Setting::get('msgraph_landing_url', '/calendar'),
            'msgraph_allow_login' => Setting::get('msgraph_allow_login', 'true'),
            'msgraph_allow_access_token_routes' => Setting::get('msgraph_allow_access_token_routes', 'true'),
            // Invoice generation settings
            'invoice_monthly_day' => Setting::get('invoice_monthly_day', 'last'),
            'invoice_quarterly_timing' => Setting::get('invoice_quarterly_timing', 'quarter_end'),
            'invoice_milestone_days' => Setting::get('invoice_milestone_days', '0'),
            'invoice_project_completion_days' => Setting::get('invoice_project_completion_days', '0'),
            'invoice_due_days' => Setting::get('invoice_due_days', '30'),
            'invoice_auto_generate' => Setting::get('invoice_auto_generate', 'false'),
        ];

        // Get list of timezones
        $timezones = $this->getTimezoneList();

        return view('settings.index', compact('settings', 'timezones'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Only super_admin and admin can update settings
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage settings.');
        }

        $validated = $request->validate([
            'timezone' => 'required|string|timezone',
            'date_format' => 'required|string|in:d-m-Y,m-d-Y,Y-m-d,d/m/Y,m/d/Y',
            'time_format' => 'required|string|in:H:i,h:i A,h:i a,H:i:s',
            // Calendar sync validation
            'calendar_auto_sync' => 'required|string|in:true,false',
            'calendar_auto_sync_interval' => 'required|integer|min:15|max:1440',
            'calendar_js_sync_interval' => 'required|integer|min:0|max:60',
            'calendar_cron_sync_interval' => 'required|integer|in:15,30,60,120,240',
            'calendar_sync_range' => 'required|integer|in:30,60,90,180,365',
            // Microsoft Graph validation
            'msgraph_client_id' => 'nullable|string|max:255',
            'msgraph_client_secret' => 'nullable|string|max:255',
            'msgraph_tenant_id' => 'required|string|max:255',
            'msgraph_redirect_uri' => 'required|url|max:255',
            'msgraph_landing_url' => 'required|string|max:255',
            'msgraph_allow_login' => 'required|string|in:true,false',
            'msgraph_allow_access_token_routes' => 'required|string|in:true,false',
            // Invoice generation validation
            'invoice_monthly_day' => 'required|string',
            'invoice_quarterly_timing' => 'required|string|in:quarter_end,quarter_start,quarter_after_15',
            'invoice_milestone_days' => 'required|integer|min:0|max:30',
            'invoice_project_completion_days' => 'required|integer|min:0|max:30',
            'invoice_due_days' => 'required|integer|in:14,21,30,45,60',
            'invoice_auto_generate' => 'required|string|in:true,false',
        ]);

        // Update settings
        Setting::set('app_timezone', $validated['timezone']);
        Setting::set('date_format', $validated['date_format']);
        Setting::set('time_format', $validated['time_format']);
        
        // Update calendar sync settings
        Setting::set('calendar_auto_sync', $validated['calendar_auto_sync']);
        Setting::set('calendar_auto_sync_interval', $validated['calendar_auto_sync_interval']);
        Setting::set('calendar_js_sync_interval', $validated['calendar_js_sync_interval']);
        Setting::set('calendar_cron_sync_interval', $validated['calendar_cron_sync_interval']);
        Setting::set('calendar_sync_range', $validated['calendar_sync_range']);
        
        // Update Microsoft Graph settings
        Setting::set('msgraph_client_id', $validated['msgraph_client_id']);
        Setting::set('msgraph_client_secret', $validated['msgraph_client_secret']);
        Setting::set('msgraph_tenant_id', $validated['msgraph_tenant_id']);
        Setting::set('msgraph_redirect_uri', $validated['msgraph_redirect_uri']);
        Setting::set('msgraph_landing_url', $validated['msgraph_landing_url']);
        Setting::set('msgraph_allow_login', $validated['msgraph_allow_login']);
        Setting::set('msgraph_allow_access_token_routes', $validated['msgraph_allow_access_token_routes']);
        
        // Update invoice generation settings
        Setting::set('invoice_monthly_day', $validated['invoice_monthly_day']);
        Setting::set('invoice_quarterly_timing', $validated['invoice_quarterly_timing']);
        Setting::set('invoice_milestone_days', $validated['invoice_milestone_days']);
        Setting::set('invoice_project_completion_days', $validated['invoice_project_completion_days']);
        Setting::set('invoice_due_days', $validated['invoice_due_days']);
        Setting::set('invoice_auto_generate', $validated['invoice_auto_generate']);
        

        // Update config
        config(['app.timezone' => $validated['timezone']]);
        date_default_timezone_set($validated['timezone']);

        // Clear cache
        Cache::flush();

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully. Timezone is now: ' . $validated['timezone']);
    }

    /**
     * Get organized list of timezones
     */
    private function getTimezoneList()
    {
        $timezones = [];
        $regions = [
            'Africa' => DateTimeZone::AFRICA,
            'America' => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Asia' => DateTimeZone::ASIA,
            'Atlantic' => DateTimeZone::ATLANTIC,
            'Australia' => DateTimeZone::AUSTRALIA,
            'Europe' => DateTimeZone::EUROPE,
            'Indian' => DateTimeZone::INDIAN,
            'Pacific' => DateTimeZone::PACIFIC,
        ];

        foreach ($regions as $name => $mask) {
            $zones = DateTimeZone::listIdentifiers($mask);
            foreach ($zones as $timezone) {
                // Get offset
                $time = new \DateTime('now', new DateTimeZone($timezone));
                $offset = $time->format('P');
                
                // Format display name
                $display = str_replace(['/', '_'], [' / ', ' '], $timezone);
                $display .= ' (UTC ' . $offset . ')';
                
                $timezones[$name][$timezone] = $display;
            }
        }

        return $timezones;
    }
    
    /**
     * Show AI Usage & Costs page
     */
    public function aiUsage(Request $request)
    {
        // Only super_admin and admin can access
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can view AI usage.');
        }
        
        // Get date range
        $startOfToday = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        
        // Calculate costs
        $todayCost = AIUsageLog::whereDate('created_at', $startOfToday)
            ->sum('estimated_cost');
        $todayRequests = AIUsageLog::whereDate('created_at', $startOfToday)
            ->count();
        
        $weekCost = AIUsageLog::where('created_at', '>=', $startOfWeek)
            ->sum('estimated_cost');
        $weekRequests = AIUsageLog::where('created_at', '>=', $startOfWeek)
            ->count();
        
        $monthCost = AIUsageLog::where('created_at', '>=', $startOfMonth)
            ->sum('estimated_cost');
        $monthRequests = AIUsageLog::where('created_at', '>=', $startOfMonth)
            ->count();
        
        $totalCost = AIUsageLog::sum('estimated_cost');
        $totalRequests = AIUsageLog::count();
        
        // Convert USD to EUR (simple conversion)
        $exchangeRate = 0.92;
        $todayCost *= $exchangeRate;
        $weekCost *= $exchangeRate;
        $monthCost *= $exchangeRate;
        $totalCost *= $exchangeRate;
        
        // Get usage by feature
        $featureUsage = AIUsageLog::select('feature', DB::raw('COUNT(*) as count'), DB::raw('SUM(estimated_cost) as cost'))
            ->groupBy('feature')
            ->orderBy('cost', 'desc')
            ->get()
            ->map(function($item) use ($exchangeRate, $totalCost) {
                $costInEur = $item->cost * $exchangeRate;
                return [
                    'name' => ucwords(str_replace('_', ' ', $item->feature)),
                    'count' => $item->count,
                    'cost' => $costInEur,
                    'percentage' => $totalCost > 0 ? round(($costInEur / $totalCost) * 100, 1) : 0,
                    'color' => $this->getFeatureColor($item->feature)
                ];
            });
        
        // Get model usage
        $modelUsage = AIUsageLog::select('model', DB::raw('COUNT(*) as count'), DB::raw('SUM(estimated_cost) as cost'))
            ->groupBy('model')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function($item) use ($exchangeRate, $totalRequests) {
                return [
                    'name' => $this->getModelDisplayName($item->model),
                    'count' => $item->count,
                    'cost' => $item->cost * $exchangeRate,
                    'percentage' => $totalRequests > 0 ? round(($item->count / $totalRequests) * 100, 1) : 0
                ];
            });
        
        // Get recent requests
        $recentRequests = AIUsageLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        // Calculate projections
        $daysInMonth = Carbon::now()->daysInMonth;
        $daysPassed = Carbon::now()->day;
        $projectedMonthlyCost = $daysPassed > 0 ? ($monthCost / $daysPassed) * $daysInMonth : 0;
        
        $avgCostPerRequest = $totalRequests > 0 ? $totalCost / $totalRequests : 0;
        $avgTokensPerRequest = AIUsageLog::avg('total_tokens') ?? 0;
        
        return view('settings.ai-usage', compact(
            'todayCost', 'todayRequests',
            'weekCost', 'weekRequests',
            'monthCost', 'monthRequests',
            'totalCost', 'totalRequests',
            'featureUsage', 'modelUsage',
            'recentRequests',
            'projectedMonthlyCost',
            'avgCostPerRequest',
            'avgTokensPerRequest'
        ));
    }
    
    /**
     * Get color for feature
     */
    private function getFeatureColor($feature)
    {
        $colors = [
            'chat' => '#3b82f6',
            'task_generator' => '#8b5cf6',
            'predictions' => '#10b981',
            'project_health' => '#f59e0b',
            'digest' => '#6366f1',
            'general' => '#6b7280'
        ];
        
        return $colors[$feature] ?? '#6b7280';
    }
    
    /**
     * Show AI invoice prompt settings
     */
    public function aiInvoicePrompts()
    {
        return view('settings.ai-invoice-prompts');
    }
    
    /**
     * Update AI invoice prompt settings
     */
    public function updateAiInvoicePrompts(Request $request)
    {
        $settings = [
            'ai_invoice_system_prompt',
            'ai_invoice_consolidation_instructions',
            'ai_invoice_description_prompt',
            'ai_invoice_output_language',
            'ai_invoice_max_description_words',
            'ai_invoice_include_technical_details',
            'ai_invoice_group_similar_threshold'
        ];
        
        foreach ($settings as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }
        
        return redirect()->route('settings.ai-invoice-prompts')
            ->with('success', 'AI invoice prompt settings updated successfully!');
    }
}