<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectMilestoneController extends Controller
{
    /**
     * Display milestones voor een project
     */
    public function index(Project $project)
    {
        // Authorization check - alleen users met toegang tot project
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) && 
            !$project->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Access denied. You do not have permission to view this project.');
        }

        // Laad milestones met relaties
        $milestones = $project->milestones()
            ->with(['tasks' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->orderBy('start_date')
            ->get();

        // Bereken statistieken
        $stats = [
            'total_milestones' => $milestones->count(),
            'completed_milestones' => $milestones->where('status', 'completed')->count(),
            'in_progress_milestones' => $milestones->where('status', 'in_progress')->count(),
            'total_estimated_hours' => $milestones->sum('estimated_hours'),
            'in_fee_count' => $milestones->where('fee_type', 'in_fee')->count(),
            'extended_count' => $milestones->where('fee_type', 'extended')->count(),
        ];

        return view('project-milestones.index', compact('project', 'milestones', 'stats'));
    }

    /**
     * Show form voor nieuwe milestone
     */
    public function create(Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create milestones.');
        }

        // Bepaal volgende sort order
        $nextSortOrder = $project->milestones()->max('sort_order') + 1;

        return view('project-milestones.create', compact('project', 'nextSortOrder'));
    }

    /**
     * Store nieuwe milestone
     */
    public function store(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create milestones.');
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
            'invoicing_trigger' => 'required|in:completion,approval,delivery',
            'deliverables' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get the next sort order
            $nextSortOrder = $project->milestones()->max('sort_order') ?? 0;
            $nextSortOrder++;
            
            // Maak milestone aan met default waardes voor modal
            $milestone = $project->milestones()->create([
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
                'invoicing_trigger' => $validated['invoicing_trigger'] ?? 'completion',
                'deliverables' => $validated['deliverables'] ?? null,
                'source_type' => 'manual',
            ]);

            DB::commit();
            Log::info('Milestone created successfully', ['milestone_id' => $milestone->id, 'project_id' => $project->id]);

            // Return JSON response if requested
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Milestone created successfully',
                    'milestone' => $milestone
                ]);
            }

            return redirect()->route('projects.milestones.index', $project)
                ->with('success', 'Milestone created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating milestone', ['error' => $e->getMessage()]);
            
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
     * Display milestone details
     */
    public function show(Project $project, ProjectMilestone $milestone)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) && 
            !$project->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Access denied. You do not have permission to view this milestone.');
        }

        // Laad tasks met subtasks
        $milestone->load(['tasks' => function($query) {
            $query->with('subtasks')->orderBy('sort_order');
        }]);

        // Bereken statistieken
        $stats = [
            'total_tasks' => $milestone->tasks->count(),
            'completed_tasks' => $milestone->tasks->where('status', 'completed')->count(),
            'total_subtasks' => $milestone->tasks->sum(fn($task) => $task->subtasks->count()),
            'estimated_hours' => $milestone->estimated_hours ?? 0,
            'actual_hours' => 0, // TODO: implement when time tracking is ready
            'progress_percentage' => $milestone->progress_percentage,
        ];

        return view('project-milestones.show', compact('project', 'milestone', 'stats'));
    }

    /**
     * Show edit form voor milestone
     */
    public function edit(Project $project, ProjectMilestone $milestone)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit milestones.');
        }

        return view('project-milestones.edit', compact('project', 'milestone'));
    }

    /**
     * Update milestone
     */
    public function update(Request $request, Project $project, ProjectMilestone $milestone)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit milestones.');
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
            'invoicing_trigger' => 'required|in:completion,approval,delivery',
            'deliverables' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Update milestone
            $milestone->update([
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
                'invoicing_trigger' => $validated['invoicing_trigger'],
                'deliverables' => $validated['deliverables'] ?? null,
            ]);

            DB::commit();
            Log::info('Milestone updated successfully', ['milestone_id' => $milestone->id]);

            // Return JSON response if requested
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Milestone updated successfully',
                    'milestone' => $milestone
                ]);
            }

            return redirect()->route('projects.milestones.show', [$project, $milestone])
                ->with('success', 'Milestone updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating milestone', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating milestone: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error updating milestone: ' . $e->getMessage());
        }
    }

    /**
     * Delete milestone (soft delete with cascade option)
     */
    public function destroy(Request $request, Project $project, ProjectMilestone $milestone)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete milestones.');
        }

        // Check if cascade delete is requested
        $cascadeDelete = $request->input('cascade_delete', false);

        // Count related items
        $taskCount = $milestone->tasks()->count();
        $subtaskCount = 0;
        foreach ($milestone->tasks as $task) {
            $subtaskCount += $task->subtasks()->count();
        }

        // Check for time entries on milestone level
        $timeEntryCount = $milestone->timeEntries()->count();
        
        // Check for time entries on tasks and subtasks
        foreach ($milestone->tasks as $task) {
            $timeEntryCount += $task->timeEntries()->count();
            foreach ($task->subtasks as $subtask) {
                $timeEntryCount += $subtask->timeEntries()->count();
            }
        }

        // If there are time entries, we cannot delete
        if ($timeEntryCount > 0) {
            return back()->with('error', "Cannot delete milestone. It has {$timeEntryCount} time entries logged. Please remove time entries first.");
        }

        // If not cascade delete and has related items, show error
        if (!$cascadeDelete && ($taskCount > 0 || $subtaskCount > 0)) {
            $message = "This milestone has {$taskCount} task(s)";
            if ($subtaskCount > 0) {
                $message .= " and {$subtaskCount} subtask(s)";
            }
            $message .= ". Use the cascade delete option to delete everything.";
            return back()->with('warning', $message);
        }

        try {
            DB::beginTransaction();

            // If cascade delete, delete all subtasks and tasks first
            if ($cascadeDelete) {
                // Delete all subtasks first
                foreach ($milestone->tasks as $task) {
                    foreach ($task->subtasks as $subtask) {
                        $subtask->delete();
                        DB::table('project_subtasks')
                            ->where('id', $subtask->id)
                            ->update(['deleted_by' => Auth::id()]);
                    }
                    
                    // Then delete the task
                    $task->delete();
                    DB::table('project_tasks')
                        ->where('id', $task->id)
                        ->update(['deleted_by' => Auth::id()]);
                }
                
                Log::info('Cascade deleted tasks and subtasks for milestone', [
                    'milestone_id' => $milestone->id,
                    'task_count' => $taskCount,
                    'subtask_count' => $subtaskCount,
                    'deleted_by' => Auth::id()
                ]);
            }

            // Finally delete the milestone
            $milestone->delete();
            DB::table('project_milestones')
                ->where('id', $milestone->id)
                ->update(['deleted_by' => Auth::id()]);

            DB::commit();
            
            $message = $cascadeDelete 
                ? "Milestone and all related items deleted successfully ({$taskCount} tasks, {$subtaskCount} subtasks)."
                : "Milestone deleted successfully.";
            
            Log::info('Milestone deleted', [
                'milestone_id' => $milestone->id,
                'cascade' => $cascadeDelete,
                'deleted_by' => Auth::id()
            ]);

            return redirect()->route('projects.milestones.index', $project)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting milestone', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting milestone: ' . $e->getMessage());
        }
    }

    /**
     * Update milestone status via AJAX
     */
    public function updateStatus(Request $request, Project $project, ProjectMilestone $milestone)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,on_hold'
        ]);

        try {
            $milestone->update(['status' => $validated['status']]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status' => $milestone->status,
                'badge_class' => $milestone->status_badge_class
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }

    /**
     * Reorder milestones via AJAX
     */
    public function reorder(Request $request, Project $project)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'milestone_ids' => 'required|array',
            'milestone_ids.*' => 'exists:project_milestones,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['milestone_ids'] as $index => $milestoneId) {
                ProjectMilestone::where('id', $milestoneId)
                    ->where('project_id', $project->id)
                    ->update(['sort_order' => $index]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Milestones reordered successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error reordering milestones'
            ], 500);
        }
    }
}