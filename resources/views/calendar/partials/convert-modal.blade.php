{{-- Convert to Time Entry Modal --}}
<div id="convertModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-4 border w-full max-w-md">
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-slate-200/60">
            <div class="px-4 py-3 border-b border-slate-200/50 flex justify-between items-center">
                <h3 class="text-base font-medium text-slate-900">Convert to Time Entry</h3>
                <div id="ai-prediction-status" class="hidden">
                    <span class="flex items-center text-xs">
                        <svg class="animate-spin h-3 w-3 mr-1 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-purple-600">AI analyzing...</span>
                    </span>
                </div>
                <div id="ai-prediction-badge" class="hidden">
                    <span class="px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-700 rounded-full flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        AI Suggested
                    </span>
                </div>
            </div>
            <form action="#" method="POST" id="convertForm" class="p-4">
                @csrf
                <input type="hidden" id="convert-event-id" name="event_id">
                
                <div class="space-y-3">
                    <div>
                        <label for="convert-project-id" class="block text-sm font-medium text-slate-700 mb-1">Project</label>
                        <select id="convert-project-id" name="project_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" required onchange="loadProjectMilestones(this.value, 'convert')">
                            <option value="">Select Project...</option>
                            @if(isset($projects))
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div>
                        <label for="convert-milestone-id" class="block text-sm font-medium text-slate-700 mb-1">Milestone (Optional)</label>
                        <select id="convert-milestone-id" name="project_milestone_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" onchange="loadMilestoneTasks(this.value, 'convert')">
                            <option value="">Select Milestone...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="convert-task-id" class="block text-sm font-medium text-slate-700 mb-1">Task (Optional)</label>
                        <select id="convert-task-id" name="project_task_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" onchange="loadTaskSubtasks(this.value, 'convert')">
                            <option value="">Select Task...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="convert-subtask-id" class="block text-sm font-medium text-slate-700 mb-1">Subtask (Optional)</label>
                        <select id="convert-subtask-id" name="project_subtask_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500">
                            <option value="">Select Subtask...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="convert-description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea id="convert-description" name="description" rows="3" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Additional notes..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Billable Status</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="is_billable" value="billable" checked class="mr-2 text-slate-600 focus:ring-slate-500">
                                <span class="text-sm text-slate-700">Billable</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="is_billable" value="non_billable" class="mr-2 text-slate-600 focus:ring-slate-500">
                                <span class="text-sm text-slate-700">Non-billable</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal('convertModal')" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200">
                        Convert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('convertForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const eventId = document.getElementById('convert-event-id').value;
    const formData = new FormData(this);
    
    fetch(`/calendar/events/${eventId}/convert`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Als er een redirect URL is, ga daarheen
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        } else {
            alert(data.error || data.message || 'Error converting event');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error converting event');
    });
});

// Functions are defined in the main calendar view
</script>