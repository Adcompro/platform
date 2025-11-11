@extends('layouts.app')

@section('title', 'Project Details - ' . $project->name)

@section('content')
<div class="min-h-screen" style="background-color: var(--theme-bg);">
    {{-- Sticky Header --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">{{ $project->name }}</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">
                        @if($project->customer)
                            {{ $project->customer->name }} •
                        @endif
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Time Entries Button --}}
                    <button onclick="openTimeEntriesModal()"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size); background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); border: none; cursor: pointer;">
                        <i class="fas fa-clock mr-1.5"></i>
                        Time Entries
                    </button>

                    {{-- Year Budget Overview Link --}}
                    @if($project->monthly_fee && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('projects.year-budget', $project->id) }}"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size); background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fas fa-calendar-alt mr-1.5"></i>
                        Year Budget
                    </a>
                    @endif

                    {{-- Recurring Series Button (voor niet-recurring projects) --}}
                    @if(!$project->is_recurring && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button onclick="openRecurringSeriesModal()"
                            id="header-recurring-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size); background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent); border: none; cursor: pointer;"
                            title="Add to Recurring Series">
                        <i class="fas fa-layer-group mr-1.5"></i>
                        Recurring Series
                    </button>
                    @endif

                    {{-- Help Button (alleen voor recurring projects) --}}
                    @if($project->is_recurring || $project->parent_recurring_project_id || $project->recurring_series_id)
                    <button onclick="openHelpModal()"
                            id="header-help-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size); background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent); border: none; cursor: pointer;"
                            title="Recurring Projects Guide">
                        <i class="fas fa-question-circle mr-1.5"></i>
                        Help
                    </button>
                    @endif

                    @if($project->customer)
                    <a href="{{ route('customers.show', $project->customer->id) }}"
                       id="header-back-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-arrow-left mr-1.5"></i>
                        Back to Customer
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="padding: 1.5rem 2rem;">

        {{-- Full width layout --}}
        <div class="projects-full-width" style="max-width: 100%;">

            {{-- All cards full width --}}
            <div class="space-y-4">
            <div id="project-info-block" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Project Information</h2>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button onclick="toggleEdit()"
                            id="edit-btn"
                            class="inline-flex items-center px-2 py-1 rounded transition-colors"
                            style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </button>
                    <div id="edit-actions" class="hidden flex items-center gap-2">
                        <button onclick="saveEdit()"
                                class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                style="background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                            <i class="fas fa-check mr-1"></i>
                            Save
                        </button>
                        <button onclick="cancelEdit()"
                                class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                style="background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                            <i class="fas fa-times mr-1"></i>
                            Cancel
                        </button>
                    </div>
                    @endif
                </div>
                <div style="padding: var(--theme-card-padding); min-height: 310px;">
                    <form id="project-form">
                        {{-- Three column grid voor fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8" style="column-gap: 3rem;">
                            {{-- Left column: Basic Info --}}
                            <div>
                                <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Basic Information</h3>
                                <dl>
                                    {{-- Name --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Name:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500; flex: 1;">{{ $project->name }}</dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <input type="text" name="name" value="{{ $project->name }}" required
                                                   class="w-full border border-gray-300 rounded"
                                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                        </dd>
                                    </div>

                                    {{-- Customer --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Customer:</dt>
                                        <dd class="field-view" style="flex: 1;">
                                            @if($project->customer)
                                                <a href="{{ route('customers.show', $project->customer) }}?from=project&project_id={{ $project->id }}"
                                                   style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                    {{ $project->customer->name }}
                                                </a>
                                            @else
                                                <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No customer</span>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <select name="customer_id" class="w-full border border-gray-300 rounded" style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                                <option value="">No customer</option>
                                                @foreach(\App\Models\Customer::where('status', 'active')->orderBy('name')->get() as $customer)
                                                    <option value="{{ $customer->id }}" {{ $project->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        </dd>
                                    </div>

                                    {{-- Companies --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Companies:</dt>
                                        <dd class="field-view" style="flex: 1;">
                                            @if($project->companies && $project->companies->count() > 0)
                                                <div style="display: flex; flex-wrap: wrap; gap: 0.375rem;">
                                                    @foreach($project->companies as $company)
                                                        <a href="{{ route('companies.show', $company) }}"
                                                           class="inline-flex items-center px-2 py-0.5 rounded"
                                                           style="background-color: rgba(var(--theme-primary-rgb), 0.1);
                                                                  color: var(--theme-primary);
                                                                  text-decoration: none;
                                                                  font-size: calc(var(--theme-font-size) - 2px);
                                                                  font-weight: 500;
                                                                  transition: all 0.2s;"
                                                           onmouseover="this.style.backgroundColor='rgba(var(--theme-primary-rgb), 0.2)'"
                                                           onmouseout="this.style.backgroundColor='rgba(var(--theme-primary-rgb), 0.1)'">
                                                            <i class="fas fa-building mr-1" style="font-size: calc(var(--theme-font-size) - 3px);"></i>
                                                            {{ $company->name }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No companies linked</span>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <select name="company_ids[]" multiple class="w-full border border-gray-300 rounded"
                                                    style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.25rem 0.5rem; min-height: 80px;">
                                                @foreach(\App\Models\Company::where('is_active', true)->orderBy('name')->get() as $company)
                                                    <option value="{{ $company->id }}"
                                                            {{ $project->companies->contains($company->id) ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                                Hold Ctrl/Cmd to select multiple companies
                                            </p>
                                        </dd>
                                    </div>

                                    {{-- Project Managers --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Project Manager(s):</dt>
                                        <dd class="field-view" style="flex: 1;">
                                            @php
                                                // Get project managers: users with role=project_manager OR role_override=project_manager
                                                $projectManagers = $project->users()
                                                    ->where(function($q) {
                                                        $q->where('users.role', 'project_manager')
                                                          ->orWhere('project_users.role_override', 'project_manager');
                                                    })
                                                    ->get();
                                            @endphp
                                            @if($projectManagers && $projectManagers->count() > 0)
                                                <div style="display: flex; flex-wrap: wrap; gap: 0.375rem;">
                                                    @foreach($projectManagers as $manager)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded"
                                                              style="background-color: rgba(var(--theme-accent-rgb), 0.1);
                                                                     color: var(--theme-accent);
                                                                     font-size: calc(var(--theme-font-size) - 2px);
                                                                     font-weight: 500;">
                                                            <i class="fas fa-user-tie mr-1" style="font-size: calc(var(--theme-font-size) - 3px);"></i>
                                                            {{ $manager->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No project managers assigned</span>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <select name="project_manager_ids[]" multiple class="w-full border border-gray-300 rounded"
                                                    style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.25rem 0.5rem; min-height: 100px;">
                                                @php
                                                    // Get all active users (any role can be project manager via role_override)
                                                    $allUsers = \App\Models\User::where('is_active', true)
                                                        ->orderBy('name')
                                                        ->get();
                                                    $currentManagerIds = $projectManagers->pluck('id')->toArray();
                                                @endphp
                                                @foreach($allUsers as $user)
                                                    <option value="{{ $user->id }}"
                                                            {{ in_array($user->id, $currentManagerIds) ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                                Hold Ctrl/Cmd to select multiple project managers. Users will get project manager permissions for this project.
                                            </p>
                                        </dd>
                                    </div>

                                    {{-- Recurring Series --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Recurring Series:</dt>
                                        <dd class="field-view" style="flex: 1;">
                                            @if($project->recurring_series_id)
                                                <a href="{{ route('projects.series-budget', $project->id) }}"
                                                   style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                    <i class="fas fa-layer-group mr-1"></i>
                                                    Series #{{ $project->recurring_series_id }}
                                                </a>
                                            @else
                                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                    <button onclick="openRecurringSeriesModal()" type="button"
                                                            class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                                            style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
                                                        <i class="fas fa-plus mr-1"></i>
                                                        Add to Series
                                                    </button>
                                                @else
                                                    <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No series</span>
                                                @endif
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <input type="text" name="recurring_series_id" value="{{ $project->recurring_series_id ?? '' }}" placeholder="Enter series ID or leave empty"
                                                   class="w-full border border-gray-300 rounded"
                                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                            <p style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                                Or use the "Add to Series" button in view mode
                                            </p>
                                        </dd>
                                    </div>

                                    {{-- Status --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Status:</dt>
                                        <dd class="field-view" style="flex: 1;">
                                            <span class="px-2 py-1 rounded-full" style="
                                                font-size: calc(var(--theme-font-size) - 2px); font-weight: 500;
                                                {{ $project->status === 'completed' ? 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);' :
                                                ($project->status === 'in_progress' || $project->status === 'active' ? 'background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);' :
                                                ($project->status === 'on_hold' ? 'background-color: rgba(var(--theme-warning-rgb), 0.1); color: var(--theme-warning);' :
                                                ($project->status === 'draft' ? 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);' : 'background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);'))) }}">
                                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                            </span>
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <select name="status" required class="w-full border border-gray-300 rounded" style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                                <option value="draft" {{ $project->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="on_hold" {{ $project->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                                <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $project->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </dd>
                                    </div>

                                    {{-- Start Date --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Start Date:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); flex: 1;">
                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M j, Y') : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <input type="date" name="start_date" value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}"
                                                   class="w-full border border-gray-300 rounded"
                                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                        </dd>
                                    </div>

                                    {{-- End Date --}}
                                    <div class="flex items-start gap-3">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">End Date:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); flex: 1;">
                                            {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M j, Y') : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <input type="date" name="end_date" value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}"
                                                   class="w-full border border-gray-300 rounded"
                                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Middle column: Description & Notes --}}
                            <div>
                                <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Description & Notes</h3>
                                <dl>
                                    {{-- Description --}}
                                    <div style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Description:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5; white-space: pre-wrap;">{{ $project->description ?: 'No description' }}</dd>
                                        <dd class="field-edit hidden">
                                            <textarea name="description" rows="1" class="w-full border border-gray-300 rounded" style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">{{ $project->description }}</textarea>
                                        </dd>
                                    </div>

                                    {{-- Notes --}}
                                    <div>
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Notes:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5; white-space: pre-wrap; font-style: italic;">{{ $project->notes ?: 'No notes' }}</dd>
                                        <dd class="field-edit hidden">
                                            <textarea name="notes" rows="1" class="w-full border border-gray-300 rounded" style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4; font-style: italic;">{{ $project->notes }}</textarea>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Right column: Financial & Settings --}}
                            <div>
                                <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Financial & Settings</h3>
                                <dl>
                                    {{-- Monthly Fee --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Monthly Fee:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); flex: 1;">
                                            @if($project->monthly_fee)
                                                <span style="font-weight: 500;">€{{ number_format($project->monthly_fee, 2) }}</span>
                                            @else
                                                <em style="color: var(--theme-text-muted);">Not set</em>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <input type="number" step="0.01" name="monthly_fee" value="{{ $project->monthly_fee }}" placeholder="0.00"
                                                   class="w-full border border-gray-300 rounded"
                                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                        </dd>
                                    </div>

                                    {{-- Hourly Rate --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Hourly Rate:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); flex: 1;">
                                            @if($project->default_hourly_rate)
                                                <span style="font-weight: 500;">€{{ number_format($project->default_hourly_rate, 2) }}/hr</span>
                                            @else
                                                <em style="color: var(--theme-text-muted);">Not set</em>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <input type="number" step="0.01" name="default_hourly_rate" value="{{ $project->default_hourly_rate }}" placeholder="0.00"
                                                   class="w-full border border-gray-300 rounded"
                                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                        </dd>
                                    </div>

                                    {{-- Time Costs (calculated, not editable) --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Time Costs:</dt>
                                        <dd style="flex: 1;">
                                            @php
                                                $totalTimeCosts = $project->total_time_costs ?? 0;
                                                $totalLoggedHours = $project->total_logged_hours ?? 0;
                                            @endphp
                                            <div style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                                <span style="font-weight: 600; color: var(--theme-primary);">€{{ number_format($totalTimeCosts, 2) }}</span>
                                            </div>
                                            <div style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted); margin-top: 0.125rem;">
                                                <i class="fas fa-stopwatch" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                                {{ number_format($totalLoggedHours, 2) }}h logged
                                            </div>
                                        </dd>
                                    </div>

                                    {{-- VAT Rate --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">VAT Rate:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); flex: 1;">
                                            {{ $project->vat_rate ? number_format($project->vat_rate, 2) . '%' : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <input type="number" step="0.01" name="vat_rate" value="{{ $project->vat_rate }}" placeholder="21.00"
                                                   class="w-full border border-gray-300 rounded"
                                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                        </dd>
                                    </div>

                                    {{-- Billing Frequency --}}
                                    <div class="flex items-start gap-3" style="margin-bottom: 1rem;">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Billing Freq:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); flex: 1;">
                                            {{ $project->billing_frequency ? ucfirst(str_replace('_', ' ', $project->billing_frequency)) : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <select name="billing_frequency" class="w-full border border-gray-300 rounded" style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                                <option value="">Not set</option>
                                                <option value="monthly" {{ $project->billing_frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="quarterly" {{ $project->billing_frequency == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                <option value="milestone" {{ $project->billing_frequency == 'milestone' ? 'selected' : '' }}>Milestone</option>
                                                <option value="project_completion" {{ $project->billing_frequency == 'project_completion' ? 'selected' : '' }}>Project Completion</option>
                                                <option value="custom" {{ $project->billing_frequency == 'custom' ? 'selected' : '' }}>Custom</option>
                                            </select>
                                        </dd>
                                    </div>

                                    {{-- Fee Rollover --}}
                                    <div class="flex items-start gap-3">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 110px;">Fee Rollover:</dt>
                                        <dd class="field-view" style="flex: 1;">
                                            @if($project->fee_rollover_enabled)
                                                <span style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-success); background-color: rgba(var(--theme-success-rgb), 0.1); padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                                    <i class="fas fa-check-circle mr-1"></i>Enabled
                                                </span>
                                            @else
                                                <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Disabled</span>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="flex: 1;">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="fee_rollover_enabled" value="1" {{ $project->fee_rollover_enabled ? 'checked' : '' }} class="mr-2">
                                                <span style="font-size: calc(var(--theme-font-size) - 1px);">Enable</span>
                                            </label>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Additional Costs Section --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleAdditionalCosts()">
                    <div class="flex items-center gap-4" style="flex: 1;">
                        <div class="flex items-center" style="min-width: 280px;">
                            <i id="additional-costs-chevron" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                            <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                <i class="fas fa-receipt mr-2"></i>
                                Additional Costs
                            </h2>
                        </div>
                        @php
                            $totalCosts = $project->additionalCosts->count();
                            $totalAmount = $project->additionalCosts->sum(function($cost) {
                                return $cost->calculateAmount();
                            });
                        @endphp
                        <div id="additional-costs-summary" class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                            <span style="color: var(--theme-text-muted); min-width: 80px;">
                                {{ $totalCosts }} {{ $totalCosts === 1 ? 'item' : 'items' }}
                            </span>
                            <span style="color: var(--theme-text); font-weight: 600; min-width: 120px;">
                                €{{ number_format($totalAmount, 2) }}
                            </span>
                        </div>
                    </div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button id="additional-costs-action-btn" onclick="event.stopPropagation(); openCreateCostModal();"
                       class="inline-flex items-center px-3 py-1.5 rounded transition-colors hidden"
                       style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
                        <i class="fas fa-plus mr-1.5"></i>
                        Add Cost
                    </button>
                    @endif
                </div>

                <div id="additional-costs-content" class="hidden" style="padding: var(--theme-card-padding);">
                    @if($project->additionalCosts->count() > 0)
                        {{-- Within Budget (in_fee) Costs --}}
                        @if($project->additionalCosts->where('fee_type', 'in_fee')->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium mb-3" style="color: #10b981;">
                                💚 Within Budget (counts toward monthly fee)
                            </h3>
                            <div class="space-y-2">
                                @foreach($project->additionalCosts->where('fee_type', 'in_fee') as $cost)
                                <div class="border-l-4 rounded" style="border-color: #10b981; background-color: #f0fdf4; padding: 0.75rem 1rem;">
                                    <div style="display: grid; grid-template-columns: 2fr 2fr 1.5fr 1fr 1fr auto; gap: 1.5rem; align-items: center;">
                                        {{-- Name --}}
                                        <div>
                                            <div class="font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $cost->name }}</div>
                                        </div>

                                        {{-- Description --}}
                                        <div>
                                            <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.4;">
                                                {{ $cost->description ?: '—' }}
                                            </div>
                                        </div>

                                        {{-- Type Badge --}}
                                        <div>
                                            @if($cost->cost_type === 'monthly_recurring')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: #e0e7ff; color: #4338ca;">
                                                    <i class="fas fa-sync-alt mr-1"></i> Monthly
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: #f3e8ff; color: #7c3aed;">
                                                    <i class="fas fa-bolt mr-1"></i> {{ $cost->start_date->format('M Y') }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Calculation --}}
                                        <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                            @if($cost->calculation_type === 'hourly_rate')
                                                {{ $cost->hours }}h × €{{ number_format($cost->hourly_rate, 2) }}
                                            @elseif($cost->calculation_type === 'quantity_based')
                                                {{ $cost->quantity }} × €{{ number_format($cost->amount, 2) }}
                                            @else
                                                Fixed
                                            @endif
                                        </div>

                                        {{-- Amount --}}
                                        <div class="text-right">
                                            <div class="font-semibold" style="color: #10b981; font-size: calc(var(--theme-font-size) + 2px);">
                                                €{{ number_format($cost->calculateAmount(), 2) }}
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        <div>
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <div class="flex items-center gap-1">
                                                <button onclick="openViewCostModal({{ $cost->id }})"
                                                        class="p-1 rounded hover:bg-slate-100 transition-colors"
                                                        style="color: #64748b;"
                                                        title="View">
                                                    <i class="fas fa-eye" style="font-size: 11px;"></i>
                                                </button>
                                                <button onclick="openEditCostModal({{ $cost->id }})"
                                                        class="p-1 rounded hover:bg-slate-100 transition-colors"
                                                        style="color: #64748b;"
                                                        title="Edit">
                                                    <i class="fas fa-edit" style="font-size: 11px;"></i>
                                                </button>
                                                <button onclick="deleteCost({{ $cost->id }})"
                                                        class="p-1 rounded hover:bg-slate-100 transition-colors"
                                                        style="color: #64748b;"
                                                        title="Delete">
                                                    <i class="fas fa-trash" style="font-size: 11px;"></i>
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Additional Billing Costs --}}
                        @if($project->additionalCosts->where('fee_type', 'additional')->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium mb-3" style="color: #f59e0b;">
                                💰 Additional Billing (billed separately)
                            </h3>
                            <div class="space-y-2">
                                @foreach($project->additionalCosts->where('fee_type', 'additional') as $cost)
                                <div class="border-l-4 rounded" style="border-color: #f59e0b; background-color: #fffbeb; padding: 0.75rem 1rem;">
                                    <div style="display: grid; grid-template-columns: 2fr 2fr 1.5fr 1fr 1fr auto; gap: 1.5rem; align-items: center;">
                                        {{-- Name --}}
                                        <div>
                                            <div class="font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $cost->name }}</div>
                                        </div>

                                        {{-- Description --}}
                                        <div>
                                            <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.4;">
                                                {{ $cost->description ?: '—' }}
                                            </div>
                                        </div>

                                        {{-- Type Badge --}}
                                        <div>
                                            @if($cost->cost_type === 'monthly_recurring')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: #e0e7ff; color: #4338ca;">
                                                    <i class="fas fa-sync-alt mr-1"></i> Monthly
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: #f3e8ff; color: #7c3aed;">
                                                    <i class="fas fa-bolt mr-1"></i> {{ $cost->start_date->format('M Y') }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Calculation --}}
                                        <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                            @if($cost->calculation_type === 'hourly_rate')
                                                {{ $cost->hours }}h × €{{ number_format($cost->hourly_rate, 2) }}
                                            @elseif($cost->calculation_type === 'quantity_based')
                                                {{ $cost->quantity }} × €{{ number_format($cost->amount, 2) }}
                                            @else
                                                Fixed
                                            @endif
                                        </div>

                                        {{-- Amount --}}
                                        <div class="text-right">
                                            <div class="font-semibold" style="color: #f59e0b; font-size: calc(var(--theme-font-size) + 2px);">
                                                €{{ number_format($cost->calculateAmount(), 2) }}
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        <div>
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <div class="flex items-center gap-1">
                                                <button onclick="openViewCostModal({{ $cost->id }})"
                                                        class="p-1 rounded hover:bg-slate-100 transition-colors"
                                                        style="color: #64748b;"
                                                        title="View">
                                                    <i class="fas fa-eye" style="font-size: 11px;"></i>
                                                </button>
                                                <button onclick="openEditCostModal({{ $cost->id }})"
                                                        class="p-1 rounded hover:bg-slate-100 transition-colors"
                                                        style="color: #64748b;"
                                                        title="Edit">
                                                    <i class="fas fa-edit" style="font-size: 11px;"></i>
                                                </button>
                                                <button onclick="deleteCost({{ $cost->id }})"
                                                        class="p-1 rounded hover:bg-slate-100 transition-colors"
                                                        style="color: #64748b;"
                                                        title="Delete">
                                                    <i class="fas fa-trash" style="font-size: 11px;"></i>
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-8" style="color: var(--theme-text-muted);">
                            <i class="fas fa-receipt fa-3x mb-3" style="opacity: 0.3;"></i>
                            <p class="font-medium">No additional costs yet</p>
                            <p class="text-sm">Add costs like hosting, software licenses, or extra services</p>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <button onclick="openCreateCostModal()"
                               class="inline-flex items-center px-4 py-2 mt-4 rounded"
                               style="background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); border: none; cursor: pointer;">
                                <i class="fas fa-plus mr-2"></i>
                                Add First Cost
                            </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

                {{-- Project Structure: Milestones & Tasks --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleProjectStructure()">
                        <div class="flex items-center gap-4" style="flex: 1;">
                            <div class="flex items-center" style="min-width: 280px;">
                                <i id="project-structure-chevron" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                    <i class="fas fa-sitemap mr-2"></i>
                                    Project Structure
                                </h2>
                            </div>
                            @php
                                // Bereken totaal aantal gelogde uren (alleen approved)
                                $totalHours = $project->timeEntries()
                                    ->where('status', 'approved')
                                    ->get()
                                    ->sum(function($entry) {
                                        return $entry->hours + ($entry->minutes / 60);
                                    });
                            @endphp
                            <div id="project-structure-summary" class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                                <span style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                                    {{ number_format($totalHours, 1) }} hours logged
                                </span>
                            </div>
                        </div>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a id="project-structure-action-btn" href="{{ route('projects.edit', $project) }}"
                           onclick="event.stopPropagation();"
                           class="inline-flex items-center px-2 py-1 rounded transition-colors hidden"
                           style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </a>
                        @endif
                    </div>
                    <div id="project-structure-content" class="hidden" style="padding: var(--theme-card-padding);">
                        @if($project->milestones->count() > 0)
                            <div class="space-y-4">
                                @foreach($project->milestones as $milestone)
                                <div class="border rounded-lg" style="border-color: rgba(203, 213, 225, 0.4);">
                                    {{-- Milestone Header - Compacter --}}
                                    <div class="px-3 py-2" style="background-color: rgba(var(--theme-primary-rgb), 0.03); border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-flag" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                                <span style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">{{ $milestone->name }}</span>
                                                <span class="px-2 py-0.5 rounded text-xs" style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 3px);">
                                                    {{ ucfirst($milestone->status) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                @php
                                                    $milestoneLoggedHours = $milestone->total_logged_hours;
                                                    $milestoneLoggedFormatted = $milestone->formatted_logged_hours;
                                                    $milestoneEstimatedHours = $milestone->estimated_hours ?? 0;
                                                    $milestoneIsOver = $milestoneEstimatedHours > 0 && $milestoneLoggedHours > $milestoneEstimatedHours;
                                                    $milestoneIsNear = $milestoneEstimatedHours > 0 && $milestoneLoggedHours >= ($milestoneEstimatedHours * 0.8) && !$milestoneIsOver;
                                                    $milestoneColor = $milestoneIsOver ? '#ef4444' : ($milestoneIsNear ? '#f59e0b' : '#10b981');
                                                @endphp
                                                @if($milestoneLoggedHours > 0 || $milestoneEstimatedHours > 0)
                                                <div class="flex items-center gap-2" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                    @if($milestoneEstimatedHours > 0)
                                                    <span style="color: var(--theme-text-muted);">
                                                        <i class="fas fa-clock mr-1"></i>{{ $milestoneEstimatedHours }}h est.
                                                    </span>
                                                    @endif
                                                    @if($milestoneLoggedHours > 0)
                                                    <span onclick="openTaskTimeEntriesModal('milestone', {{ $milestone->id }})"
                                                          style="color: {{ $milestoneColor }}; font-weight: 600; cursor: pointer; text-decoration: underline; text-decoration-style: dotted;"
                                                          title="Click to view time entries">
                                                        <i class="fas fa-stopwatch mr-1"></i>{{ $milestoneLoggedFormatted }} logged
                                                    </span>
                                                    @endif
                                                </div>
                                                @endif
                                                @if($milestone->tasks->count() > 0)
                                                <span style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                                    {{ $milestone->tasks->count() }} {{ $milestone->tasks->count() === 1 ? 'task' : 'tasks' }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($milestone->description)
                                        <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.375rem; line-height: 1.4;">
                                            {{ $milestone->description }}
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Tasks - Compacter --}}
                                    @if($milestone->tasks->count() > 0)
                                    <div class="px-3 py-2">
                                        <div class="space-y-1.5">
                                            @foreach($milestone->tasks as $task)
                                            <div class="flex items-start gap-2 px-2 py-1.5 rounded" style="background-color: rgba(248, 250, 252, 0.5);">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <i class="fas {{ $task->status === 'completed' ? 'fa-check-circle' : 'fa-circle' }}"
                                                       style="color: {{ $task->status === 'completed' ? 'var(--theme-success)' : 'var(--theme-text-muted)' }}; font-size: calc(var(--theme-font-size) - 2px);"></i>
                                                </div>
                                                <div class="flex-1" style="min-width: 0;">
                                                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text); {{ $task->status === 'completed' ? 'text-decoration: line-through; opacity: 0.7;' : '' }}">
                                                        {{ $task->name }}
                                                    </div>
                                                    @if($task->description)
                                                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; line-height: 1.4; word-wrap: break-word; overflow-wrap: break-word;">
                                                        {{ $task->description }}
                                                    </div>
                                                    @endif
                                                    <div class="flex items-center gap-2.5 mt-1 flex-wrap" style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted);">
                                                        @if($task->estimated_hours)
                                                        <span><i class="fas fa-clock mr-1"></i>{{ $task->estimated_hours }}h est.</span>
                                                        @endif
                                                        @php
                                                            $loggedHours = $task->total_logged_hours;
                                                            $loggedFormatted = $task->formatted_logged_hours;
                                                            $hasEstimate = $task->estimated_hours > 0;
                                                            $isOverBudget = $hasEstimate && $loggedHours > $task->estimated_hours;
                                                            $isNearBudget = $hasEstimate && $loggedHours >= ($task->estimated_hours * 0.8) && !$isOverBudget;
                                                            $loggedColor = $isOverBudget ? '#ef4444' : ($isNearBudget ? '#f59e0b' : '#10b981');
                                                        @endphp
                                                        @if($loggedHours > 0)
                                                        <span onclick="openTaskTimeEntriesModal('task', {{ $task->id }})"
                                                              style="color: {{ $loggedColor }}; font-weight: 600; cursor: pointer; text-decoration: underline; text-decoration-style: dotted;"
                                                              title="Click to view time entries">
                                                            <i class="fas fa-stopwatch mr-1"></i>{{ $loggedFormatted }} logged
                                                        </span>
                                                        @endif
                                                        @if($task->pricing_type === 'fixed_price' && $task->fixed_price)
                                                        <span><i class="fas fa-euro-sign mr-1"></i>€{{ number_format($task->fixed_price, 2) }}</span>
                                                        @endif
                                                        <span class="px-1.5 py-0.5 rounded" style="background-color: rgba(var(--theme-text-muted-rgb), 0.1); font-size: calc(var(--theme-font-size) - 3px);">
                                                            {{ ucfirst($task->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div class="px-3 py-2 text-center" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                        No tasks in this milestone
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                        <div class="text-center py-8" style="color: var(--theme-text-muted);">
                            <i class="fas fa-tasks" style="font-size: calc(var(--theme-font-size) + 12px); margin-bottom: 0.5rem;"></i>
                            <p style="font-size: var(--theme-font-size);">No milestones or tasks defined yet</p>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <a href="{{ route('projects.edit', $project) }}" class="inline-block mt-3 px-4 py-2 rounded" style="background-color: var(--theme-primary); color: white; font-size: calc(var(--theme-font-size) - 1px); text-decoration: none;">
                                Add Milestones & Tasks
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Team Members --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleTeamMembers()">
                        <div class="flex items-center gap-4" style="flex: 1;">
                            <div class="flex items-center" style="min-width: 280px;">
                                <i id="team-members-chevron" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                    <i class="fas fa-users mr-2"></i>
                                    Team Members
                                </h2>
                            </div>
                            @php
                                $totalMembers = $project->users->count();
                            @endphp
                            <div id="team-members-summary" class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                                <span style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                                    {{ $totalMembers }} {{ $totalMembers === 1 ? 'member' : 'members' }}
                                </span>
                            </div>
                        </div>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <button id="team-members-action-btn" onclick="event.stopPropagation(); openTeamModal();"
                           class="inline-flex items-center px-2 py-1 rounded transition-colors hidden"
                           style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
                            <i class="fas fa-edit mr-1"></i>
                            Manage
                        </button>
                        @endif
                    </div>
                    <div id="team-members-content" class="hidden" style="padding: var(--theme-card-padding);">
                        @if($project->users->count() > 0)
                            <div class="space-y-2">
                                @foreach($project->users as $user)
                                <div class="flex items-center justify-between p-3 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.1);">
                                    <div>
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $user->name }}</span>
                                        @if($user->company)
                                            <span style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-left: 0.5rem;">({{ $user->company->name }})</span>
                                        @endif
                                    </div>
                                    <span class="px-2 py-1 rounded" style="font-size: calc(var(--theme-font-size) - 2px); background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8" style="color: var(--theme-text-muted);">
                                <i class="fas fa-users" style="font-size: calc(var(--theme-font-size) + 12px); margin-bottom: 0.5rem;"></i>
                                <p style="font-size: var(--theme-font-size);">No team members assigned yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recurring Project Information Card --}}
                @if($project->is_recurring || $project->parent_recurring_project_id)
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60" style="border-radius: var(--theme-border-radius); overflow: hidden;">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleRecurringSettings()">
                        <div class="flex items-center gap-4" style="flex: 1;">
                            <div class="flex items-center" style="min-width: 280px;">
                                <i id="recurring-settings-chevron" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                    @if($project->is_recurring)
                                    <i class="fas fa-sync-alt mr-2" style="color: var(--theme-accent);"></i>
                                    Recurring Settings
                                    @else
                                    <i class="fas fa-robot mr-2" style="color: var(--theme-primary);"></i>
                                    Auto-Generated
                                    @endif
                                </h2>
                            </div>
                            @php
                                if ($project->is_recurring) {
                                    $childProjects = \App\Models\Project::where('parent_recurring_project_id', $project->id)->count();
                                    $frequency = ucfirst($project->recurring_frequency ?? 'monthly');
                                } else {
                                    $parentProject = \App\Models\Project::find($project->parent_recurring_project_id);
                                }
                            @endphp
                            <div id="recurring-settings-summary" class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                                @if($project->is_recurring)
                                    <span style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                                        {{ $frequency }} • {{ $childProjects }} generated
                                    </span>
                                @else
                                    @if(isset($parentProject))
                                    <span style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                                        From: {{ $parentProject->name }}
                                    </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @if($project->is_recurring && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <button id="recurring-settings-action-btn" onclick="event.stopPropagation(); openRecurringSettingsModal();"
                                class="inline-flex items-center transition-colors hidden"
                                style="padding: 0.25rem 0.5rem; background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; border-radius: var(--theme-border-radius); cursor: pointer;">
                            <i class="fas fa-edit mr-1"></i>
                            Edit Settings
                        </button>
                        @endif
                    </div>
                    <div id="recurring-settings-content" class="hidden" style="padding: var(--theme-card-padding);">
                        @if($project->is_recurring)
                            {{-- Master Recurring Project Info --}}
                            <div class="space-y-3">
                                <div style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle" style="color: var(--theme-accent);"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                                                <strong>Master Recurring Project:</strong> This project automatically generates new projects {{ $project->recurring_frequency === 'monthly' ? 'every month' : 'every quarter' }}.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <dl class="space-y-2">
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Base Name:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500;">
                                            {{ $project->recurring_base_name ?? 'Not set' }}
                                        </dd>
                                    </div>

                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Frequency:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            {{ ucfirst($project->recurring_frequency ?? 'monthly') }}
                                        </dd>
                                    </div>

                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Create Days Before:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            {{ $project->recurring_days_before ?? 7 }} days
                                        </dd>
                                    </div>

                                    @if($project->recurring_end_date)
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Ends On:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            {{ $project->recurring_end_date->format('d M Y') }}
                                        </dd>
                                    </div>
                                    @else
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Ends On:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                                            Continues indefinitely
                                        </dd>
                                    </div>
                                    @endif
                                </dl>

                                {{-- Generated Projects List --}}
                                @php
                                    $childProjects = \App\Models\Project::where('parent_recurring_project_id', $project->id)
                                        ->orderBy('start_date', 'desc')
                                        ->get();
                                @endphp

                                @if($childProjects->count() > 0)
                                <div class="mt-4 pt-4 border-t" style="border-color: rgba(203, 213, 225, 0.3);">
                                    <h3 style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                                        Generated Projects ({{ $childProjects->count() }})
                                    </h3>
                                    <div class="space-y-2">
                                        @foreach($childProjects as $child)
                                        <div class="flex items-center justify-between p-2 rounded hover:style="background-color: rgba(var(--theme-bg-rgb), 0.5);"">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-robot text-blue-400" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                                <a href="{{ route('projects.show', $child) }}" style="color: var(--theme-primary); text-decoration: none; font-size: calc(var(--theme-font-size) - 1px);">
                                                    {{ $child->name }}
                                                </a>
                                            </div>
                                            <span class="px-2 py-0.5 rounded-full" style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 500; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                                                {{ $child->status }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        @else
                            {{-- Auto-Generated Child Project Info --}}
                            @php
                                $parentProject = \App\Models\Project::find($project->parent_recurring_project_id);
                            @endphp

                            @if($parentProject)
                            <div class="space-y-3">
                                <div style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-info-circle" style="color: var(--theme-primary);"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                                                <strong>Auto-Generated:</strong> This project was automatically created from a recurring master project.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <dl class="space-y-2">
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Master Project:</dt>
                                        <dd style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <a href="{{ route('projects.show', $parentProject) }}" style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                {{ $parentProject->name }}
                                            </a>
                                        </dd>
                                    </div>

                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Period:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            {{ $project->recurring_period ?? 'Not set' }}
                                        </dd>
                                    </div>

                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Created From:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            {{ ucfirst($parentProject->recurring_frequency ?? 'monthly') }} recurring template
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
                @endif

                {{-- Project Activity Timeline --}}
                @include('projects.partials.activity-timeline', ['activities' => $activities])

                {{-- Add to Recurring Series - Nu een popup modal ipv statische sectie --}}
            </div>
    </div>
</div>

{{-- Time Entries Modal --}}
<div id="timeEntriesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="if(event.target === this) closeTimeEntriesModal()">
    <div class="bg-white shadow-2xl" style="width: 95%; max-width: 1200px; max-height: 85vh; overflow-y: auto; border-radius: var(--theme-border-radius);">
        {{-- Modal Header --}}
        <div class="border-b sticky top-0 bg-white z-10" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 600; color: var(--theme-text); margin: 0;">Time Entries for {{ $project->name }}</h3>
                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;" id="time-entries-subtitle">Loading...</p>
            </div>
            <button onclick="closeTimeEntriesModal()" class="text-gray-400 hover:text-gray-600" style="background: none; border: none; cursor: pointer; font-size: calc(var(--theme-font-size) + 8px);">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Statistics Cards --}}
        <div id="time-entries-stats" class="p-4 border-b" style="border-color: rgba(203, 213, 225, 0.3); display: none;">
            <div class="grid grid-cols-4 gap-4">
                <div class="p-3 rounded-lg" style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.1);">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.25rem;">Total Entries</div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 600; color: var(--theme-primary);" id="stat-total-entries">-</div>
                </div>
                <div class="p-3 rounded-lg" style="background-color: rgba(var(--theme-info-rgb), 0.05); border: 1px solid rgba(var(--theme-info-rgb), 0.1);">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.25rem;">This Month</div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 600; color: var(--theme-info);" id="stat-current-month-entries">-</div>
                </div>
                <div class="p-3 rounded-lg" style="background-color: #dbeafe; border: 1px solid #93c5fd;">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.25rem;">From Previous</div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 600; color: #2563eb;" id="stat-deferred-entries">-</div>
                </div>
                <div class="p-3 rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.05); border: 1px solid rgba(var(--theme-success-rgb), 0.1);">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.25rem;">Total Hours</div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 600; color: var(--theme-success);" id="stat-total-hours">-</div>
                </div>
            </div>
        </div>

        {{-- Modal Body - Table --}}
        <div style="padding: 1.5rem;">
            <div id="time-entries-loading" class="text-center py-12">
                <i class="fas fa-spinner fa-spin" style="font-size: calc(var(--theme-font-size) + 16px); color: var(--theme-primary); margin-bottom: 1rem;"></i>
                <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Loading time entries...</p>
            </div>

            <div id="time-entries-content" class="hidden">
                <div class="overflow-x-auto">
                    <table class="w-full" style="border-collapse: separate; border-spacing: 0;">
                        <thead>
                            <tr style="background-color: rgba(248, 250, 252, 0.8); border-bottom: 2px solid rgba(203, 213, 225, 0.5);">
                                <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Date</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">User</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Work Item</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Description</th>
                                <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Duration</th>
                                <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Billable</th>
                                <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Deferred</th>
                                <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="time-entries-table-body">
                            {{-- Populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>

                <div id="time-entries-empty" class="hidden text-center py-12">
                    <i class="fas fa-clock" style="font-size: calc(var(--theme-font-size) + 16px); color: var(--theme-text-muted); margin-bottom: 1rem;"></i>
                    <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No time entries found for this project</p>
                </div>
            </div>
        </div>

        {{-- User Statistics Section --}}
        <div id="time-entries-user-stats" class="hidden border-t" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem; background-color: rgba(var(--theme-primary-rgb), 0.02);">
            <div style="margin-bottom: 1rem;">
                <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                    <i class="fas fa-users mr-2"></i>Hours per User
                </h4>
                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin: 0;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Showing only hours logged in this month (deferred entries from previous months are not included)
                </p>
            </div>
            <div id="user-stats-list" class="space-y-2">
                {{-- Populated by JavaScript --}}
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="border-t" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('time-entries.index') }}?project_id={{ $project->id }}&return_to_project={{ $project->id }}"
               class="px-4 py-2 rounded-lg transition-colors"
               style="background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text); font-size: var(--theme-font-size); font-weight: 500; text-decoration: none; border: 1px solid rgba(var(--theme-text-muted-rgb), 0.2);">
                <i class="fas fa-list mr-2"></i>
                View Full List
            </a>
            <button onclick="closeTimeEntriesModal()"
                    class="px-4 py-2 rounded-lg transition-colors"
                    style="background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); font-weight: 500; border: none; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Task/Milestone Time Entries Modal --}}
<div id="taskTimeEntriesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="if(event.target === this) closeTaskTimeEntriesModal()">
    <div class="bg-white shadow-2xl" style="width: 95%; max-width: 900px; max-height: 85vh; overflow-y: auto; border-radius: var(--theme-border-radius);">
        {{-- Modal Header --}}
        <div class="border-b sticky top-0 bg-white z-10" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 id="task-time-entries-title" style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 600; color: var(--theme-text); margin: 0;">Time Entries</h3>
                <p id="task-time-entries-subtitle" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin: 0.25rem 0 0 0;"></p>
            </div>
            <button onclick="closeTaskTimeEntriesModal()" class="text-gray-400 hover:text-gray-600" style="background: none; border: none; cursor: pointer; font-size: calc(var(--theme-font-size) + 8px);">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Statistics --}}
        <div id="task-time-entries-stats" class="px-6 py-4 border-b" style="border-color: rgba(203, 213, 225, 0.3); display: none;">
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);" id="task-stat-total-entries">0</div>
                    <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Entries</div>
                </div>
                <div class="text-center">
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);" id="task-stat-total-hours">0h</div>
                    <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Hours</div>
                </div>
                <div class="text-center">
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-text);" id="task-stat-total-duration">0:00</div>
                    <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Duration</div>
                </div>
            </div>
        </div>

        {{-- Loading State --}}
        <div id="task-time-entries-loading" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size); margin-top: 1rem;">Loading time entries...</p>
        </div>

        {{-- Empty State --}}
        <div id="task-time-entries-empty" class="hidden p-8 text-center">
            <div style="font-size: calc(var(--theme-font-size) + 20px); color: var(--theme-text-muted); margin-bottom: 1rem;">
                <i class="fas fa-clock"></i>
            </div>
            <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No time entries found for this item.</p>
        </div>

        {{-- Content --}}
        <div id="task-time-entries-content" class="hidden" style="padding: 1.5rem;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(226, 232, 240, 0.8);">
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Date</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">User</th>
                            <th id="task-column-header" style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Task</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Description</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Duration</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Billable</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="task-time-entries-table-body">
                        <!-- Entries will be inserted here via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t" style="border-color: rgba(203, 213, 225, 0.3); display: flex; justify-content: flex-end;">
            <button onclick="closeTaskTimeEntriesModal()"
                    class="px-4 py-2 rounded-lg transition-colors"
                    style="background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); font-weight: 500; border: none; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Team Management Modal --}}
<div id="teamModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="if(event.target === this) closeTeamModal()">
    <div class="bg-white shadow-2xl" style="width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; border-radius: var(--theme-border-radius);">
        {{-- Modal Header --}}
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: white; z-index: 10;">
            <h3 style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 600; color: var(--theme-text); margin: 0;">Manage Team Members</h3>
            <button onclick="closeTeamModal()" class="text-gray-400 hover:text-gray-600" style="background: none; border: none; cursor: pointer; font-size: calc(var(--theme-font-size) + 8px);">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div style="padding: 1.5rem;">
            {{-- Current Team Members --}}
            <div class="mb-6">
                <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">Current Team Members</h4>
                <div id="current-team-members" class="space-y-2">
                    {{-- Populated by JavaScript --}}
                </div>
            </div>

            {{-- Add New Member --}}
            <div>
                <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">Add Team Member</h4>
                <div class="flex gap-2">
                    <select id="available-users" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" style="font-size: var(--theme-font-size);">
                        <option value="">Select a user to add...</option>
                    </select>
                    <button onclick="addTeamMember()" class="px-4 py-2 rounded-lg transition-colors" style="background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); font-weight: 500; border: none; cursor: pointer;">
                        <i class="fas fa-plus mr-1"></i>
                        Add
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="border-t" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button onclick="closeTeamModal(); location.reload();" class="px-4 py-2 rounded-lg transition-colors" style="background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); font-weight: 500; border: none; cursor: pointer;">
                Done
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let isEditing = false;

// Toggle Additional Costs section
function toggleAdditionalCosts() {
    const content = document.getElementById('additional-costs-content');
    const chevron = document.getElementById('additional-costs-chevron');
    const summary = document.getElementById('additional-costs-summary');
    const actionBtn = document.getElementById('additional-costs-action-btn');

    if (content.classList.contains('hidden')) {
        // Uitklappen - verberg summary, toon button
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        if (summary) summary.classList.add('hidden');
        if (actionBtn) actionBtn.classList.remove('hidden');
    } else {
        // Inklappen - toon summary, verberg button
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        if (summary) summary.classList.remove('hidden');
        if (actionBtn) actionBtn.classList.add('hidden');
    }
}

// Toggle Project Structure section
function toggleProjectStructure() {
    const content = document.getElementById('project-structure-content');
    const chevron = document.getElementById('project-structure-chevron');
    const summary = document.getElementById('project-structure-summary');
    const actionBtn = document.getElementById('project-structure-action-btn');

    if (content.classList.contains('hidden')) {
        // Uitklappen - verberg summary, toon button
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        if (summary) summary.classList.add('hidden');
        if (actionBtn) actionBtn.classList.remove('hidden');
    } else {
        // Inklappen - toon summary, verberg button
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        if (summary) summary.classList.remove('hidden');
        if (actionBtn) actionBtn.classList.add('hidden');
    }
}

// Toggle Companies section
function toggleCompanies() {
    const content = document.getElementById('companies-content');
    const chevron = document.getElementById('companies-chevron');
    const summary = document.getElementById('companies-summary');
    const actionBtn = document.getElementById('companies-action-btn');

    if (content.classList.contains('hidden')) {
        // Uitklappen - verberg summary, toon button
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        if (summary) summary.classList.add('hidden');
        if (actionBtn) actionBtn.classList.remove('hidden');
    } else {
        // Inklappen - toon summary, verberg button
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        if (summary) summary.classList.remove('hidden');
        if (actionBtn) actionBtn.classList.add('hidden');
    }
}

// Toggle Team Members section
function toggleTeamMembers() {
    const content = document.getElementById('team-members-content');
    const chevron = document.getElementById('team-members-chevron');
    const summary = document.getElementById('team-members-summary');
    const actionBtn = document.getElementById('team-members-action-btn');

    if (content.classList.contains('hidden')) {
        // Uitklappen - verberg summary, toon button
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        if (summary) summary.classList.add('hidden');
        if (actionBtn) actionBtn.classList.remove('hidden');
    } else {
        // Inklappen - toon summary, verberg button
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        if (summary) summary.classList.remove('hidden');
        if (actionBtn) actionBtn.classList.add('hidden');
    }
}

// Toggle Recurring Settings section
function toggleRecurringSettings() {
    const content = document.getElementById('recurring-settings-content');
    const chevron = document.getElementById('recurring-settings-chevron');
    const summary = document.getElementById('recurring-settings-summary');
    const actionBtn = document.getElementById('recurring-settings-action-btn');

    if (content.classList.contains('hidden')) {
        // Uitklappen - verberg summary, toon button
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        if (summary) summary.classList.add('hidden');
        if (actionBtn) actionBtn.classList.remove('hidden');
    } else {
        // Inklappen - toon summary, verberg button
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        if (summary) summary.classList.remove('hidden');
        if (actionBtn) actionBtn.classList.add('hidden');
    }
}

// Toggle Project Activity section
function toggleProjectActivity() {
    const content = document.getElementById('project-activity-content');
    const chevron = document.getElementById('project-activity-chevron');
    const summary = document.getElementById('project-activity-summary');

    if (content.classList.contains('hidden')) {
        // Uitklappen - verberg summary (geen action button bij Activity)
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        if (summary) summary.classList.add('hidden');
    } else {
        // Inklappen - toon summary
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        if (summary) summary.classList.remove('hidden');
    }
}

function toggleEdit() {
    isEditing = true;
    document.getElementById('edit-btn').classList.add('hidden');
    document.getElementById('edit-actions').classList.remove('hidden');

    // Hide all field-view, show all field-edit
    document.querySelectorAll('.field-view').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.field-edit').forEach(el => el.classList.remove('hidden'));
}

function cancelEdit() {
    isEditing = false;
    document.getElementById('edit-btn').classList.remove('hidden');
    document.getElementById('edit-actions').classList.add('hidden');

    // Show all field-view, hide all field-edit
    document.querySelectorAll('.field-view').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('.field-edit').forEach(el => el.classList.add('hidden'));

    // Reset form
    document.getElementById('project-form').reset();
    location.reload();
}

function saveEdit() {
    const form = document.getElementById('project-form');
    const formData = new FormData(form);

    // Convert to JSON
    const data = {};
    formData.forEach((value, key) => {
        if (value !== '') {
            // Handle array fields (like company_ids[])
            if (key.endsWith('[]')) {
                const arrayKey = key.slice(0, -2); // Remove []
                if (!data[arrayKey]) {
                    data[arrayKey] = [];
                }
                data[arrayKey].push(value);
            } else {
                data[key] = value;
            }
        }
    });

    // Handle checkbox separately (it won't be in formData if unchecked)
    const feeRolloverCheckbox = form.querySelector('input[name="fee_rollover_enabled"]');
    if (feeRolloverCheckbox) {
        data.fee_rollover_enabled = feeRolloverCheckbox.checked ? 1 : 0;
    }

    // Handle companies multi-select (get all selected values)
    const companiesSelect = form.querySelector('select[name="company_ids[]"]');
    if (companiesSelect) {
        data.company_ids = Array.from(companiesSelect.selectedOptions).map(option => option.value);
    }

    // Show loading
    const saveBtn = event.target;
    const originalHTML = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
    saveBtn.disabled = true;

    fetch('{{ route("projects.update-basic-info", $project) }}', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message || 'Changes saved successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorMessage(data.message || 'Failed to save changes');
            saveBtn.innerHTML = originalHTML;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An error occurred while saving');
        saveBtn.innerHTML = originalHTML;
        saveBtn.disabled = false;
    });
}

function showSuccessMessage(message) {
    const msg = document.createElement('div');
    msg.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-[9999] transition-all duration-300';
    msg.style.cssText = `
        background-color: var(--theme-success);
        color: white;
        font-size: var(--theme-font-size);
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    `;
    msg.textContent = message;
    document.body.appendChild(msg);

    setTimeout(() => {
        msg.style.transform = 'translateX(0)';
        msg.style.opacity = '1';
    }, 10);

    setTimeout(() => {
        msg.style.transform = 'translateX(100%)';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 300);
    }, 3000);
}

function showErrorMessage(message) {
    const msg = document.createElement('div');
    msg.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-[9999] transition-all duration-300';
    msg.style.cssText = `
        background-color: var(--theme-danger);
        color: white;
        font-size: var(--theme-font-size);
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transform: translateX(100%);
        opacity: 0;
    `;
    msg.textContent = message;
    document.body.appendChild(msg);

    setTimeout(() => {
        msg.style.transform = 'translateX(0)';
        msg.style.opacity = '1';
    }, 10);

    setTimeout(() => {
        msg.style.transform = 'translateX(100%)';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 300);
    }, 4000);
}

// Team Modal Functions
function openTeamModal() {
    document.getElementById('teamModal').classList.remove('hidden');
    loadTeamData();
}

function closeTeamModal() {
    document.getElementById('teamModal').classList.add('hidden');
}

function loadTeamData() {
    fetch('{{ route("projects.team-data", $project) }}')
        .then(response => response.json())
        .then(data => {
            renderTeamMembers(data.current_users);
            renderAvailableUsers(data.available_users);
        })
        .catch(error => console.error('Error loading team data:', error));
}

function renderTeamMembers(users) {
    const container = document.getElementById('current-team-members');
    if (users.length === 0) {
        container.innerHTML = '<div style="color: var(--theme-text-muted); font-size: var(--theme-font-size); padding: 1rem; text-align: center;">No team members assigned</div>';
        return;
    }

    container.innerHTML = users.map(user => `
        <div class="flex items-center justify-between p-3 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.1);">
            <div>
                <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">${user.name}</span>
                ${user.company ? `<span style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-left: 0.5rem;">(${user.company.name})</span>` : ''}
            </div>
            <button onclick="removeTeamMember(${user.id})" class="text-red-600 hover:text-red-800" style="background: none; border: none; cursor: pointer; padding: 0.25rem 0.5rem;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

function renderAvailableUsers(users) {
    const select = document.getElementById('available-users');
    select.innerHTML = '<option value="">Select a user to add...</option>' +
        users.map(user => `<option value="${user.id}">${user.name}${user.company ? ' (' + user.company.name + ')' : ''}</option>`).join('');
}

function addTeamMember() {
    const select = document.getElementById('available-users');
    const userId = select.value;

    if (!userId) return;

    fetch('{{ route("projects.add-team-member", $project) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTeamData();
            showSuccessMessage('Team member added successfully');
        } else {
            showErrorMessage(data.message || 'Failed to add team member');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An error occurred');
    });
}

function removeTeamMember(userId) {
    if (!confirm('Remove this team member from the project?')) return;

    fetch('{{ route("projects.remove-team-member", $project) }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTeamData();
            showSuccessMessage('Team member removed successfully');
        } else {
            showErrorMessage(data.message || 'Failed to remove team member');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An error occurred');
    });
}

// Time Entries Modal Functions
function openTimeEntriesModal() {
    const modal = document.getElementById('timeEntriesModal');
    modal.classList.remove('hidden');
    loadTimeEntries();
}

function closeTimeEntriesModal() {
    const modal = document.getElementById('timeEntriesModal');
    modal.classList.add('hidden');

    // Reset modal state
    document.getElementById('time-entries-loading').classList.remove('hidden');
    document.getElementById('time-entries-content').classList.add('hidden');
    document.getElementById('time-entries-stats').style.display = 'none';
    document.getElementById('time-entries-empty').classList.add('hidden');
}

function loadTimeEntries() {
    // Show loading state
    document.getElementById('time-entries-loading').classList.remove('hidden');
    document.getElementById('time-entries-content').classList.add('hidden');
    document.getElementById('time-entries-stats').style.display = 'none';

    // Fetch time entries
    fetch('{{ route("projects.time-entries", $project) }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderTimeEntries(data.entries, data.stats, data.user_stats);
        } else {
            showErrorMessage('Failed to load time entries');
            closeTimeEntriesModal();
        }
    })
    .catch(error => {
        console.error('Error loading time entries:', error);
        showErrorMessage('An error occurred while loading time entries');
        closeTimeEntriesModal();
    });
}

// Helper functie om defer informatie HTML te genereren
function getDeferInfoHTML(entry) {
    let deferHTML = '';

    // Currently deferred (will be invoiced later)
    if (entry.was_deferred && entry.deferred_at) {
        deferHTML += '<div style="font-size: calc(var(--theme-font-size) - 4px); color: var(--theme-text-muted); margin-top: 0.25rem;">';
        deferHTML += `Deferred on ${entry.deferred_at}`;

        if (entry.invoice_period_start) {
            deferHTML += `<br><strong style="color: #f97316;">→ Moved to: ${entry.invoice_period_start}</strong>`;
            if (entry.invoice_number) {
                deferHTML += ` (Invoice #${entry.invoice_number})`;
            }
        }

        if (entry.defer_reason) {
            deferHTML += `<br>${entry.defer_reason}`;
        }

        deferHTML += '</div>';
    }

    // Previously deferred (was moved FROM an earlier month)
    if (entry.was_previously_deferred && entry.invoice_period_start && entry.entry_month !== entry.invoice_period_start) {
        deferHTML += '<div style="font-size: calc(var(--theme-font-size) - 4px); color: var(--theme-text-muted); margin-top: 0.25rem; padding: 0.25rem 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px;">';
        deferHTML += `<strong style="color: #3b82f6;">⚠️ NOT invoiced in ${entry.entry_month}</strong>`;
        deferHTML += `<br>Moved to ${entry.invoice_period_start}`;

        if (entry.invoice_number) {
            deferHTML += ` (Invoice #${entry.invoice_number})`;
        }

        deferHTML += '</div>';
    }

    return deferHTML;
}

function renderTimeEntries(entries, stats, userStats) {
    // Hide loading
    document.getElementById('time-entries-loading').classList.add('hidden');

    // Update subtitle
    document.getElementById('time-entries-subtitle').textContent =
        `${stats.total_entries} ${stats.total_entries === 1 ? 'entry' : 'entries'} found`;

    // Show and update stats
    document.getElementById('time-entries-stats').style.display = 'block';
    document.getElementById('stat-total-entries').textContent = stats.total_entries;
    document.getElementById('stat-current-month-entries').textContent = stats.current_month_entries || 0;
    document.getElementById('stat-deferred-entries').textContent = stats.deferred_entries || 0;
    document.getElementById('stat-total-hours').textContent = stats.total_hours + 'h';

    // Check if empty
    if (entries.length === 0) {
        document.getElementById('time-entries-empty').classList.remove('hidden');
        return;
    }

    // Show content
    document.getElementById('time-entries-content').classList.remove('hidden');

    // Render table rows
    const tbody = document.getElementById('time-entries-table-body');
    tbody.innerHTML = entries.map(entry => {
        // Status badge styling
        let statusStyle = '';
        switch(entry.status) {
            case 'approved':
                statusStyle = 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);';
                break;
            case 'pending':
                statusStyle = 'background-color: rgba(var(--theme-warning-rgb), 0.1); color: var(--theme-warning);';
                break;
            case 'rejected':
                statusStyle = 'background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);';
                break;
            default:
                statusStyle = 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);';
        }

        // Billable badge
        const billableBadge = entry.is_billable
            ? '<span class="px-2 py-0.5 rounded text-xs" style="background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-weight: 500;"><i class="fas fa-check-circle mr-1"></i>Yes</span>'
            : '<span class="px-2 py-0.5 rounded text-xs" style="background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted); font-weight: 500;">No</span>';

        // DEBUG: Log defer status en previous month imports
        if (entry.invoice_id || entry.from_previous_month) {
            console.log('Entry', entry.id, '- was_deferred:', entry.was_deferred, 'was_previously_deferred:', entry.was_previously_deferred, 'from_previous_month:', entry.from_previous_month, 'entry_month:', entry.entry_month, 'invoice_period_start:', entry.invoice_period_start);
        }

        // Deferred badge met tooltip - HARDCODED HEX KLEUREN
        let deferredBadge = '<span style="color: #9ca3af; font-size: 14px;">-</span>';
        if (entry.was_deferred && entry.invoice_period_start) {
            // Entry is deferred to next month - ORANJE
            deferredBadge = `<div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                <span style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 11px; background-color: #fed7aa; color: #ea580c; font-weight: 600; border: 1px solid #fdba74; white-space: nowrap;">
                    <i class="fas fa-arrow-right" style="margin-right: 0.25rem;"></i>DEFERRED
                </span>
                <span style="font-size: 11px; color: #6b7280; white-space: nowrap;">
                    → ${entry.invoice_period_start}
                </span>
            </div>`;
        } else if (entry.was_previously_deferred && entry.invoice_period_start && entry.entry_month !== entry.invoice_period_start) {
            // Entry was moved FROM an earlier month (imported from defer) - BLAUW
            deferredBadge = `<div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                <span style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 11px; background-color: #dbeafe; color: #2563eb; font-weight: 600; border: 1px solid #93c5fd; white-space: nowrap;">
                    <i class="fas fa-info-circle" style="margin-right: 0.25rem;"></i>FROM ${entry.entry_month}
                </span>
                <span style="font-size: 11px; color: #6b7280; white-space: nowrap;">
                    Billed in ${entry.invoice_period_start}
                </span>
            </div>`;
        }

        // Work item (milestone → task → subtask hierarchy)
        let workItem = '-';
        if (entry.subtask !== '-') {
            workItem = `<div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">${entry.milestone}</div>
                       <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); padding-left: 0.5rem;">→ ${entry.task}</div>
                       <div style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500; padding-left: 1rem;">→ ${entry.subtask}</div>`;
        } else if (entry.task !== '-') {
            workItem = `<div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">${entry.milestone}</div>
                       <div style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500; padding-left: 0.5rem;">→ ${entry.task}</div>`;
        } else if (entry.milestone !== '-') {
            workItem = `<div style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500;">${entry.milestone}</div>`;
        }

        return `
            <tr style="border-bottom: 1px solid rgba(226, 232, 240, 0.6); transition: background-color 0.2s;"
                onmouseover="this.style.backgroundColor='rgba(248, 250, 252, 0.8)'"
                onmouseout="this.style.backgroundColor='transparent'">
                <td style="padding: 0.75rem 1rem;">
                    <div style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500;">${entry.entry_date}</div>
                </td>
                <td style="padding: 0.75rem 1rem;">
                    <div style="font-size: var(--theme-font-size); color: var(--theme-text);">${entry.user}</div>
                </td>
                <td style="padding: 0.75rem 1rem;">
                    ${workItem}
                </td>
                <td style="padding: 0.75rem 1rem;">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); max-width: 300px; white-space: normal; line-height: 1.4;">
                        ${entry.description || '<em style="color: var(--theme-text-muted);">No description</em>'}
                    </div>
                    ${getDeferInfoHTML(entry)}
                </td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                    <span style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 600; font-family: monospace;">${entry.duration_formatted}</span>
                </td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                    ${billableBadge}
                </td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                    ${deferredBadge}
                </td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                    <span class="px-2 py-0.5 rounded text-xs" style="${statusStyle} font-weight: 500; text-transform: capitalize;">
                        ${entry.status}
                    </span>
                </td>
            </tr>
        `;
    }).join('');

    // Render user statistics
    if (userStats && userStats.length > 0) {
        const userStatsContainer = document.getElementById('time-entries-user-stats');
        const userStatsList = document.getElementById('user-stats-list');

        // Show the user stats section
        userStatsContainer.classList.remove('hidden');

        // Calculate max hours for visual bar scaling
        const maxHours = userStats[0].total_duration_decimal || 1;

        // Render user stats items
        userStatsList.innerHTML = userStats.map((userStat, index) => {
            // Calculate percentage for bar width
            const percentage = (userStat.total_duration_decimal / maxHours) * 100;

            // Visual bar color based on rank
            let barColor = '';
            if (index === 0) {
                barColor = 'rgba(var(--theme-primary-rgb), 0.6)'; // Top contributor - primary color
            } else if (index === 1) {
                barColor = 'rgba(var(--theme-success-rgb), 0.5)'; // Second - success color
            } else if (index === 2) {
                barColor = 'rgba(var(--theme-warning-rgb), 0.5)'; // Third - warning color
            } else {
                barColor = 'rgba(var(--theme-text-muted-rgb), 0.3)'; // Others - muted
            }

            return `
                <div class="flex items-center justify-between p-3 rounded-lg transition-all duration-200"
                     style="background-color: rgba(var(--theme-primary-rgb), 0.03); border: 1px solid rgba(var(--theme-border-rgb), 0.2);"
                     onmouseover="this.style.backgroundColor='rgba(var(--theme-primary-rgb), 0.06)'"
                     onmouseout="this.style.backgroundColor='rgba(var(--theme-primary-rgb), 0.03)'">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background-color: ${barColor};">
                                <span style="color: white; font-weight: 600; font-size: calc(var(--theme-font-size) - 2px);">
                                    ${userStat.user_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div style="font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-text);">
                                    ${userStat.user_name}
                                </div>
                                <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                                    ${userStat.entries_count} ${userStat.entries_count === 1 ? 'entry' : 'entries'}
                                </div>
                            </div>
                        </div>
                        <div class="w-full h-2 rounded-full overflow-hidden" style="background-color: rgba(var(--theme-border-rgb), 0.2);">
                            <div class="h-full transition-all duration-500"
                                 style="width: ${percentage}%; background-color: ${barColor};">
                            </div>
                        </div>
                    </div>
                    <div class="ml-4 text-right">
                        <div style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 700; color: var(--theme-text); font-family: monospace;">
                            ${userStat.duration_formatted}
                        </div>
                        <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                            ${userStat.total_duration_decimal.toFixed(2)}h
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
}

// Task/Milestone Time Entries Modal Functions
function openTaskTimeEntriesModal(type, id) {
    const modal = document.getElementById('taskTimeEntriesModal');
    modal.classList.remove('hidden');
    loadTaskTimeEntries(type, id);
}

function closeTaskTimeEntriesModal() {
    const modal = document.getElementById('taskTimeEntriesModal');
    modal.classList.add('hidden');

    // Reset modal state
    document.getElementById('task-time-entries-loading').classList.remove('hidden');
    document.getElementById('task-time-entries-content').classList.add('hidden');
    document.getElementById('task-time-entries-stats').style.display = 'none';
    document.getElementById('task-time-entries-empty').classList.add('hidden');

    // Reset task column visibility
    document.getElementById('task-column-header').style.display = '';
}

function loadTaskTimeEntries(type, id) {
    // Show loading state
    document.getElementById('task-time-entries-loading').classList.remove('hidden');
    document.getElementById('task-time-entries-content').classList.add('hidden');
    document.getElementById('task-time-entries-stats').style.display = 'none';

    // Fetch time entries
    fetch('{{ route("projects.task-time-entries", $project) }}?type=' + type + '&id=' + id, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderTaskTimeEntries(data.entries, data.stats, data.item_name, data.item_type);
        } else {
            showErrorMessage('Failed to load time entries');
            closeTaskTimeEntriesModal();
        }
    })
    .catch(error => {
        console.error('Error loading task time entries:', error);
        showErrorMessage('An error occurred while loading time entries');
        closeTaskTimeEntriesModal();
    });
}

function renderTaskTimeEntries(entries, stats, itemName, itemType) {
    // Hide loading
    document.getElementById('task-time-entries-loading').classList.add('hidden');

    // Update title and subtitle
    const typeLabel = itemType === 'milestone' ? 'Milestone' : 'Task';
    document.getElementById('task-time-entries-title').textContent = `Time Entries for ${typeLabel}`;
    document.getElementById('task-time-entries-subtitle').textContent =
        `${itemName} - ${stats.total_entries} ${stats.total_entries === 1 ? 'entry' : 'entries'} found`;

    // Show/hide Task column based on type
    const taskColumnHeader = document.getElementById('task-column-header');
    if (itemType === 'task') {
        taskColumnHeader.style.display = 'none'; // Verberg task kolom voor task modal
    } else {
        taskColumnHeader.style.display = ''; // Toon task kolom voor milestone modal
    }

    // Show and update stats
    document.getElementById('task-time-entries-stats').style.display = 'block';
    document.getElementById('task-stat-total-entries').textContent = stats.total_entries;
    document.getElementById('task-stat-total-hours').textContent = stats.total_hours + 'h';
    document.getElementById('task-stat-total-duration').textContent = stats.total_duration_formatted;

    // Check if empty
    if (entries.length === 0) {
        document.getElementById('task-time-entries-empty').classList.remove('hidden');
        return;
    }

    // Show content
    document.getElementById('task-time-entries-content').classList.remove('hidden');

    // Render table rows
    const tbody = document.getElementById('task-time-entries-table-body');
    tbody.innerHTML = entries.map(entry => {
        // Status badge styling
        let statusStyle = '';
        switch(entry.status) {
            case 'approved':
                statusStyle = 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);';
                break;
            case 'pending':
                statusStyle = 'background-color: rgba(var(--theme-warning-rgb), 0.1); color: var(--theme-warning);';
                break;
            case 'rejected':
                statusStyle = 'background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);';
                break;
            default:
                statusStyle = 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);';
        }

        // Billable badge
        const billableBadge = entry.is_billable
            ? '<span class="px-2 py-0.5 rounded text-xs" style="background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-weight: 500;"><i class="fas fa-check-circle mr-1"></i>Yes</span>'
            : '<span class="px-2 py-0.5 rounded text-xs" style="background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted); font-weight: 500;">No</span>';

        // Task kolom (alleen voor milestone modal)
        const taskCell = itemType === 'milestone'
            ? `<td style="padding: 0.75rem 1rem;">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                        ${entry.task !== '-' ? entry.task : '<em style="color: var(--theme-text-muted);">Direct</em>'}
                    </div>
               </td>`
            : '';

        return `
            <tr style="border-bottom: 1px solid rgba(226, 232, 240, 0.6); transition: background-color 0.2s;"
                onmouseover="this.style.backgroundColor='rgba(248, 250, 252, 0.8)'"
                onmouseout="this.style.backgroundColor='transparent'">
                <td style="padding: 0.75rem 1rem;">
                    <div style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500;">${entry.entry_date}</div>
                </td>
                <td style="padding: 0.75rem 1rem;">
                    <div style="font-size: var(--theme-font-size); color: var(--theme-text);">${entry.user}</div>
                </td>
                ${taskCell}
                <td style="padding: 0.75rem 1rem;">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); max-width: 300px; white-space: normal; line-height: 1.4;">
                        ${entry.description || '<em style="color: var(--theme-text-muted);">No description</em>'}
                    </div>
                </td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                    <span style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 600; font-family: monospace;">${entry.duration_formatted}</span>
                </td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                    ${billableBadge}
                </td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                    <span class="px-2 py-0.5 rounded text-xs" style="${statusStyle} font-weight: 500; text-transform: capitalize;">
                        ${entry.status}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
}
</script>
@endpush

@push('styles')
<style>
/* Responsive layout */
@media (max-width: 1024px) {
    .projects-two-column {
        display: block !important;
    }
    .projects-two-column > div {
        margin-bottom: 2rem;
    }
}

/* Smooth transitions for inline editing */
.field-view, .field-edit {
    transition: opacity 0.2s ease;
}

/* Style inputs to match view mode better */
input[type="text"],
input[type="date"],
input[type="number"],
select,
textarea {
    border-color: rgba(var(--theme-border-rgb), 0.3);
    transition: border-color 0.2s ease;
}

input[type="text"]:focus,
input[type="date"]:focus,
input[type="number"]:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--theme-primary);
    box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.1);
}
</style>
@endpush

{{-- Recurring Settings Edit Modal --}}
@if($project->is_recurring)
<div id="recurringSettingsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white shadow-2xl max-w-2xl w-full mx-4" style="border-radius: var(--theme-border-radius);">
        <form id="recurringSettingsForm" action="{{ route('projects.updateRecurringSettings', $project) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem;">
                <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text);">
                    <i class="fas fa-sync-alt mr-2" style="color: var(--theme-accent);"></i>
                    Edit Recurring Settings
                </h3>
            </div>

            <div class="px-6 py-4 space-y-4">
                {{-- Base Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Base Project Name <span class="style="color: var(--theme-danger);"">*</span>
                    </label>
                    <input type="text"
                           name="recurring_base_name"
                           value="{{ $project->recurring_base_name }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="e.g., Anker">
                    <p class="text-xs text-gray-500 mt-1">Projects will be named: "[Base Name] [Month] [Year]"</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Frequency --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Frequency <span class="style="color: var(--theme-danger);"">*</span>
                        </label>
                        <select name="recurring_frequency"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="monthly" {{ $project->recurring_frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ $project->recurring_frequency === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        </select>
                    </div>

                    {{-- Days Before --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Create Days Before
                        </label>
                        <input type="number"
                               name="recurring_days_before"
                               value="{{ $project->recurring_days_before ?? 7 }}"
                               min="1"
                               max="30"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="7">
                        <p class="text-xs text-gray-500 mt-1">Days before new period to create project</p>
                    </div>
                </div>

                {{-- End Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Stop Recurring On (Optional)
                    </label>
                    <input type="date"
                           name="recurring_end_date"
                           value="{{ $project->recurring_end_date ? $project->recurring_end_date->format('Y-m-d') : '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to continue indefinitely</p>
                </div>

                {{-- Recurring Series ID --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Recurring Series ID
                    </label>
                    <div class="flex space-x-2">
                        <select id="recurring_series_id_select_modal"
                                onchange="handleModalSeriesSelection(this.value)"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Auto-generate (series-{{ $project->id }})</option>
                            <option value="_custom">Create new custom series ID</option>
                            @php
                                $existingSeriesIdsModal = \App\Models\Project::whereNotNull('recurring_series_id')
                                    ->select('recurring_series_id', DB::raw('COUNT(*) as project_count'))
                                    ->groupBy('recurring_series_id')
                                    ->orderBy('recurring_series_id')
                                    ->get();
                            @endphp
                            @if($existingSeriesIdsModal->count() > 0)
                                <optgroup label="Existing Series">
                                    @foreach($existingSeriesIdsModal as $seriesId)
                                    <option value="{{ $seriesId->recurring_series_id }}"
                                            {{ $project->recurring_series_id == $seriesId->recurring_series_id ? 'selected' : '' }}>
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
                           id="recurring_series_id_custom_modal"
                           value="{{ $project->recurring_series_id }}"
                           style="display: {{ $project->recurring_series_id && !$existingSeriesIdsModal->contains('recurring_series_id', $project->recurring_series_id) ? 'block' : 'none' }};"
                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="e.g., series-anker-2025">
                    <p class="text-xs text-gray-500 mt-1">
                        Group related projects together for consolidated budget tracking. Leave blank to auto-generate based on project ID.
                    </p>
                </div>

                {{-- Disable Recurring Option --}}
                <div style="background-color: rgba(var(--theme-danger-rgb), 0.05); border: 1px solid rgba(var(--theme-danger-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <div class="flex items-start">
                        <input type="checkbox"
                               name="disable_recurring"
                               id="disable_recurring"
                               value="1"
                               style="margin-top: 0.25rem; height: 1rem; width: 1rem;">
                        <div class="ml-3">
                            <label for="disable_recurring" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-danger); cursor: pointer;">
                                Disable Recurring
                            </label>
                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text); margin-top: 0.25rem;">
                                Check this to stop automatic project generation. Existing generated projects will not be affected.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t flex items-center justify-end gap-3" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem;">
                <button type="button"
                        onclick="closeRecurringSettingsModal()"
                        class="transition-colors"
                        style="padding: 0.5rem 1rem; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text); background-color: rgba(var(--theme-text-muted-rgb), 0.1); border: none; border-radius: var(--theme-border-radius); cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                        class="transition-colors"
                        style="padding: 0.5rem 1rem; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: white; background-color: var(--theme-accent); border: none; border-radius: var(--theme-border-radius); cursor: pointer;">
                    <i class="fas fa-save mr-1"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRecurringSettingsModal() {
    document.getElementById('recurringSettingsModal').classList.remove('hidden');
}

function closeRecurringSettingsModal() {
    document.getElementById('recurringSettingsModal').classList.add('hidden');
}

// Handle recurring series ID selection in modal
function handleModalSeriesSelection(value) {
    const customInput = document.getElementById('recurring_series_id_custom_modal');

    if (value === '_custom') {
        // Show custom input field
        customInput.style.display = 'block';
        customInput.focus();
        customInput.value = ''; // Clear for new custom entry
    } else {
        // Hide custom input field
        customInput.style.display = 'none';

        if (value) {
            // Set selected series ID
            customInput.value = value;
        } else {
            // Clear value for auto-generate
            customInput.value = '';
        }
    }
}

// Handle standalone project series ID selection - GLOBAL FUNCTION
window.handleStandaloneSeriesSelection = function(value) {
    console.log('handleStandaloneSeriesSelection called with value:', value);

    const customInput = document.getElementById('standalone_series_custom');

    if (!customInput) {
        console.error('ERROR: Element with ID "standalone_series_custom" not found!');
        alert('Error: Input field not found. Please refresh the page and try again.');
        return;
    }

    console.log('Custom input element found:', customInput);

    if (value === '_custom') {
        // Show custom input field with animation
        console.log('Showing custom input field...');
        customInput.style.display = 'block';
        customInput.style.marginTop = '1rem';

        // Slight delay to ensure display is rendered before focus
        setTimeout(() => {
            customInput.focus();
            customInput.value = ''; // Clear for new custom entry
            console.log('Custom input field is now visible and focused');
        }, 50);
    } else {
        // Hide custom input field
        console.log('Hiding custom input field...');
        customInput.style.display = 'none';

        if (value) {
            // Set selected series ID
            customInput.value = value;
            console.log('Set custom input value to:', value);
        } else {
            // Clear value (remove from series)
            customInput.value = '';
            console.log('Cleared custom input value');
        }
    }
};

// Log that function is now globally available
console.log('✅ handleStandaloneSeriesSelection is now globally available');

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRecurringSettingsModal();
    }
});

// Close modal on backdrop click
document.getElementById('recurringSettingsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRecurringSettingsModal();
    }
});

// Master Template Toggle Confirmation
function confirmMasterTemplateToggle(willBecomeMaster) {
    if (willBecomeMaster) {
        return confirm(
            '🔔 Upgrade to Master Template?\n\n' +
            'This will:\n' +
            '✓ Make this project the master template for the series\n' +
            '✓ Remove master status from any other project in this series\n' +
            '✓ Mark this project as recurring (if not already)\n\n' +
            'The master template will be used to auto-generate future projects.\n\n' +
            'Continue?'
        );
    } else {
        return confirm(
            '⚠️ Remove Master Template Status?\n\n' +
            'This will remove this project as the master template for the series.\n' +
            'You can designate another project as master template later.\n\n' +
            'Continue?'
        );
    }
}
</script>
@endif

{{-- Help Modal - Recurring Projects Complete Guide --}}
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="padding: 1rem;">
    <div class="w-full shadow-lg flex flex-col" style="background-color: var(--theme-bg); border-radius: var(--theme-border-radius); max-width: 72rem; max-height: 90vh; overflow: hidden;">
        {{-- Header - Fixed --}}
        <div class="border-b flex justify-between items-center flex-shrink-0" style="padding: 1.5rem; border-color: rgba(203, 213, 225, 0.3); background-color: var(--theme-primary);">
            <div class="flex items-center gap-3">
                <i class="fas fa-sync-alt" style="font-size: calc(var(--theme-font-size) + 6px); color: white;"></i>
                <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: white;">Recurring Projects - Complete Guide</h3>
            </div>
            <button onclick="closeHelpModal()" style="color: white; background: none; border: none; cursor: pointer; opacity: 0.8; transition: opacity 0.2s;">
                <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) + 4px);"></i>
            </button>
        </div>
        {{-- Content - Scrollable --}}
        <div class="overflow-y-auto flex-1" style="padding: 1.5rem; font-size: var(--theme-font-size);">
            <div class="space-y-8">
                {{-- Introduction --}}
                <div class="shadow-sm" style="background: linear-gradient(135deg, rgba(var(--theme-primary-rgb), 0.1) 0%, rgba(var(--theme-primary-rgb), 0.05) 100%); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); border-left: 4px solid var(--theme-primary);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 700; color: var(--theme-primary); margin-bottom: 0.75rem;">
                        <i class="fas fa-sync-alt mr-2" style="color: var(--theme-primary);"></i>What are Recurring Projects?
                    </h4>
                    <p style="color: var(--theme-text); line-height: 1.6; margin-bottom: 0.75rem; font-size: calc(var(--theme-font-size) + 1px);">
                        <strong>Recurring Projects</strong> is a powerful automation system that automatically creates new projects for future periods (monthly or quarterly).
                        Perfect for retainer work, ongoing maintenance, subscription services, or any work that repeats regularly.
                    </p>
                    <div style="background-color: white; border: 2px solid rgba(var(--theme-primary-rgb), 0.3); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); margin-top: 0.75rem;">
                        <p style="color: var(--theme-primary); font-size: var(--theme-font-size); font-weight: 500;">
                            <i class="fas fa-magic mr-2"></i><strong>Example:</strong> Create "Website Maintenance Aug 2025" once, and the system automatically generates
                            "Website Maintenance Sep 2025", "Website Maintenance Oct 2025", etc. - each with the same structure, team, and budget!
                        </p>
                    </div>
                </div>

                {{-- Master vs Child Projects --}}
                <div class="bg-white shadow-sm" style="border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-project-diagram mr-2" style="color: var(--theme-primary);"></i>Master vs Child Projects
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div style="border: 2px solid rgba(var(--theme-primary-rgb), 0.3); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); background-color: rgba(var(--theme-primary-rgb), 0.05);">
                            <div class="flex items-center mb-2">
                                <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-primary); color: white; border-radius: 9999px; font-size: calc(var(--theme-font-size) - 2px); font-weight: 700; margin-right: 0.5rem;">MASTER</span>
                                <span style="font-weight: 600; color: var(--theme-text);">Recurring Badge</span>
                            </div>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                The <strong>original project</strong> you create with "recurring" enabled. This is the template that gets copied.
                            </p>
                            <ul class="mt-3 space-y-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <li><i class="fas fa-check mr-2" style="color: var(--theme-success);"></i>Status must be <strong>"Active"</strong></li>
                                <li><i class="fas fa-check mr-2" style="color: var(--theme-success);"></i>Has recurring settings (frequency, base name)</li>
                                <li><i class="fas fa-check mr-2" style="color: var(--theme-success);"></i>Can be edited or disabled anytime</li>
                            </ul>
                        </div>
                        <div style="border: 2px solid rgba(var(--theme-success-rgb), 0.3); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); background-color: rgba(var(--theme-success-rgb), 0.05);">
                            <div class="flex items-center mb-2">
                                <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-success); color: white; border-radius: 9999px; font-size: calc(var(--theme-font-size) - 2px); font-weight: 700; margin-right: 0.5rem;">AUTO</span>
                                <span style="font-weight: 600; color: var(--theme-text);">Generated Badge</span>
                            </div>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Automatically created</strong> copies for future periods. These are normal projects you can use and edit.
                            </p>
                            <ul class="mt-3 space-y-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <li><i class="fas fa-check mr-2" style="color: var(--theme-success);"></i>Always status <strong>"Active"</strong></li>
                                <li><i class="fas fa-check mr-2" style="color: var(--theme-success);"></i>Complete copy (milestones, tasks, team, budget)</li>
                                <li><i class="fas fa-check mr-2" style="color: var(--theme-success);"></i>Work on them like any normal project</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- How It Works --}}
                <div class="shadow-sm" style="background-color: rgba(var(--theme-primary-rgb), 0.03); border: 1px solid rgba(var(--theme-primary-rgb), 0.15); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-primary); margin-bottom: 0.75rem;">
                        <i class="fas fa-cogs mr-2" style="color: var(--theme-primary);"></i>How the Automation Works
                    </h4>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 flex items-center justify-center font-bold mr-3" style="width: 2rem; height: 2rem; background-color: var(--theme-primary); color: white; border-radius: 50%;">1</div>
                            <div>
                                <p style="font-weight: 600; color: var(--theme-text);">System Checks Daily</p>
                                <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    A scheduled task runs every day and checks all active recurring master projects.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 flex items-center justify-center font-bold mr-3" style="width: 2rem; height: 2rem; background-color: var(--theme-primary); color: white; border-radius: 50%;">2</div>
                            <div>
                                <p style="font-weight: 600; color: var(--theme-text);">Time Window Check</p>
                                <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    If we're within the "days before" window (e.g., 7 days before next month), a new project is created.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 flex items-center justify-center font-bold mr-3" style="width: 2rem; height: 2rem; background-color: var(--theme-primary); color: white; border-radius: 50%;">3</div>
                            <div>
                                <p style="font-weight: 600; color: var(--theme-text);">Complete Duplication</p>
                                <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    Copies milestones, tasks, subtasks, team members, billing companies, and budget settings.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 flex items-center justify-center font-bold mr-3" style="width: 2rem; height: 2rem; background-color: var(--theme-primary); color: white; border-radius: 50%;">4</div>
                            <div>
                                <p style="font-weight: 600; color: var(--theme-text);">Automatic Naming</p>
                                <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    New project gets named: "[Base Name] [Month] [Year]" (e.g., "Website Maintenance Nov 2025")
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step-by-Step Creation Guide --}}
                <div class="shadow-sm" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.08) 0%, rgba(var(--theme-accent-rgb), 0.03) 100%); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); border: 2px solid rgba(var(--theme-accent-rgb), 0.25);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-primary); margin-bottom: 0.75rem;">
                        <i class="fas fa-tasks mr-2" style="color: var(--theme-primary);"></i>Step-by-Step: Creating Your First Recurring Project
                    </h4>
                    <div class="space-y-3">
                        <div style="background-color: white; border-left: 4px solid var(--theme-primary); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-primary); color: white; border-radius: 0.375rem; font-size: calc(var(--theme-font-size) - 1px); margin-right: 0.5rem;">STEP 1</span> Click "New Project"
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Fill in basic information: Customer, Project Name (with month/year like "SEO Oct 2025"), Start/End dates.
                            </p>
                        </div>
                        <div style="background-color: white; border-left: 4px solid var(--theme-primary); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-primary); color: white; border-radius: 0.375rem; font-size: calc(var(--theme-font-size) - 1px); margin-right: 0.5rem;">STEP 2</span> Enable Recurring
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Scroll to "Recurring Project Settings" section and check ✓ "Make this a recurring project"
                            </p>
                        </div>
                        <div style="background-color: white; border-left: 4px solid var(--theme-primary); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-primary); color: white; border-radius: 0.375rem; font-size: calc(var(--theme-font-size) - 1px); margin-right: 0.5rem;">STEP 3</span> Configure Settings
                            </p>
                            <ul style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); list-style: none; padding-left: 0;" class="space-y-2 mt-2">
                                <li><strong style="color: var(--theme-primary);">Base Name:</strong> "SEO" (without month/year - that's added automatically)</li>
                                <li><strong style="color: var(--theme-primary);">Frequency:</strong> Monthly or Quarterly</li>
                                <li><strong style="color: var(--theme-primary);">Days Before:</strong> 7 (create new project 7 days before next period)</li>
                                <li><strong style="color: var(--theme-primary);">End Date:</strong> Optional stop date (leave empty for infinite)</li>
                            </ul>
                        </div>
                        <div style="background-color: white; border-left: 4px solid var(--theme-primary); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-primary); color: white; border-radius: 0.375rem; font-size: calc(var(--theme-font-size) - 1px); margin-right: 0.5rem;">STEP 4</span> Add Team & Budget
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Select team members and set monthly budget. These will be copied to all future projects.
                            </p>
                        </div>
                        <div style="background-color: white; border-left: 4px solid var(--theme-primary); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-primary); color: white; border-radius: 0.375rem; font-size: calc(var(--theme-font-size) - 1px); margin-right: 0.5rem;">STEP 5</span> Create Project
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Click "Create Project". Your master project is created with status "Active" (required for automation).
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-success-rgb), 0.1); border: 2px solid rgba(var(--theme-success-rgb), 0.3); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); text-align: center;">
                            <p style="color: var(--theme-success); font-weight: 700; font-size: calc(var(--theme-font-size) + 1px);">
                                <i class="fas fa-check-circle mr-2"></i>Done! The system will now automatically create future projects.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Settings Explained --}}
                <div class="bg-white shadow-sm" style="border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-sliders-h mr-2" style="color: var(--theme-warning);"></i>Recurring Settings Explained
                    </h4>
                    <div class="space-y-3">
                        <div style="background-color: rgba(203, 213, 225, 0.1); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-tag mr-2" style="color: var(--theme-primary);"></i>Base Project Name
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                The name without time period. Example: "Website Maintenance" becomes "Website Maintenance Nov 2025", "Website Maintenance Dec 2025", etc.
                            </p>
                        </div>
                        <div style="background-color: rgba(203, 213, 225, 0.1); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-calendar-alt mr-2" style="color: var(--theme-primary);"></i>Frequency
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Monthly:</strong> Creates projects for each month (Aug 2025, Sep 2025, Oct 2025...)<br>
                                <strong>Quarterly:</strong> Creates projects per quarter (Q3 2025, Q4 2025, Q1 2026...)
                            </p>
                        </div>
                        <div style="background-color: rgba(203, 213, 225, 0.1); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-clock mr-2" style="color: var(--theme-success);"></i>Create New Project (Days Before)
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                How many days before the new period should the project be created. Default: 7 days.<br>
                                <strong>Example:</strong> For November project with 7 days → created around October 24th.
                            </p>
                        </div>
                        <div style="background-color: rgba(203, 213, 225, 0.1); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-stop-circle mr-2" style="color: var(--theme-danger);"></i>Stop Recurring On (Optional)
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                End date for automatic generation. Leave empty to continue indefinitely.<br>
                                <strong>Use case:</strong> 6-month contract → set end date to stop after 6 months.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Editing and Disabling --}}
                <div class="bg-white shadow-sm" style="border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-edit mr-2" style="color: var(--theme-primary);"></i>Editing & Disabling Recurring Projects
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-pencil-alt mr-2" style="color: var(--theme-primary);"></i>Edit Settings
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                1. Open the master project (with RECURRING badge)<br>
                                2. Find "Recurring Project Settings" card<br>
                                3. Click "Edit Settings" button<br>
                                4. Update any setting (base name, frequency, days before, end date)<br>
                                5. Save changes
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-danger-rgb), 0.05); border: 1px solid rgba(var(--theme-danger-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-ban mr-2" style="color: var(--theme-danger);"></i>Disable Recurring
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                1. Open the master project<br>
                                2. Click "Edit Settings" in Recurring card<br>
                                3. Check ✓ "Disable recurring" at bottom<br>
                                4. Save - No more automatic projects<br>
                                <strong>Note:</strong> Existing auto-generated projects remain unchanged.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Practical Examples --}}
                <div class="bg-white shadow-sm" style="border: 2px solid rgba(var(--theme-primary-rgb), 0.3); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-lightbulb mr-2" style="color: var(--theme-warning);"></i>Practical Use Cases & Examples
                    </h4>
                    <div class="space-y-4">
                        <div style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                📱 Monthly Retainer Client
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Setup:</strong> Base Name: "Client X Retainer", Frequency: Monthly, Budget: €5,000/month<br>
                                <strong>Result:</strong> Automatic projects for every month with same tasks, team, and budget<br>
                                <strong>Benefits:</strong> No manual work, consistent project structure, automated budget tracking
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-success-rgb), 0.05); border: 1px solid rgba(var(--theme-success-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                🌐 Website Maintenance
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Setup:</strong> Base Name: "Website Maintenance", Frequency: Monthly, Days Before: 3<br>
                                <strong>Milestones:</strong> Security Updates, Content Updates, Performance Check, Backup Verification<br>
                                <strong>Result:</strong> Ready-to-work maintenance project created 3 days before each month
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                📊 Quarterly Reporting
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Setup:</strong> Base Name: "Quarterly Business Review", Frequency: Quarterly, Days Before: 14<br>
                                <strong>Tasks:</strong> Data collection, Analysis, Report creation, Client presentation<br>
                                <strong>Result:</strong> Q1, Q2, Q3, Q4 projects created 2 weeks before quarter start
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-warning-rgb), 0.05); border: 1px solid rgba(var(--theme-warning-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                🎯 Multiple Services for Same Client
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Setup:</strong> Create 2 master projects:<br>
                                • "Client Y - SEO" (€3,000/month) - SEO team<br>
                                • "Client Y - Development" (€8,000/month) - Dev team<br>
                                <strong>Result:</strong> Separate tracking, separate teams, separate budgets - all automatic!
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Best Practices --}}
                <div class="shadow-sm" style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 2px solid rgba(var(--theme-accent-rgb), 0.3); border-radius: var(--theme-border-radius); padding: 1.25rem;">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-star mr-2" style="color: var(--theme-warning);"></i>Best Practices & Tips
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="bg-white" style="border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Use clear base names
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                "Client Name - Service Type" makes it easy to identify projects
                            </p>
                        </div>
                        <div class="bg-white" style="border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Set up milestones on master
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Define all recurring tasks once - they copy to all future projects
                            </p>
                        </div>
                        <div class="bg-white" style="border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Keep master status Active
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Only active masters generate new projects. Pause by changing status.
                            </p>
                        </div>
                        <div class="bg-white" style="border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Review 'days before' setting
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                7 days is good default, but adjust based on your planning needs
                            </p>
                        </div>
                        <div class="bg-white" style="border: 1px solid rgba(var(--theme-danger-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 1px);">
                                ❌ DON'T: Delete master projects
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Disable recurring instead - keeps history and allows re-enabling
                            </p>
                        </div>
                        <div class="bg-white" style="border: 1px solid rgba(var(--theme-danger-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                            <p style="font-weight: 600; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 1px);">
                                ❌ DON'T: Change status to draft/hold
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Master must stay Active or automation stops
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Troubleshooting --}}
                <div class="bg-white shadow-sm" style="border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-wrench mr-2" style="color: var(--theme-danger);"></i>Troubleshooting
                    </h4>
                    <div class="space-y-3">
                        <div style="background-color: rgba(var(--theme-danger-rgb), 0.05); border-left: 4px solid var(--theme-danger); border-radius: var(--theme-border-radius); padding: 1rem;">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                ⚠️ New project not created automatically
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Check:</strong> Master project status = "Active" ✓ | "Days before" window reached ✓ | End date not passed ✓
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-warning-rgb), 0.05); border-left: 4px solid var(--theme-warning); border-radius: var(--theme-border-radius); padding: 1rem;">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                ⚠️ Can't find my recurring project
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Solution:</strong> Look for purple "RECURRING" badge in project list. Only master projects have this badge.
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-primary-rgb), 0.05); border-left: 4px solid var(--theme-primary); border-radius: var(--theme-border-radius); padding: 1rem;">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                ℹ️ Want to change structure for future projects
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Solution:</strong> Edit milestones/tasks on the master project. Changes apply to new projects, not existing ones.
                            </p>
                        </div>
                        <div style="background-color: rgba(var(--theme-success-rgb), 0.05); border-left: 4px solid var(--theme-success); border-radius: var(--theme-border-radius); padding: 1rem;">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                ℹ️ Client contract ended - stop automation
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Solution:</strong> Open master → Edit Settings → Check "Disable recurring" → Save. Done!
                            </p>
                        </div>
                    </div>
                </div>

                {{-- FAQ --}}
                <div class="bg-white shadow-sm" style="border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-question-circle mr-2" style="color: var(--theme-accent);"></i>Frequently Asked Questions
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                Q: Can I have multiple recurring projects for the same customer?
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>A:</strong> Yes! Create separate master projects for each service. For example: "SEO Oct 2025" and "Development Oct 2025" both for same client.
                            </p>
                        </div>
                        <div>
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                Q: What happens to existing auto-generated projects if I edit the master?
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>A:</strong> Nothing - they remain unchanged. Only NEW projects created after your edit will have the updated structure.
                            </p>
                        </div>
                        <div>
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                Q: Can I manually create a project for a skipped month?
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>A:</strong> Yes - just create a regular project. The automation checks for existing projects and won't create duplicates.
                            </p>
                        </div>
                        <div>
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                Q: Does the system send notifications when projects are created?
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>A:</strong> Currently no - check your project list regularly. Email notifications are planned for future updates.
                            </p>
                        </div>
                        <div>
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                Q: Can I change from monthly to quarterly after creation?
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>A:</strong> Yes - use "Edit Settings" on master project and change frequency. Takes effect for next scheduled creation.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Visual Badge Reference --}}
                <div style="background: linear-gradient(135deg, rgba(203, 213, 225, 0.2) 0%, rgba(203, 213, 225, 0.3) 100%); border: 2px solid rgba(203, 213, 225, 0.5); border-radius: var(--theme-border-radius); padding: 1.25rem;">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-tags mr-2" style="color: var(--theme-text-muted);"></i>Quick Badge Reference
                    </h4>
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="flex items-center gap-2 bg-white shadow" style="border-radius: var(--theme-border-radius); padding: 0.75rem 1rem;">
                            <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-primary); color: white; border-radius: 9999px; font-size: calc(var(--theme-font-size) - 1px); font-weight: 700;">RECURRING</span>
                            <span style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 1px);">= Master Project (generates new)</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white shadow" style="border-radius: var(--theme-border-radius); padding: 0.75rem 1rem;">
                            <span style="padding: 0.25rem 0.75rem; background-color: var(--theme-success); color: white; border-radius: 9999px; font-size: calc(var(--theme-font-size) - 1px); font-weight: 700;">AUTO</span>
                            <span style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 1px);">= Generated Project (normal work)</span>
                        </div>
                    </div>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); margin-top: 1rem;">
                        <i class="fas fa-info-circle mr-1"></i>Both types appear in your regular project list and customer views. The badges help you identify their role.
                    </p>
                </div>

            </div>
        </div>
        {{-- Footer - Fixed --}}
        <div class="border-t flex flex-col sm:flex-row justify-between items-center gap-3 flex-shrink-0" style="padding: 1.25rem 1.5rem; background-color: var(--theme-primary); border-color: rgba(203, 213, 225, 0.3);">
            <p style="color: white; font-size: calc(var(--theme-font-size) - 1px); margin: 0;">
                <i class="fas fa-heart mr-1"></i>Need more help? Contact your administrator or check the system logs.
            </p>
            <button onclick="closeHelpModal()"
                    class="font-medium bg-white transition-all shadow-lg flex-shrink-0"
                    style="padding: 0.75rem 1.5rem; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); color: var(--theme-primary); border: none; cursor: pointer; white-space: nowrap;">
                <i class="fas fa-check mr-2"></i>Got It, Thanks!
            </button>
        </div>
    </div>
</div>

{{-- Create Cost Modal --}}
<div id="createCostModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full" style="max-height: 90vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold" style="color: var(--theme-text);">
                        <i class="fas fa-plus-circle mr-2" style="color: var(--theme-primary);"></i>
                        Add Additional Cost
                    </h3>
                    <button onclick="closeCreateCostModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <form id="createCostForm" class="px-6 py-4">
                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Cost Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="create_name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="e.g., Server Hosting, SSL Certificate">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Description
                        </label>
                        <textarea id="create_description" name="description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Additional details about this cost..."></textarea>
                    </div>

                    {{-- Cost Type --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Cost Type <span class="text-red-500">*</span>
                        </label>
                        <select id="create_cost_type" name="cost_type" required onchange="toggleCreateCostTypeFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="one_time">One-time Cost</option>
                            <option value="monthly_recurring">Monthly Recurring</option>
                        </select>
                    </div>

                    {{-- Fee Type --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Budget Impact <span class="text-red-500">*</span>
                        </label>
                        <select id="create_fee_type" name="fee_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="in_fee">Within Budget (counts toward monthly fee)</option>
                            <option value="additional">Outside Budget (billed to project, not in monthly fee)</option>
                        </select>
                        <p class="text-xs mt-1" style="color: var(--theme-text-muted);">
                            <strong>Within Budget:</strong> Counted in monthly budget tracking<br>
                            <strong>Outside Budget:</strong> Billed to the project but does not count toward monthly fee limit
                        </p>
                    </div>

                    {{-- Calculation Type --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Calculation Type <span class="text-red-500">*</span>
                        </label>
                        <select id="create_calculation_type" name="calculation_type" required onchange="toggleCreateCalculationFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="fixed_amount">Fixed Amount</option>
                            <option value="hourly_rate">Hourly Rate</option>
                        </select>
                    </div>

                    {{-- Fixed Amount (shown by default) --}}
                    <div id="create_fixed_amount_fields">
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Amount (€) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="create_amount" name="amount" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0.00">
                    </div>

                    {{-- Hourly Rate Fields (hidden by default) --}}
                    <div id="create_hourly_rate_fields" class="hidden space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                                Hours <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="create_hours" name="hours" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                                Hourly Rate (€) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="create_hourly_rate" name="hourly_rate" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="create_start_date" name="start_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- End Date (only for monthly_recurring) --}}
                    <div id="create_end_date_field" class="hidden">
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            End Date (optional)
                        </label>
                        <input type="date" id="create_end_date" name="end_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs mt-1" style="color: var(--theme-text-muted);">
                            Leave empty for indefinite recurring cost
                        </p>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Internal Notes
                        </label>
                        <textarea id="create_notes" name="notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Internal notes (not visible on invoices)"></textarea>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeCreateCostModal()"
                            class="px-4 py-2 rounded-lg font-medium transition-colors"
                            style="background-color: #e5e7eb; color: #6b7280;">
                        Cancel
                    </button>
                    <button type="button" onclick="submitCreateCostForm()"
                            class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                            style="background-color: var(--theme-primary);">
                        <i class="fas fa-plus mr-2"></i>
                        Add Cost
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Cost Modal (Read-Only) --}}
<div id="viewCostModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full" style="max-height: 90vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold" style="color: var(--theme-text);">
                        <i class="fas fa-eye mr-2" style="color: #3b82f6;"></i>
                        View Additional Cost
                    </h3>
                    <button onclick="closeViewCostModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-4">
                <div class="space-y-4">
                    {{-- Cost Name --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                            Cost Name
                        </label>
                        <p id="view_name" class="text-base font-medium" style="color: var(--theme-text);"></p>
                    </div>

                    {{-- Description --}}
                    <div id="view_description_wrapper" class="hidden">
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                            Description
                        </label>
                        <p id="view_description" class="text-sm" style="color: var(--theme-text);"></p>
                    </div>

                    {{-- Cost Type & Budget Impact --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                                Cost Type
                            </label>
                            <p id="view_cost_type" class="text-base" style="color: var(--theme-text);"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                                Budget Impact
                            </label>
                            <p id="view_fee_type" class="text-base" style="color: var(--theme-text);"></p>
                        </div>
                    </div>

                    {{-- Amount Details --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                            Amount
                        </label>
                        <p id="view_amount_details" class="text-xl font-bold" style="color: var(--theme-primary);"></p>
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                                Date
                            </label>
                            <p id="view_start_date" class="text-base" style="color: var(--theme-text);"></p>
                        </div>
                        <div id="view_end_date_wrapper" class="hidden">
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                                End Date
                            </label>
                            <p id="view_end_date" class="text-base" style="color: var(--theme-text);"></p>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div id="view_notes_wrapper" class="hidden">
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                            Internal Notes
                        </label>
                        <p id="view_notes" class="text-sm" style="color: var(--theme-text);"></p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeViewCostModal()"
                            class="px-4 py-2 rounded-lg font-medium transition-colors"
                            style="background-color: #e5e7eb; color: #6b7280;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Cost Modal --}}
<div id="editCostModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full" style="max-height: 90vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold" style="color: var(--theme-text);">
                        <i class="fas fa-edit mr-2" style="color: var(--theme-primary);"></i>
                        Edit Additional Cost
                    </h3>
                    <button onclick="closeEditCostModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <form id="editCostForm" class="px-6 py-4">
                <input type="hidden" id="edit_cost_id" name="cost_id">

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Cost Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="e.g., Server Hosting, SSL Certificate">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Description
                        </label>
                        <textarea id="edit_description" name="description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Additional details about this cost..."></textarea>
                    </div>

                    {{-- Cost Type --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Cost Type <span class="text-red-500">*</span>
                        </label>
                        <select id="edit_cost_type" name="cost_type" required onchange="toggleEditCostTypeFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="one_time">One-time Cost</option>
                            <option value="monthly_recurring">Monthly Recurring</option>
                        </select>
                    </div>

                    {{-- Fee Type --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Budget Impact <span class="text-red-500">*</span>
                        </label>
                        <select id="edit_fee_type" name="fee_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="in_fee">Within Budget (counts toward monthly fee)</option>
                            <option value="additional">Outside Budget (billed to project, not in monthly fee)</option>
                        </select>
                        <p class="text-xs mt-1" style="color: var(--theme-text-muted);">
                            <strong>Within Budget:</strong> Counted in monthly budget tracking<br>
                            <strong>Outside Budget:</strong> Billed to the project but does not count toward monthly fee limit
                        </p>
                    </div>

                    {{-- Calculation Type --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Calculation Type <span class="text-red-500">*</span>
                        </label>
                        <select id="edit_calculation_type" name="calculation_type" required onchange="toggleEditCalculationFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="fixed_amount">Fixed Amount</option>
                            <option value="hourly_rate">Hourly Rate</option>
                        </select>
                    </div>

                    {{-- Fixed Amount --}}
                    <div id="edit_fixed_amount_fields">
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Amount (€) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="edit_amount" name="amount" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0.00">
                    </div>

                    {{-- Hourly Rate Fields --}}
                    <div id="edit_hourly_rate_fields" class="hidden space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                                Hours <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="edit_hours" name="hours" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                                Hourly Rate (€) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="edit_hourly_rate" name="hourly_rate" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="edit_start_date" name="start_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    {{-- End Date --}}
                    <div id="edit_end_date_field" class="hidden">
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            End Date (optional)
                        </label>
                        <input type="date" id="edit_end_date" name="end_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs mt-1" style="color: var(--theme-text-muted);">
                            Leave empty for indefinite recurring cost
                        </p>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" style="color: var(--theme-text);">
                            Internal Notes
                        </label>
                        <textarea id="edit_notes" name="notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Internal notes (not visible on invoices)"></textarea>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeEditCostModal()"
                            class="px-4 py-2 rounded-lg font-medium transition-colors"
                            style="background-color: #e5e7eb; color: #6b7280;">
                        Cancel
                    </button>
                    <button type="button" onclick="submitEditCostForm()"
                            class="px-4 py-2 rounded-lg font-medium text-white transition-colors"
                            style="background-color: var(--theme-primary);">
                        <i class="fas fa-save mr-2"></i>
                        Update Cost
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Help modal functions
function openHelpModal() {
    document.getElementById('help-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeHelpModal() {
    document.getElementById('help-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('help-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelpModal();
    }
});

// Close modal with Escape key (alleen help modal)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const helpModal = document.getElementById('help-modal');
        if (helpModal && helpModal.style.display === 'flex') {
            closeHelpModal();
        }
    }
});

// ============================================
// Create Cost Modal Functions
// ============================================

function openCreateCostModal() {
    // Reset form
    document.getElementById('createCostForm').reset();

    // Set default values
    document.getElementById('create_cost_type').value = 'one_time';
    document.getElementById('create_fee_type').value = 'in_fee';
    document.getElementById('create_calculation_type').value = 'fixed_amount';

    // Show correct fields
    toggleCreateCalculationFields();
    toggleCreateCostTypeFields();

    // Show modal
    document.getElementById('createCostModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateCostModal() {
    document.getElementById('createCostModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function toggleCreateCalculationFields() {
    const calculationType = document.getElementById('create_calculation_type').value;

    // Hide all calculation-specific fields first
    document.getElementById('create_fixed_amount_fields').classList.add('hidden');
    document.getElementById('create_hourly_rate_fields').classList.add('hidden');

    // Show relevant fields based on calculation type
    if (calculationType === 'fixed_amount') {
        document.getElementById('create_fixed_amount_fields').classList.remove('hidden');
    } else if (calculationType === 'hourly_rate') {
        document.getElementById('create_hourly_rate_fields').classList.remove('hidden');
    }
}

function toggleCreateCostTypeFields() {
    const costType = document.getElementById('create_cost_type').value;
    const endDateField = document.getElementById('create_end_date_field');

    if (costType === 'monthly_recurring') {
        endDateField.classList.remove('hidden');
    } else {
        endDateField.classList.add('hidden');
    }
}

async function submitCreateCostForm() {
    const form = document.getElementById('createCostForm');
    const projectId = {{ $project->id }};

    // Build form data
    const formData = {
        name: document.getElementById('create_name').value,
        description: document.getElementById('create_description').value,
        cost_type: document.getElementById('create_cost_type').value,
        fee_type: document.getElementById('create_fee_type').value,
        calculation_type: document.getElementById('create_calculation_type').value,
        start_date: document.getElementById('create_start_date').value,
        auto_invoice: 1, // Always automatically include in invoices
        notes: document.getElementById('create_notes').value,
    };

    // Add calculation-specific fields
    const calculationType = formData.calculation_type;
    if (calculationType === 'fixed_amount') {
        formData.amount = document.getElementById('create_amount').value;
    } else if (calculationType === 'hourly_rate') {
        formData.hours = document.getElementById('create_hours').value;
        formData.hourly_rate = document.getElementById('create_hourly_rate').value;
    }

    // Add end_date for monthly_recurring
    if (formData.cost_type === 'monthly_recurring') {
        formData.end_date = document.getElementById('create_end_date').value;
    }

    // Basic validation
    if (!formData.name || !formData.start_date) {
        alert('Please fill in all required fields (marked with *)');
        return;
    }

    try {
        const response = await fetch(`/projects/${projectId}/additional-costs`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            closeCreateCostModal();
            // Refresh page to show new cost
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'Failed to add cost'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while adding the cost');
    }
}

// Close create modal when clicking outside
document.getElementById('createCostModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateCostModal();
    }
});

// ============================================
// VIEW COST MODAL FUNCTIONS
// ============================================

async function openViewCostModal(costId) {
    try {
        const response = await fetch(`/project-additional-costs/${costId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch cost details');
        }

        const result = await response.json();
        const cost = result.cost;

        // Populate view modal fields
        document.getElementById('view_name').textContent = cost.name || '-';

        // Description (conditionally show)
        const descWrapper = document.getElementById('view_description_wrapper');
        if (cost.description && cost.description.trim() !== '') {
            document.getElementById('view_description').textContent = cost.description;
            descWrapper.classList.remove('hidden');
        } else {
            descWrapper.classList.add('hidden');
        }

        // Cost Type
        const costTypeMap = {
            'one_time': 'One-time Cost',
            'monthly_recurring': 'Monthly Recurring'
        };
        document.getElementById('view_cost_type').textContent = costTypeMap[cost.cost_type] || cost.cost_type;

        // Budget Impact
        const feeTypeMap = {
            'in_fee': 'Within Budget (included in monthly fee)',
            'additional': 'Outside Budget (billed to project, not in monthly fee)'
        };
        document.getElementById('view_fee_type').textContent = feeTypeMap[cost.fee_type] || cost.fee_type;

        // Amount Details
        if (cost.calculation_type === 'fixed_amount') {
            document.getElementById('view_amount_details').textContent = '€' + parseFloat(cost.amount || 0).toFixed(2);
        } else if (cost.calculation_type === 'hourly_rate') {
            document.getElementById('view_amount_details').textContent =
                `${cost.hours || 0} hours × €${parseFloat(cost.hourly_rate || 0).toFixed(2)} = €${parseFloat(cost.calculateAmount || cost.amount || 0).toFixed(2)}`;
        }

        // Dates
        document.getElementById('view_start_date').textContent = cost.start_date || '-';

        // End date (conditionally show for recurring)
        const endDateWrapper = document.getElementById('view_end_date_wrapper');
        if (cost.cost_type === 'monthly_recurring' && cost.end_date) {
            document.getElementById('view_end_date').textContent = cost.end_date;
            endDateWrapper.classList.remove('hidden');
        } else {
            endDateWrapper.classList.add('hidden');
        }

        // Notes (conditionally show)
        const notesWrapper = document.getElementById('view_notes_wrapper');
        if (cost.notes && cost.notes.trim() !== '') {
            document.getElementById('view_notes').textContent = cost.notes;
            notesWrapper.classList.remove('hidden');
        } else {
            notesWrapper.classList.add('hidden');
        }

        // Show modal
        document.getElementById('viewCostModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load cost details');
    }
}

function closeViewCostModal() {
    document.getElementById('viewCostModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close view modal when clicking outside
document.getElementById('viewCostModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewCostModal();
    }
});

// ============================================
// EDIT COST MODAL FUNCTIONS
// ============================================

async function openEditCostModal(costId) {
    try {
        const response = await fetch(`/project-additional-costs/${costId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch cost details');
        }

        const result = await response.json();
        const cost = result.cost;

        // Populate edit form fields
        document.getElementById('edit_cost_id').value = cost.id;
        document.getElementById('edit_name').value = cost.name || '';
        document.getElementById('edit_description').value = cost.description || '';
        document.getElementById('edit_cost_type').value = cost.cost_type || 'one_time';
        document.getElementById('edit_fee_type').value = cost.fee_type || 'in_fee';
        document.getElementById('edit_calculation_type').value = cost.calculation_type || 'fixed_amount';
        document.getElementById('edit_start_date').value = cost.start_date || '';
        document.getElementById('edit_end_date').value = cost.end_date || '';
        document.getElementById('edit_notes').value = cost.notes || '';

        // Populate amount fields based on calculation type
        if (cost.calculation_type === 'fixed_amount') {
            document.getElementById('edit_amount').value = cost.amount || '';
        } else if (cost.calculation_type === 'hourly_rate') {
            document.getElementById('edit_hours').value = cost.hours || '';
            document.getElementById('edit_hourly_rate').value = cost.hourly_rate || '';
        }

        // Toggle field visibility
        toggleEditCalculationFields();
        toggleEditCostTypeFields();

        // Show modal
        document.getElementById('editCostModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load cost details');
    }
}

function closeEditCostModal() {
    document.getElementById('editCostForm').reset();
    document.getElementById('editCostModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function toggleEditCalculationFields() {
    const calculationType = document.getElementById('edit_calculation_type').value;
    const fixedAmountFields = document.getElementById('edit_fixed_amount_fields');
    const hourlyRateFields = document.getElementById('edit_hourly_rate_fields');

    if (calculationType === 'fixed_amount') {
        fixedAmountFields.classList.remove('hidden');
        hourlyRateFields.classList.add('hidden');
        // Clear hourly rate fields
        const hoursInput = document.getElementById('edit_hours');
        const rateInput = document.getElementById('edit_hourly_rate');
        if (hoursInput) hoursInput.value = '';
        if (rateInput) rateInput.value = '';
    } else if (calculationType === 'hourly_rate') {
        fixedAmountFields.classList.add('hidden');
        hourlyRateFields.classList.remove('hidden');
        // Clear fixed amount field
        const amountInput = document.getElementById('edit_amount');
        if (amountInput) amountInput.value = '';
    }
}

function toggleEditCostTypeFields() {
    const costType = document.getElementById('edit_cost_type').value;
    const endDateField = document.getElementById('edit_end_date_field');

    if (costType === 'monthly_recurring') {
        endDateField.classList.remove('hidden');
    } else {
        endDateField.classList.add('hidden');
        const endDateInput = document.getElementById('edit_end_date');
        if (endDateInput) endDateInput.value = '';
    }
}

async function submitEditCostForm() {
    const costId = document.getElementById('edit_cost_id').value;
    const calculationType = document.getElementById('edit_calculation_type').value;

    const formData = {
        name: document.getElementById('edit_name').value,
        description: document.getElementById('edit_description').value,
        cost_type: document.getElementById('edit_cost_type').value,
        fee_type: document.getElementById('edit_fee_type').value,
        calculation_type: calculationType,
        start_date: document.getElementById('edit_start_date').value,
        auto_invoice: 1,
        notes: document.getElementById('edit_notes').value,
    };

    // Add calculation-specific fields
    if (calculationType === 'fixed_amount') {
        formData.amount = document.getElementById('edit_amount').value;
    } else if (calculationType === 'hourly_rate') {
        formData.hours = document.getElementById('edit_hours').value;
        formData.hourly_rate = document.getElementById('edit_hourly_rate').value;
    }

    // Add end_date for monthly recurring
    if (formData.cost_type === 'monthly_recurring') {
        formData.end_date = document.getElementById('edit_end_date').value;
    }

    try {
        const response = await fetch(`/project-additional-costs/${costId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            closeEditCostModal();
            // Refresh page to show updated cost
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'Failed to update cost'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating the cost');
    }
}

// Close edit modal when clicking outside
document.getElementById('editCostModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditCostModal();
    }
});

// ============================================
// DELETE COST FUNCTION
// ============================================

async function deleteCost(costId) {
    if (!confirm('Are you sure you want to delete this additional cost? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/project-additional-costs/${costId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (result.success) {
            // Refresh page to remove deleted cost
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'Failed to delete cost'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the cost');
    }
}

// ============================================
// RECURRING SERIES MODAL FUNCTIONS
// ============================================

function openRecurringSeriesModal() {
    console.log('Opening recurring series modal...');
    document.getElementById('recurringSeriesModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRecurringSeriesModal() {
    console.log('Closing recurring series modal...');
    document.getElementById('recurringSeriesModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Handle standalone project series ID selection - INLINE DEFINITION
window.handleStandaloneSeriesSelection = function(value) {
    console.log('✅ handleStandaloneSeriesSelection called with value:', value);

    const customInput = document.getElementById('standalone_series_custom');

    if (!customInput) {
        console.error('❌ ERROR: Element with ID "standalone_series_custom" not found!');
        alert('Error: Input field not found. Please refresh the page and try again.');
        return;
    }

    console.log('✅ Custom input element found:', customInput);

    if (value === '_custom') {
        // Show custom input field with animation
        console.log('👁️ Showing custom input field...');
        customInput.style.display = 'block';
        customInput.style.marginTop = '1rem';

        // Slight delay to ensure display is rendered before focus
        setTimeout(() => {
            customInput.focus();
            customInput.value = ''; // Clear for new custom entry
            console.log('✅ Custom input field is now visible and focused');
        }, 50);
    } else {
        // Hide custom input field
        console.log('🙈 Hiding custom input field...');
        customInput.style.display = 'none';

        if (value) {
            // Set selected series ID
            customInput.value = value;
            console.log('✅ Set custom input value to:', value);
        } else {
            // Clear value (remove from series)
            customInput.value = '';
            console.log('🗑️ Cleared custom input value');
        }
    }
};

console.log('✅ Recurring Series modal functions are READY!');
</script>
@endpush

{{-- ============================================ --}}
{{-- RECURRING SERIES MODAL (voor niet-recurring projects) --}}
{{-- ============================================ --}}
@if(!$project->is_recurring && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
<div id="recurringSeriesModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            {{-- Modal Header --}}
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.5rem; background: linear-gradient(135deg, rgba(var(--theme-primary-rgb), 0.05) 0%, rgba(var(--theme-primary-rgb), 0.02) 100%);">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 600; color: var(--theme-text); margin: 0;">
                            <i class="fas fa-layer-group mr-2" style="color: var(--theme-primary);"></i>
                            Add to Recurring Series
                        </h3>
                        @if($project->recurring_series_id)
                            <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">
                                Current series: <strong style="color: var(--theme-primary);">{{ $project->recurring_series_id }}</strong>
                            </p>
                        @endif
                    </div>
                    <button onclick="closeRecurringSeriesModal()"
                            class="text-gray-400 hover:text-gray-500 transition-colors"
                            style="font-size: 1.5rem; line-height: 1; padding: 0.25rem; border: none; background: none; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div style="padding: 1.5rem;">
                {{-- Master Template Toggle (alleen als project in series zit) --}}
                @if($project->recurring_series_id)
                <div style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); margin-bottom: 1rem;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-crown" style="color: var(--theme-accent); font-size: calc(var(--theme-font-size) + 6px);"></i>
                            </div>
                            <div class="ml-3">
                                <h4 style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text);">Master Template for Series</h4>
                                <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                    @if($project->is_master_template)
                                        ✅ This project is the master template for <strong>{{ $project->recurring_series_id }}</strong>
                                    @else
                                        This project is part of series: <strong>{{ $project->recurring_series_id }}</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('projects.toggleMasterTemplate', $project) }}" method="POST" onsubmit="return confirmMasterTemplateToggle({{ $project->is_master_template ? 'false' : 'true' }})">
                            @csrf
                            @if($project->is_master_template)
                                <button type="submit" class="inline-flex items-center transition-colors"
                                        style="padding: 0.5rem 1rem; background-color: rgba(var(--theme-danger-rgb), 0.1); border: 1px solid rgba(var(--theme-danger-rgb), 0.3); border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-danger); cursor: pointer;">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    Remove Master Status
                                </button>
                            @else
                                <button type="submit" class="inline-flex items-center transition-colors"
                                        style="padding: 0.5rem 1rem; background-color: var(--theme-accent); border: 1px solid transparent; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: white; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
                                    <i class="fas fa-arrow-up mr-2"></i>
                                    Upgrade to Master Template
                                </button>
                            @endif
                        </form>
                    </div>
                    @if(!$project->is_master_template)
                    <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(var(--theme-accent-rgb), 0.2);">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                            <strong>💡 Tip:</strong> Make this the master template to use it for auto-generating future projects in this series with clean, general structure (no month-specific tasks).
                        </p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Info Box --}}
                <div style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.2); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding); margin-bottom: 1rem;">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle" style="color: var(--theme-primary);"></i>
                        </div>
                        <div class="ml-3">
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                                Group this project with other projects for consolidated budget tracking. Select an existing series or create a new one.
                            </p>
                        </div>
                    </div>
                </div>

                @php
                    $existingSeriesForStandalone = \App\Models\Project::whereNotNull('recurring_series_id')
                        ->select('recurring_series_id', DB::raw('COUNT(*) as project_count'))
                        ->groupBy('recurring_series_id')
                        ->orderBy('recurring_series_id')
                        ->get();
                @endphp

                {{-- Series Form --}}
                <form action="{{ route('projects.updateSeriesId', $project) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); display: block; margin-bottom: 0.5rem;">
                            Recurring Series ID:
                        </label>
                        <select id="standalone_series_select"
                                onchange="handleStandaloneSeriesSelection(this.value)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="font-size: var(--theme-font-size);">
                            <option value="">Remove from series</option>
                            <option value="_custom">Create new custom series ID</option>
                            @if($existingSeriesForStandalone->count() > 0)
                                <optgroup label="Existing Series">
                                    @foreach($existingSeriesForStandalone as $seriesId)
                                    <option value="{{ $seriesId->recurring_series_id }}"
                                            {{ $project->recurring_series_id == $seriesId->recurring_series_id ? 'selected' : '' }}>
                                        {{ $seriesId->recurring_series_id }}
                                        ({{ $seriesId->project_count }} {{ $seriesId->project_count == 1 ? 'project' : 'projects' }})
                                    </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>

                    {{-- Custom Series ID Input (initially hidden) --}}
                    <input type="text"
                           name="recurring_series_id"
                           id="standalone_series_custom"
                           value="{{ $project->recurring_series_id }}"
                           style="display: none; width: 100%; padding: 0.5rem 0.75rem; margin-top: 0.75rem; border: 2px solid var(--theme-primary); border-radius: var(--theme-border-radius); background-color: rgba(var(--theme-primary-rgb), 0.05); font-size: var(--theme-font-size);"
                           placeholder="e.g., anker-solix-recurring">

                    {{-- Action Buttons --}}
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button"
                                onclick="closeRecurringSeriesModal()"
                                class="px-4 py-2 rounded transition-colors"
                                style="background-color: #e5e7eb; color: #6b7280; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="submit"
                                class="inline-flex items-center transition-colors"
                                style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); font-weight: 500; border: none; border-radius: var(--theme-border-radius); cursor: pointer;">
                            <i class="fas fa-save mr-2"></i>
                            Update Series
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
