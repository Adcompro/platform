@extends('layouts.app')

@section('title', 'Service Details - ' . $service->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Moderne uitstraling met glassmorphism --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="{{ route('services.index') }}" class="text-xs text-slate-600 hover:text-slate-900">
                                    Services
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-xs text-slate-900 font-medium">{{ $service->name }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-xl font-semibold text-slate-900 mt-1">{{ $service->name }}</h1>
                    @if($service->category)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium mt-1" 
                              style="background-color: {{ $service->category->color }}20; color: {{ $service->category->color }}">
                            {{ $service->category->name }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center space-x-2">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('services.structure', $service) }}" class="px-3 py-1.5 bg-purple-500 text-white text-sm font-medium rounded-lg hover:bg-purple-600 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Manage Structure
                    </a>
                    <a href="{{ route('services.edit', $service) }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Service
                    </a>
                    @endif
                    <a href="{{ route('services.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Services
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-32">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50/50 border border-green-200/50 text-green-700 px-3 py-2.5 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-green-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Left Column - Service Details --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Service Information --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Service Information</h2>
                    </div>
                    <div class="p-4">
                        @if($service->description)
                        <div class="mb-4">
                            <h3 class="text-xs font-medium text-slate-600 mb-1">Description</h3>
                            <p class="text-sm text-slate-700">{{ $service->description }}</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-xs font-medium text-slate-600 mb-1">Status</h3>
                                @if($service->is_active)
                                    <span class="px-2 py-0.5 inline-flex text-xs font-medium rounded-lg bg-green-100/60 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 inline-flex text-xs font-medium rounded-lg bg-slate-100 text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-xs font-medium text-slate-600 mb-1">Website Visibility</h3>
                                @if($service->show_on_website)
                                    <span class="px-2 py-0.5 inline-flex text-xs font-medium rounded-lg bg-blue-100/60 text-blue-700">
                                        Visible
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 inline-flex text-xs font-medium rounded-lg bg-slate-100 text-slate-600">
                                        Hidden
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Service Structure --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50 flex justify-between items-center">
                        <h2 class="text-base font-medium text-slate-900">Service Structure</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        <a href="{{ route('services.structure', $service) }}" class="text-xs text-purple-600 hover:text-purple-700 font-medium">
                            Manage Structure →
                        </a>
                        @endif
                    </div>
                    <div class="p-4">
                        @if($service->milestones->count() > 0)
                            <div class="space-y-3">
                                @foreach($service->milestones->sortBy('sort_order') as $milestone)
                                <div class="border-l-2 border-{{ $service->color ?? 'slate' }}-200 pl-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-sm font-medium text-slate-900">{{ $milestone->name }}</h4>
                                            @if($milestone->description)
                                            <p class="text-xs text-slate-600 mt-0.5">{{ $milestone->description }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-slate-600">
                                                {{ number_format($milestone->calculateEstimatedHours(), 2) }}h
                                                @if($milestone->calculateEstimatedHours() > 0)
                                                    <span class="text-slate-400">(calculated)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    @if($milestone->tasks->count() > 0)
                                    <div class="mt-2 ml-4 space-y-2">
                                        @foreach($milestone->tasks->sortBy('sort_order') as $task)
                                        <div>
                                            <div class="text-xs text-slate-600">
                                                <span class="inline-block w-1.5 h-1.5 bg-slate-400 rounded-full mr-1.5"></span>
                                                {{ $task->name }}
                                                <span class="text-slate-500">({{ number_format($task->total_estimated_hours, 2) }}h)</span>
                                            </div>
                                            
                                            @if($task->subtasks->count() > 0)
                                            <div class="ml-4 mt-1 space-y-0.5">
                                                @foreach($task->subtasks->sortBy('sort_order') as $subtask)
                                                <div class="text-xs text-slate-500">
                                                    <span class="inline-block w-1 h-1 bg-slate-300 rounded-full mr-1"></span>
                                                    {{ $subtask->name }}
                                                    @if($subtask->estimated_hours)
                                                    <span class="text-slate-400">({{ $subtask->estimated_hours }}h)</span>
                                                    @endif
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-500 text-center py-4">No milestones defined yet</p>
                        @endif
                    </div>
                </div>
                
                {{-- Activity Timeline --}}
                <div class="bg-white/80 backdrop-blur-sm shadow-sm rounded-xl border border-slate-200/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="text-lg font-semibold text-slate-900">Activity Timeline</h2>
                        <p class="text-sm text-slate-500 mt-1">All changes and updates to this service package ({{ $service->activities->count() }} activities)</p>
                    </div>
                    @if($service->activities->count() > 0)
                    <div class="max-h-[32rem] overflow-y-auto">
                        <div class="divide-y divide-slate-100">
                            @foreach($service->activities as $activity)
                            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors duration-150">
                                {{-- Main action line --}}
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="{{ $activity->icon }}"></span>
                                        <span class="font-medium text-slate-900">
                                            {{ $activity->user ? $activity->user->name : 'System' }}
                                        </span>
                                        <span class="text-slate-600">{{ $activity->description }}</span>
                                    </div>
                                </div>
                                
                                {{-- Date and IP line --}}
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $activity->created_at->format('d-m-Y H:i:s') }}
                                    <span class="text-slate-400">({{ $activity->created_at->diffForHumans() }})</span>
                                    @if($activity->ip_address)
                                        <span class="text-slate-400">•</span>
                                        <span>IP: {{ $activity->ip_address }}</span>
                                    @endif
                                </div>
                                
                                {{-- Changed fields --}}
                                @if($activity->formatted_changes && count($activity->formatted_changes) > 0)
                                <div class="mt-3 ml-7">
                                    <div class="bg-slate-50 rounded-lg p-3">
                                        <p class="text-xs font-medium text-slate-700 mb-2">
                                            Changed Fields ({{ count($activity->formatted_changes) }})
                                        </p>
                                        <div class="space-y-2">
                                            @foreach($activity->formatted_changes as $change)
                                            <div class="text-sm">
                                                <div class="font-medium text-slate-600">{{ $change['field'] }}:</div>
                                                <div class="ml-4 mt-1">
                                                    @if($activity->action === 'created')
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-green-600">→</span>
                                                            <span class="text-slate-800">{{ is_array($change['new']) ? implode(', ', $change['new']) : ($change['new'] ?: '(empty)') }}</span>
                                                        </div>
                                                    @elseif($activity->action === 'deleted')
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-slate-500 line-through">{{ is_array($change['old']) ? implode(', ', $change['old']) : ($change['old'] ?: '(empty)') }}</span>
                                                            <span class="text-red-600">→</span>
                                                            <span class="text-red-600 italic">(deleted)</span>
                                                        </div>
                                                    @else
                                                        <div class="space-y-1">
                                                            <div class="flex items-center space-x-2">
                                                                <span class="w-4"></span>
                                                                <span class="text-slate-500 line-through">
                                                                    {{ is_array($change['old']) ? implode(', ', $change['old']) : ($change['old'] ?: '(empty)') }}
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center space-x-2">
                                                                <span class="text-blue-600">→</span>
                                                                <span class="text-slate-800 font-medium">
                                                                    {{ is_array($change['new']) ? implode(', ', $change['new']) : ($change['new'] ?: '(empty)') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="p-6 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-slate-500">No activity recorded yet</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Right Column - Pricing & Stats --}}
            <div class="space-y-4">
                {{-- Pricing Card --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Pricing</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <p class="text-xs text-slate-600">Service Price</p>
                            <p class="text-2xl font-bold text-slate-900">€ {{ number_format($service->total_price, 2, ',', '.') }}</p>
                        </div>
                        @if($service->default_hourly_rate)
                        <div>
                            <p class="text-xs text-slate-600">Default Hourly Rate</p>
                            <p class="text-lg font-semibold text-slate-700">€ {{ number_format($service->default_hourly_rate, 2, ',', '.') }}/h</p>
                        </div>
                        @endif
                        @if($service->estimated_hours)
                        <div>
                            <p class="text-xs text-slate-600">Estimated Hours</p>
                            <p class="text-lg font-semibold text-slate-700">{{ $service->estimated_hours }} hours</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Statistics Card --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Statistics</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-600">Milestones</span>
                            <span class="text-sm font-medium text-slate-900">{{ $service->milestones->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-600">Tasks</span>
                            <span class="text-sm font-medium text-slate-900">{{ $service->milestones->sum(fn($m) => $m->tasks->count()) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-600">Subtasks</span>
                            <span class="text-sm font-medium text-slate-900">{{ $service->milestones->sum(fn($m) => $m->tasks->sum(fn($t) => $t->subtasks->count())) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-600">Total Hours</span>
                            <span class="text-sm font-medium text-slate-900">{{ $totalHours ?? 0 }}h</span>
                        </div>
                    </div>
                </div>

                {{-- Meta Information --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Meta Information</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <p class="text-xs text-slate-600">Created</p>
                            <p class="text-sm text-slate-700">{{ \App\Helpers\DateHelper::format($service->created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600">Last Updated</p>
                            <p class="text-sm text-slate-700">{{ \App\Helpers\DateHelper::format($service->updated_at) }}</p>
                        </div>
                        @if($service->color)
                        <div>
                            <p class="text-xs text-slate-600 mb-1">Service Color</p>
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded bg-{{ $service->color }}-500"></div>
                                <span class="text-sm text-slate-700 capitalize">{{ $service->color }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="p-4 space-y-2">
                        <a href="{{ route('services.edit', $service) }}" class="w-full px-3 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Service
                        </a>
                        <form action="{{ route('services.destroy', $service) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-3 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-all duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Service
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Extra ruimte onderaan --}}
        <div class="pb-32"></div>
    </div>
</div>
@endsection