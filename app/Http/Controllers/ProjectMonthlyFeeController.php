<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMonthlyFee;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectMonthlyFeeController extends Controller
{
    /**
     * Display monthly fees for a project
     */
    public function index(Project $project, Request $request): View
    {
        $this->authorize('view', $project);

        $query = $project->monthlyFees()->with(['project']);

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $monthlyFees = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        // Get available years for filter
        $availableYears = $project->monthlyFees()
            ->distinct('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Calculate summary statistics
        $summary = [
            'total_allocated' => $project->monthlyFees()->sum('allocated_amount'),
            'total_used' => $project->monthlyFees()->sum('used_amount'),
            'total_rollover' => $project->monthlyFees()->sum('rollover_amount'),
            'current_month_fee' => $this->getCurrentMonthFee($project)
        ];

        return view('projects.monthly-fees.index', compact('project', 'monthlyFees', 'availableYears', 'summary'));
    }

    /**
     * Show the form for creating a new monthly fee
     */
    public function create(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.monthly-fees.create', compact('project'));
    }

    /**
     * Store a newly created monthly fee
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'allocated_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        // Check if fee already exists for this period
        $existing = $project->monthlyFees()
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Monthly fee already exists for this period.');
        }

        // Calculate rollover from previous month
        $rollover = $this->calculateRolloverAmount($project, $validated['year'], $validated['month']);

        $monthlyFee = $project->monthlyFees()->create([
            'year' => $validated['year'],
            'month' => $validated['month'],
            'allocated_amount' => $validated['allocated_amount'],
            'rollover_amount' => $rollover,
            'available_amount' => $validated['allocated_amount'] + $rollover,
            'used_amount' => 0,
            'notes' => $validated['notes'],
            'created_by' => Auth::id()
        ]);

        return redirect()->route('projects.monthly-fees.show', [$project, $monthlyFee])
            ->with('success', 'Monthly fee created successfully!');
    }

    /**
     * Display the specified monthly fee
     */
    public function show(Project $project, ProjectMonthlyFee $monthlyFee): View
    {
        $this->authorize('view', $project);

        // Get time entries for this period
        $timeEntries = TimeEntry::where('project_id', $project->id)
            ->whereYear('date', $monthlyFee->year)
            ->whereMonth('date', $monthlyFee->month)
            ->with(['user', 'milestone', 'task', 'subtask'])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate detailed usage
        $usage = [
            'total_hours' => $timeEntries->sum('hours'),
            'approved_hours' => $timeEntries->where('status', 'approved')->sum('hours'),
            'pending_hours' => $timeEntries->where('status', 'pending')->sum('hours'),
            'rejected_hours' => $timeEntries->where('status', 'rejected')->sum('hours'),
            'approved_amount' => $timeEntries->where('status', 'approved')->sum('total_amount'),
            'pending_amount' => $timeEntries->where('status', 'pending')->sum('total_amount'),
            'billable_amount' => min(
                $timeEntries->where('status', 'approved')->sum('total_amount'),
                $monthlyFee->available_amount
            )
        ];

        return view('projects.monthly-fees.show', compact('project', 'monthlyFee', 'timeEntries', 'usage'));
    }

    /**
     * Show the form for editing the specified monthly fee
     */
    public function edit(Project $project, ProjectMonthlyFee $monthlyFee): View
    {
        $this->authorize('update', $project);

        return view('projects.monthly-fees.edit', compact('project', 'monthlyFee'));
    }

    /**
     * Update the specified monthly fee
     */
    public function update(Request $request, Project $project, ProjectMonthlyFee $monthlyFee): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'allocated_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        // Recalculate available amount
        $newAvailableAmount = $validated['allocated_amount'] + $monthlyFee->rollover_amount;

        $monthlyFee->update([
            'allocated_amount' => $validated['allocated_amount'],
            'available_amount' => $newAvailableAmount,
            'notes' => $validated['notes']
        ]);

        // Recalculate usage to ensure it doesn't exceed new available amount
        $this->recalculateUsage($monthlyFee);

        return redirect()->route('projects.monthly-fees.show', [$project, $monthlyFee])
            ->with('success', 'Monthly fee updated successfully!');
    }

    /**
     * Remove the specified monthly fee
     */
    public function destroy(Project $project, ProjectMonthlyFee $monthlyFee): RedirectResponse
    {
        $this->authorize('update', $project);

        // Check if this period has time entries
        $hasTimeEntries = TimeEntry::where('project_id', $project->id)
            ->whereYear('date', $monthlyFee->year)
            ->whereMonth('date', $monthlyFee->month)
            ->exists();

        if ($hasTimeEntries) {
            return redirect()->back()
                ->with('error', 'Cannot delete monthly fee that has associated time entries.');
        }

        try {
            $monthlyFee->delete();
            
            return redirect()->route('projects.monthly-fees.index', $project)
                ->with('success', 'Monthly fee deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting monthly fee: ' . $e->getMessage());
        }
    }

    /**
     * Process monthly fee calculations
     */
    public function processMonth(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12'
        ]);

        DB::beginTransaction();
        
        try {
            $monthlyFee = $this->getOrCreateMonthlyFee($project, $validated['year'], $validated['month']);
            
            // Get approved time entries for this period
            $timeEntries = TimeEntry::where('project_id', $project->id)
                ->where('status', 'approved')
                ->whereYear('date', $validated['year'])
                ->whereMonth('date', $validated['month'])
                ->get();

            $totalAmount = $timeEntries->sum('total_amount');
            $billableAmount = min($totalAmount, $monthlyFee->available_amount);
            $usedAmount = $billableAmount;

            // Update monthly fee
            $monthlyFee->update([
                'used_amount' => $usedAmount,
                'processed_at' => now()
            ]);

            // Calculate rollover for next month
            $nextMonth = Carbon::createFromDate($validated['year'], $validated['month'], 1)->addMonth();
            $rolloverAmount = $monthlyFee->available_amount - $usedAmount;

            if ($rolloverAmount != 0) {
                $this->updateNextMonthRollover($project, $nextMonth->year, $nextMonth->month, $rolloverAmount);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Monthly fee processed successfully!',
                'monthly_fee' => $monthlyFee->fresh(),
                'billable_amount' => $billableAmount,
                'rollover_amount' => $rolloverAmount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error processing monthly fee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly fee summary for dashboard
     */
    public function getSummary(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $currentMonth = now();
        $lastMonth = now()->subMonth();

        $summary = [
            'current_month' => $this->getMonthSummary($project, $currentMonth->year, $currentMonth->month),
            'last_month' => $this->getMonthSummary($project, $lastMonth->year, $lastMonth->month),
            'year_to_date' => [
                'allocated' => $project->monthlyFees()
                    ->whereYear(DB::raw("STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d')"), $currentMonth->year)
                    ->sum('allocated_amount'),
                'used' => $project->monthlyFees()
                    ->whereYear(DB::raw("STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d')"), $currentMonth->year)
                    ->sum('used_amount')
            ]
        ];

        return response()->json($summary);
    }

    /**
     * Get budget utilization chart data
     */
    public function getBudgetChart(Project $project, Request $request): JsonResponse
    {
        $this->authorize('view', $project);

        $months = $request->input('months', 12);

        $monthlyFees = $project->monthlyFees()
            ->where('year', '>=', now()->subMonths($months)->year)
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $chartData = $monthlyFees->map(function($fee) {
            return [
                'period' => Carbon::createFromDate($fee->year, $fee->month, 1)->format('M Y'),
                'allocated' => $fee->allocated_amount,
                'used' => $fee->used_amount,
                'available' => $fee->available_amount,
                'utilization' => $fee->available_amount > 0 ? 
                    round(($fee->used_amount / $fee->available_amount) * 100, 1) : 0
            ];
        });

        return response()->json([
            'labels' => $chartData->pluck('period'),
            'datasets' => [
                [
                    'label' => 'Available Budget',
                    'data' => $chartData->pluck('available'),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ],
                [
                    'label' => 'Used Budget',
                    'data' => $chartData->pluck('used'),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)'
                ]
            ]
        ]);
    }

    /**
     * Calculate rollover amount from previous month
     */
    private function calculateRolloverAmount(Project $project, int $year, int $month): float
    {
        $previousMonth = Carbon::createFromDate($year, $month, 1)->subMonth();
        
        $previousFee = $project->monthlyFees()
            ->where('year', $previousMonth->year)
            ->where('month', $previousMonth->month)
            ->first();

        if (!$previousFee) {
            return 0;
        }

        return $previousFee->available_amount - $previousFee->used_amount;
    }

    /**
     * Get or create monthly fee for specified period
     */
    private function getOrCreateMonthlyFee(Project $project, int $year, int $month): ProjectMonthlyFee
    {
        $monthlyFee = $project->monthlyFees()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$monthlyFee) {
            $rollover = $this->calculateRolloverAmount($project, $year, $month);
            $allocated = $project->monthly_fee ?? 0;

            $monthlyFee = $project->monthlyFees()->create([
                'year' => $year,
                'month' => $month,
                'allocated_amount' => $allocated,
                'rollover_amount' => $rollover,
                'available_amount' => $allocated + $rollover,
                'used_amount' => 0,
                'created_by' => Auth::id()
            ]);
        }

        return $monthlyFee;
    }

    /**
     * Update rollover for next month
     */
    private function updateNextMonthRollover(Project $project, int $year, int $month, float $rolloverAmount): void
    {
        $nextMonthFee = $project->monthlyFees()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($nextMonthFee) {
            $nextMonthFee->update([
                'rollover_amount' => $rolloverAmount,
                'available_amount' => $nextMonthFee->allocated_amount + $rolloverAmount
            ]);
        }
    }

    /**
     * Get current month fee information
     */
    private function getCurrentMonthFee(Project $project)
    {
        $currentMonth = now();
        return $project->monthlyFees()
            ->where('year', $currentMonth->year)
            ->where('month', $currentMonth->month)
            ->first();
    }

    /**
     * Get month summary for dashboard
     */
    private function getMonthSummary(Project $project, int $year, int $month): array
    {
        $monthlyFee = $project->monthlyFees()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$monthlyFee) {
            return [
                'allocated' => 0,
                'used' => 0,
                'available' => 0,
                'utilization' => 0
            ];
        }

        return [
            'allocated' => $monthlyFee->allocated_amount,
            'used' => $monthlyFee->used_amount,
            'available' => $monthlyFee->available_amount,
            'utilization' => $monthlyFee->available_amount > 0 ? 
                round(($monthlyFee->used_amount / $monthlyFee->available_amount) * 100, 1) : 0
        ];
    }

    /**
     * Recalculate usage for monthly fee
     */
    private function recalculateUsage(ProjectMonthlyFee $monthlyFee): void
    {
        $timeEntries = TimeEntry::where('project_id', $monthlyFee->project_id)
            ->where('status', 'approved')
            ->whereYear('date', $monthlyFee->year)
            ->whereMonth('date', $monthlyFee->month)
            ->get();

        $totalAmount = $timeEntries->sum('total_amount');
        $usedAmount = min($totalAmount, $monthlyFee->available_amount);

        $monthlyFee->update(['used_amount' => $usedAmount]);
    }
}