@extends('layouts.app')

@section('title', $company->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">{{ $company->name }}</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Company details and information</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('companies.index') }}" id="header-back-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-arrow-left mr-1.5"></i>
                        Back
                    </a>
                    <a href="{{ route('companies.activity', $company) }}" id="header-activity-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-clock mr-1.5"></i>
                        Activity Log
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <a href="{{ route('companies.edit', $company) }}" id="header-edit-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-edit mr-1.5"></i>
                        Edit
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

        {{-- Statistics Cards met Progress styling --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow); padding: 1rem; transition: all 0.2s;"
                 onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)';"
                 onmouseout="this.style.boxShadow='var(--theme-card-shadow)';">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Total Users</p>
                        <p style="font-size: 1.25rem; font-weight: 600; color: var(--theme-text); margin-top: 0.25rem;">{{ $stats['total_users'] ?? $company->users->count() }}</p>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: #dbeafe; border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center;">
                        <svg class="w-5 h-5" style="color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow); padding: 1rem; transition: all 0.2s;"
                 onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)';"
                 onmouseout="this.style.boxShadow='var(--theme-card-shadow)';">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Total Customers</p>
                        <p style="font-size: 1.25rem; font-weight: 600; color: var(--theme-text); margin-top: 0.25rem;">{{ $stats['total_customers'] ?? $company->customers->count() }}</p>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: #dcfce7; border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center;">
                        <svg class="w-5 h-5" style="color: #22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow); padding: 1rem; transition: all 0.2s;"
                 onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)';"
                 onmouseout="this.style.boxShadow='var(--theme-card-shadow)';">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Active Projects</p>
                        <p style="font-size: 1.25rem; font-weight: 600; color: var(--theme-text); margin-top: 0.25rem;">{{ $company->projects()->where('status', 'active')->count() }}</p>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: #fff7ed; border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center;">
                        <svg class="w-5 h-5" style="color: var(--theme-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow); padding: 1rem; transition: all 0.2s;"
                 onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)';"
                 onmouseout="this.style.boxShadow='var(--theme-card-shadow)';">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Hourly Rate</p>
                        <p style="font-size: 1.25rem; font-weight: 600; color: var(--theme-text); margin-top: 0.25rem;">€{{ number_format($company->default_hourly_rate ?? 0, 0) }}</p>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; background: #f3e8ff; border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center;">
                        <svg class="w-5 h-5" style="color: #a855f7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Left Column --}}
            <div class="space-y-6">
                {{-- Basic Information --}}
                <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background: rgba(var(--theme-primary-rgb), 0.1); border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <i class="fas fa-building" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);"></i>
                        </div>
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Basic Information</h3>
                    </div>
                    <div class="p-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Company Name</dt>
                                <dd style="font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-text); text-align: right; flex: 1;">{{ $company->name }}</dd>
                            </div>

                            @if($company->vat_number)
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">VAT Number</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1;">{{ $company->vat_number }}</dd>
                            </div>
                            @endif

                            @if($company->registration_number)
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">CoC Number</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text); text-align: right; flex: 1;">{{ $company->registration_number }}</dd>
                            </div>
                            @endif

                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Status</dt>
                                <dd style="text-align: right; flex: 1;">
                                    @if($company->status === 'active')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                          style="background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                                        <i class="fas fa-check-circle mr-1"></i>Active
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                          style="background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);">
                                        <i class="fas fa-times-circle mr-1"></i>Inactive
                                    </span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Contact Information --}}
                <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background: rgba(var(--theme-accent-rgb), 0.1); border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <i class="fas fa-address-card" style="color: var(--theme-accent); font-size: calc(var(--theme-font-size) - 1px);"></i>
                        </div>
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Contact Information</h3>
                    </div>
                    <div class="p-4">
                        <dl class="space-y-3">
                            @if($company->email)
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Email</dt>
                                <dd style="font-size: var(--theme-font-size); text-align: right; flex: 1;">
                                    <a href="mailto:{{ $company->email }}" style="color: var(--theme-primary); text-decoration: none;" class="hover:underline">
                                        <i class="fas fa-envelope mr-1"></i>{{ $company->email }}
                                    </a>
                                </dd>
                            </div>
                            @else
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Email</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text-muted); text-align: right; flex: 1; font-style: italic;">Not set</dd>
                            </div>
                            @endif

                            @if($company->phone)
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Phone</dt>
                                <dd style="font-size: var(--theme-font-size); text-align: right; flex: 1;">
                                    <a href="tel:{{ $company->phone }}" style="color: var(--theme-primary); text-decoration: none;" class="hover:underline">
                                        <i class="fas fa-phone mr-1"></i>{{ $company->phone }}
                                    </a>
                                </dd>
                            </div>
                            @else
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Phone</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text-muted); text-align: right; flex: 1; font-style: italic;">Not set</dd>
                            </div>
                            @endif

                            @if($company->website)
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Website</dt>
                                <dd style="font-size: var(--theme-font-size); text-align: right; flex: 1;">
                                    <a href="{{ $company->website }}" target="_blank" style="color: var(--theme-primary); text-decoration: none;" class="hover:underline">
                                        <i class="fas fa-globe mr-1"></i>{{ str_replace(['http://', 'https://'], '', $company->website) }}
                                    </a>
                                </dd>
                            </div>
                            @else
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Website</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text-muted); text-align: right; flex: 1; font-style: italic;">Not set</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Financial Settings --}}
                <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background: rgba(var(--theme-success-rgb), 0.1); border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <i class="fas fa-euro-sign" style="color: var(--theme-success); font-size: calc(var(--theme-font-size) - 1px);"></i>
                        </div>
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Financial Settings</h3>
                    </div>
                    <div class="p-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); min-width: 120px;">Hourly Rate</dt>
                                <dd style="font-size: var(--theme-font-size); font-weight: 600; color: var(--theme-success); text-align: right; flex: 1;">
                                    €{{ number_format($company->default_hourly_rate ?? 0, 2) }}/hour
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Address Information --}}
                @if($company->street || $company->postal_code || $company->city)
                <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background: rgba(var(--theme-warning-rgb), 0.1); border-radius: var(--theme-border-radius); display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <i class="fas fa-map-marker-alt" style="color: var(--theme-warning); font-size: calc(var(--theme-font-size) - 1px);"></i>
                        </div>
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Address</h3>
                    </div>
                    <div class="p-4">
                        <div style="font-size: var(--theme-font-size); color: var(--theme-text); line-height: 1.6;">
                            @if($company->street)
                                <div class="flex items-start mb-2">
                                    <i class="fas fa-road mr-2 mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);"></i>
                                    <span>
                                        {{ $company->street }}
                                        @if($company->house_number){{ ' ' . $company->house_number }}@endif
                                        @if($company->addition){{ '-' . $company->addition }}@endif
                                    </span>
                                </div>
                            @endif
                            @if($company->postal_code || $company->city)
                                <div class="flex items-start mb-2">
                                    <i class="fas fa-city mr-2 mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);"></i>
                                    <span>{{ $company->postal_code }}{{ $company->city ? ' ' . $company->city : '' }}</span>
                                </div>
                            @endif
                            @if($company->country && $company->country !== 'Netherlands')
                                <div class="flex items-start">
                                    <i class="fas fa-flag mr-2 mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);"></i>
                                    <span>{{ $company->country }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Company Users Section --}}
                <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Company Users</h3>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        <a href="{{ route('users.create') }}?company_id={{ $company->id }}" 
                           style="display: inline-flex; align-items: center; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: var(--theme-primary); color: var(--theme-button-text-color); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                           onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.transform='translateY(-1px)';"
                           onmouseout="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'; this.style.transform='translateY(0);'">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add User
                        </a>
                        @endif
                    </div>
                    <div class="p-4">
                        @if($company->users->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead style="background: #f9fafb;">
                                    <tr>
                                        <th style="padding: 0.5rem 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Name</th>
                                        <th style="padding: 0.5rem 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Email</th>
                                        <th style="padding: 0.5rem 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Role</th>
                                        <th style="padding: 0.5rem 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                                        <th style="padding: 0.5rem 0.75rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($company->users as $user)
                                    <tr style="transition: background 0.2s;" onmouseover="this.style.background='#fff7ed';" onmouseout="this.style.background='white';">
                                        <td style="padding: 0.75rem; white-space: nowrap;">
                                            <div class="flex items-center">
                                                <div style="width: 2rem; height: 2rem; border-radius: 50%; background: var(--theme-primary); display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                                                    <span style="color: white; font-size: 0.875rem; font-weight: 600;">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                                <span style="font-size: var(--theme-font-size); font-weight: normal; color: var(--theme-text);">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem; white-space: nowrap;">
                                            <span style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->email }}</span>
                                        </td>
                                        <td style="padding: 0.75rem; white-space: nowrap;">
                                            @php
                                                $roleColors = [
                                                    'super_admin' => 'background: #f3e8ff; color: #6b21a8;',
                                                    'admin' => 'background: #dbeafe; color: #1e40af;',
                                                    'project_manager' => 'background: #e0e7ff; color: #3730a3;',
                                                    'user' => 'background: #f3f4f6; color: #374151;',
                                                    'reader' => 'background: #f3f4f6; color: #4b5563;',
                                                ];
                                                $roleStyle = $roleColors[$user->role] ?? 'background: #f3f4f6; color: #374151;';
                                            @endphp
                                            <span style="display: inline-flex; padding: 0.25rem 0.5rem; font-size: calc(var(--theme-font-size) - 2px); font-weight: normal; border-radius: 9999px; {{ $roleStyle }}">
                                                {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem; white-space: nowrap;">
                                            @if($user->is_active)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full status-badge active-badge"
                                                  style="background-color: var(--theme-primary); color: white;">Active</span>
                                            @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full status-badge inactive-badge"
                                                  style="background-color: var(--theme-danger); color: white;">Inactive</span>
                                            @endif
                                        </td>
                                        <td style="padding: 0.75rem; white-space: nowrap; text-align: right;">
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']) && $user->id !== Auth::id())
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        style="color: #9ca3af; padding: 0.25rem; border-radius: var(--theme-border-radius); transition: all 0.2s; background: transparent; border: none; cursor: pointer;"
                                                        onmouseover="this.style.color='var(--theme-danger)'; this.style.background='#fee2e2';"
                                                        onmouseout="this.style.color='#9ca3af'; this.style.background='transparent';"
                                                        onclick="return confirm('Are you sure you want to remove this user from the company?')"
                                                        title="Remove User">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12" style="color: #d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p style="margin-top: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">No users assigned to this company</p>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                            <a href="{{ route('users.create') }}?company_id={{ $company->id }}" style="margin-top: 0.75rem; display: inline-flex; align-items: center; font-size: var(--theme-font-size); color: var(--theme-accent); font-weight: normal; text-decoration: none;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
                                Add first user →
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Quick Actions --}}
                <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Quick Actions</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        <a href="{{ route('companies.edit', $company) }}" 
                           style="width: 100%; display: inline-flex; justify-content: center; align-items: center; padding: 0.5rem 1rem; background: var(--theme-primary); color: white; font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-border-radius); transition: all 0.2s; text-decoration: none;"
                           onmouseover="this.style.filter='brightness(0.9)';"
                           onmouseout="this.style.filter='brightness(1)';">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Company
                        </a>
                        
                        @if($company->users->count() == 0 && $company->customers->count() == 0 && $company->projects->count() == 0)
                        <button onclick="deleteCompany({{ $company->id }})" 
                                style="width: 100%; display: inline-flex; justify-content: center; align-items: center; padding: 0.5rem 1rem; border: 1px solid #fca5a5; color: var(--theme-danger); background: white; font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-border-radius); transition: all 0.2s; cursor: pointer;"
                                onmouseover="this.style.background='#fef2f2';"
                                onmouseout="this.style.background='white';">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Company
                        </button>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- Company Info --}}
                <div style="background: white; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(203, 213, 225, 0.3);">
                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">Company Info</h3>
                    </div>
                    <div class="p-4">
                        <dl class="space-y-3">
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Created</dt>
                                <dd style="margin-top: 0.125rem; font-size: var(--theme-font-size); color: var(--theme-text);">{{ \App\Helpers\DateHelper::formatDate($company->created_at) }}</dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Last Updated</dt>
                                <dd style="margin-top: 0.125rem; font-size: var(--theme-font-size); color: var(--theme-text);">{{ \App\Helpers\DateHelper::formatDate($company->updated_at) }}</dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; color: var(--theme-text-muted); text-transform: uppercase;">Total Projects</dt>
                                <dd style="margin-top: 0.125rem; font-size: var(--theme-font-size); color: var(--theme-text);">{{ $company->projects()->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 w-96">
        <div style="background: white; border-radius: var(--theme-border-radius); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <div class="p-6">
                <div style="margin: 0 auto; width: 3rem; height: 3rem; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg class="h-6 w-6" style="color: var(--theme-danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-top: 1rem; text-align: center;">Delete Company</h3>
                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.5rem; text-align: center;">
                    Are you sure you want to delete this company? This action cannot be undone.
                </p>
                <div class="mt-6 space-y-2">
                    <button id="confirmDelete" style="width: 100%; padding: 0.5rem 1rem; background: var(--theme-danger); color: white; font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-border-radius); border: none; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.filter='brightness(1.1)';"
                            onmouseout="this.style.filter='brightness(1)';">
                        Delete
                    </button>
                    <button onclick="closeDeleteModal()" style="width: 100%; padding: 0.5rem 1rem; background: white; border: 1px solid #d1d5db; color: var(--theme-text); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-border-radius); cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.background='#f9fafb';"
                            onmouseout="this.style.background='white';">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();

    // Header buttons
    const backBtn = document.getElementById('header-back-btn');
    const activityBtn = document.getElementById('header-activity-btn');
    const editBtn = document.getElementById('header-edit-btn');

    if (backBtn) {
        backBtn.style.backgroundColor = 'white';
        backBtn.style.color = primaryColor;
        backBtn.style.border = `1px solid ${primaryColor}`;
        backBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    if (activityBtn) {
        activityBtn.style.backgroundColor = primaryColor + '15';
        activityBtn.style.color = primaryColor;
        activityBtn.style.border = `1px solid ${primaryColor}30`;
        activityBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    if (editBtn) {
        editBtn.style.backgroundColor = primaryColor;
        editBtn.style.color = 'white';
        editBtn.style.border = 'none';
        editBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

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

    // Primary links in text
    const primaryLinks = document.querySelectorAll('.primary-link');
    primaryLinks.forEach(link => {
        link.style.color = primaryColor;
        link.addEventListener('mouseenter', function() {
            this.style.opacity = '0.8';
            this.style.textDecoration = 'underline';
        });
        link.addEventListener('mouseleave', function() {
            this.style.opacity = '1';
            this.style.textDecoration = 'none';
        });
    });
}

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

// Initialize all functionality when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush