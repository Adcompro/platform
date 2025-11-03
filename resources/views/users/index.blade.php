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
                    <button type="button" onclick="openHelpModal()" id="header-help-btn"
                       class="header-btn"
                       style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);"
                       title="Help & Documentation">
                        <i class="fas fa-question-circle mr-1.5"></i>
                        Help
                    </button>
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
                        <svg class="h-4 w-4" style="color: var(--theme-accent);" viewBox="0 0 20 20" fill="currentColor">
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
                        <svg class="h-4 w-4" style="color: var(--theme-danger);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-danger);">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filters - Compact Design --}}
        <div class="theme-card bg-white/80 backdrop-blur-sm p-3 mb-6" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
            <form method="GET" action="{{ route('users.index') }}">
                <div class="flex items-end gap-2">
                    {{-- Search --}}
                    <div class="flex-1">
                        <label for="search" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Name or email..."
                               class="w-full px-3 py-2 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                    </div>

                    {{-- Status Filter --}}
                    <div style="min-width: 180px;">
                        <label for="status" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Status</label>
                        <select name="status" id="status" class="w-full px-3 py-2 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    {{-- Role Filter --}}
                    <div style="min-width: 200px;">
                        <label for="role" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Role</label>
                        <select name="role" id="role" class="w-full px-3 py-2 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            <option value="">All</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="project_manager" {{ request('role') == 'project_manager' ? 'selected' : '' }}>Project Manager</option>
                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                            <option value="reader" {{ request('role') == 'reader' ? 'selected' : '' }}>Reader</option>
                        </select>
                    </div>

                    {{-- Company Filter (voor super_admin en admin) --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']) && isset($companies))
                    <div style="min-width: 200px;">
                        <label for="company_id" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.25rem; color: var(--theme-text-muted);">Company</label>
                        <select name="company_id" id="company_id" class="w-full px-3 py-2 rounded-lg transition-colors" style="font-size: var(--theme-font-size); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            <option value="">All</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Filter & Clear Buttons --}}
                    <div class="flex gap-2">
                        <button type="submit" class="transition-all duration-200 flex items-center" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background-color: var(--theme-primary); color: white; font-size: var(--theme-button-font-size); font-weight: 500; border-radius: var(--theme-button-radius); white-space: nowrap; border: none;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            <i class="fas fa-search mr-1.5"></i>
                            Filter
                        </button>
                        <a href="{{ route('users.index') }}" class="transition-all duration-200 flex items-center" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); font-size: var(--theme-button-font-size); font-weight: 500; background-color: #e5e7eb; color: #374151; border-radius: var(--theme-button-radius); white-space: nowrap;" onmouseover="this.style.backgroundColor='#d1d5db'" onmouseout="this.style.backgroundColor='#e5e7eb'">
                            <i class="fas fa-times mr-1.5"></i>
                            Clear
                        </a>
                    </div>
                </div>
            </form>
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
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <th scope="col" class="px-4 py-2.5 text-left">
                                        <input type="checkbox" id="select-all" class="rounded" style="color: var(--theme-primary);">
                                    </th>
                                    @endif
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">User</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Company</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Role</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Status</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Verified</th>
                                    <th scope="col" class="px-4 py-2.5 text-left uppercase tracking-wider" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Last Login</th>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <th scope="col" class="relative px-4 py-2.5"><span class="sr-only">Actions</span></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white/50 divide-y" style="--tw-divide-opacity: 0.3;">
                                @foreach($users as $user)
                                <tr class="transition-colors" style="border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <td class="px-4 py-3" style="">
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded" style="color: var(--theme-primary);">
                                    </td>
                                    @endif
                                    <td class="px-4 py-3 whitespace-nowrap" style="">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-7 w-7">
                                                <div class="h-7 w-7 rounded-full flex items-center justify-center" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
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
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                    <td class="px-4 py-3 whitespace-nowrap text-right" style="font-size: var(--theme-font-size); font-weight: 500;">
                                        <div class="flex items-center justify-end space-x-1">
                                            <button type="button" onclick="openViewUserModal({{ $user->id }})" class="p-1 rounded-lg transition-all duration-200" style="color: var(--theme-text-muted); background: none; border: none; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.1)'; this.style.color='var(--theme-text)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--theme-text-muted)'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                                <button type="button" onclick="openEditUserModal({{ $user->id }})" class="p-1 rounded-lg transition-all duration-200" style="color: var(--theme-text-muted); background: none; border: none; cursor: pointer;" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.1)'; this.style.color='var(--theme-text)'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--theme-text-muted)'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                @if($user->id !== Auth::id())
                                                <button type="button"
                                                        onclick="openDeleteUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                                        class="p-1 rounded-lg transition-all duration-200"
                                                        style="color: var(--theme-text-muted);"
                                                        onmouseover="this.style.backgroundColor='rgba(var(--theme-danger-rgb, 239, 68, 68), 0.1)'; this.style.color='var(--theme-danger)'"
                                                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--theme-text-muted)'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                                @endif
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Bulk Actions --}}
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

{{-- Floating Bulk Actions Bar --}}
@if(in_array(Auth::user()->role, ['super_admin', 'admin']))
<div id="floating-bulk-actions" class="fixed bottom-0 left-0 right-0 z-40 transition-all duration-300" style="transform: translateY(100%); pointer-events: none;">
    <div class="max-w-4xl mx-auto px-4 pb-6">
        <div class="backdrop-blur-lg rounded-2xl shadow-2xl border overflow-hidden"
             style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
                    border-color: rgba(var(--theme-border-rgb), 0.3);
                    pointer-events: auto;">
            <div class="flex items-center justify-between px-6 py-4">
                {{-- Left: Selection Info --}}
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                             style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <i class="fas fa-check" style="color: var(--theme-primary); font-size: calc(var(--theme-font-size) + 2px);"></i>
                        </div>
                        <div>
                            <div id="floating-selected-count" class="font-semibold" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                0 selected
                            </div>
                            <div class="text-xs" style="color: var(--theme-text-muted);">
                                Choose an action below
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="flex items-center gap-2">
                    {{-- Status Change Dropdown --}}
                    <div class="relative">
                        <button onclick="toggleStatusDropdown(event)"
                                id="status-dropdown-btn"
                                class="px-4 py-2 rounded-lg font-medium text-white text-sm transition-all duration-200 hover:opacity-90 flex items-center gap-2"
                                style="background-color: var(--theme-primary);">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Change Status</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>

                        <div id="status-dropdown" class="hidden fixed bg-white rounded-lg shadow-2xl border overflow-hidden z-50" style="min-width: 200px; border-color: rgba(var(--theme-border-rgb), 0.3);">
                            <div class="py-1">
                                <button onclick="openBulkStatusModal('activate')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 transition-colors flex items-center gap-3" style="color: var(--theme-success);">
                                    <i class="fas fa-check-circle w-4"></i>
                                    <span>Activate Users</span>
                                </button>
                                <button onclick="openBulkStatusModal('deactivate')" class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 transition-colors flex items-center gap-3" style="color: var(--theme-text-muted);">
                                    <i class="fas fa-times-circle w-4"></i>
                                    <span>Deactivate Users</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Delete Button --}}
                    <button onclick="openBulkDeleteModal()"
                            class="px-4 py-2 rounded-lg font-medium text-white text-sm transition-all duration-200 hover:opacity-90 flex items-center gap-2"
                            style="background-color: var(--theme-danger);">
                        <i class="fas fa-trash"></i>
                        <span>Delete</span>
                    </button>

                    {{-- Clear Selection --}}
                    <button onclick="clearAllSelections()"
                            class="px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200 hover:opacity-80 flex items-center gap-2 ml-2"
                            style="background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Universal Bulk Status Change Modal --}}
<div id="bulkStatusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4" style="border-radius: var(--theme-border-radius);">
        <div class="p-6">
            <div class="flex items-start">
                <div id="statusModalIcon" class="flex-shrink-0 h-12 w-12 rounded-full flex items-center justify-center">
                    <i id="statusModalIconContent" class="text-2xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 id="statusModalTitle" class="font-semibold" style="font-size: calc(var(--theme-font-size) + 2px); color: var(--theme-text);">Change Status</h3>
                    <p class="mt-2" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                        Are you sure you want to <span id="statusModalAction"></span> <span id="statusModalCount" class="font-medium" style="color: var(--theme-text);">0</span> user(s)?
                    </p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 flex justify-end gap-3" style="background-color: rgba(var(--theme-border-rgb), 0.05);">
            <button type="button" onclick="closeBulkStatusModal()" class="px-4 py-2 font-medium transition-all rounded-lg" style="background-color: #e5e7eb; color: #6b7280; font-size: 14px;">
                Cancel
            </button>
            <button type="button" id="statusModalConfirmBtn" onclick="confirmBulkStatusChange()" class="px-4 py-2 font-semibold text-white transition-all rounded-lg hover:opacity-90" style="background-color: #3b82f6; font-size: 14px;">
                Confirm
            </button>
        </div>
    </div>
</div>

{{-- View User Modal --}}
<div id="view-user-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative w-full px-4">
        <div class="relative bg-white text-left shadow-2xl transform transition-all mx-auto" style="border-radius: var(--theme-border-radius); max-width: 650px; max-height: 85vh; display: flex; flex-direction: column;">
            {{-- Modal Header (fixed) --}}
            <div class="px-5 py-3.5" style="background: linear-gradient(to right, var(--theme-primary), var(--theme-primary)); filter: brightness(0.95); border-radius: var(--theme-border-radius) var(--theme-border-radius) 0 0;">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full" style="background-color: rgba(255, 255, 255, 0.2);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h2 class="ml-3 font-semibold text-white" style="font-size: 1.125rem; margin: 0;">View User</h2>
                    </div>
                    <button type="button" onclick="closeViewUserModal()" class="text-white transition-colors" style="opacity: 0.9; background: none; border: none; cursor: pointer;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Content (scrollable) --}}
            <div class="bg-white overflow-y-auto" style="flex: 1; min-height: 0; padding: 1.25rem;">
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
<div id="edit-user-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative w-full px-4">
        <div class="relative bg-white text-left shadow-2xl transform transition-all mx-auto" style="border-radius: var(--theme-border-radius); max-width: 750px; max-height: 85vh; display: flex; flex-direction: column;">
            {{-- Modal Header (fixed) --}}
            <div class="px-5 py-3.5" style="background: linear-gradient(to right, var(--theme-primary), var(--theme-primary)); filter: brightness(0.95); border-radius: var(--theme-border-radius) var(--theme-border-radius) 0 0;">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full" style="background-color: rgba(255, 255, 255, 0.2);">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <h2 class="ml-3 font-semibold text-white" style="font-size: 1.125rem; margin: 0;">Edit User</h2>
                    </div>
                    <button type="button" onclick="closeEditUserModal()" class="text-white transition-colors" style="opacity: 0.9; background: none; border: none; cursor: pointer;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Content (scrollable) --}}
            <div class="bg-white overflow-y-auto" style="flex: 1; min-height: 0; padding: 1.25rem;">
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

{{-- Individual Delete User Modal --}}
<div id="deleteUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteUserModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                        <svg class="h-7 w-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-white">Deactivate User</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white px-6 py-5">
                <div class="mb-4">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-yellow-800">
                                    You are about to deactivate this user
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5 bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">User information:</h4>
                    <div class="space-y-2">
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-gray-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-gray-900" id="deleteUserName"></p>
                                <p class="text-xs text-gray-600" id="deleteUserEmail"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">What will happen:</h4>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <svg class="h-4 w-4 text-gray-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">User will be <strong class="text-gray-600">deactivated</strong> immediately</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-4 w-4 text-gray-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">They will not be able to log in</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-4 w-4 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">User can be reactivated later if needed</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                <form id="deleteUserForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Yes, Deactivate User
                    </button>
                </form>

                <button type="button"
                        onclick="closeDeleteUserModal()"
                        class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal --}}
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeBulkDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-white">Delete Users</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white px-6 py-5">
                <div class="mb-4">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-red-800">
                                    This will soft delete the selected users
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5 bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Action details:</h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-sm text-gray-600">Selected users:</span>
                            <span class="ml-2 text-sm font-medium text-gray-900" id="deleteUserCount">0</span>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">What will happen:</h4>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <svg class="h-4 w-4 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">Users will be <strong class="text-red-600">soft deleted</strong></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-4 w-4 text-orange-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">They will be moved to <strong>Deleted Users</strong></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-4 w-4 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">Users <strong class="text-green-600">can be restored</strong> later if needed</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-xs text-blue-800">
                        <strong>ℹ️ Note:</strong> This is a soft delete. To permanently delete users, visit the Deleted Users page.
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                <button type="button"
                        onclick="confirmBulkDelete()"
                        class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Yes, Delete Users
                </button>

                <button type="button"
                        onclick="closeBulkDeleteModal()"
                        class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Help Modal --}}
<div id="helpModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="relative w-full px-4">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeHelpModal()" style="background-color: rgba(0, 0, 0, 0.5);"></div>

        <!-- Center modal -->
        <div class="relative bg-white text-left shadow-2xl transform transition-all mx-auto" style="border-radius: var(--theme-border-radius); max-width: 900px; max-height: 85vh; display: flex; flex-direction: column;">
            <!-- Modal Header (fixed) -->
            <div class="px-5 py-3.5" style="background: linear-gradient(to right, var(--theme-primary), var(--theme-primary)); filter: brightness(0.95); border-radius: var(--theme-border-radius) var(--theme-border-radius) 0 0;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full" style="background-color: rgba(255, 255, 255, 0.2);">
                            <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-white">User Management Help Guide</h3>
                            <p class="text-sm" style="color: rgba(255, 255, 255, 0.85);">Complete documentation for managing users</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeHelpModal()" class="text-white transition-colors" style="opacity: 0.9;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body (scrollable) -->
            <div class="bg-white px-5 py-4 overflow-y-auto" style="flex: 1; min-height: 0;">
                <!-- Overview Section -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 h-7 w-7 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <svg class="h-4 w-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="ml-2.5 font-semibold" style="font-size: 1rem; color: var(--theme-text);">Overview</h4>
                    </div>
                    <div class="ml-9 space-y-2" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">
                        <p>The User Management system allows administrators to create, manage, and control user access across your organization. Users can be assigned different roles with varying levels of permissions.</p>
                        <p class="p-2.5 rounded-r-lg" style="background-color: rgba(var(--theme-primary-rgb), 0.05); border-left: 3px solid var(--theme-primary); font-size: calc(var(--theme-font-size) - 1px);">
                            <strong style="color: var(--theme-primary);">💡 Quick Tip:</strong> Only Super Administrators and Company Administrators can manage users.
                        </p>
                    </div>
                </div>

                <!-- User Roles Section -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 h-7 w-7 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <svg class="h-4 w-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h4 class="ml-2.5 text-base font-semibold" style="color: var(--theme-text);">User Roles & Permissions</h4>
                    </div>
                    <div class="ml-11">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-700 border-b">Role</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-700 border-b">Access Level</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-700 border-b">Key Permissions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-md font-semibold text-xs">Super Admin</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">All Companies</td>
                                        <td class="px-4 py-3 text-gray-600">Full system access, manage all companies, users, and settings</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-md font-semibold text-xs">Admin</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">Own Company</td>
                                        <td class="px-4 py-3 text-gray-600">Manage users, projects, and settings within their company</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-md font-semibold text-xs">Project Manager</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">Assigned Projects</td>
                                        <td class="px-4 py-3 text-gray-600">Manage assigned projects, approve time entries</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-md font-semibold text-xs">User</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">Assigned Tasks</td>
                                        <td class="px-4 py-3 text-gray-600">Log time, view assigned projects and tasks</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-md font-semibold text-xs">Reader</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">Read Only</td>
                                        <td class="px-4 py-3 text-gray-600">View-only access to assigned resources</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Creating Users Section -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 h-7 w-7 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <svg class="h-4 w-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <h4 class="ml-2.5 text-base font-semibold" style="color: var(--theme-text);">Creating & Managing Users</h4>
                    </div>
                    <div class="ml-11 space-y-3">
                        <div>
                            <h5 class="font-semibold mb-2" style="color: var(--theme-text);">Creating a New User:</h5>
                            <ol class="list-decimal list-inside space-y-1.5" style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                <li>Click the <strong class="text-blue-600">"New User"</strong> button in the header</li>
                                <li>Fill in required information: name, email, password</li>
                                <li>Select the appropriate company (for Super Admin/Admin)</li>
                                <li>Assign a role based on their responsibilities</li>
                                <li>Toggle "Active" status and "Auto-approve time entries" as needed</li>
                                <li>Click "Create User" to send a verification email</li>
                            </ol>
                        </div>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r-lg">
                            <p class="text-sm text-yellow-800">
                                <strong>⚠️ Email Verification:</strong> New users will receive a verification email. They must verify their email before logging in.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions Section -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 h-7 w-7 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <svg class="h-4 w-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h4 class="ml-2.5 text-base font-semibold" style="color: var(--theme-text);">Bulk Actions</h4>
                    </div>
                    <div class="ml-11 space-y-3">
                        <p class="text-sm text-gray-700">Efficiently manage multiple users at once using bulk actions:</p>
                        <div class="grid grid-cols-1 gap-3">
                            <div class="border border-green-200 rounded-lg p-3 bg-green-50">
                                <h5 class="font-semibold text-green-800 mb-1 flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Activate Users
                                </h5>
                                <p class="text-sm text-green-700">Enable multiple users to access the system. They will be able to log in immediately.</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <h5 class="font-semibold text-gray-800 mb-1 flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Deactivate Users
                                </h5>
                                <p class="text-sm text-gray-700">Temporarily disable user access without deleting their data. Can be reversed anytime.</p>
                            </div>
                            <div class="border border-red-200 rounded-lg p-3 bg-red-50">
                                <h5 class="font-semibold text-red-800 mb-1 flex items-center">
                                    <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Delete Users (Soft Delete)
                                </h5>
                                <p class="text-sm text-red-700">Move users to "Deleted Users" where they can be restored or permanently deleted later.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Types Section -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 h-7 w-7 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <svg class="h-4 w-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h4 class="ml-2.5 text-base font-semibold" style="color: var(--theme-text);">Understanding Delete Operations</h4>
                    </div>
                    <div class="ml-11 space-y-3">
                        <div class="border-l-4 border-orange-400 bg-orange-50 p-4 rounded-r-lg">
                            <h5 class="font-semibold text-orange-800 mb-2 flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Soft Delete (Reversible)
                            </h5>
                            <ul class="list-disc list-inside space-y-1 text-sm text-orange-800">
                                <li>User is deactivated and moved to "Deleted Users"</li>
                                <li>All data (projects, time entries) remains intact</li>
                                <li>User can be restored anytime from "Deleted Users" page</li>
                                <li>This is the <strong>default delete action</strong></li>
                            </ul>
                        </div>
                        <div class="border-l-4 border-red-600 bg-red-50 p-4 rounded-r-lg">
                            <h5 class="font-semibold text-red-800 mb-2 flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Permanent Delete (Cannot be Undone!)
                            </h5>
                            <ul class="list-disc list-inside space-y-1 text-sm text-red-800">
                                <li><strong>Only available</strong> from "Deleted Users" page</li>
                                <li>User is <strong>permanently removed</strong> from database</li>
                                <li><strong>Cannot be undone</strong> - this action is irreversible!</li>
                                <li>Blocked if user has projects or time entries (protects data integrity)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Best Practices Section -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 h-7 w-7 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <svg class="h-4 w-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <h4 class="ml-2.5 text-base font-semibold" style="color: var(--theme-text);">Best Practices</h4>
                    </div>
                    <div class="ml-11">
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-teal-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Assign minimum required permissions</strong> - Follow principle of least privilege</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-teal-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Deactivate instead of delete</strong> when an employee leaves temporarily</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-teal-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Regular audits</strong> - Review user access quarterly to ensure accuracy</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-teal-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Auto-approve time entries</strong> - Enable only for highly trusted users</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-teal-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Use filters effectively</strong> - Narrow down users by status, role, or company</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-4 w-4 text-teal-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Never permanently delete users with data</strong> - Keep soft deleted for audit trail</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mb-2">
                    <div class="flex items-center mb-2">
                        <div class="flex-shrink-0 h-7 w-7 rounded-lg flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <svg class="h-4 w-4" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="ml-2.5 text-base font-semibold" style="color: var(--theme-text);">Frequently Asked Questions</h4>
                    </div>
                    <div class="ml-11 space-y-3">
                        <div>
                            <p class="font-semibold text-gray-800 mb-1">Q: Can I restore a permanently deleted user?</p>
                            <p class="text-sm text-gray-600 ml-4">A: <strong>No</strong>. Permanent deletion cannot be undone. Always use soft delete unless absolutely certain.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 mb-1">Q: What's the difference between inactive and deleted?</p>
                            <p class="text-sm text-gray-600 ml-4">A: <strong>Inactive</strong> users cannot log in but appear in user lists. <strong>Deleted</strong> users are moved to "Deleted Users" page.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 mb-1">Q: Can admins see super admin users?</p>
                            <p class="text-sm text-gray-600 ml-4">A: <strong>No</strong>. Company Admins can only view and manage users from their own company. Super Admins are hidden from them.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 mb-1">Q: What happens to a user's projects when deleted?</p>
                            <p class="text-sm text-gray-600 ml-4">A: <strong>Nothing changes</strong>. Soft delete preserves all relationships. Permanent delete is blocked if user has projects or time entries.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (fixed) -->
            <div class="px-5 py-3" style="background-color: rgba(var(--theme-border-rgb), 0.05); border-top: 1px solid rgba(var(--theme-border-rgb), 0.2); border-radius: 0 0 var(--theme-border-radius) var(--theme-border-radius);">
                <div class="flex justify-end items-center">
                    <button type="button"
                            onclick="closeHelpModal()"
                            class="inline-flex justify-center items-center px-4 py-2 font-semibold text-white transition-all duration-200"
                            style="font-size: var(--theme-button-font-size); background-color: var(--theme-primary); border-radius: var(--theme-border-radius);"
                            onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Got it!
                    </button>
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

    // Submit bulk action - FIXED VERSION using fetch instead of form.submit()
    function submitBulkAction(action) {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one user');
            return;
        }

        // Note: No confirm() needed here - modals handle confirmation

        // Gather form data
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('action', action);

        const userIds = [];
        checkedBoxes.forEach(checkbox => {
            formData.append('user_ids[]', checkbox.value);
            userIds.push(checkbox.value);
        });

        // DEBUG: Log what we're sending
        console.log('Submitting bulk action:', {
            action: action,
            user_ids: userIds,
            formDataEntries: Array.from(formData.entries())
        });

        // Submit via fetch (this works, form.submit() doesn't)
        fetch('{{ route("users.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                // Log de volledige response voor debugging
                return response.json().then(errorData => {
                    console.error('Validation errors:', errorData);
                    throw new Error('HTTP ' + response.status + ': ' + JSON.stringify(errorData));
                }).catch(() => {
                    throw new Error('HTTP ' + response.status);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Reload page on success
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
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

        // Help button (informational action)
        const helpBtn = document.getElementById('header-help-btn');
        if (helpBtn) {
            helpBtn.style.backgroundColor = '#3b82f6'; // Blue color
            helpBtn.style.color = 'white';
            helpBtn.style.border = 'none';
            helpBtn.style.borderRadius = 'var(--theme-border-radius)';
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
        modal.style.display = 'flex';
        modalLoading.classList.remove('hidden');
        modalContent.classList.add('hidden');

        // Reset scroll position to top
        const modalBody = modal.querySelector('.overflow-y-auto');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }

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
        modal.style.display = 'none';
    }

    // Open edit user modal
    function openEditUserModal(userId) {
        const modal = document.getElementById('edit-user-modal');
        const modalLoading = document.getElementById('edit-user-modal-loading');
        const modalContent = document.getElementById('edit-user-modal-content');

        // Show modal and loading state
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        modalLoading.classList.remove('hidden');
        modalContent.classList.add('hidden');

        // Reset scroll position to top
        const modalBody = modal.querySelector('.overflow-y-auto');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }

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
        modal.style.display = 'none';
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

    /**
     * Bulk Action Modal Functions
     */

    // Bulk Delete Modal
    function openBulkDeleteModal() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one user');
            return;
        }
        document.getElementById('deleteUserCount').textContent = checkedBoxes.length;
        document.getElementById('bulkDeleteModal').classList.remove('hidden');
    }

    function closeBulkDeleteModal() {
        document.getElementById('bulkDeleteModal').classList.add('hidden');
    }

    function confirmBulkDelete() {
        closeBulkDeleteModal();
        submitBulkAction('delete');
    }

    // ESC key support for bulk action modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const activateModal = document.getElementById('bulkActivateModal');
            const deactivateModal = document.getElementById('bulkDeactivateModal');
            const deleteModal = document.getElementById('bulkDeleteModal');
            const individualDeleteModal = document.getElementById('deleteUserModal');

            if (!activateModal.classList.contains('hidden')) {
                closeBulkActivateModal();
            }
            if (!deactivateModal.classList.contains('hidden')) {
                closeBulkDeactivateModal();
            }
            if (!deleteModal.classList.contains('hidden')) {
                closeBulkDeleteModal();
            }
            if (!individualDeleteModal.classList.contains('hidden')) {
                closeDeleteUserModal();
            }
        }
    });

    /**
     * Individual Delete User Modal Functions
     */
    function openDeleteUserModal(userId, userName, userEmail) {
        // Set user info in modal
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteUserEmail').textContent = userEmail;

        // Set form action
        const form = document.getElementById('deleteUserForm');
        form.action = `/users/${userId}`;

        // Show modal
        document.getElementById('deleteUserModal').classList.remove('hidden');
    }

    function closeDeleteUserModal() {
        document.getElementById('deleteUserModal').classList.add('hidden');
    }

    /**
     * Help Modal Functions
     */
    function openHelpModal() {
        const modal = document.getElementById('helpModal');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        // Reset scroll position to top
        const modalBody = modal.querySelector('.overflow-y-auto');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }
    }

    function closeHelpModal() {
        const modal = document.getElementById('helpModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    // Add ESC key support for help modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const helpModal = document.getElementById('helpModal');
            if (helpModal && !helpModal.classList.contains('hidden')) {
                closeHelpModal();
            }

            // Close status dropdown
            const dropdown = document.getElementById('status-dropdown');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }

            // Close status modal
            closeBulkStatusModal();
        }
    });

    /**
     * FLOATING BULK ACTIONS BAR - NIEUWE STANDAARD
     */

    // Update floating bar visibility when checkboxes change
    function updateBulkActionsVisibility() {
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        const floatingBar = document.getElementById('floating-bulk-actions');
        const selectedCount = document.getElementById('floating-selected-count');

        if (checkboxes.length > 0) {
            floatingBar.style.transform = 'translateY(0)';
            selectedCount.textContent = checkboxes.length + ' selected';
        } else {
            floatingBar.style.transform = 'translateY(100%)';
        }
    }

    // Clear all selections
    function clearAllSelections() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }

        updateBulkActionsVisibility();
    }

    // Status dropdown toggle with dynamic positioning
    function toggleStatusDropdown(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('status-dropdown');
        const button = document.getElementById('status-dropdown-btn');

        if (dropdown.classList.contains('hidden')) {
            const buttonRect = button.getBoundingClientRect();
            dropdown.style.left = buttonRect.left + 'px';
            dropdown.style.bottom = (window.innerHeight - buttonRect.top + 8) + 'px';
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('status-dropdown');
        const btn = document.getElementById('status-dropdown-btn');
        if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Update checkboxes to trigger visibility
    document.getElementById('select-all')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionsVisibility();
    });

    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionsVisibility();

            // Update select all checkbox
            const allCheckboxes = document.querySelectorAll('.user-checkbox');
            const selectAllCheckbox = document.getElementById('select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = Array.from(allCheckboxes).every(cb => cb.checked);
            }
        });
    });

    // Universal Status Change Modal met HARDCODED kleuren
    let currentStatusAction = '';

    const statusConfig = {
        'activate': {
            title: 'Activate Users',
            icon: 'fas fa-check-circle',
            color: '#10b981',              // Hardcoded green
            bgColor: '#d1fae5',            // Light green background
            btnColor: '#10b981',           // Green button
            action: 'activate',
            displayName: 'activate'
        },
        'deactivate': {
            title: 'Deactivate Users',
            icon: 'fas fa-times-circle',
            color: '#6b7280',              // Hardcoded gray
            bgColor: '#f3f4f6',            // Light gray background
            btnColor: '#6b7280',           // Gray button
            action: 'deactivate',
            displayName: 'deactivate'
        }
    };

    function openBulkStatusModal(status) {
        const count = document.querySelectorAll('.user-checkbox:checked').length;
        if (count === 0) return;

        // Close dropdown
        document.getElementById('status-dropdown').classList.add('hidden');

        // Get config for this status
        const config = statusConfig[status];
        currentStatusAction = status;

        // Update modal content
        document.getElementById('statusModalCount').textContent = count;
        document.getElementById('statusModalTitle').textContent = config.title;
        document.getElementById('statusModalAction').textContent = config.displayName;

        // Update icon
        const iconContainer = document.getElementById('statusModalIcon');
        const iconContent = document.getElementById('statusModalIconContent');
        iconContainer.style.backgroundColor = config.bgColor;
        iconContent.className = config.icon + ' text-2xl';
        iconContent.style.color = config.color;

        // Update button met HARDCODED styling voor maximale zichtbaarheid
        const confirmBtn = document.getElementById('statusModalConfirmBtn');
        confirmBtn.style.backgroundColor = config.btnColor;
        confirmBtn.style.color = '#ffffff';           // ALTIJD witte tekst
        confirmBtn.style.border = 'none';             // Geen border
        confirmBtn.style.fontSize = '14px';           // Vaste font size
        confirmBtn.style.fontWeight = '600';          // Bold tekst
        confirmBtn.style.padding = '0.5rem 1rem';     // Padding
        confirmBtn.style.borderRadius = '0.5rem';     // Rounded corners

        // Show modal
        document.getElementById('bulkStatusModal').style.display = 'flex';
    }

    function closeBulkStatusModal() {
        document.getElementById('bulkStatusModal').style.display = 'none';
        currentStatusAction = '';
    }

    function confirmBulkStatusChange() {
        // Bewaar de actie VOOR we de modal sluiten (die zet currentStatusAction = '')
        const actionToPerform = currentStatusAction;
        closeBulkStatusModal();
        submitBulkAction(actionToPerform);
    }
</script>
@endpush