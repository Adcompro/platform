@extends('layouts.app')

@section('title', 'Calendar Providers')

@section('content')
{{-- Sticky Header - Exact Copy Theme Settings --}}
<div class="bg-white border-b border-gray-200 sticky z-30" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Calendar Providers</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Connect and manage your calendar integrations</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="syncAllProviders()" id="header-sync-btn"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-sync-alt mr-1.5"></i>
                    Sync All
                </button>
                <a href="{{ route('calendar.index') }}"
                   class="header-btn-secondary"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-calendar mr-1.5"></i>
                    View Calendar
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main Content - Exact Copy Theme Settings --}}
<div style="padding: 1.5rem 2rem;">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.1); border-color: var(--theme-success); color: var(--theme-success); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span style="font-size: var(--theme-font-size);">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border-color: var(--theme-danger); color: var(--theme-danger); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span style="font-size: var(--theme-font-size);">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Provider Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" style="align-items: start;">
        {{-- Microsoft Calendar --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fab fa-microsoft text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Microsoft Calendar</h2>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin: 0;">Outlook 365</p>
                        </div>
                    </div>

                    @if($providers['microsoft']['authenticated'] ?? false)
                        <span class="inline-flex px-2 py-1 rounded-full"
                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                            Connected
                        </span>
                    @else
                        <span class="inline-flex px-2 py-1 rounded-full"
                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);">
                            Not Connected
                        </span>
                    @endif
                </div>
            </div>

            <div style="padding: var(--theme-card-padding);">
                @if($providers['microsoft']['authenticated'] ?? false)
                    <div class="space-y-2 mb-4">
                        @if(!empty($providers['microsoft']['settings']['email']))
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                <strong>Account:</strong> {{ $providers['microsoft']['settings']['email'] }}
                            </p>
                        @endif
                        @if(!empty($providers['microsoft']['settings']['last_sync']))
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                <strong>Last Sync:</strong> {{ \Carbon\Carbon::parse($providers['microsoft']['settings']['last_sync'])->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button onclick="syncProvider('microsoft')"
                                class="flex-1 text-white text-center rounded-lg transition-colors"
                                style="background-color: var(--theme-primary); padding: 0.5rem; font-size: var(--theme-font-size);">
                            Sync Now
                        </button>
                        <form method="POST" action="{{ route('calendar.providers.microsoft.disconnect') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to disconnect Microsoft Calendar?')"
                                    class="text-white rounded-lg transition-colors"
                                    style="background-color: var(--theme-danger); padding: 0.5rem 0.75rem; font-size: var(--theme-font-size);">
                                Disconnect
                            </button>
                        </form>
                    </div>
                @else
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 1rem;">Connect your Microsoft 365 account to sync Outlook calendar events.</p>
                    <a href="{{ route('calendar.providers.microsoft.connect') }}"
                       class="block w-full text-white text-center rounded-lg transition-colors"
                       style="background-color: var(--theme-primary); padding: 0.75rem; font-size: var(--theme-font-size); text-decoration: none;">
                        Connect Microsoft 365
                    </a>
                @endif
            </div>
        </div>

        {{-- Google Calendar --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fab fa-google text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Google Calendar</h2>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin: 0;">Gmail Calendar</p>
                        </div>
                    </div>

                    @if($providers['google']['authenticated'] ?? false)
                        <span class="inline-flex px-2 py-1 rounded-full"
                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                            Connected
                        </span>
                    @else
                        <span class="inline-flex px-2 py-1 rounded-full"
                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);">
                            Not Connected
                        </span>
                    @endif
                </div>
            </div>

            <div style="padding: var(--theme-card-padding);">
                @if($providers['google']['authenticated'] ?? false)
                    <div class="space-y-2 mb-4">
                        @if(!empty($providers['google']['settings']['last_sync']))
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                <strong>Last Sync:</strong> {{ \Carbon\Carbon::parse($providers['google']['settings']['last_sync'])->diffForHumans() }}
                            </p>
                        @endif
                        @if(!empty($providers['google']['settings']['expires_at']))
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                <strong>Token Expires:</strong> {{ \Carbon\Carbon::parse($providers['google']['settings']['expires_at'])->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button onclick="syncProvider('google')"
                                class="flex-1 text-white text-center rounded-lg transition-colors"
                                style="background-color: var(--theme-primary); padding: 0.5rem; font-size: var(--theme-font-size);">
                            Sync Now
                        </button>
                        <form method="POST" action="{{ route('calendar.providers.google.disconnect') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to disconnect Google Calendar?')"
                                    class="text-white rounded-lg transition-colors"
                                    style="background-color: var(--theme-danger); padding: 0.5rem 0.75rem; font-size: var(--theme-font-size);">
                                Disconnect
                            </button>
                        </form>
                    </div>
                @else
                    @php
                        $hasGoogleCredentials = App\Models\Setting::get('google_calendar_client_id') && App\Models\Setting::get('google_calendar_client_secret');
                    @endphp

                    @if($hasGoogleCredentials)
                        <p style="font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 1rem;">Google Calendar API configured. Ready to connect your Google account.</p>
                        <div class="space-y-2">
                            <a href="{{ route('calendar.providers.google.connect') }}"
                               class="block w-full text-white text-center rounded-lg transition-colors"
                               style="background-color: var(--theme-primary); padding: 0.75rem; font-size: var(--theme-font-size); text-decoration: none;">
                                Connect Google Calendar
                            </a>
                            <a href="{{ route('calendar.providers.google.setup') }}"
                               class="block w-full text-center rounded-lg transition-colors"
                               style="background-color: var(--theme-text-muted); color: white; padding: 0.5rem; font-size: var(--theme-font-size); text-decoration: none;">
                                <i class="fas fa-cog mr-1"></i>Update API Settings
                            </a>
                        </div>
                    @else
                        <p style="font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 1rem;">Set up your Google Calendar API credentials to connect your Google account.</p>
                        <div class="rounded-lg p-3 mb-4" style="background-color: rgba(var(--theme-warning-rgb), 0.1); border: 1px solid var(--theme-warning);">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-0.5" style="color: var(--theme-warning);"></i>
                                <div style="color: var(--theme-warning); font-size: calc(var(--theme-font-size) - 2px);">
                                    <p class="font-medium">Setup Required</p>
                                    <p>You need to configure Google Calendar API credentials before you can connect.</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('calendar.providers.google.setup') }}"
                           class="block w-full text-white text-center rounded-lg transition-colors"
                           style="background-color: var(--theme-primary); padding: 0.75rem; font-size: var(--theme-font-size); text-decoration: none;">
                            <i class="fab fa-google mr-2"></i>Setup Google Calendar API
                        </a>
                    @endif
                @endif
            </div>
        </div>

        {{-- Apple iCloud --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-slate-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fab fa-apple text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Apple iCloud</h2>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin: 0;">iCloud Calendar</p>
                        </div>
                    </div>

                    @if($providers['apple']['authenticated'] ?? false)
                        <span class="inline-flex px-2 py-1 rounded-full"
                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                            Connected
                        </span>
                    @else
                        <span class="inline-flex px-2 py-1 rounded-full"
                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);">
                            Not Connected
                        </span>
                    @endif
                </div>
            </div>

            <div style="padding: var(--theme-card-padding);">
                @if($providers['apple']['authenticated'] ?? false)
                    <div class="space-y-2 mb-4">
                        @if(!empty($providers['apple']['settings']['username']))
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                <strong>Apple ID:</strong> {{ $providers['apple']['settings']['username'] }}
                            </p>
                        @endif
                        @if(!empty($providers['apple']['settings']['connected_at']))
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                <strong>Connected:</strong> {{ \Carbon\Carbon::parse($providers['apple']['settings']['connected_at'])->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button onclick="syncProvider('apple')"
                                class="flex-1 text-white text-center rounded-lg transition-colors"
                                style="background-color: var(--theme-primary); padding: 0.5rem; font-size: var(--theme-font-size);">
                            Sync Now
                        </button>
                        <form method="POST" action="{{ route('calendar.providers.apple.disconnect') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to disconnect Apple iCloud Calendar?')"
                                    class="text-white rounded-lg transition-colors"
                                    style="background-color: var(--theme-danger); padding: 0.5rem 0.75rem; font-size: var(--theme-font-size);">
                                Disconnect
                            </button>
                        </form>
                    </div>
                @else
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 1rem;">Connect your Apple iCloud account using CalDAV to sync calendar events.</p>
                    <a href="{{ route('calendar.providers.apple.setup') }}"
                       class="block w-full text-white text-center rounded-lg transition-colors"
                       style="background-color: var(--theme-primary); padding: 0.75rem; font-size: var(--theme-font-size); text-decoration: none;">
                        <i class="fab fa-apple mr-2"></i>Connect Apple iCloud
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for sync functionality --}}
@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-success').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();

    // Header sync button
    const syncBtn = document.getElementById('header-sync-btn');
    if (syncBtn) {
        syncBtn.style.backgroundColor = primaryColor;
        syncBtn.style.color = 'white';
        syncBtn.style.border = 'none';
        syncBtn.style.borderRadius = 'var(--theme-border-radius)';
    }
}

// Sync all providers function
function syncAllProviders() {
    const button = document.getElementById('header-sync-btn');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i>Syncing...';

    fetch('{{ route("calendar.providers.sync-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('success', data.message || 'Calendars synced successfully');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Sync failed');
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        showNotification('error', 'Sync failed due to network error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Sync specific provider
function syncProvider(provider) {
    const button = event.target;
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';

    fetch(`{{ url('calendar/providers/sync') }}/${provider}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message || `${provider} synced successfully`);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('error', data.message || `${provider} sync failed`);
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        showNotification('error', 'Sync failed due to network error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Show notification function
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg border ${
        type === 'success'
            ? 'bg-green-50 border-green-200 text-green-700'
            : 'bg-red-50 border-red-200 text-red-700'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush

@endsection