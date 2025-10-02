@extends('layouts.app')

@section('title', 'AI Project Intelligence')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        <i class="fas fa-brain text-purple-600 mr-2"></i>
                        AI Project Intelligence
                    </h1>
                    <p class="text-sm text-slate-600 mt-1">Powered by Claude AI - Real-time project health monitoring and predictions</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Insights Summary --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            {{-- Average Health --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Average Health</p>
                        <p class="text-2xl font-bold mt-1">
                            <span class="{{ $insights['average_health'] >= 70 ? 'text-green-700' : ($insights['average_health'] >= 40 ? 'text-yellow-700' : 'text-red-700') }}">
                                {{ $insights['average_health'] }}%
                            </span>
                        </p>
                    </div>
                    <div class="w-10 h-10 {{ $insights['average_health'] >= 70 ? 'bg-green-100' : ($insights['average_health'] >= 40 ? 'bg-yellow-100' : 'bg-red-100') }} rounded-lg flex items-center justify-center">
                        <i class="fas fa-heartbeat {{ $insights['average_health'] >= 70 ? 'text-green-600' : ($insights['average_health'] >= 40 ? 'text-yellow-600' : 'text-red-600') }}"></i>
                    </div>
                </div>
            </div>

            {{-- Projects at Risk --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">At Risk</p>
                        <p class="text-2xl font-bold text-orange-700 mt-1">{{ $insights['total_at_risk'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-orange-600"></i>
                    </div>
                </div>
            </div>

            {{-- Critical Projects --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Critical</p>
                        <p class="text-2xl font-bold text-red-700 mt-1">{{ count($criticalProjects) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-fire text-red-600"></i>
                    </div>
                </div>
            </div>

            {{-- Warning Projects --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Warning</p>
                        <p class="text-2xl font-bold text-yellow-700 mt-1">{{ count($warningProjects) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-yellow-600"></i>
                    </div>
                </div>
            </div>

            {{-- Overdue --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Overdue</p>
                        <p class="text-2xl font-bold text-purple-700 mt-1">{{ $insights['total_overdue'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Common Issues --}}
        @if(!empty($insights['common_issues']))
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <h3 class="text-sm font-medium text-yellow-900 mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                Most Common Issues Detected
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($insights['common_issues'] as $issue)
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                    {{ $issue }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Projects List --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200/60">
                <h2 class="text-lg font-semibold text-slate-900">Project Health Monitor</h2>
            </div>
            
            @if(empty($projectsWithHealth))
                <div class="p-8 text-center">
                    <i class="fas fa-folder-open text-slate-300 text-5xl mb-4"></i>
                    <p class="text-lg font-medium text-slate-900">No active projects found</p>
                    <p class="text-sm text-slate-600 mt-1">Create a project to start monitoring its health.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Health Score</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Key Issues</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">AI Insight</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            @foreach($projectsWithHealth as $item)
                            @php
                                $project = $item['project'];
                                $health = $item['health'];
                                $scoreColor = $health['score'] >= 70 ? 'text-green-700' : ($health['score'] >= 40 ? 'text-yellow-700' : 'text-red-700');
                                $statusClass = match($health['status']) {
                                    'healthy' => 'bg-green-100 text-green-700',
                                    'warning' => 'bg-yellow-100 text-yellow-700',
                                    'critical' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $project->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            @if($project->end_date)
                                                Due: {{ \Carbon\Carbon::parse($project->end_date)->format('d-m-Y') }}
                                            @endif
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-900">{{ $project->customer->name ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-2xl font-bold {{ $scoreColor }}">{{ $health['score'] }}</span>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="h-1.5 rounded-full {{ $health['score'] >= 70 ? 'bg-green-600' : ($health['score'] >= 40 ? 'bg-yellow-600' : 'bg-red-600') }}" 
                                                 style="width: {{ $health['score'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                        {{ ucfirst($health['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if(!empty($health['issues']))
                                        <ul class="text-xs text-slate-600 space-y-1">
                                            @foreach(array_slice($health['issues'], 0, 2) as $issue)
                                            <li>• {{ Str::limit($issue, 40) }}</li>
                                            @endforeach
                                            @if(count($health['issues']) > 2)
                                            <li class="text-slate-400">+{{ count($health['issues']) - 2 }} more</li>
                                            @endif
                                        </ul>
                                    @else
                                        <span class="text-xs text-green-600">No issues detected</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if(!empty($health['strengths']) && $health['status'] == 'healthy')
                                        <p class="text-xs text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ $health['strengths'][0] ?? 'On track' }}
                                        </p>
                                    @elseif($health['status'] == 'critical')
                                        <p class="text-xs text-red-600">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Immediate attention required
                                        </p>
                                    @elseif($health['status'] == 'warning')
                                        <p class="text-xs text-yellow-600">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            Monitor closely
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-500">
                                            {{ $health['summary'] ?? 'Analysis in progress...' }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('project-intelligence.show', $project->id) }}" 
                                           class="text-purple-600 hover:text-purple-800 transition-colors"
                                           title="View AI Analysis">
                                            <i class="fas fa-brain"></i>
                                        </a>
                                        <a href="{{ route('projects.show', $project->id) }}" 
                                           class="text-slate-400 hover:text-slate-600 transition-colors"
                                           title="View Project">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- AI Powered Badge --}}
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-100 to-indigo-100 border border-purple-200 rounded-full">
                <i class="fas fa-sparkles text-purple-600 mr-2"></i>
                <span class="text-sm font-medium text-purple-900">Powered by Claude AI</span>
                <span class="ml-2 text-xs text-purple-600">• Real-time analysis</span>
            </div>
        </div>
    </div>
</div>
@endsection