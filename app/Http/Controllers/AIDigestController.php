<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Services\ClaudeAIService;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Setting;
use App\Models\AiSetting;
use App\Models\ProjectMilestone;
use App\Mail\WeeklyDigestMail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AIDigestController extends Controller
{
    protected $claudeService;
    
    public function __construct(ClaudeAIService $claudeService)
    {
        $this->claudeService = $claudeService;
    }
    
    /**
     * Show digest configuration page
     */
    public function index()
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage AI digests.');
        }
        
        // Get digest settings from session (temporary storage)
        $settings = [
            'digest_enabled' => session('ai_digest_enabled', false),
            'digest_frequency' => session('ai_digest_frequency', 'weekly'),
            'digest_day' => session('ai_digest_day', 'monday'),
            'digest_time' => session('ai_digest_time', '09:00'),
            'digest_recipients' => session('ai_digest_recipients', []),
            'digest_include_projects' => session('ai_digest_include_projects', true),
            'digest_include_time' => session('ai_digest_include_time', true),
            'digest_include_invoices' => session('ai_digest_include_invoices', true),
            'digest_include_risks' => session('ai_digest_include_risks', true),
            'digest_include_recommendations' => session('ai_digest_include_recommendations', true),
        ];
        
        // Get available users for recipient selection
        $users = User::where('is_active', true)
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->whereIn('role', ['super_admin', 'admin', 'project_manager'])
            ->orderBy('name')
            ->get();
        
        // Get recent digests
        $recentDigests = $this->getRecentDigests();
        
        return view('ai-digest.index', compact('settings', 'users', 'recentDigests'));
    }
    
    /**
     * Update digest settings
     */
    public function updateSettings(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }
        
        $validated = $request->validate([
            'digest_enabled' => 'required|in:true,false',
            'digest_frequency' => 'required|in:daily,weekly,biweekly,monthly',
            'digest_day' => 'required_if:digest_frequency,weekly,biweekly|nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'digest_time' => 'required|date_format:H:i',
            'digest_recipients' => 'nullable|array',
            'digest_recipients.*' => 'exists:users,id',
            'digest_include_projects' => 'required|in:true,false',
            'digest_include_time' => 'required|in:true,false',
            'digest_include_invoices' => 'required|in:true,false',
            'digest_include_risks' => 'required|in:true,false',
            'digest_include_recommendations' => 'required|in:true,false',
        ]);
        
        // Save settings in session (temporary storage for digest settings)
        session(['ai_digest_enabled' => $validated['digest_enabled']]);
        session(['ai_digest_frequency' => $validated['digest_frequency']]);
        session(['ai_digest_day' => $validated['digest_day'] ?? 'monday']);
        session(['ai_digest_time' => $validated['digest_time']]);
        session(['ai_digest_recipients' => $validated['digest_recipients'] ?? []]);
        session(['ai_digest_include_projects' => $validated['digest_include_projects']]);
        session(['ai_digest_include_time' => $validated['digest_include_time']]);
        session(['ai_digest_include_invoices' => $validated['digest_include_invoices']]);
        session(['ai_digest_include_risks' => $validated['digest_include_risks']]);
        session(['ai_digest_include_recommendations' => $validated['digest_include_recommendations']]);
        
        return redirect()->route('ai-digest.index')
            ->with('success', 'Digest settings updated successfully');
    }
    
    /**
     * Generate digest manually
     */
    public function generate(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }
        
        $period = $request->get('period', 'week');
        $sendEmail = $request->get('send_email', false);
        
        try {
            $digest = $this->generateDigest($period);
            
            if ($sendEmail) {
                $this->sendDigestEmails($digest);
                $message = 'Digest generated and sent successfully';
            } else {
                $message = 'Digest generated successfully';
            }
            
            // Store digest in session for preview
            session(['latest_digest' => $digest]);
            
            return redirect()->route('ai-digest.preview')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            Log::error('Digest generation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate digest: ' . $e->getMessage());
        }
    }
    
    /**
     * Preview digest
     */
    public function preview()
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }
        
        $digest = session('latest_digest');
        
        if (!$digest) {
            // Generate a preview digest for current week
            $digest = $this->generateDigest('week');
        }
        
        return view('ai-digest.preview', compact('digest'));
    }
    
    /**
     * Download digest as PDF
     */
    public function download(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }
        
        $digest = session('latest_digest');
        
        if (!$digest) {
            $digest = $this->generateDigest('week');
        }
        
        $pdf = Pdf::loadView('ai-digest.pdf', compact('digest'));
        
        return $pdf->download('weekly-digest-' . now()->format('Y-m-d') . '.pdf');
    }
    
    /**
     * Generate the digest data
     */
    protected function generateDigest($period = 'week')
    {
        $startDate = $this->getPeriodStartDate($period);
        $endDate = now();
        
        // Gather all data
        $data = [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company' => Auth::user()->company->name ?? 'All Companies',
        ];
        
        // Get project statistics
        if (session('ai_digest_include_projects', true)) {
            $data['projects'] = $this->getProjectStatistics($startDate, $endDate);
        }
        
        // Get time tracking statistics
        if (session('ai_digest_include_time', true)) {
            $data['time'] = $this->getTimeStatistics($startDate, $endDate);
        }
        
        // Get invoice statistics
        if (session('ai_digest_include_invoices', true)) {
            $data['invoices'] = $this->getInvoiceStatistics($startDate, $endDate);
        }
        
        // Get risk analysis
        if (session('ai_digest_include_risks', true)) {
            $data['risks'] = $this->getRiskAnalysis();
        }
        
        // Generate AI summary
        $data['ai_summary'] = $this->generateAISummary($data);
        
        // Generate AI recommendations
        if (session('ai_digest_include_recommendations', true)) {
            $data['ai_recommendations'] = $this->generateAIRecommendations($data);
        }
        
        // Store digest
        $this->storeDigest($data);
        
        return $data;
    }
    
    /**
     * Get project statistics for period
     */
    protected function getProjectStatistics($startDate, $endDate)
    {
        $query = Project::query();
        
        if (Auth::user()->role !== 'super_admin' && Auth::user()->company_id) {
            $query->whereHas('companies', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }
        
        // New projects
        $newProjects = (clone $query)->whereBetween('created_at', [$startDate, $endDate])->count();
        
        // Completed projects
        $completedProjects = (clone $query)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        
        // Active projects
        $activeProjects = (clone $query)->where('status', 'active')->count();
        
        // At risk projects
        $atRiskProjects = (clone $query)
            ->where('status', 'active')
            ->where('end_date', '<', now()->addDays(7))
            ->count();
        
        // Milestone completions
        $projectIds = (clone $query)->pluck('id');
        $milestonesCompleted = ProjectMilestone::whereIn('project_id', $projectIds)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        
        // Top performing projects (by milestone completion)
        $topProjects = (clone $query)
            ->where('status', 'active')
            ->withCount(['milestones', 'milestones as completed_milestones_count' => function($q) {
                $q->where('status', 'completed');
            }])
            ->having('milestones_count', '>', 0)
            ->get()
            ->map(function($project) {
                $project->completion_rate = $project->milestones_count > 0 
                    ? round(($project->completed_milestones_count / $project->milestones_count) * 100, 1)
                    : 0;
                return $project;
            })
            ->sortByDesc('completion_rate')
            ->take(5);
        
        return [
            'new' => $newProjects,
            'completed' => $completedProjects,
            'active' => $activeProjects,
            'at_risk' => $atRiskProjects,
            'milestones_completed' => $milestonesCompleted,
            'top_projects' => $topProjects,
        ];
    }
    
    /**
     * Get time tracking statistics
     */
    protected function getTimeStatistics($startDate, $endDate)
    {
        $query = TimeEntry::whereBetween('entry_date', [$startDate, $endDate]);
        
        if (Auth::user()->role !== 'super_admin' && Auth::user()->company_id) {
            $query->whereHas('project', function($q) {
                $q->whereHas('companies', function($q2) {
                    $q2->where('company_id', Auth::user()->company_id);
                });
            });
        }
        
        $totalMinutes = $query->sum('minutes');
        $totalHours = round($totalMinutes / 60, 1);
        
        $billableMinutes = (clone $query)->where('is_billable', 'billable')->sum('minutes');
        $billableHours = round($billableMinutes / 60, 1);
        
        $approvedMinutes = (clone $query)->where('status', 'approved')->sum('minutes');
        $approvedHours = round($approvedMinutes / 60, 1);
        
        // Top contributors
        $topContributors = (clone $query)
            ->select('user_id')
            ->selectRaw('SUM(minutes) as total_minutes')
            ->groupBy('user_id')
            ->orderByDesc('total_minutes')
            ->limit(5)
            ->with('user')
            ->get()
            ->map(function($entry) {
                return [
                    'user' => $entry->user->name,
                    'hours' => round($entry->total_minutes / 60, 1)
                ];
            });
        
        // Daily average
        $daysInPeriod = $startDate->diffInDays($endDate) ?: 1;
        $dailyAverage = round($totalHours / $daysInPeriod, 1);
        
        return [
            'total_hours' => $totalHours,
            'billable_hours' => $billableHours,
            'approved_hours' => $approvedHours,
            'billable_percentage' => $totalHours > 0 ? round(($billableHours / $totalHours) * 100, 1) : 0,
            'approval_percentage' => $totalHours > 0 ? round(($approvedHours / $totalHours) * 100, 1) : 0,
            'daily_average' => $dailyAverage,
            'top_contributors' => $topContributors,
        ];
    }
    
    /**
     * Get invoice statistics
     */
    protected function getInvoiceStatistics($startDate, $endDate)
    {
        $query = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);
        
        if (Auth::user()->role !== 'super_admin' && Auth::user()->company_id) {
            $query->whereHas('project', function($q) {
                $q->whereHas('companies', function($q2) {
                    $q2->where('company_id', Auth::user()->company_id);
                });
            });
        }
        
        $newInvoices = $query->count();
        $totalAmount = (clone $query)->sum('total_inc_vat');
        
        $paidInvoices = (clone $query)->where('status', 'paid')->count();
        $paidAmount = (clone $query)->where('status', 'paid')->sum('total_inc_vat');
        
        $overdueInvoices = Invoice::where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->when(Auth::user()->role !== 'super_admin' && Auth::user()->company_id, function($q) {
                $q->whereHas('project', function($q2) {
                    $q2->whereHas('companies', function($q3) {
                        $q3->where('company_id', Auth::user()->company_id);
                    });
                });
            })
            ->count();
        
        $overdueAmount = Invoice::where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->when(Auth::user()->role !== 'super_admin' && Auth::user()->company_id, function($q) {
                $q->whereHas('project', function($q2) {
                    $q2->whereHas('companies', function($q3) {
                        $q3->where('company_id', Auth::user()->company_id);
                    });
                });
            })
            ->sum('total_inc_vat');
        
        return [
            'new_count' => $newInvoices,
            'total_amount' => $totalAmount,
            'paid_count' => $paidInvoices,
            'paid_amount' => $paidAmount,
            'overdue_count' => $overdueInvoices,
            'overdue_amount' => $overdueAmount,
            'collection_rate' => $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 1) : 0,
        ];
    }
    
    /**
     * Get risk analysis
     */
    protected function getRiskAnalysis()
    {
        $risks = [];
        
        // Get at-risk projects
        $projectsQuery = Project::where('status', 'active');
        
        if (Auth::user()->role !== 'super_admin' && Auth::user()->company_id) {
            $projectsQuery->whereHas('companies', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }
        
        $projects = $projectsQuery->get();
        
        foreach ($projects as $project) {
            // Check deadline risk
            $daysRemaining = (int) now()->diffInDays($project->end_date, false);
            if ($daysRemaining < 0) {
                $risks[] = [
                    'type' => 'deadline',
                    'severity' => 'critical',
                    'project' => $project->name,
                    'message' => "Project is " . abs($daysRemaining) . " days overdue"
                ];
            } elseif ($daysRemaining < 7 && $daysRemaining >= 0) {
                $risks[] = [
                    'type' => 'deadline',
                    'severity' => 'high',
                    'project' => $project->name,
                    'message' => "Only {$daysRemaining} days until deadline"
                ];
            }
            
            // Check milestone delays (only check milestones with reasonable dates)
            $overdueMilestones = $project->milestones()
                ->where('status', '!=', 'completed')
                ->where('end_date', '<', now())
                ->whereNotNull('end_date')
                ->where('end_date', '>', '2020-01-01') // Ignore very old/invalid dates
                ->count();
            
            if ($overdueMilestones > 0) {
                $risks[] = [
                    'type' => 'milestone',
                    'severity' => $overdueMilestones > 2 ? 'high' : 'medium',
                    'project' => $project->name,
                    'message' => "{$overdueMilestones} milestone(s) are overdue"
                ];
            }
        }
        
        // Sort by severity
        $severityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        usort($risks, function($a, $b) use ($severityOrder) {
            return $severityOrder[$a['severity']] <=> $severityOrder[$b['severity']];
        });
        
        return array_slice($risks, 0, 10); // Top 10 risks
    }
    
    /**
     * Generate AI summary
     */
    protected function generateAISummary($data)
    {
        $prompt = $this->buildSummaryPrompt($data);
        $response = $this->sendToAI($prompt);
        
        if ($response) {
            return $response;
        }
        
        // Fallback summary
        return $this->generateFallbackSummary($data);
    }
    
    /**
     * Generate AI recommendations
     */
    protected function generateAIRecommendations($data)
    {
        $prompt = $this->buildRecommendationsPrompt($data);
        $response = $this->sendToAI($prompt);
        
        if ($response) {
            return $this->parseRecommendations($response);
        }
        
        // Fallback recommendations
        return $this->generateFallbackRecommendations($data);
    }
    
    /**
     * Build summary prompt for AI
     */
    protected function buildSummaryPrompt($data)
    {
        $prompt = "Generate a professional executive summary for this period's project management data. Be concise and highlight key achievements and concerns.\n\n";
        
        $prompt .= "Period: " . Carbon::parse($data['start_date'])->format('M d') . " - " . Carbon::parse($data['end_date'])->format('M d, Y') . "\n";
        $prompt .= "Company: {$data['company']}\n\n";
        
        if (isset($data['projects'])) {
            $prompt .= "Projects:\n";
            $prompt .= "- New: {$data['projects']['new']}\n";
            $prompt .= "- Completed: {$data['projects']['completed']}\n";
            $prompt .= "- Active: {$data['projects']['active']}\n";
            $prompt .= "- At Risk: {$data['projects']['at_risk']}\n";
            $prompt .= "- Milestones Completed: {$data['projects']['milestones_completed']}\n\n";
        }
        
        if (isset($data['time'])) {
            $prompt .= "Time Tracking:\n";
            $prompt .= "- Total Hours: {$data['time']['total_hours']}\n";
            $prompt .= "- Billable: {$data['time']['billable_percentage']}%\n";
            $prompt .= "- Approved: {$data['time']['approval_percentage']}%\n\n";
        }
        
        if (isset($data['invoices'])) {
            $prompt .= "Invoicing:\n";
            $prompt .= "- New Invoices: {$data['invoices']['new_count']} (€" . number_format($data['invoices']['total_amount'], 2) . ")\n";
            $prompt .= "- Collection Rate: {$data['invoices']['collection_rate']}%\n";
            $prompt .= "- Overdue: {$data['invoices']['overdue_count']} (€" . number_format($data['invoices']['overdue_amount'], 2) . ")\n\n";
        }
        
        $prompt .= "Provide a 3-4 paragraph summary highlighting achievements, areas of concern, and overall performance.";
        
        return $prompt;
    }
    
    /**
     * Build recommendations prompt for AI
     */
    protected function buildRecommendationsPrompt($data)
    {
        $prompt = "Based on the following project management data, provide 5 specific, actionable recommendations. Return as JSON array with structure: [{priority: 'high/medium/low', action: 'specific action', reason: 'why', expected_outcome: 'result'}]\n\n";
        
        $prompt .= "Key Metrics:\n";
        
        if (isset($data['projects']) && $data['projects']['at_risk'] > 0) {
            $prompt .= "- {$data['projects']['at_risk']} projects are at risk\n";
        }
        
        if (isset($data['time']) && $data['time']['billable_percentage'] < 70) {
            $prompt .= "- Billable percentage is low at {$data['time']['billable_percentage']}%\n";
        }
        
        if (isset($data['invoices']) && $data['invoices']['overdue_count'] > 0) {
            $prompt .= "- {$data['invoices']['overdue_count']} invoices are overdue\n";
        }
        
        if (isset($data['risks']) && count($data['risks']) > 0) {
            $prompt .= "- " . count($data['risks']) . " risks identified\n";
        }
        
        $prompt .= "\nFocus on practical improvements for project delivery, team efficiency, and financial performance.";
        
        return $prompt;
    }
    
    /**
     * Send prompt to AI service
     */
    protected function sendToAI($prompt)
    {
        $aiSettings = AiSetting::current();
        $apiKey = $aiSettings->getApiKey();
        
        if (!$apiKey || !$aiSettings->ai_enabled || !$aiSettings->ai_digest_enabled) {
            return null;
        }
        
        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json'
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $aiSettings->getModel(),
                'max_tokens' => 1000,
                'temperature' => 0.7,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? null;
            }
            
        } catch (\Exception $e) {
            Log::error('AI digest generation failed', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * Parse AI recommendations response
     */
    protected function parseRecommendations($response)
    {
        try {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return array_slice($data, 0, 5);
            }
        } catch (\Exception $e) {
            // Fall through to parsing text
        }
        
        // Try to extract recommendations from text
        $recommendations = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            if (stripos($line, 'recommendation') !== false || stripos($line, 'suggest') !== false) {
                $recommendations[] = [
                    'priority' => 'medium',
                    'action' => trim($line, " -•*"),
                    'reason' => '',
                    'expected_outcome' => ''
                ];
            }
        }
        
        return array_slice($recommendations, 0, 5);
    }
    
    /**
     * Generate fallback summary
     */
    protected function generateFallbackSummary($data)
    {
        $summary = "During the period from " . Carbon::parse($data['start_date'])->format('M d') . 
                   " to " . Carbon::parse($data['end_date'])->format('M d, Y') . ", ";
        
        if (isset($data['projects'])) {
            $summary .= "the team started {$data['projects']['new']} new projects and completed {$data['projects']['completed']} projects. ";
            $summary .= "Currently, there are {$data['projects']['active']} active projects with {$data['projects']['at_risk']} requiring immediate attention. ";
        }
        
        if (isset($data['time'])) {
            $summary .= "A total of {$data['time']['total_hours']} hours were logged, with {$data['time']['billable_percentage']}% being billable. ";
        }
        
        if (isset($data['invoices'])) {
            $summary .= "The team generated {$data['invoices']['new_count']} invoices totaling €" . number_format($data['invoices']['total_amount'], 2) . ". ";
            if ($data['invoices']['overdue_count'] > 0) {
                $summary .= "Attention is needed for {$data['invoices']['overdue_count']} overdue invoices. ";
            }
        }
        
        return $summary;
    }
    
    /**
     * Generate fallback recommendations
     */
    protected function generateFallbackRecommendations($data)
    {
        $recommendations = [];
        
        if (isset($data['projects']) && $data['projects']['at_risk'] > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Review and address at-risk projects immediately',
                'reason' => "{$data['projects']['at_risk']} projects are at risk of missing deadlines",
                'expected_outcome' => 'Prevent project delays and maintain client satisfaction'
            ];
        }
        
        if (isset($data['time']) && $data['time']['billable_percentage'] < 70) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Improve billable hours tracking and allocation',
                'reason' => "Only {$data['time']['billable_percentage']}% of logged time is billable",
                'expected_outcome' => 'Increase revenue and profitability'
            ];
        }
        
        if (isset($data['invoices']) && $data['invoices']['overdue_count'] > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Follow up on overdue invoices',
                'reason' => "{$data['invoices']['overdue_count']} invoices are past due",
                'expected_outcome' => 'Improve cash flow and reduce outstanding receivables'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Get period start date
     */
    protected function getPeriodStartDate($period)
    {
        switch ($period) {
            case 'day':
                return now()->startOfDay();
            case 'week':
                return now()->startOfWeek();
            case 'month':
                return now()->startOfMonth();
            case 'quarter':
                return now()->startOfQuarter();
            case 'year':
                return now()->startOfYear();
            default:
                return now()->subWeek()->startOfDay();
        }
    }
    
    /**
     * Store digest in database
     */
    protected function storeDigest($data)
    {
        // Store in settings as JSON for now
        // In production, you'd want a dedicated digests table
        $digests = session('ai_digest_history', []);
        
        array_unshift($digests, [
            'generated_at' => now()->toIso8601String(),
            'period' => $data['period'],
            'data' => $data
        ]);
        
        // Keep only last 10 digests
        $digests = array_slice($digests, 0, 10);
        
        session(['ai_digest_history' => $digests]);
    }
    
    /**
     * Get recent digests
     */
    protected function getRecentDigests()
    {
        $digests = session('ai_digest_history', []);
        
        return collect($digests)->map(function($digest) {
            return [
                'generated_at' => Carbon::parse($digest['generated_at']),
                'period' => $digest['period']
            ];
        })->take(5);
    }
    
    /**
     * Send digest emails
     */
    protected function sendDigestEmails($digest)
    {
        $recipientIds = session('ai_digest_recipients', []);
        
        if (empty($recipientIds)) {
            return;
        }
        
        $recipients = User::whereIn('id', $recipientIds)
            ->where('is_active', true)
            ->get();
        
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new WeeklyDigestMail($digest, $recipient));
                Log::info('Digest email sent', ['recipient' => $recipient->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send digest email', [
                    'recipient' => $recipient->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}