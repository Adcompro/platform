{{-- Cancel Event Modal --}}
<div id="cancelEventModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-4 border w-full max-w-md">
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg border border-slate-200/60">
            <div class="px-4 py-3 border-b border-slate-200/50">
                <h3 class="text-base font-medium text-slate-900">Cancel Event</h3>
            </div>
            <form action="#" method="POST" id="cancelEventForm" class="p-4">
                @csrf
                @method('DELETE')
                <input type="hidden" id="cancel-event-id" name="event_id">
                
                <div class="space-y-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-red-800">
                                    Are you sure you want to cancel this event?
                                </p>
                                <p class="text-sm text-red-700 mt-1">
                                    <span class="font-medium">Event:</span> <span id="cancel-event-name"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label for="cancellation_reason" class="block text-sm font-medium text-slate-700 mb-1">
                            Cancellation Reason (Optional)
                        </label>
                        <textarea id="cancellation_reason" name="cancellation_reason" rows="3" 
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                            placeholder="Provide a reason for cancellation..."></textarea>
                    </div>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="notify_attendees" checked class="mr-2 text-slate-600 focus:ring-slate-500 rounded">
                        <span class="text-sm text-slate-700">Notify attendees about cancellation</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeCancelEventModal()" class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        Keep Event
                    </button>
                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-all duration-200">
                        Cancel Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function closeCancelEventModal() {
    document.getElementById('cancelEventModal').classList.add('hidden');
    document.getElementById('cancelEventForm').reset();
}

document.getElementById('cancelEventForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const eventId = document.getElementById('cancel-event-id').value;
    
    fetch(`/calendar/events/${eventId}/cancel`, {
        method: 'DELETE',
        body: new FormData(this),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Error cancelling event');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Try regular form submission as fallback
        this.action = `/calendar/events/${eventId}/cancel`;
        this.submit();
    });
});
</script>