@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center mb-8">
        <a href="{{ route('invoices.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Create New Invoice</h1>
            <p class="mt-2 text-gray-600">Generate an invoice from project time entries and services</p>
        </div>
    </div>

    <!-- Invoice Creation Form -->
    <div class="bg-white rounded-lg" style="box-shadow: var(--theme-card-shadow);">
        <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form" class="space-y-6">
            @csrf
            
            <!-- Invoice Header -->
            <div class="px-6 py-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Invoice Information</h3>
                <p class="mt-1 text-sm text-gray-600">Basic invoice details and billing information</p>
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
                            <option value="{{ $company->id }}" {{ old('invoicing_company_id') == $company->id ? 'selected' : '' }}>
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
                    <input type="hidden" name="invoicing_company_id" value="{{ $defaultCompany?->id }}">
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
                                    Invoicing from: <strong>{{ $defaultCompany?->name ?? 'No Company Found' }}</strong>
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
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
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
                            <!-- Projects will be loaded via JavaScript based on customer selection -->
                        </select>
                        @error('project_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                               value="{{ old('period_start') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('period_start') border-red-300 @enderror">
                        @error('period_start')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Leave empty for one-time invoice</p>
                    </div>

                    <div>
                        <label for="period_end" class="block text-sm font-medium text-gray-700">
                            Period End Date
                        </label>
                        <input type="date" 
                               name="period_end" 
                               id="period_end" 
                               value="{{ old('period_end') }}"
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
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Time Entries Section -->
            <div class="px-6 py-6 border-t border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Time Entries</h3>
                        <p class="mt-1 text-sm text-gray-600">Select time entries to include in this invoice</p>
                    </div>
                    <button type="button" 
                            id="load-time-entries"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Load Time Entries
                    </button>
                </div>

                <div id="time-entries-container" class="space-y-4">
                    <div class="text-center py-8 text-gray-500">
                        Select customer and period, then click "Load Time Entries" to view available time entries
                    </div>
                </div>
            </div>

            <!-- Services Section -->
            <div class="px-6 py-6 border-t border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Services & Additional Items</h3>
                        <p class="mt-1 text-sm text-gray-600">Add services and additional cost items</p>
                    </div>
                    <button type="button" 
                            id="add-service-line"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Service
                    </button>
                </div>

                <div id="service-lines-container" class="space-y-4">
                    <!-- Service lines will be added here via JavaScript -->
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="px-6 py-6 border-t border-gray-200 bg-gray-50">
                <div class="max-w-md ml-auto">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Subtotal (ex VAT):</span>
                            <span class="text-sm font-medium" id="subtotal-amount">€0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">VAT Amount:</span>
                            <span class="text-sm font-medium" id="vat-amount">€0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total Amount:</span>
                            <span id="total-amount">€0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                <a href="{{ route('invoices.index') }}" 
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <div class="flex space-x-3">
                    <button type="submit" 
                            name="action" 
                            value="save_draft"
                            class="bg-gray-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Save as Draft
                    </button>
                    <button type="submit" 
                            name="action" 
                            value="finalize"
                            class="bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create & Finalize
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Service Line Template (Hidden) -->
<template id="service-line-template">
    <div class="service-line bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div class="flex justify-between items-start mb-4">
            <h4 class="text-sm font-medium text-gray-900">Service Item</h4>
            <button type="button" class="remove-service-line text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" 
                       name="service_lines[INDEX][description]" 
                       placeholder="Service description"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" 
                       name="service_lines[INDEX][quantity]" 
                       value="1"
                       min="0"
                       step="0.01"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 quantity-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Price (€)</label>
                <input type="number" 
                       name="service_lines[INDEX][unit_price]" 
                       value="0"
                       min="0"
                       step="0.01"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 price-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Line Total</label>
                <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm font-medium text-gray-900 line-total">
                    €0.00
                </div>
            </div>
        </div>
        
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">VAT Rate (%)</label>
                <select name="service_lines[INDEX][vat_rate]" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 vat-rate-select">
                    <option value="21">21% (Standard NL)</option>
                    <option value="9">9% (Reduced NL)</option>
                    <option value="0">0% (Export/Exempt)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Fee Type</label>
                <select name="service_lines[INDEX][fee_type]" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="in_fee">In Fee (counts towards project budget)</option>
                    <option value="additional">Additional (outside project budget)</option>
                </select>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let serviceLineIndex = 0;

    // Customer change handler - load projects
    document.getElementById('customer_id').addEventListener('change', function() {
        const customerId = this.value;
        const projectSelect = document.getElementById('project_id');

        // Clear current projects
        projectSelect.innerHTML = '<option value="">Select project (optional)</option>';

        if (customerId) {
            // Load projects for selected customer
            fetch(`/api/customers/${customerId}/projects`)
                .then(response => response.json())
                .then(projects => {
                    projects.forEach(project => {
                        const option = document.createElement('option');
                        option.value = project.id;
                        // Toon "Customer Name - Project Name" voor betere overzichtelijkheid
                        option.textContent = project.display_name || project.name;

                        // Voeg status en monthly fee toe als data attributen (voor eventuele filtering later)
                        option.setAttribute('data-status', project.status);
                        if (project.monthly_fee) {
                            option.setAttribute('data-monthly-fee', project.monthly_fee);
                        }

                        projectSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading projects:', error);
                    alert('Error loading projects. Please try again.');
                });
        }
    });

    // Load time entries
    document.getElementById('load-time-entries').addEventListener('click', function() {
        const customerId = document.getElementById('customer_id').value;
        const projectId = document.getElementById('project_id').value;
        const periodStart = document.getElementById('period_start').value;
        const periodEnd = document.getElementById('period_end').value;
        
        if (!customerId) {
            alert('Please select a customer first.');
            return;
        }
        
        // Build query parameters
        const params = new URLSearchParams({
            customer_id: customerId,
            status: 'approved',
            uninvoiced: 'true'
        });
        
        if (projectId) params.append('project_id', projectId);
        if (periodStart) params.append('period_start', periodStart);
        if (periodEnd) params.append('period_end', periodEnd);
        
        // Show loading state
        const container = document.getElementById('time-entries-container');
        container.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-500">Loading time entries...</p></div>';
        
        // Fetch time entries
        fetch(`/api/time-entries?${params.toString()}`)
            .then(response => response.json())
            .then(timeEntries => {
                renderTimeEntries(timeEntries);
                calculateTotals();
            })
            .catch(error => {
                console.error('Error loading time entries:', error);
                container.innerHTML = '<div class="text-center py-8 text-red-500">Error loading time entries. Please try again.</div>';
            });
    });

    function renderTimeEntries(timeEntries) {
        const container = document.getElementById('time-entries-container');
        
        if (timeEntries.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-gray-500">No approved time entries found for the selected criteria.</div>';
            return;
        }
        
        let html = '<div class="space-y-2">';
        html += '<div class="flex items-center mb-4">';
        html += '<input type="checkbox" id="select-all-time-entries" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">';
        html += '<label for="select-all-time-entries" class="ml-2 text-sm font-medium text-gray-700">Select All</label>';
        html += '<span class="ml-4 text-sm text-gray-500">(' + timeEntries.length + ' entries found)</span>';
        html += '</div>';
        
        timeEntries.forEach(entry => {
            const rate = entry.hourly_rate || entry.task?.hourly_rate || entry.milestone?.hourly_rate || entry.project?.hourly_rate || entry.user?.company?.default_hourly_rate || 0;
            const amount = entry.hours * rate;
            
            html += `
                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <input type="checkbox" 
                           name="time_entry_ids[]" 
                           value="${entry.id}" 
                           class="time-entry-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           data-amount="${amount}">
                    <div class="ml-4 flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">${entry.description}</h4>
                                <p class="text-sm text-gray-600">
                                    ${entry.user?.name} • ${entry.date} • ${entry.hours}h @ €${rate}/h
                                </p>
                                <p class="text-xs text-gray-500">
                                    ${entry.project?.name}${entry.milestone ? ' → ' + entry.milestone.title : ''}${entry.task ? ' → ' + entry.task.title : ''}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">€${amount.toFixed(2)}</div>
                                <div class="text-xs text-gray-500">${entry.company?.name}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        // Add select all functionality
        document.getElementById('select-all-time-entries').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.time-entry-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            calculateTotals();
        });
        
        // Add individual checkbox listeners
        document.querySelectorAll('.time-entry-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', calculateTotals);
        });
    }

    // Add service line
    document.getElementById('add-service-line').addEventListener('click', function() {
        const template = document.getElementById('service-line-template');
        const clone = template.content.cloneNode(true);
        
        // Replace INDEX with actual index
        const html = clone.querySelector('.service-line').outerHTML.replace(/INDEX/g, serviceLineIndex);
        
        const container = document.getElementById('service-lines-container');
        container.insertAdjacentHTML('beforeend', html);
        
        // Add event listeners to new service line
        const newLine = container.lastElementChild;
        addServiceLineListeners(newLine);
        
        serviceLineIndex++;
    });

    function addServiceLineListeners(serviceLineElement) {
        // Remove button
        serviceLineElement.querySelector('.remove-service-line').addEventListener('click', function() {
            serviceLineElement.remove();
            calculateTotals();
        });
        
        // Calculation inputs
        const quantityInput = serviceLineElement.querySelector('.quantity-input');
        const priceInput = serviceLineElement.querySelector('.price-input');
        const lineTotal = serviceLineElement.querySelector('.line-total');
        
        function updateLineTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            lineTotal.textContent = `€${total.toFixed(2)}`;
            calculateTotals();
        }
        
        quantityInput.addEventListener('input', updateLineTotal);
        priceInput.addEventListener('input', updateLineTotal);
        
        // VAT rate change
        serviceLineElement.querySelector('.vat-rate-select').addEventListener('change', calculateTotals);
    }

    function calculateTotals() {
        let subtotal = 0;
        let vatAmount = 0;
        
        // Time entries
        document.querySelectorAll('.time-entry-checkbox:checked').forEach(checkbox => {
            subtotal += parseFloat(checkbox.dataset.amount) || 0;
        });
        
        // Service lines
        document.querySelectorAll('.service-line').forEach(line => {
            const quantity = parseFloat(line.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(line.querySelector('.price-input').value) || 0;
            const vatRate = parseFloat(line.querySelector('.vat-rate-select').value) || 0;
            
            const lineSubtotal = quantity * price;
            const lineVat = lineSubtotal * (vatRate / 100);
            
            subtotal += lineSubtotal;
            vatAmount += lineVat;
        });
        
        // Add VAT for time entries (assume 21% default)
        const timeEntriesVat = subtotal * 0.21; // This should come from company settings
        
        const total = subtotal + vatAmount + timeEntriesVat;
        
        document.getElementById('subtotal-amount').textContent = `€${subtotal.toFixed(2)}`;
        document.getElementById('vat-amount').textContent = `€${(vatAmount + timeEntriesVat).toFixed(2)}`;
        document.getElementById('total-amount').textContent = `€${total.toFixed(2)}`;
    }

    // Period dates auto-fill
    document.getElementById('period_start').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0); // Last day of month
        
        if (!document.getElementById('period_end').value) {
            document.getElementById('period_end').value = endDate.toISOString().split('T')[0];
        }
    });
});
</script>
@endpush