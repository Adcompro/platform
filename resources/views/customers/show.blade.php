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
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <button type="button"
                            onclick="openEditModal()"
                            id="header-edit-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-edit mr-1.5"></i>
                        Edit Customer
                    </button>
                    @endif
                    @if(request('from') === 'project' && request('project_id'))
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

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                    {{ $customer->projects->count() }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                    Total Projects
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-success-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);">
                    {{ $customer->projects->where('status', 'active')->count() }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-success);">
                    Active Projects
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                    {{ $customer->contacts ? $customer->contacts->count() : 0 }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                    Contact Persons
                </div>
            </div>

            <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-success-rgb), 0.05);">
                <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);">
                    {{ $customer->created_at->format('M Y') }}
                </div>
                <div style="font-size: var(--theme-font-size); color: var(--theme-success);">
                    Customer Since
                </div>
            </div>
        </div>

        {{-- Two-column layout with CSS Grid --}}
        <div class="customers-two-column" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
            {{-- Customer Information Column --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden" style="min-height: 600px; display: flex; flex-direction: column;">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center;">
                    <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Customer Information</h2>
                </div>
                <div style="padding: var(--theme-card-padding); flex: 1;">
                    <div class="space-y-6">
                        {{-- Basic Information --}}
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Basic Details</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Customer Name</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->name }}</dd>
                                </div>
                                <div>
                                    <dt style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Company</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->company ?: 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Email</dt>
                                    <dd>
                                        <a href="mailto:{{ $customer->email }}" style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                            {{ $customer->email }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Phone</dt>
                                    <dd>
                                        @if($customer->phone)
                                            <a href="tel:{{ $customer->phone }}" style="color: var(--theme-primary); text-decoration: none; font-size: var(--theme-font-size);">
                                                {{ $customer->phone }}
                                            </a>
                                        @else
                                            <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Contact Person</dt>
                                    <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->contact_person ?: 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Status</dt>
                                    <dd>
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
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Address Information --}}
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Address Information</h3>
                            <div style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                @if($customer->street || $customer->city || $customer->zip_code || $customer->country)
                                    @if($customer->street)
                                        <p>{{ $customer->street }}{{ $customer->addition ? ' ' . $customer->addition : '' }}</p>
                                    @endif
                                    @if($customer->zip_code || $customer->city)
                                        <p>{{ $customer->zip_code }} {{ $customer->city }}</p>
                                    @endif
                                    @if($customer->country)
                                        <p>{{ $customer->country }}</p>
                                    @endif
                                @else
                                    <p style="color: var(--theme-text-muted);">No address provided</p>
                                @endif
                            </div>
                        </div>

                        {{-- Additional Information --}}
                        @if($customer->notes)
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Notes</h3>
                            <div class="whitespace-pre-wrap" style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $customer->notes }}</div>
                        </div>
                        @endif

                        {{-- Managing Company --}}
                        <div>
                            <h3 style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Managing Company</h3>
                            <div style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                @if($customer->companyRelation)
                                    {{ $customer->companyRelation->name }}
                                @else
                                    <span style="color: var(--theme-text-muted);">No company assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Persons Column --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden" style="min-height: 600px; display: flex; flex-direction: column;">
                <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px;">
                    <div class="flex justify-between items-center" style="height: 100%;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Contact Persons</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a href="{{ route('contacts.create') }}?customer_id={{ $customer->id }}"
                           class="inline-flex items-center border border-transparent rounded-md shadow-sm font-medium text-white"
                           style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius); padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                            <i class="fas fa-plus mr-1"></i>
                            Add Contact
                        </a>
                        @endif
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
                                        <a href="{{ route('contacts.show', $contact) }}"
                                           class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                        </a>
                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                        <a href="{{ route('contacts.edit', $contact) }}"
                                           class="text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-edit" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                                        </a>
                                        @endif
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
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <a href="{{ route('contacts.create') }}?customer_id={{ $customer->id }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm font-medium text-white transition-all hover:opacity-90"
                               style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                                <i class="fas fa-plus mr-2"></i>
                                Add Contact Person
                            </a>
                            @endif

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
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Projects</h2>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                        <a href="{{ route('projects.create') }}?customer_id={{ $customer->id }}"
                           class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white"
                           style="background-color: var(--theme-primary); border-radius: var(--theme-border-radius);">
                            <i class="fas fa-plus mr-1"></i>
                            New Project
                        </a>
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
                                    <th style="padding: 0.75rem 1.5rem; text-align: right; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/60 divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                                @foreach($customer->projects as $project)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('projects.show', $project) }}" class="font-medium" style="color: var(--theme-accent); font-size: var(--theme-font-size);">
                                            {{ $project->name }}
                                        </a>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-right" style="font-size: var(--theme-font-size);">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('projects.show', $project) }}"
                                               class="text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                            <a href="{{ route('projects.edit', $project) }}"
                                               class="text-gray-400 hover:text-gray-600">
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

    {{-- Location Section (after Projects) --}}
    @if($customer->street && $customer->city && $customer->country)
    <div style="margin-left: -2rem; margin-right: -2rem; padding: 0 2rem; margin-top: 2rem;">
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Location</h2>
            </div>
            <div style="padding: var(--theme-card-padding);">
                <div class="relative">
                    @php
                        $address = urlencode($customer->street . ' ' . $customer->addition . ', ' . $customer->zip_code . ' ' . $customer->city . ', ' . $customer->country);
                    @endphp
                    <iframe
                        width="100%"
                        height="400"
                        frameborder="0"
                        style="border:0; border-radius: 0.5rem;"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q={{ $address }}"
                        allowfullscreen>
                    </iframe>
                    <div class="absolute bottom-4 left-4 bg-white bg-opacity-90 rounded-lg px-3 py-2" style="box-shadow: var(--theme-card-shadow);">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text);">
                            {{ $customer->street }}{{ $customer->addition ? ' ' . $customer->addition : '' }},
                            {{ $customer->zip_code }} {{ $customer->city }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Customer Modal --}}
    <div id="editCustomerModal" class="fixed inset-0 z-50 hidden">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        {{-- Modal Content --}}
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden bg-gray-50 border border-slate-200/60 rounded-xl shadow-xl transition-all w-full max-w-4xl max-h-[90vh] flex flex-col">
                    {{-- Modal Header --}}
                    <div class="border-b px-6 py-4" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <div class="flex items-center justify-between">
                            <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Edit Customer</h3>
                            <button type="button" onclick="closeEditModal()" style="color: var(--theme-text-muted);" class="hover:opacity-60">
                                <i class="fas fa-times" style="font-size: calc(var(--theme-font-size) + 4px);"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="flex-1 overflow-y-auto" style="padding: var(--theme-card-padding); max-height: calc(90vh - 200px);">
                        <form id="editCustomerForm">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Customer Name --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Customer Name</label>
                                    <input type="text" name="name" id="edit_name" value="{{ $customer->name }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Company --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Company</label>
                                    <input type="text" name="company" id="edit_company" value="{{ $customer->company }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Email</label>
                                    <input type="email" name="email" id="edit_email" value="{{ $customer->email }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Phone</label>
                                    <input type="text" name="phone" id="edit_phone" value="{{ $customer->phone }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Contact Person --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Contact Person</label>
                                    <input type="text" name="contact_person" id="edit_contact_person" value="{{ $customer->contact_person }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Status</label>
                                    <select name="status" id="edit_status" class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                        <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                {{-- Street --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Street</label>
                                    <input type="text" name="street" id="edit_street" value="{{ $customer->street }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Addition --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Addition</label>
                                    <input type="text" name="addition" id="edit_addition" value="{{ $customer->addition }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Zip Code --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Zip Code</label>
                                    <input type="text" name="zip_code" id="edit_zip_code" value="{{ $customer->zip_code }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- City --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">City</label>
                                    <input type="text" name="city" id="edit_city" value="{{ $customer->city }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Country --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Country</label>
                                    <input type="text" name="country" id="edit_country" value="{{ $customer->country }}"
                                           class="modal-input" style="font-size: var(--theme-font-size) !important;">
                                </div>

                                {{-- Managing Company --}}
                                <div>
                                    <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Managing Company</label>
                                    <select name="company_id" id="edit_company_id" class="modal-input" style="font-size: var(--theme-font-size) !important;">
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

                            {{-- Notes - Full width --}}
                            <div class="mt-4">
                                <label class="block font-medium mb-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Notes</label>
                                <textarea name="notes" id="edit_notes" rows="2" class="modal-input" style="font-size: var(--theme-font-size) !important; width: 100%;">{{ $customer->notes }}</textarea>
                            </div>
                        </form>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="border-t flex justify-end gap-3" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 font-medium rounded-md hover:opacity-80"
                                style="font-size: var(--theme-font-size); color: var(--theme-text-muted); background-color: rgba(var(--theme-text-muted-rgb), 0.1); border-radius: var(--theme-border-radius);">
                            Cancel
                        </button>
                        <button type="button" onclick="saveCustomer(event)"
                                class="px-4 py-2 font-medium text-white rounded-md hover:opacity-90"
                                style="font-size: var(--theme-font-size); background-color: var(--theme-primary); border-radius: var(--theme-border-radius);">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>
@endsection

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

/* Modal form styling */
#editCustomerModal label {
    font-size: var(--theme-font-size) !important;
    color: var(--theme-text) !important;
    font-weight: 500 !important;
    margin-bottom: 0.5rem !important;
    display: block !important;
}

/* FORCE smaller font size for modal inputs */
#editCustomerModal .modal-input,
#editCustomerModal input.modal-input,
#editCustomerModal select.modal-input,
#editCustomerModal textarea.modal-input {
    font-size: var(--theme-font-size) !important;
    line-height: 1.5 !important;
    border: 1px solid rgba(203, 213, 225, 0.6) !important;
    border-radius: var(--theme-border-radius) !important;
    padding: calc(var(--theme-font-size) * 0.6) calc(var(--theme-font-size) * 0.8) !important;
    width: 100% !important;
    color: var(--theme-text) !important;
    background-color: white !important;
    outline: none !important;
}

#editCustomerModal .modal-input:focus,
#editCustomerModal input.modal-input:focus,
#editCustomerModal select.modal-input:focus,
#editCustomerModal textarea.modal-input:focus {
    border-color: var(--theme-primary) !important;
    box-shadow: 0 0 0 2px rgba(var(--theme-primary-rgb), 0.2) !important;
}

#editCustomerModal .modal-input::placeholder,
#editCustomerModal input.modal-input::placeholder,
#editCustomerModal textarea.modal-input::placeholder {
    color: var(--theme-text-muted) !important;
    font-size: var(--theme-font-size) !important;
}

#editCustomerModal select.modal-input option {
    font-size: var(--theme-font-size) !important;
    color: var(--theme-text) !important;
}
</style>
@endpush

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header edit button
    const editBtn = document.getElementById('header-edit-btn');
    if (editBtn) {
        editBtn.style.backgroundColor = primaryColor;
        editBtn.style.color = 'white';
        editBtn.style.border = 'none';
        editBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

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
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});

// Modal functions
function openEditModal() {
    document.getElementById('editCustomerModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeEditModal() {
    document.getElementById('editCustomerModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restore scrolling
}

// Save customer function with AJAX
function saveCustomer(event) {
    event.preventDefault();

    const form = document.getElementById('editCustomerForm');
    const formData = new FormData(form);

    // Show loading state
    const saveButton = event.target;
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Saving...';
    saveButton.disabled = true;

    console.log('Saving customer...', Object.fromEntries(formData));
    console.log('UPDATED JAVASCRIPT VERSION - JSON HANDLING ACTIVE');

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        showErrorMessage('CSRF token not found. Please refresh the page.');
        saveButton.textContent = originalText;
        saveButton.disabled = false;
        return;
    }

    // AJAX request
    fetch('{{ route("customers.update", $customer->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // Handle non-JSON response (probably HTML redirect)
            console.log('Non-JSON response received, likely a redirect. Refreshing page...');
            showSuccessMessage('Customer updated successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            return Promise.resolve({ success: true });
        }
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Update the page content with new data
            updateCustomerDisplay(data.customer);
            closeEditModal();

            // Show success message
            showSuccessMessage('Customer updated successfully!');
        } else {
            // Show error message
            showErrorMessage(data.message || 'An error occurred while updating the customer.');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showErrorMessage('Network error: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

// Update customer display with new data
function updateCustomerDisplay(customer) {
    // Update all the display elements with new customer data
    document.querySelector('h1').textContent = 'Customer Details';

    // Update customer information display
    const customerInfoSection = document.querySelector('#customer-info-column');
    // You can add more specific updates here as needed

    // For now, just refresh the page to show updated data
    window.location.reload();
}

// Show success/error messages
function showSuccessMessage(message) {
    // Create and show a success notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-md shadow-lg z-50';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function showErrorMessage(message) {
    // Create and show an error notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-md shadow-lg z-50';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Close modal when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
    }
});
</script>
@endpush