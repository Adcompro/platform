{{-- Modal Show View for Company --}}
<div>
    {{-- Company Profile Header --}}
    <div class="text-center mb-6" style="padding: 1.5rem; background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent)); border-radius: var(--theme-border-radius); margin: -1.5rem -1.5rem 1.5rem -1.5rem;">
        <div class="flex justify-center mb-3">
            <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center">
                <span style="font-size: 1.25rem; font-weight: 700; color: var(--theme-primary);">{{ strtoupper(substr($company->name, 0, 2)) }}</span>
            </div>
        </div>
        <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: white; margin: 0;">{{ $company->name }}</h3>
        @if($company->email)
            <p style="font-size: var(--theme-font-size); color: rgba(255, 255, 255, 0.9); margin-top: 0.25rem;">{{ $company->email }}</p>
        @endif
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="text-center" style="padding: 0.75rem; background-color: rgba(var(--theme-primary-rgb), 0.05); border-radius: var(--theme-border-radius);">
            <div style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">{{ $company->users->count() }}</div>
            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Users</div>
        </div>
        <div class="text-center" style="padding: 0.75rem; background-color: rgba(var(--theme-success-rgb), 0.05); border-radius: var(--theme-border-radius);">
            <div style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">{{ $company->customers->count() }}</div>
            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Customers</div>
        </div>
        <div class="text-center" style="padding: 0.75rem; background-color: rgba(var(--theme-accent-rgb), 0.05); border-radius: var(--theme-border-radius);">
            <div style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">{{ $company->projects()->where('status', 'active')->count() }}</div>
            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Projects</div>
        </div>
    </div>

    {{-- Company Details Grid --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        {{-- Status --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Status</dt>
            <dd>
                @if($company->status === 'active')
                    <span class="px-2 py-1 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                        Active
                    </span>
                @else
                    <span class="px-2 py-1 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);">
                        Inactive
                    </span>
                @endif
            </dd>
        </div>

        {{-- Default Rate --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Default Rate</dt>
            <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">€{{ number_format($company->default_hourly_rate ?? 0, 2) }}</dd>
        </div>

        {{-- VAT Rate --}}
        @if($company->vat_rate)
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">VAT Rate</dt>
            <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $company->vat_rate }}%</dd>
        </div>
        @endif

        {{-- Created Date --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Created</dt>
            <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $company->created_at->format('M j, Y') }}</dd>
        </div>
    </div>

    {{-- Contact Information --}}
    @if($company->phone || $company->address || $company->website)
        <div class="mb-4">
            <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Contact Information</h4>
            <div class="space-y-2">
                @if($company->phone)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">{{ $company->phone }}</span>
                </div>
                @endif

                @if($company->website)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <a href="{{ $company->website }}" target="_blank" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-primary); text-decoration: none;">{{ $company->website }}</a>
                </div>
                @endif

                @if($company->address)
                <div class="flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text); line-height: 1.4;">{{ $company->address }}</span>
                </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Bank Details --}}
    @if($company->bank_details && is_array($company->bank_details) && count(array_filter($company->bank_details)) > 0)
        <div class="mb-4">
            <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Banking Information</h4>
            <div class="space-y-2" style="padding: 0.75rem; background-color: rgba(var(--theme-border-rgb), 0.05); border-radius: var(--theme-border-radius);">
                @if(!empty($company->bank_details['bank_name']))
                <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                    <strong>Bank:</strong> {{ $company->bank_details['bank_name'] }}
                </div>
                @endif
                @if(!empty($company->bank_details['account_number']))
                <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                    <strong>Account:</strong> {{ $company->bank_details['account_number'] }}
                </div>
                @endif
                @if(!empty($company->bank_details['iban']))
                <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                    <strong>IBAN:</strong> {{ $company->bank_details['iban'] }}
                </div>
                @endif
                @if(!empty($company->bank_details['bic_swift']))
                <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                    <strong>BIC/SWIFT:</strong> {{ $company->bank_details['bic_swift'] }}
                </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Notes --}}
    @if($company->notes)
        <div class="mb-4">
            <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Notes</h4>
            <div style="font-size: var(--theme-font-size); color: var(--theme-text); white-space: pre-wrap; line-height: 1.5; padding: 0.75rem; background-color: rgba(var(--theme-border-rgb), 0.05); border-radius: var(--theme-border-radius);">{{ $company->notes }}</div>
        </div>
    @endif

    {{-- Close Button --}}
    <div class="flex justify-end pt-4" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
        <button type="button" onclick="closeViewCompanyModal()"
                style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
            Close
        </button>
    </div>
</div>