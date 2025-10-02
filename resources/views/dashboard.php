@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Welcome back, {{ auth()->user()->name }}!
                        </h1>
                        <p class="text-gray-600 mt-1">
                            {{ auth()->user()->company->name }} | {{ ucfirst(auth()->user()->role) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
                        <p class="text-sm text-gray-500">{{ now()->format('H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Active Projects -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Active Projects</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_projects'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Month Hours -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">This Month Hours</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['this_month_hours'], 1) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'project_manager']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending Approvals</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_approvals'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- This Month Revenue -->
            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">This Month Revenue</p>
                            <p class="text-2xl font-semibold text-gray-900">€{{ number_format($stats['this_month_revenue'], 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Projects & Recent Activity -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recent Projects -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Projects</h2>
                            <a href="{{ route('projects.index') }}" class="text-sm text-blue-600 hover:text-blue-500">
                                View all →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($recentProjects->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentProjects as $project)
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($project->status === 'active') bg-green-100 text-green-800
                                                    @elseif($project->status === 'planning') bg-blue-100 text-blue-800
                                                    @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800
                                                    @elseif($project->status === 'completed') bg-gray-100 text-gray-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">
                                                        {{ $project->name }}
                                                    </a>
                                                </h3>
                                                <p class="text-sm text-gray-500">{{ $project->customer->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            €{{ number_format($project->monthly_fee, 0) }}/month
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $project->milestones_count }} milestones
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No projects</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new project.</p>
                                <div class="mt-6">
                                    <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        New Project
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Time Tracking Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Weekly Time Overview</h2>
                    </div>
                    <div class="p-6">
                        <canvas id="timeChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right Column - Quick Actions & Notifications -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('time-entries.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Log Time
                            </a>
                            <a href="{{ route('projects.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                New Project
                            </a>
                            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                            <a href="{{ route('invoices.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                New Invoice
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pending Time Approvals -->
                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'project_manager']) && $pendingTimeEntries->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Pending Approvals</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                {{ $pendingTimeEntries->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($pendingTimeEntries->take(5) as $entry)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $entry->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $entry->description }}</p>
                                    <p class="text-xs text-gray-400">{{ $entry->hours }}h on {{ $entry->date->format('M j') }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="approveTimeEntry({{ $entry->id }})" class="text-green-600 hover:text-green-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    <button onclick="rejectTimeEntry({{ $entry->id }})" class="text-red-600 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @if($pendingTimeEntries->count() > 5)
                            <div class="text-center">
                                <a href="{{ route('time-entries.index', ['status' => 'pending']) }}" class="text-sm text-blue-600 hover:text-blue-500">
                                    View {{ $pendingTimeEntries->count() - 5 }} more →
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Upcoming Deadlines -->
                @if($upcomingDeadlines->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Upcoming Deadlines</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($upcomingDeadlines as $milestone)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $milestone->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $milestone->project->name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium 
                                        @if($milestone->end_date->isPast()) text-red-600
                                        @elseif($milestone->end_date->isToday()) text-orange-600
                                        @elseif($milestone->end_date->diffInDays() <= 3) text-yellow-600
                                        @else text-gray-900 @endif">
                                        {{ $milestone->end_date->format('M j') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $milestone->end_date->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Time Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">Are you sure you want to perform this action?</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmButton" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700">
                    Confirm
                </button>
                <button onclick="closeModal()" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Time Chart
const ctx = document.getElementById('timeChart').getContext('2d');
const timeChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Hours',
            data: @json($chartData['data']),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + 'h';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Time Entry Approval Functions
let currentTimeEntryId = null;
let currentAction = null;

function approveTimeEntry(id) {
    currentTimeEntryId = id;
    currentAction = 'approve';
    document.getElementById('modalTitle').textContent = 'Approve Time Entry';
    document.getElementById('modalMessage').textContent = 'Are you sure you want to approve this time entry?';
    document.getElementById('confirmButton').textContent = 'Approve';
    document.getElementById('confirmButton').className = 'px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-700';
    document.getElementById('approvalModal').classList.remove('hidden');
}

function rejectTimeEntry(id) {
    currentTimeEntryId = id;
    currentAction = 'reject';
    document.getElementById('modalTitle').textContent = 'Reject Time Entry';
    document.getElementById('modalMessage').textContent = 'Are you sure you want to reject this time entry?';
    document.getElementById('confirmButton').textContent = 'Reject';
    document.getElementById('confirmButton').className = 'px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700';
    document.getElementById('approvalModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    currentTimeEntryId = null;
    currentAction = null;
}

document.getElementById('confirmButton').addEventListener('click', function() {
    if (currentTimeEntryId && currentAction) {
        fetch(`/time-entries/${currentAction}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                time_entry_ids: [currentTimeEntryId]
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
        
        closeModal();
    }
});

// Close modal when clicking outside
document.getElementById('approvalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endpush