@extends('layouts.app')

@section('title', 'Weekly Timesheet Report')

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
                        <span>Weekly Timesheet</span>
                    </div>
                    <h1 class="text-2xl font-semibold text-slate-900">Weekly Timesheet Report</h1>
                    <p class="text-sm text-slate-600 mt-1">{{ $weekStart->format('F j') }} - {{ $weekEnd->format('F j, Y') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <form method="GET" action="{{ route('reports.weekly-timesheet-pdf') }}" class="inline">
                        @if(request('user_id'))
                            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                        @endif
                        <input type="hidden" name="week_start" value="{{ $weekStart->format('Y-m-d') }}">
                        <button type="submit" class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-all">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Export PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
            <form method="GET" action="{{ route('reports.weekly-timesheet') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 mb-1">User</label>
                    <select name="user_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Week Starting</label>
                    <input type="date" name="week_start" value="{{ $weekStart->format('Y-m-d') }}" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Hours</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">
                            {{ $totalHours }}h {{ $totalMinutes }}m
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Billable Hours</p>
                        <p class="text-2xl font-bold text-green-700 mt-1">
                            {{ $totalBillableHours }}h {{ $totalBillableMinutes }}m
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Days Worked</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">
                            {{ collect($groupedEntries)->flatten(1)->unique(function($entries, $date) { return $date; })->count() }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-purple-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Team Members</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">
                            {{ $groupedEntries->count() }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-indigo-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Report Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @if($groupedEntries->isEmpty())
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-8 text-center">
                <i class="fas fa-calendar-times text-slate-300 text-5xl mb-4"></i>
                <p class="text-lg font-medium text-slate-900">No time entries found</p>
                <p class="text-sm text-slate-600 mt-1">There are no time entries for the selected week and filters.</p>
            </div>
        @else
            @foreach($groupedEntries as $userId => $userEntries)
                @php
                    $userData = $userEntries->first()->first()->user;
                    $userTotalMinutes = 0;
                    foreach($userEntries as $dayEntries) {
                        foreach($dayEntries as $entry) {
                            $userTotalMinutes += $entry->minutes;
                        }
                    }
                    $userTotalHours = floor($userTotalMinutes / 60);
                    $userTotalMinutes = $userTotalMinutes % 60;
                @endphp
                
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6">
                    {{-- User Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200/60">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $userData->name }}</h3>
                                <p class="text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $userData->role)) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-slate-600">Total Hours</p>
                                <p class="text-lg font-bold text-slate-900">{{ $userTotalHours }}h {{ $userTotalMinutes }}m</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Daily Entries --}}
                    <div class="divide-y divide-slate-200/50">
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $dayIndex => $dayName)
                            @php
                                $date = $weekStart->copy()->addDays($dayIndex);
                                $dayEntries = $userEntries[$date->format('Y-m-d')] ?? collect();
                                $dayMinutes = $dayEntries->sum('minutes');
                                $dayHours = floor($dayMinutes / 60);
                                $dayMinutes = $dayMinutes % 60;
                            @endphp
                            
                            @if($dayEntries->isNotEmpty())
                            <div class="px-6 py-4">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-medium text-slate-900">
                                        {{ $dayName }}, {{ $date->format('F j') }}
                                    </h4>
                                    <span class="text-sm font-medium text-slate-600">
                                        {{ $dayHours }}h {{ $dayMinutes }}m
                                    </span>
                                </div>
                                
                                <div class="space-y-2">
                                    @foreach($dayEntries as $entry)
                                    <div class="flex justify-between items-start p-3 bg-slate-50/50 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-sm text-slate-900">
                                                    {{ $entry->project->name ?? 'No Project' }}
                                                </span>
                                                @if($entry->is_billable === 'billable')
                                                    <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                                        Billable
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">
                                                        Non-billable
                                                    </span>
                                                @endif
                                            </div>
                                            @if($entry->milestone || $entry->task || $entry->subtask)
                                            <p class="text-xs text-slate-500 mt-1">
                                                @if($entry->milestone)
                                                    {{ $entry->milestone->name }}
                                                    @if($entry->task)
                                                        → {{ $entry->task->name }}
                                                        @if($entry->subtask)
                                                            → {{ $entry->subtask->name }}
                                                        @endif
                                                    @endif
                                                @endif
                                            </p>
                                            @endif
                                            @if($entry->description)
                                            <p class="text-sm text-slate-600 mt-1">{{ $entry->description }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            @php
                                                $entryHours = floor($entry->minutes / 60);
                                                $entryMinutes = $entry->minutes % 60;
                                            @endphp
                                            <p class="font-medium text-slate-900">{{ $entryHours }}h {{ $entryMinutes }}m</p>
                                            <p class="text-xs text-slate-500">
                                                {{ ucfirst($entry->status) }}
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection