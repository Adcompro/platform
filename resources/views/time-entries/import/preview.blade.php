@extends('layouts.app')

@section('title', 'Preview Time Entries Import')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 pb-32">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Preview Import Data</h1>
                    <p class="text-sm text-slate-600 mt-1">Review the parsed data before importing</p>
                </div>
                <div class="flex space-x-3">
                    <form action="{{ route('time-entries.import.cancel') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Import Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">File</p>
                        <p class="text-lg font-bold text-slate-900 truncate">{{ $filename }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-excel text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-green-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Valid Entries</p>
                        <p class="text-2xl font-bold text-green-600">{{ count($data) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-red-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Errors</p>
                        <p class="text-2xl font-bold text-red-600">{{ count($errors) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-purple-200/60 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Total Hours</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ array_sum(array_map(function($entry) {
                                return $entry['hours'] + ($entry['minutes'] / 60);
                            }, $data)) }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Errors Section --}}
        @if(count($errors) > 0)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200 bg-red-100">
                <h3 class="text-base font-semibold text-red-900">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ count($errors) }} Row(s) with Errors
                </h3>
                <p class="text-sm text-red-700 mt-1">These rows will not be imported. Please fix the errors and try again.</p>
            </div>
            <div class="p-6">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach($errors as $error)
                    <div class="bg-white border border-red-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-900">Row {{ $error['row'] }}</p>
                                <ul class="mt-2 space-y-1">
                                    @foreach($error['errors'] as $errorMsg)
                                    <li class="text-sm text-red-700">• {{ $errorMsg }}</li>
                                    @endforeach
                                </ul>
                                @if(isset($error['data']) && is_array($error['data']))
                                <div class="mt-2 text-xs text-slate-600 bg-slate-50 rounded p-2">
                                    <strong>Data:</strong> {{ implode(' | ', array_filter($error['data'])) }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Valid Entries Preview --}}
        @if(count($data) > 0)
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50/50 to-white/50 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Valid Entries Preview</h3>
                    <p class="text-sm text-slate-600 mt-1">These entries will be imported</p>
                </div>
                <form action="{{ route('time-entries.import.import') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                        <i class="fas fa-check mr-2"></i>Confirm Import ({{ count($data) }} entries)
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Duration</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Project</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Work Item</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Rate</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Billable</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($data as $entry)
                        <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                {{ \Carbon\Carbon::parse($entry['entry_date'])->format('d-m-Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                {{ $entry['user_name'] ?? 'Unknown' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                {{ $entry['hours'] }}h {{ $entry['minutes'] }}m
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900">
                                <div class="max-w-xs truncate" title="{{ $entry['project_name'] ?? 'Unknown' }}">
                                    {{ $entry['project_name'] ?? 'Unknown' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                @if(isset($entry['milestone_name']))
                                    <div class="text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $entry['milestone_name'] }}
                                        </span>
                                    </div>
                                @endif
                                @if(isset($entry['task_name']))
                                    <div class="text-xs mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $entry['task_name'] }}
                                        </span>
                                    </div>
                                @endif
                                @if(isset($entry['subtask_name']))
                                    <div class="text-xs mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $entry['subtask_name'] }}
                                        </span>
                                    </div>
                                @endif
                                @if(!isset($entry['milestone_name']) && !isset($entry['task_name']) && !isset($entry['subtask_name']))
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <div class="max-w-md truncate" title="{{ $entry['description'] }}">
                                    {{ $entry['description'] ?: '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">
                                @if(isset($entry['hourly_rate_override']))
                                    €{{ number_format($entry['hourly_rate_override'], 2) }}
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($entry['is_billable'] === 'billable')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Billable
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-times-circle mr-1"></i>Non-billable
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Info --}}
            @if(count($data) > 20)
            <div class="px-6 py-4 border-t border-slate-200/50 bg-slate-50/50">
                <p class="text-sm text-slate-600">
                    Showing first 20 entries. Total: {{ count($data) }} entries will be imported.
                </p>
            </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div class="mt-6 flex items-center justify-between">
            <form action="{{ route('time-entries.import.cancel') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>Cancel Import
                </button>
            </form>

            @if(count($data) > 0)
            <form action="{{ route('time-entries.import.import') }}" method="POST">
                @csrf
                <button type="submit" class="px-8 py-3 text-base font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-lg">
                    <i class="fas fa-check-circle mr-2"></i>Confirm & Import {{ count($data) }} Entries
                </button>
            </form>
            @endif
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <i class="fas fa-exclamation-circle text-yellow-600 text-3xl mb-3"></i>
            <p class="text-lg font-medium text-yellow-900">No valid entries found</p>
            <p class="text-sm text-yellow-700 mt-2">Please fix the errors and upload the file again.</p>
            <a href="{{ route('time-entries.import.index') }}" class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition-all duration-200">
                <i class="fas fa-upload mr-2"></i>Upload New File
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
