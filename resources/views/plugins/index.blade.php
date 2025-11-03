@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Plugin Management</h1>
                    <p class="text-sm text-slate-600 mt-1">Enable or disable application features</p>
                    <p class="text-xs text-slate-400">Page generated: {{ now()->format('Y-m-d H:i:s') }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-500 mr-2" id="last-refresh">Last refresh: just now</span>
                    <button onclick="window.location.reload()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Remote Management Notice --}}
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900">Remote Management Enabled</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        Plugins are managed remotely through the central portal at 
                        <a href="https://adcompro.app/admin/plugin-manager" target="_blank" class="underline font-medium">adcompro.app/admin/plugin-manager</a>. 
                        Plugin activation and deactivation can only be performed from the central management portal.
                    </p>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Total Plugins</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plug text-slate-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Active</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Inactive</p>
                        <p class="text-2xl font-bold text-slate-400">{{ $stats['inactive'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-slate-400"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Core</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['core'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-3">
                <select name="category" onchange="this.form.submit()" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Plugin::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search plugins..." 
                       class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 flex-1">
                <button type="submit" class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                @if(request()->hasAny(['category', 'status', 'search']))
                    <a href="{{ route('plugins.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                @endif
            </form>
        </div>

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

        @if(session('warning'))
            <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <p class="text-sm">{{ session('warning') }}</p>
                </div>
            </div>
        @endif

        {{-- Plugin Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($plugins as $plugin)
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-md transition-all {{ !$plugin->is_active ? 'opacity-75' : '' }}">
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center space-x-3">
                                @if($plugin->icon)
                                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                                        <i class="{{ $plugin->icon }} text-slate-600"></i>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">{{ $plugin->display_name }}</h3>
                                    <div class="flex items-center space-x-2 mt-1">
                                        {!! $plugin->status_badge !!}
                                        {!! $plugin->category_badge !!}
                                        <span class="text-xs text-slate-500">v{{ $plugin->version }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                @if(!$plugin->is_core)
                                    {{-- Remote managed indicator --}}
                                    <div class="flex items-center space-x-2">
                                        @if($plugin->is_active)
                                            <span class="text-xs text-green-600 font-medium px-2 py-1 bg-green-50 rounded-lg flex items-center">
                                                <i class="fas fa-check-circle mr-1"></i> Active
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-500 font-medium px-2 py-1 bg-slate-100 rounded-lg flex items-center">
                                                <i class="fas fa-times-circle mr-1"></i> Inactive
                                            </span>
                                        @endif
                                        <span class="text-xs text-blue-600 font-medium px-2 py-1 bg-blue-50 rounded-lg flex items-center" title="Managed remotely via adcompro.app">
                                            <i class="fas fa-cloud mr-1"></i> Remote
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-purple-600 font-medium px-2 py-1 bg-purple-50 rounded-lg">Core Module</span>
                                @endif
                            </div>
                        </div>
                        
                        <p class="text-sm text-slate-600 mb-3">{{ $plugin->description }}</p>
                        
                        {{-- Dependencies --}}
                        @if($plugin->dependencies && count($plugin->dependencies) > 0)
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Requires:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($plugin->dependencies as $dep)
                                        <span class="px-2 py-0.5 text-xs bg-slate-100 text-slate-600 rounded-full">{{ $dep }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        {{-- Permissions --}}
                        @if($plugin->permissions && count($plugin->permissions) > 0)
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Access:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($plugin->permissions as $perm)
                                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-600 rounded-full">{{ ucfirst(str_replace('_', ' ', $perm)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        {{-- Actions --}}
                        <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                            <div class="text-xs text-slate-500">
                                @if($plugin->author)
                                    By {{ $plugin->author }}
                                @endif
                            </div>
                            <div class="flex items-center space-x-1">
                                <a href="{{ route('plugins.show', $plugin) }}" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                @if(!$plugin->is_core)
                                    <a href="{{ route('plugins.edit', $plugin) }}" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-all">
                                        <i class="fas fa-cog text-sm"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($plugins->hasPages())
            <div class="mt-6">
                {{ $plugins->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh every 30 seconds to show remote changes
    let refreshTimer = 30;
    let lastRefresh = new Date();
    
    function updateRefreshTimer() {
        const now = new Date();
        const diff = Math.floor((now - lastRefresh) / 1000);
        
        let text = 'Last refresh: ';
        if (diff < 60) {
            text += diff === 0 ? 'just now' : diff + ' seconds ago';
        } else if (diff < 3600) {
            text += Math.floor(diff / 60) + ' minutes ago';
        } else {
            text += Math.floor(diff / 3600) + ' hours ago';
        }
        
        document.getElementById('last-refresh').textContent = text;
    }
    
    // Update timer every second
    setInterval(updateRefreshTimer, 1000);
    
    // Auto refresh every 30 seconds
    setInterval(function() {
        window.location.reload();
    }, 30000);
    
    // Also refresh when window becomes visible again (after being in background)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            const now = new Date();
            const diff = Math.floor((now - lastRefresh) / 1000);
            
            // If more than 30 seconds since last refresh, reload
            if (diff > 30) {
                window.location.reload();
            }
        }
    });
</script>
@endpush