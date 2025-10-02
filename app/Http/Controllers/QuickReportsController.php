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
use App\Models\ProjectMilestone;
use Barryvdh\DomPDF\Facade\Pdf;

class QuickReportsController extends Controller
{
    /**
     * Display the quick reports dashboard
     */
    public function index()
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can access reports.');
        }
        
        return view('reports.quick-reports');
    }
    
    /**
     * Generate Weekly Timesheet Report
     */
    public function weeklyTimesheet(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'week_start' => 'nullable|date'
        ]);
        
        $userId = $request->get('user_id');
        $weekStart = $request->get('week_start') ? Carbon::parse($request->get('week_start'))->startOfWeek() : Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        // Build query
        $query = TimeEntry::with(['user', 'project', 'milestone', 'task', 'subtask'])
            ->whereBetween('entry_date', [$weekStart, $weekEnd])
            ->orderBy('entry_date')
            ->orderBy('created_at');
        
        // Filter by user if specified
        if ($userId) {
            $query->where('user_id', $userId);
            $user = User::find($userId);
        } else {
            // For non-super_admin, limit to their company
            if (Auth::user()->role !== 'super_admin') {
                $query->whereHas('user', function ($q) {
                    $q->where('company_id', Auth::user()->company_id);
                });
            }
            $user = null;
        }
        
        $timeEntries = $query->get();
        
        // Group by user and date
        $groupedEntries = $timeEntries->groupBy(['user_id', function ($entry) {
            return $entry->entry_date->format('Y-m-d');
        }]);
        
        // Calculate totals - minutes field contains the TOTAL minutes for the entry
        $totalMinutes = $timeEntries->sum('minutes');
        $totalBillableMinutes = $timeEntries->where('is_billable', 'billable')->sum('minutes');
        
        // Convert total minutes to hours and remaining minutes
        $totalHours = floor($totalMinutes / 60);
        $totalMinutes = $totalMinutes % 60;
        $totalBillableHours = floor($totalBillableMinutes / 60);
        $totalBillableMinutes = $totalBillableMinutes % 60;
        
        // Get list of users for dropdown
        $users = User::where('is_active', true)
            ->when(Auth::user()->role !== 'super_admin', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();
        
        return view('reports.weekly-timesheet', compact(
            'groupedEntries', 
            'weekStart', 
            'weekEnd', 
            'user', 
            'users',
            'totalHours',
            'totalMinutes',
            'totalBillableHours',
            'totalBillableMinutes'
        ));
    }
    
    /**
     * Export Weekly Timesheet as PDF
     */
    public function weeklyTimesheetPdf(Request $request)
    {
        // Same logic as weeklyTimesheet but return PDF
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'week_start' => 'nullable|date'
        ]);
        
        $userId = $validated['user_id'] ?? null;
        $weekStart = $validated['week_start'] ? Carbon::parse($validated['week_start'])->startOfWeek() : Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $query = TimeEntry::with(['user', 'project', 'milestone', 'task', 'subtask'])
            ->whereBetween('entry_date', [$weekStart, $weekEnd])
            ->orderBy('entry_date')
            ->orderBy('created_at');
        
        if ($userId) {
            $query->where('user_id', $userId);
            $user = User::find($userId);
        } else {
            if (Auth::user()->role !== 'super_admin') {
                $query->whereHas('user', function ($q) {
                    $q->where('company_id', Auth::user()->company_id);
                });
            }
            $user = null;
        }
        
        $timeEntries = $query->get();
        $groupedEntries = $timeEntries->groupBy(['user_id', function ($entry) {
            return $entry->entry_date->format('Y-m-d');
        }]);
        
        $totalHours = $timeEntries->sum('hours');
        $totalMinutes = $timeEntries->sum('minutes');
        $totalHours += floor($totalMinutes / 60);
        $totalMinutes = $totalMinutes % 60;
        
        $pdf = PDF::loadView('reports.pdf.weekly-timesheet', compact(
            'groupedEntries', 
            'weekStart', 
            'weekEnd', 
            'user',
            'totalHours',
            'totalMinutes'
        ));
        
        $filename = 'timesheet-' . $weekStart->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
    
    /**
     * Generate Monthly Invoice Overview
     */
    public function monthlyInvoices(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m'
        ]);
        
        $month = $validated['month'] ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get invoices for the month
        $query = Invoice::with(['project', 'project.customer', 'invoicingCompany'])
            ->whereBetween('invoice_date', [$startDate, $endDate]);
        
        // Filter by company for non-super_admin
        if (Auth::user()->role !== 'super_admin') {
            $query->where('invoicing_company_id', Auth::user()->company_id);
        }
        
        $invoices = $query->orderBy('invoice_number')->get();
        
        // Calculate statistics
        $stats = [
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_inc_vat'),
            'paid_amount' => $invoices->where('status', 'paid')->sum('total_inc_vat'),
            'pending_amount' => $invoices->whereIn('status', ['sent', 'overdue'])->sum('total_inc_vat'),
            'draft_amount' => $invoices->where('status', 'draft')->sum('total_inc_vat'),
            'average_invoice' => $invoices->count() > 0 ? $invoices->avg('total_inc_vat') : 0,
            'by_status' => [
                'draft' => $invoices->where('status', 'draft')->count(),
                'sent' => $invoices->where('status', 'sent')->count(),
                'paid' => $invoices->where('status', 'paid')->count(),
                'overdue' => $invoices->where('status', 'overdue')->count(),
            ]
        ];
        
        // Generate month options
        $monthOptions = [];
        for ($i = -12; $i <= 3; $i++) {
            $date = Carbon::now()->addMonths($i);
            $monthOptions[$date->format('Y-m')] = $date->format('F Y');
        }
        
        return view('reports.monthly-invoices', compact(
            'invoices',
            'stats',
            'startDate',
            'endDate',
            'month',
            'monthOptions'
        ));
    }
    
    /**
     * Generate Project Profitability Report
     */
    public function projectProfitability(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:month,quarter,year,all',
            'sort' => 'nullable|in:profit_desc,profit_asc,margin_desc,margin_asc'
        ]);
        
        $period = $validated['period'] ?? 'month';
        $sort = $validated['sort'] ?? 'profit_desc';
        
        // Determine date range
        switch ($period) {
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                break;
            default:
                $startDate = null;
        }
        
        // Get projects with calculations
        $query = Project::with(['customer', 'timeEntries', 'additionalCosts', 'invoices']);
        
        // Filter by company for non-super_admin
        if (Auth::user()->role !== 'super_admin') {
            $query->whereHas('companies', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }
        
        $projects = $query->where('status', '!=', 'cancelled')->get();
        
        $profitabilityData = [];
        
        foreach ($projects as $project) {
            // Calculate revenue (invoiced amounts)
            $revenue = $project->invoices()
                ->when($startDate, function ($q) use ($startDate) {
                    $q->where('invoice_date', '>=', $startDate);
                })
                ->where('status', '!=', 'cancelled')
                ->sum('total_inc_vat');
            
            // Add monthly fees if applicable
            if ($project->monthly_fee && $startDate) {
                $monthsSinceStart = $startDate->diffInMonths(now()) + 1;
                $feeRevenue = $project->monthly_fee * $monthsSinceStart;
                $revenue += $feeRevenue;
            }
            
            // Calculate costs (time entries)
            $timeEntries = $project->timeEntries()
                ->when($startDate, function ($q) use ($startDate) {
                    $q->where('entry_date', '>=', $startDate);
                })
                ->where('status', 'approved')
                ->get();
            
            $laborCost = 0;
            foreach ($timeEntries as $entry) {
                $hours = $entry->hours + ($entry->minutes / 60);
                // Use a cost rate (could be different from billing rate)
                $costRate = 50; // Default internal cost per hour
                $laborCost += $hours * $costRate;
            }
            
            // Add additional costs
            $additionalCosts = $project->additionalCosts()
                ->when($startDate, function ($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                })
                ->where('is_active', true)
                ->sum('amount');
            
            $totalCost = $laborCost + $additionalCosts;
            $profit = $revenue - $totalCost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
            
            $profitabilityData[] = [
                'project' => $project,
                'revenue' => $revenue,
                'labor_cost' => $laborCost,
                'additional_costs' => $additionalCosts,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'margin' => $margin
            ];
        }
        
        // Sort results
        switch ($sort) {
            case 'profit_asc':
                $profitabilityData = collect($profitabilityData)->sortBy('profit')->values();
                break;
            case 'margin_desc':
                $profitabilityData = collect($profitabilityData)->sortByDesc('margin')->values();
                break;
            case 'margin_asc':
                $profitabilityData = collect($profitabilityData)->sortBy('margin')->values();
                break;
            default: // profit_desc
                $profitabilityData = collect($profitabilityData)->sortByDesc('profit')->values();
        }
        
        // Calculate totals
        $totals = [
            'revenue' => collect($profitabilityData)->sum('revenue'),
            'cost' => collect($profitabilityData)->sum('total_cost'),
            'profit' => collect($profitabilityData)->sum('profit'),
            'projects' => count($profitabilityData),
            'profitable' => collect($profitabilityData)->where('profit', '>', 0)->count(),
            'loss_making' => collect($profitabilityData)->where('profit', '<', 0)->count()
        ];
        
        return view('reports.project-profitability', compact(
            'profitabilityData',
            'totals',
            'period',
            'sort',
            'startDate'
        ));
    }
    
    /**
     * Generate Overdue Milestones Report
     */
    public function overdueMilestones(Request $request)
    {
        $validated = $request->validate([
            'days_overdue' => 'nullable|integer|min:0'
        ]);
        
        $daysOverdue = $validated['days_overdue'] ?? 0;
        $cutoffDate = Carbon::now()->subDays($daysOverdue);
        
        // Get overdue milestones
        $query = ProjectMilestone::with(['project', 'project.customer', 'project.users'])
            ->where('status', '!=', 'completed')
            ->whereNotNull('end_date')
            ->where('end_date', '<', Carbon::now())
            ->where('end_date', '<=', $cutoffDate);
        
        // Filter by company for non-super_admin
        if (Auth::user()->role !== 'super_admin') {
            $query->whereHas('project.companies', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            });
        }
        
        $milestones = $query->orderBy('end_date')->get();
        
        // Calculate days overdue for each
        foreach ($milestones as $milestone) {
            $milestone->days_overdue = Carbon::parse($milestone->end_date)->diffInDays(now());
            $milestone->urgency = $milestone->days_overdue > 30 ? 'critical' : 
                                 ($milestone->days_overdue > 14 ? 'high' : 
                                 ($milestone->days_overdue > 7 ? 'medium' : 'low'));
        }
        
        // Group by urgency
        $grouped = [
            'critical' => $milestones->where('urgency', 'critical'),
            'high' => $milestones->where('urgency', 'high'),
            'medium' => $milestones->where('urgency', 'medium'),
            'low' => $milestones->where('urgency', 'low')
        ];
        
        // Statistics
        $stats = [
            'total' => $milestones->count(),
            'critical' => $grouped['critical']->count(),
            'high' => $grouped['high']->count(),
            'medium' => $grouped['medium']->count(),
            'low' => $grouped['low']->count(),
            'average_delay' => $milestones->count() > 0 ? round($milestones->avg('days_overdue'), 1) : 0,
            'max_delay' => $milestones->count() > 0 ? $milestones->max('days_overdue') : 0
        ];
        
        return view('reports.overdue-milestones', compact(
            'milestones',
            'grouped',
            'stats',
            'daysOverdue'
        ));
    }
}