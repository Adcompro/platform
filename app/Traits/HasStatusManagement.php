<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HasStatusManagement
{
    /**
     * Boot the trait
     */
    public static function bootHasStatusManagement()
    {
        // Wanneer status wijzigt, update parent items
        static::updated(function ($model) {
            if ($model->isDirty('status')) {
                $model->cascadeStatusUpdate();
            }
        });
    }

    /**
     * Start werk aan dit item (zet status naar in_progress)
     */
    public function startWork(): bool
    {
        if ($this->status === 'pending') {
            $this->status = 'in_progress';
            $this->save();
            
            Log::info('Work started on ' . class_basename($this), [
                'id' => $this->id,
                'name' => $this->name ?? 'Unknown'
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Markeer item als voltooid
     */
    public function markAsCompleted(): bool
    {
        if ($this->status !== 'completed') {
            $this->status = 'completed';
            $this->save();
            
            Log::info('Item marked as completed: ' . class_basename($this), [
                'id' => $this->id,
                'name' => $this->name ?? 'Unknown'
            ]);
            
            // Check of parent ook compleet is
            $this->checkParentCompletion();
            
            return true;
        }
        
        return false;
    }

    /**
     * Zet item on hold
     */
    public function putOnHold(): bool
    {
        if ($this->status === 'in_progress') {
            $this->status = 'on_hold';
            $this->save();
            
            Log::info('Item put on hold: ' . class_basename($this), [
                'id' => $this->id,
                'name' => $this->name ?? 'Unknown'
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Hervat werk aan item
     */
    public function resumeWork(): bool
    {
        if ($this->status === 'on_hold') {
            $this->status = 'in_progress';
            $this->save();
            
            Log::info('Work resumed on ' . class_basename($this), [
                'id' => $this->id,
                'name' => $this->name ?? 'Unknown'
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Update parent status op basis van children
     */
    protected function cascadeStatusUpdate()
    {
        // Voor ProjectSubtask -> update Task
        if ($this instanceof \App\Models\ProjectSubtask) {
            $this->updateParentTaskStatus();
        }
        
        // Voor ProjectTask -> update Milestone
        if ($this instanceof \App\Models\ProjectTask) {
            $this->updateParentMilestoneStatus();
        }
        
        // Voor ProjectMilestone -> update Project
        if ($this instanceof \App\Models\ProjectMilestone) {
            $this->updateParentProjectStatus();
        }
    }

    /**
     * Update task status op basis van subtasks
     */
    protected function updateParentTaskStatus()
    {
        if (!$this->task) {
            return;
        }

        $task = $this->task;
        $subtasks = $task->subtasks;
        
        if ($subtasks->isEmpty()) {
            return;
        }

        $allCompleted = $subtasks->every(fn($s) => $s->status === 'completed');
        $anyInProgress = $subtasks->contains(fn($s) => $s->status === 'in_progress');
        $allPending = $subtasks->every(fn($s) => $s->status === 'pending');
        $anyOnHold = $subtasks->contains(fn($s) => $s->status === 'on_hold');

        $newStatus = null;

        if ($allCompleted) {
            $newStatus = 'completed';
        } elseif ($anyInProgress) {
            $newStatus = 'in_progress';
        } elseif ($anyOnHold && !$anyInProgress) {
            $newStatus = 'on_hold';
        } elseif ($allPending) {
            $newStatus = 'pending';
        } else {
            $newStatus = 'in_progress'; // Mixed status = in progress
        }

        if ($task->status !== $newStatus) {
            $task->status = $newStatus;
            $task->save();
        }
    }

    /**
     * Update milestone status op basis van tasks
     */
    protected function updateParentMilestoneStatus()
    {
        if (!$this->milestone) {
            return;
        }

        $milestone = $this->milestone;
        $tasks = $milestone->tasks;
        
        if ($tasks->isEmpty()) {
            return;
        }

        $allCompleted = $tasks->every(fn($t) => $t->status === 'completed');
        $anyInProgress = $tasks->contains(fn($t) => $t->status === 'in_progress');
        $allPending = $tasks->every(fn($t) => $t->status === 'pending');
        $anyOnHold = $tasks->contains(fn($t) => $t->status === 'on_hold');

        $newStatus = null;

        if ($allCompleted) {
            $newStatus = 'completed';
        } elseif ($anyInProgress) {
            $newStatus = 'in_progress';
        } elseif ($anyOnHold && !$anyInProgress) {
            $newStatus = 'on_hold';
        } elseif ($allPending) {
            $newStatus = 'pending';
        } else {
            $newStatus = 'in_progress';
        }

        if ($milestone->status !== $newStatus) {
            $milestone->status = $newStatus;
            $milestone->save();
        }
    }

    /**
     * Update project status op basis van milestones
     */
    protected function updateParentProjectStatus()
    {
        if (!$this->project) {
            return;
        }

        $project = $this->project;
        $milestones = $project->milestones;
        
        if ($milestones->isEmpty()) {
            return;
        }

        $allCompleted = $milestones->every(fn($m) => $m->status === 'completed');
        $anyInProgress = $milestones->contains(fn($m) => $m->status === 'in_progress');
        $allPending = $milestones->every(fn($m) => $m->status === 'pending');
        
        // Project heeft andere status opties
        $newStatus = null;

        if ($allCompleted) {
            $newStatus = 'completed';
        } elseif ($anyInProgress || (!$allPending && !$allCompleted)) {
            $newStatus = 'active';
        } elseif ($allPending) {
            $newStatus = 'draft';
        }

        if ($newStatus && $project->status !== $newStatus) {
            $project->status = $newStatus;
            $project->save();
            
            Log::info('Project status updated based on milestones', [
                'project_id' => $project->id,
                'new_status' => $newStatus
            ]);
        }
    }

    /**
     * Check of parent item ook compleet kan worden
     */
    protected function checkParentCompletion()
    {
        // Voor subtask -> check task
        if ($this instanceof \App\Models\ProjectSubtask && $this->task) {
            $allSubtasksComplete = $this->task->subtasks->every(fn($s) => $s->status === 'completed');
            if ($allSubtasksComplete) {
                $this->task->markAsCompleted();
            }
        }
        
        // Voor task -> check milestone
        if ($this instanceof \App\Models\ProjectTask && $this->milestone) {
            $allTasksComplete = $this->milestone->tasks->every(fn($t) => $t->status === 'completed');
            if ($allTasksComplete) {
                $this->milestone->markAsCompleted();
            }
        }
        
        // Voor milestone -> check project
        if ($this instanceof \App\Models\ProjectMilestone && $this->project) {
            $allMilestonesComplete = $this->project->milestones->every(fn($m) => $m->status === 'completed');
            if ($allMilestonesComplete) {
                $this->project->markAsCompleted();
            }
        }
    }

    /**
     * Get status badge HTML voor views
     */
    public function getStatusBadgeHtml(): string
    {
        $colors = [
            'pending' => 'bg-slate-100 text-slate-700',
            'in_progress' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'on_hold' => 'bg-amber-100 text-amber-700',
            'draft' => 'bg-gray-100 text-gray-700',
            'active' => 'bg-blue-100 text-blue-700',
            'cancelled' => 'bg-red-100 text-red-700'
        ];

        $color = $colors[$this->status] ?? 'bg-gray-100 text-gray-700';
        $label = str_replace('_', ' ', ucfirst($this->status));

        return sprintf(
            '<span class="px-2 py-1 text-xs font-medium rounded-lg %s">%s</span>',
            $color,
            $label
        );
    }

    /**
     * Calculate completion percentage
     */
    public function getCompletionPercentage(): int
    {
        $childrenRelation = null;
        
        if ($this instanceof \App\Models\Project) {
            $childrenRelation = 'milestones';
        } elseif ($this instanceof \App\Models\ProjectMilestone) {
            $childrenRelation = 'tasks';
        } elseif ($this instanceof \App\Models\ProjectTask) {
            $childrenRelation = 'subtasks';
        }
        
        if (!$childrenRelation || !$this->$childrenRelation) {
            return $this->status === 'completed' ? 100 : 0;
        }
        
        $total = $this->$childrenRelation->count();
        if ($total === 0) {
            return $this->status === 'completed' ? 100 : 0;
        }
        
        $completed = $this->$childrenRelation->where('status', 'completed')->count();
        
        return (int) round(($completed / $total) * 100);
    }
}