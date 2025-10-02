@extends('layouts.app')

@section('title', 'Weekly Digest Preview')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Weekly Digest Preview</h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Period: {{ \Carbon\Carbon::parse($digest['start_date'])->format('M d') }} - {{ \Carbon\Carbon::parse($digest['end_date'])->format('M d, Y') }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('ai-digest.index') }}" 
                       class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Settings
                    </a>
                    <a href="{{ route('ai-digest.download') }}" 
                       class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-all">
                        <i class="fas fa-download mr-2"></i>
                        Download PDF
                    </a>
                    <form action="{{ route('ai-digest.generate') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="send_email" value="true">
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-all">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send to Recipients
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Email Preview Container --}}
        <div class="bg-white rounded-xl overflow-hidden" style="box-shadow: var(--theme-card-shadow);">
            {{-- Email Header --}}
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-8 py-6">
                <div class="flex items-center mb-4">
                    <div class="bg-white/20 rounded-lg p-3 mr-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v7m3-2h6l2 2H7l2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Weekly Project Digest</h1>
                        <p class="text-purple-100">{{ $digest['company'] }}</p>
                    </div>
                </div>
                <div class="bg-white/10 rounded-lg px-4 py-2 inline-block">
                    <span class="text-white text-sm">
                        {{ \Carbon\Carbon::parse($digest['start_date'])->format('F d') }} - {{ \Carbon\Carbon::parse($digest['end_date'])->format('F d, Y') }}
                    </span>
                </div>
            </div>

            {{-- AI Summary --}}
            @if(isset($digest['ai_summary']))
            <div class="px-8 py-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 rounded-lg p-2 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </span>
                    Executive Summary
                </h2>
                <div class="prose prose-slate max-w-none text-sm">
                    {!! nl2br(e($digest['ai_summary'])) !!}
                </div>
            </div>
            @endif

            {{-- Project Statistics --}}
            @if(isset($digest['projects']))
            <div class="px-8 py-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Project Overview</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600">{{ $digest['projects']['new'] }}</div>
                        <div class="text-xs text-blue-700">New Projects</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-green-600">{{ $digest['projects']['completed'] }}</div>
                        <div class="text-xs text-green-700">Completed</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-yellow-600">{{ $digest['projects']['active'] }}</div>
                        <div class="text-xs text-yellow-700">Active</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-red-600">{{ $digest['projects']['at_risk'] }}</div>
                        <div class="text-xs text-red-700">At Risk</div>
                    </div>
                </div>

                @if($digest['projects']['top_projects']->count() > 0)
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Top Performing Projects</h3>
                    <div class="space-y-2">
                        @foreach($digest['projects']['top_projects'] as $project)
                        <div class="flex items-center justify-between bg-slate-50 rounded-lg px-4 py-2">
                            <span class="text-sm font-medium text-slate-700">{{ $project->name }}</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-slate-200 rounded-full h-2 mr-3">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $project->completion_rate }}%"></div>
                                </div>
                                <span class="text-xs text-slate-600">{{ $project->completion_rate }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Time Tracking --}}
            @if(isset($digest['time']))
            <div class="px-8 py-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Time Tracking Summary</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-slate-700">{{ $digest['time']['total_hours'] }}</div>
                        <div class="text-xs text-slate-500">Total Hours</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $digest['time']['billable_percentage'] }}%</div>
                        <div class="text-xs text-slate-500">Billable</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $digest['time']['daily_average'] }}</div>
                        <div class="text-xs text-slate-500">Daily Average</div>
                    </div>
                </div>

                @if($digest['time']['top_contributors']->count() > 0)
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Top Contributors</h3>
                    <div class="space-y-2">
                        @foreach($digest['time']['top_contributors'] as $contributor)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">{{ $contributor['user'] }}</span>
                            <span class="text-sm font-medium text-slate-700">{{ $contributor['hours'] }} hours</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Invoicing --}}
            @if(isset($digest['invoices']))
            <div class="px-8 py-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Invoice Summary</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-slate-600 mb-1">Generated</div>
                        <div class="text-2xl font-bold text-slate-700">
                            {{ $digest['invoices']['new_count'] }} invoices
                        </div>
                        <div class="text-lg text-slate-600">
                            €{{ number_format($digest['invoices']['total_amount'], 2) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-600 mb-1">Collected</div>
                        <div class="text-2xl font-bold text-green-600">
                            {{ $digest['invoices']['collection_rate'] }}%
                        </div>
                        <div class="text-lg text-green-600">
                            €{{ number_format($digest['invoices']['paid_amount'], 2) }}
                        </div>
                    </div>
                </div>
                
                @if($digest['invoices']['overdue_count'] > 0)
                <div class="mt-4 bg-red-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700">
                            {{ $digest['invoices']['overdue_count'] }} overdue invoices totaling €{{ number_format($digest['invoices']['overdue_amount'], 2) }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Risk Analysis --}}
            @if(isset($digest['risks']) && count($digest['risks']) > 0)
            <div class="px-8 py-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Risk Analysis</h2>
                <div class="space-y-3">
                    @foreach($digest['risks'] as $risk)
                    <div class="flex items-start space-x-3">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full 
                            {{ $risk['severity'] == 'critical' ? 'bg-red-100 text-red-600' : '' }}
                            {{ $risk['severity'] == 'high' ? 'bg-orange-100 text-orange-600' : '' }}
                            {{ $risk['severity'] == 'medium' ? 'bg-yellow-100 text-yellow-600' : '' }}
                            {{ $risk['severity'] == 'low' ? 'bg-blue-100 text-blue-600' : '' }}">
                            <span class="text-xs font-bold">!</span>
                        </span>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-slate-700">{{ $risk['project'] }}</div>
                            <div class="text-sm text-slate-600">{{ $risk['message'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- AI Recommendations --}}
            @if(isset($digest['ai_recommendations']) && count($digest['ai_recommendations']) > 0)
            <div class="px-8 py-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 rounded-lg p-2 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </span>
                    AI Recommendations
                </h2>
                <div class="space-y-4">
                    @foreach($digest['ai_recommendations'] as $index => $rec)
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4">
                        <div class="flex items-start">
                            <span class="flex-shrink-0 bg-white text-purple-600 rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1">
                                <div class="flex items-center mb-1">
                                    <span class="text-xs px-2 py-0.5 rounded-full 
                                        {{ $rec['priority'] == 'high' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $rec['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $rec['priority'] == 'low' ? 'bg-green-100 text-green-700' : '' }}">
                                        {{ ucfirst($rec['priority']) }} Priority
                                    </span>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900 mb-1">{{ $rec['action'] }}</h3>
                                @if($rec['reason'])
                                <p class="text-sm text-slate-600 mb-1">{{ $rec['reason'] }}</p>
                                @endif
                                @if($rec['expected_outcome'])
                                <p class="text-xs text-purple-700 font-medium">Expected outcome: {{ $rec['expected_outcome'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Footer --}}
            <div class="bg-slate-50 px-8 py-6 text-center">
                <p class="text-sm text-slate-600 mb-2">
                    This report was automatically generated by your AI Project Assistant
                </p>
                <p class="text-xs text-slate-500">
                    Powered by Claude AI • {{ now()->format('F d, Y H:i') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection