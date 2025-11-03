@extends('layouts.app')

@section('title', 'Select Users to Import')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('teamleader.index') }}" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Select Users to Import</h1>
                        <p class="text-sm text-slate-600">Choose which users from Teamleader to import (no emails will be sent)</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span id="selected-count" class="text-sm text-slate-600 font-medium">
                        0 selected
                    </span>
                    <button type="button" onclick="selectAll()" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-check-double mr-2"></i>
                        Select All
                    </button>
                    <button type="button" onclick="deselectAll()" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-times mr-2"></i>
                        Deselect All
                    </button>
                    <button type="button" onclick="importSelected()" id="import-btn" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-download mr-2"></i>
                        Import Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Sync Banner --}}
        @if(isset($stats) && $stats['last_sync'])
        <div class="mb-6 bg-green-50/50 backdrop-blur-sm border border-green-200/60 rounded-xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                    <div class="text-sm text-green-900">
                        <p class="font-semibold mb-1">Contacts Synced from Database</p>
                        <p class="text-green-800">
                            Last synced {{ $stats['last_sync']->diffForHumans() }}. Showing <strong>{{ $stats['total_contacts'] }}</strong> contacts from local database.
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

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Total Contacts</div>
                <div class="text-2xl font-bold text-slate-900">{{ count($allUsers) }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Standalone</div>
                <div class="text-2xl font-bold text-green-600">{{ collect($allUsers)->where('has_company', false)->count() }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Company Contacts</div>
                <div class="text-2xl font-bold text-orange-600">{{ collect($allUsers)->where('has_company', true)->count() }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Already Imported</div>
                <div class="text-2xl font-bold text-blue-600">{{ collect($allUsers)->where('is_imported', true)->count() }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Selected</div>
                <div class="text-2xl font-bold text-purple-600" id="selected-stat">0</div>
            </div>
        </div>

        {{-- Filter Controls --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="hide-company-contacts" onchange="toggleCompanyContacts()" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-slate-700 font-medium">Hide customer contacts (recommended)</span>
                    </label>
                </div>
                <div class="text-xs text-slate-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Customer contacts are typically not team members
                </div>
            </div>
        </div>

        {{-- Info Notice --}}
        <div class="bg-blue-50/50 backdrop-blur-sm border border-blue-200/60 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <strong>Import Information:</strong>
                    <ul class="mt-2 space-y-1">
                        <li>• Users will be created with a random password (no emails will be sent)</li>
                        <li>• Default role will be set to "user" (can be changed later)</li>
                        <li>• Managing company will be left empty (assign manually later)</li>
                        <li>• Email verification will be auto-completed (no verification email)</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 mb-6">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="search-input" placeholder="Search users by name, email, or phone..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        {{-- Users Table --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider w-12">
                                <input type="checkbox" id="select-all-checkbox" onchange="toggleAll(this)" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Import Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200" id="users-tbody">
                        @foreach($allUsers as $user)
                        <tr class="hover:bg-slate-50 user-row {{ $user['has_company'] ? 'company-contact' : '' }}"
                            data-name="{{ strtolower($user['name']) }}"
                            data-email="{{ strtolower($user['email']) }}"
                            data-phone="{{ strtolower($user['phone']) }}"
                            data-has-company="{{ $user['has_company'] ? '1' : '0' }}">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="checkbox" name="user_ids[]" value="{{ $user['id'] }}"
                                       class="user-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                                       {{ $user['is_imported'] ? 'disabled' : '' }}
                                       onchange="updateSelectedCount()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $user['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">{{ $user['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">{{ $user['phone'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user['has_company'])
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                        <i class="fas fa-building mr-1"></i>
                                        {{ $user['company_name'] }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-user mr-1"></i>
                                        Standalone
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user['is_imported'])
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-check mr-1"></i>
                                        Imported
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Not Imported
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Import Form (Hidden) --}}
<form id="import-form" action="{{ route('teamleader.import.users') }}" method="POST" style="display: none;">
    @csrf
    <div id="selected-users-input"></div>
</form>

@push('scripts')
<script>
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled):checked');
    const count = checkboxes.length;

    document.getElementById('selected-count').textContent = count + ' selected';
    document.getElementById('selected-stat').textContent = count;
    document.getElementById('import-btn').disabled = count === 0;
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = true);
    updateSelectedCount();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = false);
    updateSelectedCount();
}

function toggleAll(masterCheckbox) {
    if (masterCheckbox.checked) {
        selectAll();
    } else {
        deselectAll();
    }
}

function importSelected() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one user to import');
        return;
    }

    if (!confirm(`Are you sure you want to import ${checkboxes.length} users?\n\nNote: No emails will be sent to these users.\nThis may take several minutes, please be patient.`)) {
        return;
    }

    // Show loading indicator
    const importBtn = document.getElementById('import-btn');
    const originalText = importBtn.innerHTML;
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importing... Please wait';

    // Add selected user IDs to hidden form
    const form = document.getElementById('import-form');
    const inputContainer = document.getElementById('selected-users-input');
    inputContainer.innerHTML = '';

    checkboxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_ids[]';
        input.value = checkbox.value;
        inputContainer.appendChild(input);
    });

    form.submit();
}

// Toggle company contacts visibility
function toggleCompanyContacts() {
    const hideCheckbox = document.getElementById('hide-company-contacts');
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const hasCompany = row.dataset.hasCompany === '1';
        if (hideCheckbox.checked && hasCompany) {
            row.style.display = 'none';
            // Uncheck if hidden
            const checkbox = row.querySelector('.user-checkbox');
            if (checkbox && checkbox.checked) {
                checkbox.checked = false;
            }
        } else {
            row.style.display = '';
        }
    });

    updateSelectedCount();
}

// Search functionality
document.getElementById('search-input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const hideCheckbox = document.getElementById('hide-company-contacts');
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const phone = row.dataset.phone || '';
        const hasCompany = row.dataset.hasCompany === '1';

        // Check if should be hidden by company filter
        if (hideCheckbox.checked && hasCompany) {
            row.style.display = 'none';
            return;
        }

        // Apply search filter
        if (name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Initialize count on page load
updateSelectedCount();
</script>
@endpush
@endsection
