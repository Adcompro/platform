{{-- Teamleader Import Help Modal --}}
<div id="helpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-5xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between mb-4 pb-4 border-b">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Teamleader Focus Import Guide</h3>
                </div>
                <button onclick="closeHelpModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="mt-2 space-y-6 max-h-[70vh] overflow-y-auto pr-2">

                {{-- Overview Section --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5">
                    <h4 class="font-semibold text-lg mb-3 text-blue-900 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        What is Teamleader Import?
                    </h4>
                    <p class="text-sm text-gray-700 mb-3">
                        The Teamleader Focus Integration allows you to automatically import your <strong>customers, contacts, and projects</strong>
                        from Teamleader Focus CRM into Progress. This eliminates manual data entry and keeps your data synchronized.
                    </p>
                    <div class="bg-white rounded-lg p-4 mt-3">
                        <p class="text-sm text-gray-700">
                            <strong class="text-blue-900">💡 Key Benefit:</strong> All imports now run in the <strong>background</strong>!
                            You can continue working while data is being imported, and you'll receive an email notification when complete.
                        </p>
                    </div>
                </div>

                {{-- What Gets Imported Section --}}
                <div>
                    <h4 class="font-semibold text-lg mb-3 text-gray-900 flex items-center">
                        <i class="fas fa-download mr-2 text-blue-600"></i>
                        What Gets Imported?
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Companies → Customers --}}
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-building text-blue-600"></i>
                                <h5 class="font-medium text-gray-900">Companies → Customers</h5>
                            </div>
                            <ul class="text-sm text-gray-600 space-y-1 ml-6">
                                <li>• Company name and details</li>
                                <li>• VAT number and legal info</li>
                                <li>• Complete address</li>
                                <li>• Email and phone numbers</li>
                                <li>• Website</li>
                            </ul>
                        </div>

                        {{-- Contacts --}}
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-users text-green-600"></i>
                                <h5 class="font-medium text-gray-900">Contact Persons</h5>
                            </div>
                            <ul class="text-sm text-gray-600 space-y-1 ml-6">
                                <li>• First and last name</li>
                                <li>• Email and phone</li>
                                <li>• Position/role</li>
                                <li>• Company relationships</li>
                                <li>• Contact preferences</li>
                            </ul>
                        </div>

                        {{-- Projects --}}
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-project-diagram text-purple-600"></i>
                                <h5 class="font-medium text-gray-900">Projects</h5>
                            </div>
                            <ul class="text-sm text-gray-600 space-y-1 ml-6">
                                <li>• Project name and code</li>
                                <li>• Status and dates</li>
                                <li>• <strong>Budget amount</strong> (monthly fee)</li>
                                <li>• Complete milestone structure</li>
                                <li>• All tasks and subtasks</li>
                            </ul>
                        </div>

                        {{-- Time Entries - Note about Excel Import --}}
                        <div class="border border-amber-200 rounded-lg p-4 bg-amber-50">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-file-excel text-amber-600"></i>
                                <h5 class="font-medium text-gray-900">Time Entries & Project Structure</h5>
                            </div>
                            <p class="text-sm text-amber-900 mb-2">
                                <strong>Important:</strong> Time entries and detailed project structure (milestones, tasks) are <strong>not</strong> imported through this Teamleader import.
                            </p>
                            <p class="text-sm text-amber-800">
                                Instead, use the <strong>Excel Upload</strong> feature in the <strong>Timesheets</strong> section. This provides better control and allows you to import time entries with full project structure.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Import Methods Section --}}
                <div>
                    <h4 class="font-semibold text-lg mb-3 text-gray-900 flex items-center">
                        <i class="fas fa-cogs mr-2 text-blue-600"></i>
                        Import Methods
                    </h4>

                    {{-- Method 1: Master Sync --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-sync-alt text-yellow-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="font-medium text-yellow-900 mb-2">1. Master Sync (Database Cache)</h5>
                                <p class="text-sm text-yellow-800 mb-2">
                                    <strong>Purpose:</strong> Populate the local database cache with ALL data from Teamleader.
                                    This is used for fast searching and selective imports.
                                </p>
                                <div class="bg-white rounded p-3 text-sm text-gray-700">
                                    <p class="mb-2"><strong>When to use:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>Once per day/week to refresh data</li>
                                        <li>Before doing selective imports</li>
                                        <li>To populate search functionality</li>
                                    </ul>
                                    <p class="mt-2 text-yellow-700">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        <strong>Note:</strong> Master Sync only updates the cache, it does NOT create customers/projects in Progress.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Method 2: Quick Import All --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-bolt text-blue-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="font-medium text-blue-900 mb-2">2. Import All (Quick)</h5>
                                <p class="text-sm text-blue-800 mb-2">
                                    <strong>Purpose:</strong> Quickly import ALL companies/contacts/projects at once.
                                </p>
                                <div class="bg-white rounded p-3 text-sm text-gray-700">
                                    <p class="mb-2"><strong>When to use:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>Initial setup - first time importing</li>
                                        <li>When you want everything from Teamleader</li>
                                        <li>No need for selective filtering</li>
                                    </ul>
                                    <p class="mt-2 text-blue-700">
                                        <i class="fas fa-rocket mr-1"></i>
                                        <strong>Runs in background</strong> - you can continue working!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Method 3: Select & Import --}}
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-square text-green-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="font-medium text-green-900 mb-2">3. Select & Import (Recommended)</h5>
                                <p class="text-sm text-green-800 mb-2">
                                    <strong>Purpose:</strong> Choose exactly which companies/contacts/projects to import.
                                </p>
                                <div class="bg-white rounded p-3 text-sm text-gray-700">
                                    <p class="mb-2"><strong>When to use:</strong></p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>When you only want specific customers</li>
                                        <li>To re-import/update existing data</li>
                                        <li>For testing with a small dataset</li>
                                        <li>More control over what gets imported</li>
                                    </ul>
                                    <p class="mt-2 text-green-700">
                                        <i class="fas fa-star mr-1"></i>
                                        <strong>Best practice:</strong> Run Master Sync first, then use Select & Import for precise control.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Background Import Feature --}}
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-5">
                    <h4 class="font-semibold text-lg mb-3 text-purple-900 flex items-center">
                        <i class="fas fa-rocket mr-2"></i>
                        🚀 Background Import System
                    </h4>
                    <p class="text-sm text-gray-700 mb-3">
                        All imports now run as <strong>background jobs</strong>, which means:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="bg-white rounded-lg p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-user-clock text-purple-600"></i>
                                <span class="font-medium text-sm text-gray-900">Continue Working</span>
                            </div>
                            <p class="text-xs text-gray-600">
                                You can navigate away and keep working while the import runs independently
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-envelope text-purple-600"></i>
                                <span class="font-medium text-sm text-gray-900">Email Notification</span>
                            </div>
                            <p class="text-xs text-gray-600">
                                You'll receive an email with results when the import completes (with statistics!)
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-shield-alt text-purple-600"></i>
                                <span class="font-medium text-sm text-gray-900">No Data Loss</span>
                            </div>
                            <p class="text-xs text-gray-600">
                                Import won't be cancelled if you close browser or navigate away
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Import Options Section --}}
                <div>
                    <h4 class="font-semibold text-lg mb-3 text-gray-900 flex items-center">
                        <i class="fas fa-sliders-h mr-2 text-blue-600"></i>
                        Cascade Import Options
                    </h4>
                    <p class="text-sm text-gray-700 mb-3">
                        When importing companies, you can choose to automatically import related data:
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <input type="checkbox" checked disabled class="mt-1">
                            <div class="flex-1">
                                <p class="font-medium text-sm text-gray-900">Import Contacts</p>
                                <p class="text-xs text-gray-600">Automatically import all contact persons for each company (with positions and email)</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <input type="checkbox" checked disabled class="mt-1">
                            <div class="flex-1">
                                <p class="font-medium text-sm text-gray-900">Import Projects</p>
                                <p class="text-xs text-gray-600">Import all projects linked to these companies (basic project info and budget data)</p>
                            </div>
                        </div>
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-xs text-amber-900">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Note:</strong> Time entries and project structure (milestones, tasks) are imported via Excel upload in the Timesheets section, not through this import.
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-xs text-blue-900">
                            <i class="fas fa-lightbulb mr-1"></i>
                            <strong>Tip:</strong> Enable all options for a complete data import. Large imports may take 10-15 minutes but run in the background.
                        </p>
                    </div>
                </div>

                {{-- Step-by-Step Guide --}}
                <div>
                    <h4 class="font-semibold text-lg mb-3 text-gray-900 flex items-center">
                        <i class="fas fa-list-ol mr-2 text-blue-600"></i>
                        Step-by-Step Import Guide
                    </h4>
                    <div class="space-y-3">
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">1</div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 mb-1">Connect to Teamleader</p>
                                <p class="text-sm text-gray-600">Click "Connect to Teamleader" and authorize the connection with your Teamleader Focus account.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">2</div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 mb-1">Run Master Sync (Optional but Recommended)</p>
                                <p class="text-sm text-gray-600">Click "Sync All Companies", "Sync All Contacts", etc. to populate the database cache. This makes selective imports faster.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">3</div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 mb-1">Choose Import Method</p>
                                <p class="text-sm text-gray-600">
                                    • <strong>Import All:</strong> Click "Import All" for quick bulk import<br>
                                    • <strong>Select & Import:</strong> Click "Select & Import" to choose specific items
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">4</div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 mb-1">Configure Import Options</p>
                                <p class="text-sm text-gray-600">Select which related data to import (contacts, projects, time entries).</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">5</div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 mb-1">Start Import</p>
                                <p class="text-sm text-gray-600">Click "Import Selected" or "Import All". Import starts in background immediately.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-sm">6</div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 mb-1">Wait for Email Notification</p>
                                <p class="text-sm text-gray-600">Continue working! You'll receive an email when import completes with full statistics (customers, projects, duration).</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Important Notes --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold mb-2 text-yellow-900 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Important Notes
                    </h4>
                    <ul class="space-y-2 text-sm text-yellow-800">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-yellow-600 mt-1"></i>
                            <span><strong>company_id is NULL:</strong> Imported customers have NULL company_id by default, allowing manual assignment later.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-yellow-600 mt-1"></i>
                            <span><strong>Re-import is UPDATE:</strong> Importing an already-imported customer will UPDATE its data, not create duplicates.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-yellow-600 mt-1"></i>
                            <span><strong>Budget Data:</strong> Project budgets (monthly_fee) are automatically imported from Teamleader budget_amount.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-yellow-600 mt-1"></i>
                            <span><strong>Rate Limits:</strong> Teamleader API has rate limits. If you get 429 errors, wait 10 minutes before retrying.</span>
                        </li>
                    </ul>
                </div>

                {{-- Troubleshooting Section --}}
                <div>
                    <h4 class="font-semibold text-lg mb-3 text-gray-900 flex items-center">
                        <i class="fas fa-wrench mr-2 text-blue-600"></i>
                        Troubleshooting
                    </h4>
                    <div class="space-y-3">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="font-medium text-gray-900 mb-1">Q: Import was successful in logs but data is missing?</p>
                            <p class="text-sm text-gray-600">
                                <strong>A:</strong> This was a browser navigation issue (now fixed with background jobs).
                                Simply re-import the customer - it will UPDATE the data correctly.
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="font-medium text-gray-900 mb-1">Q: Getting "API rate limit exceeded (HTTP 429)" errors?</p>
                            <p class="text-sm text-gray-600">
                                <strong>A:</strong> Teamleader API has rate limits. Wait 10-15 minutes and try again.
                                For large imports, use "Select & Import" to import in smaller batches.
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="font-medium text-gray-900 mb-1">Q: How long should an import take?</p>
                            <p class="text-sm text-gray-600">
                                <strong>A:</strong> Depends on data volume:<br>
                                • 1-10 companies: 1-2 minutes<br>
                                • 10-50 companies with projects: 5-10 minutes<br>
                                • 100+ companies with full cascade: 15-30 minutes<br>
                                All imports run in background, so you can work during this time!
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="font-medium text-gray-900 mb-1">Q: Can I import the same customer twice?</p>
                            <p class="text-sm text-gray-600">
                                <strong>A:</strong> Yes! Re-importing will UPDATE the existing customer with fresh data from Teamleader.
                                This is useful to refresh data after making changes in Teamleader.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Best Practices --}}
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="font-semibold mb-3 text-green-900 flex items-center">
                        <i class="fas fa-star mr-2"></i>
                        Best Practices
                    </h4>
                    <ul class="space-y-2 text-sm text-green-800">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-600 mt-1"></i>
                            <span>Run <strong>Master Sync</strong> once per week to keep cache fresh</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-600 mt-1"></i>
                            <span>Use <strong>Select & Import</strong> for better control over what gets imported</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-600 mt-1"></i>
                            <span>Enable all cascade options (contacts, projects, time) for complete data</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-600 mt-1"></i>
                            <span>Check your email for import results - it contains detailed statistics</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-600 mt-1"></i>
                            <span>For initial setup, import in small batches (10-20 companies) to test</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-600 mt-1"></i>
                            <span>Don't worry about browser navigation - imports run independently!</span>
                        </li>
                    </ul>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 pt-4 border-t flex justify-end">
                <button onclick="closeHelpModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-all font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openHelpModal() {
    document.getElementById('helpModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeHelpModal() {
    document.getElementById('helpModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
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
