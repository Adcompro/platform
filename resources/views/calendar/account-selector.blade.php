@extends('layouts.app')

@section('title', 'Select Microsoft Account')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Account Selection -->
        <div class="bg-white/60 backdrop-blur-sm border border-blue-200/60 rounded-xl overflow-hidden mb-8">
            <div class="px-6 py-4 bg-blue-50/50 border-b border-blue-200/50">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-blue-900">Choose Microsoft Account</h2>
                </div>
            </div>
            <div class="p-6">
                <p class="text-slate-700 mb-6">
                    Select which Microsoft account you want to use for calendar synchronization:
                </p>
                
                <!-- Known Accounts -->
                <div class="space-y-3">
                    <!-- Marcela Account -->
                    <div class="border border-slate-200 rounded-lg hover:border-blue-300 transition-all">
                        <a href="{{ route('msgraph.connect', ['login_hint' => 'marcela@voedselbankgooi.nl']) }}" 
                           class="block p-4 hover:bg-blue-50 transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-slate-900">marcela@voedselbankgooi.nl</div>
                                    <div class="text-sm text-slate-600">Work account - Voedselbank</div>
                                </div>
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Admin Account (not recommended) -->
                    <div class="border border-slate-200 rounded-lg opacity-60">
                        <div class="p-4 bg-amber-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-slate-700">Marcel-admin@voedselbankgooi.nl</div>
                                    <div class="text-sm text-amber-700">⚠️ Admin account - No mailbox available</div>
                                </div>
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other Account -->
                    <div class="border border-slate-200 rounded-lg hover:border-green-300 transition-all">
                        <a href="{{ route('msgraph.connect') }}" 
                           class="block p-4 hover:bg-green-50 transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-slate-900">Use Different Account</div>
                                    <div class="text-sm text-slate-600">Login with another Microsoft 365 account</div>
                                </div>
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                <h3 class="text-base font-semibold text-slate-900">Account Requirements</h3>
            </div>
            <div class="p-6">
                <ul class="space-y-2 text-sm text-slate-700">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Must have Exchange Online license</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Calendar access must be enabled</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Admin consent must be granted (already done for voedselbankgooi.nl)</span>
                    </li>
                </ul>
                
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <strong>Tip:</strong> If you keep getting logged in with the wrong account, open this page in an incognito/private browser window.
                    </p>
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