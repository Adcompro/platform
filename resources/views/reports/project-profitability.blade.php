@extends('layouts.app')

@section('title', 'Project Profitability Report')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <div class="flex items-center space-x-2 text-sm text-slate-600 mb-1">
                        <a href="{{ route('reports.quick-reports') }}" class="hover:text-slate-900">Quick Reports</a>
                        <span>/</span>
                        <span>Project Profitability</span>
                    </div>
                    <h1 class="text-2xl font-semibold text-slate-900">Project Profitability Report</h1>
                    <p class="text-sm text-slate-600 mt-1">
                        @if($startDate)
                            Period: {{ $startDate->format('F j, Y') }} - {{ now()->format('F j, Y') }}
                        @else
                            All Time
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
            <form method="GET" action="{{ route('reports.project-profitability') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Period</label>
                    <select name="period" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="all" {{ $period == 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Sort By</label>
                    <select name="sort" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="profit_desc" {{ $sort == 'profit_desc' ? 'selected' : '' }}>Profit (High to Low)</option>
                        <option value="profit_asc" {{ $sort == 'profit_asc' ? 'selected' : '' }}>Profit (Low to High)</option>
                        <option value="margin_desc" {{ $sort == 'margin_desc' ? 'selected' : '' }}>Margin % (High to Low)</option>
                        <option value="margin_asc" {{ $sort == 'margin_asc' ? 'selected' : '' }}>Margin % (Low to High)</option>
                    </select>
                </div>
                
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all">
                    <i class="fas fa-filter mr-2"></i>
                    Apply Filters
                </button>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            {{-- Total Revenue --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Revenue</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">€{{ number_format($totals['revenue'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-up text-green-600"></i>
                    </div>
                </div>
            </div>

            {{-- Total Costs --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Costs</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">€{{ number_format($totals['cost'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-down text-red-600"></i>
                    </div>
                </div>
            </div>

            {{-- Total Profit --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Profit</p>
                        <p class="text-xl font-bold {{ $totals['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }} mt-1">
                            €{{ number_format(abs($totals['profit']), 2) }}
                        </p>
                    </div>
                    <div class="w-10 h-10 {{ $totals['profit'] >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line {{ $totals['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                    </div>
                </div>
            </div>

            {{-- Total Projects --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Projects</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totals['projects'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder text-blue-600"></i>
                    </div>
                </div>
            </div>

            {{-- Profitable Projects --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Profitable</p>
                        <p class="text-2xl font-bold text-green-700 mt-1">{{ $totals['profitable'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            {{-- Loss Making Projects --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Loss Making</p>
                        <p class="text-2xl font-bold text-red-700 mt-1">{{ $totals['loss_making'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Project List --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200/60">
                <h2 class="text-lg font-semibold text-slate-900">Project Profitability Details</h2>
            </div>
            
            @if($profitabilityData->isEmpty())
                <div class="p-8 text-center">
                    <i class="fas fa-chart-pie text-slate-300 text-5xl mb-4"></i>
                    <p class="text-lg font-medium text-slate-900">No project data found</p>
                    <p class="text-sm text-slate-600 mt-1">There are no projects to analyze for the selected period.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase tracking-wider">Labor Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase tracking-wider">Other Costs</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase tracking-wider">Total Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase tracking-wider">Profit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Margin</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            @foreach($profitabilityData as $data)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $data['project']->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            @php
                                                $statusClass = match($data['project']->status) {
                                                    'active' => 'text-green-600',
                                                    'completed' => 'text-blue-600',
                                                    'on_hold' => 'text-yellow-600',
                                                    'cancelled' => 'text-red-600',
                                                    default => 'text-gray-600'
                                                };
                                            @endphp
                                            <span class="{{ $statusClass }}">{{ ucfirst($data['project']->status) }}</span>
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-900">{{ $data['project']->customer->name ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-slate-900">€{{ number_format($data['revenue'], 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm text-slate-600">€{{ number_format($data['labor_cost'], 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm text-slate-600">€{{ number_format($data['additional_costs'], 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-slate-900">€{{ number_format($data['total_cost'], 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-bold {{ $data['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $data['profit'] >= 0 ? '' : '-' }}€{{ number_format(abs($data['profit']), 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $marginClass = $data['margin'] >= 20 ? 'bg-green-100 text-green-700' : 
                                                      ($data['margin'] >= 10 ? 'bg-yellow-100 text-yellow-700' : 
                                                      ($data['margin'] >= 0 ? 'bg-gray-100 text-gray-700' : 'bg-red-100 text-red-700'));
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $marginClass }}">
                                        {{ number_format($data['margin'], 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('projects.show', $data['project']->id) }}" 
                                       class="text-slate-400 hover:text-slate-600 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection