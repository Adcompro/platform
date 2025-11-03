@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="min-h-screen" style="background-color: var(--theme-bg);">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Customers</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Manage your customer relationships and contacts</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('customers.create') }}"
                       id="header-create-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-plus mr-1.5"></i>
                        New Customer
                    </a>
                    @endif
                    <button onclick="exportCustomers()"
                            id="header-export-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-download mr-1.5"></i>
                        Export
                    </button>
                    <button onclick="openHelpModal()"
                            id="header-help-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-question mr-1.5"></i>
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
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                    {{ number_format($stats['total_customers']) }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                    Total Customers
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-success-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);">
                    {{ number_format($stats['active_customers']) }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-success);">
                    Active
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-danger-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-danger);">
                    {{ number_format($stats['inactive_customers']) }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-danger);">
                    Inactive
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                    {{ number_format($stats['total_projects']) }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                    Total Projects
                </div>
            </div>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6">
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Search & Filters</h2>
            </div>
            <div style="padding: var(--theme-card-padding);">
                <form method="GET" action="{{ route('customers.index') }}" class="flex items-center space-x-3">
                    <div class="flex-1 max-w-md">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search customers..."
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                    </div>
                    <select name="status"
                            style="padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white"
                            style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius);">
                        <i class="fas fa-search mr-1.5"></i>
                        Search
                    </button>
                    @if(request('search') || request('status'))
                    <a href="{{ route('customers.index') }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                       style="border-radius: var(--theme-border-radius);">
                        <i class="fas fa-times mr-1.5"></i>
                        Clear
                    </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Customers Table --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            @if($customers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                    <thead style="background-color: var(--theme-table-header-bg);">
                        <tr>
                            <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Customer</th>
                            <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Contact</th>
                            <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Address</th>
                            <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Company</th>
                            <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Projects</th>
                            <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                            <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Created</th>
                            <th style="padding: 0.75rem 1.5rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/60 divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                        @foreach($customers as $customer)
                        <tr class="hover:bg-gray-50/60">
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-medium text-sm mr-3"
                                         style="background-color: var(--theme-primary);">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            <a href="{{ route('customers.show', $customer) }}"
                                               style="color: var(--theme-primary); text-decoration: none;">
                                                {{ $customer->name }}
                                            </a>
                                        </div>
                                        @if($customer->contact_person)
                                        <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">{{ $customer->contact_person }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                <div>
                                    @if($customer->email)
                                    <div>
                                        <a href="mailto:{{ $customer->email }}"
                                           style="color: var(--theme-primary); text-decoration: none;">
                                            {{ $customer->email }}
                                        </a>
                                    </div>
                                    @endif
                                    @if($customer->phone)
                                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">{{ $customer->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                <div>
                                    @if($customer->street || $customer->address || $customer->city)
                                        @if($customer->city)
                                            <div>{{ $customer->city }}</div>
                                        @endif
                                        @if($customer->country && $customer->country != 'Netherlands')
                                            <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">{{ $customer->country }}</div>
                                        @endif
                                    @else
                                        <span style="color: var(--theme-text-muted);">-</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                <div class="font-medium">{{ $customer->company ?? 'N/A' }}</div>
                                @if($customer->companyRelation)
                                <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">{{ $customer->companyRelation->name }}</div>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                <div class="font-medium">{{ $customer->projects_count ?? 0 }}</div>
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                @if($customer->status === 'active')
                                <span class="inline-flex px-2 py-1 rounded-full"
                                      style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                                    Active
                                </span>
                                @else
                                <span class="inline-flex px-2 py-1 rounded-full"
                                      style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);">
                                    Inactive
                                </span>
                                @endif
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                                {{ $customer->created_at->format('M d, Y') }}
                            </td>
                            <td style="padding: 1rem 1.5rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text); text-align: right;">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="text-gray-400 hover:text-gray-600 transition-colors"
                                       title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="text-gray-400 hover:text-gray-600 transition-colors"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('customers.destroy', $customer) }}"
                                          method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-gray-400 hover:text-red-600 transition-colors"
                                                title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($customers->hasPages())
            <div style="padding: var(--theme-card-padding); border-top: 1px solid rgba(203, 213, 225, 0.3);">
                {{ $customers->links() }}
            </div>
            @endif
            @else
            <div style="padding: 3rem; text-align: center;">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="mt-2 font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text);">No customers found</h3>
                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Get started by creating a new customer.</p>
                @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                <div class="mt-6">
                    <a href="{{ route('customers.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white"
                       style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius);">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Customer
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Help Modal (keeping existing) --}}
@include('customers.partials.help-modal')

@endsection

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-success').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();

    // Header create button
    const createBtn = document.getElementById('header-create-btn');
    if (createBtn) {
        createBtn.style.backgroundColor = primaryColor;
        createBtn.style.color = 'white';
        createBtn.style.border = 'none';
        createBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Header export button
    const exportBtn = document.getElementById('header-export-btn');
    if (exportBtn) {
        exportBtn.style.backgroundColor = '#6b7280';
        exportBtn.style.color = 'white';
        exportBtn.style.border = 'none';
        exportBtn.style.borderRadius = 'var(--theme-border-radius)';
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

// Export functionality
function exportCustomers() {
    window.location.href = '{{ route("customers.export") }}';
}

// Help modal functionality
function openHelpModal() {
    const modal = document.getElementById('helpModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush