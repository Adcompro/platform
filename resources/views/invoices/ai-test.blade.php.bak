@extends('layouts.app')

@section('title', 'AI Invoice Description Test')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-medium text-slate-900">AI Invoice Description Bundling Test</h1>
                    <p class="text-sm text-slate-600 mt-0.5">Test AI-powered summarization of time entries for invoices</p>
                </div>
                <a href="{{ route('invoices.index') }}" 
                   class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    Back to Invoices
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Info Box --}}
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900">How AI Bundling Works</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        The AI analyzes time entry descriptions to:
                    </p>
                    <ul class="text-sm text-blue-700 mt-2 space-y-1 list-disc list-inside">
                        <li>Group similar activities together</li>
                        <li>Create concise, professional summaries</li>
                        <li>Highlight key deliverables and value</li>
                        <li>Generate optimal invoice line descriptions</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Project Selection --}}
        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <h2 class="text-base font-medium text-slate-900">Select Project to Test</h2>
            </div>
            
            <div class="p-4">
                @if($projects->isEmpty())
                    <div class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm">No projects with approved time entries found</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($projects as $project)
                            <div class="border border-slate-200 rounded-lg p-4 hover:border-blue-300 hover:bg-blue-50/30 transition-all cursor-pointer"
                                 onclick="selectProject({{ $project->id }})">
                                <h3 class="font-medium text-slate-900">{{ $project->name }}</h3>
                                <p class="text-sm text-slate-600 mt-1">{{ $project->customer->name }}</p>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs text-slate-500">
                                        {{ $project->companyRelation->name }}
                                    </span>
                                    <button type="button" 
                                            onclick="event.stopPropagation(); selectProject({{ $project->id }}, '{{ addslashes($project->name) }}', '{{ addslashes($project->customer->name) }}')"
                                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Test AI
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Test Configuration Form (Initially Hidden) --}}
        <div id="testConfigForm" class="mt-6 bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl hidden">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <h2 class="text-base font-medium text-slate-900">Configure AI Test</h2>
                <p class="text-sm text-slate-600 mt-0.5" id="selectedProjectName"></p>
            </div>
            
            <form id="aiTestForm" method="POST" action="">
                @csrf
                <div class="p-4 space-y-4">
                    {{-- Period Selection --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="period_start" class="block text-sm font-medium text-slate-700 mb-1">Period Start</label>
                            <input type="date" 
                                   name="period_start" 
                                   id="period_start" 
                                   value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="period_end" class="block text-sm font-medium text-slate-700 mb-1">Period End</label>
                            <input type="date" 
                                   name="period_end" 
                                   id="period_end" 
                                   value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Consolidation Level --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Consolidation Level</label>
                        <div class="space-y-2">
                            <label class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="radio" name="consolidation_level" value="hierarchical" checked class="mt-1">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-slate-900">Hierarchical Structure (Recommended)</span>
                                    <p class="text-xs text-slate-600 mt-1">Milestone → Task → Descriptions with individual hours and amounts</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="radio" name="consolidation_level" value="smart" class="mt-1">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-slate-900">Smart AI Grouping</span>
                                    <p class="text-xs text-slate-600 mt-1">AI intelligently groups similar activities and creates optimal summaries</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="radio" name="consolidation_level" value="milestone" class="mt-1">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-slate-900">Group by Milestone</span>
                                    <p class="text-xs text-slate-600 mt-1">Summarize time entries within each milestone</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="radio" name="consolidation_level" value="task" class="mt-1">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-slate-900">Group by Task</span>
                                    <p class="text-xs text-slate-600 mt-1">Summarize time entries within each task</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="radio" name="consolidation_level" value="none" class="mt-1">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-slate-900">No AI Processing</span>
                                    <p class="text-xs text-slate-600 mt-1">Show original time entries without AI summarization</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex justify-end pt-4">
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Run AI Analysis
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function selectProject(projectId, projectName, customerName) {
    // Show the configuration form
    document.getElementById('testConfigForm').classList.remove('hidden');
    
    // Update form action with the correct route
    document.getElementById('aiTestForm').action = `/invoices/ai-test/${projectId}/test`;
    
    // Update selected project name
    document.getElementById('selectedProjectName').textContent = `${projectName} - ${customerName}`;
    
    // Scroll to form
    document.getElementById('testConfigForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>
@endsection