@extends('layouts.app')

@section('title', 'Monthly Invoice Overview')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <div class="flex items-center space-x-2 text-sm text-slate-600 mb-1">
                        <a href="{{ route('reports.quick-reports') }}" class="hover:text-slate-900">Quick Reports</a>
                        <span>/</span>
                        <span>Monthly Invoices</span>
                    </div>
                    <h1 class="text-2xl font-semibold text-slate-900">Monthly Invoice Overview</h1>
                    <p class="text-sm text-slate-600 mt-1">{{ $startDate->format('F Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
            <form method="GET" action="{{ route('reports.monthly-invoices') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Select Month</label>
                    <select name="month" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                        @foreach($monthOptions as $value => $label)
                            <option value="{{ $value }}" {{ $month == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all">
                    <i class="fas fa-filter mr-2"></i>
                    Apply Filter
                </button>
            </form>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Total Invoices --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Invoices</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $stats['total_invoices'] }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-blue-600"></i>
                    </div>
                </div>
            </div>

            {{-- Total Amount --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Total Amount</p>
                        <p class="text-xl font-bold text-slate-900 mt-1">€{{ number_format($stats['total_amount'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-euro-sign text-purple-600"></i>
                    </div>
                </div>
            </div>

            {{-- Paid Amount --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Paid</p>
                        <p class="text-xl font-bold text-green-700 mt-1">€{{ number_format($stats['paid_amount'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            {{-- Pending Amount --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Pending</p>
                        <p class="text-xl font-bold text-yellow-700 mt-1">€{{ number_format($stats['pending_amount'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
            </div>

            {{-- Draft Amount --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Draft</p>
                        <p class="text-xl font-bold text-gray-700 mt-1">€{{ number_format($stats['draft_amount'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-gray-600"></i>
                    </div>
                </div>
            </div>

            {{-- Average Invoice --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-600 font-medium">Average</p>
                        <p class="text-xl font-bold text-indigo-700 mt-1">€{{ number_format($stats['average_invoice'], 2) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calculator text-indigo-600"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Breakdown --}}
        <div class="mt-4 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
            <h3 class="text-sm font-medium text-slate-700 mb-3">Status Breakdown</h3>
            <div class="grid grid-cols-4 gap-2">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-600">{{ $stats['by_status']['draft'] }}</p>
                    <p class="text-xs text-slate-600">Draft</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['by_status']['sent'] }}</p>
                    <p class="text-xs text-slate-600">Sent</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $stats['by_status']['paid'] }}</p>
                    <p class="text-xs text-slate-600">Paid</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $stats['by_status']['overdue'] }}</p>
                    <p class="text-xs text-slate-600">Overdue</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice List --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200/60">
                <h2 class="text-lg font-semibold text-slate-900">Invoices for {{ $startDate->format('F Y') }}</h2>
            </div>
            
            @if($invoices->isEmpty())
                <div class="p-8 text-center">
                    <i class="fas fa-file-invoice text-slate-300 text-5xl mb-4"></i>
                    <p class="text-lg font-medium text-slate-900">No invoices found</p>
                    <p class="text-sm text-slate-600 mt-1">There are no invoices for the selected month.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Due Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/50">
                            @foreach($invoices as $invoice)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-slate-900">{{ $invoice->invoice_number }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $invoice->project->customer->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">{{ $invoice->project->customer->company ?? '' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-900">{{ $invoice->project->name ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $invoice->invoice_date->format('d-m-Y') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $invoice->due_date->format('d-m-Y') }}</span>
                                    @if($invoice->status == 'sent' && $invoice->due_date < now())
                                        <span class="ml-1 text-xs text-red-600">(Overdue)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match($invoice->status) {
                                            'draft' => 'bg-gray-100 text-gray-700',
                                            'sent' => 'bg-blue-100 text-blue-700',
                                            'paid' => 'bg-green-100 text-green-700',
                                            'overdue' => 'bg-red-100 text-red-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="font-medium text-slate-900">€{{ number_format($invoice->total_inc_vat, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('invoices.show', $invoice->id) }}" 
                                       class="text-slate-400 hover:text-slate-600 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection