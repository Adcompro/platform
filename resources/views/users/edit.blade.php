@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b" style="border-color: rgba(var(--theme-border-rgb), 0.6);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--theme-text);">{{ $pageTitle }}</h1>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">{{ $pageDescription }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('users.show', $user) }}" class="inline-flex items-center border border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-weight: 600; font-size: var(--theme-button-font-size); text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(var(--theme-border-rgb), 0.2); color: var(--theme-text-muted); border-color: transparent;" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.3)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View User
                    </a>
                    <a href="{{ route('users.index') }}" class="inline-flex items-center border border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-weight: 600; font-size: var(--theme-button-font-size); color: white; text-transform: uppercase; letter-spacing: 0.05em; background-color: var(--theme-primary); border-color: transparent;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        ← Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Success Messages --}}
        @if(session('success'))
            <div class="mb-4 px-3 py-2.5 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb), 0.1); border: 1px solid rgba(var(--theme-accent-rgb), 0.3); color: var(--theme-accent);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-accent-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <div style="font-size: var(--theme-font-size); font-weight: 500;">{!! session('success') !!}</div>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-3 py-2.5 rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border: 1px solid rgba(var(--theme-danger-rgb), 0.3); color: var(--theme-danger);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-danger-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500;">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 px-3 py-2.5 rounded-lg" style="background-color: rgba(var(--theme-primary-rgb), 0.1); border: 1px solid rgba(var(--theme-primary-rgb), 0.3); color: var(--theme-primary);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-primary-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500;">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif

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
                        <h3 style="font-size: var(--theme-font-size); font-weight: 500;">There were errors with your submission</h3>
                        <ul style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); list-style-type: disc; list-style-position: inside;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            {{-- User Information Section --}}
            <div class="backdrop-blur-sm rounded-xl overflow-hidden mb-4 theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6);">
                <div class="px-4 py-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                    <h2 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">User Information</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Display Name --}}
                        <div>
                            <label for="name" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                Display Name <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-3 py-1.5 rounded-lg transition-colors @error('name') border-red-300 @enderror"
                                   style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                   onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                   onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="John Doe">
                            @error('name')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                Email Address <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <div class="relative">
                                <input type="email" name="email" id="email" required
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full px-3 py-1.5 rounded-lg transition-colors @error('email') border-red-300 @enderror"
                                       style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                       onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                       onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                       placeholder="john.doe@company.com">
                                @if(!$user->hasVerifiedEmail())
                                    <div class="absolute -top-2 right-0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(255, 193, 7, 0.2); color: #d97706;">
                                            Unverified
                                        </span>
                                    </div>
                                @endif
                            </div>
                            @error('email')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                            
                            {{-- Email Verification Status --}}
                            @if($user->hasVerifiedEmail())
                                <p class="mt-1 flex items-center" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-accent);">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Email verified on {{ \App\Helpers\DateHelper::formatDate($user->email_verified_at) }}
                                </p>
                            @else
                                <div class="mt-2">
                                    <p style="font-size: calc(var(--theme-font-size) - 2px); margin-bottom: 0.25rem; color: #d97706;">Email not verified</p>
                                    <form action="{{ route('users.resend-verification', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="underline" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);" onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                                            Resend verification email
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- First Name --}}
                        <div>
                            <label for="first_name" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                First Name
                            </label>
                            <input type="text" name="first_name" id="first_name"
                                   value="{{ old('first_name', $user->first_name) }}"
                                   class="w-full px-3 py-1.5 rounded-lg transition-colors @error('first_name') border-red-300 @enderror"
                                   style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                   onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                   onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="John">
                            @error('first_name')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <label for="last_name" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                Last Name
                            </label>
                            <input type="text" name="last_name" id="last_name"
                                   value="{{ old('last_name', $user->last_name) }}"
                                   class="w-full px-3 py-1.5 rounded-lg transition-colors @error('last_name') border-red-300 @enderror"
                                   style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                   onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                   onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Doe">
                            @error('last_name')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                Phone Number
                            </label>
                            <input type="text" name="phone" id="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   class="w-full px-3 py-1.5 rounded-lg transition-colors @error('phone') border-red-300 @enderror"
                                   style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                   onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                   onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="+31 6 12345678">
                            @error('phone')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- User Since --}}
                        <div>
                            <label style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Member Since</label>
                            <p class="px-3 py-1.5 rounded-lg" style="font-size: var(--theme-font-size); background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.8); color: var(--theme-text);">
                                {{ \App\Helpers\DateHelper::formatDate($user->created_at) }} ({{ $user->created_at->diffForHumans() }})
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security & Access Section --}}
            <div class="backdrop-blur-sm rounded-xl overflow-hidden mb-4 theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6);">
                <div class="px-4 py-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                    <h2 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Security & Access</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Password --}}
                        <div>
                            <label for="password" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                New Password
                            </label>
                            <input type="password" name="password" id="password"
                                   autocomplete="new-password"
                                   class="w-full px-3 py-1.5 rounded-lg transition-colors @error('password') border-red-300 @enderror"
                                   style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                   onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                   onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Leave blank to keep current">
                            @error('password')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                            <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Only fill in if you want to change the password</p>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                Confirm New Password
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   autocomplete="new-password"
                                   class="w-full px-3 py-1.5 rounded-lg transition-colors"
                                   style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                   onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                   onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'"
                                   placeholder="Repeat new password">
                        </div>

                        {{-- Company --}}
                        @if(Auth::user()->role === 'super_admin')
                        <div>
                            <label for="company_id" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                Company <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <select name="company_id" id="company_id" required
                                    class="w-full px-3 py-1.5 rounded-lg transition-colors @error('company_id') border-red-300 @enderror"
                                    style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                    onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                    onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>
                        @else
                        <div>
                            <label style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Company</label>
                            <p class="px-3 py-1.5 rounded-lg" style="font-size: var(--theme-font-size); background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.8); color: var(--theme-text);">
                                {{ $user->company ? $user->company->name : 'No Company' }}
                            </p>
                        </div>
                        @endif

                        {{-- Role --}}
                        <div>
                            <label for="role" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">
                                Role <span style="color: var(--theme-danger);">*</span>
                            </label>
                            <select name="role" id="role" required
                                    class="w-full px-3 py-1.5 rounded-lg transition-colors @error('role') border-red-300 @enderror"
                                    style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb), 0.8); background-color: white; color: var(--theme-text);"
                                    onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 1px var(--theme-primary)'"
                                    onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.8)'; this.style.boxShadow='none'">
                                @foreach($availableRoles as $value => $label)
                                    <option value="{{ $value }}" {{ old('role', $user->role) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger);">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.5rem; color: var(--theme-text-muted);">Status</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded"
                                       style="color: var(--theme-primary); border-color: rgba(var(--theme-border-rgb), 0.8);"
                                       onfocus="this.style.boxShadow='0 0 0 2px var(--theme-primary)'">
                                <label for="is_active" class="ml-2 block" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    User is active and can log in
                                </label>
                            </div>
                        </div>

                        {{-- Auto Approve Time Entries --}}
                        <div>
                            <label style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.5rem; color: var(--theme-text-muted);">Time Entry Approval</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="auto_approve_time_entries" id="auto_approve_time_entries" value="1" 
                                       {{ old('auto_approve_time_entries', $user->auto_approve_time_entries) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded"
                                       style="color: var(--theme-accent); border-color: rgba(var(--theme-border-rgb), 0.8);"
                                       onfocus="this.style.boxShadow='0 0 0 2px var(--theme-accent)'">
                                <label for="auto_approve_time_entries" class="ml-2 block" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    <span style="font-weight: 500;">Auto-approve time entries</span>
                                    <span class="block" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Time entries are immediately approved without admin review</span>
                                </label>
                            </div>
                            @if($user->auto_approve_time_entries)
                            <p class="mt-1 flex items-center" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-accent);">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Currently enabled - time entries bypass approval
                            </p>
                            @endif
                        </div>

                        {{-- Last Login --}}
                        <div>
                            <label style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Last Login</label>
                            <p class="px-3 py-1.5 rounded-lg" style="font-size: var(--theme-font-size); background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.8); color: var(--theme-text);">
                                {{ $user->last_login_at ? \App\Helpers\DateHelper::format($user->last_login_at) : 'Never logged in' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role Information --}}
            <div class="rounded-xl p-3 mb-4" style="background-color: rgba(var(--theme-border-rgb), 0.1); border: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                <h3 style="font-size: var(--theme-font-size); font-weight: 500; margin-bottom: 0.5rem; color: var(--theme-text);">Role Permissions Guide</h3>
                <ul class="space-y-1" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                    <li><strong style="color: var(--theme-text);">Super Administrator:</strong> Full system access, manage all companies</li>
                    <li><strong style="color: var(--theme-text);">Company Administrator:</strong> Manage own company, users, and projects</li>
                    <li><strong style="color: var(--theme-text);">Project Manager:</strong> Manage assigned projects and approve time entries</li>
                    <li><strong style="color: var(--theme-text);">Regular User:</strong> Log time and work on assigned tasks</li>
                    <li><strong style="color: var(--theme-text);">Read-only User:</strong> View only access to assigned projects</li>
                </ul>
            </div>

            {{-- Form Actions --}}
            <div class="flex justify-between">
                <div>
                    @if($user->id !== Auth::id())
                    <button type="button" onclick="if(confirm('Are you sure you want to deactivate this user?')) { document.getElementById('delete-form').submit(); }" 
                            class="font-medium rounded-lg transition-all duration-200 flex items-center"
                            style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger); border: 1px solid rgba(var(--theme-danger-rgb), 0.2);"
                            onmouseover="this.style.backgroundColor='rgba(var(--theme-danger-rgb), 0.2)'"
                            onmouseout="this.style.backgroundColor='rgba(var(--theme-danger-rgb), 0.1)'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Deactivate User
                    </button>
                    @endif
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('users.show', $user) }}" class="font-medium rounded-lg transition-all duration-200"
                       style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);"
                       onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'"
                       onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.1)'">
                        Cancel
                    </a>
                    <button type="submit" class="theme-btn-primary font-medium rounded-lg transition-all duration-200 flex items-center"
                            style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background-color: var(--theme-primary); color: white;"
                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                        </svg>
                        Update User
                    </button>
                </div>
            </div>
        </form>

        {{-- Delete Form --}}
        @if($user->id !== Auth::id())
        <form id="delete-form" action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('password_confirmation');
    const confirmContainer = confirmField.closest('div');
    const confirmLabel = confirmContainer.querySelector('label');
    
    // Prevent autofill on password fields
    passwordField.setAttribute('autocomplete', 'new-password');
    confirmField.setAttribute('autocomplete', 'new-password');
    
    // Clear any autofilled values on page load
    setTimeout(function() {
        passwordField.value = '';
        confirmField.value = '';
        updateConfirmationField();
    }, 100);
    
    // Initially disable confirmation field if password is empty
    function updateConfirmationField() {
        if (passwordField.value.length > 0) {
            // Enable confirmation field
            confirmField.removeAttribute('disabled');
            confirmContainer.style.opacity = '1';
            confirmLabel.innerHTML = 'Confirm New Password <span style="color: var(--theme-danger);">*</span>';
        } else {
            // Disable confirmation field and clear it
            confirmField.setAttribute('disabled', 'disabled');
            confirmField.value = '';
            confirmContainer.style.opacity = '0.5';
            confirmLabel.innerHTML = 'Confirm New Password';
        }
    }
    
    // Check on page load
    updateConfirmationField();
    
    // Listen for changes in password field
    passwordField.addEventListener('input', updateConfirmationField);
    passwordField.addEventListener('change', updateConfirmationField);
    passwordField.addEventListener('blur', updateConfirmationField);
    
    // Also check for browser autofill
    setInterval(function() {
        if (passwordField.matches(':-webkit-autofill')) {
            // Browser has autofilled, clear it
            passwordField.value = '';
            confirmField.value = '';
            updateConfirmationField();
        }
    }, 500);
    
    // Form validation on submit
    const form = passwordField.closest('form');
    form.addEventListener('submit', function(e) {
        // Only validate confirmation if password is filled
        if (passwordField.value.length > 0) {
            if (passwordField.value !== confirmField.value) {
                e.preventDefault();
                alert('Passwords do not match. Please confirm your new password.');
                confirmField.focus();
                return false;
            }
        }
    });
});
</script>
@endsection