<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\Customer;
use App\Models\Company;
use App\Models\User;
use App\Models\ProjectTemplate;
use App\Services\ProjectBudgetService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Toon overzicht van alle projecten
     */
    public function index(Request $request): View|RedirectResponse
    {
        Log::info('ProjectController@index called', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()?->role ?? 'guest',
            'request_params' => $request->all()
        ]);

        // Authorization check - role-based
        if (!Auth::check()) {
            Log::warning('ProjectController@index: User not authenticated');
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()?->role, ['super_admin', 'admin', 'project_manager', 'user', 'reader'])) {
            Log::warning('ProjectController@index: Access denied for user', [
                'user_id' => Auth::id(),
                'role' => Auth::user()?->role
            ]);
            abort(403, 'Access denied. You do not have permission to view projects.');
        }

        // Query building met eager loading
        $query = Project::with(['customer', 'companyRelation', 'mainInvoicingCompany']);
        
        // Company isolation - super_admin ziet alles, anderen alleen eigen company
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            $query->where('company_id', Auth::user()->company_id);
            Log::info('Applied company isolation filter', ['company_id' => Auth::user()->company_id]);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($customerQuery) use ($request) {
                      $customerQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
            Log::info('Applied search filter', ['search_term' => $request->search]);
        }

        // Status filter - default naar 'active' als geen status is opgegeven
        $statusFilter = $request->filled('status') ? $request->status : 'active';
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
            Log::info('Applied status filter', ['status' => $statusFilter]);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
            Log::info('Applied customer filter', ['customer_id' => $request->customer_id]);
        }

        // Company filter (voor super_admin)
        if ($request->filled('company_id') && Auth::user()->role === 'super_admin') {
            $query->where('company_id', $request->company_id);
            Log::info('Applied company filter (super_admin)', ['company_id' => $request->company_id]);
        }

        // Add milestone counts voor progress calculation
        $query->withCount([
            'milestones',
            'milestones as completed_milestones_count' => function($q) {
                $q->where('status', 'completed');
            }
        ]);

        // NIEUWE FEATURE (03-11-2025): Sorteerbare kolommen
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        // Whitelist toegestane sorteer velden
        $allowedSortFields = [
            'name', 'status', 'start_date', 'end_date',
            'monthly_fee', 'billing_frequency', 'created_at', 'budget_used'
        ];

        // Valideer sort field
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // Valideer direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Speciale handling voor customer naam (relation)
        if ($sortField === 'customer_name') {
            $query->join('customers', 'projects.customer_id', '=', 'customers.id')
                  ->select('projects.*')
                  ->orderBy('customers.name', $sortDirection);
        } elseif ($sortField !== 'budget_used') {
            // Budget_used wordt later gesorteerd, na berekeningen
            $query->orderBy($sortField, $sortDirection);
        } else {
            // Budget_used: gebruik default created_at ordering, sorteren later
            $query->orderBy('created_at', 'desc');
        }

        try {
            $projects = $query->paginate(15)->appends([
                'search' => $request->search,
                'status' => $statusFilter,
                'customer_id' => $request->customer_id,
                'company_id' => $request->company_id,
                'sort' => $sortField,
                'direction' => $sortDirection
            ]);

            // GEOPTIMALISEERD: Batch load budget data voor alle projecten
            $budgetService = new ProjectBudgetService();
            $projectIds = $projects->pluck('id')->toArray();

            // Bepaal voor elk project de target month/year
            $projectTargets = [];
            foreach ($projects as $project) {
                if ($project->start_date) {
                    $targetDate = \Carbon\Carbon::parse($project->start_date);
                    $projectTargets[$project->id] = [
                        'month' => $targetDate->month,
                        'year' => $targetDate->year
                    ];
                } else {
                    $projectTargets[$project->id] = [
                        'month' => now()->month,
                        'year' => now()->year
                    ];
                }
            }

            // Batch load alle monthly fees voor deze projecten
            // KRITIEKE FIX (03-11-2025): Haal ALLE jaren, niet alleen current year!
            // Oudere projecten hebben data in 2024, 2023, etc.
            $allMonthlyFees = \App\Models\ProjectMonthlyFee::whereIn('project_id', $projectIds)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            // Groepeer per project en neem de meest recente maand (ANY year)
            $monthlyFees = $allMonthlyFees->groupBy('project_id')->map(function($fees) {
                return $fees->first(); // Meest recente (door desc ordering op year + month)
            });

            // Batch load time entries voor alle projecten (FIX: Budget Used grafiek - 03-11-2025)
            // KRITIEKE FIX (03-11-2025): Filter op is_billable = 'billable'!
            // Non-billable uren mogen NIET meegeteld worden in budget berekeningen
            $timeEntriesByProject = \App\Models\TimeEntry::whereIn('project_id', $projectIds)
                ->where('status', 'approved')
                ->where('is_billable', 'billable')  // BELANGRIJK: Alleen billable uren!
                ->select('project_id', \DB::raw('SUM(hours + (minutes / 60)) as total_hours'))
                ->groupBy('project_id')
                ->pluck('total_hours', 'project_id');

            // Proces elk project met de pre-loaded data
            foreach ($projects as $project) {
                $monthlyFee = $monthlyFees->get($project->id);

                // KRITIEKE FIX (03-11-2025): Bereken ALTIJD tijd costs uit logged hours
                // Gebruik NIET amount_invoiced_from_fee want dat is wat al gefactureerd is,
                // niet wat er werkelijk aan tijd gelogd is!
                $loggedHours = $timeEntriesByProject->get($project->id, 0);
                $hourlyRate = $project->default_hourly_rate ?? 165.00;
                $timeCosts = $loggedHours * $hourlyRate;

                if ($monthlyFee) {
                    // Gebruik bestaande monthly fee data voor BUDGET
                    $totalBudget = $monthlyFee->total_available_fee ?? 0;
                    // MAAR: gebruik tijd costs voor USED, niet amount_invoiced_from_fee!
                    $budgetUsed = $timeCosts;
                    $budgetRemaining = max(0, $totalBudget - $budgetUsed);

                    // KRITIEKE FIX (03-11-2025): Gebruik ALTIJD het ORIGINELE monthly_fee voor percentage!
                    // total_available_fee kan lager zijn door rollover deficits van vorige maand,
                    // wat misleidende percentages geeft (366% ipv 231%).
                    // BELANGRIJK: GEEN min(100, ...) cap! Overspent projecten moeten 175% etc kunnen tonen.
                    $originalBudget = $project->monthly_fee ?? 0;
                    if ($originalBudget > 0 && $budgetUsed > 0) {
                        // Bereken percentage op basis van ORIGINEEL maandbudget (kan >100% zijn!)
                        $project->budget_percentage = round(($budgetUsed / $originalBudget) * 100);
                    } else if ($budgetUsed > 0) {
                        // Geen budget maar wel costs: toon als 100%
                        $project->budget_percentage = 100;
                    } else {
                        // Geen budget en geen costs: 0%
                        $project->budget_percentage = 0;
                    }

                    $project->budget_total = $totalBudget;
                    $project->budget_used = $budgetUsed;
                    $project->budget_remaining = $budgetRemaining;
                    $project->total_logged_hours = $loggedHours;
                    $project->total_time_costs = $timeCosts;

                    Log::debug('Budget loaded from monthly_fees for project', [
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'total_budget' => $totalBudget,
                        'original_budget' => $project->monthly_fee,
                        'budget_used' => $budgetUsed,
                        'logged_hours' => $loggedHours,
                        'time_costs' => $timeCosts,
                        'percentage' => $project->budget_percentage
                    ]);
                } else {
                    // Geen monthly_fees data beschikbaar - gebruik project monthly_fee als fallback
                    $monthlyBudget = $project->monthly_fee ?? 0;

                    // BELANGRIJKE FIX (03-11-2025): Ook projecten zonder budget moeten time costs tonen
                    // Als er geen monthly_fee is maar wel time entries, toon gewoon de costs
                    // BELANGRIJK: GEEN min(100, ...) cap! Overspent projecten moeten 175% etc kunnen tonen.
                    if ($monthlyBudget > 0) {
                        // Project heeft budget: bereken percentage (kan >100% zijn!)
                        $project->budget_percentage = round(($timeCosts / $monthlyBudget) * 100);
                        $project->budget_total = $monthlyBudget;
                        $project->budget_used = $timeCosts;
                        $project->budget_remaining = max(0, $monthlyBudget - $timeCosts);
                    } else {
                        // Project heeft GEEN budget maar WEL time entries: toon costs zonder percentage
                        $project->budget_percentage = 0;
                        $project->budget_total = 0;
                        $project->budget_used = $timeCosts;
                        $project->budget_remaining = 0;
                    }

                    $project->total_logged_hours = $loggedHours;
                    $project->total_time_costs = $timeCosts;

                    Log::debug('No monthly fee data found, using project monthly_fee fallback', [
                        'project_id' => $project->id,
                        'logged_hours' => $loggedHours,
                        'time_costs' => $timeCosts,
                        'monthly_fee' => $monthlyBudget,
                        'has_budget' => $monthlyBudget > 0
                    ]);
                }
            }

            // NIEUWE FEATURE (03-11-2025): Sort op budget_used NA berekeningen
            if ($sortField === 'budget_used') {
                $sorted = $projects->getCollection()->sortBy(function($project) {
                    return $project->budget_used ?? 0;
                }, SORT_REGULAR, $sortDirection === 'desc');

                // Replace de collection met de gesorteerde versie
                $projects->setCollection($sorted->values());

                Log::info('Applied budget_used sorting', [
                    'direction' => $sortDirection,
                    'sample_values' => $projects->take(3)->pluck('budget_used', 'name')
                ]);
            }

            Log::info('Projects query executed successfully', [
                'total_projects' => $projects->total(),
                'current_page' => $projects->currentPage()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to execute projects query', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Statistics berekenen voor dashboard
        try {
            $statsQuery = Project::query();
            
            // Apply company isolation voor statistics
            if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
                $statsQuery->where('company_id', Auth::user()->company_id);
            }

            $stats = [
                'total_projects' => $statsQuery->count(),
                'active_projects' => $statsQuery->where('status', 'active')->count(),
                'completed_projects' => $statsQuery->where('status', 'completed')->count(),
                'draft_projects' => $statsQuery->where('status', 'draft')->count(),
                'total_value' => $statsQuery->whereNotNull('monthly_fee')->sum('monthly_fee'),
            ];
            
            Log::info('Project statistics calculated', $stats);
        } catch (\Exception $e) {
            Log::error('Error calculating project statistics: ' . $e->getMessage());
            $stats = [
                'total_projects' => 0,
                'active_projects' => 0,
                'completed_projects' => 0,
                'draft_projects' => 0,
                'total_value' => 0,
            ];
        }

        // Filter options voor dropdowns
        $customers = Customer::where('status', 'active')
            ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        $companies = Company::where('is_active', true)
            ->orderBy('name')
            ->get();

        Log::info('ProjectController@index completed successfully', [
            'projects_count' => $projects->count(),
            'customers_count' => $customers->count(),
            'companies_count' => $companies->count()
        ]);

        return view('projects.index', compact('projects', 'stats', 'customers', 'companies'));
    }

    /**
     * Toon form voor nieuw project
     */
    public function create(): View
    {
        Log::info('ProjectController@create called', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role ?? 'guest'
        ]);

        // Authorization check - alleen admin rollen kunnen projecten aanmaken
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            Log::warning('ProjectController@create: Access denied for user', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role
            ]);
            abort(403, 'Access denied. Only administrators can create projects.');
        }

        try {
            // Haal customers op voor dropdown (company isolation)
            $customers = Customer::where('status', 'active')
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();

            Log::info('Customers loaded for create form', ['count' => $customers->count()]);

            // Haal users op voor team assignment (company isolation)
            $users = User::whereNotNull('name')
                ->whereNotNull('email')
                ->where('role', '!=', 'super_admin') // Verberg super_admin users
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->with('companyRelation')
                ->orderBy('name')
                ->get();

            Log::info('Users loaded for create form', ['count' => $users->count()]);

            // Get companies - admin and super_admin see all companies
            $companies = Company::where('is_active', true)
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where('id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();
            $defaultCompany = null;

            Log::info('Companies loaded for create form', [
                'count' => $companies->count()
            ]);

            // Haal templates op voor snelle project creation
            // Templates zijn beschikbaar als ze aan de company horen OF als company_id NULL is (globale templates)
            $templates = ProjectTemplate::where('status', 'active')
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where(function($query) {
                        $query->where('company_id', Auth::user()->company_id)
                              ->orWhereNull('company_id');
                    });
                })
                ->orderBy('name')
                ->get();

            Log::info('Templates loaded for create form', ['count' => $templates->count()]);

            // Haal invoice templates op voor selectie
            $invoiceTemplates = \App\Models\InvoiceTemplate::where('is_active', true)
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where(function($query) {
                        $query->whereNull('company_id')
                              ->orWhere('company_id', Auth::user()->company_id);
                    });
                })
                ->orderBy('name')
                ->get();

            Log::info('Invoice templates loaded for create form', ['count' => $invoiceTemplates->count()]);

            // Get existing recurring series IDs for dropdown
            $existingSeriesIds = Project::whereNotNull('recurring_series_id')
                ->select('recurring_series_id', DB::raw('COUNT(*) as project_count'))
                ->groupBy('recurring_series_id')
                ->orderBy('recurring_series_id')
                ->get();

            Log::info('Existing series IDs loaded', ['count' => $existingSeriesIds->count()]);

            Log::info('ProjectController@create completed successfully');

            return view('projects.create', compact(
                'customers',
                'users',
                'companies',
                'templates',
                'invoiceTemplates',
                'defaultCompany',
                'existingSeriesIds'
            ));

        } catch (\Exception $e) {
            Log::error('Error in ProjectController@create', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Sla nieuw project op in database - COMPLEET GEFIXTE VERSIE
     */
    public function store(Request $request)
    {
        Log::info('=== ProjectController@store STARTED ===', [
            'timestamp' => now()->toISOString(),
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role,
            'user_company_id' => Auth::user()->company_id,
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Log alle request data
        Log::info('Request data received', [
            'all_request_data' => $request->all(),
            'files' => $request->allFiles(),
            'headers' => $request->headers->all()
        ]);

        Log::info('=== STEP 1: Authorization Check ===');

        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            Log::warning('Authorization failed in store method', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role,
                'required_roles' => ['super_admin', 'admin', 'project_manager']
            ]);
            abort(403, 'Access denied. Only administrators can create projects.');
        }

        Log::info('Authorization passed', ['user_role' => Auth::user()->role]);

        Log::info('=== STEP 2: Validation ===');

        // Validation
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'customer_id' => 'required|exists:customers,id',
                'template_id' => 'nullable|exists:project_templates,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'required|in:draft,active,completed,cancelled,on_hold',
                'monthly_fee' => 'nullable|numeric|min:0',
                'fee_start_date' => 'nullable|date',
                'fee_rollover_enabled' => 'boolean',
                'default_hourly_rate' => 'nullable|numeric|min:0',
                'main_invoicing_company_id' => 'nullable|exists:companies,id',
                'vat_rate' => 'nullable|numeric|min:0|max:100',
                'billing_frequency' => 'nullable|in:monthly,quarterly,milestone,project_completion,custom',
                'billing_interval_days' => 'nullable|integer|min:1|max:365|required_if:billing_frequency,custom',
                'is_recurring' => 'nullable|boolean',
                'is_master_template' => 'nullable|boolean',
                'recurring_frequency' => 'nullable|in:monthly,quarterly|required_if:is_recurring,1',
                'recurring_base_name' => 'nullable|string|max:255|required_if:is_recurring,1',
                'recurring_series_id' => 'nullable|string|max:100',
                'recurring_days_before' => 'nullable|integer|min:1|max:30',
                'recurring_end_date' => 'nullable|date|after:start_date',
                'notes' => 'nullable|string',
                'team_members' => 'nullable|array',
                'team_members.*' => 'exists:users,id',
                'companies' => 'nullable|array',
                'companies.*' => 'exists:companies,id'
            ]);

            Log::info('Validation passed', [
                'validated_fields' => array_keys($validated),
                'validated_data' => $validated
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        Log::info('=== STEP 3: Database Transaction Start ===');

        try {
            DB::beginTransaction();
            Log::info('Database transaction started');

            // Bepaal company_id (voor multi-tenant isolatie)
            $companyId = Auth::user()->company_id ?? 1;
            Log::info('Company ID determined', [
                'user_company_id' => Auth::user()->company_id,
                'final_company_id' => $companyId
            ]);

            Log::info('=== STEP 4: Create Project Record ===');

            // Maak project aan
            $projectData = [
                'company_id' => $companyId,
                'customer_id' => $validated['customer_id'],
                'template_id' => $validated['template_id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $request->has('is_recurring') ? 'active' : $validated['status'], // Recurring projects zijn altijd active
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'monthly_fee' => $validated['monthly_fee'] ?? null,
                'fee_start_date' => $validated['fee_start_date'] ?? null,
                'fee_rollover_enabled' => $request->has('fee_rollover_enabled'),
                'default_hourly_rate' => $validated['default_hourly_rate'] ?? null,
                'main_invoicing_company_id' => $validated['main_invoicing_company_id'] ?? $companyId,
                'vat_rate' => $validated['vat_rate'] ?? 21.00,
                'billing_frequency' => $validated['billing_frequency'] ?? 'monthly',
                'billing_interval_days' => $validated['billing_interval_days'] ?? null,
                'next_billing_date' => $this->calculateNextBillingDate(
                    $validated['billing_frequency'] ?? 'monthly',
                    $validated['billing_interval_days'] ?? null,
                    $validated['start_date']
                ),
                'is_recurring' => $request->has('is_recurring'),
                'is_master_template' => $request->has('is_master_template'),
                'recurring_frequency' => $request->has('is_recurring') ? ($validated['recurring_frequency'] ?? 'monthly') : null,
                'recurring_base_name' => $request->has('is_recurring') ? ($validated['recurring_base_name'] ?? null) : null,
                'recurring_days_before' => $request->has('is_recurring') ? ($validated['recurring_days_before'] ?? 7) : null,
                'recurring_end_date' => $request->has('is_recurring') ? ($validated['recurring_end_date'] ?? null) : null,
                'recurring_period' => $request->has('is_recurring') ? \Carbon\Carbon::parse($validated['start_date'])->format('M Y') : null,
                'recurring_series_id' => $request->has('is_recurring') ? ($validated['recurring_series_id'] ?? null) : null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

            Log::info('About to create project with data', $projectData);

            $project = Project::create($projectData);

            // Als recurring is enabled maar geen series_id is opgegeven, auto-generate
            if ($project->is_recurring && empty($project->recurring_series_id)) {
                $project->recurring_series_id = 'series-' . $project->id;
                $project->save();
                Log::info('Auto-generated recurring_series_id', ['series_id' => $project->recurring_series_id]);
            }

            // Als dit project een master template is, verwijder master status van andere projecten in deze serie
            if ($project->is_master_template && $project->recurring_series_id) {
                Log::info('Removing master template status from other projects in series', [
                    'new_master_id' => $project->id,
                    'series_id' => $project->recurring_series_id
                ]);

                $removedCount = Project::where('recurring_series_id', $project->recurring_series_id)
                    ->where('id', '!=', $project->id)
                    ->where('is_master_template', true)
                    ->update(['is_master_template' => false]);

                Log::info('Master template cleanup completed', [
                    'removed_count' => $removedCount
                ]);
            }

            Log::info('Project created successfully', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'created_at' => $project->created_at
            ]);

            // Import template structure if template was selected
            if (!empty($validated['template_id'])) {
                Log::info('=== STEP 5: Import Template Structure ===');
                $this->importTemplateStructure($project, $validated['template_id']);
            }

            Log::info('=== STEP 6: Add Team Members ===');

            // Voeg team members toe indien opgegeven
            if (!empty($validated['team_members'])) {
                Log::info('Processing team members', [
                    'team_member_ids' => $validated['team_members']
                ]);

                $teamData = [];
                foreach ($validated['team_members'] as $userId) {
                    $teamData[$userId] = [
                        'role_override' => null,
                        'can_edit_fee' => false,
                        'can_view_financials' => false,
                        'can_log_time' => true,
                        'can_approve_time' => false,
                        'added_by' => Auth::id(),
                        'added_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Log::info('Team data prepared', ['team_data' => $teamData]);

                try {
                    $project->users()->sync($teamData);
                    Log::info('Team members added successfully', [
                        'team_count' => count($teamData)
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to add team members', [
                        'error' => $e->getMessage(),
                        'team_data' => $teamData
                    ]);
                    throw $e;
                }
            } else {
                Log::info('No team members to add');
            }

            Log::info('=== STEP 6: Add Companies ===');

            // Voeg companies toe voor multi-BV projects - VOLLEDIG GEFIXTE VERSIE
            if (!empty($validated['companies'])) {
                Log::info('Processing companies', [
                    'company_ids' => $validated['companies']
                ]);

                $companyData = [];
                foreach ($validated['companies'] as $compId) {
                    // VOLLEDIG GEFIXTE COMPANY DATA - ALLE VARIABELEN HARDCODED
                    $companyData[$compId] = [
                        'role' => 'subcontractor',
                        'billing_method' => 'actual_hours', // GEFIXD: Was $billingMethod (undefined)
                        'billing_start_date' => $validated['start_date'], // GEFIXD: Project start datum
                        'hourly_rate' => null, // GEFIXD: Was $hourlyRate (undefined)
                        'fixed_amount' => null, // GEFIXD: Was $fixedAmount (undefined)
                        'hourly_rate_override' => null,
                        'monthly_fixed_amount' => null,
                        'is_active' => true,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Log::info('Company data prepared with all required fields (NO UNDEFINED VARIABLES)', [
                    'company_data' => $companyData
                ]);

                try {
                    $project->companies()->sync($companyData);
                    Log::info('Companies added successfully', [
                        'company_count' => count($companyData)
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to add companies', [
                        'error' => $e->getMessage(),
                        'company_data' => $companyData,
                        'sql_state' => $e->getCode(),
                        'error_details' => [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                        ]
                    ]);
                    throw $e;
                }
            } else {
                Log::info('No companies to add');
            }

            Log::info('=== STEP 7: Log Project Activity ===');

            // Log project creation activity
            ProjectActivity::log(
                $project->id,
                'created',
                'created project "' . $project->name . '"',
                null,
                'project',
                $project->id
            );

            Log::info('Project activity logged');

            Log::info('=== STEP 8: Commit Transaction ===');

            DB::commit();
            Log::info('Database transaction committed successfully');

            Log::info('=== STEP 8: Prepare Redirect ===');

            $redirectUrl = route('projects.show', $project);
            Log::info('Preparing redirect', [
                'redirect_url' => $redirectUrl,
                'project_id' => $project->id
            ]);

            Log::info('=== ProjectController@store COMPLETED SUCCESSFULLY ===', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'execution_time' => now()->toISOString()
            ]);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project created successfully!');

        } catch (\Exception $e) {
            Log::error('=== ProjectController@store FAILED ===', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            DB::rollback();
            Log::info('Database transaction rolled back');

            return back()->withInput()
                ->with('error', 'Error creating project: ' . $e->getMessage());
        }
    }

 /**
 * Toon specifiek project - DEFINITIEVE WERKENDE VERSIE MET ALLE VARIABELEN
 */
public function show(Project $project): View
{
    Log::info('ProjectController@show called', [
        'project_id' => $project->id,
        'user_id' => Auth::id()
    ]);

    // Authorization check - gebruiker moet toegang hebben tot project
    if (!$this->canAccessProject($project)) {
        Log::warning('Access denied to project', [
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role
        ]);
        abort(403, 'Access denied. You do not have permission to view this project.');
    }

    try {
        // Eager load alle benodigde relationships inclusief volledige hiërarchie
        $project->load([
            'customer.contacts' => function($query) {
                $query->where('is_active', true)->orderBy('is_primary', 'desc')->orderBy('name');
            },
            'companyRelation',
            'mainInvoicingCompany',
            'users',
            'companies',
            'milestones' => function($query) {
                $query->orderBy('sort_order')
                    ->with(['tasks' => function($taskQuery) {
                        $taskQuery->orderBy('sort_order');
                    }]);
            }
        ]);

        Log::info('Project relationships loaded including full hierarchy');

        // ESSENTIEEL: Alle view variabelen die de show view nodig heeft
        $customers = Customer::where('status', 'active')
            ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        $users = User::whereNotNull('name')
            ->where('role', '!=', 'super_admin') // Verberg super_admin users
            ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->with('companyRelation')
            ->orderBy('name')
            ->get();

        $companies = Company::where('is_active', true)
            ->orderBy('name')
            ->get();

        // TOEGEVOEGD: Templates variabele - dit was het ontbrekende onderdeel!
        // Templates zijn beschikbaar als ze aan de company horen OF als company_id NULL is (globale templates)
        $templates = ProjectTemplate::where('status', 'active')
            ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                $q->where(function($query) {
                    $query->where('company_id', Auth::user()->company_id)
                          ->orWhereNull('company_id');
                });
            })
            ->with(['milestones.tasks'])
            ->orderBy('name')
            ->get();

        Log::info('Templates loaded for show view', ['templates_count' => $templates->count()]);

        // Bereken project statistieken
        $stats = [
            'total_milestones' => 0,
            'completed_milestones' => 0,
            'total_tasks' => 0,
            'total_hours_logged' => 0,
            'total_invoiced' => 0,
            'progress_percentage' => 0,
        ];

        Log::info('Project statistics calculated', $stats);
        Log::info('ProjectController@show completed successfully');

        // KRITIEK: Alle variabelen doorgeven inclusief $templates
        // Load project activities with pagination
        $activities = $project->activities()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('projects.show', compact(
            'project',
            'stats',
            'customers',
            'users',
            'companies',
            'templates',
            'activities'
        ));

    } catch (\Exception $e) {
        Log::error('Error in ProjectController@show', [
            'error' => $e->getMessage(),
            'project_id' => $project->id,
            'trace' => $e->getTraceAsString()
        ]);
        
        // Fallback om crashes te voorkomen
        return back()->with('error', 'Error loading project: ' . $e->getMessage());
    }
}
    /**
     * Toon edit form voor project
     */
    public function edit(Project $project): View
    {
        Log::info('ProjectController@edit called', [
            'project_id' => $project->id,
            'user_id' => Auth::id()
        ]);

        // Authorization check
        if (!$this->canEditProject($project)) {
            Log::warning('Edit access denied to project', [
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()?->role
            ]);
            abort(403, 'Access denied. You do not have permission to edit this project.');
        }

        try {
            // Haal dezelfde data op als bij create
            $customers = Customer::where('status', 'active')
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();

            $users = User::whereNotNull('name')
                ->where('role', '!=', 'super_admin') // Verberg super_admin users
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->with('companyRelation')
                ->orderBy('name')
                ->get();

            // Get companies - admin and super_admin see all companies
            $companies = Company::where('is_active', true)
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where('id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();
            $defaultCompany = null;
            
            // Plugin system removed - always true
            $isCompaniesPluginActive = true;

            // Load current team members, companies with fresh data
            $project->unsetRelation('users'); // Clear any cached users
            $project->load(['users', 'companies', 'milestones.tasks']);
            
            // Log current team members for debugging - use fresh query
            $currentTeamIds = \DB::table('project_users')
                ->where('project_id', $project->id)
                ->pluck('user_id')
                ->toArray();
                
            Log::info('Edit: Current team members', [
                'project_id' => $project->id,
                'team_member_ids' => $currentTeamIds,
                'team_member_names' => $project->users->pluck('name')->toArray(),
                'fresh_from_db' => true
            ]);

            // Load project counts
            $project->loadCount([
                'milestones',
                'milestones as completed_milestones_count' => function ($query) {
                    $query->where('status', 'completed');
                }
            ]);
            
            // Calculate tasks count through milestones
            $project->tasks_count = $project->milestones->sum(function ($milestone) {
                return $milestone->tasks->count();
            });

            // Calculate total time logged
            $project->total_time_logged = \App\Models\TimeEntry::where('project_id', $project->id)
                ->where('status', 'approved')
                ->sum('hours');

            // Haal invoice templates op voor selectie
            $invoiceTemplates = \App\Models\InvoiceTemplate::where('is_active', true)
                ->when(!in_array(Auth::user()->role, ['super_admin', 'admin']), function($q) {
                    $q->where(function($query) {
                        $query->whereNull('company_id')
                              ->orWhere('company_id', Auth::user()->company_id);
                    });
                })
                ->orderBy('name')
                ->get();

            Log::info('Edit form data loaded successfully', [
                'customers_count' => $customers->count(),
                'users_count' => $users->count(),
                'companies_count' => $companies->count(),
                'invoice_templates_count' => $invoiceTemplates->count(),
                'is_companies_plugin_active' => $isCompaniesPluginActive
            ]);

            return view('projects.edit', compact(
                'project', 
                'customers', 
                'users', 
                'companies', 
                'invoiceTemplates',
                'isCompaniesPluginActive',
                'defaultCompany'
            ));

        } catch (\Exception $e) {
            Log::error('Error in ProjectController@edit', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified resource with tabbed interface.
     * 
     * @param Project $project
     * @return View
     */
    public function testSimpleForm(Project $project)
    {
        // Geen auth check voor test
        $customers = Customer::where('status', 'active')->get();
        $users = User::where('role', '!=', 'super_admin')->get(); // Verberg super_admin users
        
        return view('projects.test-simple-form', compact('project', 'customers', 'users'));
    }

    /**
     * Update project in database - MET GEFIXTE COMPANY SYNC
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        Log::info('ProjectController@update called', [
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        // Authorization check
        if (!$this->canEditProject($project)) {
            Log::warning('Update access denied to project', [
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role
            ]);
            abort(403, 'Access denied. You do not have permission to edit this project.');
        }

        // Validation (zelfde als store)
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'customer_id' => 'required|exists:customers,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'required|in:draft,active,completed,cancelled,on_hold',
                'monthly_fee' => 'nullable|numeric|min:0',
                'fee_start_date' => 'nullable|date',
                'fee_rollover_enabled' => 'boolean',
                'default_hourly_rate' => 'nullable|numeric|min:0',
                'main_invoicing_company_id' => 'nullable|exists:companies,id',
                'vat_rate' => 'nullable|numeric|min:0|max:100',
                'billing_frequency' => 'required|in:monthly,quarterly,milestone,project_completion,custom',
                'billing_interval_days' => 'nullable|integer|min:1|max:365|required_if:billing_frequency,custom',
                'next_billing_date' => 'nullable|date',
                'notes' => 'nullable|string',
                'team_members' => 'nullable|array',
                'team_members.*' => 'exists:users,id',
                'companies' => 'nullable|array',
                'companies.*' => 'exists:companies,id'
            ]);

            Log::info('Update validation passed', [
                'validated_data' => $validated,
                'raw_team_members' => $request->input('team_members'),
                'has_team_members' => $request->has('team_members')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Update validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            DB::beginTransaction();
            Log::info('Update transaction started');

            // Track old values for activity logging
            $oldValues = $project->only([
                'name', 'description', 'status', 'customer_id', 'start_date',
                'end_date', 'monthly_fee', 'default_hourly_rate', 'vat_rate'
            ]);

            // Update project
            $project->update([
                'customer_id' => $validated['customer_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'monthly_fee' => $validated['monthly_fee'] ?? null,
                'fee_start_date' => $validated['fee_start_date'] ?? null,
                'fee_rollover_enabled' => $request->has('fee_rollover_enabled'),
                'default_hourly_rate' => $validated['default_hourly_rate'] ?? null,
                'main_invoicing_company_id' => $validated['main_invoicing_company_id'] ?? null,
                'vat_rate' => $validated['vat_rate'] ?? 21.00,
                'billing_frequency' => $validated['billing_frequency'] ?? 'monthly',
                'billing_interval_days' => $validated['billing_interval_days'] ?? null,
                'next_billing_date' => $validated['next_billing_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            Log::info('Project updated successfully');

            // Log changes for important fields
            $newValues = $project->only([
                'name', 'description', 'status', 'customer_id', 'start_date',
                'end_date', 'monthly_fee', 'default_hourly_rate', 'vat_rate'
            ]);

            $changes = [];
            foreach ($newValues as $field => $newValue) {
                $oldValue = $oldValues[$field] ?? null;
                if ($oldValue != $newValue) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }

            // Log activity als er wijzigingen zijn
            if (!empty($changes)) {
                foreach ($changes as $fieldName => $fieldData) {
                    $oldValueStr = $fieldData['old'] ?? '(empty)';
                    $newValueStr = $fieldData['new'] ?? '(empty)';

                    $description = 'updated ' . strtolower(str_replace('_', ' ', $fieldName));
                    if ($oldValueStr !== '(empty)' && $newValueStr !== '(empty)') {
                        $description .= ' from "' . $oldValueStr . '" to "' . $newValueStr . '"';
                    }

                    ProjectActivity::log(
                        $project->id,
                        'updated',
                        $description,
                        [$fieldName => $fieldData],
                        'project',
                        $project->id
                    );
                }
            }

            // Update team members - FORCE SYNC ALWAYS
            // Use validated data or empty array if not set
            $teamMembers = $validated['team_members'] ?? [];
            
            // Extra safety check
            if (!is_array($teamMembers)) {
                $teamMembers = [];
            }
            
            Log::info('TEAM UPDATE - Processing team members', [
                'received_team_members' => $teamMembers,
                'count' => count($teamMembers),
                'current_team_before' => $project->users->pluck('id')->toArray()
            ]);
            
            // Build sync data with pivot attributes
            $syncData = [];
            foreach ($teamMembers as $userId) {
                // Make sure userId is valid
                if (!empty($userId)) {
                    $syncData[(int)$userId] = [
                        'role_override' => null,
                        'can_edit_fee' => false,
                        'can_view_financials' => false,
                        'can_log_time' => true,
                        'can_approve_time' => false,
                        'added_by' => Auth::id(),
                        'added_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            Log::info('Sync data prepared', [
                'sync_data_keys' => array_keys($syncData),
                'sync_data_count' => count($syncData)
            ]);
            
            // Use sync to replace all team members at once
            $changes = $project->users()->sync($syncData);
            
            Log::info('Team members synced', [
                'synced_count' => count($syncData),
                'synced_ids' => array_keys($syncData),
                'attached' => $changes['attached'] ?? [],
                'detached' => $changes['detached'] ?? [],
                'updated' => $changes['updated'] ?? []
            ]);
            
            // Immediately verify what's in the database
            $verifySync = DB::table('project_users')
                ->where('project_id', $project->id)
                ->pluck('user_id')
                ->toArray();
            
            Log::info('Immediate verification after sync', [
                'project_id' => $project->id,
                'db_team_members' => $verifySync,
                'expected' => array_keys($syncData)
            ]);
            
            // Force clear any cached relationships
            $project->unsetRelation('users');
            
            // Log the final state without reloading
            Log::info('TEAM UPDATE - Final state', [
                'final_team' => array_keys($syncData),
                'final_count' => count($syncData)
            ]);

            // Update companies - GEFIXTE VERSIE
            if (isset($validated['companies'])) {
                $companyData = [];
                foreach ($validated['companies'] as $companyId) {
                    $companyData[$companyId] = [
                        'role' => 'subcontractor',
                        'billing_method' => 'actual_hours', // GEFIXD: hardcoded
                        'billing_start_date' => $validated['start_date'], // GEFIXD: project start datum
                        'hourly_rate' => null, // GEFIXD: hardcoded
                        'fixed_amount' => null, // GEFIXD: hardcoded
                        'hourly_rate_override' => null,
                        'monthly_fixed_amount' => null,
                        'is_active' => true,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $project->companies()->sync($companyData);
                Log::info('Companies updated', ['count' => count($companyData)]);
            } else {
                $project->companies()->detach();
                Log::info('All companies removed');
            }

            DB::commit();
            Log::info('Update transaction committed');

            // Force unset the cached relationship before checking
            $project->unsetRelation('users');
            
            // Now do a direct database query to confirm what's actually saved
            $actualTeamMembers = DB::table('project_users')
                ->where('project_id', $project->id)
                ->pluck('user_id')
                ->toArray();
            
            Log::info('ProjectController@update completed successfully', [
                'project_id' => $project->id,
                'team_members_after_commit_direct_db' => $actualTeamMembers
            ]);

            // Check if we came from the tabbed editor
            if ($request->input('from_tabbed_editor') === '1') {
                return redirect()->route('projects.edit', $project)
                    ->with('success', 'Project updated successfully!');
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Project update failed', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->with('error', 'Error updating project: ' . $e->getMessage());
        }
    }

    /**
     * Verwijder project (hard delete)
     */
    public function destroy(Project $project): RedirectResponse
    {
        Log::info('ProjectController@destroy called', [
            'project_id' => $project->id,
            'user_id' => Auth::id()
        ]);

        // Authorization check
        if (!$this->canDeleteProject($project)) {
            Log::warning('Delete access denied to project', [
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role
            ]);
            abort(403, 'Access denied. You do not have permission to delete this project.');
        }

        // Check of project veilig verwijderd kan worden
        if (!$project->canBeDeleted()) {
            Log::warning('Project cannot be safely deleted', [
                'project_id' => $project->id,
                'reason' => 'Has existing time entries or invoices'
            ]);
            return back()->with('error', 'Cannot delete project with existing time entries or invoices.');
        }

        try {
            $projectName = $project->name;
            $projectId = $project->id;

            // Log deletion activity BEFORE deleting (anders geen project meer)
            ProjectActivity::log(
                $project->id,
                'deleted',
                'deleted project "' . $projectName . '"',
                null,
                'project',
                $project->id
            );

            $project->delete(); // Hard delete (SoftDeletes uitgeschakeld)

            Log::info('Project deleted successfully', [
                'project_id' => $projectId,
                'project_name' => $projectName
            ]);

            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Project deletion failed', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error deleting project: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint voor project data (AJAX calls)
     */
    public function apiShow(Project $project)
    {
        Log::info('ProjectController@apiShow called', [
            'project_id' => $project->id,
            'user_id' => Auth::id()
        ]);

        if (!$this->canAccessProject($project)) {
            Log::warning('API access denied to project', [
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $project->load(['milestones.tasks', 'customer', 'users', 'companies']);
            Log::info('API project data loaded successfully');
            return response()->json($project);
        } catch (\Exception $e) {
            Log::error('API project loading failed', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);
            return response()->json(['error' => 'Failed to load project data'], 500);
        }
    }

    /**
     * Update project status via API
     */
    public function updateStatus(Request $request, Project $project)
    {
        Log::info('ProjectController@updateStatus called', [
            'project_id' => $project->id,
            'new_status' => $request->status,
            'user_id' => Auth::id()
        ]);

        if (!$this->canEditProject($project)) {
            Log::warning('Status update access denied', [
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $request->validate([
                'status' => 'required|in:draft,active,completed,cancelled,on_hold'
            ]);

            $oldStatus = $project->status;
            $project->update(['status' => $request->status]);

            Log::info('Project status updated successfully', [
                'project_id' => $project->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Project status updated successfully',
                'project' => $project
            ]);

        } catch (\Exception $e) {
            Log::error('Status update failed', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }

    /**
     * Duplicate project - GEFIXTE VERSIE
     */
    public function duplicate(Project $project): RedirectResponse
    {
        Log::info('ProjectController@duplicate called', [
            'project_id' => $project->id,
            'user_id' => Auth::id()
        ]);

        if (!$this->canEditProject($project)) {
            Log::warning('Duplicate access denied', [
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);
            abort(403, 'Access denied. You do not have permission to duplicate this project.');
        }

        try {
            DB::beginTransaction();
            Log::info('Duplicate transaction started');

            // Dupliceer het project
            $newProject = $project->replicate();
            $newProject->name = $project->name . ' (Copy)';
            $newProject->status = 'draft';
            $newProject->created_by = Auth::id();
            $newProject->updated_by = Auth::id();
            $newProject->save();

            Log::info('Project duplicated', [
                'original_id' => $project->id,
                'new_id' => $newProject->id,
                'new_name' => $newProject->name
            ]);

            // Dupliceer team members
            foreach ($project->users as $user) {
                $pivotData = $user->pivot->toArray();
                $pivotData['added_by'] = Auth::id();
                $pivotData['added_at'] = now();
                $pivotData['created_at'] = now();
                $pivotData['updated_at'] = now();
                $newProject->users()->attach($user->id, $pivotData);
            }

            // Dupliceer companies - GEFIXTE VERSIE
            foreach ($project->companies as $company) {
                $pivotData = $company->pivot->toArray();
                // Zorg dat alle vereiste velden aanwezig zijn - HARDCODED FALLBACKS
                $pivotData['billing_start_date'] = $newProject->start_date;
                $pivotData['billing_method'] = $pivotData['billing_method'] ?? 'actual_hours';
                $pivotData['hourly_rate'] = $pivotData['hourly_rate'] ?? null;
                $pivotData['fixed_amount'] = $pivotData['fixed_amount'] ?? null;
                $pivotData['created_at'] = now();
                $pivotData['updated_at'] = now();
                $newProject->companies()->attach($company->id, $pivotData);
            }

            DB::commit();
            Log::info('Project duplication completed successfully');

            return redirect()->route('projects.show', $newProject)
                ->with('success', 'Project duplicated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Project duplication failed', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error duplicating project: ' . $e->getMessage());
        }
    }

    // ========================================
    // AUTHORIZATION HELPER METHODS
    // ========================================
    
    /**
     * Add team member to project
     */
    /**
     * Get team data for modal
     */
    public function getTeamData(Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Get current team members
        $currentUsers = $project->users()->with('companyRelation')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company' => $user->companyRelation ? [
                    'id' => $user->companyRelation->id,
                    'name' => $user->companyRelation->name
                ] : null
            ];
        });

        // Get available users (not in project)
        $currentUserIds = $currentUsers->pluck('id')->toArray();
        $availableUsersQuery = User::whereNotIn('id', $currentUserIds)
            ->whereNotNull('name')
            ->with('companyRelation')
            ->where('role', '!=', 'super_admin'); // Verberg super_admin users

        // Filter by company for non-super admins
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            $availableUsersQuery->where('company_id', Auth::user()->company_id);
        }

        $availableUsers = $availableUsersQuery->orderBy('name')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company' => $user->companyRelation ? [
                    'id' => $user->companyRelation->id,
                    'name' => $user->companyRelation->name
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'current_users' => $currentUsers,
            'available_users' => $availableUsers
        ]);
    }

    public function addTeamMember(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_override' => 'nullable|in:project_manager,user,reader',
            'can_edit_fee' => 'boolean',
            'can_view_financials' => 'boolean',
            'can_log_time' => 'boolean',
            'can_approve_time' => 'boolean',
        ]);

        try {
            // Check if user is already in the project
            if ($project->users()->where('user_id', $validated['user_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a team member of this project'
                ], 400);
            }

            // Add user to project
            $project->users()->attach($validated['user_id'], [
                'role_override' => $validated['role_override'] ?? null,
                'can_edit_fee' => $validated['can_edit_fee'] ?? false,
                'can_view_financials' => $validated['can_view_financials'] ?? false,
                'can_log_time' => $validated['can_log_time'] ?? true,
                'can_approve_time' => $validated['can_approve_time'] ?? false,
                'added_by' => Auth::id(),
                'added_at' => now(),
            ]);

            Log::info('Team member added to project', [
                'project_id' => $project->id,
                'user_id' => $validated['user_id'],
                'added_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member added successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding team member', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add team member'
            ], 500);
        }
    }

    /**
     * Remove team member from project
     */
    public function removeTeamMember(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            // Check if user is in the project
            if (!$project->users()->where('user_id', $validated['user_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a team member of this project'
                ], 400);
            }

            // Remove user from project
            $project->users()->detach($validated['user_id']);

            Log::info('Team member removed from project', [
                'project_id' => $project->id,
                'user_id' => $validated['user_id'],
                'removed_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing team member', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove team member'
            ], 500);
        }
    }

    /**
     * Update team member permissions
     */
    public function updateTeamMember(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_override' => 'nullable|in:project_manager,user,reader',
            'can_edit_fee' => 'boolean',
            'can_view_financials' => 'boolean',
            'can_log_time' => 'boolean',
            'can_approve_time' => 'boolean',
        ]);

        try {
            // Update user permissions
            $project->users()->updateExistingPivot($validated['user_id'], [
                'role_override' => $validated['role_override'] ?? null,
                'can_edit_fee' => $validated['can_edit_fee'] ?? false,
                'can_view_financials' => $validated['can_view_financials'] ?? false,
                'can_log_time' => $validated['can_log_time'] ?? true,
                'can_approve_time' => $validated['can_approve_time'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member permissions updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating team member', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team member'
            ], 500);
        }
    }

    /**
     * Check of gebruiker toegang heeft tot project
     */
    private function canAccessProject(Project $project): bool
    {
        $user = Auth::user();

        Log::info('Checking project access', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_company_id' => $user->company_id,
            'project_company_id' => $project->company_id
        ]);

        // Super admin kan alles zien
        if ($user->role === 'super_admin') {
            Log::info('Access granted: super_admin');
            return true;
        }

        // Admin kan alles van eigen bedrijf zien
        if ($user->role === 'admin' && $project->company_id === $user->company_id) {
            Log::info('Access granted: admin of same company');
            return true;
        }

        // Project manager kan projecten zien waar hij toegewezen is
        if (in_array($user->role, ['project_manager', 'user'])) {
            $isAssigned = $project->users->contains($user->id);
            $sameCompany = $project->company_id === $user->company_id;
            
            Log::info('Checking project manager/user access', [
                'is_assigned' => $isAssigned,
                'same_company' => $sameCompany
            ]);
            
            if ($isAssigned || $sameCompany) {
                Log::info('Access granted: assigned or same company');
                return true;
            }
        }

        // Reader kan alles van eigen bedrijf bekijken
        if ($user->role === 'reader' && $project->company_id === $user->company_id) {
            Log::info('Access granted: reader of same company');
            return true;
        }

        Log::info('Access denied: no matching criteria');
        return false;
    }

    /**
     * Check of gebruiker project mag bewerken
     */
    private function canEditProject(Project $project): bool
    {
        $user = Auth::user();

        // Check of gebruiker is ingelogd
        if (!$user) {
            Log::info('Edit access denied: user not authenticated');
            return false;
        }

        Log::info('Checking project edit access', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'user_role' => $user->role
        ]);

        // Alleen admin rollen kunnen bewerken
        if (!in_array($user->role, ['super_admin', 'admin', 'project_manager'])) {
            Log::info('Edit access denied: insufficient role');
            return false;
        }

        // Super admin kan alles bewerken
        if ($user->role === 'super_admin') {
            Log::info('Edit access granted: super_admin');
            return true;
        }

        // Admin kan alles van eigen bedrijf bewerken
        if ($user->role === 'admin' && $project->company_id === $user->company_id) {
            Log::info('Edit access granted: admin of same company');
            return true;
        }

        // Project manager kan alleen toegewezen projecten bewerken
        if ($user->role === 'project_manager') {
            $isAssigned = $project->users->contains($user->id);
            Log::info('Checking project manager edit access', ['is_assigned' => $isAssigned]);
            return $isAssigned;
        }

        Log::info('Edit access denied: no matching criteria');
        return false;
    }

    /**
     * Check of gebruiker project mag verwijderen
     */
    private function canDeleteProject(Project $project): bool
    {
        $user = Auth::user();

        Log::info('Checking project delete access', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'user_role' => $user->role
        ]);

        // Alleen admin en super_admin kunnen verwijderen
        if (!in_array($user->role, ['super_admin', 'admin'])) {
            Log::info('Delete access denied: insufficient role');
            return false;
        }

        // Super admin kan alles verwijderen
        if ($user->role === 'super_admin') {
            Log::info('Delete access granted: super_admin');
            return true;
        }

        // Admin kan alleen eigen bedrijf verwijderen
        $sameCompany = $project->company_id === $user->company_id;
        Log::info('Delete access check for admin', ['same_company' => $sameCompany]);
        return $sameCompany;
    }

    /**
     * Calculate next billing date based on frequency
     */
    private function calculateNextBillingDate($frequency, $intervalDays, $startDate)
    {
        $date = \Carbon\Carbon::parse($startDate);
        
        switch ($frequency) {
            case 'monthly':
                return $date->addMonth();
            case 'quarterly':
                return $date->addMonths(3);
            case 'custom':
                return $intervalDays ? $date->addDays($intervalDays) : $date->addMonth();
            case 'milestone':
            case 'project_completion':
                return null; // Will be set when milestone/project is completed
            default:
                return $date->addMonth();
        }
    }

    /**
     * Import template structure into project
     */
    private function importTemplateStructure($project, $templateId)
    {
        try {
            $template = \App\Models\ProjectTemplate::with(['milestones.tasks'])->find($templateId);
            
            if (!$template) {
                Log::warning('Template not found for import', ['template_id' => $templateId]);
                return;
            }

            Log::info('Importing template structure', [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'milestone_count' => $template->milestones->count()
            ]);

            // Import milestones
            foreach ($template->milestones as $templateMilestone) {
                // Calculate dates based on template settings
                $startDate = $project->start_date ? 
                    \Carbon\Carbon::parse($project->start_date)->addDays($templateMilestone->days_from_start ?? 0) : 
                    now();
                $endDate = $startDate->copy()->addDays($templateMilestone->duration_days ?? 30);

                $milestoneData = [
                    'project_id' => $project->id,
                    'name' => $templateMilestone->name,
                    'description' => $templateMilestone->description,
                    'status' => 'pending',
                    'sort_order' => $templateMilestone->sort_order,
                    'fee_type' => 'in_fee',
                    'pricing_type' => 'hourly_rate',
                    'fixed_price' => null,
                    'hourly_rate_override' => $templateMilestone->default_hourly_rate,
                    'estimated_hours' => $templateMilestone->estimated_hours,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'source_type' => 'template',
                    'source_id' => $templateMilestone->id,
                ];

                $milestone = \App\Models\ProjectMilestone::create($milestoneData);

                Log::info('Milestone imported', [
                    'milestone_id' => $milestone->id,
                    'milestone_name' => $milestone->name
                ]);

                // Import tasks for this milestone
                if ($templateMilestone->tasks) {
                    foreach ($templateMilestone->tasks as $templateTask) {
                        $taskData = [
                            'project_milestone_id' => $milestone->id,
                            'name' => $templateTask->name,
                            'description' => $templateTask->description,
                            'status' => 'pending',
                            'sort_order' => $templateTask->sort_order,
                            'fee_type' => 'in_fee',
                            'pricing_type' => 'hourly_rate',
                            'fixed_price' => null,
                            'hourly_rate_override' => null,
                            'estimated_hours' => $templateTask->estimated_hours,
                            'source_type' => 'template',
                            'source_id' => $templateTask->id,
                        ];

                        $task = \App\Models\ProjectTask::create($taskData);

                        // Subtasks have been removed from the system
                    }
                }
            }

            Log::info('Template structure imported successfully', [
                'project_id' => $project->id,
                'template_id' => $template->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to import template structure', [
                'error' => $e->getMessage(),
                'template_id' => $templateId,
                'project_id' => $project->id
            ]);
            // We don't throw the error to not break project creation
        }
    }

    /**
     * Get filtered project structure by month for AJAX calls
     */
    public function getMonthlyProjectStructure(Request $request, Project $project)
    {
        Log::info('ProjectController@getMonthlyProjectStructure called', [
            'project_id' => $project->id,
            'month' => $request->month,
            'user_id' => Auth::id()
        ]);

        // Authorization check
        if (!$this->canAccessProject($project)) {
            Log::warning('Access denied to monthly project structure', [
                'project_id' => $project->id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $month = $request->input('month', now()->format('Y-m'));
            $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $monthEnd = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            Log::info('Month filtering parameters', [
                'month' => $month,
                'month_start' => $monthStart->format('Y-m-d'),
                'month_end' => $monthEnd->format('Y-m-d')
            ]);

            // Get filtered milestones for the month
            $milestones = $project->milestones()
                ->where(function($query) use ($monthStart, $monthEnd) {
                    $query
                        // Milestone was created before or during this month
                        ->where('created_at', '<=', $monthEnd)
                        // AND milestone is not completed before this month starts
                        ->where(function($subQuery) use ($monthStart) {
                            $subQuery->whereNull('end_date')  // Not completed yet
                                    ->orWhere('end_date', '>=', $monthStart);  // Or completed during/after this month
                        });
                })
                ->with(['tasks' => function($taskQuery) use ($monthStart, $monthEnd) {
                    $taskQuery->where(function($query) use ($monthStart, $monthEnd) {
                        $query
                            // Task was created before or during this month
                            ->where('created_at', '<=', $monthEnd)
                            // AND task is not completed before this month starts
                            ->where(function($subQuery) use ($monthStart) {
                                $subQuery->whereNull('end_date')  // Not completed yet
                                        ->orWhere('end_date', '>=', $monthStart);  // Or completed during/after this month
                            });
                    })
                    ->with(['timeEntries' => function($timeQuery) use ($monthStart, $monthEnd) {
                        $timeQuery->where('status', 'approved')
                                 ->whereBetween('entry_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                    }])
                    ->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();

            Log::info('Monthly milestones filtered', [
                'month' => $month,
                'total_milestones' => $milestones->count(),
                'milestone_names' => $milestones->pluck('name')->toArray()
            ]);

            // Calculate stats for this month
            $stats = [
                'total_milestones' => $milestones->count(),
                'completed_milestones' => $milestones->where('status', 'completed')->count(),
                'in_progress_milestones' => $milestones->where('status', 'in_progress')->count(),
                'total_tasks' => $milestones->sum(function($milestone) {
                    return $milestone->tasks->count();
                }),
                'completed_tasks' => $milestones->sum(function($milestone) {
                    return $milestone->tasks->where('status', 'completed')->count();
                }),
                'in_progress_tasks' => $milestones->sum(function($milestone) {
                    return $milestone->tasks->where('status', 'in_progress')->count();
                }),
                'month_display' => $monthStart->format('F Y'),
            ];

            // Add monthly costs to each task
            $milestonesWithCosts = $milestones->map(function($milestone) {
                $tasksWithCosts = $milestone->tasks->map(function($task) {
                    // Calculate monthly costs for this task
                    $monthlyCost = $task->timeEntries->sum(function($entry) {
                        return $entry->hours * ($entry->hourly_rate_used ?? 0);
                    });
                    
                    $monthlyHours = $task->timeEntries->sum('hours');
                    
                    
                    // Add calculated fields to task
                    $taskArray = $task->toArray();
                    $taskArray['monthly_cost'] = round($monthlyCost, 2);
                    $taskArray['monthly_hours'] = round($monthlyHours, 2);
                    
                    return $taskArray;
                });
                
                // Calculate milestone totals (sum of all task costs)
                $milestoneMonthlyCost = $tasksWithCosts->sum('monthly_cost');
                $milestoneMonthlyHours = $tasksWithCosts->sum('monthly_hours');
                
                // Add tasks with costs to milestone
                $milestoneArray = $milestone->toArray();
                $milestoneArray['tasks'] = $tasksWithCosts;
                $milestoneArray['monthly_cost'] = round($milestoneMonthlyCost, 2);
                $milestoneArray['monthly_hours'] = round($milestoneMonthlyHours, 2);
                
                return $milestoneArray;
            });

            return response()->json([
                'success' => true,
                'month' => $month,
                'month_display' => $stats['month_display'],
                'milestones' => $milestonesWithCosts,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting monthly project structure', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'month' => $request->month,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load monthly project structure',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import service structure into project
     * Service items hebben altijd fixed prices
     */
    public function importService(Request $request, Project $project)
    {
        Log::info('Starting service import', [
            'project_id' => $project->id,
            'service_id' => $request->service_id
        ]);

        // Validation
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'custom_name' => 'nullable|string|max:255',
            'service_color' => 'nullable|string|in:blue,green,yellow,red,purple,indigo,pink,gray'
        ]);

        try {
            DB::beginTransaction();

            // Haal service op met alle relaties
            $service = \App\Models\Service::with(['milestones.tasks'])->find($validated['service_id']);
            
            if (!$service) {
                Log::error('Service not found', ['service_id' => $validated['service_id']]);
                return response()->json(['error' => 'Service not found'], 404);
            }
            
            // Debug logging
            Log::info('Service loaded for import', [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'milestones_count' => $service->milestones->count(),
                'first_milestone' => $service->milestones->first() ? $service->milestones->first()->name : 'none'
            ]);

            // Bepaal de aangepaste naam en kleur
            $customName = $validated['custom_name'] ?? $service->name;
            $serviceColor = $validated['service_color'] ?? 'blue'; // Default blauw (Tailwind class naam)

            Log::info('Importing service structure', [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'custom_name' => $customName,
                'milestone_count' => $service->milestones->count()
            ]);

            // Eerst registreren we de service in project_services
            $projectService = DB::table('project_services')->insertGetId([
                'project_id' => $project->id,
                'service_id' => $service->id,
                'custom_name' => $customName,
                'quantity' => 1,
                'unit_price' => $service->total_price,
                'total_price' => $service->total_price,
                'import_status' => 'imported',
                'added_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Bepaal de volgende beschikbare sort_order voor service milestones
            // We plaatsen ze tussen de bestaande milestones
            $maxSortOrder = $project->milestones()->max('sort_order') ?? 0;
            $nextSortOrder = $maxSortOrder + 1;
            
            // Check if service has milestones
            if ($service->milestones->count() == 0) {
                Log::warning('Service has no milestones to import', [
                    'service_id' => $service->id,
                    'service_name' => $service->name
                ]);
            }
            
            // Import service milestones
            $importedMilestones = 0;
            foreach ($service->milestones as $index => $serviceMilestone) {
                Log::info('Importing service milestone', [
                    'index' => $index,
                    'milestone_name' => $serviceMilestone->name,
                    'milestone_id' => $serviceMilestone->id,
                    'tasks_count' => $serviceMilestone->tasks ? $serviceMilestone->tasks->count() : 0
                ]);
                
                // Calculate milestone price based on proportion of total hours
                $milestonePrice = 0;
                if ($service->estimated_hours > 0 && $serviceMilestone->estimated_hours > 0) {
                    $milestonePrice = ($serviceMilestone->estimated_hours / $service->estimated_hours) * $service->total_price;
                }
                
                $milestoneData = [
                    'project_id' => $project->id,
                    'name' => $serviceMilestone->name . ' [' . $customName . ']',
                    'description' => $serviceMilestone->description,
                    'status' => 'pending',
                    'sort_order' => $nextSortOrder + $index, // Volgende beschikbare positie
                    'fee_type' => $serviceMilestone->included_in_price ? 'in_fee' : 'extended',
                    'pricing_type' => 'fixed_price', // Services zijn altijd fixed price
                    'fixed_price' => round($milestonePrice, 2),
                    'hourly_rate_override' => null,
                    'estimated_hours' => $serviceMilestone->estimated_hours ?? 0,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'source_type' => 'service',
                    'source_id' => $serviceMilestone->id,
                    'is_service_item' => true,
                    'service_name' => $customName,
                    'service_color' => $serviceColor,
                    'original_service_id' => $service->id,
                ];

                $milestone = \App\Models\ProjectMilestone::create($milestoneData);
                $importedMilestones++;

                Log::info('Service milestone imported', [
                    'milestone_id' => $milestone->id,
                    'milestone_name' => $milestone->name,
                    'project_id' => $milestone->project_id
                ]);

                // Import service tasks
                if ($serviceMilestone->tasks) {
                    foreach ($serviceMilestone->tasks as $serviceTask) {
                        $taskData = [
                            'project_milestone_id' => $milestone->id,
                            'name' => $serviceTask->name,
                            'description' => $serviceTask->description,
                            'status' => 'pending',
                            'sort_order' => $serviceTask->sort_order,
                            'fee_type' => 'in_fee',
                            'pricing_type' => 'hourly_rate', // Tasks use hourly rate
                            'fixed_price' => null,
                            'hourly_rate_override' => null,
                            'estimated_hours' => $serviceTask->estimated_hours ?? 0,
                            'source_type' => 'service',
                            'source_id' => $serviceTask->id,
                            'is_service_item' => true,
                            'service_name' => $customName,
                            'service_color' => $serviceColor,
                            'original_service_id' => $service->id,
                        ];

                        $task = \App\Models\ProjectTask::create($taskData);

                        // Subtasks have been removed from the system
                    }
                }
            }

            DB::commit();

            Log::info('Service structure imported successfully', [
                'project_id' => $project->id,
                'service_id' => $service->id,
                'custom_name' => $customName,
                'imported_milestones' => $importedMilestones,
                'total_milestones_in_service' => $service->milestones->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service imported successfully',
                'service_name' => $customName
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to import service structure', [
                'error' => $e->getMessage(),
                'service_id' => $validated['service_id'],
                'project_id' => $project->id
            ]);
            
            return response()->json([
                'error' => 'Failed to import service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get milestone details for popup modal
     */
    public function getMilestoneDetails($milestoneId)
    {
        try {
            $milestone = \App\Models\ProjectMilestone::with(['project', 'tasks'])
                ->findOrFail($milestoneId);
            
            // Authorization check - user must have access to the project
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && 
                $milestone->project->company_id !== Auth::user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $html = view('projects.partials.milestone-details', ['milestone' => $milestone])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting milestone details', [
                'milestone_id' => $milestoneId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading milestone details'
            ], 500);
        }
    }

    /**
     * Get task details for popup modal
     */
    public function getTaskDetails($taskId, Request $request)
    {
        try {
            $task = \App\Models\ProjectTask::with(['milestone.project', 'timeEntries.user'])
                ->findOrFail($taskId);
            
            // Authorization check - user must have access to the project
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && 
                $task->milestone->project->company_id !== Auth::user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Get selected month from request, default to current month
            $selectedMonth = $request->input('month', now()->format('Y-m'));

            $html = view('projects.partials.task-details', [
                'task' => $task,
                'selectedMonth' => $selectedMonth
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting task details', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading task details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task details from popup
     */
    public function updateTaskDetails($taskId, Request $request)
    {
        try {
            $task = \App\Models\ProjectTask::with(['milestone.project'])
                ->findOrFail($taskId);
            
            // Authorization check - user must have access to the project and permission to edit
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only administrators and project managers can edit tasks.'
                ], 403);
            }
            
            if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && 
                $task->milestone->project->company_id !== Auth::user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed,on_hold',
                'sort_order' => 'nullable|integer|min:0',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'fee_type' => 'required|in:in_fee,extended',
                'pricing_type' => 'required|in:hourly_rate,fixed_price',
                'fixed_price' => 'nullable|numeric|min:0',
                'hourly_rate_override' => 'nullable|numeric|min:0',
                'estimated_hours' => 'nullable|numeric|min:0'
            ]);

            // Update task
            $task->update($validated);

            Log::info('Task updated via popup', [
                'task_id' => $taskId,
                'user_id' => Auth::id(),
                'changes' => $validated
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error updating task details', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update basic project information (inline edit)
     */
    public function updateBasicInfo(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Company isolation for non-super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'status' => 'required|in:draft,active,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            // Financial fields
            'monthly_fee' => 'nullable|numeric|min:0',
            'default_hourly_rate' => 'nullable|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'billing_frequency' => 'nullable|in:monthly,quarterly,milestone,project_completion,custom',
            'billing_interval_days' => 'nullable|integer|min:1',
            'fee_rollover_enabled' => 'nullable|boolean',
            'fee_start_date' => 'nullable|date',
        ]);

        try {
            $project->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Project information updated successfully',
                'project' => $project->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update project basic info', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update project information'
            ], 500);
        }
    }

    /**
     * Update recurring settings for a master project
     */
    public function updateRecurringSettings(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can update recurring settings.');
        }

        // Company isolation for non-super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. This project does not belong to your company.');
        }

        // Check if this is a recurring master project
        if (!$project->is_recurring) {
            return back()->with('error', 'This project is not a recurring master project.');
        }

        $validated = $request->validate([
            'recurring_base_name' => 'required|string|max:255',
            'recurring_frequency' => 'required|in:monthly,quarterly',
            'recurring_days_before' => 'nullable|integer|min:1|max:30',
            'recurring_end_date' => 'nullable|date|after:today',
            'recurring_series_id' => 'nullable|string|max:50',
            'disable_recurring' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Als disable_recurring is aangevinkt, zet is_recurring op false
            if ($request->has('disable_recurring') && $request->disable_recurring) {
                $project->update([
                    'is_recurring' => false,
                    'recurring_base_name' => null,
                    'recurring_frequency' => null,
                    'recurring_days_before' => null,
                    'recurring_end_date' => null,
                    'recurring_series_id' => null,
                ]);

                Log::info('Recurring project disabled', [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'user_id' => Auth::id()
                ]);

                DB::commit();

                return redirect()->route('projects.show', $project)
                    ->with('success', 'Recurring has been disabled. No new projects will be generated automatically.');
            }

            // Update recurring settings
            $project->update([
                'recurring_base_name' => $validated['recurring_base_name'],
                'recurring_frequency' => $validated['recurring_frequency'],
                'recurring_days_before' => $validated['recurring_days_before'] ?? 7,
                'recurring_end_date' => $validated['recurring_end_date'] ?? null,
                'recurring_series_id' => $validated['recurring_series_id'] ?? null,
            ]);

            // Als geen series_id is opgegeven, auto-generate
            if (empty($project->recurring_series_id)) {
                $project->recurring_series_id = 'series-' . $project->id;
                $project->save();
                Log::info('Auto-generated recurring_series_id on update', ['series_id' => $project->recurring_series_id]);
            }

            Log::info('Recurring settings updated', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'settings' => $validated,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('projects.show', $project)
                ->with('success', 'Recurring settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update recurring settings', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to update recurring settings: ' . $e->getMessage());
        }
    }

    /**
     * Link een handmatig project aan een recurring master
     */
    public function linkToRecurring(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can link projects.');
        }

        // Company isolation for non-super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. This project does not belong to your company.');
        }

        // Check if project is not already linked or is a master
        if ($project->is_recurring) {
            return back()->with('error', 'Cannot link a recurring master project to another master.');
        }

        if ($project->parent_recurring_project_id) {
            return back()->with('error', 'This project is already linked to a recurring master.');
        }

        $validated = $request->validate([
            'parent_recurring_project_id' => 'required|exists:projects,id',
        ]);

        try {
            DB::beginTransaction();

            // Verify that the parent is a recurring master
            $parentProject = Project::findOrFail($validated['parent_recurring_project_id']);

            if (!$parentProject->is_recurring) {
                return back()->with('error', 'The selected project is not a recurring master.');
            }

            if ($parentProject->customer_id !== $project->customer_id) {
                return back()->with('error', 'Parent project must belong to the same customer.');
            }

            // Link the project to the master
            $project->update([
                'parent_recurring_project_id' => $validated['parent_recurring_project_id'],
            ]);

            Log::info('Project linked to recurring master', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'parent_id' => $parentProject->id,
                'parent_name' => $parentProject->name,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project successfully linked to recurring master: ' . $parentProject->recurring_base_name);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to link project to recurring master', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to link project: ' . $e->getMessage());
        }
    }

    /**
     * Update recurring_series_id voor een project (ook standalone projects)
     */
    public function updateSeriesId(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can update series ID.');
        }

        // Company isolation for non-super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. This project does not belong to your company.');
        }

        $validated = $request->validate([
            'recurring_series_id' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $oldSeriesId = $project->recurring_series_id;
            $newSeriesId = $validated['recurring_series_id'] ?? null;

            // Update series ID
            $project->update([
                'recurring_series_id' => $newSeriesId,
            ]);

            $message = 'Project series updated successfully.';

            if (empty($newSeriesId)) {
                $message = 'Project removed from recurring series.';
            } elseif (empty($oldSeriesId)) {
                $message = 'Project added to series: ' . $newSeriesId;
            } else {
                $message = 'Project series changed from "' . $oldSeriesId . '" to "' . $newSeriesId . '"';
            }

            Log::info('Project series ID updated', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'old_series_id' => $oldSeriesId,
                'new_series_id' => $newSeriesId,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('projects.show', $project)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update project series ID', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to update series: ' . $e->getMessage());
        }
    }

    /**
     * Toggle master template status voor een project
     */
    public function toggleMasterTemplate(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators can manage master templates.');
        }

        // Company isolation voor non-super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. This project does not belong to your company.');
        }

        // Project moet een recurring_series_id hebben om master template te kunnen zijn
        if (!$project->recurring_series_id) {
            return back()->with('error', 'Project must be part of a recurring series to become a master template.');
        }

        try {
            DB::beginTransaction();

            $wasMaster = $project->is_master_template;
            $newStatus = !$wasMaster;

            // Als we dit project master maken, verwijder master status van andere projecten in deze serie
            if ($newStatus) {
                Project::where('recurring_series_id', $project->recurring_series_id)
                    ->where('id', '!=', $project->id)
                    ->update(['is_master_template' => false]);

                $message = '✅ Project is now the Master Template for series: ' . $project->recurring_series_id;
            } else {
                $message = 'Master Template status removed from project.';
            }

            // Update dit project
            $project->update([
                'is_master_template' => $newStatus,
                'is_recurring' => $newStatus, // Als master, ook recurring
            ]);

            Log::info('Master template status toggled', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'series_id' => $project->recurring_series_id,
                'was_master' => $wasMaster,
                'is_master' => $newStatus,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('projects.show', $project)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to toggle master template status', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to update master template status: ' . $e->getMessage());
        }
    }

    /**
     * Update financial project settings (inline edit)
     */
    public function updateFinancial(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Company isolation for non-super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'monthly_fee' => 'nullable|numeric|min:0',
            'default_hourly_rate' => 'nullable|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'billing_frequency' => 'nullable|in:monthly,quarterly,milestone,project_completion,custom',
            'billing_interval_days' => 'nullable|integer|min:1',
            'fee_rollover_enabled' => 'nullable|boolean',
            'fee_start_date' => 'nullable|date',
        ]);

        try {
            $project->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Financial settings updated successfully',
                'project' => $project->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update project financial info', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update financial settings'
            ], 500);
        }
    }

    // ========================================
    // BULK ACTIONS & SOFT DELETE
    // ========================================

    /**
     * Handle bulk actions voor projecten
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        Log::info('ProjectController@bulkAction called', [
            'user_id' => Auth::id(),
            'action' => $request->action,
            'project_count' => count($request->project_ids ?? [])
        ]);

        // Authorization check - alleen admin rollen kunnen bulk actions uitvoeren
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            Log::warning('Bulk action access denied', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role
            ]);
            abort(403, 'Access denied. Only administrators can perform bulk actions.');
        }

        // Validation
        $validated = $request->validate([
            'action' => 'required|in:activate,pause,delete,status_change',
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'exists:projects,id',
            'status' => 'required_if:action,status_change|in:draft,active,on_hold,completed,cancelled'
        ]);

        try {
            DB::beginTransaction();

            // Haal projects op met company isolation
            $query = Project::whereIn('id', $validated['project_ids']);

            // Company isolation voor non-super_admin
            if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
                $query->where('company_id', Auth::user()->company_id);
            }

            $projects = $query->get();

            if ($projects->isEmpty()) {
                Log::warning('No projects found for bulk action', [
                    'project_ids' => $validated['project_ids']
                ]);
                return back()->with('error', 'No projects found or access denied.');
            }

            $count = $projects->count();
            $action = $validated['action'];

            // Voer bulk action uit
            switch ($action) {
                case 'activate':
                    $projects->each(function($project) {
                        $project->update(['status' => 'active']);
                    });
                    $message = "{$count} project(s) activated successfully.";
                    break;

                case 'pause':
                    $projects->each(function($project) {
                        $project->update(['status' => 'on_hold']);
                    });
                    $message = "{$count} project(s) paused successfully.";
                    break;

                case 'delete':
                    // Soft delete
                    $projects->each(function($project) {
                        $project->delete(); // Dit is soft delete dankzij SoftDeletes trait
                    });
                    $message = "{$count} project(s) moved to trash successfully.";
                    break;

                case 'status_change':
                    $newStatus = $validated['status'];
                    $projects->each(function($project) use ($newStatus) {
                        $project->update(['status' => $newStatus]);
                    });

                    // Friendly status namen voor message
                    $statusNames = [
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled'
                    ];

                    $statusName = $statusNames[$newStatus] ?? $newStatus;
                    $message = "{$count} project(s) status changed to {$statusName}.";
                    break;

                default:
                    DB::rollback();
                    return back()->with('error', 'Invalid action.');
            }

            DB::commit();

            Log::info('Bulk action completed successfully', [
                'action' => $action,
                'count' => $count,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('projects.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk action failed', [
                'error' => $e->getMessage(),
                'action' => $request->action,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error performing bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Toon overzicht van verwijderde projecten
     */
    public function deleted(): View
    {
        Log::info('ProjectController@deleted called', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role
        ]);

        // Authorization check - alleen admin rollen kunnen deleted projects zien
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            Log::warning('Access to deleted projects denied', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role
            ]);
            abort(403, 'Access denied. Only administrators can view deleted projects.');
        }

        try {
            // Query voor soft deleted projects
            $query = Project::onlyTrashed()
                ->with(['customer', 'companyRelation']);

            // Company isolation voor non-super_admin
            if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
                $query->where('company_id', Auth::user()->company_id);
            }

            $deletedProjects = $query->orderBy('deleted_at', 'desc')->paginate(15);

            Log::info('Deleted projects loaded', [
                'count' => $deletedProjects->total()
            ]);

            return view('projects.deleted', compact('deletedProjects'));

        } catch (\Exception $e) {
            Log::error('Error loading deleted projects', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Restore soft deleted project
     */
    public function restore($id): RedirectResponse
    {
        Log::info('ProjectController@restore called', [
            'project_id' => $id,
            'user_id' => Auth::id()
        ]);

        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            Log::warning('Restore access denied', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role
            ]);
            abort(403, 'Access denied. Only administrators can restore projects.');
        }

        try {
            // Haal soft deleted project op
            $query = Project::onlyTrashed()->where('id', $id);

            // Company isolation voor non-super_admin
            if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
                $query->where('company_id', Auth::user()->company_id);
            }

            $project = $query->first();

            if (!$project) {
                Log::warning('Project not found in trash', [
                    'project_id' => $id
                ]);
                return back()->with('error', 'Project not found or access denied.');
            }

            // Restore project
            $project->restore();

            Log::info('Project restored successfully', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'restored_by' => Auth::id()
            ]);

            return redirect()->route('projects.index')
                ->with('success', 'Project "' . $project->name . '" restored successfully!');

        } catch (\Exception $e) {
            Log::error('Project restore failed', [
                'error' => $e->getMessage(),
                'project_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error restoring project: ' . $e->getMessage());
        }
    }

    /**
     * Haal time entries op voor een project (AJAX)
     */
    public function getTimeEntries(Project $project)
    {
        // Authorization check - alleen eigen company projects of super_admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        // Haal time entries op voor dit project
        $timeEntries = $project->timeEntries()
            ->with(['user', 'milestone', 'task', 'subtask'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Bereken totalen
        $totalHours = $timeEntries->sum('hours');
        $totalMinutes = $timeEntries->sum('minutes');
        $totalDuration = $totalHours + ($totalMinutes / 60);

        // Bereken uren per user (gesorteerd op meeste uren eerst)
        $userStats = $timeEntries->groupBy('user_id')->map(function($entries, $userId) {
            $userHours = $entries->sum('hours');
            $userMinutes = $entries->sum('minutes');
            $totalUserMinutes = ($userHours * 60) + $userMinutes;
            $totalUserHours = floor($totalUserMinutes / 60);
            $remainingMinutes = $totalUserMinutes % 60;

            return [
                'user_id' => $userId,
                'user_name' => $entries->first()->user->name ?? 'Unknown',
                'entries_count' => $entries->count(),
                'total_hours' => $totalUserHours,
                'total_minutes' => $remainingMinutes,
                'total_duration_decimal' => $totalUserMinutes / 60,
                'duration_formatted' => sprintf('%d:%02d', $totalUserHours, $remainingMinutes),
            ];
        })->sortByDesc('total_duration_decimal')->values();

        return response()->json([
            'success' => true,
            'entries' => $timeEntries->map(function($entry) {
                // Convert decimale hours naar uren:minuten formaat
                // Hours kan decimaal zijn (bijv. 0.50 = 30 min, 1.50 = 1u 30min, 0.33 = 20 min)
                // BELANGRIJK: Round naar hele minuten om 19.8 → 20 te maken
                $totalMinutes = round(($entry->hours * 60) + $entry->minutes);
                $displayHours = floor($totalMinutes / 60);
                $displayMinutes = $totalMinutes % 60;

                return [
                    'id' => $entry->id,
                    'entry_date' => $entry->entry_date->format('d-m-Y'),
                    'user' => $entry->user->name ?? 'Unknown',
                    'hours' => $displayHours,
                    'minutes' => $displayMinutes,
                    'duration_formatted' => sprintf('%d:%02d', $displayHours, $displayMinutes),
                    'description' => $entry->description ?? '-',
                    'milestone' => $entry->milestone->name ?? '-',
                    'task' => $entry->task->name ?? '-',
                    'subtask' => $entry->subtask->name ?? '-',
                    'is_billable' => $entry->is_billable === 'billable',
                    'status' => $entry->status,
                ];
            }),
            'stats' => [
                'total_entries' => $timeEntries->count(),
                'total_hours' => floor($totalDuration),
                'total_minutes' => ($totalMinutes + ($totalHours * 60)) % 60,
                'total_duration_formatted' => sprintf('%d:%02d', floor($totalDuration), ($totalMinutes + ($totalHours * 60)) % 60),
            ],
            'user_stats' => $userStats
        ]);
    }

    /**
     * Haal time entries op voor een specifieke task of milestone (AJAX)
     */
    public function getTaskTimeEntries(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        $type = $request->input('type'); // 'milestone' of 'task'
        $id = $request->input('id');

        // Build query based on type
        $query = $project->timeEntries()->with(['user', 'milestone', 'task', 'subtask']);

        if ($type === 'milestone') {
            // Voor milestone: ALLE entries (direct + alle tasks onder deze milestone)
            $query->where('project_milestone_id', $id);
            $item = $project->milestones()->find($id);
            $itemName = $item ? $item->name : 'Unknown Milestone';
        } elseif ($type === 'task') {
            $query->where('project_task_id', $id);
            $item = \App\Models\ProjectTask::find($id);
            $itemName = $item ? $item->name : 'Unknown Task';
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $timeEntries = $query->orderBy('entry_date', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // Bereken totalen met correcte afronding
        $totalMinutes = $timeEntries->sum(function($entry) {
            return round(($entry->hours * 60) + $entry->minutes);
        });
        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        return response()->json([
            'success' => true,
            'item_name' => $itemName,
            'item_type' => $type,
            'entries' => $timeEntries->map(function($entry) {
                $entryMinutes = round(($entry->hours * 60) + $entry->minutes);
                $displayHours = floor($entryMinutes / 60);
                $displayMinutes = $entryMinutes % 60;

                return [
                    'id' => $entry->id,
                    'entry_date' => $entry->entry_date->format('d-m-Y'),
                    'user' => $entry->user->name ?? 'Unknown',
                    'hours' => $displayHours,
                    'minutes' => $displayMinutes,
                    'duration_formatted' => sprintf('%d:%02d', $displayHours, $displayMinutes),
                    'description' => $entry->description ?? '-',
                    'milestone' => $entry->milestone->name ?? '-',
                    'task' => $entry->task->name ?? '-',
                    'subtask' => $entry->subtask->name ?? '-',
                    'is_billable' => $entry->is_billable === 'billable',
                    'status' => $entry->status,
                ];
            }),
            'stats' => [
                'total_entries' => $timeEntries->count(),
                'total_hours' => $totalHours,
                'total_minutes' => $remainingMinutes,
                'total_duration_formatted' => sprintf('%d:%02d', $totalHours, $remainingMinutes),
            ]
        ]);
    }

    /**
     * Toon year budget overview met alle 12 maanden en rollover flow
     */
    public function yearBudget(Request $request, Project $project): View|RedirectResponse
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can view budget details.');
        }

        // Company isolation
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view projects from your own company.');
        }

        // Get year from request or use current year
        $year = $request->input('year', now()->year);

        // Check if this project is part of a recurring series
        // If so, redirect to series budget view for consolidated tracking
        if ($project->recurring_series_id) {
            // Get ALL projects in this series
            $seriesProjects = Project::where('recurring_series_id', $project->recurring_series_id)
                ->orderBy('start_date')
                ->get();

            // If there are multiple projects in the series, use series budget view
            if ($seriesProjects->count() > 1) {
                Log::info('Redirecting to series budget view', [
                    'project_id' => $project->id,
                    'series_id' => $project->recurring_series_id,
                    'projects_count' => $seriesProjects->count()
                ]);

                // Redirect to series-budget for proper consolidated view
                return redirect()->route('projects.series-budget', [
                    'project' => $project->id,
                    'year' => $year
                ]);
            }
        }

        // Initialize budget service
        $budgetService = new ProjectBudgetService();

        // Check for linked projects (parent-child relationship) - legacy support
        // Als dit een parent project is, haal alle children op
        $linkedProjects = collect([$project]); // Start met parent

        $childProjects = Project::where('parent_recurring_project_id', $project->id)
            ->orderBy('start_date')
            ->get();

        if ($childProjects->count() > 0) {
            $linkedProjects = $linkedProjects->merge($childProjects);
            Log::info('Found linked projects', [
                'parent_id' => $project->id,
                'children_count' => $childProjects->count(),
                'all_projects' => $linkedProjects->pluck('id', 'name')
            ]);
        }

        // Determine active months based on project dates
        // Voor recurring budget projects: toon vanaf start tot huidige maand
        $projectStartDate = $project->start_date ? \Carbon\Carbon::parse($project->start_date) : null;
        $currentDate = \Carbon\Carbon::now();

        // Bepaal start maand voor dit jaar
        if ($projectStartDate && $projectStartDate->year == $year) {
            $startMonth = $projectStartDate->month;
        } elseif ($projectStartDate && $projectStartDate->year < $year) {
            $startMonth = 1; // Start van jaar als project al eerder begon
        } else {
            // Project start in de toekomst
            $startMonth = 1;
            $endMonth = 0; // Geen maanden om te tonen
        }

        // Bepaal end maand voor dit jaar
        // Voor recurring budget: toon tot huidige maand (budget loopt door!)
        if ($year == $currentDate->year) {
            $endMonth = $currentDate->month;
        } elseif ($year < $currentDate->year) {
            // Vorige jaren: toon heel het jaar
            $endMonth = 12;
        } else {
            // Toekomstige jaren: nog geen data
            $endMonth = 0;
        }

        // Als project nog niet is gestart in dit jaar, skip
        if ($projectStartDate && $projectStartDate->year > $year) {
            $startMonth = 1;
            $endMonth = 0; // Geen maanden om te tonen
        }

        // Build month data array (alleen actieve maanden)
        $monthsData = [];

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            // Find het juiste project voor deze maand (parent of child)
            $monthProject = $project; // Default: gebruik parent

            // Zoek child project met start_date in deze maand
            foreach ($linkedProjects as $linkedProject) {
                $linkedStartDate = $linkedProject->start_date ? \Carbon\Carbon::parse($linkedProject->start_date) : null;
                if ($linkedStartDate && $linkedStartDate->year == $year && $linkedStartDate->month == $month) {
                    $monthProject = $linkedProject;
                    Log::debug('Using child project for month', [
                        'month' => $month,
                        'project_id' => $linkedProject->id,
                        'project_name' => $linkedProject->name
                    ]);
                    break;
                }
            }

            // Get or create monthly fee record (gebruik PARENT voor rollover continuïteit)
            $monthlyFee = \App\Models\ProjectMonthlyFee::getOrCreateForPeriod($project, $year, $month);

            // Als niet finalized, herbereken met data van het JUISTE child project
            if (!$monthlyFee->is_finalized) {
                try {
                    // Voor linked projects: haal time entries van het juiste child project
                    if ($monthProject->id != $project->id) {
                        // Dit is een child project - haal time entries daarvan op
                        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
                        $endDate = $startDate->copy()->endOfMonth();

                        $timeEntries = \App\Models\TimeEntry::where('project_id', $monthProject->id)
                            ->where('status', 'approved')
                            ->whereBetween('entry_date', [$startDate, $endDate])
                            ->get();

                        $totalHours = 0;
                        $totalCosts = 0;

                        foreach ($timeEntries as $entry) {
                            $entryHours = $entry->hours + ($entry->minutes / 60);
                            $totalHours += $entryHours;

                            if ($entry->is_billable === 'billable') {
                                $hourlyRate = $entry->hourly_rate_used ?? $monthProject->default_hourly_rate ?? 75;
                                $totalCosts += $entryHours * $hourlyRate;
                            }
                        }

                        // Manual update van monthly_fee met child project data
                        $previousMonth = $monthlyFee->getPreviousMonth();
                        $rolloverAmount = 0;
                        if ($previousMonth && $project->fee_rollover_enabled && $previousMonth->budget_remaining > 0) {
                            $rolloverAmount = $previousMonth->budget_remaining;
                        }

                        $monthlyBudget = $project->monthly_fee ?? 0;
                        $totalBudget = $monthlyBudget + $rolloverAmount;
                        $budgetRemaining = max(0, $totalBudget - $totalCosts);
                        $rolloverToNext = ($project->fee_rollover_enabled && $budgetRemaining > 0) ? $budgetRemaining : 0;

                        $monthlyFee->update([
                            'base_monthly_fee' => $monthlyBudget,
                            'rollover_from_previous' => $rolloverAmount,
                            'total_available_fee' => $totalBudget,
                            'hours_worked' => round($totalHours, 2),
                            'hours_value' => round($totalCosts, 2),
                            'amount_invoiced_from_fee' => round($totalCosts, 2),
                            'total_invoiced' => round($totalCosts, 2),
                            'rollover_to_next' => $rolloverToNext,
                        ]);

                        Log::info('Updated monthly fee with child project data', [
                            'parent_id' => $project->id,
                            'child_id' => $monthProject->id,
                            'month' => $month,
                            'hours' => $totalHours,
                            'costs' => $totalCosts
                        ]);
                    } else {
                        // Normaal parent project - gebruik bestaande service
                        $monthlyFee = $budgetService->calculateMonthlyBudget($monthProject, $year, $month);
                    }
                } catch (\Exception $e) {
                    Log::error('Error calculating monthly budget', [
                        'project_id' => $monthProject->id,
                        'year' => $year,
                        'month' => $month,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Bereken extra data
            $budgetUsed = $monthlyFee->amount_invoiced_from_fee ?? 0;
            $totalBudget = $monthlyFee->total_available_fee ?? 0;

            $remaining = max(0, $totalBudget - $budgetUsed);
            $exceeded = max(0, $budgetUsed - $totalBudget);

            // Status bepalen
            if ($exceeded > 0) {
                $status = 'exceeded';
                $statusClass = 'bg-red-100 text-red-800';
                $statusLabel = 'Over Budget';
            } elseif ($remaining > 0 && $budgetUsed > 0) {
                $percentage = ($budgetUsed / $totalBudget) * 100;
                if ($percentage > 80) {
                    $status = 'warning';
                    $statusClass = 'bg-yellow-100 text-yellow-800';
                    $statusLabel = 'Warning';
                } else {
                    $status = 'on_track';
                    $statusClass = 'bg-green-100 text-green-800';
                    $statusLabel = 'On Track';
                }
            } else {
                $status = 'not_started';
                $statusClass = 'bg-gray-100 text-gray-800';
                $statusLabel = 'Not Started';
            }

            $monthsData[] = [
                'month' => $month,
                'month_name' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('F'),
                'monthly_fee' => $monthlyFee,
                'base_monthly_fee' => $monthlyFee->base_monthly_fee ?? 0,
                'rollover_from_previous' => $monthlyFee->rollover_from_previous ?? 0,
                'total_budget' => $totalBudget,
                'budget_used' => $budgetUsed,
                'remaining' => $remaining,
                'exceeded' => $exceeded,
                'rollover_to_next' => $monthlyFee->rollover_to_next ?? 0,
                'hours_worked' => $monthlyFee->hours_worked ?? 0,
                'status' => $status,
                'status_class' => $statusClass,
                'status_label' => $statusLabel,
                'is_finalized' => $monthlyFee->is_finalized ?? false,
            ];
        }

        // Bereken totalen voor het jaar
        $yearTotals = [
            'total_base_budget' => array_sum(array_column($monthsData, 'base_monthly_fee')),
            'total_rollover_in' => array_sum(array_column($monthsData, 'rollover_from_previous')),
            'total_budget' => array_sum(array_column($monthsData, 'total_budget')),
            'total_used' => array_sum(array_column($monthsData, 'budget_used')),
            'total_remaining' => array_sum(array_column($monthsData, 'remaining')),
            'total_exceeded' => array_sum(array_column($monthsData, 'exceeded')),
            'total_hours' => array_sum(array_column($monthsData, 'hours_worked')),
        ];

        // Available years (huidige jaar +/- 2 jaar)
        $currentYear = now()->year;
        $availableYears = range($currentYear - 2, $currentYear + 2);

        // Check of dit project deel is van een recurring series
        $isPartOfSeries = false;
        $seriesParentId = null;

        // Fetch series projects for display (if applicable)
        $seriesProjects = collect([$project]); // Default: alleen dit project

        if ($project->recurring_series_id) {
            // Get all projects in this series
            $seriesProjects = Project::where('recurring_series_id', $project->recurring_series_id)
                ->orderBy('start_date')
                ->get();
            $isPartOfSeries = true;
        } elseif ($project->parent_recurring_project_id) {
            // Dit is een child project - haal alle siblings op
            $isPartOfSeries = true;
            $seriesParentId = $project->parent_recurring_project_id;

            // Get parent + all children
            $parentProject = Project::find($project->parent_recurring_project_id);
            if ($parentProject) {
                $seriesProjects = collect([$parentProject])->merge(
                    Project::where('parent_recurring_project_id', $project->parent_recurring_project_id)
                        ->orderBy('start_date')
                        ->get()
                );
            }
        } elseif ($childProjects->count() > 0) {
            // Dit is een parent project met children
            $isPartOfSeries = true;
            $seriesParentId = $project->id;
            $seriesProjects = collect([$project])->merge($childProjects);
        }

        return view('projects.year-budget', compact(
            'project',
            'year',
            'monthsData',
            'yearTotals',
            'availableYears',
            'isPartOfSeries',
            'seriesParentId',
            'seriesProjects'
        ));
    }

    /**
     * Herbereken alle maanden van een jaar (voor als er wijzigingen zijn)
     */
    public function recalculateYear(Request $request, Project $project): RedirectResponse
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can recalculate budgets.');
        }

        // Company isolation
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied.');
        }

        $year = $request->input('year', now()->year);

        try {
            DB::beginTransaction();

            $budgetService = new ProjectBudgetService();
            $recalculated = 0;

            // Herbereken alle 12 maanden
            for ($month = 1; $month <= 12; $month++) {
                $budgetService->calculateMonthlyBudget($project, $year, $month);
                $recalculated++;
            }

            DB::commit();

            Log::info('Year budget recalculated', [
                'project_id' => $project->id,
                'year' => $year,
                'months_recalculated' => $recalculated,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('projects.year-budget', ['project' => $project->id, 'year' => $year])
                ->with('success', "Successfully recalculated all {$recalculated} months for year {$year}");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error recalculating year budget', [
                'project_id' => $project->id,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error recalculating budget: ' . $e->getMessage());
        }
    }

    /**
     * Geconsolideerd budget overzicht voor recurring project series
     * Toont alle projecten in de series samen met gecombineerd budget tracking
     */
    public function seriesBudget(Request $request, Project $project): View|RedirectResponse
    {
        // Authorization check
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can view budget details.');
        }

        // Company isolation
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view projects from your own company.');
        }

        // Get year from request or use current year
        $year = $request->input('year', now()->year);

        // Get ALL projects in this series using recurring_series_id
        $seriesProjects = collect([]);

        if ($project->recurring_series_id) {
            // Use recurring_series_id to find ALL related projects
            $seriesProjects = Project::where('recurring_series_id', $project->recurring_series_id)
                ->orderBy('start_date')
                ->get();

            Log::info('Using recurring_series_id for series budget', [
                'project_id' => $project->id,
                'series_id' => $project->recurring_series_id,
                'projects_found' => $seriesProjects->count()
            ]);
        } else {
            // Legacy support: use parent_recurring_project_id
            // Determine if this is a parent or child project
            if ($project->parent_recurring_project_id) {
                $parentProject = Project::find($project->parent_recurring_project_id);
                if ($parentProject) {
                    return redirect()->route('projects.series-budget', [
                        'project' => $parentProject->id,
                        'year' => $year
                    ]);
                }
            }

            // Get projects using old parent-child method
            $seriesProjects = collect([$project]);
            $childProjects = Project::where('parent_recurring_project_id', $project->id)
                ->orderBy('start_date')
                ->get();

            if ($childProjects->count() > 0) {
                $seriesProjects = $seriesProjects->merge($childProjects);
            }
        }

        Log::info('Series Budget View', [
            'parent_id' => $project->id,
            'total_projects' => $seriesProjects->count(),
            'project_ids' => $seriesProjects->pluck('id')->toArray()
        ]);

        // Build consolidated month data voor ALLE projecten in de series
        $currentDate = \Carbon\Carbon::now();

        // Bepaal de VROEGSTE start datum van alle projecten in de series
        $earliestStartDate = null;
        $latestEndDate = null;
        foreach ($seriesProjects as $seriesProject) {
            if ($seriesProject->start_date) {
                $projectStart = \Carbon\Carbon::parse($seriesProject->start_date);
                if (!$earliestStartDate || $projectStart->lt($earliestStartDate)) {
                    $earliestStartDate = $projectStart;
                }
            }
            // Ook de laatste end_date bepalen (of start_date als end_date niet bestaat)
            if ($seriesProject->end_date) {
                $projectEnd = \Carbon\Carbon::parse($seriesProject->end_date);
                if (!$latestEndDate || $projectEnd->gt($latestEndDate)) {
                    $latestEndDate = $projectEnd;
                }
            } elseif ($seriesProject->start_date) {
                // Als er geen end_date is, gebruik start_date
                $projectStart = \Carbon\Carbon::parse($seriesProject->start_date);
                if (!$latestEndDate || $projectStart->gt($latestEndDate)) {
                    $latestEndDate = $projectStart;
                }
            }
        }

        // Als er geen start datum is, begin bij januari
        if (!$earliestStartDate) {
            $startMonth = 1;
        } elseif ($earliestStartDate->year == $year) {
            // Project start in dit jaar - begin bij die maand
            $startMonth = $earliestStartDate->month;
        } elseif ($earliestStartDate->year < $year) {
            // Project startte voor dit jaar - begin bij januari
            $startMonth = 1;
        } else {
            // Project start in de toekomst - geen maanden om te tonen
            $startMonth = 1;
            $endMonth = 0;
        }

        // KRITIEKE FIX (03-11-2025): Toon ALTIJD alle 12 maanden
        // Ook maanden zonder project moeten getoond worden met €0 budget
        $startMonth = 1;
        $endMonth = 12;

        $monthsData = [];
        $previousRollover = 0; // Track rollover across months

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $monthStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            // Aggregate data from ALL projects in series for this month
            $totalHours = 0;
            $totalCosts = 0;
            $projectsWithDataThisMonth = [];
            $activeProjectThisMonth = null; // Track which project is active this month

            foreach ($seriesProjects as $seriesProject) {
                // Check if this project was active in this month
                $projectStart = $seriesProject->start_date ? \Carbon\Carbon::parse($seriesProject->start_date) : null;
                $projectEnd = $seriesProject->end_date ? \Carbon\Carbon::parse($seriesProject->end_date) : null;

                // Check if project is active in this month
                $isActiveThisMonth = true;
                if ($projectStart && $projectStart->gt($monthEnd)) {
                    $isActiveThisMonth = false;
                }
                if ($projectEnd && $projectEnd->lt($monthStart)) {
                    $isActiveThisMonth = false;
                }

                // KRITIEKE FIX (03-11-2025): Track het actieve project voor deze maand
                // Dit project's monthly_fee wordt gebruikt als budget
                if ($isActiveThisMonth && !$activeProjectThisMonth) {
                    // Check if start_date is in this month (exact match)
                    if ($projectStart && $projectStart->month == $month && $projectStart->year == $year) {
                        $activeProjectThisMonth = $seriesProject;
                    }
                }

                if (!$isActiveThisMonth) {
                    continue;
                }

                // Get time entries for this project in this month
                $timeEntries = \App\Models\TimeEntry::where('project_id', $seriesProject->id)
                    ->where('status', 'approved')
                    ->whereBetween('entry_date', [$monthStart, $monthEnd])
                    ->get();

                if ($timeEntries->count() > 0) {
                    $projectsWithDataThisMonth[] = $seriesProject->name;
                }

                foreach ($timeEntries as $entry) {
                    $entryHours = $entry->hours + ($entry->minutes / 60);

                    // KRITIEKE FIX (03-11-2025): Alleen billable uren meetellen!
                    // Non-billable tijdregistraties mogen NIET meegeteld worden
                    if ($entry->is_billable === 'billable') {
                        $totalHours += $entryHours;  // Tel alleen billable uren mee!

                        $hourlyRate = $entry->hourly_rate_used ?? $seriesProject->default_hourly_rate ?? 75;
                        $totalCosts += $entryHours * $hourlyRate;
                    }
                }
            }

            // KRITIEKE FIX (03-11-2025): Rollover logica met "pending" voor maanden zonder project
            // Als er geen project is in een maand, blijft de rollover behouden voor de volgende maand
            // Rollover gaat door naar het eerst volgende actieve project
            if ($activeProjectThisMonth) {
                // Er is een actief project deze maand
                $monthlyBudget = $activeProjectThisMonth->monthly_fee ?? 0;

                // Pas rollover toe als rollover enabled is
                $rolloverIn = $activeProjectThisMonth->fee_rollover_enabled ? $previousRollover : 0;
                $totalBudget = $monthlyBudget + $rolloverIn;

                $remaining = max(0, $totalBudget - $totalCosts);
                $exceeded = max(0, $totalCosts - $totalBudget);

                // Bereken nieuwe rollover voor volgende maand (positief of negatief)
                $rolloverOut = $activeProjectThisMonth->fee_rollover_enabled ? ($totalBudget - $totalCosts) : 0;
                $previousRollover = $rolloverOut;
            } else {
                // Geen actief project deze maand
                // Rollover blijft "pending" en gaat door naar volgende maand
                $monthlyBudget = 0;
                $rolloverIn = 0; // Toon geen rollover in deze maand
                $totalBudget = 0;
                $remaining = 0;
                $exceeded = 0;

                // BELANGRIJK: previousRollover blijft ongewijzigd, zodat rollover doorgaat naar volgende maanden
                $rolloverOut = $previousRollover; // Rollover blijft behouden!
                // $previousRollover blijft hetzelfde voor volgende iteratie
            }

            // Determine status
            if ($exceeded > 0) {
                $statusClass = 'bg-red-100 text-red-800';
                $statusLabel = 'Over Budget';
            } elseif ($totalCosts > 0 && $totalBudget > 0) {
                $percentage = ($totalCosts / $totalBudget) * 100;
                if ($percentage > 80) {
                    $statusClass = 'bg-yellow-100 text-yellow-800';
                    $statusLabel = 'Warning';
                } else {
                    $statusClass = 'bg-green-100 text-green-800';
                    $statusLabel = 'On Track';
                }
            } else {
                $statusClass = 'bg-gray-100 text-gray-800';
                $statusLabel = 'Not Started';
            }

            $monthsData[] = [
                'month' => $month,
                'month_name' => $monthStart->format('F'),
                'base_monthly_fee' => $monthlyBudget,
                'rollover_from_previous' => $rolloverIn,
                'total_budget' => $totalBudget,
                'budget_used' => $totalCosts,
                'remaining' => $remaining,
                'exceeded' => $exceeded,
                'rollover_to_next' => $rolloverOut,
                'hours_worked' => $totalHours,
                'status_class' => $statusClass,
                'status_label' => $statusLabel,
                'projects_with_data' => $projectsWithDataThisMonth,
                'project_count' => count($projectsWithDataThisMonth),
            ];
        }

        // Calculate year totals
        $totalBaseBudget = array_sum(array_column($monthsData, 'base_monthly_fee'));
        $totalUsed = array_sum(array_column($monthsData, 'budget_used'));

        $yearTotals = [
            'total_base_budget' => $totalBaseBudget,
            // KRITIEKE FIX (03-11-2025): Rollover_in moet NIET opgeteld worden!
            // Rollover verschuift budget tussen maanden maar voegt GEEN nieuw geld toe.
            // Als je alle rollover_in bedragen optelt, tel je hetzelfde geld meerdere keren.
            'total_rollover_in' => 0, // Niet relevant voor totaal (intern verschuiving)
            // Total budget = gewoon de som van base budgets, NIET + rollover
            'total_budget' => $totalBaseBudget,
            'total_used' => $totalUsed,
            // KRITIEKE FIX (03-11-2025): Total exceeded moet berekend worden als:
            // "total used - total base budget" (als positief), niet als som van maandelijkse exceeded bedragen!
            // Dit geeft het ECHTE overspent bedrag voor het hele jaar.
            'total_remaining' => max(0, $totalBaseBudget - $totalUsed),
            'total_exceeded' => max(0, $totalUsed - $totalBaseBudget),
            'total_hours' => array_sum(array_column($monthsData, 'hours_worked')),
        ];

        // Available years
        $currentYear = now()->year;
        $availableYears = range($currentYear - 2, $currentYear + 2);

        return view('projects.series-budget', compact(
            'project',
            'seriesProjects',
            'year',
            'monthsData',
            'yearTotals',
            'availableYears'
        ));
    }
}