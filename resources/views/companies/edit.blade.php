@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Edit Company</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Update company information and settings</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('companies.show', $company) }}" id="header-cancel-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-times mr-1.5"></i>
                        Cancel
                    </a>
                    <button type="submit" form="company-form" id="header-save-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-save mr-1.5"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content - Exact Copy Theme Settings --}}
    <div style="padding: 1.5rem 2rem;">
        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form id="company-form" action="{{ route('companies.update', $company) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                <div style="padding: var(--theme-card-padding); border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Company Information</h2>
                </div>
                <div class="space-y-4" style="padding: var(--theme-card-padding);">
                    {{-- Company Name --}}
                    <div>
                        <label for="name" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Company Name <span style="color: var(--theme-danger);">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required
                               style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                               class="@error('name') !border-red-300 @enderror"
                               onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                        @error('name')
                            <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- VAT Number --}}
                    <div>
                        <label for="vat_number" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">VAT Number</label>
                        <input type="text" name="vat_number" id="vat_number" value="{{ old('vat_number', $company->vat_number) }}"
                               placeholder="NL123456789B01"
                               style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                               class="@error('vat_number') !border-red-300 @enderror"
                               onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                        @error('vat_number')
                            <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contact Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}"
                                   placeholder="info@company.com"
                                   style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                   class="@error('email') !border-red-300 @enderror"
                                   onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                   onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                            @error('email')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}"
                                   placeholder="+31 20 123 4567"
                                   style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                   class="@error('phone') !border-red-300 @enderror"
                                   onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                   onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                            @error('phone')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Website --}}
                    <div>
                        <label for="website" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Website</label>
                        <input type="url" name="website" id="website" value="{{ old('website', $company->website) }}"
                               placeholder="https://www.company.com"
                               style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                               class="@error('website') !border-red-300 @enderror"
                               onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                        @error('website')
                            <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address Fields --}}
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label for="street" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Street</label>
                                <input type="text" name="street" id="street" value="{{ old('street', $company->street) }}"
                                       placeholder="Kalverstraat"
                                       style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                       class="@error('street') !border-red-300 @enderror"
                                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                                @error('street')
                                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="house_number" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">House Number</label>
                                <input type="text" name="house_number" id="house_number" value="{{ old('house_number', $company->house_number) }}"
                                       placeholder="123"
                                       style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                       class="@error('house_number') !border-red-300 @enderror"
                                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                                @error('house_number')
                                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="addition" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Addition</label>
                                <input type="text" name="addition" id="addition" value="{{ old('addition', $company->addition) }}"
                                       placeholder="A"
                                       style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                       class="@error('addition') !border-red-300 @enderror"
                                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                                @error('addition')
                                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label for="postal_code" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Postal Code</label>
                                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $company->postal_code) }}"
                                       placeholder="1234 AB"
                                       style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                       class="@error('postal_code') !border-red-300 @enderror"
                                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                                @error('postal_code')
                                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="city" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">City</label>
                                <input type="text" name="city" id="city" value="{{ old('city', $company->city) }}"
                                       placeholder="Amsterdam"
                                       style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                       class="@error('city') !border-red-300 @enderror"
                                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                                @error('city')
                                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="country" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Country</label>
                                <input type="text" name="country" id="country" value="{{ old('country', $company->country ?? 'Netherlands') }}"
                                       placeholder="Netherlands"
                                       style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                                       class="@error('country') !border-red-300 @enderror"
                                       onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                                       onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                                @error('country')
                                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    {{-- Legacy Address Field (Hidden but maintained for compatibility) --}}
                    <input type="hidden" name="address" id="address" value="{{ old('address', $company->address) }}">

                    {{-- Default Hourly Rate --}}
                    <div>
                        <label for="default_hourly_rate" style="display: block; font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text); margin-bottom: 0.25rem;">Default Hourly Rate (€)</label>
                        <input type="number" name="default_hourly_rate" id="default_hourly_rate" 
                               value="{{ old('default_hourly_rate', $company->default_hourly_rate) }}" step="0.01" min="0"
                               style="width: 100%; padding: 0.5rem 0.75rem; font-size: var(--theme-font-size); color: var(--theme-text); border: 1px solid #d1d5db; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                               class="@error('default_hourly_rate') !border-red-300 @enderror"
                               onfocus="this.style.borderColor='var(--theme-accent)'; this.style.boxShadow='0 0 0 3px rgba(251, 146, 60, 0.1)';"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                        @error('default_hourly_rate')
                            <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Company Settings (alleen Active status) --}}
                    <div class="space-y-3">
                        <div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                                       style="border-radius: 0.25rem; border-color: #d1d5db; color: var(--theme-accent);"
                                       class="focus:border-orange-500 focus:ring-orange-500">
                                <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Active company</span>
                            </label>
                            <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Inactive companies won't appear in dropdowns</p>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header buttons
    const cancelBtn = document.getElementById('header-cancel-btn');
    const saveBtn = document.getElementById('header-save-btn');

    if (cancelBtn) {
        cancelBtn.style.backgroundColor = 'white';
        cancelBtn.style.color = primaryColor;
        cancelBtn.style.border = `1px solid ${primaryColor}`;
        cancelBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    if (saveBtn) {
        saveBtn.style.backgroundColor = primaryColor;
        saveBtn.style.color = 'white';
        saveBtn.style.border = 'none';
        saveBtn.style.borderRadius = 'var(--theme-border-radius)';
    }


    // Update focus styles for all form inputs
    const formInputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="url"], input[type="number"]');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = primaryColor;
            this.style.boxShadow = `0 0 0 3px ${primaryColor}20`;
        });

        input.addEventListener('blur', function() {
            this.style.borderColor = '#d1d5db';
            this.style.boxShadow = 'none';
        });
    });
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush

@endsection