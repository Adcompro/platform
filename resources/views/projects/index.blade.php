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

                <a href="{{ route('projects.deleted') }}"
                   id="header-deleted-btn"
                   class="header-btn"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-trash-restore mr-1.5"></i>
                    Deleted Projects
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

            <select name="customer_id"
                    class="border rounded-md focus:outline-none focus:ring-2"
                    style="font-size: var(--theme-font-size); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-color: rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); focus:border-color: var(--theme-primary); min-width: 200px;">
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    class="border rounded-md focus:outline-none focus:ring-2"
                    style="font-size: var(--theme-font-size); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-color: rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); focus:border-color: var(--theme-primary);">
                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
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

            @if(request('search') || (request('status') && request('status') !== 'active') || request('customer_id'))
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
                @php
                    // Helper functie voor sorteerbare kolom headers
                    function sortableHeader($label, $field, $currentSort, $currentDirection) {
                        $newDirection = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
                        $url = request()->fullUrlWithQuery(['sort' => $field, 'direction' => $newDirection]);

                        $icon = '';
                        if ($currentSort === $field) {
                            $icon = $currentDirection === 'asc' ? '↑' : '↓';
                        }

                        return '<a href="' . $url . '" style="color: var(--theme-text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; hover:color: var(--theme-text);">'
                               . $label . ' ' . $icon . '</a>';
                    }

                    $currentSort = request('sort', 'created_at');
                    $currentDirection = request('direction', 'desc');
                @endphp

                <thead style="background-color: var(--theme-table-header-bg);">
                    <tr>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <th style="padding: 0.75rem 1rem; width: 40px;">
                            <input type="checkbox"
                                   id="select-all-projects"
                                   class="rounded"
                                   style="color: var(--theme-primary); border-color: rgba(var(--theme-border-rgb), 0.8);">
                        </th>
                        @endif
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                            {!! sortableHeader('Project', 'name', $currentSort, $currentDirection) !!}
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Customer
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                            {!! sortableHeader('Status', 'status', $currentSort, $currentDirection) !!}
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                            {!! sortableHeader('Start Date', 'start_date', $currentSort, $currentDirection) !!}
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                            {!! sortableHeader('End Date', 'end_date', $currentSort, $currentDirection) !!}
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                            {!! sortableHeader('Monthly Fee', 'monthly_fee', $currentSort, $currentDirection) !!}
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                            {!! sortableHeader('Billing', 'billing_frequency', $currentSort, $currentDirection) !!}
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">
                            {!! sortableHeader('Budget Used', 'budget_used', $currentSort, $currentDirection) !!}
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: center; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Year Budget
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: right; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white/60 divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                    @forelse($projects as $project)
                    <tr class="hover:bg-gray-50/60">
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <td style="padding: 1rem 1rem;">
                            <input type="checkbox"
                                   class="project-checkbox rounded"
                                   value="{{ $project->id }}"
                                   style="color: var(--theme-primary); border-color: rgba(var(--theme-border-rgb), 0.8);">
                        </td>
                        @endif
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                <a href="{{ route('projects.show', $project) }}" style="color: var(--theme-primary); text-decoration: none;" class="hover:opacity-80">
                                    {{ $project->name }}
                                </a>

                                {{-- Master Template Badge (highest priority - golden) --}}
                                @if($project->is_master_template)
                                <span class="inline-flex items-center px-2 py-0.5 rounded ml-2"
                                      style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 600; background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(245, 158, 11, 0.15) 100%); color: rgb(180, 83, 9); border: 1px solid rgba(251, 191, 36, 0.3);"
                                      title="Master Template for series: {{ $project->recurring_series_id }}">
                                    <i class="fas fa-crown mr-1" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                    MASTER
                                </span>
                                @endif

                                {{-- Recurring Project Badge --}}
                                @if($project->is_recurring && !$project->is_master_template)
                                <span class="inline-flex items-center px-2 py-0.5 rounded ml-2"
                                      style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 600; background-color: rgba(139, 92, 246, 0.1); color: rgb(139, 92, 246);"
                                      title="This project automatically creates new projects every {{ $project->recurring_frequency }}">
                                    <i class="fas fa-sync-alt mr-1" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                    RECURRING
                                </span>
                                @endif

                                {{-- Auto-Generated Child Project Badge --}}
                                @if($project->parent_recurring_project_id)
                                <span class="inline-flex items-center px-2 py-0.5 rounded ml-2"
                                      style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 600; background-color: rgba(59, 130, 246, 0.1); color: rgb(59, 130, 246);"
                                      title="Auto-generated from recurring project">
                                    <i class="fas fa-robot mr-1" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                    AUTO
                                </span>
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
                            @if($project->start_date)
                                <div style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    {{ $project->start_date->format('d M Y') }}
                                </div>
                            @else
                                <span style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Not set</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($project->end_date)
                                <div style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    {{ $project->end_date->format('d M Y') }}
                                </div>
                            @else
                                <span style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Not set</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($project->monthly_fee)
                                <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                    € {{ number_format($project->monthly_fee, 2, ',', '.') }}
                                </div>
                            @else
                                <span style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Not set</span>
                            @endif
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
                            @php
                                $budgetPercentage = $project->budget_percentage ?? 0;
                                $budgetUsed = $project->budget_used ?? 0;
                                $budgetTotal = $project->budget_total ?? 0;

                                // Bereken totale tijd kosten
                                $totalTimeCosts = $project->total_time_costs ?? 0;
                                $totalLoggedHours = $project->total_logged_hours ?? 0;

                                // KRITIEKE FIX (03-11-2025): Gebruik hardcoded kleuren ipv CSS variables
                                // CSS variables werken niet altijd correct in progress bars
                                if ($budgetPercentage > 90) {
                                    // Overspent (>90%): Rood
                                    $progressColor = '#ef4444'; // red-500
                                    $textColor = '#ef4444';
                                } elseif ($budgetPercentage > 75) {
                                    // Waarschuwing (75-90%): Oranje
                                    $progressColor = '#f59e0b'; // amber-500
                                    $textColor = '#f59e0b';
                                } else {
                                    // Goed bezig (<75%): Groen
                                    $progressColor = '#10b981'; // green-500
                                    $textColor = '#10b981';
                                }
                            @endphp
                            <div class="group relative">
                                {{-- Time Costs Display --}}
                                <div class="mb-1" style="font-size: calc(var(--theme-font-size) - 2px);">
                                    <div style="font-weight: 600; color: var(--theme-text);">
                                        € {{ number_format($totalTimeCosts, 2, ',', '.') }}
                                    </div>
                                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 4px);">
                                        {{ number_format($totalLoggedHours, 2, ',', '.') }}h logged
                                    </div>
                                </div>

                                {{-- Progress Bar --}}
                                <div class="flex items-center">
                                    <div class="w-24 rounded-full h-1.5 mr-2 cursor-pointer" style="background-color: rgba(0, 0, 0, 0.1);">
                                        <div class="h-1.5 rounded-full transition-all duration-300" style="width: {{ min(100, $budgetPercentage) }}%; background-color: {{ $progressColor }};"></div>
                                    </div>
                                    <span style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: {{ $textColor }};">{{ $budgetPercentage }}%</span>
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
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; text-align: center; font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($project->recurring_series_id)
                                {{-- Show year budget link for recurring series --}}
                                <a href="{{ route('projects.year-budget', $project->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg font-medium transition-all"
                                   style="font-size: calc(var(--theme-font-size) - 2px); background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); text-decoration: none;"
                                   title="View year budget for {{ $project->recurring_series_id }}">
                                    <i class="fas fa-chart-line mr-1.5"></i>
                                    View Totals
                                </a>
                            @else
                                <span style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">-</span>
                            @endif
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
                        <td colspan="{{ in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']) ? '10' : '9' }}" style="padding: 3rem 1.5rem; text-align: center;">
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

{{-- Floating Bulk Actions Bar --}}
@if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
<div id="floating-bulk-actions" class="fixed bottom-0 left-0 right-0 z-40 transition-all duration-300" style="transform: translateY(100%); pointer-events: none;">
    <div class="max-w-4xl mx-auto px-4 pb-6">
        <div class="backdrop-blur-lg rounded-2xl shadow-2xl border overflow-hidden"
             style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
                    border-color: rgba(var(--theme-border-rgb), 0.3);
                    pointer-events: auto;">
            <div class="flex items-center justify-between px-6 py-4">
                {{-- Left: Selection Info --}}
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                             style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <i class="fas fa-check" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) + 2px);"></i>
                        </div>
                        <div>
                            <div id="floating-selected-count" class="font-semibold" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                0 selected
                            </div>
                            <div class="text-xs" style="color: var(--theme-text-muted);">
                                Choose an action below
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="flex items-center gap-2">
                    {{-- Status Change Dropdown --}}
                    <div class="relative">
                        <button onclick="toggleStatusDropdown(event)"
                                id="status-dropdown-btn"
                                class="px-4 py-2 rounded-lg font-medium text-white text-sm transition-all duration-200 hover:opacity-90 flex items-center gap-2"
                                style="background-color: var(--theme-primary);">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Change Status</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>

                        <div id="status-dropdown" class="hidden fixed bg-white rounded-lg shadow-2xl border overflow-hidden z-50" style="min-width: 200px; border-color: rgba(var(--theme-border-rgb), 0.3);">
                            <div class="py-1">
                                <button onclick="openBulkStatusModal('draft')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 transition-colors flex items-center gap-3" style="color: var(--theme-text-muted);">
                                    <i class="fas fa-file-alt w-4"></i>
                                    <span>Set to Draft</span>
                                </button>
                                <button onclick="openBulkStatusModal('active')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 transition-colors flex items-center gap-3" style="color: var(--theme-success);">
                                    <i class="fas fa-play w-4"></i>
                                    <span>Activate</span>
                                </button>
                                <button onclick="openBulkStatusModal('on_hold')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 transition-colors flex items-center gap-3" style="color: var(--theme-warning);">
                                    <i class="fas fa-pause w-4"></i>
                                    <span>Put On Hold</span>
                                </button>
                                <button onclick="openBulkStatusModal('completed')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 transition-colors flex items-center gap-3" style="color: var(--theme-primary);">
                                    <i class="fas fa-check-circle w-4"></i>
                                    <span>Mark as Completed</span>
                                </button>
                                <button onclick="openBulkStatusModal('cancelled')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 transition-colors flex items-center gap-3" style="color: var(--theme-danger);">
                                    <i class="fas fa-ban w-4"></i>
                                    <span>Cancel Projects</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Delete Button (apart, want destructief) --}}
                    <button onclick="openBulkDeleteModal()"
                            class="px-4 py-2 rounded-lg font-medium text-white text-sm transition-all duration-200 hover:opacity-90 flex items-center gap-2"
                            style="background-color: var(--theme-danger);">
                        <i class="fas fa-trash"></i>
                        <span>Delete</span>
                    </button>

                    {{-- Clear Selection --}}
                    <button onclick="clearAllSelections()"
                            class="px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200 hover:opacity-80 flex items-center gap-2 ml-2"
                            style="background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Universal Bulk Status Change Modal --}}
<div id="bulkStatusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4" style="border-radius: var(--theme-border-radius);">
        <div class="p-6">
            <div class="flex items-start">
                <div id="statusModalIcon" class="flex-shrink-0 h-12 w-12 rounded-full flex items-center justify-center">
                    <i id="statusModalIconContent" class="text-2xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 id="statusModalTitle" class="font-semibold" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-text);">Change Status</h3>
                    <p class="mt-2" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        Are you sure you want to change the status of <span id="statusModalCount" class="font-medium" style="color: var(--theme-text);">0</span> project(s) to <strong id="statusModalStatusName"></strong>?
                    </p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 flex justify-end gap-3" style="background-color: rgba(var(--theme-border-rgb), 0.05);">
            <button type="button" onclick="closeBulkStatusModal()" class="px-4 py-2 font-medium transition-all rounded-lg" style="background-color: #e5e7eb; color: #6b7280; font-size: 14px;">
                Cancel
            </button>
            <button type="button" id="statusModalConfirmBtn" onclick="confirmBulkStatusChange()" class="px-4 py-2 font-semibold text-white transition-all rounded-lg hover:opacity-90" style="background-color: #3b82f6; font-size: 14px;">
                Change Status
            </button>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal --}}
<div id="bulkDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4" style="border-radius: var(--theme-border-radius);">
        <div class="p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0 h-12 w-12 rounded-full flex items-center justify-center" style="background-color: rgba(var(--theme-danger-rgb), 0.1);">
                    <svg class="h-6 w-6" style="color: var(--theme-danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="font-semibold" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-text);">Delete Projects</h3>
                    <p class="mt-2" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        Are you sure you want to delete <span id="deleteProjectCount" class="font-medium" style="color: var(--theme-text);">0</span> project(s)? This action can be undone from "Deleted Projects".
                    </p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 flex justify-end gap-3" style="background-color: rgba(var(--theme-border-rgb), 0.05);">
            <button type="button" onclick="closeBulkDeleteModal()" class="font-medium transition-all" style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                Cancel
            </button>
            <button type="button" onclick="confirmBulkDelete()" class="font-medium text-white transition-all" style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: var(--theme-danger); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                Delete Projects
            </button>
        </div>
    </div>
</div>

{{-- Help Modal - Recurring Projects Complete Guide --}}
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-gray-50 rounded-xl shadow-lg max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center" style="border-color: rgba(203, 213, 225, 0.3); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="flex items-center gap-3">
                <i class="fas fa-sync-alt text-white" style="font-size: calc(var(--theme-font-size) + 6px);"></i>
                <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: white;">Recurring Projects - Complete Guide</h3>
            </div>
            <button onclick="closeHelpModal()" style="color: white;" class="hover:opacity-80">
                <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) + 4px);"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" style="font-size: var(--theme-font-size);">
            <div class="space-y-8">
                {{-- Introduction --}}
                <div class="bg-white rounded-lg p-5 border-l-4 border-purple-500 shadow-sm">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-info-circle text-purple-500 mr-2"></i>What are Recurring Projects?
                    </h4>
                    <p style="color: var(--theme-text-muted); line-height: 1.6; margin-bottom: 0.75rem;">
                        <strong>Recurring Projects</strong> is a powerful automation system that automatically creates new projects for future periods (monthly or quarterly).
                        Perfect for retainer work, ongoing maintenance, subscription services, or any work that repeats regularly.
                    </p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-3">
                        <p style="color: #1e40af; font-size: calc(var(--theme-font-size) - 1px);">
                            <i class="fas fa-lightbulb mr-2"></i><strong>Example:</strong> Create "Website Maintenance Aug 2025" once, and the system automatically generates
                            "Website Maintenance Sep 2025", "Website Maintenance Oct 2025", etc. - each with the same structure, team, and budget!
                        </p>
                    </div>
                </div>

                {{-- Master vs Child Projects --}}
                <div class="bg-white rounded-lg p-5 shadow-sm">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-project-diagram text-indigo-500 mr-2"></i>Master vs Child Projects
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border-2 border-purple-300 rounded-lg p-4 bg-purple-50">
                            <div class="flex items-center mb-2">
                                <span class="px-3 py-1 bg-purple-500 text-white rounded-full text-xs font-bold mr-2">MASTER</span>
                                <span style="font-weight: 600; color: var(--theme-text);">Recurring Badge</span>
                            </div>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                The <strong>original project</strong> you create with "recurring" enabled. This is the template that gets copied.
                            </p>
                            <ul class="mt-3 space-y-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Status must be <strong>"Active"</strong></li>
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Has recurring settings (frequency, base name)</li>
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Can be edited or disabled anytime</li>
                            </ul>
                        </div>
                        <div class="border-2 border-blue-300 rounded-lg p-4 bg-blue-50">
                            <div class="flex items-center mb-2">
                                <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-bold mr-2">AUTO</span>
                                <span style="font-weight: 600; color: var(--theme-text);">Generated Badge</span>
                            </div>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Automatically created</strong> copies for future periods. These are normal projects you can use and edit.
                            </p>
                            <ul class="mt-3 space-y-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Always status <strong>"Active"</strong></li>
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Complete copy (milestones, tasks, team, budget)</li>
                                <li><i class="fas fa-check text-green-600 mr-2"></i>Work on them like any normal project</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- How It Works --}}
                <div class="bg-white rounded-lg p-5 shadow-sm">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-cogs text-blue-500 mr-2"></i>How the Automation Works
                    </h4>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold mr-3">1</div>
                            <div>
                                <p style="font-weight: 600; color: var(--theme-text);">System Checks Daily</p>
                                <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    A scheduled task runs every day and checks all active recurring master projects.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold mr-3">2</div>
                            <div>
                                <p style="font-weight: 600; color: var(--theme-text);">Time Window Check</p>
                                <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    If we're within the "days before" window (e.g., 7 days before next month), a new project is created.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold mr-3">3</div>
                            <div>
                                <p style="font-weight: 600; color: var(--theme-text);">Complete Duplication</p>
                                <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    Copies milestones, tasks, subtasks, team members, billing companies, and budget settings.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold mr-3">4</div>
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
                <div class="bg-white rounded-lg p-5 shadow-sm border-2 border-green-300">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-tasks text-green-500 mr-2"></i>Step-by-Step: Creating Your First Recurring Project
                    </h4>
                    <div class="space-y-3">
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-green-500">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-sm mr-2">STEP 1</span> Click "New Project"
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Fill in basic information: Customer, Project Name (with month/year like "SEO Oct 2025"), Start/End dates.
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-green-500">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-sm mr-2">STEP 2</span> Enable Recurring
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Scroll to "Recurring Project Settings" section and check ✓ "Make this a recurring project"
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-green-500">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-sm mr-2">STEP 3</span> Configure Settings
                            </p>
                            <ul style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);" class="space-y-2 mt-2">
                                <li><strong>Base Name:</strong> "SEO" (without month/year - that's added automatically)</li>
                                <li><strong>Frequency:</strong> Monthly or Quarterly</li>
                                <li><strong>Days Before:</strong> 7 (create new project 7 days before next period)</li>
                                <li><strong>End Date:</strong> Optional stop date (leave empty for infinite)</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-green-500">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-sm mr-2">STEP 4</span> Add Team & Budget
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Select team members and set monthly budget. These will be copied to all future projects.
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-green-500">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <span class="bg-green-500 text-white px-2 py-1 rounded text-sm mr-2">STEP 5</span> Create Project
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                Click "Create Project". Your master project is created with status "Active" (required for automation).
                            </p>
                        </div>
                        <div class="bg-green-100 rounded-lg p-4 border-2 border-green-400">
                            <p style="color: #065f46; font-weight: 600;">
                                <i class="fas fa-check-circle mr-2"></i>Done! The system will now automatically create future projects.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Settings Explained --}}
                <div class="bg-white rounded-lg p-5 shadow-sm">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-sliders-h text-orange-500 mr-2"></i>Recurring Settings Explained
                    </h4>
                    <div class="space-y-3">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-tag text-blue-500 mr-2"></i>Base Project Name
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                The name without time period. Example: "Website Maintenance" becomes "Website Maintenance Nov 2025", "Website Maintenance Dec 2025", etc.
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>Frequency
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Monthly:</strong> Creates projects for each month (Aug 2025, Sep 2025, Oct 2025...)<br>
                                <strong>Quarterly:</strong> Creates projects per quarter (Q3 2025, Q4 2025, Q1 2026...)
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-clock text-green-500 mr-2"></i>Create New Project (Days Before)
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                How many days before the new period should the project be created. Default: 7 days.<br>
                                <strong>Example:</strong> For November project with 7 days → created around October 24th.
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-stop-circle text-red-500 mr-2"></i>Stop Recurring On (Optional)
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                End date for automatic generation. Leave empty to continue indefinitely.<br>
                                <strong>Use case:</strong> 6-month contract → set end date to stop after 6 months.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Editing and Disabling --}}
                <div class="bg-white rounded-lg p-5 shadow-sm">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-edit text-indigo-500 mr-2"></i>Editing & Disabling Recurring Projects
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-pencil-alt text-blue-500 mr-2"></i>Edit Settings
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                1. Open the master project (with RECURRING badge)<br>
                                2. Find "Recurring Project Settings" card<br>
                                3. Click "Edit Settings" button<br>
                                4. Update any setting (base name, frequency, days before, end date)<br>
                                5. Save changes
                            </p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                <i class="fas fa-ban text-red-500 mr-2"></i>Disable Recurring
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
                <div class="bg-white rounded-lg p-5 shadow-sm border-2 border-blue-300">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>Practical Use Cases & Examples
                    </h4>
                    <div class="space-y-4">
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                📱 Monthly Retainer Client
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Setup:</strong> Base Name: "Client X Retainer", Frequency: Monthly, Budget: €5,000/month<br>
                                <strong>Result:</strong> Automatic projects for every month with same tasks, team, and budget<br>
                                <strong>Benefits:</strong> No manual work, consistent project structure, automated budget tracking
                            </p>
                        </div>
                        <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-lg p-4 border border-green-200">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                🌐 Website Maintenance
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Setup:</strong> Base Name: "Website Maintenance", Frequency: Monthly, Days Before: 3<br>
                                <strong>Milestones:</strong> Security Updates, Content Updates, Performance Check, Backup Verification<br>
                                <strong>Result:</strong> Ready-to-work maintenance project created 3 days before each month
                            </p>
                        </div>
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">
                                📊 Quarterly Reporting
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); line-height: 1.5;">
                                <strong>Setup:</strong> Base Name: "Quarterly Business Review", Frequency: Quarterly, Days Before: 14<br>
                                <strong>Tasks:</strong> Data collection, Analysis, Report creation, Client presentation<br>
                                <strong>Result:</strong> Q1, Q2, Q3, Q4 projects created 2 weeks before quarter start
                            </p>
                        </div>
                        <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
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
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-5 shadow-sm border-2 border-indigo-300">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>Best Practices & Tips
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="bg-white rounded-lg p-3 border border-indigo-200">
                            <p style="font-weight: 600; color: #4f46e5; font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Use clear base names
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                "Client Name - Service Type" makes it easy to identify projects
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-indigo-200">
                            <p style="font-weight: 600; color: #4f46e5; font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Set up milestones on master
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Define all recurring tasks once - they copy to all future projects
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-indigo-200">
                            <p style="font-weight: 600; color: #4f46e5; font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Keep master status Active
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Only active masters generate new projects. Pause by changing status.
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-indigo-200">
                            <p style="font-weight: 600; color: #4f46e5; font-size: calc(var(--theme-font-size) - 1px);">
                                ✅ DO: Review 'days before' setting
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                7 days is good default, but adjust based on your planning needs
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-red-200">
                            <p style="font-weight: 600; color: #dc2626; font-size: calc(var(--theme-font-size) - 1px);">
                                ❌ DON'T: Delete master projects
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Disable recurring instead - keeps history and allows re-enabling
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-red-200">
                            <p style="font-weight: 600; color: #dc2626; font-size: calc(var(--theme-font-size) - 1px);">
                                ❌ DON'T: Change status to draft/hold
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Master must stay Active or automation stops
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Troubleshooting --}}
                <div class="bg-white rounded-lg p-5 shadow-sm">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-wrench text-red-500 mr-2"></i>Troubleshooting
                    </h4>
                    <div class="space-y-3">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                ⚠️ New project not created automatically
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Check:</strong> Master project status = "Active" ✓ | "Days before" window reached ✓ | End date not passed ✓
                            </p>
                        </div>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                ⚠️ Can't find my recurring project
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Solution:</strong> Look for purple "RECURRING" badge in project list. Only master projects have this badge.
                            </p>
                        </div>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <p style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">
                                ℹ️ Want to change structure for future projects
                            </p>
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Solution:</strong> Edit milestones/tasks on the master project. Changes apply to new projects, not existing ones.
                            </p>
                        </div>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
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
                <div class="bg-white rounded-lg p-5 shadow-sm">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-question-circle text-purple-500 mr-2"></i>Frequently Asked Questions
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
                <div class="bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg p-5 border-2 border-gray-300">
                    <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-tags text-gray-600 mr-2"></i>Quick Badge Reference
                    </h4>
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="flex items-center gap-2 bg-white px-4 py-3 rounded-lg shadow">
                            <span class="px-3 py-1 bg-purple-500 text-white rounded-full text-sm font-bold">RECURRING</span>
                            <span style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 1px);">= Master Project (generates new)</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white px-4 py-3 rounded-lg shadow">
                            <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-sm font-bold">AUTO</span>
                            <span style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 1px);">= Generated Project (normal work)</span>
                        </div>
                    </div>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); margin-top: 1rem;">
                        <i class="fas fa-info-circle mr-1"></i>Both types appear in your regular project list and customer views. The badges help you identify their role.
                    </p>
                </div>

            </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-between items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-color: rgba(203, 213, 225, 0.3);">
            <p style="color: white; font-size: calc(var(--theme-font-size) - 1px);">
                <i class="fas fa-heart mr-1"></i>Need more help? Contact your administrator or check the system logs.
            </p>
            <button onclick="closeHelpModal()"
                    class="font-medium text-purple-600 bg-white rounded-md hover:opacity-90 transition-all shadow-lg"
                    style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 1.2); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                <i class="fas fa-check mr-2"></i>Got It, Thanks!
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

    // Header deleted button
    const deletedBtn = document.getElementById('header-deleted-btn');
    if (deletedBtn) {
        deletedBtn.style.backgroundColor = '#6b7280';
        deletedBtn.style.color = 'white';
        deletedBtn.style.border = 'none';
        deletedBtn.style.borderRadius = 'var(--theme-border-radius)';
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

// Floating bulk actions bar functions
function updateBulkActionsVisibility() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const floatingBar = document.getElementById('floating-bulk-actions');
    const selectedCount = document.getElementById('floating-selected-count');

    if (checkboxes.length > 0) {
        // Show floating bar with slide-up animation
        floatingBar.style.transform = 'translateY(0)';
        selectedCount.textContent = checkboxes.length + ' selected';
    } else {
        // Hide floating bar with slide-down animation
        floatingBar.style.transform = 'translateY(100%)';
    }
}

function clearAllSelections() {
    const checkboxes = document.querySelectorAll('.project-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });

    const selectAllCheckbox = document.getElementById('select-all-projects');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }

    updateBulkActionsVisibility();
}

// Select all checkbox
document.getElementById('select-all-projects')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.project-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActionsVisibility();
});

// Individual checkboxes
document.querySelectorAll('.project-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        updateBulkActionsVisibility();

        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.project-checkbox');
        const selectAllCheckbox = document.getElementById('select-all-projects');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = Array.from(allCheckboxes).every(cb => cb.checked);
        }
    });
});

// Status dropdown toggle
function toggleStatusDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('status-dropdown');
    const button = document.getElementById('status-dropdown-btn');

    if (dropdown.classList.contains('hidden')) {
        // Position dropdown above the button
        const buttonRect = button.getBoundingClientRect();
        dropdown.style.left = buttonRect.left + 'px';
        dropdown.style.bottom = (window.innerHeight - buttonRect.top + 8) + 'px'; // 8px gap
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('status-dropdown');
    const btn = document.getElementById('status-dropdown-btn');
    if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Universal Status Change Modal
let currentStatusAction = '';

const statusConfig = {
    'draft': {
        title: 'Set to Draft',
        icon: 'fas fa-file-alt',
        color: '#6b7280',              // Hardcoded gray
        bgColor: '#f3f4f6',            // Light gray background
        btnColor: '#6b7280',           // Gray button
        action: 'activate',
        displayName: 'Draft'
    },
    'active': {
        title: 'Activate Projects',
        icon: 'fas fa-play',
        color: '#10b981',              // Hardcoded green
        bgColor: '#d1fae5',            // Light green background
        btnColor: '#10b981',           // Green button
        action: 'activate',
        displayName: 'Active'
    },
    'on_hold': {
        title: 'Put Projects On Hold',
        icon: 'fas fa-pause',
        color: '#f59e0b',              // Hardcoded orange
        bgColor: '#fef3c7',            // Light orange background
        btnColor: '#f59e0b',           // Orange button
        action: 'pause',
        displayName: 'On Hold'
    },
    'completed': {
        title: 'Mark as Completed',
        icon: 'fas fa-check-circle',
        color: '#3b82f6',              // Hardcoded blue
        bgColor: '#dbeafe',            // Light blue background
        btnColor: '#3b82f6',           // Blue button
        action: 'activate',
        displayName: 'Completed'
    },
    'cancelled': {
        title: 'Cancel Projects',
        icon: 'fas fa-ban',
        color: '#ef4444',              // Hardcoded red
        bgColor: '#fee2e2',            // Light red background
        btnColor: '#ef4444',           // Red button
        action: 'activate',
        displayName: 'Cancelled'
    }
};

function openBulkStatusModal(status) {
    const count = document.querySelectorAll('.project-checkbox:checked').length;
    if (count === 0) return;

    // Close dropdown
    document.getElementById('status-dropdown').classList.add('hidden');

    // Get config for this status
    const config = statusConfig[status];
    currentStatusAction = status;

    // Update modal content
    document.getElementById('statusModalCount').textContent = count;
    document.getElementById('statusModalTitle').textContent = config.title;
    document.getElementById('statusModalStatusName').textContent = config.displayName;

    // Update icon
    const iconContainer = document.getElementById('statusModalIcon');
    const iconContent = document.getElementById('statusModalIconContent');
    iconContainer.style.backgroundColor = config.bgColor;
    iconContent.className = config.icon + ' text-2xl';
    iconContent.style.color = config.color;

    // Update button met HARDCODED styling voor maximale zichtbaarheid
    const confirmBtn = document.getElementById('statusModalConfirmBtn');
    confirmBtn.style.backgroundColor = config.btnColor;
    confirmBtn.style.color = '#ffffff';           // ALTIJD witte tekst
    confirmBtn.style.border = 'none';             // Geen border
    confirmBtn.style.fontSize = '14px';           // Vaste font size
    confirmBtn.style.fontWeight = '600';          // Bold tekst
    confirmBtn.style.padding = '0.5rem 1rem';     // Padding
    confirmBtn.style.borderRadius = '0.5rem';     // Rounded corners

    // Show modal
    document.getElementById('bulkStatusModal').style.display = 'flex';
}

function closeBulkStatusModal() {
    document.getElementById('bulkStatusModal').style.display = 'none';
    currentStatusAction = '';
}

function confirmBulkStatusChange() {
    if (currentStatusAction === 'on_hold') {
        submitBulkAction('pause');
    } else if (currentStatusAction === 'active') {
        submitBulkAction('activate');
    } else {
        // For other statuses, we need to modify submitBulkAction to handle them
        // For now, let's create a generic status change
        submitBulkStatusChange(currentStatusAction);
    }
}

// Bulk Delete Modal
function openBulkDeleteModal() {
    const count = document.querySelectorAll('.project-checkbox:checked').length;
    if (count === 0) return;
    document.getElementById('deleteProjectCount').textContent = count;
    document.getElementById('bulkDeleteModal').style.display = 'flex';
}

function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').style.display = 'none';
}

function confirmBulkDelete() {
    submitBulkAction('delete');
}

// Submit bulk action (for activate/pause/delete)
function submitBulkAction(action) {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const projectIds = Array.from(checkboxes).map(cb => cb.value);

    if (projectIds.length === 0) {
        alert('Please select at least one project');
        return;
    }

    // Create form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("projects.bulk-action") }}';

    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Action
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);

    // Project IDs
    projectIds.forEach(id => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'project_ids[]';
        idInput.value = id;
        form.appendChild(idInput);
    });

    // Submit
    document.body.appendChild(form);
    form.submit();
}

// Submit bulk status change (for all other statuses)
function submitBulkStatusChange(status) {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const projectIds = Array.from(checkboxes).map(cb => cb.value);

    if (projectIds.length === 0) {
        alert('Please select at least one project');
        return;
    }

    // Create form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("projects.bulk-action") }}';

    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Action - we'll use 'status_change' and pass the status
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'status_change';
    form.appendChild(actionInput);

    // Status
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = status;
    form.appendChild(statusInput);

    // Project IDs
    projectIds.forEach(id => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'project_ids[]';
        idInput.value = id;
        form.appendChild(idInput);
    });

    // Submit
    document.body.appendChild(form);
    form.submit();
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
        closeBulkStatusModal();
        closeBulkDeleteModal();

        // Close dropdown
        const dropdown = document.getElementById('status-dropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }
});

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush

@endsection