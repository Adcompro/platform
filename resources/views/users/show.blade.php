@extends('layouts.app')

@section('title', $user->name)

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
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center border border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-weight: 600; font-size: var(--theme-button-font-size); color: white; text-transform: uppercase; letter-spacing: 0.05em; background-color: var(--theme-primary); border-color: transparent;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit User
                    </a>
                    @endif
                    <a href="{{ route('users.index') }}" class="inline-flex items-center border border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-weight: 600; font-size: var(--theme-button-font-size); text-transform: uppercase; letter-spacing: 0.05em; background-color: rgba(var(--theme-border-rgb), 0.2); color: var(--theme-text-muted); border-color: transparent;" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.3)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'">
                        ← Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 px-3 py-2.5 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb), 0.1); border: 1px solid rgba(var(--theme-accent-rgb), 0.3); color: var(--theme-accent);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-accent-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500;">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- User Profile Card --}}
            <div class="lg:col-span-1">
                <div class="rounded-lg" style="background-color: white; border: 1px solid rgba(var(--theme-border-rgb), 0.2); overflow: hidden; box-shadow: var(--theme-card-shadow);">
                    <div class="bg-gradient-to-br from-slate-400 to-slate-500" style="padding: 2rem 1.5rem;">
                        <div class="flex justify-center">
                            <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center">
                                <span style="font-size: 1.5rem; font-weight: 700; color: var(--theme-text-muted);">{{ substr($user->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <h2 style="margin-top: 0.75rem; text-align: center; font-size: 1.125rem; font-weight: 600; color: white;">{{ $user->name }}</h2>
                        <p style="text-align: center; font-size: var(--theme-font-size); color: #e2e8f0;">{{ $user->email }}</p>
                    </div>
                    <div style="padding: 1.25rem 1.5rem;">
                        <dl class="space-y-4">
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Role</dt>
                                <dd>
                                    <span class="px-2 py-0.5 inline-flex rounded-lg {{ $user->role_badge_class }}" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500;">
                                        {{ $user->role_description }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Status</dt>
                                <dd>
                                    @if($user->is_active)
                                        <span class="px-2 py-0.5 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);">
                                            Inactive
                                        </span>
                                    @endif
                                    @if($user->auto_approve_time_entries)
                                        <span class="px-2 py-0.5 inline-flex items-center rounded-lg ml-1" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);">
                                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                            </svg>
                                            Auto-approve
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Company</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->company ? $user->company->name : 'No Company' }}</dd>
                            </div>
                            @if($user->phone)
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Phone</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->phone }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Member Since</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ \App\Helpers\DateHelper::formatDate($user->created_at) }}</dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Last Login</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    {{ $user->last_login_at ? \App\Helpers\DateHelper::format($user->last_login_at) : 'Never' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="mt-4 rounded-lg" style="background-color: white; border: 1px solid rgba(var(--theme-border-rgb), 0.2); padding: 1.5rem; box-shadow: var(--theme-card-shadow);">
                    <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.1);">
                        <h3 style="font-size: 1.0625rem; font-weight: 600; color: var(--theme-text);">Statistics</h3>
                    </div>
                    <div>
                        <dl class="space-y-3">
                            <div class="flex justify-between items-center">
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Total Projects</dt>
                                <dd style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $stats['total_projects'] }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Active Projects</dt>
                                <dd style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-accent);">{{ $stats['active_projects'] }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Total Hours Logged</dt>
                                <dd style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ number_format($stats['total_hours'], 2) }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Pending Hours</dt>
                                <dd style="font-size: var(--theme-font-size); font-weight: 500; color: #d97706;">{{ number_format($stats['pending_hours'], 2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Activity and Projects --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Recent Projects --}}
                <div class="rounded-lg p-6" style="background-color: white; border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: var(--theme-card-shadow);">
                    <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.1);">
                        <h3 style="font-size: 1.0625rem; font-weight: 600; color: var(--theme-text);">Assigned Projects</h3>
                    </div>
                    <div style="border-top: 1px solid rgba(var(--theme-border-rgb), 0.1); border-color: rgba(var(--theme-border-rgb), 0.1);">
                        @forelse($user->projects()->limit(5)->get() as $project)
                        <div class="px-4 py-3 transition-colors" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.1);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <a href="{{ route('projects.show', $project) }}" style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);" onmouseover="this.style.color='var(--theme-primary)'" onmouseout="this.style.color='var(--theme-text)'">
                                        {{ $project->name }}
                                    </a>
                                    <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">{{ $project->customer ? $project->customer->name : 'No Customer' }}</p>
                                </div>
                                <div class="ml-4">
                                    <span class="px-2 py-0.5 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; 
                                        @if($project->status == 'active')" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);"
                                        @elseif($project->status == 'completed')" style="background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);"
                                        @elseif($project->status == 'on_hold')" style="background-color: rgba(255, 193, 7, 0.2); color: #d97706;"
                                        @else" style="background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);"
                                        @endif>
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-3" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                                <div>
                                    <span class="font-medium">Role:</span> 
                                    {{ $project->pivot->role_override ?? $user->role_description }}
                                </div>
                                <div>
                                    <span class="font-medium">Permissions:</span>
                                    <div class="inline-flex space-x-1 ml-1">
                                        @if($project->pivot->can_edit_fee)
                                            <span style="color: var(--theme-accent);" title="Can edit fee">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        @endif
                                        @if($project->pivot->can_view_financials)
                                            <span style="color: var(--theme-primary);" title="Can view financials">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                                </svg>
                                            </span>
                                        @endif
                                        @if($project->pivot->can_log_time)
                                            <span style="color: #9333ea;" title="Can log time">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        @endif
                                        @if($project->pivot->can_approve_time)
                                            <span style="color: #ea580c;" title="Can approve time">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="px-6 py-8 text-center">
                            <svg class="mx-auto h-12 w-12" style="color: rgba(var(--theme-border-rgb), 0.3);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">No projects assigned yet</p>
                        </div>
                        @endforelse
                    </div>
                    @if($user->projects->count() > 5)
                    <div class="px-6 py-3 text-center" style="background-color: rgba(var(--theme-border-rgb), 0.05); border-top: 1px solid rgba(var(--theme-border-rgb), 0.1);">
                        <a href="{{ route('projects.index', ['user_id' => $user->id]) }}" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);" onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                            View all {{ $user->projects->count() }} projects →
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Account Information --}}
                <div class="backdrop-blur-sm rounded-xl overflow-hidden theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6);">
                    <div class="px-6 py-4" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                        <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Account Information</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Full Name</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                    {{ $user->first_name && $user->last_name ? $user->first_name . ' ' . $user->last_name : $user->name }}
                                </dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Email Address</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Email Verified</dt>
                                <dd style="font-size: var(--theme-font-size);">
                                    @if($user->email_verified_at)
                                        <span class="flex items-center" style="color: var(--theme-accent);">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Verified on {{ \App\Helpers\DateHelper::formatDate($user->email_verified_at) }}
                                        </span>
                                    @else
                                        <span class="flex items-center" style="color: var(--theme-danger);">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Not verified
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Account Created</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ \App\Helpers\DateHelper::format($user->created_at) }}</dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">Last Updated</dt>
                                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ \App\Helpers\DateHelper::format($user->updated_at) }}</dd>
                            </div>
                            <div>
                                <dt style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; margin-bottom: 0.375rem; color: var(--theme-text-muted);">User ID</dt>
                                <dd style="font-size: var(--theme-font-size); font-family: monospace; color: var(--theme-text);">#{{ $user->id }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection