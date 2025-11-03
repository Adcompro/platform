@extends('layouts.app')

@section('title', 'Additional Costs - ' . $project->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Additional Costs</h1>
                    <p class="text-sm text-gray-600">{{ $project->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Project
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('projects.additional-costs.create-monthly', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Add Monthly Cost
                    </a>
                    <a href="{{ route('projects.additional-costs.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add One-time Cost
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Budget Overview Cards --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Budget Card (with rollover) --}}
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Budget</dt>
                                <dd>
                                    <div class="text-2xl font-semibold text-gray-900">€{{ number_format($budgetStats['total_budget'] ?? $budgetStats['monthly_budget'], 2) }}</div>
                                    @if(isset($budgetStats['rollover_from_previous']) && $budgetStats['rollover_from_previous'] > 0)
                                    <div class="text-xs text-gray-500">Incl. €{{ number_format($budgetStats['rollover_from_previous'], 2) }} rollover</div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Used This Month Card --}}
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Used ({{ date('F') }})</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">€{{ number_format($budgetStats['used_this_month'], 2) }}</div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold {{ $budgetStats['percentage_used'] > 100 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $budgetStats['percentage_used'] }}%
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Time Entry Costs Card --}}
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Time Costs</dt>
                                <dd>
                                    <div class="text-2xl font-semibold text-gray-900">€{{ number_format($budgetStats['time_entry_costs'] ?? 0, 2) }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($budgetStats['time_entry_hours'] ?? 0, 1) }} hours</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Remaining Budget Card --}}
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 {{ isset($budgetStats['is_over_budget']) && $budgetStats['is_over_budget'] ? 'text-red-500' : 'text-purple-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ isset($budgetStats['is_over_budget']) && $budgetStats['is_over_budget'] ? 'Over Budget' : 'Remaining' }}</dt>
                                <dd>
                                    @if(isset($budgetStats['is_over_budget']) && $budgetStats['is_over_budget'])
                                        <div class="text-2xl font-semibold text-red-600">-€{{ number_format($budgetStats['budget_exceeded'], 2) }}</div>
                                    @else
                                        <div class="text-2xl font-semibold text-green-600">€{{ number_format($budgetStats['remaining_budget'], 2) }}</div>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget Progress Bar --}}
        @if($budgetStats['monthly_budget'] > 0)
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Usage for {{ date('F Y') }}</h3>
            <div class="relative">
                <div class="overflow-hidden h-4 text-xs flex rounded bg-gray-200">
                    <div style="width:{{ min($budgetStats['percentage_used'], 100) }}%" 
                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center {{ $budgetStats['percentage_used'] > 100 ? 'bg-red-500' : ($budgetStats['percentage_used'] > 80 ? 'bg-yellow-500' : 'bg-green-500') }}">
                    </div>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mt-2">
                    <span>€0</span>
                    <span class="font-medium">
                        €{{ number_format($budgetStats['used_this_month'], 2) }} used of €{{ number_format($budgetStats['monthly_budget'], 2) }}
                    </span>
                    <span>€{{ number_format($budgetStats['monthly_budget'], 2) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Flash Messages --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
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
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
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
    </div>

    {{-- One-time Costs Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">One-time Additional Costs</h2>
                <div class="mt-1 flex items-center text-sm text-gray-500">
                    <span>Total: €{{ number_format($stats['total_one_time'], 2) }}</span>
                    <span class="mx-2">•</span>
                    <span class="text-blue-600">In Budget: €{{ number_format($stats['total_one_time_in_fee'], 2) }}</span>
                    <span class="mx-2">•</span>
                    <span class="text-orange-600">Extended: €{{ number_format($stats['total_one_time_extended'], 2) }}</span>
                    @if($stats['pending_approval'] > 0)
                    <span class="mx-2">•</span>
                    <span class="text-yellow-600 font-medium">{{ $stats['pending_approval'] }} pending approval</span>
                    @endif
                </div>
            </div>
            
            @if($oneTimeCosts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($oneTimeCosts as $cost)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $cost->start_date ? $cost->start_date->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium">{{ $cost->name }}</div>
                                        @if($cost->vendor)
                                            <div class="text-xs text-gray-500">Vendor: {{ $cost->vendor }}</div>
                                        @endif
                                        @if($cost->description)
                                            <div class="text-xs text-gray-500">{{ Str::limit($cost->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ \App\Models\ProjectAdditionalCost::COST_TYPES[$cost->cost_type] ?? $cost->cost_type }}
                                        </span>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cost->fee_type_badge_class }}">
                                            {{ \App\Models\ProjectAdditionalCost::FEE_TYPES[$cost->fee_type] ?? $cost->fee_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        €{{ number_format($cost->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cost->status_badge_class }}">
                                            {{ $cost->status_text }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if($cost->is_active)
                                                <form method="POST" action="{{ route('projects.additional-costs.toggle-monthly', [$project, $cost]) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-orange-600 hover:text-orange-900" title="Deactivate">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('projects.additional-costs.toggle-monthly', [$project, $cost]) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-900" title="Activate">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($cost->canBeEdited())
                                                <a href="{{ route('projects.additional-costs.edit', [$project, $cost]) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            
                                            @if($cost->canBeDeleted() && in_array(Auth::user()->role, ['super_admin', 'admin']))
                                                <form method="POST" action="{{ route('projects.additional-costs.destroy', [$project, $cost]) }}" 
                                                      class="inline" onsubmit="return confirm('Are you sure you want to delete this cost?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No one-time costs</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a new one-time cost.</p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <div class="mt-6">
                        <a href="{{ route('projects.additional-costs.create', $project) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add One-time Cost
                        </a>
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Time Entries Section --}}
    @if(isset($timeEntries) && $timeEntries->count() > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Time Entries This Month</h2>
                <div class="mt-1 flex items-center text-sm text-gray-500">
                    <span>Total Hours: {{ number_format($budgetStats['time_entry_hours'] ?? 0, 1) }}</span>
                    <span class="mx-2">•</span>
                    <span>Total Cost: €{{ number_format($budgetStats['time_entry_costs'] ?? 0, 2) }}</span>
                    @if(isset($stats['pending_approval']) && $stats['pending_approval'] > 0)
                    <span class="mx-2">•</span>
                    <span class="text-yellow-600 font-medium">{{ $stats['pending_approval'] }} pending approval</span>
                    @endif
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Item</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($timeEntries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->entry_date ? $entry->entry_date->format('d M') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $entry->user ? $entry->user->name : 'Unknown' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $entry->work_item_path ?? 'General' }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($entry->description, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($entry->hours, 1) }}h
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entry->status === 'approved' ? 'bg-green-100 text-green-800' : ($entry->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($entry->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    @if($entry->status === 'approved')
                                        €{{ number_format($entry->hours * $entry->hourly_rate_used, 2) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-3 bg-gray-50 text-right">
                <a href="{{ route('time-entries.index') }}?project_id={{ $project->id }}" class="text-sm text-blue-600 hover:text-blue-900">
                    View all time entries →
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Monthly Recurring Costs Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Monthly Recurring Costs</h2>
                <div class="mt-1 flex items-center text-sm text-gray-500">
                    <span>Active Total: €{{ number_format($stats['total_monthly'], 2) }}/month</span>
                    <span class="mx-2">•</span>
                    <span class="text-blue-600">In Budget: €{{ number_format($stats['total_monthly_in_fee'], 2) }}</span>
                    <span class="mx-2">•</span>
                    <span class="text-orange-600">Extended: €{{ number_format($stats['total_monthly_extended'], 2) }}</span>
                </div>
            </div>
            
            @if($monthlyCosts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Amount</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($monthlyCosts as $cost)
                                <tr class="hover:bg-gray-50 {{ !$cost->is_active ? 'opacity-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cost->status_badge_class }}">
                                            {{ $cost->status_text }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium">{{ $cost->name }}</div>
                                        @if($cost->vendor)
                                            <div class="text-xs text-gray-500">Vendor: {{ $cost->vendor }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($cost->start_date)
                                            From {{ $cost->start_date->format('M Y') }}
                                            @if($cost->end_date)
                                                to {{ $cost->end_date->format('M Y') }}
                                            @else
                                                (Ongoing)
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ \App\Models\ProjectAdditionalCost::CATEGORIES[$cost->category] ?? ucfirst($cost->category ?? 'other') }}
                                        </span>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cost->fee_type_badge_class }}">
                                            {{ \App\Models\ProjectAdditionalCost::FEE_TYPES[$cost->fee_type] ?? $cost->fee_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        €{{ number_format($cost->amount, 2) }}/month
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <form method="POST" action="{{ route('projects.additional-costs.toggle-monthly', [$project, $cost]) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="{{ $cost->is_active ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }}" 
                                                        title="{{ $cost->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($cost->is_active)
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        @endif
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No recurring costs</h3>
                    <p class="mt-1 text-sm text-gray-500">Add recurring costs like hosting or licenses.</p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <div class="mt-6">
                        <a href="{{ route('projects.additional-costs.create-monthly', $project) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Add Monthly Cost
                        </a>
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection