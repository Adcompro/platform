@extends('layouts.app')

@section('title', 'Edit Service - ' . $service->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Moderne uitstraling met glassmorphism --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Edit Service</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Update service information and pricing</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('services.show', $service) }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View Service
                    </a>
                    <a href="{{ route('services.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Services
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50/50 border border-green-200/50 text-green-700 px-3 py-2.5 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-green-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50/50 border border-red-200/50 text-red-700 px-3 py-2.5 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium">There were errors with your submission</h3>
                        <ul class="mt-1 text-xs list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Edit Form --}}
        <form method="POST" action="{{ route('services.update', $service) }}">
            @csrf
            @method('PUT')

            {{-- Basic Information --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Basic Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Service Name --}}
                        <div>
                            <label for="name" class="block text-xs font-medium text-slate-600 mb-1">
                                Service Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name', $service->name) }}"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors @error('name') border-red-300 @enderror"
                                   placeholder="e.g., Complete Website Development">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="service_category_id" class="block text-xs font-medium text-slate-600 mb-1">
                                Category
                            </label>
                            <select name="service_category_id" id="service_category_id"
                                    class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors @error('service_category_id') border-red-300 @enderror">
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_category_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-xs font-medium text-slate-600 mb-1">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  autocomplete="off"
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors @error('description') border-red-300 @enderror"
                                  placeholder="Describe what this service includes...">{{ old('description', $service->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Pricing Information --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Pricing Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Price --}}
                        <div>
                            <label for="total_price" class="block text-xs font-medium text-slate-600 mb-1">
                                Price (€) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500 text-sm">€</span>
                                </div>
                                <input type="number" name="total_price" id="total_price" required
                                       value="{{ old('total_price', $service->total_price) }}"
                                       step="0.01" min="0"
                                       autocomplete="off"
                                       class="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors @error('total_price') border-red-300 @enderror"
                                       placeholder="0.00">
                            </div>
                            @error('total_price')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Estimated Hours (Auto-calculated) --}}
                        <div>
                            <label for="estimated_hours_display" class="block text-xs font-medium text-slate-600 mb-1">
                                Estimated Hours <span class="text-slate-400">(Auto-calculated)</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="estimated_hours_display"
                                       value="{{ number_format($service->calculateEstimatedHours(), 2) }}"
                                       readonly
                                       class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg bg-slate-50 text-slate-600 cursor-not-allowed"
                                       placeholder="0">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Automatically calculated from all tasks and subtasks</p>
                            {{-- Hidden field voor form submission --}}
                            <input type="hidden" name="estimated_hours" value="{{ $service->calculateEstimatedHours() }}">
                        </div>

                        {{-- SKU Code --}}
                        <div>
                            <label for="sku_code" class="block text-xs font-medium text-slate-600 mb-1">
                                SKU Code
                            </label>
                            <input type="text" name="sku_code" id="sku_code"
                                   value="{{ old('sku_code', $service->sku_code) }}"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors @error('sku_code') border-red-300 @enderror"
                                   placeholder="e.g., SRV-001">
                            @error('sku_code')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Service Settings --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Service Settings</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Service Type --}}
                        <div>
                            <label for="is_package" class="block text-xs font-medium text-slate-600 mb-1">
                                Service Type
                            </label>
                            <select name="is_package" id="is_package"
                                    class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors @error('is_package') border-red-300 @enderror">
                                <option value="1" {{ old('is_package', $service->is_package) == '1' ? 'selected' : '' }}>Package</option>
                                <option value="0" {{ old('is_package', $service->is_package) == '0' ? 'selected' : '' }}>Individual Service</option>
                            </select>
                            @error('is_package')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="is_active" class="block text-xs font-medium text-slate-600 mb-1">
                                Status
                            </label>
                            <select name="is_active" id="is_active"
                                    class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-colors @error('is_active') border-red-300 @enderror">
                                <option value="1" {{ old('is_active', $service->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $service->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Public Service Checkbox --}}
                    <div class="flex items-center">
                        <input type="checkbox" name="is_public" id="is_public" value="1"
                               {{ old('is_public', $service->is_public) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                        <label for="is_public" class="ml-2 text-sm text-slate-700">
                            Show this service on public website
                        </label>
                    </div>
                </div>
            </div>

            {{-- Service Structure Info --}}
            <div class="bg-blue-50/50 border border-blue-200/50 rounded-xl p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Service Structure</h3>
                        <div class="mt-1 text-xs text-blue-700">
                            <p>This service has {{ $service->milestones->count() }} milestones. To manage the service structure (milestones, tasks, subtasks), use the "Manage Structure" button.</p>
                            <a href="{{ route('services.structure', $service) }}" class="mt-2 inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-700">
                                Manage Structure →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex justify-end space-x-2">
                <a href="{{ route('services.show', $service) }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200">
                    Update Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection