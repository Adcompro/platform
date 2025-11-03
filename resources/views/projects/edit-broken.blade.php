{{-- 📁 Locatie: resources/views/projects/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Project - ' . $project->name)

@push('styles')
<style>
    /* Drag & Drop Styles */
    .sortable-ghost {
        opacity: 0.4;
        background: #f0f9ff;
    }
    
    .sortable-drag {
        opacity: 0.9 !important;
        transform: rotate(2deg);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .sortable-task.sortable-chosen {
        border-color: #3b82f6;
        background-color: #f0f9ff;
    }
    
    .drag-handle {
        opacity: 0.5;
        transition: opacity 0.2s;
    }
    
    .sortable-task:hover .drag-handle {
        opacity: 1;
    }
    
    .sortable-tasks {
        min-height: 20px;
    }
</style>
@endpush

@section('header')
<div class="flex items-center justify-between">
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
    
    {{-- Action buttons --}}
    <div class="flex items-center space-x-3">
        {{-- Time Entries button --}}
        <a href="{{ route('time-entries.index') }}?project_id={{ $project->id }}" 
           class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm font-medium bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
           style="border-color: rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text-muted); font-size: var(--theme-font-size);">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
            </svg>
            Time Entries
        </a>
        
        {{-- View Project button --}}
        <a href="{{ route('projects.show', $project) }}" 
           class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm font-medium bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
           style="border-color: rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text-muted); font-size: var(--theme-font-size);">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View Project
        </a>
        
        {{-- Switch editor link --}}
        <a href="{{ route('projects.edit', $project) }}" 
           style="font-size: var(--theme-font-size); color: var(--theme-primary);" 
           class="hover:opacity-80">
            Switch to classic editor
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Tab Navigation --}}
        <div class="bg-white sticky top-0 z-10" style="background: white; border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.2); margin-bottom: -1px;">
            <nav class="flex space-x-0 px-6" aria-label="Tabs">
                <button onclick="" 
                        
                        
                        class="relative py-4 px-6 transition-all duration-200 flex items-center space-x-2.5"
                        style="font-size: var(--theme-font-size); text-transform: uppercase; letter-spacing: 0.05em;">
                    <svg class="w-4 h-4" 
                         
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"></path>
                    </svg>
                    <span>General</span>
                    <span x-show="hasErrors.general" 
                          class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                </button>

                <button onclick="" 
                        
                        
                        class="relative py-4 px-6 transition-all duration-200 flex items-center space-x-2.5"
                        style="font-size: var(--theme-font-size); text-transform: uppercase; letter-spacing: 0.05em;">
                    <svg class="w-4 h-4" 
                         
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 1 1-6 0 3 3 0 016 0zm6 3a2 2 0 1 1-4 0 2 2 0 014 0zM7 10a2 2 0 1 1-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Team</span>
                    <span x-show="hasErrors.team" 
                          class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                </button>

                <button onclick="" 
                        
                        
                        class="relative py-4 px-6 transition-all duration-200 flex items-center space-x-2.5"
                        style="font-size: var(--theme-font-size); text-transform: uppercase; letter-spacing: 0.05em;">
                    <svg class="w-4 h-4" 
                         
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span>Project Tasks</span>
                    <span x-show="hasErrors.structure" 
                          class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                </button>

                <button onclick="" 
                        
                        
                        class="relative py-4 px-6 transition-all duration-200 flex items-center space-x-2.5"
                        style="font-size: var(--theme-font-size); text-transform: uppercase; letter-spacing: 0.05em;">
                    <svg class="w-4 h-4" 
                         
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"></path>
                    </svg>
                    <span>Billing</span>
                    <span x-show="hasErrors.billing" 
                          class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                </button>

                <button onclick="" 
                        
                        
                        class="relative py-4 px-6 transition-all duration-200 flex items-center space-x-2.5"
                        style="font-size: var(--theme-font-size); text-transform: uppercase; letter-spacing: 0.05em;">
                    <svg class="w-4 h-4" 
                         
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"></path>
                    </svg>
                    <span>AI Settings</span>
                    <span x-show="hasErrors.ai" 
                          class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                </button>


                <button onclick="" 
                        
                        
                        class="relative py-4 px-6 transition-all duration-200 flex items-center space-x-2.5"
                        style="font-size: var(--theme-font-size); text-transform: uppercase; letter-spacing: 0.05em;">
                    <svg class="w-4 h-4" 
                         
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"></path>
                    </svg>
                    <span>Info</span>
                </button>
            </nav>
        </div>

        {{-- Form Start --}}
        <form action="{{ route('projects.update', $project) }}" method="POST" id="project-edit-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="from_tabbed_editor" value="1">
            
            {{-- Tab Content Container --}}
            <div class="bg-white" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3); border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3); border-radius: 0 0 var(--theme-border-radius, 0.75rem) var(--theme-border-radius, 0.75rem); overflow: hidden; margin-top: -1px; box-shadow: var(--theme-card-shadow);">
                
                {{-- General Tab --}}
                <div x-show="activeTab === 'general'" x-transition>
                    <div class="p-6 space-y-6">
                        {{-- Basic Information Section --}}
                        <div>
                            <h3 class="font-semibold mb-4" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Basic Information</h3>
                            
                            {{-- Project Name (full width) --}}
                            <div class="mb-6">
                                <label for="name" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                    Project Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name', $project->name) }}"
                                       required
                                       class="w-full shadow-sm @error('name') border-red-500 @enderror"
                                       style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);"
                                       placeholder="Enter project name...">
                                @error('name')
                                <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Two columns for Customer and Status --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                {{-- Customer Selection --}}
                                <div>
                                    <label for="customer_id" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Customer <span class="text-red-500">*</span>
                                    </label>
                                    <select name="customer_id" 
                                            id="customer_id" 
                                            required
                                            class="w-full shadow-sm @error('customer_id') border-red-500 @enderror"
                                            style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);">
                                        <option value="">Select a customer...</option>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $project->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label for="status" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" 
                                            id="status" 
                                            required
                                            class="w-full shadow-sm @error('status') border-red-500 @enderror"
                                            style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);">
                                        <option value="draft" {{ old('status', $project->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="on_hold" {{ old('status', $project->status) === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $project->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Project Description (full width) --}}
                            <div>
                                <label for="description" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                    Description
                                </label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="4"
                                          class="w-full shadow-sm @error('description') border-red-500 @enderror"
                                          style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);"
                                          placeholder="Describe the project scope, goals, and requirements...">{{ old('description', $project->description) }}</textarea>
                                @error('description')
                                <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Timeline Section --}}
                        <div class="pt-6" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                            <h3 class="font-semibold mb-4" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Timeline</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Start Date --}}
                                <div>
                                    <label for="start_date" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Start Date
                                    </label>
                                    <input type="date" 
                                           name="start_date" 
                                           id="start_date" 
                                           value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                                           class="w-full shadow-sm @error('start_date') border-red-500 @enderror"
                                           style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);">
                                    @error('start_date')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- End Date --}}
                                <div>
                                    <label for="end_date" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        End Date
                                    </label>
                                    <input type="date" 
                                           name="end_date" 
                                           id="end_date" 
                                           value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
                                           class="w-full shadow-sm @error('end_date') border-red-500 @enderror"
                                           style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);">
                                    @error('end_date')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Tab --}}
                <div x-show="activeTab === 'billing'" x-transition style="display: none;">
                    <div class="p-6 space-y-6">
                        {{-- Pricing Section --}}
                        <div>
                            <h3 class="font-semibold mb-4" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Pricing & Rates</h3>
                            
                            {{-- Two columns for Monthly Fee and Hourly Rate --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                {{-- Monthly Fee --}}
                                <div>
                                    <label for="monthly_fee" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Monthly Fee (€)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3" style="color: var(--theme-text-muted); font-size: var(--theme-font-size); top: 0.5rem;">€</span>
                                        <input type="number" 
                                               name="monthly_fee" 
                                               id="monthly_fee" 
                                               value="{{ old('monthly_fee', $project->monthly_fee) }}"
                                               step="0.01" 
                                               min="0"
                                               class="w-full pl-8 shadow-sm @error('monthly_fee') border-red-500 @enderror"
                                               style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem 0.5rem 2rem; color: var(--theme-text);"
                                               placeholder="0.00">
                                    </div>
                                    <p class="mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Fixed monthly budget cap</p>
                                    @error('monthly_fee')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Default Hourly Rate --}}
                                <div>
                                    <label for="hourly_rate" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Default Hourly Rate (€)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3" style="color: var(--theme-text-muted); font-size: var(--theme-font-size); top: 0.5rem;">€</span>
                                        <input type="number" 
                                               name="hourly_rate" 
                                               id="hourly_rate" 
                                               value="{{ old('hourly_rate', $project->hourly_rate) }}"
                                               step="0.01" 
                                               min="0"
                                               class="w-full pl-8 shadow-sm @error('hourly_rate') border-red-500 @enderror"
                                               style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem 0.5rem 2rem; color: var(--theme-text);"
                                               placeholder="0.00">
                                    </div>
                                    <p class="mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Can be overridden per task</p>
                                    @error('hourly_rate')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Invoice Template (full width) --}}
                            <div>
                                <label for="invoice_template_billing" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                    Invoice Template for this Project
                                </label>
                                <select name="invoice_template_id" 
                                        id="invoice_template_billing" 
                                        class="w-full shadow-sm @error('invoice_template_id') border-red-500 @enderror"
                                        style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);">
                                    <option value="">Use customer/company default template</option>
                                    @foreach($invoiceTemplates as $template)
                                    <option value="{{ $template->id }}" {{ old('invoice_template_id', $project->invoice_template_id) == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }}
                                        @if($template->company_id)
                                            (Company Template)
                                        @else
                                            (System Template)
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                                <p class="mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                    This template will be used when generating invoices for this project. Leave empty to use the customer or company default.
                                </p>
                                @error('invoice_template_id')
                                <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Billing Schedule Section --}}
                        <div class="border-t pt-6">
                            <h3 class="font-semibold mb-4" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Billing Schedule</h3>
                            
                            {{-- Two columns for Frequency and Custom Interval/Next Date --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                {{-- Billing Frequency --}}
                                <div>
                                    <label for="billing_frequency" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Billing Frequency
                                    </label>
                                    <select name="billing_frequency" 
                                            id="billing_frequency" 
                                            class="w-full shadow-sm @error('billing_frequency') border-red-500 @enderror"
                                            style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);"
                                            x-model="billingFrequency">
                                        <option value="monthly" {{ old('billing_frequency', $project->billing_frequency ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('billing_frequency', $project->billing_frequency) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="yearly" {{ old('billing_frequency', $project->billing_frequency) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        <option value="custom" {{ old('billing_frequency', $project->billing_frequency) === 'custom' ? 'selected' : '' }}>Custom</option>
                                    </select>
                                    @error('billing_frequency')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Custom Billing Interval (shows when custom is selected) --}}
                                <div x-show="billingFrequency === 'custom'" style="display: none;">
                                    <label for="custom_billing_interval" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Custom Interval (days)
                                    </label>
                                    <input type="number" 
                                           name="custom_billing_interval" 
                                           id="custom_billing_interval" 
                                           value="{{ old('custom_billing_interval', $project->custom_billing_interval) }}"
                                           min="1"
                                           class="w-full shadow-sm @error('custom_billing_interval') border-red-500 @enderror"
                                           style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);"
                                           placeholder="30">
                                    <p class="mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Days between billing cycles</p>
                                    @error('custom_billing_interval')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Next Billing Date (shows when custom is not selected) --}}
                                <div x-show="billingFrequency !== 'custom'">
                                    <label for="next_billing_date" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        Next Billing Date
                                    </label>
                                    <input type="date" 
                                           name="next_billing_date" 
                                           id="next_billing_date" 
                                           value="{{ old('next_billing_date', $project->next_billing_date?->format('Y-m-d')) }}"
                                           class="w-full shadow-sm @error('next_billing_date') border-red-500 @enderror"
                                           style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);">
                                    @error('next_billing_date')
                                    <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Last Billing Date (read-only) --}}
                            @if($project->last_billing_date)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                    Last Billing Date
                                </label>
                                <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    {{ $project->last_billing_date->format('F j, Y') }}
                                    <span class="ml-2" style="color: var(--theme-text-muted);">({{ $project->last_billing_date->diffForHumans() }})</span>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Team Tab --}}
                <div x-show="activeTab === 'team'" x-transition>
                    <div class="p-6 space-y-6">
                        <h3 class="font-semibold mb-4" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Company & Team Setup</h3>
                        
                        @if($isCompaniesPluginActive)
                        {{-- Main Invoicing Company --}}
                        <div>
                            <label for="main_invoicing_company_id" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                Main Invoicing Company <span class="text-red-500">*</span>
                            </label>
                            <select name="main_invoicing_company_id" 
                                    id="main_invoicing_company_id" 
                                    required
                                    class="w-full shadow-sm @error('main_invoicing_company_id') border-red-500 @enderror"
                                    style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem); font-size: var(--theme-font-size); padding: 0.5rem 0.75rem; color: var(--theme-text);">
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('main_invoicing_company_id', $project->main_invoicing_company_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">The company that will send invoices to the customer.</p>
                            @error('main_invoicing_company_id')
                            <p class="mt-1" style="font-size: var(--theme-font-size); color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contributing Companies --}}
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <div>
                            <label class="block font-medium mb-2" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                Contributing Companies
                            </label>
                            <div class="space-y-3" id="contributing-companies">
                                @foreach($companies as $company)
                                @php
                                    $projectCompany = $project->companies->firstWhere('id', $company->id);
                                    $isContributing = $projectCompany !== null;
                                    $billingMethod = $projectCompany?->pivot->billing_method ?? 'hourly_rate';
                                    $billingAmount = $projectCompany?->pivot->billing_amount ?? '';
                                @endphp
                                <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-md">
                                    <input type="checkbox" 
                                           name="contributing_companies[]" 
                                           value="{{ $company->id }}"
                                           id="company_{{ $company->id }}"
                                           {{ $isContributing ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <label for="company_{{ $company->id }}" class="flex-1 font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                        {{ $company->name }}
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <select name="billing_method[{{ $company->id }}]" class="rounded" style="font-size: calc(var(--theme-font-size) - 2px); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text); padding: 0.25rem 0.5rem;">
                                            <option value="hourly_rate" {{ $billingMethod === 'hourly_rate' ? 'selected' : '' }}>Hourly Rate</option>
                                            <option value="fixed_monthly" {{ $billingMethod === 'fixed_monthly' ? 'selected' : '' }}>Fixed Monthly</option>
                                        </select>
                                        <input type="number" 
                                               name="billing_amount[{{ $company->id }}]"
                                               value="{{ $billingAmount }}"
                                               placeholder="Amount"
                                               step="0.01"
                                               min="0"
                                               class="w-20 rounded"
                                               style="font-size: calc(var(--theme-font-size) - 2px); border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text); padding: 0.25rem 0.5rem;">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p class="mt-2" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Select companies that will work on this project and configure their billing method.</p>
                        </div>
                        @endif
                        @else
                        {{-- Single Company Mode --}}
                        <input type="hidden" name="main_invoicing_company_id" value="{{ $defaultCompany->id ?? Auth::user()->company_id }}">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p style="font-size: var(--theme-font-size); color: #1e40af;">
                                Single company mode: Using {{ $defaultCompany->name ?? Auth::user()->company->name }} as the invoicing company.
                            </p>
                        </div>
                        @endif

                        {{-- Team Members --}}
                        <div id="team-member-selector">
                            <label class="block text-sm font-medium mb-2" style="color: var(--theme-text-muted); ">
                                Team Members
                            </label>
                            
                            {{-- Hidden inputs for team members --}}
                            <div id="team-members-hidden-inputs">
                                {{-- Always include this to ensure team_members key exists even when empty --}}
                                <input type="hidden" name="team_members_present" value="1">
                                @foreach($project->users as $member)
                                    <input type="hidden" name="team_members[]" value="{{ $member->id }}" id="team-member-input-{{ $member->id }}">
                                @endforeach
                            </div>
                            
                            {{-- Selected Team Members Display --}}
                            <div class="mb-3 space-y-2" id="team-members-container">
                                @foreach($project->users as $member)
                                <div id="team-member-{{ $member->id }}" class="flex items-center justify-between p-2 rounded-lg" style="background-color: rgba(var(--theme-primary-rgb, 37, 99, 235), 0.05); border: 1px solid rgba(var(--theme-primary-rgb, 37, 99, 235), 0.2);">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-medium" style="background-color: var(--theme-primary);">
                                            {{ substr($member->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium" style="color: var(--theme-text); ">{{ $member->name }}</div>
                                            <div class="text-xs" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                                {{ ucfirst(str_replace('_', ' ', $member->role)) }}
                                                @if($member->companyRelation)
                                                 • {{ $member->companyRelation->name }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            onclick="event.preventDefault(); event.stopPropagation(); removeTeamMember({{ $member->id }}); return false;"
                                            class="text-red-500 hover:text-red-700 transition-colors remove-member-btn"
                                            data-member-id="{{ $member->id }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>

                            {{-- Add Team Member Input --}}
                            <div class="relative"  @click.away="setTimeout(() => showDropdown = false, 200)">
                                <div class="flex items-center space-x-2">
                                    <div class="relative flex-1">
                                        <input type="text" 
                                               id="team-member-search"
                                               x-model="search"
                                               @input="searchUsers"
                                               @focus="showDropdown = true"
                                               placeholder="Type to search and add team members..."
                                               class="w-full pl-10 pr-4 shadow-sm"
                                               style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius-sm, 0.375rem);  padding: 0.5rem 0.75rem 0.5rem 2.5rem;">
                                        <svg class="absolute left-3 top-2.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--theme-text-muted);">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 1 1-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <button type="button" 
                                            onclick=""
                                            class="px-4 py-2 text-white transition-colors"
                                            style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius-sm); ">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Dropdown Results --}}
                                <div x-show="showDropdown && filteredUsers.length > 0" 
                                     x-transition
                                     class="absolute z-10 w-full mt-1 bg-white rounded-lg overflow-hidden"
                                     style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); max-height: 240px; overflow-y: auto; box-shadow: var(--theme-card-shadow);">
                                    <template x-for="user in filteredUsers" :key="user.id">
                                        <div onclick=""
                                             class="p-3 hover:bg-gray-50 cursor-pointer transition-colors flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium" style="color: var(--theme-text);">
                                                    <span x-text="user.name.substring(0, 2).toUpperCase()"></span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium" x-text="user.name" style="color: var(--theme-text); "></div>
                                                    <div class="text-xs" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                                        <span x-text="user.role_display"></span>
                                                        <span x-show="user.company_name" x-text="' • ' + user.company_name"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span x-show="isSelected(user.id)" class="text-green-500">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <p class="mt-2 text-xs" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Search and add team members who will have access to this project.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- AI Settings Tab --}}
                <div x-show="activeTab === 'ai'" x-transition style="display: none;">
                    <div class="p-6">
                        @include('projects.ai-settings')
                    </div>
                </div>

                {{-- Project Tasks Tab --}}
                <div x-show="activeTab === 'structure'" x-transition style="display: none;">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6 pb-3" style="border-bottom: 1px solid var(--theme-border-light);">
                            <div>
                                <h3 class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Project Tasks</h3>
                                <p class="mt-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Milestones and tasks for this project</p>
                            </div>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <button type="button" 
                                    onclick="" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                                    style="background-color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 2px);"
                                    onmouseover="this.style.backgroundColor='var(--theme-primary-dark)'"
                                    onmouseout="this.style.backgroundColor='var(--theme-primary)'">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Milestone
                            </button>
                            @endif
                        </div>

                        {{-- Month Navigation for Project Tasks --}}
                        <div class="flex items-center justify-center mb-6 p-4 rounded-lg" style="background: rgba(var(--theme-bg-light-rgb), 0.5); border: 1px solid var(--theme-border-light);">
                            <div class="flex items-center space-x-4">
                                <button onclick="" 
                                        :disabled="monthLoading"
                                        class="flex items-center px-3 py-2 rounded-md font-medium transition-colors hover:bg-white/60"
                                        style="border: 1px solid var(--theme-border-light); color: var(--theme-text); font-size: var(--theme-font-size);"
                                        >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    Previous
                                </button>

                                <div class="text-center min-w-[180px]">
                                    <div class="font-semibold" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) + 2px);" x-text="currentMonthDisplay">
                                        @php
                                            $currentMonth = request('month', now()->format('Y-m'));
                                            $monthDisplay = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('F Y');
                                        @endphp
                                        {{ $monthDisplay }}
                                    </div>
                                </div>

                                <button onclick="" 
                                        :disabled="monthLoading"
                                        class="flex items-center px-3 py-2 rounded-md font-medium transition-colors hover:bg-white/60"
                                        style="border: 1px solid var(--theme-border-light); color: var(--theme-text); font-size: var(--theme-font-size);"
                                        >
                                    Next
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="ml-6 flex items-center space-x-2">
                                <button onclick="" 
                                        class="px-3 py-1 text-sm rounded-md font-medium transition-colors hover:bg-white/60"
                                        style="border: 1px solid var(--theme-border-light); color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    Today
                                </button>
                                <div x-show="monthLoading" class="ml-2">
                                    <svg class="animate-spin h-4 w-4" style="color: var(--theme-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Project Summary Stats - Dynamic based on month selection --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 rounded-lg" style="background: rgba(var(--theme-primary-rgb), 0.1); border: 1px solid rgba(var(--theme-primary-rgb), 0.2);">
                                <div class="font-bold" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) + 6px);" 
                                     x-text="monthData && monthStats ? monthStats.total_milestones : {{ $project->milestones->count() }}">
                                    {{ $project->milestones->count() }}
                                </div>
                                <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Milestones</div>
                            </div>
                            <div class="text-center p-4 rounded-lg" style="background: rgba(var(--theme-accent-rgb), 0.1); border: 1px solid rgba(var(--theme-accent-rgb), 0.2);">
                                <div class="font-bold" style="color: var(--theme-accent); font-size: calc(var(--theme-font-size) + 6px);" 
                                     x-text="monthData && monthStats ? monthStats.total_tasks : {{ $project->milestones->sum(function($m) { return $m->tasks->count(); }) }}">
                                    {{ $project->milestones->sum(function($m) { return $m->tasks->count(); }) }}
                                </div>
                                <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Tasks</div>
                            </div>
                            <div class="text-center p-4 rounded-lg" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);">
                                <div class="font-bold" style="color: rgb(34, 197, 94); font-size: calc(var(--theme-font-size) + 6px);" 
                                     x-text="monthData && monthStats ? monthStats.completed_milestones : {{ $project->milestones->where('status', 'completed')->count() }}">
                                    {{ $project->milestones->where('status', 'completed')->count() }}
                                </div>
                                <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Completed</div>
                            </div>
                            <div class="text-center p-4 rounded-lg" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                                <div class="font-bold" style="color: rgb(59, 130, 246); font-size: calc(var(--theme-font-size) + 6px);" 
                                     x-text="monthData && monthStats ? monthStats.in_progress_milestones || 0 : {{ $project->milestones->where('status', 'in_progress')->count() }}">
                                    {{ $project->milestones->where('status', 'in_progress')->count() }}
                                </div>
                                <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">In Progress</div>
                            </div>
                        </div>

                        <div id="milestones-container" class="space-y-6">
                            {{-- Server-side rendered milestones (fallback when no AJAX data) --}}
                            <div>
                                {{-- Beautiful Card Layout like show view --}}
                                <div class="space-y-6">
                                    @forelse($project->milestones()->orderBy('sort_order')->get() as $milestone)
                                        {{-- Milestone Card --}}
                                        <div class="milestone-card rounded-lg p-6" 
                                             style="border: 1px solid rgba(var(--theme-primary-rgb), 0.3); background: rgba(var(--theme-primary-rgb), 0.05);"
                                             data-milestone-id="{{ $milestone->id }}">
                                            {{-- Milestone Header --}}
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
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
                                                    <span class="status-badge px-2 py-1 rounded-full text-xs font-medium {{ $milestone->status === 'completed' ? 'bg-green-100 text-green-800' : ($milestone->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($milestone->status) }}
                                                    </span>
                                                    
                                                    {{-- Action Buttons --}}
                                                    <div class="flex items-center space-x-2">
                                                        <button onclick="editMilestone({{ $milestone->id }})" 
                                                                class="p-2 rounded-md hover:bg-white/60 transition-colors" 
                                                                style="color: var(--theme-primary);"
                                                                title="Edit Milestone">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                            <button onclick="openTaskModal({{ $milestone->id }})" 
                                                                    class="p-2 rounded-md hover:bg-white/60 transition-colors" 
                                                                    style="color: var(--theme-accent);"
                                                                    title="Add Task">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Tasks Section --}}
                                            @if($milestone->tasks->count() > 0)
                                                <div class="mt-4 space-y-2">
                                                    {{-- Tasks Cards --}}
                                                    @foreach($milestone->tasks->sortBy('sort_order') as $task)
                                                        <div class="ml-6 p-3 rounded-md sortable-task" 
                                                             style="background: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.2);"
                                                             data-task-id="{{ $task->id }}"
                                                             data-milestone-id="{{ $milestone->id }}">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex-1">
                                                                    <div class="flex items-center space-x-3">
                                                                        {{-- Drag Handle --}}
                                                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                                            <div class="drag-handle cursor-move opacity-60 hover:opacity-100 transition-opacity" title="Drag to reorder task">
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
                                                                    
                                                                    {{-- Action Buttons --}}
                                                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                                        <div class="flex items-center space-x-1">
                                                                            <button onclick=""
                                                                                    class="p-1.5 rounded hover:bg-white/60 transition-colors" 
                                                                                    style="color: var(--theme-primary);"
                                                                                    title="Edit Task">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                                </svg>
                                                                            </button>
                                                                            <button onclick="confirmDeleteTask({{ $task->id }}, '{{ $task->name }}')" 
                                                                                    class="p-1.5 rounded hover:bg-white/60 transition-colors" 
                                                                                    style="color: #dc2626;"
                                                                                    title="Delete Task">
                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                                    
                                    @empty
                                        <div class="px-4 py-8 text-center">
                                            <h3 class="mt-2 font-medium" style="color: var(--theme-text-primary);">No milestones yet</h3>
                                            <p class="mt-1" style="color: var(--theme-text-secondary);">Get started by creating your first milestone.</p>
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <div class="mt-6">
                                                <button type="button"
                                                        onclick="" 
                                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                                                        style="background-color: var(--theme-primary);"
                                                        onmouseover="this.style.backgroundColor='var(--theme-primary-dark)'"
                                                        onmouseout="this.style.backgroundColor='var(--theme-primary)'">
                                                    Create your first milestone
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="px-4 py-8 text-center">
                                            <h3 class="mt-2 font-medium" style="color: var(--theme-text);">No milestones yet</h3>
                                            <p class="mt-1" style="color: var(--theme-text-muted);">Get started by creating your first milestone.</p>
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <div class="mt-6">
                                                <button type="button"
                                                        onclick="" 
                                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                                                        style="background-color: var(--theme-primary);"
                                                        onmouseover="this.style.backgroundColor='var(--theme-primary-dark)'"
                                                        onmouseout="this.style.backgroundColor='var(--theme-primary)'">
                                                    Create your first milestone
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    @endforelse
                            </div>
                            
                            {{-- AJAX loaded content --}}
                            <template x-if="monthData">
                                <div>
                                    <template x-if="monthData.milestones && monthData.milestones.length > 0">
                                        <div>
                                            <template x-for="milestone in monthData.milestones" :key="milestone.id">
                                                <div class="milestone-item rounded-lg p-6 mb-6" 
                                                     style="border: 1px solid rgba(var(--theme-primary-rgb), 0.3); background: rgba(var(--theme-primary-rgb), 0.05);">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex-1">
                                                            <div class="flex items-center gap-3 mb-2">
                                                                <h3 class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);" x-text="milestone.name"></h3>
                                                                
                                                                <span class="px-2 py-1 rounded text-xs font-medium"
                                                                      :class="{
                                                                          'bg-green-100 text-green-800': milestone.status === 'completed',
                                                                          'bg-blue-100 text-blue-800': milestone.status === 'in_progress',
                                                                          'bg-yellow-100 text-yellow-800': milestone.status === 'pending',
                                                                          'bg-red-100 text-red-800': milestone.status === 'on_hold'
                                                                      }"
                                                                      x-text="milestone.status.charAt(0).toUpperCase() + milestone.status.slice(1).replace('_', ' ')">
                                                                </span>
                                                                
                                                                <button onclick="" class="px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-blue-600" style="background-color: var(--theme-primary); color: white;" type="button">
                                                                    Details
                                                                </button>
                                                            </div>
                                                            
                                                            <template x-if="milestone.description">
                                                                <p class="mb-3" style="color: var(--theme-text-muted); font-size: var(--theme-font-size); line-height: 1.5;" x-text="milestone.description"></p>
                                                            </template>
                                                        </div>
                                                        
                                                        {{-- Monthly Cost Display for Milestone --}}
                                                        <div class="text-right">
                                                            <template x-if="milestone.monthly_cost && milestone.monthly_cost > 0">
                                                                <div class="font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                                                    €<span x-text="milestone.monthly_cost.toFixed(2)"></span>
                                                                </div>
                                                            </template>
                                                            <template x-if="milestone.monthly_hours && milestone.monthly_hours > 0">
                                                                <div class="text-xs" style="color: var(--theme-text-muted);">
                                                                    <span x-text="milestone.monthly_hours.toFixed(1)"></span>h this month
                                                                </div>
                                                            </template>
                                                            <template x-if="(!milestone.monthly_cost || milestone.monthly_cost == 0) && (!milestone.monthly_hours || milestone.monthly_hours == 0)">
                                                                <div class="text-xs" style="color: var(--theme-text-muted);">
                                                                    No time logged
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    {{-- Tasks --}}
                                                    <template x-if="milestone.tasks && milestone.tasks.length > 0">
                                                        <div class="mt-4 space-y-2">
                                                            <template x-for="task in milestone.tasks" :key="task.id">
                                                                <div class="ml-6 p-3 rounded-md" style="background: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.2);">
                                                                    <div class="flex items-center justify-between">
                                                                        <div class="flex-1">
                                                                            <div class="flex items-center gap-2 mb-1">
                                                                                <h4 class="font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);" x-text="task.name"></h4>
                                                                                
                                                                                <span class="px-2 py-1 rounded text-xs font-medium"
                                                                                      :class="{
                                                                                          'bg-yellow-100 text-yellow-700': task.status === 'pending',
                                                                                          'bg-blue-100 text-blue-700': task.status === 'in_progress',
                                                                                          'bg-green-100 text-green-700': task.status === 'completed',
                                                                                          'bg-red-100 text-red-700': task.status === 'on_hold'
                                                                                      }"
                                                                                      x-text="task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ')">
                                                                                </span>
                                                                                
                                                                                <button onclick="" class="px-3 py-1 text-xs font-medium rounded-md transition-colors hover:bg-blue-600" style="background-color: var(--theme-primary); color: white;" type="button">
                                                                                    Details
                                                                                </button>
                                                                            </div>
                                                                            
                                                                            <template x-if="task.description">
                                                                                <p class="mb-2" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.4;" x-text="task.description"></p>
                                                                            </template>
                                                                        </div>
                                                                        
                                                                        {{-- Monthly Cost Display --}}
                                                                        <div class="text-right">
                                                                            <template x-if="task.monthly_cost && task.monthly_cost > 0">
                                                                                <div class="font-medium" style="color: var(--theme-accent); font-size: var(--theme-font-size);">
                                                                                    €<span x-text="task.monthly_cost.toFixed(2)"></span>
                                                                                </div>
                                                                            </template>
                                                                            <template x-if="task.monthly_hours && task.monthly_hours > 0">
                                                                                <div class="text-xs" style="color: var(--theme-text-muted);">
                                                                                    <span x-text="task.monthly_hours.toFixed(1)"></span>h this month
                                                                                </div>
                                                                            </template>
                                                                            <template x-if="(!task.monthly_cost || task.monthly_cost == 0) && (!task.monthly_hours || task.monthly_hours == 0)">
                                                                                <div class="text-xs" style="color: var(--theme-text-muted);">
                                                                                    No time logged
                                                                                </div>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    
                                    <template x-if="!monthData.milestones || monthData.milestones.length === 0">
                                        <div class="text-center py-12">
                                    <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"></path>
                                    </svg>
                                            <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                                No milestones found for <span x-text="currentMonthDisplay"></span>
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                    </div>
                </div>

                {{-- Info Tab --}}
                <div x-show="activeTab === 'info'" x-transition style="display: none;">
                    <div class="p-6">
                        <h3 class="font-semibold mb-4" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Project Statistics</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="text-center">
                                <div class="text-3xl font-bold" style="color: var(--theme-text);">{{ $project->milestones_count ?? 0 }}</div>
                                <div class="text-sm" style="color: var(--theme-text-muted);">Milestones</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold" style="color: var(--theme-text);">{{ $project->tasks_count ?? 0 }}</div>
                                <div class="text-sm" style="color: var(--theme-text-muted);">Tasks</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold" style="color: var(--theme-text);">{{ number_format($project->total_time_logged ?? 0, 1) }}h</div>
                                <div class="text-sm" style="color: var(--theme-text-muted);">Time Logged</div>
                            </div>
                        </div>
                        
                        @if(($project->milestones_count ?? 0) > 0)
                        <div class="mb-8">
                            <div class="flex justify-between mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                                <span>Overall Progress</span>
                                <span>{{ round((($project->completed_milestones_count ?? 0) / $project->milestones_count) * 100) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" 
                                     style="width: {{ round((($project->completed_milestones_count ?? 0) / $project->milestones_count) * 100) }}%"></div>
                            </div>
                        </div>
                        @endif

                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="font-medium mb-4" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-text);">Timeline Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium" style="color: var(--theme-text-muted);">Created:</span>
                                    <span style="color: var(--theme-text);">{{ $project->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium" style="color: var(--theme-text-muted);">Last Updated:</span>
                                    <span style="color: var(--theme-text);">{{ $project->updated_at->diffForHumans() }}</span>
                                </div>
                                @if($project->start_date)
                                <div>
                                    <span class="font-medium" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Start Date:</span>
                                    <span style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $project->start_date->format('M j, Y') }}</span>
                                </div>
                                @endif
                                @if($project->end_date)
                                <div>
                                    <span class="font-medium" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">End Date:</span>
                                    <span style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $project->end_date->format('M j, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if($project->created_by || $project->updated_by)
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h4 class="font-medium mb-4" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-text);">Audit Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if($project->created_by)
                                <div>
                                    <span class="font-medium" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Created By:</span>
                                    <span style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $project->creator->name ?? 'System' }}</span>
                                </div>
                                @endif
                                @if($project->updated_by)
                                <div>
                                    <span class="font-medium" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Last Updated By:</span>
                                    <span style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $project->updater->name ?? 'System' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Sticky Action Bar --}}
            <div class="theme-card px-6 py-4 sticky bottom-0 z-10" style="background-color: rgba(var(--theme-bg-rgb, 255, 255, 255), 0.95); backdrop-filter: blur(10px); border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); margin-top: 2rem;">
                <div class="flex justify-between items-center">
                    <div>
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <button type="button" onclick="deleteProject()" 
                                class="px-4 py-2 border border-transparent text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2"
                                style="background-color: var(--theme-danger); border-radius: var(--theme-border-radius-sm); ">
                            Delete Project
                        </button>
                        @endif
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('projects.show', $project) }}" 
                           class="px-6 py-2 border rounded-md font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                           style="border-color: rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 border border-transparent text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2"
                                style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius-sm); ">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
        {{-- Milestone Modal --}}
        <div x-show="showMilestoneModal" 
             x-cloak
             @keydown.escape.window="closeMilestoneModal()"
             class="fixed inset-0 overflow-y-auto"
             style="z-index: 9999;">
            <div x-show="showMilestoneModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 onclick=""
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed top-4 right-4 bottom-4 w-1/2 max-w-lg">
                <div x-show="showMilestoneModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-4 scale-95"
                     @click.stop
                     class="bg-white rounded-lg text-left overflow-hidden transform transition-all h-full"
                     style="box-shadow: var(--theme-card-shadow);">
                    
                    <form @submit.prevent="editingMilestoneId ? updateMilestone() : saveMilestone()">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="leading-6 font-medium mb-4" style="font-size: calc(var(--theme-font-size) + 4px); color: var(--theme-text);">
                                <span x-text="editingMilestoneId ? 'Edit Milestone' : 'Add New Milestone'"></span>
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="milestone_name" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Name *</label>
                                    <input type="text" 
                                           id="milestone_name"
                                           x-model="milestoneForm.name"
                                           required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label for="milestone_description" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Description</label>
                                    <textarea id="milestone_description"
                                              x-model="milestoneForm.description"
                                              rows="3"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="milestone_start_date" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Start Date</label>
                                        <input type="date" 
                                               id="milestone_start_date"
                                               x-model="milestoneForm.start_date"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="milestone_end_date" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">End Date</label>
                                        <input type="date" 
                                               id="milestone_end_date"
                                               x-model="milestoneForm.end_date"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="milestone_status" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Status</label>
                                        <select id="milestone_status"
                                                x-model="milestoneForm.status"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="pending">Pending</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="on_hold">On Hold</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="milestone_fee_type" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Fee Type</label>
                                        <select id="milestone_fee_type"
                                                x-model="milestoneForm.fee_type"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="in_fee">In Fee</option>
                                            <option value="extended">Extended</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="milestone_pricing_type" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Pricing Type</label>
                                        <select id="milestone_pricing_type"
                                                x-model="milestoneForm.pricing_type"
                                                @change="milestoneForm.pricing_type === 'fixed_price' ? milestoneForm.hourly_rate_override = null : milestoneForm.fixed_price = null"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="hourly_rate">Hourly Rate</option>
                                            <option value="fixed_price">Fixed Price</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="milestone_invoicing_trigger" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Invoice On</label>
                                        <select id="milestone_invoicing_trigger"
                                                x-model="milestoneForm.invoicing_trigger"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="completion">Completion</option>
                                            <option value="approval">Approval</option>
                                            <option value="delivery">Delivery</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div x-show="milestoneForm.pricing_type === 'fixed_price'">
                                        <label for="milestone_fixed_price" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Fixed Price (€)</label>
                                        <input type="number" 
                                               id="milestone_fixed_price"
                                               x-model="milestoneForm.fixed_price"
                                               step="0.01"
                                               min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div x-show="milestoneForm.pricing_type === 'hourly_rate'">
                                        <label for="milestone_hourly_rate" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Hourly Rate Override (€)</label>
                                        <input type="number" 
                                               id="milestone_hourly_rate"
                                               x-model="milestoneForm.hourly_rate_override"
                                               step="0.01"
                                               min="0"
                                               placeholder="Leave empty to use project rate"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="milestone_estimated_hours" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Estimated Hours</label>
                                        <input type="number" 
                                               id="milestone_estimated_hours"
                                               x-model="milestoneForm.estimated_hours"
                                               step="0.5"
                                               min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="milestone_deliverables" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Deliverables</label>
                                    <textarea id="milestone_deliverables"
                                              x-model="milestoneForm.deliverables"
                                              rows="2"
                                              placeholder="List the deliverables for this milestone"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    :disabled="milestoneForm.saving"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-show="!milestoneForm.saving" x-text="editingMilestoneId ? 'Update Milestone' : 'Save Milestone'"></span>
                                <span x-show="milestoneForm.saving" x-text="editingMilestoneId ? 'Updating...' : 'Saving...'"></span>
                            </button>
                            <button type="button" 
                                    onclick=""
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Unsaved Changes Modal --}}
        <div x-show="showUnsavedModal" 
             x-cloak
             @keydown.escape.window="cancelNavigation()"
             class="fixed inset-0 overflow-y-auto z-50">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showUnsavedModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     onclick=""
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showUnsavedModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     style="box-shadow: var(--theme-card-shadow);">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10" 
                                 style="background-color: rgba(var(--theme-warning-rgb, 251, 191, 36), 0.1);">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                     style="color: rgb(251, 191, 36);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="leading-6 font-medium" style="font-size: calc(var(--theme-font-size) + 4px); color: var(--theme-text);">
                                    Unsaved Changes
                                </h3>
                                <div class="mt-2">
                                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                                        You have unsaved changes. Are you sure you want to leave this page? Your changes will be lost.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                onclick=""
                                class="w-full inline-flex justify-center rounded-md border border-transparent px-4 py-2 font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto"
                                style="background-color: rgb(239, 68, 68); font-size: var(--theme-font-size);"
                                onmouseover="this.style.backgroundColor='rgb(220, 38, 38)'" 
                                onmouseout="this.style.backgroundColor='rgb(239, 68, 68)'">
                            Leave Without Saving
                        </button>
                        <button type="button" 
                                onclick=""
                                class="w-full inline-flex justify-center rounded-md border border-transparent px-4 py-2 font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto"
                                style="background-color: var(--theme-primary); font-size: var(--theme-font-size);"
                                onmouseover="this.style.opacity='0.9'" 
                                onmouseout="this.style.opacity='1'">
                            Save Changes
                        </button>
                        <button type="button" 
                                onclick=""
                                class="mt-3 w-full inline-flex justify-center rounded-md border px-4 py-2 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto"
                                style="border-color: rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Task Modal --}}
        <div x-show="showTaskModal" 
             x-cloak
             @keydown.escape.window="closeTaskModal()"
             class="fixed inset-0 overflow-y-auto"
             style="z-index: 9999;">
            <div x-show="showTaskModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 onclick=""
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed top-4 right-4 bottom-4 w-1/2 max-w-lg">
                <div x-show="showTaskModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-4 scale-95"
                     @click.stop
                     class="bg-white rounded-lg text-left overflow-hidden transform transition-all h-full"
                     style="box-shadow: var(--theme-card-shadow);">
                    
                    <form @submit.prevent="editingTaskId ? updateTask() : saveTask()">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="leading-6 font-medium mb-4" style="font-size: calc(var(--theme-font-size) + 4px); color: var(--theme-text);">
                                <span x-text="editingTaskId ? 'Edit Task' : 'Add New Task'"></span>
                            </h3>
                            
                            <div class="space-y-4">
                                {{-- Basic Information --}}
                                <div>
                                    <label for="task_name" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Name *</label>
                                    <input type="text" 
                                           id="task_name"
                                           x-model="taskForm.name"
                                           required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                           style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                </div>
                                
                                <div>
                                    <label for="task_description" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Description</label>
                                    <textarea id="task_description"
                                              x-model="taskForm.description"
                                              rows="3"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                              style="font-size: var(--theme-font-size); color: var(--theme-text);"></textarea>
                                </div>
                                
                                {{-- Status and Priority --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="task_status" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Status</label>
                                        <select id="task_status"
                                                x-model="taskForm.status"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            <option value="pending">Pending</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="on_hold">On Hold</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="task_priority" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Priority</label>
                                        <select id="task_priority"
                                                x-model="taskForm.priority"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>
                                </div>
                                
                                {{-- Timeline --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="task_start_date" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Start Date</label>
                                        <input type="date" 
                                               id="task_start_date"
                                               x-model="taskForm.start_date"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    </div>
                                    
                                    <div>
                                        <label for="task_end_date" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">End Date</label>
                                        <input type="date" 
                                               id="task_end_date"
                                               x-model="taskForm.end_date"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    </div>
                                </div>
                                
                                {{-- Fee and Pricing --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="task_fee_type" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Fee Type</label>
                                        <select id="task_fee_type"
                                                x-model="taskForm.fee_type"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            <option value="in_fee">In Fee</option>
                                            <option value="extended">Extended</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="task_pricing_type" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Pricing Type</label>
                                        <select id="task_pricing_type"
                                                x-model="taskForm.pricing_type"
                                                @change="taskForm.pricing_type === 'fixed_price' ? taskForm.hourly_rate_override = null : taskForm.fixed_price = null"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            <option value="hourly_rate">Hourly Rate</option>
                                            <option value="fixed_price">Fixed Price</option>
                                        </select>
                                    </div>
                                </div>
                                
                                {{-- Pricing Details --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div x-show="taskForm.pricing_type === 'fixed_price'">
                                        <label for="task_fixed_price" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Fixed Price (€)</label>
                                        <input type="number" 
                                               id="task_fixed_price"
                                               x-model="taskForm.fixed_price"
                                               step="0.01"
                                               min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    </div>
                                    
                                    <div x-show="taskForm.pricing_type === 'hourly_rate'">
                                        <label for="task_hourly_rate" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Hourly Rate Override (€)</label>
                                        <input type="number" 
                                               id="task_hourly_rate"
                                               x-model="taskForm.hourly_rate_override"
                                               step="0.01"
                                               min="0"
                                               placeholder="Leave empty to use milestone rate"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    </div>
                                    
                                    <div>
                                        <label for="task_estimated_hours" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Estimated Hours</label>
                                        <input type="number" 
                                               id="task_estimated_hours"
                                               x-model="taskForm.estimated_hours"
                                               step="0.5"
                                               min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    </div>
                                </div>
                                
                                {{-- Progress and Notes --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="task_completion" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Completion %</label>
                                        <input type="number" 
                                               id="task_completion"
                                               x-model="taskForm.completion_percentage"
                                               min="0"
                                               max="100"
                                               step="5"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    </div>
                                    
                                    <div>
                                        <label for="task_actual_hours" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Actual Hours</label>
                                        <input type="number" 
                                               id="task_actual_hours"
                                               x-model="taskForm.actual_hours"
                                               step="0.5"
                                               min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    </div>
                                </div>
                                
                                {{-- Notes --}}
                                <div>
                                    <label for="task_notes" class="block font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Notes</label>
                                    <textarea id="task_notes"
                                              x-model="taskForm.notes"
                                              rows="2"
                                              placeholder="Additional notes or comments about this task"
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                              style="font-size: var(--theme-font-size); color: var(--theme-text);"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    :disabled="taskForm.saving"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span x-show="!taskForm.saving" x-text="editingTaskId ? 'Update Task' : 'Save Task'"></span>
                                <span x-show="taskForm.saving" x-text="editingTaskId ? 'Updating...' : 'Saving...'"></span>
                            </button>
                            <button type="button" 
                                    onclick=""
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- New Customer Modal (reuse from original) --}}
<div id="newCustomerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 rounded-md bg-white" style="box-shadow: var(--theme-card-shadow);">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Customer</h3>
            <form id="newCustomerForm">
                <div class="mb-4">
                    <label for="customerName" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                    <input type="text" id="customerName" name="name" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="customerEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="customerEmail" name="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="customerPhone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" id="customerPhone" name="phone" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="customerAddress" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea id="customerAddress" name="address" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="customerContactPerson" class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" id="customerContactPerson" name="contact_person" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeNewCustomerModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                        Add Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Provide data for Alpine.js components --}}
<script type="application/json" id="team-users-data">
{!! json_encode($users->map(function($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'company_name' => $user->company ? $user->company->name : 'No Company'
    ];
})) !!}
</script>

<script type="application/json" id="project-data">
{!! json_encode([
    'id' => $project->id,
    'billing_frequency' => old('billing_frequency', $project->billing_frequency ?? 'monthly'),
    'current_month' => request('month', now()->format('Y-m')),
    'current_tab' => request('tab', 'general')
]) !!}
</script>

<script>
// DEFINITIVE CLEAN DRAG & DROP - CACHE BUST v2.0 - 12 Sep 2025
document.addEventListener('DOMContentLoaded', function() {
    initializeDragDrop();
});

function initializeDragDrop() {
    console.log('🎯 TASK-ONLY DRAG & DROP INITIALIZING...');
    
    // Initialize only task sorting (milestone sorting removed)
    initializeTaskSorting();
}


// Store active Sortable instances to destroy them later
let taskSortableInstances = [];

function initializeTaskSorting() {
    console.log('🔧 Initializing task sorting...');
    
    document.querySelectorAll('.milestone-card').forEach(milestone => {
        const milestoneId = milestone.getAttribute('data-milestone-id');
        if (!milestoneId || typeof Sortable === 'undefined') return;
        
        // Find tasks for this milestone
        const allTasks = document.querySelectorAll(`[data-task-id][data-milestone-id="${milestoneId}"]`);
        console.log(`Milestone ${milestoneId}: found ${allTasks.length} tasks`);
        if (allTasks.length < 2) return; // Need at least 2 to sort
        
        // Create virtual container for these tasks
        const tasksContainer = allTasks[0].parentElement;
        
        const sortableInstance = new Sortable(tasksContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.drag-handle',
            draggable: `[data-task-id][data-milestone-id="${milestoneId}"]`,
            onStart: function(evt) {
                console.log(`🚀 Task drag started for milestone ${milestoneId}`);
            },
            onEnd: function(evt) {
                console.log(`✅ Task drag ended for milestone ${milestoneId}`);
                const taskIds = Array.from(tasksContainer.querySelectorAll(`[data-task-id][data-milestone-id="${milestoneId}"]`)).map(item => 
                    parseInt(item.getAttribute('data-task-id'))
                ).filter(id => !isNaN(id));
                console.log(`New task order for milestone ${milestoneId}:`, taskIds);
                reorderTasks(milestoneId, taskIds);
            }
        });
        
        // Store instance for later cleanup
        taskSortableInstances.push(sortableInstance);
        console.log(`✅ Task sorting enabled for milestone ${milestoneId}`);
    });
}

function reinitializeTaskSorting() {
    console.log('🔄 Destroying old task Sortable instances...');
    
    // Destroy all existing task sortable instances
    taskSortableInstances.forEach(instance => {
        if (instance && instance.destroy) {
            instance.destroy();
        }
    });
    taskSortableInstances = [];
    
    console.log('🔄 Creating new task Sortable instances...');
    // Reinitialize task sorting
    initializeTaskSorting();
    
    console.log('✅ Task sorting reinitialized');
}

// Reorder functions

async function reorderTasks(milestoneId, taskIds) {
    try {
        const response = await fetch(`/project-milestones/${milestoneId}/tasks/reorder`, {
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
    } catch (error) {
        console.error('Error reordering tasks:', error);
        location.reload();
    }
}

// Make functions available globally
window.initializeDragDrop = initializeDragDrop;
window.reorderTasks = reorderTasks;
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


[data-task-id]:hover {
    cursor: grab;
}

[data-task-id]:active {
    cursor: grabbing;
}
</style>
{{-- Milestone Details Modal --}}
<div id="milestoneModal" class="fixed inset-0 bg-black bg-opacity-50 hidden" style="display: none; z-index: 9999;">
    <div class="fixed top-4 right-4 bottom-4 w-1/2 max-w-4xl">
        <div class="bg-white rounded-lg shadow-xl h-full overflow-y-auto" onclick="event.stopPropagation()">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold" style="color: var(--theme-text);">Milestone Details</h3>
                <button onclick="closeMilestoneModal(event)" class="text-gray-400 hover:text-gray-600" type="button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="milestoneModalContent" class="p-6">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

{{-- Task Details Modal --}}
<div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden" style="display: none; z-index: 9999;">
    <div class="fixed top-4 right-4 bottom-4 w-1/2 max-w-4xl">
        <div class="bg-white rounded-lg shadow-xl h-full overflow-y-auto" onclick="event.stopPropagation()">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold" style="color: var(--theme-text);">Task Details</h3>
                <button onclick="" class="text-gray-400 hover:text-gray-600" type="button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="taskModalContent" class="p-6">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>
