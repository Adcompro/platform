@extends('layouts.app')

@section('title', 'Edit Campaign - ' . $campaign->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center space-x-3">
                <a href="{{ route('projects.media-campaigns.show', [$project->id, $campaign->id]) }}" class="text-slate-600 hover:text-slate-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-800">Edit Campaign</h1>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6">
        <form action="{{ route('projects.media-campaigns.update', [$project->id, $campaign->id]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Campaign Details --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Campaign Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <label for="name" class="block text-slate-700 font-medium mb-1" style="">
                            Campaign Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $campaign->name) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               required>
                        @error('name')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label for="description" class="block text-slate-700 font-medium mb-1" style="">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                                  style="">{{ old('description', $campaign->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Press Release Date --}}
                    <div>
                        <label for="press_release_date" class="block text-slate-700 font-medium mb-1" style="">
                            Press Release Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="press_release_date" 
                               name="press_release_date" 
                               value="{{ old('press_release_date', $campaign->press_release_date->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               required>
                        @error('press_release_date')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Campaign Type --}}
                    <div>
                        <label for="campaign_type" class="block text-slate-700 font-medium mb-1" style="">
                            Campaign Type <span class="text-red-500">*</span>
                        </label>
                        <select id="campaign_type" 
                                name="campaign_type"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                                style=""
                                required>
                            <option value="">Select type...</option>
                            <option value="product_launch" {{ old('campaign_type', $campaign->campaign_type) == 'product_launch' ? 'selected' : '' }}>Product Launch</option>
                            <option value="feature_announcement" {{ old('campaign_type', $campaign->campaign_type) == 'feature_announcement' ? 'selected' : '' }}>Feature Announcement</option>
                            <option value="company_news" {{ old('campaign_type', $campaign->campaign_type) == 'company_news' ? 'selected' : '' }}>Company News</option>
                            <option value="event" {{ old('campaign_type', $campaign->campaign_type) == 'event' ? 'selected' : '' }}>Event</option>
                            <option value="partnership" {{ old('campaign_type', $campaign->campaign_type) == 'partnership' ? 'selected' : '' }}>Partnership</option>
                            <option value="other" {{ old('campaign_type', $campaign->campaign_type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('campaign_type')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Parent Campaign --}}
                    @if($parentCampaigns->count() > 0)
                    <div>
                        <label for="parent_id" class="block text-slate-700 font-medium mb-1" style="">
                            Parent Campaign (Optional)
                        </label>
                        <select id="parent_id" 
                                name="parent_id"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                                style="">
                            <option value="">None (Main Campaign)</option>
                            @foreach($parentCampaigns as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $campaign->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-slate-700 font-medium mb-1" style="">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" 
                                name="status"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                                style=""
                                required>
                            <option value="planning" {{ old('status', $campaign->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="active" {{ old('status', $campaign->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status', $campaign->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ old('status', $campaign->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Targeting & Keywords --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Targeting & Keywords</h2>
                
                <div class="space-y-4">
                    {{-- Keywords --}}
                    <div>
                        <label for="keywords" class="block text-slate-700 font-medium mb-1" style="">
                            Keywords to Monitor <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="keywords" 
                               name="keywords" 
                               value="{{ old('keywords', implode(', ', $campaign->keywords ?? [])) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               placeholder="Enter keywords separated by commas (e.g., product name, company, CEO)"
                               required>
                        <p class="mt-1 text-slate-500" style="font-size: calc(var(--theme-font-size) - 2px);">
                            Separate keywords with commas. These will be used to automatically detect related media mentions.
                        </p>
                        @error('keywords')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Target Audience --}}
                    <div>
                        <label for="target_audience" class="block text-slate-700 font-medium mb-1" style="">
                            Target Audience
                        </label>
                        <input type="text" 
                               id="target_audience" 
                               name="target_audience" 
                               value="{{ old('target_audience', $campaign->target_audience) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               placeholder="e.g., Tech professionals, Business leaders, Consumers">
                        @error('target_audience')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Performance Targets --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Performance Targets</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Expected Reach --}}
                    <div>
                        <label for="expected_reach" class="block text-slate-700 font-medium mb-1" style="">
                            Expected Reach
                        </label>
                        <input type="number" 
                               id="expected_reach" 
                               name="expected_reach" 
                               value="{{ old('expected_reach', $campaign->expected_reach) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               min="0"
                               placeholder="Number of people">
                        @error('expected_reach')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actual Reach --}}
                    <div>
                        <label for="actual_reach" class="block text-slate-700 font-medium mb-1" style="">
                            Actual Reach
                        </label>
                        <input type="number" 
                               id="actual_reach" 
                               name="actual_reach" 
                               value="{{ old('actual_reach', $campaign->actual_reach) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               min="0"
                               placeholder="Number of people">
                        @error('actual_reach')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Budget --}}
                    <div>
                        <label for="budget" class="block text-slate-700 font-medium mb-1" style="">
                            Budget (€)
                        </label>
                        <input type="number" 
                               id="budget" 
                               name="budget" 
                               value="{{ old('budget', $campaign->budget) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               min="0"
                               step="0.01"
                               placeholder="0.00">
                        @error('budget')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actual Cost --}}
                    <div>
                        <label for="actual_cost" class="block text-slate-700 font-medium mb-1" style="">
                            Actual Cost (€)
                        </label>
                        <input type="number" 
                               id="actual_cost" 
                               name="actual_cost" 
                               value="{{ old('actual_cost', $campaign->actual_cost) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                               style=""
                               min="0"
                               step="0.01"
                               placeholder="0.00">
                        @error('actual_cost')
                            <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Additional Notes</h2>
                
                <div>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                              style=""
                              placeholder="Any additional notes or context about this campaign...">{{ old('notes', $campaign->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-red-500" style="font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-between">
                <div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        <button type="button" 
                                onclick="if(confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Delete Campaign
                        </button>
                    @endif
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('projects.media-campaigns.show', [$project->id, $campaign->id]) }}" 
                       class="px-6 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-colors">
                        Update Campaign
                    </button>
                </div>
            </div>
        </form>

        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
            <form id="delete-form" action="{{ route('projects.media-campaigns.destroy', [$project->id, $campaign->id]) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
@endsection