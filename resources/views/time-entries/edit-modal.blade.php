{{-- Modal Edit Form for Time Entry --}}
<div>
    <form method="POST" action="{{ route('time-entries.update', $timeEntry) }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Project Selection (Read-only in edit) --}}
        <div>
            <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                Project <span style="color: var(--theme-danger);">*</span>
            </label>
            <div style="width: 100%; padding: 0.5rem 0.75rem; background-color: #f9fafb; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); color: var(--theme-text);">
                {{ $timeEntry->project->name }}
                @if($timeEntry->project->customer)
                    - {{ $timeEntry->project->customer->name }}
                @endif
            </div>
            <p style="margin-top: 0.5rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Project cannot be changed after creation</p>
        </div>

        {{-- Work Item Selection (Hierarchical Dropdown) --}}
        <div>
            <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                Work Item <span style="color: var(--theme-danger);">*</span>
            </label>
            <select name="work_item_id" id="work_item_id"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                    required>
                <option value="">Loading work items...</option>
            </select>
            <p style="margin-top: 0.5rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Select a task or subtask (time cannot be logged on milestones)</p>
        </div>

        {{-- Entry Date --}}
        <div>
            <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                Date <span style="color: var(--theme-danger);">*</span>
            </label>
            <input type="date" name="entry_date" id="entry_date"
                   value="{{ old('entry_date', ($timeEntry->entry_date ?? $timeEntry->date) ? ($timeEntry->entry_date ?? $timeEntry->date)->format('Y-m-d') : '') }}"
                   style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                   required>
        </div>

        {{-- Time Duration --}}
        <div>
            <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                Time Spent <span style="color: var(--theme-danger);">*</span>
            </label>
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <select name="minutes" id="minutes"
                            style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;"
                            required onchange="updateTimeDisplayModal()">
                        <option value="">Select time...</option>
                        @for($i = 5; $i <= 480; $i += 5)
                            @php
                                $hours = floor($i / 60);
                                $mins = $i % 60;
                                $display = $hours > 0 ? ($mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h") : "{$mins}m";
                                $currentMinutes = $timeEntry->minutes ?? round($timeEntry->hours * 60);
                            @endphp
                            <option value="{{ $i }}" {{ old('minutes', $currentMinutes) == $i ? 'selected' : '' }}>
                                {{ $display }} ({{ $i }} minutes)
                            </option>
                        @endfor
                    </select>
                </div>
                <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                    <span id="time-display-modal">{{ $timeEntry->formatted_duration }}</span>
                </div>
            </div>
            <p style="margin-top: 0.5rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Time must be in 5-minute increments (minimum 5 minutes, maximum 8 hours)</p>
        </div>

        {{-- Description --}}
        <div>
            <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                Work Description <span style="color: var(--theme-danger);">*</span>
            </label>
            <textarea name="description" id="description" rows="1"
                      style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white; resize: vertical;"
                      placeholder="Describe the work performed..."
                      required>{{ old('description', $timeEntry->description) }}</textarea>
            <p style="margin-top: 0.5rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Provide a detailed description of the work performed</p>
        </div>

        {{-- Billable Status --}}
        <div>
            <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                Billable Status <span style="color: var(--theme-danger);">*</span>
            </label>
            <div class="flex items-center space-x-6">
                <label class="flex items-center">
                    <input type="radio" name="is_billable" value="billable"
                           {{ old('is_billable', $timeEntry->is_billable) === 'billable' ? 'checked' : '' }}
                           class="h-4 w-4 border-gray-300 rounded"
                           style="color: var(--theme-primary);">
                    <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Billable</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="is_billable" value="non_billable"
                           {{ old('is_billable', $timeEntry->is_billable) === 'non_billable' ? 'checked' : '' }}
                           class="h-4 w-4 border-gray-300 rounded"
                           style="color: var(--theme-primary);">
                    <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Non-billable</span>
                </label>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-between pt-4" style="border-top: 1px solid rgba(203, 213, 225, 0.3);">
            <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                <span style="color: var(--theme-danger);">*</span> Required fields
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="closeEditModal()"
                        style="padding: 0.5rem 1rem; background-color: #6b7280; color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                    Cancel
                </button>
                <button type="submit"
                        style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: calc(var(--theme-font-size) - 1px);">
                    <i class="fas fa-save mr-1.5"></i>
                    Update Entry
                </button>
            </div>
        </div>
    </form>
</div>

<script>
/**
 * Modal-specific time display update function
 */
function updateTimeDisplayModal() {
    const minutesSelect = document.getElementById('minutes');
    const timeDisplay = document.getElementById('time-display-modal');

    if (!minutesSelect || !timeDisplay) return;

    const minutes = parseInt(minutesSelect.value);
    if (isNaN(minutes) || minutes <= 0) {
        timeDisplay.textContent = '0h 0m';
        return;
    }

    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (hours > 0) {
        if (remainingMinutes > 0) {
            timeDisplay.textContent = `${hours}h ${remainingMinutes}m`;
        } else {
            timeDisplay.textContent = `${hours}h`;
        }
    } else {
        timeDisplay.textContent = `${remainingMinutes}m`;
    }
}
</script>