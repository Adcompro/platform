@extends('layouts.app')

@section('title', 'Year Budget Overview - ' . $project->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('projects.show', $project->id) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-2xl font-bold text-slate-900">Year Budget Overview</h1>
                    </div>
                    <p class="text-sm text-slate-600">
                        {{ $project->name }} - Budget tracking for {{ $year }}
                        @if($project->start_date && $project->end_date)
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-500">
                                {{ \Carbon\Carbon::parse($project->start_date)->format('M Y') }} -
                                {{ \Carbon\Carbon::parse($project->end_date)->format('M Y') }}
                            </span>
                        @elseif($project->start_date)
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-500">Started {{ \Carbon\Carbon::parse($project->start_date)->format('M Y') }}</span>
                        @endif
                    </p>
                    @if($project->fee_rollover_enabled)
                        <p class="text-xs text-green-600 mt-1">✓ Rollover enabled - unused budget carries forward</p>
                    @else
                        <p class="text-xs text-slate-500 mt-1">Rollover disabled - budget resets each month</p>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    {{-- Year Selector --}}
                    <form method="GET" action="{{ route('projects.year-budget', $project->id) }}" class="flex items-center gap-2">
                        <label class="text-sm font-medium text-slate-700">Year:</label>
                        <select name="year" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:ring-slate-500 focus:border-slate-500">
                            @foreach($availableYears as $availableYear)
                                <option value="{{ $availableYear }}" {{ $availableYear == $year ? 'selected' : '' }}>
                                    {{ $availableYear }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Recalculate Button (alleen voor admin) --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <form method="POST" action="{{ route('projects.recalculate-year', $project->id) }}" onsubmit="return confirm('Recalculate all 12 months for {{ $year }}? This will update all budget calculations.');">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-all duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Recalculate Year
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recurring Series Banner --}}
        @if($isPartOfSeries)
        <div class="mb-6 bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-lg font-bold text-purple-900">Recurring Project Series Detected</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-600 text-white">
                            Recommended View
                        </span>
                    </div>
                    <p class="text-sm text-purple-800 mb-3">
                        <strong>Important:</strong> This project is part of a recurring series with multiple related projects.
                        For accurate budget tracking across all projects in the series, use the <strong>Consolidated Series View</strong>.
                    </p>
                    <div class="flex items-start gap-3 text-sm text-purple-700 mb-4">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium mb-1">What you're seeing now:</p>
                            <p>This page shows budget data for <strong>{{ $project->name }}</strong> only. It may not include all related projects in the series.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 text-sm text-purple-700 mb-4">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium mb-1">Series view shows:</p>
                            <p>Combined budget tracking across <strong>all projects</strong> in the recurring series, with proper rollover calculations and consolidated over/under spend reporting.</p>
                        </div>
                    </div>
                    <a href="{{ route('projects.series-budget', ['project' => $seriesParentId, 'year' => $year]) }}"
                       class="inline-flex items-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        View Consolidated Series Budget
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Year Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- Total Base Budget --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 uppercase tracking-wider">Total Base Budget</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">€{{ number_format($yearTotals['total_base_budget'], 2) }}</p>
                        <p class="text-xs text-slate-500 mt-1">12 months × €{{ number_format($project->monthly_fee, 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Used --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 uppercase tracking-wider">Total Used</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">€{{ number_format($yearTotals['total_used'], 2) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ number_format($yearTotals['total_hours'], 1) }} hours worked</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Remaining --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 uppercase tracking-wider">Total Remaining</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">€{{ number_format($yearTotals['total_remaining'], 2) }}</p>
                        @php
                            $totalAvailable = $yearTotals['total_budget'];
                            $percentage = $totalAvailable > 0 ? round(($yearTotals['total_remaining'] / $totalAvailable) * 100, 1) : 0;
                        @endphp
                        <p class="text-xs text-slate-500 mt-1">{{ $percentage }}% of budget available</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Exceeded --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 uppercase tracking-wider">Total Exceeded</p>
                        <p class="text-2xl font-bold {{ $yearTotals['total_exceeded'] > 0 ? 'text-red-600' : 'text-slate-400' }} mt-1">
                            €{{ number_format($yearTotals['total_exceeded'], 2) }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $yearTotals['total_exceeded'] > 0 ? 'Over budget' : 'No overruns' }}
                        </p>
                    </div>
                    <div class="w-12 h-12 {{ $yearTotals['total_exceeded'] > 0 ? 'bg-red-100' : 'bg-slate-100' }} rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 {{ $yearTotals['total_exceeded'] > 0 ? 'text-red-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Budget Table --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/50">
                <h2 class="text-lg font-semibold text-slate-900">Monthly Budget Breakdown {{ $year }}</h2>
                <p class="text-sm text-slate-600 mt-1">Track budget usage and rollover for each month</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Monthly Budget</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Rollover In
                                <svg class="inline w-3 h-3 text-slate-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Total Budget</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Used</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Remaining</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Exceeded</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Rollover Out
                                <svg class="inline w-3 h-3 text-slate-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Hours</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @if(empty($monthsData))
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-slate-900 mb-2">No Active Months in {{ $year }}</h3>
                                    <p class="text-sm text-slate-600 mb-4">
                                        This project has no active budget months in {{ $year }}.
                                    </p>
                                    @if($project->start_date)
                                    <p class="text-xs text-slate-500">
                                        Project runs from {{ \Carbon\Carbon::parse($project->start_date)->format('M Y') }}
                                        @if($project->end_date)
                                            to {{ \Carbon\Carbon::parse($project->end_date)->format('M Y') }}
                                        @endif
                                    </p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @else
                        @foreach($monthsData as $monthData)
                        <tr class="hover:bg-slate-50 transition-colors">
                            {{-- Month Name --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="font-medium text-slate-900">{{ $monthData['month_name'] }}</span>
                                    @if($monthData['is_finalized'])
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            Finalized
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Monthly Budget --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-slate-900">
                                €{{ number_format($monthData['base_monthly_fee'], 2) }}
                            </td>

                            {{-- Rollover In --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                @if($monthData['rollover_from_previous'] > 0)
                                    <span class="font-medium text-green-600">+€{{ number_format($monthData['rollover_from_previous'], 2) }}</span>
                                @elseif($monthData['rollover_from_previous'] < 0)
                                    <span class="font-medium text-red-600">-€{{ number_format(abs($monthData['rollover_from_previous']), 2) }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Total Budget --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold text-slate-900">
                                €{{ number_format($monthData['total_budget'], 2) }}
                            </td>

                            {{-- Used --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-purple-600">
                                €{{ number_format($monthData['budget_used'], 2) }}
                            </td>

                            {{-- Remaining --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                @if($monthData['remaining'] > 0)
                                    <span class="font-medium text-green-600">€{{ number_format($monthData['remaining'], 2) }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Exceeded --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                @if($monthData['exceeded'] > 0)
                                    <span class="font-medium text-red-600">€{{ number_format($monthData['exceeded'], 2) }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $monthData['status_class'] }}">
                                    {{ $monthData['status_label'] }}
                                </span>
                            </td>

                            {{-- Rollover Out --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                @if($project->fee_rollover_enabled && $monthData['rollover_to_next'] > 0)
                                    <span class="font-medium text-blue-600">€{{ number_format($monthData['rollover_to_next'], 2) }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- Hours --}}
                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-slate-600">
                                {{ number_format($monthData['hours_worked'], 1) }}h
                            </td>
                        </tr>
                        @endforeach

                        {{-- Totals Row (alleen tonen als er data is) --}}
                        @if(!empty($monthsData))
                        <tr class="bg-slate-100 font-semibold">
                            <td class="px-4 py-3 text-sm text-slate-900">TOTALS</td>
                            <td class="px-4 py-3 text-right text-sm text-slate-900">€{{ number_format($yearTotals['total_base_budget'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-slate-900">
                                @if($yearTotals['total_rollover_in'] > 0)
                                    <span class="text-green-600">+€{{ number_format($yearTotals['total_rollover_in'], 2) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-slate-900">€{{ number_format($yearTotals['total_budget'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-purple-600">€{{ number_format($yearTotals['total_used'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-green-600">€{{ number_format($yearTotals['total_remaining'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm {{ $yearTotals['total_exceeded'] > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                {{ $yearTotals['total_exceeded'] > 0 ? '€' . number_format($yearTotals['total_exceeded'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-center text-sm text-slate-900">{{ number_format($yearTotals['total_hours'], 1) }}h</td>
                        </tr>
                        @endif
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span>On Track</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <span>Warning (&gt;80%)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <span>Over Budget</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                            <span>Not Started</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500">
                        Last calculated: {{ now()->format('d-m-Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Series Information Card --}}
        @if(isset($seriesProjects) && $seriesProjects->count() > 1)
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-medium text-slate-900">
                    <i class="fas fa-sync-alt text-blue-600 mr-2"></i>
                    Series Overview
                </h3>
            </div>
            <div class="p-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Recurring Project Series - {{ $seriesProjects->count() }} Project(s)</h3>
                    <div class="space-y-1">
                        @foreach($seriesProjects as $seriesProject)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $seriesProject->id == $project->id ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $seriesProject->parent_recurring_project_id == null ? 'Parent' : 'Child' }}
                                </span>
                                <a href="{{ route('projects.show', $seriesProject->id) }}" class="text-blue-700 hover:text-blue-900 font-medium hover:underline">
                                    {{ $seriesProject->name }}
                                </a>
                            </div>
                            <div class="text-xs text-slate-600">
                                @if($seriesProject->start_date && $seriesProject->end_date)
                                    {{ \Carbon\Carbon::parse($seriesProject->start_date)->format('M Y') }} - {{ \Carbon\Carbon::parse($seriesProject->end_date)->format('M Y') }}
                                @elseif($seriesProject->start_date)
                                    Started {{ \Carbon\Carbon::parse($seriesProject->start_date)->format('M Y') }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-blue-700 mt-3">
                        <strong>How it works:</strong> This view shows budget tracking for the current project only.
                        For consolidated tracking across all {{ $seriesProjects->count() }} project(s) in this series,
                        <a href="{{ route('projects.series-budget', ['project' => $project->id, 'year' => $year]) }}" class="underline font-medium hover:text-blue-900">
                            view the series budget
                        </a>.
                        @php
                            $earliestStart = $seriesProjects->sortBy('start_date')->first()->start_date ?? null;
                        @endphp
                        @if($earliestStart)
                            This series started from <strong>{{ \Carbon\Carbon::parse($earliestStart)->format('F Y') }}</strong>.
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
