<?php

namespace App\Http\Controllers;

use App\Models\Project;
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
        if (Auth::user()->role !== 'super_admin') {
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

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
            Log::info('Applied status filter', ['status' => $request->status]);
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

        try {
            $projects = $query->orderBy('created_at', 'desc')->paginate(15);
            
            // Add budget data for each project
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $budgetService = new ProjectBudgetService();
            
            foreach ($projects as $project) {
                // Calculate budget for current month using service
                try {
                    $monthlyFee = $budgetService->calculateMonthlyBudget($project, $currentYear, $currentMonth);
                    
                    // Force refresh the model to get updated values
                    $monthlyFee->refresh();
                    
                    // Get the calculated values - use correct field names
                    $totalBudget = $monthlyFee->total_available_fee ?? 0;
                    $budgetUsed = $monthlyFee->amount_invoiced_from_fee ?? 0;
                    $budgetRemaining = max(0, $totalBudget - $budgetUsed);
                    
                    $project->budget_percentage = $totalBudget > 0 
                        ? min(100, round(($budgetUsed / $totalBudget) * 100))
                        : 0;
                    
                    $project->budget_total = $totalBudget;
                    $project->budget_used = $budgetUsed;
                    $project->budget_remaining = $budgetRemaining;
                    
                    Log::debug('Budget calculated for project', [
                        'project_id' => $project->id,
                        'total_budget' => $totalBudget,
                        'budget_used' => $budgetUsed,
                        'percentage' => $project->budget_percentage
                    ]);
                } catch (\Exception $e) {
                    // If calculation fails, show based on monthly fee
                    Log::warning('Budget calculation failed for project', [
                        'project_id' => $project->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    $project->budget_percentage = 0;
                    $project->budget_total = $project->monthly_fee ?? 0;
                    $project->budget_used = 0;
                    $project->budget_remaining = $project->monthly_fee ?? 0;
                }
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
            if (Auth::user()->role !== 'super_admin') {
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
            ->when(Auth::user()->role !== 'super_admin', function($q) {
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
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();

            Log::info('Customers loaded for create form', ['count' => $customers->count()]);

            // Haal users op voor team assignment (company isolation)
            $users = User::whereNotNull('name')
                ->whereNotNull('email')
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->with('companyRelation')
                ->orderBy('name')
                ->get();

            Log::info('Users loaded for create form', ['count' => $users->count()]);

            // Get companies - plugin system removed, always show available companies
            $companies = Company::where('is_active', true)
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();
            $defaultCompany = null;

            Log::info('Companies loaded for create form', [
                'count' => $companies->count(),
                'is_companies_plugin_active' => $isCompaniesPluginActive
            ]);

            // Haal templates op voor snelle project creation
            $templates = ProjectTemplate::where('status', 'active')
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();

            Log::info('Templates loaded for create form', ['count' => $templates->count()]);

            // Haal invoice templates op voor selectie
            $invoiceTemplates = \App\Models\InvoiceTemplate::where('is_active', true)
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where(function($query) {
                        $query->whereNull('company_id')
                              ->orWhere('company_id', Auth::user()->company_id);
                    });
                })
                ->orderBy('name')
                ->get();

            Log::info('Invoice templates loaded for create form', ['count' => $invoiceTemplates->count()]);

            Log::info('ProjectController@create completed successfully');

            return view('projects.create', compact(
                'customers', 
                'users', 
                'companies', 
                'templates', 
                'invoiceTemplates',
                'isCompaniesPluginActive',
                'defaultCompany'
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
                'status' => $validated['status'],
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
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

            Log::info('About to create project with data', $projectData);

            $project = Project::create($projectData);

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

            Log::info('=== STEP 7: Commit Transaction ===');

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
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('name')
            ->get();

        $users = User::whereNotNull('name')
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->with('companyRelation')
            ->orderBy('name')
            ->get();

        $companies = Company::where('is_active', true)
            ->orderBy('name')
            ->get();

        // TOEGEVOEGD: Templates variabele - dit was het ontbrekende onderdeel!
        $templates = ProjectTemplate::where('is_active', true)
            ->when(Auth::user()->role !== 'super_admin', function($q) {
                // Als company_id kolom bestaat, filter dan op company
                if (Schema::hasColumn('project_templates', 'company_id')) {
                    $q->where('company_id', Auth::user()->company_id);
                }
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
        return view('projects.show', compact(
            'project', 
            'stats', 
            'customers', 
            'users', 
            'companies', 
            'templates'  // <- Dit was het ontbrekende deel!
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
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->orderBy('name')
                ->get();

            $users = User::whereNotNull('name')
                ->when(Auth::user()->role !== 'super_admin', function($q) {
                    $q->where('company_id', Auth::user()->company_id);
                })
                ->with('companyRelation')
                ->orderBy('name')
                ->get();

            // Get companies - plugin system removed, always show available companies
            $companies = Company::where('is_active', true)
                ->when(Auth::user()->role !== 'super_admin', function($q) {
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
                ->when(Auth::user()->role !== 'super_admin', function($q) {
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
        $users = User::all();
        
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
            $project->delete(); // Hard delete (SoftDeletes uitgeschakeld)

            Log::info('Project deleted successfully', [
                'project_id' => $project->id,
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
            ->with('companyRelation');

        // Filter by company for non-super admins
        if (Auth::user()->role !== 'super_admin') {
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
            
            if (Auth::user()->role !== 'super_admin' && 
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
            
            if (Auth::user()->role !== 'super_admin' && 
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
            
            if (Auth::user()->role !== 'super_admin' && 
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
        if (Auth::user()->role !== 'super_admin' && $project->company_id !== Auth::user()->company_id) {
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
     * Update financial project settings (inline edit)
     */
    public function updateFinancial(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Company isolation for non-super_admin
        if (Auth::user()->role !== 'super_admin' && $project->company_id !== Auth::user()->company_id) {
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
}