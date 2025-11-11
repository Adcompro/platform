@extends('layouts.app')

@section('title', 'Time Entries')

@section('content')
{{-- Sticky Header - Exact Copy Theme Settings --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Time Entries</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Track and manage your time entries with AI-powered creation</p>
            </div>
            <div class="flex items-center gap-3">
                @if(request('return_to_project'))
                <a href="{{ route('projects.show', request('return_to_project')) }}"
                   id="header-close-btn"
                   class="header-btn"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size); background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);">
                    <i class="fas fa-times mr-1.5"></i>
                    Close
                </a>
                @endif

                <button type="button" onclick="event.preventDefault(); openTimeEntryModal(); return false;" id="header-create-btn"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-plus mr-1.5"></i>
                    New Time Entry
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Main Content - Exact Copy Theme Settings --}}
<div style="padding: 1.5rem 2rem;">

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Hours This Week --}}
        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-primary-rgb), 0.05);">
            <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-primary);">
                {{ number_format($stats['this_week_hours'] ?? 0, 1) }}h
            </div>
            <div style="font-size: var(--theme-font-size); color: var(--theme-primary);">
                This Week
            </div>
        </div>

        {{-- Total Hours --}}
        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-success-rgb), 0.05);">
            <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-success);">
                {{ number_format($stats['total_hours'] ?? 0, 1) }}h
            </div>
            <div style="font-size: var(--theme-font-size); color: var(--theme-success);">
                Total Hours
            </div>
        </div>

        {{-- Billable Hours --}}
        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(245, 158, 11, 0.05);">
            <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: #f59e0b;">
                {{ number_format($stats['billable_hours'] ?? 0, 1) }}h
            </div>
            <div style="font-size: var(--theme-font-size); color: #f59e0b;">
                Billable Hours
            </div>
        </div>

        {{-- Pending Entries --}}
        <div class="rounded-lg border border-slate-200/60" style="padding: 1rem; background-color: rgba(var(--theme-danger-rgb), 0.05);">
            <div style="font-size: calc(var(--theme-font-size) + 8px); font-weight: 700; color: var(--theme-danger);">
                {{ $stats['pending_count'] ?? 0 }}
            </div>
            <div style="font-size: var(--theme-font-size); color: var(--theme-danger);">
                Pending
            </div>
        </div>
    </div>

    {{-- Filters Card - Verberg als we vanuit een project komen --}}
    @if(!request('return_to_project'))
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Filter Time Entries</h2>
        </div>
        <div style="padding: var(--theme-card-padding);">
            <div>
                <form method="GET" action="{{ route('time-entries.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    {{-- Project Filter --}}
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Project
                        </label>
                        <select name="project_id" id="filter_project_id"
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}@if($project->customer) ({{ $project->customer->name }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- User Filter (for admins) --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            User
                        </label>
                        <select name="user_id" id="filter_user_id"
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            <option value="">All Users</option>
                            @foreach(App\Models\User::whereIn('company_id', function($q) { $q->select('id')->from('companies'); })->get() as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Status Filter --}}
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Status
                        </label>
                        <select name="status" id="filter_status"
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            From Date
                        </label>
                        <input type="date" name="date_from" id="filter_date_from"
                               value="{{ request('date_from') }}"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            To Date
                        </label>
                        <input type="date" name="date_to" id="filter_date_to"
                               value="{{ request('date_to') }}"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                    </div>

                    {{-- Filter Actions --}}
                    <div class="flex items-end space-x-2 md:col-span-2 lg:col-span-5">
                        <button type="submit" id="header-filter-btn"
                                style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                            Filter
                        </button>
                        <a href="{{ route('time-entries.index') }}"
                           style="padding: 0.5rem 1rem; background-color: rgba(107, 114, 128, 0.1); color: #6b7280; text-decoration: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                            Clear
                        </a>
                        <div class="flex space-x-1.5 ml-3">
                            <button type="button" onclick="event.preventDefault(); setQuickFilter('today')"
                                    style="padding: 0.25rem 0.5rem; background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 2px);">
                                Today
                            </button>
                            <button type="button" onclick="event.preventDefault(); setQuickFilter('week')"
                                    style="padding: 0.25rem 0.5rem; background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 2px);">
                                This Week
                            </button>
                            <button type="button" onclick="event.preventDefault(); setQuickFilter('month')"
                                    style="padding: 0.25rem 0.5rem; background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary); border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 2px);">
                                This Month
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

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

    {{-- Time Entries List Card --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <div class="flex justify-between items-center">
                <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Time Entries</h2>
                <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                    {{ $timeEntries->count() }} total entries
                </div>
            </div>
        </div>
        <div style="padding: var(--theme-card-padding);">

            @if($timeEntries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                        <thead>
                            <tr>
                                <th style="padding: 0.5rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); text-transform: uppercase; letter-spacing: 0.05em;">
                                    Date & Time
                                </th>
                                <th style="padding: 0.5rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); text-transform: uppercase; letter-spacing: 0.05em;">
                                    Project & Work Item
                                </th>
                                <th style="padding: 0.5rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); text-transform: uppercase; letter-spacing: 0.05em;">
                                    Description
                                </th>
                                <th style="padding: 0.5rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); text-transform: uppercase; letter-spacing: 0.05em;">
                                    Status
                                </th>
                                <th style="padding: 0.5rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); text-transform: uppercase; letter-spacing: 0.05em;">
                                    User
                                </th>
                                <th style="padding: 0.5rem 1rem; text-align: left; font-size: calc(var(--theme-font-size) - 2px); text-transform: uppercase; letter-spacing: 0.05em;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/60 divide-y" style="border-color: rgba(203, 213, 225, 0.3);">
                            @foreach($timeEntries as $entry)
                                <tr data-time-entry-id="{{ $entry->id }}" data-project-id="{{ $entry->project_id }}" data-selected-work-item="{{ $entry->project_task_id ? 'task:' . $entry->project_task_id : ($entry->project_milestone_id ? 'milestone:' . $entry->project_milestone_id : '') }}">
                                    {{-- Date & Time --}}
                                    <td style="padding: 0.5rem 1rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                        <div style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                            {{ \App\Helpers\DateHelper::formatDate($entry->entry_date ?? $entry->date) }}
                                        </div>
                                        <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.125rem;">
                                            {{ $entry->formatted_duration }}
                                            @if($entry->is_billable === 'billable')
                                                <span class="inline-flex px-1.5 py-0.5 rounded-full"
                                                      style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success); margin-left: 0.25rem;">
                                                    Billable
                                                </span>
                                            @else
                                                <span class="inline-flex px-1.5 py-0.5 rounded-full"
                                                      style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 600; background-color: rgba(107, 114, 128, 0.1); color: #6b7280; margin-left: 0.25rem;">
                                                    Non-billable
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Project & Work Item --}}
                                    <td style="padding: 0.5rem 1rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                                        <div style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                                            {{ $entry->project->name }}
                                        </div>
                                        <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.125rem;">
                                            {{ $entry->work_item_path }}
                                        </div>
                                        @if($entry->is_service_item)
                                            <div style="font-size: calc(var(--theme-font-size) - 2px); color: #2563eb; font-weight: 500; margin-top: 0.125rem;">
                                                📦 Service Item
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Description --}}
                                    <td style="padding: 0.5rem 1rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                                        <div style="font-size: var(--theme-font-size); color: var(--theme-text); max-width: 20rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $entry->description }}">
                                            {{ $entry->description }}
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td style="padding: 0.5rem 1rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                        @php
                                            $statusColors = [
                                                'draft' => ['bg' => 'rgba(107, 114, 128, 0.1)', 'color' => '#6b7280'],
                                                'submitted' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                                                'pending' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                                                'approved' => ['bg' => 'rgba(var(--theme-success-rgb), 0.1)', 'color' => 'var(--theme-success)'],
                                                'rejected' => ['bg' => 'rgba(var(--theme-danger-rgb), 0.1)', 'color' => 'var(--theme-danger)'],
                                            ];
                                            $colors = $statusColors[$entry->status] ?? $statusColors['draft'];
                                        @endphp
                                        <div>
                                            <span class="inline-flex px-1.5 py-0.5 rounded-full"
                                                  style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 600; background-color: {{ $colors['bg'] }}; color: {{ $colors['color'] }};">
                                                {{ ucfirst($entry->status) }}
                                                @if($entry->status === 'approved' && $entry->approved_by === $entry->user_id)
                                                    <svg class="w-3 h-3 ml-0.5" fill="currentColor" viewBox="0 0 20 20" title="Auto-approved">
                                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </span>

                                            {{-- Invoice Status --}}
                                            @if($entry->is_finalized)
                                                <span class="inline-flex px-1.5 py-0.5 rounded-full ml-1"
                                                      style="font-size: calc(var(--theme-font-size) - 4px); font-weight: 600; background-color: rgba(34, 197, 94, 0.1); color: #22c55e;"
                                                      title="Invoice {{ $entry->final_invoice_number }} has been finalized">
                                                    ✅ Finalized
                                                </span>
                                            @elseif($entry->is_invoiced)
                                                <span class="inline-flex px-1.5 py-0.5 rounded-full ml-1"
                                                      style="font-size: calc(var(--theme-font-size) - 4px); font-weight: 600; background-color: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                                    📄 Invoiced
                                                </span>
                                            @endif

                                            {{-- Defer status indicator --}}
                                            @if($entry->was_deferred)
                                                <span class="inline-flex px-1.5 py-0.5 rounded-full ml-1"
                                                      style="font-size: calc(var(--theme-font-size) - 4px); font-weight: 600; background-color: rgba(249, 115, 22, 0.1); color: #f97316;"
                                                      title="This entry was deferred from a previous invoice">
                                                    🔄 Deferred
                                                </span>
                                            @endif

                                            @if($entry->is_invoiced)
                                            @endif

                                            @if(($entry->status === 'approved' || $entry->status === 'rejected') && $entry->approver)
                                                <div style="font-size: calc(var(--theme-font-size) - 3px); color: var(--theme-text-muted); margin-top: 0.125rem;">
                                                    @if($entry->status === 'approved' && $entry->approved_by === $entry->user_id)
                                                        Auto-approved
                                                    @else
                                                        by {{ $entry->approver->name }}
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Invoice modifications details --}}
                                            @if($entry->has_invoice_modifications)
                                                <div style="font-size: calc(var(--theme-font-size) - 4px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                                    @if($entry->hours_difference != 0)
                                                        Hours: {{ $entry->hours }}h → {{ $entry->invoiced_hours }}h
                                                        <span style="color: {{ $entry->hours_difference > 0 ? '#10b981' : '#ef4444' }};">({{ $entry->hours_difference > 0 ? '+' : '' }}{{ $entry->hours_difference }}h)</span>
                                                    @endif
                                                    @if($entry->rate_difference != 0)
                                                        <br>Rate: €{{ $entry->hourly_rate_used }}/h → €{{ $entry->invoiced_rate }}/h
                                                        <span style="color: {{ $entry->rate_difference > 0 ? '#10b981' : '#ef4444' }};">({{ $entry->rate_difference > 0 ? '+' : '' }}€{{ $entry->rate_difference }}/h)</span>
                                                    @endif
                                                    @if($entry->amount_difference != 0)
                                                        <br>Total: <span style="color: {{ $entry->amount_difference > 0 ? '#10b981' : '#ef4444' }};">{{ $entry->amount_difference > 0 ? '+' : '' }}€{{ number_format($entry->amount_difference, 2) }}</span>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Defer details - Currently deferred (will be invoiced later) --}}
                                            @if($entry->was_deferred && $entry->deferred_at)
                                                <div style="font-size: calc(var(--theme-font-size) - 4px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                                    Deferred on {{ $entry->deferred_at->format('M j, Y') }}
                                                    @if($entry->deferredBy)
                                                        by {{ $entry->deferredBy->name }}
                                                    @endif
                                                    @if($entry->invoice && $entry->invoice->period_start && $entry->invoice->period_end)
                                                        <br><strong style="color: #f97316;">→ Moved to: {{ \Carbon\Carbon::parse($entry->invoice->period_start)->format('M Y') }}</strong>
                                                        @if($entry->invoice->invoice_number)
                                                            (Invoice #{{ $entry->invoice->invoice_number }})
                                                        @endif
                                                    @endif
                                                    @if($entry->defer_reason)
                                                        <br>{{ $entry->defer_reason }}
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Previously deferred (was moved FROM an earlier month) --}}
                                            @if($entry->was_previously_deferred && $entry->invoice && $entry->invoice->period_start)
                                                @php
                                                    $entryMonth = \Carbon\Carbon::parse($entry->entry_date ?? $entry->date);
                                                    $invoiceMonth = \Carbon\Carbon::parse($entry->invoice->period_start);
                                                    // Only show if months are different
                                                    if ($entryMonth->format('Y-m') !== $invoiceMonth->format('Y-m')) {
                                                        $showPreviouslyDeferred = true;
                                                    } else {
                                                        $showPreviouslyDeferred = false;
                                                    }
                                                @endphp
                                                @if($showPreviouslyDeferred)
                                                    <div style="font-size: calc(var(--theme-font-size) - 4px); color: var(--theme-text-muted); margin-top: 0.25rem; padding: 0.25rem 0.5rem; background-color: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; border-radius: 4px;">
                                                        <strong style="color: #3b82f6;">⚠️ NOT invoiced in {{ $entryMonth->format('M Y') }}</strong>
                                                        <br>Moved to {{ $invoiceMonth->format('M Y') }}
                                                        @if($entry->invoice->invoice_number)
                                                            (Invoice #{{ $entry->invoice->invoice_number }})
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    {{-- User --}}
                                    <td style="padding: 0.5rem 1rem; white-space: nowrap; font-size: var(--theme-font-size); color: var(--theme-text);">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-6 w-6">
                                                <div class="h-6 w-6 rounded-full flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                                                    <span style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 500; color: var(--theme-primary);">
                                                        {{ substr($entry->user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                <div style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $entry->user->name }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td style="padding: 0.5rem 1rem; white-space: nowrap; text-align: right; font-size: var(--theme-font-size); color: var(--theme-text);">
                                        <div class="flex items-center justify-end space-x-1">
                                            <button type="button" onclick="openViewModal({{ $entry->id }})"
                                                    style="color: var(--theme-primary); background: none; border: none; padding: 0.25rem; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer;"
                                                    onmouseover="this.style.backgroundColor='rgba(var(--theme-primary-rgb), 0.1)'"
                                                    onmouseout="this.style.backgroundColor='transparent'"
                                                    title="View">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>

                                            @if((in_array(Auth::user()->role, ['super_admin', 'admin']) || $entry->user_id === Auth::id()) && $entry->is_editable)
                                                <button type="button" onclick="openEditModal({{ $entry->id }})"
                                                        style="color: var(--theme-primary); background: none; border: none; padding: 0.25rem; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer;"
                                                        onmouseover="this.style.backgroundColor='rgba(var(--theme-primary-rgb), 0.1)'"
                                                        onmouseout="this.style.backgroundColor='transparent'"
                                                        title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            @elseif(in_array(Auth::user()->role, ['super_admin', 'admin']) || $entry->user_id === Auth::id())
                                                <span style="color: var(--theme-text-muted); padding: 0.25rem; opacity: 0.5;"
                                                      title="Cannot edit: {{ $entry->is_invoiced ? 'Already invoiced' : ($entry->is_finalized ? 'Invoice finalized' : 'Not editable') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </span>
                                            @endif

                                                @if($entry->is_editable && $entry->status !== 'approved')
                                                    <form method="POST" action="{{ route('time-entries.destroy', $entry) }}"
                                                          class="inline" onsubmit="return confirm('Are you sure you want to delete this time entry?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                style="color: var(--theme-danger); background: none; border: none; padding: 0.25rem; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer;"
                                                                onmouseover="this.style.backgroundColor='rgba(var(--theme-danger-rgb), 0.1)'"
                                                                onmouseout="this.style.backgroundColor='transparent'"
                                                                title="Delete">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination - Alleen tonen als we niet vanuit een project komen --}}
                @if(!request('return_to_project'))
                <div class="bg-white/50 px-4 py-3 border-t border-slate-200/50">
                    {{ $timeEntries->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No time entries</h3>
                    <p class="mt-1 text-sm text-slate-500">Get started by creating your first time entry.</p>
                    <div class="mt-6">
                        <a href="{{ route('time-entries.create') }}" 
                           class="theme-btn-primary px-3 py-1.5 text-white text-sm font-medium transition-all duration-200 inline-flex items-center" style="border-radius: var(--theme-border-radius);">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Log Time Entry
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Edit Time Entry Modal --}}
<div id="edit-time-entry-modal" class="fixed inset-0 z-50 hidden" style="background-color: rgba(0, 0, 0, 0.15);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white border border-slate-200/40 rounded-xl overflow-hidden shadow-xl" style="max-width: 700px; width: 100%; max-height: 85vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1rem 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">Edit Time Entry</h2>
                    <button type="button" onclick="closeEditModal()" style="color: var(--theme-text-muted); background: none; border: none; font-size: 1.5rem; cursor: pointer; padding: 0.25rem;">
                        ×
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div style="padding: 1.5rem;">
                <div id="modal-loading" class="text-center py-6">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-3" style="color: var(--theme-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Loading time entry...</p>
                </div>

                <div id="modal-content" class="hidden">
                    {{-- Content will be loaded here via AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- View Time Entry Modal --}}
<div id="view-time-entry-modal" class="fixed inset-0 z-50 hidden" style="background-color: rgba(0, 0, 0, 0.15);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white border border-slate-200/40 rounded-xl overflow-hidden shadow-xl" style="max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto;">
            {{-- Modal Header --}}
            <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: 1rem 1.5rem;">
                <div class="flex justify-between items-center">
                    <h2 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">View Time Entry</h2>
                    <button type="button" onclick="closeViewModal()" style="color: var(--theme-text-muted); background: none; border: none; font-size: 1.5rem; cursor: pointer; padding: 0.25rem;">
                        ×
                    </button>
                </div>
            </div>

            {{-- Modal Content --}}
            <div style="padding: 1.5rem;">
                <div id="view-modal-loading" class="text-center py-6">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-3" style="color: var(--theme-primary);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Loading time entry...</p>
                </div>

                <div id="view-modal-content" class="hidden">
                    {{-- Content will be loaded here via AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Time Entry Modal - Moved outside content div --}}
<div id="timeEntryModal" style="
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    z-index: 99999 !important;
    display: none !important;
    overflow-y: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-sizing: border-box !important;
">
        <div style="
            position: relative !important;
            top: 50px !important;
            margin: 0 auto !important;
            width: 90% !important;
            max-width: 800px !important;
            background-color: white !important;
            border-radius: 10px !important;
            padding: 20px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
        ">
            <div class="mt-3">
                {{-- Modal Header --}}
                <div class="flex justify-between items-center pb-3 border-b border-slate-200/50">
                    <h3 class="text-lg font-medium text-slate-900">Log Time Entry</h3>
                    <button type="button" onclick="closeTimeEntryModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form id="timeEntryForm" action="{{ route('time-entries.store') }}" method="POST" class="mt-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Project Selection --}}
                        <div class="md:col-span-2">
                            <label for="modal_project_id" class="block text-sm font-medium text-slate-700 mb-1">Project</label>
                            <select name="project_id" id="modal_project_id" required onchange="loadProjectWorkItems(); filterBackgroundTableByProject();" class="w-full px-3 py-2 border border-slate-200/60 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 bg-white/80 text-slate-900">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}@if($project->customer) ({{ $project->customer->name }})@endif</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Work Item Selection (Milestone -> Task) --}}
                        <div class="md:col-span-2">
                            <label for="modal_work_item" class="block text-sm font-medium text-slate-700 mb-1">Work Item</label>
                            <select name="work_item" id="modal_work_item" required class="w-full px-3 py-2 border border-slate-200/60 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 bg-white/80 text-slate-900">
                                <option value="">Select work item</option>
                            </select>
                        </div>

                        {{-- Date --}}
                        <div>
                            <label for="modal_date" class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" name="entry_date" id="modal_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-200/60 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 bg-white/80 text-slate-900">
                        </div>

                        {{-- Minutes (instead of hours) --}}
                        <div>
                            <label for="modal_minutes" class="block text-sm font-medium text-slate-700 mb-1">Time (minutes)</label>
                            <input type="number" name="minutes" id="modal_minutes" step="5" min="5" max="1440" required class="w-full px-3 py-2 border border-slate-200/60 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 bg-white/80 text-slate-900" placeholder="e.g. 120 for 2 hours">
                            <small class="text-slate-500 text-xs">Enter time in minutes (e.g. 120 = 2 hours)</small>
                        </div>

                        {{-- Description --}}
                        <div class="md:col-span-2">
                            <label for="modal_description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea name="description" id="modal_description" rows="3" required class="w-full px-3 py-2 border border-slate-200/60 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 bg-white/80 text-slate-900" placeholder="Describe what you worked on..."></textarea>
                        </div>

                        {{-- Billable --}}
                        <div>
                            <label for="modal_is_billable" class="block text-sm font-medium text-slate-700 mb-1">Billable</label>
                            <select name="is_billable" id="modal_is_billable" required class="w-full px-3 py-2 border border-slate-200/60 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 bg-white/80 text-slate-900">
                                <option value="billable">Billable</option>
                                <option value="non_billable">Non-billable</option>
                            </select>
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-slate-200/50">
                        <button type="button" onclick="closeTimeEntryModal()" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-500 text-white rounded-lg hover:bg-slate-600 transition-colors">
                            Save Time Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * Modal functions
 */
function openTimeEntryModal() {
    const modal = document.getElementById('timeEntryModal');
    if (!modal) {
        console.error('Modal element not found!');
        return;
    }

    // Show modal
    modal.style.display = 'block';

    // Reset form
    const form = document.getElementById('timeEntryForm');
    if (form) {
        form.reset();
    }

    const dateField = document.getElementById('modal_date');
    if (dateField) {
        dateField.value = new Date().toISOString().split('T')[0];
    }

    // Clear work items
    const workItemSelect = document.getElementById('modal_work_item');
    if (workItemSelect) {
        workItemSelect.innerHTML = '<option value="">Select work item</option>';
    }

}

function closeTimeEntryModal() {
    const modal = document.getElementById('timeEntryModal');
    if (modal) {
        modal.style.display = 'none';
    }

    // Reset background table filter when closing modal
    resetBackgroundTableFilter();
}

/**
 * Filter background table by selected project in modal
 * Sets the background filter dropdown and submits the form to load ALL entries for that project
 */
function filterBackgroundTableByProject() {
    const projectId = document.getElementById('modal_project_id').value;
    const backgroundFilterDropdown = document.getElementById('filter_project_id');

    if (!backgroundFilterDropdown) {
        console.log('Background filter dropdown not found');
        return;
    }

    // Set the background filter to the selected project
    backgroundFilterDropdown.value = projectId;

    // Trigger the auto-submit to reload with the project filter
    if (projectId) {
        console.log('Setting background filter to project:', projectId);

        // Add hidden inputs to preserve modal state after form submit
        const form = backgroundFilterDropdown.form;

        // Remove any existing modal inputs first
        const existingModalInput = form.querySelector('input[name="modal"]');
        const existingProjectInput = form.querySelector('input[name="modal_project"]');
        if (existingModalInput) existingModalInput.remove();
        if (existingProjectInput) existingProjectInput.remove();

        // Create new hidden inputs
        const modalInput = document.createElement('input');
        modalInput.type = 'hidden';
        modalInput.name = 'modal';
        modalInput.value = 'open';
        form.appendChild(modalInput);

        const modalProjectInput = document.createElement('input');
        modalProjectInput.type = 'hidden';
        modalProjectInput.name = 'modal_project';
        modalProjectInput.value = projectId;
        form.appendChild(modalProjectInput);

        // Submit the form
        form.submit();
    }
}

/**
 * Reset background table filter (show all rows)
 */
function resetBackgroundTableFilter() {
    const backgroundFilterDropdown = document.getElementById('filter_project_id');
    if (backgroundFilterDropdown && backgroundFilterDropdown.value !== '') {
        // Reset filter to "All Projects"
        backgroundFilterDropdown.value = '';
        backgroundFilterDropdown.form.submit();
    }
}

/**
 * Load work items for selected project
 */
function loadProjectWorkItems() {
    const projectId = document.getElementById('modal_project_id').value;
    const workItemSelect = document.getElementById('modal_work_item');

    // Clear existing options
    workItemSelect.innerHTML = '<option value="">Loading...</option>';

    if (!projectId) {
        workItemSelect.innerHTML = '<option value="">Select work item</option>';
        return;
    }

    // Fetch work items via AJAX
    fetch(`/api/projects/${projectId}/work-items`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API Response data:', data);
            console.log('Data type:', typeof data);
            console.log('Is array:', Array.isArray(data));

            workItemSelect.innerHTML = '<option value="">Select work item</option>';

            // Handle both arrays and objects (Laravel collection edge case)
            let milestones = [];
            if (Array.isArray(data)) {
                milestones = data;
            } else if (typeof data === 'object' && data !== null) {
                // Convert object to array (Laravel collection sometimes serializes as object)
                milestones = Object.values(data);
                console.log('Converted object to array:', milestones);
            } else {
                console.error('Expected array or object but got:', typeof data, data);
                workItemSelect.innerHTML = '<option value="">Invalid data format</option>';
                return;
            }

            milestones.forEach(milestone => {
                // Add milestone tasks
                if (milestone.tasks && Array.isArray(milestone.tasks)) {
                    milestone.tasks.forEach(task => {
                        const option = document.createElement('option');
                        option.value = `task:${task.id}`;
                        option.textContent = `${milestone.name} → ${task.name}`;
                        workItemSelect.appendChild(option);
                    });
                }
            });
        })
        .catch(error => {
            console.error('Error loading work items:', error);
            workItemSelect.innerHTML = '<option value="">Error loading work items</option>';
        });
}

// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-success').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();

    // Header create button
    const createBtn = document.getElementById('header-create-btn');
    if (createBtn) {
        createBtn.style.backgroundColor = primaryColor;
        createBtn.style.color = 'white';
        createBtn.style.border = 'none';
        createBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Filter button
    const filterBtn = document.getElementById('header-filter-btn');
    if (filterBtn) {
        filterBtn.style.backgroundColor = primaryColor;
        filterBtn.style.color = 'white';
        filterBtn.style.border = 'none';
        filterBtn.style.borderRadius = 'var(--theme-border-radius)';
    }
}

/**
 * Quick filter functions
 */
function setQuickFilter(period) {
    let startDate, endDate;
    const now = new Date();

    switch(period) {
        case 'today':
            startDate = endDate = now.toISOString().split('T')[0];
            break;

        case 'week':
            // Create new date objects to avoid modifying the original
            const weekStart = new Date(now);
            const weekEnd = new Date(now);
            const dayOfWeek = now.getDay();
            const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); // Adjust for Sunday

            weekStart.setDate(diff);
            weekEnd.setDate(diff + 6);

            startDate = weekStart.toISOString().split('T')[0];
            endDate = weekEnd.toISOString().split('T')[0];
            break;

        case 'month':
            const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
            const monthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);

            startDate = monthStart.toISOString().split('T')[0];
            endDate = monthEnd.toISOString().split('T')[0];
            break;
    }

    // Set the date values
    document.getElementById('filter_date_from').value = startDate;
    document.getElementById('filter_date_to').value = endDate;

    // Find and submit the filter form specifically
    const filterForm = document.querySelector('form[action*="time-entries"]');
    if (filterForm) {
        filterForm.submit();
    } else {
        console.error('Filter form not found');
    }
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();

    // Auto-submit form when select filters change
    const selectFilters = document.querySelectorAll('#filter_project_id, #filter_user_id, #filter_status');
    selectFilters.forEach(select => {
        select.addEventListener('change', function() {
            // Auto-submit on filter change
            this.form.submit();
        });
    });

    // Date filter auto-submit
    const dateFilters = document.querySelectorAll('#filter_date_from, #filter_date_to');
    dateFilters.forEach(input => {
        input.addEventListener('change', function() {
            // Auto-submit on date change
            this.form.submit();
        });
    });

    // Check if modal should be reopened after page reload
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('modal') === 'open') {
        const modalProjectId = urlParams.get('modal_project');

        // Open the modal
        openTimeEntryModal();

        // Set the project if provided
        if (modalProjectId) {
            setTimeout(() => {
                const modalProjectSelect = document.getElementById('modal_project_id');
                if (modalProjectSelect) {
                    modalProjectSelect.value = modalProjectId;
                    // Trigger the work items load
                    loadProjectWorkItems();
                }
            }, 100);
        }

        // Clean up URL (remove modal parameters)
        const cleanUrl = new URL(window.location);
        cleanUrl.searchParams.delete('modal');
        cleanUrl.searchParams.delete('modal_project');
        window.history.replaceState({}, '', cleanUrl);
    }
});

/**
 * Modal Functions for Time Entry Editing
 */

// Open edit modal
function openEditModal(timeEntryId) {
    const modal = document.getElementById('edit-time-entry-modal');
    const modalLoading = document.getElementById('modal-loading');
    const modalContent = document.getElementById('modal-content');

    // Show modal and loading state
    modal.classList.remove('hidden');
    modalLoading.classList.remove('hidden');
    modalContent.classList.add('hidden');

    // Load edit form via AJAX
    fetch(`/time-entries/${timeEntryId}/edit-modal`)
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
            initializeModalForm(timeEntryId);
        })
        .catch(error => {
            console.error('Error loading edit form:', error);
            modalContent.innerHTML = `
                <div class="text-center py-8">
                    <p style="color: var(--theme-danger);">Failed to load edit form. Please try again.</p>
                    <button type="button" onclick="closeEditModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius);">
                        Close
                    </button>
                </div>
            `;
            modalLoading.classList.add('hidden');
            modalContent.classList.remove('hidden');
        });
}

// Close edit modal
function closeEditModal() {
    const modal = document.getElementById('edit-time-entry-modal');
    modal.classList.add('hidden');
}

// Initialize modal form functionality
function initializeModalForm(timeEntryId) {
    // Get project ID from the time entry row in the table
    const timeEntryRow = document.querySelector(`tr[data-time-entry-id="${timeEntryId}"]`);
    const projectId = timeEntryRow?.getAttribute('data-project-id');

    if (projectId && document.getElementById('work_item_id')) {
        // Get selected work item from the time entry data
        const selectedWorkItem = timeEntryRow?.getAttribute('data-selected-work-item');
        loadWorkItemsForModal(projectId, selectedWorkItem);
    }

    // Initialize time display
    if (typeof updateTimeDisplayModal === 'function') {
        updateTimeDisplayModal();
    }

    // Handle form submission
    const form = document.querySelector('#modal-content form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitModalForm(form, timeEntryId);
        });
    }

    // Style form elements according to theme
    styleModalElements();
}

// Submit modal form
function submitModalForm(form, timeEntryId) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';
    }

    const formData = new FormData(form);

    fetch(`/time-entries/${timeEntryId}`, {
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
            closeEditModal();
            location.reload();
        } else {
            // Show error message
            alert('Error updating time entry: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        alert('Network error updating time entry');
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

// Style modal elements according to theme
function styleModalElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Style submit buttons
    const submitButtons = document.querySelectorAll('#modal-content button[type="submit"]');
    submitButtons.forEach(btn => {
        btn.style.backgroundColor = primaryColor;
        btn.style.color = 'white';
        btn.style.border = 'none';
        btn.style.borderRadius = 'var(--theme-border-radius)';
    });

    // Style radio buttons
    const radioButtons = document.querySelectorAll('#modal-content input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.style.accentColor = primaryColor;
    });
}

// Load work items for modal
function loadWorkItemsForModal(projectId, selectedWorkItem = null) {
    const workItemSelect = document.getElementById('work_item_id');
    if (!workItemSelect) return;

    workItemSelect.disabled = true;
    workItemSelect.innerHTML = '<option value="">Loading work items...</option>';

    fetch(`/time-entries/project/${projectId}/work-items`)
        .then(response => response.json())
        .then(data => {
            workItemSelect.innerHTML = '<option value="">Select work item...</option>';

            if (data.workItems && data.workItems.length > 0) {
                let currentMilestoneName = null;
                let currentTaskName = null;

                data.workItems.forEach(item => {
                    if (item.type === 'milestone' || item.is_header) {
                        currentMilestoneName = item.label;
                        currentTaskName = null;
                        return;
                    }

                    if (item.type === 'task') {
                        currentTaskName = item.label;
                    }

                    if (item.type === 'task' || item.type === 'subtask') {
                        const option = document.createElement('option');
                        option.value = item.id;

                        let label = '';
                        if (item.type === 'subtask') {
                            if (currentMilestoneName) label = `${currentMilestoneName} → `;
                            if (currentTaskName) label += `${currentTaskName} → `;
                            label += item.label;
                        } else {
                            if (currentMilestoneName) label = `${currentMilestoneName} → `;
                            label += item.label;
                        }

                        if (item.is_service) {
                            label = '📦 ' + label;
                        }

                        option.textContent = label;

                        if (item.id === selectedWorkItem) {
                            option.selected = true;
                        }

                        if (item.type === 'subtask' || item.indent === 2) {
                            option.style.paddingLeft = '40px';
                        } else if (item.type === 'task' || item.indent === 1) {
                            option.style.paddingLeft = '20px';
                        }

                        workItemSelect.appendChild(option);
                    }
                });
            }

            workItemSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading work items:', error);
            workItemSelect.innerHTML = '<option value="">Error loading work items</option>';
        });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('edit-time-entry-modal');
    if (e.target === modal) {
        closeEditModal();
    }
});

// Close modal with close button
document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.getElementById('close-modal-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeEditModal);
    }
});

/**
 * View Modal Functions for Time Entry Viewing
 */

// Open view modal
function openViewModal(timeEntryId) {
    const modal = document.getElementById('view-time-entry-modal');
    const modalLoading = document.getElementById('view-modal-loading');
    const modalContent = document.getElementById('view-modal-content');

    // Show modal and loading state
    modal.classList.remove('hidden');
    modalLoading.classList.remove('hidden');
    modalContent.classList.add('hidden');

    // Load view content via AJAX
    fetch(`/time-entries/${timeEntryId}/show-modal`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load time entry details');
            }
            return response.text();
        })
        .then(html => {
            modalContent.innerHTML = html;
            modalLoading.classList.add('hidden');
            modalContent.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading time entry details:', error);
            modalContent.innerHTML = `
                <div class="text-center py-8">
                    <p style="color: var(--theme-danger);">Failed to load time entry details. Please try again.</p>
                    <button type="button" onclick="closeViewModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius);">
                        Close
                    </button>
                </div>
            `;
            modalLoading.classList.add('hidden');
            modalContent.classList.remove('hidden');
        });
}

// Close view modal
function closeViewModal() {
    const modal = document.getElementById('view-time-entry-modal');
    modal.classList.add('hidden');
}

// Close view modal when clicking outside
document.addEventListener('click', function(e) {
    const viewModal = document.getElementById('view-time-entry-modal');
    if (e.target === viewModal) {
        closeViewModal();
    }
});

// Modal form submission and initialization
document.addEventListener('DOMContentLoaded', function() {
    // Auto-open modal if redirected from create route
    @if(session('open_modal'))
        openTimeEntryModal();
    @endif

    // Close modal when clicking outside
    const timeEntryModal = document.getElementById('timeEntryModal');
    if (timeEntryModal) {
        timeEntryModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTimeEntryModal();
            }
        });
    }

    // Handle form submission
    const timeEntryForm = document.getElementById('timeEntryForm');
    if (timeEntryForm) {
        timeEntryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Form submit response status:', response.status);
                return response.json().then(data => ({
                    status: response.status,
                    data: data
                }));
            })
            .then(({status, data}) => {
                console.log('Form submit response data:', data);

                if (status === 200 && (data.success || data.redirect)) {
                    closeTimeEntryModal();
                    // Reload page to show new entry
                    window.location.reload();
                } else if (status === 422) {
                    // Validation errors
                    console.error('Validation errors:', data.errors);
                    let errorMessage = 'Validation errors:\n';
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            errorMessage += `${field}: ${data.errors[field].join(', ')}\n`;
                        });
                    }
                    alert(errorMessage);
                } else {
                    alert(data.message || 'Error saving time entry');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving time entry');
            });
        });
    }
});
</script>
@endpush