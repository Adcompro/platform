<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        {{-- Dynamically load the selected font --}}
        @php
            $themeSettings = \App\Models\SimplifiedThemeSetting::where('is_active', true)
                ->whereNull('company_id')
                ->first();

            if (!$themeSettings) {
                $themeSettings = \App\Models\SimplifiedThemeSetting::createDefault(null);
            }

            $fontFamily = $themeSettings->font_family ?? 'inter';
        @endphp

        @if($fontFamily === 'inter')
            <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
        @elseif($fontFamily === 'roboto')
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
        @elseif($fontFamily === 'poppins')
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        @elseif($fontFamily === 'opensans')
            <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        @endif

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @if(file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="{{ asset('build/assets/app-BsOEeUTv.css') }}">
            <script src="{{ asset('build/assets/app-DtCVKgHt.js') }}" defer></script>
        @endif

        {{-- Load Theme CSS Variables --}}
        @if($themeSettings)
            {!! $themeSettings->getCssVariables() !!}
        @endif

        <style>
            body {
                font-family: var(--theme-font-family, 'Inter', sans-serif);
                font-size: var(--theme-font-size, 14px);
            }

            .login-gradient {
                background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-accent) 50%, color-mix(in srgb, var(--theme-primary) 50%, white) 100%);
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: var(--theme-border-radius, 0.5rem);
                box-shadow: var(--theme-card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.1));
            }

            .floating-animation {
                animation: float 6s ease-in-out infinite;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }

            .input-icon {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--theme-text-muted);
                pointer-events: none;
            }

            .input-with-icon {
                padding-left: 40px;
            }

            .btn-gradient {
                background: var(--theme-primary);
                transition: all 0.3s ease;
                border-radius: var(--theme-border-radius, 0.5rem);
            }

            .btn-gradient:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(var(--theme-primary-rgb), 0.4);
                filter: brightness(1.15);
            }

            .login-pattern {
                background-image:
                    radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            }

            .login-input {
                border-radius: var(--theme-border-radius, 0.5rem);
                font-size: var(--theme-font-size, 14px);
            }

            .login-input:focus {
                border-color: var(--theme-primary);
                ring-color: var(--theme-primary);
            }

            .forgot-link {
                color: var(--theme-primary);
            }

            .forgot-link:hover {
                color: var(--theme-accent);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 login-gradient login-pattern">
            <!-- Logo -->
            <div class="mb-8 floating-animation">
                <a href="/">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="h-20 w-auto">
                </a>
            </div>

            <!-- Login Card -->
            <div class="w-full sm:max-w-md px-8 py-8 glass-card shadow-2xl overflow-hidden sm:rounded-2xl">
                <!-- Welcome Text -->
                <div class="mb-6 text-center">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h1>
                    <p class="text-gray-600">Sign in to your account to continue</p>
                </div>

                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-white/80 text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>
