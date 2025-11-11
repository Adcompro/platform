{{-- Project Activity Timeline --}}
<div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding); min-height: 60px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="toggleProjectActivity()">
        <div class="flex items-center gap-4" style="flex: 1;">
            <div class="flex items-center" style="min-width: 280px;">
                <i id="project-activity-chevron" class="fas fa-chevron-down mr-2 transition-transform" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);"></i>
                <h2 style="font-size: calc(var(--theme-font-size) + 1px); font-weight: 600; color: var(--theme-text); margin: 0;">
                    <i class="fas fa-history mr-2"></i>
                    Project Activity
                </h2>
            </div>
            @php
                $totalActivities = $activities->total();
            @endphp
            <div id="project-activity-summary" class="flex items-center gap-6" style="font-size: calc(var(--theme-font-size) + 1px);">
                <span style="color: var(--theme-text); font-weight: 600; min-width: 200px;">
                    {{ $totalActivities }} {{ $totalActivities === 1 ? 'activity' : 'activities' }}
                </span>
            </div>
        </div>
    </div>

    <div id="project-activity-content" class="hidden" style="padding: var(--theme-card-padding);">
        @if($activities->count() > 0)
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($activities as $index => $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif

                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 {{ $activity->activity_badge_class }}" style="background-color: white;">
                                            <i class="{{ $activity->activity_icon }}" style="font-size: calc(var(--theme-font-size) - 3px);"></i>
                                        </span>
                                    </div>

                                    <div class="flex min-w-0 flex-1 justify-between space-x-4" style="padding-top: 0.375rem;">
                                        <div>
                                            <p style="font-size: var(--theme-font-size); color: var(--theme-text);">
                                                <span style="font-weight: 500;">{{ $activity->user ? $activity->user->name : 'System' }}</span>
                                                <span style="color: var(--theme-text-muted);">{{ $activity->description }}</span>
                                            </p>

                                            {{-- Show changed fields if available --}}
                                            @if($activity->old_values || $activity->new_values)
                                                <div class="mt-2" style="font-size: calc(var(--theme-font-size) - 2px); background-color: rgba(var(--theme-bg-rgb), 0.5); border-radius: var(--theme-border-radius); padding: 0.5rem; border: 1px solid rgba(203, 213, 225, 0.3);">
                                                    <div style="font-weight: 600; color: var(--theme-text); margin-bottom: 0.25rem;">Changed Fields:</div>
                                                    @if($activity->old_values)
                                                        @foreach($activity->old_values as $field => $oldValue)
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span style="color: var(--theme-text-muted);">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                                @if($oldValue)
                                                                    <span style="color: #ef4444; text-decoration: line-through;">{{ $oldValue }}</span>
                                                                @endif
                                                                @if(isset($activity->new_values[$field]))
                                                                    <span style="color: var(--theme-text-muted);">→</span>
                                                                    <span style="color: #10b981; font-weight: 500;">{{ $activity->new_values[$field] }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Entity type badge --}}
                                            @if($activity->entity_type)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded mt-2" style="font-size: calc(var(--theme-font-size) - 3px); font-weight: 500; background-color: rgba(var(--theme-text-muted-rgb), 0.1); color: var(--theme-text-muted);">
                                                    {{ ucfirst($activity->entity_type) }}
                                                    @if($activity->entity_id)
                                                        #{{ $activity->entity_id }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        <div class="whitespace-nowrap text-right" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">
                                            <div>{{ $activity->created_at->format('d-m-Y H:i:s') }}</div>
                                            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); opacity: 0.7;">{{ $activity->created_at->diffForHumans() }}</div>
                                            @if($activity->ip_address)
                                                <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); opacity: 0.7; margin-top: 0.25rem;">IP: {{ $activity->ip_address }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $activities->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-history" style="color: rgba(var(--theme-text-muted-rgb), 0.3); font-size: calc(var(--theme-font-size) + 32px); margin-bottom: 1rem;"></i>
                <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem;">No activity yet</h3>
                <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">Activity will appear here as changes are made to the project</p>
            </div>
        @endif
    </div>
</div>
