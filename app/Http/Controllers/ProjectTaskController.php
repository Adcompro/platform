<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectTaskController extends Controller
{
    /**
     * Display tasks voor een milestone
     */
    public function index(ProjectMilestone $projectMilestone)
    {
        // Laad project voor authorization
        $project = $projectMilestone->project;
        
        // Authorization check - alleen users met toegang tot project
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) && 
            !$project->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Access denied. You do not have permission to view these tasks.');
        }

        // Laad tasks
        $tasks = $projectMilestone->tasks()
            ->orderBy('sort_order')
            ->get();

        // Bereken statistieken
        $stats = [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'total_subtasks' => 0,
            'total_estimated_hours' => $tasks->sum('estimated_hours'),
        ];

        return view('project-tasks.index', compact('project', 'projectMilestone', 'tasks', 'stats'));
    }

    /**
     * Show form voor nieuwe task
     */
    public function create(ProjectMilestone $projectMilestone)
    {
        $project = $projectMilestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create tasks.');
        }

        // Bepaal volgende sort order
        $nextSortOrder = $projectMilestone->tasks()->max('sort_order') + 1;

        return view('project-tasks.create', compact('project', 'projectMilestone', 'nextSortOrder'));
    }

    /**
     * Store nieuwe task
     */
    public function store(Request $request, ProjectMilestone $projectMilestone)
    {
        $project = $projectMilestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create tasks.');
        }

        // Validatie
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_order' => 'nullable|integer|min:0',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'fixed_price' => 'nullable|numeric|min:0|required_if:pricing_type,fixed_price',
            'hourly_rate_override' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get the next sort order
            $nextSortOrder = $projectMilestone->tasks()->max('sort_order') ?? 0;
            $nextSortOrder++;
            
            // Maak task aan met default waardes voor modal
            $task = $projectMilestone->tasks()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'pending',
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'sort_order' => $validated['sort_order'] ?? $nextSortOrder,
                'fee_type' => $validated['fee_type'] ?? 'in_fee',
                'pricing_type' => $validated['pricing_type'] ?? 'hourly_rate',
                'fixed_price' => isset($validated['pricing_type']) && $validated['pricing_type'] === 'fixed_price' ? $validated['fixed_price'] : null,
                'hourly_rate_override' => !empty($validated['hourly_rate_override']) ? $validated['hourly_rate_override'] : null,
                'estimated_hours' => $validated['estimated_hours'] ?? null,
                'source_type' => 'manual',
            ]);

            DB::commit();
            Log::info('Task created successfully', ['task_id' => $task->id, 'milestone_id' => $projectMilestone->id]);

            // Return JSON response if requested
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task created successfully',
                    'task' => $task
                ]);
            }

            return redirect()->route('project-milestones.tasks.index', $projectMilestone)
                ->with('success', 'Task created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating task', ['error' => $e->getMessage()]);
            
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
     * Display task details
     */
    public function show(ProjectMilestone $projectMilestone, ProjectTask $task)
    {
        $project = $projectMilestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) && 
            !$project->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Access denied. You do not have permission to view this task.');
        }

        // Bereken statistieken
        $stats = [
            'total_subtasks' => 0,
            'completed_subtasks' => 0,
            'estimated_hours' => $task->estimated_hours ?? 0,
            'progress_percentage' => $task->progress_percentage ?? 0,
        ];

        return view('project-tasks.show', compact('project', 'projectMilestone', 'task', 'stats'));
    }

    /**
     * Show edit form voor task
     */
    public function edit(ProjectMilestone $projectMilestone, ProjectTask $task)
    {
        $project = $projectMilestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit tasks.');
        }

        return view('project-tasks.edit', compact('project', 'projectMilestone', 'task'));
    }

    /**
     * Update task
     */
    public function update(Request $request, ProjectMilestone $projectMilestone, ProjectTask $task)
    {
        $project = $projectMilestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit tasks.');
        }

        // Validatie
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_order' => 'nullable|integer|min:0',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'fixed_price' => 'nullable|numeric|min:0|required_if:pricing_type,fixed_price',
            'hourly_rate_override' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update task
            $task->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'fee_type' => $validated['fee_type'],
                'pricing_type' => $validated['pricing_type'],
                'fixed_price' => $validated['pricing_type'] === 'fixed_price' ? $validated['fixed_price'] : null,
                'hourly_rate_override' => !empty($validated['hourly_rate_override']) ? $validated['hourly_rate_override'] : null,
                'estimated_hours' => $validated['estimated_hours'] ?? null,
            ]);

            DB::commit();
            Log::info('Task updated successfully', ['task_id' => $task->id]);

            // Return JSON response if requested
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task updated successfully',
                    'task' => $task
                ]);
            }

            return redirect()->route('project-milestones.tasks.show', [$projectMilestone, $task])
                ->with('success', 'Task updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating task', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating task: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error updating task: ' . $e->getMessage());
        }
    }

    /**
     * Delete task (soft delete with cascade option)
     */
    public function destroy(Request $request, ProjectMilestone $projectMilestone, ProjectTask $task)
    {
        $project = $projectMilestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete tasks.');
        }

        // Check if cascade delete is requested
        $cascadeDelete = $request->input('cascade_delete', false);

        // Count related items
        $subtaskCount = 0;

        // Check for time entries
        $timeEntryCount = $task->timeEntries()->count();
        

        // If there are time entries, we cannot delete
        if ($timeEntryCount > 0) {
            return back()->with('error', "Cannot delete task. It has {$timeEntryCount} time entries logged. Please remove time entries first.");
        }

        // If not cascade delete and has subtasks, show error
        if (!$cascadeDelete && $subtaskCount > 0) {
            return back()->with('warning', "This task has {$subtaskCount} subtask(s). Use the cascade delete option to delete everything.");
        }

        try {
            DB::beginTransaction();


            // Delete the task
            $task->delete();
            DB::table('project_tasks')
                ->where('id', $task->id)
                ->update(['deleted_by' => Auth::id()]);

            DB::commit();
            
            $message = $cascadeDelete 
                ? "Task and all subtasks deleted successfully ({$subtaskCount} subtasks)."
                : "Task deleted successfully.";
            
            Log::info('Task deleted', [
                'task_id' => $task->id,
                'cascade' => $cascadeDelete,
                'deleted_by' => Auth::id()
            ]);

            return redirect()->route('project-milestones.tasks.index', $projectMilestone)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting task', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting task: ' . $e->getMessage());
        }
    }

    /**
     * Update task status via AJAX
     */
    public function updateStatus(Request $request, ProjectMilestone $projectMilestone, ProjectTask $task)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,on_hold'
        ]);

        try {
            $task->update(['status' => $validated['status']]);
            
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status' => $task->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }

    /**
     * Reorder tasks via AJAX
     */
    public function reorder(Request $request, ProjectMilestone $projectMilestone)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        Log::info('Task reorder request', [
            'milestone_id' => $projectMilestone->id,
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:project_tasks,id'
        ]);

        Log::info('Task reorder validated', [
            'validated' => $validated,
            'milestone_id' => $projectMilestone->id
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['task_ids'] as $index => $taskId) {
                $updated = ProjectTask::where('id', $taskId)
                    ->where('project_milestone_id', $projectMilestone->id)
                    ->update(['sort_order' => $index]);
                
                Log::info('Task updated', [
                    'task_id' => $taskId,
                    'sort_order' => $index,
                    'milestone_id' => $projectMilestone->id,
                    'rows_affected' => $updated
                ]);
            }

            DB::commit();

            Log::info('Tasks reordered successfully', [
                'milestone_id' => $projectMilestone->id,
                'task_count' => count($validated['task_ids'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tasks reordered successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error reordering tasks', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'milestone_id' => $projectMilestone->id,
                'task_ids' => $validated['task_ids'] ?? null
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error reordering tasks: ' . $e->getMessage()
            ], 500);
        }
    }
}