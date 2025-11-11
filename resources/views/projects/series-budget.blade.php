@extends('layouts.app')

@section('title', 'Series Budget Overview - ' . $project->name)

@section('content')
<div class="min-h-screen" style="background: linear-gradient(to bottom right, var(--theme-bg-secondary), var(--theme-bg-primary), var(--theme-bg-secondary));">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center" style="padding: var(--theme-card-padding);">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('projects.show', $project->id) }}" style="color: var(--theme-text-muted);" class="hover:opacity-75 transition-opacity">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-text);">Series Budget Overview</h1>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Recurring Series
                        </span>
                    </div>
                    <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                        {{ $project->customer->name ?? 'Customer' }} - Consolidated budget for {{ $seriesProjects->count() }} project(s) in {{ $year }}
                    </p>
                    @if($project->fee_rollover_enabled)
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-success); margin-top: 0.25rem;">✓ Rollover enabled - unused budget carries forward across all projects</p>
                    @else
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">Rollover disabled - budget resets each month</p>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    {{-- Back to Project Button --}}
                    <a href="{{ route('projects.show', $project->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg transition-all hover:opacity-75"
                       style="background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Project</span>
                    </a>

                    {{-- Help Button --}}
                    <button onclick="toggleHelpModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg transition-all hover:opacity-75"
                            style="background-color: rgba(var(--theme-info-rgb), 0.1); color: var(--theme-info); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                        <i class="fas fa-question-circle"></i>
                        <span>Help</span>
                    </button>

                    {{-- Year Selector --}}
                    <form method="GET" action="{{ route('projects.series-budget', $project->id) }}" class="flex items-center gap-2">
                        <label style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text);">Year:</label>
                        <select name="year" onchange="this.form.submit()" class="rounded-lg border text-sm" style="border-color: var(--theme-border); color: var(--theme-text);">
                            @foreach($availableYears as $availableYear)
                                <option value="{{ $availableYear }}" {{ $availableYear == $year ? 'selected' : '' }}>
                                    {{ $availableYear }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.1); border-color: var(--theme-success); padding: 1rem;">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" style="color: var(--theme-success);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-success);">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Year Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- Total Base Budget --}}
            <div class="bg-white/60 backdrop-blur-sm border rounded-xl hover:shadow-md transition-all" style="border-color: rgba(var(--theme-border-rgb), 0.3); padding: var(--theme-card-padding);">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Base Budget</p>
                        <p style="font-size: calc(var(--theme-font-size) + 10px); font-weight: 700; color: var(--theme-text); margin-top: 0.25rem;">€{{ number_format($yearTotals['total_base_budget'], 2) }}</p>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                            {{ count($monthsData) }} active month{{ count($monthsData) != 1 ? 's' : '' }} × €{{ number_format($project->monthly_fee, 2) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-info-rgb), 0.1);">
                        <svg class="w-6 h-6" style="color: var(--theme-info);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Used --}}
            <div class="bg-white/60 backdrop-blur-sm border rounded-xl hover:shadow-md transition-all" style="border-color: rgba(var(--theme-border-rgb), 0.3); padding: var(--theme-card-padding);">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Used</p>
                        <p style="font-size: calc(var(--theme-font-size) + 10px); font-weight: 700; color: var(--theme-text); margin-top: 0.25rem;">€{{ number_format($yearTotals['total_used'], 2) }}</p>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">{{ number_format($yearTotals['total_hours'], 1) }} hours worked</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                        <svg class="w-6 h-6" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Remaining --}}
            <div class="bg-white/60 backdrop-blur-sm border rounded-xl hover:shadow-md transition-all" style="border-color: rgba(var(--theme-border-rgb), 0.3); padding: var(--theme-card-padding);">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Remaining</p>
                        <p style="font-size: calc(var(--theme-font-size) + 10px); font-weight: 700; color: {{ $yearTotals['total_remaining'] > 0 ? 'var(--theme-success)' : 'var(--theme-text-muted)' }}; margin-top: 0.25rem;">
                            €{{ number_format($yearTotals['total_remaining'], 2) }}
                        </p>
                        @php
                            $totalAvailable = $yearTotals['total_budget'];
                            $percentage = $totalAvailable > 0 ? round(($yearTotals['total_remaining'] / $totalAvailable) * 100, 1) : 0;
                        @endphp
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                            {{ $yearTotals['total_remaining'] > 0 ? $percentage . '% of budget available' : 'Budget exceeded' }}
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba({{ $yearTotals['total_remaining'] > 0 ? 'var(--theme-success-rgb)' : 'var(--theme-text-muted-rgb)' }}, 0.1);">
                        <svg class="w-6 h-6" style="color: {{ $yearTotals['total_remaining'] > 0 ? 'var(--theme-success)' : 'var(--theme-text-muted)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Exceeded --}}
            <div class="bg-white/60 backdrop-blur-sm border rounded-xl hover:shadow-md transition-all" style="border-color: rgba(var(--theme-border-rgb), 0.3); padding: var(--theme-card-padding);">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Exceeded</p>
                        <p style="font-size: calc(var(--theme-font-size) + 10px); font-weight: 700; color: {{ $yearTotals['total_exceeded'] > 0 ? 'var(--theme-danger)' : 'var(--theme-text-muted)' }}; margin-top: 0.25rem;">
                            €{{ number_format($yearTotals['total_exceeded'], 2) }}
                        </p>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                            {{ $yearTotals['total_exceeded'] > 0 ? 'Over budget' : 'No overruns' }}
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba({{ $yearTotals['total_exceeded'] > 0 ? 'var(--theme-danger-rgb)' : 'var(--theme-text-muted-rgb)' }}, 0.1);">
                        <svg class="w-6 h-6" style="color: {{ $yearTotals['total_exceeded'] > 0 ? 'var(--theme-danger)' : 'var(--theme-text-muted)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Budget Table --}}
        <div class="bg-white/60 backdrop-blur-sm border rounded-xl overflow-hidden" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
            <div class="border-b" style="border-color: rgba(var(--theme-border-rgb), 0.3); padding: var(--theme-card-padding);">
                <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Consolidated Monthly Budget {{ $year }}</h2>
                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;">Combined budget tracking across all {{ $seriesProjects->count() }} project(s) in the series</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" style="border-color: var(--theme-border);">
                    <thead style="background-color: rgba(var(--theme-text-muted-rgb), 0.05);">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Month</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Monthly Budget</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Rollover In</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Budget</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Used</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Remaining</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Exceeded</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Rollover Out</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Hours</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y" style="border-color: var(--theme-border);">
                        @forelse($monthsData as $monthData)
                        <tr class="hover:bg-slate-50 transition-colors">
                            {{-- Month Name --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                <div class="flex items-center gap-2">
                                    <span style="font-weight: 500; color: var(--theme-text);">{{ $monthData['month_name'] }}</span>
                                    @if($monthData['project_count'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);"
                                              title="{{ implode(', ', $monthData['projects_with_data']) }}">
                                            {{ $monthData['project_count'] }} project(s)
                                        </span>
                                    @endif
                                    {{-- Data Source Badge --}}
                                    @if($monthData['from_invoice'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              style="background-color: rgba(var(--theme-info-rgb), 0.1); color: var(--theme-info);"
                                              title="Data from invoice #{{ $monthData['invoice_id'] }}">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Invoice
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              style="background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);"
                                              title="Data from time entries">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Time
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Monthly Budget --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                €{{ number_format($monthData['base_monthly_fee'], 2) }}
                            </td>

                            {{-- Rollover In --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size);">
                                @if($monthData['rollover_from_previous'] != 0)
                                    @if($monthData['rollover_from_previous'] > 0)
                                        <span style="font-weight: 500; color: var(--theme-success);">+€{{ number_format($monthData['rollover_from_previous'], 2) }}</span>
                                    @else
                                        <span style="font-weight: 500; color: var(--theme-danger);">-€{{ number_format(abs($monthData['rollover_from_previous']), 2) }}</span>
                                    @endif
                                @else
                                    <span style="color: var(--theme-text-muted);">-</span>
                                @endif
                            </td>

                            {{-- Total Budget --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-text);">
                                €{{ number_format($monthData['total_budget'], 2) }}
                            </td>

                            {{-- Used --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-primary);">
                                €{{ number_format($monthData['budget_used'], 2) }}
                            </td>

                            {{-- Remaining --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size);">
                                @if($monthData['remaining'] > 0)
                                    <span style="font-weight: 500; color: var(--theme-success);">€{{ number_format($monthData['remaining'], 2) }}</span>
                                @else
                                    <span style="color: var(--theme-text-muted);">-</span>
                                @endif
                            </td>

                            {{-- Exceeded --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size);">
                                @if($monthData['exceeded'] > 0)
                                    <span style="font-weight: 500; color: var(--theme-danger);">€{{ number_format($monthData['exceeded'], 2) }}</span>
                                @else
                                    <span style="color: var(--theme-text-muted);">-</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: center;">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $monthData['status_class'] }}">
                                    {{ $monthData['status_label'] }}
                                </span>
                            </td>

                            {{-- Rollover Out --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size);">
                                @if($project->fee_rollover_enabled && $monthData['rollover_to_next'] != 0)
                                    @if($monthData['rollover_to_next'] > 0)
                                        <span style="font-weight: 500; color: var(--theme-success);">+€{{ number_format($monthData['rollover_to_next'], 2) }}</span>
                                    @else
                                        <span style="font-weight: 500; color: var(--theme-danger);">-€{{ number_format(abs($monthData['rollover_to_next']), 2) }}</span>
                                    @endif
                                @else
                                    <span style="color: var(--theme-text-muted);">-</span>
                                @endif
                            </td>

                            {{-- Hours --}}
                            <td style="padding: 0.75rem 1rem; white-space: nowrap; text-align: center; font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                                {{ number_format($monthData['hours_worked'], 1) }}h
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" style="padding: 3rem 1.5rem; text-align: center;">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 mb-4" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">No Active Months in {{ $year }}</h3>
                                    <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">This project series has no active budget months in {{ $year }}.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse

                        {{-- Totals Row --}}
                        @if(!empty($monthsData))
                        <tr style="background-color: rgba(var(--theme-text-muted-rgb), 0.05); font-weight: 600;">
                            <td style="padding: 0.75rem 1rem; font-size: var(--theme-font-size); color: var(--theme-text);">TOTALS</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-size: var(--theme-font-size); color: var(--theme-text);">€{{ number_format($yearTotals['total_base_budget'], 2) }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-size: var(--theme-font-size); color: var(--theme-text);">
                                @if($yearTotals['total_rollover_in'] != 0)
                                    @if($yearTotals['total_rollover_in'] > 0)
                                        <span style="color: var(--theme-success);">+€{{ number_format($yearTotals['total_rollover_in'], 2) }}</span>
                                    @else
                                        <span style="color: var(--theme-danger);">-€{{ number_format(abs($yearTotals['total_rollover_in']), 2) }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-size: var(--theme-font-size); color: var(--theme-text);">€{{ number_format($yearTotals['total_budget'], 2) }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-size: var(--theme-font-size); color: var(--theme-primary);">€{{ number_format($yearTotals['total_used'], 2) }}</td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-size: var(--theme-font-size); color: {{ $yearTotals['total_remaining'] > 0 ? 'var(--theme-success)' : 'var(--theme-text-muted)' }};">
                                {{ $yearTotals['total_remaining'] > 0 ? '€' . number_format($yearTotals['total_remaining'], 2) : '-' }}
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-size: var(--theme-font-size); color: {{ $yearTotals['total_exceeded'] > 0 ? 'var(--theme-danger)' : 'var(--theme-text-muted)' }};">
                                {{ $yearTotals['total_exceeded'] > 0 ? '€' . number_format($yearTotals['total_exceeded'], 2) : '-' }}
                            </td>
                            <td style="padding: 0.75rem 1rem;"></td>
                            <td style="padding: 0.75rem 1rem;"></td>
                            <td style="padding: 0.75rem 1rem; text-align: center; font-size: var(--theme-font-size); color: var(--theme-text);">{{ number_format($yearTotals['total_hours'], 1) }}h</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="border-t" style="padding: var(--theme-card-padding); background-color: rgba(var(--theme-text-muted-rgb), 0.05); border-color: var(--theme-border);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: var(--theme-success);"></div>
                            <span>On Track</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <span>Warning (&gt;80%)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: var(--theme-danger);"></div>
                            <span>Over Budget</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: var(--theme-text-muted);"></div>
                            <span>Not Started</span>
                        </div>
                    </div>
                    <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                        Last calculated: {{ now()->format('d-m-Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Series Information Card --}}
        <div class="bg-white/60 backdrop-blur-sm border rounded-xl overflow-hidden mt-6" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
            <div class="border-b" style="border-color: rgba(var(--theme-border-rgb), 0.3); padding: var(--theme-card-padding);">
                <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 500; color: var(--theme-text);">
                    <i class="fas fa-sync-alt mr-2" style="color: var(--theme-info);"></i>
                    Series Overview
                </h3>
            </div>
            <div style="padding: var(--theme-card-padding);">
                <div class="border rounded-lg" style="background-color: rgba(var(--theme-info-rgb), 0.05); border-color: var(--theme-info); padding: 1rem;">
                    <h3 style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-info); margin-bottom: 0.5rem;">Recurring Project Series - {{ $seriesProjects->count() }} Project(s)</h3>
                    <div class="space-y-1">
                        @foreach($seriesProjects as $seriesProject)
                        <div class="flex items-center justify-between" style="font-size: calc(var(--theme-font-size) - 1px);">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    style="{{ $seriesProject->id == $project->id ? 'background-color: var(--theme-info); color: #ffffff;' : 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text);' }}">
                                    {{ $seriesProject->parent_recurring_project_id == null ? 'Parent' : 'Child' }}
                                </span>
                                <a href="{{ route('projects.show', $seriesProject->id) }}" class="hover:underline" style="color: var(--theme-info); font-weight: 500;">
                                    {{ $seriesProject->name }}
                                </a>
                            </div>
                            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                                @if($seriesProject->start_date && $seriesProject->end_date)
                                    {{ \Carbon\Carbon::parse($seriesProject->start_date)->format('M Y') }} - {{ \Carbon\Carbon::parse($seriesProject->end_date)->format('M Y') }}
                                @elseif($seriesProject->start_date)
                                    Started {{ \Carbon\Carbon::parse($seriesProject->start_date)->format('M Y') }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-info); margin-top: 0.75rem;">
                        <strong>How it works:</strong> This view consolidates budget tracking across all {{ $seriesProjects->count() }} project(s) in this recurring series.
                        Each month shows combined hours and costs from all active projects, with rollover calculated across the entire series.
                        @php
                            $earliestStart = $seriesProjects->sortBy('start_date')->first()->start_date ?? null;
                        @endphp
                        @if($earliestStart)
                            Budget tracking starts from <strong>{{ \Carbon\Carbon::parse($earliestStart)->format('F Y') }}</strong> (when the first project began).
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Help Modal --}}
<div id="helpModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true" onclick="toggleHelpModal()"></div>

        {{-- Modal panel --}}
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            {{-- Header --}}
            <div class="border-b" style="background-color: rgba(var(--theme-info-rgb), 0.05); border-color: var(--theme-border); padding: 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center rounded-full" style="width: 40px; height: 40px; background-color: rgba(var(--theme-info-rgb), 0.1);">
                            <i class="fas fa-question-circle" style="font-size: 20px; color: var(--theme-info);"></i>
                        </div>
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 700; color: var(--theme-text);">Series Budget Overview - Help Guide</h3>
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Understanding budget data sources and calculations</p>
                        </div>
                    </div>
                    <button onclick="toggleHelpModal()" class="rounded-lg p-2 hover:opacity-75 transition-opacity" style="color: var(--theme-text-muted);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Content --}}
            <div style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                {{-- Data Sources Section --}}
                <div class="mb-6">
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">
                        <i class="fas fa-database mr-2" style="color: var(--theme-info);"></i>
                        How Budget Data is Calculated
                    </h4>
                    <div class="border rounded-lg" style="background-color: rgba(var(--theme-warning-rgb), 0.05); border-color: var(--theme-warning); padding: 1rem; margin-bottom: 1rem;">
                        <p style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.6;">
                            <strong>Important:</strong> The system automatically determines where to get budget data for each month based on whether an invoice has been created.
                        </p>
                    </div>

                    <div class="space-y-4">
                        {{-- Invoice Data Source --}}
                        <div class="border rounded-lg" style="border-color: rgba(var(--theme-info-rgb), 0.3); padding: 1rem;">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium" style="background-color: rgba(var(--theme-info-rgb), 0.1); color: var(--theme-info);">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Invoice Data
                                    </span>
                                </div>
                                <div>
                                    <h5 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">When an Invoice Exists</h5>
                                    <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); line-height: 1.6; margin-bottom: 0.5rem;">
                                        When you have created and finalized an invoice for a specific month, the "Budget Used" data comes from the invoice:
                                    </p>
                                    <ul style="list-style: disc; padding-left: 1.5rem; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); line-height: 1.6;">
                                        <li><strong>Source:</strong> Invoice → "Used from Budget" amount (work_amount + service_amount)</li>
                                        <li><strong>Why:</strong> The invoice represents the final, approved amount for that period</li>
                                        <li><strong>Benefit:</strong> Budget tracking uses definitive, invoiced amounts instead of estimates</li>
                                        <li><strong>Indicator:</strong> Blue "Invoice" badge appears next to the month name</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Time Entry Data Source --}}
                        <div class="border rounded-lg" style="border-color: rgba(var(--theme-text-muted-rgb), 0.3); padding: 1rem;">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium" style="background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Time Entry Data
                                    </span>
                                </div>
                                <div>
                                    <h5 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">When No Invoice Exists Yet</h5>
                                    <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); line-height: 1.6; margin-bottom: 0.5rem;">
                                        Before an invoice is created, the system calculates budget usage from approved time entries:
                                    </p>
                                    <ul style="list-style: disc; padding-left: 1.5rem; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); line-height: 1.6;">
                                        <li><strong>Source:</strong> Time Entries (approved + billable only)</li>
                                        <li><strong>Calculation:</strong> Hours worked × Hourly rate = Budget Used</li>
                                        <li><strong>Filters:</strong> Only approved and billable time entries are counted</li>
                                        <li><strong>Benefit:</strong> Real-time budget tracking before invoicing</li>
                                        <li><strong>Indicator:</strong> Gray "Time" badge appears next to the month name</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Workflow Section --}}
                <div class="mb-6">
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">
                        <i class="fas fa-project-diagram mr-2" style="color: var(--theme-success);"></i>
                        Typical Workflow
                    </h4>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 rounded-full flex items-center justify-center" style="width: 32px; height: 32px; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-weight: 700; font-size: calc(var(--theme-font-size) - 2px);">1</div>
                            <div>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">During the Month</h5>
                                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Team members log time entries for work performed. Budget tracking shows real-time usage based on approved hours.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 rounded-full flex items-center justify-center" style="width: 32px; height: 32px; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-weight: 700; font-size: calc(var(--theme-font-size) - 2px);">2</div>
                            <div>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">End of Month</h5>
                                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Review budget usage via time entries. Adjust if needed before creating invoice.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 rounded-full flex items-center justify-center" style="width: 32px; height: 32px; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-weight: 700; font-size: calc(var(--theme-font-size) - 2px);">3</div>
                            <div>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">Create Invoice</h5>
                                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Generate invoice with final amounts. The "Used from Budget" amount becomes the definitive source.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 rounded-full flex items-center justify-center" style="width: 32px; height: 32px; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-weight: 700; font-size: calc(var(--theme-font-size) - 2px);">4</div>
                            <div>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">After Invoice</h5>
                                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Budget overview automatically switches to invoice data. Blue "Invoice" badge confirms this.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Key Benefits Section --}}
                <div class="mb-6">
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">
                        <i class="fas fa-star mr-2" style="color: var(--theme-warning);"></i>
                        Key Benefits
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="border rounded-lg" style="border-color: var(--theme-border); padding: 0.75rem;">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-check-circle" style="color: var(--theme-success);"></i>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">Accuracy</h5>
                            </div>
                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Final invoiced amounts are always used once available, ensuring accurate historical tracking.</p>
                        </div>
                        <div class="border rounded-lg" style="border-color: var(--theme-border); padding: 0.75rem;">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-chart-line" style="color: var(--theme-success);"></i>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">Real-time</h5>
                            </div>
                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Monitor budget usage in real-time before invoicing via time entry calculations.</p>
                        </div>
                        <div class="border rounded-lg" style="border-color: var(--theme-border); padding: 0.75rem;">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-sync-alt" style="color: var(--theme-success);"></i>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">Automatic</h5>
                            </div>
                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">No manual intervention needed - system automatically switches data sources.</p>
                        </div>
                        <div class="border rounded-lg" style="border-color: var(--theme-border); padding: 0.75rem;">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-eye" style="color: var(--theme-success);"></i>
                                <h5 style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">Transparent</h5>
                            </div>
                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Clear badges show exactly where each month's data comes from.</p>
                        </div>
                    </div>
                </div>

                {{-- Important Notes --}}
                <div class="border rounded-lg" style="background-color: rgba(var(--theme-info-rgb), 0.05); border-color: var(--theme-info); padding: 1rem;">
                    <h4 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-info); margin-bottom: 0.75rem;">
                        <i class="fas fa-info-circle mr-2"></i>
                        Important Notes
                    </h4>
                    <ul style="list-style: disc; padding-left: 1.5rem; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); line-height: 1.6; space-y: 0.25rem;">
                        <li><strong>Non-billable time:</strong> Only billable hours are counted in budget calculations</li>
                        <li><strong>Approved only:</strong> Only approved time entries are included in calculations</li>
                        <li><strong>Invoice priority:</strong> Once an invoice exists, it always takes precedence over time entries</li>
                        <li><strong>Rollover impact:</strong> Budget rollover is calculated correctly regardless of data source</li>
                        <li><strong>Historical data:</strong> Invoiced months remain accurate even if time entries are later modified</li>
                    </ul>
                </div>
            </div>

            {{-- Footer --}}
            <div class="border-t" style="background-color: rgba(var(--theme-text-muted-rgb), 0.03); border-color: var(--theme-border); padding: 1rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                        <i class="fas fa-lightbulb mr-1" style="color: var(--theme-warning);"></i>
                        Tip: Hover over the data source badges in the table to see more details
                    </p>
                    <button onclick="toggleHelpModal()" class="px-4 py-2 rounded-lg font-medium transition-all" style="background-color: var(--theme-primary); color: #ffffff; font-size: calc(var(--theme-font-size) - 1px);">
                        Got it!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleHelpModal() {
    const modal = document.getElementById('helpModal');
    modal.classList.toggle('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('helpModal');
        if (!modal.classList.contains('hidden')) {
            toggleHelpModal();
        }
    }
});
</script>
@endpush
@endsection
