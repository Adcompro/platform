@extends('layouts.app')

@section('title', 'Companies')

@push('styles')
<style>
    .header-btn {
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Companies</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Manage your business entities and organizations</p>
                </div>
                <div class="flex space-x-2">
                    @if(Auth::user()->role === 'super_admin')
                    <a href="{{ route('companies.create') }}" id="header-create-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-plus mr-1.5"></i>
                        New Company
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
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Companies Content --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">{{ $isMultiCompanyMode ? 'Companies' : 'Company' }}</h3>
                    <p style="color: var(--theme-text-muted); margin-top: var(--theme-header-spacing);">{{ $pageDescription }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    @if($canCreateNewCompany)
                        <a href="{{ route('companies.create') }}" 
                           class="inline-flex items-center text-white font-normal transition-all" 
                           style="background: var(--theme-primary); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            {{ $isMultiCompanyMode ? 'Add Company' : 'Create Company' }}
                        </a>
                    @else
                        <span class="inline-flex items-center bg-gray-100 font-normal cursor-not-allowed"
                              style="color: var(--theme-text-muted); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Single Company Mode
                        </span>
                    @endif
                    <button onclick="openHelpModal()" 
                            class="inline-flex items-center bg-white border font-normal hover:bg-gray-50 transition-all"
                            style="border-color: #e2e8f0; color: var(--theme-text); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);"
                            title="Help Guide">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        {{-- Multi-Company Mode Info --}}
        @if(!$isMultiCompanyMode)
            <div class="mb-6 bg-blue-50 border border-blue-200 px-4 py-3 rounded-lg" style="color: var(--theme-primary);">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                    <div>
                        <p class="font-semibold" style="">Single Company Mode</p>
                        <p class="mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                            Only one company can be created. To enable multiple companies, activate multi-company mode in the 
                            <a href="{{ route('plugins.edit', ['plugin' => 'companies']) }}" class="underline font-semibold" style="color: var(--theme-primary);">Companies plugin settings</a>.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border rounded-lg p-4 transition-all" style="box-shadow: var(--theme-card-shadow);" 
                 style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold uppercase" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Total Companies</p>
                        <p class="text-xl font-semibold mt-1" style="color: var(--theme-text);">{{ $companies->count() }}</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--theme-text-muted);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border rounded-lg p-4 transition-all" style="box-shadow: var(--theme-card-shadow);" 
                 style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold uppercase" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Active</p>
                        <p class="text-xl font-semibold mt-1" style="color: var(--theme-accent);">
                            {{ $companies->where('status', 'active')->count() }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

<div class="bg-white border rounded-lg p-4 transition-all" style="box-shadow: var(--theme-card-shadow);" 
                 style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold uppercase" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Total Users</p>
                        <p class="text-xl font-semibold mt-1" style="color: var(--theme-text);">
                            {{ $companies->sum(function($company) { return $company->users->count(); }) }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search en Filter Bar --}}
        <div class="bg-white border rounded-lg" style="box-shadow: var(--theme-card-shadow);" p-4 mb-6" 
             style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" 
                           id="search" 
                           placeholder="Search companies..." 
                           class="w-full px-3 py-2 border rounded-lg focus:ring-1 transition-all"
                           style="font-size: var(--theme-font-size); border-color: #e2e8f0; color: var(--theme-text); focus:border-color: var(--theme-primary); focus:ring-color: var(--theme-primary);">
                </div>
                <select id="status-filter" class="py-2 border rounded-lg focus:ring-1 transition-all"
                        style="padding-left: 0.75rem; padding-right: 2.5rem; font-size: var(--theme-font-size); border-color: #e2e8f0; color: var(--theme-text); focus:border-color: var(--theme-primary); focus:ring-color: var(--theme-primary);">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        {{-- Companies Table --}}
        <div class="bg-white border rounded-lg" style="box-shadow: var(--theme-card-shadow);" overflow-hidden" 
             style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
            @if($companies->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="text-left font-semibold uppercase tracking-wider" 
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Company
                            </th>
                            <th class="text-left font-semibold uppercase tracking-wider" 
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Status
                            </th>
                            <th class="text-left font-semibold uppercase tracking-wider" 
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Default Rate
                            </th>
                            <th class="text-left font-semibold uppercase tracking-wider" 
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Users
                            </th>
                            <th class="text-left font-semibold uppercase tracking-wider" 
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Customers
                            </th>
                            <th class="text-left font-semibold uppercase tracking-wider" 
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Revenue
                            </th>
                            <th class="text-right font-semibold uppercase tracking-wider" 
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($companies as $company)
                        <tr class="hover:bg-gray-50 transition-colors company-row" 
                            data-status="{{ $company->status ?? ($company->is_active ? 'active' : 'inactive') }}">
                            <td class="whitespace-nowrap" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center" style="background: var(--theme-primary);">
                                            <span class="text-sm font-semibold text-white">
                                                {{ strtoupper(substr($company->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <div class="font-normal company-name" style="color: var(--theme-text); ">
                                                <a href="{{ route('companies.show', $company) }}" class="hover:opacity-80 transition-colors" style="color: var(--theme-primary);">
                                                    {{ $company->name }}
                                                </a>
                                            </div>
                                        </div>
                                        @if($company->vat_number)
                                        <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">VAT: {{ $company->vat_number }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                @php
                                    $status = $company->status ?? ($company->is_active ? 'active' : 'inactive');
                                    $statusClass = $status === 'active' 
                                        ? 'bg-green-100 text-green-700' 
                                        : 'bg-red-100 text-red-700';
                                @endphp
                                <span class="font-normal {{ $statusClass }}" 
                                      style="font-size: calc(var(--theme-font-size) - 2px); padding: 0.25rem 0.5rem; border-radius: 9999px;">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap" style="color: var(--theme-text);  padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                €{{ number_format($company->default_hourly_rate, 0) }}/hour
                            </td>
                            <td class="whitespace-nowrap" style="color: var(--theme-text);  padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                {{ $company->users->count() }}
                            </td>
                            <td class="whitespace-nowrap" style="color: var(--theme-text);  padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                {{ $company->customers->count() }} customers
                            </td>
                            <td class="whitespace-nowrap font-normal" style="color: var(--theme-text);  padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                €{{ number_format($company->monthlyRevenue ?? 0, 0) }}/month
                            </td>
                            <td class="whitespace-nowrap text-right font-normal" style=" padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                <div class="flex items-center justify-end space-x-1">
                                    <a href="{{ route('companies.show', $company) }}" 
                                       class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('companies.edit', $company) }}" 
                                       class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button onclick="deleteCompany({{ $company->id }})" 
                                            class="text-gray-400 hover:text-red-600 p-1 hover:bg-red-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-building text-4xl text-gray-300 mb-4"></i>
                <h3 class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">No companies</h3>
                <p class="mt-1" style="color: var(--theme-text-muted); ">Get started by creating your first company.</p>
                <div class="mt-6">
                    <a href="{{ route('companies.create') }}" 
                       class="inline-flex items-center text-white font-normal transition-all" 
                       style="background: var(--theme-primary); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Company
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 w-96">
        <div class="bg-white rounded-lg" style="box-shadow: var(--theme-card-shadow);"">
            <div class="p-6">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--theme-danger);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold mt-4 text-center" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Delete Company</h3>
                <p class="mt-2 text-center" style="color: var(--theme-text-muted); ">
                    Are you sure you want to delete this company? This action cannot be undone and will affect all related projects and users.
                </p>
                <div class="mt-6 space-y-2">
                    <button id="confirmDelete" class="w-full text-white font-normal transition-all" 
                            style="background: var(--theme-danger); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                        Delete Company
                    </button>
                    <button onclick="closeDeleteModal()" class="w-full bg-white border font-normal hover:bg-gray-50 transition-all" 
                            style="border-color: #e2e8f0; color: var(--theme-text); font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius);">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Help Modal --}}
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden" style="box-shadow: var(--theme-card-shadow);">
        <div class="px-6 py-4 border-b flex justify-between items-center" style="border-color: rgba(203, 213, 225, 0.5);">
            <h3 class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Company Management Guide</h3>
            <button onclick="closeHelpModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div class="space-y-6">
                {{-- Introduction --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">Overview</h4>
                    <p style="color: var(--theme-text); ">
                        The Company Management system is the foundation of your multi-tenant organization structure. 
                        Each company (BV) can have its own users, customers, projects, and financial settings, 
                        allowing complete separation of business entities while maintaining central oversight.
                    </p>
                </div>

                {{-- Key Features --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">Key Features</h4>
                    <ul class="space-y-2" style="color: var(--theme-text); ">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><strong>Multi-Tenant Architecture:</strong> Complete separation between companies with data isolation</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><strong>Financial Settings:</strong> Individual hourly rates, VAT numbers, and bank details per company</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><strong>User Management:</strong> Assign users to specific companies with role-based permissions</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><strong>Cross-Company Projects:</strong> Enable collaboration between companies on shared projects</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--theme-primary);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span style="color: var(--theme-primary);"><strong>⭐ Complete Audit Trail (PREMIUM FEATURE):</strong> Every change is logged with user, timestamp, and old/new values - allowing full traceability and potential restoration</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><strong>Status Management:</strong> Activate or deactivate companies as needed</span>
                        </li>
                    </ul>
                </div>

                {{-- Activity Logging & Audit Trail --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">
                        <span style="color: var(--theme-primary);">⭐</span> Activity Logging & Audit Trail (Premium Feature)
                    </h4>
                    <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border rounded-lg p-4" style="border-color: var(--theme-primary);">
                        <p class="mb-3 font-semibold" style="color: var(--theme-text); ">
                            Complete transparency and accountability with our comprehensive audit trail system:
                        </p>
                        <ul class="space-y-2" style="color: var(--theme-text); ">
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span><strong>Every Change Tracked:</strong> All modifications are automatically logged with timestamp, user, and IP address</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span><strong>Before & After Values:</strong> See exactly what was changed - old values (red, strikethrough) and new values (green)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span><strong>Visual Timeline:</strong> Beautiful chronological timeline view in company details, newest changes at the top</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span><strong>Restoration Capability:</strong> Since all old values are preserved, data can be restored to any previous state</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span><strong>Compliance Ready:</strong> Meet audit requirements with complete change history and user accountability</span>
                            </li>
                        </ul>
                        <div class="mt-3 p-2 bg-white/50 rounded border" style="border-color: var(--theme-primary);">
                            <p style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 1px);">
                                <strong>Business Value:</strong> Protect against unauthorized changes, maintain data integrity, and provide complete transparency to stakeholders. Perfect for ISO compliance and financial audit requirements.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- How to Create a Company --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">Creating a Company</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <ol class="list-decimal list-inside space-y-1" style="color: var(--theme-primary); ">
                            <li>Click the "Add Company" button at the top of this page</li>
                            <li>Enter the company name (required)</li>
                            <li>Add VAT number for tax purposes</li>
                            <li>Enter contact details (email, phone, address)</li>
                            <li>Set the default hourly rate for billing</li>
                            <li>Company will be active by default</li>
                            <li>Click "Create Company" to save</li>
                        </ol>
                    </div>
                </div>

                {{-- Understanding the Interface --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">Understanding the Interface</h4>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <dl class="space-y-2" style="">
                            <div class="flex items-start">
                                <dt class="font-semibold w-32" style="color: var(--theme-text);">Statistics Cards:</dt>
                                <dd style="color: var(--theme-text);">Quick overview of total companies, active status, users, and customers</dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="font-semibold w-32" style="color: var(--theme-text);">Search Bar:</dt>
                                <dd style="color: var(--theme-text);">Filter companies by name, email, or VAT number</dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="font-semibold w-32" style="color: var(--theme-text);">Status Filter:</dt>
                                <dd style="color: var(--theme-text);">Show all, active only, or inactive companies</dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="font-semibold w-32" style="color: var(--theme-text);">Action Icons:</dt>
                                <dd style="color: var(--theme-text);">View (eye), Edit (pencil), Delete (trash) - based on permissions</dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="font-semibold w-32" style="color: var(--theme-text);">Activity Log:</dt>
                                <dd style="color: var(--theme-text);">View complete history in company details page</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Permissions --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">Role-Based Permissions</h4>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <table class="w-full" style="">
                            <thead>
                                <tr class="text-left font-semibold" style="color: var(--theme-danger);">
                                    <th class="pb-2">Role</th>
                                    <th class="pb-2">View</th>
                                    <th class="pb-2">Create</th>
                                    <th class="pb-2">Edit</th>
                                    <th class="pb-2">Delete</th>
                                </tr>
                            </thead>
                            <tbody style="color: var(--theme-danger);">
                                <tr>
                                    <td class="py-1">Super Admin</td>
                                    <td>✓ All</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                <tr>
                                    <td class="py-1">Admin</td>
                                    <td>✓ All</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                <tr>
                                    <td class="py-1">Project Manager</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                </tr>
                                <tr>
                                    <td class="py-1">User</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                </tr>
                                <tr>
                                    <td class="py-1">Reader</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="mt-2" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 1px);">
                            <strong>Note:</strong> Only Super Admin and Admin roles can manage companies.
                        </p>
                    </div>
                </div>

                {{-- Best Practices --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">Best Practices</h4>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <ul class="list-disc list-inside space-y-1" style="color: var(--theme-accent); ">
                            <li>Always set up company details before adding users</li>
                            <li>Use consistent VAT numbers for legal compliance</li>
                            <li>Set appropriate default hourly rates to avoid billing errors</li>
                            <li>Regularly review the activity log for unauthorized changes</li>
                            <li>Deactivate companies instead of deleting to preserve history</li>
                            <li>Keep bank details updated for accurate invoicing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openHelpModal() {
    document.getElementById('help-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeHelpModal() {
    document.getElementById('help-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('help-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelpModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHelpModal();
    }
});
</script>
@endpush

@push('scripts')
<script>
// Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status-filter');
    const companyRows = document.querySelectorAll('.company-row');

    function filterCompanies() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilterValue = statusFilter.value;

        companyRows.forEach(row => {
            const companyName = row.querySelector('.company-name').textContent.toLowerCase();
            const companyStatus = row.dataset.status;

            const matchesSearch = companyName.includes(searchTerm);
            const matchesStatus = !statusFilterValue || companyStatus === statusFilterValue;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterCompanies);
    statusFilter.addEventListener('change', filterCompanies);
});

// Delete Company Functionality
let companyToDelete = null;

function deleteCompany(companyId) {
    companyToDelete = companyId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    companyToDelete = null;
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (companyToDelete) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/companies/${companyToDelete}`;
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        const tokenField = document.createElement('input');
        tokenField.type = 'hidden';
        tokenField.name = '_token';
        tokenField.value = '{{ csrf_token() }}';
        
        form.appendChild(methodField);
        form.appendChild(tokenField);
        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endpush