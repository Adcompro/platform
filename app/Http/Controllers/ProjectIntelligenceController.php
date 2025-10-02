<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\TimeEntry;
use App\Models\Invoice;
use App\Services\ClaudeAIService;
use App\Services\ProjectBudgetService;
use Carbon\Carbon;

class ProjectIntelligenceController extends Controller
{
    protected $claudeService;
    protected $budgetService;
    
    public function __construct(ClaudeAIService $claudeService, ProjectBudgetService $budgetService)
    {
        $this->claudeService = $claudeService;
        $this->budgetService = $budgetService;
    }
    
    /**
     * AI Intelligence Dashboard
     */
    public function index(Request $request)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can access AI Intelligence.');
        }
        
        // Get projects with issues
        $projectsQuery = Project::with(['customer', 'milestones', 'timeEntries'])
            ->where('status', 'active');
        
        if (Auth::user()->role !== 'super_admin') {
            $projectsQuery->whereHas('companies', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }
        
        $projects = $projectsQuery->get();
        
        // Calculate health scores voor alle projecten
        $projectsWithHealth = [];
        $criticalProjects = [];
        $warningProjects = [];
        
        foreach ($projects as $project) {
            $healthData = $this->getProjectHealthScore($project);
            
            $projectsWithHealth[] = [
                'project' => $project,
                'health' => $healthData
            ];
            
            if ($healthData['status'] == 'critical') {
                $criticalProjects[] = $project;
            } elseif ($healthData['status'] == 'warning') {
                $warningProjects[] = $project;
            }
        }
        
        // Sort by health score
        usort($projectsWithHealth, function($a, $b) {
            return $a['health']['score'] <=> $b['health']['score'];
        });
        
        // Get aggregated insights
        $insights = $this->getAggregatedInsights($projects);
        
        return view('project-intelligence.index', compact(
            'projectsWithHealth',
            'criticalProjects',
            'warningProjects',
            'insights'
        ));
    }
    
    /**
     * Project Health Detail
     */
    public function show(Project $project)
    {
        // Check authorization
        if (Auth::user()->role !== 'super_admin') {
            $hasAccess = $project->companies()->where('company_id', Auth::user()->company_id)->exists() ||
                        $project->users()->where('user_id', Auth::id())->exists();
            
            if (!$hasAccess) {
                abort(403, 'Access denied to this project.');
            }
        }
        
        // Load relationships
        $project->load(['customer', 'milestones.tasks.subtasks', 'timeEntries', 'additionalCosts']);
        
        // Get comprehensive metrics
        $metrics = $this->calculateProjectMetrics($project);
        
        // Get AI analysis
        $healthAnalysis = $this->claudeService->analyzeProjectHealth($project, $metrics);
        $risks = $this->claudeService->detectProjectRisks(
            $project, 
            $project->timeEntries,
            $project->milestones
        );
        
        // Get recommendations based on issues
        $issues = array_merge(
            $healthAnalysis['issues'] ?? [],
            array_column($risks, 'description')
        );
        $recommendations = $this->claudeService->generateRecommendations($project, $issues);
        
        // Get predictions
        $velocity = $this->calculateTeamVelocity($project);
        $remainingWork = $this->calculateRemainingWork($project);
        $completionPrediction = $this->claudeService->predictCompletion($project, $velocity, $remainingWork);
        
        // Get timeline data
        $timelineData = $this->getProjectTimeline($project);
        
        // Get budget trends
        $budgetTrends = $this->getBudgetTrends($project);
        
        return view('project-intelligence.show', compact(
            'project',
            'metrics',
            'healthAnalysis',
            'risks',
            'recommendations',
            'completionPrediction',
            'timelineData',
            'budgetTrends'
        ));
    }
    
    /**
     * Get AI recommendations via AJAX
     */
    public function getRecommendations(Project $project)
    {
        $issues = request('issues', []);
        $recommendations = $this->claudeService->generateRecommendations($project, $issues);
        
        return response()->json([
            'recommendations' => $recommendations
        ]);
    }
    
    /**
     * Refresh AI analysis
     */
    public function refresh(Project $project)
    {
        // Clear cache voor dit project
        Cache::forget("project-health-{$project->id}-*");
        Cache::forget("project-risks-{$project->id}-*");
        Cache::forget("project-completion-{$project->id}-*");
        
        return redirect()->route('project-intelligence.show', $project)
            ->with('success', 'AI analysis refreshed successfully');
    }
    
    /**
     * Calculate project metrics
     */
    protected function calculateProjectMetrics($project)
    {
        // Time metrics
        $totalHours = $project->timeEntries->sum('minutes') / 60;
        $billableHours = $project->timeEntries->where('is_billable', 'billable')->sum('minutes') / 60;
        $thisWeekHours = $project->timeEntries
            ->where('entry_date', '>=', now()->startOfWeek())
            ->sum('minutes') / 60;
        $lastWeekHours = $project->timeEntries
            ->whereBetween('entry_date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->sum('minutes') / 60;
        
        // Milestone metrics
        $totalMilestones = $project->milestones->count();
        $completedMilestones = $project->milestones->where('status', 'completed')->count();
        $overdueMilestones = $project->milestones->filter(function($m) {
            return $m->end_date && $m->end_date < now() && $m->status != 'completed';
        })->count();
        
        // Task metrics
        $totalTasks = $project->milestones->sum(function($m) {
            return $m->tasks->count();
        });
        $completedTasks = $project->milestones->sum(function($m) {
            return $m->tasks->where('status', 'completed')->count();
        });
        
        // Budget metrics
        $budgetData = $this->budgetService->getCurrentMonthBudget($project);
        $totalBudget = $budgetData['total_budget'] ?? 0;
        $budgetUsed = $budgetData['budget_used'] ?? 0;
        $budgetRemaining = $budgetData['budget_remaining'] ?? 0;
        
        $budgetUsedPercentage = $totalBudget > 0 
            ? round(($budgetUsed / $totalBudget) * 100, 1)
            : 0;
        
        // Progress metrics
        $completionPercentage = $totalMilestones > 0 
            ? round(($completedMilestones / $totalMilestones) * 100, 1)
            : 0;
        
        // Team metrics
        $activeTeamMembers = $project->timeEntries
            ->where('entry_date', '>=', now()->subDays(7))
            ->pluck('user_id')
            ->unique()
            ->count();
        
        // Deadline metrics
        $daysRemaining = (int) now()->diffInDays($project->end_date, false);
        $daysElapsed = (int) now()->diffInDays($project->start_date);
        $totalDays = (int) Carbon::parse($project->start_date)->diffInDays($project->end_date);
        $timeElapsedPercentage = $totalDays > 0 
            ? round(($daysElapsed / $totalDays) * 100, 1)
            : 0;
        
        return [
            'total_hours' => round($totalHours, 1),
            'billable_hours' => round($billableHours, 1),
            'this_week_hours' => round($thisWeekHours, 1),
            'last_week_hours' => round($lastWeekHours, 1),
            'hours_trend' => $lastWeekHours > 0 
                ? round((($thisWeekHours - $lastWeekHours) / $lastWeekHours) * 100, 1)
                : 0,
            'total_milestones' => $totalMilestones,
            'completed_milestones' => $completedMilestones,
            'overdue_milestones' => $overdueMilestones,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'completion_percentage' => $completionPercentage,
            'budget_used' => $budgetUsed,
            'budget_total' => $totalBudget,
            'budget_used_percentage' => $budgetUsedPercentage,
            'budget_remaining' => $budgetRemaining,
            'active_team_members' => $activeTeamMembers,
            'days_remaining' => $daysRemaining,
            'days_elapsed' => $daysElapsed,
            'time_elapsed_percentage' => $timeElapsedPercentage
        ];
    }
    
    /**
     * Calculate team velocity
     */
    protected function calculateTeamVelocity($project)
    {
        // Get last 4 weeks of data
        $weeksData = [];
        
        for ($i = 0; $i < 4; $i++) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $weekHours = $project->timeEntries
                ->whereBetween('entry_date', [$weekStart, $weekEnd])
                ->sum('minutes') / 60;
            
            if ($weekHours > 0) {
                $weeksData[] = $weekHours / 5; // Average per day
            }
        }
        
        if (empty($weeksData)) {
            return 0;
        }
        
        // Return average daily velocity
        return array_sum($weeksData) / count($weeksData);
    }
    
    /**
     * Calculate remaining work
     */
    protected function calculateRemainingWork($project)
    {
        $remainingHours = 0;
        
        foreach ($project->milestones as $milestone) {
            if ($milestone->status != 'completed') {
                // Use estimated hours if available
                if ($milestone->estimated_hours) {
                    $remainingHours += $milestone->estimated_hours;
                } else {
                    // Fallback: estimate based on tasks
                    $incompleteTasks = $milestone->tasks->where('status', '!=', 'completed');
                    foreach ($incompleteTasks as $task) {
                        $remainingHours += $task->estimated_hours ?? 8; // Default 8 hours per task
                    }
                }
            }
        }
        
        return $remainingHours;
    }
    
    /**
     * Get project health score
     */
    protected function getProjectHealthScore($project)
    {
        $metrics = $this->calculateProjectMetrics($project);
        return $this->claudeService->analyzeProjectHealth($project, $metrics);
    }
    
    /**
     * Get aggregated insights
     */
    protected function getAggregatedInsights($projects)
    {
        $insights = [
            'total_at_risk' => 0,
            'total_overdue' => 0,
            'average_health' => 0,
            'budget_issues' => 0,
            'resource_bottlenecks' => [],
            'common_issues' => []
        ];
        
        $healthScores = [];
        $issueCount = [];
        
        foreach ($projects as $project) {
            $health = $this->getProjectHealthScore($project);
            $healthScores[] = $health['score'];
            
            if ($health['status'] == 'critical' || $health['status'] == 'warning') {
                $insights['total_at_risk']++;
            }
            
            if ($project->end_date < now() && $project->status != 'completed') {
                $insights['total_overdue']++;
            }
            
            // Count issues
            foreach ($health['issues'] ?? [] as $issue) {
                $issueType = $this->categorizeIssue($issue);
                $issueCount[$issueType] = ($issueCount[$issueType] ?? 0) + 1;
            }
        }
        
        $insights['average_health'] = !empty($healthScores) 
            ? round(array_sum($healthScores) / count($healthScores), 1)
            : 0;
        
        // Get top 3 common issues
        arsort($issueCount);
        $insights['common_issues'] = array_slice(array_keys($issueCount), 0, 3);
        
        return $insights;
    }
    
    /**
     * Categorize issue type
     */
    protected function categorizeIssue($issue)
    {
        $issue = strtolower($issue);
        
        if (strpos($issue, 'budget') !== false || strpos($issue, 'cost') !== false) {
            return 'Budget concerns';
        } elseif (strpos($issue, 'deadline') !== false || strpos($issue, 'overdue') !== false) {
            return 'Schedule delays';
        } elseif (strpos($issue, 'resource') !== false || strpos($issue, 'team') !== false) {
            return 'Resource issues';
        } elseif (strpos($issue, 'scope') !== false || strpos($issue, 'requirement') !== false) {
            return 'Scope changes';
        } else {
            return 'Other issues';
        }
    }
    
    /**
     * Get project timeline data
     */
    protected function getProjectTimeline($project)
    {
        $timeline = [];
        
        // Add milestones to timeline
        foreach ($project->milestones as $milestone) {
            if ($milestone->end_date) {
                $timeline[] = [
                    'date' => $milestone->end_date,
                    'type' => 'milestone',
                    'title' => $milestone->name,
                    'status' => $milestone->status,
                    'overdue' => $milestone->end_date < now() && $milestone->status != 'completed'
                ];
            }
        }
        
        // Sort by date
        usort($timeline, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        
        return $timeline;
    }
    
    /**
     * Get budget trends
     */
    protected function getBudgetTrends($project)
    {
        $trends = [];
        
        // Get last 6 months of budget data
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            // Calculate budget for specific month
            $monthlyFee = $this->budgetService->calculateMonthlyBudget(
                $project,
                $date->year,
                $date->month
            );
            
            $budget = $monthlyFee->total_budget ?? 0;
            $used = $monthlyFee->budget_used ?? 0;
            
            $trends[] = [
                'month' => $date->format('M Y'),
                'budget' => $budget,
                'used' => $used,
                'percentage' => $budget > 0 
                    ? round(($used / $budget) * 100, 1)
                    : 0
            ];
        }
        
        return $trends;
    }
}