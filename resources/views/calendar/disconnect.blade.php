@extends('layouts.app')

@section('title', 'Disconnect Microsoft 365')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white/80 backdrop-blur-sm shadow-sm rounded-xl border border-slate-200/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/50">
                <h2 class="text-lg font-semibold text-slate-900">Microsoft 365 Account Settings</h2>
            </div>
            <div class="p-6">
                @if(\Dcblogdev\MsGraph\Facades\MsGraph::isConnected())
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-slate-700 mb-2">Currently Connected Account:</h3>
                        <div class="bg-slate-50 rounded-lg p-4">
                            @php
                                try {
                                    $user = \Dcblogdev\MsGraph\Facades\MsGraph::get('me');
                                    $email = $user['mail'] ?? $user['userPrincipalName'] ?? 'Unknown';
                                    $name = $user['displayName'] ?? 'Unknown';
                                } catch (\Exception $e) {
                                    $email = 'Unable to retrieve';
                                    $name = 'Unable to retrieve';
                                }
                            @endphp
                            <p class="text-sm text-slate-600"><strong>Name:</strong> {{ $name }}</p>
                            <p class="text-sm text-slate-600"><strong>Email:</strong> {{ $email }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-medium text-yellow-900 mb-2">Disconnect Account</h3>
                        <p class="text-sm text-yellow-700 mb-4">
                            This will remove the connection to your Microsoft 365 account and delete all cached calendar data.
                        </p>
                        <form method="POST" action="{{ route('msgraph.disconnect') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                                <i class="fas fa-unlink mr-2"></i>
                                Disconnect Microsoft Account
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-blue-900 mb-2">Want to use a different account?</h3>
                        <p class="text-sm text-blue-700">
                            1. Click "Disconnect Microsoft Account" above<br>
                            2. You'll be redirected to the calendar page<br>
                            3. Click "Connect with Microsoft 365" again<br>
                            4. Login with your personal @outlook.com account
                        </p>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-unlink text-4xl text-slate-300 mb-4"></i>
                        <p class="text-slate-600 mb-4">No Microsoft account connected</p>
                        <a href="{{ route('calendar.connect') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            <i class="fab fa-microsoft mr-2"></i>
                            Connect Microsoft 365
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="{{ route('calendar.index') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Calendar
            </a>
        </div>
    </div>
</div>
@endsection