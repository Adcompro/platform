@extends('layouts.app')

@section('title', 'Create Service Category')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Service Category</h1>
                    <p class="text-sm text-gray-600">Add a new category to organize your services</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('service-categories.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:bg-gray-50 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Create Form --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Category Information</h2>
            </div>

            <form method="POST" action="{{ route('service-categories.store') }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left Column --}}
                    <div class="space-y-6">
                        {{-- Category Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Category Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror"
                                   placeholder="Enter category name">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="4"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror"
                                      placeholder="Describe what this category is for...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select name="status" 
                                    id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-300 @enderror">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-6">
                        {{-- Icon --}}
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                                Icon <span class="text-gray-500">(FontAwesome class)</span>
                            </label>
                            <div class="flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    fas fa-
                                </span>
                                <input type="text" 
                                       name="icon" 
                                       id="icon" 
                                       value="{{ old('icon') }}"
                                       class="flex-1 rounded-none rounded-r-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('icon') border-red-300 @enderror"
                                       placeholder="tags">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Enter FontAwesome icon name (e.g., "tags", "cogs", "star")
                            </p>
                            @error('icon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Color --}}
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                Color <span class="text-gray-500">(Optional)</span>
                            </label>
                            <div class="flex items-center space-x-3">
                                <input type="color" 
                                       id="color_picker" 
                                       value="{{ old('color', '#3B82F6') }}"
                                       class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                                <input type="text" 
                                       name="color" 
                                       id="color_text" 
                                       value="{{ old('color', '#3B82F6') }}"
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="#3B82F6">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Choose a color for this category
                            </p>
                            @error('color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order <span class="text-gray-500">(Optional)</span>
                            </label>
                            <input type="number" 
                                   name="sort_order" 
                                   id="sort_order" 
                                   value="{{ old('sort_order') }}"
                                   min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sort_order') border-red-300 @enderror"
                                   placeholder="Leave empty for automatic ordering">
                            <p class="mt-1 text-sm text-gray-500">
                                Lower numbers appear first. Leave empty for automatic ordering.
                            </p>
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('service-categories.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:bg-gray-50 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-save mr-2"></i>Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript Section --}}
@push('scripts')
<script>
    // Color picker synchronization
    document.addEventListener('DOMContentLoaded', function() {
        const colorPicker = document.getElementById('color_picker');
        const colorText = document.getElementById('color_text');
        
        // Update text input when color picker changes
        colorPicker.addEventListener('change', function() {
            colorText.value = this.value;
        });
        
        // Update color picker when text input changes
        colorText.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorPicker.value = this.value;
            }
        });
        
        // Icon preview (optional enhancement)
        const iconInput = document.getElementById('icon');
        iconInput.addEventListener('input', function() {
            console.log('Icon preview: fas fa-' + this.value);
        });
    });
</script>
@endpush