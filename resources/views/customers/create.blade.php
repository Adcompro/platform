@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')
<div class="min-h-screen" style="background-color: var(--theme-bg);">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-semibold" style="color: var(--theme-text);">Create New Customer</h1>
                    <p class="text-[13px] mt-0.5" style="color: var(--theme-text-muted);">Add a new customer to your database</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('customers.index') }}" 
                       class="inline-flex items-center font-normal transition-all border"
                       style="background-color: white; color: var(--theme-text); border-color: var(--theme-text-muted); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                        <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px]">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-red-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-normal">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Create Form --}}
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            
            {{-- Basic Information Card --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-[15px] font-semibold" style="color: var(--theme-text);">Customer Information</h2>
                </div>
                
                <div class="p-4 space-y-4">
                    {{-- Basic Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Customer Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('name') border-red-300 @enderror"
                                   style="color: var(--theme-text); background-color: white;">
                            @error('name')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_person" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Contact Person
                            </label>
                            <input type="text"
                                   name="contact_person"
                                   id="contact_person"
                                   value="{{ old('contact_person') }}"
                                   class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('contact_person') border-red-300 @enderror"
                                   style="color: var(--theme-text); background-color: white;">
                            @error('contact_person')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
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
                                @php
                                    $companies = Auth::user()->role === 'super_admin'
                                        ? \App\Models\Company::orderBy('name')->get()
                                        : \App\Models\Company::where('id', Auth::user()->company_id)->orderBy('name')->get();
                                @endphp
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', Auth::user()->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-[11px]" style="color: var(--theme-text-muted);">
                                {{ Auth::user()->role === 'super_admin' ? 'Select the company that will manage this customer' : 'Your company will manage this customer' }}
                            </p>
                        </div>
                        @else
                        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        <div>
                            <label class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Managing Company
                            </label>
                            <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                                <span class="text-[13px]" style="color: var(--theme-text);">{{ Auth::user()->company->name }}</span>
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
                                   value="{{ old('email') }}"
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
                                   value="{{ old('phone') }}"
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
                                       value="{{ old('street') }}"
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
                                       value="{{ old('addition') }}"
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
                                       value="{{ old('zip_code') }}"
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
                                       value="{{ old('city') }}"
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
                                    <option value="Netherlands" {{ old('country', 'Netherlands') == 'Netherlands' ? 'selected' : '' }}>Netherlands</option>
                                    <option value="Belgium" {{ old('country') == 'Belgium' ? 'selected' : '' }}>Belgium</option>
                                    <option value="Germany" {{ old('country') == 'Germany' ? 'selected' : '' }}>Germany</option>
                                    <option value="France" {{ old('country') == 'France' ? 'selected' : '' }}>France</option>
                                    <option value="United Kingdom" {{ old('country') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="Spain" {{ old('country') == 'Spain' ? 'selected' : '' }}>Spain</option>
                                    <option value="Italy" {{ old('country') == 'Italy' ? 'selected' : '' }}>Italy</option>
                                    <option value="Other" {{ old('country') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('country')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="language" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                    Preferred Language
                                </label>
                                <select name="language"
                                        id="language"
                                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('language') border-red-300 @enderror"
                                        style="color: var(--theme-text); background-color: white;">
                                    @foreach(\App\Models\Customer::getAvailableLanguages() as $code => $name)
                                        <option value="{{ $code }}" {{ old('language', 'nl') == $code ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('language')
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
                                  style="color: var(--theme-text); background-color: white;">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px]" style="color: var(--theme-text-muted);">Any additional information about the customer</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="status" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Status
                            </label>
                            <select name="status"
                                    id="status"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('status') border-red-300 @enderror"
                                    style="color: var(--theme-text); background-color: white;">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_date" class="block text-[13px] font-normal mb-1" style="color: var(--theme-text);">
                                Customer Start Date
                            </label>
                            <input type="date"
                                   name="start_date"
                                   id="start_date"
                                   value="{{ old('start_date') }}"
                                   class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg @error('start_date') border-red-300 @enderror"
                                   style="color: var(--theme-text); background-color: white;">
                            @error('start_date')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-[11px]" style="color: var(--theme-text-muted);">When did this customer relationship start?</p>
                        </div>
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
                            @if(isset($templates))
                                @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('invoice_template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }}
                                    @if($template->company_id)
                                        (Company Template)
                                    @else
                                        (System Template)
                                    @endif
                                </option>
                                @endforeach
                            @endif
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

            {{-- Form Actions --}}
            <div class="flex justify-end space-x-2">
                <a href="{{ route('customers.index') }}" 
                   class="inline-flex items-center font-normal transition-all border"
                   style="background-color: white; color: var(--theme-text); border-color: var(--theme-text-muted); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center font-normal transition-all"
                        style="background-color: var(--theme-primary); color: white; border: none; font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                    <i class="fas fa-save mr-1.5 text-xs"></i>
                    Create Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection