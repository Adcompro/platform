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

        // Dynamische validatie regels op basis van calculation_type
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'calculation_type' => 'required|in:fixed_amount,hourly_rate,quantity_based',
            'fee_type' => 'required|in:in_fee,additional',
            'cost_type' => 'required|in:one_time,monthly_recurring',
            'category' => 'nullable|in:hosting,software,licenses,services,other',
            'vendor' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'auto_invoice' => 'boolean',
            'notes' => 'nullable|string',
        ];

        // Voeg calculation-specifieke validatie toe
        $calculationType = $request->input('calculation_type', 'fixed_amount');

        if ($calculationType === 'fixed_amount') {
            $rules['amount'] = 'required|numeric|min:0';
        } elseif ($calculationType === 'hourly_rate') {
            $rules['hours'] = 'required|numeric|min:0';
            $rules['hourly_rate'] = 'required|numeric|min:0';
        } elseif ($calculationType === 'quantity_based') {
            $rules['quantity'] = 'required|numeric|min:0';
            $rules['unit'] = 'nullable|string|max:50';
            $rules['unit_price'] = 'required|numeric|min:0';
        }

        // Voeg date validatie toe voor monthly_recurring
        if ($request->input('cost_type') === 'monthly_recurring') {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'nullable|date|after:start_date';
        } else {
            $rules['start_date'] = 'required|date';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $costData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'calculation_type' => $validated['calculation_type'],
                'fee_type' => $validated['fee_type'],
                'cost_type' => $validated['cost_type'],
                'category' => $validated['category'] ?? null,
                'vendor' => $validated['vendor'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'auto_invoice' => $request->boolean('auto_invoice'),
                'notes' => $validated['notes'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
            ];

            // Voeg calculation-specifieke velden toe
            if ($calculationType === 'fixed_amount') {
                $costData['amount'] = $validated['amount'];
                $costData['hourly_rate'] = null;
                $costData['hours'] = null;
                $costData['quantity'] = null;
                $costData['unit'] = null;
                $costData['unit_price'] = null;
            } elseif ($calculationType === 'hourly_rate') {
                $costData['hours'] = $validated['hours'];
                $costData['hourly_rate'] = $validated['hourly_rate'];
                $costData['amount'] = $validated['hours'] * $validated['hourly_rate']; // Bereken amount
                $costData['quantity'] = null;
                $costData['unit'] = null;
                $costData['unit_price'] = null;
            } elseif ($calculationType === 'quantity_based') {
                $costData['quantity'] = $validated['quantity'];
                $costData['unit'] = $validated['unit'] ?? null;
                $costData['unit_price'] = $validated['unit_price'];
                $costData['amount'] = $validated['quantity'] * $validated['unit_price']; // Bereken amount
                $costData['hourly_rate'] = null;
                $costData['hours'] = null;
            }

            // Voeg date velden toe
            if ($validated['cost_type'] === 'monthly_recurring') {
                $costData['start_date'] = $validated['start_date'];
                $costData['end_date'] = $validated['end_date'] ?? null;
            } else {
                $costData['start_date'] = $validated['start_date'];
                $costData['end_date'] = null;
            }

            $cost = $project->additionalCosts()->create($costData);

            DB::commit();
            Log::info('Additional cost created', ['cost_id' => $cost->id, 'project_id' => $project->id]);

            // Als JSON request (van modal), return JSON success
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cost added successfully.',
                    'cost' => $cost
                ]);
            }

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Additional cost added successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating additional cost', ['error' => $e->getMessage()]);

            // Als JSON request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific additional cost (JSON only, for modals)
     */
    public function show(ProjectAdditionalCost $projectAdditionalCost)
    {
        // Authorization check
        $project = $projectAdditionalCost->project;

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) &&
            !$project->users()->where('user_id', Auth::id())->exists()) {

            if (request()->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Access denied.');
        }

        // Calculate the amount for display
        $amount = $projectAdditionalCost->calculateAmount();

        // Prepare cost data
        $costData = [
            'id' => $projectAdditionalCost->id,
            'name' => $projectAdditionalCost->name,
            'description' => $projectAdditionalCost->description,
            'cost_type' => $projectAdditionalCost->cost_type,
            'fee_type' => $projectAdditionalCost->fee_type,
            'calculation_type' => $projectAdditionalCost->calculation_type,
            'amount' => $projectAdditionalCost->amount,
            'hours' => $projectAdditionalCost->hours,
            'hourly_rate' => $projectAdditionalCost->hourly_rate,
            'start_date' => $projectAdditionalCost->start_date ? $projectAdditionalCost->start_date->format('Y-m-d') : null,
            'end_date' => $projectAdditionalCost->end_date ? $projectAdditionalCost->end_date->format('Y-m-d') : null,
            'notes' => $projectAdditionalCost->notes,
            'calculateAmount' => $amount,
        ];

        return response()->json([
            'success' => true,
            'cost' => $costData
        ]);
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
    public function edit(Request $request, Project $project, ProjectAdditionalCost $additionalCost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only managers can edit costs.');
        }

        if (!$additionalCost->canBeEdited()) {
            // Als JSON request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This cost cannot be edited because it is already invoiced.'
                ], 403);
            }

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('error', 'This cost cannot be edited because it is already invoiced.');
        }

        // Als JSON request (voor modal), return JSON data
        if ($request->expectsJson()) {
            return response()->json([
                'id' => $additionalCost->id,
                'name' => $additionalCost->name,
                'description' => $additionalCost->description,
                'category' => $additionalCost->category,
                'cost_type' => $additionalCost->cost_type,
                'fee_type' => $additionalCost->fee_type,
                'calculation_type' => $additionalCost->calculation_type,
                'amount' => $additionalCost->amount,
                'hourly_rate' => $additionalCost->hourly_rate,
                'hours' => $additionalCost->hours,
                'quantity' => $additionalCost->quantity,
                'unit' => $additionalCost->unit,
                'unit_price' => $additionalCost->unit_price,
                'start_date' => $additionalCost->start_date ? $additionalCost->start_date->format('Y-m-d') : null,
                'end_date' => $additionalCost->end_date ? $additionalCost->end_date->format('Y-m-d') : null,
                'vendor' => $additionalCost->vendor,
                'reference' => $additionalCost->reference,
                'auto_invoice' => $additionalCost->auto_invoice,
                'notes' => $additionalCost->notes,
            ]);
        }

        // Anders, return normale edit view
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
            // Als JSON request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This cost cannot be edited because it is already invoiced.'
                ], 403);
            }

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('error', 'This cost cannot be edited because it is already invoiced.');
        }

        // Dynamische validatie regels op basis van calculation_type
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'calculation_type' => 'required|in:fixed_amount,hourly_rate,quantity_based',
            'fee_type' => 'required|in:in_fee,additional',
            'cost_type' => 'required|in:one_time,monthly_recurring',
            'category' => 'nullable|in:hosting,software,licenses,services,other',
            'vendor' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'auto_invoice' => 'boolean',
            'notes' => 'nullable|string',
        ];

        // Voeg calculation-specifieke validatie toe
        $calculationType = $request->input('calculation_type', 'fixed_amount');

        if ($calculationType === 'fixed_amount') {
            $rules['amount'] = 'required|numeric|min:0';
        } elseif ($calculationType === 'hourly_rate') {
            $rules['hours'] = 'required|numeric|min:0';
            $rules['hourly_rate'] = 'required|numeric|min:0';
        } elseif ($calculationType === 'quantity_based') {
            $rules['quantity'] = 'required|numeric|min:0';
            $rules['unit'] = 'nullable|string|max:50';
            $rules['unit_price'] = 'required|numeric|min:0';
        }

        // Voeg date validatie toe voor monthly_recurring
        if ($request->input('cost_type') === 'monthly_recurring') {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'nullable|date|after:start_date';
        }

        $validated = $request->validate($rules);

        try {
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'calculation_type' => $validated['calculation_type'],
                'fee_type' => $validated['fee_type'],
                'cost_type' => $validated['cost_type'],
                'category' => $validated['category'] ?? null,
                'vendor' => $validated['vendor'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'auto_invoice' => $request->boolean('auto_invoice'),
                'notes' => $validated['notes'] ?? null,
            ];

            // Voeg calculation-specifieke velden toe
            if ($calculationType === 'fixed_amount') {
                $updateData['amount'] = $validated['amount'];
                $updateData['hourly_rate'] = null;
                $updateData['hours'] = null;
                $updateData['quantity'] = null;
                $updateData['unit'] = null;
                $updateData['unit_price'] = null;
            } elseif ($calculationType === 'hourly_rate') {
                $updateData['hours'] = $validated['hours'];
                $updateData['hourly_rate'] = $validated['hourly_rate'];
                $updateData['amount'] = $validated['hours'] * $validated['hourly_rate']; // Bereken amount
                $updateData['quantity'] = null;
                $updateData['unit'] = null;
                $updateData['unit_price'] = null;
            } elseif ($calculationType === 'quantity_based') {
                $updateData['quantity'] = $validated['quantity'];
                $updateData['unit'] = $validated['unit'] ?? null;
                $updateData['unit_price'] = $validated['unit_price'];
                $updateData['amount'] = $validated['quantity'] * $validated['unit_price']; // Bereken amount
                $updateData['hourly_rate'] = null;
                $updateData['hours'] = null;
            }

            // Voeg date velden toe als monthly_recurring
            if ($validated['cost_type'] === 'monthly_recurring') {
                $updateData['start_date'] = $validated['start_date'];
                $updateData['end_date'] = $validated['end_date'] ?? null;
            }

            $additionalCost->update($updateData);

            Log::info('Additional cost updated', ['cost_id' => $additionalCost->id]);

            // Als JSON request (van modal), return JSON success
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cost updated successfully.',
                    'cost' => $additionalCost->fresh()
                ]);
            }

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Additional cost updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating additional cost', ['error' => $e->getMessage()]);

            // Als JSON request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete one-time additional cost
     */
    public function destroy(Request $request, Project $project, ProjectAdditionalCost $additionalCost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            // Als JSON request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied. Only administrators can delete costs.'
                ], 403);
            }
            abort(403, 'Access denied. Only administrators can delete costs.');
        }

        if (!$additionalCost->canBeDeleted()) {
            // Als JSON request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'This cost cannot be deleted because it is invoiced.'
                ], 403);
            }

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('error', 'This cost cannot be deleted because it is invoiced.');
        }

        try {
            $additionalCost->delete();
            Log::info('Additional cost deleted', ['cost_id' => $additionalCost->id]);

            // Als JSON request (van modal), return JSON success
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cost deleted successfully.'
                ]);
            }

            return redirect()->route('projects.additional-costs.index', $project)
                ->with('success', 'Additional cost deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting additional cost', ['error' => $e->getMessage()]);

            // Als JSON request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Update additional cost without project parameter (voor modals)
     */
    public function updateDirect(Request $request, ProjectAdditionalCost $projectAdditionalCost)
    {
        // Authorization check
        $project = $projectAdditionalCost->project;

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Access denied. Only managers can edit costs.'], 403);
            }
            abort(403, 'Access denied. Only managers can edit costs.');
        }

        if (!$projectAdditionalCost->canBeEdited()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This cost cannot be edited because it is already invoiced.'], 403);
            }
            return back()->with('error', 'This cost cannot be edited because it is already invoiced.');
        }

        // Dynamische validatie regels op basis van calculation_type
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'calculation_type' => 'required|in:fixed_amount,hourly_rate',
            'fee_type' => 'required|in:in_fee,additional',
            'cost_type' => 'required|in:one_time,monthly_recurring',
            'notes' => 'nullable|string',
        ];

        // Voeg calculation-specifieke validatie toe
        $calculationType = $request->input('calculation_type', 'fixed_amount');

        if ($calculationType === 'fixed_amount') {
            $rules['amount'] = 'required|numeric|min:0';
        } elseif ($calculationType === 'hourly_rate') {
            $rules['hours'] = 'required|numeric|min:0';
            $rules['hourly_rate'] = 'required|numeric|min:0';
        }

        // Voeg date validatie toe voor monthly_recurring
        if ($request->input('cost_type') === 'monthly_recurring') {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'nullable|date|after:start_date';
        }

        $validated = $request->validate($rules);

        try {
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'calculation_type' => $validated['calculation_type'],
                'fee_type' => $validated['fee_type'],
                'cost_type' => $validated['cost_type'],
                'auto_invoice' => 1, // Altijd auto invoice enabled
                'notes' => $validated['notes'] ?? null,
            ];

            // Voeg calculation-specifieke velden toe
            if ($calculationType === 'fixed_amount') {
                $updateData['amount'] = $validated['amount'];
                $updateData['hourly_rate'] = null;
                $updateData['hours'] = null;
            } elseif ($calculationType === 'hourly_rate') {
                $updateData['hours'] = $validated['hours'];
                $updateData['hourly_rate'] = $validated['hourly_rate'];
                $updateData['amount'] = $validated['hours'] * $validated['hourly_rate'];
            }

            // Voeg date velden toe als monthly_recurring
            if ($validated['cost_type'] === 'monthly_recurring') {
                $updateData['start_date'] = $validated['start_date'];
                $updateData['end_date'] = $validated['end_date'] ?? null;
            }

            $projectAdditionalCost->update($updateData);

            Log::info('Additional cost updated via modal', ['cost_id' => $projectAdditionalCost->id]);

            return response()->json([
                'success' => true,
                'message' => 'Cost updated successfully.',
                'cost' => $projectAdditionalCost->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating additional cost via modal', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete additional cost without project parameter (voor modals)
     */
    public function destroyDirect(Request $request, ProjectAdditionalCost $projectAdditionalCost)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Access denied. Only administrators can delete costs.'], 403);
        }

        if (!$projectAdditionalCost->canBeDeleted()) {
            return response()->json(['error' => 'This cost cannot be deleted because it is invoiced.'], 403);
        }

        try {
            $projectAdditionalCost->delete();
            Log::info('Additional cost deleted via modal', ['cost_id' => $projectAdditionalCost->id]);

            return response()->json([
                'success' => true,
                'message' => 'Cost deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting additional cost via modal', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
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