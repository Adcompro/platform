<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\ProjectSubtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectSubtaskController extends Controller
{
    /**
     * Display subtasks voor een task
     */
    public function index(ProjectTask $projectTask)
    {
        // Laad milestone en project voor authorization
        $milestone = $projectTask->milestone;
        $project = $milestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) && 
            !$project->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Access denied. You do not have permission to view these subtasks.');
        }

        // Laad subtasks
        $subtasks = $projectTask->subtasks()
            ->orderBy('sort_order')
            ->get();

        // Bereken statistieken
        $stats = [
            'total_subtasks' => $subtasks->count(),
            'completed_subtasks' => $subtasks->where('status', 'completed')->count(),
            'in_progress_subtasks' => $subtasks->where('status', 'in_progress')->count(),
            'total_estimated_hours' => $subtasks->sum('estimated_hours'),
        ];

        return view('project-subtasks.index', compact('project', 'milestone', 'projectTask', 'subtasks', 'stats'));
    }

    /**
     * Show form voor nieuwe subtask
     */
    public function create(ProjectTask $projectTask)
    {
        $milestone = $projectTask->milestone;
        $project = $milestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create subtasks.');
        }

        // Bepaal volgende sort order
        $nextSortOrder = $projectTask->subtasks()->max('sort_order') + 1;

        return view('project-subtasks.create', compact('project', 'milestone', 'projectTask', 'nextSortOrder'));
    }

    /**
     * Store nieuwe subtask
     */
    public function store(Request $request, ProjectTask $projectTask)
    {
        $milestone = $projectTask->milestone;
        $project = $milestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can create subtasks.');
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

            // Maak subtask aan
            $subtask = $projectTask->subtasks()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'fee_type' => $validated['fee_type'],
                'pricing_type' => $validated['pricing_type'],
                'fixed_price' => $validated['pricing_type'] === 'fixed_price' ? $validated['fixed_price'] : null,
                'hourly_rate_override' => $validated['hourly_rate_override'] ?? null,
                'estimated_hours' => $validated['estimated_hours'] ?? null,
                'source_type' => 'manual',
            ]);

            DB::commit();
            Log::info('Subtask created successfully', ['subtask_id' => $subtask->id, 'task_id' => $projectTask->id]);

            return redirect()->route('project-tasks.subtasks.index', $projectTask)
                ->with('success', 'Subtask created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating subtask', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error creating subtask: ' . $e->getMessage());
        }
    }

    /**
     * Display subtask details
     */
    public function show(ProjectTask $projectTask, ProjectSubtask $subtask)
    {
        $milestone = $projectTask->milestone;
        $project = $milestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) && 
            !$project->users()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Access denied. You do not have permission to view this subtask.');
        }

        return view('project-subtasks.show', compact('project', 'milestone', 'projectTask', 'subtask'));
    }

    /**
     * Show edit form voor subtask
     */
    public function edit(ProjectTask $projectTask, ProjectSubtask $subtask)
    {
        $milestone = $projectTask->milestone;
        $project = $milestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit subtasks.');
        }

        return view('project-subtasks.edit', compact('project', 'milestone', 'projectTask', 'subtask'));
    }

    /**
     * Update subtask
     */
    public function update(Request $request, ProjectTask $projectTask, ProjectSubtask $subtask)
    {
        $milestone = $projectTask->milestone;
        $project = $milestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied. Only administrators and project managers can edit subtasks.');
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

            // Update subtask
            $subtask->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'fee_type' => $validated['fee_type'],
                'pricing_type' => $validated['pricing_type'],
                'fixed_price' => $validated['pricing_type'] === 'fixed_price' ? $validated['fixed_price'] : null,
                'hourly_rate_override' => $validated['hourly_rate_override'] ?? null,
                'estimated_hours' => $validated['estimated_hours'] ?? null,
            ]);

            DB::commit();
            Log::info('Subtask updated successfully', ['subtask_id' => $subtask->id]);

            return redirect()->route('project-tasks.subtasks.show', [$projectTask, $subtask])
                ->with('success', 'Subtask updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating subtask', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Error updating subtask: ' . $e->getMessage());
        }
    }

    /**
     * Delete subtask (soft delete)
     */
    public function destroy(ProjectTask $projectTask, ProjectSubtask $subtask)
    {
        $milestone = $projectTask->milestone;
        $project = $milestone->project;
        
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can delete subtasks.');
        }

        // Check for time entries
        $timeEntryCount = $subtask->timeEntries()->count();
        if ($timeEntryCount > 0) {
            return back()->with('error', "Cannot delete subtask. It has {$timeEntryCount} time entries logged against it.");
        }

        try {
            DB::beginTransaction();

            // Soft delete (keeps audit trail)
            $subtask->delete();

            // Store who deleted it
            DB::table('project_subtasks')
                ->where('id', $subtask->id)
                ->update(['deleted_by' => Auth::id()]);

            DB::commit();
            Log::info('Subtask soft deleted successfully', [
                'subtask_id' => $subtask->id,
                'deleted_by' => Auth::id()
            ]);

            return redirect()->route('project-tasks.subtasks.index', $projectTask)
                ->with('success', 'Subtask deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting subtask', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting subtask: ' . $e->getMessage());
        }
    }

    /**
     * Reorder subtasks via drag & drop
     */
    public function reorder(Request $request, ProjectTask $projectTask)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'subtask_ids' => 'required|array',
            'subtask_ids.*' => 'exists:project_subtasks,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['subtask_ids'] as $index => $subtaskId) {
                ProjectSubtask::where('id', $subtaskId)
                    ->where('project_task_id', $projectTask->id)
                    ->update(['sort_order' => $index]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Subtask order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error reordering subtasks', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subtask order'
            ], 500);
        }
    }

    /**
     * Update subtask status via AJAX
     */
    public function updateStatus(Request $request, ProjectTask $projectTask, ProjectSubtask $subtask)
    {
        // Authorization check
        if (!in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,on_hold'
        ]);

        try {
            $subtask->update(['status' => $validated['status']]);
            
            // Update parent task progress als nodig
            if ($validated['status'] === 'completed') {
                $allSubtasksCompleted = $projectTask->subtasks()
                    ->where('status', '!=', 'completed')
                    ->count() === 0;
                
                if ($allSubtasksCompleted && $projectTask->status !== 'completed') {
                    $projectTask->update(['status' => 'completed']);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status' => $subtask->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }
}