{{-- Create Event Modal - Compact Version --}}
<div id="createEventModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50" style="background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
    <div class="relative top-10 mx-auto p-2 w-full max-w-xl">
        <div class="bg-white/95 backdrop-blur-sm" style="border-radius: var(--theme-border-radius); box-shadow: var(--theme-card-shadow); border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.6);">
            <div class="px-6 py-4" style="border-bottom: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                <h3 class="text-lg font-medium" style="color: var(--theme-text);">New Calendar Event</h3>
            </div>
            <form action="{{ route('calendar.store') }}" method="POST" id="createEventForm" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label for="subject" class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Event Title</label>
                        <input type="text" id="subject" name="subject" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" required>
                    </div>
                    
                    <div>
                        <label for="start_datetime" class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Start Date & Time</label>
                        <input type="datetime-local" id="start_datetime" name="start_datetime" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" required onchange="calculateEndTime()">
                    </div>
                    
                    <div>
                        <label for="duration" class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Duration (minutes)</label>
                        <select id="duration" name="duration" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" onchange="calculateEndTime()" required>
                            <option value="15">15 minutes</option>
                            <option value="30">30 minutes</option>
                            <option value="45">45 minutes</option>
                            <option value="50">50 minutes</option>
                            <option value="60" selected>1 hour</option>
                            <option value="75">1 hour 15 min</option>
                            <option value="90">1 hour 30 min</option>
                            <option value="120">2 hours</option>
                            <option value="150">2 hours 30 min</option>
                            <option value="180">3 hours</option>
                            <option value="240">4 hours</option>
                            <option value="300">5 hours</option>
                            <option value="360">6 hours</option>
                            <option value="480">8 hours</option>
                            <option value="custom">Custom...</option>
                        </select>
                        <input type="number" id="custom_duration" name="custom_duration" class="hidden mt-2 w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" placeholder="Enter minutes" min="5" max="1440" onchange="calculateEndTime()">
                        {{-- Hidden field voor end_datetime --}}
                        <input type="hidden" id="end_datetime" name="end_datetime" required>
                    </div>
                    
                    <div class="col-span-2">
                        <label for="location" class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Location</label>
                        <input type="text" id="location" name="location" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" placeholder="Room / Link">
                    </div>
                    
                    <div class="col-span-2">
                        <label for="body" class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Description</label>
                        <textarea id="body" name="body" rows="3" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" placeholder="Event details..."></textarea>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_all_day" id="is_all_day" class="mr-2 rounded" style="color: var(--theme-primary);" onchange="toggleAllDay()">
                            <span style=" color: var(--theme-text);">All-day event</span>
                        </label>
                    </div>
                    
                    <div class="col-span-2 pt-4 mt-4" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                        <h4 class="text-sm font-medium mb-3" style="color: var(--theme-text);">Link to Project (Optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="create-project-id" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Project</label>
                                <select id="create-project-id" name="project_id" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" onchange="loadProjectMilestones(this.value, 'create')">
                                    <option value="">Select...</option>
                                    @if(isset($projects))
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <div>
                                <label for="create-milestone-id" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Milestone</label>
                                <select id="create-milestone-id" name="project_milestone_id" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" onchange="loadMilestoneTasks(this.value, 'create')">
                                    <option value="">Select...</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="create-task-id" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Task</label>
                                <select id="create-task-id" name="project_task_id" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" onchange="loadTaskSubtasks(this.value, 'create')">
                                    <option value="">Select...</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="create-subtask-id" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Subtask</label>
                                <select id="create-subtask-id" name="project_subtask_id" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'">
                                    <option value="">Select...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-3 space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_create_time_entry" class="mr-2 rounded" style="color: var(--theme-primary);">
                                <span style=" color: var(--theme-text);">Auto create time entry</span>
                            </label>
                            
                            <div class="flex space-x-4 ml-6">
                                <label class="flex items-center">
                                    <input type="radio" name="is_billable" value="billable" checked class="mr-1.5" style="color: var(--theme-primary);">
                                    <span class="text-xs" style="color: var(--theme-text-muted);">Billable</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="is_billable" value="non_billable" class="mr-1.5" style="color: var(--theme-primary);">
                                    <span class="text-xs" style="color: var(--theme-text-muted);">Non-billable</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-span-2 pt-4 mt-4" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.3);">
                        <h4 class="text-sm font-medium mb-3" style="color: var(--theme-text);">Attendees (Optional)</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="attendee_ids" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">Internal</label>
                                <select id="attendee_ids" name="attendee_ids[]" multiple class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" size="2">
                                    @if(isset($users))
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="text-xs mt-1" style="color: var(--theme-text-muted); opacity: 0.7;">Ctrl/Cmd for multiple</p>
                            </div>
                            
                            <div>
                                <label for="external_attendees" class="block text-xs font-medium mb-1" style="color: var(--theme-text-muted);">External</label>
                                <input type="text" id="external_attendees" name="external_attendees" class="w-full px-3 py-2 rounded-md" style="border: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);  color: var(--theme-text); background: white;" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.5)'; this.style.boxShadow='none'" placeholder="email@example.com">
                                <p class="text-xs mt-1" style="color: var(--theme-text-muted); opacity: 0.7;">Comma-separated</p>
                            </div>
                        </div>
                        
                        <label class="flex items-center mt-3">
                            <input type="checkbox" name="send_invitations" class="mr-2 rounded" style="color: var(--theme-primary);">
                            <span style=" color: var(--theme-text);">Send email invitations</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6 pt-6" style="border-top: 1px solid rgba(var(--theme-border-rgb, 226, 232, 240), 0.5);">
                    <button type="button" onclick="closeCreateEventModal()" class="px-4 py-2 text-sm font-medium transition-all duration-200" style="background-color: rgba(var(--theme-border-rgb, 226, 232, 240), 0.2); color: var(--theme-text); border-radius: var(--theme-border-radius);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.3)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb, 226, 232, 240), 0.2)'">
                        Cancel
                    </button>
                    <button type="submit" class="theme-btn-primary px-4 py-2 text-white text-sm font-medium transition-all duration-200" style="border-radius: var(--theme-border-radius);" onmouseover="this.style.filter='brightness(0.9)'" onmouseout="this.style.filter='brightness(1)'">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function closeCreateEventModal() {
    document.getElementById('createEventModal').classList.add('hidden');
    document.getElementById('createEventForm').reset();
    // Reset custom duration visibility
    document.getElementById('custom_duration').classList.add('hidden');
}

function calculateEndTime() {
    const startInput = document.getElementById('start_datetime');
    const durationSelect = document.getElementById('duration');
    const customDurationInput = document.getElementById('custom_duration');
    const endInput = document.getElementById('end_datetime');
    const isAllDay = document.getElementById('is_all_day').checked;
    
    if (!startInput.value) return;
    
    if (isAllDay) {
        // Voor all-day events, zet eindtijd op einde van de dag
        const startDate = new Date(startInput.value);
        startDate.setHours(23, 59, 0, 0);
        endInput.value = formatDateTimeLocal(startDate);
        return;
    }
    
    let durationMinutes;
    if (durationSelect.value === 'custom') {
        durationMinutes = parseInt(customDurationInput.value) || 60;
    } else {
        durationMinutes = parseInt(durationSelect.value);
    }
    
    const startDate = new Date(startInput.value);
    const endDate = new Date(startDate.getTime() + durationMinutes * 60000);
    
    endInput.value = formatDateTimeLocal(endDate);
}

function formatDateTimeLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

function toggleAllDay() {
    const isAllDay = document.getElementById('is_all_day').checked;
    const durationSelect = document.getElementById('duration');
    const durationLabel = durationSelect.previousElementSibling;
    
    if (isAllDay) {
        durationSelect.disabled = true;
        durationSelect.style.opacity = '0.5';
        durationLabel.style.opacity = '0.5';
        calculateEndTime(); // Recalculate voor all-day
    } else {
        durationSelect.disabled = false;
        durationSelect.style.opacity = '1';
        durationLabel.style.opacity = '1';
        calculateEndTime();
    }
}

// Handle duration dropdown change
document.getElementById('duration')?.addEventListener('change', function() {
    const customDurationInput = document.getElementById('custom_duration');
    if (this.value === 'custom') {
        customDurationInput.classList.remove('hidden');
        customDurationInput.focus();
    } else {
        customDurationInput.classList.add('hidden');
        calculateEndTime();
    }
});

// Set default start time to next hour when modal opens
function openCreateEventModal() {
    document.getElementById('createEventModal').classList.remove('hidden');
    
    // Set default start time if empty
    const startInput = document.getElementById('start_datetime');
    if (!startInput.value) {
        const now = new Date();
        now.setHours(now.getHours() + 1, 0, 0, 0); // Next hour, 0 minutes
        startInput.value = formatDateTimeLocal(now);
        calculateEndTime();
    }
}

document.getElementById('createEventForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateEventModal();
            if (window.refreshCalendar) {
                window.refreshCalendar();
            }
            window.location.reload();
        } else {
            alert(data.error || 'Error creating event');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback to regular form submission
        this.submit();
    });
});
</script>