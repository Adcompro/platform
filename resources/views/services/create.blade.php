@extends('layouts.app')

@section('title', 'Create Service')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Moderne uitstraling met glassmorphism --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 style="font-size: 1.25rem; font-weight: 600; color: var(--theme-text);">Create Service</h1>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.125rem;">Add a new service offering to your catalog</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('services.index') }}" 
                       style="display: inline-flex; align-items: center; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.5); color: var(--theme-text); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s;"
                       onmouseover="this.style.background='rgba(var(--theme-border-rgb), 0.1)'; this.style.borderColor='rgba(var(--theme-border-rgb), 0.7)';"
                       onmouseout="this.style.background='white'; this.style.borderColor='rgba(var(--theme-border-rgb), 0.5)';">
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
                        <h3 style="font-size: var(--theme-font-size); font-weight: 500;">There were errors with your submission</h3>
                        <ul style="margin-top: 0.25rem; font-size: var(--theme-font-size); list-style-type: disc; list-style-position: inside;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Create Form --}}
        <form method="POST" action="{{ route('services.store') }}">
            @csrf

            {{-- Basic Information --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 500; color: var(--theme-text);">Basic Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Service Name --}}
                        <div>
                            <label for="name" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                                Service Name <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name') }}"
                                   style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s; @error('name') border-color: #fca5a5; @enderror"
                                   class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="e.g., Complete Website Development">
                            @error('name')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="service_category_id" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                                Category
                            </label>
                            <select name="service_category_id" id="service_category_id"
                                    style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s; @error('service_category_id') border-color: #fca5a5; @enderror"
                                    class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('service_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_category_id')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s; @error('description') border-color: #fca5a5; @enderror"
                                  class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                  placeholder="Describe what this service includes...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Pricing Information --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 500; color: var(--theme-text);">Pricing Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Price --}}
                        <div>
                            <label for="price" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                                Price (€) <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">€</span>
                                </div>
                                <input type="number" name="total_price" id="total_price" required
                                       value="{{ old('total_price') }}"
                                       step="0.01" min="0"
                                       style="width: 100%; padding: 0.375rem 0.75rem 0.375rem 2rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s; @error('total_price') border-color: #fca5a5; @enderror"
                                       class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                       placeholder="0.00">
                            </div>
                            @error('total_price')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Estimated Hours --}}
                        <div>
                            <label for="estimated_hours" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                                Estimated Hours <span style="color: var(--theme-text-muted); opacity: 0.7;">(Auto-calculated after adding tasks)</span>
                            </label>
                            <input type="number" name="estimated_hours" id="estimated_hours"
                                   value="0"
                                   readonly
                                   style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); background: #f8fafc; color: var(--theme-text-muted); cursor: not-allowed;"
                                   placeholder="0">
                            <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Will be calculated from tasks after service creation</p>
                        </div>

                        {{-- SKU Code --}}
                        <div>
                            <label for="sku_code" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                                SKU Code
                            </label>
                            <input type="text" name="sku_code" id="sku_code"
                                   value="{{ old('sku_code') }}"
                                   style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s; @error('sku_code') border-color: #fca5a5; @enderror"
                                   class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="e.g., SRV-001">
                            @error('sku_code')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Service Settings --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 500; color: var(--theme-text);">Service Settings</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Service Type --}}
                        <div>
                            <label for="is_package" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                                Service Type
                            </label>
                            <select name="is_package" id="is_package"
                                    style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s; @error('is_package') border-color: #fca5a5; @enderror"
                                    class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                <option value="1" {{ old('is_package', '1') == '1' ? 'selected' : '' }}>Package</option>
                                <option value="0" {{ old('is_package') == '0' ? 'selected' : '' }}>Individual Service</option>
                            </select>
                            @error('is_package')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="is_active" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">
                                Status
                            </label>
                            <select name="is_active" id="is_active"
                                    style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s; @error('is_active') border-color: #fca5a5; @enderror"
                                    class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Public Service Checkbox --}}
                    <div class="flex items-center">
                        <input type="checkbox" name="is_public" id="is_public" value="1"
                               {{ old('is_public') ? 'checked' : '' }}
                               class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                        <label for="is_public" style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                            Show this service on public website
                        </label>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex justify-end space-x-2">
                <a href="{{ route('services.index') }}" 
                   style="display: inline-flex; align-items: center; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.5); color: var(--theme-text); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s;"
                   onmouseover="this.style.background='rgba(var(--theme-border-rgb), 0.1)'; this.style.borderColor='rgba(var(--theme-border-rgb), 0.7)';"
                   onmouseout="this.style.background='white'; this.style.borderColor='rgba(var(--theme-border-rgb), 0.5)';">
                    Cancel
                </a>
                <button type="submit" 
                        style="display: inline-flex; align-items: center; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: var(--theme-primary); color: var(--theme-button-text-color); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: none; cursor: pointer;"
                        onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.transform='translateY(-1px)';"
                        onmouseout="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'; this.style.transform='translateY(0)';">
                    Create Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection