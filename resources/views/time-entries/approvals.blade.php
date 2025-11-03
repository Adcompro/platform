@extends('layouts.app')

@section('title', 'Time Entry Approvals')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Consistent with other modules --}}
    <div class="bg-white/70 backdrop-blur-sm" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold" style="color: var(--theme-text);">Time Entry Approvals</h1>
                    <p class="text-sm" style="color: var(--theme-text-muted);">Review and approve pending time entries</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('time-entries.index') }}" class="inline-flex items-center px-4 py-2 text-white text-xs font-semibold uppercase tracking-widest transition-all duration-200" style="background-color: var(--theme-text-muted); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.85)'" onmouseout="this.style.filter='brightness(1)'">
                        ← Back to Time Entries
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 px-3 py-2.5" style="background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.05); border: 1px solid rgba(var(--theme-accent-rgb, 5, 150, 105), 0.2); border-radius: var(--theme-border-radius);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: var(--theme-accent);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium" style="color: var(--theme-accent);">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-3 py-2.5" style="background-color: rgba(var(--theme-danger-rgb, 239, 68, 68), 0.05); border: 1px solid rgba(var(--theme-danger-rgb, 239, 68, 68), 0.2); border-radius: var(--theme-border-radius);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: var(--theme-danger);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium" style="color: var(--theme-danger);">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Statistics Cards - Moderne minimale stijl --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="theme-card p-3 hover:shadow-md transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs" style="color: var(--theme-text-muted);">Pending Approval</p>
                        <p class="text-xl font-semibold mt-1" style="color: #f59e0b;">{{ $stats['pending_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-2 rounded-lg" style="background-color: rgba(245, 158, 11, 0.1);">
                        <svg class="w-5 h-5" style="color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="theme-card p-3 hover:shadow-md transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs" style="color: var(--theme-text-muted);">Pending Hours</p>
                        <p class="text-xl font-semibold mt-1" style="color: var(--theme-primary);">{{ number_format($stats['pending_hours'] ?? 0, 1) }}h</p>
                    </div>
                    <div class="p-2 rounded-lg" style="background-color: rgba(var(--theme-primary-rgb, 37, 99, 235), 0.1);">
                        <svg class="w-5 h-5" style="color: var(--theme-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="theme-card p-3 hover:shadow-md transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs" style="color: var(--theme-text-muted);">Approved Today</p>
                        <p class="text-xl font-semibold mt-1" style="color: var(--theme-accent);">{{ $stats['approved_today'] ?? 0 }}</p>
                    </div>
                    <div class="p-2 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.1);">
                        <svg class="w-5 h-5" style="color: var(--theme-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="theme-card p-3 hover:shadow-md transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.1); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs" style="color: var(--theme-text-muted);">Rejected Today</p>
                        <p class="text-xl font-semibold mt-1" style="color: var(--theme-danger);">{{ $stats['rejected_today'] ?? 0 }}</p>
                    </div>
                    <div class="p-2 rounded-lg" style="background-color: rgba(var(--theme-danger-rgb, 239, 68, 68), 0.1);">
                        <svg class="w-5 h-5" style="color: var(--theme-danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="theme-card bg-white/80 backdrop-blur-sm p-6 mb-4" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                <h2 class="text-[17px] font-semibold" style="color: var(--theme-text);">Filters</h2>
            </div>
            <div>
                <form method="GET" action="{{ route('time-entries.approvals') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label for="status" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Status</label>
                        <select id="status" name="status" class="w-full px-3 py-1.5 rounded-lg transition-colors" style=" border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            <option value="pending" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="">All</option>
                        </select>
                    </div>

                    <div>
                        <label for="user_id" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">User</label>
                        <select id="user_id" name="user_id" class="w-full px-3 py-1.5 rounded-lg transition-colors" style=" border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            <option value="">All Users</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="project_id" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Project</label>
                        <select id="project_id" name="project_id" class="w-full px-3 py-1.5 rounded-lg transition-colors" style=" border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                            <option value="">All Projects</option>
                            @foreach($projects ?? [] as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">From Date</label>
                        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" 
                               class="w-full px-3 py-1.5 rounded-lg transition-colors" style=" border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">To Date</label>
                        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" 
                               class="w-full px-3 py-1.5 rounded-lg transition-colors" style=" border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                    </div>

                    <div class="md:col-span-2 lg:col-span-5 flex justify-end space-x-2">
                        <button type="submit" class="theme-btn-primary px-3 py-1.5 text-white text-sm font-medium transition-all duration-200" style="border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                            Apply Filters
                        </button>
                        <a href="{{ route('time-entries.approvals') }}" class="px-3 py-1.5 text-sm font-medium transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2); color: var(--theme-text); border-radius: var(--theme-border-radius);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.3)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.2)'">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk Actions --}}
        @if(($timeEntries ?? collect())->count() > 0 && request('status', 'pending') == 'pending')
        <div class="p-3 mb-4" style="background-color: rgba(var(--theme-primary-rgb, 37, 99, 235), 0.05); border: 1px solid rgba(var(--theme-primary-rgb, 37, 99, 235), 0.2); border-radius: var(--theme-border-radius);">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="select-all" class="h-3.5 w-3.5 rounded" style="color: var(--theme-primary);">
                    <label for="select-all" class="ml-2 text-sm" style="color: var(--theme-text);">Select All</label>
                </div>
                <div class="flex space-x-2">
                    <button onclick="bulkApprove()" class="px-2.5 py-1 text-white text-xs font-medium transition-all duration-200 flex items-center" style="background-color: var(--theme-accent); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Bulk Approve
                    </button>
                    <button onclick="openBulkRejectModal()" class="px-2.5 py-1 text-white text-xs font-medium transition-all duration-200 flex items-center" style="background-color: var(--theme-danger); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Bulk Reject
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Time Entries List --}}
        <div class="theme-card bg-white/80 backdrop-blur-sm p-6 pb-32" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                <h2 class="text-[17px] font-semibold" style="color: var(--theme-text);">Time Entries</h2>
            </div>
            
            @if(($timeEntries ?? collect())->count() > 0)
                <div class="divide-y" style="--tw-divide-opacity: 0.3;">
                    @foreach($timeEntries as $entry)
                        <div class="py-3 transition-colors" style="border-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                            <div class="flex items-start">
                                @if(request('status', 'pending') == 'pending')
                                <div class="flex-shrink-0 mr-2 pt-0.5">
                                    <input type="checkbox" name="time_entries[]" value="{{ $entry->id }}" class="time-entry-checkbox h-3.5 w-3.5 rounded" style="color: var(--theme-primary);">
                                </div>
                                @endif
                                
                                <div class="flex-1">
                                    {{-- Compact Header Row --}}
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-x-4">
                                                {{-- Date first - fixed width --}}
                                                <span class="text-sm font-medium w-14" style="color: var(--theme-text-muted); ">
                                                    {{ \Carbon\Carbon::parse($entry->entry_date)->format('d M') }}
                                                </span>
                                                
                                                {{-- User name - fixed width with more spacing --}}
                                                <h4 class="text-sm font-medium min-w-[150px]" style="color: var(--theme-text); ">
                                                    {{ $entry->user->name }}
                                                </h4>
                                                
                                                {{-- Hours with left margin --}}
                                                <span class="text-sm font-medium ml-2" style="color: var(--theme-text); ">
                                                    {{ number_format($entry->hours, 1) }}h
                                                </span>
                                                
                                                {{-- Status --}}
                                                <span class="px-2 py-0.5 rounded text-xs font-medium" style="
                                                    {{ $entry->status === 'approved' ? 'background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.1); color: var(--theme-accent);' : 
                                                    ($entry->status === 'rejected' ? 'background-color: rgba(var(--theme-danger-rgb, 239, 68, 68), 0.1); color: var(--theme-danger);' : 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;') }}
                                                ">
                                                    {{ ucfirst($entry->status) }}
                                                </span>
                                                
                                                {{-- Billable indicator --}}
                                                @if($entry->is_billable === 'billable')
                                                    <span class="text-xs font-medium" style="color: var(--theme-accent);">€</span>
                                                @endif
                                            </div>
                                            
                                            {{-- Project and path on same line --}}
                                            <div class="mt-1 text-sm" style="color: var(--theme-text-muted); ">
                                                <span class="font-medium">{{ $entry->project->name }}</span>
                                                @if($entry->work_item_path)
                                                    <span class="mx-1" style="color: var(--theme-text-muted); opacity: 0.5;">›</span>
                                                    <span style="color: var(--theme-text-muted);">{{ Str::limit($entry->work_item_path, 60) }}</span>
                                                @endif
                                            </div>
                                            
                                            {{-- Description --}}
                                            <div class="mt-1 text-sm italic line-clamp-2" style="color: var(--theme-text); ">
                                                {{ $entry->description }}
                                            </div>
                                            
                                            @if($entry->status === 'rejected' && $entry->rejection_reason)
                                                <div class="mt-2 p-2 rounded-lg" style="background-color: rgba(var(--theme-danger-rgb, 239, 68, 68), 0.05); border-radius: var(--theme-border-radius);">
                                                    <p class="text-xs" style="color: var(--theme-danger);">
                                                        <span class="font-medium">Rejection reason:</span> {{ $entry->rejection_reason }}
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            @if($entry->status === 'approved' && $entry->approver)
                                                <div class="mt-2 p-2 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb, 5, 150, 105), 0.05); border-radius: var(--theme-border-radius);">
                                                    <p class="text-xs" style="color: var(--theme-accent);">
                                                        @if($entry->approved_by === $entry->user_id)
                                                            <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                                            </svg>
                                                            <span class="font-medium">Auto-approved</span> on {{ \App\Helpers\DateHelper::format($entry->approved_at) }}
                                                            <span class="text-xs" style="color: var(--theme-accent);">(User has auto-approve enabled)</span>
                                                        @else
                                                            <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Approved by <span class="font-medium">{{ $entry->approver->name }}</span> on {{ \App\Helpers\DateHelper::format($entry->approved_at) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            @if($entry->status === 'rejected' && $entry->approver)
                                                <div class="mt-2 text-xs" style="color: var(--theme-text-muted);">
                                                    Rejected by <span class="font-medium">{{ $entry->approver->name }}</span> on {{ \App\Helpers\DateHelper::format($entry->approved_at) }}
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Compact Action Buttons --}}
                                        <div class="ml-2 flex-shrink-0 flex items-center space-x-1">
                                            @if($entry->status === 'pending')
                                                <button onclick="approveEntry({{ $entry->id }})" 
                                                        class="p-1 rounded transition-colors" 
                                                        style="color: var(--theme-accent);" 
                                                        onmouseover="this.style.backgroundColor='rgba(var(--theme-accent-rgb, 5, 150, 105), 0.1)'" 
                                                        onmouseout="this.style.backgroundColor='transparent'"
                                                        title="Approve">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                                <button onclick="openRejectModal({{ $entry->id }})" 
                                                        class="p-1 rounded transition-colors"
                                                        style="color: var(--theme-danger);" 
                                                        onmouseover="this.style.backgroundColor='rgba(var(--theme-danger-rgb, 239, 68, 68), 0.1)'" 
                                                        onmouseout="this.style.backgroundColor='transparent'"
                                                        title="Reject">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                            <a href="{{ route('time-entries.index') }}#entry-{{ $entry->id }}"
                                               class="p-1 rounded transition-colors"
                                               style="color: var(--theme-text-muted);"
                                               onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.1)'"
                                               onmouseout="this.style.backgroundColor='transparent'"
                                               title="View in List">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                {{-- Pagination --}}
                @if($timeEntries->hasPages())
                    <div class="px-4 py-3" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                        {{ $timeEntries->links() }}
                    </div>
                @endif
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12" style="color: var(--theme-text-muted); opacity: 0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm" style="color: var(--theme-text-muted); ">No time entries found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-4 w-96 bg-white" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
        <div class="mt-2">
            <h3 class="text-base font-medium" style="color: var(--theme-text);">Reject Time Entry</h3>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="mt-3">
                    <label for="rejection_reason" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Rejection Reason</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                        class="w-full px-3 py-1.5 rounded-lg transition-colors"
                        style=" border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;"
                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'"
                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'"
                        placeholder="Please provide a reason for rejection..."></textarea>
                </div>
                
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="closeRejectModal()" class="px-3 py-1.5 text-sm font-medium transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2); color: var(--theme-text); border-radius: var(--theme-border-radius);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.3)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.2)'">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1.5 text-white text-sm font-medium transition-all duration-200" style="background-color: var(--theme-danger); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Reject Modal --}}
<div id="bulkRejectModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative top-20 mx-auto p-4 w-96 bg-white" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow);">
        <div class="mt-2">
            <h3 class="text-base font-medium" style="color: var(--theme-text);">Bulk Reject Time Entries</h3>
            <form id="bulkRejectForm" method="POST" action="{{ route('time-entries.bulk-reject') }}">
                @csrf
                <input type="hidden" id="bulk-reject-ids" name="time_entry_ids">
                <div class="mt-3">
                    <label for="bulk_rejection_reason" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Rejection Reason</label>
                    <textarea id="bulk_rejection_reason" name="rejection_reason" rows="3" required
                        class="w-full px-3 py-1.5 rounded-lg transition-colors"
                        style=" border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5); color: var(--theme-text); background: white;"
                        onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'"
                        onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'"
                        placeholder="Please provide a reason for rejection..."></textarea>
                </div>
                
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="closeBulkRejectModal()" class="px-3 py-1.5 text-sm font-medium transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2); color: var(--theme-text); border-radius: var(--theme-border-radius);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.3)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.2)'">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1.5 text-white text-sm font-medium transition-all duration-200" style="background-color: var(--theme-danger); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                        Reject Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all checkbox
document.getElementById('select-all')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.time-entry-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Approve single entry
function approveEntry(id) {
    if (confirm('Are you sure you want to approve this time entry?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/time-entries/${id}/approve`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Open reject modal
function openRejectModal(id) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/time-entries/${id}/reject`;
    modal.classList.remove('hidden');
}

// Close reject modal
function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
}

// Bulk approve
function bulkApprove() {
    const selected = getSelectedEntries();
    if (selected.length === 0) {
        alert('Please select at least one time entry');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${selected.length} time entries?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("time-entries.bulk-approve") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'time_entry_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Open bulk reject modal
function openBulkRejectModal() {
    const selected = getSelectedEntries();
    if (selected.length === 0) {
        alert('Please select at least one time entry');
        return;
    }
    
    document.getElementById('bulk-reject-ids').value = JSON.stringify(selected);
    document.getElementById('bulkRejectModal').classList.remove('hidden');
}

// Close bulk reject modal
function closeBulkRejectModal() {
    document.getElementById('bulkRejectModal').classList.add('hidden');
}

// Get selected entries
function getSelectedEntries() {
    const checkboxes = document.querySelectorAll('.time-entry-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Handle bulk reject form submission
document.getElementById('bulkRejectForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const selected = JSON.parse(document.getElementById('bulk-reject-ids').value);
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = this.action;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);
    
    const reason = document.createElement('input');
    reason.type = 'hidden';
    reason.name = 'rejection_reason';
    reason.value = document.getElementById('bulk_rejection_reason').value;
    form.appendChild(reason);
    
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'time_entry_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
});
</script>
@endpush