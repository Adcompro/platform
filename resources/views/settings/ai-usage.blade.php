@extends('layouts.app')

@section('title', 'AI Usage & Costs')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 flex items-center">
                        <svg class="w-8 h-8 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        AI Usage & Costs
                    </h1>
                    <p class="text-sm text-slate-600 mt-1">Monitor AI API usage and associated costs</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('settings.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        Back to Settings
                    </a>
                    <button onclick="refreshStats()" class="px-3 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-all duration-200">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Cost Overview Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- Today's Cost --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 mb-1">Today's Cost</p>
                        <p class="text-2xl font-bold text-slate-900">€{{ number_format($todayCost, 4) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $todayRequests }} requests</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            {{-- This Week --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 mb-1">This Week</p>
                        <p class="text-2xl font-bold text-slate-900">€{{ number_format($weekCost, 4) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $weekRequests }} requests</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            {{-- This Month --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 mb-1">This Month</p>
                        <p class="text-2xl font-bold text-slate-900">€{{ number_format($monthCost, 4) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $monthRequests }} requests</p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            {{-- Total All Time --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-600 mb-1">Total All Time</p>
                        <p class="text-2xl font-bold text-slate-900">€{{ number_format($totalCost, 4) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $totalRequests }} requests</p>
                    </div>
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Usage by Feature --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Feature Breakdown --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Usage by Feature</h2>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @foreach($featureUsage as $feature)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full mr-3" style="background: {{ $feature['color'] }}"></span>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $feature['name'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $feature['count'] }} requests</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-900">€{{ number_format($feature['cost'], 4) }}</p>
                                <p class="text-xs text-slate-500">{{ $feature['percentage'] }}%</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            {{-- Model Usage --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Model Distribution</h2>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @foreach($modelUsage as $model)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-slate-700">{{ $model['name'] }}</span>
                                <span class="text-sm text-slate-600">{{ $model['percentage'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 h-2 rounded-full" style="width: {{ $model['percentage'] }}%"></div>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-xs text-slate-500">{{ $model['count'] }} requests</span>
                                <span class="text-xs text-slate-600">€{{ number_format($model['cost'], 4) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent AI Requests --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200/50 flex justify-between items-center">
                <h2 class="text-base font-medium text-slate-900">Recent AI Requests</h2>
                <span class="text-xs text-slate-500">Last 50 requests</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Feature</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Model</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tokens</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cost</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Response</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($recentRequests as $request)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-slate-600">
                                {{ $request->created_at->format('d-m H:i') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $request->feature == 'chat' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $request->feature == 'task_generator' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $request->feature == 'predictions' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $request->feature == 'project_health' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $request->feature == 'digest' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                ">
                                    {{ str_replace('_', ' ', $request->feature) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-slate-600">
                                {{ str_replace('claude-3-', '', $request->model) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-slate-600">
                                {{ number_format($request->total_tokens) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-slate-900">
                                €{{ number_format($request->cost_in_euros, 6) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-slate-600">
                                {{ $request->response_time_ms }}ms
                                @if($request->cached_response)
                                    <span class="text-green-600">(cached)</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if($request->status == 'success')
                                    <span class="text-green-600">✓</span>
                                @else
                                    <span class="text-red-600 cursor-help" title="{{ $request->error_message }}">✗</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Cost Projection --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-blue-900">Monthly Cost Projection</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        Based on current usage patterns, estimated monthly cost: 
                        <span class="font-bold">€{{ number_format($projectedMonthlyCost, 2) }}</span>
                    </p>
                    <p class="text-xs text-blue-600 mt-2">
                        Average cost per request: €{{ number_format($avgCostPerRequest, 6) }} | 
                        Average tokens per request: {{ number_format($avgTokensPerRequest) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshStats() {
    location.reload();
}
</script>
@endsection