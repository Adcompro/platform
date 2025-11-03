@extends('layouts.app')

@section('title', 'Calendar')


@section('content')
{{-- Sticky Header - Consistent with Time Entries --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div class="flex items-center space-x-4">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Multi-Calendar View</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">
                        Unified view of all connected calendar providers:
                        @forelse($connectedProviders as $provider)
                            <span class="capitalize">{{ $provider }}</span>@if(!$loop->last), @endif
                        @empty
                            No calendars connected
                        @endforelse
                    </p>
                </div>

                {{-- Sync Status --}}
                <div class="flex items-center space-x-2">
                    @if($lastSync)
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="ml-1.5 text-xs font-medium text-gray-500">
                                Synced
                                @if($calendarSyncSettings['auto_sync'])
                                    @if(isset($nextSyncMinutes))
                                        @if($nextSyncMinutes <= 0)
                                            • next sync now
                                        @else
                                            • next sync in {{ $nextSyncMinutes }}min
                                        @endif
                                    @else
                                        • auto-sync enabled
                                    @endif
                                @else
                                    • auto-sync disabled
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
                {{-- Manage Providers Button --}}
                <a href="{{ route('calendar.providers.index') }}"
                   class="header-btn-secondary"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-cog mr-1.5"></i>
                    Manage Providers
                </a>

                {{-- Sync All Button --}}
                <button type="button" onclick="syncAllProviders()"
                        class="header-btn"
                        style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-sync-alt mr-1.5"></i>
                    Sync All
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Main Content - Consistent with Time Entries --}}
<div style="padding: 1.5rem 2rem;">

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

    {{-- Provider Status Cards --}}
    @if(count($connectedProviders) > 1)
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($providers as $providerName => $status)
            @if($status['authenticated'])
                <div class="bg-white border border-slate-200/60 rounded-lg p-4" style="box-shadow: var(--theme-card-shadow);">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @if($providerName === 'microsoft')
                                <i class="fab fa-microsoft text-blue-600 text-lg mr-3"></i>
                            @elseif($providerName === 'google')
                                <i class="fab fa-google text-red-500 text-lg mr-3"></i>
                            @elseif($providerName === 'apple')
                                <i class="fab fa-apple text-gray-800 text-lg mr-3"></i>
                            @endif
                            <div>
                                <h4 style="font-weight: 500; color: var(--theme-text); font-size: var(--theme-font-size); text-transform: capitalize;">{{ $providerName }}</h4>
                                <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-secondary);">
                                    @if(isset($status['settings']['last_sync']))
                                        Last sync: {{ \Carbon\Carbon::parse($status['settings']['last_sync'])->diffForHumans() }}
                                    @else
                                        Never synced
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    </div>
                </div>
            @endif
        @endforeach
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
                <h3 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">Upcoming Events</h3>

                @forelse($events->take(8) as $event)
                @php
                    $dateParts = \App\Helpers\DateHelper::formatDateParts($event->start_datetime);
                @endphp
                <div class="flex items-start space-x-4 py-3 border-b border-gray-100 last:border-b-0">
                    {{-- Date/Time --}}
                    <div class="flex-shrink-0 text-center">
                        <div class="text-xs text-gray-500 uppercase tracking-wider">
                            {{ $dateParts['month'] }}
                        </div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $dateParts['day'] }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $dateParts['time'] }}
                        </div>
                    </div>

                    {{-- Event Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 mb-1">
                            <h4 class="text-sm font-medium text-gray-900 truncate">{{ $event->subject }}</h4>
                            {{-- Provider Badge --}}
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium capitalize
                                @if($event->provider_type === 'microsoft') bg-blue-100 text-blue-800
                                @elseif($event->provider_type === 'google') bg-red-100 text-red-800
                                @elseif($event->provider_type === 'apple') bg-gray-100 text-gray-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($event->provider_type === 'microsoft')
                                    <i class="fab fa-microsoft mr-1"></i>
                                @elseif($event->provider_type === 'google')
                                    <i class="fab fa-google mr-1"></i>
                                @elseif($event->provider_type === 'apple')
                                    <i class="fab fa-apple mr-1"></i>
                                @endif
                                {{ $event->provider_type }}
                            </span>
                        </div>
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
                    <p class="text-xs text-gray-400 mt-1">
                        @if(empty($connectedProviders))
                            <a href="{{ route('calendar.providers.index') }}" class="text-blue-600 hover:text-blue-800">Connect a calendar provider</a> to see events here
                        @else
                            Sync your calendars to see events here
                        @endif
                    </p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white border border-slate-200/60 rounded-lg" style="padding: 1.5rem; box-shadow: var(--theme-card-shadow);">
                <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">Quick Actions</h4>

                <div class="space-y-3">
                    <a href="{{ route('calendar.providers.index') }}" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors block">
                        <div class="flex items-center">
                            <i class="fas fa-cog text-blue-500 mr-3"></i>
                            <span class="font-medium">Manage Providers</span>
                        </div>
                    </a>

                    <button onclick="syncAllProviders(event)" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-sync-alt text-green-500 mr-3"></i>
                            <span class="font-medium">Sync All Calendars</span>
                        </div>
                    </button>

                    @if(count($connectedProviders) === 0)
                    <a href="{{ route('calendar.providers.index') }}" class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200 block">
                        <div class="flex items-center">
                            <i class="fas fa-plus text-blue-500 mr-3"></i>
                            <span class="font-medium text-blue-700">Connect Your First Calendar</span>
                        </div>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Provider Summary --}}
            <div class="bg-white border border-slate-200/60 rounded-lg" style="padding: 1.5rem; box-shadow: var(--theme-card-shadow);">
                <h4 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin-bottom: 1rem;">Connected Providers</h4>
                <div class="space-y-2">
                    @foreach($providers as $providerName => $status)
                        <div class="flex items-center justify-between py-1">
                            <div class="flex items-center">
                                @if($providerName === 'microsoft')
                                    <i class="fab fa-microsoft text-blue-600 mr-2"></i>
                                @elseif($providerName === 'google')
                                    <i class="fab fa-google text-red-500 mr-2"></i>
                                @elseif($providerName === 'apple')
                                    <i class="fab fa-apple text-gray-800 mr-2"></i>
                                @endif
                                <span class="text-sm capitalize">{{ $providerName }}</span>
                            </div>
                            @if($status['authenticated'])
                                <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">Connected</span>
                            @else
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Not connected</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Event Creation Modal --}}
<div id="eventModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="relative bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl max-w-2xl w-full mx-4 border border-slate-200/60"
             style="border-radius: var(--theme-border-radius); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.5);">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200/60 bg-gradient-to-r from-blue-50/30 via-white/50 to-purple-50/30">
                <div>
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 flex items-center" style="font-size: calc(var(--theme-font-size) + 4px);">
                        <i class="fas fa-calendar-plus mr-3 text-blue-600"></i>
                        Create New Event
                    </h3>
                    <p class="text-sm text-gray-500 mt-1" style="font-size: calc(var(--theme-font-size) - 1px);">Add a new appointment to your calendar and invite attendees</p>
                </div>
                <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <form id="eventForm" class="p-6 space-y-6 bg-gradient-to-br from-gray-50/30 via-white to-blue-50/20">
                {{-- Event Title --}}
                <div class="space-y-2">
                    <label for="eventTitle" class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-heading mr-2 text-blue-500"></i>Event Title *
                    </label>
                    <input type="text" id="eventTitle" name="title" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/80 hover:bg-white focus:bg-white shadow-sm"
                           style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);"
                           placeholder="Enter event title">
                </div>

                {{-- Date and Time Row --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Start Date/Time --}}
                    <div class="space-y-2">
                        <label for="eventStart" class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                            <i class="fas fa-play mr-2 text-green-500"></i>Start Date & Time *
                        </label>
                        <input type="datetime-local" id="eventStart" name="start" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/80 hover:bg-white focus:bg-white shadow-sm"
                               style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                    </div>

                    {{-- End Date/Time --}}
                    <div class="space-y-2">
                        <label for="eventEnd" class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                            <i class="fas fa-stop mr-2 text-red-500"></i>End Date & Time *
                        </label>
                        <input type="datetime-local" id="eventEnd" name="end" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/80 hover:bg-white focus:bg-white shadow-sm"
                               style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                    </div>
                </div>

                {{-- Calendar Provider --}}
                <div class="space-y-2">
                    <label for="eventProvider" class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-calendar-alt mr-2 text-purple-500"></i>Calendar Provider *
                    </label>
                    <select id="eventProvider" name="provider" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/80 hover:bg-white focus:bg-white shadow-sm"
                            style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                        <option value="">Choose calendar provider...</option>
                        @foreach($providers as $providerName => $status)
                            @if($status['authenticated'])
                                <option value="{{ $providerName }}">
                                    @if($providerName === 'microsoft')
                                        📧 Microsoft Calendar
                                    @elseif($providerName === 'google')
                                        🔍 Google Calendar
                                    @elseif($providerName === 'apple')
                                        🍎 Apple iCloud Calendar
                                    @else
                                        📅 {{ ucfirst($providerName) }} Calendar
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div class="space-y-2">
                    <label for="eventDescription" class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-align-left mr-2 text-gray-500"></i>Description
                    </label>
                    <textarea id="eventDescription" name="description" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none bg-white/80 hover:bg-white focus:bg-white shadow-sm"
                              style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);"
                              placeholder="Optional description or notes for this event"></textarea>
                </div>

                {{-- Location --}}
                <div class="space-y-2">
                    <label for="eventLocation" class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>Location
                    </label>
                    <input type="text" id="eventLocation" name="location"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/80 hover:bg-white focus:bg-white shadow-sm"
                           style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);"
                           placeholder="Meeting room, address, or video call link">
                </div>

                {{-- Attendees Section --}}
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-users mr-2 text-purple-500"></i>Attendees & Invitations
                    </label>

                    {{-- Add Attendee Input --}}
                    <div class="flex gap-2 p-3 bg-gray-50/50 rounded-lg border border-gray-200">
                        <input type="email" id="attendeeEmail" placeholder="Enter attendee email address"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm"
                               style="border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                        <button type="button" onclick="addAttendee()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-sm hover:shadow"
                                style="border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                            <i class="fas fa-plus mr-1"></i>Add
                        </button>
                    </div>

                    {{-- Attendees List --}}
                    <div id="attendeesList" class="space-y-2 max-h-32 overflow-y-auto">
                        {{-- Attendees will be added here dynamically --}}
                    </div>

                    <div class="text-xs text-gray-500 flex items-center mt-2" style="font-size: calc(var(--theme-font-size) - 3px);">
                        <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                        Calendar invitations will be sent automatically to all attendees
                    </div>
                </div>
            </form>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between p-6 border-t border-gray-200/60 bg-gradient-to-r from-gray-50/50 via-white/80 to-blue-50/30 rounded-b-xl">
                <div class="text-sm text-gray-500 flex items-center" style="font-size: calc(var(--theme-font-size) - 2px);">
                    <i class="fas fa-info-circle mr-1.5 text-blue-400"></i>
                    <span>Event will be created in your selected calendar and invitations sent</span>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEventModal()"
                            class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-sm hover:shadow"
                            style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                        <i class="fas fa-times mr-1.5"></i>Cancel
                    </button>
                    <button type="button" onclick="saveEvent()" id="saveEventBtn"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-[1.02]"
                            style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Create Event
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Event Detail Modal --}}
<div id="eventDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="relative bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl max-w-2xl w-full mx-4 border border-slate-200/60 modal-content"
             style="border-radius: var(--theme-border-radius); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.5);">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200/60 bg-gradient-to-r from-green-50/30 via-white/50 to-blue-50/30">
                <div class="flex-1">
                    <h3 id="detailModalTitle" class="text-lg font-semibold text-gray-900 flex items-center mb-2" style="font-size: calc(var(--theme-font-size) + 4px);">
                        <i class="fas fa-calendar-check mr-3 text-green-600"></i>
                        <span id="detailEventTitle">Event Details</span>
                    </h3>
                    <div class="flex items-center text-sm text-gray-500" style="font-size: calc(var(--theme-font-size) - 1px);">
                        <i id="detailProviderIcon" class="fas fa-calendar mr-2"></i>
                        <span id="detailProviderName">Calendar Provider</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="editEvent()" id="editEventBtn" class="text-blue-600 hover:text-blue-800 transition-colors p-2 hover:bg-blue-50 rounded-lg" title="Edit Event">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteEvent()" id="deleteEventBtn" class="text-red-600 hover:text-red-800 transition-colors p-2 hover:bg-red-50 rounded-lg" title="Delete Event">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button onclick="closeEventDetailModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-6 bg-gradient-to-br from-gray-50/30 via-white to-blue-50/20">

                {{-- Date and Time Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                            <i class="fas fa-play mr-2 text-green-500"></i>Start Time
                        </label>
                        <div class="px-4 py-3 bg-white/80 border border-gray-200 rounded-lg shadow-sm" style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                            <span id="detailStartTime">-</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                            <i class="fas fa-stop mr-2 text-red-500"></i>End Time
                        </label>
                        <div class="px-4 py-3 bg-white/80 border border-gray-200 rounded-lg shadow-sm" style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                            <span id="detailEndTime">-</span>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="space-y-2" id="descriptionSection">
                    <label class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-align-left mr-2 text-gray-500"></i>Description
                    </label>
                    <div class="px-4 py-3 bg-white/80 border border-gray-200 rounded-lg shadow-sm min-h-[60px]" style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                        <span id="detailDescription" class="text-gray-600">No description provided</span>
                    </div>
                </div>

                {{-- Location --}}
                <div class="space-y-2" id="locationSection">
                    <label class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>Location
                    </label>
                    <div class="px-4 py-3 bg-white/80 border border-gray-200 rounded-lg shadow-sm" style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                        <span id="detailLocation" class="text-gray-600">No location specified</span>
                    </div>
                </div>

                {{-- Attendees --}}
                <div class="space-y-3" id="attendeesSection">
                    <label class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-users mr-2 text-purple-500"></i>Attendees
                    </label>
                    <div id="detailAttendeesList" class="space-y-2 max-h-40 overflow-y-auto">
                        {{-- Attendees will be populated here --}}
                    </div>
                </div>

                {{-- Organizer Info --}}
                <div class="space-y-2" id="organizerSection">
                    <label class="block text-sm font-medium text-gray-700" style="font-size: var(--theme-font-size);">
                        <i class="fas fa-user-tie mr-2 text-indigo-500"></i>Organizer
                    </label>
                    <div class="px-4 py-3 bg-white/80 border border-gray-200 rounded-lg shadow-sm" style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                        <span id="detailOrganizer" class="text-gray-600">-</span>
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between p-6 border-t border-gray-200/60 bg-gradient-to-r from-gray-50/50 via-white/80 to-blue-50/30 rounded-b-xl">
                <div class="text-sm text-gray-500 flex items-center" style="font-size: calc(var(--theme-font-size) - 2px);">
                    <i class="fas fa-info-circle mr-1.5 text-blue-400"></i>
                    <span>Event synchronized from <span id="detailProviderSource">calendar provider</span></span>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEventDetailModal()"
                            class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-sm hover:shadow"
                            style="border-radius: var(--theme-border-radius); font-size: var(--theme-font-size);">
                        <i class="fas fa-times mr-1.5"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CSS for modal animations --}}
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-content {
    animation: fadeIn 0.2s ease-out;
}
</style>

{{-- JavaScript for sync functionality --}}
<script>
// Calendar sync settings from backend
const syncSettings = @json($calendarSyncSettings);
const shouldAutoSync = @json($shouldAutoSync);
let backgroundSyncInterval;

// Sync all providers function
function syncAllProviders(clickEvent) {
    // Find the button that was clicked
    const button = clickEvent ? clickEvent.target.closest('button') : document.querySelector('button[onclick*="syncAllProviders"]');
    if (!button) return;

    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i>Syncing...';

    fetch('{{ route("calendar.providers.sync-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('success', data.message || 'Calendars synced successfully');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Sync failed');
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        showNotification('error', 'Sync failed due to network error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Show notification function
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg border ${
        type === 'success'
            ? 'bg-green-50 border-green-200 text-green-700'
            : 'bg-red-50 border-red-200 text-red-700'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

// Auto-sync functionality
function setupAutoSync() {
    if (shouldAutoSync && syncSettings.auto_sync) {
        setTimeout(function() {
            syncAllProviders();
        }, 2000);
    }

    if (syncSettings.auto_sync && syncSettings.background_interval > 0) {
        const intervalMs = syncSettings.background_interval * 60 * 1000;
        backgroundSyncInterval = setInterval(function() {
            syncAllProviders();
        }, intervalMs);
    }
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
                    end: '{{ $event->end_datetime->toISOString() }}',
                    color: @if($event->provider_type === 'microsoft') '#0078d4'
                           @elseif($event->provider_type === 'google') '#ea4335'
                           @elseif($event->provider_type === 'apple') '#000000'
                           @else '#6b7280' @endif,
                    extendedProps: {
                        provider: '{{ $event->provider_type }}'
                    }
                });
            @endforeach
        @endif

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,dayGridMonth,timeGridDay,listMonth'
            },
            timeZone: '{{ \App\Models\Setting::get("app_timezone", "Europe/Amsterdam") }}',
            firstDay: 1,
            height: 'auto',

            // Time settings for week view
            slotMinTime: '07:00:00',
            slotMaxTime: '22:00:00',
            slotDuration: '00:30:00',
            slotLabelInterval: '01:00:00',
            scrollTime: '08:00:00',
            nowIndicator: true,

            // Enable event creation
            selectable: true,
            selectMirror: true,
            editable: true,

            // Time format based on settings
            @if(\App\Models\Setting::get('app_time_format', '24-hour') === '24-hour')
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            @else
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            },
            @endif

            events: eventsData,

            // Event interaction handlers
            eventClick: function(info) {
                showEventDetails(info.event);
            },

            // Create new event when user selects time slot
            select: function(info) {
                showCreateEventModal(info.start, info.end);
            },

            // Handle event editing
            eventDrop: function(info) {
                updateEvent(info.event);
            },

            eventResize: function(info) {
                updateEvent(info.event);
            },

            views: {
                listMonth: {
                    buttonText: 'List'
                }
            },
            // Add formatting after calendar renders
            viewDidMount: function(info) {
                setTimeout(function() {
                    applyCustomDateFormatting();
                }, 200);
            },
            datesSet: function(info) {
                setTimeout(function() {
                    applyCustomDateFormatting();
                }, 200);
            }
        });
        calendar.render();

        // Custom date formatting function (safe version)
        function applyCustomDateFormatting() {
            try {
                // Only apply if DD-MM-YYYY format is selected
                const dateFormat = '{{ \App\Models\Setting::get("app_date_format", "DD-MM-YYYY") }}';

                if (dateFormat === 'DD-MM-YYYY' || dateFormat === 'DD/MM/YYYY') {
                    // Update day headers to DD/MM format
                    const dayHeaders = document.querySelectorAll('#calendar .fc-col-header-cell .fc-col-header-cell-cushion');
                    if (dayHeaders.length === 7) { // Only for week view headers
                        // Get the actual dates from FullCalendar's current view
                        const calendarApi = calendar;
                        const view = calendarApi.view;
                        const startOfWeek = view.activeStart;

                        dayHeaders.forEach((header, index) => {
                            const dayDate = new Date(startOfWeek);
                            dayDate.setDate(startOfWeek.getDate() + index);

                            const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                            const weekday = weekdays[dayDate.getDay()];
                            const day = String(dayDate.getDate()).padStart(2, '0');
                            const month = String(dayDate.getMonth() + 1).padStart(2, '0');

                            // Format as "Mon 23/09"
                            header.textContent = `${weekday} ${day}/${month}`;
                        });
                    }
                }
            } catch (error) {
                console.log('Date formatting error (non-critical):', error);
                // Don't break calendar if formatting fails
            }
        }
    }

    // Initialize auto-sync
    setupAutoSync();

    // Modal and helper functions
    window.formatDateTimeLocal = function(date) {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    };

    window.closeEventModal = function() {
        document.getElementById('eventModal').classList.add('hidden');
    };

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('eventModal');
            if (!modal.classList.contains('hidden')) {
                closeEventModal();
            }
        }
        // Save on Ctrl+Enter
        if (e.ctrlKey && e.key === 'Enter') {
            const modal = document.getElementById('eventModal');
            if (!modal.classList.contains('hidden')) {
                saveEvent();
            }
        }
    });

    // Allow adding attendee with Enter key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.id === 'attendeeEmail') {
            e.preventDefault();
            addAttendee();
        }
    });

    window.addAttendee = function() {
        const emailInput = document.getElementById('attendeeEmail');
        const email = emailInput.value.trim();

        if (!email) {
            alert('Please enter an email address');
            return;
        }

        if (!email.includes('@')) {
            alert('Please enter a valid email address');
            return;
        }

        // Check if already added
        const existingAttendees = document.querySelectorAll('.attendee-email');
        for (let attendee of existingAttendees) {
            if (attendee.textContent === email) {
                alert('This attendee has already been added');
                return;
            }
        }

        // Add to attendees list
        const attendeesList = document.getElementById('attendeesList');
        const attendeeDiv = document.createElement('div');
        attendeeDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg border';
        attendeeDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-user-circle text-gray-400 mr-2"></i>
                <span class="attendee-email" style="font-size: var(--theme-font-size);">${email}</span>
            </div>
            <button type="button" onclick="removeAttendee(this)"
                    class="text-red-500 hover:text-red-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        `;

        attendeesList.appendChild(attendeeDiv);
        emailInput.value = '';
    };

    window.removeAttendee = function(button) {
        button.closest('div').remove();
    };

    window.saveEvent = function() {
        const form = document.getElementById('eventForm');
        const formData = new FormData(form);

        // Check if we're editing an existing event
        const editingEventId = form.dataset.editingEventId;

        // Validate required fields
        if (!formData.get('title')) {
            alert('Please enter an event title');
            document.getElementById('eventTitle').focus();
            return;
        }

        if (!formData.get('start')) {
            alert('Please select a start date and time');
            document.getElementById('eventStart').focus();
            return;
        }

        if (!formData.get('end')) {
            alert('Please select an end date and time');
            document.getElementById('eventEnd').focus();
            return;
        }

        if (!formData.get('provider')) {
            alert('Please select a calendar provider');
            document.getElementById('eventProvider').focus();
            return;
        }

        // Validate end time is after start time
        const startTime = new Date(formData.get('start'));
        const endTime = new Date(formData.get('end'));

        if (endTime <= startTime) {
            alert('End time must be after start time');
            document.getElementById('eventEnd').focus();
            return;
        }

        // Get attendees
        const attendees = Array.from(document.querySelectorAll('.attendee-email')).map(span => ({
            email: span.textContent,
            name: span.textContent.split('@')[0] // Use part before @ as name
        }));

        // Prepare event data
        const eventData = {
            title: formData.get('title'),
            description: formData.get('description') || '',
            location: formData.get('location') || '',
            start: formData.get('start'),
            end: formData.get('end'),
            provider: formData.get('provider'),
            attendees: attendees
        };

        // Disable save button and show loading
        const saveBtn = document.getElementById('saveEventBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;

        if (editingEventId) {
            // Update existing event
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
            eventData.id = editingEventId;
            updateEvent(eventData);
            // Clear edit mode
            delete form.dataset.editingEventId;
        } else {
            // Create new event
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            createEvent(eventData);
        }
    };

    // Event management functions
    window.showEventDetails = function(event) {
        const eventDate = formatDateBySettings(event.start);
        const eventTime = event.start.toLocaleTimeString('en-US', {
            hour12: appTimeFormat !== '24-hour',
            hour: appTimeFormat === '24-hour' ? '2-digit' : 'numeric',
            minute: '2-digit'
        });

        const provider = event.extendedProps.provider || 'local';
        const canEdit = provider !== 'readonly';

        let message = `Event: ${event.title}\n`;
        message += `Provider: ${provider}\n`;
        message += `Date: ${eventDate}\n`;
        message += `Time: ${eventTime}`;

        if (canEdit) {
            message += '\n\nOptions:\n- Click OK to edit\n- Cancel to close';
            if (confirm(message)) {
                showEditEventModal(event);
            }
        } else {
            alert(message);
        }
    };

    window.showCreateEventModal = function(start, end) {
        // Check if providers are connected
        const providers = @json(array_keys($providers));
        const connectedProviders = providers.filter(provider => {
            const status = @json($providers);
            return status[provider] && status[provider].authenticated;
        });

        if (connectedProviders.length === 0) {
            alert('No calendar providers connected. Please connect a calendar provider first.');
            return;
        }

        // Set modal title and reset everything for create mode
        document.getElementById('modalTitle').textContent = 'Create New Event';
        document.querySelector('#eventModal p').textContent = 'Add a new appointment to your calendar';
        document.getElementById('saveEventBtn').innerHTML = '<i class="fas fa-save mr-2"></i>Create Event';

        // Clear form and reset to create mode
        const form = document.getElementById('eventForm');
        form.reset();
        delete form.dataset.editingEventId;

        // Re-enable provider selection
        const providerSelect = document.getElementById('eventProvider');
        providerSelect.disabled = false;

        // Set default times in datetime-local format
        const startFormatted = formatDateTimeLocal(start);
        const endFormatted = formatDateTimeLocal(end);

        document.getElementById('eventStart').value = startFormatted;
        document.getElementById('eventEnd').value = endFormatted;

        // Clear attendees list
        document.getElementById('attendeesList').innerHTML = '';

        // Show modal
        document.getElementById('eventModal').classList.remove('hidden');

        // Focus title input
        setTimeout(() => {
            document.getElementById('eventTitle').focus();
        }, 100);
    };


    window.createEvent = function(eventData) {
        fetch('{{ route("calendar.events.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(eventData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal
                closeEventModal();

                // Clear form dataset
                const form = document.getElementById('eventForm');
                delete form.dataset.editingEventId;

                // Show success message with attendees info
                let message = 'Event created successfully';
                if (eventData.attendees && eventData.attendees.length > 0) {
                    message += ` and invitations sent to ${eventData.attendees.length} attendee(s)`;
                }

                showNotification('success', message);

                // Refresh calendar to show new event
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('error', data.message || 'Failed to create event');
            }
        })
        .catch(error => {
            console.error('Create event error:', error);
            showNotification('error', 'Failed to create event');
        })
        .finally(() => {
            // Reset save button
            const saveBtn = document.getElementById('saveEventBtn');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Event';
        });
    };

    window.updateEvent = function(eventData) {
        fetch(`{{ route("calendar.events.update", ":id") }}`.replace(':id', eventData.id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(eventData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal
                closeEventModal();

                // Clear form dataset
                const form = document.getElementById('eventForm');
                delete form.dataset.editingEventId;

                showNotification('success', 'Event updated successfully');
                // Refresh calendar to show updated event
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('error', data.message || 'Failed to update event');
            }
        })
        .catch(error => {
            console.error('Update event error:', error);
            showNotification('error', 'Failed to update event');
        })
        .finally(() => {
            // Reset save button
            const saveBtn = document.getElementById('saveEventBtn');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Event';
        });
    };

    // Event Detail Modal Functions
    window.showEventDetails = function(event) {
        // Get event data - try different ID properties
        let eventId = event.id || event.extendedProps?.id || event.extendedProps?.database_id;

        // Ensure eventId is a valid value
        if (eventId && typeof eventId === 'string') {
            eventId = eventId.trim();
        }

        const title = event.title || 'Untitled Event';
        const start = event.start;
        const end = event.end;
        const provider = event.extendedProps?.provider || 'unknown';

        // Set modal title and provider info
        document.getElementById('detailEventTitle').textContent = title;
        document.getElementById('detailProviderName').textContent = getProviderDisplayName(provider);
        document.getElementById('detailProviderSource').textContent = getProviderDisplayName(provider);

        // Set provider icon
        const providerIcon = document.getElementById('detailProviderIcon');
        if (provider === 'microsoft') {
            providerIcon.className = 'fab fa-microsoft mr-2 text-blue-600';
        } else if (provider === 'google') {
            providerIcon.className = 'fab fa-google mr-2 text-red-500';
        } else if (provider === 'apple') {
            providerIcon.className = 'fab fa-apple mr-2 text-gray-800';
        } else {
            providerIcon.className = 'fas fa-calendar mr-2 text-gray-600';
        }

        // Format dates using the DateHelper approach
        const formatDateTime = (date) => {
            if (!date) return '-';
            const options = {
                @if(\App\Models\Setting::get('app_date_format', 'DD-MM-YYYY') === 'DD-MM-YYYY')
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                @elseif(\App\Models\Setting::get('app_date_format', 'DD-MM-YYYY') === 'MM-DD-YYYY')
                month: '2-digit',
                day: '2-digit',
                year: 'numeric',
                @else
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                @endif
                @if(\App\Models\Setting::get('app_time_format', '24-hour') === '24-hour')
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
                @else
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
                @endif
            };

            return new Date(date).toLocaleString('{{ str_replace('_', '-', app()->getLocale()) }}', options);
        };

        // Set date and time info
        document.getElementById('detailStartTime').textContent = formatDateTime(start);
        document.getElementById('detailEndTime').textContent = formatDateTime(end);

        // Get extended event data from server
        if (!eventId) {
            console.error('No event ID found, cannot fetch details');
            return;
        }

        console.log('Fetching details for event ID:', eventId);
        fetchEventDetails(eventId).then(eventData => {
            console.log('Received event data:', eventData);
            if (eventData) {
                // Store event data for edit/delete functionality
                currentEventData = eventData;
                // Set description
                const descriptionEl = document.getElementById('detailDescription');
                if (eventData.body && eventData.body.trim()) {
                    descriptionEl.textContent = eventData.body;
                    descriptionEl.className = descriptionEl.className.replace('text-gray-600', 'text-gray-900');
                } else {
                    descriptionEl.textContent = 'No description provided';
                    descriptionEl.className = descriptionEl.className.replace('text-gray-900', 'text-gray-600');
                }

                // Set location
                const locationEl = document.getElementById('detailLocation');
                if (eventData.location && eventData.location.trim()) {
                    locationEl.textContent = eventData.location;
                    locationEl.className = locationEl.className.replace('text-gray-600', 'text-gray-900');
                } else {
                    locationEl.textContent = 'No location specified';
                    locationEl.className = locationEl.className.replace('text-gray-900', 'text-gray-600');
                }

                // Set attendees
                const attendeesListEl = document.getElementById('detailAttendeesList');
                if (eventData.attendees && eventData.attendees.length > 0) {
                    attendeesListEl.innerHTML = '';
                    eventData.attendees.forEach(attendee => {
                        const attendeeEl = document.createElement('div');
                        attendeeEl.className = 'flex items-center p-3 bg-white/80 border border-gray-200 rounded-lg shadow-sm';
                        attendeeEl.style.borderRadius = 'var(--theme-border-radius)';
                        attendeeEl.innerHTML = `
                            <div class="flex items-center">
                                <i class="fas fa-user-circle text-gray-400 mr-3 text-lg"></i>
                                <div>
                                    <div class="font-medium text-gray-900" style="font-size: var(--theme-font-size);">${attendee.name || attendee.email}</div>
                                    ${attendee.name && attendee.email !== attendee.name ? `<div class="text-sm text-gray-500" style="font-size: calc(var(--theme-font-size) - 2px);">${attendee.email}</div>` : ''}
                                </div>
                            </div>
                            ${attendee.status ? `<div class="ml-auto">
                                <span class="px-2 py-1 text-xs rounded-full ${getStatusColor(attendee.status)}" style="font-size: calc(var(--theme-font-size) - 3px);">
                                    ${getStatusText(attendee.status)}
                                </span>
                            </div>` : ''}
                        `;
                        attendeesListEl.appendChild(attendeeEl);
                    });
                } else {
                    attendeesListEl.innerHTML = `
                        <div class="text-center py-4 text-gray-500" style="font-size: var(--theme-font-size);">
                            <i class="fas fa-users text-2xl mb-2 text-gray-300"></i>
                            <p>No attendees for this event</p>
                        </div>
                    `;
                }

                // Set organizer
                const organizerEl = document.getElementById('detailOrganizer');
                if (eventData.organizer_name || eventData.organizer_email) {
                    const organizerText = eventData.organizer_name ?
                        `${eventData.organizer_name} (${eventData.organizer_email || 'No email'})` :
                        eventData.organizer_email;
                    organizerEl.textContent = organizerText;
                    organizerEl.className = organizerEl.className.replace('text-gray-600', 'text-gray-900');
                } else {
                    organizerEl.textContent = 'Unknown organizer';
                    organizerEl.className = organizerEl.className.replace('text-gray-900', 'text-gray-600');
                }
            }
        }).catch(error => {
            console.error('Error fetching event details:', error);
            // Still show modal with basic info even if fetch fails
        });

        // Show modal
        const modal = document.getElementById('eventDetailModal');
        modal.classList.remove('hidden');

        // Force scroll to top and focus
        document.body.style.overflow = 'hidden'; // Prevent background scroll
        modal.scrollTop = 0; // Reset modal scroll
        window.scrollTo(0, 0); // Reset page scroll

        // Add a visible flash effect to make it clear the modal is showing
        modal.style.animation = 'none';
        setTimeout(() => {
            modal.style.animation = 'fadeIn 0.2s ease-out';
        }, 10);

        // Add ESC key handler
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closeEventDetailModal();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    };

    window.closeEventDetailModal = function() {
        document.getElementById('eventDetailModal').classList.add('hidden');
        document.body.style.overflow = ''; // Restore background scroll
    };

    let currentEventData = null; // Store current event data for edit/delete

    window.editEvent = function() {
        if (!currentEventData) {
            showNotification('error', 'No event selected for editing');
            return;
        }

        // Close detail modal
        closeEventDetailModal();

        // Open create modal in edit mode
        setTimeout(() => {
            showEditEventModal(currentEventData);
        }, 200);
    };

    window.deleteEvent = function() {
        if (!currentEventData) {
            showNotification('error', 'No event selected for deletion');
            return;
        }

        // Show confirmation dialog
        if (confirm(`Are you sure you want to delete "${currentEventData.title}"?\n\nThis will permanently remove the event from your ${getProviderDisplayName(currentEventData.provider_type)}.`)) {
            deleteEventConfirmed(currentEventData.id);
        }
    };

    // Helper functions
    function getProviderDisplayName(provider) {
        switch(provider) {
            case 'microsoft': return 'Microsoft Calendar';
            case 'google': return 'Google Calendar';
            case 'apple': return 'Apple iCloud Calendar';
            default: return 'Calendar Provider';
        }
    }

    function getStatusColor(status) {
        switch(status?.toLowerCase()) {
            case 'accepted': return 'bg-green-100 text-green-800';
            case 'tentative': return 'bg-yellow-100 text-yellow-800';
            case 'declined': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function getStatusText(status) {
        switch(status?.toLowerCase()) {
            case 'accepted': return 'Accepted';
            case 'tentative': return 'Tentative';
            case 'declined': return 'Declined';
            case 'none': return 'No Response';
            default: return 'Pending';
        }
    }

    function fetchEventDetails(eventId) {
        return fetch(`{{ route("calendar.events.details", ":id") }}`.replace(':id', eventId), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            return data.event || null;
        })
        .catch(error => {
            console.error('Fetch event details error:', error);
            return null;
        });
    }

    // Edit Event Modal Functions
    window.showEditEventModal = function(eventData) {
        console.log('Editing event:', eventData);

        // Change modal title and button text
        document.getElementById('modalTitle').textContent = 'Edit Event';
        document.querySelector('#eventModal p').textContent = 'Update your appointment details';
        document.getElementById('saveEventBtn').innerHTML = '<i class="fas fa-save mr-2"></i>Update Event';

        // Pre-fill form fields
        document.getElementById('eventTitle').value = eventData.title || '';
        document.getElementById('eventDescription').value = eventData.body || '';
        document.getElementById('eventLocation').value = eventData.location || '';

        // Format dates for datetime-local input (requires YYYY-MM-DDTHH:MM format)
        const startDate = new Date(eventData.start_datetime);
        const endDate = new Date(eventData.end_datetime);

        document.getElementById('eventStart').value = formatDateTimeLocal(startDate);
        document.getElementById('eventEnd').value = formatDateTimeLocal(endDate);

        // Set provider (disabled for editing)
        const providerSelect = document.getElementById('eventProvider');
        providerSelect.value = eventData.provider_type;
        providerSelect.disabled = true; // Can't change provider when editing

        // Pre-fill attendees if any
        const attendeesList = document.getElementById('attendeesList');
        attendeesList.innerHTML = ''; // Clear existing attendees

        if (eventData.attendees && eventData.attendees.length > 0) {
            eventData.attendees.forEach(attendee => {
                addAttendeeToList(attendee.email, attendee.name);
            });
        }

        // Store original event ID for update
        document.getElementById('eventForm').dataset.editingEventId = eventData.id;

        // Show modal
        document.getElementById('eventModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    function addAttendeeToList(email, name) {
        const attendeesList = document.getElementById('attendeesList');
        const attendeeDiv = document.createElement('div');
        attendeeDiv.className = 'flex items-center justify-between p-3 bg-white/80 border border-gray-200 rounded-lg shadow-sm';
        attendeeDiv.style.borderRadius = 'var(--theme-border-radius)';

        attendeeDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-user-circle text-gray-400 mr-3 text-lg"></i>
                <div>
                    <div class="font-medium text-gray-900" style="font-size: var(--theme-font-size);">${name || email}</div>
                    ${name && email !== name ? `<div class="text-sm text-gray-500" style="font-size: calc(var(--theme-font-size) - 2px);">${email}</div>` : ''}
                </div>
            </div>
            <button type="button" onclick="removeAttendee(this)"
                    class="text-red-500 hover:text-red-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        `;

        attendeesList.appendChild(attendeeDiv);
    }

    // Delete Event Functions
    window.deleteEventConfirmed = function(eventId) {
        console.log('Deleting event ID:', eventId);

        fetch(`{{ route("calendar.events.delete", ":id") }}`.replace(':id', eventId), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('success', 'Event deleted successfully');
                closeEventDetailModal();
                // Refresh calendar to remove deleted event
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('error', data.message || 'Failed to delete event');
            }
        })
        .catch(error => {
            console.error('Delete event error:', error);
            showNotification('error', 'Failed to delete event');
        });
    };
});
</script>

{{-- FullCalendar CSS and JS --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

@endsection