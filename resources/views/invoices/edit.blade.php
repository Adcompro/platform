@extends('layouts.app')

@section('title', 'Edit Invoice - ' . ($invoice->invoice_number ?: 'DRAFT-' . $invoice->id))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center">
            <a href="{{ route('invoices.show', $invoice) }}" class="text-gray-500 hover:text-gray-700 mr-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Draft Invoice</h1>
                <p class="mt-2 text-gray-600">
                    {{ $invoice->customer->name }}
                    @if($invoice->project)
                    • {{ $invoice->project->name }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('invoices.show', $invoice) }}" 
               class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Preview Invoice
            </a>
            
            <button type="button"
                    onclick="if(confirm('Are you sure you want to delete this draft invoice?\n\nThis action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Delete Draft
            </button>
        </div>
    </div>

    <!-- Invoice Edit Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoice-edit-form" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Invoice Header -->
            <div class="px-6 py-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Invoice Information</h3>
                <p class="mt-1 text-sm text-gray-600">Update invoice details and billing information</p>
            </div>

            <div class="px-6 space-y-6">
                <!-- Invoice Basics -->
                <div class="grid grid-cols-1 md:grid-cols-{{ $isCompaniesPluginActive ? '3' : '2' }} gap-6">
                    @if($isCompaniesPluginActive)
                    <!-- Invoicing Company (Multi-Company Mode) -->
                    <div>
                        <label for="invoicing_company_id" class="block text-sm font-medium text-gray-700">
                            Invoicing Company <span class="text-red-500">*</span>
                        </label>
                        <select name="invoicing_company_id" 
                                id="invoicing_company_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('invoicing_company_id') border-red-300 @enderror">
                            <option value="">Select invoicing company</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('invoicing_company_id', $invoice->invoicing_company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('invoicing_company_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @else
                    <!-- Hidden Company Field (Single Company Mode) -->
                    <input type="hidden" name="invoicing_company_id" value="{{ $defaultCompany?->id ?? 1 }}">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Single Company Mode</h3>
                                <div class="mt-1 text-sm text-blue-700">
                                    Invoicing from: <strong>{{ $defaultCompany?->name ?? 'Default Company' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Customer -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <select name="customer_id" 
                                id="customer_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('customer_id') border-red-300 @enderror">
                            <option value="">Select customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Project (Optional) -->
                    <div>
                        <label for="project_id" class="block text-sm font-medium text-gray-700">
                            Project (Optional)
                        </label>
                        <select name="project_id" 
                                id="project_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('project_id') border-red-300 @enderror">
                            <option value="">Select project (optional)</option>
                            @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $invoice->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Invoice Template -->
                    <div>
                        <label for="invoice_template_id" class="block text-sm font-medium text-gray-700">
                            Invoice Template
                            <span class="text-gray-500 font-normal">(layout style)</span>
                        </label>
                        <select name="invoice_template_id" 
                                id="invoice_template_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('invoice_template_id') border-red-300 @enderror">
                            <option value="">Use default template</option>
                            @foreach($templates as $template)
                            <option value="{{ $template->id }}" 
                                    {{ old('invoice_template_id', $invoice->invoice_template_id) == $template->id ? 'selected' : '' }}
                                    data-description="{{ $template->description }}">
                                {{ $template->name }} 
                                @if($template->is_default)
                                (Default)
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('invoice_template_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500" id="template-description">
                            Select a template to customize the invoice layout
                        </p>
                    </div>
                </div>

                <!-- Invoice Period -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="period_start" class="block text-sm font-medium text-gray-700">
                            Period Start Date
                        </label>
                        <input type="date" 
                               name="period_start" 
                               id="period_start" 
                               value="{{ old('period_start', $invoice->period_start?->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('period_start') border-red-300 @enderror">
                        @error('period_start')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="period_end" class="block text-sm font-medium text-gray-700">
                            Period End Date
                        </label>
                        <input type="date" 
                               name="period_end" 
                               id="period_end" 
                               value="{{ old('period_end', $invoice->period_end?->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('period_end') border-red-300 @enderror">
                        @error('period_end')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Invoice Notes
                    </label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="3"
                              placeholder="Additional notes for this invoice..."
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror">{{ old('notes', $invoice->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Budget Overview Section -->
            <div class="px-6 py-6 border-t border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Previous Month Remaining -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Previous Month Remaining</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    €{{ number_format($invoice->previous_month_remaining ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Budget -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Monthly Budget</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    €{{ number_format($invoice->monthly_budget ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="bg-green-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Available -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Available</p>
                                <p class="text-2xl font-bold text-indigo-600">
                                    €{{ number_format($invoice->total_budget ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="bg-indigo-100 rounded-full p-2">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Next Month Rollover/Shortage -->
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">
                                    @if(($invoice->next_month_rollover ?? 0) >= 0)
                                        Rollover to Next Month
                                    @else
                                        Budget Shortage
                                    @endif
                                </p>
                                <p class="text-2xl font-bold {{ ($invoice->next_month_rollover ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    €{{ number_format(abs($invoice->next_month_rollover ?? 0), 2) }}
                                </p>
                            </div>
                            <div class="{{ ($invoice->next_month_rollover ?? 0) >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full p-2">
                                @if(($invoice->next_month_rollover ?? 0) >= 0)
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                                @else
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget Breakdown -->
                <div class="mt-6 bg-white rounded-lg p-4 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Budget Usage Breakdown</h4>
                    <div class="space-y-3">
                        <!-- Work Amount -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Work (Time Entries)</span>
                            <span class="text-sm font-medium text-gray-900">€{{ number_format($invoice->work_amount ?? 0, 2) }}</span>
                        </div>
                        
                        <!-- Service Amount -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Service Packages</span>
                            <span class="text-sm font-medium text-gray-900">€{{ number_format($invoice->service_amount ?? 0, 2) }}</span>
                        </div>
                        
                        <!-- Additional Costs -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Additional Costs</span>
                            <span class="text-sm font-medium text-gray-900">€{{ number_format($invoice->additional_costs ?? 0, 2) }}</span>
                        </div>
                        
                        <div class="border-t pt-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">Total Used</span>
                                <span class="text-lg font-bold text-gray-900">
                                    €{{ number_format(($invoice->work_amount ?? 0) + ($invoice->service_amount ?? 0) + ($invoice->additional_costs ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Lines Section -->
            <div class="px-6 py-6 border-t border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Invoice Lines</h3>
                        <p class="mt-1 text-sm text-gray-600">Drag to reorder, edit descriptions and amounts</p>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" 
                                id="merge-selected-lines"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg inline-flex items-center hidden"
                                onclick="mergeSelectedLines()">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Merge Selected (<span id="merge-count">0</span>)
                        </button>
                        <button type="button" 
                                id="add-time-entries"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Add Time Entries
                        </button>
                        <button type="button" 
                                id="add-custom-line"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Custom Line
                        </button>
                    </div>
                </div>

                <!-- Invoice Lines Container -->
                <div id="invoice-lines-container" class="space-y-4">
                    @forelse($invoice->lines as $line)
                    @php
                        // Determine line type based on description patterns and clean the description
                        $lineType = 'normal';
                        $bgClass = 'bg-gray-50';
                        $borderClass = 'border-gray-200';
                        $textClass = 'text-gray-900';
                        $iconHtml = '';
                        $cleanDescription = $line->description;
                        
                        // Check source_type first, then fall back to description patterns
                        if ($line->source_type === 'milestone_header' || str_contains($line->description, '(Milestone Total)')) {
                            $lineType = 'milestone';
                            $bgClass = 'bg-blue-50';
                            $borderClass = 'border-blue-300';
                            $textClass = 'text-blue-900 font-semibold';
                            $iconHtml = '<svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 012-2z" clip-rule="evenodd"></path></svg>';
                        } elseif ($line->source_type === 'task_header' || str_starts_with(trim($line->description), '→')) {
                            $lineType = 'task';
                            $bgClass = 'bg-green-50';
                            $borderClass = 'border-green-300';
                            $textClass = 'text-green-900 font-medium';
                            $iconHtml = '<svg class="w-4 h-4 text-green-600 mr-2 ml-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>';
                            // Remove arrow prefix from description if present
                            $cleanDescription = str_starts_with(trim($line->description), '→') ?
                                ltrim(str_replace('→', '', $line->description)) : $line->description;
                        } elseif ($line->source_type === 'time_entry' || str_starts_with(trim($line->description), '•')) {
                            $lineType = 'description';
                            $bgClass = 'bg-gray-50';
                            $borderClass = 'border-gray-200';
                            $textClass = 'text-gray-700';
                            $iconHtml = '<span class="text-gray-400 mr-2 ml-8">•</span>';
                            // Remove bullet prefix from description if present
                            $cleanDescription = str_starts_with(trim($line->description), '•') ?
                                ltrim(str_replace('•', '', $line->description)) : $line->description;
                        }
                    @endphp
                    @php
                        // Get milestone/task info from line or time entry
                        $milestoneId = $line->group_milestone_id;
                        $taskId = $line->group_task_id;
                        $milestoneName = '';
                        $taskName = '';

                        // Try to get names from the database relationships if we have IDs
                        if ($milestoneId) {
                            $milestone = \App\Models\ProjectMilestone::find($milestoneId);
                            $milestoneName = $milestone ? $milestone->name : '';
                        }

                        if ($taskId) {
                            $task = \App\Models\ProjectTask::find($taskId);
                            $taskName = $task ? $task->name : '';
                        }

                        // If no group IDs, fall back to time entry relationships
                        if (!$milestoneId && !$taskId && $lineType === 'description' && $line->source_type === 'time_entry') {
                            $timeEntry = $line->timeEntries()->first();
                            if ($timeEntry) {
                                $milestoneId = $timeEntry->project_milestone_id;
                                $taskId = $timeEntry->project_task_id;
                                $milestoneName = $timeEntry->milestone ? $timeEntry->milestone->name : '';
                                $taskName = $timeEntry->task ? $timeEntry->task->name : '';
                            }
                        }
                    @endphp
                    <div class="invoice-line {{ $bgClass }} p-4 rounded-lg border {{ $borderClass }}"
                         data-line-id="{{ $line->id }}"
                         data-line-type="{{ $lineType }}"
                         data-milestone-id="{{ $milestoneId }}"
                         data-task-id="{{ $taskId }}"
                         data-milestone-name="{{ $milestoneName }}"
                         data-task-name="{{ $taskName }}">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       class="line-selector mr-3 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                       data-line-id="{{ $line->id }}"
                                       onchange="updateMergeButton()">
                                <div class="drag-handle cursor-move text-gray-400 mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                    </svg>
                                </div>
                                {!! $iconHtml !!}
                                <h4 class="text-sm font-medium {{ $textClass }}">
                                    @if($lineType === 'milestone')
                                        @if($line->source_type === 'milestone_header')
                                            Milestone
                                        @else
                                            Milestone Total
                                        @endif
                                    @elseif($lineType === 'task')
                                        Task Subtotal
                                    @elseif($lineType === 'description')
                                        Time Entry
                                    @else
                                        {{ $line->source_type === 'time_entry' ? 'Time Entry' : 'Custom Line' }}
                                    @endif
                                    @if($line->source_type === 'time_entry' && $line->timeEntry)
                                    - {{ $line->timeEntry->user->name }}
                                    @endif
                                </h4>
                            </div>
                            <button type="button" 
                                    class="remove-line text-red-600 hover:text-red-800"
                                    onclick="removeLine(this)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <input type="text" 
                                       name="lines[{{ $line->id }}][description]" 
                                       value="{{ $cleanDescription }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       required>
                                <input type="hidden" name="lines[{{ $line->id }}][original_description]" value="{{ $line->description }}">
                                <input type="hidden" name="lines[{{ $line->id }}][id]" value="{{ $line->id }}">
                                @if($line->source_type === 'time_entry')
                                <input type="hidden" name="lines[{{ $line->id }}][source_type]" value="time_entry">
                                <input type="hidden" name="lines[{{ $line->id }}][source_id]" value="{{ $line->source_id }}">
                                @endif
                            </div>
                            
                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input type="number" 
                                       name="lines[{{ $line->id }}][quantity]" 
                                       value="{{ $line->quantity }}"
                                       min="0"
                                       step="0.01"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 quantity-input"
                                       required>
                            </div>
                            
                            <!-- Unit Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unit Price (€)</label>
                                <input type="number" 
                                       name="lines[{{ $line->id }}][unit_price]" 
                                       value="{{ $line->unit_price }}"
                                       min="0"
                                       step="0.01"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 price-input"
                                       required>
                            </div>
                            
                            <!-- VAT Rate -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">VAT Rate (%)</label>
                                <select name="lines[{{ $line->id }}][vat_rate]" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 vat-rate-select">
                                    <option value="21" {{ $line->vat_rate == 21 ? 'selected' : '' }}>21% (Standard NL)</option>
                                    <option value="9" {{ $line->vat_rate == 9 ? 'selected' : '' }}>9% (Reduced NL)</option>
                                    <option value="0" {{ $line->vat_rate == 0 ? 'selected' : '' }}>0% (Export/Exempt)</option>
                                </select>
                            </div>
                            
                            <!-- Line Total -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Line Total</label>
                                <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-900 line-total">
                                    €{{ number_format($line->line_total_ex_vat ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Defer to Next Month Option -->
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center">
                                {{-- Hidden input ensures unchecked checkboxes send a value --}}
                                <input type="hidden" name="lines[{{ $line->id }}][defer_to_next_month]" value="0">
                                <input type="checkbox"
                                       name="lines[{{ $line->id }}][defer_to_next_month]"
                                       id="defer_{{ $line->id }}"
                                       value="1"
                                       {{ $line->defer_to_next_month ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="defer_{{ $line->id }}" class="ml-2 text-sm text-gray-700">
                                    Defer to next month
                                </label>
                            </div>
                            
                            @if($line->defer_to_next_month)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Deferred
                            </span>
                            @endif
                        </div>
                        
                        @if($line->source_type === 'time_entry' && $line->timeEntry)
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center text-sm text-blue-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Time Entry: {{ $line->timeEntry->date->format('M j, Y') }} • {{ $line->timeEntry->user->name }} • {{ $line->timeEntry->hours }}h</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500" id="no-lines-message">
                        No invoice lines yet. Add time entries or custom lines to get started.
                    </div>
                    @endforelse
                </div>

                <!-- Bulk Actions -->
                @if($invoice->lines->count() > 0)
                <div class="mt-6 flex justify-between items-center">
                    <div class="flex space-x-3">
                        <button type="button" 
                                onclick="selectAllLines()"
                                class="text-sm text-blue-600 hover:text-blue-800">
                            Select All
                        </button>
                        <button type="button" 
                                onclick="deselectAllLines()"
                                class="text-sm text-gray-600 hover:text-gray-800">
                            Deselect All
                        </button>
                        <button type="button" 
                                onclick="removeSelectedLines()"
                                class="text-sm text-red-600 hover:text-red-800">
                            Remove Selected
                        </button>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $invoice->lines->count() }} line(s)
                    </div>
                </div>
                @endif
            </div>

            <!-- Invoice Summary -->
            <div class="px-6 py-6 border-t border-gray-200 bg-gray-50">
                <div class="max-w-md ml-auto">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Subtotal (ex VAT):</span>
                            <span class="text-sm font-medium" id="subtotal-amount">€{{ number_format($invoice->subtotal_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">VAT Amount:</span>
                            <span class="text-sm font-medium" id="vat-amount">€{{ number_format($invoice->vat_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total Amount:</span>
                            <span id="total-amount">€{{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                <div class="flex space-x-3">
                    <a href="{{ route('invoices.show', $invoice) }}" 
                       class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    @can('delete', $invoice)
                    <button type="button" 
                            onclick="deleteInvoice({{ $invoice->id }})"
                            class="bg-red-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete Invoice
                    </button>
                    @endcan
                </div>
                <div class="flex space-x-3">
                    @php
                        $deferredCount = $invoice->lines()->where('defer_to_next_month', true)->count();
                    @endphp

                    @php
                        $hasDefers = $deferredCount > 0;
                    @endphp

                    <button type="submit"
                            name="action"
                            value="save_draft"
                            class="bg-gray-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Save Changes
                    </button>
                    <button type="submit"
                            name="action"
                            value="finalize"
                            class="bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save & Finalize
                    </button>
                </div>
            </div>
        </form>

        {{-- Execute Defers Form (outside main form to prevent conflicts) --}}
        @if($deferredCount > 0)
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Deferred Items</h3>
                            <p class="text-sm text-gray-600">{{ $deferredCount }} item(s) marked for next month</p>
                        </div>
                        <div class="flex space-x-3">
                            <form action="{{ route('invoices.execute-defers', $invoice) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Execute {{ $deferredCount }} deferred item(s)? This will remove them from this invoice and make them available for next month.')"
                                        class="bg-orange-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    🔄 Execute Defers Now
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add Time Entries Modal -->
<div id="timeEntriesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl rounded-md bg-white" style="box-shadow: var(--theme-card-shadow);">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add Time Entries</h3>
                <button onclick="closeTimeEntriesModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Time Entry Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="date" 
                       id="time-entry-start" 
                       placeholder="Start Date" 
                       class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <input type="date" 
                       id="time-entry-end" 
                       placeholder="End Date" 
                       class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <button type="button" 
                        id="load-available-entries"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Load Entries
                </button>
            </div>
            
            <div id="available-time-entries" class="max-h-96 overflow-y-auto">
                <div class="text-center py-8 text-gray-500">
                    Set date range and click "Load Entries" to view available time entries
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeTimeEntriesModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button onclick="addSelectedTimeEntries()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Add Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 rounded-md bg-white" style="box-shadow: var(--theme-card-shadow);">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Delete Invoice</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete this draft invoice? This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700">
                    Delete Invoice
                </button>
                <button onclick="closeDeleteModal()" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Line Template (Hidden) -->
<template id="custom-line-template">
    <div class="invoice-line bg-gray-50 p-4 rounded-lg border border-gray-200" data-line-id="new">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center">
                <div class="drag-handle cursor-move text-gray-400 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                </div>
                <h4 class="text-sm font-medium text-gray-900">Custom Line</h4>
            </div>
            <button type="button" 
                    class="remove-line text-red-600 hover:text-red-800"
                    onclick="removeLine(this)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" 
                       name="new_lines[INDEX][description]" 
                       placeholder="Service description"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       required>
                <input type="hidden" name="new_lines[INDEX][source_type]" value="custom">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" 
                       name="new_lines[INDEX][quantity]" 
                       value="1"
                       min="0"
                       step="0.01"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 quantity-input"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Price (€)</label>
                <input type="number" 
                       name="new_lines[INDEX][unit_price]" 
                       value="0"
                       min="0"
                       step="0.01"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 price-input"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">VAT Rate (%)</label>
                <select name="new_lines[INDEX][vat_rate]" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 vat-rate-select">
                    <option value="21">21% (Standard NL)</option>
                    <option value="9">9% (Reduced NL)</option>
                    <option value="0">0% (Export/Exempt)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Line Total</label>
                <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-900 line-total">
                    €0.00
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
// Global variables and functions for merge functionality
let sortable;

function initializeSortable() {
    const container = document.getElementById('invoice-lines-container');
    if (sortable) {
        sortable.destroy();
    }

    sortable = new Sortable(container, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        onEnd: function(evt) {
            calculateTotals();
        }
    });
}

function calculateTotals() {
    let subtotal = 0;
    let vatAmount = 0;

    document.querySelectorAll('.invoice-line').forEach(line => {
        const quantity = parseFloat(line.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(line.querySelector('.price-input').value) || 0;
        const vatRate = parseFloat(line.querySelector('.vat-rate-select').value) || 0;

        const lineSubtotal = quantity * price;
        const lineVat = lineSubtotal * (vatRate / 100);

        subtotal += lineSubtotal;
        vatAmount += lineVat;
    });

    const total = subtotal + vatAmount;

    document.getElementById('subtotal-amount').textContent = `€${subtotal.toFixed(2)}`;
    document.getElementById('vat-amount').textContent = `€${vatAmount.toFixed(2)}`;
    document.getElementById('total-amount').textContent = `€${total.toFixed(2)}`;
}

document.addEventListener('DOMContentLoaded', function() {
    let newLineIndex = 0;

    // Initialize sortable for invoice lines
    initializeSortable();

    // Add custom line
    document.getElementById('add-custom-line').addEventListener('click', function() {
        const template = document.getElementById('custom-line-template');
        const clone = template.content.cloneNode(true);
        
        // Replace INDEX with actual index
        const html = clone.querySelector('.invoice-line').outerHTML.replace(/INDEX/g, newLineIndex);
        
        const container = document.getElementById('invoice-lines-container');
        const noLinesMessage = document.getElementById('no-lines-message');
        if (noLinesMessage) {
            noLinesMessage.remove();
        }
        
        container.insertAdjacentHTML('beforeend', html);
        
        // Add event listeners to new line
        const newLine = container.lastElementChild;
        addLineListeners(newLine);
        
        newLineIndex++;
        calculateTotals();
        
        // Reinitialize sortable
        initializeSortable();
    });

    // Add time entries modal
    document.getElementById('add-time-entries').addEventListener('click', function() {
        document.getElementById('timeEntriesModal').classList.remove('hidden');
    });

    // Load available time entries
    document.getElementById('load-available-entries').addEventListener('click', function() {
        const customerId = document.getElementById('customer_id').value;
        const projectId = document.getElementById('project_id').value;
        const startDate = document.getElementById('time-entry-start').value;
        const endDate = document.getElementById('time-entry-end').value;
        
        if (!customerId) {
            alert('Customer is required to load time entries.');
            return;
        }
        
        const params = new URLSearchParams({
            customer_id: customerId,
            status: 'approved',
            uninvoiced: 'true'
        });
        
        if (projectId) params.append('project_id', projectId);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        
        const container = document.getElementById('available-time-entries');
        container.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div></div>';
        
        fetch(`/api/time-entries?${params.toString()}`)
            .then(response => response.json())
            .then(timeEntries => {
                renderAvailableTimeEntries(timeEntries);
            })
            .catch(error => {
                console.error('Error loading time entries:', error);
                container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading time entries</div>';
            });
    });

    function renderAvailableTimeEntries(timeEntries) {
        const container = document.getElementById('available-time-entries');
        
        if (timeEntries.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-500">No available time entries found</div>';
            return;
        }
        
        let html = '<div class="space-y-2">';
        html += '<div class="flex items-center mb-4">';
        html += '<input type="checkbox" id="select-all-available" class="rounded border-gray-300 text-blue-600 shadow-sm">';
        html += '<label for="select-all-available" class="ml-2 text-sm font-medium text-gray-700">Select All</label>';
        html += '</div>';
        
        timeEntries.forEach(entry => {
            const rate = entry.hourly_rate || 75; // Default rate
            const amount = entry.hours * rate;
            
            html += `
                <div class="flex items-center p-3 bg-white rounded-lg border border-gray-200">
                    <input type="checkbox" 
                           class="time-entry-checkbox rounded border-gray-300 text-blue-600 shadow-sm"
                           data-entry='${JSON.stringify(entry)}'>
                    <div class="ml-4 flex-1">
                        <div class="flex justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">${entry.description}</h4>
                                <p class="text-sm text-gray-600">${entry.user?.name} • ${entry.date} • ${entry.hours}h @ €${rate}/h</p>
                            </div>
                            <div class="text-sm font-medium text-gray-900">€${amount.toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        // Add select all functionality
        document.getElementById('select-all-available').addEventListener('change', function() {
            document.querySelectorAll('.time-entry-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    function addLineListeners(lineElement) {
        const quantityInput = lineElement.querySelector('.quantity-input');
        const priceInput = lineElement.querySelector('.price-input');
        const lineTotal = lineElement.querySelector('.line-total');
        const vatRateSelect = lineElement.querySelector('.vat-rate-select');
        
        function updateLineTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            lineTotal.textContent = `€${total.toFixed(2)}`;
            calculateTotals();
        }
        
        quantityInput.addEventListener('input', updateLineTotal);
        priceInput.addEventListener('input', updateLineTotal);
        vatRateSelect.addEventListener('change', calculateTotals);
    }

    // Add listeners to existing lines
    document.querySelectorAll('.invoice-line').forEach(line => {
        addLineListeners(line);
    });

    // Calculate initial totals
    calculateTotals();
});

// Modal functions
function closeTimeEntriesModal() {
    document.getElementById('timeEntriesModal').classList.add('hidden');
}

function addSelectedTimeEntries() {
    const selectedEntries = Array.from(document.querySelectorAll('.time-entry-checkbox:checked'))
        .map(cb => JSON.parse(cb.dataset.entry));
    
    selectedEntries.forEach(entry => {
        addTimeEntryLine(entry);
    });
    
    closeTimeEntriesModal();
    calculateTotals();
}

function addTimeEntryLine(entry) {
    const rate = entry.hourly_rate || 75;
    const lineId = `time_entry_${entry.id}`;
    
    const html = `
        <div class="invoice-line bg-gray-50 p-4 rounded-lg border border-gray-200" data-line-id="${lineId}">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="drag-handle cursor-move text-gray-400 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                        </svg>
                    </div>
                    <h4 class="text-sm font-medium text-gray-900">Time Entry - ${entry.user?.name}</h4>
                </div>
                <button type="button" class="remove-line text-red-600 hover:text-red-800" onclick="removeLine(this)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" 
                           name="time_entries[${entry.id}][description]" 
                           value="${entry.description}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                    <input type="hidden" name="time_entries[${entry.id}][time_entry_id]" value="${entry.id}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hours</label>
                    <input type="number" 
                           name="time_entries[${entry.id}][quantity]" 
                           value="${entry.hours}"
                           min="0"
                           step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 quantity-input"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rate (€/h)</label>
                    <input type="number" 
                           name="time_entries[${entry.id}][unit_price]" 
                           value="${rate}"
                           min="0"
                           step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 price-input"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">VAT Rate (%)</label>
                    <select name="time_entries[${entry.id}][vat_rate]" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 vat-rate-select">
                        <option value="21" selected>21% (Standard NL)</option>
                        <option value="9">9% (Reduced NL)</option>
                        <option value="0">0% (Export/Exempt)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Line Total</label>
                    <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-900 line-total">
                        €${(entry.hours * rate).toFixed(2)}
                    </div>
                </div>
            </div>
            
            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center text-sm text-blue-800">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Time Entry: ${entry.date} • ${entry.user?.name} • ${entry.hours}h</span>
                </div>
            </div>
        </div>
    `;
    
    const container = document.getElementById('invoice-lines-container');
    const noLinesMessage = document.getElementById('no-lines-message');
    if (noLinesMessage) {
        noLinesMessage.remove();
    }
    
    container.insertAdjacentHTML('beforeend', html);
    
    // Add listeners to new line
    const newLine = container.lastElementChild;
    addLineListeners(newLine);
}

// Line management functions
function removeLine(button) {
    const line = button.closest('.invoice-line');
    line.remove();
    calculateTotals();
    
    // Show no lines message if no lines left
    const container = document.getElementById('invoice-lines-container');
    if (container.children.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500" id="no-lines-message">No invoice lines yet. Add time entries or custom lines to get started.</div>';
    }
}

function selectAllLines() {
    document.querySelectorAll('.invoice-line input[type="checkbox"]').forEach(cb => {
        cb.checked = true;
    });
}

function deselectAllLines() {
    document.querySelectorAll('.invoice-line input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
}

function removeSelectedLines() {
    document.querySelectorAll('.invoice-line input[type="checkbox"]:checked').forEach(cb => {
        cb.closest('.invoice-line').remove();
    });
    calculateTotals();
}

// Update merge button visibility and count
function updateMergeButton() {
    const selectedLines = document.querySelectorAll('.line-selector:checked');
    const mergeButton = document.getElementById('merge-selected-lines');
    const mergeCount = document.getElementById('merge-count');
    
    if (selectedLines.length >= 2) {
        mergeButton.classList.remove('hidden');
        mergeCount.textContent = selectedLines.length;
    } else {
        mergeButton.classList.add('hidden');
    }
}

// Merge selected lines
function mergeSelectedLines() {
    const selectedCheckboxes = document.querySelectorAll('.line-selector:checked');

    if (selectedCheckboxes.length < 2) {
        alert('Please select at least 2 lines to merge');
        return;
    }

    // Validate that all selected lines are time entries under the same milestone and task
    const timeEntryLines = [];
    let firstMilestoneId = null;
    let firstTaskId = null;
    let milestoneName = '';
    let taskName = '';

    for (const checkbox of selectedCheckboxes) {
        const line = checkbox.closest('.invoice-line');
        const lineType = line.dataset.lineType;

        // Only allow merging of time entry lines (description type)
        if (lineType !== 'description') {
            alert('You can only merge time entry lines (bullet point lines). Please unselect milestone and task headers.');
            return;
        }

        const milestoneId = line.dataset.milestoneId;
        const taskId = line.dataset.taskId;

        // Set reference milestone/task from first line
        if (firstMilestoneId === null) {
            firstMilestoneId = milestoneId;
            firstTaskId = taskId;
            milestoneName = line.dataset.milestoneName;
            taskName = line.dataset.taskName;
        }

        // Check that all lines belong to the same milestone and task
        if (milestoneId !== firstMilestoneId || taskId !== firstTaskId) {
            alert(`All selected time entries must belong to the same milestone and task.\n\nFirst selection: ${milestoneName} → ${taskName}\nConflicting selection found.\n\nPlease select only time entries from the same milestone and task.`);
            return;
        }

        timeEntryLines.push(line);
    }

    console.log(`Merging ${timeEntryLines.length} time entries from: ${milestoneName} → ${taskName}`);
    
    // Collect data from selected lines
    const selectedLines = [];
    let totalQuantity = 0;
    let totalAmount = 0;
    let descriptions = [];
    let vatRates = [];
    
    selectedCheckboxes.forEach(checkbox => {
        const line = checkbox.closest('.invoice-line');
        const lineId = checkbox.dataset.lineId;
        const quantityInput = line.querySelector('.quantity-input');
        const priceInput = line.querySelector('.price-input');
        const descriptionInput = line.querySelector('input[name*="[description]"]');
        const vatRateSelect = line.querySelector('.vat-rate-select');
        
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const amount = quantity * price;
        
        totalQuantity += quantity;
        totalAmount += amount;
        descriptions.push(descriptionInput.value);
        vatRates.push(parseFloat(vatRateSelect.value));
        
        selectedLines.push({
            id: lineId,
            description: descriptionInput.value,
            quantity: quantity,
            price: price,
            amount: amount,
            vatRate: vatRateSelect.value
        });
    });
    
    // Get the highest VAT rate
    const maxVatRate = Math.max(...vatRates);
    
    // Create modal for merge confirmation
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Merge Invoice Lines</h3>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-blue-800 font-medium">Merging ${selectedLines.length} time entries from:</p>
                <p class="text-sm text-blue-700">${milestoneName} → ${taskName}</p>
            </div>
            <p class="text-sm text-gray-600 mb-4">You are about to merge ${selectedLines.length} lines into one. The original lines will be removed.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">New Description</label>
                    <textarea id="merge-description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">${descriptions.join('\n')}</textarea>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Quantity</label>
                        <input type="number" id="merge-quantity" value="${totalQuantity.toFixed(2)}" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                        <input type="number" id="merge-price" value="${(totalAmount / totalQuantity).toFixed(2)}" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">VAT Rate (%)</label>
                        <select id="merge-vat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="21" ${maxVatRate == 21 ? 'selected' : ''}>21% (Standard NL)</option>
                            <option value="9" ${maxVatRate == 9 ? 'selected' : ''}>9% (Reduced NL)</option>
                            <option value="0" ${maxVatRate == 0 ? 'selected' : ''}>0% (Export/Exempt)</option>
                        </select>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-3 rounded">
                    <p class="text-sm text-gray-700">Total Amount: <span class="font-bold">€${totalAmount.toFixed(2)}</span></p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="this.closest('.fixed').remove()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="button" onclick="confirmMerge(${JSON.stringify(selectedLines).replace(/"/g, '&quot;')}, '${firstMilestoneId}', '${firstTaskId}', '${milestoneName.replace(/'/g, "\\'")}', '${taskName.replace(/'/g, "\\'")}')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                    Merge Lines
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Confirm and execute merge
function confirmMerge(selectedLines, firstMilestoneId, firstTaskId, milestoneName, taskName) {
    const description = document.getElementById('merge-description').value;
    const quantity = document.getElementById('merge-quantity').value;
    const price = document.getElementById('merge-price').value;
    const vatRate = document.getElementById('merge-vat').value;
    
    // Remove selected lines
    selectedLines.forEach(line => {
        const lineElement = document.querySelector(`.invoice-line[data-line-id="${line.id}"]`);
        if (lineElement) {
            lineElement.remove();
        }
    });
    
    // Add new merged line
    const newLineId = 'merged_' + Date.now();
    const lineTotal = (parseFloat(quantity) * parseFloat(price)).toFixed(2);
    
    const newLineHtml = `
        <div class="invoice-line bg-purple-50 p-4 rounded-lg border border-purple-300"
             data-line-id="${newLineId}"
             data-line-type="description"
             data-milestone-id="${firstMilestoneId}"
             data-task-id="${firstTaskId}"
             data-milestone-name="${milestoneName}"
             data-task-name="${taskName}">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <input type="checkbox" class="line-selector mr-3 rounded border-gray-300 text-purple-600 focus:ring-purple-500" data-line-id="${newLineId}" onchange="updateMergeButton()">
                    <div class="drag-handle cursor-move text-gray-400 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                    </svg>
                    <h4 class="text-sm font-medium text-purple-900 font-semibold">Merged Line</h4>
                </div>
                <button type="button" class="remove-line text-red-600 hover:text-red-800" onclick="removeLine(this)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="lines[${newLineId}][description]" value="${description}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <input type="hidden" name="lines[${newLineId}][is_merged]" value="1">
                    <input type="hidden" name="lines[${newLineId}][merged_from]" value="${selectedLines.map(l => l.id).join(',')}">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" name="lines[${newLineId}][quantity]" value="${quantity}" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 quantity-input" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit Price (€)</label>
                    <input type="number" name="lines[${newLineId}][unit_price]" value="${price}" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 price-input" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">VAT Rate (%)</label>
                    <select name="lines[${newLineId}][vat_rate]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 vat-rate-select">
                        <option value="21" ${vatRate == 21 ? 'selected' : ''}>21% (Standard NL)</option>
                        <option value="9" ${vatRate == 9 ? 'selected' : ''}>9% (Reduced NL)</option>
                        <option value="0" ${vatRate == 0 ? 'selected' : ''}>0% (Export/Exempt)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Line Total</label>
                    <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-900 line-total">
                        €${lineTotal}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Find the correct position to insert the merged line (after the task header, but before next milestone/task)
    const container = document.getElementById('invoice-lines-container');
    const noLinesMessage = container.querySelector('#no-lines-message');
    if (noLinesMessage) {
        noLinesMessage.remove();
    }

    // Find the task header for this milestone/task combination
    let insertAfterElement = null;
    const allLines = container.querySelectorAll('.invoice-line');

    for (const line of allLines) {
        const lineType = line.dataset.lineType;
        const lineMilestoneId = line.dataset.milestoneId;
        const lineTaskId = line.dataset.taskId;

        // Found the matching task header
        if (lineType === 'task' && lineMilestoneId === firstMilestoneId && lineTaskId === firstTaskId) {
            insertAfterElement = line;
            continue;
        }

        // If we've found our task header, look for the last time entry under it
        if (insertAfterElement && lineType === 'description' &&
            lineMilestoneId === firstMilestoneId && lineTaskId === firstTaskId) {
            insertAfterElement = line;
        }

        // Stop if we hit a different milestone or task
        if (insertAfterElement && (lineType === 'milestone' || lineType === 'task') &&
            (lineMilestoneId !== firstMilestoneId || lineTaskId !== firstTaskId)) {
            break;
        }
    }

    // Insert the merged line after the determined position
    if (insertAfterElement) {
        insertAfterElement.insertAdjacentHTML('afterend', newLineHtml);
    } else {
        // Fallback: append at the end
        container.insertAdjacentHTML('beforeend', newLineHtml);
    }
    
    // Reinitialize sortable
    initializeSortable();
    
    // Clear checkboxes and update button
    updateMergeButton();
    
    // Recalculate totals
    calculateTotals();
    
    // Close modal
    const modal = document.querySelector('.fixed.inset-0.bg-gray-500.bg-opacity-75');
    if (modal) {
        modal.remove();
    }
}

// Delete invoice
function deleteInvoice(invoiceId) {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/invoices/{{ $invoice->id }}`;
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    
    const tokenField = document.createElement('input');
    tokenField.type = 'hidden';
    tokenField.name = '_token';
    tokenField.value = '{{ csrf_token() }}';
    
    form.appendChild(methodField);
    form.appendChild(tokenField);
    document.body.appendChild(form);
    form.submit();
});
</script>

<style>
.sortable-ghost {
    opacity: 0.5;
    background: #e5e7eb;
}

.drag-handle:hover {
    color: #6b7280;
}

/* Hierarchical invoice line styles */
.invoice-line[data-line-type="milestone"] {
    border-left: 4px solid #3b82f6;
    background: linear-gradient(to right, #dbeafe, #eff6ff);
}

.invoice-line[data-line-type="task"] {
    border-left: 4px solid #10b981;
    margin-left: 20px;
    background: linear-gradient(to right, #d1fae5, #f0fdf4);
}

.invoice-line[data-line-type="description"] {
    margin-left: 40px;
    border-left: 2px solid #d1d5db;
}

/* Visual indicators for line types */
.invoice-line[data-line-type="milestone"]::before {
    content: "MILESTONE";
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 10px;
    font-weight: bold;
    color: #3b82f6;
    background: white;
    padding: 2px 6px;
    border-radius: 4px;
}

.invoice-line[data-line-type="task"]::before {
    content: "TASK";
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 10px;
    font-weight: bold;
    color: #10b981;
    background: white;
    padding: 2px 6px;
    border-radius: 4px;
}

.invoice-line[data-line-type="description"]::before {
    content: "DETAIL";
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 10px;
    color: #6b7280;
    background: white;
    padding: 2px 6px;
    border-radius: 4px;
}

.invoice-line {
    position: relative;
}
</style>

<!-- Hidden Delete Form -->
<form id="delete-form" action="{{ route('invoices.destroy', $invoice) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endpush