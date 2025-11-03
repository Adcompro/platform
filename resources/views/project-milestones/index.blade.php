@extends('layouts.app')

@section('title', 'Project Milestones')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li>
                                <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">
                                    Projects
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('projects.show', $project) }}" class="ml-1 text-gray-500 hover:text-gray-700">
                                        {{ $project->name }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-gray-700 font-medium">Milestones</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Project Milestones</h1>
                    <p class="text-sm text-gray-600">Manage milestones for {{ $project->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to Project
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('projects.milestones.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Milestone
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
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

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_milestones'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['completed_milestones'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['in_progress_milestones'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Est. Hours</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_estimated_hours'], 1) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">In Fee</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['in_fee_count'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Extended</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['extended_count'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Milestones List --}}
        @if($milestones->count() > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">All Milestones</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($milestones as $milestone)
                        @php
                            // Bepaal de achtergrond en border kleuren voor service items
                            $bgColorClass = 'hover:bg-gray-50';
                            $borderColorClass = '';
                            $badgeColorClass = '';
                            
                            if ($milestone->is_service_item) {
                                $color = $milestone->service_color ?? 'blue';
                                $bgColorClass = match($color) {
                                    'blue' => 'bg-blue-50 hover:bg-blue-100',
                                    'green' => 'bg-green-50 hover:bg-green-100',
                                    'yellow' => 'bg-yellow-50 hover:bg-yellow-100',
                                    'red' => 'bg-red-50 hover:bg-red-100',
                                    'purple' => 'bg-purple-50 hover:bg-purple-100',
                                    'indigo' => 'bg-indigo-50 hover:bg-indigo-100',
                                    'pink' => 'bg-pink-50 hover:bg-pink-100',
                                    'gray' => 'bg-gray-50 hover:bg-gray-100',
                                    default => 'bg-blue-50 hover:bg-blue-100'
                                };
                                $borderColorClass = match($color) {
                                    'blue' => 'border-l-4 border-l-blue-500',
                                    'green' => 'border-l-4 border-l-green-500',
                                    'yellow' => 'border-l-4 border-l-yellow-500',
                                    'red' => 'border-l-4 border-l-red-500',
                                    'purple' => 'border-l-4 border-l-purple-500',
                                    'indigo' => 'border-l-4 border-l-indigo-500',
                                    'pink' => 'border-l-4 border-l-pink-500',
                                    'gray' => 'border-l-4 border-l-gray-500',
                                    default => 'border-l-4 border-l-blue-500'
                                };
                                $badgeColorClass = match($color) {
                                    'blue' => 'bg-blue-100 text-blue-800',
                                    'green' => 'bg-green-100 text-green-800',
                                    'yellow' => 'bg-yellow-100 text-yellow-800',
                                    'red' => 'bg-red-100 text-red-800',
                                    'purple' => 'bg-purple-100 text-purple-800',
                                    'indigo' => 'bg-indigo-100 text-indigo-800',
                                    'pink' => 'bg-pink-100 text-pink-800',
                                    'gray' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-blue-100 text-blue-800'
                                };
                            }
                        @endphp
                        <div class="p-6 {{ $bgColorClass }} {{ $borderColorClass }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" class="hover:text-blue-600">
                                                @if($milestone->is_service_item)
                                                    📦 {{ $milestone->name }}
                                                @else
                                                    📋 {{ $milestone->name }}
                                                @endif
                                            </a>
                                        </h3>
                                        @if($milestone->is_service_item)
                                            <span class="ml-2 px-2.5 py-0.5 rounded text-xs font-medium {{ $badgeColorClass }}">
                                                SERVICE
                                            </span>
                                        @endif
                                        <span class="ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $milestone->status_color_attribute ? 'bg-' . $milestone->status_color_attribute . '-100 text-' . $milestone->status_color_attribute . '-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                        </span>
                                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $milestone->fee_type === 'in_fee' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                            {{ $milestone->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                        </span>
                                        @if($milestone->pricing_type === 'fixed_price')
                                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Fixed: €{{ number_format($milestone->fixed_price, 2) }}
                                            </span>
                                        @else
                                            <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Hourly
                                            </span>
                                        @endif
                                    </div>
                                    @if($milestone->description)
                                        <p class="mt-1 text-sm text-gray-600">{{ Str::limit($milestone->description, 150) }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center text-sm text-gray-500">
                                        @if($milestone->start_date || $milestone->end_date)
                                            <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            @if($milestone->start_date && $milestone->end_date)
                                                {{ $milestone->start_date->format('M d, Y') }} - {{ $milestone->end_date->format('M d, Y') }}
                                            @elseif($milestone->start_date)
                                                Starts {{ $milestone->start_date->format('M d, Y') }}
                                            @else
                                                Ends {{ $milestone->end_date->format('M d, Y') }}
                                            @endif
                                        @endif
                                        @if($milestone->estimated_hours)
                                            <span class="ml-4">
                                                <svg class="inline mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ number_format($milestone->estimated_hours, 1) }} hours estimated
                                            </span>
                                        @endif
                                        <span class="ml-4">
                                            <svg class="inline mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            {{ $milestone->tasks->count() }} tasks
                                        </span>
                                    </div>
                                    {{-- Progress Bar --}}
                                    @if($milestone->tasks->count() > 0)
                                        <div class="mt-3">
                                            <div class="flex items-center">
                                                <div class="flex-1">
                                                    <div class="bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $milestone->progress_percentage }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="ml-3 text-sm text-gray-600">{{ $milestone->progress_percentage }}%</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4 flex-shrink-0 flex space-x-2">
                                    <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                        <a href="{{ route('projects.milestones.edit', [$project, $milestone]) }}" class="text-gray-400 hover:text-gray-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white shadow rounded-lg">
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No milestones</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new milestone for this project.</p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <div class="mt-6">
                            <a href="{{ route('projects.milestones.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create First Milestone
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection