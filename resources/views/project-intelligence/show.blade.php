@extends('layouts.app')

@section('title', 'AI Analysis - ' . $project->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <div class="flex items-center space-x-2 text-sm text-slate-600 mb-1">
                        <a href="{{ route('project-intelligence.index') }}" class="hover:text-slate-900">AI Intelligence</a>
                        <span>/</span>
                        <span>{{ $project->name }}</span>
                    </div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        <i class="fas fa-brain text-purple-600 mr-2"></i>
                        AI Project Analysis
                    </h1>
                    <p class="text-sm text-slate-600 mt-1">{{ $project->customer->name ?? 'N/A' }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <form method="POST" action="{{ route('project-intelligence.refresh', $project->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-all">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh AI Analysis
                        </button>
                    </form>
                    <a href="{{ route('projects.show', $project->id) }}" class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-all">
                        <i class="fas fa-folder-open mr-2"></i>
                        View Project
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Health Score Card --}}
        <div class="mb-6">
            @php
                $scoreColor = $healthAnalysis['score'] >= 70 ? 'from-green-500 to-emerald-600' : 
                             ($healthAnalysis['score'] >= 40 ? 'from-yellow-500 to-orange-600' : 'from-red-500 to-pink-600');
                $statusIcon = $healthAnalysis['status'] == 'healthy' ? 'fa-check-circle' : 
                             ($healthAnalysis['status'] == 'warning' ? 'fa-exclamation-circle' : 'fa-times-circle');
            @endphp
            <div class="bg-gradient-to-r {{ $scoreColor }} rounded-xl p-6 text-white shadow-lg">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-3xl font-bold mb-2">Project Health Score: {{ $healthAnalysis['score'] }}/100</h2>
                        <p class="text-white/90 text-lg">
                            <i class="fas {{ $statusIcon }} mr-2"></i>
                            Status: {{ ucfirst($healthAnalysis['status']) }}
                        </p>
                        @if(isset($healthAnalysis['summary']))
                        <p class="text-white/80 mt-3">{{ $healthAnalysis['summary'] }}</p>
                        @endif
                    </div>
                    <div class="text-6xl opacity-30">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Completion --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <p class="text-xs text-slate-600 font-medium">Completion</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $metrics['completion_percentage'] }}%</p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $metrics['completion_percentage'] }}%"></div>
                </div>
            </div>

            {{-- Budget Used --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <p class="text-xs text-slate-600 font-medium">Budget Used</p>
                <p class="text-2xl font-bold {{ $metrics['budget_used_percentage'] > 90 ? 'text-red-700' : 'text-slate-900' }} mt-1">
                    {{ $metrics['budget_used_percentage'] }}%
                </p>
                <p class="text-xs text-slate-500 mt-1">€{{ number_format($metrics['budget_used'], 2) }} / €{{ number_format($metrics['budget_total'], 2) }}</p>
            </div>

            {{-- Days Remaining --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <p class="text-xs text-slate-600 font-medium">Days Remaining</p>
                <p class="text-2xl font-bold {{ $metrics['days_remaining'] < 7 ? 'text-red-700' : 'text-slate-900' }} mt-1">
                    {{ $metrics['days_remaining'] }}
                </p>
                <p class="text-xs text-slate-500 mt-1">{{ $metrics['time_elapsed_percentage'] }}% time elapsed</p>
            </div>

            {{-- Team Activity --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <p class="text-xs text-slate-600 font-medium">Active Team</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $metrics['active_team_members'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Last 7 days</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Issues & Risks --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-red-200/50">
                    <h3 class="text-lg font-semibold text-slate-900">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                        Detected Risks & Issues
                    </h3>
                </div>
                <div class="p-6">
                    @if(!empty($healthAnalysis['issues']) || !empty($risks))
                        <div class="space-y-3">
                            @foreach($healthAnalysis['issues'] ?? [] as $issue)
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-orange-500 mt-1 mr-3"></i>
                                <p class="text-sm text-slate-700">{{ $issue }}</p>
                            </div>
                            @endforeach
                            
                            @foreach($risks as $risk)
                            <div class="border-l-4 {{ $risk['severity'] == 'critical' ? 'border-red-500' : ($risk['severity'] == 'high' ? 'border-orange-500' : 'border-yellow-500') }} pl-4 py-2">
                                <p class="text-sm font-medium text-slate-900">{{ $risk['description'] }}</p>
                                <p class="text-xs text-slate-600 mt-1">
                                    <span class="font-medium">Mitigation:</span> {{ $risk['mitigation'] }}
                                </p>
                                <span class="inline-block mt-2 px-2 py-1 text-xs font-medium rounded-full
                                    {{ $risk['severity'] == 'critical' ? 'bg-red-100 text-red-700' : 
                                       ($risk['severity'] == 'high' ? 'bg-orange-100 text-orange-700' : 
                                       ($risk['severity'] == 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ ucfirst($risk['severity']) }} Risk
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-green-600">
                            <i class="fas fa-check-circle mr-2"></i>
                            No significant risks detected at this time.
                        </p>
                    @endif
                </div>
            </div>

            {{-- AI Recommendations --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-200/50">
                    <h3 class="text-lg font-semibold text-slate-900">
                        <i class="fas fa-lightbulb text-purple-600 mr-2"></i>
                        AI Recommendations
                    </h3>
                </div>
                <div class="p-6">
                    @if(!empty($recommendations))
                        <div class="space-y-3">
                            @foreach($recommendations as $rec)
                            <div class="border-l-4 {{ $rec['priority'] == 'high' ? 'border-purple-500' : ($rec['priority'] == 'medium' ? 'border-blue-500' : 'border-gray-400') }} pl-4 py-2">
                                <p class="text-sm font-medium text-slate-900">{{ $rec['action'] }}</p>
                                <p class="text-xs text-slate-600 mt-1">
                                    <span class="font-medium">Reason:</span> {{ $rec['reason'] }}
                                </p>
                                <p class="text-xs text-slate-600 mt-1">
                                    <span class="font-medium">Impact:</span> {{ $rec['impact'] }}
                                </p>
                                <span class="inline-block mt-2 px-2 py-1 text-xs font-medium rounded-full
                                    {{ $rec['priority'] == 'high' ? 'bg-purple-100 text-purple-700' : 
                                       ($rec['priority'] == 'medium' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ ucfirst($rec['priority']) }} Priority
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-600">
                            <i class="fas fa-info-circle mr-2"></i>
                            AI is analyzing project data to generate recommendations...
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Completion Prediction --}}
        @if($completionPrediction)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-3">
                <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                Completion Prediction
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-slate-600">Predicted Completion</p>
                    <p class="text-xl font-bold text-slate-900">
                        {{ \Carbon\Carbon::parse($completionPrediction['predicted_date'])->format('d F Y') }}
                    </p>
                    @if($completionPrediction['predicted_date'] > $project->end_date)
                        <p class="text-xs text-red-600 mt-1">
                            {{ (int) \Carbon\Carbon::parse($completionPrediction['predicted_date'])->diffInDays($project->end_date) }} days after deadline
                        </p>
                    @else
                        <p class="text-xs text-green-600 mt-1">
                            {{ (int) \Carbon\Carbon::parse($project->end_date)->diffInDays($completionPrediction['predicted_date']) }} days before deadline
                        </p>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-slate-600">Confidence Level</p>
                    <p class="text-xl font-bold {{ $completionPrediction['confidence'] == 'high' ? 'text-green-700' : ($completionPrediction['confidence'] == 'medium' ? 'text-yellow-700' : 'text-red-700') }}">
                        {{ ucfirst($completionPrediction['confidence']) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-slate-600">Key Factors</p>
                    <ul class="text-xs text-slate-700 mt-1">
                        @foreach(array_slice($completionPrediction['factors'] ?? [], 0, 3) as $factor)
                        <li>• {{ $factor }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Strengths --}}
        @if(!empty($healthAnalysis['strengths']))
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-3">
                <i class="fas fa-star text-green-600 mr-2"></i>
                Project Strengths
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($healthAnalysis['strengths'] as $strength)
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                    <p class="text-sm text-slate-700">{{ $strength }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- AI Powered Badge --}}
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-100 to-indigo-100 border border-purple-200 rounded-full">
                <i class="fas fa-sparkles text-purple-600 mr-2"></i>
                <span class="text-sm font-medium text-purple-900">Analysis powered by Claude AI</span>
                <span class="ml-2 text-xs text-purple-600">• Updated {{ now()->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
@endsection