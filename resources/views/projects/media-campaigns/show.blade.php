@extends('layouts.app')

@section('title', $campaign->name . ' - Media Campaign')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('projects.media-campaigns.index', $project->id) }}" class="text-slate-600 hover:text-slate-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $campaign->name }}</h1>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $campaign->getStatusBadgeColor() }}-100 text-{{ $campaign->getStatusBadgeColor() }}-700">
                        {{ ucfirst($campaign->status) }}
                    </span>
                </div>
                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('projects.media-campaigns.edit', [$project->id, $campaign->id]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Campaign
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        {{-- Campaign Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Campaign Details --}}
            <div class="md:col-span-2 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Campaign Details</h2>
                
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-slate-500" style="">Type</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            {{ str_replace('_', ' ', ucfirst($campaign->campaign_type)) }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-slate-500" style="">Press Release Date</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            {{ $campaign->press_release_date->format('M d, Y') }}
                        </dd>
                    </div>
                    
                    @if($campaign->target_audience)
                    <div>
                        <dt class="text-slate-500" style="">Target Audience</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $campaign->target_audience }}</dd>
                    </div>
                    @endif
                    
                    @if($campaign->keywords)
                    <div class="md:col-span-2">
                        <dt class="text-slate-500" style="">Keywords</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-2">
                                @foreach($campaign->keywords as $keyword)
                                    <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded-md text-sm">
                                        {{ $keyword }}
                                    </span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    @endif
                    
                    @if($campaign->description)
                    <div class="md:col-span-2">
                        <dt class="text-slate-500" style="">Description</dt>
                        <dd class="mt-1 text-slate-700">{{ $campaign->description }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Combined Metrics Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Performance</h2>
                
                <div class="space-y-4">
                    {{-- RSS Mentions --}}
                    <div class="pb-3 border-b border-slate-200">
                        <p class="text-slate-500" style="">RSS/News Mentions</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $metrics['total_mentions'] ?? 0 }}</p>
                        <p class="text-green-600" style="font-size: calc(var(--theme-font-size) - 2px);">
                            {{ $metrics['high_relevance_mentions'] ?? 0 }} high relevance
                        </p>
                    </div>
                    
                    {{-- Social Media Mentions --}}
                    <div class="pb-3 border-b border-slate-200">
                        <p class="text-slate-500" style="">Social Media</p>
                        @php
                            $socialMentions = \App\Models\ProjectSocialMention::where('campaign_id', $campaign->id)->count();
                            $highConfidenceSocial = \App\Models\ProjectSocialMention::where('campaign_id', $campaign->id)
                                ->where('confidence_score', '>=', 70)->count();
                        @endphp
                        <p class="text-2xl font-bold text-slate-900">{{ $socialMentions }}</p>
                        <p class="text-blue-600" style="font-size: calc(var(--theme-font-size) - 2px);">
                            {{ $highConfidenceSocial }} high confidence
                        </p>
                    </div>
                    
                    {{-- Combined Metrics --}}
                    <div>
                        <p class="text-slate-500" style="">Average Sentiment</p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-lg">😊</span>
                            <span class="text-slate-700">{{ $metrics['avg_sentiment'] ?? 'Neutral' }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-slate-500" style="">Total Reach</p>
                        @php
                            $totalReach = 0;
                            $socialReach = \App\Models\ProjectSocialMention::where('campaign_id', $campaign->id)
                                ->join('social_media_mentions', 'project_social_mentions.social_mention_id', '=', 'social_media_mentions.id')
                                ->sum('social_media_mentions.author_followers');
                            $totalReach += $socialReach;
                        @endphp
                        <p class="text-xl font-bold text-slate-900">
                            @if($totalReach >= 1000000)
                                {{ round($totalReach / 1000000, 1) }}M
                            @elseif($totalReach >= 1000)
                                {{ round($totalReach / 1000, 1) }}K
                            @else
                                {{ $totalReach }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mentions Timeline Chart --}}
        @if($mentionsTimeline->count() > 0)
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Mentions Timeline</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="mentionsChart"></canvas>
            </div>
        </div>
        @endif

        {{-- Mentions Tabs --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
            <div class="border-b border-slate-200">
                <nav class="flex -mb-px" x-data="{ activeTab: 'all' }">
                    <button @click="activeTab = 'all'" 
                            :class="activeTab === 'all' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="py-3 px-6 border-b-2 font-medium transition-colors">
                        All Mentions
                    </button>
                    <button @click="activeTab = 'rss'" 
                            :class="activeTab === 'rss' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="py-3 px-6 border-b-2 font-medium transition-colors">
                        RSS/News ({{ $mentions->count() }})
                    </button>
                    <button @click="activeTab = 'social'" 
                            :class="activeTab === 'social' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="py-3 px-6 border-b-2 font-medium transition-colors">
                        Social Media ({{ $socialMentions }})
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div x-data="{ activeTab: 'all' }">
                {{-- All Mentions Tab --}}
                <div x-show="activeTab === 'all'" class="overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Platform</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Title/Content</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Source</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Date</th>
                                <th class="px-6 py-3 text-center font-medium text-slate-700" style="">Score</th>
                                <th class="px-6 py-3 text-center font-medium text-slate-700" style="">Sentiment</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-700" style="">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            {{-- RSS Mentions --}}
                            @foreach($mentions as $mention)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                        RSS
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ $mention->mediaMention->article_url }}" target="_blank" class="text-slate-900 hover:text-blue-600 font-medium">
                                        {{ Str::limit($mention->mediaMention->article_title, 80) }}
                                    </a>
                                    @if($mention->mediaMention->article_excerpt)
                                        <p class="text-slate-500 mt-1" style="font-size: calc(var(--theme-font-size) - 2px);">
                                            {{ Str::limit($mention->mediaMention->article_excerpt, 120) }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-slate-700">{{ $mention->mediaMention->source?->name ?? 'Unknown' }}</p>
                                    <p class="text-slate-500" style="font-size: calc(var(--theme-font-size) - 2px);">
                                        {{ ucfirst($mention->mediaMention->source?->category ?? 'general') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-slate-600" style="">
                                    {{ $mention->mediaMention->published_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $mention->getRelevanceBadgeColor() }}-100 text-{{ $mention->getRelevanceBadgeColor() }}-700">
                                        {{ $mention->relevance_score }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($mention->sentiment)
                                        <span class="text-lg">{{ $mention->getSentimentEmoji() }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                        <form action="{{ route('projects.media-campaigns.unlink-mention', [$project->id, $campaign->id, $mention->id]) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Unlink this mention from the campaign?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            
                            {{-- Social Media Mentions --}}
                            @php
                                $socialMentionsList = \App\Models\ProjectSocialMention::where('campaign_id', $campaign->id)
                                    ->with(['socialMention', 'socialMention.source'])
                                    ->orderBy('created_at', 'desc')
                                    ->get();
                            @endphp
                            @foreach($socialMentionsList as $socialMention)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                        {{ ucfirst($socialMention->socialMention->source->platform ?? 'Social') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ $socialMention->socialMention->post_url }}" target="_blank" class="text-slate-900 hover:text-blue-600 font-medium">
                                        {{ Str::limit($socialMention->socialMention->content, 120) }}
                                    </a>
                                    @if($socialMention->socialMention->hashtags)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($socialMention->socialMention->hashtags as $hashtag)
                                                <span class="text-blue-600" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                    #{{ $hashtag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-slate-700">{{ $socialMention->socialMention->author_name }}</p>
                                    <p class="text-slate-500" style="font-size: calc(var(--theme-font-size) - 2px);">
                                        @{{ $socialMention->socialMention->author_handle }}
                                        @if($socialMention->socialMention->author_verified)
                                            <span class="text-blue-500">✓</span>
                                        @endif
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-slate-600" style="">
                                    {{ $socialMention->socialMention->published_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $socialMention->getConfidenceBadgeColor() }}-100 text-{{ $socialMention->getConfidenceBadgeColor() }}-700">
                                        {{ $socialMention->confidence_score }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <span title="Likes">❤️ {{ $socialMention->socialMention->formatMetric($socialMention->socialMention->likes_count) }}</span>
                                        <span title="Shares">🔄 {{ $socialMention->socialMention->formatMetric($socialMention->socialMention->shares_count) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                        <button onclick="unlinkSocialMention({{ $socialMention->id }})" class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($mentions->isEmpty() && $socialMentionsList->isEmpty())
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    No mentions found yet. They will appear here as they are discovered.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- RSS Mentions Tab --}}
                <div x-show="activeTab === 'rss'" class="overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Title</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Source</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Date</th>
                                <th class="px-6 py-3 text-center font-medium text-slate-700" style="">Relevance</th>
                                <th class="px-6 py-3 text-center font-medium text-slate-700" style="">Sentiment</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-700" style="">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($mentions as $mention)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <a href="{{ $mention->mediaMention->article_url }}" target="_blank" class="text-slate-900 hover:text-blue-600 font-medium">
                                            {{ Str::limit($mention->mediaMention->article_title, 80) }}
                                        </a>
                                        @if($mention->mediaMention->article_excerpt)
                                            <p class="text-slate-500 mt-1" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                {{ Str::limit($mention->mediaMention->article_excerpt, 120) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-slate-700">{{ $mention->mediaMention->source?->name ?? 'Unknown' }}</p>
                                        <p class="text-slate-500" style="font-size: calc(var(--theme-font-size) - 2px);">
                                            {{ ucfirst($mention->mediaMention->source?->category ?? 'general') }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600" style="">
                                        {{ $mention->mediaMention->published_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $mention->getRelevanceBadgeColor() }}-100 text-{{ $mention->getRelevanceBadgeColor() }}-700">
                                            {{ $mention->relevance_score }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($mention->sentiment)
                                            <span class="text-lg">{{ $mention->getSentimentEmoji() }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <form action="{{ route('projects.media-campaigns.unlink-mention', [$project->id, $campaign->id, $mention->id]) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Unlink this mention from the campaign?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        No RSS/News mentions found yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Social Media Tab --}}
                <div x-show="activeTab === 'social'" class="overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Platform</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Content</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-700" style="">Author</th>
                                <th class="px-6 py-3 text-center font-medium text-slate-700" style="">Engagement</th>
                                <th class="px-6 py-3 text-center font-medium text-slate-700" style="">Confidence</th>
                                <th class="px-6 py-3 text-right font-medium text-slate-700" style="">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($socialMentionsList as $socialMention)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                            {{ ucfirst($socialMention->socialMention->source->platform ?? 'Social') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ $socialMention->socialMention->post_url }}" target="_blank" class="text-slate-900 hover:text-blue-600 font-medium">
                                            {{ Str::limit($socialMention->socialMention->content, 120) }}
                                        </a>
                                        @if($socialMention->socialMention->hashtags)
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($socialMention->socialMention->hashtags as $hashtag)
                                                    <span class="text-blue-600" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                        #{{ $hashtag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-slate-700">{{ $socialMention->socialMention->author_name }}</p>
                                        <p class="text-slate-500" style="font-size: calc(var(--theme-font-size) - 2px);">
                                            @{{ $socialMention->socialMention->author_handle }}
                                            @if($socialMention->socialMention->author_verified)
                                                <span class="text-blue-500">✓</span>
                                            @endif
                                        </p>
                                        <p class="text-slate-400" style="font-size: calc(var(--theme-font-size) - 2px);">
                                            {{ number_format($socialMention->socialMention->author_followers) }} followers
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center space-y-1">
                                            <div class="flex items-center space-x-2">
                                                <span title="Likes">❤️ {{ $socialMention->socialMention->formatMetric($socialMention->socialMention->likes_count) }}</span>
                                                <span title="Shares">🔄 {{ $socialMention->socialMention->formatMetric($socialMention->socialMention->shares_count) }}</span>
                                                <span title="Comments">💬 {{ $socialMention->socialMention->formatMetric($socialMention->socialMention->comments_count) }}</span>
                                            </div>
                                            @if($socialMention->socialMention->engagement_rate)
                                                <span class="text-xs text-slate-500">{{ number_format($socialMention->socialMention->engagement_rate, 1) }}% rate</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $socialMention->getConfidenceBadgeColor() }}-100 text-{{ $socialMention->getConfidenceBadgeColor() }}-700">
                                            {{ $socialMention->confidence_score }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <button onclick="unlinkSocialMention({{ $socialMention->id }})" class="text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        No social media mentions found yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($mentionsTimeline->count() > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    // Unique ID for this chart instance
    const chartId = 'mentionsChart-' + Date.now();
    
    // Wait for DOM and Chart.js to be ready
    function initChart() {
        const canvas = document.getElementById('mentionsChart');
        if (!canvas || !window.Chart) {
            return;
        }
        
        // Check if chart already exists on this canvas
        if (canvas.chartInstance) {
            canvas.chartInstance.destroy();
            canvas.chartInstance = null;
        }
        
        // Set fixed height to prevent growing
        const container = canvas.parentElement;
        container.style.position = 'relative';
        container.style.height = '300px';
        
        // Create chart with fixed configuration
        const chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: @json($mentionsTimeline->pluck('date')),
                datasets: [{
                    label: 'Mentions',
                    data: @json($mentionsTimeline->pluck('count')),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
        
        // Store reference on canvas element
        canvas.chartInstance = chart;
    }
    
    // Initialize only once when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        // DOM already loaded
        setTimeout(initChart, 100);
    }
})();

// Function to unlink social mentions
function unlinkSocialMention(mentionId) {
    if (!confirm('Unlink this social media mention from the campaign?')) {
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/projects/{{ $project->id }}/media-campaigns/{{ $campaign->id }}/social-mentions/${mentionId}/unlink`;
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_token';
    csrfField.value = '{{ csrf_token() }}';
    form.appendChild(csrfField);
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endif
@endsection