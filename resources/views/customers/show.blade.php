@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="min-h-screen" style="background-color: var(--theme-bg);">
    {{-- Sticky Header --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">{{ $customer->name }}</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">
                        {{ $customer->company }} • {{ ucfirst($customer->status) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if(request('from') === 'contact' && request('contact_id'))
                        <a href="{{ route('contacts.show', request('contact_id')) }}"
                           class="header-btn"
                           style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                            <i class="fas fa-times mr-1.5"></i>
                            Close
                        </a>
                    @elseif(request('from') === 'project' && request('project_id'))
                        <a href="{{ route('projects.show', request('project_id')) }}"
                           class="header-btn"
                           style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                            <i class="fas fa-arrow-left mr-1.5"></i>
                            Back to Project
                        </a>
                    @else
                        <a href="{{ route('customers.index') }}"
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

    {{-- Main Content --}}
    <div style="padding: 1.5rem 2rem 8rem 2rem;">
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

        {{-- Full width layout --}}
        <div style="max-width: 100%;">
            <div class="space-y-4">

                @include('customers.show-customer-info-section')

                {{-- Contacts Card --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleSection('contacts')">
                        <div class="flex items-center gap-4" style="flex: 1;">
                            <div class="flex items-center" style="min-width: 280px;">
                                <i id="contacts-icon" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                    <i class="fas fa-users mr-2"></i>
                                    Contacts
                                </h2>
                            </div>
                            <div class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                                <span style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                                    {{ $customer->contacts->count() }} {{ $customer->contacts->count() === 1 ? 'contact' : 'contacts' }}
                                </span>
                            </div>
                        </div>
                        <div class="section-actions" data-section="contacts" style="display: none;">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('contacts.create', ['customer_id' => $customer->id]) }}"
                                   onclick="event.stopPropagation();"
                                   class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                   style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                                    <i class="fas fa-plus mr-1"></i>
                                    Add
                                </a>
                                <a href="{{ route('teamleader.select.contacts', ['customer_id' => $customer->id]) }}"
                                   onclick="event.stopPropagation();"
                                   class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                   style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                                    <i class="fas fa-cloud-download-alt mr-1"></i>
                                    Import
                                </a>
                            </div>
                        </div>
                    </div>
                    <div id="contacts-content" style="padding: var(--theme-card-padding); display: none;">
                        @if($customer->contacts->count() > 0)
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                @foreach($customer->contacts as $contact)
                                <div style="padding: 1rem; border: 1px solid rgba(203, 213, 225, 0.3); border-radius: var(--theme-border-radius);">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <div style="font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-text);">{{ $contact->name }}</div>
                                                @if($contact->is_primary)
                                                <span style="padding: 0.125rem 0.5rem; background-color: #dbeafe; color: #2563eb; border-radius: 9999px; font-size: 10px; font-weight: 600;">PRIMARY</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('contacts.edit', $contact->id) }}" class="p-1 hover:bg-gray-100 rounded transition-colors" title="Edit Contact">
                                                <i class="fas fa-edit" style="color: var(--theme-text-muted); font-size: 12px;"></i>
                                            </a>
                                            <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this contact?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 hover:bg-red-50 rounded transition-colors" title="Delete Contact">
                                                    <i class="fas fa-trash" style="color: #dc2626; font-size: 12px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @if($contact->position)
                                        <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-bottom: 0.5rem;">{{ $contact->position }}</div>
                                    @endif
                                    @if($contact->email)
                                        <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-primary); margin-bottom: 0.25rem;">
                                            <i class="fas fa-envelope mr-1"></i>
                                            <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                        </div>
                                    @endif
                                    @if($contact->phone)
                                        <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-primary);">
                                            <i class="fas fa-phone mr-1"></i>
                                            <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                                        </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="mt-2" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No contacts for this customer</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Projects Card --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleSection('projects')">
                        <div class="flex items-center gap-4" style="flex: 1;">
                            <div class="flex items-center" style="min-width: 280px;">
                                <i id="projects-icon" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                    <i class="fas fa-project-diagram mr-2"></i>
                                    Projects
                                </h2>
                            </div>
                            <div class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                                <span id="projects-count" style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                                    @php
                                        $currentYearProjects = $customer->projects->filter(function($p) {
                                            return $p->start_date && $p->start_date->year == date('Y');
                                        });
                                    @endphp
                                    {{ $currentYearProjects->count() }} {{ $currentYearProjects->count() === 1 ? 'project' : 'projects' }} (this year)
                                </span>
                            </div>
                        </div>
                        <div class="section-actions" data-section="projects" style="display: none;">
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center px-2 py-1 rounded transition-colors cursor-pointer"
                                       style="background-color: rgba(var(--theme-info-rgb), 0.1); color: var(--theme-info); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;"
                                       onclick="event.stopPropagation();">
                                    <input type="checkbox" id="show-all-years" onchange="toggleProjectYears()" class="mr-2">
                                    <span>Show All Years</span>
                                </label>
                                <a href="{{ route('projects.create', ['customer_id' => $customer->id]) }}"
                                   onclick="event.stopPropagation();"
                                   class="inline-flex items-center px-2 py-1 rounded transition-colors"
                                   style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                                    <i class="fas fa-plus mr-1"></i>
                                    New Project
                                </a>
                            </div>
                        </div>
                    </div>
                    <div id="projects-content" style="padding: var(--theme-card-padding); display: none;">
                        @if($customer->projects->count() > 0)
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid var(--theme-border);">
                                            <th style="padding: 0.75rem; text-align: center; width: 40px;">
                                                <input type="checkbox" id="select-all-projects" onchange="toggleAllProjects()" class="rounded" style="cursor: pointer;">
                                            </th>
                                            <th style="padding: 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Project Name</th>
                                            <th style="padding: 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Status</th>
                                            <th style="padding: 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Start Date</th>
                                            <th style="padding: 0.75rem; text-align: right; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Monthly Fee</th>
                                            <th style="padding: 0.75rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->projects->sortByDesc(function($p) { return $p->start_date; }) as $project)
                                        @php
                                            $projectYear = $project->start_date ? $project->start_date->year : 'unknown';
                                            $isCurrentYear = $projectYear == date('Y');
                                        @endphp
                                        <tr class="project-row" data-year="{{ $projectYear }}" data-project-id="{{ $project->id }}" style="border-bottom: 1px solid var(--theme-border); {{ !$isCurrentYear ? 'display: none;' : '' }}">
                                            <td style="padding: 1rem 0.75rem; text-align: center;">
                                                <input type="checkbox" class="project-checkbox rounded" value="{{ $project->id }}" onchange="updateBulkActionsVisibility()" style="cursor: pointer;">
                                            </td>
                                            <td style="padding: 1rem 0.75rem;">
                                                <a href="{{ route('projects.show', $project->id) }}" style="font-weight: 500; color: var(--theme-primary); font-size: var(--theme-font-size); text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                                                    {{ $project->name }}
                                                </a>
                                                @if(!$isCurrentYear)
                                                <span style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted); margin-left: 0.5rem;">({{ $projectYear }})</span>
                                                @endif
                                            </td>
                                            <td style="padding: 1rem 0.75rem;">
                                                @php
                                                    $statusConfig = [
                                                        'draft' => ['label' => 'Draft', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
                                                        'active' => ['label' => 'Active', 'color' => '#059669', 'bg' => '#d1fae5'],
                                                        'completed' => ['label' => 'Completed', 'color' => '#2563eb', 'bg' => '#dbeafe'],
                                                        'on_hold' => ['label' => 'On Hold', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
                                                        'cancelled' => ['label' => 'Cancelled', 'color' => '#dc2626', 'bg' => '#fee2e2'],
                                                    ];
                                                    $status = $statusConfig[$project->status] ?? ['label' => ucfirst($project->status), 'color' => '#6b7280', 'bg' => '#f3f4f6'];
                                                @endphp
                                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: {{ $status['color'] }}; background-color: {{ $status['bg'] }};">
                                                    {{ $status['label'] }}
                                                </span>
                                            </td>
                                            <td style="padding: 1rem 0.75rem;">
                                                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                                    {{ $project->start_date ? $project->start_date->format('d-m-Y') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td style="padding: 1rem 0.75rem; text-align: right;">
                                                <span style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">
                                                    @if($project->monthly_fee)
                                                        €{{ number_format($project->monthly_fee, 0) }}
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                            </td>
                                            <td style="padding: 1rem 0.75rem; text-align: center;">
                                                <a href="{{ route('projects.show', $project->id) }}" class="p-1.5 hover:bg-gray-100 rounded transition-colors" title="View Project">
                                                    <i class="fas fa-eye" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-2" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No projects for this customer</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Invoices Card --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleSection('invoices')">
                        <div class="flex items-center gap-4" style="flex: 1;">
                            <div class="flex items-center" style="min-width: 280px;">
                                <i id="invoices-icon" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                                    <i class="fas fa-file-invoice mr-2"></i>
                                    Invoices
                                </h2>
                            </div>
                            @php
                                $totalInvoiceAmount = $invoices->sum('total_inc_vat');
                            @endphp
                            <div class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                                <span style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                                    {{ $invoices->count() }} {{ $invoices->count() === 1 ? 'invoice' : 'invoices' }}
                                </span>
                                <span style="color: var(--theme-primary); font-weight: 600;">
                                    • €{{ number_format($totalInvoiceAmount, 2) }} total
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="invoices-content" style="padding: var(--theme-card-padding); display: none;">
                        @if($invoices->count() > 0)
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 1px solid var(--theme-border);">
                                            <th style="padding: 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Invoice #</th>
                                            <th style="padding: 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Project</th>
                                            <th style="padding: 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Date</th>
                                            <th style="padding: 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Status</th>
                                            <th style="padding: 0.75rem; text-align: right; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Amount</th>
                                            <th style="padding: 0.75rem; text-align: center; font-size: calc(var(--theme-font-size) - 1px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoices as $invoice)
                                        <tr style="border-bottom: 1px solid var(--theme-border);">
                                            <td style="padding: 1rem 0.75rem;">
                                                <span style="font-weight: 500; color: var(--theme-text); font-size: var(--theme-font-size);">{{ $invoice->invoice_number }}</span>
                                            </td>
                                            <td style="padding: 1rem 0.75rem;">
                                                <a href="{{ route('projects.show', $invoice->project_id) }}" class="hover:underline" style="color: var(--theme-primary); font-size: var(--theme-font-size);">
                                                    {{ $invoice->project->name ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td style="padding: 1rem 0.75rem;">
                                                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                                                    {{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : 'N/A' }}
                                                </span>
                                            </td>
                                            <td style="padding: 1rem 0.75rem;">
                                                @php
                                                    $statusConfig = [
                                                        'draft' => ['label' => 'Draft', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
                                                        'finalized' => ['label' => 'Finalized', 'color' => '#2563eb', 'bg' => '#dbeafe'],
                                                        'sent' => ['label' => 'Sent', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
                                                        'paid' => ['label' => 'Paid', 'color' => '#059669', 'bg' => '#d1fae5'],
                                                        'overdue' => ['label' => 'Overdue', 'color' => '#dc2626', 'bg' => '#fee2e2'],
                                                        'cancelled' => ['label' => 'Cancelled', 'color' => '#991b1b', 'bg' => '#fecaca'],
                                                    ];
                                                    $status = $statusConfig[$invoice->status] ?? ['label' => ucfirst($invoice->status), 'color' => '#6b7280', 'bg' => '#f3f4f6'];
                                                @endphp
                                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: {{ $status['color'] }}; background-color: {{ $status['bg'] }};">
                                                    {{ $status['label'] }}
                                                </span>
                                            </td>
                                            <td style="padding: 1rem 0.75rem; text-align: right;">
                                                <span style="font-weight: 600; color: var(--theme-text); font-size: var(--theme-font-size);">
                                                    €{{ number_format($invoice->subtotal_ex_vat ?? 0, 2) }}
                                                </span>
                                            </td>
                                            <td style="padding: 1rem 0.75rem; text-align: center;">
                                                <div class="flex justify-center gap-1">
                                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="p-1.5 hover:bg-gray-100 rounded transition-colors" title="View Invoice">
                                                        <i class="fas fa-eye" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                                                    </a>
                                                    @if($invoice->status === 'draft')
                                                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="p-1.5 hover:bg-gray-100 rounded transition-colors" title="Edit Invoice">
                                                        <i class="fas fa-edit" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
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
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2" style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No invoices for this customer</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Floating Bulk Actions Bar (Projects) --}}
    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
    <div id="floating-bulk-actions" class="fixed bottom-0 left-0 right-0 z-40 transition-all duration-300"
         style="transform: translateY(100%); pointer-events: none;">
        <div class="max-w-4xl mx-auto px-4 pb-6">
            <div class="backdrop-blur-lg rounded-2xl shadow-2xl border overflow-hidden"
                 style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
                        border-color: rgba(var(--theme-border-rgb), 0.3);
                        pointer-events: auto;">
                <div class="flex items-center justify-between px-6 py-4">
                    {{-- Left: Selection Info --}}
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                 style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                                <i class="fas fa-check" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) + 2px);"></i>
                            </div>
                            <div>
                                <div id="floating-selected-count" class="font-semibold" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                    0 selected
                                </div>
                                <div class="text-xs" style="color: var(--theme-text-muted);">
                                    Choose an action below
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Action Buttons --}}
                    <div class="flex items-center gap-2">
                        {{-- Status Change Dropdown --}}
                        <div class="relative">
                            <button onclick="toggleStatusDropdown(event)" id="status-dropdown-btn"
                                    class="px-4 py-2 rounded-lg font-medium text-white text-sm"
                                    style="background-color: #3b82f6;">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                <span>Change Status</span>
                            </button>

                            {{-- Fixed positioning dropdown --}}
                            <div id="status-dropdown" class="hidden fixed bg-white rounded-lg shadow-2xl border overflow-hidden z-50"
                                 style="border-color: rgba(var(--theme-border-rgb), 0.3); min-width: 200px;">
                                <button onclick="openBulkStatusModal('draft')" class="w-full px-4 py-2 text-left hover:bg-gray-50 transition-colors flex items-center gap-2" style="color: #6b7280; font-size: 14px;">
                                    <i class="fas fa-file-alt w-4"></i>
                                    <span>Set to Draft</span>
                                </button>
                                <button onclick="openBulkStatusModal('active')" class="w-full px-4 py-2 text-left hover:bg-gray-50 transition-colors flex items-center gap-2" style="color: #10b981; font-size: 14px;">
                                    <i class="fas fa-play w-4"></i>
                                    <span>Activate</span>
                                </button>
                                <button onclick="openBulkStatusModal('on_hold')" class="w-full px-4 py-2 text-left hover:bg-gray-50 transition-colors flex items-center gap-2" style="color: #f59e0b; font-size: 14px;">
                                    <i class="fas fa-pause w-4"></i>
                                    <span>Put On Hold</span>
                                </button>
                                <button onclick="openBulkStatusModal('completed')" class="w-full px-4 py-2 text-left hover:bg-gray-50 transition-colors flex items-center gap-2" style="color: #3b82f6; font-size: 14px;">
                                    <i class="fas fa-check-circle w-4"></i>
                                    <span>Mark Completed</span>
                                </button>
                                <button onclick="openBulkStatusModal('cancelled')" class="w-full px-4 py-2 text-left hover:bg-gray-50 transition-colors flex items-center gap-2" style="color: #ef4444; font-size: 14px;">
                                    <i class="fas fa-times-circle w-4"></i>
                                    <span>Cancel</span>
                                </button>
                            </div>
                        </div>

                        {{-- Delete Button --}}
                        <button onclick="openBulkDeleteModal()" class="px-4 py-2 rounded-lg font-medium text-sm text-white"
                                style="background-color: #ef4444;">
                            <i class="fas fa-trash mr-2"></i>
                            <span>Delete</span>
                        </button>

                        {{-- Clear Selection --}}
                        <button onclick="clearSelection()" class="px-4 py-2 rounded-lg font-medium text-sm"
                                style="background-color: #e5e7eb; color: #6b7280;">
                            <i class="fas fa-times mr-2"></i>
                            <span>Clear</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Status Change Modal --}}
    <div id="bulkStatusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div id="statusModalIcon" class="w-12 h-12 rounded-full flex items-center justify-center">
                        <i id="statusModalIconElement" class="text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 id="statusModalTitle" class="text-lg font-semibold" style="color: var(--theme-text);">Change Status</h3>
                        <p class="text-sm" style="color: var(--theme-text-muted);">
                            <span id="statusModalCount">0</span> projects selected
                        </p>
                    </div>
                </div>

                <p id="statusModalMessage" class="text-sm mb-6" style="color: var(--theme-text-muted);">
                    Are you sure you want to change the status of these projects?
                </p>
            </div>

            <div class="border-t px-6 py-4 flex justify-end gap-3" style="border-color: var(--theme-border);">
                <button onclick="closeBulkStatusModal()"
                        style="background-color: #e5e7eb; color: #6b7280; font-size: 14px; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600;">
                    Cancel
                </button>
                <button id="statusModalConfirmBtn" onclick="confirmBulkStatusChange()"
                        style="font-size: 14px; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600;">
                    Change Status
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Modal --}}
    <div id="bulkDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         style="background-color: rgba(239, 68, 68, 0.1);">
                        <i class="fas fa-trash text-xl" style="color: #ef4444;"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold" style="color: var(--theme-text);">Delete Projects</h3>
                        <p class="text-sm" style="color: var(--theme-text-muted);">
                            <span id="deleteModalCount">0</span> projects selected
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm mb-3" style="color: var(--theme-text-muted);">
                        Are you sure you want to delete these projects? This action cannot be undone.
                    </p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <p class="text-sm text-red-700 font-medium">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Warning: This will permanently delete all associated data including milestones, tasks, and time entries.
                        </p>
                    </div>
                </div>
            </div>

            <div class="border-t px-6 py-4 flex justify-end gap-3" style="border-color: var(--theme-border);">
                <button onclick="closeBulkDeleteModal()"
                        style="background-color: #e5e7eb; color: #6b7280; font-size: 14px; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600;">
                    Cancel
                </button>
                <button onclick="confirmBulkDelete()"
                        style="background-color: #ef4444; color: #ffffff; font-size: 14px; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600;">
                    Delete Projects
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let isEditing = false;
let currentBulkStatus = null;

// Status configuration with hardcoded colors
const statusConfig = {
    'draft': {
        title: 'Set to Draft',
        icon: 'fas fa-file-alt',
        color: '#6b7280',
        bgColor: '#f3f4f6',
        btnColor: '#6b7280',
        displayName: 'Draft',
        message: 'Move these projects back to draft status?'
    },
    'active': {
        title: 'Activate Projects',
        icon: 'fas fa-play',
        color: '#10b981',
        bgColor: '#d1fae5',
        btnColor: '#10b981',
        displayName: 'Active',
        message: 'Activate these projects?'
    },
    'on_hold': {
        title: 'Put Projects On Hold',
        icon: 'fas fa-pause',
        color: '#f59e0b',
        bgColor: '#fef3c7',
        btnColor: '#f59e0b',
        displayName: 'On Hold',
        message: 'Put these projects on hold?'
    },
    'completed': {
        title: 'Mark Projects as Completed',
        icon: 'fas fa-check-circle',
        color: '#3b82f6',
        bgColor: '#dbeafe',
        btnColor: '#3b82f6',
        displayName: 'Completed',
        message: 'Mark these projects as completed?'
    },
    'cancelled': {
        title: 'Cancel Projects',
        icon: 'fas fa-times-circle',
        color: '#ef4444',
        bgColor: '#fee2e2',
        btnColor: '#ef4444',
        displayName: 'Cancelled',
        message: 'Cancel these projects? This action cannot be undone.'
    }
};

function toggleSection(sectionId) {
    const content = document.getElementById(sectionId + '-content');
    const icon = document.getElementById(sectionId + '-icon');
    const actions = document.querySelector('.section-actions[data-section="' + sectionId + '"]');

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
        if (actions) {
            actions.style.display = 'block';
        }
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
        if (actions) {
            actions.style.display = 'none';
        }
    }
}

function toggleProjectYears() {
    const showAllYears = document.getElementById('show-all-years').checked;
    const currentYear = {{ date('Y') }};
    const projectRows = document.querySelectorAll('.project-row');
    const projectsCount = document.getElementById('projects-count');

    let visibleCount = 0;

    projectRows.forEach(row => {
        const year = row.getAttribute('data-year');

        if (showAllYears || year == currentYear) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update counter
    if (showAllYears) {
        projectsCount.textContent = visibleCount + (visibleCount === 1 ? ' project' : ' projects') + ' (all years)';
    } else {
        projectsCount.textContent = visibleCount + (visibleCount === 1 ? ' project' : ' projects') + ' (this year)';
    }

    // Update bulk actions visibility
    updateBulkActionsVisibility();
}

// Bulk Actions Functions
function toggleAllProjects() {
    const selectAll = document.getElementById('select-all-projects');
    const checkboxes = document.querySelectorAll('.project-checkbox');
    const visibleCheckboxes = Array.from(checkboxes).filter(cb => {
        const row = cb.closest('.project-row');
        return row && row.style.display !== 'none';
    });

    visibleCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    updateBulkActionsVisibility();
}

function updateBulkActionsVisibility() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const floatingBar = document.getElementById('floating-bulk-actions');
    const selectedCount = document.getElementById('floating-selected-count');
    const selectAll = document.getElementById('select-all-projects');

    if (checkboxes.length > 0) {
        floatingBar.style.transform = 'translateY(0)';
        selectedCount.textContent = checkboxes.length + ' selected';
    } else {
        floatingBar.style.transform = 'translateY(100%)';
    }

    // Update select-all checkbox state
    const visibleCheckboxes = Array.from(document.querySelectorAll('.project-checkbox')).filter(cb => {
        const row = cb.closest('.project-row');
        return row && row.style.display !== 'none';
    });
    const allVisibleChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
    if (selectAll) {
        selectAll.checked = allVisibleChecked;
    }
}

function clearSelection() {
    document.querySelectorAll('.project-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('select-all-projects').checked = false;
    updateBulkActionsVisibility();
}

function toggleStatusDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('status-dropdown');
    const button = document.getElementById('status-dropdown-btn');

    if (dropdown.classList.contains('hidden')) {
        // Position dropdown
        const buttonRect = button.getBoundingClientRect();
        dropdown.style.left = buttonRect.left + 'px';
        dropdown.style.bottom = (window.innerHeight - buttonRect.top + 8) + 'px';
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function openBulkStatusModal(status) {
    currentBulkStatus = status;
    const config = statusConfig[status];
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');

    // Close dropdown
    document.getElementById('status-dropdown').classList.add('hidden');

    // Update modal content
    document.getElementById('statusModalTitle').textContent = config.title;
    document.getElementById('statusModalCount').textContent = checkboxes.length;
    document.getElementById('statusModalMessage').textContent = config.message;

    // Update icon
    const iconContainer = document.getElementById('statusModalIcon');
    const iconElement = document.getElementById('statusModalIconElement');
    iconContainer.style.backgroundColor = config.bgColor;
    iconElement.className = config.icon + ' text-xl';
    iconElement.style.color = config.color;

    // Update confirm button - FORCE all styles
    const confirmBtn = document.getElementById('statusModalConfirmBtn');
    confirmBtn.style.backgroundColor = config.btnColor;
    confirmBtn.style.color = '#ffffff';
    confirmBtn.style.border = 'none';
    confirmBtn.style.fontSize = '14px';
    confirmBtn.style.fontWeight = '600';
    confirmBtn.style.padding = '0.5rem 1rem';
    confirmBtn.style.borderRadius = '0.5rem';
    confirmBtn.textContent = 'Change to ' + config.displayName;

    // Show modal
    document.getElementById('bulkStatusModal').classList.remove('hidden');
}

function closeBulkStatusModal() {
    document.getElementById('bulkStatusModal').classList.add('hidden');
    currentBulkStatus = null;
}

function confirmBulkStatusChange() {
    if (!currentBulkStatus) return;

    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const projectIds = Array.from(checkboxes).map(cb => cb.value);

    if (projectIds.length === 0) {
        closeBulkStatusModal();
        return;
    }

    // Show loading
    const confirmBtn = document.getElementById('statusModalConfirmBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    confirmBtn.disabled = true;

    // Submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("customers.bulk-action-projects", $customer) }}';

    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Action
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'status_change';
    form.appendChild(actionInput);

    // Status
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = currentBulkStatus;
    form.appendChild(statusInput);

    // Project IDs
    projectIds.forEach(id => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'project_ids[]';
        idInput.value = id;
        form.appendChild(idInput);
    });

    document.body.appendChild(form);
    form.submit();
}

// Bulk Delete Functions
function openBulkDeleteModal() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const count = checkboxes.length;

    if (count === 0) {
        alert('Please select at least one project to delete');
        return;
    }

    document.getElementById('deleteModalCount').textContent = count;
    document.getElementById('bulkDeleteModal').classList.remove('hidden');
}

function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.add('hidden');
}

function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('.project-checkbox:checked');
    const projectIds = Array.from(checkboxes).map(cb => cb.value);

    if (projectIds.length === 0) {
        closeBulkDeleteModal();
        return;
    }

    // Submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("customers.bulk-action-projects", $customer) }}';

    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Action
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete';
    form.appendChild(actionInput);

    // Project IDs
    projectIds.forEach(id => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'project_ids[]';
        idInput.value = id;
        form.appendChild(idInput);
    });

    document.body.appendChild(form);
    form.submit();
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#status-dropdown-btn') && !e.target.closest('#status-dropdown')) {
        document.getElementById('status-dropdown').classList.add('hidden');
    }
});

// Close dropdown on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('status-dropdown').classList.add('hidden');
        closeBulkStatusModal();
        closeBulkDeleteModal();
    }
});

// Other existing functions
function toggleEdit() {
    isEditing = true;
    document.getElementById('edit-btn').classList.add('hidden');
    document.getElementById('edit-actions').classList.remove('hidden');

    // Show all field-edit, hide all field-view
    document.querySelectorAll('.field-view').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.field-edit').forEach(el => el.classList.remove('hidden'));
}

function cancelEdit() {
    isEditing = false;
    document.getElementById('edit-btn').classList.remove('hidden');
    document.getElementById('edit-actions').classList.add('hidden');

    // Show all field-view, hide all field-edit
    document.querySelectorAll('.field-view').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('.field-edit').forEach(el => el.classList.add('hidden'));

    // Reset form
    document.getElementById('customer-form').reset();
    location.reload();
}

function handleCompanyCheckboxChange(companyId) {
    // Als checkbox wordt uitgechecked, disable de bijbehorende radio button
    const checkbox = document.getElementById('company_' + companyId);
    const radio = document.getElementById('primary_' + companyId);

    if (radio) {
        if (checkbox.checked) {
            radio.disabled = false;
        } else {
            radio.disabled = true;
            radio.checked = false;
        }
    }
}

function saveEdit() {
    const form = document.getElementById('customer-form');
    const formData = new FormData(form);

    // Convert to JSON
    const data = {};

    // Handle checkboxes array speciaal (companies[])
    const companiesCheckboxes = document.querySelectorAll('input[name="companies[]"]:checked');
    if (companiesCheckboxes.length > 0) {
        data.companies = Array.from(companiesCheckboxes).map(cb => cb.value);
    }

    // Handle radio button voor primary company
    const primaryRadio = document.querySelector('input[name="company_primary"]:checked');
    if (primaryRadio) {
        data.company_primary = primaryRadio.value;
    }

    // Alle andere velden
    formData.forEach((value, key) => {
        // Skip companies[] want die hebben we al
        if (key !== 'companies[]' && key !== 'company_primary') {
            if (value !== '') {
                data[key] = value;
            }
        }
    });

    // Show loading
    const saveBtn = event.target;
    const originalHTML = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
    saveBtn.disabled = true;

    fetch('{{ route("customers.update-inline", $customer) }}', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message || 'Changes saved successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorMessage(data.message || 'Failed to save changes');
            saveBtn.innerHTML = originalHTML;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('An error occurred while saving');
        saveBtn.innerHTML = originalHTML;
        saveBtn.disabled = false;
    });
}

function showSuccessMessage(message) {
    const msg = document.createElement('div');
    msg.className = 'fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50';
    msg.style.backgroundColor = 'rgba(var(--theme-success-rgb), 0.95)';
    msg.style.color = '#ffffff';
    msg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + message;
    document.body.appendChild(msg);
    setTimeout(() => msg.remove(), 3000);
}

function showErrorMessage(message) {
    const msg = document.createElement('div');
    msg.className = 'fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50';
    msg.style.backgroundColor = 'rgba(var(--theme-danger-rgb), 0.95)';
    msg.style.color = '#ffffff';
    msg.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + message;
    document.body.appendChild(msg);
    setTimeout(() => msg.remove(), 5000);
}
</script>
@endpush
