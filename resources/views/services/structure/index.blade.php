@extends('layouts.app')

@section('title', 'Service Structure - ' . $service->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Service Structure</h1>
                    <p class="text-sm text-gray-600">{{ $service->name }} • Build milestones, tasks, and deliverables</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('services.show', $service) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:bg-gray-50 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Service
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Service Info Card --}}
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $service->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $service->category->name ?? 'No Category' }} • {{ $service->formatted_price }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 text-sm rounded-full {{ $service->status_badge_class }}">
                            {{ ucfirst($service->status) }}
                        </span>
                        @if($service->is_package)
                            <span class="px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-800">Package</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Milestones:</span>
                        <span class="font-medium text-gray-900 ml-2" id="total-milestones">{{ $service->milestones->count() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Tasks:</span>
                        <span class="font-medium text-gray-900 ml-2" id="total-tasks">{{ $service->milestones->sum(function($m) { return $m->tasks->count(); }) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Subtasks:</span>
                        <span class="font-medium text-gray-900 ml-2" id="total-subtasks">{{ $service->milestones->sum(function($m) { return $m->tasks->sum(function($t) { return $t->subtasks->count(); }); }) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Est. Hours:</span>
                        <span class="font-medium text-gray-900 ml-2" id="total-hours">{{ number_format($service->calculateEstimatedHours(), 1) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Structure Builder --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Service Structure</h2>
                    <button onclick="addMilestone()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i>Add Milestone
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div id="milestones-container" class="space-y-6">
                    @forelse($service->milestones as $milestone)
                        <div class="milestone-item border border-gray-200 rounded-lg" data-milestone-id="{{ $milestone->id }}">
                            <div class="p-4 bg-gray-50 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M7 2a2 2 0 11-4 0 2 2 0 014 0zM7 6a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0zM7 14a2 2 0 11-4 0 2 2 0 014 0zM7 18a2 2 0 11-4 0 2 2 0 014 0zM17 2a2 2 0 11-4 0 2 2 0 014 0zM17 6a2 2 0 11-4 0 2 2 0 014 0zM17 10a2 2 0 11-4 0 2 2 0 014 0zM17 14a2 2 0 11-4 0 2 2 0 014 0zM17 18a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $milestone->name }}</h3>
                                            @if($milestone->description)
                                                <p class="text-sm text-gray-600">{{ $milestone->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500">{{ number_format($milestone->calculateEstimatedHours(), 1) }}h</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $milestone->included_in_price ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $milestone->included_text }}
                                        </span>
                                        <button onclick="editMilestone({{ $milestone->id }})" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteMilestone({{ $milestone->id }})" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button onclick="addTask({{ $milestone->id }})" class="text-green-600 hover:text-green-800 text-sm">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tasks --}}
                            <div class="tasks-container p-4 space-y-3" data-milestone-id="{{ $milestone->id }}">
                                @foreach($milestone->tasks as $task)
                                    <div class="task-item ml-6 border border-gray-100 rounded bg-white" data-task-id="{{ $task->id }}">
                                        <div class="p-3 flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="task-drag-handle cursor-move text-gray-300 hover:text-gray-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M7 2a2 2 0 11-4 0 2 2 0 014 0zM7 6a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0zM7 14a2 2 0 11-4 0 2 2 0 014 0zM7 18a2 2 0 11-4 0 2 2 0 014 0zM17 2a2 2 0 11-4 0 2 2 0 014 0zM17 6a2 2 0 11-4 0 2 2 0 014 0zM17 10a2 2 0 11-4 0 2 2 0 014 0zM17 14a2 2 0 11-4 0 2 2 0 014 0zM17 18a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $task->name }}</span>
                                                    @if($task->description)
                                                        <p class="text-xs text-gray-500">{{ $task->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if($task->estimated_hours)
                                                    <span class="text-xs text-gray-500">{{ $task->formatted_hours }}</span>
                                                @endif
                                                <span class="px-2 py-1 text-xs rounded {{ $task->included_in_price ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $task->included_text }}
                                                </span>
                                                <button onclick="editTask({{ $task->id }})" class="text-blue-600 hover:text-blue-800 text-xs">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteTask({{ $task->id }})" class="text-red-600 hover:text-red-800 text-xs">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button onclick="addSubtask({{ $task->id }})" class="text-green-600 hover:text-green-800 text-xs">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Subtasks --}}
                                        @if($task->subtasks->count() > 0)
                                            <div class="subtasks-container ml-6 pb-3 space-y-2" data-task-id="{{ $task->id }}">
                                                @foreach($task->subtasks as $subtask)
                                                    <div class="subtask-item flex items-center justify-between py-2 px-3 bg-gray-50 rounded" data-subtask-id="{{ $subtask->id }}">
                                                        <div class="flex items-center space-x-2">
                                                            <div class="subtask-drag-handle cursor-move text-gray-300 hover:text-gray-500">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path d="M7 2a2 2 0 11-4 0 2 2 0 014 0zM7 6a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0zM7 14a2 2 0 11-4 0 2 2 0 014 0zM7 18a2 2 0 11-4 0 2 2 0 014 0zM17 2a2 2 0 11-4 0 2 2 0 014 0zM17 6a2 2 0 11-4 0 2 2 0 014 0zM17 10a2 2 0 11-4 0 2 2 0 014 0zM17 14a2 2 0 11-4 0 2 2 0 014 0zM17 18a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                                </svg>
                                                            </div>
                                                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                                            <span class="text-xs text-gray-900">{{ $subtask->name }}</span>
                                                            @if($subtask->estimated_hours)
                                                                <span class="text-xs text-gray-500">({{ $subtask->formatted_hours }})</span>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            <span class="px-2 py-1 text-xs rounded {{ $subtask->included_in_price ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                                {{ $subtask->included_text }}
                                                            </span>
                                                            <button onclick="editSubtask({{ $subtask->id }})" class="text-blue-600 hover:text-blue-800 text-xs">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button onclick="deleteSubtask({{ $subtask->id }})" class="text-red-600 hover:text-red-800 text-xs">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12" id="empty-state">
                            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-tasks text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Structure Defined</h3>
                            <p class="text-gray-500 mb-4">Start building your service by adding milestones and tasks.</p>
                            <button onclick="addMilestone()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-plus mr-2"></i>Add First Milestone
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal for Add/Edit Forms --}}
<div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-title">Add Milestone</h3>
            <form id="modal-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" name="name" id="modal-name" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="modal-description" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    {{-- Estimated hours verwijderd - wordt automatisch berekend uit taken --}}
                    <div class="flex items-center">
                        <input type="checkbox" name="included_in_price" id="modal-included" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="modal-included" class="ml-2 text-sm text-gray-700">Included in service price</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript --}}
@push('scripts')
<script>
    const serviceId = {{ $service->id }};
    let currentEditId = null;
    let currentEditType = null;

    // Modal functions
    function openModal(title, data = {}) {
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-name').value = data.name || '';
        document.getElementById('modal-description').value = data.description || '';
        // Estimated hours verwijderd - wordt automatisch berekend
        document.getElementById('modal-included').checked = data.included_in_price !== false;
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
        currentEditId = null;
        currentEditType = null;
    }

    // Milestone functions
    function addMilestone() {
        currentEditType = 'milestone';
        openModal('Add Milestone');
    }

    function editMilestone(id) {
        currentEditId = id;
        currentEditType = 'milestone';
        // In production, fetch current data via AJAX
        openModal('Edit Milestone');
    }

    function deleteMilestone(id) {
        if (confirm('Are you sure you want to delete this milestone and all its tasks?')) {
            fetch(`/services/${serviceId}/milestones/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Simple reload for now
                } else {
                    alert(data.error || 'Error deleting milestone');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting milestone');
            });
        }
    }

    // Task functions
    function addTask(milestoneId) {
        currentEditId = milestoneId;
        currentEditType = 'task';
        openModal('Add Task');
    }

    function editTask(id) {
        currentEditId = id;
        currentEditType = 'task-edit';
        openModal('Edit Task');
    }

    function deleteTask(id) {
        if (confirm('Are you sure you want to delete this task and all its subtasks?')) {
            fetch(`/services/${serviceId}/tasks/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error deleting task');
                }
            });
        }
    }

    // Subtask functions
    function addSubtask(taskId) {
        currentEditId = taskId;
        currentEditType = 'subtask';
        openModal('Add Subtask');
    }

    function editSubtask(id) {
        currentEditId = id;
        currentEditType = 'subtask-edit';
        openModal('Edit Subtask');
    }

    function deleteSubtask(id) {
        if (confirm('Are you sure you want to delete this subtask?')) {
            fetch(`/services/${serviceId}/subtasks/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error deleting subtask');
                }
            });
        }
    }

    // Form submission
    document.getElementById('modal-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            name: formData.get('name'),
            description: formData.get('description'),
            // estimated_hours verwijderd voor milestones - wordt automatisch berekend
            included_in_price: formData.get('included_in_price') ? true : false
        };
        
        // Voor tasks en subtasks, voeg estimated_hours wel toe
        if (currentEditType === 'task' || currentEditType === 'task-edit' || 
            currentEditType === 'subtask' || currentEditType === 'subtask-edit') {
            data.estimated_hours = formData.get('estimated_hours');
        }

        let url, method;

        switch (currentEditType) {
            case 'milestone':
                if (currentEditId) {
                    url = `/services/${serviceId}/milestones/${currentEditId}`;
                    method = 'PUT';
                } else {
                    url = `/services/${serviceId}/milestones`;
                    method = 'POST';
                }
                break;
            case 'task':
                url = `/services/${serviceId}/milestones/${currentEditId}/tasks`;
                method = 'POST';
                break;
            case 'task-edit':
                url = `/services/${serviceId}/tasks/${currentEditId}`;
                method = 'PUT';
                break;
            case 'subtask':
                url = `/services/${serviceId}/tasks/${currentEditId}/subtasks`;
                method = 'POST';
                break;
            case 'subtask-edit':
                url = `/services/${serviceId}/subtasks/${currentEditId}`;
                method = 'PUT';
                break;
        }

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload(); // Simple reload for now
            } else {
                alert(data.error || 'Error saving');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving');
        });
    });

    // Close modal when clicking outside
    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Sortable.js implementatie voor drag & drop
    // Wacht tot DOM geladen is
    document.addEventListener('DOMContentLoaded', function() {
        // Laad Sortable.js library
        if (typeof Sortable === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
            script.onload = function() {
                console.log('Sortable.js loaded');
                // Kleine delay om zeker te zijn dat DOM volledig is gerenderd
                setTimeout(initializeSortable, 100);
            };
            script.onerror = function() {
                console.error('Failed to load Sortable.js');
            };
            document.head.appendChild(script);
        } else {
            console.log('Sortable.js already loaded');
            // Kleine delay om zeker te zijn dat DOM volledig is gerenderd
            setTimeout(initializeSortable, 100);
        }
    });

    function initializeSortable() {
        console.log('=== STARTING SORTABLE INITIALIZATION ===');
        console.log('Sortable version:', typeof Sortable !== 'undefined' ? 'Loaded' : 'NOT LOADED');
        
        // Debug DOM structure
        console.log('Checking DOM structure...');
        
        // Sortable voor milestones
        const milestonesContainer = document.getElementById('milestones-container');
        console.log('Milestones container:', milestonesContainer);
        
        if (milestonesContainer) {
            const milestoneItems = milestonesContainer.querySelectorAll('.milestone-item');
            console.log('Found milestone items:', milestoneItems.length);
            
            // Check handles
            const handles = milestonesContainer.querySelectorAll('.drag-handle');
            console.log('Found drag handles:', handles.length);
            
            // Log first handle for debugging
            if (handles.length > 0) {
                console.log('First handle element:', handles[0]);
                console.log('First handle HTML:', handles[0].outerHTML);
                console.log('First handle parent:', handles[0].parentElement);
            }
            
            try {
                console.log('Creating Sortable for milestones...');
                const sortableMilestones = new Sortable(milestonesContainer, {
                    animation: 150,
                    handle: '.drag-handle',
                    draggable: '.milestone-item',
                    ghostClass: 'opacity-25',
                    chosenClass: 'opacity-50',
                    dragClass: 'shadow-lg',
                    forceFallback: true,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    onStart: function(evt) {
                        console.log('=== DRAG START ===');
                        console.log('Dragging element:', evt.item);
                        console.log('From index:', evt.oldIndex);
                    },
                    onMove: function(evt) {
                        console.log('=== DRAG MOVE ===');
                        console.log('Related element:', evt.related);
                        return true; // Allow move
                    },
                    onEnd: function(evt) {
                        console.log('=== DRAG END ===');
                        console.log('To index:', evt.newIndex);
                        console.log('Element:', evt.item);
                        updateMilestoneOrder();
                    },
                    onChoose: function(evt) {
                        console.log('=== CHOSE ELEMENT ===');
                        console.log('Chosen element:', evt.item);
                    },
                    onUnchoose: function(evt) {
                        console.log('=== UNCHOSE ELEMENT ===');
                    }
                });
                console.log('Sortable created successfully for milestones:', sortableMilestones);
                
                // Test if sortable is working
                console.log('Sortable options:', sortableMilestones.options);
                
            } catch (error) {
                console.error('ERROR creating Sortable for milestones:', error);
                console.error('Error stack:', error.stack);
            }
        } else {
            console.error('Milestones container not found!');
        }

        // Sortable voor tasks binnen elke milestone
        console.log('\n=== INITIALIZING TASKS ===');
        const taskContainers = document.querySelectorAll('.tasks-container');
        console.log('Found task containers:', taskContainers.length);
        
        taskContainers.forEach((container, index) => {
            const taskItems = container.querySelectorAll('.task-item');
            console.log(`Task container ${index}:`, container);
            console.log(`- Has ${taskItems.length} tasks`);
            console.log(`- Container dataset:`, container.dataset);
            
            // Check task handles
            const taskHandles = container.querySelectorAll('.task-drag-handle');
            console.log(`- Found ${taskHandles.length} task drag handles`);
            
            if (taskHandles.length > 0) {
                console.log('- First task handle:', taskHandles[0]);
                console.log('- First task handle HTML:', taskHandles[0].outerHTML);
            }
            
            try {
                console.log(`Creating Sortable for task container ${index}...`);
                const sortableTasks = new Sortable(container, {
                    animation: 150,
                    handle: '.task-drag-handle',
                    draggable: '.task-item',
                    ghostClass: 'opacity-25',
                    chosenClass: 'opacity-50',
                    dragClass: 'shadow-lg',
                    group: 'tasks',
                    forceFallback: true,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    onStart: function(evt) {
                        console.log('=== TASK DRAG START ===');
                        console.log('Task element:', evt.item);
                        console.log('From index:', evt.oldIndex);
                    },
                    onMove: function(evt) {
                        console.log('=== TASK DRAG MOVE ===');
                        return true;
                    },
                    onEnd: function(evt) {
                        console.log('=== TASK DRAG END ===');
                        console.log('To index:', evt.newIndex);
                        const milestoneId = evt.to.dataset.milestoneId;
                        console.log('Milestone ID:', milestoneId);
                        updateTaskOrder(milestoneId);
                    },
                    onChoose: function(evt) {
                        console.log('=== CHOSE TASK ===');
                        console.log('Chosen task:', evt.item);
                    }
                });
                console.log(`Sortable created for task container ${index}:`, sortableTasks);
            } catch (error) {
                console.error(`ERROR creating Sortable for task container ${index}:`, error);
            }
        });

        // Sortable voor subtasks binnen elke task
        console.log('\n=== INITIALIZING SUBTASKS ===');
        const subtaskContainers = document.querySelectorAll('.subtasks-container');
        console.log('Found subtask containers:', subtaskContainers.length);
        
        subtaskContainers.forEach((container, index) => {
            const subtaskItems = container.querySelectorAll('.subtask-item');
            console.log(`Subtask container ${index}:`, container);
            console.log(`- Has ${subtaskItems.length} subtasks`);
            console.log(`- Container dataset:`, container.dataset);
            
            // Check subtask handles
            const subtaskHandles = container.querySelectorAll('.subtask-drag-handle');
            console.log(`- Found ${subtaskHandles.length} subtask drag handles`);
            
            if (subtaskHandles.length > 0) {
                console.log('- First subtask handle:', subtaskHandles[0]);
                console.log('- First subtask handle HTML:', subtaskHandles[0].outerHTML);
            }
            
            try {
                console.log(`Creating Sortable for subtask container ${index}...`);
                const sortableSubtasks = new Sortable(container, {
                    animation: 150,
                    handle: '.subtask-drag-handle',
                    draggable: '.subtask-item',
                    ghostClass: 'opacity-25',
                    chosenClass: 'opacity-50',
                    dragClass: 'shadow-lg',
                    group: 'subtasks',
                    forceFallback: true,
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    onStart: function(evt) {
                        console.log('=== SUBTASK DRAG START ===');
                        console.log('Subtask element:', evt.item);
                        console.log('From index:', evt.oldIndex);
                    },
                    onMove: function(evt) {
                        console.log('=== SUBTASK DRAG MOVE ===');
                        return true;
                    },
                    onEnd: function(evt) {
                        console.log('=== SUBTASK DRAG END ===');
                        console.log('To index:', evt.newIndex);
                        const taskId = evt.to.dataset.taskId;
                        console.log('Task ID:', taskId);
                        updateSubtaskOrder(taskId);
                    },
                    onChoose: function(evt) {
                        console.log('=== CHOSE SUBTASK ===');
                        console.log('Chosen subtask:', evt.item);
                    }
                });
                console.log(`Sortable created for subtask container ${index}:`, sortableSubtasks);
            } catch (error) {
                console.error(`ERROR creating Sortable for subtask container ${index}:`, error);
            }
        });
        
        console.log('\n=== SORTABLE INITIALIZATION COMPLETE ===');
        
        // Test function to manually check elements
        window.testDragAndDrop = function() {
            console.log('\n=== MANUAL TEST ===');
            
            // Test milestone handles
            const milestoneHandles = document.querySelectorAll('.drag-handle');
            console.log('Milestone handles found:', milestoneHandles.length);
            milestoneHandles.forEach((handle, i) => {
                console.log(`Handle ${i}:`, handle);
                console.log(`- Parent classes:`, handle.parentElement.className);
                console.log(`- Closest .milestone-item:`, handle.closest('.milestone-item'));
            });
            
            // Test if Sortable is attached
            const container = document.getElementById('milestones-container');
            if (container && container._sortable) {
                console.log('Sortable is attached to milestones container!');
                console.log('Sortable instance:', container._sortable);
            } else {
                console.log('WARNING: Sortable is NOT attached to milestones container');
            }
            
            // Check for CSS conflicts
            const firstMilestone = document.querySelector('.milestone-item');
            if (firstMilestone) {
                const styles = window.getComputedStyle(firstMilestone);
                console.log('First milestone computed styles:');
                console.log('- position:', styles.position);
                console.log('- z-index:', styles.zIndex);
                console.log('- pointer-events:', styles.pointerEvents);
                console.log('- user-select:', styles.userSelect);
            }
        };
        
        console.log('Run window.testDragAndDrop() in console to test');
        
        // Alternative simple initialization test
        window.testSimpleSortable = function() {
            console.log('\n=== TESTING SIMPLE SORTABLE ===');
            
            const container = document.getElementById('milestones-container');
            if (!container) {
                console.error('Container not found!');
                return;
            }
            
            // Destroy existing sortable if any
            if (container._sortable) {
                console.log('Destroying existing Sortable instance');
                container._sortable.destroy();
            }
            
            // Create simplest possible Sortable
            try {
                console.log('Creating simple Sortable without handle...');
                const simple = new Sortable(container, {
                    animation: 150,
                    onStart: function(evt) {
                        console.log('SIMPLE DRAG START!', evt.item);
                    },
                    onEnd: function(evt) {
                        console.log('SIMPLE DRAG END!', evt.item);
                    }
                });
                console.log('Simple Sortable created:', simple);
                console.log('Try dragging a milestone now (click anywhere on it)');
            } catch (error) {
                console.error('Failed to create simple Sortable:', error);
            }
        };
        
        console.log('Run window.testSimpleSortable() to test without handles');
    }

    // Update milestone order via AJAX
    function updateMilestoneOrder() {
        const milestones = [];
        document.querySelectorAll('.milestone-item').forEach((item, index) => {
            milestones.push({
                id: item.dataset.milestoneId,
                sort_order: index + 1
            });
        });

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
                console.log('Milestone order updated');
            }
        })
        .catch(error => {
            console.error('Error updating milestone order:', error);
        });
    }

    // Update task order via AJAX
    function updateTaskOrder(milestoneId) {
        const tasks = [];
        document.querySelectorAll(`.tasks-container[data-milestone-id="${milestoneId}"] .task-item`).forEach((item, index) => {
            tasks.push({
                id: item.dataset.taskId,
                sort_order: index + 1
            });
        });

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
                console.log('Task order updated');
            }
        })
        .catch(error => {
            console.error('Error updating task order:', error);
        });
    }

    // Update subtask order via AJAX
    function updateSubtaskOrder(taskId) {
        const subtasks = [];
        document.querySelectorAll(`.subtasks-container[data-task-id="${taskId}"] .subtask-item`).forEach((item, index) => {
            subtasks.push({
                id: item.dataset.subtaskId,
                sort_order: index + 1
            });
        });

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
                console.log('Subtask order updated');
            }
        })
        .catch(error => {
            console.error('Error updating subtask order:', error);
        });
    }
</script>
@endpush