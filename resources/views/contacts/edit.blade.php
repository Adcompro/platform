@extends('layouts.app')

@section('title', 'Edit Contact')

@section('content')
{{-- Sticky Header - Exact Copy Theme Settings --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Edit Contact</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Update contact information for {{ $contact->name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" form="contact-edit-form" id="header-save-btn"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-save mr-1.5"></i>
                    Save Changes
                </button>

                <a href="{{ route('contacts.show', $contact) }}"
                   id="header-cancel-btn"
                   class="header-btn"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-times mr-1.5"></i>
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main Content - Exact Copy Theme Settings --}}
<div style="padding: 1.5rem 2rem;">
    {{-- Flash Messages --}}
    @if($errors->any())
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border-color: var(--theme-danger); color: var(--theme-danger); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                    <span style="font-size: var(--theme-font-size); font-weight: 600;">Please correct the following errors:</span>
                    <ul class="mt-2 list-disc list-inside" style="font-size: var(--theme-font-size);">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Form --}}
    <form id="contact-edit-form" method="POST" action="{{ route('contacts.update', $contact) }}">
        @csrf
        @method('PUT')

        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Contact Information</h2>
            </div>

            <div style="padding: var(--theme-card-padding);">
                <div class="space-y-6">
                    {{-- Customer Selection --}}
                    <div>
                        <label for="customer_id" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Customer <span style="color: var(--theme-danger);">*</span>
                        </label>
                        <select name="customer_id"
                                id="customer_id"
                                required
                                class="@error('customer_id') border-red-300 @enderror"
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $contact->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                    @if($customer->company)
                                        ({{ $customer->company }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p style="margin-top: 0.25rem; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Basic Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                                Contact Name <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $contact->name) }}"
                                   required
                                   class="@error('name') border-red-300 @enderror"
                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            @error('name')
                                <p style="margin-top: 0.25rem; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="position" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                                Position/Title
                            </label>
                            <input type="text"
                                   name="position"
                                   id="position"
                                   value="{{ old('position', $contact->position) }}"
                                   placeholder="e.g. Marketing Manager"
                                   class="@error('position') border-red-300 @enderror"
                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            @error('position')
                                <p style="margin-top: 0.25rem; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Contact Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="email" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                                Email Address
                            </label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email', $contact->email) }}"
                                   class="@error('email') border-red-300 @enderror"
                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            @error('email')
                                <p style="margin-top: 0.25rem; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                                Phone Number
                            </label>
                            <input type="text"
                                   name="phone"
                                   id="phone"
                                   value="{{ old('phone', $contact->phone) }}"
                                   placeholder="+31 6 12345678"
                                   class="@error('phone') border-red-300 @enderror"
                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            @error('phone')
                                <p style="margin-top: 0.25rem; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Relation of (Multiple Companies) --}}
                    @if(Auth::user()->role === 'super_admin')
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Relation of
                        </label>
                        <div class="space-y-3">
                            {{-- Selected Companies Display --}}
                            <div id="selected-companies" class="flex flex-wrap gap-2">
                                @foreach($contact->companies as $company)
                                <div class="inline-flex items-center rounded-lg" style="background-color: rgba(248, 250, 252, 0.8); padding: 0.5rem 0.75rem;">
                                    <span class="font-medium" style="color: {{ $company->pivot->is_primary ? 'var(--theme-primary)' : 'var(--theme-text)' }}; font-size: var(--theme-font-size);">
                                        {{ $company->name }}
                                    </span>
                                    @if($company->pivot->is_primary)
                                        <span class="ml-2 px-1.5 py-0.5 rounded" style="background-color: var(--theme-primary); color: white; font-size: calc(var(--theme-font-size) - 2px);">Primary</span>
                                    @endif
                                    <input type="hidden" name="companies[]" value="{{ $company->id }}">
                                    <input type="hidden" name="company_primary[{{ $company->id }}]" value="{{ $company->pivot->is_primary ? '1' : '0' }}">
                                    <button type="button" onclick="removeCompany(this, {{ $company->id }})" class="ml-2 hover:opacity-60" style="color: var(--theme-danger);">
                                        <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>

                            {{-- Add Company Button --}}
                            <button type="button" onclick="openCompanyModal()" class="font-medium rounded-md hover:opacity-90 transition-all"
                                    style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); border-radius: var(--theme-border-radius); border: none;">
                                Add Company Relation
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <label for="notes" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Notes / Comments
                        </label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="4"
                                  class="@error('notes') border-red-300 @enderror"
                                  style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">{{ old('notes', $contact->notes) }}</textarea>
                        @error('notes')
                            <p style="margin-top: 0.25rem; color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status and Primary --}}
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="is_primary"
                                   value="1"
                                   {{ old('is_primary', $contact->is_primary) ? 'checked' : '' }}
                                   class="h-4 w-4 border-gray-300 rounded"
                                   style="color: var(--theme-primary);">
                            <span style="margin-left: 0.5rem; color: var(--theme-text); font-size: var(--theme-font-size);">Set as Primary Contact</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $contact->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 border-gray-300 rounded"
                                   style="color: var(--theme-primary);">
                            <span style="margin-left: 0.5rem; color: var(--theme-text); font-size: var(--theme-font-size);">Active</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Company Selection Modal --}}
@if(Auth::user()->role === 'super_admin')
<div id="company-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Add Company Relation</h3>
        </div>
        <div style="padding: var(--theme-card-padding);">
            <div class="space-y-4">
                <div>
                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Select Company</label>
                    <select id="company-select" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                        <option value="">Choose a company...</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="is-primary-checkbox" class="h-4 w-4 border-gray-300 rounded" style="color: var(--theme-primary);">
                        <span style="margin-left: 0.5rem; color: var(--theme-text); font-size: var(--theme-font-size);">Set as Primary Company</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="border-t flex justify-end gap-2" style="border-color: rgba(203, 213, 225, 0.3); background-color: rgba(248, 250, 252, 0.5); padding: var(--theme-card-padding);">
            <button type="button" onclick="closeCompanyModal()" class="font-medium rounded-md hover:opacity-90 transition-all"
                    style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted); font-size: var(--theme-font-size); border-radius: var(--theme-border-radius); border: none;">
                Cancel
            </button>
            <button type="button" onclick="addCompany()" class="font-medium rounded-md hover:opacity-90 transition-all"
                    style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); border-radius: var(--theme-border-radius); border: none;">
                Add Company
            </button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header save button
    const saveBtn = document.getElementById('header-save-btn');
    if (saveBtn) {
        saveBtn.style.backgroundColor = primaryColor;
        saveBtn.style.color = 'white';
        saveBtn.style.border = 'none';
        saveBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Header cancel button
    const cancelBtn = document.getElementById('header-cancel-btn');
    if (cancelBtn) {
        cancelBtn.style.backgroundColor = '#6b7280';
        cancelBtn.style.color = 'white';
        cancelBtn.style.border = 'none';
        cancelBtn.style.borderRadius = 'var(--theme-border-radius)';
        cancelBtn.style.textDecoration = 'none';
    }

    // Form checkboxes
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.style.accentColor = primaryColor;
    });

    // Form elements focus styling
    const formElements = document.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
        element.addEventListener('focus', function() {
            this.style.borderColor = primaryColor;
            this.style.outline = `2px solid ${primaryColor}20`;
        });

        element.addEventListener('blur', function() {
            if (!this.classList.contains('border-red-300')) {
                this.style.borderColor = 'rgba(203, 213, 225, 0.6)';
            }
            this.style.outline = 'none';
        });
    });
}

@if(Auth::user()->role === 'super_admin')
// Track already selected companies
let selectedCompanies = [
    @foreach($contact->companies as $company)
    {{ $company->id }},
    @endforeach
];

function openCompanyModal() {
    document.getElementById('company-modal').style.display = 'flex';
    // Reset selection
    document.getElementById('company-select').value = '';
    document.getElementById('is-primary-checkbox').checked = false;
}

function closeCompanyModal() {
    document.getElementById('company-modal').style.display = 'none';
}

function addCompany() {
    const select = document.getElementById('company-select');
    const companyId = select.value;
    const companyName = select.options[select.selectedIndex].text;
    const isPrimary = document.getElementById('is-primary-checkbox').checked;

    if (!companyId) {
        alert('Please select a company');
        return;
    }

    if (selectedCompanies.includes(parseInt(companyId))) {
        alert('This company is already selected');
        return;
    }

    // If setting as primary, update all existing to non-primary
    if (isPrimary) {
        document.querySelectorAll('input[name^="company_primary"]').forEach(input => {
            input.value = '0';
        });
        // Update visual indicators
        document.querySelectorAll('#selected-companies .font-medium').forEach(span => {
            span.style.color = 'var(--theme-text)';
        });
        document.querySelectorAll('#selected-companies .ml-2.px-1\\.5').forEach(badge => {
            badge.remove();
        });
    }

    // Add to selected companies
    selectedCompanies.push(parseInt(companyId));

    // Create company element
    const container = document.getElementById('selected-companies');
    const companyDiv = document.createElement('div');
    companyDiv.className = 'inline-flex items-center rounded-lg';
    companyDiv.style.backgroundColor = 'rgba(248, 250, 252, 0.8)';
    companyDiv.style.padding = '0.5rem 0.75rem';

    let html = `
        <span class="font-medium" style="color: ${isPrimary ? 'var(--theme-primary)' : 'var(--theme-text)'}; font-size: var(--theme-font-size);">
            ${companyName}
        </span>
    `;

    if (isPrimary) {
        html += '<span class="ml-2 px-1.5 py-0.5 rounded" style="background-color: var(--theme-primary); color: white; font-size: calc(var(--theme-font-size) - 2px);">Primary</span>';
    }

    html += `
        <input type="hidden" name="companies[]" value="${companyId}">
        <input type="hidden" name="company_primary[${companyId}]" value="${isPrimary ? '1' : '0'}">
        <button type="button" onclick="removeCompany(this, ${companyId})" class="ml-2 hover:opacity-60" style="color: var(--theme-danger);">
            <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
        </button>
    `;

    companyDiv.innerHTML = html;
    container.appendChild(companyDiv);

    closeCompanyModal();
}

function removeCompany(button, companyId) {
    // Remove from selected companies
    selectedCompanies = selectedCompanies.filter(id => id !== companyId);

    // Remove the element
    button.closest('.inline-flex').remove();
}

// Close modal when clicking outside
document.getElementById('company-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCompanyModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCompanyModal();
    }
});
@endif

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush
@endsection