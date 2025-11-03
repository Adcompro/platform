@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('plugins.index') }}" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 flex items-center">
                            @if($plugin->icon)
                                <i class="{{ $plugin->icon }} mr-3 text-slate-500"></i>
                            @endif
                            {{ $plugin->display_name }}
                        </h1>
                        <div class="flex items-center space-x-2 mt-1">
                            {!! $plugin->status_badge !!}
                            {!! $plugin->category_badge !!}
                            <span class="text-sm text-slate-500">v{{ $plugin->version }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if(!$plugin->is_core)
                        <a href="{{ route('plugins.edit', $plugin) }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-cog mr-1"></i> Configure
                        </a>
                        <form action="{{ route('plugins.toggle', $plugin) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 {{ $plugin->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white text-sm font-medium rounded-lg transition-all">
                                <i class="fas fa-{{ $plugin->is_active ? 'power-off' : 'play' }} mr-1"></i>
                                {{ $plugin->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1.5 bg-purple-100 text-purple-700 text-sm font-medium rounded-lg">
                            <i class="fas fa-shield-alt mr-1"></i> Core Module
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Description --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-3">Description</h2>
                    <p class="text-slate-600">{{ $plugin->description }}</p>
                    
                    @if($plugin->author || $plugin->url)
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            @if($plugin->author)
                                <p class="text-sm text-slate-500">Author: <span class="font-medium text-slate-700">{{ $plugin->author }}</span></p>
                            @endif
                            @if($plugin->url)
                                <p class="text-sm text-slate-500 mt-1">
                                    Documentation: <a href="{{ $plugin->url }}" target="_blank" class="font-medium text-blue-600 hover:text-blue-700">
                                        {{ $plugin->url }} <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Dependencies --}}
                @if($dependencies->isNotEmpty() || $dependentPlugins->isNotEmpty())
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Dependencies</h2>
                        
                        @if($dependencies->isNotEmpty())
                            <div class="mb-4">
                                <p class="text-sm text-slate-500 mb-2">This plugin requires:</p>
                                <div class="space-y-2">
                                    @foreach($dependencies as $dep)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                            <div class="flex items-center space-x-2">
                                                @if($dep->icon)
                                                    <i class="{{ $dep->icon }} text-slate-500"></i>
                                                @endif
                                                <span class="font-medium text-slate-700">{{ $dep->display_name }}</span>
                                            </div>
                                            {!! $dep->status_badge !!}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($dependentPlugins->isNotEmpty())
                            <div>
                                <p class="text-sm text-slate-500 mb-2">Required by:</p>
                                <div class="space-y-2">
                                    @foreach($dependentPlugins as $dep)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                            <div class="flex items-center space-x-2">
                                                @if($dep->icon)
                                                    <i class="{{ $dep->icon }} text-slate-500"></i>
                                                @endif
                                                <span class="font-medium text-slate-700">{{ $dep->display_name }}</span>
                                            </div>
                                            {!! $dep->status_badge !!}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Recent Activity --}}
                @if($activities->isNotEmpty())
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Recent Activity</h2>
                        <div class="space-y-3">
                            @foreach($activities as $activity)
                                <div class="flex items-start space-x-3 pb-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                                    <div class="mt-1">
                                        {!! $activity->action_badge !!}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-slate-700">
                                            <span class="font-medium">{{ $activity->user->name }}</span>
                                            {{ $activity->description }}
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $activity->created_at->diffForHumans() }}
                                            @if($activity->ip_address)
                                                • IP: {{ $activity->ip_address }}
                                            @endif
                                        </p>
                                        @if($activity->notes)
                                            <p class="text-sm text-slate-600 mt-2 p-2 bg-slate-50 rounded">{{ $activity->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Technical Details --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-3">Technical Details</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-slate-500 uppercase tracking-wider">Plugin Name</dt>
                            <dd class="font-mono text-sm text-slate-700">{{ $plugin->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 uppercase tracking-wider">Version</dt>
                            <dd class="text-sm text-slate-700">{{ $plugin->version }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 uppercase tracking-wider">Category</dt>
                            <dd class="text-sm text-slate-700">{{ \App\Models\Plugin::CATEGORIES[$plugin->category] ?? $plugin->category }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 uppercase tracking-wider">Sort Order</dt>
                            <dd class="text-sm text-slate-700">{{ $plugin->sort_order }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Permissions --}}
                @if($plugin->permissions)
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Access Control</h2>
                        <p class="text-sm text-slate-500 mb-3">This plugin is accessible to:</p>
                        <div class="space-y-2">
                            @foreach($plugin->permissions as $permission)
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-user-shield text-slate-400"></i>
                                    <span class="text-sm text-slate-700">{{ ucfirst(str_replace('_', ' ', $permission)) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Access Control</h2>
                        <p class="text-sm text-slate-500">No restrictions - accessible to all users</p>
                    </div>
                @endif

                {{-- Routes --}}
                @if($plugin->routes && count($plugin->routes) > 0)
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Protected Routes</h2>
                        <div class="space-y-1">
                            @foreach($plugin->routes as $route)
                                <div class="font-mono text-xs text-slate-600 p-2 bg-slate-50 rounded">{{ $route }}.*</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Company Settings --}}
                @if($companySettings->isNotEmpty())
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-3">Company Overrides</h2>
                        <div class="space-y-2">
                            @foreach($companySettings as $setting)
                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                    <span class="text-sm text-slate-700">{{ $setting->name }}</span>
                                    @if($setting->is_enabled)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Enabled</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">Disabled</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection