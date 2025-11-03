{{-- Task Details Partial --}}
<form id="taskDetailsForm" onsubmit="saveTaskDetails(event)">
    <input type="hidden" name="task_id" value="{{ $task->id }}">
    <div class="space-y-6">
        {{-- Basic Information --}}
        <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Basic Information</h4>
                <div class="text-xs" style="color: var(--theme-text-muted);">Click fields to edit</div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Task Name:</label>
                    <input type="text" 
                           name="name" 
                           value="{{ $task->name }}"
                           class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
                </div>
                <div>
                    <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Status:</label>
                    <select name="status" 
                            class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
                        <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="on_hold" {{ $task->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    </select>
                </div>
            
            <div>
                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Milestone:</span>
                <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ $task->milestone->name }}</div>
            </div>
            
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Sort Order:</label>
                <input type="number" 
                       name="sort_order" 
                       value="{{ $task->sort_order }}"
                       class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
            </div>
            
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Start Date:</label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $task->start_date ? $task->start_date->format('Y-m-d') : '' }}"
                       class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
            </div>
            
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">End Date:</label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $task->end_date ? $task->end_date->format('Y-m-d') : '' }}"
                       class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
            </div>
        </div>
        
        <div class="mt-4 col-span-2">
            <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Description:</label>
            <textarea name="description" 
                      rows="3"
                      class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                      style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);"
                      placeholder="Task description...">{{ $task->description }}</textarea>
        </div>
        </div>
    </div>

    {{-- Financial Information --}}
    <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
        <h4 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Financial Details</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Fee Type:</label>
                <select name="fee_type" 
                        class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
                    <option value="in_fee" {{ $task->fee_type === 'in_fee' ? 'selected' : '' }}>In Fee</option>
                    <option value="extended" {{ $task->fee_type === 'extended' ? 'selected' : '' }}>Extended</option>
                </select>
            </div>
            
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Pricing Type:</label>
                <select name="pricing_type" 
                        class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
                    <option value="hourly_rate" {{ $task->pricing_type === 'hourly_rate' ? 'selected' : '' }}>Hourly Rate</option>
                    <option value="fixed_price" {{ $task->pricing_type === 'fixed_price' ? 'selected' : '' }}>Fixed Price</option>
                </select>
            </div>
            
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Fixed Price (€):</label>
                <input type="number" 
                       name="fixed_price" 
                       value="{{ $task->fixed_price }}"
                       step="0.01"
                       min="0"
                       class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
            </div>
            
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Hourly Rate Override (€):</label>
                <input type="number" 
                       name="hourly_rate_override" 
                       value="{{ $task->hourly_rate_override }}"
                       step="0.01"
                       min="0"
                       class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
            </div>
            
            <div>
                <label style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Estimated Hours:</label>
                <input type="number" 
                       name="estimated_hours" 
                       value="{{ $task->estimated_hours }}"
                       step="0.25"
                       min="0"
                       class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       style="border-color: rgba(var(--theme-border-rgb), 0.3); font-size: var(--theme-font-size); color: var(--theme-text);">
            </div>
            
            <div>
                <span style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">Source:</span>
                <div class="font-medium mt-1" style="color: var(--theme-text); font-size: var(--theme-font-size);">{{ ucwords($task->source_type ?? 'Manual') }}</div>
            </div>
        </div>
    </div>


    {{-- Time Tracking Summary --}}
    @php
        // Debug information
        $selectedMonth = $selectedMonth ?? now()->format('Y-m');
        $selectedMonthStart = \Carbon\Carbon::parse($selectedMonth . '-01');
        $selectedMonthEnd = $selectedMonthStart->copy()->endOfMonth();
        
        // Get all time entries for debugging
        $allTimeEntries = $task->timeEntries ? $task->timeEntries : collect();
        $allApprovedEntries = $allTimeEntries->where('status', 'approved');
        
        // Filter time entries for the selected month
        $approvedEntries = $allApprovedEntries->filter(function($entry) use ($selectedMonth) {
            $entryDate = \Carbon\Carbon::parse($entry->date);
            $entryYearMonth = $entryDate->format('Y-m');
            return $entryYearMonth === $selectedMonth;
        });
            
        $totalHours = $approvedEntries->sum('hours');
        $entryCount = $approvedEntries->count();
        
        // Get all approved entries for the selected month (for detailed list) - sorted by date descending
        $monthlyEntries = $approvedEntries->sortByDesc('date')->values();
    @endphp
    
    
    @if($entryCount > 0)
        <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
            <h4 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">
                Time Tracking Summary - {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-3 rounded-md" style="background-color: rgba(var(--theme-bg-light-rgb), 0.3);">
                    <div class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">{{ number_format($totalHours, 1) }}h</div>
                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Total Hours</div>
                </div>
                <div class="text-center p-3 rounded-md" style="background-color: rgba(var(--theme-bg-light-rgb), 0.3);">
                    @php
                        $totalCost = $approvedEntries->sum(function($entry) {
                            return $entry->hours * ($entry->hourly_rate_used ?? 0);
                        });
                    @endphp
                    <div class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">&euro;{{ number_format($totalCost, 2) }}</div>
                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Total Cost</div>
                </div>
                <div class="text-center p-3 rounded-md" style="background-color: rgba(var(--theme-bg-light-rgb), 0.3);">
                    <div class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">{{ $entryCount }}</div>
                    <div style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">Time Entries</div>
                </div>
            </div>
            
            {{-- Detailed Time Entries List --}}
            <div class="mt-6">
                <h5 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 1px);">Time Entries Detail</h5>
                <div class="space-y-1 max-h-64 overflow-y-auto">
                    @foreach($monthlyEntries as $entry)
                        <div class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50/50 transition-colors" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.1);">
                            <div class="flex items-center space-x-4 flex-1">
                                {{-- User Avatar & Name --}}
                                <div class="flex items-center space-x-2 min-w-[120px]">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium" style="background-color: var(--theme-primary); color: white;">
                                        {{ strtoupper(substr($entry->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="font-medium truncate" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 1px);">
                                        {{ $entry->user->name ?? 'Unknown User' }}
                                    </div>
                                </div>
                                
                                {{-- Date --}}
                                <div class="min-w-[80px] font-medium" style="color: var(--theme-text); font-size: var(--theme-font-size);">
                                    {{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}
                                </div>
                                
                                {{-- Hours --}}
                                <div class="min-w-[50px] font-semibold" style="color: var(--theme-primary); font-size: var(--theme-font-size);">
                                    {{ number_format($entry->hours, 1) }}h
                                </div>
                                
                                {{-- Description --}}
                                <div class="flex-1 truncate" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                                    {{ $entry->description ?? 'No description' }}
                                </div>
                            </div>
                            
                            {{-- Cost --}}
                            <div class="text-right min-w-[80px]">
                                @if($entry->hourly_rate_used)
                                    <div class="font-semibold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) - 1px);">
                                        €{{ number_format($entry->hours * $entry->hourly_rate_used, 2) }}
                                    </div>
                                    <div class="text-xs" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 2px);">
                                        @€{{ number_format($entry->hourly_rate_used, 0) }}/hr
                                    </div>
                                @else
                                    <div class="text-xs" style="color: var(--theme-text-muted);">
                                        No rate
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        {{-- No entries for selected month --}}
        <div class="bg-white border rounded-lg p-4" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
            <h4 class="font-semibold mb-3" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">
                Time Tracking Summary - {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}
            </h4>
            <div class="text-center py-6">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p style="color: var(--theme-text-muted); font-size: var(--theme-font-size);">
                    No time entries found for {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}
                </p>
                @if($allApprovedEntries->count() > 0)
                    <p class="mt-2" style="color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) - 1px);">
                        This task has {{ $allApprovedEntries->count() }} approved time {{ $allApprovedEntries->count() === 1 ? 'entry' : 'entries' }} in other months.
                    </p>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Save Button --}}
    <div class="border-t pt-4 mt-6 flex justify-between items-center" style="border-color: rgba(var(--theme-border-rgb), 0.3);">
        <div class="text-xs" style="color: var(--theme-text-muted);">
            Changes are saved automatically when you modify fields
        </div>
        <div class="flex space-x-2">
            <button type="button" 
                    onclick="resetTaskForm()"
                    class="px-4 py-2 text-sm font-medium border rounded-md hover:bg-gray-50 transition-colors"
                    style="border-color: rgba(var(--theme-border-rgb), 0.5); color: var(--theme-text-muted);">
                Reset
            </button>
            <button type="submit" 
                    class="px-4 py-2 text-sm font-medium rounded-md text-white hover:opacity-90 transition-opacity"
                    style="background-color: var(--theme-primary);">
                Save Changes
            </button>
        </div>
    </div>
</div>
</form>