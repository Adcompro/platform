<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMonthlyFee;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecurringDashboardController extends Controller
{
    /**
     * Toon recurring projects dashboard met maand-per-maand overzicht
     */
    public function index(Request $request): View
    {
        // Authorization check - alleen project_manager en hoger
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only project managers and administrators can view this dashboard.');
        }

        $currentYear = $request->input('year', Carbon::now()->year);
        $selectedCompanyId = $request->input('company_id', null);

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = [
                'number' => $m,
                'name' => Carbon::create($currentYear, $m, 1)->format('M'),
                'full_name' => Carbon::create($currentYear, $m, 1)->format('F')
            ];
        }

        // Haal alle beschikbare companies op voor de filter dropdown
        $companiesQuery = \App\Models\Company::where('is_active', true)->orderBy('name');

        // Company isolation voor non-super_admin/admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            $companiesQuery->where('id', Auth::user()->company_id);
        }

        $allCompanies = $companiesQuery->get();

        // Haal alle projecten op die deel uitmaken van een recurring series
        $recurringQuery = Project::with(['customer', 'companyRelation'])
            ->whereNotNull('recurring_series_id')
            ->whereYear('start_date', $currentYear);

        // Company isolation voor non-super_admin/admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            $recurringQuery->where(function($q) {
                // Check direct company_id OF via pivot tabel
                $q->where('company_id', Auth::user()->company_id)
                  ->orWhereHas('companies', function($subQ) {
                      $subQ->where('companies.id', Auth::user()->company_id);
                  });
            });
        }

        // Optionele company filter (geselecteerd via dropdown)
        if ($selectedCompanyId) {
            $recurringQuery->where(function($q) use ($selectedCompanyId) {
                $q->where('company_id', $selectedCompanyId)
                  ->orWhereHas('companies', function($subQ) use ($selectedCompanyId) {
                      $subQ->where('companies.id', $selectedCompanyId);
                  });
            });
        }

        $allRecurringProjects = $recurringQuery->orderBy('recurring_series_id')->orderBy('start_date')->get();

        // NIEUW (08-11-2025): Haal ook losse projecten op ZONDER recurring_series_id
        $individualQuery = Project::with(['customer', 'companyRelation'])
            ->whereNull('recurring_series_id')
            ->whereYear('start_date', $currentYear)
            ->where('status', '!=', 'cancelled'); // Skip cancelled projecten

        // Company isolation voor non-super_admin/admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            $individualQuery->where(function($q) {
                $q->where('company_id', Auth::user()->company_id)
                  ->orWhereHas('companies', function($subQ) {
                      $subQ->where('companies.id', Auth::user()->company_id);
                  });
            });
        }

        // Optionele company filter (geselecteerd via dropdown)
        if ($selectedCompanyId) {
            $individualQuery->where(function($q) use ($selectedCompanyId) {
                $q->where('company_id', $selectedCompanyId)
                  ->orWhereHas('companies', function($subQ) use ($selectedCompanyId) {
                      $subQ->where('companies.id', $selectedCompanyId);
                  });
            });
        }

        $allIndividualProjects = $individualQuery->orderBy('start_date')->get();

        // Groepeer projecten per recurring_series_id
        $seriesGroups = $allRecurringProjects->groupBy('recurring_series_id');

        // Haal alle monthly fee data op voor alle projecten (recurring + individual)
        $allProjectIds = $allRecurringProjects->pluck('id')->merge($allIndividualProjects->pluck('id'));
        $allMonthlyFees = ProjectMonthlyFee::whereIn('project_id', $allProjectIds)
            ->where('year', $currentYear)
            ->get();

        // Bereken statistieken per series
        $projectsData = [];
        $grandTotals = [
            'budget' => 0,
            'spent' => 0,
            'variance' => 0,
            'hours' => 0
        ];

        foreach ($seriesGroups as $seriesId => $projects) {
            // Extraheer series base naam (eerste project naam zonder maand/jaar)
            $firstProject = $projects->first();
            $seriesBaseName = $this->extractSeriesBaseName($firstProject->name);

            // Index projecten per maand (op basis van start_date)
            $projectsByMonth = [];
            foreach ($projects as $project) {
                if ($project->start_date) {
                    $monthNum = Carbon::parse($project->start_date)->month;
                    $projectsByMonth[$monthNum] = $project;
                }
            }

            // Verzamel monthly_fees voor deze serie
            // KRITIEKE FIX (08-11-2025): Gebruik ALLE fee records, niet alleen matching met start_date
            // Elke fee record representeert de data voor die specifieke maand
            $seriesMonthlyFees = collect();

            foreach ($projects as $project) {
                // Haal alle fees op voor dit project
                $projectFees = $allMonthlyFees->filter(function($fee) use ($project) {
                    return $fee->project_id == $project->id;
                });

                // Voeg alle fees toe aan de series collection, geïndexeerd op maand nummer
                foreach ($projectFees as $fee) {
                    $seriesMonthlyFees->put($fee->month, $fee);
                }
            }

            $monthlyData = [];
            $yearTotals = [
                'budget' => 0,
                'spent' => 0,
                'variance' => 0,
                'hours' => 0
            ];

            for ($month = 1; $month <= 12; $month++) {
                $project = $projectsByMonth[$month] ?? null;

                // Haal fee data op basis van MONTH nummer, niet project_id
                $fee = $seriesMonthlyFees->get($month);

                if ($fee) {
                    // BELANGRIJKE WIJZIGING (03-11-2025):
                    // Voor maandweergave: toon ALLEEN base_monthly_fee ZONDER rollover
                    // Rollover effecten zijn zichtbaar in de Totalen kolom
                    $baseMonthlyBudget = $fee->base_monthly_fee; // Budget voor deze maand (ZONDER rollover)
                    $budgetWithRollover = $fee->base_monthly_fee + $fee->rollover_from_previous; // Voor totalen berekening
                    $spent = $fee->hours_value + $fee->additional_costs_in_fee;

                    // Variance ZONDER rollover (voor maandweergave)
                    $monthVariance = $baseMonthlyBudget - $spent;
                    $monthVariancePercentage = $baseMonthlyBudget > 0 ? (($monthVariance / $baseMonthlyBudget) * 100) : 0;

                    // Bepaal status kleur op basis van maand variance (ZONDER rollover)
                    $status = 'no-data';
                    if ($spent == 0) {
                        $status = 'no-data';
                    } elseif ($monthVariancePercentage > 10) {
                        $status = 'underspent';
                    } elseif ($monthVariancePercentage < -10) {
                        $status = 'overspent';
                    } elseif ($monthVariancePercentage >= -10 && $monthVariancePercentage <= 10) {
                        $status = 'on-budget';
                    }

                    $monthlyData[$month] = [
                        'project_id' => $project?->id,
                        'project_name' => $project?->name,
                        'project_status' => $project?->status,
                        'base_budget' => $baseMonthlyBudget, // Budget ZONDER rollover (voor maandweergave)
                        'budget' => $budgetWithRollover, // Budget MET rollover (voor totalen berekening)
                        'spent' => $spent,
                        'hours_value' => $fee->hours_value, // Breakdown: tijd registratie kosten
                        'additional_costs_in_fee' => $fee->additional_costs_in_fee, // Breakdown: additional costs binnen budget
                        'additional_costs_outside_fee' => $fee->additional_costs_outside_fee, // Breakdown: additional costs buiten budget
                        'month_variance' => $monthVariance, // Variance ZONDER rollover (voor maandweergave)
                        'variance' => $budgetWithRollover - $spent, // Variance MET rollover (voor totalen)
                        'variance_percentage' => $monthVariancePercentage, // Percentage ZONDER rollover
                        'hours' => $fee->hours_worked,
                        'rollover' => $fee->rollover_to_next,
                        'status' => $status,
                        'has_data' => true
                    ];

                    // KRITIEKE FIX (03-11-2025): Totalen = SOM van BASE budgets ZONDER rollover
                    // Rollover verhoogt niet het totale jaarbudget, het verschuift alleen budget tussen maanden
                    $yearTotals['budget'] += $baseMonthlyBudget; // Som van base budgets ZONDER rollover
                    $yearTotals['spent'] += $spent;
                    $yearTotals['variance'] += ($baseMonthlyBudget - $spent); // Correcte variance: totaal budget - totaal spent
                    $yearTotals['hours'] += $fee->hours_worked;
                } else {
                    // Geen fee data voor deze maand
                    $monthlyData[$month] = [
                        'project_id' => $project?->id,
                        'project_name' => $project?->name,
                        'project_status' => $project?->status,
                        'budget' => $project?->monthly_fee ?? 0,
                        'spent' => 0,
                        'variance' => 0,
                        'variance_percentage' => 0,
                        'hours' => 0,
                        'rollover' => 0,
                        'status' => 'no-data',
                        'has_data' => false
                    ];
                }
            }

            // Check of project een budget heeft
            $hasMonthlyFee = ($firstProject->monthly_fee ?? 0) > 0;

            // Tel alleen projecten MET budget mee in grandTotals
            // Projecten zonder budget (tracking only) tellen niet mee in budget/variance
            if ($hasMonthlyFee) {
                $grandTotals['budget'] += $yearTotals['budget'];
                $grandTotals['spent'] += $yearTotals['spent'];
                $grandTotals['variance'] += $yearTotals['variance'];
                $grandTotals['hours'] += $yearTotals['hours'];
            } else {
                // Voor tracking-only projecten tellen we alleen spent en hours mee
                $grandTotals['spent'] += $yearTotals['spent'];
                $grandTotals['hours'] += $yearTotals['hours'];
            }

            $projectsData[] = [
                'series_id' => $seriesId,
                'series_name' => $seriesBaseName,
                'customer' => $firstProject->customer,
                'monthly_data' => $monthlyData,
                'year_totals' => $yearTotals,
                'has_monthly_fee' => $hasMonthlyFee,
                'is_individual' => false
            ];
        }

        // NIEUW (08-11-2025): Verwerk individuele projecten (zonder recurring_series_id)
        $individualProjectsData = [];
        foreach ($allIndividualProjects as $project) {
            // Haal fee data op voor dit project
            $projectFees = $allMonthlyFees->filter(function($fee) use ($project) {
                return $fee->project_id == $project->id;
            });

            $monthlyData = [];
            $yearTotals = [
                'budget' => 0,
                'spent' => 0,
                'variance' => 0,
                'hours' => 0
            ];

            for ($month = 1; $month <= 12; $month++) {
                $fee = $projectFees->where('month', $month)->first();

                if ($fee) {
                    $baseMonthlyBudget = $fee->base_monthly_fee;
                    $budgetWithRollover = $fee->base_monthly_fee + $fee->rollover_from_previous;
                    $spent = $fee->hours_value + $fee->additional_costs_in_fee;
                    $monthVariance = $baseMonthlyBudget - $spent;
                    $monthVariancePercentage = $baseMonthlyBudget > 0 ? (($monthVariance / $baseMonthlyBudget) * 100) : 0;

                    $status = 'no-data';
                    if ($spent == 0) {
                        $status = 'no-data';
                    } elseif ($monthVariancePercentage > 10) {
                        $status = 'underspent';
                    } elseif ($monthVariancePercentage < -10) {
                        $status = 'overspent';
                    } elseif ($monthVariancePercentage >= -10 && $monthVariancePercentage <= 10) {
                        $status = 'on-budget';
                    }

                    $monthlyData[$month] = [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'project_status' => $project->status,
                        'base_budget' => $baseMonthlyBudget,
                        'budget' => $budgetWithRollover,
                        'spent' => $spent,
                        'hours_value' => $fee->hours_value,
                        'additional_costs_in_fee' => $fee->additional_costs_in_fee,
                        'additional_costs_outside_fee' => $fee->additional_costs_outside_fee,
                        'month_variance' => $monthVariance,
                        'variance' => $budgetWithRollover - $spent,
                        'variance_percentage' => $monthVariancePercentage,
                        'hours' => $fee->hours_worked,
                        'rollover' => $fee->rollover_to_next,
                        'status' => $status,
                        'has_data' => true
                    ];

                    $yearTotals['budget'] += $baseMonthlyBudget;
                    $yearTotals['spent'] += $spent;
                    $yearTotals['variance'] += ($baseMonthlyBudget - $spent);
                    $yearTotals['hours'] += $fee->hours_worked;
                } else {
                    $monthlyData[$month] = [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'project_status' => $project->status,
                        'budget' => $project->monthly_fee ?? 0,
                        'spent' => 0,
                        'variance' => 0,
                        'variance_percentage' => 0,
                        'hours' => 0,
                        'rollover' => 0,
                        'status' => 'no-data',
                        'has_data' => false
                    ];
                }
            }

            $hasMonthlyFee = ($project->monthly_fee ?? 0) > 0;

            // Tel mee in grandTotals
            if ($hasMonthlyFee) {
                $grandTotals['budget'] += $yearTotals['budget'];
                $grandTotals['spent'] += $yearTotals['spent'];
                $grandTotals['variance'] += $yearTotals['variance'];
                $grandTotals['hours'] += $yearTotals['hours'];
            } else {
                $grandTotals['spent'] += $yearTotals['spent'];
                $grandTotals['hours'] += $yearTotals['hours'];
            }

            $individualProjectsData[] = [
                'series_id' => null,
                'series_name' => $project->name,
                'customer' => $project->customer,
                'monthly_data' => $monthlyData,
                'year_totals' => $yearTotals,
                'has_monthly_fee' => $hasMonthlyFee,
                'is_individual' => true,
                'project' => $project
            ];
        }

        // Bereken totalen per maand (alleen projecten MET budget)
        $monthTotals = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthTotals[$month] = [
                'budget' => 0,
                'spent' => 0,
                'variance' => 0,
                'hours' => 0
            ];

            // Tel recurring series mee
            foreach ($projectsData as $seriesData) {
                if ($seriesData['has_monthly_fee']) {
                    $monthTotals[$month]['budget'] += $seriesData['monthly_data'][$month]['budget'];
                    $monthTotals[$month]['spent'] += $seriesData['monthly_data'][$month]['spent'];
                    $monthTotals[$month]['variance'] += $seriesData['monthly_data'][$month]['variance'];
                    $monthTotals[$month]['hours'] += $seriesData['monthly_data'][$month]['hours'];
                }
            }

            // NIEUW (08-11-2025): Tel ook individuele projecten mee
            foreach ($individualProjectsData as $individualData) {
                if ($individualData['has_monthly_fee']) {
                    $monthTotals[$month]['budget'] += $individualData['monthly_data'][$month]['budget'];
                    $monthTotals[$month]['spent'] += $individualData['monthly_data'][$month]['spent'];
                    $monthTotals[$month]['variance'] += $individualData['monthly_data'][$month]['variance'];
                    $monthTotals[$month]['hours'] += $individualData['monthly_data'][$month]['hours'];
                }
            }
        }

        // Available years voor dropdown
        $availableYears = range(Carbon::now()->year - 2, Carbon::now()->year + 1);

        // Splits projecten in overspent, underspent en no-budget (recurring + individual)
        $overspentProjects = [];
        $underspentProjects = [];
        $noBudgetProjects = [];

        // Verwerk recurring series
        foreach ($projectsData as $project) {
            if (!$project['has_monthly_fee']) {
                $noBudgetProjects[] = $project;
            } elseif ($project['year_totals']['variance'] < 0) {
                $overspentProjects[] = $project;
            } else {
                $underspentProjects[] = $project;
            }
        }

        // NIEUW (08-11-2025): Verwerk ook individuele projecten
        foreach ($individualProjectsData as $project) {
            if (!$project['has_monthly_fee']) {
                $noBudgetProjects[] = $project;
            } elseif ($project['year_totals']['variance'] < 0) {
                $overspentProjects[] = $project;
            } else {
                $underspentProjects[] = $project;
            }
        }

        // Bereken totalen per groep
        $overspentTotals = [
            'budget' => array_sum(array_map(fn($p) => $p['year_totals']['budget'], $overspentProjects)),
            'spent' => array_sum(array_map(fn($p) => $p['year_totals']['spent'], $overspentProjects)),
            'variance' => array_sum(array_map(fn($p) => $p['year_totals']['variance'], $overspentProjects)),
            'hours' => array_sum(array_map(fn($p) => $p['year_totals']['hours'], $overspentProjects)),
        ];

        $underspentTotals = [
            'budget' => array_sum(array_map(fn($p) => $p['year_totals']['budget'], $underspentProjects)),
            'spent' => array_sum(array_map(fn($p) => $p['year_totals']['spent'], $underspentProjects)),
            'variance' => array_sum(array_map(fn($p) => $p['year_totals']['variance'], $underspentProjects)),
            'hours' => array_sum(array_map(fn($p) => $p['year_totals']['hours'], $underspentProjects)),
        ];

        $noBudgetTotals = [
            'spent' => array_sum(array_map(fn($p) => $p['year_totals']['spent'], $noBudgetProjects)),
            'hours' => array_sum(array_map(fn($p) => $p['year_totals']['hours'], $noBudgetProjects)),
        ];

        return view('recurring-dashboard.index', compact(
            'overspentProjects',
            'underspentProjects',
            'noBudgetProjects',
            'overspentTotals',
            'underspentTotals',
            'noBudgetTotals',
            'months',
            'monthTotals',
            'grandTotals',
            'currentYear',
            'availableYears',
            'allCompanies',
            'selectedCompanyId'
        ));
    }

    /**
     * Extraheer base naam van recurring series uit project naam
     * Bijvoorbeeld: "AVM Social January 2025" → "AVM Social"
     */
    private function extractSeriesBaseName(string $projectName): string
    {
        // Verwijder maanden (Engels en Nederlands) en jaartallen
        $monthsPattern = '/(January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|januari|februari|maart|april|mei|juni|juli|augustus|september|oktober|november|december)\s*\d{4}/i';

        $baseName = preg_replace($monthsPattern, '', $projectName);

        // Trim whitespace
        return trim($baseName);
    }

    /**
     * Handmatig budget tracking data verversen
     * Roept het recurring:update-monthly-fees command aan voor het huidige jaar
     */
    public function refreshData(Request $request)
    {
        // Authorization check - alleen project_manager en hoger
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only project managers and administrators can refresh budget data.');
        }

        try {
            $year = $request->input('year', Carbon::now()->year);
            $companyId = $request->input('company_id', null);

            \Log::info('Manual budget tracking refresh initiated', [
                'user_id' => Auth::id(),
                'year' => $year,
                'company_id' => $companyId
            ]);

            // Roep het artisan command aan met timeout van 300 seconden (5 minuten)
            \Artisan::call('recurring:update-monthly-fees', [
                '--year' => $year
            ]);

            $output = \Artisan::output();

            \Log::info('Budget tracking refresh completed successfully', [
                'user_id' => Auth::id(),
                'year' => $year,
                'company_id' => $companyId
            ]);

            // Build redirect parameters
            $redirectParams = ['year' => $year];
            if ($companyId) {
                $redirectParams['company_id'] = $companyId;
            }

            return redirect()->route('recurring-dashboard', $redirectParams)
                ->with('success', 'Budget tracking data successfully refreshed for ' . $year . '!');

        } catch (\Exception $e) {
            \Log::error('Budget tracking refresh failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('recurring-dashboard')
                ->with('error', 'Failed to refresh data: ' . $e->getMessage());
        }
    }
}
