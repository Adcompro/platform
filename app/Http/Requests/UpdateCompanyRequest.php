<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check() && in_array(Auth::user()->role, ['super_admin', 'admin']);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'vat_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('companies', 'vat_number')->ignore($this->company->id)
            ],
            'registration_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:1000',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'default_hourly_rate' => 'nullable|numeric|min:0|max:9999.99',
            'default_fixed_price' => 'nullable|numeric|min:0|max:999999.99',
            'invoice_prefix' => 'nullable|string|max:10',
            'is_main_invoicing' => 'boolean',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:2000',
            
            // Bank details
            'bank_details.bank_name' => 'nullable|string|max:255',
            'bank_details.account_number' => 'nullable|string|max:255', 
            'bank_details.iban' => 'nullable|string|max:34',
            'bank_details.bic' => 'nullable|string|max:11',
            
            // Invoice settings
            'invoice_settings.template' => 'nullable|string|max:100',
            'invoice_settings.payment_terms' => 'nullable|integer|min:1|max:365',
            'invoice_settings.auto_send' => 'boolean',
        ];
    }

    protected function prepareForValidation()
    {
        // Data preprocessing
        if ($this->default_hourly_rate) {
            $this->merge([
                'default_hourly_rate' => str_replace(',', '.', $this->default_hourly_rate)
            ]);
        }
        
        if ($this->default_fixed_price) {
            $this->merge([
                'default_fixed_price' => str_replace(',', '.', $this->default_fixed_price)
            ]);
        }

        $this->merge([
            'is_main_invoicing' => $this->boolean('is_main_invoicing'),
        ]);
    }
}