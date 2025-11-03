{{-- Help Modal --}}
<div id="helpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between mb-4 pb-4 border-b">
                <h3 class="text-lg font-medium" style="color: var(--theme-text);">Contact Management Help Guide</h3>
                <button onclick="closeHelpModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="mt-2 space-y-6" style="">
                {{-- Overview Section --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Overview</h4>
                    <p style="color: var(--theme-text-muted);">
                        The Contact Management system allows you to manage all contact persons associated with your customers. 
                        You can track multiple contacts per customer, designate primary contacts, and maintain detailed information 
                        about each person including their position, email, phone, and company relations.
                    </p>
                </div>

                {{-- Key Features --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Key Features</h4>
                    <ul class="list-disc list-inside space-y-1" style="color: var(--theme-text-muted);">
                        <li>Create and manage contact persons for customers</li>
                        <li>Designate primary contacts for each customer</li>
                        <li>Track multiple company relations per contact</li>
                        <li>Store contact details (email, phone, position)</li>
                        <li>Add notes and comments for each contact</li>
                        <li>View complete activity history and audit trail</li>
                        <li>Quick search and filtering options</li>
                        <li>Export contact lists (coming soon)</li>
                    </ul>
                </div>

                {{-- Contact Actions --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Available Actions</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h5 class="font-medium mb-1" style="color: var(--theme-text);">Create Contact</h5>
                            <p class="text-sm" style="color: var(--theme-text-muted);">
                                Click the "New Contact" button to add a new contact. You can create contacts from:
                            </p>
                            <ul class="list-disc list-inside text-sm mt-1" style="color: var(--theme-text-muted);">
                                <li>The main contacts list</li>
                                <li>Within a customer's detail page</li>
                            </ul>
                        </div>
                        <div>
                            <h5 class="font-medium mb-1" style="color: var(--theme-text);">Edit Contact</h5>
                            <p class="text-sm" style="color: var(--theme-text-muted);">
                                Click the edit icon to modify contact information. You can update all fields including 
                                customer assignment and company relations.
                            </p>
                        </div>
                        <div>
                            <h5 class="font-medium mb-1" style="color: var(--theme-text);">View Details</h5>
                            <p class="text-sm" style="color: var(--theme-text-muted);">
                                Click the eye icon or contact name to view full details, activity history, and quick actions.
                            </p>
                        </div>
                        <div>
                            <h5 class="font-medium mb-1" style="color: var(--theme-text);">Delete Contact</h5>
                            <p class="text-sm" style="color: var(--theme-text-muted);">
                                Click the trash icon to delete a contact. This action requires confirmation and is logged 
                                in the activity history.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Company Relations --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Company Relations</h4>
                    <p style="color: var(--theme-text-muted);">
                        Contacts can be related to multiple companies. This is useful when a person works with 
                        multiple entities or has roles in different organizations. Super administrators can manage 
                        these relations through the contact edit page.
                    </p>
                    <ul class="list-disc list-inside text-sm mt-2" style="color: var(--theme-text-muted);">
                        <li>One contact can be linked to multiple companies</li>
                        <li>Each company relation can be marked as primary</li>
                        <li>Company badges show abbreviated names for space efficiency</li>
                        <li>Hover over badges to see full company names</li>
                    </ul>
                </div>

                {{-- Activity Tracking --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Activity Tracking</h4>
                    <p style="color: var(--theme-text-muted);">
                        Every change made to a contact is automatically logged in the activity timeline. This includes:
                    </p>
                    <ul class="list-disc list-inside text-sm mt-2" style="color: var(--theme-text-muted);">
                        <li><span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1"></span> Contact creation</li>
                        <li><span class="inline-block w-2 h-2 bg-blue-500 rounded-full mr-1"></span> Field updates (shows old and new values)</li>
                        <li><span class="inline-block w-2 h-2 bg-purple-500 rounded-full mr-1"></span> Company relations added</li>
                        <li><span class="inline-block w-2 h-2 bg-pink-500 rounded-full mr-1"></span> Company relations removed</li>
                        <li><span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> Contact deletion</li>
                    </ul>
                    <p class="text-sm mt-2" style="color: var(--theme-text-muted);">
                        Activities show who made the change, when it occurred, and the user's IP address for security auditing.
                    </p>
                </div>

                {{-- Permissions --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Permissions</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2" style="color: var(--theme-text);">Role</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">View</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">Create</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">Edit</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">Delete</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">Manage Companies</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-2" style="color: var(--theme-text-muted);">Super Admin</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2" style="color: var(--theme-text-muted);">Admin</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">-</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2" style="color: var(--theme-text-muted);">Project Manager</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">✓</td>
                                    <td class="text-center px-2 py-2">-</td>
                                    <td class="text-center px-2 py-2">-</td>
                                </tr>
                                <tr>
                                    <td class="py-2" style="color: var(--theme-text-muted);">User/Reader</td>
                                    <td class="text-center px-2 py-2">-</td>
                                    <td class="text-center px-2 py-2">-</td>
                                    <td class="text-center px-2 py-2">-</td>
                                    <td class="text-center px-2 py-2">-</td>
                                    <td class="text-center px-2 py-2">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tips --}}
                <div>
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Quick Tips</h4>
                    <ul class="list-disc list-inside text-sm space-y-1" style="color: var(--theme-text-muted);">
                        <li>Use the search bar to quickly find contacts by name, email, phone, or customer</li>
                        <li>Filter by customer or company to see specific contact groups</li>
                        <li>Click on a customer name to navigate to their detail page</li>
                        <li>Primary contacts are shown with a star badge for easy identification</li>
                        <li>The activity timeline shows all changes in reverse chronological order</li>
                        <li>Hover over truncated text to see the full content</li>
                    </ul>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 pt-4 border-t flex justify-end">
                <button onclick="closeHelpModal()" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openHelpModal() {
    document.getElementById('helpModal').classList.remove('hidden');
    document.getElementById('helpModal').classList.add('flex');
}

function closeHelpModal() {
    document.getElementById('helpModal').classList.add('hidden');
    document.getElementById('helpModal').classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('helpModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelpModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('helpModal').classList.contains('hidden')) {
        closeHelpModal();
    }
});
</script>