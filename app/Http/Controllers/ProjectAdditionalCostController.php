<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAdditionalCost;
use App\Models\ProjectMonthlyAdditionalCost;
use App\Models\ProjectMonthlyFee;
use App\Models\TimeEntry;
use App\Services\ProjectBudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProjectAdditionalCostController extends Controller
{
    /**
     * Display additional costs for a project
     */
    public function index(Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) && 
            !$project->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Access denied. You are not authorized to view this project\'s costs.');
        }

        // Get one-time additional costs
        $oneTimeCosts = $project->additionalCosts()
            ->with(['creator'])
            ->where('cost_type', 'one_time')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get monthly recurring costs
        $monthlyCosts = $project->additionalCosts()
            ->with(['creator'])
            ->where('cost_type', 'monthly_recurring')
            ->orderBy('is_active', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get current month's time entries
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        
        $timeEntries = TimeEntry::where('project_id', $project->id)
            ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
            ->with(['user', 'milestone', 'task', 'subtask'])
            ->orderBy('entry_date', 'desc')
            ->limit(10)
            ->get();
        
        // Calculate time entry costs
        $timeEntryCosts = TimeEntry::where('project_id', $project->id)
            ->where('status', 'approved')
            ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('hours * hourly_rate_used'));

        // Calculate totals
        $stats = [
            'total_one_time' => $oneTimeCosts->sum('amount'),
            'total_one_time_in_fee' => $oneTimeCosts->where('fee_type', 'in_fee')->sum('amount'),
            'total_one_time_extended' => $oneTimeCosts->where('fee_type', 'additional')->sum('amount'),
            'total_monthly' => $monthlyCosts->where('is_active', true)->sum('amount'),
            'total_monthly_in_fee' => $monthlyCosts->where('is_active', true)->where('fee_type', 'in_fee')->sum('amount'),
            'total_monthly_extended' => $monthlyCosts->where('is_active', true)->where('fee_type', 'additional')->sum('amount'),
            'total_time_costs' => $timeEntryCosts,
            'pending_approval' => TimeEntry::where('project_id', $project->id)->where('status', 'pending')->count(),
        ];

        // Use the budget service for complete calculations
        $budgetService = new ProjectBudgetService();
        $budgetData = $budgetService->getCurrentMonthBudget($project);
        
        $budgetStats = [
            'monthly_budget' => $budgetData['monthly_fee']->base_monthly_fee,
            'rollover_from_previous' => $budgetData['monthly_fee']->rollover_from_previous,
            'total_budget' => $budgetData['monthly_fee']->total_available_fee,
            'used_this_month' => $budgetData['monthly_fee']->amount_invoiced_from_fee,
            'remaining_budget' => $budgetData['monthly_fee']->budget_remaining,
            'percentage_used' => $budgetData['monthly_fee']->budget_percentage_used,
            'is_over_budget' => $budgetData['monthly_fee']->is_over_budget,
            'budget_exceeded' => $budgetData['monthly_fee']->budget_exceeded,
            'budget_status' => $budgetData['budget_status'],
            'time_entry_hours' => $budgetData['monthly_fee']->hours_worked,
            'time_entry_costs' => $budgetData['monthly_fee']->hours_value,
        ];

        // Get budget history (last 6 months)
        $budgetHistory = $budgetService->getBudgetHistory($project, 6);

        return view('projects.additional-costs.index', compact(
            'project', 
            'oneTimeCosts', 
            'monthlyCosts',
            'timeEntries',
            'stats',
            'budgetStats',
            'budgetHistory'
        ));
    }

    /**
     * Show form for creating one-time additional cost
     */
    public function create(Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can add costs.');
        }

        return view('projects.additional-costs.create', compact('project'));
    }

    /**
     * Store one-time additional cost
     */
    public function store(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can add costs.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'fee_type' => 'required|in:in_fee,additional',
            'cost_type' => 'required|in:one_time,monthly_recurring',
            'category' => 'required|in:hosting,software,licenses,services,other',
            'vendor' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'auto_invoice' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $cost = $project->additionalCosts()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'],
                'amount' => $validated['amount'],
                'fee_type' => $validated['fee_type'],
                'cost_type' => $validated['cost_type'],
                'category' => $validated['category'],
                'vendor' => $validated['vendor'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'auto_invoice' => $request->boolean('auto_invoice'),
                'notes' => $validated['notes'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            Log::info('Additional cost created', ['cost_id' => $cost->id, 'project_id' => $project->id]);

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Additional cost added successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating additional cost', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show form for creating monthly recurring cost
     */
    public function createMonthly(Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can add costs.');
        }

        return view('projects.additional-costs.create-monthly', compact('project'));
    }

    /**
     * Store monthly recurring cost
     */
    public function storeMonthly(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can add costs.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'fee_type' => 'required|in:in_fee,additional',
            'cost_type' => 'required|in:one_time,monthly_recurring',
            'category' => 'required|in:hosting,software,licenses,services,other',
            'vendor' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'auto_invoice' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $monthlyCost = $project->additionalCosts()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'amount' => $validated['amount'],
                'fee_type' => $validated['fee_type'],
                'cost_type' => $validated['cost_type'],
                'category' => $validated['category'],
                'vendor' => $validated['vendor'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'auto_invoice' => $request->boolean('auto_invoice'),
                'notes' => $validated['notes'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            Log::info('Monthly additional cost created', ['cost_id' => $monthlyCost->id, 'project_id' => $project->id]);

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Monthly recurring cost added successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating monthly additional cost', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Edit one-time additional cost
     */
    public function edit(Project $project, ProjectAdditionalCost $additionalCost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can edit costs.');
        }

        if (!$additionalCost->canBeEdited()) {
            return redirect()->route('projects.additional-costs.index', $project)
                ->with('error', 'This cost cannot be edited because it is not active.');
        }

        return view('projects.additional-costs.edit', compact(
            'project',
            'additionalCost'
        ));
    }

    /**
     * Update one-time additional cost
     */
    public function update(Request $request, Project $project, ProjectAdditionalCost $additionalCost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can edit costs.');
        }

        if (!$additionalCost->canBeEdited()) {
            return redirect()->route('projects.additional-costs.index', $project)
                ->with('error', 'This cost cannot be edited because it is not active.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'fee_type' => 'required|in:in_fee,additional',
            'cost_type' => 'required|in:one_time,monthly_recurring',
            'category' => 'required|in:hosting,software,licenses,services,other',
            'vendor' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'auto_invoice' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $additionalCost->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'],
                'amount' => $validated['amount'],
                'fee_type' => $validated['fee_type'],
                'cost_type' => $validated['cost_type'],
                'category' => $validated['category'],
                'vendor' => $validated['vendor'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'auto_invoice' => $request->boolean('auto_invoice'),
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Additional cost updated', ['cost_id' => $additionalCost->id]);

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Additional cost updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating additional cost', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete one-time additional cost
     */
    public function destroy(Project $project, ProjectAdditionalCost $additionalCost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete costs.');
        }

        if (!$additionalCost->canBeDeleted()) {
            return redirect()->route('projects.additional-costs.index', $project)
                ->with('error', 'This cost cannot be deleted because it is invoiced.');
        }

        try {
            $additionalCost->delete();
            Log::info('Additional cost deleted', ['cost_id' => $additionalCost->id]);

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Additional cost deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting additional cost', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Approve additional cost
     */
    public function approve(Project $project, ProjectAdditionalCost $additionalCost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can approve costs.');
        }

        try {
            $additionalCost->approve(Auth::user());
            Log::info('Additional cost approved', ['cost_id' => $additionalCost->id, 'approved_by' => Auth::id()]);

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Additional cost approved successfully.');

        } catch (\Exception $e) {
            Log::error('Error approving additional cost', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Toggle cost active status
     */
    public function toggleMonthly(Project $project, ProjectAdditionalCost $cost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can toggle costs.');
        }

        try {
            $cost->update([
                'is_active' => !$cost->is_active
            ]);

            $status = $cost->is_active ? 'activated' : 'deactivated';
            Log::info("Cost {$status}", ['cost_id' => $cost->id]);

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', "Cost {$status} successfully.");

        } catch (\Exception $e) {
            Log::error('Error toggling cost', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Calculate monthly usage for budget tracking
     */
    private function calculateMonthlyUsage(Project $project, $year, $month)
    {
        // One-time costs in this month marked as in_fee
        $oneTimeCosts = $project->additionalCosts()
            ->where('cost_type', 'one_time')
            ->where('fee_type', 'in_fee')
            ->where('is_active', true)
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->sum('amount');

        // Monthly recurring costs marked as in_fee
        $recurringCosts = $project->additionalCosts()
            ->where('cost_type', 'monthly_recurring')
            ->where('fee_type', 'in_fee')
            ->where('is_active', true)
            ->where('start_date', '<=', Carbon::create($year, $month)->endOfMonth())
            ->where(function($q) use ($year, $month) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', Carbon::create($year, $month)->startOfMonth());
            })
            ->sum('amount');

        // TODO: Add time entries and milestone costs when implemented

        return $oneTimeCosts + $recurringCosts;
    }
}