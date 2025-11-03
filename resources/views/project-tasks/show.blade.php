@extends('layouts.app')

@section('title', 'Task Details')

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
                                    <a href="{{ route('projects.milestones.show', [$project, $projectMilestone]) }}" class="ml-1 text-gray-500 hover:text-gray-700">{{ $projectMilestone->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-gray-700 font-medium">{{ $task->name }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $task->name }}</h1>
                    <div class="mt-1 flex items-center space-x-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                            $task->status === 'completed' ? 'bg-green-100 text-green-800' : 
                            ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                            ($task->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $task->fee_type === 'in_fee' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                            {{ $task->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                        </span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a href="{{ route('project-milestones.tasks.edit', [$projectMilestone, $task]) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Task
                        </a>
                    @endif
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        @php
                            $subtaskCount = 0;
                        @endphp
                        <button type="button" onclick="openDeleteModal({{ $subtaskCount }})" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Task
                        </button>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Task Details --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="border-b border-gray-100 mb-4 pb-3">
                        <h2 class="text-[17px] font-semibold">Task Information</h2>
                    </div>
                    <div>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            @if($task->description)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $task->description }}</dd>
                                </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $task->start_date ? $task->start_date->format('M d, Y') : 'Not set' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $task->end_date ? $task->end_date->format('M d, Y') : 'Not set' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estimated Hours</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($task->estimated_hours ?? 0, 1) }} hours</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Pricing Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($task->pricing_type === 'fixed_price')
                                        Fixed Price: €{{ number_format($task->fixed_price, 2) }}
                                    @else
                                        Hourly Rate
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Quick Actions & Stats --}}
            <div>
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="border-b border-gray-100 mb-4 pb-3">
                        <h2 class="text-[17px] font-semibold">Quick Actions</h2>
                    </div>
                    <div class="space-y-2">
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <a href="{{ route('project-tasks.subtasks.create', $task) }}" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200 group">
                                <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="text-sm">Add Subtask</span>
                            </a>
                        @endif
                        
                        <button onclick="updateTaskStatus()" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200 group">
                            <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm">Update Status</span>
                        </button>
                    </div>
                </div>

                {{-- Statistics --}}
                <div class="mt-6 bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="border-b border-gray-100 mb-4 pb-3">
                        <h2 class="text-[17px] font-semibold">Statistics</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-50 transition-colors">
                            <span class="text-sm font-medium text-gray-500">Total Subtasks</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $stats['total_subtasks'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-50 transition-colors">
                            <span class="text-sm font-medium text-gray-500">Completed Subtasks</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $stats['completed_subtasks'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-50 transition-colors">
                            <span class="text-sm font-medium text-gray-500">Progress</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $stats['progress_percentage'] }}%</span>
                        </div>
                        <div class="pt-2">
                            <div class="bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stats['progress_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Subtasks Overview --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <div class="border-b border-gray-100 mb-4 pb-3 flex items-center justify-between">
                <h2 class="text-[17px] font-semibold">Subtasks</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse([] as $subtask)
                    <div class="py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">{{ $subtask->name }}</h3>
                                @if($subtask->description)
                                    <p class="mt-1 text-sm text-gray-500">{{ Str::limit($subtask->description, 100) }}</p>
                                @endif
                                <div class="mt-1 flex items-center text-xs text-gray-500">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ 
                                        $subtask->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                        ($subtask->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                        ($subtask->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $subtask->status)) }}
                                    </span>
                                    @if($subtask->estimated_hours)
                                        <span class="ml-2">{{ number_format($subtask->estimated_hours, 1) }} hours</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex items-center space-x-2">
                                <button onclick="updateSubtaskStatus({{ $subtask->id }})" class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                    <a href="{{ route('project-tasks.subtasks.edit', [$task, $subtask]) }}" class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-gray-500">
                        No subtasks yet. 
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <a href="{{ route('project-tasks.subtasks.create', $task) }}" class="text-blue-600 hover:text-blue-500">Create the first subtask →</a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function updateTaskStatus() {
    // TODO: Implement AJAX status update
    alert('Status update functionality coming soon!');
}

function updateSubtaskStatus(subtaskId) {
    // TODO: Implement AJAX subtask status update
    alert('Subtask status update functionality coming soon!');
}
</script>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('project-milestones.tasks.destroy', [$projectMilestone, $task]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Delete Task
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete the task "<strong>{{ $task->name }}</strong>"?
                                </p>
                                <div id="cascadeWarning" class="hidden mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Warning:</strong> This task contains <span id="subtaskCountText"></span> subtask(s).
                                    </p>
                                    <div class="mt-3">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="cascade_delete" value="1" id="cascadeDeleteCheck" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">Delete all subtasks</span>
                                        </label>
                                    </div>
                                </div>
                                <div id="noChildrenMessage" class="hidden mt-2">
                                    <p class="text-sm text-gray-500">This action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="confirmDeleteBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(subtaskCount) {
    const modal = document.getElementById('deleteModal');
    const cascadeWarning = document.getElementById('cascadeWarning');
    const noChildrenMessage = document.getElementById('noChildrenMessage');
    const subtaskCountText = document.getElementById('subtaskCountText');
    const cascadeCheck = document.getElementById('cascadeDeleteCheck');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (subtaskCount > 0) {
        cascadeWarning.classList.remove('hidden');
        noChildrenMessage.classList.add('hidden');
        subtaskCountText.textContent = subtaskCount;
        
        // Disable delete button until cascade is checked
        confirmBtn.disabled = true;
        confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
        
        // Enable button when cascade is checked
        cascadeCheck.addEventListener('change', function() {
            if (this.checked) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                confirmBtn.textContent = 'Delete All';
            } else {
                confirmBtn.disabled = true;
                confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                confirmBtn.textContent = 'Delete';
            }
        });
    } else {
        cascadeWarning.classList.add('hidden');
        noChildrenMessage.classList.remove('hidden');
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target == document.getElementById('deleteModal')) {
        closeDeleteModal();
    }
});
</script>
@endsection