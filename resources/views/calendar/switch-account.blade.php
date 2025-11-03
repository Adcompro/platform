@extends('layouts.app')

@section('title', 'Switch Microsoft Account')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Current Account Info -->
        <div class="bg-white/60 backdrop-blur-sm border border-red-200/60 rounded-xl overflow-hidden mb-8">
            <div class="px-6 py-4 bg-red-50/50 border-b border-red-200/50">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-red-900">Wrong Microsoft Account</h2>
                </div>
            </div>
            <div class="p-6">
                <p class="text-slate-700 mb-4">
                    You're currently connected with an account that doesn't have a Microsoft 365 mailbox with calendar access.
                </p>
                
                @if(session('ms_user_email'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-red-800">
                        <strong>Currently connected as:</strong><br>
                        {{ session('ms_user_email') }}
                    </p>
                </div>
                @endif
                
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-sm text-amber-800">
                        <strong>Common causes:</strong>
                    </p>
                    <ul class="text-sm text-amber-700 mt-2 space-y-1">
                        <li>• You're logged in with an admin account without a mailbox</li>
                        <li>• The account uses on-premises Exchange (not Exchange Online)</li>
                        <li>• The account doesn't have a valid Exchange Online license</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Solution Steps -->
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                <h3 class="text-base font-semibold text-slate-900">How to Fix This</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Step 1 -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                                1
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-sm font-semibold text-slate-900">Disconnect Current Account</h4>
                            <p class="text-sm text-slate-600 mt-1">
                                First, disconnect from the current Microsoft account.
                            </p>
                            <form action="{{ route('calendar.disconnect') }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-all duration-200">
                                    Disconnect Current Account
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                                2
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-sm font-semibold text-slate-900">Clear Browser Session</h4>
                            <p class="text-sm text-slate-600 mt-1">
                                Clear your Microsoft login session in the browser to ensure you can login with a different account.
                            </p>
                            <div class="mt-3">
                                <a href="https://login.microsoftonline.com/logout.srf" 
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-all duration-200">
                                    Sign Out of Microsoft
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                                3
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-sm font-semibold text-slate-900">Connect with Correct Account</h4>
                            <p class="text-sm text-slate-600 mt-1">
                                After clearing your session, connect with the correct Microsoft 365 account (e.g., marcela@voedselbankgooi.nl).
                            </p>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3">
                                <p class="text-xs text-blue-800">
                                    <strong>Important:</strong> Make sure to select "Use another account" or "Sign in with a different account" when prompted.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                <h3 class="text-base font-semibold text-slate-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Disconnect and Reconnect -->
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Option 1: Full Reset</h4>
                        <form action="{{ route('calendar.disconnect') }}" method="POST">
                            @csrf
                            <input type="hidden" name="redirect_to_connect" value="true">
                            <button type="submit" class="w-full px-4 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200">
                                Disconnect & Reconnect
                            </button>
                        </form>
                    </div>

                    <!-- Try Manual Entry -->
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Option 2: Manual Calendar</h4>
                        <a href="{{ route('calendar.manual') }}" class="block w-full text-center px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-all duration-200">
                            Use Manual Calendar
                        </a>
                    </div>
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
@endsection