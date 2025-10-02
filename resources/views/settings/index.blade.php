@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto" style="padding: 1rem 2rem;">
            <div class="flex justify-between items-center" style="padding: 1.5rem 0;">
                <div>
                    <h1 class="text-2xl font-bold" style="color: var(--theme-text);">System Settings</h1>
                    <p class="text-sm" style="color: var(--theme-text-muted);">Configure application settings and preferences</p>
                </div>
                <div>
                    <button onclick="openSettingsHelp()" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Help Guide
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto" style="padding: 2rem;">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-md" style="background-color: rgba(var(--theme-success-rgb), 0.1); border: 1px solid rgba(var(--theme-success-rgb), 0.3); color: var(--theme-success);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 px-4 py-3 rounded-md" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border: 1px solid rgba(var(--theme-danger-rgb), 0.3); color: var(--theme-danger);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Settings Form --}}
            <div class="md:col-span-2">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- AI Settings Notice --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                        <div class="border-b border-gray-200 flex justify-between items-center" style="padding: 1rem 1.5rem;">
                            <h2 class="text-lg font-medium" style="color: var(--theme-text);">AI Configuration</h2>
                        </div>
                        <div style="padding: 1.5rem;">
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm text-purple-800">
                                        AI settings have been moved to a dedicated configuration page for better management.
                                    </p>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('ai-settings.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Go to AI Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Calendar Sync Settings --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                        <div class="border-b border-gray-200" style="padding: 1rem 1.5rem;">
                            <h2 class="text-lg font-medium" style="color: var(--theme-text);">Calendar Synchronization</h2>
                        </div>
                        <div class="space-y-6" style="padding: 1.5rem;">
                            {{-- Auto Sync Enable/Disable --}}
                            <div>
                                <label for="calendar_auto_sync" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Automatic Synchronization
                                </label>
                                <select id="calendar_auto_sync" name="calendar_auto_sync" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="true" {{ ($settings['calendar_auto_sync'] ?? 'true') == 'true' ? 'selected' : '' }}>
                                        Enabled - Automatically sync calendar events
                                    </option>
                                    <option value="false" {{ ($settings['calendar_auto_sync'] ?? 'true') == 'false' ? 'selected' : '' }}>
                                        Disabled - Manual sync only
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Enable automatic synchronization of calendar events from Microsoft 365
                                </p>
                            </div>

                            {{-- Sync Interval on Page Load --}}
                            <div>
                                <label for="calendar_auto_sync_interval" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Page Load Sync Interval
                                </label>
                                <select id="calendar_auto_sync_interval" name="calendar_auto_sync_interval" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="15" {{ ($settings['calendar_auto_sync_interval'] ?? '60') == '15' ? 'selected' : '' }}>
                                        Every 15 minutes
                                    </option>
                                    <option value="30" {{ ($settings['calendar_auto_sync_interval'] ?? '60') == '30' ? 'selected' : '' }}>
                                        Every 30 minutes
                                    </option>
                                    <option value="60" {{ ($settings['calendar_auto_sync_interval'] ?? '60') == '60' ? 'selected' : '' }}>
                                        Every hour (recommended)
                                    </option>
                                    <option value="120" {{ ($settings['calendar_auto_sync_interval'] ?? '60') == '120' ? 'selected' : '' }}>
                                        Every 2 hours
                                    </option>
                                    <option value="240" {{ ($settings['calendar_auto_sync_interval'] ?? '60') == '240' ? 'selected' : '' }}>
                                        Every 4 hours
                                    </option>
                                    <option value="480" {{ ($settings['calendar_auto_sync_interval'] ?? '60') == '480' ? 'selected' : '' }}>
                                        Every 8 hours
                                    </option>
                                    <option value="1440" {{ ($settings['calendar_auto_sync_interval'] ?? '60') == '1440' ? 'selected' : '' }}>
                                        Once per day
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Sync calendar when visiting the calendar page if this interval has passed
                                </p>
                            </div>

                            {{-- JavaScript Interval Sync --}}
                            <div>
                                <label for="calendar_js_sync_interval" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Background Sync Interval (While on Calendar Page)
                                </label>
                                <select id="calendar_js_sync_interval" name="calendar_js_sync_interval" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="0" {{ ($settings['calendar_js_sync_interval'] ?? '15') == '0' ? 'selected' : '' }}>
                                        Disabled - No background sync
                                    </option>
                                    <option value="5" {{ ($settings['calendar_js_sync_interval'] ?? '15') == '5' ? 'selected' : '' }}>
                                        Every 5 minutes
                                    </option>
                                    <option value="10" {{ ($settings['calendar_js_sync_interval'] ?? '15') == '10' ? 'selected' : '' }}>
                                        Every 10 minutes
                                    </option>
                                    <option value="15" {{ ($settings['calendar_js_sync_interval'] ?? '15') == '15' ? 'selected' : '' }}>
                                        Every 15 minutes (recommended)
                                    </option>
                                    <option value="30" {{ ($settings['calendar_js_sync_interval'] ?? '15') == '30' ? 'selected' : '' }}>
                                        Every 30 minutes
                                    </option>
                                    <option value="60" {{ ($settings['calendar_js_sync_interval'] ?? '15') == '60' ? 'selected' : '' }}>
                                        Every hour
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Automatically sync in the background while users are on the calendar page
                                </p>
                            </div>

                            {{-- Cron Job Interval --}}
                            <div>
                                <label for="calendar_cron_sync_interval" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Server Cron Job Interval
                                </label>
                                <select id="calendar_cron_sync_interval" name="calendar_cron_sync_interval" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="15" {{ ($settings['calendar_cron_sync_interval'] ?? '60') == '15' ? 'selected' : '' }}>
                                        Every 15 minutes
                                    </option>
                                    <option value="30" {{ ($settings['calendar_cron_sync_interval'] ?? '60') == '30' ? 'selected' : '' }}>
                                        Every 30 minutes
                                    </option>
                                    <option value="60" {{ ($settings['calendar_cron_sync_interval'] ?? '60') == '60' ? 'selected' : '' }}>
                                        Every hour (recommended)
                                    </option>
                                    <option value="120" {{ ($settings['calendar_cron_sync_interval'] ?? '60') == '120' ? 'selected' : '' }}>
                                        Every 2 hours
                                    </option>
                                    <option value="240" {{ ($settings['calendar_cron_sync_interval'] ?? '60') == '240' ? 'selected' : '' }}>
                                        Every 4 hours
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Server-side synchronization for all users (requires cron job setup)
                                </p>
                            </div>

                            {{-- Sync Range --}}
                            <div>
                                <label for="calendar_sync_range" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Event Sync Range
                                </label>
                                <select id="calendar_sync_range" name="calendar_sync_range" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="30" {{ ($settings['calendar_sync_range'] ?? '90') == '30' ? 'selected' : '' }}>
                                        Next 30 days
                                    </option>
                                    <option value="60" {{ ($settings['calendar_sync_range'] ?? '90') == '60' ? 'selected' : '' }}>
                                        Next 60 days
                                    </option>
                                    <option value="90" {{ ($settings['calendar_sync_range'] ?? '90') == '90' ? 'selected' : '' }}>
                                        Next 90 days (recommended)
                                    </option>
                                    <option value="180" {{ ($settings['calendar_sync_range'] ?? '90') == '180' ? 'selected' : '' }}>
                                        Next 180 days
                                    </option>
                                    <option value="365" {{ ($settings['calendar_sync_range'] ?? '90') == '365' ? 'selected' : '' }}>
                                        Next year
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    How far in the future to sync calendar events
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Microsoft 365 Integration Settings --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                        <div class="border-b border-gray-200" style="padding: 1rem 1.5rem;">
                            <h2 class="text-lg font-medium" style="color: var(--theme-text);">Microsoft 365 Integration</h2>
                        </div>
                        <div class="space-y-6" style="padding: 1.5rem;">
                            {{-- Azure AD Client ID --}}
                            <div>
                                <label for="msgraph_client_id" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Azure AD Application (Client) ID
                                </label>
                                <input type="text" 
                                       id="msgraph_client_id" 
                                       name="msgraph_client_id" 
                                       value="{{ $settings['msgraph_client_id'] ?? '' }}"
                                       class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none sm:text-sm"
                                       style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';"
                                       placeholder="e.g., 152e1745-747b-4f86-81bd-bdc8d8e253b5">
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    The Application (client) ID from your Azure AD app registration
                                </p>
                            </div>

                            {{-- Azure AD Client Secret --}}
                            <div>
                                <label for="msgraph_client_secret" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Azure AD Client Secret
                                </label>
                                <input type="password" 
                                       id="msgraph_client_secret" 
                                       name="msgraph_client_secret" 
                                       value="{{ $settings['msgraph_client_secret'] ?? '' }}"
                                       class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none sm:text-sm"
                                       style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';"
                                       placeholder="Enter client secret">
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    The client secret from your Azure AD app registration (keep this secure!)
                                </p>
                            </div>

                            {{-- Tenant ID --}}
                            <div>
                                <label for="msgraph_tenant_id" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Azure AD Tenant ID
                                </label>
                                <input type="text" 
                                       id="msgraph_tenant_id" 
                                       name="msgraph_tenant_id" 
                                       value="{{ $settings['msgraph_tenant_id'] ?? 'common' }}"
                                       class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none sm:text-sm"
                                       style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';"
                                       placeholder="common">
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Use 'common' for multi-tenant access, or your specific tenant ID for single tenant
                                </p>
                            </div>

                            {{-- Redirect URI --}}
                            <div>
                                <label for="msgraph_redirect_uri" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    OAuth Redirect URI
                                </label>
                                <input type="url" 
                                       id="msgraph_redirect_uri" 
                                       name="msgraph_redirect_uri" 
                                       value="{{ $settings['msgraph_redirect_uri'] ?? '' }}"
                                       class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none sm:text-sm"
                                       style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';"
                                       placeholder="https://yourdomain.com/msgraph/oauth">
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Must match exactly with the redirect URI configured in Azure AD
                                </p>
                            </div>

                            {{-- Landing URL --}}
                            <div>
                                <label for="msgraph_landing_url" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Landing URL After Authentication
                                </label>
                                <input type="text" 
                                       id="msgraph_landing_url" 
                                       name="msgraph_landing_url" 
                                       value="{{ $settings['msgraph_landing_url'] ?? '/calendar' }}"
                                       class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none sm:text-sm"
                                       style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';"
                                       placeholder="/calendar">
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Where to redirect users after successful Microsoft authentication
                                </p>
                            </div>

                            {{-- Allow Login --}}
                            <div>
                                <label for="msgraph_allow_login" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Allow Microsoft Login
                                </label>
                                <select id="msgraph_allow_login" name="msgraph_allow_login" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="true" {{ ($settings['msgraph_allow_login'] ?? 'true') == 'true' ? 'selected' : '' }}>
                                        Yes - Allow users to login with Microsoft account
                                    </option>
                                    <option value="false" {{ ($settings['msgraph_allow_login'] ?? 'true') == 'false' ? 'selected' : '' }}>
                                        No - Disable Microsoft login
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Enable or disable Microsoft account login functionality
                                </p>
                            </div>

                            {{-- Allow Access Token Routes --}}
                            <div>
                                <label for="msgraph_allow_access_token_routes" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Allow Access Token Routes
                                </label>
                                <select id="msgraph_allow_access_token_routes" name="msgraph_allow_access_token_routes" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="true" {{ ($settings['msgraph_allow_access_token_routes'] ?? 'true') == 'true' ? 'selected' : '' }}>
                                        Yes - Enable API access token routes
                                    </option>
                                    <option value="false" {{ ($settings['msgraph_allow_access_token_routes'] ?? 'true') == 'false' ? 'selected' : '' }}>
                                        No - Disable API access token routes
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Enable routes for accessing Microsoft Graph API tokens
                                </p>
                            </div>

                            {{-- Help Text --}}
                            <div class="rounded-md p-4" style="background-color: rgba(var(--theme-info-rgb), 0.1); border: 1px solid rgba(var(--theme-info-rgb), 0.3);">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium" style="color: var(--theme-info);">Azure AD Configuration Required</h3>
                                        <div class="mt-2 text-sm" style="color: var(--theme-info);">
                                            <p>To set up Microsoft 365 integration:</p>
                                            <ol class="list-decimal ml-5 mt-1 space-y-1">
                                                <li>Register an app in Azure AD Portal</li>
                                                <li>Add delegated permissions: Calendars.ReadWrite, User.Read</li>
                                                <li>Configure redirect URI: {{ url('/msgraph/oauth') }}</li>
                                                <li>Generate a client secret</li>
                                                <li>Enter the details above</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Date & Time Settings --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200" style="padding: 1rem 1.5rem;">
                            <h2 class="text-lg font-medium" style="color: var(--theme-text);">Date & Time Settings</h2>
                        </div>
                        <div class="space-y-6" style="padding: 1.5rem;">
                            {{-- Timezone --}}
                            <div>
                                <label for="timezone" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Timezone
                                </label>
                                <select id="timezone" name="timezone" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    @foreach($timezones as $region => $zones)
                                        <optgroup label="{{ $region }}">
                                            @foreach($zones as $value => $display)
                                                <option value="{{ $value }}" {{ $settings['timezone'] == $value ? 'selected' : '' }}>
                                                    {{ $display }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Select your timezone for accurate date and time display
                                </p>
                                @error('timezone')
                                    <p class="mt-2 text-sm" style="color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date Format --}}
                            <div>
                                <label for="date_format" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Date Format
                                </label>
                                <select id="date_format" name="date_format" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="d-m-Y" {{ $settings['date_format'] == 'd-m-Y' ? 'selected' : '' }}>
                                        DD-MM-YYYY ({{ now()->format('d-m-Y') }})
                                    </option>
                                    <option value="m-d-Y" {{ $settings['date_format'] == 'm-d-Y' ? 'selected' : '' }}>
                                        MM-DD-YYYY ({{ now()->format('m-d-Y') }})
                                    </option>
                                    <option value="Y-m-d" {{ $settings['date_format'] == 'Y-m-d' ? 'selected' : '' }}>
                                        YYYY-MM-DD ({{ now()->format('Y-m-d') }})
                                    </option>
                                    <option value="d/m/Y" {{ $settings['date_format'] == 'd/m/Y' ? 'selected' : '' }}>
                                        DD/MM/YYYY ({{ now()->format('d/m/Y') }})
                                    </option>
                                    <option value="m/d/Y" {{ $settings['date_format'] == 'm/d/Y' ? 'selected' : '' }}>
                                        MM/DD/YYYY ({{ now()->format('m/d/Y') }})
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Choose how dates are displayed throughout the application
                                </p>
                                @error('date_format')
                                    <p class="mt-2 text-sm" style="color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Time Format --}}
                            <div>
                                <label for="time_format" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Time Format
                                </label>
                                <select id="time_format" name="time_format" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="H:i" {{ $settings['time_format'] == 'H:i' ? 'selected' : '' }}>
                                        24-hour ({{ now()->format('H:i') }})
                                    </option>
                                    <option value="h:i A" {{ $settings['time_format'] == 'h:i A' ? 'selected' : '' }}>
                                        12-hour AM/PM ({{ now()->format('h:i A') }})
                                    </option>
                                    <option value="h:i a" {{ $settings['time_format'] == 'h:i a' ? 'selected' : '' }}>
                                        12-hour am/pm ({{ now()->format('h:i a') }})
                                    </option>
                                    <option value="H:i:s" {{ $settings['time_format'] == 'H:i:s' ? 'selected' : '' }}>
                                        24-hour with seconds ({{ now()->format('H:i:s') }})
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Choose how times are displayed throughout the application
                                </p>
                                @error('time_format')
                                    <p class="mt-2 text-sm" style="color: var(--theme-danger);">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Invoice Timing Settings --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                        <div class="border-b border-gray-200" style="padding: 1rem 1.5rem;">
                            <h2 class="text-lg font-medium" style="color: var(--theme-text);">Invoice Generation Settings</h2>
                            <p class="text-sm" style="color: var(--theme-text-muted);">Configure when invoices should be generated for different billing frequencies</p>
                        </div>
                        <div class="space-y-6" style="padding: 1.5rem;">
                            {{-- Monthly Invoice Day --}}
                            <div>
                                <label for="invoice_monthly_day" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Monthly Invoice Generation Day
                                </label>
                                <select id="invoice_monthly_day" name="invoice_monthly_day" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="last" {{ ($settings['invoice_monthly_day'] ?? 'last') == 'last' ? 'selected' : '' }}>Last day of month</option>
                                    @for($day = 1; $day <= 28; $day++)
                                        <option value="{{ $day }}" {{ ($settings['invoice_monthly_day'] ?? 'last') == $day ? 'selected' : '' }}>
                                            Day {{ $day }} of month
                                        </option>
                                    @endfor
                                    <option value="first_next" {{ ($settings['invoice_monthly_day'] ?? 'last') == 'first_next' ? 'selected' : '' }}>1st of next month</option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    When to generate invoices for projects with monthly billing
                                </p>
                            </div>

                            {{-- Quarterly Invoice Timing --}}
                            <div>
                                <label for="invoice_quarterly_timing" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Quarterly Invoice Generation
                                </label>
                                <select id="invoice_quarterly_timing" name="invoice_quarterly_timing" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="quarter_end" {{ ($settings['invoice_quarterly_timing'] ?? 'quarter_end') == 'quarter_end' ? 'selected' : '' }}>
                                        End of quarter (Mar 31, Jun 30, Sep 30, Dec 31)
                                    </option>
                                    <option value="quarter_start" {{ ($settings['invoice_quarterly_timing'] ?? 'quarter_end') == 'quarter_start' ? 'selected' : '' }}>
                                        Start of next quarter (Apr 1, Jul 1, Oct 1, Jan 1)
                                    </option>
                                    <option value="quarter_after_15" {{ ($settings['invoice_quarterly_timing'] ?? 'quarter_end') == 'quarter_after_15' ? 'selected' : '' }}>
                                        15th of month after quarter
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    When to generate invoices for quarterly billing projects
                                </p>
                            </div>

                            {{-- Milestone Invoice Days --}}
                            <div>
                                <label for="invoice_milestone_days" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Days After Milestone Completion
                                </label>
                                <input type="number" 
                                       id="invoice_milestone_days" 
                                       name="invoice_milestone_days" 
                                       value="{{ $settings['invoice_milestone_days'] ?? '0' }}"
                                       min="0"
                                       max="30"
                                       class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none sm:text-sm"
                                       style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Number of days after milestone completion to generate invoice (0 = immediately)
                                </p>
                            </div>

                            {{-- Project Completion Invoice Days --}}
                            <div>
                                <label for="invoice_project_completion_days" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Days After Project Completion
                                </label>
                                <input type="number" 
                                       id="invoice_project_completion_days" 
                                       name="invoice_project_completion_days" 
                                       value="{{ $settings['invoice_project_completion_days'] ?? '0' }}"
                                       min="0"
                                       max="30"
                                       class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm focus:outline-none sm:text-sm"
                                       style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Number of days after project completion to generate final invoice
                                </p>
                            </div>

                            {{-- Invoice Due Days --}}
                            <div>
                                <label for="invoice_due_days" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Invoice Payment Terms (Days)
                                </label>
                                <select id="invoice_due_days" name="invoice_due_days" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="14" {{ ($settings['invoice_due_days'] ?? '30') == '14' ? 'selected' : '' }}>14 days</option>
                                    <option value="21" {{ ($settings['invoice_due_days'] ?? '30') == '21' ? 'selected' : '' }}>21 days</option>
                                    <option value="30" {{ ($settings['invoice_due_days'] ?? '30') == '30' ? 'selected' : '' }}>30 days</option>
                                    <option value="45" {{ ($settings['invoice_due_days'] ?? '30') == '45' ? 'selected' : '' }}>45 days</option>
                                    <option value="60" {{ ($settings['invoice_due_days'] ?? '30') == '60' ? 'selected' : '' }}>60 days</option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Default payment terms for generated invoices
                                </p>
                            </div>

                            {{-- Auto-Generate Invoices --}}
                            <div>
                                <label for="invoice_auto_generate" class="block text-sm font-medium" style="color: var(--theme-text);">
                                    Automatic Invoice Generation
                                </label>
                                <select id="invoice_auto_generate" name="invoice_auto_generate" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none sm:text-sm rounded-md"
                                        style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 2px rgba(var(--theme-primary-rgb), 0.1)';"
                                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none';">
                                    <option value="false" {{ ($settings['invoice_auto_generate'] ?? 'false') == 'false' ? 'selected' : '' }}>
                                        Manual - Invoices must be created manually
                                    </option>
                                    <option value="true" {{ ($settings['invoice_auto_generate'] ?? 'false') == 'true' ? 'selected' : '' }}>
                                        Automatic - Generate invoices automatically based on schedule
                                    </option>
                                </select>
                                <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">
                                    Whether to automatically generate draft invoices when due dates are reached
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Current Time Display --}}
            <div>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="border-b border-gray-200" style="padding: 1rem 1.5rem;">
                        <h2 class="text-lg font-medium" style="color: var(--theme-text);">Current Time</h2>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="text-center">
                            <div class="text-3xl font-bold" style="color: var(--theme-text);" id="current-time">
                                {{ now()->setTimezone($settings['timezone'])->format($settings['time_format']) }}
                            </div>
                            <div class="mt-2 text-lg" style="color: var(--theme-text-muted);" id="current-date">
                                {{ now()->setTimezone($settings['timezone'])->format($settings['date_format']) }}
                            </div>
                            <div class="mt-4 text-sm" style="color: var(--theme-text-muted);">
                                Timezone: {{ $settings['timezone'] }}
                            </div>
                            <div class="mt-2 text-xs" style="color: var(--theme-text-muted);">
                                Server time: {{ now()->format('Y-m-d H:i:s') }} UTC
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info Box --}}
                <div class="mt-6 rounded-lg p-4" style="background-color: rgba(var(--theme-info-rgb), 0.1); border: 1px solid rgba(var(--theme-info-rgb), 0.3);">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium" style="color: var(--theme-info);">
                                Timezone Information
                            </h3>
                            <div class="mt-2 text-sm" style="color: var(--theme-info);">
                                <p>Changing the timezone will affect how dates and times are displayed throughout the application.</p>
                                <p class="mt-2">All times are stored in UTC and converted to the selected timezone for display.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Theme Settings Card --}}
                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                <div class="mt-6 rounded-lg p-4" style="background: linear-gradient(135deg, rgba(var(--theme-primary-rgb), 0.1), rgba(var(--theme-secondary-rgb), 0.1)); border: 1px solid rgba(var(--theme-primary-rgb), 0.3);">
                    <div class="flex items-center mb-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                            </svg>
                        </div>
                        <h3 class="ml-2 text-sm font-medium" style="color: var(--theme-primary);">
                            Theme Customization
                        </h3>
                    </div>
                    <div class="text-sm" style="color: var(--theme-primary);">
                        <p>Customize the look and feel of your application with advanced theme settings.</p>
                        <div class="mt-3 space-y-2">
                            <a href="{{ route('settings.theme') }}" class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all duration-200">
                                <i class="fas fa-palette mr-2"></i>
                                Customize Theme
                            </a>
                            <div class="text-xs text-center" style="color: var(--theme-primary);">
                                <i class="fas fa-sparkles mr-1"></i>
                                Premium Feature
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
{{-- Settings Help Modal --}}
<div id="settingsHelpModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-hidden">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Settings Help Guide</h2>
                        <p class="text-blue-100 text-sm mt-1">Complete guide for system configuration</p>
                    </div>
                    <button onclick="closeSettingsHelp()" class="text-white hover:text-blue-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div class="overflow-y-auto max-h-[calc(90vh-100px)]" style="padding: 1.5rem;">
                {{-- Quick Navigation --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-2">Quick Navigation</h3>
                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <a href="#calendar-sync" onclick="scrollToSection('calendar-sync')" class="text-blue-600 hover:text-blue-800 hover:underline">📅 Calendar Sync</a>
                        <a href="#microsoft-365" onclick="scrollToSection('microsoft-365')" class="text-blue-600 hover:text-blue-800 hover:underline">🔗 Microsoft 365</a>
                        <a href="#date-time" onclick="scrollToSection('date-time')" class="text-blue-600 hover:text-blue-800 hover:underline">🕐 Date & Time</a>
                        <a href="#azure-setup" onclick="scrollToSection('azure-setup')" class="text-blue-600 hover:text-blue-800 hover:underline">☁️ Azure AD Setup</a>
                        <a href="#permissions" onclick="scrollToSection('permissions')" class="text-blue-600 hover:text-blue-800 hover:underline">🔒 Permissions</a>
                        <a href="#troubleshooting" onclick="scrollToSection('troubleshooting')" class="text-blue-600 hover:text-blue-800 hover:underline">🛠️ Troubleshooting</a>
                    </div>
                </div>

                {{-- Overview Section --}}
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">📋</span>
                        Overview
                    </h3>
                    <div class="prose max-w-none text-gray-700">
                        <p class="mb-3">
                            The Settings module is the central configuration hub for your application. From here, system administrators can manage:
                        </p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Calendar Synchronization</strong> - Control how often calendar events sync with Microsoft 365</li>
                            <li><strong>Microsoft 365 Integration</strong> - Configure Azure AD authentication and API access</li>
                            <li><strong>Date & Time Settings</strong> - Set timezone and display formats for the entire system</li>
                        </ul>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mt-4">
                            <p class="text-sm text-yellow-800">
                                <strong>⚠️ Important:</strong> Only administrators (super_admin and admin roles) can access and modify these settings. Changes affect all users immediately.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Calendar Synchronization Section --}}
                <div id="calendar-sync" class="mb-8 scroll-mt-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-green-100 text-green-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">📅</span>
                        Calendar Synchronization
                    </h3>
                    <div class="space-y-4 text-gray-700">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold mb-2">Automatic Synchronization</h4>
                            <p class="text-sm mb-2">Enable or disable automatic calendar syncing across the application.</p>
                            <ul class="text-sm space-y-1 ml-4">
                                <li>• <strong>Enabled:</strong> Events sync automatically based on configured intervals</li>
                                <li>• <strong>Disabled:</strong> Users must manually sync using the sync button</li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold mb-2">Sync Intervals Explained</h4>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">Setting</th>
                                        <th class="text-left py-2">When It Runs</th>
                                        <th class="text-left py-2">Recommended</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr>
                                        <td class="py-2"><strong>Page Load Sync</strong></td>
                                        <td class="py-2">When user visits calendar page</td>
                                        <td class="py-2 text-green-600">Every hour</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2"><strong>Background Sync</strong></td>
                                        <td class="py-2">While user is on calendar page</td>
                                        <td class="py-2 text-green-600">Every 15 minutes</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2"><strong>Server Cron Job</strong></td>
                                        <td class="py-2">Automatic server-side for all users</td>
                                        <td class="py-2 text-green-600">Every hour</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold mb-2">Event Sync Range</h4>
                            <p class="text-sm mb-2">Determines how far into the future events are synchronized:</p>
                            <ul class="text-sm space-y-1 ml-4">
                                <li>• <strong>30 days:</strong> Minimal data, fast sync, good for high-activity calendars</li>
                                <li>• <strong>90 days (Recommended):</strong> Balance between data and performance</li>
                                <li>• <strong>365 days:</strong> Full year overview, slower sync, more storage</li>
                            </ul>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                            <p class="text-sm text-blue-800">
                                <strong>💡 Pro Tip:</strong> Set shorter intervals for teams that heavily rely on calendar scheduling. For casual users, longer intervals save server resources.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Microsoft 365 Integration Section --}}
                <div id="microsoft-365" class="mb-8 scroll-mt-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-purple-100 text-purple-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">🔗</span>
                        Microsoft 365 Integration
                    </h3>
                    <div class="space-y-4 text-gray-700">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold mb-2">Configuration Fields</h4>
                            <dl class="space-y-2 text-sm">
                                <div>
                                    <dt class="font-medium">Azure AD Application (Client) ID</dt>
                                    <dd class="text-gray-600 ml-4">The unique identifier for your Azure AD app. Format: GUID (e.g., 152e1745-747b-4f86-81bd-bdc8d8e253b5)</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Azure AD Client Secret</dt>
                                    <dd class="text-gray-600 ml-4">Secret key for authentication. Keep this secure! Expires periodically (check Azure Portal)</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Azure AD Tenant ID</dt>
                                    <dd class="text-gray-600 ml-4">
                                        • Use "common" for multi-organization access<br/>
                                        • Use specific tenant ID for single organization only
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium">OAuth Redirect URI</dt>
                                    <dd class="text-gray-600 ml-4">Must exactly match Azure AD configuration. Default: https://yourdomain.com/msgraph/oauth</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Landing URL</dt>
                                    <dd class="text-gray-600 ml-4">Where users go after Microsoft login. Usually /calendar</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <p class="text-sm text-red-800">
                                <strong>🔐 Security Warning:</strong> Never share your Client Secret. Rotate it regularly (every 90 days recommended). If compromised, regenerate immediately in Azure Portal.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Azure AD Setup Guide --}}
                <div id="azure-setup" class="mb-8 scroll-mt-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-indigo-100 text-indigo-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">☁️</span>
                        Azure AD Setup Guide (Step-by-Step)
                    </h3>
                    <div class="space-y-4 text-gray-700">
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4">
                            <h4 class="font-semibold mb-3 text-indigo-900">Complete Setup Process for Beginners</h4>
                            
                            <ol class="space-y-4 text-sm">
                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">1</span>
                                        <div>
                                            <strong>Go to Azure Portal</strong>
                                            <p class="text-gray-600 mt-1">Navigate to <a href="https://portal.azure.com" target="_blank" class="text-blue-600 hover:underline">portal.azure.com</a> and sign in with your Microsoft work account.</p>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">2</span>
                                        <div>
                                            <strong>Navigate to App Registrations</strong>
                                            <p class="text-gray-600 mt-1">Search for "App registrations" in the top search bar or find it under Azure Active Directory → App registrations</p>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">3</span>
                                        <div>
                                            <strong>Create New Registration</strong>
                                            <p class="text-gray-600 mt-1">Click "New registration" and fill in:</p>
                                            <ul class="ml-4 mt-2 space-y-1 text-gray-600">
                                                <li>• <strong>Name:</strong> Progress Calendar Sync (or your app name)</li>
                                                <li>• <strong>Supported account types:</strong> Select "Accounts in any organizational directory"</li>
                                                <li>• <strong>Redirect URI:</strong> Web → {{ url('/msgraph/oauth') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">4</span>
                                        <div>
                                            <strong>Copy Application (Client) ID</strong>
                                            <p class="text-gray-600 mt-1">After registration, you'll see the overview page. Copy the "Application (client) ID" - you'll need this for settings.</p>
                                            <div class="bg-white border rounded p-2 mt-2 font-mono text-xs">
                                                Example: 152e1745-747b-4f86-81bd-bdc8d8e253b5
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">5</span>
                                        <div>
                                            <strong>Create Client Secret</strong>
                                            <p class="text-gray-600 mt-1">Go to "Certificates & secrets" → "New client secret":</p>
                                            <ul class="ml-4 mt-2 space-y-1 text-gray-600">
                                                <li>• <strong>Description:</strong> Progress App Secret</li>
                                                <li>• <strong>Expires:</strong> Choose 24 months (mark calendar for renewal!)</li>
                                                <li>• Click "Add" and IMMEDIATELY copy the secret value (shown only once!)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">6</span>
                                        <div>
                                            <strong>Set API Permissions</strong>
                                            <p class="text-gray-600 mt-1">Go to "API permissions" → "Add a permission" → "Microsoft Graph" → "Delegated permissions":</p>
                                            <ul class="ml-4 mt-2 space-y-1 text-gray-600">
                                                <li>✓ Calendars.ReadWrite</li>
                                                <li>✓ User.Read</li>
                                                <li>✓ offline_access (for refresh tokens)</li>
                                                <li>✓ openid (for authentication)</li>
                                                <li>✓ profile (for user info)</li>
                                            </ul>
                                            <p class="text-gray-600 mt-2">Click "Add permissions" when done.</p>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">7</span>
                                        <div>
                                            <strong>Grant Admin Consent (Optional)</strong>
                                            <p class="text-gray-600 mt-1">If you're an admin, click "Grant admin consent for [Organization]" to pre-approve for all users.</p>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="flex">
                                        <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold mr-3">8</span>
                                        <div>
                                            <strong>Configure in Application Settings</strong>
                                            <p class="text-gray-600 mt-1">Return to this Settings page and enter:</p>
                                            <ul class="ml-4 mt-2 space-y-1 text-gray-600">
                                                <li>• The Client ID you copied</li>
                                                <li>• The Client Secret you copied</li>
                                                <li>• Tenant ID: use "common" for multi-tenant</li>
                                                <li>• Save settings</li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-md p-3">
                            <p class="text-sm text-green-800">
                                <strong>✅ Success Check:</strong> After setup, users should be able to connect their Microsoft account from the Calendar page and see their Outlook events.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Date & Time Settings Section --}}
                <div id="date-time" class="mb-8 scroll-mt-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-orange-100 text-orange-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">🕐</span>
                        Date & Time Settings
                    </h3>
                    <div class="space-y-4 text-gray-700">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold mb-2">Timezone Configuration</h4>
                            <p class="text-sm mb-2">Sets the default timezone for all date/time displays and calculations in the application.</p>
                            <ul class="text-sm space-y-1 ml-4">
                                <li>• Affects: Time entries, calendar events, activity logs</li>
                                <li>• Default: Europe/Amsterdam (CET/CEST)</li>
                                <li>• Users cannot override individually (system-wide setting)</li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold mb-2">Format Options</h4>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">Format</th>
                                        <th class="text-left py-2">Example</th>
                                        <th class="text-left py-2">Region</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr>
                                        <td class="py-2"><strong>DD-MM-YYYY</strong></td>
                                        <td class="py-2">26-08-2025</td>
                                        <td class="py-2">Europe, Asia, Africa</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2"><strong>MM-DD-YYYY</strong></td>
                                        <td class="py-2">08-26-2025</td>
                                        <td class="py-2">United States</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2"><strong>YYYY-MM-DD</strong></td>
                                        <td class="py-2">2025-08-26</td>
                                        <td class="py-2">ISO Standard, Japan</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Permissions Section --}}
                <div id="permissions" class="mb-8 scroll-mt-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-red-100 text-red-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">🔒</span>
                        Permissions & Access Control
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold mb-2">Who Can Access Settings?</h4>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Role</th>
                                    <th class="text-center py-2">View Settings</th>
                                    <th class="text-center py-2">Modify Settings</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr>
                                    <td class="py-2">Super Admin</td>
                                    <td class="py-2 text-center text-green-600">✓</td>
                                    <td class="py-2 text-center text-green-600">✓</td>
                                </tr>
                                <tr>
                                    <td class="py-2">Admin</td>
                                    <td class="py-2 text-center text-green-600">✓</td>
                                    <td class="py-2 text-center text-green-600">✓</td>
                                </tr>
                                <tr>
                                    <td class="py-2">Project Manager</td>
                                    <td class="py-2 text-center text-red-600">✗</td>
                                    <td class="py-2 text-center text-red-600">✗</td>
                                </tr>
                                <tr>
                                    <td class="py-2">User</td>
                                    <td class="py-2 text-center text-red-600">✗</td>
                                    <td class="py-2 text-center text-red-600">✗</td>
                                </tr>
                                <tr>
                                    <td class="py-2">Reader</td>
                                    <td class="py-2 text-center text-red-600">✗</td>
                                    <td class="py-2 text-center text-red-600">✗</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Troubleshooting Section --}}
                <div id="troubleshooting" class="mb-8 scroll-mt-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-gray-100 text-gray-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">🛠️</span>
                        Troubleshooting Common Issues
                    </h3>
                    <div class="space-y-4">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <h4 class="font-semibold text-yellow-900 mb-2">Calendar sync not working</h4>
                            <ol class="text-sm text-yellow-800 space-y-1 ml-4">
                                <li>1. Check if Microsoft 365 credentials are correctly entered</li>
                                <li>2. Verify the Client Secret hasn't expired (check Azure Portal)</li>
                                <li>3. Ensure Redirect URI exactly matches (including https://)</li>
                                <li>4. Check if automatic sync is enabled in settings</li>
                                <li>5. Look for error messages in Calendar Activity Timeline</li>
                            </ol>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <h4 class="font-semibold text-yellow-900 mb-2">Microsoft login fails</h4>
                            <ol class="text-sm text-yellow-800 space-y-1 ml-4">
                                <li>1. Verify Tenant ID (use "common" for multi-tenant)</li>
                                <li>2. Check API permissions in Azure (need Calendars.ReadWrite)</li>
                                <li>3. Ensure "Allow Microsoft Login" is set to "Yes"</li>
                                <li>4. Clear browser cookies and try again</li>
                                <li>5. Check if user's organization allows third-party apps</li>
                            </ol>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <h4 class="font-semibold text-yellow-900 mb-2">Time shows incorrectly</h4>
                            <ol class="text-sm text-yellow-800 space-y-1 ml-4">
                                <li>1. Verify timezone setting matches your location</li>
                                <li>2. Check server time is correct (contact hosting provider)</li>
                                <li>3. Clear application cache after changing timezone</li>
                                <li>4. Ensure date/time format settings are configured</li>
                            </ol>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <h4 class="font-semibold text-yellow-900 mb-2">Settings not saving</h4>
                            <ol class="text-sm text-yellow-800 space-y-1 ml-4">
                                <li>1. Check you have admin or super_admin role</li>
                                <li>2. Verify all required fields are filled</li>
                                <li>3. Check for validation errors (red text under fields)</li>
                                <li>4. Try clearing browser cache and cookies</li>
                                <li>5. Check database connection is working</li>
                            </ol>
                        </div>
                    </div>
                </div>

                {{-- Best Practices Section --}}
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="bg-green-100 text-green-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">⭐</span>
                        Best Practices & Tips
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-green-50 rounded-lg p-4">
                            <h4 class="font-semibold text-green-900 mb-2">Security</h4>
                            <ul class="text-sm text-green-800 space-y-1">
                                <li>✓ Rotate Client Secret every 90 days</li>
                                <li>✓ Use strong, unique secrets</li>
                                <li>✓ Never share credentials via email</li>
                                <li>✓ Regularly review API permissions</li>
                                <li>✓ Monitor sync logs for anomalies</li>
                            </ul>
                        </div>

                        <div class="bg-blue-50 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-2">Performance</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>✓ Balance sync frequency with server load</li>
                                <li>✓ Use 90-day sync range as default</li>
                                <li>✓ Disable background sync for light users</li>
                                <li>✓ Monitor server resources during peak times</li>
                                <li>✓ Clear old sync logs monthly</li>
                            </ul>
                        </div>

                        <div class="bg-purple-50 rounded-lg p-4">
                            <h4 class="font-semibold text-purple-900 mb-2">Maintenance</h4>
                            <ul class="text-sm text-purple-800 space-y-1">
                                <li>✓ Document all configuration changes</li>
                                <li>✓ Test settings in development first</li>
                                <li>✓ Keep Azure AD app registration updated</li>
                                <li>✓ Review settings quarterly</li>
                                <li>✓ Train new admins on settings</li>
                            </ul>
                        </div>

                        <div class="bg-orange-50 rounded-lg p-4">
                            <h4 class="font-semibold text-orange-900 mb-2">User Experience</h4>
                            <ul class="text-sm text-orange-800 space-y-1">
                                <li>✓ Inform users before major changes</li>
                                <li>✓ Provide calendar sync status visibility</li>
                                <li>✓ Use consistent date/time formats</li>
                                <li>✓ Enable auto-sync for active teams</li>
                                <li>✓ Monitor user feedback on sync speed</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Quick Reference Card --}}
                <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-lg p-6 text-white">
                    <h3 class="text-xl font-bold mb-4">🎯 Quick Reference Card</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <h4 class="font-semibold mb-2 text-gray-300">Essential URLs</h4>
                            <ul class="space-y-1">
                                <li>Azure Portal: <span class="text-blue-300">portal.azure.com</span></li>
                                <li>MS Graph Docs: <span class="text-blue-300">docs.microsoft.com/graph</span></li>
                                <li>OAuth Redirect: <span class="text-blue-300">{{ url('/msgraph/oauth') }}</span></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2 text-gray-300">Required Permissions</h4>
                            <ul class="space-y-1">
                                <li>• Calendars.ReadWrite</li>
                                <li>• User.Read</li>
                                <li>• offline_access</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2 text-gray-300">Support Contacts</h4>
                            <ul class="space-y-1">
                                <li>System Admin: settings@company.com</li>
                                <li>Azure Support: azure.com/support</li>
                                <li>Documentation: /help/settings</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-500 text-center">
                        Last updated: {{ now()->format('F j, Y') }} • Settings Help Guide v1.0 • 
                        <a href="#" onclick="window.print(); return false;" class="text-blue-600 hover:underline">Print this guide</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Help Modal --}}
<script>
    function openSettingsHelp() {
        document.getElementById('settingsHelpModal').classList.remove('hidden');
    }

    function closeSettingsHelp() {
        document.getElementById('settingsHelpModal').classList.add('hidden');
    }

    function scrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSettingsHelp();
        }
    });

    // Print-friendly styles
    if (window.matchMedia) {
        const mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (mql.matches) {
                document.getElementById('settingsHelpModal').classList.remove('hidden');
            }
        });
    }
</script>

<style>
    @media print {
        #settingsHelpModal {
            position: static !important;
            display: block !important;
            background: white !important;
        }
        
        #settingsHelpModal .bg-gray-500 {
            background: white !important;
        }
        
        button, .no-print {
            display: none !important;
        }
    }
    
    .scroll-mt-4 {
        scroll-margin-top: 1rem;
    }
</style>
@endsection

@push('scripts')
<script>
// Update current time every second
function updateTime() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    const dateElement = document.getElementById('current-date');
    
    // This will be updated with the actual timezone from the server
    // For now, just show local time
    if (timeElement) {
        const timezone = '{{ $settings["timezone"] }}';
        const options = { 
            timeZone: timezone,
            hour12: '{{ $settings["time_format"] }}'.includes('A') || '{{ $settings["time_format"] }}'.includes('a'),
            hour: '2-digit',
            minute: '2-digit',
            second: '{{ $settings["time_format"] }}'.includes('s') ? '2-digit' : undefined
        };
        timeElement.textContent = now.toLocaleTimeString('en-US', options);
    }
}

setInterval(updateTime, 1000);
updateTime();
</script>
@endpush