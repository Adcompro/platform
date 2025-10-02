@extends('layouts.app')

@section('title', 'Subtask Details')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li><a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">Projects</a></li>
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
                                    <span class="ml-1 text-gray-700 font-medium">{{ $subtask->name }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $subtask->name }}</h1>
                    <div class="mt-1 flex items-center space-x-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                            $subtask->status === 'completed' ? 'bg-green-100 text-green-800' : 
                            ($subtask->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                            ($subtask->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $subtask->status)) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subtask->fee_type === 'in_fee' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ $subtask->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                        </span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a href="{{ route('project-tasks.subtasks.edit', [$projectTask, $subtask]) }}" 
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Subtask
                        </a>
                    @endif
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        <form action="{{ route('project-tasks.subtasks.destroy', [$projectTask, $subtask]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this subtask? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Subtask
                            </button>
                        </form>
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

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Subtask Details --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="border-b border-gray-100 mb-4 pb-3">
                        <h2 class="text-[17px] font-semibold">Subtask Information</h2>
                    </div>
                    <div>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            @if($subtask->description)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $subtask->description }}</dd>
                                </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $subtask->start_date ? $subtask->start_date->format('M d, Y') : 'Not set' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $subtask->end_date ? $subtask->end_date->format('M d, Y') : 'Not set' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estimated Hours</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($subtask->estimated_hours ?? 0, 1) }} hours</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pricing Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($subtask->pricing_type === 'fixed_price')
                                        Fixed Price: €{{ number_format($subtask->fixed_price, 2) }}
                                    @else
                                        Hourly Rate
                                        @if($subtask->hourly_rate_override)
                                            : €{{ number_format($subtask->hourly_rate_override, 2) }}/hour
                                        @endif
                                    @endif
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $subtask->sort_order }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Source</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($subtask->source_type ?? 'manual') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Quick Actions & Parent Info --}}
            <div>
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="border-b border-gray-100 mb-4 pb-3">
                        <h2 class="text-[17px] font-semibold">Quick Actions</h2>
                    </div>
                    <div class="space-y-2">
                        <button onclick="updateSubtaskStatus()" 
                            class="flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200 group">
                            <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm">Update Status</span>
                        </button>
                        
                        @if($subtask->status !== 'completed')
                            <button onclick="markAsComplete()" 
                                class="flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200 group">
                                <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm">Mark as Complete</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Parent Information --}}
                <div class="mt-6 bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="border-b border-gray-100 mb-4 pb-3">
                        <h2 class="text-[17px] font-semibold">Hierarchy</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="py-2 px-3 rounded hover:bg-gray-50 transition-colors">
                            <span class="text-sm font-medium text-gray-500">Project</span>
                            <a href="{{ route('projects.show', $project) }}" class="block text-sm text-blue-600 hover:text-blue-500">
                                {{ $project->name }}
                            </a>
                        </div>
                        <div class="py-2 px-3 rounded hover:bg-gray-50 transition-colors">
                            <span class="text-sm font-medium text-gray-500">Milestone</span>
                            <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" class="block text-sm text-blue-600 hover:text-blue-500">
                                {{ $milestone->name }}
                            </a>
                        </div>
                        <div class="py-2 px-3 rounded hover:bg-gray-50 transition-colors">
                            <span class="text-sm font-medium text-gray-500">Task</span>
                            <a href="{{ route('project-milestones.tasks.show', [$milestone, $projectTask]) }}" class="block text-sm text-blue-600 hover:text-blue-500">
                                {{ $projectTask->name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateSubtaskStatus() {
    // TODO: Implement AJAX status update
    alert('Status update functionality coming soon!');
}

function markAsComplete() {
    // TODO: Implement AJAX mark as complete
    alert('Mark as complete functionality coming soon!');
}
</script>
@endsection