{{-- Modal Edit Form for Company --}}
<div>
    <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Basic Company Information --}}
        <div class="space-y-3">
            {{-- Company Name and Email Row --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- Company Name --}}
                <div>
                    <label for="name" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Company Name <span style="color: var(--theme-danger);">*</span>
                    </label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name', $company->name) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="Company Name BV">
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Email Address
                    </label>
                    <input type="email" name="email" id="email"
                           value="{{ old('email', $company->email) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="info@company.com">
                </div>
            </div>

            {{-- Contact Information Grid --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- Phone --}}
                <div>
                    <label for="phone" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Phone
                    </label>
                    <input type="text" name="phone" id="phone"
                           value="{{ old('phone', $company->phone) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="+31 20 123 4567">
                </div>

                {{-- Website --}}
                <div>
                    <label for="website" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Website
                    </label>
                    <input type="url" name="website" id="website"
                           value="{{ old('website', $company->website) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="https://www.company.com">
                </div>
            </div>

            {{-- Address --}}
            <div>
                <label for="address" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Address
                </label>
                <textarea name="address" id="address" rows="1"
                          style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white; resize: vertical;"
                          placeholder="Street Address, City, Country">{{ old('address', $company->address) }}</textarea>
            </div>

            {{-- Financial Information and Status Grid --}}
            <div class="grid grid-cols-3 gap-4">
                {{-- Default Hourly Rate --}}
                <div>
                    <label for="default_hourly_rate" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Default Hourly Rate (€)
                    </label>
                    <input type="number" name="default_hourly_rate" id="default_hourly_rate" step="0.01" min="0"
                           value="{{ old('default_hourly_rate', $company->default_hourly_rate) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="85.00">
                </div>

                {{-- VAT Rate --}}
                <div>
                    <label for="vat_rate" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        VAT Rate (%)
                    </label>
                    <input type="number" name="vat_rate" id="vat_rate" step="0.01" min="0" max="100"
                           value="{{ old('vat_rate', $company->vat_rate) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           placeholder="21.00">
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Status <span style="color: var(--theme-danger);">*</span>
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="status" value="active"
                                   {{ old('status', $company->status) === 'active' ? 'checked' : '' }}
                                   class="h-4 w-4 border-gray-300 rounded"
                                   style="color: var(--theme-primary);">
                            <span style="margin-left: 0.25rem; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">Active</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="status" value="inactive"
                                   {{ old('status', $company->status) === 'inactive' ? 'checked' : '' }}
                                   class="h-4 w-4 border-gray-300 rounded"
                                   style="color: var(--theme-primary);">
                            <span style="margin-left: 0.25rem; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">Inactive</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Bank Details Section --}}
            <div>
                <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Banking Information</h4>
                <div class="grid grid-cols-2 gap-4">
                    {{-- Bank Name --}}
                    <div>
                        <label for="bank_name" style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">
                            Bank Name
                        </label>
                        <input type="text" name="bank_details[bank_name]" id="bank_name"
                               value="{{ old('bank_details.bank_name', $company->bank_details['bank_name'] ?? '') }}"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                               placeholder="ING Bank">
                    </div>

                    {{-- Account Number --}}
                    <div>
                        <label for="account_number" style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">
                            Account Number
                        </label>
                        <input type="text" name="bank_details[account_number]" id="account_number"
                               value="{{ old('bank_details.account_number', $company->bank_details['account_number'] ?? '') }}"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                               placeholder="123456789">
                    </div>

                    {{-- IBAN --}}
                    <div>
                        <label for="iban" style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">
                            IBAN
                        </label>
                        <input type="text" name="bank_details[iban]" id="iban"
                               value="{{ old('bank_details.iban', $company->bank_details['iban'] ?? '') }}"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                               placeholder="NL91 INGB 0001 2345 67">
                    </div>

                    {{-- BIC/SWIFT --}}
                    <div>
                        <label for="bic_swift" style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">
                            BIC/SWIFT
                        </label>
                        <input type="text" name="bank_details[bic_swift]" id="bic_swift"
                               value="{{ old('bank_details.bic_swift', $company->bank_details['bic_swift'] ?? '') }}"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                               placeholder="INGBNL2A">
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                    Notes
                </label>
                <textarea name="notes" id="notes" rows="1"
                          style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white; resize: vertical;"
                          placeholder="Additional notes about this company...">{{ old('notes', $company->notes) }}</textarea>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-between pt-4" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                <span style="color: var(--theme-danger);">*</span> Required fields
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="closeEditCompanyModal()"
                        style="padding: 0.5rem 1rem; background-color: #6b7280; color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                    Cancel
                </button>
                <button type="submit"
                        style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                    <i class="fas fa-save mr-1.5"></i>
                    Update Company
                </button>
            </div>
        </div>
    </form>
</div>