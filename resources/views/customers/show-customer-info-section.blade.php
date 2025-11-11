{{-- Customer Information Card with Inline Editing --}}
<div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Customer Information</h2>
        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
        <button onclick="toggleEdit()"
                id="edit-btn"
                class="inline-flex items-center px-2 py-1 rounded transition-colors"
                style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
            <i class="fas fa-edit mr-1"></i>
            Edit
        </button>
        <div id="edit-actions" class="hidden flex items-center gap-2">
            <button onclick="saveEdit()"
                    class="inline-flex items-center px-2 py-1 rounded transition-colors"
                    style="background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
                <i class="fas fa-check mr-1"></i>
                Save
            </button>
            <button onclick="cancelEdit()"
                    class="inline-flex items-center px-2 py-1 rounded transition-colors"
                    style="background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; border: none; cursor: pointer;">
                <i class="fas fa-times mr-1"></i>
                Cancel
            </button>
        </div>
        @endif
    </div>
    <div style="padding: var(--theme-card-padding);">
        <form id="customer-form">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            {{-- Column 1: Basic Info --}}
            <div>
                <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">
                    <i class="fas fa-building mr-2" style="color: var(--theme-primary);"></i>Basic Information
                </h3>
                <div class="space-y-3">
                    {{-- Customer Name --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Customer Name</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-text);">{{ $customer->name }}</div>
                        <div class="field-edit hidden">
                            <input type="text" name="name" value="{{ $customer->name }}" required
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Status</div>
                        <div class="field-view">
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; {{ $customer->status === 'active' ? 'color: #059669; background-color: #d1fae5;' : 'color: #dc2626; background-color: #fee2e2;' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </div>
                        <div class="field-edit hidden">
                            <select name="status" required class="w-full border border-gray-300 rounded"
                                    style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    {{-- Customer Start Date --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Customer Start Date</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($customer->start_date)
                                {{ $customer->start_date->format('d-m-Y') }}
                            @else
                                <span style="color: var(--theme-text-muted);">Not set</span>
                            @endif
                        </div>
                        <div class="field-edit hidden">
                            <input type="date" name="start_date" value="{{ $customer->start_date ? $customer->start_date->format('Y-m-d') : '' }}"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Column 2: Contact Info --}}
            <div>
                <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">
                    <i class="fas fa-address-card mr-2" style="color: var(--theme-primary);"></i>Contact Information
                </h3>
                <div class="space-y-3">
                    {{-- Email --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Email</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($customer->email)
                                <a href="mailto:{{ $customer->email }}" style="color: var(--theme-primary);">{{ $customer->email }}</a>
                            @else
                                <span style="color: var(--theme-text-muted);">No email</span>
                            @endif
                        </div>
                        <div class="field-edit hidden">
                            <input type="email" name="email" value="{{ $customer->email }}"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Phone</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($customer->phone)
                                <a href="tel:{{ $customer->phone }}" style="color: var(--theme-primary);">{{ $customer->phone }}</a>
                            @else
                                <span style="color: var(--theme-text-muted);">No phone</span>
                            @endif
                        </div>
                        <div class="field-edit hidden">
                            <input type="text" name="phone" value="{{ $customer->phone }}"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                        </div>
                    </div>

                    {{-- Language --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Language</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                            {{ $customer->language_flag }} {{ $customer->language_name }}
                        </div>
                        <div class="field-edit hidden">
                            <select name="language" class="w-full border border-gray-300 rounded"
                                    style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                                <option value="nl" {{ $customer->language === 'nl' ? 'selected' : '' }}>🇳🇱 Nederlands</option>
                                <option value="en" {{ $customer->language === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                                <option value="fr" {{ $customer->language === 'fr' ? 'selected' : '' }}>🇫🇷 Français</option>
                                <option value="de" {{ $customer->language === 'de' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
                                <option value="es" {{ $customer->language === 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                                <option value="it" {{ $customer->language === 'it' ? 'selected' : '' }}>🇮🇹 Italiano</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Column 3: Address --}}
            <div>
                <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">
                    <i class="fas fa-map-marker-alt mr-2" style="color: var(--theme-primary);"></i>Address
                </h3>
                <div class="space-y-3">
                    {{-- Street --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Street</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                            {{ $customer->street ?: '-' }}@if($customer->addition) {{ $customer->addition }}@endif
                        </div>
                        <div class="field-edit hidden space-y-2">
                            <input type="text" name="street" value="{{ $customer->street }}" placeholder="Street"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                            <input type="text" name="addition" value="{{ $customer->addition }}" placeholder="Addition"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                        </div>
                    </div>

                    {{-- Zip Code & City --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Zip Code & City</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                            @if($customer->zip_code || $customer->city)
                                {{ $customer->zip_code }} {{ $customer->city }}
                            @else
                                <span style="color: var(--theme-text-muted);">-</span>
                            @endif
                        </div>
                        <div class="field-edit hidden space-y-2">
                            <input type="text" name="zip_code" value="{{ $customer->zip_code }}" placeholder="Zip Code"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                            <input type="text" name="city" value="{{ $customer->city }}" placeholder="City"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                        </div>
                    </div>

                    {{-- Country --}}
                    <div>
                        <div style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Country</div>
                        <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->country ?: '-' }}</div>
                        <div class="field-edit hidden">
                            <input type="text" name="country" value="{{ $customer->country }}"
                                   class="w-full border border-gray-300 rounded"
                                   style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.15rem 0.5rem; line-height: 1.4;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Managing Companies & Notes (2-column layout) --}}
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(203, 213, 225, 0.3);">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                {{-- Managing Companies --}}
                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                <div>
                    <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-sitemap mr-2" style="color: var(--theme-primary);"></i>Managing Companies
                    </h3>
                    <div class="field-view">
                        @if($customer->companies && $customer->companies->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach($customer->companies as $company)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                          style="background-color: {{ $company->pivot->is_primary ? 'rgba(59, 130, 246, 0.1)' : 'rgba(100, 116, 139, 0.1)' }};
                                                 color: {{ $company->pivot->is_primary ? '#2563eb' : '#64748b' }};">
                                        @if($company->pivot->is_primary)
                                            <i class="fas fa-check-circle mr-1.5"></i>
                                        @endif
                                        {{ $company->name }}
                                    </span>
                                @endforeach
                            </div>
                            <div style="font-size: 11px; color: #999;">
                                Total: {{ $customer->companies->count() }} managing {{ $customer->companies->count() === 1 ? 'company' : 'companies' }}
                            </div>
                        @else
                            <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No managing companies assigned</span>
                        @endif
                    </div>
                    <div class="field-edit hidden">
                        @php
                            // Both super_admin and admin can see all companies
                            $allCompanies = \App\Models\Company::orderBy('name')->get();
                            $selectedCompanies = $customer->companies->pluck('id')->toArray();
                            $primaryCompanyId = $customer->companies->where('pivot.is_primary', true)->first()?->id;
                        @endphp

                        @if($allCompanies->count() > 0)
                            <div style="font-size: 11px; color: #999; margin-bottom: 8px;">
                                Available: {{ $allCompanies->count() }} companies | Selected: {{ count($selectedCompanies) }} | Primary: {{ $primaryCompanyId ? 'ID '.$primaryCompanyId : 'None' }}
                            </div>
                            <div style="max-height: 400px; overflow-y: auto; border: 1px solid rgba(203, 213, 225, 0.3); border-radius: 0.375rem; padding: 0.5rem; background-color: white;">
                                @foreach($allCompanies as $company)
                                <div class="flex items-center gap-3 mb-2 pb-2" style="border-bottom: 1px solid rgba(203, 213, 225, 0.2);">
                                    {{-- Checkbox voor company selectie --}}
                                    <input type="checkbox"
                                           name="companies[]"
                                           value="{{ $company->id }}"
                                           id="company_{{ $company->id }}"
                                           class="company-checkbox"
                                           {{ in_array($company->id, $selectedCompanies) ? 'checked' : '' }}
                                           onchange="handleCompanyCheckboxChange({{ $company->id }})"
                                           style="width: 16px; height: 16px; cursor: pointer;">

                                    {{-- Company naam als label --}}
                                    <label for="company_{{ $company->id }}"
                                           style="flex: 1; cursor: pointer; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                                        {{ $company->name }}
                                    </label>

                                    {{-- Radio button voor primary selectie --}}
                                    <div class="flex items-center gap-1">
                                        <input type="radio"
                                               name="company_primary"
                                               value="{{ $company->id }}"
                                               id="primary_{{ $company->id }}"
                                               class="primary-radio"
                                               {{ $primaryCompanyId == $company->id ? 'checked' : '' }}
                                               {{ !in_array($company->id, $selectedCompanies) ? 'disabled' : '' }}
                                               style="width: 14px; height: 14px; cursor: pointer;">
                                        <label for="primary_{{ $company->id }}"
                                               style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); cursor: pointer;">
                                            Primary
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <p class="text-xs mt-2" style="color: var(--theme-text-muted);">
                                <i class="fas fa-info-circle mr-1"></i>Check companies to assign, select one as primary
                            </p>
                        @else
                            <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">No companies available</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Notes --}}
                <div @if(!in_array(Auth::user()->role, ['super_admin', 'admin'])) style="grid-column: 1 / -1;" @endif>
                    <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.75rem;">
                        <i class="fas fa-sticky-note mr-2" style="color: var(--theme-primary);"></i>Notes
                    </h3>
                    <div class="field-view" style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.6; white-space: pre-wrap;">{{ $customer->notes ?: 'No notes' }}</div>
                    <div class="field-edit hidden">
                        <textarea name="notes" rows="4" class="w-full border border-gray-300 rounded"
                                  style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.5rem; line-height: 1.4;">{{ $customer->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
