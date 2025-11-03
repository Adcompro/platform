@extends('layouts.app')

@section('title', 'Edit Milestone')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li><a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">Projects</a></li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('projects.show', $project) }}" class="ml-1 text-gray-500 hover:text-gray-700">{{ $project->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('projects.milestones.index', $project) }}" class="ml-1 text-gray-500 hover:text-gray-700">Milestones</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-gray-700 font-medium">Edit {{ $milestone->name }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit Milestone</h1>
                    <p class="text-sm text-gray-600">Update milestone information for {{ $project->name }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('projects.milestones.update', [$project, $milestone]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow rounded-lg">
                {{-- Basic Information --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Basic Information</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Milestone Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $milestone->name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description', $milestone->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status and Dates --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                            <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('status') border-red-300 @enderror">
                                <option value="pending" {{ old('status', $milestone->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ old('status', $milestone->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ old('status', $milestone->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ old('status', $milestone->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $milestone->start_date?->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('start_date') border-red-300 @enderror">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $milestone->end_date?->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('end_date') border-red-300 @enderror">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial Settings --}}
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Financial Settings</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Fee Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fee Type *</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="fee_type" value="in_fee" {{ old('fee_type', $milestone->fee_type) == 'in_fee' ? 'checked' : '' }}
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2">In Fee</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="fee_type" value="extended" {{ old('fee_type', $milestone->fee_type) == 'extended' ? 'checked' : '' }}
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2">Extended (Additional Cost)</span>
                            </label>
                        </div>
                        @error('fee_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pricing Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pricing Type *</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pricing_type" value="hourly_rate" {{ old('pricing_type', $milestone->pricing_type) == 'hourly_rate' ? 'checked' : '' }}
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    onchange="togglePricingFields()">
                                <span class="ml-2">Hourly Rate</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pricing_type" value="fixed_price" {{ old('pricing_type', $milestone->pricing_type) == 'fixed_price' ? 'checked' : '' }}
                                    class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    onchange="togglePricingFields()">
                                <span class="ml-2">Fixed Price</span>
                            </label>
                        </div>
                        @error('pricing_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pricing Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div id="fixed_price_field" style="{{ old('pricing_type', $milestone->pricing_type) == 'fixed_price' ? '' : 'display:none' }}">
                            <label for="fixed_price" class="block text-sm font-medium text-gray-700">Fixed Price (€)</label>
                            <input type="number" name="fixed_price" id="fixed_price" value="{{ old('fixed_price', $milestone->fixed_price) }}" step="0.01" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('fixed_price') border-red-300 @enderror">
                            @error('fixed_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hourly_rate_override" class="block text-sm font-medium text-gray-700">Hourly Rate Override (€)</label>
                            <input type="number" name="hourly_rate_override" id="hourly_rate_override" value="{{ old('hourly_rate_override', $milestone->hourly_rate_override) }}" step="0.01" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('hourly_rate_override') border-red-300 @enderror"
                                placeholder="Leave empty to use project default">
                            @error('hourly_rate_override')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="estimated_hours" class="block text-sm font-medium text-gray-700">Estimated Hours</label>
                            <input type="number" name="estimated_hours" id="estimated_hours" value="{{ old('estimated_hours', $milestone->estimated_hours) }}" step="0.5" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('estimated_hours') border-red-300 @enderror">
                            @error('estimated_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Invoicing Trigger --}}
                    <div>
                        <label for="invoicing_trigger" class="block text-sm font-medium text-gray-700">Invoicing Trigger *</label>
                        <select name="invoicing_trigger" id="invoicing_trigger" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('invoicing_trigger') border-red-300 @enderror">
                            <option value="completion" {{ old('invoicing_trigger', $milestone->invoicing_trigger) == 'completion' ? 'selected' : '' }}>On Completion</option>
                            <option value="approval" {{ old('invoicing_trigger', $milestone->invoicing_trigger) == 'approval' ? 'selected' : '' }}>On Approval</option>
                            <option value="delivery" {{ old('invoicing_trigger', $milestone->invoicing_trigger) == 'delivery' ? 'selected' : '' }}>On Delivery</option>
                        </select>
                        @error('invoicing_trigger')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Additional Information --}}
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Additional Information</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Deliverables --}}
                    <div>
                        <label for="deliverables" class="block text-sm font-medium text-gray-700">Deliverables</label>
                        <textarea name="deliverables" id="deliverables" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('deliverables') border-red-300 @enderror"
                            placeholder="List the expected deliverables for this milestone">{{ old('deliverables', $milestone->deliverables) }}</textarea>
                        @error('deliverables')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $milestone->sort_order) }}" min="0"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('sort_order') border-red-300 @enderror">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" 
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Update Milestone
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePricingFields() {
    const pricingType = document.querySelector('input[name="pricing_type"]:checked').value;
    const fixedPriceField = document.getElementById('fixed_price_field');
    
    if (pricingType === 'fixed_price') {
        fixedPriceField.style.display = 'block';
        document.getElementById('fixed_price').required = true;
    } else {
        fixedPriceField.style.display = 'none';
        document.getElementById('fixed_price').required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePricingFields();
});
</script>
@endsection