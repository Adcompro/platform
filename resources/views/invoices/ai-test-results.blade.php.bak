@extends('layouts.app')

@section('title', 'AI Invoice Test Results')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-medium text-slate-900">AI Analysis Results</h1>
                    <p class="text-sm text-slate-600 mt-0.5">{{ $project->name }} - {{ $periodStart->format('M d') }} to {{ $periodEnd->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('invoices.ai-test') }}" 
                       class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        Back to Test
                    </a>
                    @if($consolidationLevel !== 'none')
                    <button onclick="applyToInvoice()" 
                            class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Apply to Invoice
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-xs text-slate-600 uppercase tracking-wider">Total Entries</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $entryCount }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-xs text-slate-600 uppercase tracking-wider">Total Hours</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalHours }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-xs text-slate-600 uppercase tracking-wider">Average Rate</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">€{{ $averageRate }}</div>
            </div>
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-4">
                <div class="text-xs text-slate-600 uppercase tracking-wider">Est. Amount</div>
                <div class="text-2xl font-bold text-green-600 mt-1">€{{ number_format($estimatedAmount, 2) }}</div>
            </div>
        </div>

        {{-- AI Results Based on Consolidation Level --}}
        @if($consolidationLevel === 'hierarchical')
            {{-- Hierarchical Structure Results --}}
            @if(isset($results['hierarchical_summary']) && $results['hierarchical_summary']['success'])
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Hierarchical Invoice Structure</h2>
                        <span class="text-xs text-slate-500">AI-optimized consolidation with amounts</span>
                    </div>
                    <div class="p-4">
                        @php
                            $hierarchicalData = $results['hierarchical_summary']['data']['milestones'] ?? [];
                        @endphp
                        
                        @foreach($hierarchicalData as $milestone)
                        <div class="mb-6 border-l-4 border-blue-500 pl-4">
                            {{-- Milestone Level --}}
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-slate-900">
                                    <svg class="inline w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2h-1.5a1 1 0 000 2H14a2 2 0 100 4h-2a2 2 0 100-4h.5a1 1 0 000-2H12a2 2 0 00-2 2v11z"></path>
                                    </svg>
                                    {{ $milestone['name'] }}
                                </h3>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm font-medium text-slate-600">
                                        {{ number_format($milestone['total_hours'], 2) }} hrs
                                    </span>
                                    <span class="text-sm font-bold text-green-600">
                                        €{{ number_format($milestone['total_amount'], 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Tasks within Milestone --}}
                            @if(isset($milestone['tasks']) && count($milestone['tasks']) > 0)
                            <div class="ml-4 space-y-3">
                                @foreach($milestone['tasks'] as $task)
                                <div class="border-l-2 border-slate-300 pl-4">
                                    {{-- Task Level --}}
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-slate-800 mb-1">
                                                <svg class="inline w-4 h-4 mr-1 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $task['name'] }}
                                            </h4>
                                        </div>
                                        <div class="flex items-center space-x-3 ml-4">
                                            <span class="text-xs text-slate-500">{{ number_format($task['total_hours'], 2) }} hrs</span>
                                            <span class="text-xs font-semibold text-green-600">€{{ number_format($task['total_amount'], 2) }}</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Show consolidated descriptions for the task with hours and amounts --}}
                                    @if(isset($task['consolidated_descriptions']) && count($task['consolidated_descriptions']) > 0)
                                    <div class="ml-4 mt-2 space-y-1">
                                        @php
                                            $descCount = count($task['consolidated_descriptions']);
                                            $hoursPerDesc = $descCount > 0 ? $task['total_hours'] / $descCount : 0;
                                            $amountPerDesc = $descCount > 0 ? $task['total_amount'] / $descCount : 0;
                                        @endphp
                                        <ul class="text-sm text-slate-600 space-y-1">
                                            @foreach($task['consolidated_descriptions'] as $desc)
                                                <li class="flex items-start justify-between">
                                                    <div class="flex items-start flex-1">
                                                        <span class="text-slate-400 mr-2">•</span>
                                                        <span>{{ $desc }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2 ml-4 text-xs">
                                                        <span class="text-slate-500">{{ number_format($hoursPerDesc, 2) }}h</span>
                                                        <span class="font-medium text-green-600">€{{ number_format($amountPerDesc, 2) }}</span>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                        
                        {{-- Grand Total --}}
                        @if(count($hierarchicalData) > 0)
                        <div class="mt-6 pt-4 border-t-2 border-slate-300">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-slate-900">Grand Total</span>
                                <div class="flex items-center space-x-4">
                                    <span class="text-base font-semibold text-slate-700">
                                        @php
                                            $totalHours = collect($hierarchicalData)->sum('total_hours');
                                            $totalAmount = collect($hierarchicalData)->sum('total_amount');
                                        @endphp
                                        {{ number_format($totalHours, 2) }} hours
                                    </span>
                                    <span class="text-lg font-bold text-green-600">
                                        €{{ number_format($totalAmount, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endif
        @elseif($consolidationLevel === 'smart')
            {{-- Smart AI Grouping Results - Now uses same structure as hierarchical --}}
            @if(isset($results['ai_summary']) && $results['ai_summary']['success'])
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Smart AI Consolidation</h2>
                        <span class="text-xs text-slate-500">Intelligently grouped activities</span>
                    </div>
                    <div class="p-4">
                        @php
                            $smartData = $results['ai_summary']['data']['milestones'] ?? [];
                        @endphp
                        
                        @foreach($smartData as $milestone)
                        <div class="mb-6 border-l-4 border-green-500 pl-4">
                            {{-- Milestone Level --}}
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-slate-900">
                                    <svg class="inline w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2h-1.5a1 1 0 000 2H14a2 2 0 100 4h-2a2 2 0 100-4h.5a1 1 0 000-2H12a2 2 0 00-2 2v11z"></path>
                                    </svg>
                                    {{ $milestone['name'] }}
                                </h3>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm font-medium text-slate-600">
                                        {{ number_format($milestone['total_hours'], 2) }} hrs
                                    </span>
                                    <span class="text-sm font-bold text-green-600">
                                        €{{ number_format($milestone['total_amount'], 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Tasks within Milestone --}}
                            @if(isset($milestone['tasks']) && count($milestone['tasks']) > 0)
                            <div class="space-y-3">
                                @foreach($milestone['tasks'] as $task)
                                <div class="bg-slate-50 rounded-lg p-3">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-slate-800 mb-1">
                                                <svg class="inline w-4 h-4 mr-1 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $task['name'] }}
                                            </h4>
                                        </div>
                                        <div class="flex items-center space-x-3 ml-4">
                                            <span class="text-xs text-slate-500">{{ number_format($task['total_hours'], 2) }} hrs</span>
                                            <span class="text-xs font-semibold text-green-600">€{{ number_format($task['total_amount'], 2) }}</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Show consolidated descriptions for the task with hours and amounts --}}
                                    @if(isset($task['consolidated_descriptions']) && count($task['consolidated_descriptions']) > 0)
                                    <div class="ml-4 mt-2 space-y-1">
                                        @php
                                            $descCount = count($task['consolidated_descriptions']);
                                            $hoursPerDesc = $descCount > 0 ? $task['total_hours'] / $descCount : 0;
                                            $amountPerDesc = $descCount > 0 ? $task['total_amount'] / $descCount : 0;
                                        @endphp
                                        <ul class="text-sm text-slate-600 space-y-1">
                                            @foreach($task['consolidated_descriptions'] as $desc)
                                                <li class="flex items-start justify-between">
                                                    <div class="flex items-start flex-1">
                                                        <span class="text-slate-400 mr-2">•</span>
                                                        <span>{{ $desc }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2 ml-4 text-xs">
                                                        <span class="text-slate-500">{{ number_format($hoursPerDesc, 2) }}h</span>
                                                        <span class="font-medium text-green-600">€{{ number_format($amountPerDesc, 2) }}</span>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                        
                        {{-- Grand Total --}}
                        @if(count($smartData) > 0)
                        <div class="mt-6 pt-4 border-t-2 border-slate-300">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-slate-900">Grand Total</span>
                                <div class="flex items-center space-x-4">
                                    <span class="text-base font-semibold text-slate-700">
                                        @php
                                            $totalHours = collect($smartData)->sum('total_hours');
                                            $totalAmount = collect($smartData)->sum('total_amount');
                                        @endphp
                                        {{ number_format($totalHours, 2) }} hours
                                    </span>
                                    <span class="text-lg font-bold text-green-600">
                                        €{{ number_format($totalAmount, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endif
            
            {{-- Consolidated Lines Suggestion --}}
            @if(isset($results['consolidated_lines']))
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Suggested Invoice Lines</h2>
                    </div>
                    <div class="p-4">
                        @php
                            $consolidatedGroups = $results['consolidated_lines']['data']['consolidated_groups'] ?? [];
                        @endphp
                        
                        @if(!empty($consolidatedGroups))
                        <div class="space-y-3">
                            @foreach($consolidatedGroups as $group)
                            <div class="border border-slate-200 rounded-lg p-3">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm text-slate-800">{{ $group['group_name'] ?? 'Work Group' }}</h4>
                                        <p class="text-sm text-slate-600 mt-1">{{ $group['combined_description'] ?? '' }}</p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <div class="text-sm font-medium text-slate-900">{{ $group['total_hours'] ?? 0 }} hours</div>
                                        @if(isset($group['suggested_pricing']))
                                        <div class="text-xs text-slate-500">{{ $group['suggested_pricing'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-sm text-slate-500 italic">
                            No invoice lines could be consolidated. Creating fallback grouping...
                        </div>
                        @endif
                    </div>
                </div>
            @endif
            
        @elseif($consolidationLevel === 'milestone' || $consolidationLevel === 'task')
            {{-- Grouped Summaries --}}
            @if(isset($results['grouped_summaries']))
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">
                            {{ $consolidationLevel === 'milestone' ? 'Milestone' : 'Task' }} Summaries
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($results['grouped_summaries'] as $groupName => $summary)
                            <div class="border border-slate-200 rounded-lg p-4">
                                <h3 class="font-medium text-slate-900 mb-3">{{ $groupName }}</h3>
                                
                                @if($summary['success'] && isset($summary['data']))
                                    @php $groupData = $summary['data']; @endphp
                                    
                                    @if(isset($groupData['main_summary']))
                                    <p class="text-sm text-slate-600 mb-3">{{ $groupData['main_summary'] }}</p>
                                    @endif
                                    
                                    @if(isset($groupData['invoice_description']))
                                    <div class="bg-slate-50 rounded p-2 mt-2">
                                        <span class="text-xs font-medium text-slate-500">Invoice Line:</span>
                                        <p class="text-sm text-slate-700">{{ $groupData['invoice_description'] }}</p>
                                    </div>
                                    @endif
                                @else
                                    <p class="text-sm text-slate-500">Failed to generate summary</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
        @elseif($consolidationLevel === 'none')
            {{-- Original Entries --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Original Time Entries</h2>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">User</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase">Description</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-slate-500 uppercase">Hours</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($results['original_entries'] as $entry)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-slate-900">{{ $entry['entry_date'] }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $entry['user']['name'] }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $entry['description'] }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-900 text-right">{{ number_format($entry['hours'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Invoice Description Suggestion --}}
        @if(isset($results['invoice_description']) && !empty($results['invoice_description']))
        <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-4">
            <h3 class="text-sm font-medium text-green-900 mb-2">Aanbevolen Factuur Omschrijving / Recommended Invoice Description</h3>
            <div class="text-sm text-green-800 bg-white/70 rounded-lg p-3">
                @php
                    // invoice_description is now a string from the controller
                    $description = $results['invoice_description'];
                    
                    // If it's still JSON, try to extract the actual description
                    if (is_string($description) && (str_starts_with($description, '{') || str_starts_with($description, '['))) {
                        $decoded = json_decode($description, true);
                        if ($decoded && isset($decoded['invoice_description'])) {
                            $description = $decoded['invoice_description'];
                        } elseif ($decoded && isset($decoded['description'])) {
                            $description = $decoded['description'];
                        }
                    }
                @endphp
                {{ $description }}
            </div>
            @if(!empty($description))
            <div class="mt-3 flex items-center justify-between">
                <span class="text-xs text-green-700">
                    @if(isset($results['ai_summary']['tokens_used']))
                        Tokens gebruikt: {{ $results['ai_summary']['tokens_used'] }}
                    @endif
                </span>
                <button onclick="copyToClipboard(@json($description))" 
                        class="px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                    Kopieer naar Klembord / Copy to Clipboard
                </button>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Hidden form for Apply to Invoice --}}
<form id="applyInvoiceForm" method="POST" action="{{ route('invoices.ai-test.apply', $project) }}" style="display: none;">
    @csrf
    <input type="hidden" name="period_start" value="{{ $periodStart->format('Y-m-d') }}">
    <input type="hidden" name="period_end" value="{{ $periodEnd->format('Y-m-d') }}">
    <input type="hidden" name="consolidation_level" value="{{ $consolidationLevel }}">
    
    {{-- Add consolidated description --}}
    @php
        $invoiceDescription = '';
        if (isset($results['invoice_description'])) {
            $invoiceDescription = is_array($results['invoice_description']) 
                ? ($results['invoice_description']['data'] ?? $results['invoice_description'][0] ?? 'Services rendered') 
                : $results['invoice_description'];
        } elseif (isset($results['ai_summary']['data']['invoice_description'])) {
            $invoiceDescription = is_array($results['ai_summary']['data']['invoice_description'])
                ? ($results['ai_summary']['data']['invoice_description'][0] ?? 'Services rendered')
                : $results['ai_summary']['data']['invoice_description'];
        } else {
            $invoiceDescription = "Services rendered for " . $project->name;
        }
    @endphp
    <input type="hidden" name="consolidated_description" value="{{ $invoiceDescription }}">
    
    {{-- Add line items based on consolidation type --}}
    @if($consolidationLevel === 'hierarchical' && isset($results['hierarchical_summary']['data']['milestones']))
        @php $lineIndex = 0; @endphp
        @foreach($results['hierarchical_summary']['data']['milestones'] as $milestone)
            {{-- Milestone level line --}}
            <input type="hidden" name="line_items[{{ $lineIndex }}][description]" value="{{ $milestone['name'] }} (Milestone Total)">
            <input type="hidden" name="line_items[{{ $lineIndex }}][quantity]" value="{{ $milestone['total_hours'] }}">
            <input type="hidden" name="line_items[{{ $lineIndex }}][unit]" value="hours">
            <input type="hidden" name="line_items[{{ $lineIndex }}][unit_price]" value="{{ round($milestone['total_amount'] / max($milestone['total_hours'], 0.01), 2) }}">
            <input type="hidden" name="line_items[{{ $lineIndex }}][is_header]" value="true">
            @php $lineIndex++; @endphp
            
            @foreach($milestone['tasks'] as $task)
                {{-- Task level line --}}
                <input type="hidden" name="line_items[{{ $lineIndex }}][description]" value="{{ $task['name'] }}">
                <input type="hidden" name="line_items[{{ $lineIndex }}][quantity]" value="{{ $task['total_hours'] }}">
                <input type="hidden" name="line_items[{{ $lineIndex }}][unit]" value="hours">
                <input type="hidden" name="line_items[{{ $lineIndex }}][unit_price]" value="{{ round($task['total_amount'] / max($task['total_hours'], 0.01), 2) }}">
                <input type="hidden" name="line_items[{{ $lineIndex }}][is_subtask]" value="true">
                <input type="hidden" name="line_items[{{ $lineIndex }}][line_prefix]" value="task">
                @php $lineIndex++; @endphp
                
                {{-- Individual descriptions --}}
                @foreach($task['descriptions'] as $desc)
                    <input type="hidden" name="line_items[{{ $lineIndex }}][description]" value="{{ $desc['description'] }}">
                    <input type="hidden" name="line_items[{{ $lineIndex }}][quantity]" value="{{ $desc['hours'] }}">
                    <input type="hidden" name="line_items[{{ $lineIndex }}][unit]" value="hours">
                    <input type="hidden" name="line_items[{{ $lineIndex }}][unit_price]" value="{{ $desc['rate'] }}">
                    <input type="hidden" name="line_items[{{ $lineIndex }}][is_detail]" value="true">
                    <input type="hidden" name="line_items[{{ $lineIndex }}][line_prefix]" value="description">
                    @php $lineIndex++; @endphp
                @endforeach
            @endforeach
        @endforeach
    @elseif(isset($results['consolidated_lines']['data']['consolidated_groups']) && !empty($results['consolidated_lines']['data']['consolidated_groups']))
        @foreach($results['consolidated_lines']['data']['consolidated_groups'] as $index => $group)
            @php
                // Create a structured description with group name and details
                $lineDescription = '';
                if (!empty($group['group_name'])) {
                    $lineDescription = $group['group_name'];
                    if (!empty($group['combined_description'])) {
                        $lineDescription .= ': ' . $group['combined_description'];
                    }
                } else {
                    $lineDescription = $group['combined_description'] ?? 'Development services';
                }
            @endphp
            <input type="hidden" name="line_items[{{ $index }}][description]" value="{{ $lineDescription }}">
            <input type="hidden" name="line_items[{{ $index }}][quantity]" value="{{ $group['total_hours'] ?? 1 }}">
            <input type="hidden" name="line_items[{{ $index }}][unit]" value="hours">
            <input type="hidden" name="line_items[{{ $index }}][unit_price]" value="{{ $averageRate }}">
        @endforeach
    @else
        {{-- Fallback: single line item with total hours --}}
        <input type="hidden" name="line_items[0][description]" value="{{ $invoiceDescription }}">
        <input type="hidden" name="line_items[0][quantity]" value="{{ $totalHours }}">
        <input type="hidden" name="line_items[0][unit]" value="hours">
        <input type="hidden" name="line_items[0][unit_price]" value="{{ $averageRate }}">
    @endif
</form>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Description copied to clipboard!');
    });
}

function applyToInvoice() {
    if (confirm('Apply these AI suggestions to create a new invoice?')) {
        // Submit the form to create the invoice
        document.getElementById('applyInvoiceForm').submit();
    }
}
</script>
@endsection