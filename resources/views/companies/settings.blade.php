@extends('layouts.app')

@section('title', 'Company Settings')

@push('styles')
@php
    $themeSettings = \App\Models\SimplifiedThemeSetting::whereNull('company_id')
        ->where('is_active', true)
        ->first();
    
    if ($themeSettings) {
        echo $themeSettings->getCssVariables();
    }
@endphp

<style>
    /* Override text sizes with theme scaling */
    .text-xs { font-size: calc(var(--theme-font-size) * 0.75) !important; }
    .text-sm { font-size: calc(var(--theme-font-size) * 0.875) !important; }
    .text-base { font-size: var(--theme-font-size) !important; }
    .text-lg { font-size: calc(var(--theme-font-size) * 1.125) !important; }
    .text-xl { font-size: calc(var(--theme-font-size) * 1.25) !important; }
    .text-2xl { font-size: calc(var(--theme-font-size) * 1.5) !important; }
    .text-3xl { font-size: calc(var(--theme-font-size) * 1.875) !important; }
    .text-4xl { font-size: calc(var(--theme-font-size) * 2.25) !important; }
    
    /* Headers scaling with theme */
    h1 { font-size: calc(var(--theme-font-size) * 2.5) !important; }
    h2 { font-size: calc(var(--theme-font-size) * 2) !important; }
    h3 { font-size: calc(var(--theme-font-size) * 1.75) !important; }
    h4 { font-size: calc(var(--theme-font-size) * 1.5) !important; }
    h5 { font-size: calc(var(--theme-font-size) * 1.25) !important; }
    h6 { font-size: calc(var(--theme-font-size) * 1.125) !important; }
    
    /* Apply to form elements and paragraphs without Tailwind classes */
    input, select, textarea, label, p:not([class*="text-"]) {
        font-size: var(--theme-font-size) !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center" style="padding: var(--theme-header-custom-padding);">
                <div>
                    <h1 class="text-slate-900" style="font-size: var(--theme-header-title-size); font-weight: var(--theme-header-title-weight);">{{ $pageTitle }}</h1>
                    <p class="text-sm text-slate-600" style="margin-top: var(--theme-header-spacing);">{{ $pageDescription }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    @if($isCompaniesPluginActive)
                        <a href="{{ route('companies.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-building mr-1"></i> All Companies
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Info Banner --}}
        @if(!$isCompaniesPluginActive)
            <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium">Single Company Mode</p>
                        <p class="text-xs text-blue-600 mt-1">The Companies plugin is disabled, but you can still manage your company information here for invoicing and legal purposes.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium mb-1">Please fix the following errors:</p>
                        <ul class="text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Company Settings Form --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/50">
                <h2 class="text-lg font-semibold text-slate-900">Company Information</h2>
                <p class="text-sm text-slate-500 mt-1">Update your company details for invoicing and legal purposes</p>
            </div>
            
            <form action="{{ route('company.settings.update') }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Company Name --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Company Name *</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               required>
                    </div>

                    {{-- VAT Number --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">VAT Number</label>
                        <input type="text" name="vat_number" value="{{ old('vat_number', $company->vat_number) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               placeholder="NL123456789B01">
                    </div>

                    {{-- Default Hourly Rate --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Default Hourly Rate (€)</label>
                        <input type="number" name="default_hourly_rate" value="{{ old('default_hourly_rate', $company->default_hourly_rate) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               min="0" max="9999.99" step="0.01" placeholder="75.00">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               placeholder="info@company.com">
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               placeholder="+31 20 123 4567">
                    </div>

                    {{-- Website --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Website</label>
                        <input type="url" name="website" value="{{ old('website', $company->website) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               placeholder="https://www.company.com">
                    </div>
                </div>

                {{-- Address Section --}}
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <h3 class="text-base font-semibold text-slate-900 mb-4">Address Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- Street --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Street</label>
                            <input type="text" name="street" value="{{ old('street', $company->street) }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="Main Street">
                        </div>

                        {{-- House Number --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">House Number</label>
                            <input type="text" name="house_number" value="{{ old('house_number', $company->house_number) }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="123">
                        </div>

                        {{-- Addition --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Addition</label>
                            <input type="text" name="addition" value="{{ old('addition', $company->addition) }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="A">
                        </div>

                        {{-- Postal Code --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="1234 AB">
                        </div>

                        {{-- City --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">City</label>
                            <input type="text" name="city" value="{{ old('city', $company->city) }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="Amsterdam">
                        </div>

                        {{-- Country --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Country</label>
                            <input type="text" name="country" value="{{ old('country', $company->country ?? 'Netherlands') }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="Netherlands">
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="mt-8 pt-6 border-t border-slate-200 flex justify-end space-x-3">
                    <a href="{{ $isCompaniesPluginActive ? route('companies.index') : route('dashboard') }}" 
                       class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection