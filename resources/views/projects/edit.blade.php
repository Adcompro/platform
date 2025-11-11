@extends('layouts.app')

@section('title', 'Edit Project - ' . $project->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('projects.show', $project) }}" class="hover:opacity-70" style="color: var(--theme-text-muted);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div>
                            <h2 class="font-semibold leading-tight" style="font-size: calc(var(--theme-font-size) + 6px); color: var(--theme-text);">
                                Edit Project
                            </h2>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">{{ $project->name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Project Tasks Section --}}
        <div class="bg-white/60 backdrop-blur-sm rounded-xl" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3); box-shadow: var(--theme-card-shadow);">
            <div class="px-6 py-4 border-b" style="border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2);">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold" style="font-size: calc(var(--theme-font-size) + 4px); color: var(--theme-text);">
                        <i class="fas fa-tasks mr-2" style="color: var(--theme-primary);"></i>
                        Project Tasks
                    </h3>

                    {{-- Monthly Navigation --}}
                    <div class="flex items-center space-x-4">
                        @php
                            $currentMonth = request('month', now()->format('Y-m'));
                            $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->startOfMonth();
                            $monthEnd = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->endOfMonth();
                            $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
                            $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');
                        @endphp

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('projects.edit', $project) }}?month={{ $prevMonth }}"
                               class="p-2 rounded-md hover:bg-gray-100 transition-colors"
                               style="color: var(--theme-text-muted);"
                               title="Previous Month">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>

                            <div class="text-center min-w-32">
                                <div class="font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                    {{ $monthStart->format('F Y') }}
                                </div>
                                <div class="text-xs" style="color: var(--theme-text-muted);">
                                    {{ $monthStart->format('M j') }} - {{ $monthEnd->format('M j') }}
                                </div>
                            </div>

                            <a href="{{ route('projects.edit', $project) }}?month={{ $nextMonth }}"
                               class="p-2 rounded-md hover:bg-gray-100 transition-colors"
                               style="color: var(--theme-text-muted);"
                               title="Next Month">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>

                            @if($currentMonth !== now()->format('Y-m'))
                                <a href="{{ route('projects.edit', $project) }}"
                                   class="ml-2 px-3 py-1 rounded-md text-xs font-medium transition-colors"
                                   style="background-color: var(--theme-primary); color: white;"
                                   title="Current Month">
                                    Today
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div id="milestones-container" class="space-y-6">
                    @php
                        // Filter milestones for the selected month
                        $allMilestones = $project->milestones()->orderBy('sort_order')->get();
                        $filteredMilestones = $allMilestones->filter(function($milestone) use ($monthStart, $monthEnd) {
                            $created = $milestone->created_at;
                            $ended = $milestone->end_date;

                            // Show milestone if:
                            // 1. Created before/during month AND (no end date OR ended after month start)
                            // 2. OR milestone is in_progress (should show in future months until completed)
                            return ($created <= $monthEnd && ($ended === null || $ended >= $monthStart)) ||
                                   ($milestone->status === 'in_progress' && $created <= $monthEnd);
                        });
                    @endphp

                    @forelse($filteredMilestones as $milestone)
                        {{-- Milestone Card --}}
                        <div class="milestone-card rounded-lg p-6 sortable-milestone"
                             style="border: 1px solid rgba(var(--theme-primary-rgb), 0.3); background: rgba(var(--theme-primary-rgb), 0.05);"
                             data-milestone-id="{{ $milestone->id }}">
                            {{-- Milestone Header --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                        <div class="milestone-handle cursor-grab hover:cursor-grabbing opacity-60 hover:opacity-100 transition-opacity"
                                             title="Drag to reorder milestone">
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M7 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 14a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 18a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM17 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM17 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM17 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM17 14a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM17 18a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="w-3 h-3 rounded-full" style="background-color: var(--theme-primary);"></div>
                                    <h4 class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">
                                        {{ $milestone->name }}
                                    </h4>
                                    @if($milestone->description)
                                        <span class="text-sm" style="color: var(--theme-text-muted);">{{ Str::limit($milestone->description, 50) }}</span>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-4">
                                    {{-- Status Badge --}}
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $milestone->status === 'completed' ? 'bg-green-100 text-green-800' : ($milestone->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($milestone->status) }}
                                    </span>

                                    {{-- Action Buttons --}}
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('projects.milestones.edit', [$project, $milestone]) }}"
                                               class="p-2 rounded-md hover:bg-white/60 transition-colors"
                                               style="color: var(--theme-primary);"
                                               title="Edit Milestone">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>

                                            <a href="{{ route('project-milestones.tasks.create', $milestone) }}"
                                               class="p-2 rounded-md hover:bg-white/60 transition-colors"
                                               style="color: var(--theme-accent);"
                                               title="Add Task">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Tasks Section --}}
                            @php
                                // Filter tasks for the selected month
                                $allTasks = $milestone->tasks()->orderBy('sort_order')->get();
                                $filteredTasks = $allTasks->filter(function($task) use ($monthStart, $monthEnd) {
                                    $created = $task->created_at;
                                    $completed = $task->completed_at;

                                    // Show task if:
                                    // 1. Created before/during month AND (no completion date OR completed after month start)
                                    // 2. OR task is in_progress (should show in future months until completed)
                                    return ($created <= $monthEnd && ($completed === null || $completed >= $monthStart)) ||
                                           ($task->status === 'in_progress' && $created <= $monthEnd);
                                });
                            @endphp

                            @if($filteredTasks->count() > 0)
                                <div class="mt-4 space-y-2">
                                    {{-- Tasks Cards --}}
                                    @foreach($filteredTasks as $task)
                                        <div class="ml-6 p-3 rounded-md sortable-task"
                                             style="background: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.2);"
                                             data-task-id="{{ $task->id }}"
                                             data-milestone-id="{{ $milestone->id }}">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-3">
                                                        {{-- Task Drag Handle --}}
                                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                            <div class="task-handle cursor-grab hover:cursor-grabbing opacity-60 hover:opacity-100 transition-opacity"
                                                                 title="Drag to reorder task">
                                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                                                </svg>
                                                            </div>
                                                        @endif

                                                        {{-- Task Name --}}
                                                        <div class="flex-1">
                                                            <h5 class="font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                                                {{ $task->name }}
                                                            </h5>
                                                            @if($task->description)
                                                                <p class="text-sm mt-1" style="color: var(--theme-text-muted);">
                                                                    {{ Str::limit($task->description, 100) }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center space-x-3">
                                                    {{-- Status Badge --}}
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' : ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                    </span>

                                                    {{-- Task Action Buttons --}}
                                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                        <div class="flex items-center space-x-1">
                                                            <a href="{{ route('project-milestones.tasks.edit', [$milestone, $task]) }}"
                                                               class="p-1.5 rounded hover:bg-white/60 transition-colors"
                                                               style="color: var(--theme-primary);"
                                                               title="Edit Task">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                            </a>
                                                            <form action="{{ route('project-milestones.tasks.destroy', [$milestone, $task]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="p-1.5 rounded hover:bg-white/60 transition-colors"
                                                                        style="color: #dc2626;"
                                                                        title="Delete Task">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- No tasks for this month in this milestone --}}
                                @if($milestone->tasks->count() > 0)
                                    <div class="mt-4 ml-6 p-3 rounded-md text-center" style="background: rgba(var(--theme-accent-rgb), 0.05); border: 1px dashed rgba(var(--theme-accent-rgb), 0.3);">
                                        <p class="text-sm" style="color: var(--theme-text-muted);">
                                            No tasks active in {{ $monthStart->format('F Y') }} for this milestone
                                        </p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center">
                            @if($allMilestones->count() === 0)
                                {{-- No milestones at all --}}
                                <h3 class="mt-2 font-medium" style="color: var(--theme-text);">No milestones yet</h3>
                                <p class="mt-1" style="color: var(--theme-text-muted);">Get started by creating your first milestone.</p>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                <div class="mt-6">
                                    <a href="{{ route('projects.milestones.create', $project) }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                                       style="background-color: var(--theme-primary);">
                                        Create your first milestone
                                    </a>
                                </div>
                                @endif
                            @else
                                {{-- No milestones for this specific month --}}
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v4m-3 8a3 3 0 1 0 6 0 3 3 0 0 0-6 0z"/>
                                    </svg>
                                    <h3 class="mt-2 font-medium" style="color: var(--theme-text);">No milestones for {{ $monthStart->format('F Y') }}</h3>
                                    <p class="mt-1" style="color: var(--theme-text-muted);">
                                        No milestones were active during this month. Try browsing other months or create a new milestone.
                                    </p>
                                    <div class="mt-4 flex justify-center space-x-3">
                                        <a href="{{ route('projects.edit', $project) }}"
                                           class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            View All Months
                                        </a>
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <a href="{{ route('projects.milestones.create', $project) }}"
                                               class="inline-flex items-center px-3 py-2 border border-transparent rounded-md text-sm font-medium text-white"
                                               style="background-color: var(--theme-primary);">
                                                Create Milestone
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforelse

                    {{-- Add Milestone Button for when there are existing milestones --}}
                    @if($project->milestones->count() > 0 && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <div class="mt-6 text-center">
                            <a href="{{ route('projects.milestones.create', $project) }}"
                               class="inline-flex items-center px-4 py-2 border rounded-md font-medium transition-colors"
                               style="border-color: var(--theme-primary); color: var(--theme-primary); background: white;">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Milestone
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Drag & Drop --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDragDrop();
});

// ========================================
// DRAG & DROP FUNCTIONS
// ========================================

function initializeDragDrop() {
    console.log('Initializing drag & drop...');

    // Check if Sortable is available
    if (typeof Sortable === 'undefined') {
        console.warn('Sortable.js not loaded - drag & drop disabled');
        return;
    }

    // Initialize milestone sorting
    const milestonesContainer = document.getElementById('milestones-container');
    if (milestonesContainer) {
        new Sortable(milestonesContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.milestone-handle',
            draggable: '.sortable-milestone',
            onEnd: function(evt) {
                const milestoneIds = Array.from(milestonesContainer.children).map(item =>
                    parseInt(item.getAttribute('data-milestone-id'))
                ).filter(id => !isNaN(id));

                console.log('New milestone order:', milestoneIds);
                reorderMilestones(milestoneIds);
            }
        });
        console.log('Milestone sorting initialized');
    }

    // Initialize task sorting for each milestone
    initializeTaskSorting();
}

function initializeTaskSorting() {
    document.querySelectorAll('.sortable-milestone').forEach(milestone => {
        const milestoneId = milestone.getAttribute('data-milestone-id');
        const tasksContainer = milestone.querySelector('.space-y-2');

        if (tasksContainer && tasksContainer.children.length > 1) {
            new Sortable(tasksContainer, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.task-handle',
                draggable: '.sortable-task',
                onEnd: function(evt) {
                    const taskIds = Array.from(tasksContainer.children).map(item =>
                        parseInt(item.getAttribute('data-task-id'))
                    ).filter(id => !isNaN(id));

                    console.log('New task order for milestone', milestoneId, ':', taskIds);
                    reorderTasks(milestoneId, taskIds);
                }
            });
        }
    });
    console.log('Task sorting initialized');
}

// Reorder functions
async function reorderMilestones(milestoneIds) {
    try {
        const projectId = window.location.pathname.split('/')[2];
        const response = await fetch('/projects/' + projectId + '/milestones/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ milestone_ids: milestoneIds })
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to reorder milestones');
        }
        console.log('Milestones reordered successfully');
    } catch (error) {
        console.error('Error reordering milestones:', error);
        alert('Failed to save milestone order. Please refresh the page.');
    }
}

async function reorderTasks(milestoneId, taskIds) {
    try {
        const response = await fetch('/project-milestones/' + milestoneId + '/tasks/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ task_ids: taskIds })
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to reorder tasks');
        }
        console.log('Tasks reordered successfully');
    } catch (error) {
        console.error('Error reordering tasks:', error);
        alert('Failed to save task order. Please refresh the page.');
    }
}
</script>

{{-- CSS for drag & drop visual feedback --}}
<style>
.sortable-ghost {
    opacity: 0.4;
    background: #f3f4f6 !important;
    border: 2px dashed #d1d5db !important;
}

.sortable-chosen {
    cursor: grabbing !important;
    transform: rotate(2deg);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.sortable-drag {
    background: white !important;
    border: 1px solid #3b82f6 !important;
    z-index: 9999 !important;
}

.sortable-milestone:hover {
    cursor: grab;
}

.sortable-task:hover {
    cursor: grab;
}
</style>

@endsection
