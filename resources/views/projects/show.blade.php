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
                    {{-- Year Budget Overview Link --}}
                    @if($project->monthly_fee && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('projects.year-budget', $project->id) }}"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size); background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fas fa-calendar-alt mr-1.5"></i>
                        Year Budget
                    </a>
                    @endif

                    <a href="{{ route('projects.index') }}"
                       id="header-back-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-arrow-left mr-1.5"></i>
                        Back to Projects
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="padding: 1.5rem 2rem;">

        {{-- Project Status Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-6">
            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                    {{ $project->milestones->count() }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                    Milestones
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-accent-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-accent);">
                    {{ $project->milestones->sum(function($m) { return $m->tasks->count(); }) }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-accent);">
                    Tasks
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-success-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);">
                    {{ $project->milestones->where('status', 'completed')->count() }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-success);">
                    Completed
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-warning-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-warning);">
                    {{ $project->milestones->where('status', 'in_progress')->count() }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-warning);">
                    In Progress
                </div>
            </div>
        </div>

        {{-- Two-column layout --}}
        <div class="projects-two-column" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">

            {{-- Left Column: Project Information & Structure --}}
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
                <div style="padding: var(--theme-card-padding);">
                    <form id="project-form">
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            {{-- Left column: Basic & Timeline info --}}
                            <div style="padding-right: 2rem;">
                                <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Project Details</h3>
                                <dl class="space-y-2">
                                    {{-- Name --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Name:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500; text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->name }}</dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <input type="text" name="name" value="{{ $project->name }}" required
                                                   class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                   style="font-size: var(--theme-font-size);">
                                        </dd>
                                    </div>

                                    {{-- Customer --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Customer:</dt>
                                        <dd class="field-view" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            @if($project->customer)
                                                <a href="{{ route('customers.show', $project->customer) }}?from=project&project_id={{ $project->id }}"
                                                   style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                    {{ $project->customer->name }}
                                                </a>
                                            @else
                                                <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No customer</span>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <select name="customer_id" class="w-full px-2 py-1 border border-gray-300 rounded text-right" style="font-size: var(--theme-font-size);">
                                                <option value="">No customer</option>
                                                @foreach(\App\Models\Customer::where('status', 'active')->orderBy('name')->get() as $customer)
                                                    <option value="{{ $customer->id }}" {{ $project->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        </dd>
                                    </div>

                                    {{-- Status --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Status:</dt>
                                        <dd class="field-view" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <span class="px-2 py-1 rounded-full" style="
                                                font-size: calc(var(--theme-font-size) - 2px); font-weight: 500;
                                                {{ $project->status === 'completed' ? 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);' :
                                                ($project->status === 'in_progress' || $project->status === 'active' ? 'background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);' :
                                                ($project->status === 'on_hold' ? 'background-color: rgba(var(--theme-warning-rgb), 0.1); color: var(--theme-warning);' :
                                                ($project->status === 'draft' ? 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);' : 'background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);'))) }}">
                                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                            </span>
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <select name="status" required class="w-full px-2 py-1 border border-gray-300 rounded text-right" style="font-size: var(--theme-font-size);">
                                                <option value="draft" {{ $project->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="on_hold" {{ $project->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                                <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $project->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </dd>
                                    </div>

                                    {{-- Start Date --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Start Date:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M j, Y') : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <input type="date" name="start_date" value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}"
                                                   class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                   style="font-size: var(--theme-font-size);">
                                        </dd>
                                    </div>

                                    {{-- End Date --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">End Date:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                            {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M j, Y') : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <input type="date" name="end_date" value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}"
                                                   class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                   style="font-size: var(--theme-font-size);">
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Right column: Financial & Settings --}}
                            <div style="padding-left: 2rem;">
                                <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Financial & Settings</h3>
                                <dl class="space-y-2">
                                    {{-- Monthly Fee --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Monthly Fee:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                            @if($project->monthly_fee)
                                                <span style="font-weight: 500;">€{{ number_format($project->monthly_fee, 2) }}</span>
                                            @else
                                                <em style="color: var(--theme-text-muted);">Not set</em>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <input type="number" step="0.01" name="monthly_fee" value="{{ $project->monthly_fee }}" placeholder="0.00"
                                                   class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                   style="font-size: var(--theme-font-size);">
                                        </dd>
                                    </div>

                                    {{-- Hourly Rate --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Hourly Rate:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                            @if($project->default_hourly_rate)
                                                <span style="font-weight: 500;">€{{ number_format($project->default_hourly_rate, 2) }}/hr</span>
                                            @else
                                                <em style="color: var(--theme-text-muted);">Not set</em>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <input type="number" step="0.01" name="default_hourly_rate" value="{{ $project->default_hourly_rate }}" placeholder="0.00"
                                                   class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                   style="font-size: var(--theme-font-size);">
                                        </dd>
                                    </div>

                                    {{-- Time Costs (calculated, not editable) --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Time Costs:</dt>
                                        <dd style="text-align: right; flex: 1; margin-left: 0.5rem;">
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
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">VAT Rate:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                            {{ $project->vat_rate ? number_format($project->vat_rate, 2) . '%' : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <input type="number" step="0.01" name="vat_rate" value="{{ $project->vat_rate }}" placeholder="21.00"
                                                   class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                   style="font-size: var(--theme-font-size);">
                                        </dd>
                                    </div>

                                    {{-- Billing Frequency --}}
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Billing Freq:</dt>
                                        <dd class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                            {{ $project->billing_frequency ? ucfirst(str_replace('_', ' ', $project->billing_frequency)) : 'Not set' }}
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <select name="billing_frequency" class="w-full px-2 py-1 border border-gray-300 rounded text-right" style="font-size: var(--theme-font-size);">
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
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Fee Rollover:</dt>
                                        <dd class="field-view" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            @if($project->fee_rollover_enabled)
                                                <span style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-success); background-color: rgba(var(--theme-success-rgb), 0.1); padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                                    <i class="fas fa-check-circle mr-1"></i>Enabled
                                                </span>
                                            @else
                                                <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Disabled</span>
                                            @endif
                                        </dd>
                                        <dd class="field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <label class="flex items-center justify-end">
                                                <input type="checkbox" name="fee_rollover_enabled" value="1" {{ $project->fee_rollover_enabled ? 'checked' : '' }} class="mr-2">
                                                <span style="font-size: calc(var(--theme-font-size) - 1px);">Enable</span>
                                            </label>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Description and Notes --}}
                            <div class="col-span-2" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(203, 213, 225, 0.3);">
                                {{-- Description --}}
                                <div class="mb-4">
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Description</h3>
                                    <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5; white-space: pre-wrap;">{{ $project->description ?: 'No description' }}</div>
                                    <div class="field-edit hidden">
                                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded" style="font-size: var(--theme-font-size);">{{ $project->description }}</textarea>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Notes</h3>
                                    <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5; white-space: pre-wrap; font-style: italic;">{{ $project->notes ?: 'No notes' }}</div>
                                    <div class="field-edit hidden">
                                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded" style="font-size: var(--theme-font-size); font-style: italic;">{{ $project->notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

                {{-- Project Structure: Milestones & Tasks --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Project Structure</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a href="{{ route('projects.edit', $project) }}"
                           class="inline-flex items-center px-2 py-1 rounded transition-colors"
                           style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </a>
                        @endif
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        @if($project->milestones->count() > 0)
                            <div class="space-y-4">
                                @foreach($project->milestones as $milestone)
                                <div class="border rounded-lg" style="border-color: rgba(203, 213, 225, 0.4);">
                                    {{-- Milestone Header --}}
                                    <div class="p-3" style="background-color: rgba(var(--theme-primary-rgb), 0.03); border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-flag" style="color: var(--theme-primary); font-size: var(--theme-font-size);"></i>
                                                <span style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">{{ $milestone->name }}</span>
                                                <span class="px-2 py-0.5 rounded text-xs" style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 2px);">
                                                    {{ ucfirst($milestone->status) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                @php
                                                    $milestoneLoggedHours = $milestone->total_logged_hours;
                                                    $milestoneLoggedFormatted = $milestone->formatted_logged_hours;
                                                    $milestoneEstimatedHours = $milestone->estimated_hours ?? 0;
                                                    $milestoneIsOver = $milestoneEstimatedHours > 0 && $milestoneLoggedHours > $milestoneEstimatedHours;
                                                    $milestoneIsNear = $milestoneEstimatedHours > 0 && $milestoneLoggedHours >= ($milestoneEstimatedHours * 0.8) && !$milestoneIsOver;
                                                    $milestoneColor = $milestoneIsOver ? '#ef4444' : ($milestoneIsNear ? '#f59e0b' : '#10b981');
                                                @endphp
                                                @if($milestoneLoggedHours > 0 || $milestoneEstimatedHours > 0)
                                                <div class="flex items-center gap-2" style="font-size: calc(var(--theme-font-size) - 1px);">
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
                                                <span style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                                    {{ $milestone->tasks->count() }} {{ $milestone->tasks->count() === 1 ? 'task' : 'tasks' }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($milestone->description)
                                        <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); margin-top: 0.5rem;">
                                            {{ $milestone->description }}
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Tasks --}}
                                    @if($milestone->tasks->count() > 0)
                                    <div class="p-3">
                                        <div class="space-y-2">
                                            @foreach($milestone->tasks as $task)
                                            <div class="flex items-start gap-2 p-2 rounded" style="background-color: rgba(248, 250, 252, 0.5);">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <i class="fas {{ $task->status === 'completed' ? 'fa-check-circle' : 'fa-circle' }}"
                                                       style="color: {{ $task->status === 'completed' ? 'var(--theme-success)' : 'var(--theme-text-muted)' }}; font-size: var(--theme-font-size);"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <div style="font-size: var(--theme-font-size); color: var(--theme-text); {{ $task->status === 'completed' ? 'text-decoration: line-through; opacity: 0.7;' : '' }}">
                                                        {{ $task->name }}
                                                    </div>
                                                    @if($task->description)
                                                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); margin-top: 0.25rem;">
                                                        {{ Str::limit($task->description, 100) }}
                                                    </div>
                                                    @endif
                                                    <div class="flex items-center gap-3 mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
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
                                                        <span class="px-1.5 py-0.5 rounded" style="background-color: rgba(var(--theme-text-muted-rgb), 0.1);">
                                                            {{ ucfirst($task->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div class="p-3 text-center" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
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
            {{-- Recurring Project Information Card --}}
            @if($project->is_recurring || $project->parent_recurring_project_id)
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                    <div class="flex items-center justify-between">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">
                            @if($project->is_recurring)
                            <i class="fas fa-sync-alt mr-2" style="color: rgb(139, 92, 246);"></i>
                            Recurring Project Settings
                            @else
                            <i class="fas fa-robot mr-2" style="color: rgb(59, 130, 246);"></i>
                            Auto-Generated Project
                            @endif
                        </h2>
                        @if($project->is_recurring && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <button onclick="openRecurringSettingsModal()"
                                class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                            <i class="fas fa-edit mr-1"></i>
                            Edit Settings
                        </button>
                        @endif
                    </div>
                </div>
                <div style="padding: var(--theme-card-padding);">
                    @if($project->is_recurring)
                        {{-- Master Recurring Project Info --}}
                        <div class="space-y-3">
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-purple-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-purple-700">
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
                                    <div class="flex items-center justify-between p-2 rounded hover:bg-gray-50">
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
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
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


            {{-- Add to Recurring Series (for any non-recurring project) --}}
            @if(!$project->is_recurring && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">
                            <i class="fas fa-layer-group mr-2" style="color: rgb(59, 130, 246);"></i>
                            Add to Recurring Series
                        </h2>
                        @if($project->recurring_series_id)
                            <p class="text-xs mt-1" style="color: var(--theme-text-muted);">
                                Current series: <strong style="color: var(--theme-primary);">{{ $project->recurring_series_id }}</strong>
                            </p>
                        @endif
                    </div>
                </div>
                <div style="padding: var(--theme-card-padding);">
                    {{-- Master Template Toggle (alleen als project in series zit) --}}
                    @if($project->recurring_series_id)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-crown text-purple-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-purple-900">Master Template for Series</h4>
                                    <p class="text-xs text-purple-700 mt-1">
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
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-100 border border-red-300 rounded-lg text-sm font-medium text-red-700 hover:bg-red-200 transition-colors">
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Remove Master Status
                                    </button>
                                @else
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-purple-700 transition-colors shadow-sm">
                                        <i class="fas fa-arrow-up mr-2"></i>
                                        Upgrade to Master Template
                                    </button>
                                @endif
                            </form>
                        </div>
                        @if(!$project->is_master_template)
                        <div class="mt-3 pt-3 border-t border-purple-200">
                            <p class="text-xs text-purple-600">
                                <strong>💡 Tip:</strong> Make this the master template to use it for auto-generating future projects in this series with clean, general structure (no month-specific tasks).
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
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

                        <input type="text"
                               name="recurring_series_id"
                               id="standalone_series_custom"
                               value="{{ $project->recurring_series_id }}"
                               style="display: none;"
                               class="w-full px-3 py-2 mt-3 border-2 border-blue-400 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50"
                               placeholder="e.g., anker-solix-recurring">

                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 rounded-lg transition-colors"
                                style="background-color: rgb(59, 130, 246); color: white; font-size: var(--theme-font-size); font-weight: 500;"
                                onmouseover="this.style.backgroundColor='rgb(37, 99, 235)'"
                                onmouseout="this.style.backgroundColor='rgb(59, 130, 246)'">
                            <i class="fas fa-save mr-2"></i>
                            Update Series
                        </button>
                    </form>

                    {{-- INLINE JavaScript: Must be here to be available when form loads --}}
                    <script>
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

                    console.log('✅ handleStandaloneSeriesSelection function is READY!');
                    </script>
                </div>
            </div>
            @endif

            </div> {{-- End Left Column --}}

            {{-- Right Column: Customer, Team & Structure --}}
            <div class="space-y-4">

                {{-- Customer Information --}}
                @if($project->customer)
                <div id="customer-info-block" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Customer Information</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <button onclick="toggleCustomerEdit()"
                                id="edit-customer-btn"
                                class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </button>
                        <div id="edit-customer-actions" class="hidden flex items-center gap-2">
                            <button onclick="saveCustomerEdit()"
                                    class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                    style="background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                                <i class="fas fa-check mr-1"></i>
                                Save
                            </button>
                            <button onclick="cancelCustomerEdit()"
                                    class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                    style="background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                                <i class="fas fa-times mr-1"></i>
                                Cancel
                            </button>
                        </div>
                        @endif
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        <form id="customer-form">
                            <div class="grid grid-cols-1 md:grid-cols-2">
                                <div style="padding-right: 2rem;">
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Primary Contact</h3>
                                    <dl class="space-y-2">
                                        {{-- Name --}}
                                        <div class="flex justify-between items-start">
                                            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Name:</dt>
                                            <dd class="customer-field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500; text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->customer->name }}</dd>
                                            <dd class="customer-field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                <input type="text" name="name" value="{{ $project->customer->name }}" required
                                                       class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                       style="font-size: var(--theme-font-size);">
                                            </dd>
                                        </div>

                                        {{-- Email --}}
                                        <div class="flex justify-between items-start">
                                            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Email:</dt>
                                            <dd class="customer-field-view" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                @if($project->customer->email)
                                                    <a href="mailto:{{ $project->customer->email }}"
                                                       style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                        {{ $project->customer->email }}
                                                    </a>
                                                @else
                                                    <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Not set</span>
                                                @endif
                                            </dd>
                                            <dd class="customer-field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                <input type="email" name="email" value="{{ $project->customer->email }}" placeholder="email@example.com"
                                                       class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                       style="font-size: var(--theme-font-size);">
                                            </dd>
                                        </div>

                                        {{-- Phone --}}
                                        <div class="flex justify-between items-start">
                                            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Phone:</dt>
                                            <dd class="customer-field-view" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                @if($project->customer->phone)
                                                    <a href="tel:{{ $project->customer->phone }}"
                                                       style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                        {{ $project->customer->phone }}
                                                    </a>
                                                @else
                                                    <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Not set</span>
                                                @endif
                                            </dd>
                                            <dd class="customer-field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                <input type="tel" name="phone" value="{{ $project->customer->phone }}" placeholder="+31 6 12345678"
                                                       class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                       style="font-size: var(--theme-font-size);">
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div style="padding-left: 2rem;">
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Address</h3>
                                    <dl class="space-y-2">
                                        {{-- Street --}}
                                        <div class="flex justify-between items-start">
                                            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Street:</dt>
                                            <dd class="customer-field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                                {{ $project->customer->street ?: 'Not set' }}
                                            </dd>
                                            <dd class="customer-field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                <input type="text" name="street" value="{{ $project->customer->street }}" placeholder="Street name and number"
                                                       class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                       style="font-size: var(--theme-font-size);">
                                            </dd>
                                        </div>

                                        {{-- Addition (optional) --}}
                                        @if($project->customer->addition)
                                        <div class="flex justify-between items-start">
                                            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Addition:</dt>
                                            <dd class="customer-field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                                {{ $project->customer->addition }}
                                            </dd>
                                            <dd class="customer-field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                <input type="text" name="addition" value="{{ $project->customer->addition }}" placeholder="e.g., 2nd floor, rear"
                                                       class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                       style="font-size: var(--theme-font-size);">
                                            </dd>
                                        </div>
                                        @endif

                                        {{-- Zip & City --}}
                                        <div class="flex justify-between items-start">
                                            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">City:</dt>
                                            <dd class="customer-field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                                {{ $project->customer->zip_code ? $project->customer->zip_code . ' ' : '' }}{{ $project->customer->city ?: 'Not set' }}
                                            </dd>
                                            <dd class="customer-field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                <div class="flex gap-1">
                                                    <input type="text" name="zip_code" value="{{ $project->customer->zip_code }}" placeholder="1234 AB"
                                                           class="w-24 px-2 py-1 border border-gray-300 rounded text-right"
                                                           style="font-size: var(--theme-font-size);">
                                                    <input type="text" name="city" value="{{ $project->customer->city }}" placeholder="City"
                                                           class="flex-1 px-2 py-1 border border-gray-300 rounded text-right"
                                                           style="font-size: var(--theme-font-size);">
                                                </div>
                                            </dd>
                                        </div>

                                        {{-- Country --}}
                                        <div class="flex justify-between items-start">
                                            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Country:</dt>
                                            <dd class="customer-field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                                {{ $project->customer->country ?: 'Not set' }}
                                            </dd>
                                            <dd class="customer-field-edit hidden" style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                                <input type="text" name="country" value="{{ $project->customer->country }}" placeholder="Netherlands"
                                                       class="w-full px-2 py-1 border border-gray-300 rounded text-right"
                                                       style="font-size: var(--theme-font-size);">
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Customer Contact --}}
                @if($project->customer && $project->customer->contacts->isNotEmpty())
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Customer Contact</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a href="{{ route('contacts.index', ['customer_id' => $project->customer_id, 'return_to_project' => $project->id]) }}"
                           class="inline-flex items-center px-2 py-1 rounded transition-colors"
                           style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                            <i class="fas fa-users-cog mr-1"></i>
                            Manage
                        </a>
                        @endif
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        <div class="space-y-3">
                            @foreach($project->customer->contacts as $contact)
                            <div class="flex items-start" style="padding: 0.75rem; background-color: rgba(248, 250, 252, 0.5); border-radius: 0.5rem; border: 1px solid rgba(226, 232, 240, 0.8);">
                                <div class="flex-shrink-0" style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background: linear-gradient(135deg, var(--theme-primary) 0%, rgba(var(--theme-primary-rgb), 0.7) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: calc(var(--theme-font-size) + 2px);">
                                    {{ strtoupper(substr($contact->name, 0, 1)) }}
                                </div>
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">{{ $contact->name }}</span>
                                        @if($contact->is_primary)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: rgba(34, 197, 94, 0.1); color: #16a34a; font-size: calc(var(--theme-font-size) - 2px);">
                                            <i class="fas fa-star mr-1" style="font-size: calc(var(--theme-font-size) - 3px);"></i>
                                            Primary
                                        </span>
                                        @endif
                                    </div>
                                    @if($contact->position)
                                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); margin-top: 0.25rem;">
                                        <i class="fas fa-briefcase mr-1" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                        {{ $contact->position }}
                                    </div>
                                    @endif
                                    <div class="flex flex-wrap gap-3 mt-2">
                                        @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="inline-flex items-center hover:underline" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);">
                                            <i class="fas fa-envelope mr-1" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                            {{ $contact->email }}
                                        </a>
                                        @endif
                                        @if($contact->phone)
                                        <a href="tel:{{ $contact->phone }}" class="inline-flex items-center hover:underline" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);">
                                            <i class="fas fa-phone mr-1" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                            {{ $contact->phone }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Team & Companies --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Team & Companies</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <button onclick="openTeamModal()"
                           class="inline-flex items-center px-2 py-1 rounded transition-colors"
                           style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
                            <i class="fas fa-edit mr-1"></i>
                            Manage
                        </button>
                        @endif
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        {{-- Companies --}}
                        @if($project->companies->count() > 0)
                        <div class="mb-6">
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Associated Companies</h3>
                            <div class="space-y-2">
                                @foreach($project->companies as $company)
                                <div class="flex items-center justify-between p-3 rounded-lg" style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.1);">
                                    <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $company->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Team Members --}}
                        @if($project->users->count() > 0)
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Team Members</h3>
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
                        </div>
                        @endif

                        {{-- No team/companies message --}}
                        @if($project->companies->count() === 0 && $project->users->count() === 0)
                        <div class="text-center py-8" style="color: var(--theme-text-muted);">
                            <i class="fas fa-users" style="font-size: calc(var(--theme-font-size) + 12px); margin-bottom: 0.5rem;"></i>
                            <p style="font-size: var(--theme-font-size);">No team members or companies assigned yet</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Quick Actions</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        <div class="grid grid-cols-1 gap-3">
                            <button onclick="openTimeEntriesModal()"
                                    class="flex items-center p-3 rounded-lg transition-colors text-left w-full"
                                    style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.1); cursor: pointer;">
                                <i class="fas fa-clock mr-3" style="color: var(--theme-primary); font-size: var(--theme-font-size);"></i>
                                <div>
                                    <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Time Entries</div>
                                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">View and manage time tracking</div>
                                </div>
                            </button>
                            @if($project->customer)
                            <a href="{{ route('customers.show', $project->customer) }}?from=project&project_id={{ $project->id }}"
                               class="flex items-center p-3 rounded-lg transition-colors"
                               style="background-color: rgba(var(--theme-success-rgb), 0.05); border: 1px solid rgba(var(--theme-success-rgb), 0.1); text-decoration: none;">
                                <i class="fas fa-user mr-3" style="color: var(--theme-success); font-size: var(--theme-font-size);"></i>
                                <div>
                                    <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">View Customer</div>
                                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">{{ $project->customer->name }}</div>
                                </div>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Project Activity Timeline --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('projects.partials.activity-timeline', ['activities' => $activities])
</div>

{{-- Time Entries Modal --}}
<div id="timeEntriesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="if(event.target === this) closeTimeEntriesModal()">
    <div class="bg-white rounded-xl shadow-2xl" style="width: 95%; max-width: 1200px; max-height: 85vh; overflow-y: auto;">
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
            <div class="grid grid-cols-3 gap-4">
                <div class="p-3 rounded-lg" style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.1);">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.25rem;">Total Entries</div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 600; color: var(--theme-primary);" id="stat-total-entries">-</div>
                </div>
                <div class="p-3 rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.05); border: 1px solid rgba(var(--theme-success-rgb), 0.1);">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.25rem;">Total Hours</div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 600; color: var(--theme-success);" id="stat-total-hours">-</div>
                </div>
                <div class="p-3 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.1);">
                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.25rem;">Duration</div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 600; color: var(--theme-accent);" id="stat-total-duration">-</div>
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
            <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">
                <i class="fas fa-users mr-2"></i>Hours per User
            </h4>
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
    <div class="bg-white rounded-xl shadow-2xl" style="width: 95%; max-width: 900px; max-height: 85vh; overflow-y: auto;">
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
    <div class="bg-white rounded-xl shadow-2xl" style="width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
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
            data[key] = value;
        }
    });

    // Handle checkbox separately (it won't be in formData if unchecked)
    const feeRolloverCheckbox = form.querySelector('input[name="fee_rollover_enabled"]');
    if (feeRolloverCheckbox) {
        data.fee_rollover_enabled = feeRolloverCheckbox.checked ? 1 : 0;
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

// Customer Edit Functions
let isEditingCustomer = false;

function toggleCustomerEdit() {
    isEditingCustomer = true;
    document.getElementById('edit-customer-btn').classList.add('hidden');
    document.getElementById('edit-customer-actions').classList.remove('hidden');

    // Hide all customer-field-view, show all customer-field-edit
    document.querySelectorAll('.customer-field-view').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.customer-field-edit').forEach(el => el.classList.remove('hidden'));
}

function cancelCustomerEdit() {
    isEditingCustomer = false;
    document.getElementById('edit-customer-btn').classList.remove('hidden');
    document.getElementById('edit-customer-actions').classList.add('hidden');

    // Show all customer-field-view, hide all customer-field-edit
    document.querySelectorAll('.customer-field-view').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('.customer-field-edit').forEach(el => el.classList.add('hidden'));

    // Reset form
    document.getElementById('customer-form').reset();
}

function saveCustomerEdit() {
    const form = document.getElementById('customer-form');
    const formData = new FormData(form);

    // Convert to JSON
    const data = {};
    formData.forEach((value, key) => {
        if (value !== '') {
            data[key] = value;
        }
    });

    // Show loading
    const saveBtn = event.target;
    const originalHTML = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
    saveBtn.disabled = true;

    fetch('{{ route("customers.update-inline", $project->customer) }}', {
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
            showSuccessMessage(data.message || 'Customer updated successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorMessage(data.message || 'Failed to update customer');
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

function renderTimeEntries(entries, stats, userStats) {
    // Hide loading
    document.getElementById('time-entries-loading').classList.add('hidden');

    // Update subtitle
    document.getElementById('time-entries-subtitle').textContent =
        `${stats.total_entries} ${stats.total_entries === 1 ? 'entry' : 'entries'} found`;

    // Show and update stats
    document.getElementById('time-entries-stats').style.display = 'block';
    document.getElementById('stat-total-entries').textContent = stats.total_entries;
    document.getElementById('stat-total-hours').textContent = stats.total_hours + 'h';
    document.getElementById('stat-total-duration').textContent = stats.total_duration_formatted;

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
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4">
        <form id="recurringSettingsForm" action="{{ route('projects.updateRecurringSettings', $project) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-sync-alt mr-2 text-purple-500"></i>
                    Edit Recurring Settings
                </h3>
            </div>

            <div class="px-6 py-4 space-y-4">
                {{-- Base Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Base Project Name <span class="text-red-500">*</span>
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
                            Frequency <span class="text-red-500">*</span>
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
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <input type="checkbox"
                               name="disable_recurring"
                               id="disable_recurring"
                               value="1"
                               class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <div class="ml-3">
                            <label for="disable_recurring" class="text-sm font-medium text-red-900">
                                Disable Recurring
                            </label>
                            <p class="text-xs text-red-700 mt-1">
                                Check this to stop automatic project generation. Existing generated projects will not be affected.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
                <button type="button"
                        onclick="closeRecurringSettingsModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
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

@endsection
