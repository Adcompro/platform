<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectMonthlyFee;
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
    protected $description = 'Update project_monthly_fees from time_entries for recurring projects';

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

            // Bereken rollover
            $baseFee = $project->monthly_fee ?? 0;
            $availableFee = $baseFee + $previousRollover;
            $rolloverToNext = $availableFee - $totalValue;

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
                    'rollover_to_next' => $rolloverToNext,
                    'is_finalized' => false,
                ]
            );

            $status = $rolloverToNext >= 0 ? '🟢' : '🔴';
            $this->line(sprintf(
                "  %s Month %2d: %s - %.2fh = €%.2f (Variance: €%.2f)",
                $status,
                $month,
                $project->name,
                $totalHours,
                $totalValue,
                $rolloverToNext
            ));

            // Rollover voor volgende maand
            $previousRollover = $rolloverToNext;
        }
    }
}
