<?php

namespace App\Http\Controllers;

use App\Models\ServiceTask;
use App\Models\ServiceSubtask;
use App\Models\ServiceActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceSubtaskController extends Controller
{
    /**
     * Display a listing of subtasks for a specific task
     */
    public function index(ServiceTask $serviceTask)
    {
        // Authorization check - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager', 'user', 'reader'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view services from your own company.');
        }

        // Load task met milestone, service en alle subtaken
        $serviceTask->load(['milestone.service.category', 'subtasks']);

        $subtasks = $serviceTask->subtasks()->ordered()->get();

        return view('service-subtasks.index', compact('serviceTask', 'subtasks'));
    }

    /**
     * Show the form for creating a new subtask
     */
    public function create(ServiceTask $serviceTask)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create subtasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only create subtasks for services from your own company.');
        }

        // Get next sort order
        $nextSortOrder = $serviceTask->subtasks()->max('sort_order') + 1;

        return view('service-subtasks.create', compact('serviceTask', 'nextSortOrder'));
    }

/**
 * Store a newly created subtask in storage
 */
public function store(Request $request, $taskId)  // ✅ CHANGED: Use taskId instead of ServiceTask model binding
{
    // Authorization check - ROLE-BASED
    if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
        abort(403, 'Access denied. Only administrators and project managers can create subtasks.');
    }

    // ✅ FIND THE TASK MANUALLY
    $serviceTask = ServiceTask::findOrFail($taskId);
    
    // Company isolation via service
    if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
        abort(403, 'Access denied. You can only create subtasks for services from your own company.');
    }

    // Validation
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'estimated_hours' => 'nullable|numeric|min:0',
        'included_in_price' => 'nullable',
        'sort_order' => 'nullable|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        // Bepaal sort order automatisch als niet gegeven
        $sortOrder = $request->sort_order ?? ($serviceTask->subtasks()->max('sort_order') + 1);

        $subtask = ServiceSubtask::create([
            'service_task_id' => $serviceTask->id,  // ✅ NOW CORRECTLY SET
            'name' => $request->name,
            'description' => $request->description,
            'estimated_hours' => $request->estimated_hours ?? 0,
            'included_in_price' => $request->has('included_in_price'),
            'sort_order' => $sortOrder,
        ]);

        // Log the subtask creation in service activities
        ServiceActivity::log(
            $serviceTask->milestone->service->id,
            'structure_added',
            'added subtask "' . $subtask->name . '" to task "' . $serviceTask->name . '"',
            ['Subtask' => ['old' => null, 'new' => $subtask->name . ' (in ' . $serviceTask->name . ')']]
        );

        // Update eerst de milestone uren, dan de service uren
        $serviceTask->milestone->calculateAndUpdateEstimatedHours();
        $serviceTask->milestone->service->calculateAndUpdateEstimatedHours();

        DB::commit();

        // Return JSON response for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask created successfully',
                'subtask' => $subtask
            ]);
        }

        return redirect()->route('service-tasks.subtasks.index', $serviceTask)
            ->with('success', 'Subtask created successfully');

    } catch (\Exception $e) {
        DB::rollback();
        
        // Return JSON error for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating subtask: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->withInput()
            ->with('error', 'Error creating subtask: ' . $e->getMessage());
    }
}
    /**
     * Display the specified subtask
     */
    public function show(ServiceTask $serviceTask, ServiceSubtask $subtask)
    {
        // Authorization check - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view services from your own company.');
        }

        // Verify subtask belongs to task
        if ($subtask->service_task_id !== $serviceTask->id) {
            abort(404, 'Subtask not found for this task.');
        }

        // Load relationships
        $subtask->load(['task.milestone.service.category']);

        return view('service-subtasks.show', compact('serviceTask', 'subtask'));
    }

    /**
     * Show the form for editing the specified subtask
     */
    public function edit(ServiceTask $serviceTask, ServiceSubtask $subtask)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit subtasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit subtasks for services from your own company.');
        }

        // Verify subtask belongs to task
        if ($subtask->service_task_id !== $serviceTask->id) {
            abort(404, 'Subtask not found for this task.');
        }

        return view('service-subtasks.edit', compact('serviceTask', 'subtask'));
    }

    /**
     * Update the specified subtask in storage
     */
    public function update(Request $request, ServiceTask $serviceTask, ServiceSubtask $subtask)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can update subtasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only update subtasks for services from your own company.');
        }

        // Verify subtask belongs to task
        if ($subtask->service_task_id !== $serviceTask->id) {
            abort(404, 'Subtask not found for this task.');
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'included_in_price' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Update sort order als deze is gewijzigd
            if ($request->sort_order && $request->sort_order != $subtask->sort_order) {
                $subtask->updateSortOrder($request->sort_order);
            }

            $subtask->update([
                'name' => $request->name,
                'description' => $request->description,
                'estimated_hours' => $request->estimated_hours ?? 0,
                'included_in_price' => $request->boolean('included_in_price'),
            ]);

            // Update eerst de milestone uren, dan de service uren
            $serviceTask->milestone->calculateAndUpdateEstimatedHours();
            $serviceTask->milestone->service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return redirect()->route('service-tasks.subtasks.index', $serviceTask)
                ->with('success', 'Subtask updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating subtask: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified subtask from storage
     */
    public function destroy(ServiceTask $serviceTask, ServiceSubtask $subtask)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete subtasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only delete subtasks for services from your own company.');
        }

        // Verify subtask belongs to task
        if ($subtask->service_task_id !== $serviceTask->id) {
            abort(404, 'Subtask not found for this task.');
        }

        try {
            DB::beginTransaction();

            $subtask->delete();

            // Update sort order van overgebleven subtaken
            $serviceTask->subtasks()
                ->where('sort_order', '>', $subtask->sort_order)
                ->decrement('sort_order');

            // Update eerst de milestone uren, dan de service uren
            $serviceTask->milestone->calculateAndUpdateEstimatedHours();
            $serviceTask->milestone->service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return redirect()->route('service-tasks.subtasks.index', $serviceTask)
                ->with('success', 'Subtask deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting subtask: ' . $e->getMessage());
        }
    }

    /**
     * Reorder subtasks via AJAX
     */
    public function reorder(Request $request, ServiceTask $serviceTask)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'subtasks' => 'required|array',
            'subtasks.*' => 'exists:service_subtasks,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->subtasks as $index => $subtaskId) {
                ServiceSubtask::where('id', $subtaskId)
                    ->where('service_task_id', $serviceTask->id)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error reordering subtasks'], 500);
        }
    }

    /**
     * Duplicate subtask
     */
    public function duplicate(ServiceTask $serviceTask, ServiceSubtask $subtask)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can duplicate subtasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceTask->milestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only duplicate subtasks for services from your own company.');
        }

        // Verify subtask belongs to task
        if ($subtask->service_task_id !== $serviceTask->id) {
            abort(404, 'Subtask not found for this task.');
        }

        try {
            DB::beginTransaction();

            $newSubtask = $subtask->duplicate($serviceTask->id);

            DB::commit();

            return redirect()->route('service-tasks.subtasks.index', $serviceTask)
                ->with('success', 'Subtask duplicated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error duplicating subtask: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get subtask data for editing (direct route)
     */
    public function ajaxEdit($id)
    {
        $subtask = ServiceSubtask::findOrFail($id);
        
        // Check authorization via task->milestone->service
        $service = $subtask->task->milestone->service;
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Always return JSON for this AJAX method
        return response()->json($subtask);
    }

    /**
     * AJAX: Update subtask (direct route)
     */
    public function ajaxUpdate(Request $request, $id)
    {
        $subtask = ServiceSubtask::findOrFail($id);
        $task = $subtask->task;
        $milestone = $task->milestone;
        $service = $milestone->service;
        
        // Check authorization
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'included_in_price' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $subtask->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'estimated_hours' => $validated['estimated_hours'] ?? 0,
                'included_in_price' => $request->has('included_in_price')
            ]);

            // Update task, milestone and service hours
            $task->calculateAndUpdateEstimatedHours();
            $milestone->calculateAndUpdateEstimatedHours();
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Subtask updated successfully']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Delete subtask (direct route)
     */
    public function ajaxDestroy($id)
    {
        $subtask = ServiceSubtask::findOrFail($id);
        $task = $subtask->task;
        $milestone = $task->milestone;
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

            // Delete subtask
            $subtask->delete();

            // Update sort order
            $task->subtasks()
                ->where('sort_order', '>', $subtask->sort_order)
                ->decrement('sort_order');

            // Update task, milestone and service hours
            $task->calculateAndUpdateEstimatedHours();
            $milestone->calculateAndUpdateEstimatedHours();
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Subtask deleted successfully']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}