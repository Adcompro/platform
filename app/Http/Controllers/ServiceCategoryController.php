<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of service categories
     */
    public function index(Request $request): View
    {
        // Authorization check - role-based access control
        if (!Auth::check()) {
            abort(403, 'Access denied. Please log in.');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can view service categories.');
        }

        // Build query - GEEN services relatie tot services tabel correct bestaat
        $query = ServiceCategory::query();
        
        // Alleen creator en updater laden (als ze bestaan)
        if (Schema::hasTable('users')) {
            $query->with(['creator', 'updater']);
        }

        // Company isolation - super_admin ziet alles, anderen alleen eigen company
        if (Auth::user()->role !== 'super_admin') {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Search functionaliteit
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Order by sort_order en naam
        $query->ordered();

        // Paginate results
        $categories = $query->paginate(20)->withQueryString();

        // Add services count to each category - TIJDELIJK OP 0 zetten
        $categories->getCollection()->transform(function ($category) {
            // Voor nu altijd 0 services tot de services tabel correct bestaat
            $category->services_count = 0;
            return $category;
        });

        // Calculate statistics
        $stats = $this->calculateStats();

        return view('service-categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new service category
     */
    public function create(): View
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create service categories.');
        }

        return view('service-categories.create');
    }

    /**
     * Store a newly created service category
     */
    public function store(Request $request): RedirectResponse
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create service categories.');
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7', // Voor hex color codes
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Bereid data voor
            $data = [
                'company_id' => Auth::user()->company_id,
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'icon' => $request->icon,
                'color' => $request->color,
                'is_active' => true,
                'sort_order' => $request->sort_order ?? ServiceCategory::getNextSortOrder(Auth::user()->company_id),
            ];

            $category = ServiceCategory::create($data);

            DB::commit();

            return redirect()->route('service-categories.index')
                ->with('success', 'Service category created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating service category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified service category
     */
    public function show(ServiceCategory $serviceCategory): View
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager', 'user', 'reader'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $serviceCategory->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view service categories from your own company.');
        }

        // Load relationships voor detailed view - GEEN services tot tabel bestaat
        $serviceCategory->load(['creator', 'updater', 'company']);

        // Calculate category statistics - TIJDELIJK 0 services
        $categoryStats = [
            'total_services' => 0,
            'active_services' => 0,
            'total_projects_using_services' => 0,
        ];

        return view('service-categories.show', compact('serviceCategory', 'categoryStats'));
    }

    /**
     * Show the form for editing the service category
     */
    public function edit(ServiceCategory $serviceCategory): View
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit service categories.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $serviceCategory->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit service categories from your own company.');
        }

        return view('service-categories.edit', compact('serviceCategory'));
    }

    /**
     * Update the specified service category
     */
    public function update(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can update service categories.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $serviceCategory->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only update service categories from your own company.');
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Update category
            $serviceCategory->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status ?? $serviceCategory->status,
                'icon' => $request->icon,
                'color' => $request->color,
                'sort_order' => $request->sort_order ?? $serviceCategory->sort_order,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $serviceCategory->is_active,
            ]);

            DB::commit();

            return redirect()->route('service-categories.index')
                ->with('success', 'Service category updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating service category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified service category
     */
    public function destroy(ServiceCategory $serviceCategory): RedirectResponse
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete service categories.');
        }

        // Company isolation check
        if (Auth::user()->role !== 'super_admin' && $serviceCategory->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only delete service categories from your own company.');
        }

        // Check if category can be deleted - TIJDELIJK ALTIJD TOESTAAN
        // if (!$serviceCategory->canBeDeleted()) {
        //     return back()->with('error', 'Cannot delete service category. Please remove all associated services first.');
        // }

        try {
            DB::beginTransaction();

            $categoryName = $serviceCategory->name;
            $serviceCategory->delete(); // Soft delete

            DB::commit();

            return redirect()->route('service-categories.index')
                ->with('success', "Service category '{$categoryName}' deleted successfully.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting service category: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update service categories (optional extra feature)
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can perform bulk operations.');
        }

        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:service_categories,id',
            'action' => 'required|in:activate,deactivate,delete',
        ]);

        try {
            DB::beginTransaction();

            $categories = ServiceCategory::whereIn('id', $request->categories);

            // Company isolation
            if (Auth::user()->role !== 'super_admin') {
                $categories->where('company_id', Auth::user()->company_id);
            }

            $count = 0;

            switch ($request->action) {
                case 'activate':
                    $count = $categories->update(['status' => 'active', 'is_active' => true]);
                    break;
                case 'deactivate':
                    $count = $categories->update(['status' => 'inactive', 'is_active' => false]);
                    break;
                case 'delete':
                    // Voor nu toestaan - check services later als tabel bestaat
                    $count = $categories->delete(); // Soft delete
                    break;
            }

            DB::commit();

            $actionText = match($request->action) {
                'activate' => 'activated',
                'deactivate' => 'deactivated',
                'delete' => 'deleted',
            };

            return back()->with('success', "{$count} service categories {$actionText} successfully.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error performing bulk operation: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics voor de index page
     */
    private function calculateStats(): array
    {
        $query = ServiceCategory::query();

        // Company isolation
        if (Auth::user()->role !== 'super_admin') {
            $query->where('company_id', Auth::user()->company_id);
        }

        $totalCategories = $query->count();
        $activeCategories = $query->where('status', 'active')->count();
        
        // Voor nu services stats op 0 zetten tot services tabel correct bestaat
        $totalServices = 0;
        $avgServices = 0;

        return [
            'total_categories' => $totalCategories,
            'active_categories' => $activeCategories,
            'inactive_categories' => $totalCategories - $activeCategories,
            'total_services' => $totalServices,
            'avg_services' => $avgServices,
        ];
    }
}