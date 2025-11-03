@extends('layouts.app')

@section('title', 'Teamleader Focus Integration')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Teamleader Focus Integration</h1>
                        <p class="text-sm text-slate-600">Import data from Teamleader Focus to Progress</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Help Button tijdelijk uitgeschakeld - functie nog niet beschikbaar
                    <button onclick="openHelpModal()" class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-all duration-200" title="Import Guide">
                        <i class="fas fa-question-circle mr-2"></i>
                        Help
                    </button>
                    --}}

                    @if($isAuthorized)
                        <form action="{{ route('teamleader.disconnect') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition-all duration-200 border border-red-200">
                                <i class="fas fa-unlink mr-2"></i>
                                Disconnect
                            </button>
                        </form>
                    @else
                        <a href="{{ route('teamleader.authorize') }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-sm">
                            <i class="fas fa-link mr-2"></i>
                            Connect to Teamleader
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Connection Status Card --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200/50">
                <h2 class="text-lg font-medium text-slate-900">Connection Status</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @if($isAuthorized)
                            <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Connected to Teamleader Focus</p>
                                @if($teamleaderUser)
                                    <p class="text-xs text-slate-600 mt-1">
                                        Logged in as: <strong>{{ $teamleaderUser['first_name'] ?? '' }} {{ $teamleaderUser['last_name'] ?? '' }}</strong>
                                        ({{ $teamleaderUser['email'] ?? 'No email' }})
                                    </p>
                                @endif
                                <p class="text-xs text-slate-500 mt-1">Token expires: {{ \Carbon\Carbon::parse($tokenExpiresAt)->format('d-m-Y H:i') }}</p>
                            </div>
                        @else
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">Not connected</p>
                                <p class="text-xs text-slate-600 mt-1">Click "Connect to Teamleader" to authorize access</p>
                            </div>
                        @endif
                    </div>
                    @if($isAuthorized)
                        <button onclick="testConnection()" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-plug mr-2"></i>
                            Test Connection
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if($isAuthorized)
            {{-- Global Sync All Data Card --}}
            <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl overflow-hidden mb-6 shadow-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <i class="fas fa-sync-alt text-white text-2xl"></i>
                            </div>
                            <div class="text-white">
                                <h3 class="text-xl font-bold mb-1">Global Data Synchronization</h3>
                                <p class="text-sm text-emerald-50">Sync ALL data from Teamleader Focus to Progress database</p>
                                <p class="text-xs text-emerald-100 mt-2">
                                    <i class="fas fa-database mr-1"></i>
                                    This will sync: <strong>Companies, Contacts & Projects</strong>
                                </p>
                                <p class="text-xs text-emerald-200 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Note: Time Tracking will be uploaded separately
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('teamleader.sync.all') }}" method="POST" onsubmit="return confirmGlobalSync()">
                            @csrf
                            <button type="submit"
                                    id="global-sync-btn"
                                    class="px-6 py-3 bg-white text-emerald-600 text-base font-bold rounded-lg hover:bg-emerald-50 transition-all shadow-md hover:shadow-lg">
                                <i class="fas fa-cloud-download-alt mr-2"></i>
                                Sync All Data Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ✅ REMOVED: Reverse Sync - No longer needed with smart filtering in cascade import! --}}

            {{-- Sync Progress Bar (hidden by default, shown during sync) --}}
            <div id="sync-progress-container" class="bg-white/80 backdrop-blur-sm border border-indigo-200 rounded-xl overflow-hidden mb-6 shadow-sm" style="display: none;">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <i class="fas fa-sync-alt fa-spin text-indigo-600"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-slate-900">Sync in Progress</h4>
                                <p id="sync-current-item" class="text-sm text-slate-600">Initializing...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-indigo-600" id="sync-progress-percentage">0%</div>
                            <div class="text-xs text-slate-500" id="sync-progress-count">0 / 0</div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div id="sync-progress-bar" class="bg-gradient-to-r from-indigo-500 to-purple-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>

                    {{-- Statistics --}}
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-xl font-bold text-green-600" id="sync-successful">0</div>
                            <div class="text-xs text-green-700">Successful</div>
                        </div>
                        <div class="text-center p-3 bg-red-50 rounded-lg">
                            <div class="text-xl font-bold text-red-600" id="sync-failed">0</div>
                            <div class="text-xs text-red-700">Failed</div>
                        </div>
                        <div class="text-center p-3 bg-slate-50 rounded-lg">
                            <div class="text-xs text-slate-600" id="sync-elapsed-time">Elapsed: 0s</div>
                            <div class="text-xs text-slate-500" id="sync-eta">ETA: calculating...</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🎯 ONE-CLICK COMPLETE IMPORT - Featured Card --}}
            <div class="bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 rounded-xl overflow-hidden mb-6 shadow-xl">
                <div class="p-8">
                    <div class="flex items-start justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fas fa-magic text-white text-3xl"></i>
                                </div>
                                <div class="text-white">
                                    <h3 class="text-2xl font-bold mb-1">🚀 One-Click Complete Import</h3>
                                    <p class="text-sm text-blue-100">Import everything with one selection!</p>
                                </div>
                            </div>
                            <div class="text-white space-y-2 mb-6">
                                <p class="text-base">Select companies and automatically import:</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg p-3">
                                        <i class="fas fa-users text-blue-200"></i>
                                        <span class="text-sm font-medium">All Contacts</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg p-3">
                                        <i class="fas fa-project-diagram text-green-200"></i>
                                        <span class="text-sm font-medium">All Projects</span>
                                    </div>
                                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg p-3">
                                        <i class="fas fa-clock text-purple-200"></i>
                                        <span class="text-sm font-medium">Time Entries</span>
                                    </div>
                                </div>
                                <p class="text-xs text-blue-100 flex items-center gap-2 mt-3">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Includes positions, budgets, and complete project structures!</span>
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('teamleader.select.companies') }}"
                           class="px-8 py-4 bg-white text-indigo-600 text-lg font-bold rounded-xl hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl flex items-center gap-3">
                            <i class="fas fa-rocket"></i>
                            <span>Start Import</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Import Cards - Simplified --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Users Import --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                                <i class="fas fa-user-plus text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Team Members (Users)</h3>
                                <p class="text-xs text-slate-500">Import internal team members</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 mb-4">Import Teamleader contacts as team users (no emails sent, auto-verified)</p>
                        <a href="{{ route('teamleader.select.users') }}" class="block w-full px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all text-center">
                            <i class="fas fa-check-square mr-2"></i>
                            Select & Import Users
                        </a>
                    </div>
                </div>

                {{-- Data Preview --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center">
                                <i class="fas fa-database text-slate-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Preview Synced Data</h3>
                                <p class="text-xs text-slate-500">View cached data</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 mb-4">Preview data from the last global sync before importing</p>
                        <div class="grid grid-cols-3 gap-2">
                            <button onclick="previewData('companies')" class="px-3 py-2 bg-slate-100 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-200 transition-all">
                                <i class="fas fa-building mr-1"></i>
                                Companies
                            </button>
                            <button onclick="previewData('contacts')" class="px-3 py-2 bg-slate-100 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-200 transition-all">
                                <i class="fas fa-users mr-1"></i>
                                Contacts
                            </button>
                            <button onclick="previewData('projects')" class="px-3 py-2 bg-slate-100 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-200 transition-all">
                                <i class="fas fa-project-diagram mr-1"></i>
                                Projects
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Configuration Notice --}}
            <div class="bg-orange-50/50 backdrop-blur-sm border border-orange-200/60 rounded-xl overflow-hidden mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-orange-900 mb-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Important: Update Redirect URI
                    </h3>
                    <p class="text-sm text-orange-800 mb-3">
                        The Teamleader integration has been moved to Settings. You need to update the redirect URI in your Teamleader Marketplace app:
                    </p>
                    <div class="bg-white rounded-lg p-3 border border-orange-200">
                        <code class="text-sm text-orange-900 font-mono">https://progress.adcompro.app/settings/teamleader/oauth/callback</code>
                    </div>
                    <p class="text-xs text-orange-700 mt-3">
                        <i class="fas fa-link mr-1"></i>
                        Update this in: <a href="https://marketplace.focus.teamleader.eu/build" target="_blank" class="underline hover:text-orange-900">Teamleader Marketplace Build Page</a>
                    </p>
                </div>
            </div>
        @else
            {{-- Not Connected State --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden text-center py-12">
                <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <i class="fas fa-link text-slate-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">Connect to Teamleader Focus</h3>
                <p class="text-slate-600 mb-6">Authorize access to start importing your data</p>
                <a href="{{ route('teamleader.authorize') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all shadow-sm">
                    <i class="fas fa-link mr-2"></i>
                    Connect Now
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Help Modal wordt ge-include onderaan via partials --}}
@push('scripts')
<script>
function confirmCombinedImport() {
    return confirm(
        '⚠️ Combined Import Warning\n\n' +
        'This will import ALL companies from Teamleader as customers, ' +
        'then automatically import all their contacts.\n\n' +
        'This operation may take 10-30 minutes depending on the amount of data.\n\n' +
        'Do you want to continue?'
    );
}

function confirmGlobalSync() {
    const confirmed = confirm(
        '🌍 GLOBAL DATA SYNCHRONIZATION\n\n' +
        'This will sync ALL data from Teamleader Focus to Progress database:\n\n' +
        '✓ Companies (all companies)\n' +
        '✓ Contacts (all contacts)\n' +
        '✓ Projects (all projects)\n\n' +
        'ℹ️ NOTE: Milestones & Tasks will be created during project import\n' +
        'ℹ️ NOTE: Time Tracking will be uploaded separately\n\n' +
        'This operation may take 10-20 minutes depending on data volume.\n\n' +
        '⚠️ WARNING: This will process thousands of records!\n\n' +
        'Do you want to continue?'
    );

    if (confirmed) {
        const btn = document.getElementById('global-sync-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing... Please wait';
    }

    return confirmed;
}

function confirmReverseSyncContactCompanies() {
    const confirmed = confirm(
        '🔄 CONTACT-COMPANY RELATIONSHIPS SYNC\n\n' +
        'This will build contact-company relationships by:\n\n' +
        '1. Processing ALL companies in the database (no limit)\n' +
        '2. For each company, fetching its contacts from Teamleader\n' +
        '3. Updating the "companies" field in teamleader_contacts\n\n' +
        'This is required for accurate contact filtering per customer.\n\n' +
        '⏱️ Estimated time: 30-60 minutes (~1,900 companies)\n\n' +
        '📝 NOTE: Run this AFTER the global sync!\n' +
        '♾️ This will process ALL companies, regardless of count!\n\n' +
        'Do you want to continue?'
    );

    if (confirmed) {
        const btn = document.getElementById('reverse-sync-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing... Please wait';
    }

    return confirmed;
}

function testConnection() {
    fetch('{{ route('teamleader.test-connection') }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Connection successful!\n\nUser: ' + data.user.first_name + ' ' + data.user.last_name);
            } else {
                alert('❌ Connection failed:\n' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error testing connection: ' + error);
        });
}

function previewData(type) {
    const modal = document.getElementById('previewModal');
    const title = document.getElementById('previewTitle');
    const content = document.getElementById('previewContent');

    title.textContent = 'Preview ' + type.charAt(0).toUpperCase() + type.slice(1);
    modal.classList.remove('hidden');

    content.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-3xl text-slate-400"></i>
            <p class="text-slate-600 mt-2">Loading ${type}...</p>
        </div>
    `;

    fetch(`/settings/teamleader/preview/${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="space-y-2">';
                if (data.data.data && data.data.data.length > 0) {
                    data.data.data.forEach(item => {
                        html += `
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <pre class="text-xs overflow-x-auto">${JSON.stringify(item, null, 2)}</pre>
                            </div>
                        `;
                    });
                    html += `<p class="text-sm text-slate-600 mt-4">Showing ${data.data.data.length} of many records...</p>`;
                } else {
                    html += '<p class="text-slate-600 text-center py-8">No data found</p>';
                }
                html += '</div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-400"></i>
                        <p class="text-red-600 mt-2">${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-400"></i>
                    <p class="text-red-600 mt-2">Error: ${error}</p>
                </div>
            `;
        });
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
}

// ========================================
// SYNC PROGRESS POLLING
// ========================================
let syncPollingInterval = null;
let syncStartTime = null;

function startSyncPolling() {
    // Toon progress container
    document.getElementById('sync-progress-container').style.display = 'block';
    syncStartTime = Date.now();

    // Poll elke 2 seconden
    syncPollingInterval = setInterval(checkSyncStatus, 2000);

    // Check direct bij start
    checkSyncStatus();
}

function checkSyncStatus() {
    fetch('{{ route('teamleader.sync.status') }}?job_type=contact_companies')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'not_found') {
                // Geen actieve sync
                stopSyncPolling();
                return;
            }

            // Update progress bar
            const percentage = data.progress_percentage || 0;
            document.getElementById('sync-progress-bar').style.width = percentage + '%';
            document.getElementById('sync-progress-percentage').textContent = percentage + '%';
            document.getElementById('sync-progress-count').textContent =
                `${data.processed_items || 0} / ${data.total_items || 0}`;

            // Update current item
            if (data.current_item) {
                document.getElementById('sync-current-item').textContent = data.current_item;
            }

            // Update statistics
            document.getElementById('sync-successful').textContent = data.successful_items || 0;
            document.getElementById('sync-failed').textContent = data.failed_items || 0;

            // Update elapsed time
            if (syncStartTime) {
                const elapsed = Math.floor((Date.now() - syncStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                document.getElementById('sync-elapsed-time').textContent =
                    `Elapsed: ${minutes}m ${seconds}s`;

                // Calculate ETA
                if (data.processed_items > 0 && data.total_items > 0) {
                    const itemsLeft = data.total_items - data.processed_items;
                    const secondsPerItem = elapsed / data.processed_items;
                    const etaSeconds = Math.floor(itemsLeft * secondsPerItem);
                    const etaMinutes = Math.floor(etaSeconds / 60);
                    document.getElementById('sync-eta').textContent =
                        `ETA: ${etaMinutes}m ${etaSeconds % 60}s`;
                }
            }

            // Check of sync compleet is
            if (data.status === 'completed') {
                stopSyncPolling();
                document.getElementById('sync-current-item').textContent =
                    '✅ Sync completed successfully!';

                // Auto-hide na 10 seconden
                setTimeout(() => {
                    document.getElementById('sync-progress-container').style.display = 'none';
                }, 10000);

                // Refresh pagina om success message te tonen
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else if (data.status === 'failed') {
                stopSyncPolling();
                document.getElementById('sync-current-item').textContent =
                    '❌ Sync failed: ' + (data.error_message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Error checking sync status:', error);
        });
}

function stopSyncPolling() {
    if (syncPollingInterval) {
        clearInterval(syncPollingInterval);
        syncPollingInterval = null;
    }
}

// Start polling bij page load als er een actieve sync is
document.addEventListener('DOMContentLoaded', function() {
    // Check of er een actieve sync is
    fetch('{{ route('teamleader.sync.status') }}?job_type=contact_companies')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'running') {
                startSyncPolling();
            }
        })
        .catch(error => {
            console.error('Error checking sync status:', error);
            // Silently fail - not critical for page functionality
        });
});

// Start polling wanneer sync button wordt geklikt
const reverseSyncForm = document.querySelector('form[action="{{ route('teamleader.sync.contact-companies') }}"]');
if (reverseSyncForm) {
    reverseSyncForm.addEventListener('submit', function(e) {
        // Na bevestiging, start polling over 1 seconde
        setTimeout(() => {
            startSyncPolling();
        }, 1000);
    });
}
</script>
@endpush

{{-- Help Modal tijdelijk uitgeschakeld - wordt later toegevoegd --}}

@endsection
