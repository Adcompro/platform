@extends('layouts.app')

@section('title', 'New User')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Moderne uitstraling met glassmorphism --}}
    <div class="bg-white/70 backdrop-blur-sm sticky top-0 z-10" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
        <div class="max-w-7xl mx-auto" style="padding: calc(var(--theme-view-header-padding) * 0.75) var(--theme-view-header-padding);">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="font-semibold" style="color: var(--theme-text); font-size: var(--theme-view-header-title-size);">{{ $pageTitle }}</h1>
                    <p class="mt-0.5" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">{{ $pageDescription }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('users.index') }}" class="font-medium transition-all duration-200 flex items-center" style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted); border-radius: var(--theme-border-radius); font-size: var(--theme-view-header-button-size);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.1)'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="mb-4 px-3 py-2.5" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border: 1px solid rgba(var(--theme-danger-rgb), 0.3); color: var(--theme-danger); border-radius: var(--theme-border-radius);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-danger-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium" style="font-size: var(--theme-font-size);">There were errors with your submission</h3>
                        <ul class="mt-1 list-disc list-inside" style="font-size: calc(var(--theme-font-size) - 2px);">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            {{-- User Information Section --}}
            <div class="backdrop-blur-sm overflow-hidden mb-4 theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6); border-radius: var(--theme-border-radius);">
                <div class="px-4 py-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                    <h2 class="font-medium" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">User Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Display Name --}}
                        <div>
                            <label for="name" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Display Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name') }}"
                                   class="w-full px-3 py-1.5 transition-colors @error('name') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="John Doe">
                            @error('name')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   class="w-full px-3 py-1.5 transition-colors @error('email') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="john.doe@company.com">
                            @error('email')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- First Name --}}
                        <div>
                            <label for="first_name" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                First Name
                            </label>
                            <input type="text" name="first_name" id="first_name"
                                   value="{{ old('first_name') }}"
                                   class="w-full px-3 py-1.5 transition-colors @error('first_name') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="John">
                            @error('first_name')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <label for="last_name" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Last Name
                            </label>
                            <input type="text" name="last_name" id="last_name"
                                   value="{{ old('last_name') }}"
                                   class="w-full px-3 py-1.5 transition-colors @error('last_name') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Doe">
                            @error('last_name')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Phone Number
                            </label>
                            <input type="text" name="phone" id="phone"
                                   value="{{ old('phone') }}"
                                   class="w-full px-3 py-1.5 transition-colors @error('phone') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="+31 6 12345678">
                            @error('phone')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security & Access Section --}}
            <div class="backdrop-blur-sm overflow-hidden mb-4 theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6); border-radius: var(--theme-border-radius);">
                <div class="px-4 py-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                    <h2 class="font-medium" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Security & Access</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Password --}}
                        <div>
                            <label for="password" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password" required
                                   class="w-full px-3 py-1.5 transition-colors @error('password') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Minimum 8 characters">
                            @error('password')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                            <p class="mt-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Must be at least 8 characters long</p>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="w-full px-3 py-1.5 transition-colors" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Repeat password">
                        </div>

                        {{-- Company --}}
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                        <div>
                            <label for="company_id" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Company <span class="text-red-500">*</span>
                            </label>
                            <select name="company_id" id="company_id" required
                                    class="w-full px-3 py-1.5 transition-colors @error('company_id') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'">
                                <option value="">Select a company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>
                        @else
                        <div>
                            <label class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Company</label>
                            <p class="px-3 py-1.5" style="background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.8); color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                                {{ Auth::user()->company->name }}
                            </p>
                        </div>
                        @endif

                        {{-- Role --}}
                        <div>
                            <label for="role" class="block font-medium mb-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select name="role" id="role" required
                                    class="w-full px-3 py-1.5 transition-colors @error('role') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'">
                                <option value="">Select a role</option>
                                @foreach($availableRoles as $value => $label)
                                    <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1" style="color: var(--theme-danger); font-size: calc(var(--theme-font-size) - 2px);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block font-medium mb-2" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Status</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded" style="color: var(--theme-primary); border-color: rgba(var(--theme-border-rgb), 0.8);" onfocus="this.style.boxShadow='0 0 0 2px var(--theme-primary)'">
                                <label for="is_active" class="ml-2 block" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                    User is active and can log in
                                </label>
                            </div>
                        </div>

                        {{-- Auto Approve Time Entries --}}
                        <div>
                            <label class="block font-medium mb-2" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Time Entry Approval</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="auto_approve_time_entries" id="auto_approve_time_entries" value="1"
                                       {{ old('auto_approve_time_entries', false) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded" style="color: var(--theme-accent); border-color: rgba(var(--theme-border-rgb), 0.8);" onfocus="this.style.boxShadow='0 0 0 2px var(--theme-accent)'">
                                <label for="auto_approve_time_entries" class="ml-2 block" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                    <span class="font-medium">Auto-approve time entries</span>
                                    <span class="block" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">Time entries are immediately approved without admin review</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role Information --}}
            <div class="p-3 mb-4" style="background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.5); border-radius: var(--theme-border-radius);">
                <h3 class="font-medium mb-2" style="color: var(--theme-text); font-size: var(--theme-font-size);">Role Permissions Guide</h3>
                <ul class="space-y-1" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                    <li><strong style="color: var(--theme-text);">Super Administrator:</strong> Full system access, manage all companies</li>
                    <li><strong style="color: var(--theme-text);">Company Administrator:</strong> Manage own company, users, and projects</li>
                    <li><strong style="color: var(--theme-text);">Project Manager:</strong> Manage assigned projects and approve time entries</li>
                    <li><strong style="color: var(--theme-text);">Regular User:</strong> Log time and work on assigned tasks</li>
                    <li><strong style="color: var(--theme-text);">Read-only User:</strong> View only access to assigned projects</li>
                </ul>
            </div>

            {{-- Form Actions --}}
            <div class="flex justify-end space-x-2">
                <a href="{{ route('users.index') }}" class="font-medium transition-all duration-200" style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted); border-radius: var(--theme-border-radius); font-size: var(--theme-button-font-size);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.1)'">
                    Cancel
                </a>
                <button type="submit" class="theme-btn-primary font-medium transition-all duration-200 flex items-center" style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); background-color: var(--theme-primary); color: white; border-radius: var(--theme-border-radius); font-size: var(--theme-button-font-size);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                    </svg>
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
