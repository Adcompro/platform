@extends('layouts.app')

@section('title', 'Edit Service Category')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Service Category</h1>
                    <p class="text-sm text-gray-600">Update category: {{ $serviceCategory->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('service-categories.show', $serviceCategory) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-eye mr-2"></i>View Category
                    </a>
                    <a href="{{ route('service-categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

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

        {{-- Edit Form --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Category Information</h2>
            </div>
            
            <form method="POST" action="{{ route('service-categories.update', $serviceCategory) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Basic Information --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Category Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Category Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $serviceCategory->name) }}" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror"
                               placeholder="Enter category name">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-300 @enderror">
                            <option value="active" {{ old('status', $serviceCategory->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $serviceCategory->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror"
                              placeholder="Enter a detailed description of this service category">{{ old('description', $serviceCategory->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters</p>
                </div>

                {{-- Visual Settings --}}
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Visual Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Icon --}}
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                                Icon Class
                            </label>
                            <input type="text" name="icon" id="icon" value="{{ old('icon', $serviceCategory->icon) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('icon') border-red-300 @enderror"
                                   placeholder="fas fa-cogs">
                            @error('icon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">FontAwesome icon class (optional)</p>
                        </div>

                        {{-- Color --}}
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                Color
                            </label>
                            <div class="flex space-x-2">
                                <input type="color" name="color" id="color" value="{{ old('color', $serviceCategory->color ?? '#6B7280') }}"
                                       class="w-12 h-10 rounded border border-gray-300">
                                <input type="text" id="color-text" value="{{ old('color', $serviceCategory->color ?? '#6B7280') }}"
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('color') border-red-300 @enderror"
                                       placeholder="#6B7280">
                            </div>
                            @error('color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Hex color code for the category</p>
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $serviceCategory->sort_order) }}" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sort_order') border-red-300 @enderror">
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
                        </div>
                    </div>
                </div>

                {{-- Active Toggle --}}
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', $serviceCategory->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Category is active and visible
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Inactive categories are hidden from service selection</p>
                </div>

                {{-- Preview Section --}}
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preview</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="max-w-sm">
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center mb-2">
                                    <i id="preview-icon" class="{{ $serviceCategory->icon ?? 'fas fa-cogs' }} text-lg mr-2" style="color: {{ $serviceCategory->color ?? '#6B7280' }}"></i>
                                    <h3 id="preview-name" class="text-lg font-semibold text-gray-900">{{ $serviceCategory->name }}</h3>
                                </div>
                                <div class="flex items-center space-x-2 mb-2">
                                    <span id="preview-status" class="px-2 py-1 text-xs rounded-full {{ $serviceCategory->status_badge_class }}">
                                        {{ ucfirst($serviceCategory->status) }}
                                    </span>
                                    <span id="preview-active" class="px-2 py-1 text-xs rounded-full {{ $serviceCategory->is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $serviceCategory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <p id="preview-description" class="text-sm text-gray-600">
                                    {{ $serviceCategory->description ?: 'Category description will appear here...' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Category Stats --}}
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Category Statistics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $serviceCategory->services_count }}</div>
                            <div class="text-sm text-gray-600">Services in category</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $serviceCategory->formatted_created_at }}</div>
                            <div class="text-sm text-gray-600">Created on</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $serviceCategory->updated_at->format('M j, Y') }}</div>
                            <div class="text-sm text-gray-600">Last updated</div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <div>
                        @if($serviceCategory->can_be_deleted)
                            <button type="button" onclick="confirmDelete()" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-trash mr-2"></i>Delete Category
                            </button>
                        @else
                            <p class="text-sm text-gray-500">Cannot delete category with existing services</p>
                        @endif
                    </div>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('service-categories.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-save mr-2"></i>Update Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Form --}}
@if($serviceCategory->can_be_deleted)
<form id="delete-form" method="POST" action="{{ route('service-categories.destroy', $serviceCategory) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

{{-- JavaScript Section --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live preview functionaliteit
        const nameInput = document.getElementById('name');
        const statusSelect = document.getElementById('status');
        const descriptionTextarea = document.getElementById('description');
        const iconInput = document.getElementById('icon');
        const colorInput = document.getElementById('color');
        const colorTextInput = document.getElementById('color-text');
        const isActiveCheckbox = document.getElementById('is_active');

        // Preview elements
        const previewName = document.getElementById('preview-name');
        const previewStatus = document.getElementById('preview-status');
        const previewDescription = document.getElementById('preview-description');
        const previewIcon = document.getElementById('preview-icon');
        const previewActive = document.getElementById('preview-active');

        // Update preview naam
        nameInput.addEventListener('input', function() {
            previewName.textContent = this.value || 'Category Name';
        });

        // Update preview status
        statusSelect.addEventListener('change', function() {
            const status = this.value;
            previewStatus.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            previewStatus.className = status === 'active' 
                ? 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800'
                : 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800';
        });

        // Update preview beschrijving
        descriptionTextarea.addEventListener('input', function() {
            const desc = this.value.trim();
            previewDescription.textContent = desc || 'Category description will appear here...';
        });

        // Update preview icon
        iconInput.addEventListener('input', function() {
            const iconClass = this.value.trim() || 'fas fa-cogs';
            previewIcon.className = iconClass + ' text-lg mr-2';
        });

        // Update preview active status
        isActiveCheckbox.addEventListener('change', function() {
            previewActive.textContent = this.checked ? 'Active' : 'Inactive';
            previewActive.className = this.checked 
                ? 'px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800'
                : 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800';
        });

        // Color picker synchronisatie
        colorInput.addEventListener('input', function() {
            const color = this.value;
            colorTextInput.value = color;
            previewIcon.style.color = color;
        });

        colorTextInput.addEventListener('input', function() {
            const color = this.value;
            if (/^#[0-9A-F]{6}$/i.test(color)) {
                colorInput.value = color;
                previewIcon.style.color = color;
            }
        });

        // Character counter voor description
        const maxLength = 1000;
        const charCounter = document.createElement('div');
        charCounter.className = 'text-sm text-gray-500 mt-1';
        charCounter.textContent = `${descriptionTextarea.value.length} / ${maxLength} characters`;
        descriptionTextarea.parentNode.appendChild(charCounter);

        descriptionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = `${length} / ${maxLength} characters`;
            charCounter.className = length > maxLength 
                ? 'text-sm text-red-500 mt-1' 
                : 'text-sm text-gray-500 mt-1';
        });
    });

    // Delete confirmation
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this service category? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endpush