<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectTemplate;
use App\Models\TemplateMilestone;
use App\Models\TemplateTask;
use App\Models\Company;

class ProjectTemplateController extends Controller
{
    public function index(Request $request)
    {
        // Only admin and super_admin can access templates
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage project templates.');
        }

        $user = Auth::user();
        $query = ProjectTemplate::with(['milestones']);

        // Super admin sees all templates, admin sees only their company's templates
        if ($user->role !== 'super_admin') {
            // Admin only sees their company's templates
            if (Schema::hasColumn('project_templates', 'company_id')) {
                $query->where('company_id', $user->company_id);
            }
        }

        // Apply company filter if specified (for super_admin and admin to filter)
        if ($request->filled('company_id') && in_array($user->role, ['super_admin', 'admin'])) {
            if (Schema::hasColumn('project_templates', 'company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Temporary: Use get() instead of paginate() to debug
        $allTemplates = $query->orderBy('name')->get();
        
        // Create manual pagination
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $templates = new \Illuminate\Pagination\LengthAwarePaginator(
            $allTemplates->forPage($currentPage, $perPage),
            $allTemplates->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url()]
        );
        
        // Get filter options - safely check if columns exist
        $categories = collect();
        if (Schema::hasColumn('project_templates', 'category')) {
            $categories = ProjectTemplate::distinct('category')->pluck('category')->filter();
        }
        
        $companies = Company::orderBy('name')->get();

        return view('project-templates.index', compact('templates', 'categories', 'companies'));
    }

    public function show(ProjectTemplate $projectTemplate)
    {
        // Only admin and super_admin can access templates
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage project templates.');
        }

        // Load complete template structure
        $projectTemplate->load([
            'milestones' => function($query) {
                $query->orderBy('sort_order');
            },
            'milestones.tasks' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Calculate template statistics
        $stats = [
            'total_milestones' => $projectTemplate->milestones->count(),
            'total_tasks' => $projectTemplate->milestones->sum(function($milestone) {
                return $milestone->tasks->count();
            }),
            'total_hours' => $projectTemplate->calculateTotalHours(),
            'estimated_value' => $projectTemplate->calculateTotalValue()
        ];

        return view('project-templates.show', compact('projectTemplate', 'stats'));
    }

    public function create()
    {
        // Only admin and super_admin can access templates
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage project templates.');
        }

        // Get categories for dropdown - safely check if column exists
        $categories = collect();
        if (Schema::hasColumn('project_templates', 'category')) {
            $categories = ProjectTemplate::distinct('category')->pluck('category')->filter();
        }
        
        return view('project-templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Only admin and super_admin can access templates
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage project templates.');
        }

        // Debug: Log all incoming data
        \Log::info('ProjectTemplate Store - Raw Request Data:', [
            'all' => $request->all(),
            'milestones_count' => is_array($request->milestones) ? count($request->milestones) : 'not array',
            'method' => $request->method(),
            'has_name' => $request->has('name'),
            'name_value' => $request->name
        ]);

        // Build validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'milestones' => 'nullable|array',
            'milestones.*.name' => 'required|string|max:255',
            'milestones.*.description' => 'nullable|string',
            'milestones.*.estimated_hours' => 'nullable|numeric|min:0',
            'milestones.*.hourly_rate' => 'nullable|numeric|min:0',
            'milestones.*.days_from_start' => 'nullable|integer|min:0',
            'milestones.*.duration_days' => 'nullable|integer|min:0',
            'milestones.*.fee_type' => 'nullable|in:in_fee,extended',
            'milestones.*.start_date' => 'nullable|date',
            'milestones.*.end_date' => 'nullable|date|after_or_equal:milestones.*.start_date',
            'milestones.*.tasks' => 'nullable|array',
            'milestones.*.tasks.*.name' => 'required|string|max:255',
            'milestones.*.tasks.*.description' => 'nullable|string',
            'milestones.*.tasks.*.estimated_hours' => 'nullable|numeric|min:0',
            'milestones.*.tasks.*.fee_type' => 'nullable|in:in_fee,extended',
            'milestones.*.tasks.*.start_date' => 'nullable|date',
            'milestones.*.tasks.*.end_date' => 'nullable|date|after_or_equal:milestones.*.tasks.*.start_date',
        ];

        // Add optional validation rules based on existing columns
        if (Schema::hasColumn('project_templates', 'category')) {
            $rules['category'] = 'nullable|string|max:100';
        }
        if (Schema::hasColumn('project_templates', 'default_hourly_rate')) {
            $rules['default_hourly_rate'] = 'nullable|numeric|min:0';
        }
        if (Schema::hasColumn('project_templates', 'estimated_duration_days')) {
            $rules['estimated_duration_days'] = 'nullable|integer|min:0';
        }
        if (Schema::hasColumn('project_templates', 'status')) {
            $rules['status'] = 'required|in:active,inactive';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Build template data
            $templateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ];

            // Add optional fields if columns exist
            if (Schema::hasColumn('project_templates', 'category')) {
                // Use 'general' as default if category is empty (database default)
                $templateData['category'] = $validated['category'] ?? 'general';
            }
            if (Schema::hasColumn('project_templates', 'default_hourly_rate')) {
                $templateData['default_hourly_rate'] = $validated['default_hourly_rate'] ?? null;
            }
            if (Schema::hasColumn('project_templates', 'estimated_duration_days')) {
                $templateData['estimated_duration_days'] = $validated['estimated_duration_days'] ?? null;
            }
            if (Schema::hasColumn('project_templates', 'status')) {
                $templateData['status'] = $validated['status'] ?? 'active';
            }
            if (Schema::hasColumn('project_templates', 'company_id')) {
                $templateData['company_id'] = Auth::user()->company_id ?? null;
            }
            if (Schema::hasColumn('project_templates', 'created_by')) {
                $templateData['created_by'] = Auth::id();
            }

            \Log::info('Creating template with data:', $templateData);
            $template = ProjectTemplate::create($templateData);
            \Log::info('Template created with ID: ' . $template->id);

            // Process milestones
            if (isset($validated['milestones'])) {
                foreach ($validated['milestones'] as $milestoneIndex => $milestoneData) {
                    $milestoneCreateData = [
                        'project_template_id' => $template->id,
                        'name' => $milestoneData['name'],
                        'description' => $milestoneData['description'] ?? null,
                        'estimated_hours' => $milestoneData['estimated_hours'] ?? null,
                        'default_hourly_rate' => $milestoneData['hourly_rate'] ?? null,
                        'days_from_start' => $milestoneData['days_from_start'] ?? 0,
                        'duration_days' => $milestoneData['duration_days'] ?? 1,
                        'fee_type' => $milestoneData['fee_type'] ?? 'in_fee',
                        'start_date' => $milestoneData['start_date'] ?? null,
                        'end_date' => $milestoneData['end_date'] ?? null,
                        'sort_order' => $milestoneIndex + 1,
                    ];

                    $milestone = TemplateMilestone::create($milestoneCreateData);

                    // Process tasks
                    if (isset($milestoneData['tasks'])) {
                        foreach ($milestoneData['tasks'] as $taskIndex => $taskData) {
                            TemplateTask::create([
                                'template_milestone_id' => $milestone->id,
                                'name' => $taskData['name'],
                                'description' => $taskData['description'] ?? null,
                                'estimated_hours' => $taskData['estimated_hours'] ?? null,
                                'fee_type' => $taskData['fee_type'] ?? 'in_fee',
                                'start_date' => $taskData['start_date'] ?? null,
                                'end_date' => $taskData['end_date'] ?? null,
                                'sort_order' => $taskIndex + 1,
                            ]);
                        }
                    }
                }
            }

            // Update template totals - temporarily disabled for debugging
            try {
                $template->updateTotals();
            } catch (\Exception $e) {
                \Log::error('Error updating totals: ' . $e->getMessage());
                // Continue anyway - totals are not critical
            }

            DB::commit();
            
            \Log::info('Template saved successfully with ID: ' . $template->id);

            return redirect()->route('project-templates.show', $template)
                ->with('success', 'Project template created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in store method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()
                ->with('error', 'Error creating template: ' . $e->getMessage());
        }
    }

    public function edit(ProjectTemplate $projectTemplate)
    {
        // Only admin and super_admin can access templates
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage project templates.');
        }

        // Load complete template structure for editing
        $projectTemplate->load([
            'milestones' => function($query) {
                $query->orderBy('sort_order');
            },
            'milestones.tasks' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        $categories = collect();
        if (Schema::hasColumn('project_templates', 'category')) {
            $categories = ProjectTemplate::distinct('category')->pluck('category')->filter();
        }
        
        return view('project-templates.edit', compact('projectTemplate', 'categories'));
    }

    public function update(Request $request, ProjectTemplate $projectTemplate)
    {
        // Only admin and super_admin can access templates
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage project templates.');
        }

        // Build validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'milestones' => 'nullable|array',
            'milestones.*.name' => 'required|string|max:255',
            'milestones.*.description' => 'nullable|string',
            'milestones.*.estimated_hours' => 'nullable|numeric|min:0',
            'milestones.*.hourly_rate' => 'nullable|numeric|min:0',
            'milestones.*.days_from_start' => 'nullable|integer|min:0',
            'milestones.*.duration_days' => 'nullable|integer|min:0',
            'milestones.*.fee_type' => 'nullable|in:in_fee,extended',
            'milestones.*.start_date' => 'nullable|date',
            'milestones.*.end_date' => 'nullable|date|after_or_equal:milestones.*.start_date',
            'milestones.*.tasks' => 'nullable|array',
            'milestones.*.tasks.*.name' => 'required|string|max:255',
            'milestones.*.tasks.*.description' => 'nullable|string',
            'milestones.*.tasks.*.estimated_hours' => 'nullable|numeric|min:0',
            'milestones.*.tasks.*.fee_type' => 'nullable|in:in_fee,extended',
            'milestones.*.tasks.*.start_date' => 'nullable|date',
            'milestones.*.tasks.*.end_date' => 'nullable|date|after_or_equal:milestones.*.tasks.*.start_date',
        ];

        if (Schema::hasColumn('project_templates', 'category')) {
            $rules['category'] = 'nullable|string|max:100';
        }
        if (Schema::hasColumn('project_templates', 'default_hourly_rate')) {
            $rules['default_hourly_rate'] = 'nullable|numeric|min:0';
        }
        if (Schema::hasColumn('project_templates', 'estimated_duration_days')) {
            $rules['estimated_duration_days'] = 'nullable|integer|min:0';
        }
        if (Schema::hasColumn('project_templates', 'status')) {
            $rules['status'] = 'required|in:active,inactive';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Update template data
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'],
            ];

            if (Schema::hasColumn('project_templates', 'category')) {
                // Use 'general' as default if category is empty (database default)
                $updateData['category'] = $validated['category'] ?? 'general';
            }
            if (Schema::hasColumn('project_templates', 'default_hourly_rate')) {
                $updateData['default_hourly_rate'] = $validated['default_hourly_rate'] ?? null;
            }
            if (Schema::hasColumn('project_templates', 'estimated_duration_days')) {
                $updateData['estimated_duration_days'] = $validated['estimated_duration_days'] ?? null;
            }
            if (Schema::hasColumn('project_templates', 'status')) {
                $updateData['status'] = $validated['status'];
            }

            $projectTemplate->update($updateData);

            // Remove existing structure and recreate
            $projectTemplate->milestones()->delete();

            // Process milestones
            if (isset($validated['milestones'])) {
                foreach ($validated['milestones'] as $milestoneIndex => $milestoneData) {
                    $milestone = TemplateMilestone::create([
                        'project_template_id' => $projectTemplate->id,
                        'name' => $milestoneData['name'],
                        'description' => $milestoneData['description'] ?? null,
                        'estimated_hours' => $milestoneData['estimated_hours'] ?? null,
                        'default_hourly_rate' => $milestoneData['hourly_rate'] ?? null,
                        'days_from_start' => $milestoneData['days_from_start'] ?? 0,
                        'duration_days' => $milestoneData['duration_days'] ?? 1,
                        'fee_type' => $milestoneData['fee_type'] ?? 'in_fee',
                        'start_date' => $milestoneData['start_date'] ?? null,
                        'end_date' => $milestoneData['end_date'] ?? null,
                        'sort_order' => $milestoneIndex + 1,
                    ]);

                    // Process tasks
                    if (isset($milestoneData['tasks'])) {
                        foreach ($milestoneData['tasks'] as $taskIndex => $taskData) {
                            TemplateTask::create([
                                'template_milestone_id' => $milestone->id,
                                'name' => $taskData['name'],
                                'description' => $taskData['description'] ?? null,
                                'estimated_hours' => $taskData['estimated_hours'] ?? null,
                                'fee_type' => $taskData['fee_type'] ?? 'in_fee',
                                'start_date' => $taskData['start_date'] ?? null,
                                'end_date' => $taskData['end_date'] ?? null,
                                'sort_order' => $taskIndex + 1,
                            ]);
                        }
                    }
                }
            }

            // Update template totals
            $projectTemplate->updateTotals();

            DB::commit();

            return redirect()->route('project-templates.show', $projectTemplate)
                ->with('success', 'Project template updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating template: ' . $e->getMessage());
        }
    }

    public function destroy(ProjectTemplate $projectTemplate)
    {
        // Only admin and super_admin can access templates
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage project templates.');
        }

        try {
            $projectTemplate->delete();

            return redirect()->route('project-templates.index')
                ->with('success', 'Project template deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting template: ' . $e->getMessage());
        }
    }
}