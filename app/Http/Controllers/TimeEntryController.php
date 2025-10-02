<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
    /**
     * Display pending time entries for approval (admin/super_admin only)
     */
    public function approvals(Request $request)
    {
        // Check authorization - alleen super_admin en admin kunnen approvals zien
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can view time approvals.');
        }

        // Build query met eager loading
        $query = TimeEntry::with(['user', 'project', 'milestone', 'task', 'approver', 'deferredBy']);

        // Filter op company voor admin (super_admin ziet alles)
        if (Auth::user()->role === 'admin') {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Filter op status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: toon alleen pending entries
            $query->where('status', 'pending');
        }

        // Filter op user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter op project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter op datum range
        if ($request->filled('start_date')) {
            $query->where('entry_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('entry_date', '<=', $request->end_date);
        }

        // Sorteer op datum (nieuwste eerst)
        $timeEntries = $query->orderBy('entry_date', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(20)
                            ->withQueryString();

        // Haal filter opties op
        $users = \App\Models\User::when(Auth::user()->role === 'admin', function($q) {
                        $q->where('company_id', Auth::user()->company_id);
                    })
                    ->orderBy('name')
                    ->get();

        $projects = Project::when(Auth::user()->role === 'admin', function($q) {
                            $q->where('company_id', Auth::user()->company_id);
                        })
                        ->orderBy('name')
                        ->get();

        // Bereken statistieken
        $stats = [
            'pending_count' => TimeEntry::pending()
                                ->when(Auth::user()->role === 'admin', function($q) {
                                    $q->where('company_id', Auth::user()->company_id);
                                })
                                ->count(),
            'pending_hours' => TimeEntry::pending()
                                ->when(Auth::user()->role === 'admin', function($q) {
                                    $q->where('company_id', Auth::user()->company_id);
                                })
                                ->sum('hours'),
            'approved_today' => TimeEntry::approved()
                                ->whereDate('approved_at', today())
                                ->when(Auth::user()->role === 'admin', function($q) {
                                    $q->where('company_id', Auth::user()->company_id);
                                })
                                ->count(),
            'rejected_today' => TimeEntry::rejected()
                                ->whereDate('approved_at', today())
                                ->when(Auth::user()->role === 'admin', function($q) {
                                    $q->where('company_id', Auth::user()->company_id);
                                })
                                ->count(),
        ];

        return view('time-entries.approvals', compact('timeEntries', 'users', 'projects', 'stats'));
    }

    /**
     * Display a listing of time entries
     */
    public function index(Request $request)
    {
        Log::info('TimeEntryController@index called', [
            'user_id' => Auth::id(),
            'filters' => $request->all()
        ]);

        $user = Auth::user();
        
        // Query builder met eager loading
        $query = TimeEntry::with(['user', 'project', 'milestone', 'task', 'approver', 'deferredBy']);

        // Alleen eigen entries tenzij admin/super_admin
        if (!in_array($user->role, ['super_admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

        // Filters toepassen
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('user_id') && in_array($user->role, ['super_admin', 'admin'])) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('entry_date', '<=', $request->date_to);
        }

        // Sorteer op datum (nieuwste eerst)
        $query->orderBy('entry_date', 'desc')->orderBy('created_at', 'desc');

        // Paginatie
        $timeEntries = $query->paginate(20);

        // Get projects voor filter dropdown
        $projects = Project::whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        // Bereken statistieken
        $stats = [
            'total_hours' => $query->sum('hours'),
            'total_minutes' => $query->sum('minutes'),
            'billable_hours' => $query->where('is_billable', 'billable')->sum('hours'),
            'pending_count' => $query->where('status', 'pending')->count(),
            'this_week_hours' => $query->whereBetween('entry_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->sum('hours')
        ];

        return view('time-entries.index', compact('timeEntries', 'projects', 'stats'));
    }


    /**
     * Store a newly created time entry
     */
    public function store(Request $request)
    {
        Log::info('TimeEntryController@store called', [
            'user_id' => Auth::id(),
            'data' => $request->all()
        ]);

        try {
            // Validatie
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'work_item' => 'required|string',
                'entry_date' => 'required|date',
                'minutes' => 'required|integer|min:5|max:1440', // Max 24 uur
                'description' => 'required|string|max:1000',
            ], [
                'minutes.min' => 'Minimum time entry is 5 minutes',
                'minutes.max' => 'Maximum time entry is 24 hours (1440 minutes)',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            // Valideer dat minutes in stappen van 5 is
            if ($validated['minutes'] % 5 !== 0) {
                Log::warning('Minutes not in 5-minute increments', ['minutes' => $validated['minutes']]);
                return back()->withErrors(['minutes' => 'Time must be in 5-minute increments'])->withInput();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed'
                ], 422);
            }
            
            throw $e;
        }

        try {
            // Parse work_item (format: "milestone:1", "task:5", "subtask:12")
            if (!str_contains($validated['work_item'], ':')) {
                throw new \Exception('Invalid work_item format: ' . $validated['work_item']);
            }

            [$type, $id] = explode(':', $validated['work_item']);
            
            Log::info('Parsing work item', ['type' => $type, 'id' => $id]);

            $milestoneId = null;
            $taskId = null;
            $subtaskId = null;

            switch ($type) {
                case 'task':
                    $task = ProjectTask::findOrFail($id);
                    $taskId = $task->id;
                    $milestoneId = $task->project_milestone_id;
                    Log::info('Task parsed', ['task_id' => $taskId, 'milestone_id' => $milestoneId]);
                    break;
                    
                case 'milestone':
                    $milestoneId = $id;
                    Log::info('Milestone parsed', ['milestone_id' => $milestoneId]);
                    break;
                    
                default:
                    throw new \Exception('Invalid work item type: ' . $type);
            }
        } catch (\Exception $e) {
            Log::error('Error parsing work item', [
                'error' => $e->getMessage(),
                'work_item' => $validated['work_item']
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid work item selected',
                    'errors' => ['work_item' => ['Invalid work item selected']]
                ], 400);
            }

            return back()->withErrors(['work_item' => 'Invalid work item selected'])->withInput();
        }

        // Check dat user member is van het project
        $project = Project::findOrFail($validated['project_id']);
        
        if (!$project->users->contains(Auth::user())) {
            abort(403, 'You are not a member of this project');
        }

        try {
            DB::beginTransaction();

            // Check if user has auto-approve enabled
            $user = Auth::user();
            $status = 'pending';
            $approvedBy = null;
            $approvedAt = null;
            
            if ($user->auto_approve_time_entries) {
                $status = 'approved';
                $approvedBy = $user->id; // Self-approved
                $approvedAt = now();
                
                Log::info('Auto-approving time entry for user with auto-approve enabled', [
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
            }

            // Use original description without AI improvement
            $originalDescription = $validated['description'];
            $improvedDescription = $originalDescription;
            $aiConfidence = null;

            // Maak time entry
            $timeEntry = TimeEntry::create([
                'user_id' => Auth::id(),
                'company_id' => $project->company_id,
                'project_id' => $validated['project_id'],
                'customer_id' => $project->customer_id,
                'project_milestone_id' => $milestoneId,
                'project_task_id' => $taskId,
                'project_subtask_id' => $subtaskId,
                'entry_date' => $validated['entry_date'],
                'minutes' => $validated['minutes'],
                'hours' => round($validated['minutes'] / 60, 2),
                'description' => $improvedDescription,
                'is_billable' => 'billable', // Default to billable
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'hourly_rate_used' => $this->determineHourlyRate($project, $milestoneId, $taskId, $subtaskId),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Sla laatste entry date op in session
            Session::put('last_entry_date', $validated['entry_date']);
            
            // Update work item status naar in_progress als het nog pending is
            $this->updateWorkItemStatus($milestoneId, $taskId, $subtaskId);

            DB::commit();

            Log::info('Time entry created successfully', [
                'time_entry_id' => $timeEntry->id,
                'user_id' => Auth::id(),
                'auto_approved' => $user->auto_approve_time_entries
            ]);

            $message = $user->auto_approve_time_entries 
                ? 'Time entry created and automatically approved' 
                : 'Time entry created successfully';

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'time_entry_id' => $timeEntry->id,
                    'auto_approved' => $user->auto_approve_time_entries
                ]);
            }

            return redirect()->route('time-entries.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create time entry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'user_id' => Auth::id(),
                'validated_data' => $validated ?? 'Not available'
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create time entry',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to create time entry: ' . $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Unexpected error creating time entry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return back()->withErrors(['error' => 'Unexpected error: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Update work item status when time is logged
     */
    private function updateWorkItemStatus($milestoneId, $taskId, $subtaskId)
    {
        try {
            // Update task status als het pending is
            if ($taskId) {
                $task = \App\Models\ProjectTask::find($taskId);
                if ($task && $task->status === 'pending') {
                    $task->startWork();
                    Log::info('Task status updated to in_progress', ['task_id' => $taskId]);
                }
            }
            // Als er alleen een milestone is, update die
            elseif ($milestoneId) {
                $milestone = \App\Models\ProjectMilestone::find($milestoneId);
                if ($milestone && $milestone->status === 'pending') {
                    $milestone->startWork();
                    Log::info('Milestone status updated to in_progress', ['milestone_id' => $milestoneId]);
                }
            }
        } catch (\Exception $e) {
            // Log de error maar laat de time entry creatie doorgaan
            Log::warning('Could not update work item status', [
                'error' => $e->getMessage(),
                'milestone_id' => $milestoneId,
                'task_id' => $taskId,
                'subtask_id' => $subtaskId
            ]);
        }
    }
    
    /**
     * Return edit form HTML for modal
     */
    public function editModal(TimeEntry $timeEntry)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) &&
            $timeEntry->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check if editable
        if (!$timeEntry->is_editable) {
            return response()->json(['error' => 'This time entry cannot be edited'], 403);
        }

        $user = Auth::user();

        // Get projects
        $projects = Project::whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get work items voor selected project
        $workItems = $this->getProjectWorkItems($timeEntry->project);

        // Bepaal selected work item
        $selectedWorkItem = null;
        if ($timeEntry->project_task_id) {
            $selectedWorkItem = 'task:' . $timeEntry->project_task_id;
        } elseif ($timeEntry->project_milestone_id) {
            $selectedWorkItem = 'milestone:' . $timeEntry->project_milestone_id;
        }

        // Return modal form view
        return view('time-entries.edit-modal', compact('timeEntry', 'projects', 'workItems', 'selectedWorkItem'));
    }

    /**
     * Return show details HTML for modal
     */
    public function showModal(TimeEntry $timeEntry)
    {
        // Check authorization - anyone can view if they have access to the entry
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) &&
            $timeEntry->user_id !== Auth::id() &&
            !$timeEntry->project->users->contains(Auth::id())) {
            abort(403, 'Unauthorized');
        }

        // Return modal view
        return view('time-entries.show-modal', compact('timeEntry'));
    }

    /**
     * Update the specified time entry
     */
    public function update(Request $request, TimeEntry $timeEntry)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && 
            $timeEntry->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check if editable
        if (!$timeEntry->is_editable) {
            return redirect()->route('time-entries.show', $timeEntry)
                ->with('error', 'This time entry cannot be edited');
        }

        // Validatie
        $validated = $request->validate([
            'work_item_id' => 'required|string',
            'entry_date' => 'required|date',
            'minutes' => 'required|integer|min:5|max:1440',
            'description' => 'required|string|max:1000',
            'is_billable' => 'required|in:billable,non_billable',
        ]);

        // Valideer dat minutes in stappen van 5 is
        if ($validated['minutes'] % 5 !== 0) {
            return back()->withErrors(['minutes' => 'Time must be in 5-minute increments'])->withInput();
        }

        // Parse work_item_id
        [$type, $id] = explode(':', $validated['work_item_id']);

        $milestoneId = null;
        $taskId = null;
        $subtaskId = null;

        switch ($type) {
            case 'task':
                $task = ProjectTask::findOrFail($id);
                $taskId = $task->id;
                $milestoneId = $task->project_milestone_id;
                break;
                
            case 'milestone':
                $milestoneId = $id;
                break;
        }

        try {
            DB::beginTransaction();

            // Update time entry and reset status if needed
            $updateData = [
                'project_milestone_id' => $milestoneId,
                'project_task_id' => $taskId,
                'project_subtask_id' => $subtaskId,
                'entry_date' => $validated['entry_date'],
                'minutes' => $validated['minutes'],
                'hours' => round($validated['minutes'] / 60, 2),
                'description' => $validated['description'],
                'is_billable' => $validated['is_billable'],
                'updated_by' => Auth::id(),
            ];

            // Keep current status - no automatic reset to draft on updates

            $timeEntry->update($updateData);

            DB::commit();

            // Check if this is an AJAX request (from modal)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Time entry updated successfully'
                ]);
            }

            return redirect()->route('time-entries.index')
                ->with('success', 'Time entry updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update time entry', [
                'error' => $e->getMessage(),
                'time_entry_id' => $timeEntry->id
            ]);

            // Check if this is an AJAX request (from modal)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update time entry: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update time entry'])->withInput();
        }
    }

    /**
     * Remove the specified time entry
     */
    public function destroy(TimeEntry $timeEntry)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin']) && 
            $timeEntry->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check if deletable
        if ($timeEntry->is_invoiced || $timeEntry->status === 'approved') {
            return redirect()->route('time-entries.index')
                ->with('error', 'This time entry cannot be deleted');
        }

        try {
            $timeEntry->delete();

            return redirect()->route('time-entries.index')
                ->with('success', 'Time entry deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete time entry', [
                'error' => $e->getMessage(),
                'time_entry_id' => $timeEntry->id
            ]);

            return redirect()->route('time-entries.index')
                ->with('error', 'Failed to delete time entry');
        }
    }

    /**
     * Get work items for AJAX request
     */
    public function getWorkItems(Request $request, $project)
    {
        try {
            Log::info('Getting work items for project', [
                'project_id' => $project,
                'user_id' => Auth::id()
            ]);
            
            $project = Project::findOrFail($project);
            
            // Check dat user member is
            if (!$project->users->contains(Auth::user())) {
                Log::warning('User not member of project', [
                    'project_id' => $project->id,
                    'user_id' => Auth::id()
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $workItems = $this->getProjectWorkItems($project);
            
            Log::info('Work items loaded successfully', [
                'project_id' => $project->id,
                'work_items_count' => count($workItems)
            ]);

            return response()->json([
                'workItems' => $workItems,
                'project_name' => $project->name,
                'debug' => [
                    'project_id' => $project->id,
                    'milestones_count' => $project->milestones()->count(),
                    'user_is_member' => true
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting work items', [
                'error' => $e->getMessage(),
                'project_id' => $project,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'error' => 'Failed to load work items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get hierarchical work items voor een project
     */
    private function getProjectWorkItems(Project $project)
    {
        $items = [];

        // Haal milestones op met tasks (subtasks zijn verwijderd)
        $milestones = $project->milestones()
            ->with(['tasks'])
            ->where(function($q) {
                $q->where('status', '!=', 'completed')
                  ->orWhereNull('status');
            })
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->subDays(30)); // Toon items van afgelopen 30 dagen
            })
            ->orderBy('sort_order')
            ->get();

        foreach ($milestones as $milestone) {
            // Voeg milestone toe
            $items[] = [
                'id' => 'milestone:' . $milestone->id,
                'label' => $milestone->name,
                'type' => 'milestone',
                'is_service' => $milestone->is_service_item ?? false,
                'indent' => 0
            ];

            // Voeg tasks toe (filter op status en datum)
            foreach ($milestone->tasks as $task) {
                // Skip completed tasks en expired tasks
                if ($task->status === 'completed' || 
                    ($task->end_date && $task->end_date < now()->subDays(30))) {
                    continue;
                }
                
                $items[] = [
                    'id' => 'task:' . $task->id,
                    'label' => '→ ' . $task->name,
                    'type' => 'task',
                    'is_service' => $task->is_service_item ?? false,
                    'indent' => 1
                ];

            }
        }

        return $items;
    }

    /**
     * Helper: Bepaal uurtarief op basis van hiërarchie
     */
    private function determineHourlyRate($project, $milestoneId, $taskId, $subtaskId)
    {
        // Check task level
        if ($taskId) {
            $task = ProjectTask::find($taskId);
            if ($task && $task->hourly_rate_override) {
                return $task->hourly_rate_override;
            }
        }

        // Check milestone level
        if ($milestoneId) {
            $milestone = ProjectMilestone::find($milestoneId);
            if ($milestone && $milestone->hourly_rate_override) {
                return $milestone->hourly_rate_override;
            }
        }

        // Use project default
        return $project->default_hourly_rate ?? 0;
    }

    /**
     * Approve a time entry
     */
    public function approve(Request $request, TimeEntry $timeEntry)
    {
        // Check authorization - alleen super_admin en admin kunnen approven
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can approve time entries.');
        }

        // Admin kan alleen entries van eigen company approven
        if (Auth::user()->role === 'admin' && $timeEntry->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only approve time entries from your company.');
        }

        // Check if already processed
        if ($timeEntry->status !== 'pending') {
            return back()->with('error', 'This time entry has already been processed.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Update time entry
            $timeEntry->status = 'approved';
            $timeEntry->approved_by = Auth::id();
            $timeEntry->approved_at = now();
            $timeEntry->notes = $validated['approval_notes'] ?? $timeEntry->notes;
            $timeEntry->rejection_reason = null;
            $timeEntry->save();

            DB::commit();

            Log::info('Time entry approved', [
                'time_entry_id' => $timeEntry->id,
                'approved_by' => Auth::id()
            ]);

            return redirect()->route('time-entries.approvals')
                ->with('success', 'Time entry approved successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to approve time entry', [
                'error' => $e->getMessage(),
                'time_entry_id' => $timeEntry->id
            ]);
            return back()->with('error', 'Failed to approve time entry: ' . $e->getMessage());
        }
    }

    /**
     * Reject a time entry
     */
    public function reject(Request $request, TimeEntry $timeEntry)
    {
        // Check authorization - alleen super_admin en admin kunnen rejecten
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can reject time entries.');
        }

        // Admin kan alleen entries van eigen company rejecten
        if (Auth::user()->role === 'admin' && $timeEntry->company_id !== Auth::user()->company_id) {
            abort(403, 'Access denied. You can only reject time entries from your company.');
        }

        // Check if already processed
        if ($timeEntry->status !== 'pending') {
            return back()->with('error', 'This time entry has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Update time entry
            $timeEntry->status = 'rejected';
            $timeEntry->approved_by = Auth::id();
            $timeEntry->approved_at = now();
            $timeEntry->rejection_reason = $validated['rejection_reason'];
            $timeEntry->save();

            DB::commit();

            Log::info('Time entry rejected', [
                'time_entry_id' => $timeEntry->id,
                'rejected_by' => Auth::id(),
                'reason' => $validated['rejection_reason']
            ]);

            return redirect()->route('time-entries.approvals')
                ->with('success', 'Time entry rejected.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reject time entry', [
                'error' => $e->getMessage(),
                'time_entry_id' => $timeEntry->id
            ]);
            return back()->with('error', 'Failed to reject time entry: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve time entries
     */
    public function bulkApprove(Request $request)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can approve time entries.');
        }

        $validated = $request->validate([
            'time_entry_ids' => 'required|array',
            'time_entry_ids.*' => 'exists:time_entries,id'
        ]);

        try {
            DB::beginTransaction();

            $query = TimeEntry::whereIn('id', $validated['time_entry_ids'])
                            ->where('status', 'pending');

            // Admin kan alleen entries van eigen company approven
            if (Auth::user()->role === 'admin') {
                $query->where('company_id', Auth::user()->company_id);
            }

            $count = $query->count();

            $query->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            DB::commit();

            Log::info('Bulk approved time entries', [
                'count' => $count,
                'approved_by' => Auth::id()
            ]);

            return back()->with('success', "$count time entries approved successfully.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to bulk approve time entries', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to approve time entries: ' . $e->getMessage());
        }
    }

    /**
     * Bulk reject time entries
     */
    public function bulkReject(Request $request)
    {
        // Check authorization
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can reject time entries.');
        }

        $validated = $request->validate([
            'time_entry_ids' => 'required|array',
            'time_entry_ids.*' => 'exists:time_entries,id',
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $query = TimeEntry::whereIn('id', $validated['time_entry_ids'])
                            ->where('status', 'pending');

            // Admin kan alleen entries van eigen company rejecten
            if (Auth::user()->role === 'admin') {
                $query->where('company_id', Auth::user()->company_id);
            }

            $count = $query->count();

            $query->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason']
            ]);

            DB::commit();

            Log::info('Bulk rejected time entries', [
                'count' => $count,
                'rejected_by' => Auth::id(),
                'reason' => $validated['rejection_reason']
            ]);

            return back()->with('success', "$count time entries rejected.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to bulk reject time entries', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to reject time entries: ' . $e->getMessage());
        }
    }
}