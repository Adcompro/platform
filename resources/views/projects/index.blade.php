{{-- 📁 Locatie: resources/views/projects/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Projects')

@section('content')
{{-- Sticky Header - Exact Copy Theme Settings --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Projects</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Manage your projects and track progress</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                <a href="{{ route('projects.create') }}"
                   id="header-create-btn"
                   class="header-btn"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-plus mr-1.5"></i>
                    New Project
                </a>
                @endif

                {{-- Help Button --}}
                <button onclick="openHelpModal()"
                        id="header-help-btn"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);"
                        title="Project Management Guide">
                    <i class="fas fa-question-circle mr-1.5"></i>
                    Help
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Main Content - Exact Copy Theme Settings --}}
<div style="padding: 1.5rem 2rem;">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.1); border-color: var(--theme-success); color: var(--theme-success); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span style="font-size: var(--theme-font-size);">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border-color: var(--theme-danger); color: var(--theme-danger); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span style="font-size: var(--theme-font-size);">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Statistics Cards --}}
    @if(isset($stats))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
            <div class="flex items-center justify-between">
                <div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                        {{ number_format($stats['total_projects'] ?? 0) }}
                    </div>
                    <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                        Total Projects
                    </div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                    <i class="fas fa-folder" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-primary);"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-success-rgb), 0.05);">
            <div class="flex items-center justify-between">
                <div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);">
                        {{ number_format($stats['active_projects'] ?? 0) }}
                    </div>
                    <div style="font-size: var(--theme-font-size); color: var(--theme-success);">
                        Active
                    </div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.1);">
                    <i class="fas fa-play" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-success);"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
            <div class="flex items-center justify-between">
                <div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                        {{ number_format($stats['completed_projects'] ?? 0) }}
                    </div>
                    <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                        Completed
                    </div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                    <i class="fas fa-check-circle" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-primary);"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-warning-rgb), 0.05);">
            <div class="flex items-center justify-between">
                <div>
                    <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-warning);">
                        {{ number_format($stats['on_hold_projects'] ?? 0) }}
                    </div>
                    <div style="font-size: var(--theme-font-size); color: var(--theme-warning);">
                        On Hold
                    </div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-lg" style="background-color: rgba(var(--theme-warning-rgb), 0.1);">
                    <i class="fas fa-pause" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-warning);"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6" style="padding: var(--theme-card-padding);">
        <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text"
                       name="search"
                       placeholder="Search projects..."
                       value="{{ request('search') }}"
                       class="w-full border rounded-md focus:outline-none focus:ring-2"
                       style="font-size: var(--theme-font-size); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-color: rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); focus:border-color: var(--theme-primary); focus:ring-color: rgba(var(--theme-primary-rgb), 0.2);">
            </div>

            <select name="status"
                    class="border rounded-md focus:outline-none focus:ring-2"
                    style="font-size: var(--theme-font-size); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-color: rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); focus:border-color: var(--theme-primary);">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <button type="submit"
                    class="font-medium rounded-md hover:opacity-90 transition-all"
                    style="font-size: var(--theme-font-size); background-color: var(--theme-primary); color: white; padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-radius: var(--theme-border-radius);">
                <i class="fas fa-search mr-1.5"></i>
                Search
            </button>

            @if(request('search') || request('status'))
            <a href="{{ route('projects.index') }}"
               class="font-medium rounded-md hover:opacity-90 transition-all"
               style="font-size: var(--theme-font-size); background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-radius: var(--theme-border-radius); text-decoration: none;">
                <i class="fas fa-times mr-1.5"></i>
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Projects Table --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                <thead style="background-color: var(--theme-table-header-bg);">
                    <tr>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Project
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Customer
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Status
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Billing
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Team
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Budget Used
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: right; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white/60 divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                    @forelse($projects as $project)
                    <tr class="hover:bg-gray-50/60">
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div>
                                <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                    <a href="{{ route('projects.show', $project) }}" style="color: var(--theme-primary); text-decoration: none;" class="hover:opacity-80">
                                        {{ $project->name }}
                                    </a>
                                </div>
                                @if($project->start_date)
                                <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">{{ $project->start_date->format('M d, Y') }}</div>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($project->customer)
                            <div>
                                <a href="{{ route('customers.show', $project->customer) }}" style="color: var(--theme-accent); text-decoration: none;" class="hover:opacity-80">
                                    {{ $project->customer->name }}
                                </a>
                            </div>
                            @else
                            <span style="color: var(--theme-text-muted);">No Customer</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            @php
                                $statusColors = [
                                    'draft' => ['bg' => 'rgba(var(--theme-text-muted-rgb), 0.1)', 'color' => 'var(--theme-text-muted)'],
                                    'active' => ['bg' => 'rgba(var(--theme-success-rgb), 0.1)', 'color' => 'var(--theme-success)'],
                                    'completed' => ['bg' => 'rgba(var(--theme-primary-rgb), 0.1)', 'color' => 'var(--theme-primary)'],
                                    'on_hold' => ['bg' => 'rgba(var(--theme-warning-rgb), 0.1)', 'color' => 'var(--theme-warning)'],
                                    'cancelled' => ['bg' => 'rgba(var(--theme-danger-rgb), 0.1)', 'color' => 'var(--theme-danger)'],
                                ];
                                $statusStyle = $statusColors[$project->status] ?? $statusColors['draft'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full"
                                  style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['color'] }};">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div>
                                @php
                                    $nextBilling = $project->calculateNextBillingDate();
                                    $isReady = $project->isReadyForInvoicing();
                                @endphp
                                @if($isReady)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full"
                                          style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                                        <i class="fas fa-check mr-1" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                        Ready
                                    </span>
                                @elseif($nextBilling)
                                    <div style="font-size: calc(var(--theme-font-size) - 2px);">
                                        <div style="font-weight: 500; color: var(--theme-text);">{{ $nextBilling->format('d M') }}</div>
                                        <div style="color: var(--theme-text-muted);">{{ $nextBilling->diffForHumans(null, true) }}</div>
                                    </div>
                                @else
                                    <span style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Not scheduled</span>
                                @endif
                                @if($project->billing_frequency)
                                    <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                        @switch($project->billing_frequency)
                                            @case('monthly') Monthly @break
                                            @case('quarterly') Quarterly @break
                                            @case('milestone') Per Milestone @break
                                            @case('custom') Every {{$project->billing_interval_days}}d @break
                                            @default {{ ucfirst($project->billing_frequency) }}
                                        @endswitch
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div class="flex -space-x-2">
                                @foreach($project->users->take(3) as $user)
                                <div class="relative group">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center border border-white cursor-pointer"
                                         style="background-color: var(--theme-primary);">
                                        <span class="text-white" style="font-size: calc(var(--theme-font-size) - 4px); font-weight: 500;">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    {{-- User Hover Tooltip --}}
                                    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-20">
                                        <div class="bg-gray-800 text-white rounded-lg py-1.5 px-2.5 whitespace-nowrap" style="font-size: calc(var(--theme-font-size) - 2px);">
                                            <div style="font-weight: 500;">{{ $user->name }}</div>
                                            <div style="color: #d1d5db; font-size: calc(var(--theme-font-size) - 4px);">{{ $user->company->name ?? 'No Company' }}</div>
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -translate-y-1">
                                                <div class="border-4 border-transparent border-t-gray-800"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @if($project->users->count() > 3)
                                <div class="relative group">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center border border-white cursor-pointer"
                                         style="background-color: rgba(var(--theme-text-muted-rgb), 0.2);">
                                        <span style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 4px); font-weight: 500;">+{{ $project->users->count() - 3 }}</span>
                                    </div>
                                    {{-- Additional Users Tooltip --}}
                                    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-20">
                                        <div class="bg-gray-800 text-white rounded-lg py-2 px-3 whitespace-nowrap max-w-xs" style="font-size: calc(var(--theme-font-size) - 2px);">
                                            <div style="font-weight: 500; margin-bottom: 0.25rem;">{{ $project->users->count() - 3 }} more team members</div>
                                            @foreach($project->users->skip(3)->take(5) as $user)
                                            <div style="color: #d1d5db; font-size: calc(var(--theme-font-size) - 4px);">• {{ $user->name }}</div>
                                            @endforeach
                                            @if($project->users->count() > 8)
                                            <div style="color: #9ca3af; font-size: calc(var(--theme-font-size) - 4px); margin-top: 0.25rem;">and {{ $project->users->count() - 8 }} others...</div>
                                            @endif
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -translate-y-1">
                                                <div class="border-4 border-transparent border-t-gray-800"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            @php
                                $budgetPercentage = $project->budget_percentage ?? 0;
                                $budgetUsed = $project->budget_used ?? 0;
                                $budgetTotal = $project->budget_total ?? 0;

                                // Bepaal kleur op basis van percentage
                                $progressColor = 'var(--theme-success)';
                                if ($budgetPercentage > 90) {
                                    $progressColor = 'var(--theme-danger)';
                                } elseif ($budgetPercentage > 75) {
                                    $progressColor = 'var(--theme-warning)';
                                }
                            @endphp
                            <div class="group relative">
                                <div class="flex items-center">
                                    <div class="w-24 rounded-full h-1.5 mr-2 cursor-pointer" style="background-color: rgba(var(--theme-text-muted-rgb), 0.2);">
                                        <div class="h-1.5 rounded-full transition-all duration-300" style="width: {{ min(100, $budgetPercentage) }}%; background-color: {{ $progressColor }};"></div>
                                    </div>
                                    <span style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text);">{{ $budgetPercentage }}%</span>
                                </div>

                                {{-- Hover Tooltip --}}
                                <div class="absolute bottom-6 left-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10">
                                    <div class="bg-gray-800 text-white rounded-lg py-1.5 px-3 whitespace-nowrap" style="font-size: calc(var(--theme-font-size) - 2px);">
                                        <div style="font-weight: 500;">€{{ number_format($budgetUsed, 2, ',', '.') }} / €{{ number_format($budgetTotal, 2, ',', '.') }}</div>
                                        <div class="absolute top-full left-8 transform -translate-y-1">
                                            <div class="border-4 border-transparent border-t-gray-800"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="text-gray-400 hover:text-gray-600" title="View" style="font-size: var(--theme-font-size);">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                <a href="{{ route('projects.edit', $project) }}"
                                   class="text-gray-400 hover:text-gray-600" title="Edit" style="font-size: var(--theme-font-size);">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem 1.5rem; text-align: center;">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-3" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No projects found</p>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                <a href="{{ route('projects.create') }}"
                                   style="margin-top: 0.75rem; color: var(--theme-accent); font-size: var(--theme-font-size); text-decoration: none;"
                                   class="hover:opacity-80">
                                    Create your first project
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
        <div style="padding: 0.75rem 1.5rem; border-top: 1px solid rgba(203, 213, 225, 0.3); background-color: rgba(var(--theme-table-header-bg), 0.5); font-size: var(--theme-font-size);">
            {{ $projects->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Help Modal --}}
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-gray-50 rounded-xl shadow-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center" style="border-color: rgba(203, 213, 225, 0.3); background-color: rgba(var(--theme-table-header-bg), 0.5);">
            <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text);">Project Management Guide</h3>
            <button onclick="closeHelpModal()" style="color: var(--theme-text-muted);" class="hover:opacity-60">
                <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) + 4px);"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" style="font-size: var(--theme-font-size);">
            <div class="space-y-6">
                {{-- Introduction --}}
                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">Overview</h4>
                    <p style="color: var(--theme-text-muted);">
                        The Project Management system allows you to create, track, and manage projects with detailed budgeting, team assignments, and milestone tracking.
                        Each project can have multiple milestones, tasks, and subtasks for complete project organization.
                    </p>
                </div>

                {{-- Key Features --}}
                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">Key Features</h4>
                    <ul class="space-y-2" style="color: var(--theme-text-muted);">
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Hierarchical Structure:</strong> Projects → Milestones → Tasks → Subtasks</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Budget Tracking:</strong> Real-time budget usage monitoring with visual indicators</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Team Management:</strong> Multi-company team assignments with role-based access</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Billing Integration:</strong> Automated billing schedules and invoice generation</span>
                        </li>
                    </ul>
                </div>

                {{-- Project Status Guide --}}
                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">Project Status Guide</h4>
                    <div class="rounded-lg p-4" style="background-color: rgba(var(--theme-primary-rgb), 0.05);">
                        <ul class="space-y-2" style="color: var(--theme-text-muted);">
                            <li><strong style="color: var(--theme-text-muted);">Draft:</strong> Project is being prepared and not yet started</li>
                            <li><strong style="color: var(--theme-success);">Active:</strong> Project is currently in progress</li>
                            <li><strong style="color: var(--theme-primary);">Completed:</strong> Project has been finished successfully</li>
                            <li><strong style="color: var(--theme-warning);">On Hold:</strong> Project is temporarily paused</li>
                            <li><strong style="color: var(--theme-danger);">Cancelled:</strong> Project has been cancelled</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end" style="background-color: rgba(var(--theme-table-header-bg), 0.5); border-color: rgba(203, 213, 225, 0.3);">
            <button onclick="closeHelpModal()"
                    class="font-medium text-white rounded-md hover:opacity-90 transition-all"
                    style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: var(--theme-primary); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                Close Guide
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header create button
    const createBtn = document.getElementById('header-create-btn');
    if (createBtn) {
        createBtn.style.backgroundColor = primaryColor;
        createBtn.style.color = 'white';
        createBtn.style.border = 'none';
        createBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Header help button
    const helpBtn = document.getElementById('header-help-btn');
    if (helpBtn) {
        helpBtn.style.backgroundColor = '#6b7280';
        helpBtn.style.color = 'white';
        helpBtn.style.border = 'none';
        helpBtn.style.borderRadius = 'var(--theme-border-radius)';
    }
}

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

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHelpModal();
    }
});

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush

@endsection