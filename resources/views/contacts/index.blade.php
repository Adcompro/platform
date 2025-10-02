@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
{{-- Sticky Header - Exact Copy Theme Settings --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Contacts</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Manage customer contacts and relationships</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                <a href="{{ route('contacts.create') }}"
                   id="header-create-btn"
                   class="header-btn"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-plus mr-1.5"></i>
                    New Contact
                </a>
                @endif

                {{-- Help Button --}}
                <button onclick="openHelpModal()"
                        id="header-help-btn"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);"
                        title="Contact Management Guide">
                    <i class="fas fa-question-circle mr-1.5"></i>
                    Help
                </button>
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

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
            <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                {{ number_format($stats['total_contacts']) }}
            </div>
            <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                Total Contacts
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-success-rgb), 0.05);">
            <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);">
                {{ number_format($stats['active_contacts']) }}
            </div>
            <div style="font-size: var(--theme-font-size); color: var(--theme-success);">
                Active Contacts
            </div>
        </div>

        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
            <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                {{ number_format($stats['new_this_month']) }}
            </div>
            <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                New This Month
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6" style="padding: var(--theme-card-padding);">
        <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text"
                       name="search"
                       placeholder="Search contacts..."
                       value="{{ request('search') }}"
                       class="w-full border rounded-md focus:outline-none focus:ring-2"
                       style="font-size: var(--theme-font-size); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-color: rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); focus:border-color: var(--theme-primary); focus:ring-color: rgba(var(--theme-primary-rgb), 0.2);">
            </div>

            <select name="customer_id"
                    class="border rounded-md focus:outline-none focus:ring-2"
                    style="font-size: var(--theme-font-size); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-color: rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); focus:border-color: var(--theme-primary);">
                <option value="">All Customers</option>
                @foreach(\App\Models\Customer::orderBy('name')->get() as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    class="border rounded-md focus:outline-none focus:ring-2"
                    style="font-size: var(--theme-font-size); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-color: rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); focus:border-color: var(--theme-primary);">
                <option value="">All Status</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit"
                    class="font-medium rounded-md hover:opacity-90 transition-all"
                    style="font-size: var(--theme-font-size); background-color: var(--theme-primary); color: white; padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-radius: var(--theme-border-radius);">
                Filter
            </button>

            @if(request()->hasAny(['search', 'customer_id', 'status']))
            <a href="{{ route('contacts.index') }}"
               class="font-medium rounded-md hover:opacity-90 transition-all"
               style="font-size: var(--theme-font-size); background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted); padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); border-radius: var(--theme-border-radius); text-decoration: none;">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Contacts Table --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                <thead style="background-color: var(--theme-table-header-bg);">
                    <tr>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Contact
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Customer
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Contact Info
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Companies
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Status
                        </th>
                        <th style="padding: 0.75rem 1.5rem; text-align: right; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white/60 divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                    @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50/60">
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-medium"
                                     style="background-color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 2px);">
                                    {{ strtoupper(substr($contact->name, 0, 2)) }}
                                </div>
                                <div class="ml-4">
                                    <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                        <a href="{{ route('contacts.show', $contact) }}" style="color: var(--theme-primary); text-decoration: none;" class="hover:opacity-80">
                                            {{ $contact->name }}
                                        </a>
                                    </div>
                                    @if($contact->position)
                                    <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">{{ $contact->position }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($contact->customer)
                            <div>
                                <a href="{{ route('customers.show', $contact->customer) }}" style="color: var(--theme-accent); text-decoration: none;" class="hover:opacity-80">
                                    {{ $contact->customer->name }}
                                </a>
                            </div>
                            @else
                            <span style="color: var(--theme-text-muted);">-</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div>
                                @if($contact->email)
                                <div>
                                    <a href="mailto:{{ $contact->email }}" style="color: var(--theme-accent); text-decoration: none;" class="hover:opacity-80">
                                        {{ $contact->email }}
                                    </a>
                                </div>
                                @endif
                                @if($contact->phone)
                                <div style="color: var(--theme-text-muted);">
                                    {{ $contact->phone }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 1rem 1.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $companies = $contact->companies->take(3);
                                    $remaining = $contact->companies->count() - 3;
                                @endphp
                                @foreach($companies as $company)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full"
                                      style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; {{ $company->pivot->is_primary ? 'background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);' : 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);' }}"
                                      title="{{ $company->name }}">
                                    @php
                                        $words = explode(' ', $company->name);
                                        $abbreviation = '';
                                        if(count($words) >= 2) {
                                            $abbreviation = strtoupper(substr($words[0], 0, 2) . ' ' . substr($words[count($words)-1], 0, 2));
                                        } else {
                                            $abbreviation = strtoupper(substr($company->name, 0, 2) . ' ' . substr($company->name, -2));
                                        }
                                    @endphp
                                    {{ $abbreviation }}
                                    @if($company->pivot->is_primary)
                                        <span class="ml-0.5">✓</span>
                                    @endif
                                </span>
                                @endforeach
                                @if($remaining > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full"
                                      style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-text-muted-rgb), 0.05); color: var(--theme-text-muted);">
                                    +{{ $remaining }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <span class="inline-flex items-center px-2 py-1 rounded-full"
                                  style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; {{ $contact->is_active ? 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);' : 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);' }}">
                                {{ $contact->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="padding: 1rem 1.5rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size); color: var(--theme-text);">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('contacts.show', $contact) }}"
                                   class="text-gray-400 hover:text-gray-600" title="View" style="font-size: var(--theme-font-size);">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                <a href="{{ route('contacts.edit', $contact) }}"
                                   class="text-gray-400 hover:text-gray-600" title="Edit" style="font-size: var(--theme-font-size);">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this contact?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-gray-400 hover:text-red-600" title="Delete" style="font-size: var(--theme-font-size);">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 3rem 1.5rem; text-align: center;">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 mb-3" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 715.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No contacts found</p>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                <a href="{{ route('contacts.create') }}"
                                   style="margin-top: 0.75rem; color: var(--theme-accent); font-size: var(--theme-font-size); text-decoration: none;"
                                   class="hover:opacity-80">
                                    Create your first contact
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($contacts->hasPages())
        <div style="padding: 0.75rem 1.5rem; border-top: 1px solid rgba(203, 213, 225, 0.3); background-color: rgba(var(--theme-table-header-bg), 0.5); font-size: var(--theme-font-size);">
            {{ $contacts->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Help Modal --}}
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-gray-50 rounded-xl shadow-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center" style="border-color: rgba(203, 213, 225, 0.3); background-color: rgba(var(--theme-table-header-bg), 0.5);">
            <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text);">Contact Management Guide</h3>
            <button onclick="closeHelpModal()" style="color: var(--theme-text-muted);" class="hover:opacity-60">
                <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) + 4px);"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" style="font-size: var(--theme-font-size);">
            <div class="space-y-6">
                {{-- Introduction --}}
                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">Overview</h4>
                    <p style="color: var(--theme-text-muted);">
                        The Contact Management system allows you to maintain a comprehensive database of all people associated with your customers.
                        Each contact can be linked to one customer and multiple companies, making it easy to track complex business relationships.
                    </p>
                </div>

                {{-- Key Features --}}
                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">Key Features</h4>
                    <ul class="space-y-2" style="color: var(--theme-text-muted);">
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Customer Integration:</strong> Contacts are displayed directly in the customer detail view for easy access</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Primary Contact Designation:</strong> Mark one contact as primary for each customer</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Multiple Company Relations:</strong> Link contacts to multiple companies with primary designation</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mr-2 mt-1" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 2px);"></i>
                            <span><strong>Quick Communication:</strong> Click-to-email and click-to-call functionality</span>
                        </li>
                    </ul>
                </div>

                {{-- Best Practices --}}
                <div>
                    <h4 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">Best Practices</h4>
                    <div class="rounded-lg p-4" style="background-color: rgba(var(--theme-primary-rgb), 0.05);">
                        <ul class="space-y-2" style="color: var(--theme-text-muted);">
                            <li class="flex items-start">
                                <span style="color: var(--theme-text-muted); margin-right: 0.5rem;">1.</span>
                                <span><strong>Always designate a primary contact</strong> for each customer to establish the main point of communication</span>
                            </li>
                            <li class="flex items-start">
                                <span style="color: var(--theme-text-muted); margin-right: 0.5rem;">2.</span>
                                <span><strong>Keep contact information updated</strong> - Regularly verify email addresses and phone numbers</span>
                            </li>
                            <li class="flex items-start">
                                <span style="color: var(--theme-text-muted); margin-right: 0.5rem;">3.</span>
                                <span><strong>Use the position field</strong> to understand the contact's role and decision-making authority</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end" style="background-color: rgba(var(--theme-table-header-bg), 0.5); border-color: rgba(203, 213, 225, 0.3);">
            <button onclick="closeHelpModal()"
                    class="font-medium text-white rounded-md hover:opacity-90 transition-all"
                    style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: var(--theme-primary); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                Close Guide
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header create button
    const createBtn = document.getElementById('header-create-btn');
    if (createBtn) {
        createBtn.style.backgroundColor = primaryColor;
        createBtn.style.color = 'white';
        createBtn.style.border = 'none';
        createBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Header help button
    const helpBtn = document.getElementById('header-help-btn');
    if (helpBtn) {
        helpBtn.style.backgroundColor = '#6b7280';
        helpBtn.style.color = 'white';
        helpBtn.style.border = 'none';
        helpBtn.style.borderRadius = 'var(--theme-border-radius)';
    }
}

// Help modal functions
function openHelpModal() {
    document.getElementById('help-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeHelpModal() {
    document.getElementById('help-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('help-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelpModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHelpModal();
    }
});

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush
@endsection