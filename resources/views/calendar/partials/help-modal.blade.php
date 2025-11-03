{{-- Help Modal --}}
<div id="helpModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-4 border w-full max-w-3xl">
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-slate-200/60 max-h-[80vh] overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-slate-200/50 flex justify-between items-center">
                <h3 class="text-base font-medium text-slate-900">Calendar Help Guide</h3>
                <button onclick="closeHelpModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="p-4 overflow-y-auto">
                <div class="space-y-6">
                    {{-- Overview Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Overview</h4>
                        <p class="text-sm text-slate-600">
                            The Calendar module integrates with Microsoft 365 to sync your Outlook events and allows you to convert them into time entries for accurate project tracking.
                        </p>
                    </div>
                    
                    {{-- Features Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Key Features</h4>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <span class="text-sm font-medium text-slate-700">Microsoft 365 Integration</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Sync events from your Outlook calendar automatically</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <span class="text-sm font-medium text-slate-700">Time Entry Conversion</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Convert calendar events to billable time entries with one click</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <span class="text-sm font-medium text-slate-700">Event Management</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Create, edit, and cancel events directly from the application</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <span class="text-sm font-medium text-slate-700">Attendee Tracking</span>
                                    <p class="text-xs text-slate-500 mt-0.5">View and manage meeting attendees with RSVP status</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <span class="text-sm font-medium text-slate-700">AI Smart Predictions</span>
                                    <span class="inline-block ml-1.5 px-1.5 py-0.5 bg-gradient-to-r from-purple-100 to-purple-200 text-purple-700 text-xs font-medium rounded-full">Premium Feature</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Automatically suggest projects, milestones, and tasks when converting events</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    
                    {{-- Quick Actions Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Quick Actions</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-slate-50 rounded-lg p-3">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 text-slate-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700">New Event</span>
                                </div>
                                <p class="text-xs text-slate-500 ml-6">Create a new calendar event with project linking</p>
                            </div>
                            
                            <div class="bg-slate-50 rounded-lg p-3">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 text-slate-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700">Sync</span>
                                </div>
                                <p class="text-xs text-slate-500 ml-6">Fetch latest events from Microsoft 365</p>
                            </div>
                            
                            <div class="bg-slate-50 rounded-lg p-3">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 text-slate-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700">Convert</span>
                                </div>
                                <p class="text-xs text-slate-500 ml-6">Convert events to time entries</p>
                            </div>
                            
                            <div class="bg-slate-50 rounded-lg p-3">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 text-slate-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700">Settings</span>
                                </div>
                                <p class="text-xs text-slate-500 ml-6">Configure Microsoft 365 connection</p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- AI Features Section --}}
                    <div class="bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 rounded-lg p-4 border border-purple-200/50">
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                            <h4 class="text-sm font-semibold text-slate-900">AI Smart Predictions</h4>
                            <span class="ml-2 px-2 py-1 bg-gradient-to-r from-purple-100 to-purple-200 text-purple-700 text-xs font-medium rounded-full">Premium Feature</span>
                        </div>
                        
                        <p class="text-xs text-slate-600 mb-3">
                            Our AI assistant analyzes your calendar events and provides intelligent suggestions when converting them to time entries, saving you time and improving accuracy.
                        </p>
                        
                        <div class="space-y-3">
                            <div class="bg-white/60 rounded-lg p-3 border border-slate-200/30">
                                <h5 class="text-xs font-semibold text-slate-800 mb-1.5">How It Works</h5>
                                <ul class="space-y-1">
                                    <li class="flex items-start text-xs text-slate-600">
                                        <span class="text-purple-400 mr-1.5">1.</span>
                                        <span>AI analyzes the event title, description, attendees, and location</span>
                                    </li>
                                    <li class="flex items-start text-xs text-slate-600">
                                        <span class="text-purple-400 mr-1.5">2.</span>
                                        <span>Compares with your historical time entry patterns</span>
                                    </li>
                                    <li class="flex items-start text-xs text-slate-600">
                                        <span class="text-purple-400 mr-1.5">3.</span>
                                        <span>Suggests the most likely project, milestone, task, and subtask</span>
                                    </li>
                                    <li class="flex items-start text-xs text-slate-600">
                                        <span class="text-purple-400 mr-1.5">4.</span>
                                        <span>Pre-fills the time entry form with intelligent defaults</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="bg-white/60 rounded-lg p-3 border border-slate-200/30">
                                <h5 class="text-xs font-semibold text-slate-800 mb-1.5">AI Features</h5>
                                <div class="grid grid-cols-1 gap-2">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-purple-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs text-slate-600">Smart project detection based on meeting context</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-purple-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs text-slate-600">Attendee-based project matching</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-purple-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs text-slate-600">Learning from your time entry history</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-purple-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs text-slate-600">Keyword similarity algorithms</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-purple-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs text-slate-600">Confidence scoring for predictions</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-amber-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h6 class="text-xs font-semibold text-amber-800 mb-1">Premium Feature Notice</h6>
                                        <p class="text-xs text-amber-700">
                                            AI Smart Predictions is an advanced feature not included in the standard package. It requires a premium subscription and uses Claude AI for intelligent analysis. 
                                            <span class="font-medium">Contact your administrator to enable this feature.</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Calendar Views Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Calendar Views</h4>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <span class="text-xs font-medium text-slate-600 w-20">Month:</span>
                                <span class="text-xs text-slate-500">Overview of all events in the month</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-xs font-medium text-slate-600 w-20">Week:</span>
                                <span class="text-xs text-slate-500">Detailed weekly schedule with time slots (default view)</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-xs font-medium text-slate-600 w-20">Day:</span>
                                <span class="text-xs text-slate-500">Focus on single day events and meetings</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Tips Section --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Tips & Best Practices</h4>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <span class="text-slate-400 mr-2">•</span>
                                <p class="text-xs text-slate-600">Sync your calendar regularly to ensure all events are up to date</p>
                            </li>
                            <li class="flex items-start">
                                <span class="text-slate-400 mr-2">•</span>
                                <p class="text-xs text-slate-600">Convert events to time entries promptly for accurate billing</p>
                            </li>
                            <li class="flex items-start">
                                <span class="text-slate-400 mr-2">•</span>
                                <p class="text-xs text-slate-600">Use project linking when creating events for better organization</p>
                            </li>
                            <li class="flex items-start">
                                <span class="text-slate-400 mr-2">•</span>
                                <p class="text-xs text-slate-600">Green events indicate they've been converted to time entries</p>
                            </li>
                            <li class="flex items-start">
                                <span class="text-purple-400 mr-2">•</span>
                                <p class="text-xs text-slate-600"><span class="font-medium text-purple-700">AI Tips:</span> Use descriptive event titles and include project names for better AI predictions</p>
                            </li>
                            <li class="flex items-start">
                                <span class="text-purple-400 mr-2">•</span>
                                <p class="text-xs text-slate-600"><span class="font-medium text-purple-700">AI Tips:</span> Include relevant team members as attendees to help AI match projects</p>
                            </li>
                            <li class="flex items-start">
                                <span class="text-purple-400 mr-2">•</span>
                                <p class="text-xs text-slate-600"><span class="font-medium text-purple-700">AI Tips:</span> The more time entries you create, the smarter AI predictions become</p>
                            </li>
                        </ul>
                    </div>
                    
                    {{-- Support Section --}}
                    <div class="border-t pt-4">
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Need More Help?</h4>
                        <p class="text-xs text-slate-600 mb-3">
                            If you encounter issues with Microsoft 365 sync or have questions about calendar features:
                        </p>
                        <div class="flex space-x-3">
                            <a href="mailto:support@adcompro.app" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Contact Support
                            </a>
                            <a href="/docs/calendar" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Documentation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function closeHelpModal() {
    document.getElementById('helpModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('helpModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelpModal();
    }
});
</script>