@extends('layouts.app')

@section('title', 'Spreadsheet View - Invoice #' . ($invoice->invoice_number ?? $invoice->id))

@section('content')
{{-- Simple Editable Table - No external libraries! --}}
<style>
/* Excel-like table styling */
#spreadsheet-container {
    background: white;
    padding: 0;
    border: 1px solid #c0c0c0;
    overflow-x: auto;
}

#spreadsheet-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
    font-size: 11pt;
    background: white;
}

/* Excel-style header */
#spreadsheet-table thead {
    background: #f2f2f2;
    position: sticky;
    top: 0;
    z-index: 10;
}

#spreadsheet-table th {
    padding: 6px 8px;
    text-align: left;
    font-weight: 700;
    color: #000;
    border: 1px solid #d4d4d4;
    background: #f2f2f2;
    white-space: nowrap;
    font-size: 11pt;
}

#spreadsheet-table tbody tr {
    background: white;
}

/* Zebra striping zoals Excel */
#spreadsheet-table tbody tr:nth-child(even) {
    background: #fafafa;
}

#spreadsheet-table tbody tr:hover {
    background: #e7f3ff !important;
}

#spreadsheet-table td {
    padding: 4px 8px;
    border: 1px solid #d4d4d4;
    background: inherit;
    min-height: 21px;
    line-height: 1.4;
}

/* Editable cells - Excel style */
.editable-cell {
    cursor: cell;
    min-height: 21px;
    background: white;
}

.editable-cell:focus {
    outline: 2px solid #217346;
    outline-offset: -2px;
    background: white;
    border-color: #217346;
}

/* Number cells - right aligned zoals Excel */
.number-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-family: 'Consolas', 'Courier New', monospace;
}

/* Read-only cells - licht grijs zoals Excel */
.readonly-cell {
    background: #f5f5f5;
    color: #000;
    cursor: default;
}

/* Row numbers - Excel style */
.row-number {
    background: #f2f2f2;
    text-align: center;
    font-weight: 700;
    color: #000;
    width: 40px;
    border-right: 2px solid #c0c0c0;
    cursor: default;
    user-select: none;
}

/* Checkbox column */
.checkbox-cell {
    background: #f2f2f2;
    text-align: center;
    width: 35px;
    padding: 4px;
    cursor: pointer;
}

.checkbox-cell input[type="checkbox"] {
    cursor: pointer;
    width: 16px;
    height: 16px;
}

/* Floating action bar */
#floating-merge-bar {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: white;
    border: 2px solid #217346;
    border-radius: 8px;
    padding: 12px 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none;
    align-items: center;
    gap: 16px;
    z-index: 1000;
    transition: transform 0.3s ease;
}

#floating-merge-bar.show {
    transform: translateX(-50%) translateY(0);
    display: flex;
}

#floating-merge-bar .count {
    font-weight: 700;
    color: #217346;
}

#floating-merge-bar button {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

#merge-btn {
    background: #217346;
    color: white;
}

#merge-btn:hover {
    background: #1a5c37;
}

#clear-selection-btn {
    background: #e5e7eb;
    color: #374151;
}

#clear-selection-btn:hover {
    background: #d1d5db;
}

/* Undo Button */
#undo-button {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #f59e0b;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none;
    align-items: center;
    gap: 8px;
    z-index: 1000;
    cursor: pointer;
    border: none;
    font-weight: 600;
    transition: all 0.2s;
}

#undo-button:hover {
    background: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

#undo-button.show {
    display: flex;
}

/* Deferred rows styling */
tr.deferred {
    background: #fef3c7 !important;
    opacity: 0.6;
    position: relative;
}

tr.deferred td {
    text-decoration: line-through;
    color: #92400e;
}

tr.deferred .row-number {
    background: #fbbf24;
}

tr.deferred .row-number::after {
    content: '⏭️';
    margin-left: 4px;
    font-size: 12px;
}

/* Merge Modal */
#merge-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

#merge-modal.show {
    display: flex;
}

#merge-modal .modal-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

#merge-modal h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 16px;
    color: #000;
}

#merge-modal .option {
    margin-bottom: 12px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
}

#merge-modal label {
    display: block;
    font-weight: 600;
    margin-bottom: 4px;
    color: #374151;
}

#merge-modal select {
    width: 100%;
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

#merge-modal .buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

#merge-modal .buttons button {
    flex: 1;
    padding: 10px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    border: none;
}

#merge-modal .btn-cancel {
    background: #e5e7eb;
    color: #374151;
}

#merge-modal .btn-confirm {
    background: #217346;
    color: white;
}

/* Excel grid effect */
#spreadsheet-table {
    border: 1px solid #c0c0c0;
}

/* Scrollbar styling voor Windows/Excel look */
#spreadsheet-container::-webkit-scrollbar {
    height: 17px;
    width: 17px;
}

#spreadsheet-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#spreadsheet-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border: 1px solid #b1b1b1;
}

#spreadsheet-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

#spreadsheet-container::-webkit-scrollbar-corner {
    background: #f1f1f1;
}
</style>

<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white shadow">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        📊 Spreadsheet View
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Invoice {{ $invoice->invoice_number ?? '#'.$invoice->id }} - {{ $invoice->customer->name }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('invoices.edit', $invoice) }}"
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all">
                        ← Back to Normal View
                    </a>
                    <button type="button" onclick="saveSpreadsheet()" id="save-btn"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-all">
                        💾 Save Changes
                    </button>
                    <a href="{{ route('invoices.finalize-view', $invoice) }}"
                       class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-all">
                        📄 Preview Finalize →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                {{ session('error') }}
            </div>
        @endif

        {{-- Help Info --}}
        <div class="mb-4 bg-blue-50 border border-blue-200 px-4 py-3 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Simple spreadsheet editing - No complex libraries!</h3>
                    <div class="mt-1 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Milestone & Task columns</strong> are read-only (show project structure)</li>
                            <li><strong>Click Description, Quantity or Price cells</strong> to edit values instantly</li>
                            <li><strong>Press Enter</strong> to move to the next row in the same column</li>
                            <li><strong>Total automatically updates</strong> when you change Quantity or Price</li>
                            <li><strong>Numbers auto-format</strong> to 2 decimals when you click away</li>
                            <li><strong>Select checkboxes and click Merge</strong> to combine multiple rows</li>
                            <li><strong>Undo button appears</strong> after merge - click to restore deleted rows</li>
                            <li><strong>Select rows and click "Defer to Next Month"</strong> to postpone billing (yellow highlight)</li>
                            <li><strong>Select deferred rows again</strong> and click button to undo defer (toggle)</li>
                            <li><strong>Click "Save Changes"</strong> when you're done editing!</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget Overview Spreadsheet - Excel-style table --}}
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg">
                    💰 Budget Overview
                </span>
                <span class="ml-3 text-sm font-normal text-gray-600">
                    ({{ $invoice->project->name ?? 'Project' }} - {{ date('F Y', strtotime($invoice->invoice_date)) }})
                </span>
            </h2>

            {{-- Budget Overview Table --}}
            <div style="background: white; padding: 0; border: 1px solid #c0c0c0; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-family: 'Calibri', 'Segoe UI', Arial, sans-serif; font-size: 11pt; background: white;">
                    <thead style="background: #f2f2f2; position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="padding: 6px 12px; text-align: left; font-weight: 700; color: #000; border: 1px solid #d4d4d4; background: #4472C4; color: white; width: 40%;">Budget Item</th>
                            <th style="padding: 6px 12px; text-align: right; font-weight: 700; color: #000; border: 1px solid #d4d4d4; background: #4472C4; color: white; width: 20%;">Amount (€)</th>
                            <th style="padding: 6px 12px; text-align: left; font-weight: 700; color: #000; border: 1px solid #d4d4d4; background: #4472C4; color: white; width: 40%;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Previous Month Remaining --}}
                        <tr style="background: #E7E6E6;">
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; font-weight: 600;">
                                📊 Previous Month Remaining
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; font-weight: 600; color: {{ ($invoice->previous_month_remaining ?? 0) >= 0 ? '#008000' : '#C00000' }};">
                                €{{ number_format($invoice->previous_month_remaining ?? 0, 2, '.', ',') }}
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #666;">
                                Rollover from previous billing period
                            </td>
                        </tr>

                        {{-- Monthly Budget --}}
                        <tr style="background: white;">
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; font-weight: 600;">
                                💵 Monthly Budget
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; font-weight: 600; color: #0070C0;">
                                €{{ number_format($invoice->monthly_budget ?? 0, 2, '.', ',') }}
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #666;">
                                Fixed monthly fee for this period
                            </td>
                        </tr>

                        {{-- Total Available --}}
                        <tr style="background: #DDEEFB; font-weight: 700;">
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; font-weight: 700;">
                                💰 Total Available Budget
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; font-weight: 700; color: #0070C0; font-size: 12pt;">
                                €{{ number_format($invoice->total_budget ?? 0, 2, '.', ',') }}
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #333;">
                                Previous remaining + Monthly budget
                            </td>
                        </tr>

                        {{-- Empty row for spacing --}}
                        <tr style="height: 10px; background: #F9F9F9;">
                            <td colspan="3" style="border: none;"></td>
                        </tr>

                        {{-- Work Amount --}}
                        <tr style="background: white;">
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4;">
                                &nbsp;&nbsp;&nbsp;⏱️ Work (Time Entries)
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; color: #C00000;">
                                -€{{ number_format($invoice->work_amount ?? 0, 2, '.', ',') }}
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #666;">
                                Billable hours within budget
                            </td>
                        </tr>

                        {{-- Service Amount --}}
                        <tr style="background: #E7E6E6;">
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4;">
                                &nbsp;&nbsp;&nbsp;📦 Service Packages
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; color: #C00000;">
                                -€{{ number_format($invoice->service_amount ?? 0, 2, '.', ',') }}
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #666;">
                                Fixed-price services within budget
                            </td>
                        </tr>

                        {{-- Total Used from Budget --}}
                        <tr style="background: #FFE699; font-weight: 600;">
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; font-weight: 600;">
                                📊 Total Used from Budget
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; font-weight: 600; color: #C00000;">
                                -€{{ number_format(($invoice->work_amount ?? 0) + ($invoice->service_amount ?? 0), 2, '.', ',') }}
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #333;">
                                Sum of work + services from budget
                            </td>
                        </tr>

                        {{-- Empty row for spacing --}}
                        <tr style="height: 10px; background: #F9F9F9;">
                            <td colspan="3" style="border: none;"></td>
                        </tr>

                        {{-- Additional Costs (Extra) --}}
                        <tr style="background: #FCE4D6; border-top: 2px dashed #FF9900;">
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #FF6600; font-weight: 600;">
                                &nbsp;&nbsp;&nbsp;💳 Additional Costs (Extra)
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; color: #FF6600; font-weight: 600;">
                                +€{{ number_format($invoice->additional_costs ?? 0, 2, '.', ',') }}
                            </td>
                            <td style="padding: 6px 12px; border: 1px solid #d4d4d4; color: #666;">
                                Extra costs billed separately (NOT from budget)
                            </td>
                        </tr>

                        {{-- Empty row for spacing --}}
                        <tr style="height: 10px; background: #F9F9F9;">
                            <td colspan="3" style="border: none;"></td>
                        </tr>

                        {{-- Rollover to Next Month --}}
                        @php
                            $rollover = $invoice->next_month_rollover ?? 0;
                            $isPositive = $rollover >= 0;
                        @endphp
                        <tr style="background: {{ $isPositive ? '#C6E0B4' : '#F4CCCC' }}; font-weight: 700; border: 2px solid {{ $isPositive ? '#70AD47' : '#CC0000' }};">
                            <td style="padding: 8px 12px; border: 1px solid #d4d4d4; font-weight: 700;">
                                {{ $isPositive ? '➡️ Rollover to Next Month' : '⚠️ Budget Shortage' }}
                            </td>
                            <td style="padding: 8px 12px; border: 1px solid #d4d4d4; text-align: right; font-family: 'Consolas', monospace; font-weight: 700; color: {{ $isPositive ? '#008000' : '#C00000' }}; font-size: 13pt;">
                                €{{ number_format(abs($rollover), 2, '.', ',') }}
                            </td>
                            <td style="padding: 8px 12px; border: 1px solid #d4d4d4; color: #333; font-weight: 600;">
                                {{ $isPositive ? 'Available budget for next period' : 'Budget exceeded - requires additional payment' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Additional Costs Spreadsheet - Separate section above time entries --}}
        @if(count($costsData) > 0)
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-lg">
                    💰 Additional Costs
                </span>
                <span class="ml-3 text-sm font-normal text-gray-600">
                    ({{ count($costsData) }} items)
                </span>
            </h2>

            <div id="costs-spreadsheet-container" style="background: white; padding: 0; border: 1px solid #c0c0c0; overflow-x: auto;">
                <table id="costs-spreadsheet-table" style="width: 100%; border-collapse: collapse; font-family: 'Calibri', 'Segoe UI', Arial, sans-serif; font-size: 11pt; background: white;">
                    <thead style="background: #f2f2f2; position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th class="checkbox-cell" style="width: 35px;">
                                <input type="checkbox" id="costs-select-all" title="Select all costs">
                            </th>
                            <th style="width: 30px; text-align: center;" title="Drag to reorder">⋮⋮</th>
                            <th class="row-number" style="width: 40px;">#</th>
                            <th style="width: 35%;">Description</th>
                            <th style="width: 12%;">Fee Type</th>
                            <th style="width: 12%;">Quantity</th>
                            <th style="width: 13%;">Unit Price (€)</th>
                            <th style="width: 13%;">Total (€)</th>
                        </tr>
                    </thead>
                    <tbody id="costs-spreadsheet-body">
                        @foreach($costsData as $index => $cost)
                        <tr data-cost-id="{{ $cost['id'] }}" data-row="{{ $index }}" class="{{ $cost['defer_to_next_month'] ? 'deferred' : '' }}">
                            <td class="checkbox-cell" style="background: #f2f2f2; text-align: center; width: 35px; padding: 4px; border: 1px solid #d4d4d4;">
                                <input type="checkbox" class="cost-row-checkbox" value="{{ $cost['id'] }}">
                            </td>
                            <td class="drag-handle-cost" style="cursor: move; text-align: center; user-select: none; color: #999; background: #f2f2f2; border: 1px solid #d4d4d4;">
                                ⋮⋮
                            </td>
                            <td class="row-number" style="background: #f2f2f2; text-align: center; font-weight: 700; color: #000; width: 40px; border: 1px solid #d4d4d4; border-right: 2px solid #c0c0c0;">{{ $index + 1 }}</td>
                            <td class="editable-cell" style="padding: 4px 8px; border: 1px solid #d4d4d4;"
                                contenteditable="true"
                                data-field="description"
                                onkeydown="if(event.key==='Enter'){event.preventDefault(); moveToNextCostCell(this, 'right');}">{{ $cost['description'] }}</td>
                            <td class="readonly-cell" style="background: #f5f5f5; text-align: center; padding: 4px 8px; border: 1px solid #d4d4d4;">
                                @if($cost['fee_type'] === 'in_fee')
                                    <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 10pt; font-weight: 600;">In Fee</span>
                                @else
                                    <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 10pt; font-weight: 600;">Additional</span>
                                @endif
                            </td>
                            <td class="editable-cell number-cell" style="text-align: right; padding: 4px 8px; border: 1px solid #d4d4d4;"
                                contenteditable="true"
                                data-field="quantity"
                                onkeydown="if(event.key==='Enter'){event.preventDefault(); moveToNextCostCell(this, 'down');}"
                                onblur="formatCostNumber(this); recalculateCostTotal(this.closest('tr'));">{{ number_format($cost['quantity'], 2, '.', '') }}</td>
                            <td class="editable-cell number-cell" style="text-align: right; padding: 4px 8px; border: 1px solid #d4d4d4;"
                                contenteditable="true"
                                data-field="price"
                                onkeydown="if(event.key==='Enter'){event.preventDefault(); moveToNextCostCell(this, 'down');}"
                                onblur="formatCostNumber(this); recalculateCostTotal(this.closest('tr'));">{{ number_format($cost['price'], 2, '.', '') }}</td>
                            <td class="readonly-cell number-cell" style="background: #f5f5f5; text-align: right; padding: 4px 8px; border: 1px solid #d4d4d4;">{{ number_format($cost['total'], 2, '.', '') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Time Entries Spreadsheet - Original section --}}
        <div>
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg">
                    ⏱️ Time Entries & Work
                </span>
                <span class="ml-3 text-sm font-normal text-gray-600">
                    ({{ count($linesData) }} items)
                </span>
            </h2>
        </div>

        {{-- Spreadsheet Container --}}
        <div id="spreadsheet-container">
            <table id="spreadsheet-table">
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" id="select-all" title="Select all">
                        </th>
                        <th style="width: 30px; text-align: center;" title="Drag to reorder">⋮⋮</th>
                        <th class="row-number">#</th>
                        <th style="width: 18%;">Milestone</th>
                        <th style="width: 18%;">Task</th>
                        <th style="width: 25%;">Description</th>
                        <th style="width: 10%;">Quantity</th>
                        <th style="width: 12%;">Price (€)</th>
                        <th style="width: 12%;">Total (€)</th>
                    </tr>
                </thead>
                <tbody id="spreadsheet-body">
                    @foreach($linesData as $index => $line)
                    <tr data-line-id="{{ $line['id'] }}" data-row="{{ $index }}" class="{{ $line['defer_to_next_month'] ? 'deferred' : '' }}">
                        <td class="checkbox-cell">
                            <input type="checkbox" class="row-checkbox" value="{{ $line['id'] }}">
                        </td>
                        <td class="drag-handle" style="cursor: move; text-align: center; user-select: none; color: #999;">
                            ⋮⋮
                        </td>
                        <td class="row-number">{{ $index + 1 }}</td>
                        <td class="readonly-cell">{{ $line['milestone'] }}</td>
                        <td class="readonly-cell">{{ $line['task'] }}</td>
                        <td class="editable-cell"
                            contenteditable="true"
                            data-field="description">{{ $line['description'] }}</td>
                        <td class="editable-cell number-cell"
                            contenteditable="true"
                            data-field="quantity">{{ number_format($line['quantity'], 2, '.', '') }}</td>
                        <td class="editable-cell number-cell"
                            contenteditable="true"
                            data-field="price">{{ number_format($line['price'], 2, '.', '') }}</td>
                        <td class="readonly-cell number-cell"
                            data-field="total">{{ number_format($line['total'], 2, '.', '') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Action Buttons Below --}}
        <div class="mt-6 flex justify-between items-center">
            <a href="{{ route('invoices.edit', $invoice) }}"
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-all">
                ← Back to Normal View
            </a>
            <button type="button" onclick="saveSpreadsheet()"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
                💾 Save All Changes
            </button>
        </div>
    </div>
</div>

{{-- Floating Merge Action Bar --}}
<div id="floating-merge-bar">
    <span class="count"><span id="selected-count">0</span> rows selected</span>
    <button id="insert-row-btn" onclick="insertRowBelow()" style="background: #10b981; color:white;">
        ➕ Insert Row Below
    </button>
    <button id="merge-btn" onclick="openMergeModal()">
        🔗 Merge Rows
    </button>
    <button id="defer-btn" onclick="deferToNextMonth()" style="color:white;">
        ⏭️ Defer to Next Month
    </button>
    <button id="delete-btn" onclick="deleteSelectedRows()" style="background: #ef4444; color:white;">
        🗑️ Delete Rows
    </button>
    <button id="restore-btn" onclick="restoreSelectedRows()" style="background: #10b981; color:white;">
        ↩️ Restore Rows
    </button>
    <button id="clear-selection-btn" onclick="clearSelection()">
        ✕ Clear
    </button>
</div>

{{-- Undo Merge Button --}}
<button id="undo-button" onclick="undoLastMerge()">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
    </svg>
    Undo Merge
</button>


{{-- Merge Modal --}}
<div id="merge-modal">
    <div class="modal-content">
        <h3>🔗 Merge Selected Rows</h3>

        <div class="option">
            <label>Description:</label>
            <select id="merge-description">
                <option value="combine">Combine all descriptions</option>
                <option value="first">Keep first row description</option>
                <option value="custom">Custom description</option>
            </select>
            <input type="text" id="custom-description" placeholder="Enter custom description"
                   style="display:none; width:100%; margin-top:8px; padding:8px; border:1px solid #d1d5db; border-radius:6px;">
        </div>

        <div class="option">
            <label>Quantity:</label>
            <select id="merge-quantity">
                <option value="sum">Sum all quantities</option>
                <option value="first">Keep first row quantity</option>
                <option value="average">Average of quantities</option>
            </select>
        </div>

        <div class="option">
            <label>Price:</label>
            <select id="merge-price">
                <option value="first">Keep first row price</option>
                <option value="average">Weighted average price</option>
                <option value="highest">Highest price</option>
                <option value="lowest">Lowest price</option>
            </select>
        </div>

        <div style="background:#fef3c7; padding:12px; border-radius:6px; font-size:13px; margin-top:12px;">
            <strong>Note:</strong> Milestone and Task will be taken from the first selected row.
            Other selected rows will be deleted after merge.
        </div>

        <div class="buttons">
            <button class="btn-cancel" onclick="closeMergeModal()">Cancel</button>
            <button class="btn-confirm" onclick="confirmMerge()">Merge Rows</button>
        </div>
    </div>
</div>

{{-- Sortable.js CDN for drag & drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

{{-- Simple Editable Table - Pure JavaScript --}}
<script>
// Global merge history object
let mergeHistory = null;

// ===========================================
// COSTS SPREADSHEET FUNCTIONS
// ===========================================

// Format cost number to 2 decimals
function formatCostNumber(cell) {
    const value = parseFloat(cell.textContent.replace(',', '.')) || 0;
    cell.textContent = value.toFixed(2);
}

// Recalculate cost row total
function recalculateCostTotal(row) {
    const quantityCell = row.querySelector('[data-field="quantity"]');
    const priceCell = row.querySelector('[data-field="price"]');
    const totalCell = row.querySelector('.number-cell:last-child');

    const quantity = parseFloat(quantityCell.textContent.replace(',', '.')) || 0;
    const price = parseFloat(priceCell.textContent.replace(',', '.')) || 0;
    const total = quantity * price;

    totalCell.textContent = total.toFixed(2);
}

// Navigate to next cost cell (Enter key)
function moveToNextCostCell(currentCell, direction) {
    const row = currentCell.closest('tr');

    if (direction === 'down') {
        const nextRow = row.nextElementSibling;
        if (nextRow) {
            const field = currentCell.dataset.field;
            const nextCell = nextRow.querySelector(`[data-field="${field}"]`);
            if (nextCell) {
                nextCell.focus();
            }
        }
    } else if (direction === 'right') {
        // Move from description to quantity on same row
        const field = currentCell.dataset.field;
        if (field === 'description') {
            const nextCell = row.querySelector('[data-field="quantity"]');
            if (nextCell) {
                nextCell.focus();
            }
        }
    }
}

// ===========================================
// MAIN INITIALIZATION
// ===========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing editable spreadsheet...');

    // Get all editable cells
    const editableCells = document.querySelectorAll('.editable-cell');

    // Add input event listeners for auto-calculation
    editableCells.forEach(cell => {
        cell.addEventListener('input', function() {
            const field = this.dataset.field;

            // Only recalculate if quantity or price changed
            if (field === 'quantity' || field === 'price') {
                recalculateRow(this.closest('tr'));
            }
        });

        // Format numbers on blur
        cell.addEventListener('blur', function() {
            const field = this.dataset.field;

            if (field === 'quantity' || field === 'price') {
                const value = parseFloat(this.textContent) || 0;
                this.textContent = value.toFixed(2);
            }
        });

        // Select all on focus for easier editing
        cell.addEventListener('focus', function() {
            const range = document.createRange();
            range.selectNodeContents(this);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        });

        // Prevent Enter key from creating new lines
        cell.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Move to next row, same column
                const row = this.closest('tr');
                const nextRow = row.nextElementSibling;
                if (nextRow) {
                    const field = this.dataset.field;
                    const nextCell = nextRow.querySelector(`[data-field="${field}"]`);
                    if (nextCell) {
                        nextCell.focus();
                    }
                }
            }
        });
    });

    // ========================================
    // CHECKBOX SELECTION & MERGE FUNCTIONALITY
    // ========================================

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const floatingBar = document.getElementById('floating-merge-bar');
    const selectedCountSpan = document.getElementById('selected-count');

    selectAllCheckbox.addEventListener('change', function() {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateFloatingBar();
    });

    // Individual checkboxes
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateFloatingBar();
        });
    });

    function updateSelectAllState() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === rowCheckboxes.length && checkedCount > 0;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
    }

    // COSTS CHECKBOX FUNCTIONALITY
    const costsSelectAllCheckbox = document.getElementById('costs-select-all');
    const costRowCheckboxes = document.querySelectorAll('.cost-row-checkbox');

    if (costsSelectAllCheckbox && costRowCheckboxes.length > 0) {
        costsSelectAllCheckbox.addEventListener('change', function() {
            costRowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            // BELANGRIJK: Update floating bar ook bij select-all voor costs
            updateFloatingBar();
        });

        costRowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.cost-row-checkbox:checked').length;
                costsSelectAllCheckbox.checked = checkedCount === costRowCheckboxes.length && checkedCount > 0;
                costsSelectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < costRowCheckboxes.length;

                // BELANGRIJK: Update floating bar ook voor cost checkboxes!
                updateFloatingBar();
            });
        });
    }

    function updateFloatingBar() {
        // Check BEIDE types checkboxes: costs EN time entries
        const checkedCostCheckboxes = document.querySelectorAll('.cost-row-checkbox:checked');
        const checkedTimeEntryCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        const totalCheckedCount = checkedCostCheckboxes.length + checkedTimeEntryCheckboxes.length;

        selectedCountSpan.textContent = totalCheckedCount;

        if (totalCheckedCount > 0) {
            floatingBar.classList.add('show');

            // Update defer button tekst op basis van de eerste geselecteerde rij (costs eerst, dan time entries)
            const firstRow = (checkedCostCheckboxes.length > 0 ? checkedCostCheckboxes[0] : checkedTimeEntryCheckboxes[0]).closest('tr');
            const isDeferred = firstRow.classList.contains('deferred');
            const deferBtn = document.getElementById('defer-btn');

            if (isDeferred) {
                deferBtn.innerHTML = '↩️ Undo Defer';
                deferBtn.style.background = '#10b981'; // Groen voor undo
            } else {
                deferBtn.innerHTML = '⏭️ Defer to Next Month';
                deferBtn.style.background = '#f59e0b'; // Oranje voor defer
            }
        } else {
            floatingBar.classList.remove('show');
        }
    }

    // Custom description toggle
    document.getElementById('merge-description').addEventListener('change', function() {
        const customInput = document.getElementById('custom-description');
        if (this.value === 'custom') {
            customInput.style.display = 'block';
        } else {
            customInput.style.display = 'none';
        }
    });

    console.log('Spreadsheet initialized successfully');
});

/**
 * Recalculate total for a row
 */
function recalculateRow(row) {
    const qtyCell = row.querySelector('[data-field="quantity"]');
    const priceCell = row.querySelector('[data-field="price"]');
    const totalCell = row.querySelector('[data-field="total"]');

    const qty = parseFloat(qtyCell.textContent) || 0;
    const price = parseFloat(priceCell.textContent) || 0;
    const total = qty * price;

    totalCell.textContent = total.toFixed(2);
}

/**
 * Save spreadsheet data back to server
 */
function saveSpreadsheet() {
    // Get all rows from table
    const rows = document.querySelectorAll('#spreadsheet-body tr');
    const lines = [];
    const deletedLineIds = []; // Voor verwijderde time entry lines

    // Validate and collect data
    let hasErrors = false;

    rows.forEach((row, index) => {
        const lineId = row.dataset.lineId;
        const isDeleted = row.dataset.deleted === 'true';

        // Als rij deleted is, voeg ID toe aan deleted lijst en skip verdere processing
        if (isDeleted) {
            deletedLineIds.push(parseInt(lineId));
            return; // Skip deze rij
        }

        const descriptionCell = row.querySelector('[data-field="description"]');
        const quantityCell = row.querySelector('[data-field="quantity"]');
        const priceCell = row.querySelector('[data-field="price"]');

        // BELANGRIJK: Ook milestone en task ophalen voor reconstructie in backend
        // Na toevoegen drag handle: Checkbox(1), DragHandle(2), RowNum(3), Milestone(4), Task(5)
        const milestoneCell = row.querySelector('.readonly-cell:nth-child(4)'); // Milestone kolom (was 3, nu 4)
        const taskCell = row.querySelector('.readonly-cell:nth-child(5)'); // Task kolom (was 4, nu 5)

        const milestone = milestoneCell ? milestoneCell.textContent.trim() : '';
        const task = taskCell ? taskCell.textContent.trim() : '';
        const description = descriptionCell.textContent.trim();
        const quantity = parseFloat(quantityCell.textContent) || 0;
        const price = parseFloat(priceCell.textContent) || 0;

        // Validation - description is optioneel als milestone of task aanwezig is
        if (!description && !milestone && !task) {
            alert(`Row ${index + 1}: Description, milestone or task must be provided`);
            hasErrors = true;
            return;
        }
        if (quantity < 0) {
            alert(`Row ${index + 1}: Quantity cannot be negative`);
            hasErrors = true;
            return;
        }
        if (price < 0) {
            alert(`Row ${index + 1}: Price cannot be negative`);
            hasErrors = true;
            return;
        }

        // Check if row is deferred to next month
        const isDeferred = row.classList.contains('deferred');

        lines.push({
            id: parseInt(lineId),
            milestone: milestone, // NIEUW: Voor reconstructie in backend
            task: task, // NIEUW: Voor reconstructie in backend
            description: description,
            quantity: quantity,
            unit_price: price,
            defer_to_next_month: isDeferred
        });
    });

    if (hasErrors) return;

    // Collect costs data (existing and new)
    const costsRows = document.querySelectorAll('#costs-spreadsheet-body tr');
    const costs = [];
    const newCosts = [];
    const deletedCostIds = []; // Voor verwijderde costs

    costsRows.forEach((row, index) => {
        const costId = row.dataset.costId;
        const isNew = row.dataset.isNew === 'true';
        const isDeleted = row.dataset.deleted === 'true';

        console.log(`Cost row ${index}:`, {
            costId: costId,
            isNew: isNew,
            isDeleted: isDeleted,
            hasIsNewAttr: row.hasAttribute('data-is-new'),
            dataIsNewValue: row.getAttribute('data-is-new')
        });

        // Als cost rij deleted is, voeg ID toe aan deleted lijst en skip
        if (isDeleted && !isNew) {
            deletedCostIds.push(parseInt(costId));
            return; // Skip deze rij
        }

        // Als het een nieuwe EN deleted rij is, gewoon skippen (was al verwijderd uit DOM)
        if (isDeleted && isNew) {
            return;
        }
        const descriptionCell = row.querySelector('[data-field="description"]');
        const quantityCell = row.querySelector('[data-field="quantity"]');
        const priceCell = row.querySelector('[data-field="price"]');

        console.log(`Cost row ${index} cells:`, {
            descriptionCell: descriptionCell ? descriptionCell.textContent : 'NULL',
            quantityCell: quantityCell ? quantityCell.textContent : 'NULL',
            priceCell: priceCell ? priceCell.textContent : 'NULL'
        });

        const description = descriptionCell.textContent.trim();
        const quantity = parseFloat(quantityCell.textContent) || 0;
        const price = parseFloat(priceCell.textContent) || 0;

        // Check if cost is deferred to next month
        const isDeferred = row.classList.contains('deferred');

        // Get fee type (in_fee or additional) voor nieuwe regels
        let feeType = 'in_fee'; // Default
        const feeTypeCell = row.querySelector('.fee-type-cell');
        if (feeTypeCell) {
            const select = feeTypeCell.querySelector('select');
            if (select) {
                // Voor nieuwe regels met select dropdown
                feeType = select.value;
            } else {
                // Voor bestaande regels, check data attribuut of badge tekst
                feeType = feeTypeCell.dataset.feeType || 'in_fee';
            }
        }

        const costData = {
            description: description,
            quantity: quantity,
            unit_price: price,
            defer_to_next_month: isDeferred,
            fee_type: feeType  // BELANGRIJK: Voeg fee_type toe
        };

        if (isNew) {
            // New cost row - will be created as new InvoiceLine
            newCosts.push(costData);
        } else {
            // Existing cost row - will be updated
            costData.id = parseInt(costId);
            costs.push(costData);
        }
    });

    console.log('Saving time entries:', lines);
    console.log('Saving costs:', costs);
    console.log('New costs to create:', newCosts);
    console.log('Deleted line IDs:', deletedLineIds);
    console.log('Deleted cost IDs:', deletedCostIds);

    // Disable save button
    const saveBtn = document.getElementById('save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    // Prepare form data
    const formData = {
        _token: '{{ csrf_token() }}',
        lines: lines,
        costs: costs,
        new_costs: newCosts,
        deleted_line_ids: deletedLineIds,     // IDs van verwijderde time entries
        deleted_cost_ids: deletedCostIds      // IDs van verwijderde costs
    };

    // Submit via fetch
    fetch('{{ route("invoices.update-spreadsheet", $invoice) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to normal edit view with success message
            window.location.href = '{{ route("invoices.edit", $invoice) }}?saved=1';
        } else {
            alert('Error: ' + (data.message || 'Failed to save changes'));
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('Error saving changes: ' + error.message);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

// Keyboard shortcut: Ctrl+S to save
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveSpreadsheet();
    }
});

// ========================================
// MERGE FUNCTIONALITY
// ========================================

function openMergeModal() {
    const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
    if (selectedCount < 2) {
        alert('Please select at least 2 rows to merge.');
        return;
    }
    document.getElementById('merge-modal').classList.add('show');
}

function closeMergeModal() {
    document.getElementById('merge-modal').classList.remove('show');
}

function clearSelection() {
    // Clear BEIDE types checkboxes
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.cost-row-checkbox').forEach(cb => cb.checked = false);

    // Clear select-all voor beide tabellen
    document.getElementById('select-all').checked = false;
    const costsSelectAll = document.getElementById('costs-select-all');
    if (costsSelectAll) costsSelectAll.checked = false;

    document.getElementById('floating-merge-bar').classList.remove('show');
}

function confirmMerge() {
    const selectedCheckboxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));

    if (selectedCheckboxes.length < 2) {
        alert('Please select at least 2 rows to merge.');
        return;
    }

    // Get selected rows data
    const selectedRows = selectedCheckboxes.map(checkbox => {
        const row = checkbox.closest('tr');
        const lineId = parseInt(row.dataset.lineId);
        const description = row.querySelector('[data-field="description"]').textContent.trim();
        const quantity = parseFloat(row.querySelector('[data-field="quantity"]').textContent) || 0;
        const price = parseFloat(row.querySelector('[data-field="price"]').textContent) || 0;
        const milestone = row.querySelectorAll('.readonly-cell')[0].textContent.trim();
        const task = row.querySelectorAll('.readonly-cell')[1].textContent.trim();

        return { lineId, description, quantity, price, milestone, task, row };
    });

    // Get merge options
    const descOption = document.getElementById('merge-description').value;
    const qtyOption = document.getElementById('merge-quantity').value;
    const priceOption = document.getElementById('merge-price').value;

    // Calculate merged values
    let mergedDescription = '';
    let mergedQuantity = 0;
    let mergedPrice = 0;

    // Description logic
    if (descOption === 'combine') {
        mergedDescription = selectedRows.map(r => r.description).filter(d => d).join('; ');
    } else if (descOption === 'first') {
        mergedDescription = selectedRows[0].description;
    } else if (descOption === 'custom') {
        mergedDescription = document.getElementById('custom-description').value.trim();
        if (!mergedDescription) {
            alert('Please enter a custom description.');
            return;
        }
    }

    // Quantity logic
    if (qtyOption === 'sum') {
        mergedQuantity = selectedRows.reduce((sum, r) => sum + r.quantity, 0);
    } else if (qtyOption === 'first') {
        mergedQuantity = selectedRows[0].quantity;
    } else if (qtyOption === 'average') {
        mergedQuantity = selectedRows.reduce((sum, r) => sum + r.quantity, 0) / selectedRows.length;
    }

    // Price logic
    if (priceOption === 'first') {
        mergedPrice = selectedRows[0].price;
    } else if (priceOption === 'average') {
        // Weighted average by quantity
        const totalQty = selectedRows.reduce((sum, r) => sum + r.quantity, 0);
        if (totalQty > 0) {
            mergedPrice = selectedRows.reduce((sum, r) => sum + (r.price * r.quantity), 0) / totalQty;
        } else {
            mergedPrice = selectedRows.reduce((sum, r) => sum + r.price, 0) / selectedRows.length;
        }
    } else if (priceOption === 'highest') {
        mergedPrice = Math.max(...selectedRows.map(r => r.price));
    } else if (priceOption === 'lowest') {
        mergedPrice = Math.min(...selectedRows.map(r => r.price));
    }

    // Save pre-merge state for undo
    const firstRow = selectedRows[0].row;
    const tbody = document.getElementById('spreadsheet-body');
    const allRows = Array.from(tbody.querySelectorAll('tr'));

    mergeHistory = {
        firstRow: firstRow,
        firstRowIndex: allRows.indexOf(firstRow),
        originalFirstRowData: {
            lineId: selectedRows[0].lineId,
            description: selectedRows[0].description,
            quantity: selectedRows[0].quantity,
            price: selectedRows[0].price,
            milestone: selectedRows[0].milestone,
            task: selectedRows[0].task
        },
        deletedRows: []
    };

    // Store deleted rows with their HTML and ORIGINAL INDEX positions
    for (let i = 1; i < selectedRows.length; i++) {
        const row = selectedRows[i].row;
        const originalIndex = allRows.indexOf(row);
        mergeHistory.deletedRows.push({
            html: row.outerHTML,
            originalIndex: originalIndex, // Sla de originele index op
            lineId: selectedRows[i].lineId
        });
    }

    // Update first row with merged data
    firstRow.querySelector('[data-field="description"]').textContent = mergedDescription;
    firstRow.querySelector('[data-field="quantity"]').textContent = mergedQuantity.toFixed(2);
    firstRow.querySelector('[data-field="price"]').textContent = mergedPrice.toFixed(2);

    // Recalculate total
    recalculateRow(firstRow);

    // Remove other selected rows from DOM
    for (let i = 1; i < selectedRows.length; i++) {
        selectedRows[i].row.remove();
    }

    // Close modal and clear selection
    closeMergeModal();
    clearSelection();

    // Renumber rows
    renumberRows();

    // Show undo button
    document.getElementById('undo-button').classList.add('show');

    // Show success message
    console.log(`Merged ${selectedRows.length} rows. Undo available.`);
}

/**
 * Undo the last merge action
 */
function undoLastMerge() {
    if (!mergeHistory) {
        alert('No merge action to undo.');
        return;
    }

    // Restore first row to original values
    const firstRow = mergeHistory.firstRow;
    const original = mergeHistory.originalFirstRowData;

    firstRow.querySelector('[data-field="description"]').textContent = original.description;
    firstRow.querySelector('[data-field="quantity"]').textContent = original.quantity.toFixed(2);
    firstRow.querySelector('[data-field="price"]').textContent = original.price.toFixed(2);
    recalculateRow(firstRow);

    const tbody = document.getElementById('spreadsheet-body');

    // Restore deleted rows
    mergeHistory.deletedRows.forEach((deletedRow) => {
        // Use tbody container to correctly parse <tr> elements
        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = deletedRow.html;

        // Find the <tr> element explicitly
        const restoredRow = tempContainer.querySelector('tr');

        if (!restoredRow) {
            console.error('Failed to restore row from HTML');
            return;
        }

        // Append to end of table
        tbody.appendChild(restoredRow);

        // Re-attach event listeners for checkboxes
        const checkbox = restoredRow.querySelector('.row-checkbox');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                const selectAllCheckbox = document.getElementById('select-all');
                const floatingBar = document.getElementById('floating-merge-bar');
                const rowCheckboxes = document.querySelectorAll('.row-checkbox');

                // Update select-all state
                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length && checkedCount > 0;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;

                // Update floating bar
                if (checkedCount > 0) {
                    floatingBar.classList.add('show');
                    document.getElementById('selected-count').textContent = checkedCount;
                } else {
                    floatingBar.classList.remove('show');
                }
            });
        }

        // Re-attach editable cell listeners
        const editableCells = restoredRow.querySelectorAll('.editable-cell');
        editableCells.forEach(cell => {
            // Input listener for recalculation
            cell.addEventListener('input', function() {
                const field = this.dataset.field;
                if (field === 'quantity' || field === 'price') {
                    recalculateRow(this.closest('tr'));
                }
            });

            // Blur listener for number formatting
            cell.addEventListener('blur', function() {
                const field = this.dataset.field;
                if (field === 'quantity' || field === 'price') {
                    const value = parseFloat(this.textContent) || 0;
                    this.textContent = value.toFixed(2);
                }
            });

            // Focus listener for text selection
            cell.addEventListener('focus', function() {
                const range = document.createRange();
                range.selectNodeContents(this);
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            });

            // Keydown listener for Enter key navigation
            cell.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const row = this.closest('tr');
                    const nextRow = row.nextElementSibling;
                    if (nextRow) {
                        const field = this.dataset.field;
                        const nextCell = nextRow.querySelector(`[data-field="${field}"]`);
                        if (nextCell) {
                            nextCell.focus();
                        }
                    }
                }
            });
        });
    });

    // Sort all rows by line_id to restore original order
    const allRows = Array.from(tbody.querySelectorAll('tr'));
    allRows.sort((a, b) => {
        const lineIdA = parseInt(a.dataset.lineId);
        const lineIdB = parseInt(b.dataset.lineId);
        return lineIdA - lineIdB;
    });

    // Remove all rows (keeps event listeners attached!)
    allRows.forEach(row => row.remove());

    // Re-append in sorted order (event listeners remain intact!)
    allRows.forEach(row => tbody.appendChild(row));

    // Renumber row numbers
    renumberRows();

    // Hide undo button
    document.getElementById('undo-button').classList.remove('show');

    // Clear merge history
    mergeHistory = null;
}

/**
 * Renumber all row numbers
 */
function renumberRows() {
    const rows = document.querySelectorAll('#spreadsheet-body tr');
    rows.forEach((row, index) => {
        const rowNumberCell = row.querySelector('.row-number');
        if (rowNumberCell) {
            rowNumberCell.textContent = index + 1;
        }
    });
}

/**
 * Update fee type badge styling when select changes
 */
function updateFeeTypeBadge(selectElement) {
    const selectedValue = selectElement.value;
    const cell = selectElement.closest('td');

    if (selectedValue === 'additional') {
        // Additional (red)
        selectElement.style.background = '#fee2e2';
        selectElement.style.color = '#991b1b';
        cell.dataset.feeType = 'additional';
    } else {
        // In Fee (green)
        selectElement.style.background = '#d1fae5';
        selectElement.style.color = '#065f46';
        cell.dataset.feeType = 'in_fee';
    }
}

// ========================================
// DEFER TO NEXT MONTH FUNCTIONALITY
// ========================================

/**
 * Insert een lege rij onder geselecteerde rijen
 * Werkt voor zowel costs als time entries tables
 */
function insertRowBelow() {
    console.log('INSERT ROW BELOW CALLED!');

    // Check costs table checkboxes
    const costCheckboxes = Array.from(document.querySelectorAll('.cost-row-checkbox:checked'));
    // Check time entries table checkboxes
    const timeEntryCheckboxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));

    console.log('Cost checkboxes:', costCheckboxes.length, 'Time entry checkboxes:', timeEntryCheckboxes.length);

    if (costCheckboxes.length === 0 && timeEntryCheckboxes.length === 0) {
        alert('Please select at least 1 row to insert below.');
        return;
    }

    // Insert for costs table
    if (costCheckboxes.length > 0) {
        console.log('INSERTING COST ROWS - Count:', costCheckboxes.length);
        const costsBody = document.getElementById('costs-spreadsheet-body');
        costCheckboxes.forEach((checkbox, idx) => {
            const row = checkbox.closest('tr');
            const newId = 'new-cost-' + Date.now() + '-' + idx;

            console.log('Creating new cost row with ID:', newId);

            // Create new empty row
            const newRow = document.createElement('tr');
            newRow.dataset.costId = newId;
            newRow.dataset.isNew = 'true';

            console.log('New row dataset:', newRow.dataset);
            newRow.innerHTML = `
                <td class="checkbox-cell" style="background: #f2f2f2; text-align: center; width: 35px; padding: 4px; border: 1px solid #d4d4d4;">
                    <input type="checkbox" class="cost-row-checkbox" value="${newId}">
                </td>
                <td class="drag-handle-cost" style="cursor: move; text-align: center; user-select: none; color: #999; background: #f2f2f2; border: 1px solid #d4d4d4;">
                    ⋮⋮
                </td>
                <td class="row-number" style="background: #fff8e1; text-align: center; font-weight: 700; color: #f59e0b; width: 40px; border: 1px solid #d4d4d4; border-right: 2px solid #c0c0c0;">NEW</td>
                <td class="editable-cell" style="padding: 4px 8px; border: 1px solid #d4d4d4;"
                    contenteditable="true"
                    data-field="description"
                    onkeydown="if(event.key==='Enter'){event.preventDefault(); moveToNextCostCell(this, 'right');}">[Enter description]</td>
                <td class="fee-type-cell" style="background: #f5f5f5; text-align: center; padding: 4px 8px; border: 1px solid #d4d4d4;" data-fee-type="in_fee">
                    <select onchange="updateFeeTypeBadge(this)" style="width: 100%; padding: 2px 8px; border-radius: 4px; font-size: 10pt; font-weight: 600; border: 1px solid #d4d4d4; background: #d1fae5; color: #065f46; cursor: pointer;">
                        <option value="in_fee" style="background: #d1fae5; color: #065f46;">In Fee</option>
                        <option value="additional" style="background: #fee2e2; color: #991b1b;">Additional</option>
                    </select>
                </td>
                <td class="editable-cell number-cell" style="text-align: right; padding: 4px 8px; border: 1px solid #d4d4d4;"
                    contenteditable="true"
                    data-field="quantity"
                    onkeydown="if(event.key==='Enter'){event.preventDefault(); moveToNextCostCell(this, 'down');}"
                    onblur="formatCostNumber(this); recalculateCostTotal(this.closest('tr'));">1.00</td>
                <td class="editable-cell number-cell" style="text-align: right; padding: 4px 8px; border: 1px solid #d4d4d4;"
                    contenteditable="true"
                    data-field="price"
                    onkeydown="if(event.key==='Enter'){event.preventDefault(); moveToNextCostCell(this, 'down');}"
                    onblur="formatCostNumber(this); recalculateCostTotal(this.closest('tr'));">0.00</td>
                <td class="readonly-cell number-cell" style="background: #f5f5f5; text-align: right; padding: 4px 8px; border: 1px solid #d4d4d4;">0.00</td>
            `;

            // Insert after selected row
            row.parentNode.insertBefore(newRow, row.nextSibling);

            // Uncheck the checkbox
            checkbox.checked = false;
        });
    }

    // Insert for time entries table
    if (timeEntryCheckboxes.length > 0) {
        const timeEntriesBody = document.getElementById('spreadsheet-body');
        timeEntryCheckboxes.forEach((checkbox, idx) => {
            const row = checkbox.closest('tr');
            const newId = 'new-line-' + Date.now() + '-' + idx;

            // Create new empty row
            const newRow = document.createElement('tr');
            newRow.dataset.lineId = newId;
            newRow.dataset.isNew = 'true';
            newRow.innerHTML = `
                <td class="checkbox-cell">
                    <input type="checkbox" class="row-checkbox" value="${newId}">
                </td>
                <td class="row-number" style="background: #fff8e1; color: #f59e0b; font-weight: 700;">NEW</td>
                <td class="readonly-cell"></td>
                <td class="readonly-cell"></td>
                <td class="editable-cell"
                    contenteditable="true"
                    data-field="description"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();moveToNextCell(this,'down');}">[Enter description]</td>
                <td class="editable-cell number-cell"
                    contenteditable="true"
                    data-field="quantity"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();moveToNextCell(this,'down');}"
                    onblur="formatNumber(this);recalculateRow(this.closest('tr'));">1.00</td>
                <td class="editable-cell number-cell"
                    contenteditable="true"
                    data-field="price"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();moveToNextCell(this,'down');}"
                    onblur="formatNumber(this);recalculateRow(this.closest('tr'));">0.00</td>
                <td class="readonly-cell number-cell">0.00</td>
            `;

            // Insert after selected row
            row.parentNode.insertBefore(newRow, row.nextSibling);

            // Uncheck the checkbox
            checkbox.checked = false;
        });
    }

    // Clear selection and hide floating bar
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = false;

    const costsSelectAll = document.getElementById('costs-select-all');
    if (costsSelectAll) costsSelectAll.checked = false;

    document.getElementById('floating-merge-bar').classList.remove('show');

    console.log(`Inserted ${costCheckboxes.length} cost rows and ${timeEntryCheckboxes.length} time entry rows`);
}

/**
 * Toggle defer status for selected rows
 * Als regels al deferred zijn, worden ze normaal gemaakt
 * Als regels normaal zijn, worden ze deferred gemaakt
 */
function deferToNextMonth() {
    const selectedCheckboxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));

    if (selectedCheckboxes.length === 0) {
        alert('Please select at least 1 row to defer.');
        return;
    }

    // Check of de eerste geselecteerde rij al deferred is
    const firstRow = selectedCheckboxes[0].closest('tr');
    const isCurrentlyDeferred = firstRow.classList.contains('deferred');

    // Toggle alle geselecteerde rijen
    selectedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');

        if (isCurrentlyDeferred) {
            // Remove deferred status
            row.classList.remove('deferred');
        } else {
            // Add deferred status
            row.classList.add('deferred');
        }

        // Uncheck checkbox
        checkbox.checked = false;
    });

    // Clear selection and hide floating bar
    document.getElementById('select-all').checked = false;
    document.getElementById('floating-merge-bar').classList.remove('show');
}

/**
 * Delete selected rows (zowel costs als time entries)
 */
function deleteSelectedRows() {
    const costCheckboxes = Array.from(document.querySelectorAll('.cost-row-checkbox:checked'));
    const timeEntryCheckboxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));
    const totalSelected = costCheckboxes.length + timeEntryCheckboxes.length;

    if (totalSelected === 0) {
        alert('Please select at least 1 row to delete.');
        return;
    }

    // Bevestiging
    const confirmMessage = `Are you sure you want to DELETE ${totalSelected} row(s)?\n\n` +
                          `This will permanently remove these items from the invoice.\n\n` +
                          `This action CANNOT be undone!`;

    if (!confirm(confirmMessage)) {
        return;
    }

    // Delete cost rows
    costCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const isNew = row.dataset.isNew === 'true';

        if (isNew) {
            // Nieuwe regels kunnen direct uit DOM verwijderd worden
            row.remove();
        } else {
            // Bestaande regels: Doorstrepen met rode achtergrond
            row.dataset.deleted = 'true';
            row.style.opacity = '0.6';
            row.style.backgroundColor = '#fee2e2'; // Licht rood
            row.style.textDecoration = 'line-through';

            // Maak alle cellen non-editable
            row.querySelectorAll('.editable-cell').forEach(cell => {
                cell.contentEditable = 'false';
                cell.style.textDecoration = 'line-through';
            });
        }
    });

    // Delete time entry rows
    timeEntryCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const isNew = row.dataset.isNew === 'true';

        if (isNew) {
            // Nieuwe regels kunnen direct uit DOM verwijderd worden
            row.remove();
        } else {
            // Bestaande regels: Doorstrepen met rode achtergrond
            row.dataset.deleted = 'true';
            row.style.opacity = '0.6';
            row.style.backgroundColor = '#fee2e2'; // Licht rood
            row.style.textDecoration = 'line-through';

            // Maak alle cellen non-editable
            row.querySelectorAll('.editable-cell').forEach(cell => {
                cell.contentEditable = 'false';
                cell.style.textDecoration = 'line-through';
            });
        }
    });

    // Clear checkboxes
    document.querySelectorAll('.cost-row-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);

    // Clear select-all checkboxes
    document.getElementById('select-all').checked = false;
    const costsSelectAll = document.getElementById('costs-select-all');
    if (costsSelectAll) costsSelectAll.checked = false;

    // Hide floating bar
    document.getElementById('floating-merge-bar').classList.remove('show');

    // Alert gebruiker
    alert(`${totalSelected} row(s) marked for deletion.\n\nClick "Save Changes" to permanently delete these rows.`);
}

/**
 * Restore selected rows (maak delete ongedaan)
 */
function restoreSelectedRows() {
    const costCheckboxes = Array.from(document.querySelectorAll('.cost-row-checkbox:checked'));
    const timeEntryCheckboxes = Array.from(document.querySelectorAll('.row-checkbox:checked'));
    const totalSelected = costCheckboxes.length + timeEntryCheckboxes.length;

    if (totalSelected === 0) {
        alert('Please select at least 1 row to restore.');
        return;
    }

    // Restore cost rows
    costCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const isDeleted = row.dataset.deleted === 'true';

        if (isDeleted) {
            // Verwijder deleted status en styling
            delete row.dataset.deleted;
            row.style.opacity = '1';
            row.style.backgroundColor = '';
            row.style.textDecoration = '';

            // Maak cellen weer editable
            row.querySelectorAll('.editable-cell').forEach(cell => {
                cell.contentEditable = 'true';
                cell.style.textDecoration = '';
            });
        }

        // Uncheck checkbox
        checkbox.checked = false;
    });

    // Restore time entry rows
    timeEntryCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const isDeleted = row.dataset.deleted === 'true';

        if (isDeleted) {
            // Verwijder deleted status en styling
            delete row.dataset.deleted;
            row.style.opacity = '1';
            row.style.backgroundColor = '';
            row.style.textDecoration = '';

            // Maak cellen weer editable
            row.querySelectorAll('.editable-cell').forEach(cell => {
                cell.contentEditable = 'true';
                cell.style.textDecoration = '';
            });
        }

        // Uncheck checkbox
        checkbox.checked = false;
    });

    // Clear checkboxes
    document.querySelectorAll('.cost-row-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);

    // Clear select-all checkboxes
    document.getElementById('select-all').checked = false;
    const costsSelectAll = document.getElementById('costs-select-all');
    if (costsSelectAll) costsSelectAll.checked = false;

    // Hide floating bar
    document.getElementById('floating-merge-bar').classList.remove('show');

    // Alert gebruiker
    alert(`${totalSelected} row(s) restored successfully!`);
}

// ===========================================
// DRAG & DROP REORDERING (Sortable.js)
// ===========================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag & drop for TIME ENTRIES
    const tbody = document.getElementById('spreadsheet-body');

    if (tbody && typeof Sortable !== 'undefined') {
        Sortable.create(tbody, {
            handle: '.drag-handle', // Alleen drag icon is handle
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                // Update row numbers na drag
                updateRowNumbers();

                // Auto-save nieuwe volgorde
                saveRowOrder();
            }
        });

        console.log('Drag & drop initialized for time entries');
    }

    // Initialize drag & drop for ADDITIONAL COSTS
    const costsTbody = document.getElementById('costs-spreadsheet-body');

    if (costsTbody && typeof Sortable !== 'undefined') {
        Sortable.create(costsTbody, {
            handle: '.drag-handle-cost', // Alleen drag icon is handle
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                // Update row numbers na drag
                updateCostRowNumbers();

                // Auto-save nieuwe volgorde
                saveCostOrder();
            }
        });

        console.log('Drag & drop initialized for additional costs');
    }
});

// Update row numbers na reordering
function updateRowNumbers() {
    const rows = document.querySelectorAll('#spreadsheet-body tr');
    rows.forEach((row, index) => {
        const rowNumberCell = row.querySelector('.row-number');
        if (rowNumberCell) {
            rowNumberCell.textContent = index + 1;
        }
    });
}

// Save nieuwe row order naar backend
function saveRowOrder() {
    const rows = document.querySelectorAll('#spreadsheet-body tr');
    const lineIds = Array.from(rows).map(row => parseInt(row.dataset.lineId));

    // Toon saving indicator
    const saveBtn = document.querySelector('button[onclick="saveSpreadsheet()"]');
    if (saveBtn) {
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '💾 Saving order...';
        saveBtn.disabled = true;
    }

    fetch('{{ route("invoices.reorder-lines", $invoice) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            line_ids: lineIds
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Row order saved:', data);

        // Reset save button
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }

        // Toon success feedback (kort)
        const tempMsg = document.createElement('div');
        tempMsg.textContent = '✓ Order saved';
        tempMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        document.body.appendChild(tempMsg);

        setTimeout(() => tempMsg.remove(), 2000);
    })
    .catch(error => {
        console.error('Error saving row order:', error);

        // Reset save button
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }

        alert('Error saving row order. Please try again.');
    });
}

// ===========================================
// COSTS DRAG & DROP FUNCTIONS
// ===========================================

// Update cost row numbers na reordering
function updateCostRowNumbers() {
    const rows = document.querySelectorAll('#costs-spreadsheet-body tr');
    rows.forEach((row, index) => {
        const rowNumberCell = row.querySelector('.row-number');
        if (rowNumberCell) {
            rowNumberCell.textContent = index + 1;
        }
    });
}

// Save nieuwe cost order naar backend
function saveCostOrder() {
    const rows = document.querySelectorAll('#costs-spreadsheet-body tr');
    const costIds = Array.from(rows).map(row => parseInt(row.dataset.costId));

    // Toon saving indicator
    const saveBtn = document.querySelector('button[onclick="saveSpreadsheet()"]');
    if (saveBtn) {
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '💾 Saving cost order...';
        saveBtn.disabled = true;
    }

    fetch('{{ route("invoices.reorder-costs", $invoice) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            cost_ids: costIds
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Cost order saved:', data);

        // Reset save button
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }

        // Toon success feedback (kort)
        const tempMsg = document.createElement('div');
        tempMsg.textContent = '✓ Cost order saved';
        tempMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #f59e0b; color: white; padding: 12px 20px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        document.body.appendChild(tempMsg);

        setTimeout(() => tempMsg.remove(), 2000);
    })
    .catch(error => {
        console.error('Error saving cost order:', error);

        // Reset save button
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }

        alert('Error saving cost order. Please try again.');
    });
}
</script>

{{-- Drag & Drop Styling --}}
<style>
.sortable-ghost {
    opacity: 0.4;
    background: #e3f2fd;
}

.sortable-chosen {
    background: #bbdefb;
}

.sortable-drag {
    opacity: 1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.drag-handle:hover {
    background: #f5f5f5;
    color: #333 !important;
}

.drag-handle-cost:hover {
    background: #e5e7eb !important;
    color: #333 !important;
}
</style>
@endsection
