<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectMonthlyFee;
use App\Models\ProjectAdditionalCost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateMonthlyFees extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'recurring:update-monthly-fees
                            {--year= : The year to process (defaults to current year)}
                            {--project= : Process only specific project ID}
                            {--series= : Process only specific recurring series}';

    /**
     * The console command description.
     */
    protected $description = 'Update project_monthly_fees from time_entries for recurring and individual projects';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?? Carbon::now()->year;
        $projectId = $this->option('project');
        $seriesId = $this->option('series');

        $this->info("🔄 Updating monthly fees for year {$year}...");

        // Haal recurring projects op
        $query = Project::whereNotNull('recurring_series_id');

        if ($projectId) {
            $query->where('id', $projectId);
        }

        if ($seriesId) {
            $query->where('recurring_series_id', $seriesId);
        }

        // BELANGRIJK: Filter alleen projecten uit het opgegeven jaar
        // Om te voorkomen dat oude projecten uit 2021-2024 ook monthly_fees krijgen
        $query->whereYear('start_date', $year);

        $recurringProjects = $query->get();

        if ($recurringProjects->isEmpty()) {
            $this->warn('⚠️  No recurring projects found');
            return 1;
        }

        $this->info("📊 Processing {$recurringProjects->count()} recurring projects...");

        // Groepeer per series voor rollover berekeningen
        $seriesGroups = $recurringProjects->groupBy('recurring_series_id');

        foreach ($seriesGroups as $seriesId => $projects) {
            $this->info("\n📁 Processing series: {$seriesId}");
            $this->processSeries($projects, $year);
        }

        // NIEUW (08-11-2025): Verwerk ook individuele projecten (ZONDER recurring_series_id)
        $individualQuery = Project::whereNull('recurring_series_id')
            ->whereYear('start_date', $year)
            ->where('status', '!=', 'cancelled');

        if ($projectId) {
            $individualQuery->where('id', $projectId);
        }

        $individualProjects = $individualQuery->get();

        if ($individualProjects->isNotEmpty()) {
            $this->info("\n📄 Processing {$individualProjects->count()} individual projects...");
            foreach ($individualProjects as $project) {
                $this->processIndividualProject($project, $year);
            }
        }

        $this->info("\n✅ Monthly fees update completed!");
        return 0;
    }

    /**
     * Process alle projecten in een recurring series
     */
    private function processSeries($projects, $year)
    {
        $projectIds = $projects->pluck('id')->toArray();

        // Haal ALL time entries op voor deze series
        $timeEntries = DB::table('time_entries')
            ->whereIn('project_id', $projectIds)
            ->whereYear('entry_date', $year)
            ->where('status', 'approved')
            ->select(
                DB::raw('MONTH(entry_date) as month'),
                DB::raw('SUM(hours + minutes/60) as total_hours'),
                DB::raw('SUM((hours + minutes/60) * hourly_rate_used) as total_value'),
                'project_id'
            )
            ->groupBy('project_id', DB::raw('MONTH(entry_date)'))
            ->get()
            ->groupBy('month');

        if ($timeEntries->isEmpty()) {
            $this->warn("  ⚠️  No time entries found for this series");
            return;
        }

        // Index projects per maand op basis van start_date
        $projectsByMonth = [];
        foreach ($projects as $project) {
            if ($project->start_date) {
                $monthNum = Carbon::parse($project->start_date)->month;
                $projectsByMonth[$monthNum] = $project;
            }
        }

        // Bereken monthly fees per maand met rollover chain
        $previousRollover = 0;

        for ($month = 1; $month <= 12; $month++) {
            $project = $projectsByMonth[$month] ?? null;

            if (!$project) {
                continue; // Geen project voor deze maand in de serie
            }

            // Haal time entries voor deze maand (kunnen van meerdere projecten zijn)
            $monthEntries = $timeEntries->get($month, collect());

            $totalHours = $monthEntries->sum('total_hours');
            $totalValue = $monthEntries->sum('total_value');

            // Bereken additional costs voor deze maand
            $additionalCostsInFee = 0;
            $additionalCostsOutsideFee = 0;

            // Haal alle additional costs op voor dit project
            $additionalCosts = ProjectAdditionalCost::where('project_id', $project->id)
                ->where('cost_type', 'monthly_recurring') // Alleen recurring costs
                ->where('is_active', true)
                ->get();

            foreach ($additionalCosts as $cost) {
                // Check of deze cost actief is in deze maand
                if ($cost->isActiveInMonth($year, $month)) {
                    $costAmount = $cost->getAmountForMonth($year, $month);

                    if ($cost->fee_type === 'in_fee') {
                        $additionalCostsInFee += $costAmount;
                    } else {
                        $additionalCostsOutsideFee += $costAmount;
                    }
                }
            }

            // Bereken rollover
            // BELANGRIJK: additional_costs_in_fee tellen mee in de budget berekening
            $baseFee = $project->monthly_fee ?? 0;
            $totalCostsInFee = $totalValue + $additionalCostsInFee;
            $availableFee = $baseFee + $previousRollover;
            $rolloverToNext = $availableFee - $totalCostsInFee;

            // Update of insert monthly_fee record
            $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
            $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

            ProjectMonthlyFee::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'year' => $year,
                    'month' => $month
                ],
                [
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'base_monthly_fee' => $baseFee,
                    'rollover_from_previous' => $previousRollover,
                    'total_available_fee' => $availableFee,
                    'hours_worked' => round($totalHours, 2),
                    'hours_value' => round($totalValue, 2),
                    'additional_costs_in_fee' => round($additionalCostsInFee, 2),
                    'additional_costs_outside_fee' => round($additionalCostsOutsideFee, 2),
                    'rollover_to_next' => $rolloverToNext,
                    'is_finalized' => false,
                ]
            );

            $status = $rolloverToNext >= 0 ? '🟢' : '🔴';
            $costsDisplay = '';
            if ($additionalCostsInFee > 0 || $additionalCostsOutsideFee > 0) {
                $costsDisplay = sprintf(
                    " + Costs: €%.2f (in) / €%.2f (add)",
                    $additionalCostsInFee,
                    $additionalCostsOutsideFee
                );
            }
            $this->line(sprintf(
                "  %s Month %2d: %s - %.2fh = €%.2f%s (Variance: €%.2f)",
                $status,
                $month,
                $project->name,
                $totalHours,
                $totalValue,
                $costsDisplay,
                $rolloverToNext
            ));

            // Rollover voor volgende maand
            $previousRollover = $rolloverToNext;
        }
    }

    /**
     * NIEUW (08-11-2025): Verwerk een individueel project (zonder recurring series)
     * Individuele projecten hebben GEEN rollover tussen maanden
     */
    private function processIndividualProject($project, $year)
    {
        $this->info("\n  📄 {$project->name}");

        // Bepaal de actieve maanden voor dit project (van start tot end date binnen het jaar)
        $projectStart = $project->start_date ? Carbon::parse($project->start_date) : null;
        $projectEnd = $project->end_date ? Carbon::parse($project->end_date) : null;

        if (!$projectStart) {
            $this->warn("    ⚠️  No start_date - skipping");
            return;
        }

        // Bepaal eerste en laatste maand binnen het opgegeven jaar
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);

        $startMonth = max($projectStart->month, $projectStart->year == $year ? $projectStart->month : 1);
        $endMonth = $projectEnd ? min($projectEnd->month, $projectEnd->year == $year ? $projectEnd->month : 12) : 12;

        // Als project niet in dit jaar valt, skip
        if ($projectStart->year > $year || ($projectEnd && $projectEnd->year < $year)) {
            $this->warn("    ⚠️  Project not active in {$year} - skipping");
            return;
        }

        // Haal time entries op voor dit project
        $timeEntries = DB::table('time_entries')
            ->where('project_id', $project->id)
            ->whereYear('entry_date', $year)
            ->where('status', 'approved')
            ->where('is_billable', 'billable') // KRITIEK: Alleen billable uren
            ->select(
                DB::raw('MONTH(entry_date) as month'),
                DB::raw('SUM(hours + minutes/60) as total_hours'),
                DB::raw('SUM((hours + minutes/60) * hourly_rate_used) as total_value')
            )
            ->groupBy(DB::raw('MONTH(entry_date)'))
            ->get()
            ->keyBy('month');

        // Process elke maand waarin het project actief is
        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $monthEntry = $timeEntries->get($month);
            $totalHours = $monthEntry ? $monthEntry->total_hours : 0;
            $totalValue = $monthEntry ? $monthEntry->total_value : 0;

            // Bereken additional costs voor deze maand
            $additionalCostsInFee = 0;
            $additionalCostsOutsideFee = 0;

            $additionalCosts = ProjectAdditionalCost::where('project_id', $project->id)
                ->where('cost_type', 'monthly_recurring')
                ->where('is_active', true)
                ->get();

            foreach ($additionalCosts as $cost) {
                if ($cost->isActiveInMonth($year, $month)) {
                    $costAmount = $cost->getAmountForMonth($year, $month);

                    if ($cost->fee_type === 'in_fee') {
                        $additionalCostsInFee += $costAmount;
                    } else {
                        $additionalCostsOutsideFee += $costAmount;
                    }
                }
            }

            // BELANGRIJK: Individuele projecten hebben GEEN rollover
            // Elke maand staat op zichzelf
            $baseFee = $project->monthly_fee ?? 0;
            $totalCostsInFee = $totalValue + $additionalCostsInFee;
            $variance = $baseFee - $totalCostsInFee; // Geen rollover!

            // Update of insert monthly_fee record
            $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
            $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

            ProjectMonthlyFee::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'year' => $year,
                    'month' => $month
                ],
                [
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'base_monthly_fee' => $baseFee,
                    'rollover_from_previous' => 0, // GEEN rollover voor individuele projecten
                    'total_available_fee' => $baseFee,
                    'hours_worked' => round($totalHours, 2),
                    'hours_value' => round($totalValue, 2),
                    'additional_costs_in_fee' => round($additionalCostsInFee, 2),
                    'additional_costs_outside_fee' => round($additionalCostsOutsideFee, 2),
                    'rollover_to_next' => 0, // GEEN rollover voor individuele projecten
                    'is_finalized' => false,
                ]
            );

            // Alleen output tonen als er data is
            if ($totalHours > 0 || $additionalCostsInFee > 0 || $additionalCostsOutsideFee > 0) {
                $status = $variance >= 0 ? '🟢' : '🔴';
                $costsDisplay = '';
                if ($additionalCostsInFee > 0 || $additionalCostsOutsideFee > 0) {
                    $costsDisplay = sprintf(
                        " + Costs: €%.2f (in) / €%.2f (add)",
                        $additionalCostsInFee,
                        $additionalCostsOutsideFee
                    );
                }
                $this->line(sprintf(
                    "    %s Month %2d: %.2fh = €%.2f%s (Variance: €%.2f)",
                    $status,
                    $month,
                    $totalHours,
                    $totalValue,
                    $costsDisplay,
                    $variance
                ));
            }
        }
    }
}
