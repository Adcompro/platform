@extends('layouts.app')

@section('title', 'Budget Tracking')

@section('content')
{{-- Sticky Header --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">
                    <i class="fas fa-chart-line mr-2" style="color: var(--theme-primary);"></i>
                    Budget Tracking Dashboard
                </h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Monthly budget overview for recurring series and individual projects</p>
            </div>

            {{-- Filters & Refresh Button --}}
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('recurring-dashboard') }}" class="flex items-center gap-4" id="filter-form">
                    {{-- Company Filter --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']) && $allCompanies->count() > 1)
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium" style="color: var(--theme-text);">Company:</label>
                        <select name="company_id" onchange="this.form.submit()"
                                class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2"
                                style="border-color: rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text); font-size: var(--theme-font-size); min-width: 200px;">
                            <option value="">All Companies</option>
                            @foreach($allCompanies as $company)
                                <option value="{{ $company->id }}" {{ $selectedCompanyId == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Year Filter --}}
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium" style="color: var(--theme-text);">Year:</label>
                        <select name="year" onchange="this.form.submit()"
                                class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2"
                                style="border-color: rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text); font-size: var(--theme-font-size);">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                {{-- Refresh Data Button --}}
                <form method="POST" action="{{ route('recurring-dashboard.refresh') }}" id="refresh-form" class="inline">
                    @csrf
                    <input type="hidden" name="year" value="{{ $currentYear }}">
                    @if($selectedCompanyId)
                    <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                    @endif
                    <button type="button" onclick="confirmRefresh()"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-sync-alt mr-1.5"></i>
                        Refresh Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div style="padding: 1.5rem 2rem;">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-md" style="background-color: rgba(var(--theme-success-rgb), 0.1); border: 1px solid rgba(var(--theme-success-rgb), 0.3); color: var(--theme-success);">
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
            <div class="mb-6 px-4 py-3 rounded-md" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border: 1px solid rgba(var(--theme-danger-rgb), 0.3); color: var(--theme-danger);">
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

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div style="padding: 1.5rem;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Total Budget {{ $currentYear }}</p>
                            <p class="text-2xl font-bold mt-1" style="color: var(--theme-primary);">€{{ number_format($grandTotals['budget'], 0, ',', '.') }}</p>
                        </div>
                        <div class="p-3 rounded-full" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <i class="fas fa-wallet text-xl" style="color: var(--theme-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div style="padding: 1.5rem;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Total Spent</p>
                            <p class="text-2xl font-bold mt-1" style="color: #ef4444;">€{{ number_format($grandTotals['spent'], 0, ',', '.') }}</p>
                        </div>
                        <div class="p-3 rounded-full" style="background-color: rgba(239, 68, 68, 0.1);">
                            <i class="fas fa-money-bill-wave text-xl" style="color: #ef4444;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div style="padding: 1.5rem;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Variance</p>
                            <p class="text-2xl font-bold mt-1" style="color: {{ $grandTotals['variance'] >= 0 ? '#10b981' : '#ef4444' }};">
                                €{{ number_format($grandTotals['variance'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-3 rounded-full" style="background-color: rgba({{ $grandTotals['variance'] >= 0 ? '16, 185, 129' : '239, 68, 68' }}, 0.1);">
                            <i class="fas fa-chart-line text-xl" style="color: {{ $grandTotals['variance'] >= 0 ? '#10b981' : '#ef4444' }};"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div style="padding: 1.5rem;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Total Hours</p>
                            <p class="text-2xl font-bold mt-1" style="color: var(--theme-text);">{{ number_format($grandTotals['hours'], 0) }}h</p>
                        </div>
                        <div class="p-3 rounded-full" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <i class="fas fa-clock text-xl" style="color: var(--theme-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- OVERSPENT PROJECTS --}}
        @if(count($overspentProjects) > 0)
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="border-b border-gray-200 flex justify-between items-center" style="padding: 1rem 1.5rem;">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded" style="background-color: #ef4444;"></div>
                    <h2 class="text-lg font-semibold" style="color: #ef4444;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Overspent Projects ({{ count($overspentProjects) }})
                    </h2>
                </div>
                <div class="text-sm" style="color: var(--theme-text-muted);">
                    Total Variance: <span class="font-bold" style="color: #ef4444;">€{{ number_format($overspentTotals['variance'], 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Series</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            @foreach($months as $month)
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $month['name'] }}</th>
                            @endforeach
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($overspentProjects as $series)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" style="color: var(--theme-text);">
                                    @if($series['is_individual'] ?? false)
                                        <i class="fas fa-file-alt mr-1" style="color: var(--theme-info); opacity: 0.7;" title="Individual Project"></i>
                                    @else
                                        <i class="fas fa-layer-group mr-1" style="color: var(--theme-primary); opacity: 0.7;" title="Recurring Series"></i>
                                    @endif
                                    {{ $series['series_name'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" style="color: var(--theme-text-muted);">{{ $series['customer']->name ?? 'N/A' }}</div>
                            </td>
                            @foreach($months as $month)
                            @php
                                $monthData = $series['monthly_data'][$month['number']];
                                $variancePercent = $monthData['variance_percentage'];
                                $bgColor = $monthData['status'] === 'no-data' ? '#f3f4f6' :
                                          ($variancePercent > 10 ? '#d1fae5' :
                                          ($variancePercent < -10 ? '#fee2e2' : '#dbeafe'));
                                $textColor = $monthData['status'] === 'no-data' ? '#9ca3af' :
                                            ($variancePercent > 10 ? '#065f46' :
                                            ($variancePercent < -10 ? '#991b1b' : '#1e40af'));
                            @endphp
                            <td class="px-2 py-4 text-center" style="background-color: {{ $bgColor }};">
                                @if($monthData['has_data'])
                                {{-- NIEUWE LAYOUT (03-11-2025): Budget / Spent / Variance --}}
                                <div class="text-xs font-medium" style="color: {{ $textColor }}; opacity: 0.8;">
                                    €{{ number_format($monthData['base_budget'], 0) }}
                                </div>
                                @php
                                    $hasAdditionalCosts = ($monthData['additional_costs_in_fee'] ?? 0) > 0 || ($monthData['additional_costs_outside_fee'] ?? 0) > 0;
                                    $tooltipText = '';
                                    if ($hasAdditionalCosts) {
                                        $tooltipText = 'Time: €' . number_format($monthData['hours_value'], 0);
                                        if (($monthData['additional_costs_in_fee'] ?? 0) > 0) {
                                            $tooltipText .= '\nCosts (in fee): €' . number_format($monthData['additional_costs_in_fee'], 0);
                                        }
                                        if (($monthData['additional_costs_outside_fee'] ?? 0) > 0) {
                                            $tooltipText .= '\nCosts (additional): €' . number_format($monthData['additional_costs_outside_fee'], 0);
                                        }
                                        $tooltipText .= '\nTotal: €' . number_format($monthData['spent'], 0);
                                    }
                                @endphp
                                <div class="text-xs {{ $hasAdditionalCosts ? 'cursor-help' : '' }}"
                                     style="color: {{ $textColor }}; opacity: 0.7;"
                                     @if($hasAdditionalCosts) title="{{ $tooltipText }}" @endif>
                                    €{{ number_format($monthData['spent'], 0) }}
                                    @if($hasAdditionalCosts)
                                        <i class="fas fa-info-circle ml-1" style="opacity: 0.5; font-size: 0.7rem;"></i>
                                    @endif
                                </div>
                                <div class="text-sm font-bold" style="color: {{ $textColor }};">
                                    €{{ number_format($monthData['month_variance'], 0) }}
                                </div>
                                @else
                                <div class="text-xs" style="color: #9ca3af;">-</div>
                                @endif
                            </td>
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                {{-- NIEUWE LAYOUT (03-11-2025): Budget / Used / Variance met labels --}}
                                <div class="text-xs font-medium" style="color: #991b1b; opacity: 0.8;">
                                    <span style="opacity: 0.6;">Budget:</span> €{{ number_format($series['year_totals']['budget'], 0) }}
                                </div>
                                <div class="text-xs" style="color: #991b1b; opacity: 0.7;">
                                    <span style="opacity: 0.6;">Used:</span> €{{ number_format($series['year_totals']['spent'], 0) }}
                                </div>
                                <div class="text-sm font-bold" style="color: #ef4444;">
                                    <span style="font-size: 0.75rem; font-weight: 500; opacity: 0.7;">Variance:</span> €{{ number_format($series['year_totals']['variance'], 0) }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- UNDERSPENT PROJECTS --}}
        @if(count($underspentProjects) > 0)
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="border-b border-gray-200 flex justify-between items-center" style="padding: 1rem 1.5rem;">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded" style="background-color: #10b981;"></div>
                    <h2 class="text-lg font-semibold" style="color: #10b981;">
                        <i class="fas fa-check-circle mr-2"></i>
                        Underspent Projects ({{ count($underspentProjects) }})
                    </h2>
                </div>
                <div class="text-sm" style="color: var(--theme-text-muted);">
                    Total Variance: <span class="font-bold" style="color: #10b981;">€{{ number_format($underspentTotals['variance'], 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Series</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            @foreach($months as $month)
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $month['name'] }}</th>
                            @endforeach
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($underspentProjects as $series)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" style="color: var(--theme-text);">
                                    @if($series['is_individual'] ?? false)
                                        <i class="fas fa-file-alt mr-1" style="color: var(--theme-info); opacity: 0.7;" title="Individual Project"></i>
                                    @else
                                        <i class="fas fa-layer-group mr-1" style="color: var(--theme-primary); opacity: 0.7;" title="Recurring Series"></i>
                                    @endif
                                    {{ $series['series_name'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" style="color: var(--theme-text-muted);">{{ $series['customer']->name ?? 'N/A' }}</div>
                            </td>
                            @foreach($months as $month)
                            @php
                                $monthData = $series['monthly_data'][$month['number']];
                                $variancePercent = $monthData['variance_percentage'];
                                $bgColor = $monthData['status'] === 'no-data' ? '#f3f4f6' :
                                          ($variancePercent > 10 ? '#d1fae5' :
                                          ($variancePercent < -10 ? '#fee2e2' : '#dbeafe'));
                                $textColor = $monthData['status'] === 'no-data' ? '#9ca3af' :
                                            ($variancePercent > 10 ? '#065f46' :
                                            ($variancePercent < -10 ? '#991b1b' : '#1e40af'));
                            @endphp
                            <td class="px-2 py-4 text-center" style="background-color: {{ $bgColor }};">
                                @if($monthData['has_data'])
                                {{-- NIEUWE LAYOUT (03-11-2025): Budget / Spent / Variance --}}
                                <div class="text-xs font-medium" style="color: {{ $textColor }}; opacity: 0.8;">
                                    €{{ number_format($monthData['base_budget'], 0) }}
                                </div>
                                @php
                                    $hasAdditionalCosts = ($monthData['additional_costs_in_fee'] ?? 0) > 0 || ($monthData['additional_costs_outside_fee'] ?? 0) > 0;
                                    $tooltipText = '';
                                    if ($hasAdditionalCosts) {
                                        $tooltipText = 'Time: €' . number_format($monthData['hours_value'], 0);
                                        if (($monthData['additional_costs_in_fee'] ?? 0) > 0) {
                                            $tooltipText .= '\nCosts (in fee): €' . number_format($monthData['additional_costs_in_fee'], 0);
                                        }
                                        if (($monthData['additional_costs_outside_fee'] ?? 0) > 0) {
                                            $tooltipText .= '\nCosts (additional): €' . number_format($monthData['additional_costs_outside_fee'], 0);
                                        }
                                        $tooltipText .= '\nTotal: €' . number_format($monthData['spent'], 0);
                                    }
                                @endphp
                                <div class="text-xs {{ $hasAdditionalCosts ? 'cursor-help' : '' }}"
                                     style="color: {{ $textColor }}; opacity: 0.7;"
                                     @if($hasAdditionalCosts) title="{{ $tooltipText }}" @endif>
                                    €{{ number_format($monthData['spent'], 0) }}
                                    @if($hasAdditionalCosts)
                                        <i class="fas fa-info-circle ml-1" style="opacity: 0.5; font-size: 0.7rem;"></i>
                                    @endif
                                </div>
                                <div class="text-sm font-bold" style="color: {{ $textColor }};">
                                    €{{ number_format($monthData['month_variance'], 0) }}
                                </div>
                                @else
                                <div class="text-xs" style="color: #9ca3af;">-</div>
                                @endif
                            </td>
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                {{-- NIEUWE LAYOUT (03-11-2025): Budget / Used / Variance met labels --}}
                                <div class="text-xs font-medium" style="color: #065f46; opacity: 0.8;">
                                    <span style="opacity: 0.6;">Budget:</span> €{{ number_format($series['year_totals']['budget'], 0) }}
                                </div>
                                <div class="text-xs" style="color: #065f46; opacity: 0.7;">
                                    <span style="opacity: 0.6;">Used:</span> €{{ number_format($series['year_totals']['spent'], 0) }}
                                </div>
                                <div class="text-sm font-bold" style="color: #10b981;">
                                    <span style="font-size: 0.75rem; font-weight: 500; opacity: 0.7;">Variance:</span> €{{ number_format($series['year_totals']['variance'], 0) }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- NO BUDGET PROJECTS (HOURS TRACKING ONLY) --}}
        @if(count($noBudgetProjects) > 0)
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="border-b border-gray-200 flex justify-between items-center" style="padding: 1rem 1.5rem;">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded" style="background-color: #6b7280;"></div>
                    <h2 class="text-lg font-semibold" style="color: #6b7280;">
                        <i class="fas fa-clock mr-2"></i>
                        Hours Tracking Only ({{ count($noBudgetProjects) }})
                    </h2>
                </div>
                <div class="text-sm" style="color: var(--theme-text-muted);">
                    Total Hours: <span class="font-bold" style="color: #6b7280;">{{ number_format($noBudgetTotals['hours'], 0) }}h</span> |
                    Total Cost: <span class="font-bold" style="color: #6b7280;">€{{ number_format($noBudgetTotals['spent'], 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Series</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            @foreach($months as $month)
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $month['name'] }}</th>
                            @endforeach
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($noBudgetProjects as $series)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" style="color: var(--theme-text);">
                                    @if($series['is_individual'] ?? false)
                                        <i class="fas fa-file-alt mr-1" style="color: var(--theme-info); opacity: 0.7;" title="Individual Project"></i>
                                    @else
                                        <i class="fas fa-layer-group mr-1" style="color: var(--theme-primary); opacity: 0.7;" title="Recurring Series"></i>
                                    @endif
                                    {{ $series['series_name'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm" style="color: var(--theme-text-muted);">{{ $series['customer']->name ?? 'N/A' }}</div>
                            </td>
                            @foreach($months as $month)
                            @php
                                $monthData = $series['monthly_data'][$month['number']];
                            @endphp
                            <td class="px-2 py-4 text-center" style="background-color: #f9fafb;">
                                @if($monthData['has_data'])
                                <div class="text-sm font-semibold text-gray-700">
                                    {{ number_format($monthData['hours'], 1) }}h
                                </div>
                                <div class="text-xs text-gray-500">
                                    €{{ number_format($monthData['spent'], 0) }}
                                </div>
                                @else
                                <div class="text-xs text-gray-400">-</div>
                                @endif
                            </td>
                            @endforeach
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                {{-- NIEUWE LAYOUT (03-11-2025): Hours / Spent met labels voor No Budget projecten --}}
                                <div class="text-xs font-medium" style="color: #6b7280; opacity: 0.8;">
                                    <span style="opacity: 0.6;">Hours:</span> {{ number_format($series['year_totals']['hours'], 0) }}h
                                </div>
                                <div class="text-sm font-bold" style="color: #6b7280;">
                                    <span style="font-size: 0.75rem; font-weight: 500; opacity: 0.7;">Spent:</span> €{{ number_format($series['year_totals']['spent'], 0) }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-blue-900">How to Read This Dashboard</h3>
                    <div class="mt-2 text-sm text-blue-800">
                        <p class="font-semibold mb-2">Each month cell shows (top to bottom):</p>
                        <ul class="list-disc list-inside space-y-1 mb-3">
                            <li><strong>Budget</strong>: Monthly budget for that month (excluding rollover)</li>
                            <li><strong>Spent</strong>: Actual costs incurred in that month</li>
                            <li><strong>Variance</strong>: Remaining budget for that month (Budget - Spent)</li>
                        </ul>
                        <p class="font-semibold mb-2">Color coding:</p>
                        <ul class="list-disc list-inside space-y-1 mb-3">
                            <li>Green cells indicate underspending (&gt;10% remaining budget)</li>
                            <li>Blue cells indicate on-budget spending (±10%)</li>
                            <li>Red cells indicate overspending (&gt;10% over budget)</li>
                            <li>Gray cells show hours tracking without budget constraints</li>
                        </ul>
                        <p class="text-xs"><strong>Note:</strong> The "Total" column includes rollover effects from all months. Individual month cells show only that month's base budget without rollover for clarity.</p>
                        <p class="text-xs mt-2">Data is automatically updated every night at 03:00 AM. Use "Refresh Data" button for manual updates.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function confirmRefresh() {
    if (confirm('This will recalculate all budget tracking data for {{ $currentYear }}.\n\nThis may take a few minutes. Continue?')) {
        const form = document.getElementById('refresh-form');
        const button = form.querySelector('button');

        // Disable button en toon loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Refreshing...';

        // Submit form
        form.submit();
    }
}
</script>
@endpush

@endsection
