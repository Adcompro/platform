@extends('layouts.app')

@section('title', 'Overdue Milestones Report')

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
                        <span>Overdue Milestones</span>
                    </div>
                    <h1 class="text-2xl font-semibold text-slate-900">Overdue Milestones Report</h1>
                    <p class="text-sm text-slate-600 mt-1">Milestones past their due date</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
            <form method="GET" action="{{ route('reports.overdue-milestones') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Minimum Days Overdue</label>
                    <input type="number" name="days_overdue" value="{{ $daysOverdue }}" min="0"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                           placeholder="0">
                </div>
                
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all">
                    <i class="fas fa-filter mr-2"></i>
                    Apply Filter
                </button>
            </form>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4">
            {{-- Total Overdue --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Overdue</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-orange-600"></i>
                    </div>
                </div>
            </div>

            {{-- Critical --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Critical</p>
                        <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['critical'] }}</p>
                        <p class="text-xs text-slate-500">&gt; 30 days</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-fire text-red-600"></i>
                    </div>
                </div>
            </div>

            {{-- High --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">High</p>
                        <p class="text-2xl font-bold text-orange-700 mt-1">{{ $stats['high'] }}</p>
                        <p class="text-xs text-slate-500">15-30 days</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation text-orange-600"></i>
                    </div>
                </div>
            </div>

            {{-- Medium --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Medium</p>
                        <p class="text-2xl font-bold text-yellow-700 mt-1">{{ $stats['medium'] }}</p>
                        <p class="text-xs text-slate-500">8-14 days</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
            </div>

            {{-- Low --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Low</p>
                        <p class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['low'] }}</p>
                        <p class="text-xs text-slate-500">1-7 days</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                </div>
            </div>

            {{-- Average Delay --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Avg Delay</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['average_delay'] }}</p>
                        <p class="text-xs text-slate-500">days</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
            </div>

            {{-- Max Delay --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Max Delay</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['max_delay'] }}</p>
                        <p class="text-xs text-slate-500">days</p>
                    </div>
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-arrow-up text-indigo-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Milestone List by Urgency --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @if($milestones->isEmpty())
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-8 text-center">
                <i class="fas fa-check-circle text-green-400 text-5xl mb-4"></i>
                <p class="text-lg font-medium text-slate-900">No overdue milestones</p>
                <p class="text-sm text-slate-600 mt-1">Great! All milestones are on track.</p>
            </div>
        @else
            {{-- Critical Milestones --}}
            @if($grouped['critical']->isNotEmpty())
            <div class="bg-white/60 backdrop-blur-sm border-2 border-red-200 rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-red-100 border-b border-red-200">
                    <h2 class="text-lg font-semibold text-red-900">
                        <i class="fas fa-fire mr-2"></i>
                        Critical - Over 30 Days Overdue ({{ $grouped['critical']->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-red-100">
                    @foreach($grouped['critical'] as $milestone)
                    <div class="p-6 hover:bg-red-50/30 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900">{{ $milestone->name }}</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Project:</strong> {{ $milestone->project->name }}
                                    | <strong>Customer:</strong> {{ $milestone->project->customer->name ?? 'N/A' }}
                                </p>
                                @if($milestone->description)
                                <p class="text-sm text-slate-500 mt-2">{{ Str::limit($milestone->description, 150) }}</p>
                                @endif
                                <div class="flex items-center space-x-4 mt-3">
                                    <span class="text-xs text-slate-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Due: {{ Carbon\Carbon::parse($milestone->end_date)->format('d-m-Y') }}
                                    </span>
                                    <span class="text-xs font-medium text-red-600">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $milestone->days_overdue }} days overdue
                                    </span>
                                    @if($milestone->project->users->isNotEmpty())
                                    <span class="text-xs text-slate-500">
                                        <i class="fas fa-user mr-1"></i>
                                        {{ $milestone->project->users->first()->name }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex space-x-2">
                                <a href="{{ route('projects.milestones.show', [$milestone->project_id, $milestone->id]) }}" 
                                   class="text-slate-400 hover:text-slate-600 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- High Priority Milestones --}}
            @if($grouped['high']->isNotEmpty())
            <div class="bg-white/60 backdrop-blur-sm border-2 border-orange-200 rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
                    <h2 class="text-lg font-semibold text-orange-900">
                        <i class="fas fa-exclamation mr-2"></i>
                        High Priority - 15-30 Days Overdue ({{ $grouped['high']->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-orange-100">
                    @foreach($grouped['high'] as $milestone)
                    <div class="p-6 hover:bg-orange-50/30 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900">{{ $milestone->name }}</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Project:</strong> {{ $milestone->project->name }}
                                    | <strong>Customer:</strong> {{ $milestone->project->customer->name ?? 'N/A' }}
                                </p>
                                <div class="flex items-center space-x-4 mt-3">
                                    <span class="text-xs text-slate-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Due: {{ Carbon\Carbon::parse($milestone->end_date)->format('d-m-Y') }}
                                    </span>
                                    <span class="text-xs font-medium text-orange-600">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $milestone->days_overdue }} days overdue
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <a href="{{ route('projects.milestones.show', [$milestone->project_id, $milestone->id]) }}" 
                                   class="text-slate-400 hover:text-slate-600 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Medium Priority Milestones --}}
            @if($grouped['medium']->isNotEmpty())
            <div class="bg-white/60 backdrop-blur-sm border border-yellow-200 rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-yellow-100 border-b border-yellow-200">
                    <h2 class="text-lg font-semibold text-yellow-900">
                        <i class="fas fa-clock mr-2"></i>
                        Medium Priority - 8-14 Days Overdue ({{ $grouped['medium']->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-yellow-100">
                    @foreach($grouped['medium'] as $milestone)
                    <div class="p-6 hover:bg-yellow-50/30 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900">{{ $milestone->name }}</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Project:</strong> {{ $milestone->project->name }}
                                    | <strong>Customer:</strong> {{ $milestone->project->customer->name ?? 'N/A' }}
                                </p>
                                <div class="flex items-center space-x-4 mt-3">
                                    <span class="text-xs text-slate-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Due: {{ Carbon\Carbon::parse($milestone->end_date)->format('d-m-Y') }}
                                    </span>
                                    <span class="text-xs font-medium text-yellow-600">
                                        {{ $milestone->days_overdue }} days overdue
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <a href="{{ route('projects.milestones.show', [$milestone->project_id, $milestone->id]) }}" 
                                   class="text-slate-400 hover:text-slate-600 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Low Priority Milestones --}}
            @if($grouped['low']->isNotEmpty())
            <div class="bg-white/60 backdrop-blur-sm border border-blue-200 rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                    <h2 class="text-lg font-semibold text-blue-900">
                        <i class="fas fa-info-circle mr-2"></i>
                        Low Priority - 1-7 Days Overdue ({{ $grouped['low']->count() }})
                    </h2>
                </div>
                <div class="divide-y divide-blue-100">
                    @foreach($grouped['low'] as $milestone)
                    <div class="p-6 hover:bg-blue-50/30 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900">{{ $milestone->name }}</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Project:</strong> {{ $milestone->project->name }}
                                    | <strong>Customer:</strong> {{ $milestone->project->customer->name ?? 'N/A' }}
                                </p>
                                <div class="flex items-center space-x-4 mt-3">
                                    <span class="text-xs text-slate-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Due: {{ Carbon\Carbon::parse($milestone->end_date)->format('d-m-Y') }}
                                    </span>
                                    <span class="text-xs font-medium text-blue-600">
                                        {{ $milestone->days_overdue }} days overdue
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <a href="{{ route('projects.milestones.show', [$milestone->project_id, $milestone->id]) }}" 
                                   class="text-slate-400 hover:text-slate-600 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection