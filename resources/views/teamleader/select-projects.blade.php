@extends('layouts.app')

@section('title', 'Import Projects from Teamleader')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Import Projects from Teamleader</h1>
                        <p class="text-sm text-slate-600">Customer: <strong>{{ $customer->name }}</strong></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Sync Banner --}}
        @if($stats['needs_sync'])
        <div class="mb-6 bg-blue-50/50 backdrop-blur-sm border border-blue-200/60 rounded-xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                    </div>
                    <div class="text-sm text-blue-900">
                        <p class="font-semibold mb-1">No Projects Synced Yet</p>
                        <p class="text-blue-800">
                            @if($stats['total_projects'] === 0)
                                No projects have been synced for this customer yet. Use the <strong>Global Sync</strong> button in the Teamleader dashboard to sync all data from Teamleader.
                            @else
                                Projects are available but may be outdated. Use the <strong>Global Sync</strong> in the Teamleader dashboard to refresh all data.
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('teamleader.index') }}"
                   class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all shadow-sm whitespace-nowrap">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Go to Global Sync
                </a>
            </div>
        </div>
        @elseif($stats['last_sync'])
        <div class="mb-6 bg-green-50/50 backdrop-blur-sm border border-green-200/60 rounded-xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                    <div class="text-sm text-green-900">
                        <p class="font-semibold mb-1">Projects Synced from Database</p>
                        <p class="text-green-800">
                            Last synced {{ $stats['last_sync']->diffForHumans() }}. Showing <strong>{{ $stats['total_projects'] }}</strong> projects from local database.
                            <br><small class="text-green-700">To refresh data, use the Global Sync in the Teamleader dashboard.</small>
                        </p>
                    </div>
                </div>
                <a href="{{ route('teamleader.index') }}"
                   class="px-4 py-2 bg-green-100 text-green-700 text-sm font-medium rounded-lg hover:bg-green-200 transition-all whitespace-nowrap">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Go to Teamleader Dashboard
                </a>
            </div>
        </div>
        @endif

        {{-- Filters --}}
        @if($stats['total_projects'] > 0)
        <div class="mb-6 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
            <form method="GET" action="{{ route('teamleader.select.projects') }}" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by title or description..."
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="min-w-[200px]">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Filter</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses ({{ $stats['total_all_statuses'] }})</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active ({{ $stats['by_status']['active'] }})</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold ({{ $stats['by_status']['on_hold'] }})</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done ({{ $stats['by_status']['done'] }})</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled ({{ $stats['by_status']['cancelled'] }})</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-all">
                    <i class="fas fa-filter mr-2"></i>
                    Apply Filters
                </button>

                @if(request('search') || request('status'))
                <a href="{{ route('teamleader.select.projects', ['customer_id' => $customer->id]) }}"
                   class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
                @endif
            </form>
        </div>
        @endif

        {{-- Status Breakdown Info --}}
        @if($stats['total_all_statuses'] > 0)
        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/60 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Projects by Status (Total: {{ $stats['total_all_statuses'] }})</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                        <div class="bg-white/60 rounded-lg px-3 py-2">
                            <div class="font-semibold text-green-700">{{ $stats['by_status']['active'] }}</div>
                            <div class="text-xs text-slate-600">Active</div>
                        </div>
                        <div class="bg-white/60 rounded-lg px-3 py-2">
                            <div class="font-semibold text-blue-700">{{ $stats['by_status']['done'] }}</div>
                            <div class="text-xs text-slate-600">Done</div>
                        </div>
                        <div class="bg-white/60 rounded-lg px-3 py-2">
                            <div class="font-semibold text-yellow-700">{{ $stats['by_status']['on_hold'] }}</div>
                            <div class="text-xs text-slate-600">On Hold</div>
                        </div>
                        <div class="bg-white/60 rounded-lg px-3 py-2">
                            <div class="font-semibold text-red-700">{{ $stats['by_status']['cancelled'] }}</div>
                            <div class="text-xs text-slate-600">Cancelled</div>
                        </div>
                    </div>
                    <p class="text-xs text-blue-800 mt-2">
                        <i class="fas fa-lightbulb mr-1"></i>
                        <strong>Tip:</strong> You can import projects with ANY status. Use the filter above to view specific statuses, or keep "All Statuses" selected to see everything.
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-2xl font-bold text-slate-900">{{ $stats['total_projects'] }}</div>
                <div class="text-sm text-slate-600">Total Projects</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-blue-200/60 rounded-xl p-4">
                <div class="text-2xl font-bold text-blue-900">{{ $stats['already_imported'] }}</div>
                <div class="text-sm text-blue-600">Already Imported</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-green-200/60 rounded-xl p-4">
                <div class="text-2xl font-bold text-green-900">{{ $stats['available_to_import'] }}</div>
                <div class="text-sm text-green-600">Available to Import</div>
            </div>
        </div>

        {{-- Import Form --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200/50 bg-slate-50/50">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-slate-900">Select Projects to Import</h2>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="selectAll()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            Select All Available
                        </button>
                        <button type="button" onclick="clearAll()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>

            <form action="{{ route('teamleader.import.projects') }}" method="POST" id="import-form">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="p-6">
                    @if($allProjects->count() === 0)
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-slate-600 mb-4">
                                @if($stats['needs_sync'])
                                    No projects have been synced yet. Click "Sync from Teamleader" above to fetch projects.
                                @else
                                    No projects match your filters.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($allProjects as $project)
                            <div class="project-row p-4 border border-slate-200/60 rounded-lg hover:bg-slate-50/50 transition-all {{ $project->is_imported ? 'opacity-60' : '' }}"
                                 data-project-id="{{ $project->teamleader_id }}"
                                 data-is-imported="{{ $project->is_imported ? '1' : '0' }}">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 mt-1">
                                        @if($project->is_imported)
                                            <div class="w-5 h-5 rounded bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-check text-blue-600 text-xs"></i>
                                            </div>
                                        @else
                                            <input type="checkbox"
                                                   name="project_ids[]"
                                                   value="{{ $project->teamleader_id }}"
                                                   class="project-checkbox w-5 h-5 rounded border-slate-300 text-green-600 focus:ring-green-500 cursor-pointer"
                                                   onchange="updateSelectedCount()">
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-base font-semibold text-slate-900">{{ $project->title }}</h3>

                                            {{-- Status Badge --}}
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $project->status_badge_color }}">
                                                {{ ucfirst($project->status) }}
                                            </span>

                                            @if($project->is_imported)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Already Imported
                                                </span>
                                            @endif
                                        </div>

                                        @if($project->description)
                                            <p class="text-sm text-slate-600 mb-2">{{ Str::limit($project->description, 150) }}</p>
                                        @endif

                                        <div class="flex items-center gap-4 text-xs text-slate-500">
                                            <span>
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                Start: {{ $project->starts_on ? $project->starts_on->format('d-m-Y') : 'N/A' }}
                                            </span>
                                            <span>
                                                <i class="fas fa-calendar-check mr-1"></i>
                                                End: {{ $project->due_on ? $project->due_on->format('d-m-Y') : 'N/A' }}
                                            </span>
                                            @if($project->budget_amount && $project->budget_amount > 0)
                                            <span class="text-slate-400">•</span>
                                            <span class="font-semibold text-green-700">
                                                <i class="fas fa-euro-sign mr-1"></i>
                                                Budget: {{ $project->formatted_budget }}
                                            </span>
                                            @endif
                                            <span class="text-slate-400">•</span>
                                            <span class="text-slate-400 font-mono text-xs">ID: {{ Str::limit($project->teamleader_id, 20) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Import Button --}}
                        <div class="mt-6 pt-6 border-t border-slate-200/50">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-slate-600">
                                    <span id="selected-count" class="font-semibold text-slate-900">0</span> project(s) selected
                                </div>
                                <button type="submit"
                                        id="import-btn"
                                        class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                    <i class="fas fa-cloud-download-alt mr-2"></i>
                                    Import Selected Projects
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- Help Info --}}
        <div class="mt-6 bg-blue-50/50 backdrop-blur-sm border border-blue-200/60 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </div>
                <div class="text-sm text-blue-900">
                    <p class="font-semibold mb-1">Import Information</p>
                    <ul class="space-y-1 text-blue-800">
                        <li>• Projects will be imported with their basic information (title, description, dates, status)</li>
                        <li>• <strong>Budget information</strong> (total project value) will be imported from Teamleader</li>
                        <li>• <strong>Monthly fee</strong> must be set manually in the project settings if needed</li>
                        <li>• <strong>Customer link</strong> is automatically determined from Teamleader (not the filter customer)</li>
                        <li>• Completed projects are imported for reference/reporting purposes</li>
                        <li>• Already imported projects cannot be selected again</li>
                        <li>• Projects without an existing customer in Progress will be skipped</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Select/Clear All functions
function selectAll() {
    document.querySelectorAll('.project-checkbox:not(:disabled)').forEach(cb => {
        const row = cb.closest('.project-row');
        const isImported = row.dataset.isImported === '1';
        if (!isImported) {
            cb.checked = true;
        }
    });
    updateSelectedCount();
}

function clearAll() {
    document.querySelectorAll('.project-checkbox').forEach(cb => cb.checked = false);
    updateSelectedCount();
}

// Update selected count and enable/disable submit button
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const count = checkboxes.length;

    document.getElementById('selected-count').textContent = count;
    document.getElementById('import-btn').disabled = count === 0;
}

// Form submission confirmation
document.getElementById('import-form').addEventListener('submit', function(e) {
    const count = document.querySelectorAll('.project-checkbox:checked').length;

    if (count === 0) {
        e.preventDefault();
        alert('Please select at least one project to import.');
        return false;
    }

    const confirmMsg = `Import ${count} project(s) from Teamleader?\n\n` +
                      `Customer: {{ $customer->name }}\n\n` +
                      `This will create new projects in Progress.\n` +
                      `This typically takes a few seconds per project.\n\n` +
                      `Continue?`;

    if (!confirm(confirmMsg)) {
        e.preventDefault();
        return false;
    }

    // Show loading state
    const btn = document.getElementById('import-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importing... Please wait';
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>
@endpush
@endsection
