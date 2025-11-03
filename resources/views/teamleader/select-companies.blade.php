@extends('layouts.app')

@section('title', 'Select Companies to Import')

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
                        <h1 class="text-2xl font-bold text-slate-900">Select Companies to Import</h1>
                        <p class="text-sm text-slate-600">Choose which companies from Teamleader to import as customers</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span id="selected-count" class="text-sm text-slate-600 font-medium">
                        0 selected
                    </span>
                    {{-- Help Button tijdelijk uitgeschakeld
                    <button type="button" onclick="openHelpModal()" class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-all" title="Import Guide">
                        <i class="fas fa-question-circle mr-2"></i>
                        Help
                    </button>
                    --}}
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
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Total Companies</div>
                <div class="text-2xl font-bold text-slate-900">{{ count($allCompanies) }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Can Update</div>
                <div class="text-2xl font-bold text-orange-600">{{ collect($allCompanies)->where('is_imported', true)->count() }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">New to Import</div>
                <div class="text-2xl font-bold text-blue-600">{{ collect($allCompanies)->where('is_imported', false)->count() }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-sm text-slate-600">Selected</div>
                <div class="text-2xl font-bold text-orange-600" id="selected-stat">0</div>
            </div>
        </div>

        {{-- Import Options --}}
        <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 backdrop-blur-sm border border-blue-200/60 rounded-xl p-6 mb-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-magic text-white text-xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">One-Click Complete Import</h3>
                    <p class="text-sm text-slate-600 mb-4">Choose what to import along with the selected companies:</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Option 1: Import Contacts --}}
                        <label class="flex items-start gap-3 p-4 bg-white/60 backdrop-blur-sm border border-slate-200 rounded-lg hover:border-blue-300 transition-all cursor-pointer group">
                            <input type="checkbox" id="import-contacts" checked class="mt-1 w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-users text-blue-600 text-sm"></i>
                                    <span class="font-medium text-slate-900 text-sm">Import Contacts</span>
                                </div>
                                <p class="text-xs text-slate-600">Automatically import all contact persons for each company (with positions)</p>
                            </div>
                        </label>

                        {{-- Option 2: Import Projects --}}
                        <label class="flex items-start gap-3 p-4 bg-white/60 backdrop-blur-sm border border-slate-200 rounded-lg hover:border-blue-300 transition-all cursor-pointer group">
                            <input type="checkbox" id="import-projects" checked class="mt-1 w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-project-diagram text-green-600 text-sm"></i>
                                    <span class="font-medium text-slate-900 text-sm">Import Projects</span>
                                </div>
                                <p class="text-xs text-slate-600">Import all projects linked to these companies (structure will be imported from Excel)</p>
                            </div>
                        </label>

                        {{-- Note: Time Entries & Milestones are imported via Excel upload --}}
                        <div class="p-3 bg-amber-50/50 border border-amber-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-info-circle text-amber-600 text-sm mt-0.5"></i>
                                <p class="text-xs text-amber-900">
                                    <strong>Note:</strong> Time entries and detailed project structure (milestones, tasks) are imported via Excel upload in the Timesheets section.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-blue-100/50 border border-blue-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5"></i>
                            <p class="text-xs text-blue-900">
                                <strong>Tip:</strong> This may take a few minutes for large imports.
                                Progress will be tracked and you can continue working while the import runs in the background.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 mb-6">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="search-input" placeholder="Search companies by name, VAT, or email..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        {{-- Companies Table --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider w-12">
                                <input type="checkbox" id="select-all-checkbox" onchange="toggleAll(this)" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Company Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">VAT Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Import Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200" id="companies-tbody">
                        @foreach($allCompanies as $company)
                        <tr class="hover:bg-slate-50 company-row" data-name="{{ strtolower($company['name']) }}" data-vat="{{ strtolower($company['vat_number']) }}" data-email="{{ strtolower($company['email']) }}">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="checkbox" name="company_ids[]" value="{{ $company['id'] }}"
                                       class="company-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                                       onchange="updateSelectedCount()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ $company['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">{{ $company['vat_number'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">{{ $company['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $company['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($company['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($company['is_imported'])
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                        <i class="fas fa-sync-alt mr-1"></i>
                                        Can Re-Import/Update
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
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
<form id="import-form" action="{{ route('teamleader.import.companies') }}" method="POST" style="display: none;">
    @csrf
    <div id="selected-companies-input"></div>
</form>

@push('scripts')
<script>
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.company-checkbox:not(:disabled):checked');
    const count = checkboxes.length;

    document.getElementById('selected-count').textContent = count + ' selected';
    document.getElementById('selected-stat').textContent = count;
    document.getElementById('import-btn').disabled = count === 0;
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.company-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = true);
    updateSelectedCount();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.company-checkbox:not(:disabled)');
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
    const checkboxes = document.querySelectorAll('.company-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one company to import');
        return;
    }

    // Get import options
    const importContacts = document.getElementById('import-contacts').checked;
    const importProjects = document.getElementById('import-projects').checked;

    // Build confirmation message
    let confirmMessage = `Start background import for ${checkboxes.length} ${checkboxes.length === 1 ? 'company' : 'companies'}`;
    let includedItems = [];
    if (importContacts) includedItems.push('contacts');
    if (importProjects) includedItems.push('projects (structure only)');

    if (includedItems.length > 0) {
        confirmMessage += ' including:\n- ' + includedItems.map(item => item.charAt(0).toUpperCase() + item.slice(1)).join('\n- ');
    }
    confirmMessage += '\n\n✅ Import will run in background\n✅ You can continue working\n✅ Email notification when complete\n\nContinue?';

    if (!confirm(confirmMessage)) {
        return;
    }

    // Show loading indicator
    const importBtn = document.getElementById('import-btn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-rocket mr-2"></i>Starting background import...';

    // Add selected company IDs to hidden form
    const form = document.getElementById('import-form');
    const inputContainer = document.getElementById('selected-companies-input');
    inputContainer.innerHTML = '';

    checkboxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'company_ids[]';
        input.value = checkbox.value;
        inputContainer.appendChild(input);
    });

    // Add import options as hidden inputs
    const addOption = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value ? '1' : '0';
        inputContainer.appendChild(input);
    };

    addOption('import_contacts', importContacts);
    addOption('import_projects', importProjects);

    form.submit();
}

// Search functionality
document.getElementById('search-input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.company-row');

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const vat = row.dataset.vat || '';
        const email = row.dataset.email || '';

        if (name.includes(searchTerm) || vat.includes(searchTerm) || email.includes(searchTerm)) {
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

{{-- Help Modal tijdelijk uitgeschakeld - wordt later toegevoegd --}}

@endsection
