<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Project Manager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    {{-- Dynamically load the selected font --}}
    @php
        $fontFamily = isset($themeSettings) ? $themeSettings->font_family : 'inter';
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
    
    <!-- Application Styles & Scripts -->
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app-BsOEeUTv.css') }}">
        <script src="{{ asset('build/assets/app-DtCVKgHt.js') }}" defer></script>
    @endif
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SortableJS for drag & drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <!-- jQuery UI as fallback for sortable -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <!-- Alpine.js for reactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Simplified Theme CSS Variables --}}
    @php
        // Get global theme settings (not company-specific)
        $themeSettings = \App\Models\SimplifiedThemeSetting::where('is_active', true)
            ->whereNull('company_id')
            ->first();
        
        if (!$themeSettings) {
            $themeSettings = \App\Models\SimplifiedThemeSetting::createDefault(null);
        }
        
        // Get AI Settings for conditional display
        $aiSettings = \App\Models\AiSetting::current();
    @endphp
    
    @if($themeSettings)
        {!! $themeSettings->getCssVariables() !!}
    @else
        <style>
            /* Fallback CSS variables if theme not loaded */
            :root {
                --theme-card-padding: 1rem;
                --theme-border-radius: 0.5rem;
                --theme-card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
                --theme-header-height: 4rem;
                --theme-font-size: 14px;
            }
        </style>
    {{-- View-specific Theme Styles --}}
    @stack('styles')
    
    <style>
        /* DEBUG: Show current theme values */
        body::before {
            content: "Card Padding: var(--theme-card-padding) | Border Radius: var(--theme-border-radius)";
            position: fixed;
            bottom: 0;
            left: 0;
            background: #000;
            color: #0f0;
            padding: 5px;
            font-size: 10px;
            z-index: 9999;
            display: none; /* Set to 'block' to debug */
        }
    </style>
    
    <style>
        /* Apply theme variables to common elements */
        /* Set base font size from theme - MUST be on :root for rem units to work */
        :root {
            font-size: var(--theme-font-size) !important;
        }
        
        html {
            font-size: var(--theme-font-size) !important;
        }
        
        body {
            font-family: var(--theme-font-family) !important;
            font-size: var(--theme-font-size) !important;
            line-height: var(--theme-line-height);
            color: var(--theme-text);
            background-color: var(--theme-bg);
        }
        
        /* Override ALL Tailwind text sizes with calc based on theme font size */
        .text-xs { font-size: calc(var(--theme-font-size) * 0.75) !important; }
        .text-sm { font-size: calc(var(--theme-font-size) * 0.875) !important; }
        .text-base { font-size: var(--theme-font-size) !important; }
        .text-lg { font-size: calc(var(--theme-font-size) * 1.125) !important; }
        .text-xl { font-size: calc(var(--theme-font-size) * 1.25) !important; }
        .text-2xl { font-size: calc(var(--theme-font-size) * 1.5) !important; }
        .text-3xl { font-size: calc(var(--theme-font-size) * 1.875) !important; }
        .text-4xl { font-size: calc(var(--theme-font-size) * 2.25) !important; }
        .text-5xl { font-size: calc(var(--theme-font-size) * 3) !important; }
        .text-6xl { font-size: calc(var(--theme-font-size) * 3.75) !important; }
        
        /* Apply header height from theme */
        .nav-header {
            height: var(--theme-header-height, 4rem);
        }
        
        /* Apply card styles from theme */
        .theme-card {
            padding: var(--theme-card-padding);
            border-radius: var(--theme-border-radius);
            box-shadow: var(--theme-card-shadow);
        }
        
        /* Global theme card class - use this for all cards */
        .theme-card,
        [data-theme-card] {
            padding: var(--theme-card-padding) !important;
            border-radius: var(--theme-border-radius) !important;
            box-shadow: var(--theme-card-shadow) !important;
        }
        
        /* Theme button styles */
        .theme-btn-primary {
            background-color: var(--theme-primary) !important;
            color: white !important;
            border-radius: var(--theme-border-radius) !important;
            padding: var(--theme-button-padding-y) var(--theme-button-padding-x) !important;
            font-size: var(--theme-button-font-size) !important;
        }
        
        .theme-btn-primary:hover {
            filter: brightness(0.9);
        }
        
        .theme-btn-secondary {
            background-color: var(--theme-accent) !important;
            color: white !important;
            border-radius: var(--theme-border-radius) !important;
            padding: var(--theme-button-padding-y) var(--theme-button-padding-x) !important;
            font-size: var(--theme-button-font-size) !important;
        }
        
        .theme-btn-danger {
            background-color: var(--theme-danger) !important;
            color: white !important;
            border-radius: var(--theme-border-radius) !important;
            padding: var(--theme-button-padding-y) var(--theme-button-padding-x) !important;
            font-size: var(--theme-button-font-size) !important;
        }
        
        /* Override border-radius for rounded elements */
        .rounded-xl,
        .rounded-lg,
        .rounded-md,
        .rounded {
            border-radius: var(--theme-border-radius) !important;
        }
        
        /* Override shadow for shadowed elements */
        .shadow-sm,
        .shadow,
        .shadow-md,
        .shadow-lg {
            box-shadow: var(--theme-card-shadow) !important;
        }
        
        /* Keep inner content untouched */
        .rounded-xl > *,
        .rounded-lg > * {
            padding: revert;
        }
        
        /* Ensure all elements inherit the font */
        * {
            font-family: inherit;
        }
        
        /* Headers use calc based on theme font-size */
        h1 { font-size: calc(var(--theme-font-size) * 2.5) !important; }
        h2 { font-size: calc(var(--theme-font-size) * 2) !important; }
        h3 { font-size: calc(var(--theme-font-size) * 1.75) !important; }
        h4 { font-size: calc(var(--theme-font-size) * 1.5) !important; }
        h5 { font-size: calc(var(--theme-font-size) * 1.25) !important; }
        h6 { font-size: calc(var(--theme-font-size) * 1.125) !important; }
        
        /* Text color utilities */
        .text-muted {
            color: var(--theme-text-muted);
        }
        
        .btn-primary {
            background-color: var(--theme-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--theme-primary);
            filter: brightness(0.9);
        }
        
        .btn-danger {
            background-color: var(--theme-danger);
            color: white;
        }
        
        .text-primary {
            color: var(--theme-primary);
        }
        
        .bg-primary {
            background-color: var(--theme-primary);
        }
        
        .border-primary {
            border-color: var(--theme-primary);
        }
        
        .sidebar {
            width: var(--theme-sidebar-width);
        }
        
        .card {
            border-radius: var(--theme-border-radius);
            box-shadow: var(--theme-card-shadow);
        }
        
        /* Apply layout variables */
        .nav-container {
            padding: var(--theme-header-padding, 1rem) 1rem;
        }
    </style>
    
    <style>
        /* Teamleader-style menu styling using theme variables */
        * {
            transition: all 0.2s ease;
        }

        /* Top navigation tab styling */
        .tab-item {
            position: relative;
            padding: calc(var(--theme-header-height) * 0.1875) 1.5rem; /* Responsive padding based on header height */
            color: var(--theme-text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: var(--theme-font-size);
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
            line-height: calc(var(--theme-header-height) * 0.625); /* Responsive line height */
        }

        .tab-item:hover {
            color: var(--theme-text);
            background-color: rgba(var(--theme-primary-rgb), 0.05);
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .tab-item.active {
            color: var(--theme-primary);
            border-bottom-color: var(--theme-nav-active);
            font-weight: 600;
            background-color: rgba(var(--theme-primary-rgb), 0.1);
            border-radius: 0.5rem 0.5rem 0 0;
        }

        /* Badge styling for navigation */
        .tab-item .bg-red-500 {
            background-color: var(--theme-danger) !important;
        }

        /* Sidebar styling using theme variables with maximum specificity */
        aside.w-16.teamleader-sidebar.fixed {
            background-color: var(--theme-sidebar-bg) !important;
            color: var(--theme-sidebar-text) !important;
            border-right: 1px solid rgba(0, 0, 0, 0.1) !important;
        }

        aside.w-16.teamleader-sidebar.fixed nav {
            background: transparent !important;
        }

        aside.w-16.teamleader-sidebar.fixed nav button,
        aside.w-16.teamleader-sidebar.fixed nav a {
            color: var(--theme-sidebar-text) !important;
            transition: all 0.2s ease !important;
            background: transparent !important;
        }

        aside.w-16.teamleader-sidebar.fixed nav button:hover,
        aside.w-16.teamleader-sidebar.fixed nav a:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        aside.w-16.teamleader-sidebar.fixed nav button.active,
        aside.w-16.teamleader-sidebar.fixed nav a.active {
            background-color: var(--theme-sidebar-active) !important;
            color: white !important;
        }

        /* Force sidebar background with even higher specificity */
        .w-16.teamleader-sidebar[class*="fixed"] {
            background: var(--theme-sidebar-bg) !important;
        }

        /* DEBUG: Ultimate fallback CSS - this should definitely work */
        aside {
            background: var(--theme-sidebar-bg) !important;
        }

        /* Target all asides with teamleader class */
        aside[class*="teamleader"] {
            background-color: var(--theme-sidebar-bg) !important;
            border-right-color: var(--theme-sidebar-bg) !important;
        }

        /* Header styling */
        header {
            height: var(--theme-header-height);
        }

        /* Sidebar positioning */
        aside {
            top: var(--theme-header-height) !important;
        }

        /* Main content styling */
        main {
            min-height: calc(100vh - var(--theme-header-height));
        }

        /* Header tab navigation styling - Dynamic based on nav style */
        a.tab-item {
            color: var(--theme-text) !important;
            font-size: var(--theme-font-size) !important;
            font-family: var(--theme-font-family) !important;
            padding: 0.5rem 1rem !important;
            transition: all 0.2s ease !important;
            text-decoration: none !important;
            position: relative !important;
            display: inline-block !important;
        }

        /* Default fallback for all navigation styles */
        .nav-tabs a.tab-item,
        .nav-pills a.tab-item,
        .nav-underline a.tab-item {
            background-color: transparent !important;
            border: 1px solid transparent !important;
        }


        /* Tabs Style */
        div.nav-tabs a.tab-item {
            border-radius: 0.375rem 0.375rem 0 0 !important;
            border: 1px solid rgba(var(--theme-text-muted-rgb), 0.3) !important;
            border-bottom: 1px solid transparent !important;
            background-color: rgba(var(--theme-bg-rgb), 0.5) !important;
        }

        div.nav-tabs a.tab-item:hover {
            background-color: rgba(var(--theme-nav-active-rgb, 221, 85, 85), 0.1) !important;
            color: var(--theme-nav-active, #dd5555) !important;
            border-color: var(--theme-nav-active, #dd5555) !important;
        }

        div.nav-tabs a.tab-item.active {
            background-color: var(--theme-nav-active, #dd5555) !important;
            color: white !important;
            border-color: var(--theme-nav-active, #dd5555) !important;
            border-bottom-color: var(--theme-nav-active, #dd5555) !important;
        }

        /* Pills Style */
        div.nav-pills a.tab-item {
            border-radius: 1.5rem !important;
            margin: 0 0.25rem !important;
            background-color: rgba(var(--theme-text-muted-rgb), 0.1) !important;
            border: 1px solid transparent !important;
        }

        div.nav-pills a.tab-item:hover {
            background-color: rgba(var(--theme-nav-active-rgb, 221, 85, 85), 0.2) !important;
            color: var(--theme-nav-active, #dd5555) !important;
        }

        div.nav-pills a.tab-item.active {
            background-color: var(--theme-nav-active, #dd5555) !important;
            color: white !important;
        }

        /* Underline Style */
        div.nav-underline a.tab-item {
            border-radius: 0 !important;
            border: none !important;
            border-bottom: 3px solid transparent !important;
            padding-bottom: 0.75rem !important;
            background-color: transparent !important;
        }

        div.nav-underline a.tab-item:hover {
            color: var(--theme-nav-active, #dd5555) !important;
            border-bottom-color: rgba(var(--theme-nav-active-rgb, 221, 85, 85), 0.5) !important;
            background-color: rgba(var(--theme-nav-active-rgb, 221, 85, 85), 0.05) !important;
        }

        div.nav-underline a.tab-item.active {
            color: var(--theme-nav-active, #dd5555) !important;
            border-bottom-color: var(--theme-nav-active, #dd5555) !important;
            background-color: transparent !important;
        }

        /* Dynamic active tab color using PHP variable directly */
        @php
            $activeTabColor = $themeSettings->top_nav_active_color ?: '#dd5555';
            $activeTabColorRgb = implode(', ', [
                hexdec(substr(ltrim($activeTabColor, '#'), 0, 2)),
                hexdec(substr(ltrim($activeTabColor, '#'), 2, 2)),
                hexdec(substr(ltrim($activeTabColor, '#'), 4, 2))
            ]);
        @endphp

        div.nav-tabs a.tab-item.active,
        div.nav-pills a.tab-item.active {
            background-color: {{ $activeTabColor }} !important;
            color: white !important;
        }

        div.nav-tabs a.tab-item:hover,
        div.nav-pills a.tab-item:hover,
        div.nav-underline a.tab-item:hover {
            color: {{ $activeTabColor }} !important;
            background-color: rgba({{ $activeTabColorRgb }}, 0.1) !important;
        }

        div.nav-underline a.tab-item.active {
            background-color: transparent !important;
            border-bottom-color: {{ $activeTabColor }} !important;
            color: {{ $activeTabColor }} !important;
        }

        /* Content alignment classes */
        .content-align-left {
            margin-left: 196px !important; /* sidebar(80px) + header-padding(24px) + logo(~60px) + nav-margin(32px) */
            max-width: calc(100vw - 276px) !important; /* Full width minus total left offset */
            padding-left: 0 !important;
            padding-right: 2rem !important;
        }

        .content-full-width {
            margin-left: 0 !important;
        }

        /* Search input and component styling */
        .header-component {
            background-color: rgba(var(--theme-topbar-bg-rgb), 0.9) !important;
            border: 1px solid rgba(var(--theme-text-muted-rgb), 0.3) !important;
            color: var(--theme-text) !important;
            font-size: var(--theme-font-size) !important;
            font-family: var(--theme-font-family) !important;
        }

        .header-component:focus {
            border-color: var(--theme-primary) !important;
            box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.1) !important;
        }
        
        /* Timer component responsiveness - already handled with sm:flex class */
        
        @php
            // Theme and AI settings already loaded in head section
            // Debug: Show current header style
            $headerStyle = $themeSettings ? $themeSettings->table_header_style : 'none';
        @endphp
        
        /* Current table header style: {{ $headerStyle }} */
        /* Theme settings found: {{ $themeSettings ? 'YES' : 'NO' }} */
        /* Table header style value: {{ $themeSettings ? $themeSettings->table_header_style : 'NULL' }} */
        
        /* Table striping and hover effects */
        @if($themeSettings && $themeSettings->table_striped)
        tbody tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        @endif
        
        /* Table hover effects */
        @if($themeSettings && $themeSettings->table_hover_effect === 'light')
        tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        @elseif($themeSettings && $themeSettings->table_hover_effect === 'dark')
        tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.08);
        }
        @elseif($themeSettings && $themeSettings->table_hover_effect === 'colored')
        tbody tr:hover {
            background-color: color-mix(in srgb, var(--theme-primary) 5%, transparent);
        }
        @endif
        
        /* Table header styles */
        @if($themeSettings && $themeSettings->table_header_style === 'light')
        thead {
            background-color: #f9fafb !important;
        }
        thead th {
            font-weight: 500 !important;
            background-color: #f9fafb !important;
        }
        @elseif($themeSettings && $themeSettings->table_header_style === 'bold')
        thead {
            background-color: #e5e7eb !important;
        }
        thead th {
            font-weight: 700 !important;
            background-color: #e5e7eb !important;
        }
        @elseif($themeSettings && $themeSettings->table_header_style === 'dark')
        thead {
            background-color: var(--theme-text) !important;
        }
        thead th {
            color: white !important;
            background-color: var(--theme-text) !important;
        }
        @elseif($themeSettings && $themeSettings->table_header_style === 'colored')
        thead {
            background-color: var(--theme-primary) !important;
        }
        thead th {
            color: white !important;
            background-color: var(--theme-primary) !important;
        }
        @endif
    </style>
    @endif
</head>
<body class="antialiased bg-gradient-to-br from-slate-50 via-white to-slate-50" style="font-family: 'Open Sans', sans-serif;" x-data="{
    activeSection: @if(request()->routeIs('calendar.*')) 'calendar' @elseif(request()->routeIs('projects.*') || request()->routeIs('services.*')) 'projects' @elseif(request()->routeIs('customers.*') || request()->routeIs('contacts.*')) 'crm' @elseif(request()->routeIs('time-entries.*')) 'timetracking' @elseif(request()->routeIs('invoices.*')) 'invoices' @elseif(request()->routeIs('quick-reports.*')) 'reports' @elseif(request()->routeIs('settings.*') || request()->routeIs('users.*') || request()->routeIs('companies.*')) 'configuration' @else localStorage.getItem('activeMenuSection') || 'dashboard' @endif,
    sidebarOpen: true
}" x-init="$watch('activeSection', value => localStorage.setItem('activeMenuSection', value))">
    @php
        // Ensure AI Settings are available throughout the layout
        if (!isset($aiSettings)) {
            $aiSettings = \App\Models\AiSetting::current();
        }
    @endphp
    <div class="min-h-screen flex flex-col">
        
        <!-- Top Header with Logo and User Menu -->
        <header class="sticky top-0 z-40" style="margin-left: 5rem; background-color: var(--theme-topbar-bg) !important; border-bottom: 1px solid rgba(var(--theme-text-muted-rgb), 0.2) !important; font-family: var(--theme-font-family) !important; height: var(--theme-header-height) !important; min-height: var(--theme-header-height) !important; max-height: var(--theme-header-height) !important;">
            <div class="flex items-center justify-between px-6" style="height: var(--theme-header-height) !important; min-height: var(--theme-header-height) !important;">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="group">
                        <img src="{{ asset('logo.png') }}"
                             alt="Logo"
                             class="w-auto group-hover:scale-105 transition-transform duration-200"
                             style="height: calc(var(--theme-header-height) * 0.625); max-height: calc(var(--theme-header-height) * 0.625); object-fit: contain;">
                    </a>
                </div>

                <!-- Dynamic Top Navigation Tabs -->
                <nav class="flex-1 mx-8">
                    <div class="flex space-x-8 nav-{{ $themeSettings->top_nav_style ?: 'tabs' }}"
                         data-nav-style="{{ $themeSettings->top_nav_style ?: 'tabs' }}">
                        <!-- Dashboard Tabs -->
                        <div x-show="activeSection === 'dashboard'" x-transition class="flex space-x-6">
                            <a href="{{ route('dashboard') }}"
                               class="tab-item @if(request()->routeIs('dashboard')) active @endif">
                                Overview
                            </a>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <a href="{{ route('invoices.dashboard') }}"
                               class="tab-item @if(request()->routeIs('invoices.dashboard')) active @endif">
                                Invoices
                            </a>
                            @endif
                        </div>

                        <!-- Projects Tabs -->
                        <div x-show="activeSection === 'projects'" x-transition class="flex space-x-6">
                            <a href="{{ route('projects.index') }}"
                               class="tab-item @if(request()->routeIs('projects.*')) active @endif">
                                Overview
                            </a>
                            <a href="{{ route('services.index') }}"
                               class="tab-item @if(request()->routeIs('services.*')) active @endif">
                                Services
                            </a>
                        </div>

                        <!-- CRM Tabs -->
                        <div x-show="activeSection === 'crm'" x-transition class="flex space-x-6">
                            <a href="{{ route('customers.index') }}"
                               class="tab-item @if(request()->routeIs('customers.*')) active @endif">
                                Customers
                            </a>
                            <a href="{{ route('contacts.index') }}"
                               class="tab-item @if(request()->routeIs('contacts.*')) active @endif">
                                Contacts
                            </a>
                        </div>

                        <!-- Time Tracking Tabs -->
                        <div x-show="activeSection === 'timetracking'" x-transition class="flex space-x-6">
                            <a href="{{ route('time-entries.index') }}"
                               class="tab-item @if(request()->routeIs('time-entries.*') && !request()->routeIs('time-entries.approvals')) active @endif">
                                Overview
                            </a>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                            <a href="{{ route('time-entries.approvals') }}"
                               class="tab-item @if(request()->routeIs('time-entries.approvals')) active @endif">
                                Approvals
                                @php
                                    $pendingCount = \App\Models\TimeEntry::pending()
                                        ->when(Auth::user()->role === 'admin', function($q) {
                                            $q->where('company_id', Auth::user()->company_id);
                                        })
                                        ->count();
                                @endphp
                                @if($pendingCount > 0)
                                    <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $pendingCount }}</span>
                                @endif
                            </a>
                            @endif
                        </div>

                        <!-- Calendar Tabs -->
                        <div x-show="activeSection === 'calendar'" x-transition class="flex space-x-6">
                            <a href="{{ route('calendar.index') }}"
                               class="tab-item @if(request()->routeIs('calendar.index')) active @endif">
                                Calendar
                            </a>

                        </div>

                        <!-- Invoices Tabs -->
                        <div x-show="activeSection === 'invoices'" x-transition class="flex space-x-6">
                            <a href="{{ route('invoices.index') }}"
                               class="tab-item @if(request()->routeIs('invoices.*') && !request()->routeIs('invoices.dashboard') && !request()->routeIs('invoices.ai-test*') && !request()->routeIs('invoice-templates.*')) active @endif">
                                Overview
                            </a>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                            <a href="{{ route('invoices.dashboard') }}"
                               class="tab-item @if(request()->routeIs('invoices.dashboard')) active @endif">
                                Dashboard
                            </a>
                            @endif
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                            <a href="{{ route('invoice-templates.index') }}"
                               class="tab-item @if(request()->routeIs('invoice-templates.*')) active @endif">
                                Templates
                            </a>
                            @endif
                            <a href="{{ route('invoices.ai-test') }}"
                               class="tab-item @if(request()->routeIs('invoices.ai-test*')) active @endif">
                                AI Test
                            </a>
                        </div>

                        <!-- Reports Tabs -->
                        @if(in_array(Auth::user()->role ?? '', ['super_admin', 'admin', 'project_manager']))
                        <div x-show="activeSection === 'reports'" x-transition class="flex space-x-6">
                            <a href="{{ route('reports.quick-reports') }}"
                               class="tab-item @if(request()->routeIs('reports.*')) active @endif">
                                Overview
                            </a>
                            <a href="{{ route('project-intelligence.index') }}"
                               class="tab-item @if(request()->routeIs('project-intelligence.*')) active @endif">
                                AI Intelligence
                            </a>
                            <a href="{{ route('settings.ai-usage') }}"
                               class="tab-item @if(request()->routeIs('settings.ai-usage')) active @endif">
                                AI Usage
                            </a>
                        </div>
                        @endif

                        <!-- Configuration Tabs -->
                        @if(in_array(Auth::user()->role ?? '', ['super_admin', 'admin']))
                        <div x-show="activeSection === 'configuration'" x-transition class="flex space-x-6">
                            <a href="{{ route('users.index') }}"
                               class="tab-item @if(request()->routeIs('users.*')) active @endif">
                                Users
                            </a>
                            <a href="{{ route('companies.index') }}"
                               class="tab-item @if(request()->routeIs('companies.*')) active @endif">
                                Companies
                            </a>
                            <a href="{{ route('project-templates.index') }}"
                               class="tab-item @if(request()->routeIs('project-templates.*')) active @endif">
                                Templates
                            </a>
                            @if(Auth::user()->role === 'super_admin')
                            <a href="{{ route('system.status') }}"
                               class="tab-item @if(request()->routeIs('system.status')) active @endif">
                                Status
                            </a>
                            @endif
                            <a href="{{ route('settings.index') }}"
                               class="tab-item @if(request()->routeIs('settings.index')) active @endif">
                                Settings
                            </a>
                            <a href="{{ route('settings.theme') }}"
                               class="tab-item @if(request()->routeIs('settings.theme*')) active @endif">
                                Theme
                            </a>
                            <a href="{{ route('ai-settings.index') }}"
                               class="tab-item @if(request()->routeIs('ai-settings.*')) active @endif">
                                AI Settings
                            </a>
                            <a href="{{ route('service-categories.index') }}"
                               class="tab-item @if(request()->routeIs('service-categories.*')) active @endif">
                                Categories
                            </a>
                        </div>
                        @endif
                    </div>
                </nav>

                <!-- Right side - Search, User Menu and Timer -->
                <div class="flex items-center space-x-4 flex-shrink-0">
                    <!-- Search -->
                    <div class="hidden md:block">
                        <input type="text" placeholder="Search..."
                               class="w-64 px-3 py-1.5 rounded-lg transition-all header-component">
                    </div>

                    <!-- Timer Component -->
                    <div id="timerComponent" class="flex items-center rounded-lg px-3 py-1.5 header-component">
                        <div id="timerDisplay" class="font-mono font-semibold mr-2 min-w-[70px]">00:00:00</div>
                        <div class="flex items-center space-x-1">
                            <button id="startTimer" onclick="toggleTimer()" class="p-1 bg-green-500 text-white rounded hover:bg-green-600 transition-all">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            <button id="pauseTimer" onclick="toggleTimer()" class="p-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition-all hidden">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            <button id="stopTimer" onclick="stopTimer()" class="p-1 bg-red-500 text-white rounded hover:bg-red-600 transition-all">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"/>
                                </svg>
                            </button>
                            <button id="saveTimer" onclick="openQuickTimeEntry()" class="p-1 bg-slate-600 text-white rounded hover:bg-slate-700 transition-all">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center px-3 py-1.5 rounded-lg header-component">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center mr-2" style="background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent));">
                            <span class="text-white font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <span class="font-medium hidden sm:inline">{{ Auth::user()->name }}</span>
                    </div>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg transition-all header-component"
                                style="color: var(--theme-text-muted) !important;"
                                onmouseover="this.style.color='var(--theme-danger)'; this.style.backgroundColor='rgba(var(--theme-danger-rgb), 0.1)';"
                                onmouseout="this.style.color='var(--theme-text-muted)'; this.style.backgroundColor='rgba(var(--theme-bg-rgb), 0.9)';">
                            <i class="fas fa-sign-out-alt mr-1.5"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Runtime Theme Application Script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get theme variables from CSS
            const rootStyles = getComputedStyle(document.documentElement);
            const activeColor = rootStyles.getPropertyValue('--theme-nav-active').trim();
            const navStyle = rootStyles.getPropertyValue('--theme-nav-style').trim();
            const textColor = rootStyles.getPropertyValue('--theme-text').trim();
            const fontSize = rootStyles.getPropertyValue('--theme-font-size').trim();

            console.log('Theme values:', { activeColor, navStyle, textColor, fontSize });

            // Apply styles to all tab items
            const tabItems = document.querySelectorAll('.tab-item');

            tabItems.forEach(function(tab) {
                // Base styles for all tabs
                tab.style.setProperty('color', textColor, 'important');
                tab.style.setProperty('font-size', fontSize, 'important');
                tab.style.setProperty('padding', '0.5rem 1rem', 'important');
                tab.style.setProperty('text-decoration', 'none', 'important');
                tab.style.setProperty('transition', 'all 0.2s ease', 'important');
                tab.style.setProperty('display', 'inline-block', 'important');

                // Reset styles
                tab.style.setProperty('background-color', 'transparent', 'important');
                tab.style.setProperty('border', 'none', 'important');
                tab.style.setProperty('border-radius', '0', 'important');

                // Apply navigation style
                if (navStyle === 'pills') {
                    tab.style.setProperty('border-radius', '1.5rem', 'important');
                    tab.style.setProperty('background-color', 'rgba(0,0,0,0.05)', 'important');
                    tab.style.setProperty('margin', '0 0.125rem', 'important');
                } else if (navStyle === 'underline') {
                    tab.style.setProperty('border-bottom', '2px solid transparent', 'important');
                    tab.style.setProperty('padding-bottom', '0.75rem', 'important');
                } else { // tabs
                    tab.style.setProperty('border-radius', '0.375rem 0.375rem 0 0', 'important');
                    tab.style.setProperty('border', '1px solid rgba(0,0,0,0.1)', 'important');
                    tab.style.setProperty('border-bottom', 'none', 'important');
                    tab.style.setProperty('background-color', 'rgba(0,0,0,0.02)', 'important');
                }

                // Apply active state
                if (tab.classList.contains('active')) {
                    if (navStyle === 'underline') {
                        tab.style.setProperty('color', activeColor, 'important');
                        tab.style.setProperty('border-bottom-color', activeColor, 'important');
                        tab.style.setProperty('background-color', 'transparent', 'important');
                    } else {
                        tab.style.setProperty('background-color', activeColor, 'important');
                        tab.style.setProperty('color', 'white', 'important');
                        if (navStyle === 'tabs') {
                            tab.style.setProperty('border-color', activeColor, 'important');
                        }
                    }
                }

                // Add hover effects
                tab.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('active')) {
                        if (navStyle === 'underline') {
                            this.style.setProperty('color', activeColor, 'important');
                            this.style.setProperty('border-bottom-color', activeColor, 'important');
                        } else {
                            this.style.setProperty('background-color', activeColor, 'important');
                            this.style.setProperty('color', 'white', 'important');
                        }
                    }
                });

                tab.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        // Reset to base state
                        this.style.setProperty('color', textColor, 'important');
                        if (navStyle === 'pills') {
                            this.style.setProperty('background-color', 'rgba(0,0,0,0.05)', 'important');
                        } else if (navStyle === 'underline') {
                            this.style.setProperty('border-bottom-color', 'transparent', 'important');
                            this.style.setProperty('background-color', 'transparent', 'important');
                        } else { // tabs
                            this.style.setProperty('background-color', 'rgba(0,0,0,0.02)', 'important');
                        }
                    }
                });
            });
        });
        </script>
        
        <!-- Main Content Area with Teamleader-style Sidebar -->
        <div class="flex-1 flex">
            <!-- Teamleader-style Narrow Sidebar -->
            <aside class="w-20 teamleader-sidebar border-r fixed top-0 bottom-0 left-0 z-50"
                   style="background-color: var(--theme-sidebar-bg) !important; color: var(--theme-sidebar-text) !important;">
                <nav class="flex flex-col items-center space-y-2" style="background: transparent !important; padding-top: var(--theme-header-height) !important;">

                    <!-- Dashboard -->
                    <button @click="activeSection = 'dashboard'"
                            :class="activeSection === 'dashboard' ? 'active' : ''"
                            class="flex flex-col items-center justify-center w-16 h-16 rounded-lg px-1 py-2"
                            title="Dashboard"
                            :style="activeSection === 'dashboard' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-tachometer-alt mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Dashboard</span>
                    </button>

                    <!-- Projects -->
                    <a href="{{ route('projects.index') }}"
                       @click="activeSection = 'projects'"
                       :class="activeSection === 'projects' ? 'active' : ''"
                       class="flex flex-col items-center justify-center w-16 h-16 rounded-lg px-1 py-2"
                       title="Projects"
                       :style="activeSection === 'projects' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-project-diagram mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Projects</span>
                    </a>

                    <!-- CRM -->
                    <a href="{{ route('customers.index') }}"
                       @click="activeSection = 'crm'"
                       :class="activeSection === 'crm' ? 'active' : ''"
                       class="flex flex-col items-center justify-center w-16 h-16 rounded-lg px-1 py-2"
                       title="CRM"
                       :style="activeSection === 'crm' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-users mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Customers</span>
                    </a>

                    <!-- Time Tracking -->
                    <button @click="activeSection = 'timetracking'"
                            :class="activeSection === 'timetracking' ? 'active' : ''"
                            class="flex flex-col items-center justify-center w-16 h-16 rounded-lg px-1 py-2"
                            title="Time Tracking"
                            :style="activeSection === 'timetracking' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-clock mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Timesheets</span>
                    </button>

                    <!-- Calendar -->
                    <button @click="activeSection = 'calendar'; window.location.href = '{{ route('calendar.index') }}'"
                            :class="activeSection === 'calendar' ? 'active' : ''"
                            class="flex flex-col items-center justify-center w-16 h-16 rounded-lg px-1 py-2"
                            title="Calendar"
                            :style="activeSection === 'calendar' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-calendar-alt mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Calendar</span>
                    </button>

                    <!-- Invoices -->
                    <button @click="activeSection = 'invoices'"
                            :class="activeSection === 'invoices' ? 'active' : ''"
                            class="flex flex-col items-center justify-center w-16 h-16 rounded-lg relative px-1 py-2"
                            title="Invoices"
                            :style="activeSection === 'invoices' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-file-invoice mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Invoices</span>
                        @php
                            $readyCount = \App\Models\Project::where('status', 'active')
                                ->get()
                                ->filter(function($project) {
                                    return $project->isReadyForInvoicing();
                                })
                                ->count();
                        @endphp
                        @if($readyCount > 0)
                            <span class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $readyCount }}</span>
                        @endif
                    </button>

                    <!-- Reports -->
                    @if(in_array(Auth::user()->role ?? '', ['super_admin', 'admin', 'project_manager']))
                    <button @click="activeSection = 'reports'"
                            :class="activeSection === 'reports' ? 'active' : ''"
                            class="flex flex-col items-center justify-center w-16 h-16 rounded-lg px-1 py-2"
                            title="Reports"
                            :style="activeSection === 'reports' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-chart-bar mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Statistics</span>
                    </button>
                    @endif

                    <!-- Configuration -->
                    @if(in_array(Auth::user()->role ?? '', ['super_admin', 'admin']))
                    <button @click="activeSection = 'configuration'"
                            :class="activeSection === 'configuration' ? 'active' : ''"
                            class="flex flex-col items-center justify-center w-16 h-16 rounded-lg px-1 py-2"
                            title="Configuration"
                            :style="activeSection === 'configuration' ? 'background-color: var(--theme-sidebar-active) !important; color: white !important;' : 'color: var(--theme-sidebar-text) !important; background: transparent !important;'">
                        <i class="fas fa-cog mb-1" style="font-size: var(--theme-sidebar-icon-size);"></i>
                        <span class="leading-tight text-center" style="font-size: var(--theme-sidebar-text-size);">Settings</span>
                    </button>
                    @endif

                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 min-h-screen" style="margin-left: 5rem;">
                <div style="margin-left: 90px !important; max-width: calc(100vw - 170px) !important; padding-right: 2rem !important;">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- AI Chat Widget - Simple floating button -->
    @if($aiSettings && $aiSettings->ai_enabled && $aiSettings->ai_chat_enabled)
        @include('components.ai-chat-simple')
    @endif

    <!-- Page-specific scripts -->
    @stack('scripts')
    
    <!-- Timer Modal -->
    <div id="quickTimeEntryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-4 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="mt-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Save Time Entry</h3>
                    <button onclick="closeQuickTimeEntry()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form id="quickTimeEntryForm" onsubmit="saveQuickTimeEntry(event)">
                    @csrf
                    <div class="space-y-4">
                        <!-- Timer Duration Display -->
                        <div class="bg-slate-50 rounded-lg p-3 text-center">
                            <p class="text-xs text-slate-500 mb-1">Duration</p>
                            <p id="modalTimerDuration" class="text-2xl font-mono font-semibold text-slate-700">00:00:00</p>
                            <input type="hidden" id="timerMinutes" name="minutes" value="0">
                        </div>
                        
                        <!-- Project Selection -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Project *</label>
                            <select id="quickProjectSelect" name="project_id" required onchange="loadQuickWorkItems(this.value)"
                                    class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                <option value="">Select a project</option>
                                @php
                                    $userProjects = \App\Models\Project::whereHas('users', function($q) {
                                        $q->where('user_id', Auth::id());
                                    })->where('status', 'active')->orderBy('name')->get();
                                @endphp
                                @foreach($userProjects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Work Item Selection -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Work Item *</label>
                            <select id="quickWorkItemSelect" name="work_item_id" required
                                    class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                <option value="">First select a project</option>
                            </select>
                        </div>
                        
                        <!-- Date -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Date *</label>
                            <input type="date" id="quickEntryDate" name="entry_date" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Description *</label>
                            <textarea id="quickDescription" name="description" rows="2" required
                                      class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                      placeholder="What did you work on?"></textarea>
                        </div>
                        
                        <!-- Billable -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="quickBillable" name="is_billable" value="billable" checked
                                       class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded">
                                <span class="ml-2 text-sm text-slate-700">Billable</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" onclick="closeQuickTimeEntry()" 
                                class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                            Save Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Timer JavaScript -->
    <script>
        // Timer variables
        let timerInterval = null;
        let timerSeconds = 0;
        let timerRunning = false;
        let timerStartTime = null;
        
        // Load timer state from localStorage
        function loadTimerState() {
            const savedState = localStorage.getItem('timerState');
            if (savedState) {
                const state = JSON.parse(savedState);
                timerSeconds = state.seconds || 0;
                timerRunning = state.running || false;
                timerStartTime = state.startTime ? new Date(state.startTime) : null;
                
                // If timer was running, calculate elapsed time
                if (timerRunning && timerStartTime) {
                    const now = new Date();
                    const elapsed = Math.floor((now - timerStartTime) / 1000);
                    timerSeconds += elapsed;
                    timerStartTime = now;
                    startTimerInterval();
                }
                
                updateTimerDisplay();
                updateTimerButtons();
            }
        }
        
        // Save timer state to localStorage
        function saveTimerState() {
            const state = {
                seconds: timerSeconds,
                running: timerRunning,
                startTime: timerStartTime ? timerStartTime.toISOString() : null
            };
            localStorage.setItem('timerState', JSON.stringify(state));
        }
        
        // Format seconds to HH:MM:SS
        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
        
        // Update timer display
        function updateTimerDisplay() {
            document.getElementById('timerDisplay').textContent = formatTime(timerSeconds);
        }
        
        // Update timer buttons
        function updateTimerButtons() {
            const startBtn = document.getElementById('startTimer');
            const pauseBtn = document.getElementById('pauseTimer');
            
            if (timerRunning) {
                startBtn.classList.add('hidden');
                pauseBtn.classList.remove('hidden');
            } else {
                startBtn.classList.remove('hidden');
                pauseBtn.classList.add('hidden');
            }
        }
        
        // Start timer interval
        function startTimerInterval() {
            if (timerInterval) clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timerSeconds++;
                updateTimerDisplay();
                saveTimerState();
            }, 1000);
        }
        
        // Toggle timer (start/pause)
        function toggleTimer() {
            timerRunning = !timerRunning;
            
            if (timerRunning) {
                timerStartTime = new Date();
                startTimerInterval();
            } else {
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
            }
            
            updateTimerButtons();
            saveTimerState();
        }
        
        // Stop timer
        function stopTimer() {
            if (confirm('Are you sure you want to stop and reset the timer?')) {
                timerRunning = false;
                timerSeconds = 0;
                timerStartTime = null;
                
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
                
                updateTimerDisplay();
                updateTimerButtons();
                localStorage.removeItem('timerState');
            }
        }
        
        // Open quick time entry modal
        function openQuickTimeEntry() {
            if (timerSeconds < 60) {
                alert('Timer must run for at least 1 minute before saving.');
                return;
            }
            
            // Pause timer if running
            if (timerRunning) {
                toggleTimer();
            }
            
            // Update modal with timer duration
            document.getElementById('modalTimerDuration').textContent = formatTime(timerSeconds);
            document.getElementById('timerMinutes').value = Math.ceil(timerSeconds / 60);
            
            // Show modal
            document.getElementById('quickTimeEntryModal').classList.remove('hidden');
        }
        
        // Close quick time entry modal
        function closeQuickTimeEntry() {
            document.getElementById('quickTimeEntryModal').classList.add('hidden');
        }
        
        // Load work items for selected project
        function loadQuickWorkItems(projectId) {
            const workItemSelect = document.getElementById('quickWorkItemSelect');
            
            if (!projectId) {
                workItemSelect.innerHTML = '<option value="">First select a project</option>';
                return;
            }
            
            workItemSelect.innerHTML = '<option value="">Loading...</option>';
            
            fetch(`/time-entries/project/${projectId}/work-items`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                workItemSelect.innerHTML = '<option value="">Select a work item</option>';
                
                if (data.workItems && data.workItems.length > 0) {
                    data.workItems.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.label;
                        if (item.indent > 0) {
                            option.style.paddingLeft = `${item.indent * 20}px`;
                        }
                        workItemSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading work items:', error);
                workItemSelect.innerHTML = '<option value="">Error loading work items</option>';
            });
        }
        
        // Save quick time entry
        function saveQuickTimeEntry(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Convert checkbox to proper value
            const billableCheckbox = document.getElementById('quickBillable');
            formData.set('is_billable', billableCheckbox.checked ? 'billable' : 'non_billable');
            
            // Round minutes to nearest 5
            const minutes = parseInt(formData.get('minutes'));
            const roundedMinutes = Math.ceil(minutes / 5) * 5;
            formData.set('minutes', roundedMinutes);
            
            // Submit form
            fetch('{{ route("time-entries.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.message) {
                    // Reset timer
                    timerSeconds = 0;
                    timerRunning = false;
                    timerStartTime = null;
                    updateTimerDisplay();
                    updateTimerButtons();
                    localStorage.removeItem('timerState');
                    
                    // Close modal
                    closeQuickTimeEntry();
                    
                    // Show success message and redirect
                    alert('Time entry saved successfully!');
                    window.location.href = '{{ route("time-entries.create") }}';
                } else {
                    alert('Error saving time entry. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error saving time entry:', error);
                alert('Error saving time entry. Please try again.');
            });
        }
        
        // Initialize timer on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTimerState();
        });
    </script>
    
    {{-- OVERRIDE Tailwind CSS at the very end with maximum specificity --}}
    <style>
        /* Debug: Check if theme-font-size is loaded */
        body::after {
            content: "Font size: " var(--theme-font-size);
            position: fixed;
            bottom: 0;
            right: 0;
            background: red;
            color: white;
            padding: 5px;
            z-index: 9999;
            display: none; /* Debug disabled - set to 'block' to see font size */
        }
        
        /* MAXIMUM SPECIFICITY OVERRIDES */
        html body .text-xs { font-size: calc(var(--theme-font-size) * 0.75) !important; }
        html body .text-sm { font-size: calc(var(--theme-font-size) * 0.875) !important; }
        html body .text-base { font-size: var(--theme-font-size) !important; }
        html body .text-lg { font-size: calc(var(--theme-font-size) * 1.125) !important; }
        html body .text-xl { font-size: calc(var(--theme-font-size) * 1.25) !important; }
        html body .text-2xl { font-size: calc(var(--theme-font-size) * 1.5) !important; }
        html body .text-3xl { font-size: calc(var(--theme-font-size) * 1.875) !important; }
        html body .text-4xl { font-size: calc(var(--theme-font-size) * 2.25) !important; }
        html body .text-5xl { font-size: calc(var(--theme-font-size) * 3) !important; }
        html body .text-6xl { font-size: calc(var(--theme-font-size) * 3.75) !important; }
        
        /* Force all table content */
        html body table * {
            font-size: var(--theme-font-size) !important;
        }
        
        html body table .text-xs { font-size: calc(var(--theme-font-size) * 0.75) !important; }
        html body table .text-sm { font-size: calc(var(--theme-font-size) * 0.875) !important; }
    </style>
</body>
</html>