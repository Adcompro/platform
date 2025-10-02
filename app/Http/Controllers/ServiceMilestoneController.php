<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceMilestone;
use App\Models\ServiceActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceMilestoneController extends Controller
{
    /**
     * Display a listing of milestones for a specific service
     */
    public function index(Service $service)
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

        // Load service met alle milestones en hun taken
        $service->load(['milestones.tasks', 'category']);

        $milestones = $service->milestones()->ordered()->get();

        return view('service-milestones.index', compact('service', 'milestones'));
    }

    /**
     * Show the form for creating a new milestone
     */
    public function create(Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create milestones.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only create milestones for services from your own company.');
        }

        // Get next sort order
        $nextSortOrder = $service->milestones()->max('sort_order') + 1;

        return view('service-milestones.create', compact('service', 'nextSortOrder'));
    }

    /**
     * Store a newly created milestone in storage
     */
    /**
 * Store a newly created milestone in storage
 */
public function store(Request $request, Service $service)
{
    // Authorization check - ROLE-BASED
    if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
        abort(403, 'Access denied. Only administrators and project managers can create milestones.');
    }

    // Company isolation
    if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
        abort(403, 'Access denied. You can only create milestones for services from your own company.');
    }

    // ✅ FIXED: Updated validation for checkbox handling
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        // estimated_hours verwijderd - wordt automatisch berekend uit taken
        'included_in_price' => 'nullable', // ✅ CHANGED: Remove boolean validation
        'sort_order' => 'nullable|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        // Bepaal sort order automatisch als niet gegeven
        $sortOrder = $request->sort_order ?? ($service->milestones()->max('sort_order') + 1);

        $milestone = ServiceMilestone::create([
            'service_id' => $service->id,
            'name' => $request->name,
            'description' => $request->description,
            'estimated_hours' => 0, // Start met 0, wordt automatisch berekend uit taken
            'included_in_price' => $request->has('included_in_price'), // ✅ FIXED: Check if checkbox is present
            'sort_order' => $sortOrder,
        ]);

        // Log the milestone creation in service activities
        ServiceActivity::log(
            $service->id,
            'structure_added',
            'added milestone "' . $milestone->name . '"',
            ['Milestone' => ['old' => null, 'new' => $milestone->name]]
        );

        // Update de service estimated hours automatisch
        $service->calculateAndUpdateEstimatedHours();

        DB::commit();

        // ✅ RETURN JSON RESPONSE for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Milestone created successfully',
                'milestone' => $milestone
            ]);
        }

        return redirect()->route('services.milestones.index', $service)
            ->with('success', 'Milestone created successfully');

    } catch (\Exception $e) {
        DB::rollback();
        
        // ✅ RETURN JSON ERROR for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating milestone: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->withInput()
            ->with('error', 'Error creating milestone: ' . $e->getMessage());
    }
}
    /**
     * Display the specified milestone
     */
    public function show(Service $service, ServiceMilestone $milestone)
    {
        // Authorization check - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view services from your own company.');
        }

        // Verify milestone belongs to service
        if ($milestone->service_id !== $service->id) {
            abort(404, 'Milestone not found for this service.');
        }

        // Load relationships
        $milestone->load(['tasks.subtasks', 'service.category']);

        return view('service-milestones.show', compact('service', 'milestone'));
    }

    /**
     * Show the form for editing the specified milestone
     */
    public function edit(Service $service, ServiceMilestone $milestone)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit milestones.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit milestones for services from your own company.');
        }

        // Verify milestone belongs to service
        if ($milestone->service_id !== $service->id) {
            abort(404, 'Milestone not found for this service.');
        }

        return view('service-milestones.edit', compact('service', 'milestone'));
    }

    /**
     * Update the specified milestone in storage
     */
    public function update(Request $request, Service $service, ServiceMilestone $milestone)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can update milestones.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only update milestones for services from your own company.');
        }

        // Verify milestone belongs to service
        if ($milestone->service_id !== $service->id) {
            abort(404, 'Milestone not found for this service.');
        }

        // Validation - estimated_hours verwijderd, wordt automatisch berekend
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Track changes for activity log
            $changes = [];
            if ($milestone->name !== $request->name) {
                $changes['Milestone Name'] = ['old' => $milestone->name, 'new' => $request->name];
            }
            if ($milestone->description !== $request->description) {
                $changes['Milestone Description'] = [
                    'old' => $milestone->description ?: '(empty)',
                    'new' => $request->description ?: '(empty)'
                ];
            }

            // Update sort order als deze is gewijzigd
            if ($request->sort_order && $request->sort_order != $milestone->sort_order) {
                $milestone->updateSortOrder($request->sort_order);
            }

            $milestone->update([
                'name' => $request->name,
                'description' => $request->description,
                // estimated_hours wordt niet geüpdatet, wordt automatisch berekend
                'included_in_price' => $request->has('included_in_price'),
            ]);

            // Log changes if any
            if (!empty($changes)) {
                foreach ($changes as $field => $change) {
                    $description = 'updated milestone';
                    if ($field === 'Milestone Name') {
                        $description = 'updated milestone name from "' . $change['old'] . '" to "' . $change['new'] . '"';
                    } elseif ($field === 'Milestone Description') {
                        $description = 'updated milestone description';
                    }
                    
                    ServiceActivity::log(
                        $service->id,
                        'structure_updated',
                        $description,
                        [$field => $change]
                    );
                }
            }

            // Update de service estimated hours automatisch
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return redirect()->route('services.milestones.index', $service)
                ->with('success', 'Milestone updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating milestone: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified milestone from storage
     */
    public function destroy(Service $service, ServiceMilestone $milestone)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete milestones.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only delete milestones for services from your own company.');
        }

        // Verify milestone belongs to service
        if ($milestone->service_id !== $service->id) {
            abort(404, 'Milestone not found for this service.');
        }

        try {
            DB::beginTransaction();

            // Check if milestone can be deleted
            if (!$milestone->canBeDeleted()) {
                return back()->with('error', 'Cannot delete milestone with existing tasks. Please remove all tasks first.');
            }

            // Store milestone name for logging before deletion
            $milestoneName = $milestone->name;

            $milestone->delete();

            // Log the milestone deletion
            ServiceActivity::log(
                $service->id,
                'structure_removed',
                'removed milestone "' . $milestoneName . '"',
                ['Milestone' => ['old' => $milestoneName, 'new' => null]]
            );

            // Update sort order van overgebleven milestones
            $service->milestones()
                ->where('sort_order', '>', $milestone->sort_order)
                ->decrement('sort_order');

            // Update de service estimated hours automatisch
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return redirect()->route('services.milestones.index', $service)
                ->with('success', 'Milestone deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting milestone: ' . $e->getMessage());
        }
    }

    /**
     * Reorder milestones via AJAX
     */
    public function reorder(Request $request, Service $service)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'milestones' => 'required|array',
            'milestones.*' => 'exists:service_milestones,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->milestones as $index => $milestoneId) {
                ServiceMilestone::where('id', $milestoneId)
                    ->where('service_id', $service->id)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error reordering milestones'], 500);
        }
    }

    /**
     * Duplicate milestone with all tasks and subtasks
     */
    public function duplicate(Service $service, ServiceMilestone $milestone)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can duplicate milestones.');
        }

        // Company isolation
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only duplicate milestones for services from your own company.');
        }

        // Verify milestone belongs to service
        if ($milestone->service_id !== $service->id) {
            abort(404, 'Milestone not found for this service.');
        }

        try {
            DB::beginTransaction();

            $newMilestone = $milestone->duplicate($service->id);

            DB::commit();

            return redirect()->route('services.milestones.index', $service)
                ->with('success', 'Milestone duplicated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error duplicating milestone: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get milestone data for editing (direct route)
     */
    public function ajaxEdit($id)
    {
        $milestone = ServiceMilestone::findOrFail($id);
        
        // Check authorization
        $service = $milestone->service;
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Always return JSON for this AJAX method
        return response()->json($milestone);
    }

    /**
     * AJAX: Update milestone (direct route)
     */
    public function ajaxUpdate(Request $request, $id)
    {
        $milestone = ServiceMilestone::findOrFail($id);
        $service = $milestone->service;
        
        // Check authorization
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $milestone->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'included_in_price' => $request->has('included_in_price')
            ]);

            // Update service hours
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Milestone updated successfully']);
            }

            return redirect()->route('services.structure', $service)
                ->with('success', 'Milestone updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Error updating milestone: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Delete milestone (direct route)
     */
    public function ajaxDestroy($id)
    {
        $milestone = ServiceMilestone::findOrFail($id);
        $service = $milestone->service;
        
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Delete milestone and all its tasks/subtasks (cascade)
            $milestone->delete();

            // Update sort order
            $service->milestones()
                ->where('sort_order', '>', $milestone->sort_order)
                ->decrement('sort_order');

            // Update service hours
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Milestone deleted successfully']);
            }

            return redirect()->route('services.structure', $service)
                ->with('success', 'Milestone deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Error deleting milestone: ' . $e->getMessage());
        }
    }
}