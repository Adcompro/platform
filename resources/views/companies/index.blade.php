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

        {{-- Multi-Company Mode Info --}}
        @if(!$isMultiCompanyMode)
            <div class="mb-6 bg-blue-50 border border-blue-200 px-4 py-3 rounded-lg" style="color: var(--theme-primary);">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                    <div>
                        <p class="font-semibold">Single Company Mode</p>
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
            <div class="bg-white border rounded-lg p-4 transition-all" style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
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

            <div class="bg-white border rounded-lg p-4 transition-all" style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
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

            <div class="bg-white border rounded-lg p-4 transition-all" style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
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

            <div class="bg-white border rounded-lg p-4 transition-all" style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold uppercase" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Total Customers</p>
                        <p class="text-xl font-semibold mt-1" style="color: var(--theme-text);">
                            {{ $companies->sum(function($company) { return $company->customers->count(); }) }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search en Filter Bar --}}
        <div class="bg-white border rounded-lg p-4 mb-6" style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text"
                           id="search"
                           placeholder="Search companies..."
                           class="w-full px-3 py-2 border rounded-lg focus:ring-1 transition-all"
                           style="font-size: var(--theme-font-size); border-color: #e2e8f0; color: var(--theme-text);">
                </div>
                <select id="status-filter" class="py-2 border rounded-lg focus:ring-1 transition-all"
                        style="padding-left: 0.75rem; padding-right: 2.5rem; font-size: var(--theme-font-size); border-color: #e2e8f0; color: var(--theme-text);">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        {{-- Companies Table --}}
        <div class="bg-white border rounded-lg overflow-hidden" style="border-color: rgba(203, 213, 225, 0.6); box-shadow: var(--theme-card-shadow);">
            @if($companies->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full" id="companies-table">
                    <thead id="table-header">
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
                                Created
                            </th>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                            <th class="text-center font-semibold uppercase tracking-wider"
                                style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px); padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                Actions
                            </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @foreach($companies as $company)
                        <tr class="transition-colors company-row table-row"
                            data-name="{{ strtolower($company->name) }}"
                            data-email="{{ strtolower($company->email ?? '') }}"
                            data-status="{{ $company->status }}">
                            <td style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3 company-avatar"
                                         style="background-color: var(--theme-primary); color: white;">
                                        <span class="font-semibold" style="font-size: calc(var(--theme-font-size) - 1px);">
                                            {{ strtoupper(substr($company->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-semibold" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $company->name }}</p>
                                        @if($company->email)
                                        <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">{{ $company->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                @if($company->status === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full status-badge active-badge"
                                      style="background-color: var(--theme-primary); color: white;">Active</span>
                                @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full status-badge inactive-badge"
                                      style="background-color: var(--theme-danger); color: white;">Inactive</span>
                                @endif
                            </td>
                            <td style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text); font-size: var(--theme-font-size);">
                                €{{ number_format($company->default_hourly_rate ?? 0, 2) }}
                            </td>
                            <td style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text); font-size: var(--theme-font-size);">
                                {{ $company->users->count() }}
                            </td>
                            <td style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text); font-size: var(--theme-font-size);">
                                {{ $company->customers->count() }}
                            </td>
                            <td style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text); font-size: var(--theme-font-size);">
                                {{ $company->created_at->format('M d, Y') }}
                            </td>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                            <td style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" onclick="openViewCompanyModal({{ $company->id }})"
                                            class="p-1 hover:bg-gray-100 rounded transition-colors action-link"
                                            style="background: none; border: none; cursor: pointer;"
                                            title="View Company">
                                        <svg class="w-4 h-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="openEditCompanyModal({{ $company->id }})"
                                            class="p-1 hover:bg-gray-100 rounded transition-colors action-link"
                                            style="background: none; border: none; cursor: pointer;"
                                            title="Edit Company">
                                        <svg class="w-4 h-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @if(Auth::user()->role === 'super_admin')
                                    <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1 hover:bg-red-50 rounded transition-colors"
                                                title="Delete Company"
                                                onclick="return confirm('Are you sure you want to delete this company? This action cannot be undone.')">
                                            <svg class="w-4 h-4" style="color: var(--theme-danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No companies</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new company.</p>
                @if(Auth::user()->role === 'super_admin')
                <div class="mt-6">
                    <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>
                        New Company
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

{{-- View Company Modal --}}
<div id="view-company-modal" class="fixed inset-0 z-50 hidden" style="background-color: rgba(0, 0, 0, 0.15);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white border border-slate-200/40 rounded-xl overflow-hidden shadow-xl" style="max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1rem 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">View Company</h2>
                    <button type="button" onclick="closeViewCompanyModal()" style="color: var(--theme-text-muted); background: none; border: none; font-size: 1.5rem; cursor: pointer; padding: 0.25rem;">
                        ×
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div style="padding: 1.5rem;">
                <div id="view-company-modal-loading" class="text-center py-6">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-3" style="color: var(--theme-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Loading company details...</p>
                </div>

                <div id="view-company-modal-content" class="hidden">
                    {{-- Content will be loaded here via AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Company Modal --}}
<div id="edit-company-modal" class="fixed inset-0 z-50 hidden" style="background-color: rgba(0, 0, 0, 0.15);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white border border-slate-200/40 rounded-xl overflow-hidden shadow-xl" style="max-width: 900px; width: 100%; max-height: 90vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1rem 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">Edit Company</h2>
                    <button type="button" onclick="closeEditCompanyModal()" style="color: var(--theme-text-muted); background: none; border: none; font-size: 1.5rem; cursor: pointer; padding: 0.25rem;">
                        ×
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div style="padding: 1.5rem;">
                <div id="edit-company-modal-loading" class="text-center py-6">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-3" style="color: var(--theme-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Loading company form...</p>
                </div>

                <div id="edit-company-modal-content" class="hidden">
                    {{-- Content will be loaded here via AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Apply comprehensive theme styling
    function styleThemeElements() {
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
        const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();
        const tableHeaderStyle = getComputedStyle(document.documentElement).getPropertyValue('--theme-table-header-style').trim();
        const tableHoverEffect = getComputedStyle(document.documentElement).getPropertyValue('--theme-table-hover-effect').trim();
        const tableStriped = getComputedStyle(document.documentElement).getPropertyValue('--theme-table-striped').trim();

        // Create button (primary action)
        const createBtn = document.getElementById('header-create-btn');
        if (createBtn) {
            createBtn.style.backgroundColor = primaryColor;
            createBtn.style.color = 'white';
            createBtn.style.border = 'none';
            createBtn.style.borderRadius = 'var(--theme-border-radius)';
        }

        // Table header styling
        const tableHeader = document.getElementById('table-header');
        if (tableHeader) {
            switch (tableHeaderStyle) {
                case 'light':
                    tableHeader.style.backgroundColor = '#f9fafb';
                    break;
                case 'dark':
                    tableHeader.style.backgroundColor = '#374151';
                    tableHeader.style.color = 'white';
                    break;
                case 'colored':
                    tableHeader.style.backgroundColor = primaryColor;
                    tableHeader.style.color = 'white';
                    break;
                case 'bold':
                    tableHeader.style.backgroundColor = '#e5e7eb';
                    break;
            }
        }

        // Table row striping and hover effects
        const tableRows = document.querySelectorAll('.table-row');
        tableRows.forEach((row, index) => {
            // Striped rows
            if (tableStriped === 'true' && index % 2 === 1) {
                row.style.backgroundColor = '#f9fafb';
            }

            // Hover effects
            row.addEventListener('mouseenter', function() {
                switch (tableHoverEffect) {
                    case 'light':
                        this.style.backgroundColor = '#f9fafb';
                        break;
                    case 'dark':
                        this.style.backgroundColor = '#f3f4f6';
                        break;
                    case 'colored':
                        this.style.backgroundColor = primaryColor + '10';
                        break;
                }
            });

            row.addEventListener('mouseleave', function() {
                if (tableStriped === 'true' && index % 2 === 1) {
                    this.style.backgroundColor = '#f9fafb';
                } else {
                    this.style.backgroundColor = 'transparent';
                }
            });
        });

        // Status badges - already styled with inline CSS variables
        const activeBadges = document.querySelectorAll('.active-badge');
        activeBadges.forEach(badge => {
            badge.style.backgroundColor = primaryColor;
            badge.style.color = 'white';
        });

        const inactiveBadges = document.querySelectorAll('.inactive-badge');
        inactiveBadges.forEach(badge => {
            badge.style.backgroundColor = dangerColor;
            badge.style.color = 'white';
        });

        // Action links - already use primary color via CSS variables
        const actionLinks = document.querySelectorAll('.action-link svg');
        actionLinks.forEach(svg => {
            svg.style.color = primaryColor;
        });

        // Primary links in text
        const primaryLinks = document.querySelectorAll('.primary-link');
        primaryLinks.forEach(link => {
            link.style.color = primaryColor;
            link.addEventListener('mouseenter', function() {
                this.style.opacity = '0.8';
            });
            link.addEventListener('mouseleave', function() {
                this.style.opacity = '1';
            });
        });
    }

    // Search and filter functionality
    function initializeFilters() {
        const searchInput = document.getElementById('search');
        const statusFilter = document.getElementById('status-filter');
        const companyRows = document.querySelectorAll('.company-row');

        function filterCompanies() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;

            companyRows.forEach(row => {
                const name = row.dataset.name;
                const email = row.dataset.email;
                const status = row.dataset.status;

                const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
                const matchesStatus = !statusValue || status === statusValue;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterCompanies);
        statusFilter.addEventListener('change', filterCompanies);
    }

    // Initialize all functionality when page loads
    document.addEventListener('DOMContentLoaded', function() {
        styleThemeElements();
        initializeFilters();
    });

    /**
     * Company Modal Functions
     */

    // Open view company modal
    function openViewCompanyModal(companyId) {
        const modal = document.getElementById('view-company-modal');
        const modalLoading = document.getElementById('view-company-modal-loading');
        const modalContent = document.getElementById('view-company-modal-content');

        // Show modal and loading state
        modal.classList.remove('hidden');
        modalLoading.classList.remove('hidden');
        modalContent.classList.add('hidden');

        // Load view content via AJAX
        fetch(`/companies/${companyId}/show-modal`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load company details');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    modalContent.innerHTML = data.html;
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');
                } else {
                    throw new Error(data.message || 'Failed to load company details');
                }
            })
            .catch(error => {
                console.error('Error loading company details:', error);
                modalContent.innerHTML = `
                    <div class="text-center py-8">
                        <p style="color: var(--theme-danger);">Failed to load company details. Please try again.</p>
                        <button type="button" onclick="closeViewCompanyModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius);">
                            Close
                        </button>
                    </div>
                `;
                modalLoading.classList.add('hidden');
                modalContent.classList.remove('hidden');
            });
    }

    // Close view company modal
    function closeViewCompanyModal() {
        const modal = document.getElementById('view-company-modal');
        modal.classList.add('hidden');
    }

    // Open edit company modal
    function openEditCompanyModal(companyId) {
        const modal = document.getElementById('edit-company-modal');
        const modalLoading = document.getElementById('edit-company-modal-loading');
        const modalContent = document.getElementById('edit-company-modal-content');

        // Show modal and loading state
        modal.classList.remove('hidden');
        modalLoading.classList.remove('hidden');
        modalContent.classList.add('hidden');

        // Load edit form via AJAX
        fetch(`/companies/${companyId}/edit-modal`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load edit form');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    modalContent.innerHTML = data.html;
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');

                    // Initialize form functionality
                    initializeCompanyModalForm(companyId);
                } else {
                    throw new Error(data.message || 'Failed to load edit form');
                }
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                modalContent.innerHTML = `
                    <div class="text-center py-8">
                        <p style="color: var(--theme-danger);">Failed to load edit form. Please try again.</p>
                        <button type="button" onclick="closeEditCompanyModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius);">
                            Close
                        </button>
                    </div>
                `;
                modalLoading.classList.add('hidden');
                modalContent.classList.remove('hidden');
            });
    }

    // Close edit company modal
    function closeEditCompanyModal() {
        const modal = document.getElementById('edit-company-modal');
        modal.classList.add('hidden');
    }

    // Initialize company modal form functionality
    function initializeCompanyModalForm(companyId) {
        // Handle form submission
        const form = document.querySelector('#edit-company-modal-content form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitCompanyModalForm(form, companyId);
            });
        }

        // Style form elements according to theme
        styleCompanyModalElements();
    }

    // Submit company modal form
    function submitCompanyModalForm(form, companyId) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
        }

        const formData = new FormData(form);

        fetch(`/companies/${companyId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and refresh page
                closeEditCompanyModal();
                location.reload();
            } else {
                // Show error message
                alert('Error updating company: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            alert('Network error updating company');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    // Style company modal elements according to theme
    function styleCompanyModalElements() {
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

        // Style submit buttons
        const submitButtons = document.querySelectorAll('#edit-company-modal-content button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.style.backgroundColor = primaryColor;
            btn.style.color = 'white';
            btn.style.border = 'none';
            btn.style.borderRadius = 'var(--theme-border-radius)';
        });

        // Style radio buttons and checkboxes
        const inputElements = document.querySelectorAll('#edit-company-modal-content input[type="radio"], #edit-company-modal-content input[type="checkbox"]');
        inputElements.forEach(input => {
            input.style.accentColor = primaryColor;
        });
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        const viewModal = document.getElementById('view-company-modal');
        const editModal = document.getElementById('edit-company-modal');

        if (e.target === viewModal) {
            closeViewCompanyModal();
        }
        if (e.target === editModal) {
            closeEditCompanyModal();
        }
    });
</script>
@endpush
@endsection