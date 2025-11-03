@extends('layouts.app')

@section('title', 'Import Contacts for Customer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('customers.show', $customer) }}" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Import Contacts for Customer</h1>
                        <p class="text-sm text-slate-600">Customer: <strong>{{ $customer->name }}</strong></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span id="selected-count" class="text-sm text-slate-600 font-medium">
                        0 selected
                    </span>
                    <button type="button" onclick="selectAll()" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-check-double mr-2"></i>
                        Select All Available
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
        <div class="mb-6 bg-blue-50/50 backdrop-blur-sm border border-blue-200/60 rounded-xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-filter text-blue-600 text-lg"></i>
                    </div>
                    <div class="text-sm text-blue-900">
                        <p class="font-semibold mb-1">🎯 Smart Filtered Contacts for {{ $customer->name }}</p>
                        <p class="text-blue-800">
                            Last synced {{ $stats['last_sync']->diffForHumans() }}.
                            Showing <strong>{{ $stats['total_contacts'] }}</strong> contacts automatically filtered for this customer.
                            <br><small class="text-blue-700">
                                <i class="fas fa-magic mr-1"></i>
                                Uses intelligent filtering: database cache (fast) with API fallback (accurate).
                            </small>
                        </p>
                    </div>
                </div>
                <a href="{{ route('teamleader.index') }}"
                   class="px-4 py-2 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-200 transition-all whitespace-nowrap">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Sync Dashboard
                </a>
            </div>
        </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Total Contacts</div>
                <div class="text-2xl font-bold text-slate-900">{{ $stats['total_contacts'] }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-green-200/60 rounded-xl p-4">
                <div class="text-sm text-green-600">Available to Import</div>
                <div class="text-2xl font-bold text-green-700">{{ $stats['available_to_import'] }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-blue-200/60 rounded-xl p-4">
                <div class="text-sm text-blue-600">Already Imported</div>
                <div class="text-2xl font-bold text-blue-700">{{ $stats['already_imported'] }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-purple-200/60 rounded-xl p-4">
                <div class="text-sm text-purple-600">Selected</div>
                <div class="text-2xl font-bold text-purple-700" id="selected-stat">0</div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="mb-6 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-search text-slate-400 text-lg"></i>
                </div>
                <input type="text"
                       id="search-input"
                       placeholder="Search within {{ $customer->name }}'s contacts (name, email, phone, position)..."
                       class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                       onkeyup="filterContacts()">
                <div class="text-sm text-slate-600">
                    <span id="filtered-count">{{ count($allContacts) }}</span> / {{ count($allContacts) }} contacts shown
                </div>
            </div>
        </div>

        {{-- Contact List --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/50 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-900">Select Contacts to Import</h2>
                <p class="text-xs text-slate-500 mt-1">
                    <i class="fas fa-filter mr-1"></i>
                    Pre-filtered for <strong>{{ $customer->name }}</strong>. Showing {{ count($allContacts) }} contact(s) linked to this customer.
                    @if(count($allContacts) > 5)
                    <br><i class="fas fa-search mr-1"></i>Use the search bar above to quickly find specific contacts.
                    @endif
                </p>
            </div>

            <form action="{{ route('teamleader.import.contacts') }}" method="POST" id="import-form">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="p-6">
                    @if(count($allContacts) === 0)
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-slate-600 mb-4">No contacts have been synced yet.</p>
                            <a href="{{ route('teamleader.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                Go to Global Sync
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-12">
                                            <!-- Checkbox header -->
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            Phone
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            Position
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($allContacts as $contact)
                                    <tr class="contact-row hover:bg-slate-50 transition-colors {{ $contact['is_imported'] ? 'opacity-60' : '' }}">
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            @if($contact['is_imported'])
                                                <div class="w-5 h-5 rounded bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-check text-blue-600 text-xs"></i>
                                                </div>
                                            @else
                                                <input type="checkbox"
                                                       name="contact_ids[]"
                                                       value="{{ $contact['id'] }}"
                                                       class="contact-checkbox w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                       onchange="updateSelectedCount()">
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <div class="contact-name text-sm font-medium text-slate-900">{{ $contact['name'] }}</div>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600">{{ $contact['email'] }}</div>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600">{{ $contact['phone'] }}</div>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-600">{{ $contact['position'] }}</div>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            @if($contact['is_imported'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Imported
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Available
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.contact-checkbox:checked');
        const count = checkboxes.length;

        document.getElementById('selected-count').textContent = count + ' selected';
        document.getElementById('selected-stat').textContent = count;
        document.getElementById('import-btn').disabled = count === 0;
    }

    function selectAll() {
        const checkboxes = document.querySelectorAll('.contact-checkbox:not(:disabled)');
        checkboxes.forEach(cb => {
            if (!cb.closest('.contact-row').classList.contains('opacity-60')) {
                cb.checked = true;
            }
        });
        updateSelectedCount();
    }

    function deselectAll() {
        const checkboxes = document.querySelectorAll('.contact-checkbox:checked');
        checkboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    }

    function importSelected() {
        const count = document.querySelectorAll('.contact-checkbox:checked').length;

        if (!confirm(`Are you sure you want to import ${count} contact(s) for this customer?`)) {
            return;
        }

        const importBtn = document.getElementById('import-btn');
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importing...';

        document.getElementById('import-form').submit();
    }

    // Initialize count on page load
    document.addEventListener('DOMContentLoaded', updateSelectedCount);

    // ========================================
    // SEARCH/FILTER FUNCTIONALITY
    // ========================================
    function filterContacts() {
        const searchInput = document.getElementById('search-input');
        const searchTerm = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.contact-row');
        let visibleCount = 0;

        rows.forEach(row => {
            if (searchTerm === '') {
                // Geen filter - toon alles
                row.style.display = '';
                visibleCount++;
            } else {
                // Haal alle zoekbare data uit de row
                const name = row.querySelector('.contact-name').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const phone = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                const position = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

                // Check of search term voorkomt in een van de velden
                const matches = name.includes(searchTerm) ||
                               email.includes(searchTerm) ||
                               phone.includes(searchTerm) ||
                               position.includes(searchTerm);

                if (matches) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';

                    // Uncheck verborgen rows
                    const checkbox = row.querySelector('.contact-checkbox');
                    if (checkbox && checkbox.checked) {
                        checkbox.checked = false;
                    }
                }
            }
        });

        // Update filtered count
        document.getElementById('filtered-count').textContent = visibleCount;

        // Update selected count (na unchecken van verborgen items)
        updateSelectedCount();
    }

    // Clear search knop (optioneel)
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');

        // Focus op zoekbalk bij Ctrl+F / Cmd+F
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });

        // Clear button bij ESC
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                filterContacts();
            }
        });
    });
</script>
@endpush
@endsection
