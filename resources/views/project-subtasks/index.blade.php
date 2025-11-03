@extends('layouts.app')

@section('title', 'Subtasks')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li>
                                <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">Projects</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('projects.show', $project) }}" class="ml-1 text-gray-500 hover:text-gray-700">{{ $project->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" class="ml-1 text-gray-500 hover:text-gray-700">{{ $milestone->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('project-milestones.tasks.show', [$milestone, $projectTask]) }}" class="ml-1 text-gray-500 hover:text-gray-700">{{ $projectTask->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-gray-700 font-medium">Subtasks</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Subtasks for {{ $projectTask->name }}</h1>
                </div>
                <div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a href="{{ route('project-tasks.subtasks.create', $projectTask) }}" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Subtask
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Subtasks List --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">All Subtasks</h2>
            </div>
            
            @if($subtasks->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($subtasks as $subtask)
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $subtask->name }}</h3>
                                    @if($subtask->description)
                                        <p class="mt-1 text-sm text-gray-500">{{ $subtask->description }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ 
                                            $subtask->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                            ($subtask->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                            ($subtask->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $subtask->status)) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ 
                                            $subtask->fee_type === 'in_fee' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'
                                        }}">
                                            {{ $subtask->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                        </span>
                                        @if($subtask->estimated_hours)
                                            <span>{{ number_format($subtask->estimated_hours, 1) }} hours</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4 flex items-center space-x-2">
                                    <a href="{{ route('project-tasks.subtasks.show', [$projectTask, $subtask]) }}" 
                                        class="text-blue-600 hover:text-blue-900">View</a>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                        <a href="{{ route('project-tasks.subtasks.edit', [$projectTask, $subtask]) }}" 
                                            class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No subtasks</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new subtask for this task.</p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <div class="mt-6">
                            <a href="{{ route('project-tasks.subtasks.create', $projectTask) }}" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create First Subtask
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection