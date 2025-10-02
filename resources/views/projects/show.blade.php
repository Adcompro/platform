@extends('layouts.app')

@section('title', 'Project Details - ' . $project->name)

@section('content')
<div class="min-h-screen" style="background-color: var(--theme-bg);">
    {{-- Sticky Header - Theme Settings Style --}}
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
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('projects.edit', $project) }}"
                       id="header-edit-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-edit mr-1.5"></i>
                        Edit Project
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

    {{-- Main Content - Theme Settings Style --}}
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

        {{-- Two-column layout with CSS Grid --}}
        <div class="projects-two-column" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">

            {{-- Left Column: Project Information --}}
            <div id="project-info-block" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center;">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Project Information</h2>
                </div>
                <div style="padding: var(--theme-card-padding);">
                    {{-- Two-column layout for project info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        {{-- Left column: Basic & Timeline info --}}
                        <div style="padding-right: 2rem;">
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Project Details</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Name:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500; text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->name }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Customer:</dt>
                                    <dd style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                        @if($project->customer)
                                            <a href="{{ route('customers.show', $project->customer) }}?from=project&project_id={{ $project->id }}"
                                               style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                {{ $project->customer->name }}
                                            </a>
                                        @else
                                            <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No customer assigned</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Status:</dt>
                                    <dd style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                        <span class="px-2 py-1 rounded-full" style="
                                            font-size: calc(var(--theme-font-size) - 2px); font-weight: 500;
                                            {{ $project->status === 'completed' ? 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);' :
                                            ($project->status === 'in_progress' ? 'background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);' :
                                            ($project->status === 'on_hold' ? 'background-color: rgba(var(--theme-warning-rgb), 0.1); color: var(--theme-warning);' :
                                            ($project->status === 'draft' ? 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);' : 'background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);'))) }}">
                                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Start Date:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M j, Y') : 'Not set' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">End Date:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M j, Y') : 'Not set' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Created:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->created_at->format('M j, Y') }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Updated:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->updated_at->format('M j, Y') }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 90px;">Project ID:</dt>
                                    <dd style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text); font-family: monospace; text-align: right; flex: 1; margin-left: 0.5rem;">#{{ $project->id }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Right column: Financial & Settings --}}
                        <div style="padding-left: 2rem; ">
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Financial & Settings</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Monthly Fee:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                        @if($project->monthly_fee)
                                            <span style="font-weight: 500;">€{{ number_format($project->monthly_fee, 2) }}</span>
                                        @else
                                            <em style="color: var(--theme-text-muted);">Not set</em>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Hourly Rate:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">
                                        @if($project->default_hourly_rate)
                                            <span style="font-weight: 500;">€{{ number_format($project->default_hourly_rate, 2) }}/hr</span>
                                        @else
                                            <em style="color: var(--theme-text-muted);">Not set</em>
                                        @endif
                                    </dd>
                                </div>
                                @if($project->vat_rate)
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">VAT Rate:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">{{ number_format($project->vat_rate, 2) }}%</dd>
                                </div>
                                @endif
                                @if($project->pricing_type)
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Pricing Type:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">{{ ucfirst(str_replace('_', ' ', $project->pricing_type)) }}</dd>
                                </div>
                                @endif
                                @if($project->billing_frequency)
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Billing Freq:</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">{{ ucfirst(str_replace('_', ' ', $project->billing_frequency)) }}</dd>
                                </div>
                                @endif
                                @if($project->fee_rollover_enabled)
                                <div class="flex justify-between items-start">
                                    <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 100px;">Fee Rollover:</dt>
                                    <dd style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                        <span style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-success); background-color: rgba(var(--theme-success-rgb), 0.1); padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                                            <i class="fas fa-check-circle mr-1"></i>Enabled
                                        </span>
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Full-width description and notes --}}
                    @if($project->description || $project->notes)
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(203, 213, 225, 0.3); space-y: 1.5rem;">
                        @if($project->description)
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Description</h3>
                            <div style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5; white-space: pre-wrap;">{{ $project->description }}</div>
                        </div>
                        @endif

                        @if($project->notes)
                        <div style="margin-top: 1.5rem;">
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Notes</h3>
                            <div style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5; white-space: pre-wrap; font-style: italic;">{{ $project->notes }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Team & Structure --}}
            <div class="space-y-4">

                {{-- Customer Information --}}
                @if($project->customer)
                <div id="customer-info-block" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Customer Information</h2>
                        <a href="{{ route('customers.show', $project->customer) }}?from=project&project_id={{ $project->id }}"
                           class="inline-flex items-center px-2 py-1 rounded transition-colors"
                           style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; text-decoration: none;">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            View Details
                        </a>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        {{-- Two-column layout for customer info --}}
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            {{-- Left column: Primary contact info --}}
                            <div style="padding-right: 2rem;">
                                <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Primary Contact</h3>
                                <dl class="space-y-2">
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Name:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500; text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->customer->name }}</dd>
                                    </div>
                                    @if($project->customer->company)
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Company:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->customer->company }}</dd>
                                    </div>
                                    @endif
                                    @if($project->customer->contact_person)
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Contact:</dt>
                                        <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1; margin-left: 0.5rem;">{{ $project->customer->contact_person }}</dd>
                                    </div>
                                    @endif
                                    @if($project->customer->email)
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Email:</dt>
                                        <dd style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <a href="mailto:{{ $project->customer->email }}"
                                               style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                {{ $project->customer->email }}
                                            </a>
                                        </dd>
                                    </div>
                                    @endif
                                    @if($project->customer->phone)
                                    <div class="flex justify-between items-start">
                                        <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 80px;">Phone:</dt>
                                        <dd style="text-align: right; flex: 1; margin-left: 0.5rem;">
                                            <a href="tel:{{ $project->customer->phone }}"
                                               style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                {{ $project->customer->phone }}
                                            </a>
                                        </dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>

                            {{-- Right column: Address --}}
                            @if($project->customer->street || $project->customer->city || $project->customer->address)
                            <div style="padding-left: 2rem; ">
                                <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Address</h3>
                                <div style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5;">
                                    @if($project->customer->street)
                                        <div style="font-weight: 500;">{{ $project->customer->street }}@if($project->customer->addition) {{ $project->customer->addition }}@endif</div>
                                    @endif
                                    @if($project->customer->zip_code || $project->customer->city)
                                        <div>@if($project->customer->zip_code){{ $project->customer->zip_code }} @endif{{ $project->customer->city }}</div>
                                    @endif
                                    @if($project->customer->country)
                                        <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">{{ $project->customer->country }}</div>
                                    @endif
                                    @if($project->customer->address && !$project->customer->street)
                                        <div>{{ $project->customer->address }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Full-width customer notes --}}
                        @if($project->customer->notes)
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(203, 213, 225, 0.3);">
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Notes</h3>
                            <div style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.5; white-space: pre-wrap; font-style: italic;">{{ $project->customer->notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Team & Companies --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Team & Companies</h2>
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

                {{-- Project Structure --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Project Structure</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <a href="{{ route('projects.milestones.create', $project) }}"
                               class="inline-flex items-center px-2 py-1 rounded transition-colors"
                               style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                                <i class="fas fa-plus mr-1"></i>
                                Add Milestone
                            </a>
                        @endif
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        @if($project->milestones->count() > 0)
                            <div class="space-y-4" id="milestones-container">
                                @foreach($project->milestones as $milestone)
                                <div class="milestone-item border rounded-lg overflow-hidden"
                                     style="border-color: rgba(var(--theme-border-rgb), 0.2);"
                                     data-milestone-id="{{ $milestone->id }}">
                                    <div class="flex items-center justify-between p-3" style="background-color: rgba(var(--theme-text-muted-rgb), 0.05);">
                                        <div class="flex items-center space-x-3">
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                <div class="milestone-drag-handle"
                                                     title="Drag to reorder milestone"
                                                     style="cursor: grab; cursor: -webkit-grab; cursor: -moz-grab;">
                                                    <svg class="w-4 h-4" style="color: var(--theme-text-muted); cursor: inherit;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $milestone->name }}</h3>
                                            <span class="px-2 py-1 rounded-full" style="
                                                font-size: calc(var(--theme-font-size) - 2px); font-weight: 500;
                                                {{ $milestone->status === 'completed' ? 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);' :
                                                ($milestone->status === 'in_progress' ? 'background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);' : 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);') }}">
                                                {{ ucfirst($milestone->status) }}
                                            </span>
                                        </div>
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('project-milestones.tasks.create', $milestone) }}"
                                                   style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"
                                                   title="Add task">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                                <a href="{{ route('projects.milestones.edit', [$project, $milestone]) }}"
                                                   style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"
                                                   title="Edit milestone">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    @if($milestone->tasks->count() > 0)
                                    <div class="p-3">
                                        <div class="space-y-2 tasks-container" data-milestone-id="{{ $milestone->id }}">
                                            @foreach($milestone->tasks as $task)
                                            <div class="sortable-task flex items-center justify-between p-2 rounded border"
                                                 style="background-color: white; border-color: rgba(var(--theme-border-rgb), 0.1);"
                                                 data-task-id="{{ $task->id }}" data-milestone-id="{{ $milestone->id }}">
                                                <div class="flex items-center space-x-2">
                                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                        <div class="task-drag-handle"
                                                             title="Drag to reorder task"
                                                             style="cursor: grab; cursor: -webkit-grab; cursor: -moz-grab;">
                                                            <svg class="w-3 h-3" style="color: var(--theme-text-muted); cursor: inherit;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <span style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $task->name }}</span>
                                                    <span class="px-2 py-0.5 rounded" style="
                                                        font-size: calc(var(--theme-font-size) - 3px); font-weight: 500;
                                                        {{ $task->status === 'completed' ? 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);' :
                                                        ($task->status === 'in_progress' ? 'background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);' : 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);') }}">
                                                        {{ ucfirst($task->status) }}
                                                    </span>
                                                </div>
                                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                                    <a href="{{ route('project-milestones.tasks.edit', [$milestone, $task]) }}"
                                                       style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"
                                                       title="Edit task">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @else
                                    <div class="p-3 text-center" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                        No tasks in this milestone
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8" style="color: var(--theme-text-muted);">
                                <i class="fas fa-tasks" style="font-size: calc(var(--theme-font-size) + 16px); margin-bottom: 0.75rem;"></i>
                                <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem;">No milestones yet</h3>
                                <p style="font-size: var(--theme-font-size);">Add your first milestone to get started with project tasks</p>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                    <a href="{{ route('projects.milestones.create', $project) }}"
                                       class="inline-flex items-center px-3 py-2 rounded-lg transition-colors shadow-sm mt-3"
                                       style="background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); font-weight: 500;">
                                        <i class="fas fa-plus mr-1.5"></i>
                                        Add First Milestone
                                    </a>
                                @endif
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
                            <a href="{{ route('time-entries.index') }}?project_id={{ $project->id }}"
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if user has permission to drag & drop
    const userRole = '{{ Auth::user()->role }}';
    const canDragDrop = ['super_admin', 'admin', 'project_manager'].includes(userRole);

    if (!canDragDrop) {
        console.log('User does not have drag & drop permissions');
        return;
    }

    console.log('Initializing drag & drop for project structure...');

    // Make project info and customer info blocks same height
    function matchBlockHeights() {
        const projectBlock = document.getElementById('project-info-block');
        const customerBlock = document.getElementById('customer-info-block');

        if (projectBlock && customerBlock) {
            // Reset heights first
            projectBlock.style.height = 'auto';
            customerBlock.style.height = 'auto';

            // Get natural heights
            const projectHeight = projectBlock.offsetHeight;
            const customerHeight = customerBlock.offsetHeight;

            // Set both to the taller height
            const maxHeight = Math.max(projectHeight, customerHeight);
            projectBlock.style.height = maxHeight + 'px';
            customerBlock.style.height = maxHeight + 'px';

            console.log('Matched block heights:', maxHeight + 'px');
        }
    }

    // Match heights on load and resize
    matchBlockHeights();
    window.addEventListener('resize', matchBlockHeights);

    // Initialize milestone drag & drop
    const milestonesContainer = document.getElementById('milestones-container');
    if (milestonesContainer && typeof Sortable !== 'undefined') {
        console.log('Setting up milestone drag & drop');

        Sortable.create(milestonesContainer, {
            handle: '.milestone-drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            chosenClass: 'sortable-chosen',
            onEnd: function(evt) {
                console.log('Milestone dragged from', evt.oldIndex, 'to', evt.newIndex);
                if (evt.oldIndex !== evt.newIndex) {
                    reorderMilestones();
                }
            }
        });
    }

    // Initialize task drag & drop for each milestone
    const taskContainers = document.querySelectorAll('.tasks-container');
    taskContainers.forEach(container => {
        const milestoneId = container.dataset.milestoneId;
        console.log('Setting up task drag & drop for milestone', milestoneId);

        Sortable.create(container, {
            handle: '.task-drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            chosenClass: 'sortable-chosen',
            onEnd: function(evt) {
                console.log('Task dragged from', evt.oldIndex, 'to', evt.newIndex, 'in milestone', milestoneId);
                if (evt.oldIndex !== evt.newIndex) {
                    reorderTasks(milestoneId);
                }
            }
        });
    });
});

// Function to reorder milestones
function reorderMilestones() {
    const milestoneItems = document.querySelectorAll('#milestones-container .milestone-item');
    const milestoneIds = Array.from(milestoneItems).map(item => item.dataset.milestoneId);

    console.log('Reordering milestones:', milestoneIds);

    fetch(`/projects/{{ $project->id }}/milestones/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            milestone_ids: milestoneIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Milestone order updated');
        } else {
            console.error('Failed to update milestone order:', data);
            showErrorMessage('Failed to update milestone order');
            // Reload page to restore original order
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error updating milestone order:', error);
        showErrorMessage('Error updating milestone order');
        setTimeout(() => location.reload(), 1000);
    });
}

// Function to reorder tasks within a milestone
function reorderTasks(milestoneId) {
    const taskItems = document.querySelectorAll(`.tasks-container[data-milestone-id="${milestoneId}"] .sortable-task`);
    const taskIds = Array.from(taskItems).map(item => item.dataset.taskId);

    console.log('Reordering tasks for milestone', milestoneId, ':', taskIds);

    fetch(`/project-milestones/${milestoneId}/tasks/reorder`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            task_ids: taskIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Task order updated');
        } else {
            console.error('Failed to update task order:', data);
            showErrorMessage('Failed to update task order');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error updating task order:', error);
        showErrorMessage('Error updating task order');
        setTimeout(() => location.reload(), 1000);
    });
}

// Helper function to show success message
function showSuccessMessage(message) {
    const successMsg = document.createElement('div');
    successMsg.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-[9999] transition-all duration-300';
    successMsg.style.cssText = `
        background-color: var(--theme-success);
        color: white;
        font-size: var(--theme-font-size);
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    `;
    successMsg.textContent = message;
    document.body.appendChild(successMsg);

    // Add entrance animation
    setTimeout(() => {
        successMsg.style.transform = 'translateX(0)';
        successMsg.style.opacity = '1';
    }, 10);

    // Remove after 3 seconds
    setTimeout(() => {
        successMsg.style.transform = 'translateX(100%)';
        successMsg.style.opacity = '0';
        setTimeout(() => successMsg.remove(), 300);
    }, 3000);
}

// Helper function to show error message
function showErrorMessage(message) {
    const errorMsg = document.createElement('div');
    errorMsg.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-[9999] transition-all duration-300';
    errorMsg.style.cssText = `
        background-color: var(--theme-danger);
        color: white;
        font-size: var(--theme-font-size);
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transform: translateX(100%);
        opacity: 0;
    `;
    errorMsg.textContent = message;
    document.body.appendChild(errorMsg);

    // Add entrance animation
    setTimeout(() => {
        errorMsg.style.transform = 'translateX(0)';
        errorMsg.style.opacity = '1';
    }, 10);

    // Remove after 4 seconds
    setTimeout(() => {
        errorMsg.style.transform = 'translateX(100%)';
        errorMsg.style.opacity = '0';
        setTimeout(() => errorMsg.remove(), 300);
    }, 4000);
}
</script>
@endpush

@push('styles')
<style>
/* Responsive layout for projects */
@media (max-width: 1024px) {
    .projects-two-column {
        display: block !important;
    }
    .projects-two-column > div {
        margin-bottom: 2rem;
    }
}

/* Responsive 3-column layout in project information */
@media (max-width: 768px) {
    .grid-cols-1.md\:grid-cols-3 {
        grid-template-columns: 1fr !important;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .grid-cols-1.md\:grid-cols-3 {
        grid-template-columns: 1fr 1fr !important;
    }
    .grid-cols-1.md\:grid-cols-3 > div:last-child {
        grid-column: 1 / -1;
    }
}

/* Drag & Drop Styles */
.sortable-ghost {
    opacity: 0.4;
    background: rgba(var(--theme-primary-rgb), 0.1) !important;
    border-color: var(--theme-primary) !important;
}

.sortable-drag {
    opacity: 0.9 !important;
    transform: rotate(1deg);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 1000;
}

.milestone-item.sortable-chosen,
.sortable-task.sortable-chosen {
    border-color: var(--theme-primary) !important;
    background-color: rgba(var(--theme-primary-rgb), 0.05) !important;
}

.milestone-drag-handle,
.task-drag-handle {
    opacity: 0.5;
    transition: all 0.2s ease;
    cursor: move; /* Fallback for older browsers */
    cursor: -webkit-grab; /* Webkit browsers */
    cursor: -moz-grab; /* Firefox */
    cursor: grab !important; /* Modern browsers */
    padding: 0.25rem;
    border-radius: 0.25rem;
    display: inline-block;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.milestone-drag-handle *,
.task-drag-handle * {
    cursor: move; /* Fallback */
    cursor: -webkit-grab; /* Webkit */
    cursor: -moz-grab; /* Firefox */
    cursor: grab !important; /* Modern browsers */
    pointer-events: none;
}

.milestone-drag-handle:active,
.task-drag-handle:active {
    cursor: move; /* Fallback */
    cursor: -webkit-grabbing; /* Webkit */
    cursor: -moz-grabbing; /* Firefox */
    cursor: grabbing !important; /* Modern browsers */
}

.milestone-drag-handle:active *,
.task-drag-handle:active * {
    cursor: move; /* Fallback */
    cursor: -webkit-grabbing; /* Webkit */
    cursor: -moz-grabbing; /* Firefox */
    cursor: grabbing !important; /* Modern browsers */
}

.milestone-item:hover .milestone-drag-handle,
.sortable-task:hover .task-drag-handle {
    opacity: 1;
    background-color: rgba(var(--theme-primary-rgb), 0.1);
}

.milestone-drag-handle:hover,
.task-drag-handle:hover {
    color: var(--theme-primary) !important;
    background-color: rgba(var(--theme-primary-rgb), 0.15) !important;
    cursor: move; /* Fallback */
    cursor: -webkit-grab; /* Webkit */
    cursor: -moz-grab; /* Firefox */
    cursor: grab !important; /* Modern browsers */
    opacity: 1 !important;
}

.milestone-drag-handle:hover *,
.task-drag-handle:hover * {
    cursor: move; /* Fallback */
    cursor: -webkit-grab; /* Webkit */
    cursor: -moz-grab; /* Firefox */
    cursor: grab !important; /* Modern browsers */
}

.milestone-drag-handle:active,
.task-drag-handle:active {
    background-color: rgba(var(--theme-primary-rgb), 0.2) !important;
    transform: scale(0.95);
    cursor: move; /* Fallback */
    cursor: -webkit-grabbing; /* Webkit */
    cursor: -moz-grabbing; /* Firefox */
    cursor: grabbing !important; /* Modern browsers */
}

.tasks-container {
    min-height: 20px;
}

.milestone-item {
    cursor: default;
    transition: all 0.2s ease;
}

.sortable-task {
    cursor: default;
    transition: all 0.2s ease;
}

.milestone-item:hover {
    border-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}

.sortable-task:hover {
    border-color: rgba(var(--theme-primary-rgb), 0.3) !important;
}

/* During dragging */
.sortable-drag.milestone-item,
.sortable-drag.sortable-task {
    cursor: grabbing !important;
}

.sortable-drag.milestone-item *,
.sortable-drag.sortable-task * {
    cursor: grabbing !important;
}

/* Hide drag handles for non-admin users */
@media (max-width: 640px) {
    .milestone-drag-handle,
    .task-drag-handle {
        display: none;
    }
}
</style>
@endpush
@endsection