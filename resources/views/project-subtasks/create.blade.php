@extends('layouts.app')

@section('title', 'Create Subtask')

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
                                    <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" class="ml-1 text-gray-500 hover:text-gray-700">{{ $milestone->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('project-milestones.tasks.show', [$milestone, $projectTask]) }}" class="ml-1 text-gray-500 hover:text-gray-700">{{ $projectTask->name }}</a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-gray-700 font-medium">New Subtask</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Create New Subtask</h1>
                    <p class="mt-1 text-sm text-gray-600">Add a new subtask to task: {{ $projectTask->name }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('project-tasks.subtasks.store', $projectTask) }}" method="POST">
            @csrf
            
            <div class="bg-white shadow rounded-lg">
                {{-- Basic Information --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Subtask Information</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Subtask Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status and Dates --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ old('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">
                                Start Date
                            </label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">
                                End Date
                            </label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Financial Settings --}}
                <div class="px-6 py-4 border-t border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Financial Settings</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        {{-- Fee Type --}}
                        <div>
                            <label for="fee_type" class="block text-sm font-medium text-gray-700">
                                Fee Type <span class="text-red-500">*</span>
                            </label>
                            <select name="fee_type" id="fee_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="in_fee" {{ old('fee_type', 'in_fee') === 'in_fee' ? 'selected' : '' }}>In Fee</option>
                                <option value="extended" {{ old('fee_type') === 'extended' ? 'selected' : '' }}>Extended</option>
                            </select>
                            @error('fee_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pricing Type --}}
                        <div>
                            <label for="pricing_type" class="block text-sm font-medium text-gray-700">
                                Pricing Type <span class="text-red-500">*</span>
                            </label>
                            <select name="pricing_type" id="pricing_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                onchange="togglePricingFields()">
                                <option value="hourly_rate" {{ old('pricing_type', 'hourly_rate') === 'hourly_rate' ? 'selected' : '' }}>Hourly Rate</option>
                                <option value="fixed_price" {{ old('pricing_type') === 'fixed_price' ? 'selected' : '' }}>Fixed Price</option>
                            </select>
                            @error('pricing_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fixed Price --}}
                        <div id="fixed_price_field" style="display: {{ old('pricing_type', 'hourly_rate') === 'fixed_price' ? 'block' : 'none' }}">
                            <label for="fixed_price" class="block text-sm font-medium text-gray-700">
                                Fixed Price (€)
                            </label>
                            <input type="number" name="fixed_price" id="fixed_price" step="0.01" min="0" value="{{ old('fixed_price') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('fixed_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Hourly Rate Override --}}
                        <div id="hourly_rate_field" style="display: {{ old('pricing_type', 'hourly_rate') === 'hourly_rate' ? 'block' : 'none' }}">
                            <label for="hourly_rate_override" class="block text-sm font-medium text-gray-700">
                                Hourly Rate Override (€)
                            </label>
                            <input type="number" name="hourly_rate_override" id="hourly_rate_override" step="0.01" min="0" value="{{ old('hourly_rate_override') }}"
                                placeholder="Leave empty to use task rate"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('hourly_rate_override')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Estimated Hours --}}
                        <div>
                            <label for="estimated_hours" class="block text-sm font-medium text-gray-700">
                                Estimated Hours
                            </label>
                            <input type="number" name="estimated_hours" id="estimated_hours" step="0.5" min="0" value="{{ old('estimated_hours') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('estimated_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $nextSortOrder ?? 0) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('project-milestones.tasks.show', [$milestone, $projectTask]) }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Create Subtask
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePricingFields() {
    const pricingType = document.getElementById('pricing_type').value;
    const fixedPriceField = document.getElementById('fixed_price_field');
    const hourlyRateField = document.getElementById('hourly_rate_field');
    
    if (pricingType === 'fixed_price') {
        fixedPriceField.style.display = 'block';
        hourlyRateField.style.display = 'none';
    } else {
        fixedPriceField.style.display = 'none';
        hourlyRateField.style.display = 'block';
    }
}
</script>
@endsection