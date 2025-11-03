@extends('layouts.app')

@section('title', 'Calendar Setup - Microsoft 365')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Microsoft 365 Calendar Setup</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Configure Azure App Registration for calendar integration</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Current Configuration Status --}}
        <div class="bg-white/80 backdrop-blur-sm shadow-sm rounded-xl border border-slate-200/50 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200/50">
                <h2 class="text-lg font-semibold text-slate-900">Current Configuration Status</h2>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm font-medium text-slate-700">Client ID:</span>
                        <span class="text-sm text-slate-600">
                            @if(config('msgraph.clientId'))
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Configured</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Not configured</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm font-medium text-slate-700">Client Secret:</span>
                        <span class="text-sm text-slate-600">
                            @if(config('msgraph.clientSecret'))
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Configured</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Not configured</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm font-medium text-slate-700">Tenant ID:</span>
                        <span class="text-sm text-slate-600">
                            {{ config('msgraph.tenantId', 'common') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm font-medium text-slate-700">Redirect URI:</span>
                        <span class="text-sm text-slate-600 font-mono text-xs">
                            https://progress.adcompro.app/msgraph/oauth
                        </span>
                    </div>
                </div>

                @if(!config('msgraph.clientId') || !config('msgraph.clientSecret'))
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Azure App Registration is not configured. Follow the steps below to set it up.
                    </p>
                </div>
                @else
                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        Azure App Registration is configured. You can now connect your Microsoft 365 account.
                    </p>
                    <a href="{{ route('msgraph.connect') }}" class="mt-3 inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                        <i class="fab fa-microsoft mr-2"></i>
                        Connect Microsoft 365
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Setup Instructions --}}
        <div class="bg-white/80 backdrop-blur-sm shadow-sm rounded-xl border border-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/50">
                <h2 class="text-lg font-semibold text-slate-900">Setup Instructions</h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    {{-- Step 1 --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Step 1: Create Azure App Registration</h3>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-slate-600">
                            <li>Go to <a href="https://portal.azure.com" target="_blank" class="text-blue-600 hover:underline">Azure Portal</a></li>
                            <li>Navigate to <strong>Azure Active Directory</strong> → <strong>App registrations</strong></li>
                            <li>Click <strong>"New registration"</strong></li>
                            <li>Enter the following details:
                                <ul class="list-disc list-inside ml-5 mt-2 space-y-1">
                                    <li><strong>Name:</strong> Progress AdCompro Calendar</li>
                                    <li><strong>Supported account types:</strong> Accounts in any organizational directory (Any Azure AD directory - Multitenant)</li>
                                    <li><strong>Redirect URI:</strong> Web → <code class="bg-slate-100 px-2 py-1 rounded text-xs">https://progress.adcompro.app/msgraph/oauth</code></li>
                                </ul>
                            </li>
                            <li>Click <strong>"Register"</strong></li>
                        </ol>
                    </div>

                    {{-- Step 2 --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Step 2: Configure API Permissions</h3>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-slate-600">
                            <li>In your app registration, go to <strong>"API permissions"</strong></li>
                            <li>Click <strong>"Add a permission"</strong> → <strong>"Microsoft Graph"</strong> → <strong>"Delegated permissions"</strong></li>
                            <li>Add these permissions:
                                <ul class="list-disc list-inside ml-5 mt-2 space-y-1">
                                    <li><code class="bg-slate-100 px-2 py-1 rounded text-xs">User.Read</code> - Sign in and read user profile</li>
                                    <li><code class="bg-slate-100 px-2 py-1 rounded text-xs">Calendars.Read</code> - Read user calendars</li>
                                    <li><code class="bg-slate-100 px-2 py-1 rounded text-xs">Calendars.ReadWrite</code> - Read and write user calendars</li>
                                    <li><code class="bg-slate-100 px-2 py-1 rounded text-xs">offline_access</code> - Maintain access to data</li>
                                </ul>
                            </li>
                            <li>Click <strong>"Add permissions"</strong></li>
                        </ol>
                    </div>

                    {{-- Step 3 --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Step 3: Create Client Secret</h3>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-slate-600">
                            <li>Go to <strong>"Certificates & secrets"</strong></li>
                            <li>Click <strong>"New client secret"</strong></li>
                            <li>Enter a description (e.g., "Progress Calendar Integration")</li>
                            <li>Select expiry (recommended: 24 months)</li>
                            <li>Click <strong>"Add"</strong></li>
                            <li><strong class="text-red-600">Important:</strong> Copy the secret value immediately (it won't be shown again)</li>
                        </ol>
                    </div>

                    {{-- Step 4 --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Step 4: Update .env File</h3>
                        <div class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs"><code># Microsoft Graph API Settings
MSGRAPH_CLIENT_ID=your-client-id-here
MSGRAPH_CLIENT_SECRET=your-client-secret-here
MSGRAPH_TENANT_ID=common</code></pre>
                        </div>
                        <div class="mt-3 space-y-2 text-sm text-slate-600">
                            <p><strong>Where to find these values:</strong></p>
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Client ID:</strong> Overview page of your app registration</li>
                                <li><strong>Client Secret:</strong> The value you copied in Step 3</li>
                                <li><strong>Tenant ID:</strong> Keep as "common" for multi-tenant</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Step 5 --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Step 5: Clear Cache and Test</h3>
                        <div class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs"><code>php artisan config:clear
php artisan cache:clear</code></pre>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">
                            After updating the .env file and clearing cache, refresh this page to see the updated status.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Troubleshooting --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-blue-900 mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                Troubleshooting Tips
            </h3>
            <ul class="list-disc list-inside space-y-2 text-sm text-blue-800">
                <li>Make sure you're using a work or school Microsoft account</li>
                <li>Ensure the redirect URI matches exactly: <code class="bg-blue-100 px-2 py-1 rounded text-xs">https://progress.adcompro.app/msgraph/oauth</code></li>
                <li>If you get permission errors, ask your Azure AD admin to grant consent</li>
                <li>Client secret expires after the selected period - remember to renew it</li>
            </ul>
        </div>

        {{-- Back Button --}}
        <div class="mt-6 text-center">
            <a href="{{ route('calendar.index') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Calendar
            </a>
        </div>
    </div>
</div>
@endsection