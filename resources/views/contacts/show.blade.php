@extends('layouts.app')

@section('title', 'Contact Details')

@section('content')
{{-- Sticky Header - Exact Copy Theme Settings --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">{{ $contact->name }}</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">
                    Contact details
                    @if($contact->position) • {{ $contact->position }}@endif
                    @if($contact->customer) • {{ $contact->customer->name }}@endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if(request('from') === 'customer' && request('customer_id'))
                    <a href="{{ route('customers.show', request('customer_id')) }}"
                       id="header-close-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-times mr-1.5"></i>
                        Close
                    </a>
                @else
                    <a href="{{ route('contacts.index') }}"
                       id="header-back-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-arrow-left mr-1.5"></i>
                        Back to List
                    </a>
                @endif
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Contact Information Card --}}
        <div class="lg:col-span-2">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); display: flex; align-items: center; justify-content: space-between;">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Contact Information</h2>
                    <button type="button" onclick="toggleEditMode('contact-info')" id="edit-contact-btn"
                            style="padding: 0.25rem 0.75rem; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-primary); background: transparent; border: 1px solid var(--theme-primary); border-radius: var(--theme-border-radius); cursor: pointer;">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                </div>

                <div style="padding: var(--theme-card-padding);">
                    <div class="space-y-6" id="contact-info-view">
                        {{-- Basic Information --}}
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Basic Details</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Name</dt>
                                    <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                        {{ $contact->name }}
                                        @php
                                            // Check if this contact is primary for any company
                                            $isPrimary = $contact->companies()
                                                ->wherePivot('is_primary', true)
                                                ->exists();
                                        @endphp
                                        @if($isPrimary)
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full"
                                                  style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: var(--theme-primary); color: white;">
                                                <i class="fas fa-star mr-1" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                                Primary
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Position</dt>
                                    <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size); color: var(--theme-text);">{{ $contact->position ?: 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Email</dt>
                                    <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size);">
                                        @if($contact->email)
                                            <a href="mailto:{{ $contact->email }}" style="color: var(--theme-primary); text-decoration: none;" class="hover:opacity-80">
                                                {{ $contact->email }}
                                            </a>
                                        @else
                                            <span style="color: var(--theme-text-muted);">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Phone</dt>
                                    <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size);">
                                        @if($contact->phone)
                                            <a href="tel:{{ $contact->phone }}" style="color: var(--theme-text); text-decoration: none;" class="hover:opacity-80">
                                                {{ $contact->phone }}
                                            </a>
                                        @else
                                            <span style="color: var(--theme-text-muted);">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Status</dt>
                                    <dd style="margin-top: 0.25rem;">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full"
                                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; {{ $contact->is_active ? 'background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);' : 'background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);' }}">
                                            <span class="w-2 h-2 rounded-full mr-1" style="{{ $contact->is_active ? 'background-color: var(--theme-success);' : 'background-color: var(--theme-text-muted);' }}"></span>
                                            {{ $contact->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Customer Information --}}
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Customer</h3>
                            <div class="rounded-lg border" style="background-color: rgba(248, 250, 252, 0.5); border-color: rgba(203, 213, 225, 0.5); padding: 1rem;">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $contact->customer->name }}</p>
                                        @if($contact->customer->company)
                                        <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">{{ $contact->customer->company }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('customers.show', $contact->customer) }}?from=contact&contact_id={{ $contact->id }}"
                                       style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); text-decoration: none;"
                                       class="hover:opacity-80">
                                        View Customer →
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Companies Relations --}}
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Companies</h3>
                            <div class="rounded-lg border" style="background-color: rgba(248, 250, 252, 0.5); border-color: rgba(203, 213, 225, 0.5); padding: 1rem;">
                                @if($contact->companies->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($contact->companies as $company)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $company->name }}</p>
                                                @if($company->pivot->is_primary)
                                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full"
                                                          style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: var(--theme-primary); color: white;">
                                                        <i class="fas fa-star mr-1" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                                        Primary
                                                    </span>
                                                @endif
                                            </div>
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                            <a href="{{ route('companies.show', $company->id) }}"
                                               style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); text-decoration: none;"
                                               class="hover:opacity-80">
                                                View →
                                            </a>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                @elseif($contact->company)
                                    {{-- Fallback to legacy company_id if no companies in pivot table --}}
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $contact->company->name }}</p>
                                            @if($contact->company->email)
                                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">{{ $contact->company->email }}</p>
                                            @endif
                                        </div>
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                        <a href="{{ route('companies.show', $contact->company_id) }}"
                                           style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); text-decoration: none;"
                                           class="hover:opacity-80">
                                            View Company →
                                        </a>
                                        @endif
                                    </div>
                                @else
                                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); font-style: italic;">No company relations assigned</p>
                                @endif
                            </div>
                        </div>

                        {{-- Notes --}}
                        @if($contact->notes)
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Notes</h3>
                            <div class="rounded-lg border" style="background-color: rgba(248, 250, 252, 0.5); border-color: rgba(203, 213, 225, 0.5); padding: 1rem;">
                                <p style="font-size: var(--theme-font-size); color: var(--theme-text); white-space: pre-wrap;">{{ $contact->notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Edit Form (Hidden by default) --}}
                    <form id="contact-info-edit" style="display: none;" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="customer_id" value="{{ $contact->customer_id }}">

                        {{-- Basic Information --}}
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Basic Details</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Name</label>
                                    <input type="text" name="name" value="{{ $contact->name }}" required
                                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Position</label>
                                    <input type="text" name="position" value="{{ $contact->position }}"
                                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Email</label>
                                    <input type="email" name="email" value="{{ $contact->email }}"
                                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Phone</label>
                                    <input type="text" name="phone" value="{{ $contact->phone }}"
                                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Status</label>
                                    <select name="is_active"
                                            style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        <option value="1" {{ $contact->is_active ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$contact->is_active ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Notes</label>
                            <textarea name="notes" rows="3"
                                      style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">{{ $contact->notes }}</textarea>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex justify-end gap-2 pt-4" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
                            <button type="button" onclick="toggleEditMode('contact-info')"
                                    style="padding: 0.5rem 1rem; background-color: #6b7280; color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); cursor: pointer;">
                                <i class="fas fa-times mr-1.5"></i>Cancel
                            </button>
                            <button type="button" onclick="saveInlineEdit('contact-info')" id="save-contact-btn"
                                    style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); cursor: pointer;">
                                <i class="fas fa-save mr-1.5"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Actions & Meta Card --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Quick Actions Card --}}
            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Quick Actions</h2>
                </div>
                <div style="padding: var(--theme-card-padding);">
                    <div class="space-y-3">
                        @if($contact->email)
                        <a href="mailto:{{ $contact->email }}"
                           class="w-full inline-flex items-center justify-center rounded-md hover:opacity-90 transition-all"
                           style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: var(--theme-primary); color: white; font-size: var(--theme-font-size); font-weight: 500; text-decoration: none; border-radius: var(--theme-border-radius);">
                            <i class="fas fa-envelope mr-1.5"></i>
                            Send Email
                        </a>
                        @endif

                        @if($contact->phone)
                        <a href="tel:{{ $contact->phone }}"
                           class="w-full inline-flex items-center justify-center rounded-md hover:opacity-90 transition-all"
                           style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text); font-size: var(--theme-font-size); font-weight: 500; text-decoration: none; border-radius: var(--theme-border-radius);">
                            <i class="fas fa-phone mr-1.5"></i>
                            Call Contact
                        </a>
                        @endif

                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        <form action="{{ route('contacts.destroy', $contact) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this contact?');"
                              class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center rounded-md hover:opacity-90 transition-all"
                                    style="padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8); background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger); font-size: var(--theme-font-size); font-weight: 500; border-radius: var(--theme-border-radius); border: none;">
                                <i class="fas fa-trash mr-1.5"></i>
                                Delete Contact
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Meta Information Card --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Information</h2>
                </div>
                <div style="padding: var(--theme-card-padding);">
                    <div class="space-y-4">
                        <div>
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Created</p>
                            <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $contact->created_at->format('d-m-Y H:i') }}</p>
                        </div>
                        <div>
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Last Updated</p>
                            <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $contact->updated_at->format('d-m-Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Timeline --}}
    <div class="mt-8">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Activity Timeline</h2>
                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.25rem;">All changes and updates to this contact</p>
            </div>
            <div style="padding: var(--theme-card-padding);">
                @php
                    $activities = $contact->activities()->with('user')->orderBy('created_at', 'desc')->get();
                @endphp

                @if($activities->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($activities as $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5" aria-hidden="true" style="background-color: rgba(203, 213, 225, 0.5);"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            @php
                                                $badgeColor = match($activity->activity_type ?? 'updated') {
                                                    'created' => 'var(--theme-success)',
                                                    'updated' => 'var(--theme-primary)',
                                                    'deleted' => 'var(--theme-danger)',
                                                    'company_added' => 'var(--theme-accent)',
                                                    'company_removed' => 'var(--theme-danger)',
                                                    default => 'var(--theme-text-muted)'
                                                };
                                            @endphp
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" style="background-color: {{ $badgeColor }};">
                                                <i class="text-white" style="font-size: calc(var(--theme-font-size) - 2px);">
                                                    @if($activity->activity_type == 'created')
                                                        <i class="fas fa-plus"></i>
                                                    @elseif($activity->activity_type == 'updated')
                                                        <i class="fas fa-edit"></i>
                                                    @elseif($activity->activity_type == 'deleted')
                                                        <i class="fas fa-trash"></i>
                                                    @else
                                                        <i class="fas fa-info"></i>
                                                    @endif
                                                </i>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div>
                                                <div>
                                                    <span style="font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-text);">
                                                        {{ $activity->user ? $activity->user->name : 'System' }}
                                                    </span>
                                                    <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                                                        @if($activity->activity_type == 'created')
                                                            created this contact
                                                        @elseif($activity->activity_type == 'updated')
                                                            {{ $activity->description }}
                                                        @elseif($activity->activity_type == 'company_added')
                                                            added a company relation
                                                        @elseif($activity->activity_type == 'company_removed')
                                                            removed a company relation
                                                        @else
                                                            {{ $activity->description }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                                    {{ $activity->created_at->format('d-m-Y H:i:s') }}
                                                    @if($activity->created_at->diffInMinutes(now()) < 60)
                                                        <span>({{ $activity->created_at->diffForHumans() }})</span>
                                                    @elseif($activity->created_at->isToday())
                                                        <span>(Today)</span>
                                                    @elseif($activity->created_at->isYesterday())
                                                        <span>(Yesterday)</span>
                                                    @endif
                                                    @if($activity->ip_address)
                                                        <span>• IP: {{ $activity->ip_address }}</span>
                                                    @endif
                                                </p>
                                            </div>

                                            @if($activity->changes && count($activity->changes) > 0)
                                                <div class="mt-3 rounded-lg border" style="background-color: rgba(248, 250, 252, 0.5); border-color: rgba(203, 213, 225, 0.5); padding: 1rem;">
                                                    <div style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                                        @if($activity->activity_type == 'created')
                                                            Initial Values
                                                        @elseif($activity->activity_type == 'updated')
                                                            Changed Fields ({{ count($activity->changes) }})
                                                        @else
                                                            Details
                                                        @endif
                                                    </div>
                                                    <div class="space-y-2">
                                                        @foreach($activity->changes as $field => $values)
                                                        <div class="flex items-start">
                                                            <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); min-width: 140px;">{{ $field }}:</span>
                                                            <div class="flex-1 ml-2">
                                                                @if($activity->activity_type == 'created')
                                                                    {{-- For creation, only show the new value --}}
                                                                    <span style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                                                        @if($values['new'] === null || $values['new'] === '')
                                                                            <em style="color: var(--theme-text-muted);">empty</em>
                                                                        @else
                                                                            {{ $values['new'] }}
                                                                        @endif
                                                                    </span>
                                                                @else
                                                                    {{-- For updates, show old → new --}}
                                                                    <div class="flex items-start gap-2">
                                                                        @if($values['old'] !== null && $values['old'] !== '')
                                                                            <span class="px-2 py-0.5 rounded line-through" style="background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger); font-size: var(--theme-font-size);">
                                                                                {{ Str::limit($values['old'], 50) }}
                                                                            </span>
                                                                        @else
                                                                            <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted); font-style: italic;">empty</span>
                                                                        @endif

                                                                        <span style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">→</span>

                                                                        <span class="px-2 py-0.5 rounded" style="background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); font-size: var(--theme-font-size);">
                                                                            @if($values['new'] === null || $values['new'] === '')
                                                                                <em style="color: var(--theme-text-muted);">empty</em>
                                                                            @else
                                                                                {{ Str::limit($values['new'], 50) }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto mb-4 w-16 h-16 flex items-center justify-center rounded-full" style="background-color: rgba(241, 245, 249, 0.5);">
                            <i class="fas fa-clock" style="font-size: calc(var(--theme-font-size) + 8px); color: var(--theme-text-muted);"></i>
                        </div>
                        <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">No activity yet</h3>
                        <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;">Activity will be shown here when changes are made.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle between view and edit mode for inline editing
function toggleEditMode(blockId) {
    const viewEl = document.getElementById(blockId + '-view');
    const editEl = document.getElementById(blockId + '-edit');
    const editBtn = document.getElementById('edit-' + blockId.replace('-info', '') + '-btn');

    if (viewEl.style.display === 'none') {
        // Switch back to view mode
        viewEl.style.display = 'block';
        editEl.style.display = 'none';
        if (editBtn) {
            editBtn.innerHTML = '<i class="fas fa-edit mr-1"></i>Edit';
        }
    } else {
        // Switch to edit mode
        viewEl.style.display = 'none';
        editEl.style.display = 'block';
        if (editBtn) {
            editBtn.innerHTML = '<i class="fas fa-times mr-1"></i>Cancel';
        }
    }
}

// Save inline edit with AJAX
function saveInlineEdit(blockId) {
    const form = document.getElementById(blockId + '-edit');
    const formData = new FormData(form);
    const saveBtn = document.getElementById('save-' + blockId.replace('-info', '') + '-btn');

    // Show loading state
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i>Saving...';
    saveBtn.disabled = true;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showErrorMessage('CSRF token not found. Please refresh the page.');
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        return;
    }

    // AJAX request
    fetch('{{ route("contacts.update", $contact->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // Non-JSON response, probably a redirect
            showSuccessMessage('Contact updated successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return Promise.resolve({ success: true });
        }
    })
    .then(data => {
        if (data.success) {
            showSuccessMessage('Contact updated successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showErrorMessage(data.message || 'An error occurred while updating the contact.');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showErrorMessage('Network error: ' + error.message);
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Show success/error messages
function showSuccessMessage(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 px-6 py-3 rounded-md shadow-lg z-50';
    notification.style.backgroundColor = 'var(--theme-success)';
    notification.style.color = 'white';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function showErrorMessage(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 px-6 py-3 rounded-md shadow-lg z-50';
    notification.style.backgroundColor = 'var(--theme-danger)';
    notification.style.color = 'white';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header back button
    const backBtn = document.getElementById('header-back-btn');
    if (backBtn) {
        backBtn.style.backgroundColor = '#6b7280';
        backBtn.style.color = 'white';
        backBtn.style.border = 'none';
        backBtn.style.borderRadius = 'var(--theme-border-radius)';
        backBtn.style.textDecoration = 'none';
    }

    // Header close button
    const closeBtn = document.getElementById('header-close-btn');
    if (closeBtn) {
        closeBtn.style.backgroundColor = '#6b7280';
        closeBtn.style.color = 'white';
        closeBtn.style.border = 'none';
        closeBtn.style.borderRadius = 'var(--theme-border-radius)';
        closeBtn.style.textDecoration = 'none';
    }

    // Edit button hover effect
    const editBtn = document.getElementById('edit-contact-btn');
    if (editBtn) {
        editBtn.addEventListener('mouseenter', function() {
            this.style.backgroundColor = primaryColor;
            this.style.color = 'white';
        });
        editBtn.addEventListener('mouseleave', function() {
            if (this.innerHTML.includes('Edit')) {
                this.style.backgroundColor = 'transparent';
                this.style.color = primaryColor;
            }
        });
    }

    // Save button
    const saveBtn = document.getElementById('save-contact-btn');
    if (saveBtn) {
        saveBtn.style.backgroundColor = primaryColor;
    }
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush
@endsection