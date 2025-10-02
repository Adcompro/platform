@extends('layouts.app')

@section('title', 'Service Structure - ' . $service->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Moderne uitstraling met glassmorphism --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    {{-- Breadcrumb Navigation --}}
                    <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1">
                            <li class="inline-flex items-center">
                                <a href="{{ route('services.index') }}" class="hover:text-slate-700 transition-colors duration-200">
                                    Services
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('services.show', $service) }}" class="hover:text-slate-700 transition-colors duration-200">
                                        {{ $service->name }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-slate-700 font-medium">Structure</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-xl font-semibold text-slate-900">Service Structure</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage milestones, tasks and subtasks for {{ $service->name }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button onclick="addMilestone()" class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Milestone
                    </button>
                    @endif
                    <a href="{{ route('services.show', $service) }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Service
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            {{-- Service Structure Panel --}}
            <div class="flex-1">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="mb-4 bg-green-50/50 border border-green-200/50 text-green-700 px-3 py-2.5 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-green-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50/50 border border-red-200/50 text-red-700 px-3 py-2.5 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Structure Content --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <div class="flex justify-between items-center">
                            <h2 class="text-base font-medium text-slate-900">Service Structure</h2>
                            <div class="text-xs text-slate-500">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                    {{ $service->milestones->count() }} milestones
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 ml-1">
                                    {{ $service->milestones->sum(function($m) { return $m->tasks->count(); }) }} tasks
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 ml-1">
                                    {{ $service->milestones->sum(function($m) { return $m->tasks->sum(function($t) { return $t->subtasks->count(); }); }) }} subtasks
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        @if($service->milestones->count() > 0)
                            <div id="milestones-container" class="space-y-4">
                                @foreach($service->milestones->sortBy('sort_order') as $milestone)
                                    <div class="milestone-item border border-slate-200/60 rounded-lg hover:shadow-md transition-all duration-200" data-milestone-id="{{ $milestone->id }}">
                                        {{-- Milestone Header --}}
                                        <div class="bg-slate-50/50 px-3 py-2.5 border-b border-slate-200/50 flex justify-between items-center cursor-move">
                                            <div class="flex items-center space-x-3">
                                                <div class="drag-handle text-slate-400 hover:text-slate-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                    </svg>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M3 5a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V5zM3 11a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM9 15a1 1 0 011-1h6a1 1 0 110 2h-6a1 1 0 01-1-1z"/>
                                                    </svg>
                                                    <h3 class="text-sm font-semibold text-slate-900">{{ $milestone->name }}</h3>
                                                    @if($milestone->included_in_price)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                            Included
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                            Extra
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-slate-500">
                                                    {{ $milestone->estimated_hours ?? 0 }}h
                                                </span>
                                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                <button onclick="addTask({{ $milestone->id }})" class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-100 rounded transition-all duration-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                                <button onclick="editMilestone({{ $milestone->id }})" class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-100 rounded transition-all duration-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                @endif
                                                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                                <button onclick="deleteMilestone({{ $milestone->id }})" class="text-red-400 hover:text-red-600 p-1 hover:bg-red-50 rounded transition-all duration-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Milestone Description --}}
                                        @if($milestone->description)
                                        <div class="px-3 py-2 bg-slate-25 border-b border-slate-100">
                                            <p class="text-xs text-slate-600">{{ $milestone->description }}</p>
                                        </div>
                                        @endif

                                        {{-- Tasks Container --}}
                                        <div class="tasks-container" data-milestone-id="{{ $milestone->id }}">
                                            @if($milestone->tasks->count() > 0)
                                                <div class="space-y-2 p-3">
                                                    @foreach($milestone->tasks->sortBy('sort_order') as $task)
                                                        <div class="task-item bg-slate-50/50 border border-slate-200/50 rounded-md hover:shadow-sm transition-all duration-200" data-task-id="{{ $task->id }}">
                                                            {{-- Task Header --}}
                                                            <div class="px-2.5 py-2 flex justify-between items-center">
                                                                <div class="flex items-center space-x-2">
                                                                    <div class="task-drag-handle text-slate-400 hover:text-slate-600 cursor-move">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="flex items-center space-x-2">
                                                                        <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 012-2h2a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                        <span class="text-xs font-medium text-slate-900">{{ $task->name }}</span>
                                                                        @if($task->included_in_price)
                                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                                                Included
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                                                                Extra
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center space-x-1">
                                                                    <span class="text-xs text-slate-500">
                                                                        {{ $task->estimated_hours ?? 0 }}h
                                                                    </span>
                                                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                                    <button onclick="addSubtask({{ $task->id }})" class="text-slate-400 hover:text-slate-600 p-0.5 hover:bg-slate-100 rounded transition-all duration-200">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                                        </svg>
                                                                    </button>
                                                                    <button onclick="editTask({{ $task->id }})" class="text-slate-400 hover:text-slate-600 p-0.5 hover:bg-slate-100 rounded transition-all duration-200">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                        </svg>
                                                                    </button>
                                                                    @endif
                                                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                                                    <button onclick="deleteTask({{ $task->id }})" class="text-red-400 hover:text-red-600 p-0.5 hover:bg-red-50 rounded transition-all duration-200">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                        </svg>
                                                                    </button>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            {{-- Task Description --}}
                                                            @if($task->description)
                                                            <div class="px-4 py-1">
                                                                <p class="text-xs text-slate-600">{{ $task->description }}</p>
                                                            </div>
                                                            @endif

                                                            {{-- Subtasks Container --}}
                                                            <div class="subtasks-container px-4 pb-2" data-task-id="{{ $task->id }}">
                                                                @if($task->subtasks->count() > 0)
                                                                    <div class="space-y-1 mt-2">
                                                                        @foreach($task->subtasks->sortBy('sort_order') as $subtask)
                                                                            <div class="subtask-item bg-white/70 border border-slate-200/40 rounded px-2 py-1.5 hover:shadow-sm transition-all duration-200" data-subtask-id="{{ $subtask->id }}">
                                                                                <div class="flex justify-between items-center">
                                                                                    <div class="flex items-center space-x-2">
                                                                                        <div class="subtask-drag-handle text-slate-400 hover:text-slate-600 cursor-move">
                                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                                                            </svg>
                                                                                        </div>
                                                                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                                                        </svg>
                                                                                        <span class="text-xs text-slate-700">{{ $subtask->name }}</span>
                                                                                        @if($subtask->included_in_price)
                                                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                                                                Inc
                                                                                            </span>
                                                                                        @else
                                                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                                                                Extra
                                                                                            </span>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="flex items-center space-x-1">
                                                                                        <span class="text-xs text-slate-500">
                                                                                            {{ $subtask->estimated_hours ?? 0 }}h
                                                                                        </span>
                                                                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                                                        <button onclick="editSubtask({{ $subtask->id }})" class="text-slate-400 hover:text-slate-600 p-0.5 hover:bg-slate-100 rounded transition-all duration-200">
                                                                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                                            </svg>
                                                                                        </button>
                                                                                        @endif
                                                                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                                                                        <button onclick="deleteSubtask({{ $subtask->id }})" class="text-red-400 hover:text-red-600 p-0.5 hover:bg-red-50 rounded transition-all duration-200">
                                                                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                                            </svg>
                                                                                        </button>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                                @if($subtask->description)
                                                                                <div class="mt-1 ml-4">
                                                                                    <p class="text-xs text-slate-500">{{ $subtask->description }}</p>
                                                                                </div>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <div class="text-center py-2">
                                                                        <p class="text-xs text-slate-500">No subtasks yet</p>
                                                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                                        <button onclick="addSubtask({{ $task->id }})" class="mt-1 text-slate-600 hover:text-slate-800 text-xs">
                                                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                                            </svg>
                                                                            Add Subtask
                                                                        </button>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="p-3 text-center">
                                                    <p class="text-xs text-slate-500">No tasks yet</p>
                                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                    <button onclick="addTask({{ $milestone->id }})" class="mt-1 text-slate-600 hover:text-slate-800 text-xs">
                                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Add Task
                                                    </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <h3 class="text-sm font-medium text-slate-900 mb-1">No Structure Defined</h3>
                                    <p class="text-xs text-slate-500 mb-4">Start building your service structure by adding milestones.</p>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                    <button onclick="addMilestone()" class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add First Milestone
                                    </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar with Statistics --}}
            <div class="lg:w-80">
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h3 class="text-base font-medium text-slate-900">Structure Summary</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        {{-- Service Info --}}
                        <div>
                            <h4 class="text-xs font-semibold text-slate-700 mb-2">Service Information</h4>
                            <dl class="space-y-1.5">
                                <div class="flex justify-between">
                                    <dt class="text-xs text-slate-500">Total Price:</dt>
                                    <dd class="text-xs font-semibold text-slate-900">{{ $service->formatted_price }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs text-slate-500">Estimated Hours:</dt>
                                    <dd class="text-xs font-semibold text-slate-900">{{ $service->estimated_hours ?? 0 }}h</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs text-slate-500">Category:</dt>
                                    <dd class="text-xs font-semibold text-slate-900">{{ $service->category->name ?? 'No Category' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs text-slate-500">Status:</dt>
                                    <dd>
                                        @if($service->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                Inactive
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Structure Stats --}}
                        <div>
                            <h4 class="text-xs font-semibold text-slate-700 mb-2">Structure Statistics</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-blue-50 border border-blue-200/50 rounded-lg p-2.5 text-center">
                                    <div class="text-lg font-bold text-blue-600">{{ $service->milestones->count() }}</div>
                                    <div class="text-xs text-blue-600">Milestones</div>
                                </div>
                                <div class="bg-green-50 border border-green-200/50 rounded-lg p-2.5 text-center">
                                    <div class="text-lg font-bold text-green-600">{{ $service->milestones->sum(function($m) { return $m->tasks->count(); }) }}</div>
                                    <div class="text-xs text-green-600">Tasks</div>
                                </div>
                                <div class="bg-purple-50 border border-purple-200/50 rounded-lg p-2.5 text-center">
                                    <div class="text-lg font-bold text-purple-600">{{ $service->milestones->sum(function($m) { return $m->tasks->sum(function($t) { return $t->subtasks->count(); }); }) }}</div>
                                    <div class="text-xs text-purple-600">Subtasks</div>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200/50 rounded-lg p-2.5 text-center">
                                    <div class="text-lg font-bold text-yellow-600">{{ $service->milestones->sum('total_estimated_hours') }}h</div>
                                    <div class="text-xs text-yellow-600">Total Hours</div>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Actions --}}
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <div>
                            <h4 class="text-xs font-semibold text-slate-700 mb-2">Quick Actions</h4>
                            <div class="space-y-1">
                                <button onclick="addMilestone()" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50 rounded-lg transition-all duration-200">
                                    <svg class="w-3 h-3 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Milestone
                                </button>
                                <button onclick="duplicateService()" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50 rounded-lg transition-all duration-200">
                                    <svg class="w-3 h-3 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Duplicate Service
                                </button>
                                <button onclick="exportStructure()" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50 rounded-lg transition-all duration-200">
                                    <svg class="w-3 h-3 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Export Structure
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================= --}}
{{-- ADD/EDIT MODALS --}}
{{-- ============================================= --}}

{{-- Add Milestone Modal --}}
<div id="addMilestoneModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 w-96">
        <div class="bg-white rounded-xl shadow-xl">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-slate-900">Add Milestone</h3>
                    <button onclick="closeModal('addMilestoneModal')" class="text-slate-400 hover:text-slate-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="addMilestoneForm" class="p-4">
                <div class="space-y-3">
                    <div>
                        <label for="milestone_name" class="block text-xs font-medium text-slate-600 mb-1">Name *</label>
                        <input type="text" id="milestone_name" name="name" required
                               class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                               placeholder="e.g. Design Phase">
                    </div>
                    
                    <div>
                        <label for="milestone_description" class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                        <textarea id="milestone_description" name="description" rows="3"
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                                  placeholder="Brief description of this milestone"></textarea>
                    </div>
                    
                    <div>
                        <input type="hidden" name="included_in_price_hidden" value="0">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" id="milestone_included_in_price" name="included_in_price" value="1" checked
                                   class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                            <span class="ml-2 text-sm text-slate-700">Included in price</span>
                        </label>
                        <p class="text-xs text-slate-500 mt-1 ml-6">Hours are calculated automatically from tasks and subtasks</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal('addMilestoneModal')"
                            class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Milestone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Task Modal --}}
<div id="addTaskModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 w-96">
        <div class="bg-white rounded-xl shadow-xl">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-slate-900">Add Task</h3>
                    <button onclick="closeModal('addTaskModal')" class="text-slate-400 hover:text-slate-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="addTaskForm" class="p-4">
                <input type="hidden" id="task_milestone_id" name="milestone_id">
                
                <div class="space-y-3">
                    <div>
                        <label for="task_name" class="block text-xs font-medium text-slate-600 mb-1">Name *</label>
                        <input type="text" id="task_name" name="name" required
                               class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                               placeholder="e.g. Logo Design">
                    </div>
                    
                    <div>
                        <label for="task_description" class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                        <textarea id="task_description" name="description" rows="3"
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                                  placeholder="Brief description of this task"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="task_estimated_hours" class="block text-xs font-medium text-slate-600 mb-1">Estimated Hours</label>
                            <input type="number" id="task_estimated_hours" name="estimated_hours" step="0.5" min="0"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                                   placeholder="0">
                        </div>
                        
                        <div class="flex items-end">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="task_included_in_price" name="included_in_price" checked
                                       class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                                <span class="ml-2 text-xs text-slate-700">Included in price</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal('addTaskModal')"
                            class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-3 py-1.5 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Subtask Modal --}}
<div id="addSubtaskModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 w-96">
        <div class="bg-white rounded-xl shadow-xl">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-slate-900">Add Subtask</h3>
                    <button onclick="closeModal('addSubtaskModal')" class="text-slate-400 hover:text-slate-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="addSubtaskForm" class="p-4">
                <input type="hidden" id="subtask_task_id" name="task_id">
                
                <div class="space-y-3">
                    <div>
                        <label for="subtask_name" class="block text-xs font-medium text-slate-600 mb-1">Name *</label>
                        <input type="text" id="subtask_name" name="name" required
                               class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                               placeholder="e.g. Color Variations">
                    </div>
                    
                    <div>
                        <label for="subtask_description" class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                        <textarea id="subtask_description" name="description" rows="3"
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                                  placeholder="Brief description of this subtask"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="subtask_estimated_hours" class="block text-xs font-medium text-slate-600 mb-1">Estimated Hours</label>
                            <input type="number" id="subtask_estimated_hours" name="estimated_hours" step="0.5" min="0"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors"
                                   placeholder="0">
                        </div>
                        
                        <div class="flex items-end">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="subtask_included_in_price" name="included_in_price" checked
                                       class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                                <span class="ml-2 text-xs text-slate-700">Included in price</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal('addSubtaskModal')"
                            class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-3 py-1.5 bg-purple-500 text-white text-sm font-medium rounded-lg hover:bg-purple-600 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Subtask
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================
// GLOBAL FUNCTIONS - Direct loaded
// ============================================

// Service configuration
const serviceId = {{ $service->id }};
const csrfToken = '{{ csrf_token() }}';

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Reset form
    const form = document.querySelector(`#${modalId} form`);
    if (form) {
        form.reset();
    }
}

// Add functions
function addMilestone() {
    console.log('Add milestone clicked');
    openModal('addMilestoneModal');
}

function addTask(milestoneId) {
    console.log('Add task clicked for milestone:', milestoneId);
    document.getElementById('task_milestone_id').value = milestoneId;
    openModal('addTaskModal');
}

function addSubtask(taskId) {
    console.log('Add subtask clicked for task:', taskId);
    document.getElementById('subtask_task_id').value = taskId;
    openModal('addSubtaskModal');
}

// Edit functions
function editMilestone(milestoneId) {
    // Get milestone data via AJAX
    fetch(`/service-milestones/${milestoneId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Populate edit form in the add modal (reuse it for edit)
        document.getElementById('milestone_name').value = data.name || '';
        document.getElementById('milestone_description').value = data.description || '';
        const includedCheckbox = document.getElementById('milestone_included_in_price');
        if (includedCheckbox) {
            includedCheckbox.checked = data.included_in_price || false;
        }
        
        // Change form action for update
        const form = document.getElementById('addMilestoneForm');
        form.setAttribute('data-edit-mode', 'true');
        form.setAttribute('data-milestone-id', milestoneId);
        
        // Change modal title
        document.querySelector('#addMilestoneModal h3').textContent = 'Edit Milestone';
        
        // Show modal
        openModal('addMilestoneModal');
    })
    .catch(error => {
        console.error('Error loading milestone:', error);
        showNotification('Failed to load milestone data', 'error');
    });
}

function editTask(taskId) {
    // Get task data via AJAX
    fetch(`/service-tasks/${taskId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Set milestone for the task
        document.getElementById('task_milestone_id').value = data.service_milestone_id;
        document.getElementById('task_name').value = data.name || '';
        document.getElementById('task_description').value = data.description || '';
        document.getElementById('task_estimated_hours').value = data.estimated_hours || 0;
        const taskIncludedCheckbox = document.getElementById('task_included_in_price');
        if (taskIncludedCheckbox) {
            taskIncludedCheckbox.checked = data.included_in_price || false;
        }
        
        // Change form for update
        const form = document.getElementById('addTaskForm');
        form.setAttribute('data-edit-mode', 'true');
        form.setAttribute('data-task-id', taskId);
        
        // Change modal title
        document.querySelector('#addTaskModal h3').textContent = 'Edit Task';
        
        // Show modal
        openModal('addTaskModal');
    })
    .catch(error => {
        console.error('Error loading task:', error);
        showNotification('Failed to load task data', 'error');
    });
}

function editSubtask(subtaskId) {
    // Get subtask data via AJAX
    fetch(`/service-subtasks/${subtaskId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Set task for the subtask
        document.getElementById('subtask_task_id').value = data.service_task_id;
        document.getElementById('subtask_name').value = data.name || '';
        document.getElementById('subtask_description').value = data.description || '';
        document.getElementById('subtask_estimated_hours').value = data.estimated_hours || 0;
        const subtaskIncludedCheckbox = document.getElementById('subtask_included_in_price');
        if (subtaskIncludedCheckbox) {
            subtaskIncludedCheckbox.checked = data.included_in_price || false;
        }
        
        // Change form for update
        const form = document.getElementById('addSubtaskForm');
        form.setAttribute('data-edit-mode', 'true');
        form.setAttribute('data-subtask-id', subtaskId);
        
        // Change modal title
        document.querySelector('#addSubtaskModal h3').textContent = 'Edit Subtask';
        
        // Show modal
        openModal('addSubtaskModal');
    })
    .catch(error => {
        console.error('Error loading subtask:', error);
        showNotification('Failed to load subtask data', 'error');
    });
}

// Delete functions
function deleteMilestone(milestoneId) {
    if (confirm('Are you sure you want to delete this milestone? This will also delete all tasks and subtasks.')) {
        fetch(`/service-milestones/${milestoneId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Milestone deleted successfully', 'success');
                // Remove milestone from DOM
                document.querySelector(`.milestone-item[data-milestone-id="${milestoneId}"]`).remove();
                // Update totals
                updateTotals();
            } else {
                showNotification(data.message || 'Failed to delete milestone', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting milestone:', error);
            showNotification('Failed to delete milestone', 'error');
        });
    }
}

function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task? This will also delete all subtasks.')) {
        fetch(`/service-tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Task deleted successfully', 'success');
                // Remove task from DOM
                document.querySelector(`.task-item[data-task-id="${taskId}"]`).remove();
                // Update totals
                updateTotals();
            } else {
                showNotification(data.message || 'Failed to delete task', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting task:', error);
            showNotification('Failed to delete task', 'error');
        });
    }
}

function deleteSubtask(subtaskId) {
    if (confirm('Are you sure you want to delete this subtask?')) {
        fetch(`/service-subtasks/${subtaskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Subtask deleted successfully', 'success');
                // Remove subtask from DOM
                document.querySelector(`.subtask-item[data-subtask-id="${subtaskId}"]`).remove();
                // Update totals
                updateTotals();
            } else {
                showNotification(data.message || 'Failed to delete subtask', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting subtask:', error);
            showNotification('Failed to delete subtask', 'error');
        });
    }
}

// Update totals function
function updateTotals() {
    // Simple solution: reload the page after 1 second
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Utility functions
function duplicateService() {
    console.log('Duplicate service clicked');
    showNotification('Duplicate Service functionality will be implemented in the next step!', 'info');
}

function exportStructure() {
    console.log('Export structure clicked');
    showNotification('Export Structure functionality will be implemented in the next step!', 'info');
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-white font-medium transition-all duration-300 ${
        type === 'success' ? 'bg-green-600' :
        type === 'error' ? 'bg-red-600' :
        type === 'warning' ? 'bg-yellow-600' :
        'bg-blue-600'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// ============================================
// SORTABLE.JS INITIALIZATION
// ============================================

function initializeSortable() {
    console.log('Initializing Sortable.js...');
    
    // Check if Sortable is loaded
    if (typeof Sortable === 'undefined') {
        console.log('Loading Sortable.js from CDN...');
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
        script.onload = function() {
            console.log('Sortable.js loaded successfully');
            setupSortableElements();
        };
        script.onerror = function() {
            console.error('Failed to load Sortable.js');
        };
        document.head.appendChild(script);
    } else {
        console.log('Sortable.js already loaded');
        setupSortableElements();
    }
}

function setupSortableElements() {
    console.log('Setting up sortable elements...');
    
    // Sortable for milestones
    const milestonesContainer = document.getElementById('milestones-container');
    if (milestonesContainer) {
        console.log('Found milestones container with', milestonesContainer.children.length, 'items');
        
        new Sortable(milestonesContainer, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'opacity-50',
            chosenClass: 'bg-slate-50',
            dragClass: 'shadow-lg',
            onEnd: function(evt) {
                console.log('Milestone moved from', evt.oldIndex, 'to', evt.newIndex);
                updateMilestoneOrder();
            }
        });
        console.log('Milestones are now sortable');
    }
    
    // Sortable for tasks within each milestone
    document.querySelectorAll('.tasks-container').forEach((container, index) => {
        console.log('Setting up task container', index, 'with', container.children.length, 'tasks');
        
        // Find the actual task items in the container (they might be nested)
        const taskSelector = container.querySelector('.space-y-2') || container;
        
        if (taskSelector) {
            new Sortable(taskSelector, {
                animation: 150,
                handle: '.task-drag-handle',
                draggable: '.task-item',
                group: 'tasks',
                ghostClass: 'opacity-50',
                chosenClass: 'bg-slate-50',
                dragClass: 'shadow-md',
                onEnd: function(evt) {
                    console.log('Task moved');
                    const milestoneId = evt.to.closest('.milestone-item').dataset.milestoneId || evt.to.dataset.milestoneId;
                    updateTaskOrder(milestoneId);
                }
            });
            console.log('Task container', index, 'is now sortable');
        }
    });
    
    // Sortable for subtasks within each task
    document.querySelectorAll('.subtasks-container').forEach((container, index) => {
        console.log('Setting up subtask container', index, 'with', container.children.length, 'subtasks');
        
        // Find the actual subtask items container (they might be nested)
        const subtaskSelector = container.querySelector('.space-y-1') || container;
        
        if (subtaskSelector) {
            new Sortable(subtaskSelector, {
                animation: 150,
                handle: '.subtask-drag-handle',
                draggable: '.subtask-item',
                group: 'subtasks',
                ghostClass: 'opacity-50',
                chosenClass: 'bg-slate-50',
                dragClass: 'shadow-sm',
                onEnd: function(evt) {
                    console.log('Subtask moved');
                    const taskId = evt.to.closest('.task-item').dataset.taskId || evt.to.dataset.taskId;
                    updateSubtaskOrder(taskId);
                }
            });
            console.log('Subtask container', index, 'is now sortable');
        }
    });
    
    console.log('All sortable elements initialized');
}

// Update order functions for AJAX calls
function updateMilestoneOrder() {
    const milestones = [];
    document.querySelectorAll('.milestone-item').forEach((item, index) => {
        milestones.push({
            id: item.dataset.milestoneId,
            sort_order: index + 1
        });
    });
    
    const serviceId = {{ $service->id }};
    
    fetch(`/services/${serviceId}/milestones/reorder`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ milestones: milestones })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Milestone order updated', 'success');
        }
    })
    .catch(error => {
        console.error('Error updating milestone order:', error);
        showNotification('Failed to update order', 'error');
    });
}

function updateTaskOrder(milestoneId) {
    const tasks = [];
    const container = document.querySelector(`.tasks-container[data-milestone-id="${milestoneId}"]`);
    if (container) {
        container.querySelectorAll('.task-item').forEach((item, index) => {
            tasks.push({
                id: item.dataset.taskId,
                sort_order: index + 1
            });
        });
    }
    
    fetch(`/service-milestones/${milestoneId}/tasks/reorder`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ tasks: tasks })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Task order updated', 'success');
        }
    })
    .catch(error => {
        console.error('Error updating task order:', error);
        showNotification('Failed to update order', 'error');
    });
}

function updateSubtaskOrder(taskId) {
    const subtasks = [];
    const container = document.querySelector(`.subtasks-container[data-task-id="${taskId}"]`);
    if (container) {
        container.querySelectorAll('.subtask-item').forEach((item, index) => {
            subtasks.push({
                id: item.dataset.subtaskId,
                sort_order: index + 1
            });
        });
    }
    
    fetch(`/service-tasks/${taskId}/subtasks/reorder`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ subtasks: subtasks })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Subtask order updated', 'success');
        }
    })
    .catch(error => {
        console.error('Error updating subtask order:', error);
        showNotification('Failed to update order', 'error');
    });
}

// ============================================
// DOM READY FUNCTIONS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Service Structure page loaded successfully');
    
    // Initialize Sortable.js for drag and drop
    initializeSortable();
    
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-opacity-50')) {
            const modals = ['addMilestoneModal', 'addTaskModal', 'addSubtaskModal'];
            modals.forEach(modalId => {
                if (!document.getElementById(modalId).classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });

    // Form submissions
    setupFormSubmissions();
});

function setupFormSubmissions() {
    // Add Milestone Form
    const addMilestoneForm = document.getElementById('addMilestoneForm');
    if (addMilestoneForm) {
        addMilestoneForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEditMode = this.getAttribute('data-edit-mode') === 'true';
            const milestoneId = this.getAttribute('data-milestone-id');
            
            let url = `/services/${serviceId}/milestones`;
            let method = 'POST';
            
            if (isEditMode && milestoneId) {
                url = `/service-milestones/${milestoneId}`;
                method = 'PUT';
                formData.append('_method', 'PUT');
            }
            
            try {
                const response = await fetch(url, {
                    method: 'POST', // Always POST for FormData with _method field
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    closeModal('addMilestoneModal');
                    showNotification(isEditMode ? 'Milestone updated successfully!' : 'Milestone added successfully!', 'success');
                    // Reset form state
                    this.removeAttribute('data-edit-mode');
                    this.removeAttribute('data-milestone-id');
                    document.querySelector('#addMilestoneModal h3').textContent = 'Add Milestone';
                    location.reload();
                } else {
                    const result = await response.json();
                    showNotification(result.message || 'Error saving milestone', 'error');
                }
            } catch (error) {
                showNotification('Error saving milestone', 'error');
                console.error('Error:', error);
            }
        });
    }

    // Add Task Form
    const addTaskForm = document.getElementById('addTaskForm');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const milestoneId = document.getElementById('task_milestone_id').value;
            
            try {
                const response = await fetch(`/services/${serviceId}/milestones/${milestoneId}/tasks`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    closeModal('addTaskModal');
                    showNotification('Task added successfully!', 'success');
                    location.reload();
                } else {
                    const result = await response.json();
                    showNotification(result.message || 'Error adding task', 'error');
                }
            } catch (error) {
                showNotification('Error adding task', 'error');
                console.error('Error:', error);
            }
        });
    }

    // Add Subtask Form
    const addSubtaskForm = document.getElementById('addSubtaskForm');
    if (addSubtaskForm) {
        addSubtaskForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const taskId = document.getElementById('subtask_task_id').value;
            const isEditMode = this.getAttribute('data-edit-mode') === 'true';
            const subtaskId = this.getAttribute('data-subtask-id');
            
            let url = `/services/tasks/${taskId}/subtasks`;
            
            if (isEditMode && subtaskId) {
                url = `/service-subtasks/${subtaskId}`;
                formData.append('_method', 'PUT');
            }
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    closeModal('addSubtaskModal');
                    showNotification(isEditMode ? 'Subtask updated successfully!' : 'Subtask added successfully!', 'success');
                    // Reset form state
                    this.removeAttribute('data-edit-mode');
                    this.removeAttribute('data-subtask-id');
                    document.querySelector('#addSubtaskModal h3').textContent = 'Add Subtask';
                    location.reload();
                } else {
                    const result = await response.json();
                    showNotification(result.message || 'Error saving subtask', 'error');
                }
            } catch (error) {
                showNotification('Error saving subtask', 'error');
                console.error('Error:', error);
            }
        });
    }
}
</script>

@endsection