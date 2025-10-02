@extends('layouts.app')

@section('title', 'New User')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Moderne uitstraling met glassmorphism --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold" style="color: var(--theme-text);">{{ $pageTitle }}</h1>
                    <p class="text-xs mt-0.5" style="color: var(--theme-text-muted);">{{ $pageDescription }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('users.index') }}" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 flex items-center" style="background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.1)'">
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
            <div class="mb-4 px-3 py-2.5 rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border: 1px solid rgba(var(--theme-danger-rgb), 0.3); color: var(--theme-danger);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-danger-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium">There were errors with your submission</h3>
                        <ul class="mt-1 text-xs list-disc list-inside">
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
            <div class="backdrop-blur-sm rounded-xl overflow-hidden mb-4 theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6);">
                <div class="px-4 py-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                    <h2 class="text-base font-medium" style="color: var(--theme-text);">User Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Display Name --}}
                        <div>
                            <label for="name" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Display Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name') }}"
                                   class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('name') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="John Doe">
                            @error('name')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('email') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="john.doe@company.com">
                            @error('email')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- First Name --}}
                        <div>
                            <label for="first_name" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                First Name
                            </label>
                            <input type="text" name="first_name" id="first_name"
                                   value="{{ old('first_name') }}"
                                   class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('first_name') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="John">
                            @error('first_name')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <label for="last_name" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Last Name
                            </label>
                            <input type="text" name="last_name" id="last_name"
                                   value="{{ old('last_name') }}"
                                   class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('last_name') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Doe">
                            @error('last_name')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Phone Number
                            </label>
                            <input type="text" name="phone" id="phone"
                                   value="{{ old('phone') }}"
                                   class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('phone') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="+31 6 12345678">
                            @error('phone')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security & Access Section --}}
            <div class="backdrop-blur-sm rounded-xl overflow-hidden mb-4 theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6);">
                <div class="px-4 py-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                    <h2 class="text-base font-medium" style="color: var(--theme-text);">Security & Access</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password" required
                                   class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('password') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Minimum 8 characters">
                            @error('password')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs" style="color: var(--theme-text-muted);">Must be at least 8 characters long</p>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Repeat password">
                        </div>

                        {{-- Company --}}
                        @if(Auth::user()->role === 'super_admin')
                        <div>
                            <label for="company_id" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Company <span class="text-red-500">*</span>
                            </label>
                            <select name="company_id" id="company_id" required
                                    class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('company_id') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'">
                                <option value="">Select a company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                        @else
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Company</label>
                            <p class="px-3 py-1.5 rounded-lg text-sm" style="background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.8); color: var(--theme-text);">
                                {{ Auth::user()->company->name }}
                            </p>
                        </div>
                        @endif

                        {{-- Role --}}
                        <div>
                            <label for="role" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select name="role" id="role" required
                                    class="w-full px-3 py-1.5 text-sm rounded-lg transition-colors @error('role') border-red-300 @enderror" style="border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'">
                                <option value="">Select a role</option>
                                @foreach($availableRoles as $value => $label)
                                    <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1 text-xs" style="color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-medium mb-2" style="color: var(--theme-text-muted);">Status</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded" style="color: var(--theme-primary); border-color: rgba(var(--theme-border-rgb), 0.8);" onfocus="this.style.boxShadow='0 0 0 2px var(--theme-primary)'">
                                <label for="is_active" class="ml-2 block text-sm" style="color: var(--theme-text);">
                                    User is active and can log in
                                </label>
                            </div>
                        </div>

                        {{-- Auto Approve Time Entries --}}
                        <div>
                            <label class="block text-xs font-medium mb-2" style="color: var(--theme-text-muted);">Time Entry Approval</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="auto_approve_time_entries" id="auto_approve_time_entries" value="1" 
                                       {{ old('auto_approve_time_entries', false) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded" style="color: var(--theme-accent); border-color: rgba(var(--theme-border-rgb), 0.8);" onfocus="this.style.boxShadow='0 0 0 2px var(--theme-accent)'">
                                <label for="auto_approve_time_entries" class="ml-2 block text-sm" style="color: var(--theme-text);">
                                    <span class="font-medium">Auto-approve time entries</span>
                                    <span class="text-xs block" style="color: var(--theme-text-muted);">Time entries are immediately approved without admin review</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role Information --}}
            <div class="rounded-xl p-3 mb-4" style="background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                <h3 class="text-sm font-medium mb-2" style="color: var(--theme-text);">Role Permissions Guide</h3>
                <ul class="text-xs space-y-1" style="color: var(--theme-text-muted);">
                    <li><strong style="color: var(--theme-text);">Super Administrator:</strong> Full system access, manage all companies</li>
                    <li><strong style="color: var(--theme-text);">Company Administrator:</strong> Manage own company, users, and projects</li>
                    <li><strong style="color: var(--theme-text);">Project Manager:</strong> Manage assigned projects and approve time entries</li>
                    <li><strong style="color: var(--theme-text);">Regular User:</strong> Log time and work on assigned tasks</li>
                    <li><strong style="color: var(--theme-text);">Read-only User:</strong> View only access to assigned projects</li>
                </ul>
            </div>

            {{-- Form Actions --}}
            <div class="flex justify-end space-x-2">
                <a href="{{ route('users.index') }}" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.1)'">
                    Cancel
                </a>
                <button type="submit" class="theme-btn-primary px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 flex items-center" style="background-color: var(--theme-primary); color: white;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
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