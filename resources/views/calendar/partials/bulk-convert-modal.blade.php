{{-- Bulk Convert Modal --}}
<div id="bulkConvertModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-4 border w-full max-w-md">
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-slate-200/60">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <h3 class="text-base font-medium text-slate-900">Bulk Convert Events</h3>
            </div>
            <form action="{{ route('calendar.bulk-convert') }}" method="POST" id="bulkConvertForm" class="p-4">
                @csrf
                <div id="bulk-event-ids"></div>
                
                <div class="space-y-3">
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                        <p class="text-sm text-slate-600">
                            <span class="font-medium">Selected Events:</span> 
                            <span id="bulk-event-count">0</span> events
                        </p>
                    </div>
                    
                    <div>
                        <label for="bulk-project-id" class="block text-sm font-medium text-slate-700 mb-1">Project</label>
                        <select id="bulk-project-id" name="project_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" required>
                            <option value="">Select Project...</option>
                            @if(isset($projects))
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            @endif
                        </select>
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
                    
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <div class="flex">
                            <svg class="w-4 h-4 text-amber-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs text-amber-700">
                                All selected events will be converted to draft time entries for the selected project.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeBulkConvertModal()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all duration-200">
                        Convert All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBulkConvertModal() {
    const checkedBoxes = document.querySelectorAll('.event-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one event to convert');
        return;
    }
    
    // Update count
    document.getElementById('bulk-event-count').textContent = checkedBoxes.length;
    
    // Add hidden inputs for event IDs
    let hiddenInputs = '';
    checkedBoxes.forEach(checkbox => {
        hiddenInputs += `<input type="hidden" name="event_ids[]" value="${checkbox.value}">`;
    });
    document.getElementById('bulk-event-ids').innerHTML = hiddenInputs;
    
    document.getElementById('bulkConvertModal').classList.remove('hidden');
}

function closeBulkConvertModal() {
    document.getElementById('bulkConvertModal').classList.add('hidden');
}
</script>