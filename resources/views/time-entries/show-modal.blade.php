{{-- Modal Show View for Time Entry --}}
<div>
    {{-- Header with status badges --}}
    <div class="flex justify-between items-start mb-4">
        <div>
            <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin: 0;">
                {{ $timeEntry->formatted_duration }} - {{ $timeEntry->entry_date ? $timeEntry->entry_date->format('M j, Y') : $timeEntry->date->format('M j, Y') }}
            </h3>
        </div>
        <div class="flex items-center space-x-2">
            @php
                $statusColors = [
                    'draft' => ['bg' => 'rgba(107, 114, 128, 0.1)', 'color' => '#6b7280'],
                    'submitted' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                    'pending' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                    'approved' => ['bg' => 'rgba(var(--theme-success-rgb), 0.1)', 'color' => 'var(--theme-success)'],
                    'rejected' => ['bg' => 'rgba(var(--theme-danger-rgb), 0.1)', 'color' => 'var(--theme-danger)'],
                ];
                $statusColor = $statusColors[$timeEntry->status] ?? $statusColors['draft'];
            @endphp
            <span class="inline-flex px-2 py-1 rounded-full"
                  style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['color'] }};">
                {{ ucfirst($timeEntry->status) }}
            </span>
            @if($timeEntry->is_billable === 'billable')
                <span class="inline-flex px-2 py-1 rounded-full"
                      style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                    Billable
                </span>
            @else
                <span class="inline-flex px-2 py-1 rounded-full"
                      style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(107, 114, 128, 0.1); color: #6b7280;">
                    Non-billable
                </span>
            @endif
        </div>
    </div>

    {{-- Project & Work Item --}}
    <div class="mb-4">
        <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Project & Work Item</h4>
        <div style="font-size: var(--theme-font-size); color: var(--theme-text);">
            <strong>{{ $timeEntry->project->name }}</strong>
            @if($timeEntry->project->customer)
                - {{ $timeEntry->project->customer->name }}
            @endif
        </div>
        <div style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;">
            {{ $timeEntry->work_item_path }}
        </div>
        @if($timeEntry->is_service_item)
            <div style="font-size: calc(var(--theme-font-size) - 1px); color: #2563eb; font-weight: 500; margin-top: 0.25rem;">
                📦 Service Item
            </div>
        @endif
    </div>

    {{-- Basic Details Grid --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        {{-- Date --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Date</dt>
            <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                {{ $timeEntry->entry_date ? $timeEntry->entry_date->format('l, M j, Y') : $timeEntry->date->format('l, M j, Y') }}
            </dd>
        </div>

        {{-- Duration --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Time Spent</dt>
            <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                {{ $timeEntry->formatted_duration }}
                <span style="color: var(--theme-text-muted);">({{ $timeEntry->minutes ?? round($timeEntry->hours * 60) }} minutes)</span>
            </dd>
        </div>

        {{-- User --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Logged by</dt>
            <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-5 w-5">
                        <div class="h-5 w-5 rounded-full flex items-center justify-center" style="background-color: rgba(var(--theme-primary-rgb), 0.1);">
                            <span style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 500; color: var(--theme-primary);">
                                {{ substr($timeEntry->user->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-2">
                        {{ $timeEntry->user->name }}
                    </div>
                </div>
            </dd>
        </div>

        {{-- Created --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted);">Created</dt>
            <dd style="margin-top: 0.25rem; font-size: var(--theme-font-size); color: var(--theme-text);">
                {{ $timeEntry->created_at->format('M j, Y \a\t g:i A') }}
            </dd>
        </div>
    </div>

    {{-- Work Description --}}
    <div class="mb-4">
        <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Work Description</h4>
        <div style="font-size: var(--theme-font-size); color: var(--theme-text); white-space: pre-wrap; line-height: 1.5;">{{ $timeEntry->description }}</div>
    </div>

    {{-- Approval Status --}}
    @if($timeEntry->status !== 'draft')
        <div class="mb-4">
            <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Approval Status</h4>
            @if($timeEntry->status === 'approved')
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" style="color: var(--theme-success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); color: var(--theme-success);">
                            This time entry has been <strong>approved</strong>.
                        </p>
                        @if($timeEntry->approved_by && $timeEntry->approved_at && $timeEntry->approver)
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                Approved by {{ $timeEntry->approver->name }} on {{ \App\Helpers\DateHelper::format($timeEntry->approved_at) }}
                            </p>
                        @endif
                    </div>
                </div>
            @elseif($timeEntry->status === 'rejected')
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" style="color: var(--theme-danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); color: var(--theme-danger);">
                            This time entry has been <strong>rejected</strong>.
                        </p>
                        @if($timeEntry->approved_by && $timeEntry->approved_at && $timeEntry->approver)
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted); margin-top: 0.25rem;">
                                Rejected by {{ $timeEntry->approver->name }} on {{ \App\Helpers\DateHelper::format($timeEntry->approved_at) }}
                            </p>
                        @endif
                        @if($timeEntry->rejection_reason)
                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-danger); margin-top: 0.5rem; font-style: italic;">
                                "{{ $timeEntry->rejection_reason }}"
                            </p>
                        @endif
                    </div>
                </div>
            @elseif($timeEntry->status === 'pending')
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); color: #f59e0b;">
                            This time entry is <strong>pending approval</strong>.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Close Button --}}
    <div class="flex justify-end pt-4" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
        <button type="button" onclick="closeViewModal()"
                style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
            Close
        </button>
    </div>
</div>