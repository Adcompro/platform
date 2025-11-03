@extends('layouts.app')

@section('title', 'Invoice Details - ' . ($invoice->invoice_number ?: 'DRAFT-' . $invoice->id))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $invoice->invoice_number ?: 'Draft Invoice' }}
                        <span class="ml-3 px-3 py-1 text-sm rounded-full 
                            {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $invoice->status === 'finalized' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $invoice->status === 'sent' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $invoice->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </h1>
                    <p class="text-sm text-gray-600">
                        {{ $invoice->customer->name }}
                        @if($invoice->project)
                        • {{ $invoice->project->name }}
                        @endif
                    </p>
                </div>
                
                <div class="flex items-center space-x-2">
                    <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        ← Back to Invoices
                    </a>
                    
                    @if($invoice->status === 'draft')
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <a href="{{ route('invoices.edit', $invoice) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Invoice
                    </a>
                    @endif
                    @endif

                    @if(in_array($invoice->status, ['finalized', 'sent', 'paid']))
                    <a href="{{ route('invoices.pdf', $invoice) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download PDF
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Preview -->
                <div class="bg-white border border-gray-200 rounded-lg p-6" style="box-shadow: var(--theme-card-shadow);">
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
                    @php
                        $hasLogo = $invoice->template && $invoice->template->logo_path && $invoice->template->logo_position !== 'none';
                        $logoPosition = $hasLogo ? $invoice->template->logo_position : 'none';
                    @endphp
                    
                    {{-- Logo Position: Center --}}
                    @if($hasLogo && $logoPosition === 'center')
                        <div class="text-center mb-4">
                            <img src="{{ url('template-logo/' . str_replace('logos/', '', $invoice->template->logo_path)) }}" 
                                 alt="{{ $invoice->invoicingCompany->name }} Logo" 
                                 class="h-20 mx-auto object-contain">
                        </div>
                    @endif
                    
                    <div class="flex justify-between items-start">
                        <div class="flex items-start space-x-4">
                            {{-- Logo Position: Left --}}
                            @if($hasLogo && $logoPosition === 'left')
                                <div class="flex-shrink-0">
                                    <img src="{{ url('template-logo/' . str_replace('logos/', '', $invoice->template->logo_path)) }}" 
                                         alt="{{ $invoice->invoicingCompany->name }} Logo" 
                                         class="h-16 object-contain">
                                </div>
                            @endif
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ $invoice->invoicingCompany->name }}</h2>
                            @if($invoice->invoicingCompany->street || $invoice->invoicingCompany->postal_code || $invoice->invoicingCompany->city)
                            <div class="mt-2 text-sm text-gray-600">
                                @if($invoice->invoicingCompany->street)
                                    {{ $invoice->invoicingCompany->street }}
                                    @if($invoice->invoicingCompany->house_number){{ ' ' . $invoice->invoicingCompany->house_number }}@endif
                                    @if($invoice->invoicingCompany->addition){{ '-' . $invoice->invoicingCompany->addition }}@endif
                                    <br>
                                @endif
                                @if($invoice->invoicingCompany->postal_code || $invoice->invoicingCompany->city)
                                    {{ $invoice->invoicingCompany->postal_code }}{{ $invoice->invoicingCompany->city ? ' ' . $invoice->invoicingCompany->city : '' }}<br>
                                @endif
                                @if($invoice->invoicingCompany->country && $invoice->invoicingCompany->country !== 'Netherlands')
                                    {{ $invoice->invoicingCompany->country }}
                                @endif
                            </div>
                            @endif
                            @if($invoice->invoicingCompany->vat_number)
                            <div class="mt-1 text-sm text-gray-600">VAT: {{ $invoice->invoicingCompany->vat_number }}</div>
                            @endif
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="text-right">
                                <h3 class="text-xl font-semibold text-gray-900">INVOICE</h3>
                                @if($invoice->invoice_number)
                                <div class="text-lg font-medium text-gray-700"># {{ $invoice->invoice_number }}</div>
                                @else
                                <div class="text-lg font-medium text-gray-500">DRAFT</div>
                                @endif
                            </div>
                            {{-- Logo Position: Right --}}
                            @if($hasLogo && $logoPosition === 'right')
                                <div class="flex-shrink-0">
                                    <img src="{{ url('template-logo/' . str_replace('logos/', '', $invoice->template->logo_path)) }}" 
                                         alt="{{ $invoice->invoicingCompany->name }} Logo" 
                                         class="h-16 object-contain">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6">
                    <!-- Invoice Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Bill To</h4>
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ $invoice->customer->name }}</div>
                                @if($invoice->customer->company)
                                <div class="text-gray-600">{{ $invoice->customer->company }}</div>
                                @endif
                                @if($invoice->customer->address)
                                <div class="text-gray-600 whitespace-pre-line mt-1">{{ $invoice->customer->address }}</div>
                                @endif
                                @if($invoice->customer->vat_number)
                                <div class="text-gray-600 mt-1">VAT: {{ $invoice->customer->vat_number }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Invoice Details</h4>
                            <div class="text-sm space-y-1">
                                @if($invoice->invoice_date)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Invoice Date:</span>
                                    <span class="text-gray-900">{{ $invoice->invoice_date->format('M j, Y') }}</span>
                                </div>
                                @endif
                                @if($invoice->due_date)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Due Date:</span>
                                    <span class="text-gray-900 {{ $invoice->due_date->isPast() && $invoice->status === 'sent' ? 'text-red-600 font-medium' : '' }}">
                                        {{ $invoice->due_date->format('M j, Y') }}
                                    </span>
                                </div>
                                @endif
                                @if($invoice->period_start && $invoice->period_end)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Period:</span>
                                    <span class="text-gray-900">{{ $invoice->period_start->format('M j') }} - {{ $invoice->period_end->format('M j, Y') }}</span>
                                </div>
                                @endif
                                @if($invoice->project)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Project:</span>
                                    <span class="text-gray-900">{{ $invoice->project->name }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Budget Overview -->
                    @if($invoice->monthly_budget > 0 || $invoice->previous_month_remaining > 0)
                    @php
                        // Calculate invoice total ex VAT
                        $workServiceLines = $invoice->lines->whereIn('category', ['work', 'service'])->where('defer_to_next_month', false);
                        $workServiceSubtotal = $workServiceLines->sum('line_total_ex_vat');
                        $billableCosts = $invoice->lines->where('category', 'cost')->where('is_billable', true)->where('defer_to_next_month', false);
                        $billableCostsTotal = $billableCosts->sum('line_total_ex_vat');
                        $invoiceTotalExVat = $workServiceSubtotal + $billableCostsTotal;
                        
                        // Calculate rollover
                        $previousRemaining = $invoice->previous_month_remaining ?? 0;
                        $monthlyBudget = $invoice->monthly_budget ?? 0;
                        $availableBudget = $previousRemaining + $monthlyBudget;
                        $rollover = $availableBudget - $invoiceTotalExVat;
                    @endphp
                    <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Budget Overview</h4>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <!-- Previous Month Remaining -->
                            <div class="bg-white rounded p-3">
                                <div class="text-xs text-gray-600 uppercase">Previous Remaining</div>
                                <div class="text-lg font-bold text-gray-900">€{{ number_format($previousRemaining, 2) }}</div>
                            </div>
                            
                            <!-- Monthly Budget -->
                            <div class="bg-white rounded p-3">
                                <div class="text-xs text-gray-600 uppercase">Monthly Budget</div>
                                <div class="text-lg font-bold text-gray-900">€{{ number_format($monthlyBudget, 2) }}</div>
                            </div>
                            
                            <!-- Invoice Total -->
                            <div class="bg-white rounded p-3">
                                <div class="text-xs text-gray-600 uppercase">Invoice Total</div>
                                <div class="text-lg font-bold text-blue-600">€{{ number_format($invoiceTotalExVat, 2) }}</div>
                            </div>
                            
                            <!-- Available Budget -->
                            <div class="bg-white rounded p-3">
                                <div class="text-xs text-gray-600 uppercase">Available Budget</div>
                                <div class="text-lg font-bold text-gray-900">€{{ number_format($availableBudget, 2) }}</div>
                            </div>
                            
                            <!-- Rollover to Next Month -->
                            <div class="bg-white rounded p-3">
                                <div class="text-xs text-gray-600 uppercase">Rollover to Next</div>
                                <div class="text-lg font-bold {{ $rollover >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    €{{ number_format($rollover, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Additional Costs Section -->
                    @php
                        $additionalCostLines = $invoice->lines->where('category', 'cost');
                        $hasAdditionalCosts = $additionalCostLines->count() > 0;
                    @endphp
                    
                    @if($hasAdditionalCosts)
                    <div class="mb-8">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Additional Costs</h4>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="space-y-2">
                                @foreach($additionalCostLines as $costLine)
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-700">{{ $costLine->description }}</span>
                                        @if($costLine->is_billable)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Additional
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                In Fee
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">€{{ number_format($costLine->line_total_ex_vat ?? 0, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-3 pt-3 border-t border-yellow-300">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-700">Total Additional Costs:</span>
                                    <span class="text-sm font-bold text-gray-900">€{{ number_format($additionalCostLines->sum('line_total_ex_vat'), 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Invoice Lines -->
                    <div class="mb-8">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Invoice Lines</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($invoice->lines->where('category', '!=', 'cost') as $line)
                                    @php
                                        // Determine line type and styling for hierarchical display
                                        $indentClass = '';
                                        $fontClass = 'text-xs font-medium text-gray-900';
                                        $cleanDescription = $line->description;

                                        if ($line->source_type === 'milestone_header') {
                                            // Milestone header - bold, no indent
                                            $fontClass = 'text-sm font-bold text-blue-900';
                                        } elseif (str_starts_with(trim($line->description), '→')) {
                                            // Task header - indented, semibold
                                            $indentClass = 'pl-4';
                                            $fontClass = 'text-xs font-semibold text-green-800';
                                        } elseif (str_starts_with(trim($line->description), '•')) {
                                            // Time entry - more indented, normal weight
                                            $indentClass = 'pl-8';
                                            $fontClass = 'text-xs text-gray-700';
                                        } elseif (str_contains($line->description, '(Milestone Total)')) {
                                            // Milestone total - bold, highlighted
                                            $fontClass = 'text-xs font-bold text-blue-900 bg-blue-50';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="{{ $fontClass }} {{ $indentClass }}">{{ $cleanDescription }}</div>
                                            @if($line->source_type === 'time_entry' && $line->timeEntry)
                                            <div class="text-xs text-gray-500">
                                                {{ $line->timeEntry->user->name }} • {{ $line->timeEntry->date->format('M j, Y') }}
                                                @if($line->timeEntry->projectTask)
                                                • {{ $line->timeEntry->projectTask->title }}
                                                @endif
                                            </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right {{ $fontClass }}">
                                            @if($line->is_billable && $line->quantity > 0)
                                                {{ $line->quantity }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right {{ $fontClass }}">
                                            @if($line->is_billable && $line->line_total_ex_vat > 0)
                                                €{{ number_format($line->line_total_ex_vat ?? 0, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="flex justify-end">
                        <div class="w-full max-w-sm">
                            <div class="space-y-2">
                                @php
                                    // Calculate work/service lines (without additional costs)
                                    $workServiceLines = $invoice->lines->whereIn('category', ['work', 'service'])->where('defer_to_next_month', false);
                                    $workServiceSubtotal = $workServiceLines->sum('line_total_ex_vat');
                                    
                                    // Get billable additional costs
                                    $billableCosts = $invoice->lines->where('category', 'cost')->where('is_billable', true)->where('defer_to_next_month', false);
                                    $billableCostsTotal = $billableCosts->sum('line_total_ex_vat');
                                    
                                    // Total subtotal
                                    $subtotalExVat = $workServiceSubtotal + $billableCostsTotal;
                                    $vatAmount = $subtotalExVat * (($invoice->vat_rate ?? 21) / 100);
                                    $totalIncVat = $subtotalExVat + $vatAmount;
                                @endphp
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Work & Services (ex VAT):</span>
                                    <span class="text-gray-900">€{{ number_format($workServiceSubtotal, 2) }}</span>
                                </div>
                                
                                @if($billableCostsTotal > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Additional Costs (billable):</span>
                                    <span class="text-gray-900">€{{ number_format($billableCostsTotal, 2) }}</span>
                                </div>
                                @endif
                                
                                <div class="flex justify-between text-sm font-medium border-t border-gray-200 pt-2">
                                    <span class="text-gray-700">Subtotal (ex VAT):</span>
                                    <span class="text-gray-900">€{{ number_format($subtotalExVat, 2) }}</span>
                                </div>
                                
                                {{-- VAT display disabled per user request --}}
                                
                                <div class="border-t border-gray-200 pt-2">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span class="text-gray-900">Total Amount:</span>
                                        <span class="text-gray-900">€{{ number_format($subtotalExVat, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Notes -->
                    @if($invoice->notes)
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-2">Notes</h4>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $invoice->notes }}</p>
                    </div>
                    @endif

                    <!-- Payment Information -->
                    @if($invoice->invoicingCompany->bank_details && ($invoice->status === 'sent' || $invoice->status === 'finalized'))
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-2">Payment Information</h4>
                        <div class="text-sm text-gray-600 space-y-1">
                            @if($invoice->invoicingCompany->bank_details['bank_name'] ?? null)
                            <div>Bank: {{ $invoice->invoicingCompany->bank_details['bank_name'] }}</div>
                            @endif
                            @if($invoice->invoicingCompany->bank_details['iban'] ?? null)
                            <div>IBAN: {{ $invoice->invoicingCompany->bank_details['iban'] }}</div>
                            @endif
                            @if($invoice->invoicingCompany->bank_details['bic'] ?? null)
                            <div>BIC: {{ $invoice->invoicingCompany->bank_details['bic'] }}</div>
                            @endif
                            @if($invoice->due_date)
                            <div class="mt-2 font-medium">Please pay within {{ $invoice->due_date->diffInDays(now()) }} days.</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Invoice History -->
            @if($invoice->draftActions->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Invoice History</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($invoice->draftActions->sortByDesc('created_at') as $action)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full 
                                                {{ $action->action_type === 'created' ? 'bg-green-500' : '' }}
                                                {{ $action->action_type === 'updated' ? 'bg-blue-500' : '' }}
                                                {{ $action->action_type === 'line_added' ? 'bg-yellow-500' : '' }}
                                                {{ $action->action_type === 'line_removed' ? 'bg-red-500' : '' }}
                                                {{ $action->action_type === 'finalized' ? 'bg-purple-500' : '' }}
                                                flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    @if($action->action_type === 'created')
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    @else
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                    @endif
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900">{{ $action->user->name }}</span>
                                                    {{ ucwords(str_replace('_', ' ', $action->action_type)) }}
                                                    @if($action->description)
                                                    - {{ $action->description }}
                                                    @endif
                                                </p>
                                                @if($action->details)
                                                <div class="mt-1 text-xs text-gray-400">
                                                    @if(is_array($action->details))
                                                        @foreach($action->details as $key => $value)
                                                            <div>{{ $key }}: {{ $value }}</div>
                                                        @endforeach
                                                    @else
                                                        {{ $action->details }}
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $action->created_at->format('M j, g:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Invoice Status -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Invoice Status</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Status</span>
                        <span class="px-3 py-1 text-sm rounded-full 
                            {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $invoice->status === 'finalized' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $invoice->status === 'sent' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $invoice->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>

                    @if($invoice->invoice_date)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Invoice Date</span>
                        <span class="text-sm text-gray-900">{{ $invoice->invoice_date->format('M j, Y') }}</span>
                    </div>
                    @endif

                    @if($invoice->due_date)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Due Date</span>
                        <span class="text-sm text-gray-900">{{ $invoice->due_date->format('M j, Y') }}</span>
                    </div>
                    @endif

                    @if($invoice->paid_date)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Paid Date</span>
                        <span class="text-sm text-gray-900">{{ $invoice->paid_date->format('M j, Y') }}</span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Total Amount</span>
                        <span class="text-lg font-semibold text-gray-900">€{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>

                    @if($invoice->status === 'sent' && $invoice->due_date && $invoice->due_date->isPast())
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <span class="text-sm font-medium text-red-800">Overdue</span>
                        </div>
                        <p class="mt-1 text-sm text-red-600">
                            Payment was due {{ $invoice->due_date->diffForHumans() }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Project Information -->
            @if($invoice->project)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Project Information</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Project</label>
                        <p class="mt-1">
                            <a href="{{ route('projects.show', $invoice->project) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $invoice->project->name }}
                            </a>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Monthly Fee</label>
                        <p class="mt-1 text-gray-900">€{{ number_format($invoice->project->monthly_fee ?? 0, 0) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Project Status</label>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $invoice->project->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $invoice->project->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $invoice->project->status === 'on_hold' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ ucwords(str_replace('_', ' ', $invoice->project->status)) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Customer Information</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Customer</label>
                        <p class="mt-1">
                            <a href="{{ route('customers.show', $invoice->customer) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $invoice->customer->name }}
                            </a>
                        </p>
                    </div>
                    @if($invoice->customer->email)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1">
                            <a href="mailto:{{ $invoice->customer->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $invoice->customer->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    @if($invoice->customer->vat_number)
                    <div>
                        <label class="text-sm font-medium text-gray-500">VAT Number</label>
                        <p class="mt-1 text-gray-900">{{ $invoice->customer->vat_number }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-2">
                    @if($invoice->status === 'draft')
                    @can('update', $invoice)
                    <a href="{{ route('invoices.edit', $invoice) }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                        ✏️ Edit Invoice
                    </a>
                    @endcan
                    @endif

                    @if(in_array($invoice->status, ['finalized', 'sent', 'paid']))
                    <a href="{{ route('invoices.pdf', $invoice) }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                        📄 Download PDF
                    </a>
                    @endif

                    @if($invoice->customer->email && in_array($invoice->status, ['finalized', 'sent']))
                    <a href="mailto:{{ $invoice->customer->email }}?subject=Invoice {{ $invoice->invoice_number }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                        ✉️ Email Customer
                    </a>
                    @endif

                    @if($invoice->project)
                    <a href="{{ route('projects.show', $invoice->project) }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                        📁 View Project
                    </a>
                    @endif

                    <a href="{{ route('invoices.create', ['customer' => $invoice->customer->id]) }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                        🔄 Create Similar Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modals -->
<!-- Mark as Sent Modal -->
<div id="sentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 rounded-md bg-white" style="box-shadow: var(--theme-card-shadow);">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900">Mark Invoice as Sent</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    This will mark the invoice as sent and set the due date. The invoice can no longer be edited.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmSent" class="px-4 py-2 bg-yellow-600 text-white text-base font-medium rounded-md w-full hover:bg-yellow-700" style="box-shadow: var(--theme-card-shadow);">
                    Mark as Sent
                </button>
                <button onclick="closeSentModal()" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full hover:bg-gray-400" style="box-shadow: var(--theme-card-shadow);">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div id="paidModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 rounded-md bg-white" style="box-shadow: var(--theme-card-shadow);">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900">Mark Invoice as Paid</h3>
            <div class="mt-2 px-7 py-3">
                <label for="paid_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                <input type="date" 
                       id="paid_date" 
                       value="{{ date('Y-m-d') }}"
                       class="block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500" style="box-shadow: var(--theme-card-shadow);">
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmPaid" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-full hover:bg-green-700" style="box-shadow: var(--theme-card-shadow);">
                    Mark as Paid
                </button>
                <button onclick="closePaidModal()" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full hover:bg-gray-400" style="box-shadow: var(--theme-card-shadow);">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentInvoiceId = {{ $invoice->id }};

function markAsSent(invoiceId) {
    document.getElementById('sentModal').classList.remove('hidden');
}

function closeSentModal() {
    document.getElementById('sentModal').classList.add('hidden');
}

function markAsPaid(invoiceId) {
    document.getElementById('paidModal').classList.remove('hidden');
}

function closePaidModal() {
    document.getElementById('paidModal').classList.add('hidden');
}

// Confirm Actions
document.getElementById('confirmSent').addEventListener('click', function() {
    updateInvoiceStatus(currentInvoiceId, 'sent');
});

document.getElementById('confirmPaid').addEventListener('click', function() {
    const paidDate = document.getElementById('paid_date').value;
    updateInvoiceStatus(currentInvoiceId, 'paid', { paid_date: paidDate });
});

function updateInvoiceStatus(invoiceId, status, extraData = {}) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/invoices/${invoiceId}/status`;
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'PATCH';
    
    const tokenField = document.createElement('input');
    tokenField.type = 'hidden';
    tokenField.name = '_token';
    tokenField.value = '{{ csrf_token() }}';
    
    const statusField = document.createElement('input');
    statusField.type = 'hidden';
    statusField.name = 'status';
    statusField.value = status;
    
    form.appendChild(methodField);
    form.appendChild(tokenField);
    form.appendChild(statusField);
    
    // Add extra data
    Object.keys(extraData).forEach(key => {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = key;
        field.value = extraData[key];
        form.appendChild(field);
    });
    
    document.body.appendChild(form);
    form.submit();
}

function confirmDeleteInvoice(invoiceId) {
    if (confirm('Are you sure you want to delete this draft invoice?\n\nThis action cannot be undone.')) {
        // Create form for DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/invoices/${invoiceId}`;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = '{{ csrf_token() }}';
        form.appendChild(csrfField);
        
        // Add DELETE method
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush