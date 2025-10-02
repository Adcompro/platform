{{-- Milestone Details Partial --}}
<div class="space-y-6">
    {{-- Basic Information --}}
    <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
        <h4 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Basic Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Milestone Name:</span>
                <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $milestone->name }}</div>
            </div>
            <div>
                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Status:</span>
                <div class="mt-1">
                    @php
                        $statusColors = [
                            'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                            'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                            'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                            'on_hold' => ['bg' => 'bg-red-100', 'text' => 'text-red-800']
                        ];
                        $colors = $statusColors[$milestone->status] ?? $statusColors['pending'];
                    @endphp
                    <span class="px-3 py-1 rounded text-sm font-medium {{ $colors['bg'] }} {{ $colors['text'] }}">
                        {{ ucwords(str_replace('_', ' ', $milestone->status)) }}
                    </span>
                </div>
            </div>
            
            @if($milestone->start_date)
                <div>
                    <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Start Date:</span>
                    <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $milestone->start_date->format('M j, Y') }}</div>
                </div>
            @endif
            
            @if($milestone->end_date)
                <div>
                    <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">End Date:</span>
                    <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $milestone->end_date->format('M j, Y') }}</div>
                </div>
            @endif
        </div>
        
        @if($milestone->description)
            <div class="mt-4">
                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Description:</span>
                <p class="mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size); line-height: 1.6;">{{ $milestone->description }}</p>
            </div>
        @endif
    </div>

    {{-- Financial Information --}}
    <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
        <h4 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Financial Details</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Fee Type:</span>
                <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ ucwords(str_replace('_', ' ', $milestone->fee_type)) }}</div>
            </div>
            
            <div>
                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Pricing Type:</span>
                <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ ucwords(str_replace('_', ' ', $milestone->pricing_type)) }}</div>
            </div>
            
            @if($milestone->pricing_type === 'fixed_price' && $milestone->fixed_price)
                <div>
                    <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Fixed Price:</span>
                    <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">&euro;{{ number_format($milestone->fixed_price, 2) }}</div>
                </div>
            @endif
            
            @if($milestone->hourly_rate_override)
                <div>
                    <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Hourly Rate:</span>
                    <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">&euro;{{ number_format($milestone->hourly_rate_override, 2) }}/hr</div>
                </div>
            @endif
            
            @if($milestone->estimated_hours)
                <div>
                    <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Estimated Hours:</span>
                    <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $milestone->estimated_hours }}h</div>
                </div>
            @endif
            
            @if($milestone->source_type)
                <div>
                    <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Source:</span>
                    <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ ucwords($milestone->source_type) }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Tasks Summary --}}
    @if($milestone->tasks && $milestone->tasks->count() > 0)
        <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
            <h4 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Tasks ({{ $milestone->tasks->count() }})</h4>
            <div class="space-y-2">
                @foreach($milestone->tasks->take(5) as $task)
                    <div class="flex items-center justify-between p-2 rounded-md" style="background-color: rgba(var(--theme-bg-light-rgb), 0.3);">
                        <div class="flex-1">
                            <div class="font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $task->name }}</div>
                            @if($task->description)
                                <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">{{ Str::limit($task->description, 100) }}</div>
                            @endif
                        </div>
                        <div class="ml-3">
                            @php
                                $taskStatusColors = [
                                    'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                                    'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                    'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
                                    'on_hold' => ['bg' => 'bg-red-100', 'text' => 'text-red-700']
                                ];
                                $taskColors = $taskStatusColors[$task->status] ?? $taskStatusColors['pending'];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $taskColors['bg'] }} {{ $taskColors['text'] }}">
                                {{ ucwords(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                    </div>
                @endforeach
                
                @if($milestone->tasks->count() > 5)
                    <div class="text-center py-2">
                        <span style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">... and {{ $milestone->tasks->count() - 5 }} more tasks</span>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
            <h4 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Tasks</h4>
            <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">No tasks have been added to this milestone yet.</p>
        </div>
    @endif
</div>