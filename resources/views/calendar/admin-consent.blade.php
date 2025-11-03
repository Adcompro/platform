@extends('layouts.app')

@section('title', 'Admin Consent Required')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Alert Box -->
        <div class="bg-white/60 backdrop-blur-sm border border-amber-200/60 rounded-xl overflow-hidden mb-8">
            <div class="px-6 py-4 bg-amber-50/50 border-b border-amber-200/50">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-amber-900">Administrator Approval Required</h2>
                </div>
            </div>
            <div class="p-6">
                <p class="text-slate-700 mb-4">
                    Your organization requires administrator approval before you can connect your Microsoft 365 calendar.
                    This is a security policy set by your IT department.
                </p>
                
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-amber-800">
                        <strong>Organization detected:</strong> {{ request()->get('domain', 'Your organization') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Solutions -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Option 1: Contact Admin -->
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                    <h3 class="text-base font-semibold text-slate-900">Option 1: Contact Your IT Admin</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-slate-600 mb-4">
                        Send this information to your IT administrator:
                    </p>
                    
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-4">
                        <p class="text-xs text-slate-500 mb-2">App Name:</p>
                        <p class="font-medium text-sm text-slate-900 mb-3">Progress AdCompro Calendar</p>
                        
                        <p class="text-xs text-slate-500 mb-2">Application ID:</p>
                        <p class="font-mono text-xs text-slate-700 mb-3">152e1745-747b-4f86-81bd-bdc8d8e253b5</p>
                        
                        <p class="text-xs text-slate-500 mb-2">Required Permissions:</p>
                        <ul class="text-xs text-slate-700 space-y-1">
                            <li>• User.Read (Read user profile)</li>
                            <li>• Calendars.Read (Read calendars)</li>
                            <li>• Calendars.ReadWrite (Manage calendars)</li>
                        </ul>
                    </div>
                    
                    <button onclick="copyAdminLink()" class="w-full px-3 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200">
                        Copy Admin Consent Link
                    </button>
                </div>
            </div>

            <!-- Option 2: Use Personal Account -->
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                    <h3 class="text-base font-semibold text-slate-900">Option 2: Use Personal Account</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-slate-600 mb-4">
                        You can also connect using a personal Microsoft account that doesn't require admin approval.
                    </p>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <p class="text-xs text-blue-800">
                            Personal accounts like @outlook.com, @hotmail.com, or @live.com don't require admin consent.
                        </p>
                    </div>
                    
                    <a href="{{ route('msgraph.connect') }}" class="block w-full text-center px-3 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-all duration-200">
                        Try Different Account
                    </a>
                </div>
            </div>
        </div>

        <!-- Admin Section -->
        <div class="mt-8 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                <h3 class="text-base font-semibold text-slate-900">For IT Administrators</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-4">
                    To grant consent for all users in your organization, use this admin consent URL:
                </p>
                
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-4">
                    <code id="adminConsentUrl" class="text-xs break-all text-slate-700">
                        https://login.microsoftonline.com/common/adminconsent?client_id=152e1745-747b-4f86-81bd-bdc8d8e253b5&redirect_uri=https://progress.adcompro.app/msgraph/oauth
                    </code>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="copyAdminConsentUrl()" class="flex-1 px-3 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200">
                        Copy URL
                    </button>
                    <a href="https://login.microsoftonline.com/common/adminconsent?client_id=152e1745-747b-4f86-81bd-bdc8d8e253b5&redirect_uri=https://progress.adcompro.app/msgraph/oauth" 
                       target="_blank"
                       class="flex-1 text-center px-3 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-all duration-200">
                        Grant Admin Consent
                    </a>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('calendar.index') }}" class="text-slate-600 hover:text-slate-800 text-sm">
                ← Back to Calendar
            </a>
        </div>
    </div>
</div>

<script>
function copyAdminLink() {
    const text = `Please approve our calendar integration app:

App Name: Progress AdCompro Calendar
App ID: 152e1745-747b-4f86-81bd-bdc8d8e253b5

Admin Consent URL:
https://login.microsoftonline.com/common/adminconsent?client_id=152e1745-747b-4f86-81bd-bdc8d8e253b5&redirect_uri=https://progress.adcompro.app/msgraph/oauth

This app needs permission to:
- Read user profiles
- Read and write calendar events`;

    navigator.clipboard.writeText(text);
    alert('Admin information copied to clipboard!');
}

function copyAdminConsentUrl() {
    const url = document.getElementById('adminConsentUrl').textContent.trim();
    navigator.clipboard.writeText(url);
    alert('Admin consent URL copied to clipboard!');
}
</script>
@endsection