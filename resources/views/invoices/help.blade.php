@extends('layouts.app')

@section('title', 'Invoices - Complete Help Guide')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/80 backdrop-blur-md border-b border-slate-200/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Invoice Management Help Guide</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Complete guide to creating, managing, and tracking invoices</p>
                </div>
                <a href="{{ route('invoices.index') }}" 
                   class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                    Back to Invoices
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-12 gap-6">
            {{-- Table of Contents --}}
            <div class="col-span-3">
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden sticky top-20">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Table of Contents</h2>
                    </div>
                    <div class="p-4">
                        <nav class="space-y-2">
                            <a href="#overview" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                1. Overview
                            </a>
                            <a href="#workflow" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                2. Invoice Workflow
                            </a>
                            <a href="#creating-invoices" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                3. Creating Invoices
                            </a>
                            <a href="#budget-system" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                4. Budget & Fee System
                            </a>
                            <a href="#editing-drafts" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                5. Editing Draft Invoices
                            </a>
                            <a href="#merging-lines" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                6. Merging Invoice Lines
                            </a>
                            <a href="#ai-features" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                7. AI Features
                            </a>
                            <a href="#activity-reports" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                8. Activity Reports
                            </a>
                            <a href="#templates" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                9. Invoice Templates
                            </a>
                            <a href="#status-management" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                10. Status Management
                            </a>
                            <a href="#best-practices" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                11. Best Practices
                            </a>
                            <a href="#troubleshooting" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                12. Troubleshooting
                            </a>
                            <a href="#keyboard-shortcuts" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                13. Keyboard Shortcuts
                            </a>
                            <a href="#faq" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                14. FAQ
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Help Content --}}
            <div class="col-span-9 space-y-6">
                {{-- 1. Overview Section --}}
                <div id="overview" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">1. Overview</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600">
                            The Invoice Management System is the financial heart of your project management platform. It automatically generates invoices based on tracked time entries, project milestones, and additional costs, while managing complex budget calculations with monthly fee systems and rollover amounts.
                        </p>
                        
                        <h3 class="text-base font-semibold text-slate-900 mt-4">Key Capabilities:</h3>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Automated Generation:</strong> Create invoices from time entries with one click</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Budget Management:</strong> Monthly fee system with automatic rollover calculations</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>AI Enhancement:</strong> Automatic description generation and consolidation</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Line Merging:</strong> Combine multiple invoice lines into consolidated entries</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Activity Reports:</strong> Detailed breakdowns for customer transparency</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Template System:</strong> Customizable invoice layouts per company/customer</span>
                            </li>
                        </ul>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-lightbulb text-blue-500 mr-2"></i>
                                <strong>Pro Tip:</strong> The system automatically tracks which time entries have been invoiced, preventing duplicate billing and ensuring accurate financial records.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 2. Invoice Workflow Section --}}
                <div id="workflow" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">2. Invoice Workflow</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            Invoices follow a structured workflow from creation to payment. Understanding this flow helps ensure proper financial management.
                        </p>

                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 mb-4">
                            <h3 class="text-base font-semibold text-slate-900 mb-3">Invoice Lifecycle:</h3>
                            <div class="flex items-center justify-between text-sm">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-yellow-500 text-white rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <span class="font-medium">Draft</span>
                                </div>
                                <i class="fas fa-arrow-right text-slate-400"></i>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <span class="font-medium">Finalized</span>
                                </div>
                                <i class="fas fa-arrow-right text-slate-400"></i>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-purple-500 text-white rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>
                                    <span class="font-medium">Sent</span>
                                </div>
                                <i class="fas fa-arrow-right text-slate-400"></i>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <span class="font-medium">Paid</span>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">Status Descriptions:</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex">
                                <dt class="font-medium text-slate-700 w-24">Draft:</dt>
                                <dd class="text-slate-600">Invoice can be edited, lines added/removed, amounts changed</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium text-slate-700 w-24">Finalized:</dt>
                                <dd class="text-slate-600">Invoice is locked, gets official number, no more edits allowed</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium text-slate-700 w-24">Sent:</dt>
                                <dd class="text-slate-600">Invoice has been sent to customer, payment timer starts</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium text-slate-700 w-24">Paid:</dt>
                                <dd class="text-slate-600">Payment received, invoice is complete</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium text-slate-700 w-24">Overdue:</dt>
                                <dd class="text-red-600">Payment not received within due date (automatic)</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- 3. Creating Invoices Section --}}
                <div id="creating-invoices" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">3. Creating Invoices</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <h3 class="text-base font-semibold text-slate-900 mb-3">Step-by-Step Process:</h3>
                        
                        <ol class="space-y-4 text-sm text-slate-600">
                            <li>
                                <strong>1. Navigate to Invoices:</strong>
                                <p class="mt-1">Click "Invoices" in the main menu or use shortcut key 'I'</p>
                            </li>
                            <li>
                                <strong>2. Click "Create Invoice":</strong>
                                <p class="mt-1">Green button in top-right corner opens the creation form</p>
                            </li>
                            <li>
                                <strong>3. Select Invoice Type:</strong>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-start">
                                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full mt-1.5 mr-2"></span>
                                        <div>
                                            <strong>Regular Invoice:</strong> Standard invoice from approved time entries
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="inline-block w-2 h-2 bg-purple-500 rounded-full mt-1.5 mr-2"></span>
                                        <div>
                                            <strong>Activity Report:</strong> Detailed breakdown with time entry descriptions
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="inline-block w-2 h-2 bg-green-500 rounded-full mt-1.5 mr-2"></span>
                                        <div>
                                            <strong>Fixed Price:</strong> Based on project milestones/fixed amounts
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <strong>4. Configure Basic Settings:</strong>
                                <ul class="mt-2 space-y-1">
                                    <li>• Select customer (required)</li>
                                    <li>• Choose project (optional)</li>
                                    <li>• Set billing period (from/to dates)</li>
                                    <li>• Select invoice template</li>
                                    <li>• Choose invoicing company (if multi-company)</li>
                                </ul>
                            </li>
                            <li>
                                <strong>5. Review Time Entries:</strong>
                                <p class="mt-1">System shows all approved, unbilled time entries for the period</p>
                                <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mt-2">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                    Only approved time entries can be invoiced
                                </div>
                            </li>
                            <li>
                                <strong>6. Apply AI Enhancement (Optional):</strong>
                                <p class="mt-1">Click "Enhance with AI" to generate professional descriptions</p>
                            </li>
                            <li>
                                <strong>7. Generate Invoice:</strong>
                                <p class="mt-1">Click "Create Invoice" to generate draft invoice</p>
                            </li>
                        </ol>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <strong>Success!</strong> Invoice is created in Draft status. You can now edit lines, merge entries, or finalize for sending.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 4. Budget & Fee System Section --}}
                <div id="budget-system" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">4. Budget & Monthly Fee System</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            The budget system manages monthly fee allocations with automatic rollover calculations, ensuring transparent budget tracking across billing periods.
                        </p>

                        <h3 class="text-base font-semibold text-slate-900 mb-3">How It Works:</h3>
                        
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <h4 class="font-semibold text-slate-900 mb-2">Budget Components:</h4>
                                    <ul class="space-y-1 text-slate-600">
                                        <li>• Previous Month Remaining</li>
                                        <li>• Current Monthly Budget</li>
                                        <li>• Total Available Budget</li>
                                        <li>• Work Performed Amount</li>
                                        <li>• Next Month Rollover</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-900 mb-2">Calculation Formula:</h4>
                                    <div class="bg-white/80 rounded p-2 font-mono text-xs">
                                        Total Available = Previous + Monthly<br>
                                        Used = Work + Services + Costs<br>
                                        Rollover = Total - Used<br>
                                        <span class="text-green-600">If positive: rolls to next month</span><br>
                                        <span class="text-red-600">If negative: overservicing alert</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">Budget Scenarios:</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-plus text-sm"></i>
                                </div>
                                <div>
                                    <strong class="text-slate-900">Underutilization:</strong>
                                    <p class="text-sm text-slate-600 mt-1">Unused budget rolls over to next month, accumulating available hours</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-equals text-sm"></i>
                                </div>
                                <div>
                                    <strong class="text-slate-900">Full Utilization:</strong>
                                    <p class="text-sm text-slate-600 mt-1">Budget fully used, no rollover, starts fresh next month</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-minus text-sm"></i>
                                </div>
                                <div>
                                    <strong class="text-slate-900">Overservicing:</strong>
                                    <p class="text-sm text-slate-600 mt-1">Work exceeds budget, negative rollover reduces next month's available budget</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. Editing Draft Invoices Section --}}
                <div id="editing-drafts" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">5. Editing Draft Invoices</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            Draft invoices can be fully edited before finalization. This flexibility allows you to perfect the invoice before sending to customers.
                        </p>

                        <h3 class="text-base font-semibold text-slate-900 mb-3">Available Edit Actions:</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="font-semibold text-blue-900 mb-2">
                                    <i class="fas fa-edit text-blue-600 mr-2"></i>Line Editing
                                </h4>
                                <ul class="text-sm text-blue-800 space-y-1">
                                    <li>• Edit descriptions</li>
                                    <li>• Adjust quantities</li>
                                    <li>• Change unit prices</li>
                                    <li>• Modify VAT rates</li>
                                    <li>• Set defer to next month</li>
                                </ul>
                            </div>
                            
                            <div class="bg-green-50 rounded-lg p-4">
                                <h4 class="font-semibold text-green-900 mb-2">
                                    <i class="fas fa-plus text-green-600 mr-2"></i>Line Management
                                </h4>
                                <ul class="text-sm text-green-800 space-y-1">
                                    <li>• Add custom lines</li>
                                    <li>• Add time entries</li>
                                    <li>• Remove lines</li>
                                    <li>• Reorder with drag & drop</li>
                                    <li>• Merge multiple lines</li>
                                </ul>
                            </div>
                        </div>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">Drag & Drop Reordering:</h3>
                        <ol class="text-sm text-slate-600 space-y-2">
                            <li>1. Hover over the <i class="fas fa-grip-lines text-slate-400"></i> drag handle on any line</li>
                            <li>2. Click and hold to grab the line</li>
                            <li>3. Drag to new position (visual indicator shows placement)</li>
                            <li>4. Release to drop in new position</li>
                            <li>5. Order is automatically saved</li>
                        </ol>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-exclamation-circle text-yellow-600 mr-2"></i>
                                <strong>Important:</strong> Once an invoice is finalized, it cannot be edited. Make sure all information is correct before finalizing.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 6. Merging Invoice Lines Section --}}
                <div id="merging-lines" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">6. Merging Invoice Lines</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            The merge feature allows you to combine multiple invoice lines into a single consolidated entry. This is perfect for creating cleaner, more summarized invoices.
                        </p>

                        <h3 class="text-base font-semibold text-slate-900 mb-3">How to Merge Lines:</h3>
                        
                        <div class="bg-purple-50 rounded-lg p-4 mb-4">
                            <ol class="space-y-3 text-sm text-purple-900">
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-600 text-white rounded-full text-xs mr-2">1</span>
                                    <div>
                                        <strong>Select Lines:</strong>
                                        <p class="text-purple-700">Click checkboxes next to lines you want to merge (minimum 2)</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-600 text-white rounded-full text-xs mr-2">2</span>
                                    <div>
                                        <strong>Click Merge Button:</strong>
                                        <p class="text-purple-700">Purple "Merge Selected" button appears when 2+ lines selected</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-600 text-white rounded-full text-xs mr-2">3</span>
                                    <div>
                                        <strong>Configure Merged Line:</strong>
                                        <p class="text-purple-700">Edit description, adjust totals in popup dialog</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-600 text-white rounded-full text-xs mr-2">4</span>
                                    <div>
                                        <strong>Confirm Merge:</strong>
                                        <p class="text-purple-700">Click "Merge Lines" to create consolidated entry</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">What Happens During Merge:</h3>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <h4 class="font-semibold text-slate-700 mb-2">Automatic Actions:</h4>
                                <ul class="space-y-1 text-slate-600">
                                    <li>✓ Quantities are summed</li>
                                    <li>✓ Amounts are totaled</li>
                                    <li>✓ Highest VAT rate is used</li>
                                    <li>✓ Time entries remain linked</li>
                                    <li>✓ Original data is preserved</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-700 mb-2">You Can Adjust:</h4>
                                <ul class="space-y-1 text-slate-600">
                                    <li>✓ Combined description</li>
                                    <li>✓ Total quantity</li>
                                    <li>✓ Unit price</li>
                                    <li>✓ VAT rate</li>
                                    <li>✓ Final amount</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                            <p class="text-sm text-green-800">
                                <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                                <strong>Data Safety:</strong> Time entries linked to merged lines remain marked as invoiced and maintain their connection to the new merged line.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 7. AI Features Section --}}
                <div id="ai-features" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">7. AI-Powered Features</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            Advanced AI features help create professional, clear invoices by automatically generating and consolidating descriptions based on your time entries.
                        </p>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4">
                                <h3 class="text-base font-semibold text-purple-900 mb-2">
                                    <i class="fas fa-magic text-purple-600 mr-2"></i>AI Description Generation
                                </h3>
                                <p class="text-sm text-purple-700 mb-2">
                                    Transforms technical time entries into professional invoice descriptions
                                </p>
                                <ul class="text-sm text-purple-600 space-y-1">
                                    <li>• Groups related activities</li>
                                    <li>• Creates clear summaries</li>
                                    <li>• Uses business language</li>
                                    <li>• Maintains context</li>
                                </ul>
                            </div>
                            
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4">
                                <h3 class="text-base font-semibold text-blue-900 mb-2">
                                    <i class="fas fa-compress-alt text-blue-600 mr-2"></i>Smart Consolidation
                                </h3>
                                <p class="text-sm text-blue-700 mb-2">
                                    Intelligently combines similar time entries into logical groups
                                </p>
                                <ul class="text-sm text-blue-600 space-y-1">
                                    <li>• Reduces line items</li>
                                    <li>• Groups by activity type</li>
                                    <li>• Maintains accuracy</li>
                                    <li>• Cleaner invoices</li>
                                </ul>
                            </div>
                        </div>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">Using AI Features:</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="inline-block w-2 h-2 bg-purple-500 rounded-full mt-1.5 mr-2"></span>
                                <div>
                                    <strong>During Creation:</strong>
                                    <p class="text-sm text-slate-600">Enable "Enhance with AI" when generating invoice</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="inline-block w-2 h-2 bg-purple-500 rounded-full mt-1.5 mr-2"></span>
                                <div>
                                    <strong>After Creation:</strong>
                                    <p class="text-sm text-slate-600">Click "Regenerate AI Descriptions" in draft invoice</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="inline-block w-2 h-2 bg-purple-500 rounded-full mt-1.5 mr-2"></span>
                                <div>
                                    <strong>Manual Override:</strong>
                                    <p class="text-sm text-slate-600">Edit any AI-generated description manually if needed</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mt-4">
                            <p class="text-sm text-purple-800">
                                <i class="fas fa-brain text-purple-600 mr-2"></i>
                                <strong>AI Confidence Score:</strong> Each AI-generated invoice shows a confidence score (e.g., 95%) indicating the AI's certainty in its consolidation and descriptions.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 8. Activity Reports Section --}}
                <div id="activity-reports" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">8. Activity Reports</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            Activity Reports provide detailed breakdowns of all work performed, perfect for transparent client communication and detailed billing documentation.
                        </p>

                        <h3 class="text-base font-semibold text-slate-900 mb-3">Report Components:</h3>
                        
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h4 class="font-semibold text-slate-900">Fee Balance Overview</h4>
                                <p class="text-sm text-slate-600">Shows budget utilization, rollover calculations, and remaining balance</p>
                            </div>
                            <div class="border-l-4 border-green-500 pl-4">
                                <h4 class="font-semibold text-slate-900">Expenses Overview</h4>
                                <p class="text-sm text-slate-600">Breaks down costs included in fee vs. additional billable expenses</p>
                            </div>
                            <div class="border-l-4 border-purple-500 pl-4">
                                <h4 class="font-semibold text-slate-900">Activity Details</h4>
                                <p class="text-sm text-slate-600">Comprehensive list of all time entries with descriptions and hours</p>
                            </div>
                            <div class="border-l-4 border-orange-500 pl-4">
                                <h4 class="font-semibold text-slate-900">Invoicing Summary</h4>
                                <p class="text-sm text-slate-600">Total amounts to be invoiced with VAT calculations</p>
                            </div>
                        </div>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">Generating Activity Reports:</h3>
                        
                        <ol class="space-y-2 text-sm text-slate-600">
                            <li>1. Select "Activity Report" as invoice type during creation</li>
                            <li>2. System automatically includes all time entry details</li>
                            <li>3. AI consolidates similar activities while maintaining detail</li>
                            <li>4. Preview shows full breakdown before finalizing</li>
                            <li>5. Export to Excel for additional analysis</li>
                        </ol>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-file-excel text-blue-600 mr-2"></i>
                                <strong>Excel Export:</strong> Activity reports can be exported to Excel format, maintaining all formatting and calculations for easy sharing and analysis.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 9. Invoice Templates Section --}}
                <div id="templates" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">9. Invoice Templates</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            Custom invoice templates allow you to maintain brand consistency and meet specific customer requirements for invoice formatting.
                        </p>

                        <h3 class="text-base font-semibold text-slate-900 mb-3">Template Hierarchy:</h3>
                        
                        <div class="flex items-center justify-center mb-4">
                            <div class="flex items-center space-x-4 text-sm">
                                <div class="text-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-globe text-2xl text-gray-600"></i>
                                    </div>
                                    <p class="mt-2 font-medium">System</p>
                                    <p class="text-xs text-gray-500">Default</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400"></i>
                                <div class="text-center">
                                    <div class="w-20 h-20 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-building text-2xl text-blue-600"></i>
                                    </div>
                                    <p class="mt-2 font-medium">Company</p>
                                    <p class="text-xs text-gray-500">Override</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400"></i>
                                <div class="text-center">
                                    <div class="w-20 h-20 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-2xl text-green-600"></i>
                                    </div>
                                    <p class="mt-2 font-medium">Customer</p>
                                    <p class="text-xs text-gray-500">Override</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-400"></i>
                                <div class="text-center">
                                    <div class="w-20 h-20 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-folder text-2xl text-purple-600"></i>
                                    </div>
                                    <p class="mt-2 font-medium">Project</p>
                                    <p class="text-xs text-gray-500">Specific</p>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-slate-600 text-center mb-4">
                            Templates cascade from general to specific. Most specific template is used.
                        </p>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">Managing Templates:</h3>
                        
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-start">
                                <i class="fas fa-cog text-slate-400 mr-2 mt-1"></i>
                                <span>Access via Settings → Invoice Templates</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-paint-brush text-slate-400 mr-2 mt-1"></i>
                                <span>Use visual builder to design layouts</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-copy text-slate-400 mr-2 mt-1"></i>
                                <span>Duplicate existing templates as starting point</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-eye text-slate-400 mr-2 mt-1"></i>
                                <span>Preview with sample data before using</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- 10. Status Management Section --}}
                <div id="status-management" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">10. Status Management</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <h3 class="text-base font-semibold text-slate-900 mb-3">Status Transitions:</h3>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">From Status</th>
                                        <th class="text-left py-2">Can Move To</th>
                                        <th class="text-left py-2">Action Required</th>
                                    </tr>
                                </thead>
                                <tbody class="text-slate-600">
                                    <tr class="border-b">
                                        <td class="py-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Draft
                                            </span>
                                        </td>
                                        <td class="py-2">Finalized, Cancelled</td>
                                        <td class="py-2">Click "Finalize Invoice"</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Finalized
                                            </span>
                                        </td>
                                        <td class="py-2">Sent, Cancelled</td>
                                        <td class="py-2">Mark as "Sent to Customer"</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Sent
                                            </span>
                                        </td>
                                        <td class="py-2">Paid, Overdue</td>
                                        <td class="py-2">Mark as "Paid" or wait for due date</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Overdue
                                            </span>
                                        </td>
                                        <td class="py-2">Paid, Cancelled</td>
                                        <td class="py-2">Mark as "Paid" when received</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Paid
                                            </span>
                                        </td>
                                        <td class="py-2">None (Final)</td>
                                        <td class="py-2">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h3 class="text-base font-semibold text-slate-900 mt-4">Automatic Status Changes:</h3>
                        
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-start">
                                <i class="fas fa-clock text-orange-500 mr-2 mt-1"></i>
                                <div>
                                    <strong>Sent → Overdue:</strong>
                                    <p>Automatically changes when due date passes without payment</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-calendar-check text-green-500 mr-2 mt-1"></i>
                                <div>
                                    <strong>Due Date Calculation:</strong>
                                    <p>Default 30 days from invoice date (configurable per customer)</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- 11. Best Practices Section --}}
                <div id="best-practices" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">11. Best Practices</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-base font-semibold text-green-700 mb-3">
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>Do's
                                </h3>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li>✓ Review all time entries before invoicing</li>
                                    <li>✓ Use AI to enhance descriptions</li>
                                    <li>✓ Merge similar lines for clarity</li>
                                    <li>✓ Check budget calculations</li>
                                    <li>✓ Preview before finalizing</li>
                                    <li>✓ Set appropriate due dates</li>
                                    <li>✓ Use templates consistently</li>
                                    <li>✓ Track payment status</li>
                                    <li>✓ Export for accounting</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-red-700 mb-3">
                                    <i class="fas fa-times-circle text-red-600 mr-2"></i>Don'ts
                                </h3>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li>✗ Don't finalize without review</li>
                                    <li>✗ Don't ignore overdue invoices</li>
                                    <li>✗ Don't skip budget validation</li>
                                    <li>✗ Don't use wrong templates</li>
                                    <li>✗ Don't forget VAT rates</li>
                                    <li>✗ Don't invoice unapproved time</li>
                                    <li>✗ Don't delete paid invoices</li>
                                    <li>✗ Don't modify finalized invoices</li>
                                    <li>✗ Don't ignore AI suggestions</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <h4 class="font-semibold text-blue-900 mb-2">Monthly Invoice Checklist:</h4>
                            <ol class="text-sm text-blue-800 space-y-1">
                                <li>1. Ensure all time entries are approved</li>
                                <li>2. Review and apply monthly budget</li>
                                <li>3. Generate invoices with AI enhancement</li>
                                <li>4. Review and merge lines as needed</li>
                                <li>5. Preview with correct template</li>
                                <li>6. Finalize and send to customers</li>
                                <li>7. Track payment status</li>
                                <li>8. Export for accounting records</li>
                            </ol>
                        </div>
                    </div>
                </div>

                {{-- 12. Troubleshooting Section --}}
                <div id="troubleshooting" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">12. Troubleshooting</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <div class="space-y-4">
                            <div class="border-l-4 border-yellow-500 pl-4">
                                <h3 class="font-semibold text-slate-900">Missing Time Entries</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Problem:</strong> Some time entries don't appear in invoice
                                </p>
                                <p class="text-sm text-slate-600 mt-2">
                                    <strong>Solution:</strong> Check that entries are:
                                </p>
                                <ul class="text-sm text-slate-600 mt-1">
                                    <li>• Approved (not pending)</li>
                                    <li>• Within selected date range</li>
                                    <li>• Not already invoiced</li>
                                    <li>• Marked as billable</li>
                                </ul>
                            </div>

                            <div class="border-l-4 border-red-500 pl-4">
                                <h3 class="font-semibold text-slate-900">Cannot Edit Invoice</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Problem:</strong> Edit buttons are disabled
                                </p>
                                <p class="text-sm text-slate-600 mt-2">
                                    <strong>Solution:</strong> Invoice must be in Draft status. Finalized invoices cannot be edited. Create a credit note if changes needed.
                                </p>
                            </div>

                            <div class="border-l-4 border-blue-500 pl-4">
                                <h3 class="font-semibold text-slate-900">Wrong Budget Calculations</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Problem:</strong> Rollover amount seems incorrect
                                </p>
                                <p class="text-sm text-slate-600 mt-2">
                                    <strong>Solution:</strong> Verify:
                                </p>
                                <ul class="text-sm text-slate-600 mt-1">
                                    <li>• Previous month's invoice is finalized</li>
                                    <li>• Monthly fee is set correctly</li>
                                    <li>• All costs are included</li>
                                    <li>• No duplicate entries</li>
                                </ul>
                            </div>

                            <div class="border-l-4 border-green-500 pl-4">
                                <h3 class="font-semibold text-slate-900">AI Not Working</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Problem:</strong> AI features unavailable or failing
                                </p>
                                <p class="text-sm text-slate-600 mt-2">
                                    <strong>Solution:</strong> 
                                </p>
                                <ul class="text-sm text-slate-600 mt-1">
                                    <li>• Check AI credits in Settings</li>
                                    <li>• Ensure time entries have descriptions</li>
                                    <li>• Try regenerating descriptions</li>
                                    <li>• Contact admin if persists</li>
                                </ul>
                            </div>

                            <div class="border-l-4 border-purple-500 pl-4">
                                <h3 class="font-semibold text-slate-900">Template Not Applied</h3>
                                <p class="text-sm text-slate-600 mt-1">
                                    <strong>Problem:</strong> Invoice uses wrong template
                                </p>
                                <p class="text-sm text-slate-600 mt-2">
                                    <strong>Solution:</strong> Check template hierarchy - most specific template wins. Verify template is active and assigned correctly.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 13. Keyboard Shortcuts Section --}}
                <div id="keyboard-shortcuts" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">13. Keyboard Shortcuts</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600 mb-4">
                            Speed up your workflow with these keyboard shortcuts:
                        </p>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 mb-3">Navigation</h3>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Go to Invoices</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Alt + I</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Create New</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Alt + N</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Search</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Ctrl + K</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Filter</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Alt + F</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 mb-3">Actions</h3>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Save Draft</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Ctrl + S</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Preview</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Ctrl + P</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Finalize</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Ctrl + Enter</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Cancel</dt>
                                        <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Esc</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 mt-4">
                            <h3 class="text-base font-semibold text-slate-900 mb-3">Line Editing Shortcuts</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-slate-600">Select All Lines</dt>
                                    <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Ctrl + A</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-600">Merge Selected</dt>
                                    <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Ctrl + M</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-600">Delete Selected</dt>
                                    <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Delete</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-600">Add Custom Line</dt>
                                    <dd class="font-mono bg-gray-100 px-2 py-1 rounded">Alt + L</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- 14. FAQ Section --}}
                <div id="faq" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">14. Frequently Asked Questions</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <div class="space-y-4">
                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    Can I edit an invoice after it's been sent?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    No, once an invoice is finalized and sent, it cannot be edited. This ensures invoice integrity and compliance. If changes are needed, create a credit note and issue a new invoice.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    How does the budget rollover work?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    Unused budget from the previous month is automatically added to the current month's available budget. If you exceeded the budget (overservicing), the negative amount reduces the current month's available budget.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    What happens to time entries when I merge invoice lines?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    Time entries remain linked and marked as invoiced. They are automatically reassigned to the new merged line, maintaining full audit trail and preventing duplicate billing.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    Can I invoice time entries from multiple projects?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    Yes, when creating an invoice for a customer without selecting a specific project, all approved time entries across all projects for that customer will be included.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    How do I handle different VAT rates?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    Each invoice line can have its own VAT rate. Standard Dutch rates (21%, 9%, 0%) are available. When merging lines, the highest VAT rate is used by default but can be adjusted.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    What's the difference between 'in fee' and 'additional' costs?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    'In fee' costs are covered by the monthly budget/retainer. 'Additional' costs are billed separately on top of the monthly fee. The system tracks both for transparent reporting.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    Can I defer invoice lines to next month?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    Yes, each invoice line has a "Defer to next month" checkbox. Checked lines will be excluded from the current invoice and available for the next billing period.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    How do I export invoices for accounting?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    Use the bulk export feature to download invoices in PDF or Excel format. Activity reports can be exported to Excel with full detail preservation. Integration with accounting software is also available.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    What triggers an overdue status?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    The system automatically changes invoice status from 'Sent' to 'Overdue' when the due date passes without payment being recorded. Daily checks ensure timely status updates.
                                </p>
                            </details>

                            <details class="group">
                                <summary class="cursor-pointer font-semibold text-slate-900 hover:text-blue-600">
                                    Can I customize invoice numbering?
                                </summary>
                                <p class="mt-2 text-sm text-slate-600 pl-4">
                                    Yes, invoice numbering format can be configured in Settings. Options include prefixes, year inclusion, and starting numbers. Each company can have its own numbering sequence.
                                </p>
                            </details>
                        </div>
                    </div>
                </div>

                {{-- Additional Resources --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mt-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-3">Need More Help?</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <a href="{{ route('invoice-templates.help') }}" class="flex items-center p-3 bg-white rounded-lg transition-all" style="box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.98)'" onmouseout="this.style.filter='brightness(1)'">
                            <i class="fas fa-file-alt text-blue-600 text-xl mr-3"></i>
                            <div>
                                <p class="font-medium text-slate-900">Template Guide</p>
                                <p class="text-xs text-slate-500">Learn about templates</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center p-3 bg-white rounded-lg transition-all" style="box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.98)'" onmouseout="this.style.filter='brightness(1)'">
                            <i class="fas fa-video text-purple-600 text-xl mr-3"></i>
                            <div>
                                <p class="font-medium text-slate-900">Video Tutorials</p>
                                <p class="text-xs text-slate-500">Watch how-to videos</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center p-3 bg-white rounded-lg transition-all" style="box-shadow: var(--theme-card-shadow);" onmouseover="this.style.filter='brightness(0.98)'" onmouseout="this.style.filter='brightness(1)'">
                            <i class="fas fa-headset text-green-600 text-xl mr-3"></i>
                            <div>
                                <p class="font-medium text-slate-900">Contact Support</p>
                                <p class="text-xs text-slate-500">Get personal help</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Highlight active section in TOC
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('[id]');
    const scrollY = window.pageYOffset;
    
    sections.forEach(section => {
        const sectionHeight = section.offsetHeight;
        const sectionTop = section.offsetTop - 100;
        const sectionId = section.getAttribute('id');
        
        if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('bg-blue-50', 'text-blue-600', 'font-medium');
                link.classList.add('text-slate-600');
            });
            
            const activeLink = document.querySelector(`nav a[href="#${sectionId}"]`);
            if (activeLink) {
                activeLink.classList.remove('text-slate-600');
                activeLink.classList.add('bg-blue-50', 'text-blue-600', 'font-medium');
            }
        }
    });
});
</script>
@endpush
@endsection