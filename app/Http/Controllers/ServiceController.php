<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of services
     */
    public function index(Request $request)
    {
        // Authorization check - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager', 'user', 'reader'])) {
            abort(403, 'Access denied.');
        }

        // Query building met eager loading
        $query = Service::with(['category', 'milestones.tasks.subtasks']);
        
        // Company isolation (super_admin ziet alles, anderen alleen eigen company)
        if (Auth::user()->role !== 'super_admin') {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku_code', 'like', '%' . $searchTerm . '%');
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Type filter (package or individual)
        if ($request->filled('is_package')) {
            $query->where('is_package', $request->boolean('is_package'));
        }

        // Get services with pagination
        $services = $query->orderBy('name')->paginate(20);
        
        // Get categories for filter dropdown
        $categoriesQuery = ServiceCategory::query();
        if (Auth::user()->role !== 'super_admin') {
            $categoriesQuery->where('company_id', Auth::user()->company_id);
        }
        $categories = $categoriesQuery->orderBy('name')->get();
        
        // Calculate statistics - FIXED: Use total_price from your model
        $statsQuery = Service::query();
        if (Auth::user()->role !== 'super_admin') {
            $statsQuery->where('company_id', Auth::user()->company_id);
        }
        
        $stats = [
            'total_services' => $statsQuery->count(),
            'active_services' => $statsQuery->where('is_active', true)->count(),
            'total_value' => $statsQuery->sum('total_price') ?? 0, // ✅ FIXED: Use total_price
            'total_categories' => $categories->count(),
        ];
        
        return view('services.index', compact('services', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new service
     */
    public function create()
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create services.');
        }

        // Get categories for dropdown
        $categoriesQuery = ServiceCategory::query();
        if (Auth::user()->role !== 'super_admin') {
            $categoriesQuery->where('company_id', Auth::user()->company_id);
        }
        $categories = $categoriesQuery->orderBy('name')->get();

        return view('services.create', compact('categories'));
    }

    /**
     * Store a newly created service in storage
     */
    public function store(Request $request)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create services.');
        }

        // Validation - estimated_hours verwijderd omdat dit automatisch berekend wordt
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'sku_code' => 'nullable|string|max:100|unique:services,sku_code',
            'total_price' => 'required|numeric|min:0',
            'is_package' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            DB::beginTransaction();

            $service = Service::create([
                'company_id' => Auth::user()->company_id, // Voor multi-tenant
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'service_category_id' => $validated['service_category_id'] ?? null,
                'sku_code' => $validated['sku_code'] ?? null,
                'total_price' => $validated['total_price'],
                'estimated_hours' => 0, // Start met 0, wordt automatisch berekend bij toevoegen taken
                'is_package' => $request->boolean('is_package'),
                'is_active' => $request->boolean('is_active', true),
                'is_public' => $request->boolean('is_public'),
                'status' => $validated['status'] ?? 'active',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Log the creation activity
            $creationDetails = [];
            $creationDetails['Name'] = ['old' => null, 'new' => $service->name];
            if ($service->description) $creationDetails['Description'] = ['old' => null, 'new' => $service->description];
            if ($service->sku_code) $creationDetails['SKU Code'] = ['old' => null, 'new' => $service->sku_code];
            $creationDetails['Price'] = ['old' => null, 'new' => '€ ' . number_format($service->total_price, 2, ',', '.')];
            if ($service->estimated_hours) $creationDetails['Estimated Hours'] = ['old' => null, 'new' => $service->estimated_hours . ' hours'];
            $creationDetails['Status'] = ['old' => null, 'new' => $service->is_active ? 'Active' : 'Inactive'];
            if ($service->category) {
                $creationDetails['Category'] = ['old' => null, 'new' => $service->category->name];
            }

            ServiceActivity::log(
                $service->id,
                'created',
                'created new service package',
                $creationDetails
            );

            DB::commit();

            return redirect()->route('services.index')
                ->with('success', 'Service package created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating service: ' . $e->getMessage());
        }
    }

    /**
 * Display the specified service
 */
public function show(Service $service)
{
    // Authorization check - ROLE-BASED
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    // Company isolation
    if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
        abort(403, 'Access denied. You can only view services from your own company.');
    }

    // Load relationships
    $service->load([
        'category',
        'creator',
        'updater',
        'milestones' => function($query) {
            $query->orderBy('sort_order');
        },
        'milestones.tasks' => function($query) {
            $query->orderBy('sort_order');
        },
        'milestones.tasks.subtasks' => function($query) {
            $query->orderBy('sort_order');
        }
    ]);
    
    // Load activities separately to ensure they're loaded
    $service->load(['activities' => function($query) {
        $query->with('user')->orderBy('created_at', 'desc');
    }]);

    // ✅ FIXED: Changed variable name to match view expectation
    $serviceStats = $service->getUsageStats();
    
    // Calculate total hours from all milestones, tasks and subtasks
    $totalHours = $service->calculateEstimatedHours();
    
    // Debug: Log activities count
    \Log::info('Service ' . $service->id . ' has ' . $service->activities->count() . ' activities loaded');

    return view('services.show', compact('service', 'serviceStats', 'totalHours'));
}

    /**
     * 🚀 Show service structure management page
     */
    public function structure(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager', 'user', 'reader'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view services from your own company.');
        }

        // Load service met alle relationships voor structure management
        $service->load([
            'category',
            'milestones' => function($query) {
                $query->orderBy('sort_order');
            },
            'milestones.tasks' => function($query) {
                $query->orderBy('sort_order');
            },
            'milestones.tasks.subtasks' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('services.structure', compact('service'));
    }

    /**
     * Show the form for editing the specified service
     */
    public function edit(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit services.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit services from your own company.');
        }

        // Get categories for dropdown
        $categoriesQuery = ServiceCategory::query();
        if (Auth::user()->role !== 'super_admin') {
            $categoriesQuery->where('company_id', Auth::user()->company_id);
        }
        $categories = $categoriesQuery->orderBy('name')->get();

        return view('services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified service in storage
     */
    public function update(Request $request, Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can update services.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only update services from your own company.');
        }

        // Validation - estimated_hours verwijderd omdat dit automatisch berekend wordt
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'sku_code' => 'nullable|string|max:100|unique:services,sku_code,' . $service->id,
            'total_price' => 'required|numeric|min:0',
            'is_package' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            DB::beginTransaction();

            // Track changes for activity log
            $oldValues = $service->toArray();
            $oldCategory = $service->category ? $service->category->name : null;
            $changes = [];

            // Force fill om casting problemen te omzeilen - estimated_hours wordt niet overschreven
            $service->forceFill([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'service_category_id' => $validated['service_category_id'] ?? null,
                'sku_code' => $validated['sku_code'] ?? null,
                'total_price' => $validated['total_price'],
                // estimated_hours wordt automatisch berekend uit taken, niet handmatig ingevoerd
                'is_package' => $request->boolean('is_package'),
                'is_active' => $request->boolean('is_active'),
                'is_public' => $request->boolean('is_public'),
                'status' => $validated['status'] ?? 'active',
                'updated_by' => Auth::id(),
            ]);
            
            $service->save();

            // Herbereken de estimated hours op basis van de huidige structure
            $service->calculateAndUpdateEstimatedHours();

            // Track field changes for activity log
            $fieldsToTrack = [
                'name' => 'Name',
                'description' => 'Description',
                'sku_code' => 'SKU Code',
                'total_price' => 'Price',
                'estimated_hours' => 'Estimated Hours',
                'is_active' => 'Status',
                'is_public' => 'Public Status'
            ];
            
            foreach ($fieldsToTrack as $field => $label) {
                $oldValue = $oldValues[$field] ?? null;
                $newValue = $service->$field ?? null;
                
                // Skip if values are the same
                if ($oldValue == $newValue) {
                    continue;
                }
                
                // Format values for display
                if ($field === 'total_price') {
                    $oldValue = $oldValue ? '€ ' . number_format($oldValue, 2, ',', '.') : '(empty)';
                    $newValue = $newValue ? '€ ' . number_format($newValue, 2, ',', '.') : '(empty)';
                } elseif ($field === 'estimated_hours') {
                    $oldValue = $oldValue ? $oldValue . ' hours' : '(empty)';
                    $newValue = $newValue ? $newValue . ' hours' : '(empty)';
                } elseif ($field === 'is_active') {
                    $oldValue = $oldValue ? 'Active' : 'Inactive';
                    $newValue = $newValue ? 'Active' : 'Inactive';
                } elseif ($field === 'is_public') {
                    $oldValue = $oldValue ? 'Public' : 'Private';
                    $newValue = $newValue ? 'Public' : 'Private';
                } else {
                    // Handle empty values
                    if ($oldValue === null || $oldValue === '') {
                        $oldValue = '(empty)';
                    }
                    if ($newValue === null || $newValue === '') {
                        $newValue = '(empty)';
                    }
                }
                
                $changes[$label] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
            
            // Check category change
            $newCategory = $service->category ? $service->category->name : null;
            if ($oldCategory !== $newCategory) {
                $changes['Category'] = [
                    'old' => $oldCategory ?: '(empty)',
                    'new' => $newCategory ?: '(empty)'
                ];
            }

            // Log all field changes if any
            if (!empty($changes)) {
                foreach ($changes as $fieldName => $fieldData) {
                    // Create descriptive message per CLAUDE.md requirements
                    $description = 'updated ' . strtolower($fieldName);
                    if ($fieldData['old'] !== '(empty)' && $fieldData['new'] !== '(empty)') {
                        $description .= ' from "' . $fieldData['old'] . '" to "' . $fieldData['new'] . '"';
                    } elseif ($fieldData['old'] === '(empty)') {
                        $description .= ' to "' . $fieldData['new'] . '"';
                    } elseif ($fieldData['new'] === '(empty)') {
                        $description .= ' from "' . $fieldData['old'] . '" to empty';
                    }
                    
                    ServiceActivity::log(
                        $service->id,
                        'updated',
                        $description,
                        [$fieldName => $fieldData]
                    );
                }
            }

            DB::commit();

            return redirect()->route('services.index')
                ->with('success', 'Service package updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating service: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified service from storage (soft delete)
     */
    public function destroy(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete services.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only delete services from your own company.');
        }

        try {
            DB::beginTransaction();

            // Check if service is being used in any projects
            $usageCount = DB::table('project_services')
                ->where('service_id', $service->id)
                ->count();
                
            if ($usageCount > 0) {
                return back()->with('error', 'Cannot delete service that is being used in ' . $usageCount . ' project(s). Please remove it from all projects first.');
            }

            // Collect deletion details for activity log
            $deletionDetails = [];
            $deletionDetails['Name'] = ['old' => $service->name, 'new' => null];
            $deletionDetails['Price'] = ['old' => '€ ' . number_format($service->total_price, 2, ',', '.'), 'new' => null];
            if ($service->sku_code) $deletionDetails['SKU Code'] = ['old' => $service->sku_code, 'new' => null];
            if ($service->category) $deletionDetails['Category'] = ['old' => $service->category->name, 'new' => null];
            $deletionDetails['Status'] = ['old' => $service->is_active ? 'Active' : 'Inactive', 'new' => null];
            
            // Log the deletion activity
            ServiceActivity::log(
                $service->id,
                'deleted',
                'deleted service package',
                $deletionDetails
            );

            // Soft delete the service (will also soft delete related milestones, tasks, subtasks via cascade)
            $service->delete();

            DB::commit();

            return redirect()->route('services.index')
                ->with('success', 'Service package deleted successfully. It can be restored if needed.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting service package: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified service
     */
    public function archive(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can archive services.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only archive services from your own company.');
        }

        try {
            DB::beginTransaction();

            if (!$service->canBeArchived()) {
                return back()->with('error', 'Cannot archive this service. Service must be active.');
            }

            $service->archive();

            DB::commit();

            return redirect()->route('services.index')
                ->with('success', 'Service archived successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error archiving service: ' . $e->getMessage());
        }
    }

    /**
     * Activate the specified service
     */
    public function activate(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can activate services.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only activate services from your own company.');
        }

        try {
            DB::beginTransaction();

            $service->activate();

            DB::commit();

            return redirect()->route('services.index')
                ->with('success', 'Service activated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error activating service: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate the specified service
     */
    public function duplicate(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can duplicate services.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only duplicate services from your own company.');
        }

        try {
            DB::beginTransaction();

            // ✅ UPDATED: Use duplicate method from your model
            $newService = $service->duplicate();
            $newService->created_by = Auth::id();
            $newService->updated_by = Auth::id();
            $newService->save();

            DB::commit();

            return redirect()->route('services.index')
                ->with('success', 'Service duplicated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error duplicating service: ' . $e->getMessage());
        }
    }

    /**
     * Export service data
     */
    public function export(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can export services.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only export services from your own company.');
        }

        // Load all relationships
        $service->load([
            'category',
            'creator',
            'updater',
            'milestones.tasks.subtasks'
        ]);

        // Get usage stats
        $usageStats = $service->getUsageStats();

        // TODO: Implement actual export functionality (Excel, PDF, etc.)
        return response()->json([
            'message' => 'Export functionality will be implemented',
            'service' => $service->toArray(),
            'usage_stats' => $usageStats
        ]);
    }
}