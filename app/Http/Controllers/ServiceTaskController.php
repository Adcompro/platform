<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceMilestone;
use App\Models\ServiceTask;
use App\Models\ServiceSubtask;
use App\Models\ServiceActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceTaskController extends Controller
{
    /**
     * Display a listing of tasks for a specific milestone
     */
    public function index(ServiceMilestone $serviceMilestone)
    {
        // Authorization check - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager', 'user', 'reader'])) {
            abort(403, 'Access denied.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view services from your own company.');
        }

        // Load milestone met service en alle taken met subtaken
        $serviceMilestone->load(['service.category', 'tasks.subtasks']);

        $tasks = $serviceMilestone->tasks()->ordered()->get();

        return view('service-tasks.index', compact('serviceMilestone', 'tasks'));
    }

    /**
     * Show the form for creating a new task
     */
    public function create(ServiceMilestone $serviceMilestone)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create tasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only create tasks for services from your own company.');
        }

        // Get next sort order
        $nextSortOrder = $serviceMilestone->tasks()->max('sort_order') + 1;

        return view('service-tasks.create', compact('serviceMilestone', 'nextSortOrder'));
    }

    /**
 * Store a newly created task in storage
 */
public function store(Request $request, Service $service, ServiceMilestone $milestone)
{
    // Authorization check - ROLE-BASED
    if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
        abort(403, 'Access denied. Only administrators and project managers can create tasks.');
    }

    // Company isolation via service
    if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
        abort(403, 'Access denied. You can only create tasks for services from your own company.');
    }

    // ✅ FIXED: Updated validation for checkbox handling
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'estimated_hours' => 'nullable|numeric|min:0',
        'included_in_price' => 'nullable', // ✅ CHANGED: Remove boolean validation
        'sort_order' => 'nullable|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        // Bepaal sort order automatisch als niet gegeven
        $sortOrder = $request->sort_order ?? ($milestone->tasks()->max('sort_order') + 1);

        $task = ServiceTask::create([
            'service_milestone_id' => $milestone->id,
            'name' => $request->name,
            'description' => $request->description,
            'estimated_hours' => $request->estimated_hours ?? 0,
            'included_in_price' => $request->has('included_in_price'), // ✅ FIXED: Check if checkbox is present
            'sort_order' => $sortOrder,
        ]);

        // Log the task creation in service activities
        ServiceActivity::log(
            $service->id,
            'structure_added',
            'added task "' . $task->name . '" to milestone "' . $milestone->name . '"',
            ['Task' => ['old' => null, 'new' => $task->name . ' (in ' . $milestone->name . ')']]
        );

        // Update eerst de milestone uren, dan de service uren
        $milestone->calculateAndUpdateEstimatedHours();
        $service->calculateAndUpdateEstimatedHours();

        DB::commit();

        // ✅ RETURN JSON RESPONSE for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => $task
            ]);
        }

        return redirect()->route('service-milestones.tasks.index', $milestone)
            ->with('success', 'Task created successfully');

    } catch (\Exception $e) {
        DB::rollback();
        
        // ✅ RETURN JSON ERROR for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating task: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->withInput()
            ->with('error', 'Error creating task: ' . $e->getMessage());
    }
}
    /**
     * Display the specified task
     */
    public function show(ServiceMilestone $serviceMilestone, ServiceTask $task)
    {
        // Authorization check - ROLE-BASED
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only view services from your own company.');
        }

        // Verify task belongs to milestone
        if ($task->service_milestone_id !== $serviceMilestone->id) {
            abort(404, 'Task not found for this milestone.');
        }

        // Load relationships
        $task->load(['subtasks', 'milestone.service.category']);

        return view('service-tasks.show', compact('serviceMilestone', 'task'));
    }

    /**
     * Show the form for editing the specified task
     */
    public function edit(ServiceMilestone $serviceMilestone, ServiceTask $task)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit tasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only edit tasks for services from your own company.');
        }

        // Verify task belongs to milestone
        if ($task->service_milestone_id !== $serviceMilestone->id) {
            abort(404, 'Task not found for this milestone.');
        }

        return view('service-tasks.edit', compact('serviceMilestone', 'task'));
    }

    /**
     * Update the specified task in storage
     */
    public function update(Request $request, ServiceMilestone $serviceMilestone, ServiceTask $task)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can update tasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only update tasks for services from your own company.');
        }

        // Verify task belongs to milestone
        if ($task->service_milestone_id !== $serviceMilestone->id) {
            abort(404, 'Task not found for this milestone.');
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
            if ($request->sort_order && $request->sort_order != $task->sort_order) {
                $task->updateSortOrder($request->sort_order);
            }

            $task->update([
                'name' => $request->name,
                'description' => $request->description,
                'estimated_hours' => $request->estimated_hours ?? 0,
                'included_in_price' => $request->boolean('included_in_price'),
            ]);

            // Update eerst de milestone uren, dan de service uren
            $serviceMilestone->calculateAndUpdateEstimatedHours();
            $serviceMilestone->service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return redirect()->route('service-milestones.tasks.index', $serviceMilestone)
                ->with('success', 'Task updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating task: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified task from storage
     */
    public function destroy(ServiceMilestone $serviceMilestone, ServiceTask $task)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete tasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only delete tasks for services from your own company.');
        }

        // Verify task belongs to milestone
        if ($task->service_milestone_id !== $serviceMilestone->id) {
            abort(404, 'Task not found for this milestone.');
        }

        try {
            DB::beginTransaction();

            // Check if task can be deleted
            if (!$task->canBeDeleted()) {
                return back()->with('error', 'Cannot delete task with existing subtasks. Please remove all subtasks first.');
            }

            $task->delete();

            // Update sort order van overgebleven taken
            $serviceMilestone->tasks()
                ->where('sort_order', '>', $task->sort_order)
                ->decrement('sort_order');

            // Update eerst de milestone uren, dan de service uren
            $serviceMilestone->calculateAndUpdateEstimatedHours();
            $serviceMilestone->service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return redirect()->route('service-milestones.tasks.index', $serviceMilestone)
                ->with('success', 'Task deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting task: ' . $e->getMessage());
        }
    }

    /**
     * Reorder tasks via AJAX
     */
    public function reorder(Request $request, ServiceMilestone $serviceMilestone)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'tasks' => 'required|array',
            'tasks.*' => 'exists:service_tasks,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->tasks as $index => $taskId) {
                ServiceTask::where('id', $taskId)
                    ->where('service_milestone_id', $serviceMilestone->id)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error reordering tasks'], 500);
        }
    }

    /**
     * Duplicate task with all subtasks
     */
    public function duplicate(ServiceMilestone $serviceMilestone, ServiceTask $task)
    {
        // Authorization check - ROLE-BASED
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can duplicate tasks.');
        }

        // Company isolation via service
        if (Auth::user()->role !== 'super_admin' && $serviceMilestone->service->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only duplicate tasks for services from your own company.');
        }

        // Verify task belongs to milestone
        if ($task->service_milestone_id !== $serviceMilestone->id) {
            abort(404, 'Task not found for this milestone.');
        }

        try {
            DB::beginTransaction();

            $newTask = $task->duplicate($serviceMilestone->id);

            DB::commit();

            return redirect()->route('service-milestones.tasks.index', $serviceMilestone)
                ->with('success', 'Task duplicated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error duplicating task: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get task data for editing (direct route)
     */
    public function ajaxEdit($id)
    {
        $task = ServiceTask::findOrFail($id);
        
        // Check authorization via milestone->service
        $service = $task->milestone->service;
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Always return JSON for this AJAX method
        return response()->json($task);
    }

    /**
     * AJAX: Update task (direct route)
     */
    public function ajaxUpdate(Request $request, $id)
    {
        $task = ServiceTask::findOrFail($id);
        $service = $task->milestone->service;
        
        // Check authorization
        if (Auth::user()->role !== 'super_admin' && $service->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'task_estimated_hours' => 'nullable|numeric|min:0',
            'task_included' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $task->update([
                'name' => $validated['task_name'],
                'description' => $validated['task_description'] ?? null,
                'estimated_hours' => $validated['task_estimated_hours'] ?? 0,
                'included_in_price' => $request->boolean('task_included')
            ]);

            // Update milestone and service hours
            $task->milestone->calculateAndUpdateEstimatedHours();
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Task updated successfully']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Delete task (direct route)
     */
    public function ajaxDestroy($id)
    {
        $task = ServiceTask::findOrFail($id);
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

            // Delete task and all its subtasks (cascade)
            $task->delete();

            // Update sort order
            $milestone->tasks()
                ->where('sort_order', '>', $task->sort_order)
                ->decrement('sort_order');

            // Update milestone and service hours
            $milestone->calculateAndUpdateEstimatedHours();
            $service->calculateAndUpdateEstimatedHours();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Task deleted successfully']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}