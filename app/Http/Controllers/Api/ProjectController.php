<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Get work items (milestones and tasks) for a project
     */
    public function getWorkItems(Project $project)
    {
        Log::info('getWorkItems called', ['project_id' => $project->id, 'user_id' => Auth::id()]);

        // Controleer of gebruiker toegang heeft tot dit project
        if (!$this->userCanAccessProject($project)) {
            Log::warning('Unauthorized access attempt', ['project_id' => $project->id, 'user_id' => Auth::id()]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Haal milestones met taken op, alleen actieve items
        $milestones = $project->milestones()
            ->with(['tasks' => function($query) {
                $query->where('status', '!=', 'completed')
                      ->orderBy('sort_order');
            }])
            ->where('status', '!=', 'completed')
            ->orderBy('sort_order')
            ->get();

        // Filter milestones die taken hebben
        $milestonesWithTasks = $milestones->filter(function($milestone) {
            return $milestone->tasks->count() > 0;
        });

        // Transform data voor frontend - ensure it's a proper array
        $workItems = $milestonesWithTasks->map(function($milestone) {
            return [
                'id' => $milestone->id,
                'name' => $milestone->name,
                'tasks' => $milestone->tasks->map(function($task) {
                    return [
                        'id' => $task->id,
                        'name' => $task->name,
                        'status' => $task->status
                    ];
                })->values()->toArray()
            ];
        })->values()->toArray();

        Log::info('Returning work items', ['count' => count($workItems), 'data' => $workItems]);

        return response()->json($workItems);
    }

    /**
     * Check if user can access project
     */
    private function userCanAccessProject(Project $project): bool
    {
        $user = Auth::user();

        // Super admin heeft altijd toegang
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admin kan alleen projecten van eigen company zien
        if ($user->role === 'admin') {
            return $project->company_id === $user->company_id;
        }

        // Project manager en users moeten toegewezen zijn aan het project
        return $project->users()->where('user_id', $user->id)->exists();
    }
}