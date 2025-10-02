@extends('layouts.app')

@section('title', 'Users')

@push('styles')
<style>
    /* Header button theme styling */
    .header-btn {
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Sticky Header - Full Width Under Top Menu --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">{{ $pageTitle }}</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">{{ $pageDescription }}</p>
                </div>
                <div class="flex space-x-2">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <a href="{{ route('users.create') }}" id="header-create-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-plus mr-1.5"></i>
                        New User
                    </a>
                    <a href="{{ route('users.deleted') }}" id="header-deleted-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-trash mr-1.5"></i>
                        Deleted Users
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="padding: 1.5rem 2rem;">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3" style="background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.05); border: 1px solid rgba(var(--theme-accent-rgb, 5, 150, 105), 0.2); border-radius: var(--theme-border-radius);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" style="color: var(--theme-accent);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-accent);">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 px-4 py-3" style="background-color: rgba(var(--theme-danger-rgb, 239, 68, 68), 0.05); border: 1px solid rgba(var(--theme-danger-rgb, 239, 68, 68), 0.2); border-radius: var(--theme-border-radius);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" style="color: var(--theme-danger);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-danger);">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Statistics Cards - Moderne minimale stijl --}}
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="theme-card p-3 transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.98)'" onmouseout="this.style.filter='brightness(1)'">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Total Users</p>
                        <p style="font-size: 1.25rem; font-weight: 600; margin-top: 0.25rem; color: var(--theme-text);">{{ $stats['total_users'] }}</p>
                    </div>
                    <div class="p-2 rounded-lg" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                        <svg class="w-5 h-5" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="theme-card p-3 transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.98)'" onmouseout="this.style.filter='brightness(1)'">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Active Users</p>
                        <p style="font-size: 1.25rem; font-weight: 600; margin-top: 0.25rem; color: var(--theme-accent);">{{ $stats['active_users'] }}</p>
                    </div>
                    <div class="p-2 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.1);">
                        <svg class="w-5 h-5" style="color: var(--theme-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="theme-card p-3 transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.98)'" onmouseout="this.style.filter='brightness(1)'">
                <div class="flex items-center justify-between">
                    <div>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Inactive Users</p>
                        <p style="font-size: 1.25rem; font-weight: 600; margin-top: 0.25rem; color: var(--theme-text-muted); opacity: 0.7;">{{ $stats['inactive_users'] }}</p>
                    </div>
                    <div class="p-2 rounded-lg" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2);">
                        <svg class="w-5 h-5" style="color: var(--theme-text-muted); opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="theme-card bg-white/80 backdrop-blur-sm p-6 mb-6" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                <h2 style="font-size: 1.0625rem; font-weight: 600; color: var(--theme-text);">Filter Users</h2>
            </div>
            <div>
                <form method="GET" action="{{ route('users.index') }}" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        {{-- Search --}}
                        <div>
                            <label for="search" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Name or email..." 
                                   class="w-full px-3 py-1.5 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                        </div>

                        {{-- Status Filter --}}
                        <div>
                            <label for="status" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Status</label>
                            <select name="status" id="status" class="w-full px-3 py-1.5 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        {{-- Role Filter --}}
                        <div>
                            <label for="role" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Role</label>
                            <select name="role" id="role" class="w-full px-3 py-1.5 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                                <option value="">All Roles</option>
                                <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="project_manager" {{ request('role') == 'project_manager' ? 'selected' : '' }}>Project Manager</option>
                                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                                <option value="reader" {{ request('role') == 'reader' ? 'selected' : '' }}>Reader</option>
                            </select>
                        </div>

                        {{-- Company Filter (only for super_admin) --}}
                        @if(Auth::user()->role === 'super_admin' && isset($companies))
                        <div>
                            <label for="company_id" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Company</label>
                            <select name="company_id" id="company_id" class="w-full px-3 py-1.5 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit" class="theme-btn-primary transition-all duration-200 flex items-center" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); color: white; font-size: var(--theme-button-font-size); font-weight: 500; border-radius: var(--theme-button-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('users.index') }}" class="transition-all duration-200 flex items-center" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); font-size: var(--theme-button-font-size); font-weight: 500; background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2); color: var(--theme-text); border-radius: var(--theme-button-radius);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.3)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.2)'">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Users Table --}}
        <div class="theme-card bg-white/80 backdrop-blur-sm p-6" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                <h2 style="font-size: 1.0625rem; font-weight: 600; color: var(--theme-text);">Users List</h2>
            </div>

            @if($users->count() > 0)
                <form id="bulk-form" method="POST" action="{{ route('users.bulk-action') }}">
                    @csrf
                    <input type="hidden" name="action" id="bulk-action">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y" style="--tw-divide-opacity: 0.3;">
                            <thead style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1);">
                                <tr>
                                    <th scope="col" class="px-4 py-2.5 text-left">
                                        <input type="checkbox" id="select-all" class="rounded" style="color: var(--theme-primary);">
                                    </th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">User</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Company</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Role</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Status</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Verified</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Last Login</th>
                                    <th scope="col" class="relative px-4 py-2.5"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/50 divide-y" style="--tw-divide-opacity: 0.3;">
                                @foreach($users as $user)
                                <tr class="transition-colors" style="border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                                    <td class="px-4 py-3" style="">
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded" style="color: var(--theme-primary);">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap" style="">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                                                    <span style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">{{ substr($user->name, 0, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $user->name }}</div>
                                                <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->company ? $user->company->name : 'No Company' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 inline-flex leading-5 rounded-full {{ $user->role_badge_class }}" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600;">
                                            {{ $user->role_description }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-1">
                                            @if($user->is_active)
                                                <span class="px-2 py-0.5 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.1); color: var(--theme-accent);">
                                                    Active
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2); color: var(--theme-text-muted);">
                                                    Inactive
                                                </span>
                                            @endif
                                            @if($user->auto_approve_time_entries)
                                                <span class="px-1.5 py-0.5 inline-flex items-center rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-primary-rgb, 37, 99, 235), 0.1); color: var(--theme-primary);" title="Time entries are automatically approved">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($user->hasVerifiedEmail())
                                            <span class="px-2 py-0.5 inline-flex items-center rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.1); color: var(--theme-accent);">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Verified
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                                Unverified
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                                        {{ $user->last_login_at ? \App\Helpers\DateHelper::format($user->last_login_at) : 'Never' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right" style="font-size: var(--theme-font-size); font-weight: 500;">
                                        <div class="flex items-center justify-end space-x-1">
                                            <button type="button" onclick="openViewUserModal({{ $user->id }})" class="p-1 rounded-lg transition-all duration-200" style="color: var(--theme-text-muted); background: none; border: none; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.1)'; this.style.color='var(--theme-text)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--theme-text-muted)'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                                <button type="button" onclick="openEditUserModal({{ $user->id }})" class="p-1 rounded-lg transition-all duration-200" style="color: var(--theme-text-muted); background: none; border: none; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.1)'; this.style.color='var(--theme-text)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--theme-text-muted)'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                @if($user->id !== Auth::id())
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to deactivate this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 rounded-lg transition-all duration-200" style="color: var(--theme-text-muted);" onmouseover="this.style.backgroundColor='rgba(var(--theme-danger-rgb, 239, 68, 68), 0.1)'; this.style.color='var(--theme-danger)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--theme-text-muted)'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Bulk Actions --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <div class="px-4 py-2.5" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                        <div class="flex items-center space-x-2">
                            <span style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">With selected:</span>
                            <button type="button" onclick="submitBulkAction('activate')" class="px-2.5 py-1 text-white transition-all duration-200" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: var(--theme-accent); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                                Activate
                            </button>
                            <button type="button" onclick="submitBulkAction('deactivate')" class="px-2.5 py-1 text-white transition-all duration-200" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: var(--theme-text-muted); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.85)'" onmouseout="this.style.filter='brightness(1)'">
                                Deactivate
                            </button>
                            <button type="button" onclick="if(confirm('Are you sure you want to delete selected users? This will soft delete them and they can be restored later.')) submitBulkAction('delete')" class="px-2.5 py-1 text-white transition-all duration-200" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: var(--theme-danger); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                                Delete
                            </button>
                        </div>
                    </div>
                    @endif
                </form>

                {{-- Pagination --}}
                <div class="px-4 py-3" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                    {{ $users->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12" style="color: var(--theme-text-muted); opacity: 0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <h3 class="mt-4" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">No users found</h3>
                    <p class="mt-1" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        @if(request()->has('search') || request()->has('status') || request()->has('role'))
                            Try adjusting your filters to find what you're looking for.
                        @else
                            Get started by creating your first user.
                        @endif
                    </p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <div class="mt-6">
                        <a href="{{ route('users.create') }}" class="theme-btn-primary text-white transition-all duration-200 inline-flex items-center" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); font-size: var(--theme-button-font-size); font-weight: 500; border-radius: var(--theme-button-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            New User
                        </a>
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- View User Modal --}}
<div id="view-user-modal" class="fixed inset-0 z-50 hidden" style="background-color: rgba(0, 0, 0, 0.15);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white border border-slate-200/40 rounded-xl overflow-hidden shadow-xl" style="max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1rem 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">View User</h2>
                    <button type="button" onclick="closeViewUserModal()" style="color: var(--theme-text-muted); background: none; border: none; font-size: 1.5rem; cursor: pointer; padding: 0.25rem;">
                        ×
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div style="padding: 1.5rem;">
                <div id="view-user-modal-loading" class="text-center py-6">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-3" style="color: var(--theme-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Loading user details...</p>
                </div>

                <div id="view-user-modal-content" class="hidden">
                    {{-- Content will be loaded here via AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div id="edit-user-modal" class="fixed inset-0 z-50 hidden" style="background-color: rgba(0, 0, 0, 0.15);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white border border-slate-200/40 rounded-xl overflow-hidden shadow-xl" style="max-width: 700px; width: 100%; max-height: 85vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1rem 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">Edit User</h2>
                    <button type="button" onclick="closeEditUserModal()" style="color: var(--theme-text-muted); background: none; border: none; font-size: 1.5rem; cursor: pointer; padding: 0.25rem;">
                        ×
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div style="padding: 1.5rem;">
                <div id="edit-user-modal-loading" class="text-center py-6">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-3" style="color: var(--theme-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Loading user form...</p>
                </div>

                <div id="edit-user-modal-content" class="hidden">
                    {{-- Content will be loaded here via AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Select all checkboxes
    document.getElementById('select-all')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Submit bulk action
    function submitBulkAction(action) {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one user');
            return;
        }

        if (confirm(`Are you sure you want to ${action} the selected users?`)) {
            document.getElementById('bulk-action').value = action;
            document.getElementById('bulk-form').submit();
        }
    }

    // Apply theme button styling to header buttons
    function styleHeaderButtons() {
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-text').trim();

        // Create button (primary action)
        const createBtn = document.getElementById('header-create-btn');
        if (createBtn) {
            createBtn.style.backgroundColor = primaryColor;
            createBtn.style.color = 'white';
            createBtn.style.border = 'none';
            createBtn.style.borderRadius = 'var(--theme-border-radius)';
        }

        // Deleted users button (secondary action)
        const deletedBtn = document.getElementById('header-deleted-btn');
        if (deletedBtn) {
            deletedBtn.style.backgroundColor = '#6b7280';
            deletedBtn.style.color = 'white';
            deletedBtn.style.border = 'none';
            deletedBtn.style.borderRadius = 'var(--theme-border-radius)';
        }
    }

    // Initialize header button styling when page loads
    document.addEventListener('DOMContentLoaded', function() {
        styleHeaderButtons();
    });

    /**
     * User Modal Functions
     */

    // Open view user modal
    function openViewUserModal(userId) {
        const modal = document.getElementById('view-user-modal');
        const modalLoading = document.getElementById('view-user-modal-loading');
        const modalContent = document.getElementById('view-user-modal-content');

        // Show modal and loading state
        modal.classList.remove('hidden');
        modalLoading.classList.remove('hidden');
        modalContent.classList.add('hidden');

        // Load view content via AJAX
        fetch(`/users/${userId}/show-modal`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load user details');
                }
                return response.text();
            })
            .then(html => {
                modalContent.innerHTML = html;
                modalLoading.classList.add('hidden');
                modalContent.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading user details:', error);
                modalContent.innerHTML = `
                    <div class="text-center py-8">
                        <p style="color: var(--theme-danger);">Failed to load user details. Please try again.</p>
                        <button type="button" onclick="closeViewUserModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius);">
                            Close
                        </button>
                    </div>
                `;
                modalLoading.classList.add('hidden');
                modalContent.classList.remove('hidden');
            });
    }

    // Close view user modal
    function closeViewUserModal() {
        const modal = document.getElementById('view-user-modal');
        modal.classList.add('hidden');
    }

    // Open edit user modal
    function openEditUserModal(userId) {
        const modal = document.getElementById('edit-user-modal');
        const modalLoading = document.getElementById('edit-user-modal-loading');
        const modalContent = document.getElementById('edit-user-modal-content');

        // Show modal and loading state
        modal.classList.remove('hidden');
        modalLoading.classList.remove('hidden');
        modalContent.classList.add('hidden');

        // Load edit form via AJAX
        fetch(`/users/${userId}/edit-modal`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load edit form');
                }
                return response.text();
            })
            .then(html => {
                modalContent.innerHTML = html;
                modalLoading.classList.add('hidden');
                modalContent.classList.remove('hidden');

                // Initialize form functionality
                initializeUserModalForm(userId);
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                modalContent.innerHTML = `
                    <div class="text-center py-8">
                        <p style="color: var(--theme-danger);">Failed to load edit form. Please try again.</p>
                        <button type="button" onclick="closeEditUserModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius);">
                            Close
                        </button>
                    </div>
                `;
                modalLoading.classList.add('hidden');
                modalContent.classList.remove('hidden');
            });
    }

    // Close edit user modal
    function closeEditUserModal() {
        const modal = document.getElementById('edit-user-modal');
        modal.classList.add('hidden');
    }

    // Initialize user modal form functionality
    function initializeUserModalForm(userId) {
        // Handle form submission
        const form = document.querySelector('#edit-user-modal-content form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitUserModalForm(form, userId);
            });
        }

        // Style form elements according to theme
        styleUserModalElements();
    }

    // Submit user modal form
    function submitUserModalForm(form, userId) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
        }

        const formData = new FormData(form);

        fetch(`/users/${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and refresh page
                closeEditUserModal();
                location.reload();
            } else {
                // Show error message
                alert('Error updating user: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            alert('Network error updating user');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    // Style user modal elements according to theme
    function styleUserModalElements() {
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

        // Style submit buttons
        const submitButtons = document.querySelectorAll('#edit-user-modal-content button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.style.backgroundColor = primaryColor;
            btn.style.color = 'white';
            btn.style.border = 'none';
            btn.style.borderRadius = 'var(--theme-border-radius)';
        });

        // Style radio buttons and checkboxes
        const inputElements = document.querySelectorAll('#edit-user-modal-content input[type="radio"], #edit-user-modal-content input[type="checkbox"]');
        inputElements.forEach(input => {
            input.style.accentColor = primaryColor;
        });
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        const viewModal = document.getElementById('view-user-modal');
        const editModal = document.getElementById('edit-user-modal');

        if (e.target === viewModal) {
            closeViewUserModal();
        }
        if (e.target === editModal) {
            closeEditUserModal();
        }
    });
</script>
@endpush