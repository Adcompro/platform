@extends('layouts.app')

@section('title', 'Media Campaigns - ' . $project->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('projects.show', $project->id) }}" class="text-slate-600 hover:text-slate-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-slate-800">Media Campaigns - {{ $project->name }}</h1>
                </div>
                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('projects.media-campaigns.create', $project->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Campaign
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        {{-- Overview Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500" style="">Total Campaigns</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $campaigns->count() }}</p>
                    </div>
                    <div class="bg-slate-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500" style="">Active Campaigns</p>
                        <p class="text-2xl font-bold text-green-600">{{ $campaigns->where('status', 'active')->count() }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500" style="">Total Mentions</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $campaigns->sum(function($c) { return $c->metrics['total_mentions']; }) }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500" style="">Avg. Relevance</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ number_format($campaigns->avg(function($c) { return $c->metrics['average_relevance']; }), 1) }}%
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Campaigns List --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
            <div class="p-6 border-b border-slate-200/60">
                <h2 class="text-lg font-semibold text-slate-800">Campaigns</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200/60">
                            <th class="px-6 py-3 text-left" style="">Campaign</th>
                            <th class="px-6 py-3 text-left" style="">Type</th>
                            <th class="px-6 py-3 text-left" style="">Press Date</th>
                            <th class="px-6 py-3 text-left" style="">Status</th>
                            <th class="px-6 py-3 text-center" style="">Mentions</th>
                            <th class="px-6 py-3 text-center" style="">Reach</th>
                            <th class="px-6 py-3 text-center" style="">ROI</th>
                            <th class="px-6 py-3 text-right" style="">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <a href="{{ route('projects.media-campaigns.show', [$project->id, $campaign->id]) }}" 
                                           class="font-medium text-slate-900 hover:text-slate-700" style="">
                                            {{ $campaign->name }}
                                        </a>
                                        @if($campaign->children->count() > 0)
                                            <p class="text-slate-500 mt-1" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                {{ $campaign->children->count() }} sub-campaigns
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-600" style="">
                                        {{ str_replace('_', ' ', ucfirst($campaign->campaign_type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-600" style="">
                                        {{ $campaign->press_release_date->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $campaign->getStatusBadgeColor() }}-100 text-{{ $campaign->getStatusBadgeColor() }}-700">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="font-medium text-slate-900" style="">
                                            {{ $campaign->metrics['total_mentions'] }}
                                        </span>
                                        @if($campaign->metrics['high_relevance_mentions'] > 0)
                                            <span class="text-green-600" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                {{ $campaign->metrics['high_relevance_mentions'] }} high
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($campaign->expected_reach > 0)
                                        <div class="flex flex-col items-center">
                                            <span class="font-medium text-slate-900" style="">
                                                {{ number_format($campaign->actual_reach ?? 0) }}
                                            </span>
                                            <span class="text-slate-500" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                of {{ number_format($campaign->expected_reach) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-400" style="">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($campaign->metrics['roi'] !== null)
                                        <span class="{{ $campaign->metrics['roi'] > 0 ? 'text-green-600' : 'text-red-600' }} font-medium" 
                                              style="">
                                            {{ number_format($campaign->metrics['roi'], 1) }}%
                                        </span>
                                    @else
                                        <span class="text-slate-400" style="">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('projects.media-campaigns.show', [$project->id, $campaign->id]) }}" 
                                           class="text-slate-600 hover:text-slate-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <a href="{{ route('projects.media-campaigns.edit', [$project->id, $campaign->id]) }}" 
                                               class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                    No campaigns found. Create your first campaign to start tracking media coverage.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Unlinked Mentions --}}
        @if($unlinkedMentions->count() > 0)
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
                <div class="p-6 border-b border-slate-200/60">
                    <h2 class="text-lg font-semibold text-slate-800">Potential Mentions (Not Linked)</h2>
                    <p class="text-slate-600 mt-1" style="">
                        Recent media mentions that might belong to this project
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200/60">
                                <th class="px-6 py-3 text-left" style="">Article</th>
                                <th class="px-6 py-3 text-left" style="">Source</th>
                                <th class="px-6 py-3 text-left" style="">Published</th>
                                <th class="px-6 py-3 text-center" style="">Relevance</th>
                                <th class="px-6 py-3 text-right" style="">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unlinkedMentions->take(10) as $mention)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <a href="{{ $mention->article_url }}" target="_blank" 
                                           class="font-medium text-slate-900 hover:text-slate-700" style="">
                                            {{ Str::limit($mention->article_title, 60) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-slate-600" style="">
                                            {{ $mention->source_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-slate-600" style="">
                                            {{ $mention->published_at->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $mention->getRelevanceBadgeColor() }}-100 text-{{ $mention->getRelevanceBadgeColor() }}-700">
                                            {{ $mention->relevance_score }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <button onclick="showLinkModal({{ $mention->id }})" 
                                                    class="text-blue-600 hover:text-blue-800" style="">
                                                Link to Campaign
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Link Mention Modal (simplified) --}}
@if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
<script>
function showLinkModal(mentionId) {
    if (confirm('Link this mention to a campaign?')) {
        // In a real implementation, this would open a modal to select the campaign
        // For now, we'll just redirect to the first active campaign
        @if($campaigns->where('status', 'active')->first())
            window.location.href = '{{ route("projects.media-campaigns.show", [$project->id, $campaigns->where("status", "active")->first()->id]) }}';
        @else
            alert('Please create an active campaign first.');
        @endif
    }
}
</script>
@endif
@endsection