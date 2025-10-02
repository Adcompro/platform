@extends('layouts.app')

@section('title', 'Force Microsoft Logout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Problem Description -->
        <div class="bg-white/60 backdrop-blur-sm border border-red-200/60 rounded-xl overflow-hidden mb-8">
            <div class="px-6 py-4 bg-red-50/50 border-b border-red-200/50">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-red-900">Microsoft Account Stuck</h2>
                </div>
            </div>
            <div class="p-6">
                <p class="text-slate-700 mb-4">
                    <strong>Marcel-admin@voedselbankgooi.nl</strong> blijft ingelogd ondanks uitlog pogingen.
                </p>
                
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <p class="text-sm text-amber-800 font-semibold mb-2">
                        Volg deze stappen EXACT in deze volgorde:
                    </p>
                </div>
            </div>
        </div>

        <!-- Aggressive Solution -->
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                <h3 class="text-base font-semibold text-slate-900">🔥 Forceer Complete Uitlog</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Step 1 -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="font-semibold text-slate-900 mb-2">Stap 1: Open een NIEUW Incognito/Private venster</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-2">
                        <p class="text-sm text-blue-800">
                            <strong>Chrome:</strong> Ctrl+Shift+N (Windows) of Cmd+Shift+N (Mac)<br>
                            <strong>Edge:</strong> Ctrl+Shift+N<br>
                            <strong>Firefox:</strong> Ctrl+Shift+P<br>
                            <strong>Safari:</strong> Cmd+Shift+N
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-slate-900 mb-2">Stap 2: In het Incognito venster</h4>
                    <ol class="text-sm text-slate-700 space-y-2">
                        <li>1. Ga naar: <code class="bg-slate-100 px-2 py-1 rounded">https://progress.adcompro.app/calendar</code></li>
                        <li>2. Login met je Laravel account (niet Microsoft)</li>
                        <li>3. Klik op "Connect to Microsoft 365"</li>
                        <li>4. <strong class="text-green-600">NU kun je met marcela@voedselbankgooi.nl inloggen!</strong></li>
                    </ol>
                </div>

                <!-- Alternative Step -->
                <div class="border-l-4 border-purple-500 pl-4">
                    <h4 class="font-semibold text-slate-900 mb-2">Alternatief: Clear ALLE Microsoft cookies</h4>
                    <div class="space-y-3">
                        <a href="https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri=https://progress.adcompro.app/calendar/force-logout" 
                           class="inline-block px-4 py-2 bg-purple-500 text-white text-sm font-medium rounded-lg hover:bg-purple-600">
                            Forceer Microsoft Uitlog
                        </a>
                        
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                            <p class="text-xs text-purple-800">
                                Of handmatig cookies verwijderen:
                            </p>
                            <ol class="text-xs text-purple-700 mt-2 space-y-1">
                                <li>1. Open browser settings (Ctrl+Shift+Delete)</li>
                                <li>2. Kies "Cookies and site data"</li>
                                <li>3. Zoek naar "microsoft" en "live.com"</li>
                                <li>4. Verwijder ALLE Microsoft/Live/Office cookies</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct Links -->
        <div class="mt-8 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/50">
                <h3 class="text-base font-semibold text-slate-900">Directe Login Links</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-4">
                    Open deze link in een <strong>incognito venster</strong> om direct met het juiste account in te loggen:
                </p>
                
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                    <p class="text-xs text-slate-500 mb-2">Voor Marcela:</p>
                    <code class="text-xs break-all text-slate-700">
                        https://login.microsoftonline.com/common/oauth2/v2.0/authorize?prompt=select_account&login_hint=marcela@voedselbankgooi.nl&client_id=152e1745-747b-4f86-81bd-bdc8d8e253b5&response_type=code&redirect_uri=https://progress.adcompro.app/msgraph/oauth&scope=offline_access%20openid%20User.Read%20Calendars.Read%20Calendars.ReadWrite
                    </code>
                </div>
                
                <button onclick="copyMarcelaLink()" class="mt-3 px-4 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600">
                    Copy Marcela Login Link
                </button>
            </div>
        </div>

        <!-- Emergency Option -->
        <div class="mt-8 bg-red-50 border border-red-200 rounded-xl p-6">
            <h3 class="font-semibold text-red-900 mb-3">🚨 Noodoplossing</h3>
            <p class="text-sm text-red-800 mb-3">
                Als niets werkt, probeer dan:
            </p>
            <ol class="text-sm text-red-700 space-y-2">
                <li>1. Gebruik een <strong>andere browser</strong> (bijv. Edge als je Chrome gebruikt)</li>
                <li>2. Of gebruik een <strong>andere computer/device</strong></li>
                <li>3. Login daar met marcela@voedselbankgooi.nl</li>
                <li>4. Na succesvolle connectie werkt het op alle devices</li>
            </ol>
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('calendar.index') }}" class="text-slate-600 hover:text-slate-800 text-sm">
                ← Terug naar Calendar
            </a>
        </div>
    </div>
</div>

<script>
function copyMarcelaLink() {
    const link = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?prompt=select_account&login_hint=marcela@voedselbankgooi.nl&client_id=152e1745-747b-4f86-81bd-bdc8d8e253b5&response_type=code&redirect_uri=https://progress.adcompro.app/msgraph/oauth&scope=offline_access%20openid%20User.Read%20Calendars.Read%20Calendars.ReadWrite';
    navigator.clipboard.writeText(link);
    alert('Login link voor Marcela gekopieerd!');
}
</script>
@endsection