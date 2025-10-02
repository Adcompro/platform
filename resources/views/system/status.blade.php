@extends('layouts.app')

@section('title', 'System Status')

@push('styles')
<style>
    .header-btn {
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .status-card {
        transition: all 0.2s ease;
        border-radius: var(--theme-border-radius);
        box-shadow: var(--theme-card-shadow);
    }

    .status-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-healthy { background-color: #10b981; }
    .status-warning { background-color: #f59e0b; }
    .status-critical { background-color: #ef4444; }

    .metric-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--theme-text);
    }

    .metric-label {
        font-size: var(--theme-font-size);
        color: var(--theme-text-muted);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900 flex items-center" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">
                        <span class="status-indicator status-{{ $systemStatus['overall'] }} mr-2"></span>
                        System Status
                    </h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">
                        Real-time monitoring of all system components
                    </p>
                </div>
                <div class="flex space-x-2">
                    <button onclick="refreshStatus()" id="header-refresh-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-sync-alt mr-1.5"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content - Exact Copy Theme Settings --}}
    <div style="padding: 1.5rem 2rem;">
        {{-- Overall Status Banner --}}
        <div class="mb-6 p-4 rounded-lg border {{ $systemStatus['overall'] === 'healthy' ? 'bg-green-50 border-green-200' : ($systemStatus['overall'] === 'warning' ? 'bg-yellow-50 border-yellow-200' : 'bg-red-50 border-red-200') }}">
            <div class="flex items-center">
                <span class="status-indicator status-{{ $systemStatus['overall'] }} mr-3"></span>
                <div>
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">
                        System Status:
                        @if($systemStatus['overall'] === 'healthy')
                            <span class="text-green-800">All Systems Operational</span>
                        @elseif($systemStatus['overall'] === 'warning')
                            <span class="text-yellow-800">Some Issues Detected</span>
                        @else
                            <span class="text-red-800">Critical Issues Detected</span>
                        @endif
                    </h2>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin: 0.25rem 0 0 0;">
                        Last checked: {{ $systemStatus['last_check']->format('d-m-Y H:i:s') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- System Components Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($systemStatus['sections'] as $sectionKey => $section)
            <div class="status-card bg-white border" style="border-color: rgba(203, 213, 225, 0.6); padding: var(--theme-card-padding);">
                {{-- Section Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <i class="{{ $section['icon'] }} mr-2" style="color: var(--theme-primary);"></i>
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">
                            {{ $section['title'] }}
                        </h3>
                    </div>
                    <span class="status-indicator status-{{ $section['status'] }}"></span>
                </div>

                {{-- Status Checks --}}
                <div class="space-y-2 mb-4">
                    @foreach($section['checks'] as $check)
                    <div class="flex items-center justify-between">
                        <span style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $check['name'] }}</span>
                        <div class="flex items-center">
                            <span class="status-indicator status-{{ $check['status'] }} mr-2"></span>
                            <span style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                {{ Str::limit($check['message'], 30) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Metrics --}}
                @if(!empty($section['metrics']))
                <div class="border-t pt-3" style="border-color: rgba(203, 213, 225, 0.3);">
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($section['metrics'] as $metric)
                        <div class="text-center">
                            <div class="metric-value">{{ $metric['value'] }}</div>
                            <div class="metric-label">{{ $metric['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Detailed Health Information --}}
        <div class="mt-8 bg-white rounded-lg border" style="border-color: rgba(203, 213, 225, 0.6); padding: var(--theme-card-padding);">
            <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0 0 1rem 0;">
                Detailed System Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0 0 0.5rem 0;">
                        Environment
                    </h4>
                    <ul class="space-y-1" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        <li>Laravel: {{ app()->version() }}</li>
                        <li>PHP: {{ PHP_VERSION }}</li>
                        <li>Environment: {{ app()->environment() }}</li>
                        <li>Debug: {{ config('app.debug') ? 'Enabled' : 'Disabled' }}</li>
                    </ul>
                </div>

                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0 0 0.5rem 0;">
                        Server
                    </h4>
                    <ul class="space-y-1" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        <li>OS: {{ PHP_OS }}</li>
                        <li>Server: {{ $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' }}</li>
                        <li>Memory Limit: {{ ini_get('memory_limit') }}</li>
                        <li>Max Execution: {{ ini_get('max_execution_time') }}s</li>
                    </ul>
                </div>

                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0 0 0.5rem 0;">
                        Database
                    </h4>
                    <ul class="space-y-1" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        <li>Driver: {{ config('database.default') }}</li>
                        <li>Host: {{ config('database.connections.mysql.host') }}</li>
                        <li>Database: {{ config('database.connections.mysql.database') }}</li>
                        <li>Timezone: {{ config('app.timezone') }}</li>
                    </ul>
                </div>

                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0 0 0.5rem 0;">
                        Cache & Storage
                    </h4>
                    <ul class="space-y-1" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        <li>Cache: {{ config('cache.default') }}</li>
                        <li>Session: {{ config('session.driver') }}</li>
                        <li>Storage: {{ config('filesystems.default') }}</li>
                        <li>Queue: {{ config('queue.default') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header buttons
    const refreshBtn = document.getElementById('header-refresh-btn');

    if (refreshBtn) {
        refreshBtn.style.backgroundColor = primaryColor;
        refreshBtn.style.color = 'white';
        refreshBtn.style.border = 'none';
        refreshBtn.style.borderRadius = 'var(--theme-border-radius)';
    }
}

// Refresh system status
function refreshStatus() {
    const refreshBtn = document.getElementById('header-refresh-btn');
    const icon = refreshBtn.querySelector('i');

    // Show loading state
    refreshBtn.disabled = true;
    icon.classList.add('fa-spin');

    // Reload page after a short delay to show loading state
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

// Auto-refresh every 5 minutes
setInterval(() => {
    window.location.reload();
}, 300000); // 5 minutes

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush
@endsection