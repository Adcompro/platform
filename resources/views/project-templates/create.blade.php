@extends('layouts.app')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, var(--theme-background) 0%, var(--theme-surface) 50%, var(--theme-background) 100%);">
    {{-- Header Section --}}
    <div class="backdrop-blur-sm border-b" style="background: rgba(var(--theme-surface-rgb), 0.6); border-color: rgba(var(--theme-border-rgb), 0.6);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold" style="color: var(--theme-text);">New Project Template</h1>
                    <p class="text-sm" style="color: var(--theme-text-muted);">Create a reusable template for quick project setup</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('project-templates.index') }}" class="flex items-center transition-colors duration-200 group" style="color: var(--theme-text-muted);" onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                        <svg class="w-4 h-4 mr-2 group-hover:text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--theme-text-muted);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span class="text-sm">Back to Templates</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <form method="POST" action="{{ route('project-templates.store') }}" id="template-form">
            @csrf
            
            {{-- Single Grid: All content --}}
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                {{-- Left Column: Template Basic Info + Milestones Builder --}}
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    {{-- Template Basic Info --}}
                    <div class="rounded-lg shadow-sm" style="background: var(--theme-surface); border: 1px solid rgba(var(--theme-border-rgb), 0.3);">
                        <div class="p-6">
                            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.2);">
                                <h2 class="text-[17px] font-semibold" style="color: var(--theme-text);">Template Information</h2>
                                <p class="text-sm mt-1" style="color: var(--theme-text-muted);">Basic details for the template</p>
                            </div>
                            <div class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium" style="color: var(--theme-text);">Template Name*</label>
                                <input type="text" name="name" id="name" required
                                       class="mt-1 block w-full rounded-md shadow-sm"
                                       style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium" style="color: var(--theme-text);">Beschrijving</label>
                                <textarea name="description" id="description" rows="3"
                                          class="mt-1 block w-full rounded-md shadow-sm" 
                                          style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                                          onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                          onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'"></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="default_hourly_rate" class="block text-sm font-medium" style="color: var(--theme-text);">Standaard Uurtarief (€)</label>
                                    <input type="number" name="default_hourly_rate" id="default_hourly_rate" step="0.01" min="0"
                                           class="mt-1 block w-full rounded-md shadow-sm" 
                                           style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                                           onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                           onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                                    @error('default_hourly_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="estimated_duration_days" class="block text-sm font-medium" style="color: var(--theme-text);">Geschatte Duur (dagen)</label>
                                    <input type="number" name="estimated_duration_days" id="estimated_duration_days" min="1"
                                           class="mt-1 block w-full rounded-md shadow-sm" 
                                           style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                                           onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                           onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                                    @error('estimated_duration_days')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium" style="color: var(--theme-text);">Status</label>
                                    <select name="status" id="status" 
                                            class="mt-1 block w-full rounded-md shadow-sm" 
                                            style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                                            onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                            onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                
                    {{-- Milestones Builder --}}
                    <div class="shadow rounded-lg" style="background: var(--theme-surface);">
                        <div class="px-6 py-4" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.3);">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium" style="color: var(--theme-text);">Template Structuur</h3>
                                    <p class="mt-1 text-sm" style="color: var(--theme-text-muted);">Voeg milestones en tasks toe</p>
                                </div>
                                <button type="button" onclick="addMilestone()" class="text-white px-4 py-2 rounded-lg transition-colors" 
                                        style="background: var(--theme-primary);" 
                                        onmouseover="this.style.opacity='0.9'" 
                                        onmouseout="this.style.opacity='1'">
                                    <i class="fas fa-plus mr-2"></i>Milestone Toevoegen
                                </button>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <div id="milestones-container" class="space-y-6">
                                {{-- Milestones worden hier dynamisch toegevoegd --}}
                                <div class="text-center py-8" id="empty-state" style="color: var(--theme-text-muted);">
                                    <i class="fas fa-tasks text-4xl mb-4"></i>
                                    <p>Nog geen milestones toegevoegd</p>
                                    <p class="text-sm">Klik op "Milestone Toevoegen" om te beginnen</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Sidebar --}}
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    {{-- Template Stats --}}
                    <div class="rounded-lg shadow-sm" style="background: var(--theme-surface); border: 1px solid rgba(var(--theme-border-rgb), 0.3);">
                        <div class="p-6">
                            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.2);">
                                <h2 class="text-[17px] font-semibold" style="color: var(--theme-text);">Template Statistics</h2>
                            </div>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm" style="color: var(--theme-text-muted);">Milestones:</span>
                                    <span class="font-medium" id="stats-milestones" style="color: var(--theme-text);">0</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm" style="color: var(--theme-text-muted);">Tasks:</span>
                                    <span class="font-medium" id="stats-tasks" style="color: var(--theme-text);">0</span>
                                </div>
                                <hr>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm" style="color: var(--theme-text-muted);">Total Hours:</span>
                                    <span class="font-medium" id="stats-hours" style="color: var(--theme-text);">0h</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm" style="color: var(--theme-text-muted);">Estimated Value:</span>
                                    <span class="font-medium" id="stats-value" style="color: var(--theme-text);">€0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="rounded-lg shadow-sm" style="background: var(--theme-surface); border: 1px solid rgba(var(--theme-border-rgb), 0.3);">
                        <div class="p-6">
                            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.2);">
                                <h2 class="text-[17px] font-semibold" style="color: var(--theme-text);">Actions</h2>
                            </div>
                            <div class="space-y-3">
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" 
                                        style="background: var(--theme-primary); border-color: var(--theme-primary);" 
                                        onmouseover="this.style.opacity='0.9'" 
                                        onmouseout="this.style.opacity='1'" 
                                        onfocus="this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.2)'" 
                                        onblur="this.style.boxShadow='none'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Template
                                </button>
                                <a href="{{ route('project-templates.index') }}" class="w-full inline-flex items-center justify-center transition-colors duration-200 group" 
                                   style="color: var(--theme-text-muted);" 
                                   onmouseover="this.style.color='var(--theme-text)'" 
                                   onmouseout="this.style.color='var(--theme-text-muted)'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: inherit;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="text-sm">Cancel</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Milestone Template --}}
<template id="milestone-template">
    <div class="milestone-item rounded-lg p-6" data-milestone-index="" style="padding: 1.5rem; background: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.3);">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="drag-handle cursor-move mr-3" style="color: var(--theme-text-muted);">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <h4 class="text-lg font-medium" style="color: var(--theme-primary);">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full mr-2" style="background: rgba(var(--theme-primary-rgb), 0.2); color: var(--theme-primary); font-size: 0.875rem;">
                        <span class="milestone-number"></span>
                    </span>
                    Milestone
                </h4>
            </div>
            <button type="button" onclick="removeMilestone(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium" style="color: var(--theme-text);">Milestone Name*</label>
                <input type="text" name="milestones[0][name]" required
                       class="mt-1 block w-full rounded-md shadow-sm"
                       style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
            </div>
            <div>
                <label class="block text-sm font-medium" style="color: var(--theme-text);">Estimated Hours</label>
                <input type="number" name="milestones[0][estimated_hours]" min="0" step="0.5"
                       class="mt-1 block w-full rounded-md shadow-sm"
                       style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium" style="color: var(--theme-text);">Description</label>
            <textarea name="milestones[0][description]" rows="3"
                      class="mt-1 block w-full rounded-md shadow-sm"
                      style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                      onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                      onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium" style="color: var(--theme-text);">Fee Type</label>
                <select name="milestones[0][fee_type]"
                        class="mt-1 block w-full rounded-md shadow-sm"
                        style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text); padding: 0.5rem 0.75rem;"
                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                    <option value="in_fee">In Fee</option>
                    <option value="extended">Extended</option>
                </select>
            </div>
        </div>

        {{-- Tasks Container --}}
        <div class="tasks-container mt-6">
            <div class="flex items-center justify-between mb-4">
                <h5 class="text-md font-medium" style="color: var(--theme-text);">Tasks</h5>
                <button type="button" onclick="addTask(this)" class="text-white px-3 py-1 rounded text-sm" 
                        style="background: var(--theme-accent);" 
                        onmouseover="this.style.opacity='0.9'" 
                        onmouseout="this.style.opacity='1'">
                    <i class="fas fa-plus mr-1"></i>Add Task
                </button>
            </div>
            <div class="tasks-list space-y-3">
                {{-- Tasks worden hier toegevoegd --}}
            </div>
        </div>
    </div>
</template>

{{-- Task Template --}}
<template id="task-template">
    <div class="task-item rounded p-4" data-task-index="" style="padding: 1rem; background: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.3);">
        <div class="flex items-center justify-between mb-3">
            <h6 class="font-medium" style="color: var(--theme-accent);">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded mr-2" style="background: rgba(var(--theme-accent-rgb), 0.2); color: var(--theme-accent); font-size: 0.75rem;">
                    <span class="task-number"></span>
                </span>
                Task
            </h6>
            <button type="button" onclick="removeTask(this)" class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium" style="color: var(--theme-text);">Task Name*</label>
                <input type="text" name="milestones[0][tasks][0][name]" required
                       class="mt-1 block w-full rounded-md shadow-sm text-sm"
                       style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 1px var(--theme-accent)'"
                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
            </div>
            <div>
                <label class="block text-sm font-medium" style="color: var(--theme-text);">Estimated Hours</label>
                <input type="number" name="milestones[0][tasks][0][estimated_hours]" min="0" step="0.5"
                       class="mt-1 block w-full rounded-md shadow-sm text-sm"
                       style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 1px var(--theme-accent)'"
                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium" style="color: var(--theme-text);">Description</label>
            <textarea name="milestones[0][tasks][0][description]" rows="3"
                      class="mt-1 block w-full rounded-md shadow-sm text-sm"
                      style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);"
                      onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 1px var(--theme-accent)'"
                      onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-3">
            <div>
                <label class="block text-sm font-medium" style="color: var(--theme-text);">Fee Type</label>
                <select name="milestones[0][tasks][0][fee_type]"
                        class="mt-1 block w-full rounded-md shadow-sm text-sm"
                        style="background: var(--theme-input-bg); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text); padding: 0.5rem 0.75rem;"
                        onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 1px var(--theme-accent)'"
                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                    <option value="in_fee">In Fee</option>
                    <option value="extended">Extended</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
let milestoneCount = 0;

function addMilestone() {
    const container = document.getElementById('milestones-container');
    const emptyState = document.getElementById('empty-state');
    const template = document.getElementById('milestone-template');
    
    const clone = template.content.cloneNode(true);
    const milestoneDiv = clone.querySelector('.milestone-item');
    milestoneDiv.setAttribute('data-milestone-index', milestoneCount);
    clone.querySelector('.milestone-number').textContent = milestoneCount + 1;
    
    // Update form input names for proper indexing
    updateMilestoneInputNames(clone, milestoneCount);
    
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    container.appendChild(clone);
    milestoneCount++;
    updateStats();
}

function removeMilestone(button) {
    if (confirm('Are you sure you want to remove this milestone?')) {
        const milestoneDiv = button.closest('.milestone-item');
        milestoneDiv.remove();
        
        updateMilestoneNumbers();
        updateStats();
        
        // Show empty state if no milestones
        const container = document.getElementById('milestones-container');
        const emptyState = document.getElementById('empty-state');
        if (container.children.length === 1 && emptyState) { // Only empty state left
            emptyState.style.display = 'block';
        }
    }
}

function addTask(button) {
    const tasksContainer = button.closest('.milestone-item').querySelector('.tasks-list');
    const template = document.getElementById('task-template');
    const milestoneIndex = button.closest('.milestone-item').getAttribute('data-milestone-index');
    
    const taskCount = tasksContainer.children.length;
    
    const clone = template.content.cloneNode(true);
    clone.querySelector('.task-number').textContent = taskCount + 1;
    clone.querySelector('.task-item').setAttribute('data-task-index', taskCount);
    
    // Update form input names
    updateTaskInputNames(clone, milestoneIndex, taskCount);
    
    tasksContainer.appendChild(clone);
    updateStats();
}

function removeTask(button) {
    if (confirm('Are you sure you want to remove this task?')) {
        button.closest('.task-item').remove();
        updateTaskNumbers(button.closest('.tasks-list'));
        updateStats();
    }
}

function updateMilestoneInputNames(clone, index) {
    const inputs = clone.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        let name = input.getAttribute('name');
        if (name && name.includes('milestones[0]')) {
            name = name.replace('milestones[0]', `milestones[${index}]`);
            input.setAttribute('name', name);
        }
    });
}

function updateTaskInputNames(clone, milestoneIndex, taskIndex) {
    const inputs = clone.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        let name = input.getAttribute('name');
        if (name && name.includes('milestones[0][tasks][0]')) {
            name = name.replace('milestones[0][tasks][0]', `milestones[${milestoneIndex}][tasks][${taskIndex}]`);
            input.setAttribute('name', name);
        }
    });
}

function updateMilestoneNumbers() {
    const milestones = document.querySelectorAll('.milestone-item');
    milestones.forEach((milestone, index) => {
        milestone.setAttribute('data-milestone-index', index);
        milestone.querySelector('.milestone-number').textContent = index + 1;
        
        // Update input names
        updateMilestoneInputNames(milestone, index);
        
        // Update tasks within this milestone
        const tasks = milestone.querySelectorAll('.task-item');
        tasks.forEach((task, taskIndex) => {
            updateTaskInputNames(task, index, taskIndex);
        });
    });
    milestoneCount = milestones.length;
}

function updateTaskNumbers(tasksContainer) {
    const tasks = tasksContainer.querySelectorAll('.task-item');
    tasks.forEach((task, index) => {
        task.setAttribute('data-task-index', index);
        task.querySelector('.task-number').textContent = index + 1;
    });
}

function updateStats() {
    const milestones = document.querySelectorAll('.milestone-item').length;
    const tasks = document.querySelectorAll('.task-item').length;
    
    let totalHours = 0;
    const defaultRate = document.getElementById('default_hourly_rate').value || 75;
    
    // Calculate total hours from all inputs
    document.querySelectorAll('input[name*="estimated_hours"]').forEach(input => {
        const hours = parseFloat(input.value) || 0;
        totalHours += hours;
    });
    
    const totalValue = totalHours * parseFloat(defaultRate);
    
    document.getElementById('stats-milestones').textContent = milestones;
    document.getElementById('stats-tasks').textContent = tasks;
    document.getElementById('stats-hours').textContent = totalHours + 'h';
    document.getElementById('stats-value').textContent = '€' + totalValue.toLocaleString('nl-NL', {minimumFractionDigits: 2});
}

// Event listeners for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Update stats when form fields change
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name*="estimated_hours"], #default_hourly_rate, #name, #category')) {
            updateStats();
        }
    });
    
    // Initialize sortable for milestones (if SortableJS is loaded)
    if (typeof Sortable !== 'undefined') {
        new Sortable(document.getElementById('milestones-container'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                updateMilestoneNumbers();
                updateStats();
            }
        });
    }

    // Block form submission and use AJAX to see what happens
    const form = document.getElementById('template-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // STOP the form submission
            
            console.log('=== FORM SUBMISSION BLOCKED - TESTING WITH AJAX ===');
            
            const formData = new FormData(this);
            
            // Show what we're sending
            console.log('Sending to:', this.action);
            console.log('Method:', this.method);
            console.log('Data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log('  ' + key + ':', value);
            }
            
            // Send via AJAX
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response URL:', response.url);
                
                // Check if it's a redirect
                if (response.redirected) {
                    console.log('REDIRECTED TO:', response.url);
                    
                    // Check if redirected to show page (success)
                    if (response.url.match(/\/project-templates\/\d+$/)) {
                        console.log('SUCCESS! Template created. Redirecting to:', response.url);
                        window.location.href = response.url;
                        return null; // Stop processing
                    }
                }
                
                return response.text();
            })
            .then(html => {
                console.log('Response received. Length:', html.length);
                
                // Check for validation errors in the response
                if (html.includes('alert-danger') || html.includes('invalid-feedback')) {
                    console.log('VALIDATION ERRORS FOUND IN RESPONSE');
                    
                    // Try to extract error messages
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const errors = tempDiv.querySelectorAll('.invalid-feedback, .alert-danger');
                    errors.forEach(error => {
                        console.error('ERROR:', error.textContent.trim());
                    });
                }
                
                // Check for success message
                if (html.includes('success') || html.includes('created successfully')) {
                    console.log('SUCCESS MESSAGE FOUND!');
                    
                    // Extract success message
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const successAlerts = tempDiv.querySelectorAll('.alert-success');
                    successAlerts.forEach(alert => {
                        console.log('SUCCESS:', alert.textContent.trim());
                    });
                    
                    // Check if we're actually on the index page now
                    if (html.includes('project-templates') && html.includes('index')) {
                        console.log('TEMPLATE WAS SAVED! Redirected to index page.');
                        alert('Template saved successfully!');
                        window.location.href = '{{ route("project-templates.index") }}';
                    } else if (response.url.includes('/project-templates/') && !response.url.includes('/create')) {
                        // We're on the show page
                        const matches = response.url.match(/\/project-templates\/(\d+)/);
                        if (matches) {
                            console.log('TEMPLATE CREATED WITH ID:', matches[1]);
                            alert('Template saved successfully! ID: ' + matches[1]);
                            window.location.href = response.url;
                        }
                    }
                } else {
                    console.log('No success message found. First 1000 chars of response:');
                    console.log(html.substring(0, 1000));
                }
            })
            .catch(error => {
                console.error('AJAX ERROR:', error);
            });
        });
    }
});
</script>
@endsection