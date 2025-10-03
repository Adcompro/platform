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
                                            @if($milestone->tasks->count() > 0)
                                            <span style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                                {{ $milestone->tasks->count() }} {{ $milestone->tasks->count() === 1 ? 'task' : 'tasks' }}
                                            </span>
                                            @endif
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
                            <a href="{{ route('time-entries.index') }}?project_id={{ $project->id }}&return_to_project={{ $project->id }}"
                               class="flex items-center p-3 rounded-lg transition-colors"
                               style="background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.1); text-decoration: none;">
                                <i class="fas fa-clock mr-3" style="color: var(--theme-primary); font-size: var(--theme-font-size);"></i>
                                <div>
                                    <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Time Entries</div>
                                    <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">View and manage time tracking</div>
                                </div>
                            </a>
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
@endsection
