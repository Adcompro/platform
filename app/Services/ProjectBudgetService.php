<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMonthlyFee;
use App\Models\ProjectAdditionalCost;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectBudgetService
{
    /**
     * Calculate complete budget for a project for a specific month
     */
    public function calculateMonthlyBudget(Project $project, int $year, int $month): ProjectMonthlyFee
    {
        try {
            DB::beginTransaction();

            Log::info('Starting budget calculation', [
                'project_id' => $project->id,
                'year' => $year,
                'month' => $month,
                'monthly_fee' => $project->monthly_fee
            ]);

            // Get or create monthly fee record
            $monthlyFee = ProjectMonthlyFee::getOrCreateForPeriod($project, $year, $month);
            
            // Set period dates
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            Log::debug('Date range for calculation', [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]);

            // Calculate time entry costs
            $timeData = $this->calculateTimeEntryCosts($project, $startDate, $endDate);
            
            Log::debug('Time data calculated', $timeData);
            
            // Calculate additional costs
            $additionalCostsData = $this->calculateAdditionalCosts($project, $startDate, $endDate);
            
            // Get previous month for rollover
            $previousMonth = $monthlyFee->getPreviousMonth();
            $rolloverAmount = 0;
            
            if ($previousMonth && $project->fee_rollover_enabled) {
                // Only rollover if previous month has remaining budget
                if ($previousMonth->budget_remaining > 0) {
                    $rolloverAmount = $previousMonth->budget_remaining;
                }
            }
            
            // Calculate totals
            $monthlyBudget = $project->monthly_fee ?? 0;
            $totalBudget = $monthlyBudget + $rolloverAmount;
            
            // Total costs (only in_fee costs count against budget)
            $budgetCosts = $timeData['in_fee_costs'] + 
                          $additionalCostsData['in_fee_onetime'] + 
                          $additionalCostsData['in_fee_recurring'];
            
            $additionalOutsideFee = $additionalCostsData['total_onetime'] - $additionalCostsData['in_fee_onetime'] +
                                    $additionalCostsData['total_recurring'] - $additionalCostsData['in_fee_recurring'];
            
            $totalCosts = $budgetCosts + $additionalOutsideFee;
            
            // Calculate budget status
            $budgetRemaining = max(0, $totalBudget - $budgetCosts);
            $budgetExceeded = max(0, $budgetCosts - $totalBudget);
            
            // Determine rollover to next month
            $rolloverToNext = 0;
            if ($project->fee_rollover_enabled && $budgetRemaining > 0) {
                $rolloverToNext = $budgetRemaining;
            }
            
            // Prepare calculation details
            $calculationDetails = [
                'time_entries' => [
                    'count' => $timeData['count'],
                    'hours' => $timeData['hours'],
                    'billable_hours' => $timeData['billable_hours'],
                    'total_costs' => $timeData['total_costs'],
                    'in_fee_costs' => $timeData['in_fee_costs'],
                    'additional_costs' => $timeData['additional_costs'],
                ],
                'additional_costs' => [
                    'onetime_count' => $additionalCostsData['onetime_count'],
                    'recurring_count' => $additionalCostsData['recurring_count'],
                    'total_onetime' => $additionalCostsData['total_onetime'],
                    'total_recurring' => $additionalCostsData['total_recurring'],
                    'in_fee_onetime' => $additionalCostsData['in_fee_onetime'],
                    'in_fee_recurring' => $additionalCostsData['in_fee_recurring'],
                ],
                'budget' => [
                    'monthly_budget' => $monthlyBudget,
                    'rollover_from_previous' => $rolloverAmount,
                    'total_budget' => $totalBudget,
                    'budget_costs' => $budgetCosts,
                    'total_costs' => $totalCosts,
                ],
                'calculated_at' => now()->toIso8601String(),
            ];
            
            // Update monthly fee record
            $monthlyFee->update([
                'base_monthly_fee' => $monthlyBudget,
                'rollover_from_previous' => $rolloverAmount,
                'total_available_fee' => $totalBudget,
                'hours_worked' => $timeData['hours'],
                'hours_value' => $timeData['total_costs'],
                'amount_invoiced_from_fee' => $budgetCosts,
                'additional_costs_in_fee' => $additionalCostsData['in_fee_onetime'] + $additionalCostsData['in_fee_recurring'],
                'additional_costs_outside_fee' => $additionalOutsideFee,
                'total_invoiced' => $totalCosts,
                'rollover_to_next' => $rolloverToNext,
                'notes' => json_encode($calculationDetails),
            ]);
            
            DB::commit();
            
            Log::info('Monthly budget calculated', [
                'project_id' => $project->id,
                'year' => $year,
                'month' => $month,
                'total_budget' => $totalBudget,
                'total_costs' => $totalCosts,
            ]);
            
            return $monthlyFee->fresh();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error calculating monthly budget', [
                'project_id' => $project->id,
                'year' => $year,
                'month' => $month,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Calculate time entry costs for a period
     */
    private function calculateTimeEntryCosts(Project $project, Carbon $startDate, Carbon $endDate): array
    {
        $timeEntries = TimeEntry::where('project_id', $project->id)
            ->where('status', 'approved')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();
        
        $totalHours = 0;
        $billableHours = 0;
        $totalCosts = 0;
        $inFeeCosts = 0;
        $additionalCosts = 0;
        
        foreach ($timeEntries as $entry) {
            // Convert hours and minutes to decimal hours
            $entryHours = $entry->hours + ($entry->minutes / 60);
            $totalHours += $entryHours;
            
            if ($entry->is_billable === 'billable') {
                $billableHours += $entryHours;
                
                // Get hourly rate - check if it's set on the entry, otherwise use project default
                $hourlyRate = $entry->hourly_rate_used ?? $project->default_hourly_rate ?? 75;
                $cost = $entryHours * $hourlyRate;
                $totalCosts += $cost;
                
                // Determine if this counts as in_fee or additional
                // For now, all billable time is considered in_fee
                // This can be customized based on business rules
                $inFeeCosts += $cost;
                
                Log::debug('Time entry cost calculated', [
                    'entry_id' => $entry->id,
                    'hours' => $entryHours,
                    'rate' => $hourlyRate,
                    'cost' => $cost
                ]);
            }
        }
        
        return [
            'count' => $timeEntries->count(),
            'hours' => round($totalHours, 2),
            'billable_hours' => round($billableHours, 2),
            'total_costs' => round($totalCosts, 2),
            'in_fee_costs' => round($inFeeCosts, 2),
            'additional_costs' => round($additionalCosts, 2),
        ];
    }
    
    /**
     * Calculate additional costs for a period
     */
    private function calculateAdditionalCosts(Project $project, Carbon $startDate, Carbon $endDate): array
    {
        // One-time costs in this period
        $oneTimeCosts = ProjectAdditionalCost::where('project_id', $project->id)
            ->where('cost_type', 'one_time')
            ->where('is_active', true)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();
        
        // Recurring costs active in this period
        $recurringCosts = ProjectAdditionalCost::where('project_id', $project->id)
            ->where('cost_type', 'monthly_recurring')
            ->where('is_active', true)
            ->where('start_date', '<=', $endDate)
            ->where(function($q) use ($startDate) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $startDate);
            })
            ->get();
        
        // Calculate totals
        $totalOnetime = 0;
        $inFeeOnetime = 0;
        
        foreach ($oneTimeCosts as $cost) {
            $totalOnetime += $cost->amount;
            if ($cost->fee_type === 'in_fee') {
                $inFeeOnetime += $cost->amount;
            }
        }
        
        $totalRecurring = 0;
        $inFeeRecurring = 0;
        
        foreach ($recurringCosts as $cost) {
            $totalRecurring += $cost->amount;
            if ($cost->fee_type === 'in_fee') {
                $inFeeRecurring += $cost->amount;
            }
        }
        
        return [
            'onetime_count' => $oneTimeCosts->count(),
            'recurring_count' => $recurringCosts->count(),
            'total_onetime' => round($totalOnetime, 2),
            'total_recurring' => round($totalRecurring, 2),
            'in_fee_onetime' => round($inFeeOnetime, 2),
            'in_fee_recurring' => round($inFeeRecurring, 2),
        ];
    }
    
    /**
     * Get budget summary for current month
     */
    public function getCurrentMonthBudget(Project $project): array
    {
        $now = Carbon::now();
        $monthlyFee = ProjectMonthlyFee::getOrCreateForPeriod($project, $now->year, $now->month);
        
        // If not finalized, recalculate
        if (!$monthlyFee->is_finalized) {
            $monthlyFee = $this->calculateMonthlyBudget($project, $now->year, $now->month);
        }
        
        return [
            'monthly_fee' => $monthlyFee,
            'budget_status' => $this->getBudgetStatus($monthlyFee),
            'quick_stats' => [
                'total_budget' => $monthlyFee->total_available_fee,
                'used' => $monthlyFee->amount_invoiced_from_fee,
                'remaining' => $monthlyFee->budget_remaining,
                'percentage' => $monthlyFee->budget_percentage_used,
                'is_over_budget' => $monthlyFee->is_over_budget,
            ],
        ];
    }
    
    /**
     * Get budget status color and message
     */
    private function getBudgetStatus(ProjectMonthlyFee $monthlyFee): array
    {
        if ($monthlyFee->is_over_budget) {
            return [
                'color' => 'red',
                'message' => 'Over budget by €' . number_format($monthlyFee->budget_exceeded, 2),
                'icon' => 'exclamation-triangle',
            ];
        } elseif ($monthlyFee->budget_percentage_used > 80) {
            return [
                'color' => 'yellow',
                'message' => 'Warning: ' . $monthlyFee->budget_percentage_used . '% of budget used',
                'icon' => 'exclamation',
            ];
        } else {
            return [
                'color' => 'green',
                'message' => 'Within budget (' . $monthlyFee->budget_percentage_used . '% used)',
                'icon' => 'check-circle',
            ];
        }
    }
    
    /**
     * Get budget history for a project
     */
    public function getBudgetHistory(Project $project, int $months = 12): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths($months - 1)->startOfMonth();
        
        $history = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $monthlyFee = ProjectMonthlyFee::where('project_id', $project->id)
                ->forPeriod($current->year, $current->month)
                ->first();
            
            if (!$monthlyFee) {
                // Create placeholder
                $monthlyFee = ProjectMonthlyFee::getOrCreateForPeriod($project, $current->year, $current->month);
            }
            
            $history[] = [
                'year' => $current->year,
                'month' => $current->month,
                'label' => $current->format('M Y'),
                'data' => $monthlyFee,
            ];
            
            $current->addMonth();
        }
        
        return $history;
    }
    
    /**
     * Recalculate all months for a project (useful after changes)
     */
    public function recalculateAllMonths(Project $project): void
    {
        $months = ProjectMonthlyFee::where('project_id', $project->id)
            ->notFinalized()
            ->get();
        
        foreach ($months as $month) {
            $this->calculateMonthlyBudget($project, $month->year, $month->month);
        }
    }
}