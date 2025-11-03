@extends('layouts.app')

@section('title', 'Add Monthly Recurring Cost - ' . $project->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add Monthly Recurring Cost</h1>
                    <p class="text-sm text-gray-600">{{ $project->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('projects.additional-costs.index', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Costs
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium">There were errors with your submission</h3>
                        <div class="mt-2 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <form action="{{ route('projects.additional-costs.store-monthly', $project) }}" method="POST">
                @csrf
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Monthly Cost Details</h2>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Cost Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name') }}"
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               placeholder="e.g., Web Hosting, Office 365 License">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                  placeholder="Detailed description of the recurring cost">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Monthly Amount --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">
                            Monthly Amount (€) <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">€</span>
                            </div>
                            <input type="number" name="amount" id="amount" required
                                   value="{{ old('amount') }}"
                                   min="0" step="0.01"
                                   class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-8 pr-3 sm:text-sm border-gray-300 rounded-md"
                                   placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">/month</span>
                            </div>
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Start and End Date Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Start Date --}}
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" required
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- End Date --}}
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">
                                End Date <span class="text-sm text-gray-500">(Optional - leave empty for ongoing)</span>
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                   value="{{ old('end_date') }}"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Fee Type and Category Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Fee Type --}}
                        <div>
                            <label for="fee_type" class="block text-sm font-medium text-gray-700">
                                Budget Type <span class="text-red-500">*</span>
                            </label>
                            <select name="fee_type" id="fee_type" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select budget type</option>
                                @foreach(\App\Models\ProjectAdditionalCost::FEE_TYPES as $value => $label)
                                    <option value="{{ $value }}" {{ old('fee_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fee_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="category" id="category" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select category</option>
                                @foreach(\App\Models\ProjectAdditionalCost::CATEGORIES as $value => $label)
                                    <option value="{{ $value }}" {{ old('category', 'hosting') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Vendor and Reference Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Vendor --}}
                        <div>
                            <label for="vendor" class="block text-sm font-medium text-gray-700">
                                Vendor/Supplier
                            </label>
                            <input type="text" name="vendor" id="vendor"
                                   value="{{ old('vendor') }}"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                   placeholder="e.g., DigitalOcean, Microsoft">
                            @error('vendor')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Reference Number --}}
                        <div>
                            <label for="reference" class="block text-sm font-medium text-gray-700">
                                Account/Reference Number
                            </label>
                            <input type="text" name="reference" id="reference"
                                   value="{{ old('reference') }}"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                   placeholder="e.g., Account #12345">
                            @error('reference')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Auto Invoice Checkbox --}}
                    <div>
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="auto_invoice" id="auto_invoice" value="1"
                                       {{ old('auto_invoice', true) ? 'checked' : '' }}
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="auto_invoice" class="font-medium text-gray-700">
                                    Include in automatic monthly invoicing
                                </label>
                                <p class="text-gray-500">When checked, this cost will be automatically included in monthly invoices.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">
                            Internal Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                  placeholder="Any additional notes (not visible to customer)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hidden field for cost_type --}}
                    <input type="hidden" name="cost_type" value="monthly_recurring">
                </div>

                {{-- Form Actions --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('projects.additional-costs.index', $project) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Create Monthly Cost
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">About Monthly Recurring Costs</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Monthly costs are automatically calculated in budget tracking</li>
                            <li>Set an end date to automatically stop the cost on a specific date</li>
                            <li>Costs marked as "Within Budget" count against the monthly project budget</li>
                            <li>Costs marked as "Additional Cost" are billed separately from the budget</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection