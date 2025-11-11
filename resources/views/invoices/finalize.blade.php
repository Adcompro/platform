@extends('layouts.app')

@section('title', 'Finalize Invoice - ' . $invoice->invoice_number)

@section('content')
<style>
    /* Excel-style styling */
    .excel-container {
        background: #f0f0f0;
        padding: 20px;
        min-height: 100vh;
    }

    .excel-sheet {
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px;
    }

    .excel-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
        font-size: 11pt;
    }

    .excel-table td {
        padding: 4px 8px;
        border: 1px solid #d4d4d4;
        vertical-align: top;
    }

    .excel-table .no-border {
        border: none;
    }

    .excel-table .border-bottom {
        border-bottom: 2px solid #000;
    }

    .excel-table .border-top {
        border-top: 2px solid #000;
    }

    .excel-table .bold {
        font-weight: 700;
    }

    .excel-table .right {
        text-align: right;
    }

    .excel-table .center {
        text-align: center;
    }

    .excel-table .gray-bg {
        background: #f2f2f2;
    }

    .excel-table .light-gray-bg {
        background: #f9f9f9;
    }

    /* Hiërarchie indentatie */
    .indent-0 { padding-left: 8px; }
    .indent-1 { padding-left: 24px; }
    .indent-2 { padding-left: 40px; }
    .indent-3 { padding-left: 56px; }

    /* Collapse toggle */
    .collapse-toggle {
        cursor: pointer;
        user-select: none;
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 1px solid #666;
        text-align: center;
        line-height: 14px;
        margin-right: 4px;
        font-size: 10px;
        background: white;
    }

    .collapse-toggle:hover {
        background: #f0f0f0;
    }

    /* Print button */
    .action-buttons {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .btn-primary {
        background: #0070C0;
        color: white;
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
    }

    .btn-success {
        background: #70AD47;
        color: white;
    }

    @media print {
        /* Remove browser headers and footers */
        @page {
            margin: 0;
            size: A4;
        }

        /* Hide navigation elements */
        .action-buttons,
        .teamleader-sidebar,
        aside,
        header,
        nav {
            display: none !important;
        }

        /* Reset body/html for print */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
            width: 100%;
        }

        /* Reset main content area */
        main {
            margin-left: 0 !important;
            margin-top: 0 !important;
            padding: 0 !important;
        }

        /* Reset container styling for print */
        .excel-container {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            min-height: auto !important;
        }

        /* Clean up sheet for print */
        .excel-sheet {
            box-shadow: none !important;
            padding: 20px !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        /* Hide collapse toggles */
        .collapse-toggle {
            display: none !important;
        }

        /* Ensure all content is visible */
        .excel-table {
            page-break-inside: auto;
        }

        .excel-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        /* Make sure borders are visible */
        .excel-table td {
            border: 1px solid #d4d4d4 !important;
        }

        .excel-table .no-border {
            border: none !important;
        }

        /* Full width for print */
        * {
            max-width: 100% !important;
        }
    }

    /* Editable cells */
    .editable {
        cursor: text;
        position: relative;
    }

    .editable:hover {
        background: #fffacd !important;
    }

    .editable:focus {
        outline: 2px solid #0070C0;
        background: white !important;
    }
</style>

<div class="excel-container">
    {{-- Action Buttons --}}
    <div class="action-buttons">
        <a href="{{ route('invoices.spreadsheet', $invoice->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Spreadsheet
        </a>
        <button onclick="printReport()" class="btn btn-primary" title="Print report - Make sure to disable browser headers/footers in print settings">
            <i class="fas fa-print"></i> Print / PDF
        </button>
        <button onclick="finalizeInvoice()" class="btn btn-success">
            <i class="fas fa-check"></i> Finalize Report
        </button>
    </div>

    <div class="excel-sheet">
        <table class="excel-table">
            {{-- Header Section --}}
            <tr>
                <td colspan="2" class="no-border bold" style="font-size: 14pt;">
                    Activity Report for:
                </td>
                <td class="no-border bold right" style="font-size: 14pt;">
                    {{ strtoupper($invoice->project->customer->name ?? 'CUSTOMER NAME') }}
                </td>
            </tr>
            <tr>
                <td colspan="3" class="no-border" style="height: 10px;"></td>
            </tr>

            {{-- Reporting Period Info --}}
            <tr>
                <td colspan="2" class="no-border">Reporting Period</td>
                <td class="no-border right bold">
                    @if($invoice->period_start && $invoice->period_end)
                        {{ date('F Y', strtotime($invoice->period_start)) }}
                    @else
                        {{ date('F Y', strtotime($invoice->invoice_date)) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2" class="no-border">Fee for Reporting Period</td>
                <td class="no-border right">€ {{ number_format($invoice->monthly_budget ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="no-border">Performed Fee for Reporting Period</td>
                <td class="no-border right bold">€ {{ number_format(($invoice->work_amount ?? 0) + ($invoice->service_amount ?? 0), 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="no-border">Fee Balance previous Reporting Period</td>
                <td class="no-border right">€ {{ number_format($invoice->previous_month_remaining ?? 0, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="no-border bold">New Fee Balance (negative means overservicing)</td>
                <td class="no-border right bold" style="color: {{ ($invoice->next_month_rollover ?? 0) >= 0 ? '#008000' : '#C00000' }};">
                    € {{ number_format($invoice->next_month_rollover ?? 0, 2, ',', '.') }}
                </td>
            </tr>

            <tr><td colspan="3" class="no-border" style="height: 20px;"></td></tr>

            {{-- Expenses Overview --}}
            <tr>
                <td colspan="3" class="no-border bold" style="font-size: 12pt;">Expenses overview for Reporting Period</td>
            </tr>
            <tr>
                <td class="gray-bg bold">Description of Costs</td>
                <td class="gray-bg bold center">Invoicing</td>
                <td class="gray-bg bold right">Cost ex. VAT</td>
            </tr>

            @php
                $additionalCosts = $invoice->lines()->where('category', 'cost')->get();
                $inFeeCosts = $additionalCosts->where('is_billable', false)->sum('line_total_ex_vat');
                $additionalCostTotal = $additionalCosts->where('is_billable', true)->sum('line_total_ex_vat');
            @endphp

            @foreach($additionalCosts as $cost)
            <tr>
                <td class="editable" contenteditable="true" data-line-id="{{ $cost->id }}" data-field="description">
                    {{ $cost->description }}
                </td>
                <td class="center">{{ $cost->is_billable ? 'Additional' : 'In fee' }}</td>
                <td class="right">€ {{ number_format($cost->line_total_ex_vat, 2, ',', '.') }}</td>
            </tr>
            @endforeach

            <tr>
                <td class="bold gray-bg">Total</td>
                <td class="gray-bg"></td>
                <td class="bold right gray-bg">€ {{ number_format($additionalCosts->sum('line_total_ex_vat'), 2, ',', '.') }}</td>
            </tr>

            <tr><td colspan="3" class="no-border" style="height: 20px;"></td></tr>

            {{-- Invoicing Overview --}}
            <tr>
                <td colspan="3" class="no-border bold" style="font-size: 12pt;">Invoicing overview for Reporting Period</td>
            </tr>
            <tr>
                <td colspan="2" class="no-border">Activity fees for Reporting Period</td>
                <td class="no-border right">€ {{ number_format(($invoice->work_amount ?? 0) + ($invoice->service_amount ?? 0), 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="no-border">Additional expenses for Reporting Period</td>
                <td class="no-border right">€ {{ number_format($additionalCostTotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="no-border bold" style="font-size: 11pt;">To be Invoiced for Reporting Period EX VAT</td>
                <td class="no-border right bold" style="font-size: 11pt; background: #ffffcc;">
                    € {{ number_format($additionalCostTotal, 2, ',', '.') }}
                </td>
            </tr>

            <tr><td colspan="3" class="no-border" style="height: 20px;"></td></tr>

            {{-- Activity Report Header --}}
            <tr>
                <td colspan="3" class="no-border bold" style="font-size: 12pt;">Activity Report</td>
            </tr>
            <tr><td colspan="3" class="no-border" style="height: 5px;"></td></tr>

            {{-- Activity Report Table --}}
            <tr>
                <td class="gray-bg bold" style="width: 60%;">Description</td>
                <td class="gray-bg bold right" style="width: 20%;">Hours</td>
                <td class="gray-bg bold right" style="width: 20%;">Price</td>
            </tr>

            @php
                // Groepeer time entries per milestone/task (zonder subtasks)
                $timeLines = $invoice->lines()
                    ->where('category', 'work')
                    ->where('line_type', 'hours')
                    ->with(['milestone', 'task', 'timeEntries'])
                    ->get()
                    ->sortBy(function($line) {
                        $milestone = $line->milestone;
                        $task = $line->task;

                        $milestoneOrder = $milestone ? $milestone->sort_order : 9999;
                        $taskOrder = $task ? $task->sort_order : 9999;

                        return sprintf('%04d%04d', $milestoneOrder, $taskOrder);
                    });

                // Bouw hiërarchie (milestone -> tasks)
                $hierarchy = [];
                foreach ($timeLines as $line) {
                    $milestoneId = $line->group_milestone_id ?? 0;
                    $taskId = $line->group_task_id;

                    // Initialiseer milestone als die nog niet bestaat
                    if (!isset($hierarchy[$milestoneId])) {
                        $hierarchy[$milestoneId] = [
                            'milestone' => $line->milestone,
                            'hours' => 0,
                            'amount' => 0,
                            'tasks' => [],
                            'direct_entries' => [] // Voor entries zonder task
                        ];
                    }

                    // Als er een task is, voeg toe aan tasks
                    if ($taskId) {
                        if (!isset($hierarchy[$milestoneId]['tasks'][$taskId])) {
                            $hierarchy[$milestoneId]['tasks'][$taskId] = [
                                'task' => $line->task,
                                'hours' => 0,
                                'amount' => 0
                            ];
                        }

                        $hierarchy[$milestoneId]['tasks'][$taskId]['hours'] += $line->quantity ?? 0;
                        $hierarchy[$milestoneId]['tasks'][$taskId]['amount'] += $line->line_total_ex_vat ?? 0;
                    } else {
                        // Geen task - direct onder milestone
                        $hierarchy[$milestoneId]['direct_entries'][] = [
                            'description' => $line->description,
                            'hours' => $line->quantity ?? 0,
                            'amount' => $line->line_total_ex_vat ?? 0
                        ];
                    }

                    // Tel alles bij het milestone totaal
                    $hierarchy[$milestoneId]['hours'] += $line->quantity ?? 0;
                    $hierarchy[$milestoneId]['amount'] += $line->line_total_ex_vat ?? 0;
                }

                $totalHours = 0;
                $totalAmount = 0;
            @endphp

            @foreach($hierarchy as $milestoneId => $milestoneData)
                @php
                    $milestone = $milestoneData['milestone'];
                    $milestoneHours = $milestoneData['hours'];
                    $milestoneAmount = $milestoneData['amount'];
                    $totalHours += $milestoneHours;
                    $totalAmount += $milestoneAmount;
                @endphp

                {{-- Milestone Row --}}
                <tr class="milestone-row" data-milestone-id="{{ $milestoneId }}">
                    <td class="indent-0">
                        <span class="collapse-toggle" onclick="toggleMilestone({{ $milestoneId }})">☑</span>
                        <strong>{{ $milestone ? $milestone->name : 'Uncategorized' }}</strong>
                    </td>
                    <td class="right bold">{{ number_format($milestoneHours, 2, ',', '.') }}</td>
                    <td class="right bold">€ {{ number_format($milestoneAmount, 2, ',', '.') }}</td>
                </tr>

                {{-- Direct entries (zonder task) --}}
                @foreach($milestoneData['direct_entries'] as $entry)
                    <tr class="task-row milestone-{{ $milestoneId }}">
                        <td class="indent-1 light-gray-bg">
                            {{ $entry['description'] }}
                        </td>
                        <td class="right light-gray-bg">{{ number_format($entry['hours'], 2, ',', '.') }}</td>
                        <td class="right light-gray-bg">€ {{ number_format($entry['amount'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- Task entries --}}
                @foreach($milestoneData['tasks'] as $taskId => $taskData)
                    @php
                        $task = $taskData['task'];
                        $taskHours = $taskData['hours'];
                        $taskAmount = $taskData['amount'];
                    @endphp

                    {{-- Task Row --}}
                    <tr class="task-row milestone-{{ $milestoneId }}" data-task-id="{{ $taskId }}">
                        <td class="indent-1 light-gray-bg">
                            {{ $task->name ?? 'Task' }}
                        </td>
                        <td class="right light-gray-bg">{{ number_format($taskHours, 2, ',', '.') }}</td>
                        <td class="right light-gray-bg">€ {{ number_format($taskAmount, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Total Row --}}
            <tr>
                <td colspan="3" class="no-border" style="height: 5px;"></td>
            </tr>
            <tr>
                <td class="bold border-top">Total</td>
                <td class="bold right border-top">{{ number_format($totalHours, 2, ',', '.') }}</td>
                <td class="bold right border-top">€ {{ number_format($totalAmount, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Print report with instructions
    function printReport() {
        // Show alert with instructions
        const message = 'PRINT INSTRUCTIONS:\n\n' +
            '✓ In the print dialog, make sure to:\n' +
            '  • DISABLE "Headers and footers"\n' +
            '  • Set margins to "None" or "Minimum"\n' +
            '  • Select "Save as PDF" if you want a PDF file\n\n' +
            'This will ensure a clean print without date/time and URL.\n\n' +
            'Click OK to open the print dialog.';

        if (confirm(message)) {
            // Open print dialog
            window.print();
        }
    }

    // Toggle milestone collapse/expand
    function toggleMilestone(milestoneId) {
        const toggle = event.target;
        const rows = document.querySelectorAll('.milestone-' + milestoneId);

        rows.forEach(row => {
            if (row.style.display === 'none') {
                row.style.display = '';
                toggle.textContent = '☑';
            } else {
                row.style.display = 'none';
                toggle.textContent = '☐';
            }
        });
    }

    // Toggle task collapse/expand
    function toggleTask(taskId) {
        const toggle = event.target;
        const rows = document.querySelectorAll('.task-' + taskId);

        rows.forEach(row => {
            if (row.style.display === 'none') {
                row.style.display = '';
                toggle.textContent = '☑';
            } else {
                row.style.display = 'none';
                toggle.textContent = '☐';
            }
        });
    }

    // Save edits via AJAX
    document.querySelectorAll('.editable').forEach(cell => {
        cell.addEventListener('blur', function() {
            const lineId = this.dataset.lineId;
            const field = this.dataset.field;
            const newValue = this.textContent.trim();

            console.log('Saving edit:', { lineId, field, newValue });

            fetch(`/invoices/lines/${lineId}/update-field`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    field: field,
                    value: newValue
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Saved successfully');
                    this.style.background = '#d4edda';
                    setTimeout(() => {
                        this.style.background = '';
                    }, 1000);
                } else {
                    console.error('Save failed:', data.message);
                    alert('Error saving: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving changes');
            });
        });
    });

    // Finalize invoice
    function finalizeInvoice() {
        if (!confirm('Are you sure you want to finalize this report?\n\nOnce finalized, you cannot edit the report anymore.')) {
            return;
        }

        // Check if we're in reports context (URL contains /reports/)
        const isReportsContext = window.location.pathname.includes('/reports/');

        if (isReportsContext) {
            window.location.href = '{{ route("reports.finalize-confirm", $invoice->id) }}';
        } else {
            window.location.href = '{{ route("invoices.finalize-confirm", $invoice->id) }}';
        }
    }
</script>
@endpush

@endsection
