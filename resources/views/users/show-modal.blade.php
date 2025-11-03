{{-- Modal Show View for User --}}
<div>
    {{-- User Profile Header --}}
    <div class="text-center mb-6" style="padding: 1.5rem; background: linear-gradient(135deg, #64748b, #475569); border-radius: var(--theme-border-radius); margin: -1.5rem -1.5rem 1.5rem -1.5rem;">
        <div class="flex justify-center mb-3">
            <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center">
                <span style="font-size: 1.25rem; font-weight: 700; color: var(--theme-text-muted);">{{ substr($user->name, 0, 2) }}</span>
            </div>
        </div>
        <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: white; margin: 0;">{{ $user->name }}</h3>
        <p style="font-size: var(--theme-font-size); color: #e2e8f0; margin-top: 0.25rem;">{{ $user->email }}</p>
    </div>

    {{-- User Details Grid --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        {{-- Role --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Role</dt>
            <dd>
                <span class="px-2 py-1 inline-flex rounded-lg {{ $user->role_badge_class }}" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500;">
                    {{ $user->role_description }}
                </span>
            </dd>
        </div>

        {{-- Status --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Status</dt>
            <dd>
                @if($user->is_active)
                    <span class="px-2 py-1 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                        Active
                    </span>
                @else
                    <span class="px-2 py-1 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; background-color: rgba(107, 114, 128, 0.1); color: #6b7280;">
                        Inactive
                    </span>
                @endif
            </dd>
        </div>

        {{-- Company --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Company</dt>
            <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->company ? $user->company->name : 'No Company' }}</dd>
        </div>

        {{-- Email Verified --}}
        <div>
            <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.5rem;">Email Verified</dt>
            <dd>
                @if($user->email_verified_at)
                    <span class="px-2 py-1 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                        Verified
                    </span>
                @else
                    <span class="px-2 py-1 inline-flex rounded-lg" style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        Unverified
                    </span>
                @endif
            </dd>
        </div>
    </div>

    {{-- Additional Info --}}
    @if($user->phone || $user->department || $user->last_login_at)
        <div class="space-y-3 mb-4">
            @if($user->phone)
            <div>
                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Phone</dt>
                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->phone }}</dd>
            </div>
            @endif

            @if($user->department)
            <div>
                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Department</dt>
                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">{{ $user->department }}</dd>
            </div>
            @endif

            <div>
                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Last Login</dt>
                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                    {{ $user->last_login_at ? \App\Helpers\DateHelper::format($user->last_login_at) : 'Never' }}
                </dd>
            </div>

            <div>
                <dt style="font-size: calc(var(--theme-font-size) - 1px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Member Since</dt>
                <dd style="font-size: var(--theme-font-size); color: var(--theme-text);">
                    {{ $user->created_at->format('M j, Y') }}
                </dd>
            </div>
        </div>
    @endif

    {{-- Special Features --}}
    @if($user->auto_approve_time_entries || $user->timezone)
        <div class="mb-4">
            <h4 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Features & Settings</h4>

            @if($user->auto_approve_time_entries)
                <div class="flex items-center mb-2">
                    <svg class="w-4 h-4 mr-2" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                    <span style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">Auto-approve time entries enabled</span>
                </div>
            @endif

            @if($user->timezone)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text);">Timezone: {{ $user->timezone }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Close Button --}}
    <div class="flex justify-end pt-4" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
        <button type="button" onclick="closeViewUserModal()"
                style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
            Close
        </button>
    </div>
</div>