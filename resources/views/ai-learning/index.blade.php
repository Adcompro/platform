@extends('layouts.app')

@section('title', 'AI Learning Review')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.6);">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold" style="color: var(--theme-text);">AI Learning Review</h1>
                    <p class="text-sm mt-1" style="color: var(--theme-text-muted);">Review and improve AI time entry suggestions</p>
                </div>
                @if(isset($stats))
                <div class="flex space-x-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold" style="color: var(--theme-primary);">{{ $stats['accuracy'] }}%</div>
                        <div class="text-xs" style="color: var(--theme-text-muted);">Accuracy</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" style="color: var(--theme-text);">{{ $stats['total'] }}</div>
                        <div class="text-xs" style="color: var(--theme-text-muted);">Total AI Entries</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" style="color: #ea580c;">{{ $stats['unreviewed'] }}</div>
                        <div class="text-xs" style="color: var(--theme-text-muted);">Unreviewed</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="theme-card bg-white/60 backdrop-blur-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.6); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
            <form method="GET" action="{{ route('ai-learning.index') }}" class="flex flex-wrap gap-4">
                {{-- Project Filter --}}
                <div class="flex-1 min-w-[200px]">
                    <select name="project_id" onchange="this.form.submit()" 
                            class="w-full text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);  padding: 0.5rem;">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Feedback Filter --}}
                <div class="flex-1 min-w-[150px]">
                    <select name="feedback_filter" onchange="this.form.submit()" 
                            class="w-full text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);  padding: 0.5rem;">
                        <option value="">All Feedback</option>
                        <option value="unreviewed" {{ request('feedback_filter') == 'unreviewed' ? 'selected' : '' }}>
                            🔍 Unreviewed
                        </option>
                        <option value="good" {{ request('feedback_filter') == 'good' ? 'selected' : '' }}>
                            ✅ Good
                        </option>
                        <option value="bad" {{ request('feedback_filter') == 'bad' ? 'selected' : '' }}>
                            ❌ Bad
                        </option>
                        <option value="adjusted" {{ request('feedback_filter') == 'adjusted' ? 'selected' : '' }}>
                            ✏️ Adjusted
                        </option>
                    </select>
                </div>

                {{-- Date Range --}}
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);  padding: 0.5rem;">
                </div>
                <div>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);  padding: 0.5rem;">
                </div>

                <button type="submit" class="theme-btn-primary px-4 py-2 text-white text-sm" style="border-radius: var(--theme-border-radius);">
                    Apply Filters
                </button>
                
                <a href="{{ route('ai-learning.index') }}" class="px-4 py-2 text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);" onmouseover="this.style.backgroundColor='rgba(var(--theme-text-rgb, 30, 41, 59), 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                    Clear
                </a>
            </form>
        </div>
    </div>

    {{-- Statistics Panel --}}
    @if(isset($stats) && $stats['total'] > 0)
    <div class="max-w-7xl mx-auto px-4 pb-4">
        <div class="theme-card bg-white/60 backdrop-blur-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.6); border-radius: var(--theme-border-radius); padding: var(--theme-card-padding);">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 text-center">
                <div>
                    <div class="text-lg font-semibold" style="color: var(--theme-text);">{{ $stats['total'] }}</div>
                    <div class="text-xs" style="color: var(--theme-text-muted);">Total Entries</div>
                </div>
                <div>
                    <div class="text-lg font-semibold" style="color: var(--theme-accent);">{{ $stats['good'] }}</div>
                    <div class="text-xs" style="color: var(--theme-text-muted);">Good (✅)</div>
                </div>
                <div>
                    <div class="text-lg font-semibold" style="color: var(--theme-danger);">{{ $stats['bad'] }}</div>
                    <div class="text-xs" style="color: var(--theme-text-muted);">Bad (❌)</div>
                </div>
                <div>
                    <div class="text-lg font-semibold" style="color: #eab308;">{{ $stats['adjusted'] }}</div>
                    <div class="text-xs" style="color: var(--theme-text-muted);">Adjusted (✏️)</div>
                </div>
                <div>
                    <div class="text-lg font-semibold" style="color: #ea580c;">{{ $stats['unreviewed'] }}</div>
                    <div class="text-xs" style="color: var(--theme-text-muted);">Unreviewed (🔍)</div>
                </div>
                <div>
                    <div class="text-lg font-semibold" style="color: var(--theme-primary);">{{ $stats['accuracy'] }}%</div>
                    <div class="text-xs" style="color: var(--theme-text-muted);">Accuracy</div>
                </div>
                <div>
                    <div class="text-lg font-semibold" style="color: #9333ea;">{{ $stats['avg_confidence'] }}%</div>
                    <div class="text-xs" style="color: var(--theme-text-muted);">Avg Confidence</div>
                </div>
            </div>
            
            {{-- Apply Learning Button --}}
            @if(request('project_id') && $stats['reviewed'] > 0)
            <div class="mt-4 pt-4" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                <form action="{{ route('ai-learning.apply', request('project_id')) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-white text-sm" style="background-color: var(--theme-accent); border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                        🤖 Apply Learning to Project AI
                    </button>
                </form>
                <span class="ml-2 text-xs" style="color: var(--theme-text-muted);">
                    This will update the project's AI patterns based on reviewed feedback
                </span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Time Entries Table --}}
    <div class="max-w-7xl mx-auto px-4 pb-6">
        <div class="bg-white/60 backdrop-blur-sm overflow-hidden" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.6); border-radius: var(--theme-border-radius);">
            <table class="w-full">
                <thead style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                    <tr>
                        <th class="text-left text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">Date</th>
                        <th class="text-left text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">Project</th>
                        <th class="text-left text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">Original Description</th>
                        <th class="text-left text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">AI Improved</th>
                        <th class="text-left text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">Final Description</th>
                        <th class="text-center text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">Confidence</th>
                        <th class="text-center text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">Feedback</th>
                        <th class="text-center text-xs font-medium uppercase" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timeEntries as $entry)
                    <tr class="hover:bg-slate-50/50 transition-colors" data-entry-id="{{ $entry->id }}" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.2);">
                        <td class="text-sm" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text); ">
                            {{ $entry->entry_date->format('d M Y') }}
                        </td>
                        <td class="text-sm" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text); ">
                            {{ $entry->project->name ?? 'N/A' }}
                        </td>
                        <td class="text-sm" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); color: var(--theme-text); ">
                            <div class="max-w-xs overflow-hidden text-ellipsis" title="{{ $entry->original_description ?? $entry->description }}">
                                {{ Str::limit($entry->original_description ?? $entry->description, 40) }}
                            </div>
                        </td>
                        <td class="text-sm" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                            <div class="font-medium" style="color: var(--theme-primary); ">
                                {{ Str::limit($entry->ai_improved_description, 40) }}
                            </div>
                        </td>
                        <td class="text-sm" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                            <div style="color: var(--theme-text); ">
                                {{ Str::limit($entry->description, 40) }}
                            </div>
                        </td>
                        <td class="text-center text-sm" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); ">
                            @if($entry->ai_confidence)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $entry->ai_confidence >= 0.8 ? 'bg-green-100 text-green-800' : 
                                   ($entry->ai_confidence >= 0.5 ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800') }}">
                                {{ round($entry->ai_confidence * 100) }}%
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="text-center text-sm" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x); ">
                            <div class="feedback-status">
                                @if($entry->ai_feedback === 'good')
                                    <span class="text-green-600 text-lg">✅</span>
                                @elseif($entry->ai_feedback === 'bad')
                                    <span class="text-red-600 text-lg">❌</span>
                                @elseif($entry->ai_feedback === 'adjusted')
                                    <span class="text-yellow-600 text-lg">✏️</span>
                                @else
                                    <span class="text-gray-400 text-lg">🔍</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center" style="padding: var(--theme-table-padding-y) var(--theme-table-padding-x);">
                            @if(!$entry->ai_feedback)
                            <div class="flex justify-center space-x-1">
                                <button onclick="provideFeedback({{ $entry->id }}, 'good')" 
                                        class="p-1 hover:bg-green-100 rounded" title="Good suggestion">
                                    ✅
                                </button>
                                <button onclick="provideFeedback({{ $entry->id }}, 'bad')" 
                                        class="p-1 hover:bg-red-100 rounded" title="Bad suggestion">
                                    ❌
                                </button>
                                <button onclick="adjustSuggestion({{ $entry->id }}, '{{ addslashes($entry->ai_improved_description) }}')" 
                                        class="p-1 hover:bg-yellow-100 rounded" title="Adjust suggestion">
                                    ✏️
                                </button>
                            </div>
                            @else
                            <button onclick="resetFeedback({{ $entry->id }})" 
                                    class="text-xs" style="color: var(--theme-text-muted);" onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                                Reset
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 2rem var(--theme-table-padding-x); color: var(--theme-text-muted); ">
                            No AI-generated time entries found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            {{-- Pagination --}}
            @if($timeEntries->hasPages())
            <div class="px-4 py-3" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                {{ $timeEntries->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Adjustment Modal --}}
<div id="adjustmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="relative top-20 mx-auto p-5 w-96 bg-white" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); box-shadow: var(--theme-card-shadow); border-radius: var(--theme-border-radius);">
        <h3 class="text-lg font-medium mb-4" style="color: var(--theme-text);">Adjust AI Description</h3>
        <form id="adjustmentForm">
            <input type="hidden" id="adjustEntryId">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">Original Description</label>
                <div id="originalDescription" class="px-3 py-2 text-sm" style="background-color: rgba(var(--theme-text-rgb, 30, 41, 59), 0.05); border-radius: var(--theme-border-radius); color: var(--theme-text); "></div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">AI Improved Version</label>
                <div id="originalSuggestion" class="px-3 py-2 text-sm" style="background-color: rgba(var(--theme-primary-rgb, 37, 99, 235), 0.05); border-radius: var(--theme-border-radius); color: var(--theme-primary); "></div>
            </div>
            <div class="mb-4">
                <label for="correctedDescription" class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                    Better Description <span style="color: var(--theme-danger);">*</span>
                </label>
                <textarea id="correctedDescription" required rows="3"
                       class="w-full text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);  padding: 0.5rem;"
                       placeholder="Enter the correct/better description for consistency"></textarea>
            </div>
            <div class="mb-4">
                <label for="learningNotes" class="block text-sm font-medium mb-1" style="color: var(--theme-text-muted);">
                    Learning Notes (optional)
                </label>
                <textarea id="learningNotes" rows="3" 
                          class="w-full text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);  padding: 0.5rem;"
                          placeholder="Why was the AI wrong? What pattern should it learn?"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAdjustmentModal()" 
                        class="px-4 py-2 text-sm" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.8); border-radius: var(--theme-border-radius); color: var(--theme-text);" onmouseover="this.style.backgroundColor='rgba(var(--theme-text-rgb, 30, 41, 59), 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                    Cancel
                </button>
                <button type="submit" 
                        class="theme-btn-primary px-4 py-2 text-white text-sm" style="border-radius: var(--theme-border-radius);">
                    Save Adjustment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function provideFeedback(entryId, feedback) {
    fetch(`/ai-learning/feedback/${entryId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ feedback: feedback })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to save feedback');
        }
    });
}

function adjustSuggestion(entryId, aiImprovedDesc) {
    // Get the original description from the table row
    const row = document.querySelector(`tr[data-entry-id="${entryId}"]`);
    const originalDesc = row ? row.querySelector('td:nth-child(3) div').getAttribute('title') : '';
    
    document.getElementById('adjustEntryId').value = entryId;
    document.getElementById('originalDescription').textContent = originalDesc;
    document.getElementById('originalSuggestion').textContent = aiImprovedDesc;
    document.getElementById('correctedDescription').value = aiImprovedDesc; // Pre-fill with AI version
    document.getElementById('learningNotes').value = '';
    document.getElementById('adjustmentModal').classList.remove('hidden');
}

function closeAdjustmentModal() {
    document.getElementById('adjustmentModal').classList.add('hidden');
}

function resetFeedback(entryId) {
    if (confirm('Reset feedback for this entry?')) {
        provideFeedback(entryId, null);
    }
}

document.getElementById('adjustmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const entryId = document.getElementById('adjustEntryId').value;
    const correctedDescription = document.getElementById('correctedDescription').value;
    const learningNotes = document.getElementById('learningNotes').value;
    
    fetch(`/ai-learning/feedback/${entryId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            feedback: 'adjusted',
            corrected_subtask: correctedDescription, // This field name stays for backward compatibility in controller
            learning_notes: learningNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAdjustmentModal();
            location.reload();
        } else {
            alert('Failed to save adjustment');
        }
    });
});

// Close modal when clicking outside
document.getElementById('adjustmentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAdjustmentModal();
    }
});
</script>
@endsection