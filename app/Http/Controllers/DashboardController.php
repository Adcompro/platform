<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Customer;
use App\Models\Company;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;
        
        // Get role-based data with comprehensive statistics
        $data = $this->getDashboardData($user);
        
        // Add additional comprehensive data
        $data['revenue_chart'] = $this->getRevenueChartData($user);
        $data['productivity_data'] = $this->getProductivityData($user);
        $data['project_health'] = $this->getProjectHealthData($user);
        $data['upcoming_deadlines'] = $this->getUpcomingDeadlines($user);
        $data['team_performance'] = $this->getTeamPerformanceData($user);
        $data['recent_activities'] = $this->getRecentActivities($user);
        $data['budget_overview'] = $this->getBudgetOverview($user);
        
        return view('dashboard', compact('data', 'user', 'company'));
    }

    public function getChartData(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '7'); // Default 7 days
        
        $startDate = Carbon::now()->subDays($period - 1);
        $endDate = Carbon::now();
        
        // Get daily time entries
        $timeEntries = TimeEntry::where('user_id', $user->id)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->selectRaw('DATE(entry_date) as date, SUM(hours) as total_hours')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Create data array for Chart.js
        $labels = [];
        $data = [];
        
        for ($i = 0; $i < $period; $i++) {
            $date = $startDate->copy()->addDays($i);
            $labels[] = $date->format('M j');
            
            $entry = $timeEntries->firstWhere('date', $date->format('Y-m-d'));
            $data[] = $entry ? (float) $entry->total_hours : 0;
        }
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Hours Logged',
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ]);
    }

    public function getWeeklySummary()
    {
        $user = Auth::user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Get this week's time entries
        $weeklyEntries = TimeEntry::where('user_id', $user->id)
            ->whereBetween('entry_date', [$startOfWeek, $endOfWeek])
            ->with(['project', 'projectMilestone', 'projectTask'])
            ->orderBy('entry_date')
            ->get();
        
        $totalHours = $weeklyEntries->sum('hours');
        $approvedHours = $weeklyEntries->where('status', 'approved')->sum('hours');
        $pendingHours = $weeklyEntries->where('status', 'pending')->sum('hours');
        
        return response()->json([
            'total_hours' => $totalHours,
            'approved_hours' => $approvedHours, 
            'pending_hours' => $pendingHours,
            'entries' => $weeklyEntries->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'date' => $entry->date->format('M j'),
                    'hours' => $entry->hours,
                    'description' => $entry->description,
                    'project' => $entry->project?->name,
                    'milestone' => $entry->projectMilestone?->name,
                    'task' => $entry->projectTask?->name,
                    'status' => $entry->status,
                    'hourly_rate' => $entry->hourly_rate
                ];
            })
        ]);
    }

    private function getDashboardData($user)
    {
        $role = $user->role;
        $companyId = $user->company_id;
        
        $data = [
            'stats' => [],
            'recent_projects' => [],
            'pending_approvals' => [],
            'upcoming_deadlines' => []
        ];
        
        // Base stats for all roles
        $data['stats']['active_projects'] = Project::where('status', 'active')
            ->whereHas('companies', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->count();
        
        // Role-specific data
        switch ($role) {
            case 'super_admin':
            case 'admin':
                $data = $this->getAdminDashboardData($data, $user);
                break;
                
            case 'project_manager':
                $data = $this->getProjectManagerDashboardData($data, $user);
                break;
                
            case 'user':
            case 'reader':
                $data = $this->getUserDashboardData($data, $user);
                break;
        }
        
        return $data;
    }

    private function getAdminDashboardData($data, $user)
    {
        $companyId = $user->company_id;
        
        // Admin stats
        $data['stats']['total_revenue'] = Invoice::whereHas('project.companies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->where('status', 'paid')
        ->sum('total_amount');
        
        $data['stats']['pending_invoices'] = Invoice::whereHas('project.companies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->whereIn('status', ['sent', 'overdue'])
        ->count();
        
        $data['stats']['total_users'] = User::where('company_id', $companyId)->count();
        
        // Recent projects
        $data['recent_projects'] = Project::whereHas('companies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->with(['customer', 'mainInvoicingCompany'])
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();
        
        // Pending time approvals
        $data['pending_approvals'] = TimeEntry::whereHas('project.companies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->where('status', 'pending')
        ->with(['user', 'project'])
        ->orderBy('entry_date', 'desc')
        ->limit(10)
        ->get();
        
        return $data;
    }

    private function getProjectManagerDashboardData($data, $user)
    {
        $companyId = $user->company_id;
        
        // Project manager stats
        $data['stats']['managed_projects'] = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'active')
        ->count();
        
        $data['stats']['team_hours_today'] = TimeEntry::whereHas('project.users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereDate('entry_date', today())
        ->sum('hours');
        
        $data['stats']['pending_approvals'] = TimeEntry::whereHas('project.users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->count();
        
        // Projects managed by this user
        $data['recent_projects'] = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['customer', 'mainInvoicingCompany'])
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();
        
        // Time entries awaiting approval from this manager
        $data['pending_approvals'] = TimeEntry::whereHas('project.users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->with(['user', 'project'])
        ->orderBy('entry_date', 'desc')
        ->limit(10)
        ->get();
        
        return $data;
    }

    private function getUserDashboardData($data, $user)
    {
        // User/Reader stats
        $data['stats']['my_projects'] = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'active')
        ->count();
        
        $data['stats']['hours_today'] = TimeEntry::where('user_id', $user->id)
            ->whereDate('entry_date', today())
            ->sum('hours');
        
        $data['stats']['hours_this_week'] = TimeEntry::where('user_id', $user->id)
            ->whereBetween('entry_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->sum('hours');
        
        $data['stats']['pending_entries'] = TimeEntry::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        // User's recent projects
        $data['recent_projects'] = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['customer', 'mainInvoicingCompany'])
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();
        
        return $data;
    }
    
    private function getRevenueChartData($user)
    {
        // Get last 6 months of revenue data
        $months = collect();
        $revenueData = [];
        $projectedData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->format('M Y'));
            
            // Calculate actual revenue (monthly fees + invoiced amounts)
            $monthlyRevenue = Project::where('status', 'active')
                ->when($user->role !== 'super_admin', function ($q) use ($user) {
                    $q->whereHas('companies', function ($query) use ($user) {
                        $query->where('company_id', $user->company_id);
                    });
                })
                ->whereDate('created_at', '<=', $date->endOfMonth())
                ->sum('monthly_fee');
                
            $revenueData[] = $monthlyRevenue;
            
            // Calculate projected revenue (based on active projects)
            $projectedRevenue = $monthlyRevenue * 1.1; // 10% growth projection
            $projectedData[] = $projectedRevenue;
        }
        
        return [
            'labels' => $months,
            'actual' => $revenueData,
            'projected' => $projectedData
        ];
    }
    
    private function getProductivityData($user)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Daily hours for current week
        $dailyHours = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $hours = TimeEntry::whereDate('entry_date', $date)
                ->when($user->role !== 'super_admin', function ($q) use ($user) {
                    if ($user->role === 'admin') {
                        $q->whereHas('user', function ($query) use ($user) {
                            $query->where('company_id', $user->company_id);
                        });
                    } else {
                        $q->where('user_id', $user->id);
                    }
                })
                ->sum('hours');
            
            $dailyHours[] = [
                'day' => $date->format('D'),
                'hours' => round($hours, 1),
                'date' => $date->format('Y-m-d')
            ];
        }
        
        // Calculate productivity metrics
        $totalHours = collect($dailyHours)->sum('hours');
        $targetHours = 40; // Standard work week
        $productivity = $totalHours > 0 ? round(($totalHours / $targetHours) * 100, 1) : 0;
        
        return [
            'daily_hours' => $dailyHours,
            'total_hours' => $totalHours,
            'target_hours' => $targetHours,
            'productivity_percentage' => $productivity,
            'average_daily' => $totalHours > 0 ? round($totalHours / 5, 1) : 0 // Assuming 5 work days
        ];
    }
    
    private function getProjectHealthData($user)
    {
        $projects = Project::with(['milestones', 'customer'])
            ->when($user->role !== 'super_admin', function ($q) use ($user) {
                if (in_array($user->role, ['admin', 'project_manager'])) {
                    $q->whereHas('companies', function ($query) use ($user) {
                        $query->where('company_id', $user->company_id);
                    });
                } else {
                    $q->whereHas('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                }
            })
            ->where('status', 'active')
            ->get();
        
        $healthData = [
            'on_track' => 0,
            'at_risk' => 0,
            'delayed' => 0,
            'completed' => 0
        ];
        
        foreach ($projects as $project) {
            // Calculate project health based on milestones and deadlines
            $completedMilestones = $project->milestones->where('status', 'completed')->count();
            $totalMilestones = $project->milestones->count();
            $progress = $totalMilestones > 0 ? ($completedMilestones / $totalMilestones) * 100 : 0;
            
            if ($project->end_date && Carbon::parse($project->end_date)->isPast()) {
                $healthData['delayed']++;
            } elseif ($progress >= 75 || !$project->end_date) {
                $healthData['on_track']++;
            } elseif ($progress >= 50) {
                $healthData['at_risk']++;
            } else {
                $healthData['delayed']++;
            }
        }
        
        $healthData['completed'] = Project::where('status', 'completed')
            ->when($user->role !== 'super_admin', function ($q) use ($user) {
                $q->whereHas('companies', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                });
            })
            ->count();
        
        return $healthData;
    }
    
    private function getUpcomingDeadlines($user)
    {
        $deadlines = collect();
        
        // Project deadlines
        $projects = Project::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '>=', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addDays(30))
            ->when($user->role !== 'super_admin', function ($q) use ($user) {
                if (in_array($user->role, ['admin', 'project_manager'])) {
                    $q->whereHas('companies', function ($query) use ($user) {
                        $query->where('company_id', $user->company_id);
                    });
                } else {
                    $q->whereHas('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                }
            })
            ->orderBy('end_date')
            ->limit(5)
            ->get();
        
        foreach ($projects as $project) {
            $deadlines->push([
                'type' => 'project',
                'title' => $project->name,
                'date' => Carbon::parse($project->end_date),
                'days_left' => Carbon::now()->diffInDays($project->end_date),
                'urgency' => Carbon::now()->diffInDays($project->end_date) <= 7 ? 'high' : 'medium'
            ]);
        }
        
        // Milestone deadlines
        $milestones = ProjectMilestone::whereHas('project', function ($q) {
                $q->where('status', 'active');
            })
            ->where('status', '!=', 'completed')
            ->whereNotNull('end_date')
            ->where('end_date', '>=', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addDays(14))
            ->when($user->role !== 'super_admin', function ($q) use ($user) {
                $q->whereHas('project.companies', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                });
            })
            ->with('project')
            ->orderBy('end_date')
            ->limit(5)
            ->get();
        
        foreach ($milestones as $milestone) {
            $deadlines->push([
                'type' => 'milestone',
                'title' => $milestone->name,
                'project' => $milestone->project->name,
                'date' => Carbon::parse($milestone->end_date),
                'days_left' => Carbon::now()->diffInDays($milestone->end_date),
                'urgency' => Carbon::now()->diffInDays($milestone->end_date) <= 3 ? 'high' : 'medium'
            ]);
        }
        
        return $deadlines->sortBy('days_left')->take(8);
    }
    
    private function getTeamPerformanceData($user)
    {
        if (!in_array($user->role, ['super_admin', 'admin', 'project_manager'])) {
            return null;
        }
        
        $teamMembers = User::where('is_active', true)
            ->when($user->role === 'admin', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->when($user->role === 'project_manager', function ($q) use ($user) {
                // Get team members from projects managed by this user
                $q->whereHas('projects', function ($query) use ($user) {
                    $query->whereHas('users', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
                });
            })
            ->get();
        
        $performanceData = [];
        
        foreach ($teamMembers as $member) {
            $weekHours = TimeEntry::where('user_id', $member->id)
                ->whereBetween('entry_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->sum('hours');
            
            $approvedHours = TimeEntry::where('user_id', $member->id)
                ->whereBetween('entry_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->where('status', 'approved')
                ->sum('hours');
            
            $performanceData[] = [
                'name' => $member->name,
                'role' => ucfirst(str_replace('_', ' ', $member->role)),
                'hours_this_week' => round($weekHours, 1),
                'approved_hours' => round($approvedHours, 1),
                'approval_rate' => $weekHours > 0 ? round(($approvedHours / $weekHours) * 100, 1) : 0,
                'active_projects' => $member->projects()->where('status', 'active')->count()
            ];
        }
        
        // Sort by hours this week
        return collect($performanceData)->sortByDesc('hours_this_week')->take(10)->values();
    }
    
    private function getRecentActivities($user)
    {
        $activities = collect();
        
        // Recent time entries
        $recentEntries = TimeEntry::with(['user', 'project'])
            ->when($user->role !== 'super_admin', function ($q) use ($user) {
                if ($user->role === 'admin') {
                    $q->whereHas('user', function ($query) use ($user) {
                        $query->where('company_id', $user->company_id);
                    });
                } else {
                    $q->where('user_id', $user->id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentEntries as $entry) {
            $activities->push([
                'type' => 'time_entry',
                'icon' => 'clock',
                'color' => 'blue',
                'title' => $entry->user->name . ' logged ' . $entry->hours . ' hours',
                'description' => $entry->project ? 'on ' . $entry->project->name : '',
                'time' => $entry->created_at->diffForHumans()
            ]);
        }
        
        // Recent project updates
        $recentProjects = Project::when($user->role !== 'super_admin', function ($q) use ($user) {
                $q->whereHas('companies', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                });
            })
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
        
        foreach ($recentProjects as $project) {
            $activities->push([
                'type' => 'project',
                'icon' => 'project-diagram',
                'color' => 'green',
                'title' => 'Project ' . $project->name . ' was updated',
                'description' => 'Status: ' . ucfirst($project->status),
                'time' => $project->updated_at->diffForHumans()
            ]);
        }
        
        return $activities->sortByDesc(function ($activity) {
            return $activity['time'];
        })->take(10);
    }
    
    private function getBudgetOverview($user)
    {
        $projects = Project::with(['timeEntries', 'additionalCosts'])
            ->where('status', 'active')
            ->when($user->role !== 'super_admin', function ($q) use ($user) {
                if (in_array($user->role, ['admin'])) {
                    $q->whereHas('companies', function ($query) use ($user) {
                        $query->where('company_id', $user->company_id);
                    });
                } else {
                    $q->whereHas('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                }
            })
            ->get();
        
        $totalBudget = 0;
        $totalSpent = 0;
        $overBudgetProjects = 0;
        
        foreach ($projects as $project) {
            $monthlyBudget = $project->monthly_fee ?? 0;
            $totalBudget += $monthlyBudget;
            
            // Calculate spent amount from time entries
            $hoursLogged = $project->timeEntries()
                ->whereMonth('entry_date', Carbon::now()->month)
                ->sum('hours');
            
            $hourlyRate = $project->default_hourly_rate ?? 75;
            $spent = $hoursLogged * $hourlyRate;
            
            // Add additional costs
            $additionalCosts = $project->additionalCosts()
                ->where('is_active', true)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('amount');
            
            $spent += $additionalCosts;
            $totalSpent += $spent;
            
            if ($monthlyBudget > 0 && $spent > $monthlyBudget) {
                $overBudgetProjects++;
            }
        }
        
        return [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'remaining' => $totalBudget - $totalSpent,
            'usage_percentage' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0,
            'over_budget_projects' => $overBudgetProjects,
            'total_projects' => $projects->count()
        ];
    }
}