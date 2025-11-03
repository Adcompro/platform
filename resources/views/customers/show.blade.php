@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="min-h-screen" style="background-color: var(--theme-bg);">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Customer Details</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">View customer information and projects</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(request('from') === 'contact' && request('contact_id'))
                        <a href="{{ route('contacts.show', request('contact_id')) }}"
                           id="header-close-btn"
                           class="header-btn"
                           style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                            <i class="fas fa-times mr-1.5"></i>
                            Close
                        </a>
                    @elseif(request('from') === 'project' && request('project_id'))
                        <a href="{{ route('projects.show', request('project_id')) }}"
                           id="header-back-project-btn"
                           class="header-btn"
                           style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                            <i class="fas fa-arrow-left mr-1.5"></i>
                            Back to Project
                        </a>
                    @else
                        <a href="{{ route('customers.index') }}"
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

        {{-- Two-column layout with CSS Grid --}}
        <div class="customers-two-column" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
            {{-- Company Details Block --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden" style="min-height: 600px; display: flex; flex-direction: column;">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between;">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Customer Information</h2>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button type="button" onclick="toggleEditMode('company-details')" id="edit-company-btn"
                            style="padding: 0.25rem 0.75rem; font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-primary); background: transparent; border: 1px solid var(--theme-primary); border-radius: var(--theme-border-radius); cursor: pointer;">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    @endif
                </div>
                <div style="padding: var(--theme-card-padding); flex: 1;">
                    <div id="company-details-view">
                        {{-- Key-Value Table Layout --}}
                        <table style="width: 100%; border-collapse: collapse;">
                            <tbody>
                                {{-- BASIC INFORMATION SECTION --}}
                                <tr>
                                    <td colspan="2" style="padding: 0.75rem 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05); border-bottom: 2px solid rgba(var(--theme-primary-rgb), 0.2);">
                                        <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fas fa-building mr-2" style="color: var(--theme-primary);"></i>Basic Information
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Company Name</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        <span style="font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-text);">{{ $customer->company ?: $customer->name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Customer Name</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        <span style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Contact Person</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        <span style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->contact_person ?: 'N/A' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Status</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        @if($customer->status === 'active')
                                            <span class="inline-flex px-2 py-1 rounded-full"
                                                  style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 rounded-full"
                                                  style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- CONTACT INFORMATION SECTION --}}
                                <tr>
                                    <td colspan="2" style="padding: 0.75rem 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05); border-bottom: 2px solid rgba(var(--theme-primary-rgb), 0.2);">
                                        <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fas fa-envelope mr-2" style="color: var(--theme-primary);"></i>Contact Information
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Email</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        @if($customer->email)
                                            <a href="mailto:{{ $customer->email }}" style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                <i class="fas fa-envelope mr-2" style="font-size: calc(var(--theme-font-size) - 2px);"></i>{{ $customer->email }}
                                            </a>
                                        @else
                                            <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Phone</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        @if($customer->phone)
                                            <a href="tel:{{ $customer->phone }}" style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                <i class="fas fa-phone mr-2" style="font-size: calc(var(--theme-font-size) - 2px);"></i>{{ $customer->phone }}
                                            </a>
                                        @else
                                            <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">N/A</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- LOCATION & LANGUAGE SECTION --}}
                                <tr>
                                    <td colspan="2" style="padding: 0.75rem 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05); border-bottom: 2px solid rgba(var(--theme-primary-rgb), 0.2);">
                                        <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fas fa-map-marker-alt mr-2" style="color: var(--theme-primary);"></i>Location & Language
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Address</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        @if($customer->street || $customer->city || $customer->zip_code || $customer->country)
                                            <div style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.6;">
                                                @if($customer->street)
                                                    <div>{{ $customer->street }}{{ $customer->addition ? ' ' . $customer->addition : '' }}</div>
                                                @endif
                                                @if($customer->zip_code || $customer->city)
                                                    <div>{{ $customer->zip_code }} {{ $customer->city }}</div>
                                                @endif
                                                @if($customer->country)
                                                    <div>{{ $customer->country }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No address provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Preferred Language</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        <div class="flex items-center" style="gap: 0.5rem;">
                                            <span style="font-size: 1.25rem;">{{ $customer->language_flag }}</span>
                                            <span style="font-weight: 500; font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->language_name }}</span>
                                        </div>
                                    </td>
                                </tr>

                                {{-- RELATIONSHIP INFORMATION SECTION --}}
                                <tr>
                                    <td colspan="2" style="padding: 0.75rem 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05); border-bottom: 2px solid rgba(var(--theme-primary-rgb), 0.2);">
                                        <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fas fa-handshake mr-2" style="color: var(--theme-primary);"></i>Relationship Information
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Managing Company</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        @if($customer->companyRelation)
                                            <span style="font-weight: 500; font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->companyRelation->name }}</span>
                                        @else
                                            <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No company assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 30%; padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); vertical-align: top;">
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">Customer Since</span>
                                    </td>
                                    <td style="padding: 0.875rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                                        <span style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->created_at->format('F Y') }}</span>
                                    </td>
                                </tr>

                                {{-- NOTES SECTION --}}
                                <tr>
                                    <td colspan="2" style="padding: 0.75rem 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05); border-bottom: 2px solid rgba(var(--theme-primary-rgb), 0.2);">
                                        <h3 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                                            <i class="fas fa-sticky-note mr-2" style="color: var(--theme-primary);"></i>Notes
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 0.875rem 1rem;">
                                        @if($customer->notes)
                                            <div class="whitespace-pre-wrap" style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.6; padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.03); border-radius: var(--theme-border-radius); border-left: 3px solid var(--theme-primary);">{{ $customer->notes }}</div>
                                        @else
                                            <div style="font-size: var(--theme-font-size); color: var(--theme-text-muted); font-style: italic;">No notes available</div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Edit Form (Hidden by default) --}}
                    <form id="company-details-edit" style="display: none;">
                        @csrf
                        @method('PUT')

                        {{-- Two Column Grid Layout --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Left Column --}}
                            <div class="space-y-6">
                                {{-- Basic Information --}}
                                <div>
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Basic Information</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Company Name</label>
                                            <input type="text" name="company" value="{{ $customer->company }}"
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Customer Name</label>
                                            <input type="text" name="name" value="{{ $customer->name }}" required
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Contact Person</label>
                                            <input type="text" name="contact_person" value="{{ $customer->contact_person }}"
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Status</label>
                                            <select name="status"
                                                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                                <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contact Information --}}
                                <div>
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Contact Information</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Email</label>
                                            <input type="email" name="email" value="{{ $customer->email }}"
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Phone</label>
                                            <input type="text" name="phone" value="{{ $customer->phone }}"
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                    </div>
                                </div>

                                {{-- Location & Language --}}
                                <div>
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Location & Language</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Street</label>
                                            <input type="text" name="street" value="{{ $customer->street }}"
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Addition</label>
                                            <input type="text" name="addition" value="{{ $customer->addition }}"
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Zip Code</label>
                                                <input type="text" name="zip_code" value="{{ $customer->zip_code }}"
                                                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                            </div>
                                            <div>
                                                <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">City</label>
                                                <input type="text" name="city" value="{{ $customer->city }}"
                                                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                            </div>
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Country</label>
                                            <input type="text" name="country" value="{{ $customer->country }}"
                                                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Preferred Language</label>
                                            <select name="language"
                                                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                                @foreach(\App\Models\Customer::getAvailableLanguages() as $code => $name)
                                                    <option value="{{ $code }}" {{ ($customer->language ?? 'nl') == $code ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- End Left Column --}}

                            {{-- Right Column --}}
                            <div class="space-y-6">
                                {{-- Relationship Information --}}
                                <div>
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Relationship Information</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label style="display: block; font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Managing Company</label>
                                            <select name="company_id"
                                                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                                <option value="">Select a company</option>
                                                @php
                                                    $companies = \App\Models\Company::when(Auth::user()->role !== 'super_admin', function($q) {
                                                        $q->where('id', Auth::user()->company_id);
                                                    })->get();
                                                @endphp
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ $customer->company_id == $company->id ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Notes</h3>
                                    <textarea name="notes" rows="8"
                                              style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">{{ $customer->notes }}</textarea>
                                </div>
                            </div>
                            {{-- End Right Column --}}
                        </div>
                        {{-- End Two Column Grid --}}

                        {{-- Action Buttons --}}
                        <div class="flex justify-end gap-2 pt-4 mt-6" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
                            <button type="button" onclick="toggleEditMode('company-details')"
                                    style="padding: 0.5rem 1rem; background-color: #6b7280; color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); cursor: pointer;">
                                <i class="fas fa-times mr-1.5"></i>Cancel
                            </button>
                            <button type="button" onclick="saveInlineEdit('company-details')" id="save-company-btn"
                                    style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); cursor: pointer;">
                                <i class="fas fa-save mr-1.5"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Contact Persons Column --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden" style="min-height: 600px; display: flex; flex-direction: column;">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px;">
                    <div class="flex justify-between items-center" style="height: 100%;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Contact Persons</h2>
                        <div class="flex items-center gap-2">
                            @if($customer->teamleader_id && in_array(Auth::user()->role, ['super_admin', 'admin']))
                                <a href="{{ route('teamleader.select.contacts') }}?customer_id={{ $customer->id }}"
                                   class="inline-flex items-center border border-transparent rounded-md shadow-sm font-medium text-white"
                                   style="background-color: #10b981; border-radius: var(--theme-border-radius); padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                                    <i class="fas fa-cloud-download-alt mr-1"></i>
                                    Import from Teamleader
                                </a>
                            @endif
                            <a href="{{ route('contacts.create') }}?customer_id={{ $customer->id }}"
                               class="inline-flex items-center border border-transparent rounded-md shadow-sm font-medium text-white"
                               style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius); padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                                <i class="fas fa-plus mr-1"></i>
                                Add Contact
                            </a>
                        </div>
                    </div>
                </div>
                <div style="padding: var(--theme-card-padding); flex: 1;">
                    @if($customer->contacts && $customer->contacts->count() > 0)
                        <div class="space-y-4">
                            @foreach($customer->contacts as $contact)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                            {{ $contact->name }}
                                            @if($contact->pivot && $contact->pivot->is_primary)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded"
                                                      style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);">
                                                    Primary
                                                </span>
                                            @endif
                                        </h4>
                                        @if($contact->position)
                                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">{{ $contact->position }}</p>
                                        @endif
                                    </div>
                                    <div class="flex space-x-1">
                                        <a href="{{ route('contacts.show', $contact) }}?from=customer&customer_id={{ $customer->id }}"
                                           class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-3 space-y-1">
                                    @if($contact->email)
                                    <a href="mailto:{{ $contact->email }}" class="flex items-center" style="color: var(--theme-primary); text-decoration: none; font-size: calc(var(--theme-font-size) - 2px);">
                                        <i class="fas fa-envelope mr-1.5" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                        {{ $contact->email }}
                                    </a>
                                    @endif
                                    @if($contact->phone)
                                    <a href="tel:{{ $contact->phone }}" class="flex items-center" style="color: var(--theme-primary); text-decoration: none; font-size: calc(var(--theme-font-size) - 2px);">
                                        <i class="fas fa-phone mr-1.5" style="font-size: calc(var(--theme-font-size) - 4px);"></i>
                                        {{ $contact->phone }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col justify-center items-center h-full py-12">
                            <div class="rounded-full p-4 mb-4" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                                <svg class="h-8 w-8" style="color: var(--theme-primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="font-medium text-center mb-2" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-text);">No contact persons yet</h3>
                            <p class="text-center mb-6 max-w-xs" style="font-size: var(--theme-font-size); color: var(--theme-text-muted); line-height: 1.5;">Add contact persons to keep track of who you work with at this customer.</p>
                            <a href="{{ route('contacts.create') }}?customer_id={{ $customer->id }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm font-medium text-white transition-all hover:opacity-90"
                               style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                                <i class="fas fa-plus mr-2"></i>
                                Add Contact Person
                            </a>

                            {{-- Example/Preview Card --}}
                            <div class="mt-8 w-full max-w-sm">
                                <div class="border-2 border-dashed rounded-lg p-4" style="border-color: rgba(var(--theme-primary-rgb), 0.3); background-color: rgba(var(--theme-primary-rgb), 0.02);">
                                    <div class="text-center" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                        <i class="fas fa-user-circle text-2xl mb-2" style="color: rgba(var(--theme-primary-rgb), 0.5);"></i>
                                        <p class="mb-1">Contact persons will appear here</p>
                                        <p class="text-xs">with name, email, phone & position</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    {{-- Projects Section (true full width) --}}
    <div style="margin-left: -2rem; margin-right: -2rem; padding: 0 2rem; margin-top: 2rem;">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Projects</h2>
                            @if($customer->projects->count() > 0)
                                <div class="flex items-center gap-2">
                                    <button onclick="toggleProjectFilter('active')" id="filter-active"
                                            class="px-3 py-1 rounded-md text-sm font-medium transition-all"
                                            style="background-color: var(--theme-primary); color: white; border: none; cursor: pointer;">
                                        Active ({{ $customer->projects->where('status', 'active')->count() }})
                                    </button>
                                    <button onclick="toggleProjectFilter('all')" id="filter-all"
                                            class="px-3 py-1 rounded-md text-sm font-medium transition-all"
                                            style="background-color: transparent; color: var(--theme-text-muted); border: 1px solid var(--theme-border); cursor: pointer;">
                                        All ({{ $customer->projects->count() }})
                                    </button>
                                </div>
                            @endif
                        </div>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <button type="button"
                                    onclick="openNewProjectModal()"
                                    class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white"
                                    style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius); cursor: pointer;">
                                <i class="fas fa-plus mr-1"></i>
                                New Project
                            </button>
                        @endif
                    </div>
                </div>
                @if($customer->projects->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                            <thead style="background-color: var(--theme-table-header-bg);">
                                <tr>
                                    <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Project Name
                                    </th>
                                    <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Status
                                    </th>
                                    <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Start Date
                                    </th>
                                    <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        End Date
                                    </th>
                                    <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Budget
                                    </th>
                                    <th style="padding: 0.75rem 1.5rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Budget Used
                                    </th>
                                    <th style="padding: 0.75rem 1.5rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/60 divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                                @foreach($customer->projects->sortByDesc('start_date') as $project)
                                <tr class="hover:bg-gray-50 project-row" data-status="{{ $project->status }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('projects.show', $project) }}" class="font-medium" style="color: var(--theme-accent); font-size: var(--theme-font-size);">
                                                {{ $project->name }}
                                            </a>

                                            @if($project->is_master_template)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800" title="Master Template">
                                                    <i class="fas fa-crown mr-1"></i>
                                                    MASTER
                                                </span>
                                            @endif

                                            @if($project->is_recurring)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" title="Recurring Project">
                                                    <i class="fas fa-sync-alt mr-1"></i>
                                                    RECURRING
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full font-medium
                                            @if($project->status === 'active') bg-green-100 text-green-800
                                            @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                                            @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800
                                            @elseif($project->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif"
                                            style="font-size: calc(var(--theme-font-size) - 2px);">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                        {{ $project->start_date ? $project->start_date->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                        {{ $project->end_date ? $project->end_date->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                        @if($project->monthly_fee)
                                            €{{ number_format($project->monthly_fee, 2, ',', '.') }}/month
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                        @php
                                            $budgetUsed = $project->budget_used ?? 0;
                                            $budgetTotal = $project->budget_total ?? $project->monthly_fee ?? 0;
                                            $percentage = $budgetTotal > 0 ? min(100, round(($budgetUsed / $budgetTotal) * 100)) : 0;
                                        @endphp
                                        @if($budgetTotal > 0)
                                            <div style="font-weight: 600; color: var(--theme-text);">
                                                €{{ number_format($budgetUsed, 2, ',', '.') }}
                                            </div>
                                            <div style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted);">
                                                {{ $percentage }}% of budget
                                            </div>
                                        @else
                                            <span style="color: var(--theme-text-muted);">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right" style="font-size: var(--theme-font-size);">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('projects.show', $project) }}"
                                               class="text-gray-400 hover:text-gray-600"
                                               title="View Project">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($project->monthly_fee && in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <a href="{{ route('projects.year-budget', $project->id) }}"
                                               class="text-blue-400 hover:text-blue-600"
                                               title="Year Budget">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                            @endif
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <a href="{{ route('projects.edit', $project) }}"
                                               class="text-gray-400 hover:text-gray-600"
                                               title="Edit Project">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 font-medium" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">No projects</h3>
                        <p class="mt-1" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">This customer doesn't have any projects yet.</p>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <div class="mt-6">
                            <a href="{{ route('projects.create') }}?customer_id={{ $customer->id }}"
                               class="inline-flex items-center font-normal transition-all"
                               style="background-color: var(--theme-primary); color: white; border: none; font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                                <i class="fas fa-plus mr-1.5 text-xs"></i>
                                Create First Project
                            </a>
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>


    </div>

    {{-- New Project Modal --}}
    <div id="newProjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50" style="backdrop-filter: blur(4px);">
        <div class="bg-white rounded-xl shadow-2xl w-full mx-4" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <form action="{{ route('projects.store') }}" method="POST" id="newProjectForm" onsubmit="copySeriesIdBeforeSubmit(event)">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="status" value="active">

                {{-- Modal Header --}}
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1.25rem 1.5rem;">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                <i class="fas fa-folder-plus mr-2" style="color: var(--theme-primary);"></i>
                                New Project
                            </h3>
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                Create a new project for {{ $customer->name }}
                            </p>
                        </div>
                        <button type="button" onclick="closeNewProjectModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) + 4px);"></i>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div style="padding: 1.5rem;">
                    <div class="space-y-4">
                        {{-- Customer (Read-only) --}}
                        <div>
                            <label class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                Customer
                            </label>
                            <div style="padding: 0.75rem; background-color: rgba(var(--theme-primary-rgb), 0.05); border: 1px solid rgba(var(--theme-primary-rgb), 0.2); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); color: var(--theme-text); font-weight: 500;">
                                <i class="fas fa-user mr-2" style="color: var(--theme-primary);"></i>
                                {{ $customer->name }}
                            </div>
                        </div>

                        {{-- Status (Read-only, shown as Active) --}}
                        <div>
                            <label class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                Status
                            </label>
                            <div style="padding: 0.75rem; background-color: rgba(var(--theme-success-rgb), 0.05); border: 1px solid rgba(var(--theme-success-rgb), 0.2); border-radius: var(--theme-border-radius);">
                                <span class="inline-flex items-center px-2 py-1 rounded-full" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Active
                                </span>
                            </div>
                        </div>

                        {{-- Template Selection --}}
                        <div>
                            <label for="modal_template_id" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                Project Template (Optional)
                            </label>
                            <select name="template_id"
                                    id="modal_template_id"
                                    style="width: 100%; padding: 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                <option value="">-- No template (start from scratch) --</option>
                                @if(isset($templates) && $templates->count() > 0)
                                    @foreach($templates as $template)
                                    <option value="{{ $template->id }}">
                                        {{ $template->name }}
                                        @if($template->milestones->count() > 0)
                                            ({{ $template->milestones->count() }} milestones)
                                        @endif
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                            <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                                <i class="fas fa-info-circle mr-1"></i>
                                Start from a template to quickly set up milestones and tasks
                            </p>
                        </div>

                        {{-- Project Name --}}
                        <div>
                            <label for="modal_project_name" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                Project Name <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="modal_project_name"
                                   required
                                   style="width: 100%; padding: 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);"
                                   placeholder="Enter project name...">
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="modal_project_description" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                Description
                            </label>
                            <textarea name="description"
                                      id="modal_project_description"
                                      rows="3"
                                      style="width: 100%; padding: 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);"
                                      placeholder="Describe the project scope and goals..."></textarea>
                        </div>

                        {{-- Dates Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="modal_start_date" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                    Start Date
                                </label>
                                <input type="date"
                                       name="start_date"
                                       id="modal_start_date"
                                       value="{{ date('Y-m-d') }}"
                                       style="width: 100%; padding: 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                            </div>
                            <div>
                                <label for="modal_end_date" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                    End Date
                                </label>
                                <input type="date"
                                       name="end_date"
                                       id="modal_end_date"
                                       style="width: 100%; padding: 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                            </div>
                        </div>

                        {{-- Monthly Fee --}}
                        <div>
                            <label for="modal_monthly_fee" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                Monthly Fee (€)
                            </label>
                            <input type="number"
                                   name="monthly_fee"
                                   id="modal_monthly_fee"
                                   step="0.01"
                                   min="0"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);"
                                   placeholder="0.00">
                        </div>

                        {{-- Recurring Project Checkbox --}}
                        <div class="border-t pt-4" style="border-color: rgba(203, 213, 225, 0.3); margin-top: 1rem;">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox"
                                           name="is_recurring"
                                           id="modal_is_recurring"
                                           value="1"
                                           onchange="toggleModalRecurringSettings(this.checked)"
                                           class="w-4 h-4 rounded"
                                           style="color: var(--theme-primary); border-color: rgba(203, 213, 225, 0.6);">
                                </div>
                                <div class="ml-3">
                                    <label for="modal_is_recurring" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                        <i class="fas fa-sync-alt mr-1" style="color: var(--theme-primary);"></i>
                                        Make this a recurring project
                                    </label>
                                    <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                        Automatically create new projects at regular intervals
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Recurring Settings (hidden by default) --}}
                        <div id="modal-recurring-settings" style="display: none;" class="space-y-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            {{-- Recurring Frequency --}}
                            <div>
                                <label for="modal_recurring_frequency" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                    Frequency <span style="color: var(--theme-danger);">*</span>
                                </label>
                                <select name="recurring_frequency"
                                        id="modal_recurring_frequency"
                                        style="width: 100%; padding: 0.5rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>

                            {{-- Recurring Base Name --}}
                            <div>
                                <label for="modal_recurring_base_name" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                    Base Name <span style="color: var(--theme-danger);">*</span>
                                </label>
                                <input type="text"
                                       name="recurring_base_name"
                                       id="modal_recurring_base_name"
                                       style="width: 100%; padding: 0.5rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);"
                                       placeholder="e.g., Website Maintenance">
                                <p class="mt-1" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                                    New projects will be named: "[Base Name] [Month] [Year]"
                                </p>
                            </div>

                            {{-- Recurring Series ID --}}
                            <div>
                                <label for="modal_recurring_series_id_select" class="block mb-1" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted);">
                                    Series ID
                                </label>
                                <select name="recurring_series_id_select"
                                        id="modal_recurring_series_id_select"
                                        onchange="handleModalSeriesSelection(this.value)"
                                        style="width: 100%; padding: 0.5rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                    <option value="">-- Auto-generate --</option>
                                    @if(isset($existingSeriesIds) && $existingSeriesIds->count() > 0)
                                        <optgroup label="Existing Series">
                                            @foreach($existingSeriesIds as $series)
                                                <option value="{{ $series->recurring_series_id }}">
                                                    {{ $series->recurring_series_id }} ({{ $series->project_count }} projects)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    <option value="_custom">-- Enter custom series ID --</option>
                                </select>
                                {{-- Hidden field dat ALTIJD de waarde bevat (ook al is het hidden) --}}
                                <input type="hidden"
                                       name="recurring_series_id"
                                       id="modal_recurring_series_id_hidden"
                                       value="">
                                {{-- Visible custom input (alleen zichtbaar bij _custom selectie) --}}
                                <input type="text"
                                       id="modal_recurring_series_id_custom_visible"
                                       style="display: none; width: 100%; padding: 0.5rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); margin-top: 0.5rem;"
                                       placeholder="Enter custom series ID"
                                       oninput="document.getElementById('modal_recurring_series_id_hidden').value = this.value">
                            </div>
                        </div>

                        {{-- Master Template Mode Checkbox (only shown when is_recurring is checked) --}}
                        <div id="modal-master-template-mode-container" style="display: none;" class="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox"
                                           name="is_master_template"
                                           id="modal_is_master_template"
                                           value="1"
                                           class="w-4 h-4 text-purple-600 bg-white border-purple-300 rounded focus:ring-purple-500">
                                </div>
                                <div class="ml-3">
                                    <label for="modal_is_master_template" class="text-sm font-medium text-purple-900">
                                        <i class="fas fa-crown text-purple-600 mr-1"></i>
                                        Use as Master Template
                                    </label>
                                    <p class="text-xs text-purple-700 mt-1">
                                        This project will serve as the template for all future projects in this series.
                                        Only general structure and settings will be stored - no month-specific milestones or time entries.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="border-t" style="border-color: rgba(203, 213, 225, 0.3); padding: 1rem 1.5rem;">
                    <div class="flex justify-end gap-2">
                        <button type="button"
                                onclick="closeNewProjectModal()"
                                style="padding: 0.625rem 1.25rem; background-color: #e5e7eb; color: #6b7280; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); font-weight: 500; cursor: pointer;">
                            <i class="fas fa-times mr-1.5"></i>
                            Cancel
                        </button>
                        <button type="submit"
                                style="padding: 0.625rem 1.25rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); font-weight: 500; cursor: pointer;">
                            <i class="fas fa-plus mr-1.5"></i>
                            Create Project
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle between view and edit mode for inline editing
function toggleEditMode(blockId) {
    const viewEl = document.getElementById(blockId + '-view');
    const editEl = document.getElementById(blockId + '-edit');
    const editBtn = document.getElementById('edit-' + blockId.replace('-details', '') + '-btn');

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
    const saveBtn = document.getElementById('save-' + blockId.replace('-details', '') + '-btn');

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

    // Add _method field for PATCH request (Laravel spoofing)
    formData.append('_method', 'PATCH');

    // AJAX request
    fetch('{{ route("customers.update-inline", $customer->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        if (!response.ok) {
            // Try to get validation errors from response
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const errorData = await response.json();
                console.error('Validation errors:', errorData);

                if (errorData.errors) {
                    // Show all validation errors
                    const errorMessages = Object.values(errorData.errors).flat().join(', ');
                    throw new Error(`Validation failed: ${errorMessages}`);
                } else if (errorData.message) {
                    throw new Error(errorData.message);
                }
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // Non-JSON response, probably a redirect
            showSuccessMessage('Customer updated successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return Promise.resolve({ success: true });
        }
    })
    .then(data => {
        if (data && data.success) {
            showSuccessMessage('Customer updated successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else if (data && data.success === false) {
            showErrorMessage(data.message || 'An error occurred while updating the customer.');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showErrorMessage(error.message || 'Network error occurred');
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

    // Header back buttons
    const backBtn = document.getElementById('header-back-btn');
    if (backBtn) {
        backBtn.style.backgroundColor = '#6b7280';
        backBtn.style.color = 'white';
        backBtn.style.border = 'none';
        backBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    const backProjectBtn = document.getElementById('header-back-project-btn');
    if (backProjectBtn) {
        backProjectBtn.style.backgroundColor = '#6b7280';
        backProjectBtn.style.color = 'white';
        backProjectBtn.style.border = 'none';
        backProjectBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    const closeBtn = document.getElementById('header-close-btn');
    if (closeBtn) {
        closeBtn.style.backgroundColor = '#6b7280';
        closeBtn.style.color = 'white';
        closeBtn.style.border = 'none';
        closeBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Edit button hover effect
    const editBtn = document.getElementById('edit-company-btn');
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
    const saveBtn = document.getElementById('save-company-btn');
    if (saveBtn) {
        saveBtn.style.backgroundColor = primaryColor;
    }
}

// Toggle project filter between active and all
function toggleProjectFilter(filter) {
    const activeBtn = document.getElementById('filter-active');
    const allBtn = document.getElementById('filter-all');
    const projectRows = document.querySelectorAll('.project-row');

    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const textMuted = getComputedStyle(document.documentElement).getPropertyValue('--theme-text-muted').trim();
    const borderColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-border').trim();

    if (filter === 'active') {
        // Style active button
        activeBtn.style.backgroundColor = primaryColor;
        activeBtn.style.color = 'white';
        activeBtn.style.border = 'none';

        // Style all button
        allBtn.style.backgroundColor = 'transparent';
        allBtn.style.color = textMuted;
        allBtn.style.border = '1px solid ' + borderColor;

        // Show only active projects
        projectRows.forEach(row => {
            if (row.dataset.status === 'active') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    } else {
        // Style all button
        allBtn.style.backgroundColor = primaryColor;
        allBtn.style.color = 'white';
        allBtn.style.border = 'none';

        // Style active button
        activeBtn.style.backgroundColor = 'transparent';
        activeBtn.style.color = textMuted;
        activeBtn.style.border = '1px solid ' + borderColor;

        // Show all projects
        projectRows.forEach(row => {
            row.style.display = '';
        });
    }
}

// New Project Modal Functions
function openNewProjectModal() {
    const modal = document.getElementById('newProjectModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling

    // Focus on project name input
    setTimeout(() => {
        document.getElementById('modal_project_name').focus();
    }, 100);
}

function closeNewProjectModal() {
    const modal = document.getElementById('newProjectModal');
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Restore scrolling

    // Reset form
    document.getElementById('newProjectForm').reset();

    // Reset start date to today
    document.getElementById('modal_start_date').value = '{{ date("Y-m-d") }}';

    // Reset template selection
    document.getElementById('modal_template_id').value = '';

    // Reset recurring settings
    const recurringSettings = document.getElementById('modal-recurring-settings');
    const masterTemplateContainer = document.getElementById('modal-master-template-mode-container');
    if (recurringSettings) {
        recurringSettings.style.display = 'none';
    }
    if (masterTemplateContainer) {
        masterTemplateContainer.style.display = 'none';
    }
}

// Toggle recurring settings visibility in modal
function toggleModalRecurringSettings(isChecked) {
    const settingsDiv = document.getElementById('modal-recurring-settings');
    const masterTemplateDiv = document.getElementById('modal-master-template-mode-container');

    if (settingsDiv) {
        settingsDiv.style.display = isChecked ? 'block' : 'none';
    }

    if (masterTemplateDiv) {
        masterTemplateDiv.style.display = isChecked ? 'block' : 'none';
    }

    // Clear fields als recurring wordt uitgezet
    if (!isChecked) {
        document.getElementById('modal_recurring_base_name').value = '';
        document.getElementById('modal_recurring_series_id_select').value = '';
        document.getElementById('modal_recurring_series_id_hidden').value = '';
        document.getElementById('modal_recurring_series_id_custom_visible').style.display = 'none';
        document.getElementById('modal_is_master_template').checked = false;
    }
}

// Handle series selection in modal
function handleModalSeriesSelection(value) {
    const hiddenInput = document.getElementById('modal_recurring_series_id_hidden');
    const customInput = document.getElementById('modal_recurring_series_id_custom_visible');

    if (value === '_custom') {
        // Show custom input voor handmatige invoer
        customInput.style.display = 'block';
        customInput.focus();
        customInput.value = '';
        customInput.readOnly = false;
        hiddenInput.value = ''; // Clear hidden field voor custom entry
    } else {
        // Hide custom input en vul hidden field met geselecteerde waarde
        customInput.style.display = 'none';
        customInput.value = '';
        customInput.readOnly = true;
        hiddenInput.value = value; // Kan '' zijn (auto-generate) of een bestaande series ID
    }

    console.log('Series selected:', value, 'Hidden input value:', hiddenInput.value);
}

// Copy series ID before form submit (CRITICAL FIX!)
function copySeriesIdBeforeSubmit(event) {
    const selectDropdown = document.getElementById('modal_recurring_series_id_select');
    const hiddenInput = document.getElementById('modal_recurring_series_id_hidden');
    const customInput = document.getElementById('modal_recurring_series_id_custom_visible');
    const isRecurring = document.getElementById('modal_is_recurring');

    // Als recurring NIET gecheckt is, skip deze logica
    if (!isRecurring || !isRecurring.checked) {
        console.log('Not a recurring project, skipping series ID copy');
        return true;
    }

    // Kopieer dropdown waarde naar hidden field
    if (selectDropdown && hiddenInput) {
        const dropdownValue = selectDropdown.value;

        if (dropdownValue === '_custom') {
            // Gebruik waarde van custom visible input
            if (customInput && customInput.value) {
                hiddenInput.value = customInput.value;
            }
        } else if (dropdownValue) {
            // Gebruik dropdown waarde direct
            hiddenInput.value = dropdownValue;
        }

        console.log('BEFORE SUBMIT - Dropdown:', dropdownValue, 'Hidden field:', hiddenInput.value);
    }

    return true; // Allow form submission
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('newProjectModal');
    if (event.target === modal) {
        closeNewProjectModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('newProjectModal');
        if (!modal.classList.contains('hidden')) {
            closeNewProjectModal();
        }
    }
});

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();

    // Set initial filter to active projects
    toggleProjectFilter('active');
});

// Month Detail Modal for Budget Timeline
function openMonthDetailModal(monthData, monthName) {
    if (!monthData) {
        alert('No data available for this month yet.');
        return;
    }

    const percentage = monthData.percentage;
    let statusColor, statusText;

    if (percentage <= 80) {
        statusColor = 'green';
        statusText = 'Excellent';
    } else if (percentage <= 100) {
        statusColor = 'yellow';
        statusText = 'On Track';
    } else {
        statusColor = 'red';
        statusText = 'Over Budget';
    }

    const modalHTML = `
        <div id="monthDetailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeMonthDetailModal()">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full" onclick="event.stopPropagation()">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-indigo-500">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-white">${monthName} - Budget Details</h3>
                        <button onclick="closeMonthDetailModal()" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="text-xs text-blue-600 font-semibold mb-1">Total Budget</div>
                            <div class="text-2xl font-bold text-blue-700">€${Number(monthData.budget_total).toLocaleString('nl-NL')}</div>
                        </div>

                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <div class="text-xs text-purple-600 font-semibold mb-1">Budget Used</div>
                            <div class="text-2xl font-bold text-purple-700">€${Number(monthData.budget_used).toLocaleString('nl-NL')}</div>
                        </div>

                        <div class="bg-${statusColor}-50 rounded-lg p-4 border border-${statusColor}-200">
                            <div class="text-xs text-${statusColor}-600 font-semibold mb-1">Usage Percentage</div>
                            <div class="text-2xl font-bold text-${statusColor}-700">${percentage}%</div>
                            <div class="text-xs text-${statusColor}-600 mt-1">${statusText}</div>
                        </div>

                        <div class="bg-${monthData.budget_remaining >= 0 ? 'green' : 'red'}-50 rounded-lg p-4 border border-${monthData.budget_remaining >= 0 ? 'green' : 'red'}-200">
                            <div class="text-xs text-${monthData.budget_remaining >= 0 ? 'green' : 'red'}-600 font-semibold mb-1">${monthData.budget_remaining >= 0 ? 'Remaining' : 'Overspent'}</div>
                            <div class="text-2xl font-bold text-${monthData.budget_remaining >= 0 ? 'green' : 'red'}-700">€${Math.abs(monthData.budget_remaining).toLocaleString('nl-NL')}</div>
                        </div>
                    </div>

                    ${Math.abs(monthData.rollover_amount) > 0 ? `
                    <div class="bg-gradient-to-r from-${monthData.rollover_amount > 0 ? 'green' : 'orange'}-50 to-${monthData.rollover_amount > 0 ? 'green' : 'orange'}-100 rounded-lg p-4 border-2 border-${monthData.rollover_amount > 0 ? 'green' : 'orange'}-300 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-${monthData.rollover_amount > 0 ? 'green' : 'orange'}-700 mb-1">
                                    ${monthData.rollover_amount > 0 ? '💰 Budget Rollover to Next Month' : '⚠️ Budget Deficit from Previous Month'}
                                </div>
                                <div class="text-xs text-${monthData.rollover_amount > 0 ? 'green' : 'orange'}-600">
                                    ${monthData.rollover_amount > 0
                                        ? 'Unused budget will be added to next month'
                                        : 'Overspent amount will be deducted from next month'}
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-${monthData.rollover_amount > 0 ? 'green' : 'orange'}-700">
                                ${monthData.rollover_amount > 0 ? '+' : ''}€${Math.abs(monthData.rollover_amount).toLocaleString('nl-NL')}
                            </div>
                        </div>
                    </div>
                    ` : ''}

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-3">Budget Breakdown</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Base Monthly Budget:</span>
                                <span class="font-semibold">€${Number(monthData.budget_total - monthData.rollover_amount).toLocaleString('nl-NL')}</span>
                            </div>
                            ${Math.abs(monthData.rollover_amount) > 0 ? `
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Rollover from Previous:</span>
                                <span class="font-semibold ${monthData.rollover_amount > 0 ? 'text-green-600' : 'text-red-600'}">
                                    ${monthData.rollover_amount > 0 ? '+' : ''}€${Math.abs(monthData.rollover_amount).toLocaleString('nl-NL')}
                                </span>
                            </div>
                            ` : ''}
                            <div class="border-t pt-2 flex justify-between text-sm font-bold">
                                <span class="text-gray-700">Total Available:</span>
                                <span>€${Number(monthData.budget_total).toLocaleString('nl-NL')}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="/projects/${monthData.project.id}" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i>View Project Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    document.body.style.overflow = 'hidden';
}

function closeMonthDetailModal() {
    const modal = document.getElementById('monthDetailModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto';
    }
}
</script>
@endpush

@push('styles')
<style>
/* Two-column layout responsive styles */
@media (max-width: 768px) {
    .customers-two-column {
        display: block !important;
    }
    .customers-two-column > div {
        margin-bottom: 2rem;
    }
}
</style>
@endpush

