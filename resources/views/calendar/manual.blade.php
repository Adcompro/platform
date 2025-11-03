@extends('layouts.app')

@section('title', 'Manual Calendar Entry')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Calendar Events</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manual calendar event management</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="openAddEventModal()" class="px-3 py-1.5 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-all duration-200">
                        <i class="fas fa-plus mr-1.5"></i>
                        Add Event
                    </button>
                    <a href="{{ route('calendar.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-sync mr-1.5"></i>
                        Try Sync Again
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50/50 border border-green-200/50 text-green-700 px-3 py-2.5 rounded-lg">
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50/50 border border-red-200/50 text-red-700 px-3 py-2.5 rounded-lg">
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Exchange Issue Notice --}}
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-900">Microsoft 365 Calendar Sync Issue</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Your Microsoft account appears to be using on-premises Exchange or doesn't have Exchange Online enabled. This prevents automatic calendar synchronization.</p>
                        <p class="mt-2"><strong>Alternative options:</strong></p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>Manually add calendar events below</li>
                            <li>Export your calendar from Outlook and import events</li>
                            <li>Contact your IT administrator to enable Exchange Online</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Calendar View --}}
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-sm shadow-sm rounded-xl border border-slate-200/50 overflow-hidden">
                    <div class="p-4">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>

            {{-- Event List --}}
            <div class="lg:col-span-1">
                <div class="bg-white/80 backdrop-blur-sm shadow-sm rounded-xl border border-slate-200/50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h3 class="text-sm font-medium text-slate-900">Manual Events</h3>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto">
                        <div class="space-y-2">
                            @forelse($events as $event)
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/50 hover:shadow-sm transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-slate-900">{{ $event->subject }}</h4>
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $event->start_datetime->format('M d, H:i') }} - {{ $event->end_datetime->format('H:i') }}
                                        </p>
                                        @if($event->location)
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $event->location }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        @if($event->is_converted)
                                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                            Converted
                                        </span>
                                        @else
                                        <button onclick="convertEvent({{ $event->id }})" class="text-blue-500 hover:text-blue-700 p-1">
                                            <i class="fas fa-exchange-alt text-xs"></i>
                                        </button>
                                        @endif
                                        <button onclick="deleteEvent({{ $event->id }})" class="text-red-500 hover:text-red-700 p-1">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-slate-500 text-center py-4">No events added yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Import Section --}}
                <div class="mt-4 bg-blue-50 rounded-lg border border-blue-200 p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Import Options</h4>
                    <button onclick="openImportModal()" class="w-full px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all">
                        <i class="fas fa-file-import mr-1.5"></i>
                        Import ICS File
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Event Modal --}}
<div id="addEventModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full">
            <form method="POST" action="{{ route('calendar.manual.store') }}">
                @csrf
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Add Calendar Event</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Event Title *</label>
                        <input type="text" name="subject" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Start Date *</label>
                            <input type="datetime-local" name="start_datetime" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">End Date *</label>
                            <input type="datetime-local" name="end_datetime" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
                        <input type="text" name="location" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="body" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex justify-between">
                    <button type="button" onclick="closeAddEventModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Add Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full">
            <form method="POST" action="{{ route('calendar.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Import ICS File</h3>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Select ICS File</label>
                        <input type="file" name="ics_file" accept=".ics" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <p class="text-xs text-slate-500 mt-1">Export your calendar from Outlook as an ICS file and upload it here</p>
                    </div>
                    
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                        <h4 class="text-sm font-medium text-slate-700 mb-2">How to export from Outlook:</h4>
                        <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1">
                            <li>Open Outlook and go to File → Open & Export</li>
                            <li>Select "Import/Export"</li>
                            <li>Choose "Export to a file"</li>
                            <li>Select "iCalendar Format (.ics)"</li>
                            <li>Choose your calendar and date range</li>
                            <li>Save the file and upload it here</li>
                        </ol>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex justify-between">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Import Events
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- FullCalendar --}}
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
    let calendar;

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '{{ route("calendar.events") }}',
            eventClick: function(info) {
                // Handle event click
            }
        });
        calendar.render();
    });

    function openAddEventModal() {
        document.getElementById('addEventModal').classList.remove('hidden');
    }

    function closeAddEventModal() {
        document.getElementById('addEventModal').classList.add('hidden');
    }

    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

    function convertEvent(eventId) {
        window.location.href = `/calendar/events/${eventId}/convert`;
    }

    function deleteEvent(eventId) {
        if (confirm('Are you sure you want to delete this event?')) {
            // Add delete functionality
        }
    }
</script>
@endpush