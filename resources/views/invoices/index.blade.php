@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                    <p class="text-sm text-gray-600">Manage project invoices and billing</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('invoices.help') }}" 
                       class="inline-flex items-center px-3 py-2 bg-slate-100 border border-slate-200 rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition ease-in-out duration-150"
                       title="Invoice Help Guide">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Help
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button onclick="openGenerateModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Generate Invoice
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600">Draft Invoices</p>
                        <p class="text-xl font-semibold text-slate-900 mt-1">{{ $stats['draft_count'] ?? 0 }}</p>
                        <p class="text-xs text-slate-500 mt-1">€{{ number_format($stats['draft_total'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-gray-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600">Finalized</p>
                        <p class="text-xl font-semibold text-slate-900 mt-1">{{ $stats['finalized_count'] ?? 0 }}</p>
                        <p class="text-xs text-slate-500 mt-1">€{{ number_format($stats['finalized_total'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600">Sent</p>
                        <p class="text-xl font-semibold text-slate-900 mt-1">{{ $stats['sent_count'] ?? 0 }}</p>
                        <p class="text-xs text-slate-500 mt-1">€{{ number_format($stats['sent_total'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-yellow-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600">Paid</p>
                        <p class="text-xl font-semibold text-slate-900 mt-1">{{ $stats['paid_count'] ?? 0 }}</p>
                        <p class="text-xs text-slate-500 mt-1">€{{ number_format($stats['paid_total'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-green-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
        <div class="bg-white border border-gray-200 rounded-lg p-6" style="box-shadow: var(--theme-card-shadow);">
            <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap gap-3">
                <input type="text" name="search" placeholder="Search invoices..." value="{{ request('search') }}" 
                    class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                
                <select name="status" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="finalized" {{ request('status') == 'finalized' ? 'selected' : '' }}>Finalized</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <select name="project_id" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                    <option value="">All Projects</option>
                    @foreach($projects ?? [] as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>

                <select name="period" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                    <option value="">All Periods</option>
                    <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_quarter" {{ request('period') == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>

                <button type="submit" class="px-4 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    Filter
                </button>
                
                @if(request()->anyFilled(['search', 'status', 'project_id', 'period']))
                    <a href="{{ route('invoices.index') }}" class="px-4 py-1.5 bg-white text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all border border-slate-200">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-32">
        <div class="bg-white border border-gray-200 rounded-lg p-6" style="box-shadow: var(--theme-card-shadow);">
            <div class="border-b border-gray-100 mb-4 pb-3">
                <h2 class="text-[17px] font-semibold">Invoices List</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Project</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Period</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/40 divide-y divide-slate-200/30">
                        @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-4 py-3">
                                <div>
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $invoice->invoice_number ?: $invoice->draft_name ?: 'Draft #' . $invoice->id }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $invoice->invoice_date?->format('d/m/Y') ?: 'Draft' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-slate-900">{{ $invoice->project->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ ucfirst($invoice->billing_type ?? 'monthly') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-slate-900">{{ $invoice->customer->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-slate-900">
                                    @if($invoice->period_start && $invoice->period_end)
                                        {{ $invoice->period_start->format('d/m') }} - {{ $invoice->period_end->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                    @if($invoice->status == 'draft') bg-gray-100 text-gray-700
                                    @elseif($invoice->status == 'finalized') bg-blue-100 text-blue-700
                                    @elseif($invoice->status == 'sent') bg-yellow-100 text-yellow-700
                                    @elseif($invoice->status == 'paid') bg-green-100 text-green-700
                                    @elseif($invoice->status == 'overdue') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="text-sm font-medium text-slate-900">€{{ number_format($invoice->total_amount ?? 0, 2) }}</div>
                                @if($invoice->next_month_rollover)
                                    <div class="text-xs text-slate-500">Rollover: €{{ number_format($invoice->next_month_rollover, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end space-x-1">
                                    <a href="{{ route('invoices.show', $invoice) }}" 
                                        class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-50 rounded-lg transition-all"
                                        title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @if($invoice->status == 'draft' && in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <a href="{{ route('invoices.edit', $invoice) }}" 
                                        class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-50 rounded-lg transition-all"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    @if($invoice->status == 'finalized' && in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <button onclick="markAsSent({{ $invoice->id }})" 
                                        class="text-slate-400 hover:text-yellow-600 p-1 hover:bg-yellow-50 rounded-lg transition-all"
                                        title="Mark as Sent">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    @if(($invoice->status == 'sent' || $invoice->status == 'overdue') && in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <button onclick="markAsPaid({{ $invoice->id }})" 
                                        class="text-slate-400 hover:text-green-600 p-1 hover:bg-green-50 rounded-lg transition-all"
                                        title="Mark as Paid">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                                No invoices found. Click "Generate Invoice" to create your first invoice.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($invoices->hasPages())
            <div class="px-4 py-3 border-t border-slate-200/50">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Generate Invoice Modal --}}
<div id="generateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 rounded-xl bg-white" style="box-shadow: var(--theme-card-shadow);">
        <div class="px-4 py-3 border-b border-slate-200/50">
            <h3 class="text-base font-medium text-slate-900">Generate Invoice</h3>
        </div>
        <form action="{{ route('invoices.generate') }}" method="POST" id="generateInvoiceForm">
            @csrf
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" id="project_id" required class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <option value="">Select Project</option>
                        @foreach($projects ?? [] as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Period Start <span class="text-red-500">*</span></label>
                    <input type="date" name="period_start" id="period_start" required 
                        value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Period End <span class="text-red-500">*</span></label>
                    <input type="date" name="period_end" id="period_end" required 
                        value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                        class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
                </div>
                
                <div id="generateError" class="hidden bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-sm"></div>
            </div>
            
            <div class="px-4 py-3 border-t border-slate-200/50 flex justify-end space-x-2">
                <button type="button" onclick="closeGenerateModal()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    Cancel
                </button>
                <button type="submit" id="generateBtn" class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                    <span id="generateBtnText">Generate</span>
                    <span id="generateBtnLoader" class="hidden">Generating...</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openGenerateModal() {
    document.getElementById('generateModal').classList.remove('hidden');
    document.getElementById('generateError').classList.add('hidden');
}

function closeGenerateModal() {
    document.getElementById('generateModal').classList.add('hidden');
    document.getElementById('generateError').classList.add('hidden');
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('generateInvoiceForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const projectId = document.getElementById('project_id').value;
            const periodStart = document.getElementById('period_start').value;
            const periodEnd = document.getElementById('period_end').value;
            const errorDiv = document.getElementById('generateError');
            const generateBtn = document.getElementById('generateBtn');
            const btnText = document.getElementById('generateBtnText');
            const btnLoader = document.getElementById('generateBtnLoader');
            
            // Validate inputs
            if (!projectId) {
                e.preventDefault();
                errorDiv.textContent = 'Please select a project';
                errorDiv.classList.remove('hidden');
                return false;
            }
            
            if (!periodStart || !periodEnd) {
                e.preventDefault();
                errorDiv.textContent = 'Please select both start and end dates';
                errorDiv.classList.remove('hidden');
                return false;
            }
            
            if (periodStart > periodEnd) {
                e.preventDefault();
                errorDiv.textContent = 'End date must be after start date';
                errorDiv.classList.remove('hidden');
                return false;
            }
            
            // Show loading state
            generateBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoader.classList.remove('hidden');
            
            // Form will submit normally
            console.log('Submitting form with:', {
                project_id: projectId,
                period_start: periodStart,
                period_end: periodEnd
            });
        });
    }
});

function markAsSent(invoiceId) {
    if (confirm('Mark this invoice as sent?')) {
        fetch(`/invoices/${invoiceId}/status`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: 'sent' })
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Error updating invoice status');
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('Error updating invoice status');
        });
    }
}

function markAsPaid(invoiceId) {
    if (confirm('Mark this invoice as paid?')) {
        fetch(`/invoices/${invoiceId}/status`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: 'paid' })
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Error updating invoice status');
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('Error updating invoice status');
        });
    }
}
</script>
@endpush