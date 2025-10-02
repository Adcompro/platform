@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="min-h-screen" style="background-color: var(--theme-bg);">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Edit Customer</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Update customer information for {{ $customer->name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" form="customer-form" id="header-save-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-save mr-1.5"></i>
                        Save Customer
                    </button>
                    <a href="{{ route('customers.show', $customer) }}"
                       id="header-back-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-arrow-left mr-1.5"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content - Exact Copy Theme Settings --}}
    <div style="padding: 1.5rem 2rem;">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.1); border-color: var(--theme-success); color: var(--theme-success); padding: var(--theme-card-padding);">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span style="font-size: var(--theme-font-size);">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border-color: var(--theme-danger); color: var(--theme-danger); padding: var(--theme-card-padding);">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span style="font-size: var(--theme-font-size);">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Edit Form --}}
        <form id="customer-form" method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')

            {{-- Basic Information Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Customer Information</h2>
                </div>

                <div style="padding: var(--theme-card-padding);" class="space-y-4">
                    {{-- Basic Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                                Customer Name <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $customer->name) }}"
                                   required
                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            @error('name')
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_person" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Contact Person
                            </label>
                            <input type="text" 
                                   name="contact_person" 
                                   id="contact_person"
                                   value="{{ old('contact_person', $customer->contact_person) }}"
                                   class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('contact_person') border-red-300 @enderror"
                                   style="color: var(--theme-text); background-color: white;">
                            @error('contact_person')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Company
                            </label>
                            <input type="text" 
                                   name="company" 
                                   id="company"
                                   value="{{ old('company', $customer->company) }}"
                                   class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('company') border-red-300 @enderror"
                                   style="color: var(--theme-text); background-color: white;">
                            @error('company')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(Auth::user()->role === 'super_admin')
                        <div>
                            <label for="company_id" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Managing Company <span class="text-red-500">*</span>
                            </label>
                            <select name="company_id" 
                                    id="company_id"
                                    required
                                    class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('company_id') border-red-300 @enderror"
                                    style="color: var(--theme-text); background-color: white;">
                                <option value="">Select a company</option>
                                @foreach(\App\Models\Company::orderBy('name')->get() as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $customer->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @else
                        <input type="hidden" name="company_id" value="{{ $customer->company_id }}">
                        <div>
                            <label class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Managing Company
                            </label>
                            <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                                <span class="text-[13px]" style="color: var(--theme-text);">{{ $customer->companyRelation->name }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Contact Information Card --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-[15px] font-semibold" style="color: var(--theme-text);">Contact Information</h2>
                </div>
                
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   value="{{ old('email', $customer->email) }}"
                                   required
                                   class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('email') border-red-300 @enderror"
                                   style="color: var(--theme-text); background-color: white;">
                            @error('email')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Phone Number
                            </label>
                            <input type="tel" 
                                   name="phone" 
                                   id="phone"
                                   value="{{ old('phone', $customer->phone) }}"
                                   class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('phone') border-red-300 @enderror"
                                   style="color: var(--theme-text); background-color: white;">
                            @error('phone')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address Information Card --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-[15px] font-semibold" style="color: var(--theme-text);">Address Information</h2>
                </div>
                
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        {{-- Street and Addition --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label for="street" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                    Street Address
                                </label>
                                <input type="text" 
                                       name="street" 
                                       id="street"
                                       value="{{ old('street', $customer->street) }}"
                                       placeholder="e.g. Main Street 123"
                                       class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('street') border-red-300 @enderror"
                                       style="color: var(--theme-text); background-color: white;">
                                @error('street')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="addition" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                    Addition
                                </label>
                                <input type="text" 
                                       name="addition" 
                                       id="addition"
                                       value="{{ old('addition', $customer->addition) }}"
                                       placeholder="Apt, Suite, Unit"
                                       class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('addition') border-red-300 @enderror"
                                       style="color: var(--theme-text); background-color: white;">
                                @error('addition')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Zip Code, City and Country --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label for="zip_code" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                    ZIP/Postal Code
                                </label>
                                <input type="text" 
                                       name="zip_code" 
                                       id="zip_code"
                                       value="{{ old('zip_code', $customer->zip_code) }}"
                                       placeholder="1234 AB"
                                       class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('zip_code') border-red-300 @enderror"
                                       style="color: var(--theme-text); background-color: white;">
                                @error('zip_code')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="city" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                    City
                                </label>
                                <input type="text" 
                                       name="city" 
                                       id="city"
                                       value="{{ old('city', $customer->city) }}"
                                       placeholder="Amsterdam"
                                       class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('city') border-red-300 @enderror"
                                       style="color: var(--theme-text); background-color: white;">
                                @error('city')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="country" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                    Country
                                </label>
                                <select name="country" 
                                        id="country"
                                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('country') border-red-300 @enderror"
                                        style="color: var(--theme-text); background-color: white;">
                                    <option value="Netherlands" {{ old('country', $customer->country) == 'Netherlands' ? 'selected' : '' }}>Netherlands</option>
                                    <option value="Belgium" {{ old('country', $customer->country) == 'Belgium' ? 'selected' : '' }}>Belgium</option>
                                    <option value="Germany" {{ old('country', $customer->country) == 'Germany' ? 'selected' : '' }}>Germany</option>
                                    <option value="France" {{ old('country', $customer->country) == 'France' ? 'selected' : '' }}>France</option>
                                    <option value="United Kingdom" {{ old('country', $customer->country) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="Spain" {{ old('country', $customer->country) == 'Spain' ? 'selected' : '' }}>Spain</option>
                                    <option value="Italy" {{ old('country', $customer->country) == 'Italy' ? 'selected' : '' }}>Italy</option>
                                    <option value="Other" {{ old('country', $customer->country) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('country')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional Information Card --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-[15px] font-semibold" style="color: var(--theme-text);">Additional Information</h2>
                </div>
                
                <div class="p-4 space-y-4">
                    <div>
                        <label for="notes" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                            Notes
                        </label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="4"
                                  class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('notes') border-red-300 @enderror"
                                  style="color: var(--theme-text); background-color: white;">{{ old('notes', $customer->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px]" style="color: var(--theme-text-muted);">Any additional information about the customer</p>
                    </div>

                    <div>
                        <label for="status" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                            Status
                        </label>
                        <select name="status" 
                                id="status"
                                class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('status') border-red-300 @enderror"
                                style="color: var(--theme-text); background-color: white;">
                            <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Invoice Template --}}
                    <div>
                        <label for="invoice_template_id" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                            Invoice Template
                        </label>
                        <select name="invoice_template_id" 
                                id="invoice_template_id" 
                                class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('invoice_template_id') border-red-300 @enderror"
                                style="color: var(--theme-text); background-color: white;">
                            <option value="">Use default template</option>
                            @php
                                $templates = \App\Models\InvoiceTemplate::where('is_active', true)
                                    ->when(Auth::user()->role !== 'super_admin', function($q) {
                                        $q->where('company_id', Auth::user()->company_id)
                                          ->orWhereNull('company_id');
                                    })
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ old('invoice_template_id', $customer->invoice_template_id) == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                                @if($template->company_id)
                                    (Company Template)
                                @else
                                    (System Template)
                                @endif
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px]" style="color: var(--theme-text-muted);">
                            Select a default invoice template for this customer. This will be used for all invoices unless overridden at project level.
                        </p>
                        @error('invoice_template_id')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-success').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();

    // Header save button
    const saveBtn = document.getElementById('header-save-btn');
    if (saveBtn) {
        saveBtn.style.backgroundColor = primaryColor;
        saveBtn.style.color = 'white';
        saveBtn.style.border = 'none';
        saveBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Header back button
    const backBtn = document.getElementById('header-back-btn');
    if (backBtn) {
        backBtn.style.backgroundColor = '#6b7280';
        backBtn.style.color = 'white';
        backBtn.style.border = 'none';
        backBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Form checkboxes
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.style.accentColor = primaryColor;
    });
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush