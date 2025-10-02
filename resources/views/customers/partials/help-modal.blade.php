{{-- Customer Management Help Modal --}}
<div id="help-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-3xl w-full max-h-[80vh] overflow-hidden">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold" style="color: var(--theme-text);">
                        Customer Management Guide
                    </h3>
                    <button onclick="closeHelpModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-4 overflow-y-auto max-h-[60vh]">
                {{-- Overview Section --}}
                <div class="mb-6">
                    <h4 class="text-[15px] font-semibold mb-3" style="color: var(--theme-text);">Overview</h4>
                    <p class="text-[13px] mb-2" style="color: var(--theme-text-muted);">
                        The Customer Management module allows you to manage all your customer relationships, track their projects, 
                        and maintain contact information. Customers are the foundation of your project management system.
                    </p>
                </div>

                {{-- Features Section --}}
                <div class="mb-6">
                    <h4 class="text-[15px] font-semibold mb-3" style="color: var(--theme-text);">Key Features</h4>
                    <ul class="space-y-2 text-[13px]" style="color: var(--theme-text-muted);">
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span><strong>Customer Profiles:</strong> Store complete customer information including contact details, addresses, and notes</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span><strong>Project Tracking:</strong> View all projects associated with each customer</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span><strong>Contact Management:</strong> Manage multiple contact persons per customer</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span><strong>Status Management:</strong> Track active and inactive customers</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span><strong>Invoice Templates:</strong> Assign default invoice templates per customer</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span><strong>Company Assignment:</strong> Organize customers by managing company</span>
                        </li>
                    </ul>
                </div>

                {{-- Actions Section --}}
                <div class="mb-6">
                    <h4 class="text-[15px] font-semibold mb-3" style="color: var(--theme-text);">Available Actions</h4>
                    <div class="space-y-3">
                        <div>
                            <h5 class="text-[13px] font-medium mb-1" style="color: var(--theme-text);">Creating Customers</h5>
                            <p class="text-[12px]" style="color: var(--theme-text-muted);">
                                Click the "Create Customer" button to add a new customer. Fill in required fields (name, email) 
                                and optional information like address and contact person.
                            </p>
                        </div>
                        <div>
                            <h5 class="text-[13px] font-medium mb-1" style="color: var(--theme-text);">Bulk Operations</h5>
                            <p class="text-[12px]" style="color: var(--theme-text-muted);">
                                Select multiple customers using checkboxes and use the bulk action dropdown to:
                                <br>• Change status (activate/deactivate)
                                <br>• Delete multiple customers at once
                                <br>• Export selected customers
                            </p>
                        </div>
                        <div>
                            <h5 class="text-[13px] font-medium mb-1" style="color: var(--theme-text);">Search & Filter</h5>
                            <p class="text-[12px]" style="color: var(--theme-text-muted);">
                                Use the search bar to find customers by name, email, company, or contact person. 
                                Filter by status or managing company for more precise results.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Permissions Section --}}
                <div class="mb-6">
                    <h4 class="text-[15px] font-semibold mb-3" style="color: var(--theme-text);">Permissions</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-[12px]">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 pr-4" style="color: var(--theme-text);">Role</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">View</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">Create</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">Edit</th>
                                    <th class="text-center px-2 py-2" style="color: var(--theme-text);">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-2 pr-4" style="color: var(--theme-text-muted);">Super Admin</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2 pr-4" style="color: var(--theme-text-muted);">Admin</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-red-500">✗</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2 pr-4" style="color: var(--theme-text-muted);">Project Manager</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-green-500">✓</td>
                                    <td class="text-center px-2 py-2 text-red-500">✗</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pr-4" style="color: var(--theme-text-muted);">User</td>
                                    <td class="text-center px-2 py-2 text-red-500">✗</td>
                                    <td class="text-center px-2 py-2 text-red-500">✗</td>
                                    <td class="text-center px-2 py-2 text-red-500">✗</td>
                                    <td class="text-center px-2 py-2 text-red-500">✗</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tips Section --}}
                <div>
                    <h4 class="text-[15px] font-semibold mb-3" style="color: var(--theme-text);">Quick Tips</h4>
                    <ul class="space-y-1 text-[12px]" style="color: var(--theme-text-muted);">
                        <li>• Keep customer information up to date for accurate project tracking</li>
                        <li>• Use the notes field to record important customer preferences or requirements</li>
                        <li>• Assign invoice templates to streamline your billing process</li>
                        <li>• Regularly review inactive customers to maintain a clean database</li>
                        <li>• Add multiple contact persons for better communication management</li>
                    </ul>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-end">
                    <button onclick="closeHelpModal()" 
                            class="px-4 py-2 text-[13px] font-normal rounded-lg transition-all border"
                            style="background-color: white; color: var(--theme-text); border-color: var(--theme-text-muted);">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openHelpModal() {
    document.getElementById('help-modal').classList.remove('hidden');
}

function closeHelpModal() {
    document.getElementById('help-modal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('help-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelpModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHelpModal();
    }
});
</script>