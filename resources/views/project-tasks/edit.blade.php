@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li><a href="{{ route('projects.index') }}" class="hover:opacity-80" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Projects</a></li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('projects.show', $project) }}" class="ml-1 hover:opacity-80" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">{{ $project->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('projects.milestones.show', [$project, $projectMilestone]) }}" class="ml-1 hover:opacity-80" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">{{ $projectMilestone->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text);">Edit: {{ $task->name }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold" style="color: var(--theme-text);">Edit Task</h1>
                    <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Update task information for milestone: {{ $projectMilestone->name }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3" style="background-color: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--theme-accent); border-radius: var(--theme-border-radius);">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" style="color: var(--theme-accent);">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium" style="font-size: var(--theme-font-size);">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 px-4 py-3" style="background-color: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); color: #dc2626; border-radius: var(--theme-border-radius);">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" style="color: #dc2626;">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium" style="font-size: var(--theme-font-size);">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('project-milestones.tasks.update', [$projectMilestone, $task]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="bg-white/60 backdrop-blur-sm rounded-xl" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3); box-shadow: var(--theme-card-shadow);">
                {{-- Basic Information --}}
                <div class="px-6 py-4 border-b" style="border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2);">
                    <h2 class="font-semibold" style="font-size: calc(var(--theme-font-size) + 4px); color: var(--theme-text);">Task Information</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                            Task Name <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $task->name) }}" required
                            class="block w-full rounded-md border transition-colors duration-200" 
                            style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                            onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                            onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                        @error('name')
                            <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="block w-full rounded-md border transition-colors duration-200" 
                            style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white; resize: vertical;"
                            onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                            onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">{{ old('description', $task->description) }}</textarea>
                        @error('description')
                            <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status and Dates --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label for="status" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Status <span style="color: #dc2626;">*</span>
                            </label>
                            <select name="status" id="status" required
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                                <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ old('status', $task->status) === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                            @error('status')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_date" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Start Date
                            </label>
                            <input type="date" name="start_date" id="start_date" 
                                value="{{ old('start_date', $task->start_date ? $task->start_date->format('Y-m-d') : '') }}"
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            @error('start_date')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                End Date
                            </label>
                            <input type="date" name="end_date" id="end_date" 
                                value="{{ old('end_date', $task->end_date ? $task->end_date->format('Y-m-d') : '') }}"
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            @error('end_date')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Financial Settings --}}
                <div class="px-6 py-4 border-t" style="border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2);">
                    <h2 class="font-semibold" style="font-size: calc(var(--theme-font-size) + 4px); color: var(--theme-text);">Financial Settings</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        {{-- Fee Type --}}
                        <div>
                            <label for="fee_type" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Fee Type <span style="color: #dc2626;">*</span>
                            </label>
                            <select name="fee_type" id="fee_type" required
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                                <option value="in_fee" {{ old('fee_type', $task->fee_type) === 'in_fee' ? 'selected' : '' }}>In Fee</option>
                                <option value="extended" {{ old('fee_type', $task->fee_type) === 'extended' ? 'selected' : '' }}>Extended</option>
                            </select>
                            @error('fee_type')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pricing Type --}}
                        <div>
                            <label for="pricing_type" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Pricing Type <span style="color: #dc2626;">*</span>
                            </label>
                            <select name="pricing_type" id="pricing_type" required
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'"
                                onchange="togglePricingFields()">
                                <option value="hourly_rate" {{ old('pricing_type', $task->pricing_type) === 'hourly_rate' ? 'selected' : '' }}>Hourly Rate</option>
                                <option value="fixed_price" {{ old('pricing_type', $task->pricing_type) === 'fixed_price' ? 'selected' : '' }}>Fixed Price</option>
                            </select>
                            @error('pricing_type')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fixed Price --}}
                        <div id="fixed_price_field" style="display: {{ old('pricing_type', $task->pricing_type) === 'fixed_price' ? 'block' : 'none' }}">
                            <label for="fixed_price" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Fixed Price (€)
                            </label>
                            <input type="number" name="fixed_price" id="fixed_price" step="0.01" min="0" 
                                value="{{ old('fixed_price', $task->fixed_price) }}"
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            @error('fixed_price')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Hourly Rate Override --}}
                        <div id="hourly_rate_field" style="display: {{ old('pricing_type', $task->pricing_type) === 'hourly_rate' ? 'block' : 'none' }}">
                            <label for="hourly_rate_override" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Hourly Rate Override (€)
                            </label>
                            <input type="number" name="hourly_rate_override" id="hourly_rate_override" step="0.01" min="0" 
                                value="{{ old('hourly_rate_override', $task->hourly_rate_override) }}"
                                placeholder="Leave empty to use milestone rate"
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            @error('hourly_rate_override')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Estimated Hours --}}
                        <div>
                            <label for="estimated_hours" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Estimated Hours
                            </label>
                            <input type="number" name="estimated_hours" id="estimated_hours" step="0.5" min="0" 
                                value="{{ old('estimated_hours', $task->estimated_hours) }}"
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            @error('estimated_hours')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="sort_order" class="block font-medium mb-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" id="sort_order" min="0" 
                                value="{{ old('sort_order', $task->sort_order) }}"
                                class="block w-full rounded-md border transition-colors duration-200" 
                                style="font-size: var(--theme-font-size); padding: 12px 16px; border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); background: white;"
                                onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(' + getComputedStyle(document.documentElement).getPropertyValue('--theme-primary-rgb') + ', 0.1)'"
                                onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            @error('sort_order')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="px-6 py-4 border-t flex justify-between" style="background: rgba(var(--theme-border-rgb, 226, 232, 240), 0.05); border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2);">
                    <button type="button" onclick="if(confirm('Are you sure you want to delete this task?')) { document.getElementById('delete-form').submit(); }"
                        class="inline-flex items-center px-4 py-2 rounded-md font-medium transition-all"
                        style="background-color: #dc2626; color: white; font-size: var(--theme-font-size); border: none;"
                        onmouseover="this.style.backgroundColor='#b91c1c'"
                        onmouseout="this.style.backgroundColor='#dc2626'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Task
                    </button>
                    <div class="flex space-x-3">
                        <a href="{{ route('project-milestones.tasks.show', [$projectMilestone, $task]) }}"
                            class="inline-flex items-center px-4 py-2 rounded-md font-medium transition-all border"
                            style="background-color: white; color: var(--theme-text); border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); font-size: var(--theme-font-size);"
                            onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.05)'"
                            onmouseout="this.style.backgroundColor='white'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-md font-medium transition-all"
                            style="background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); border: none;"
                            onmouseover="this.style.opacity='0.9'"
                            onmouseout="this.style.opacity='1'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Task
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Delete Form --}}
        <form id="delete-form" action="{{ route('project-milestones.tasks.destroy', [$projectMilestone, $task]) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
function togglePricingFields() {
    const pricingType = document.getElementById('pricing_type').value;
    const fixedPriceField = document.getElementById('fixed_price_field');
    const hourlyRateField = document.getElementById('hourly_rate_field');
    
    if (pricingType === 'fixed_price') {
        fixedPriceField.style.display = 'block';
        hourlyRateField.style.display = 'none';
    } else {
        fixedPriceField.style.display = 'none';
        hourlyRateField.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePricingFields();
});
</script>
@endsection