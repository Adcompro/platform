@extends('layouts.app')

@section('title', 'Connect Microsoft 365 Calendar')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/70 backdrop-blur-sm sticky top-0 z-10" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold" style="color: var(--theme-text);">Connect Microsoft 365</h1>
                    <p class="text-xs mt-0.5" style="color: var(--theme-text-muted);">Sync your Outlook calendar with time tracking</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="theme-card bg-white/80 backdrop-blur-sm overflow-hidden" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4" style="background: linear-gradient(135deg, var(--theme-primary), var(--theme-primary));">
                        <i class="fab fa-microsoft text-white text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2" style="color: var(--theme-text);">Connect Your Microsoft 365 Account</h2>
                    <p class="max-w-2xl mx-auto" style="color: var(--theme-text-muted);">
                        Connect your Microsoft 365 account to sync calendar events and convert them into time entries automatically.
                    </p>
                </div>

                {{-- Features --}}
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.1);">
                                <i class="fas fa-sync" style="color: var(--theme-accent);"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-medium" style="color: var(--theme-text);">Automatic Sync</h3>
                            <p class="text-sm mt-1" style="color: var(--theme-text-muted); ">
                                Keep your calendar events synchronized with your time tracking system
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background-color: rgba(var(--theme-primary-rgb, 37, 99, 235), 0.1);">
                                <i class="fas fa-exchange-alt" style="color: var(--theme-primary);"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-medium" style="color: var(--theme-text);">Easy Conversion</h3>
                            <p class="text-sm mt-1" style="color: var(--theme-text-muted); ">
                                Convert calendar events to time entries with just one click
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background-color: rgba(147, 51, 234, 0.1);">
                                <i class="fas fa-calendar-check" style="color: #9333ea;"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-medium" style="color: var(--theme-text);">Smart Matching</h3>
                            <p class="text-sm mt-1" style="color: var(--theme-text-muted); ">
                                Automatically match events to projects based on categories and keywords
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background-color: rgba(234, 179, 8, 0.1);">
                                <i class="fas fa-clock" style="color: #eab308;"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-medium" style="color: var(--theme-text);">Save Time</h3>
                            <p class="text-sm mt-1" style="color: var(--theme-text-muted); ">
                                No more manual entry - import your meetings directly as time entries
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Security Notice --}}
                <div class="p-4 mb-8" style="background-color: rgba(var(--theme-primary-rgb, 37, 99, 235), 0.05); border: 1px solid rgba(var(--theme-primary-rgb, 37, 99, 235), 0.2); border-radius: var(--theme-border-radius);">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt mt-0.5" style="color: var(--theme-primary);"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium" style="color: var(--theme-primary);">Secure Connection</h3>
                            <p class="text-sm mt-1" style="color: var(--theme-primary); opacity: 0.9; ">
                                Your Microsoft 365 credentials are never stored. We use OAuth 2.0 for secure authentication.
                                You can revoke access at any time from your Microsoft account settings.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Connect Button --}}
                <div class="text-center">
                    @if(config('msgraph.clientId') && config('msgraph.clientSecret'))
                        <a href="{{ route('msgraph.connect') }}" class="theme-btn-primary inline-flex items-center px-6 py-3 text-white font-medium transition-all duration-200" style="border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                            <i class="fab fa-microsoft mr-2"></i>
                            Connect with Microsoft 365
                        </a>
                        <p class="text-xs mt-3" style="color: var(--theme-text-muted);">
                            You will be redirected to Microsoft to authorize access
                        </p>
                    @else
                        <div class="p-4 mb-4" style="background-color: rgba(234, 179, 8, 0.05); border: 1px solid rgba(234, 179, 8, 0.2); border-radius: var(--theme-border-radius);">
                            <p class="text-sm" style="color: #a16207; ">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Azure App Registration is not configured yet.
                            </p>
                        </div>
                        <a href="{{ route('calendar.setup') }}" class="inline-flex items-center px-6 py-3 text-white font-medium transition-all duration-200" style="background-color: #eab308; border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                            <i class="fas fa-cog mr-2"></i>
                            Setup Azure App Registration
                        </a>
                        <p class="text-xs mt-3" style="color: var(--theme-text-muted);">
                            You need to configure Azure App Registration first
                        </p>
                    @endif
                </div>

                {{-- What We Access --}}
                <div class="mt-8 pt-8" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                    <h3 class="text-sm font-semibold mb-3" style="color: var(--theme-text);">What we access:</h3>
                    <ul class="space-y-2 text-sm" style="color: var(--theme-text-muted); ">
                        <li class="flex items-start">
                            <i class="fas fa-check mt-0.5 mr-2" style="color: var(--theme-accent);"></i>
                            <span>Read your calendar events (past 30 days and future 90 days)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check mt-0.5 mr-2" style="color: var(--theme-accent);"></i>
                            <span>Basic profile information (name and email)</span>
                        </li>
                    </ul>
                    <h3 class="text-sm font-semibold mb-3 mt-4" style="color: var(--theme-text);">What we don't access:</h3>
                    <ul class="space-y-2 text-sm" style="color: var(--theme-text-muted); ">
                        <li class="flex items-start">
                            <i class="fas fa-times mt-0.5 mr-2" style="color: var(--theme-danger);"></i>
                            <span>Your emails or personal files</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-times mt-0.5 mr-2" style="color: var(--theme-danger);"></i>
                            <span>Modify or delete your calendar events</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-times mt-0.5 mr-2" style="color: var(--theme-danger);"></i>
                            <span>Access to other Microsoft 365 services</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="mt-6 text-center">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium" style="color: var(--theme-text-muted); " onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection