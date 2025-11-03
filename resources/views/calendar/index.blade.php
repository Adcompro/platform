@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
{{-- Sticky Header - Consistent with Time Entries --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div class="flex items-center space-x-4">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Calendar</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Manage your calendar events and synchronize with Microsoft 365</p>
                </div>

                {{-- Sync Status --}}
                <div class="flex items-center space-x-2">
                    @if($lastSync)
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="ml-1.5 text-xs font-medium text-gray-500">
                                Synced
                                @if(isset($nextSyncMinutes))
                                    @if($nextSyncMinutes === null)
                                        {{-- Auto-sync disabled --}}
                                    @elseif($nextSyncMinutes <= 0)
                                        • next sync now
                                    @else
                                        • next sync in {{ $nextSyncMinutes }}min
                                    @endif
                                @else
                                    {{-- Variable not set - debug --}}
                                    • (sync info unavailable)
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 bg-amber-500 rounded-full"></div>
                            <span class="ml-1.5 text-xs font-medium text-gray-500">Not synced</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- New Event Button --}}
                <button type="button" onclick="openCreateEventModal()"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-plus mr-1.5"></i>
                    New Event
                </button>

                {{-- Sync Button --}}
                <form action="{{ route('calendar.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="header-btn-secondary"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-sync-alt mr-1.5"></i>
                        Sync
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- Main Content - Consistent with Time Entries --}}
<div style="padding: 1.5rem 2rem;">


    {{-- Notifications --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Calendar Container --}}
    <div class="bg-white border border-slate-200/60 rounded-lg" style="padding: 1.5rem; box-shadow: var(--theme-card-shadow);">
        <div id="calendar"></div>
    </div>

    {{-- Events Section --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Upcoming Events --}}
        <div class="col-span-1 lg:col-span-2">
            <div class="bg-white border border-slate-200/60 rounded-lg" style="padding: 1.5rem; box-shadow: var(--theme-card-shadow);">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Events</h3>

                @forelse($events->take(8) as $event)
                <div class="flex items-start space-x-4 py-3 border-b border-gray-100 last:border-b-0">
                    {{-- Date/Time --}}
                    <div class="flex-shrink-0 text-center">
                        <div class="text-xs text-gray-500 uppercase tracking-wider">
                            {{ $event->start_datetime->format('M') }}
                        </div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $event->start_datetime->format('d') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ \App\Helpers\DateHelper::formatTime($event->start_datetime) }}
                        </div>
                    </div>

                    {{-- Event Details --}}
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ $event->subject }}</h4>
                        <div class="flex items-center mt-1 space-x-3">
                            @php
                                $duration = $event->start_datetime->diff($event->end_datetime);
                                $hours = $duration->h;
                                $minutes = $duration->i;
                            @endphp

                            <span class="text-xs text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                @if($hours > 0){{ $hours }}h @endif{{ $minutes }}m
                            </span>

                            @if($event->location)
                            <span class="text-xs text-gray-500">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ Str::limit($event->location, 20) }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-2">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                    <p class="text-sm text-gray-500">No upcoming events</p>
                    <p class="text-xs text-gray-400 mt-1">Sync your calendar to see events here</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white border border-slate-200/60 rounded-lg" style="padding: 1.5rem; box-shadow: var(--theme-card-shadow);">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h4>

                <div class="space-y-3">
                    <button onclick="openCreateEventModal()" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-plus text-blue-500 mr-3"></i>
                            <span class="font-medium">Create Event</span>
                        </div>
                    </button>

                    <button onclick="openBulkConvertModal()" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-exchange-alt text-green-500 mr-3"></i>
                            <span class="font-medium">Bulk Convert</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Basic Modal and Script placeholders --}}
<script>
function openCreateEventModal() {
    alert('Create Event Modal - To be implemented');
}

function openBulkConvertModal() {
    alert('Bulk Convert Modal - To be implemented');
}

// Calendar initialization
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        // Events data
        var eventsData = [];
        @if(isset($events) && $events->count() > 0)
            @foreach($events as $event)
                eventsData.push({
                    id: '{{ $event->id }}',
                    title: '{{ addslashes($event->subject) }}',
                    start: '{{ $event->start_datetime->toISOString() }}',
                    end: '{{ $event->end_datetime->toISOString() }}'
                });
            @endforeach
        @endif

        // Debug: Log events data and sync info
        console.log('Events passed to calendar:', eventsData);
        console.log('Total events:', eventsData.length);
        @if(isset($nextSyncMinutes))
        console.log('Next sync minutes from backend:', {{ $nextSyncMinutes ?? 'null' }});
        @endif

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '21:00:00',
            allDaySlot: true,
            weekends: true,
            height: 'auto',
            slotDuration: '00:30:00',
            slotLabelInterval: '01:00:00',
            scrollTime: '08:00:00',
            nowIndicator: true,
            events: eventsData
        });
        calendar.render();
    }
});
</script>

{{-- FullCalendar CSS and JS --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

@endsection