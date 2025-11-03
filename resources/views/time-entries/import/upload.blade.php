@extends('layouts.app')

@section('title', 'Upload Time Entries')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 pb-32">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Upload Time Entries</h1>
                    <p class="text-sm text-slate-600 mt-1">Import time registrations from Excel or CSV files</p>
                </div>
                <div>
                    <a href="{{ route('time-entries.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Time Entries
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Total Entries</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $stats['total_entries'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Active Users</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $stats['users_count'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600">Active Projects</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $stats['projects_count'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-project-diagram text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Form Card --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200/50 bg-gradient-to-r from-slate-50/50 to-white/50">
                <h2 class="text-lg font-semibold text-slate-900">Upload File</h2>
                <p class="text-sm text-slate-600 mt-1">Select an Excel or CSV file containing time entries</p>
            </div>

            <div class="p-8">
                <form action="{{ route('time-entries.import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- File Input --}}
                    <div>
                        <label for="file" class="block text-sm font-medium text-slate-700 mb-2">
                            Choose File <span class="text-red-500">*</span>
                        </label>
                        <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg hover:border-slate-400 transition-colors duration-200">
                            <div class="space-y-2 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-slate-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="file" name="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv" required onchange="updateFileName(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-slate-500">XLSX, XLS or CSV up to 10MB</p>
                                <p id="file-name" class="text-sm font-medium text-slate-700 mt-2"></p>
                            </div>
                        </div>
                        @error('file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Supported Format Info --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-blue-800">Supported Formats</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p class="mb-2">The file should contain the following columns:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li><strong>Date</strong> (Datum) - Entry date</li>
                                        <li><strong>User</strong> (Gebruiker) - User name or email</li>
                                        <li><strong>Hours</strong> (Uren) OR <strong>Duration (minutes)</strong> (Duur in minuten)</li>
                                        <li><strong>Description</strong> (Omschrijving) - Work description</li>
                                        <li><strong>Project</strong> - Project name</li>
                                        <li><em>Optional:</em> Milestone (Fase), Task (Type), Hourly Rate (Uurprijs), Billable (Factureerbaar)</li>
                                    </ul>
                                    <p class="mt-3 text-xs">✅ Supports both English and Dutch column names</p>
                                    <p class="text-xs">✅ Automatically detects format</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('time-entries.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <i class="fas fa-upload mr-2"></i>Upload & Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Example File Section --}}
        <div class="mt-6 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/50">
                <h3 class="text-base font-medium text-slate-900">Need an Example?</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-4">
                    Download a sample template to see the expected format:
                </p>
                <a href="#" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all duration-200">
                    <i class="fas fa-download mr-2 text-slate-500"></i>
                    Download Sample Template
                </a>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
@push('scripts')
<script>
function updateFileName(input) {
    const fileNameDisplay = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        const fileName = input.files[0].name;
        const fileSize = (input.files[0].size / 1024 / 1024).toFixed(2);
        fileNameDisplay.textContent = `📄 ${fileName} (${fileSize} MB)`;
    } else {
        fileNameDisplay.textContent = '';
    }
}

// Drag & Drop functionaliteit
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file');
    const fileNameDisplay = document.getElementById('file-name');

    if (!dropZone || !fileInput) {
        console.error('Drop zone or file input not found!');
        return;
    }

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
        dropZone.classList.remove('border-slate-300');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        dropZone.classList.add('border-slate-300');
    }

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            const file = files[0];

            // Validate file type
            const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                              'application/vnd.ms-excel',
                              'text/csv'];
            const validExtensions = ['.xlsx', '.xls', '.csv'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

            if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
                alert('Please upload a valid Excel or CSV file (.xlsx, .xls, or .csv)');
                return;
            }

            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB');
                return;
            }

            // Assign file to input element
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            // Update file name display
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            fileNameDisplay.textContent = `📄 ${file.name} (${fileSize} MB)`;
            fileNameDisplay.classList.add('text-green-600');

            console.log('File successfully assigned:', file.name);
        }
    }
});
</script>
@endpush
@endsection
