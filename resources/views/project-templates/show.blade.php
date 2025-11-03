@extends('layouts.app')

@section('content')
<div class="min-h-screen" style="background: var(--theme-gradient-bg);">
    {{-- Header Section --}}
    <div style="background: var(--theme-card-bg); backdrop-filter: blur(8px); border-bottom: 1px solid var(--theme-border-light);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold" style="color: var(--theme-text-primary);">{{ $projectTemplate->name }}</h1>
                    <p style=" color: var(--theme-text-secondary);">
                        @if(isset($projectTemplate->category) && $projectTemplate->category)
                            {{ $projectTemplate->category }} • 
                        @endif
                        Created {{ $projectTemplate->created_at->format('M j, Y') }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('project-templates.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                       style="background-color: var(--theme-secondary);  border-color: var(--theme-secondary);"
                       onmouseover="this.style.backgroundColor='var(--theme-secondary-dark)'"
                       onmouseout="this.style.backgroundColor='var(--theme-secondary)'">
                        ← Back to Templates
                    </a>
                    <a href="{{ route('project-templates.edit', $projectTemplate) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                       style="background-color: var(--theme-primary);  border-color: var(--theme-primary);"
                       onmouseover="this.style.backgroundColor='var(--theme-primary-dark)'"
                       onmouseout="this.style.backgroundColor='var(--theme-primary)'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Template
                    </a>
                    <div class="relative">
                        <button onclick="toggleDropdown('actions-dropdown')" 
                                class="px-4 py-2 rounded-lg transition-colors"
                                style="background-color: var(--theme-muted); color: var(--theme-text-secondary); "
                                onmouseover="this.style.backgroundColor='var(--theme-muted-hover)'"
                                onmouseout="this.style.backgroundColor='var(--theme-muted)'">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div id="actions-dropdown" 
                             class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg z-10"
                             style="background: var(--theme-card-bg); border: 1px solid var(--theme-border);">
                            <div class="py-1">
                                <a href="#" onclick="duplicateTemplate()" 
                                   class="block px-4 py-2 transition-colors"
                                   style=" color: var(--theme-text-secondary);"
                                   onmouseover="this.style.backgroundColor='var(--theme-hover)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    <i class="fas fa-copy mr-2"></i>Dupliceren
                                </a>
                                <a href="#" onclick="exportTemplate()" 
                                   class="block px-4 py-2 transition-colors"
                                   style=" color: var(--theme-text-secondary);"
                                   onmouseover="this.style.backgroundColor='var(--theme-hover)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    <i class="fas fa-download mr-2"></i>Exporteren
                                </a>
                                <div style="border-top: 1px solid var(--theme-border);"></div>
                                <a href="#" onclick="deleteTemplate()" 
                                   class="block px-4 py-2 transition-colors"
                                   style=" color: var(--theme-danger);"
                                   onmouseover="this.style.backgroundColor='var(--theme-hover)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    <i class="fas fa-trash mr-2"></i>Verwijderen
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                {{-- Template Info --}}
                <div class="rounded-lg shadow-sm p-6 mb-8" style="background: var(--theme-card-bg); border: 1px solid var(--theme-border);">
                    <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--theme-border-light);">
                        <h3 class="text-[17px] font-semibold" style="color: var(--theme-text-primary);">Template Details</h3>
                    </div>
                    <div>
                        @if($projectTemplate->description)
                            <div class="mb-6">
                                <h4 class="font-medium mb-2" style=" color: var(--theme-text-secondary);">Description</h4>
                                <p style=" color: var(--theme-text-secondary);">{{ $projectTemplate->description }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @if(isset($projectTemplate->default_hourly_rate) && $projectTemplate->default_hourly_rate)
                                <div>
                                    <h4 class="font-medium mb-1" style=" color: var(--theme-text-secondary);">Default Hourly Rate</h4>
                                    <p class="text-lg font-semibold" style="color: var(--theme-text-primary);">€{{ number_format($projectTemplate->default_hourly_rate, 2) }}</p>
                                </div>
                            @endif

                            @if(isset($projectTemplate->estimated_duration_days) && $projectTemplate->estimated_duration_days)
                                <div>
                                    <h4 class="font-medium mb-1" style=" color: var(--theme-text-secondary);">Estimated Duration</h4>
                                    <p class="text-lg font-semibold" style="color: var(--theme-text-primary);">{{ $projectTemplate->estimated_duration_days }} days</p>
                                </div>
                            @endif

                            @if(isset($projectTemplate->status))
                                <div>
                                    <h4 class="font-medium mb-1" style=" color: var(--theme-text-secondary);">Status</h4>
                                    <span class="inline-flex px-2 py-1 font-semibold rounded-full" 
                                          style="font-size: calc(var(--theme-font-size) - 2px); 
                                                 {{ $projectTemplate->status === 'active' ? 'background-color: var(--theme-success-bg); color: var(--theme-success);' : 'background-color: var(--theme-muted); color: var(--theme-text-secondary);' }}">
                                        {{ ucfirst($projectTemplate->status) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Template Structure --}}
                <div class="rounded-lg shadow-sm p-6" style="background: var(--theme-card-bg); border: 1px solid var(--theme-border);">
                    <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--theme-border-light);">
                        <h3 class="text-[17px] font-semibold" style="color: var(--theme-text-primary);">Template Structure</h3>
                        <p class="mt-1" style=" color: var(--theme-text-muted);">Milestones and tasks in this template</p>
                    </div>
                    <div>
                        @if($projectTemplate->milestones->count() > 0)
                            <div class="space-y-6">
                                @foreach($projectTemplate->milestones as $milestone)
                                    <div class="rounded-lg p-6" style="border: 1px solid rgba(var(--theme-primary-rgb), 0.3); background: rgba(var(--theme-primary-rgb), 0.05);">
                                        {{-- Milestone Header --}}
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-medium mr-3"
                                                         style="background: rgba(var(--theme-primary-rgb), 0.2); color: var(--theme-primary); ">
                                                        {{ $loop->iteration }}
                                                    </div>
                                                    <h4 class="text-lg font-medium" style="color: var(--theme-primary);">{{ $milestone->name }}</h4>
                                                </div>
                                                @if($milestone->description)
                                                    <p class="mt-2 ml-11" style=" color: var(--theme-text-secondary);">{{ $milestone->description }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right ml-4">
                                                @if($milestone->estimated_hours)
                                                    <div style=" color: var(--theme-text-muted);">{{ $milestone->estimated_hours }}h</div>
                                                @endif
                                                @if($milestone->effective_hourly_rate)
                                                    <div class="font-medium" style=" color: var(--theme-text-primary);">€{{ number_format($milestone->estimated_value, 2) }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Milestone Details --}}
                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 ml-11" style=" color: var(--theme-text-secondary);">
                                            @if($milestone->fee_type)
                                                <div>
                                                    <span class="font-medium">Type:</span>
                                                    <span class="inline-flex px-2 py-1 rounded-full ml-1"
                                                          style="font-size: calc(var(--theme-font-size) - 2px); 
                                                                 {{ $milestone->fee_type === 'in_fee' ? 'background-color: var(--theme-success-bg); color: var(--theme-success);' : 'background-color: var(--theme-warning-bg); color: var(--theme-warning);' }}">
                                                        {{ $milestone->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                                    </span>
                                                </div>
                                            @endif
                                            @if($milestone->start_date)
                                                <div>
                                                    <span class="font-medium">Start:</span> {{ \Carbon\Carbon::parse($milestone->start_date)->format('M j, Y') }}
                                                </div>
                                            @endif
                                            @if($milestone->end_date)
                                                <div>
                                                    <span class="font-medium">End:</span> {{ \Carbon\Carbon::parse($milestone->end_date)->format('M j, Y') }}
                                                </div>
                                            @endif
                                            @if($milestone->hourly_rate)
                                                <div>
                                                    <span class="font-medium">Rate:</span> €{{ number_format($milestone->hourly_rate, 2) }}/h
                                                </div>
                                            @endif
                                            <div>
                                                <span class="font-medium">Tasks:</span> {{ $milestone->tasks->count() }}
                                            </div>
                                        </div>

                                        {{-- Tasks --}}
                                        @if($milestone->tasks->count() > 0)
                                            <div class="ml-11 space-y-3">
                                                @foreach($milestone->tasks as $task)
                                                    <div class="rounded p-4" style="background: rgba(var(--theme-accent-rgb), 0.05); border: 1px solid rgba(var(--theme-accent-rgb), 0.3);">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center">
                                                                    <div class="w-6 h-6 rounded flex items-center justify-center font-medium mr-2"
                                                                         style="background: rgba(var(--theme-accent-rgb), 0.2); color: var(--theme-accent); font-size: calc(var(--theme-font-size) - 2px);">
                                                                        {{ $loop->iteration }}
                                                                    </div>
                                                                    <h5 class="font-medium" style="color: var(--theme-accent);">{{ $task->name }}</h5>
                                                                </div>
                                                                @if($task->description)
                                                                    <p class="mt-1 ml-8" style=" color: var(--theme-text-secondary);">{{ $task->description }}</p>
                                                                @endif
                                                            </div>
                                                            <div class="text-right ml-4">
                                                                @if($task->estimated_hours)
                                                                    <div style=" color: var(--theme-text-muted);">{{ $task->estimated_hours }}h</div>
                                                                @endif
                                                                <div class="font-medium" style=" color: var(--theme-accent);">€{{ number_format($task->estimated_value, 2) }}</div>
                                                            </div>
                                                        </div>

                                                        {{-- Task Details --}}
                                                        @if($task->fee_type || $task->hourly_rate || $task->start_date || $task->end_date)
                                                            <div class="mt-2 ml-8 grid grid-cols-1 md:grid-cols-4 gap-4" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-secondary);">
                                                                @if($task->fee_type)
                                                                    <div>
                                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                                              style="background: {{ $task->fee_type === 'extended' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(34, 197, 94, 0.1)' }}; 
                                                                                     color: {{ $task->fee_type === 'extended' ? 'rgb(239, 68, 68)' : 'rgb(34, 197, 94)' }};">
                                                                            {{ $task->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                                @if($task->hourly_rate)
                                                                    <div>
                                                                        <span class="font-medium">Rate:</span> €{{ number_format($task->hourly_rate, 2) }}/h
                                                                    </div>
                                                                @endif
                                                                @if($task->start_date)
                                                                    <div>
                                                                        <span class="font-medium">Start:</span> {{ \Carbon\Carbon::parse($task->start_date)->format('M j, Y') }}
                                                                    </div>
                                                                @endif
                                                                @if($task->end_date)
                                                                    <div>
                                                                        <span class="font-medium">End:</span> {{ \Carbon\Carbon::parse($task->end_date)->format('M j, Y') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif

                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                        @else
                            {{-- Empty State --}}
                            <div class="text-center py-12" style="color: var(--theme-text-muted);">
                                <i class="fas fa-tasks text-4xl mb-4" style="color: rgba(var(--theme-primary-rgb), 0.3);"></i>
                                <h4 class="text-lg font-medium mb-2" style="color: var(--theme-text-primary);">No Template Structure</h4>
                                <p style="">This template doesn't have any milestones and tasks yet.</p>
                                <div class="mt-6">
                                    <a href="{{ route('project-templates.edit', $projectTemplate) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                                       style="background-color: var(--theme-primary);  border-color: var(--theme-primary);"
                                       onmouseover="this.style.backgroundColor='var(--theme-primary-dark)'"
                                       onmouseout="this.style.backgroundColor='var(--theme-primary)'">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit Template
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1">
                {{-- Template Statistics --}}
                <div class="rounded-lg shadow-sm p-6 mb-6" style="background: var(--theme-card-bg); border: 1px solid var(--theme-border);">
                    <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--theme-border-light);">
                        <h3 class="text-[17px] font-semibold" style="color: var(--theme-text-primary);">Statistics</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span style=" color: var(--theme-text-secondary);">Milestones:</span>
                            <span class="font-medium" style="color: var(--theme-text-primary);">{{ $stats['total_milestones'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style=" color: var(--theme-text-secondary);">Tasks:</span>
                            <span class="font-medium" style="color: var(--theme-text-primary);">{{ $stats['total_tasks'] }}</span>
                        </div>
                        <hr style="border-color: var(--theme-border);">
                        <div class="flex justify-between items-center">
                            <span style=" color: var(--theme-text-secondary);">Total Hours:</span>
                            <span class="font-medium" style="color: var(--theme-text-primary);">{{ $stats['total_hours'] }}h</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span style=" color: var(--theme-text-secondary);">Estimated Value:</span>
                            <span class="font-medium" style="color: var(--theme-text-primary);">€{{ number_format($stats['estimated_value'], 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Template Usage --}}
                <div class="rounded-lg shadow-sm p-6 mb-6" style="background: var(--theme-card-bg); border: 1px solid var(--theme-border);">
                    <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--theme-border-light);">
                        <h3 class="text-[17px] font-semibold" style="color: var(--theme-text-primary);">Template Usage</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold" style="color: var(--theme-primary);">0</div>
                            <div style=" color: var(--theme-text-secondary);">Projects created</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold" style="color: var(--theme-text-primary);">Last used</div>
                            <div style=" color: var(--theme-text-secondary);">Not used yet</div>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="rounded-lg shadow-sm p-6" style="background: var(--theme-card-bg); border: 1px solid var(--theme-border);">
                    <div class="mb-4 pb-3" style="border-bottom: 1px solid var(--theme-border-light);">
                        <h3 class="text-[17px] font-semibold" style="color: var(--theme-text-primary);">Quick Actions</h3>
                    </div>
                    <div class="space-y-2">
                        <button onclick="useTemplate()" 
                                class="flex items-center transition-colors duration-200 group"
                                style="color: var(--theme-text-secondary);"
                                onmouseover="this.style.color='var(--theme-text-primary)'"
                                onmouseout="this.style.color='var(--theme-text-secondary)'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 style="color: var(--theme-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span style="">Create New Project</span>
                        </button>
                        <a href="{{ route('project-templates.edit', $projectTemplate) }}" 
                           class="flex items-center transition-colors duration-200 group"
                           style="color: var(--theme-text-secondary);"
                           onmouseover="this.style.color='var(--theme-text-primary)'"
                           onmouseout="this.style.color='var(--theme-text-secondary)'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 style="color: var(--theme-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span style="">Edit Template</span>
                        </a>
                        <button onclick="duplicateTemplate()" 
                                class="flex items-center transition-colors duration-200 group"
                                style="color: var(--theme-text-secondary);"
                                onmouseover="this.style.color='var(--theme-text-primary)'"
                                onmouseout="this.style.color='var(--theme-text-secondary)'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 style="color: var(--theme-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            <span style="">Duplicate Template</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Success/Error Messages --}}
@if(session('success'))
    <div class="fixed top-4 right-4 text-white px-6 py-4 rounded-lg shadow-lg z-50"
         style="background-color: var(--theme-success); ">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    </div>
    <script>
        setTimeout(() => {
            const successMsg = document.querySelector('.fixed.top-4.right-4');
            if (successMsg) successMsg.remove();
        }, 5000);
    </script>
@endif

@if(session('error'))
    <div class="fixed top-4 right-4 text-white px-6 py-4 rounded-lg shadow-lg z-50"
         style="background-color: var(--theme-danger); ">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    </div>
    <script>
        setTimeout(() => {
            const errorMsg = document.querySelector('.fixed.top-4.right-4');
            if (errorMsg) errorMsg.remove();
        }, 5000);
    </script>
@endif

<script>
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('hidden');
    
    // Close when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!dropdown.contains(e.target) && !e.target.closest(`button[onclick="toggleDropdown('${dropdownId}')"]`)) {
            dropdown.classList.add('hidden');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

function useTemplate() {
    // Placeholder for template usage functionality
    alert('Template usage functionality komt binnenkort! Dit zal een nieuw project aanmaken gebaseerd op deze template.');
}

function duplicateTemplate() {
    if (confirm('Weet je zeker dat je deze template wilt dupliceren?')) {
        // Placeholder for duplicate functionality
        alert('Template duplicatie functionaliteit komt binnenkort!');
    }
}

function exportTemplate() {
    // Placeholder for export functionality
    alert('Template export functionaliteit komt binnenkort!');
}

function deleteTemplate() {
    if (confirm('Weet je zeker dat je deze template wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.')) {
        // Create a form and submit for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("project-templates.destroy", $projectTemplate) }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection