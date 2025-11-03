@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('companies.show', $company) }}" 
                           class="flex items-center px-2 py-1 bg-slate-100 text-slate-600 text-sm rounded-lg hover:bg-slate-200 transition-all">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back
                        </a>
                        <div>
                            <h1 class="text-xl font-medium text-slate-900">Activity Log</h1>
                            <p class="text-sm text-slate-600 mt-0.5">{{ $company->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 bg-slate-100 text-slate-600 text-xs font-medium rounded-full">
                        {{ $activities->total() }} activities
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Activity Timeline --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <h2 class="text-base font-medium text-slate-900">Complete Activity Timeline</h2>
                <p class="text-xs text-slate-500 mt-0.5">All changes made by all users</p>
            </div>
            
            @if($activities->count() > 0)
            <div class="p-4">
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($activities as $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex items-start space-x-3">
                                    {{-- Activity Icon --}}
                                    <div class="relative">
                                        <div class="h-10 w-10 rounded-full bg-slate-50 flex items-center justify-center ring-8 ring-white">
                                            <i class="{{ $activity->icon }} text-sm"></i>
                                        </div>
                                    </div>
                                    
                                    {{-- Activity Content --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium text-slate-900">
                                                        {{ $activity->user->name ?? 'System' }}
                                                    </span>
                                                    <span class="text-slate-600">{{ $activity->description }}</span>
                                                </div>
                                                <div class="mt-1 flex items-center space-x-2">
                                                    <p class="text-xs text-slate-500">
                                                        {{ $activity->created_at->format('d-m-Y H:i:s') }}
                                                        ({{ $activity->created_at->diffForHumans() }})
                                                    </p>
                                                    @if($activity->ip_address)
                                                    <span class="text-xs text-slate-400">• IP: {{ $activity->ip_address }}</span>
                                                    @endif
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $activity->badge_color }}">
                                                        {{ ucfirst($activity->action) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Changed Fields --}}
                                        @if($activity->formatted_changes && count($activity->formatted_changes) > 0)
                                        <div class="mt-3 bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs font-medium text-slate-700 mb-2">Changed Fields:</p>
                                            <div class="space-y-2">
                                                @foreach($activity->formatted_changes as $change)
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="font-medium text-slate-600">{{ $change['field'] }}</span>
                                                    <div class="flex items-center space-x-2">
                                                        @if($change['old'])
                                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded line-through">
                                                            {{ $change['old'] }}
                                                        </span>
                                                        <span class="text-slate-400">→</span>
                                                        @endif
                                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded">
                                                            {{ $change['new'] ?? '(empty)' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                
                {{-- Pagination --}}
                @if($activities->hasPages())
                <div class="mt-6 border-t border-slate-200 pt-4">
                    {{ $activities->links() }}
                </div>
                @endif
            </div>
            @else
            <div class="p-8 text-center">
                <i class="fas fa-history text-4xl text-slate-300 mb-3"></i>
                <p class="text-sm text-slate-500">No activity recorded yet</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection