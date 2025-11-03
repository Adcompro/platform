@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
{{-- Sticky Header --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">New Project</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Create a new project for your customer</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.index') }}"
                   id="header-back-btn"
                   class="header-btn"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-arrow-left mr-1.5"></i>
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div style="padding: 1.5rem 2rem;">
        <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
            @csrf
            
            {{-- Template Selection Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Project Template (Optional)</h2>
                    <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;">Start from a template to quickly set up your project structure</p>
                </div>
                <div style="padding: var(--theme-card-padding);">
                    <select name="template_id"
                            id="template_id"
                            onchange="loadTemplateData(this.value)"
                            style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                        <option value="">-- No template (start from scratch) --</option>
                        @if(isset($templates))
                            @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }} 
                                @if($template->milestones_count)
                                    ({{ $template->milestones_count }} milestones)
                                @endif
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            
            {{-- Basic Project Information Card --}}
            <div class="theme-card bg-white/60 backdrop-blur-sm border border-slate-200/60 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Basic Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    {{-- Project Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                            Project Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}"
                               required
                               class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('name') border-red-300 @enderror"
                               placeholder="Enter project name...">
                        @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Customer Selection --}}
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-slate-700 mb-1">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-2">
                            <select name="customer_id" 
                                    id="customer_id" 
                                    required
                                    class="flex-1 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('customer_id') border-red-300 @enderror">
                                <option value="">Select a customer...</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                                @endforeach
                            </select>
                            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                            <button type="button" onclick="openNewCustomerModal()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                                <i class="fas fa-plus mr-1 text-xs"></i>
                                New
                            </button>
                            @endif
                        </div>
                        @error('customer_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Invoice Template --}}
                    <div>
                        <label for="invoice_template_id" class="block text-sm font-medium text-slate-700 mb-1">
                            Invoice Template
                        </label>
                        <select name="invoice_template_id" 
                                id="invoice_template_id" 
                                class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('invoice_template_id') border-red-300 @enderror">
                            <option value="">Use default template</option>
                            @if(isset($invoiceTemplates))
                                @foreach($invoiceTemplates as $template)
                                <option value="{{ $template->id }}" {{ old('invoice_template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }}
                                    @if($template->company_id)
                                        (Company Template)
                                    @else
                                        (System Template)
                                    @endif
                                </option>
                                @endforeach
                            @endif
                        </select>
                        <p class="mt-1 text-xs text-slate-500">
                            Select a specific invoice template for this project. If none is selected, the customer or company default will be used.
                        </p>
                        @error('invoice_template_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Project Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">
                            Description
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3"
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('description') border-red-300 @enderror"
                                  placeholder="Describe the project scope, goals, and requirements...">{{ old('description') }}</textarea>
                        @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" 
                                id="status" 
                                required
                                class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('status') border-red-300 @enderror">
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ old('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Project Timeline Card --}}
            <div class="theme-card bg-white/60 backdrop-blur-sm border border-slate-200/60 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Timeline</h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {{-- Start Date --}}
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   id="start_date" 
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('start_date') border-red-300 @enderror">
                            @error('start_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- End Date --}}
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">
                                End Date
                            </label>
                            <input type="date" 
                                   name="end_date" 
                                   id="end_date" 
                                   value="{{ old('end_date') }}"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('end_date') border-red-300 @enderror">
                            @error('end_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recurring Project Settings Card --}}
            <div class="theme-card bg-white/60 backdrop-blur-sm border border-slate-200/60 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Recurring Project Settings</h2>
                    <p class="text-xs text-slate-600 mt-1">Automatically create new projects for future periods</p>
                </div>
                <div class="p-4 space-y-4">
                    {{-- Recurring Checkbox --}}
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox"
                                   name="is_recurring"
                                   id="is_recurring"
                                   value="1"
                                   {{ old('is_recurring') ? 'checked' : '' }}
                                   onchange="toggleRecurringSettings(this.checked)"
                                   class="w-4 h-4 text-slate-600 bg-white border-slate-300 rounded focus:ring-slate-500">
                        </div>
                        <div class="ml-3">
                            <label for="is_recurring" class="text-sm font-medium text-slate-700">
                                Make this a recurring project
                            </label>
                            <p class="text-xs text-slate-500">
                                Automatically duplicate this project each month with the same structure, budget, and team
                            </p>
                        </div>
                    </div>

                    {{-- Master Template Mode Checkbox (only shown when is_recurring is checked) --}}
                    <div id="master-template-mode-container" style="display: {{ old('is_recurring') ? 'block' : 'none' }};" class="ml-7 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       name="is_master_template"
                                       id="is_master_template"
                                       value="1"
                                       {{ old('is_master_template') ? 'checked' : '' }}
                                       class="w-4 h-4 text-purple-600 bg-white border-purple-300 rounded focus:ring-purple-500">
                            </div>
                            <div class="ml-3">
                                <label for="is_master_template" class="text-sm font-medium text-purple-900">
                                    <i class="fas fa-crown text-purple-600 mr-1"></i>
                                    Use as Master Template
                                </label>
                                <p class="text-xs text-purple-700 mt-1">
                                    This project will serve as the template for all future projects in this series.
                                    Only general structure and settings will be stored - no month-specific milestones or time entries.
                                </p>
                                <div class="mt-2 p-2 bg-purple-100 rounded text-xs text-purple-800">
                                    <strong>Recommended for:</strong> Projects with variable monthly tasks where you want a clean template without month-specific items.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recurring Settings (alleen zichtbaar als checkbox is aangevinkt) --}}
                    <div id="recurring-settings" style="display: {{ old('is_recurring') ? 'block' : 'none' }};">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {{-- Base Name --}}
                                <div>
                                    <label for="recurring_base_name" class="block text-sm font-medium text-slate-700 mb-1">
                                        Base Project Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="recurring_base_name"
                                           id="recurring_base_name"
                                           value="{{ old('recurring_base_name') }}"
                                           class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                                           placeholder="e.g., Anker">
                                    <p class="text-xs text-slate-500 mt-1">
                                        Projects will be named: "[Base Name] [Month] [Year]" (e.g., "Anker Nov 2025")
                                    </p>
                                </div>

                                {{-- Frequency --}}
                                <div>
                                    <label for="recurring_frequency" class="block text-sm font-medium text-slate-700 mb-1">
                                        Frequency <span class="text-red-500">*</span>
                                    </label>
                                    <select name="recurring_frequency"
                                            id="recurring_frequency"
                                            class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                        <option value="monthly" {{ old('recurring_frequency', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('recurring_frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {{-- Days Before --}}
                                <div>
                                    <label for="recurring_days_before" class="block text-sm font-medium text-slate-700 mb-1">
                                        Create New Project (Days Before)
                                    </label>
                                    <input type="number"
                                           name="recurring_days_before"
                                           id="recurring_days_before"
                                           value="{{ old('recurring_days_before', 7) }}"
                                           min="1"
                                           max="30"
                                           class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                                           placeholder="7">
                                    <p class="text-xs text-slate-500 mt-1">
                                        How many days before the new period should the project be created
                                    </p>
                                </div>

                                {{-- End Date --}}
                                <div>
                                    <label for="recurring_end_date" class="block text-sm font-medium text-slate-700 mb-1">
                                        Stop Recurring On (Optional)
                                    </label>
                                    <input type="date"
                                           name="recurring_end_date"
                                           id="recurring_end_date"
                                           value="{{ old('recurring_end_date') }}"
                                           class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <p class="text-xs text-slate-500 mt-1">
                                        Leave empty to continue indefinitely
                                    </p>
                                </div>
                            </div>

                            {{-- Series ID Selection --}}
                            <div>
                                <label for="recurring_series_id" class="block text-sm font-medium text-slate-700 mb-1">
                                    Recurring Series ID
                                </label>
                                <div class="flex space-x-2">
                                    <select name="recurring_series_id_select"
                                            id="recurring_series_id_select"
                                            onchange="handleSeriesSelection(this.value)"
                                            class="flex-1 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                        <option value="">Auto-generate (series-{project_id})</option>
                                        <option value="_custom">Create new custom series ID</option>
                                        @if(isset($existingSeriesIds) && $existingSeriesIds->count() > 0)
                                            <optgroup label="Existing Series">
                                                @foreach($existingSeriesIds as $seriesId)
                                                <option value="{{ $seriesId->recurring_series_id }}"
                                                        {{ old('recurring_series_id') == $seriesId->recurring_series_id ? 'selected' : '' }}>
                                                    {{ $seriesId->recurring_series_id }}
                                                    ({{ $seriesId->project_count }} {{ $seriesId->project_count == 1 ? 'project' : 'projects' }})
                                                </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>
                                <input type="text"
                                       name="recurring_series_id"
                                       id="recurring_series_id_custom"
                                       value="{{ old('recurring_series_id') }}"
                                       style="display: none;"
                                       class="mt-2 w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                                       placeholder="e.g., series-anker-2025">
                                <p class="text-xs text-slate-500 mt-1">
                                    Group related projects together for consolidated budget tracking. Leave blank to auto-generate.
                                </p>
                            </div>

                            {{-- Info Box --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs text-blue-700">
                                            <strong>How it works:</strong> The system will automatically create a new project for each period with the same milestones, tasks, subtasks, team members, and budget. All new projects will start with "pending" status.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial Settings Card --}}
            <div class="theme-card bg-white/60 backdrop-blur-sm border border-slate-200/60 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Financial Settings</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {{-- Monthly Fee --}}
                        <div>
                            <label for="monthly_fee" class="block text-sm font-medium text-slate-700 mb-1">
                                Monthly Fee (€)
                            </label>
                            <input type="number" 
                                   name="monthly_fee" 
                                   id="monthly_fee" 
                                   value="{{ old('monthly_fee') }}"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('monthly_fee') border-red-300 @enderror"
                                   placeholder="0.00">
                            @error('monthly_fee')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Default Hourly Rate --}}
                        <div>
                            <label for="default_hourly_rate" class="block text-sm font-medium text-slate-700 mb-1">
                                Default Hourly Rate (€)
                            </label>
                            <input type="number" 
                                   name="default_hourly_rate" 
                                   id="default_hourly_rate" 
                                   value="{{ old('default_hourly_rate') }}"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('default_hourly_rate') border-red-300 @enderror"
                                   placeholder="75.00">
                            @error('default_hourly_rate')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- VAT Rate --}}
                        <div>
                            <label for="vat_rate" class="block text-sm font-medium text-slate-700 mb-1">
                                VAT Rate (%)
                            </label>
                            <input type="number" 
                                   name="vat_rate" 
                                   id="vat_rate" 
                                   value="{{ old('vat_rate', '21.00') }}"
                                   step="0.01" 
                                   min="0"
                                   max="100"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('vat_rate') border-red-300 @enderror"
                                   placeholder="21.00">
                            @error('vat_rate')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Billing Frequency --}}
                        <div>
                            <label for="billing_frequency" class="block text-sm font-medium text-slate-700 mb-1">
                                Billing Frequency
                            </label>
                            <select name="billing_frequency" 
                                    id="billing_frequency"
                                    onchange="toggleCustomInterval()"
                                    class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('billing_frequency') border-red-300 @enderror">
                                <option value="monthly" {{ old('billing_frequency', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('billing_frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="milestone" {{ old('billing_frequency') == 'milestone' ? 'selected' : '' }}>Per Milestone</option>
                                <option value="project_completion" {{ old('billing_frequency') == 'project_completion' ? 'selected' : '' }}>On Completion</option>
                                <option value="custom" {{ old('billing_frequency') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            @error('billing_frequency')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Custom Billing Interval --}}
                    <div id="custom_interval_section" style="{{ old('billing_frequency') == 'custom' ? '' : 'display: none;' }}">
                        <label for="billing_interval_days" class="block text-sm font-medium text-slate-700 mb-1">
                            Custom Interval (Days)
                        </label>
                        <input type="number" 
                               name="billing_interval_days" 
                               id="billing_interval_days" 
                               value="{{ old('billing_interval_days', 30) }}"
                               min="1"
                               max="365"
                               class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('billing_interval_days') border-red-300 @enderror"
                               placeholder="30">
                        @error('billing_interval_days')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Fee Rollover --}}
                    <div class="bg-slate-50 rounded-lg p-3">
                        <label class="flex items-start">
                            <input type="checkbox" 
                                   name="fee_rollover_enabled" 
                                   id="fee_rollover_enabled" 
                                   value="1"
                                   {{ old('fee_rollover_enabled', true) ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                            <div class="ml-2">
                                <span class="text-sm font-medium text-slate-700">Enable Fee Rollover</span>
                                <p class="text-xs text-slate-500 mt-0.5">Allow unused monthly fee to roll over to next month</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Company & Team Setup Card --}}
            <div class="theme-card bg-white/60 backdrop-blur-sm border border-slate-200/60 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Company & Team Setup</h2>
                </div>
                <div class="p-4 space-y-4">
                    {{-- Invoicing Company Selection --}}
                    @if(isset($companies) && $companies->count() > 0)
                    {{-- Show dropdown for company selection --}}
                    <div>
                        <label for="main_invoicing_company_id" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Invoicing Company <span style="color: var(--theme-danger);">*</span>
                        </label>
                        <select name="main_invoicing_company_id"
                                id="main_invoicing_company_id"
                                required
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            <option value="">Select company that will invoice the customer...</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('main_invoicing_company_id', auth()->user()->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">The company that will send invoices to the customer for this project</p>
                        @error('main_invoicing_company_id')
                        <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                        @enderror
                    </div>
                    @else
                    {{-- Fallback: Hidden field with default company --}}
                    <input type="hidden" name="main_invoicing_company_id" value="{{ auth()->user()->company_id }}">
                    @endif

                    {{-- Contributing Companies --}}
                    @if(isset($companies) && $companies->count() > 1 && in_array(auth()->user()->role, ['super_admin', 'admin']))
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Contributing Companies
                        </label>
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3">
                            @foreach($companies as $company)
                            <div class="flex items-center space-x-3 p-2 hover:bg-slate-50 rounded">
                                <input type="checkbox" 
                                       name="companies[]" 
                                       value="{{ $company->id }}"
                                       id="company_{{ $company->id }}"
                                       {{ in_array($company->id, old('companies', [])) || $company->id == auth()->user()->company_id ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                                <label for="company_{{ $company->id }}" class="flex-1 text-sm text-slate-700">
                                    {{ $company->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Select companies that will work on this project</p>
                    </div>
                    @endif

                    {{-- Team Members --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Team Members
                        </label>
                        <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3 space-y-2">
                            @foreach($users as $user)
                            <div class="flex items-center space-x-3 hover:bg-slate-50 rounded p-1">
                                <input type="checkbox" 
                                       name="team_members[]" 
                                       value="{{ $user->id }}"
                                       id="user_{{ $user->id }}"
                                       {{ in_array($user->id, old('team_members', [])) || $user->id == auth()->id() ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                                <label for="user_{{ $user->id }}" class="flex-1 text-sm text-slate-700">
                                    {{ $user->name }} 
                                    @if($user->companyRelation)
                                        <span class="text-slate-500">({{ $user->companyRelation->name }})</span>
                                    @endif
                                </label>
                                <span class="text-xs text-slate-500">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                            </div>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Select team members who will have access to this project</p>
                    </div>
                </div>
            </div>

            {{-- Notes Card --}}
            <div class="theme-card bg-white/60 backdrop-blur-sm border border-slate-200/60 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Additional Notes</h2>
                </div>
                <div class="p-4">
                    <textarea name="notes" 
                              id="notes" 
                              rows="3"
                              class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent @error('notes') border-red-300 @enderror"
                              placeholder="Add any internal notes about this project...">{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end space-x-2">
                <a href="{{ route('projects.index') }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200 shadow-sm">
                    <i class="fas fa-save mr-1.5 text-xs"></i>
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>

{{-- New Customer Modal (moderne styling) --}}
<div id="newCustomerModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 w-96">
        <div class="bg-white rounded-xl shadow-2xl">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Add New Customer</h3>
                <form id="newCustomerForm" class="space-y-3">
                    @csrf
                    <div>
                        <label for="customerName" class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                        <input type="text" id="customerName" name="name" required class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="customerEmail" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" id="customerEmail" name="email" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="customerPhone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                        <input type="text" id="customerPhone" name="phone" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="customerAddress" class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                        <textarea id="customerAddress" name="address" rows="2" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <div>
                        <label for="customerContactPerson" class="block text-sm font-medium text-slate-700 mb-1">Contact Person</label>
                        <input type="text" id="customerContactPerson" name="contact_person" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent">
                    </div>
                    
                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" onclick="closeNewCustomerModal()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                            Add Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Toggle custom interval field based on billing frequency selection
function toggleCustomInterval() {
    const frequency = document.getElementById('billing_frequency').value;
    const customSection = document.getElementById('custom_interval_section');
    
    if (frequency === 'custom') {
        customSection.style.display = 'block';
    } else {
        customSection.style.display = 'none';
    }
}

// Load template data when a template is selected
function loadTemplateData(templateId) {
    if (!templateId) {
        return;
    }
    console.log('Template selected:', templateId);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleCustomInterval();
    
    // Auto-check invoicing company in contributing companies
    const mainCompanySelect = document.getElementById('main_invoicing_company_id');
    if (mainCompanySelect) {
        mainCompanySelect.addEventListener('change', function() {
            const mainCompanyId = this.value;
            const contributingCheckboxes = document.querySelectorAll('input[name="companies[]"]');
            
            contributingCheckboxes.forEach(checkbox => {
                if (checkbox.value === mainCompanyId && mainCompanyId) {
                    checkbox.checked = true;
                }
            });
        });
        
        // Trigger on load
        mainCompanySelect.dispatchEvent(new Event('change'));
    }
});

// New Customer Modal Functions
function openNewCustomerModal() {
    document.getElementById('newCustomerModal').classList.remove('hidden');
}

function closeNewCustomerModal() {
    document.getElementById('newCustomerModal').classList.add('hidden');
    document.getElementById('newCustomerForm').reset();
}

// Handle new customer form submission
document.getElementById('newCustomerForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    fetch('{{ route("customers.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new customer to dropdown
            const customerSelect = document.getElementById('customer_id');
            const newOption = document.createElement('option');
            newOption.value = data.customer.id;
            newOption.textContent = data.customer.name;
            newOption.selected = true;
            customerSelect.appendChild(newOption);
            
            closeNewCustomerModal();
            alert('Customer created successfully!');
        } else {
            alert('Error: ' + (data.message || 'Failed to create customer'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the customer');
    });
});

// Close modal when clicking outside
document.getElementById('newCustomerModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeNewCustomerModal();
    }
});

// Form validation
document.querySelector('form')?.addEventListener('submit', function(e) {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        e.preventDefault();
        alert('End date must be after start date');
        return false;
    }
});

// Toggle recurring settings visibility
function toggleRecurringSettings(isChecked) {
    const settingsDiv = document.getElementById('recurring-settings');
    const masterTemplateDiv = document.getElementById('master-template-mode-container');

    if (settingsDiv) {
        settingsDiv.style.display = isChecked ? 'block' : 'none';
    }

    if (masterTemplateDiv) {
        masterTemplateDiv.style.display = isChecked ? 'block' : 'none';
    }

    // Als recurring wordt uitgezet, clear de velden
    if (!isChecked) {
        document.getElementById('recurring_base_name').value = '';
        document.getElementById('recurring_end_date').value = '';
        document.getElementById('recurring_series_id_select').value = '';
        document.getElementById('recurring_series_id_custom').style.display = 'none';
        document.getElementById('is_master_template').checked = false;
    }
}

// Handle recurring series ID selection
function handleSeriesSelection(value) {
    const customInput = document.getElementById('recurring_series_id_custom');
    const hiddenInput = document.createElement('input');

    if (value === '_custom') {
        // Show custom input field
        customInput.style.display = 'block';
        customInput.focus();
    } else {
        // Hide custom input field
        customInput.style.display = 'none';

        if (value) {
            // Set selected series ID as a hidden input
            customInput.value = value;
        } else {
            // Clear value for auto-generate
            customInput.value = '';
        }
    }
}

// Apply theme styling to buttons
function styleThemeElements() {
    // Header back button
    const backBtn = document.getElementById('header-back-btn');
    if (backBtn) {
        backBtn.style.backgroundColor = '#6b7280';
        backBtn.style.color = 'white';
        backBtn.style.border = 'none';
        backBtn.style.borderRadius = 'var(--theme-border-radius)';
    }
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush