{{-- Projects Help Modal --}}
<div id="helpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between mb-4 pb-4 border-b">
                <h3 class="text-lg font-medium" style="color: var(--theme-text);">Project Management Help Guide</h3>
                <button onclick="closeHelpModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="mt-2 space-y-6">

                {{-- Recurring Projects Section --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold mb-3 text-blue-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Recurring Projects (Automatic Monthly Projects)
                    </h4>

                    <div class="space-y-4">
                        {{-- Overview --}}
                        <div>
                            <h5 class="font-medium mb-2 text-gray-900">What are Recurring Projects?</h5>
                            <p class="text-sm text-gray-700">
                                Recurring projects allow you to create a <strong>master project template</strong> that automatically generates
                                new projects every month (or custom interval). This is perfect for retainer clients, subscription services,
                                or any recurring work.
                            </p>
                        </div>

                        {{-- How It Works --}}
                        <div>
                            <h5 class="font-medium mb-2 text-gray-900">How It Works</h5>
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                                <li>
                                    <strong>Create a Master Project</strong>
                                    <ul class="list-disc list-inside ml-6 mt-1 space-y-1">
                                        <li>Enable "Recurring Project" toggle when creating/editing a project</li>
                                        <li>Set up the complete project structure (milestones, tasks)</li>
                                        <li>Configure monthly fee (budget per period)</li>
                                        <li>Choose frequency (monthly, quarterly, etc.)</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>System Creates Child Projects Automatically</strong>
                                    <ul class="list-disc list-inside ml-6 mt-1 space-y-1">
                                        <li>Every night at 02:00 AM, the system checks all master projects</li>
                                        <li>New projects are created <strong>7 days before</strong> the next period (configurable)</li>
                                        <li>Complete structure is copied: milestones, tasks, budget</li>
                                        <li>Project name: "[Base Name] [Month] [Year]" (e.g., "Anker Nov 2025")</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>Work in Generated Projects</strong>
                                    <ul class="list-disc list-inside ml-6 mt-1 space-y-1">
                                        <li>Time entries, progress tracking happen in child projects</li>
                                        <li>Each period has its own project with independent budget tracking</li>
                                        <li>Budget rollover from previous month (if enabled)</li>
                                    </ul>
                                </li>
                            </ol>
                        </div>

                        {{-- Key Settings --}}
                        <div>
                            <h5 class="font-medium mb-2 text-gray-900">Key Settings</h5>
                            <div class="bg-white rounded-lg p-3 space-y-2">
                                <div class="flex items-start">
                                    <span class="font-medium text-gray-900 w-48">Recurring Enabled:</span>
                                    <span class="text-gray-700">Toggle to activate automatic generation</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-medium text-gray-900 w-48">Base Name:</span>
                                    <span class="text-gray-700">Used for generated project names (e.g., "Anker")</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-medium text-gray-900 w-48">Frequency:</span>
                                    <span class="text-gray-700">Monthly, Quarterly, or Custom interval</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-medium text-gray-900 w-48">Days Before:</span>
                                    <span class="text-gray-700">How many days before period to create project (default: 7)</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-medium text-gray-900 w-48">Monthly Fee:</span>
                                    <span class="text-gray-700">Budget amount copied to each generated project</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-medium text-gray-900 w-48">Fee Rollover:</span>
                                    <span class="text-gray-700">Enable to carry unused budget to next month</span>
                                </div>
                            </div>
                        </div>

                        {{-- Example Timeline --}}
                        <div>
                            <h5 class="font-medium mb-2 text-gray-900">Example Timeline</h5>
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
                                <div class="flex items-center">
                                    <span class="w-32 font-medium text-gray-700">Oct 24:</span>
                                    <span class="text-gray-600">System creates "Anker Nov 2025" (7 days before Nov 1)</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-32 font-medium text-gray-700">Nov 1:</span>
                                    <span class="text-gray-600">Work starts on November project</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-32 font-medium text-gray-700">Nov 24:</span>
                                    <span class="text-gray-600">System creates "Anker Dec 2025" automatically</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-32 font-medium text-gray-700">Dec 1:</span>
                                    <span class="text-gray-600">December project becomes active</span>
                                </div>
                            </div>
                        </div>

                        {{-- Benefits --}}
                        <div>
                            <h5 class="font-medium mb-2 text-gray-900">Benefits</h5>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                                <li><strong>Zero Manual Work:</strong> Projects create themselves automatically</li>
                                <li><strong>Consistent Structure:</strong> Every month has the same setup</li>
                                <li><strong>Budget Tracking:</strong> Each period has independent budget</li>
                                <li><strong>Template Control:</strong> Update master = all future projects updated</li>
                                <li><strong>Audit Trail:</strong> Complete history per period</li>
                                <li><strong>Time Savings:</strong> Eliminates repetitive project setup</li>
                            </ul>
                        </div>

                        {{-- Best Practices --}}
                        <div>
                            <h5 class="font-medium mb-2 text-gray-900">Best Practices</h5>
                            <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                                <li>Set up master project with complete milestone/task structure</li>
                                <li>Always configure monthly fee in master project</li>
                                <li>Use clear base names (customer name or project type)</li>
                                <li>Enable fee rollover for flexible budget management</li>
                                <li>Keep master project status as "Active" (pausing stops generation)</li>
                                <li>Don't log time in master project - use generated child projects</li>
                            </ul>
                        </div>

                        {{-- Troubleshooting --}}
                        <div>
                            <h5 class="font-medium mb-2 text-gray-900">Troubleshooting</h5>
                            <div class="bg-white rounded-lg p-3 space-y-2 text-sm">
                                <div>
                                    <p class="font-medium text-gray-900">Q: Project wasn't created automatically?</p>
                                    <p class="text-gray-700">A: Check if master project status is "Active" and recurring is enabled. System runs at 02:00 AM daily.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Q: How do I stop automatic generation?</p>
                                    <p class="text-gray-700">A: Disable "Recurring Project" toggle in master project, or set status to "Paused".</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Q: Can I edit generated projects?</p>
                                    <p class="text-gray-700">A: Yes! Each generated project is independent. Changes don't affect master or other periods.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Q: How do I change the structure for future months?</p>
                                    <p class="text-gray-700">A: Edit the master project. New structure will be used for all future generated projects.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Regular Projects Section --}}
                <div>
                    <h4 class="font-semibold mb-3" style="color: var(--theme-text);">Regular Projects</h4>
                    <p class="text-sm" style="color: var(--theme-text-muted);">
                        For one-time projects or non-recurring work, simply create a project without enabling the recurring toggle.
                        You can still use project templates to quickly set up milestones and tasks.
                    </p>
                </div>

                {{-- Project Templates --}}
                <div>
                    <h4 class="font-semibold mb-3" style="color: var(--theme-text);">Using Project Templates</h4>
                    <p class="text-sm mb-2" style="color: var(--theme-text-muted);">
                        Speed up project creation by selecting a template when creating new projects. Templates include
                        pre-configured milestones, tasks, and estimated hours.
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-sm" style="color: var(--theme-text-muted);">
                        <li>Select template in "Project Template" dropdown during creation</li>
                        <li>Structure is automatically imported</li>
                        <li>Customize after import as needed</li>
                        <li>Create your own templates from successful projects</li>
                    </ul>
                </div>

                {{-- Permissions --}}
                <div>
                    <h4 class="font-semibold mb-3" style="color: var(--theme-text);">Permissions</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">View</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Create</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Edit</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Delete</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Recurring Setup</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">Super Admin</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ All</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">Admin</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Company</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">Project Manager</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Assigned</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Yes</td>
                                    <td class="px-4 py-2 text-sm text-yellow-600">⚠ Limited</td>
                                    <td class="px-4 py-2 text-sm text-red-600">✗ No</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">User</td>
                                    <td class="px-4 py-2 text-sm text-green-600">✓ Assigned</td>
                                    <td class="px-4 py-2 text-sm text-red-600">✗ No</td>
                                    <td class="px-4 py-2 text-sm text-yellow-600">⚠ Limited</td>
                                    <td class="px-4 py-2 text-sm text-red-600">✗ No</td>
                                    <td class="px-4 py-2 text-sm text-red-600">✗ No</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Need More Help --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold mb-2" style="color: var(--theme-text);">Need More Help?</h4>
                    <p class="text-sm" style="color: var(--theme-text-muted);">
                        If you have questions about project management or recurring projects, please contact your system administrator
                        or refer to the user documentation.
                    </p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 pt-4 border-t flex justify-end">
                <button onclick="closeHelpModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openHelpModal() {
    document.getElementById('helpModal').classList.remove('hidden');
}

function closeHelpModal() {
    document.getElementById('helpModal').classList.add('hidden');
}

// Close on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeHelpModal();
    }
});

// Close when clicking outside modal
document.getElementById('helpModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeHelpModal();
    }
});
</script>
