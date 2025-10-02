@extends('layouts.app')

@section('title', 'Apple iCloud Calendar Setup')

@section('content')
{{-- Sticky Header - Exact Copy Theme Settings --}}
<div class="bg-white border-b border-gray-200 sticky z-30" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Apple iCloud Calendar Setup</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Connect your Apple iCloud calendar using an app-specific password</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" form="apple-setup-form" id="header-save-btn"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fab fa-apple mr-1.5"></i>
                    Connect iCloud
                </button>
                <a href="{{ route('calendar.providers.index') }}"
                   class="header-btn-secondary"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-arrow-left mr-1.5"></i>
                    Back
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main Content - Exact Copy Theme Settings --}}
<div style="padding: 1.5rem 2rem;">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.1); border-color: var(--theme-success); color: var(--theme-success); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span style="font-size: var(--theme-font-size);">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border-color: var(--theme-danger); color: var(--theme-danger); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span style="font-size: var(--theme-font-size);">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border-color: var(--theme-danger); color: var(--theme-danger); padding: var(--theme-card-padding);">
            <div class="flex items-center">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div style="font-size: var(--theme-font-size);">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Setup Instructions --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">
                <i class="fab fa-apple mr-2"></i>Apple iCloud Calendar Setup Instructions
            </h2>
        </div>
        <div style="padding: var(--theme-card-padding);">
            <div class="space-y-4">
                <div class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-0.5"
                          style="background-color: var(--theme-primary); color: white; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600;">1</span>
                    <div>
                        <p style="font-weight: 600; font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 0.25rem;">Enable Two-Factor Authentication:</p>
                        <p style="font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 0.5rem;">First, ensure your Apple ID has two-factor authentication enabled:</p>
                        <ol style="margin-left: 1rem; font-size: var(--theme-font-size); color: var(--theme-text); list-style-type: decimal;">
                            <li>• Go to <a href="https://appleid.apple.com/" target="_blank" style="color: var(--theme-primary); text-decoration: underline;">appleid.apple.com</a></li>
                            <li>• Sign in with your Apple ID</li>
                            <li>• Check "Sign-In and Security" → "Two-Factor Authentication" is ON</li>
                        </ol>
                    </div>
                </div>

                <div class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-0.5"
                          style="background-color: var(--theme-primary); color: white; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600;">2</span>
                    <div>
                        <p style="font-weight: 600; font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 0.25rem;">Create an App-Specific Password:</p>
                        <ol style="margin-left: 1rem; font-size: var(--theme-font-size); color: var(--theme-text); list-style-type: decimal;">
                            <li>• In Apple ID settings, go to "Sign-In and Security" → "App-Specific Passwords"</li>
                            <li>• Click "Generate an app-specific password"</li>
                            <li>• Enter a label like <code style="background-color: rgba(var(--theme-primary-rgb), 0.1); padding: 0.125rem 0.25rem; border-radius: 0.25rem;">Progress Calendar Integration</code></li>
                            <li>• Copy the generated password (format: xxxx-xxxx-xxxx-xxxx)</li>
                            <li>• <strong>Important:</strong> This password can only be viewed once!</li>
                        </ol>
                    </div>
                </div>

                <div class="flex items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-0.5"
                          style="background-color: var(--theme-primary); color: white; font-size: calc(var(--theme-font-size) - 2px); font-weight: 600;">3</span>
                    <div>
                        <p style="font-weight: 600; font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 0.25rem;">Test Your Setup:</p>
                        <p style="font-size: var(--theme-font-size); color: var(--theme-text);">Use your full Apple ID email and the app-specific password below.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Setup Form --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">
                <i class="fas fa-key mr-2"></i>Enter Your Apple iCloud Credentials
            </h2>
        </div>
        <div style="padding: var(--theme-card-padding);">
            <form id="apple-setup-form" method="POST" action="{{ route('calendar.providers.apple.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="username" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        Apple ID Email <span style="color: var(--theme-danger);">*</span>
                    </label>
                    <input type="email"
                           id="username"
                           name="username"
                           value="{{ old('username') }}"
                           placeholder="your-apple-id@icloud.com"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           required>
                    @error('username')
                        <p style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                        App-Specific Password <span style="color: var(--theme-danger);">*</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="xxxx-xxxx-xxxx-xxxx"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                           required>
                    <p style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem;">
                        This should be the 16-character app-specific password from Apple, not your regular Apple ID password.
                    </p>
                    @error('password')
                        <p style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg p-4" style="background-color: rgba(var(--theme-warning-rgb), 0.1); border: 1px solid var(--theme-warning);">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle mr-3 mt-0.5" style="color: var(--theme-warning);"></i>
                        <div style="color: var(--theme-warning);">
                            <p style="font-weight: 600; font-size: var(--theme-font-size); margin-bottom: 0.5rem;">Important Notes:</p>
                            <ul style="font-size: calc(var(--theme-font-size) - 2px); space-y: 0.25rem;">
                                <li>• Keep your credentials secure and don't share them</li>
                                <li>• Your credentials are encrypted and stored securely</li>
                                <li>• You can revoke the app-specific password at any time from your Apple ID settings</li>
                                <li>• The system will test your credentials against Apple's CalDAV server</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Additional Help --}}
    <div class="mt-6 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Need Help?</h2>
        </div>
        <div style="padding: var(--theme-card-padding);">
            <p style="font-size: var(--theme-font-size); color: var(--theme-text); margin-bottom: 0.5rem;">If you encounter issues, make sure:</p>
            <ul style="margin-left: 1rem; font-size: var(--theme-font-size); color: var(--theme-text); space-y: 0.25rem;">
                <li>• Two-factor authentication is enabled on your Apple ID</li>
                <li>• You're using your full Apple ID email address</li>
                <li>• You're using an app-specific password (not your regular Apple ID password)</li>
                <li>• The app-specific password was generated recently</li>
                <li>• Your Apple ID has calendar access enabled</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();

    // Header save button
    const saveBtn = document.getElementById('header-save-btn');
    if (saveBtn) {
        saveBtn.style.backgroundColor = primaryColor;
        saveBtn.style.color = 'white';
        saveBtn.style.border = 'none';
        saveBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Form inputs focus styling
    const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = primaryColor;
            this.style.outline = 'none';
        });

        input.addEventListener('blur', function() {
            this.style.borderColor = 'rgba(203, 213, 225, 0.6)';
        });
    });
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush

@endsection