@extends('layouts.app')

@section('title', 'Invoice Templates - Help Guide')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/80 backdrop-blur-md border-b border-slate-200/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Invoice Template Help Guide</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Everything you need to know about creating and managing invoice templates</p>
                </div>
                <a href="{{ route('invoice-templates.index') }}" 
                   class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                    Back to Templates
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
                            <a href="#getting-started" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                2. Getting Started
                            </a>
                            <a href="#template-builder" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                3. Template Builder
                            </a>
                            <a href="#blocks" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                4. Available Blocks
                            </a>
                            <a href="#configuration" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                5. Block Configuration
                            </a>
                            <a href="#template-hierarchy" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                6. Template Hierarchy
                            </a>
                            <a href="#best-practices" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                7. Best Practices
                            </a>
                            <a href="#troubleshooting" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                8. Troubleshooting
                            </a>
                            <a href="#faq" class="block text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 px-2 py-1 rounded">
                                9. FAQ
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Help Content --}}
            <div class="col-span-9 space-y-6">
                {{-- Overview Section --}}
                <div id="overview" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">1. Overview</h2>
                    </div>
                    <div class="p-6 prose prose-slate max-w-none">
                        <p class="text-slate-600">
                            The Invoice Template Builder is a powerful drag-and-drop system that allows you to create custom invoice layouts for your business. 
                            Think of it as a "LEGO blocks" system where you can arrange different components to build the perfect invoice template.
                        </p>
                        
                        <h3 class="text-base font-semibold text-slate-900 mt-4">Key Features:</h3>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Drag & Drop Builder:</strong> Intuitive visual interface for building templates</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>18 Configurable Blocks:</strong> Various content blocks for complete customization</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Live Preview:</strong> See how your invoice will look in real-time</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Template Hierarchy:</strong> System → Company → Customer → Project level templates</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                <span><strong>Multi-tenant Support:</strong> Company-specific or system-wide templates</span>
                            </li>
                        </ul>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-900">Pro Tip</h4>
                                    <p class="text-sm text-blue-700 mt-1">
                                        Start with a standard template and customize it to match your brand. You can always duplicate and modify existing templates.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Getting Started Section --}}
                <div id="getting-started" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">2. Getting Started</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <h3 class="text-base font-semibold text-slate-900">Creating Your First Template</h3>
                        
                        <ol class="space-y-3 text-sm text-slate-600">
                            <li class="flex">
                                <span class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-xs font-semibold text-slate-700 mr-3">1</span>
                                <div>
                                    <strong>Navigate to Invoice Templates</strong>
                                    <p class="text-slate-500">Click on "Invoice Templates" in the main menu</p>
                                </div>
                            </li>
                            <li class="flex">
                                <span class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-xs font-semibold text-slate-700 mr-3">2</span>
                                <div>
                                    <strong>Click "New Template"</strong>
                                    <p class="text-slate-500">This opens the template builder interface</p>
                                </div>
                            </li>
                            <li class="flex">
                                <span class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-xs font-semibold text-slate-700 mr-3">3</span>
                                <div>
                                    <strong>Configure Basic Settings</strong>
                                    <p class="text-slate-500">Set template name, type, colors, and fonts in the left panel</p>
                                </div>
                            </li>
                            <li class="flex">
                                <span class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-xs font-semibold text-slate-700 mr-3">4</span>
                                <div>
                                    <strong>Drag Blocks to Canvas</strong>
                                    <p class="text-slate-500">Select blocks from the middle panel and drag them to the canvas</p>
                                </div>
                            </li>
                            <li class="flex">
                                <span class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-xs font-semibold text-slate-700 mr-3">5</span>
                                <div>
                                    <strong>Configure Each Block</strong>
                                    <p class="text-slate-500">Click on any block to open its configuration options</p>
                                </div>
                            </li>
                            <li class="flex">
                                <span class="flex-shrink-0 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center text-xs font-semibold text-slate-700 mr-3">6</span>
                                <div>
                                    <strong>Preview and Save</strong>
                                    <p class="text-slate-500">Use the preview button to see the result, then save your template</p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                {{-- Template Builder Section --}}
                <div id="template-builder" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">3. Template Builder Interface</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-600">The template builder is divided into three main sections:</p>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-semibold text-slate-900 mb-2">
                                    <i class="fas fa-cog text-slate-500 mr-2"></i>
                                    Left Panel: Settings
                                </h4>
                                <ul class="text-xs text-slate-600 space-y-1">
                                    <li>• Template name & description</li>
                                    <li>• Template type selection</li>
                                    <li>• Color scheme options</li>
                                    <li>• Font family & size</li>
                                    <li>• Logo positioning</li>
                                    <li>• Active/default toggles</li>
                                </ul>
                            </div>
                            
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-semibold text-slate-900 mb-2">
                                    <i class="fas fa-th-large text-slate-500 mr-2"></i>
                                    Middle Panel: Blocks
                                </h4>
                                <ul class="text-xs text-slate-600 space-y-1">
                                    <li>• 18 available blocks</li>
                                    <li>• Drag to add to template</li>
                                    <li>• Visual icons for each type</li>
                                    <li>• Green check for added blocks</li>
                                    <li>• Hover for descriptions</li>
                                </ul>
                            </div>
                            
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-semibold text-slate-900 mb-2">
                                    <i class="fas fa-file-invoice text-slate-500 mr-2"></i>
                                    Right Panel: Canvas
                                </h4>
                                <ul class="text-xs text-slate-600 space-y-1">
                                    <li>• Drop zone for blocks</li>
                                    <li>• Reorder with drag handles</li>
                                    <li>• Click blocks to configure</li>
                                    <li>• Remove blocks with X</li>
                                    <li>• Clear all button</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lightbulb text-yellow-400 mt-1"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-yellow-900">Quick Tip</h4>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        You can reorder blocks at any time by dragging the handle icon (≡) on the left side of each block.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Available Blocks Section --}}
                <div id="blocks" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">4. Available Blocks</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-600 mb-4">Each block serves a specific purpose in your invoice:</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Essential Blocks --}}
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-3">Essential Blocks</h4>
                                <div class="space-y-2">
                                    <div class="flex items-start">
                                        <i class="fas fa-heading text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Header</strong>
                                            <p class="text-xs text-slate-500">Company logo and invoice title</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-building text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Company Info</strong>
                                            <p class="text-xs text-slate-500">Your business details and contact</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-user text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Customer Info</strong>
                                            <p class="text-xs text-slate-500">Client billing information</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Invoice Details</strong>
                                            <p class="text-xs text-slate-500">Invoice number, dates, terms</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-list text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Line Items</strong>
                                            <p class="text-xs text-slate-500">Products/services being invoiced</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-calculator text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Total Amount</strong>
                                            <p class="text-xs text-slate-500">Final invoice total</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Optional Blocks --}}
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-3">Optional Blocks</h4>
                                <div class="space-y-2">
                                    <div class="flex items-start">
                                        <i class="fas fa-clock text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Time Entries</strong>
                                            <p class="text-xs text-slate-500">Detailed time tracking records</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-chart-pie text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Budget Overview</strong>
                                            <p class="text-xs text-slate-500">Project budget status</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-plus-circle text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Additional Costs</strong>
                                            <p class="text-xs text-slate-500">Extra charges and expenses</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-percentage text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Tax & Discounts</strong>
                                            <p class="text-xs text-slate-500">VAT calculations and discounts</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-university text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">Bank Details</strong>
                                            <p class="text-xs text-slate-500">Payment information</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-qrcode text-slate-400 w-5 mt-1"></i>
                                        <div class="ml-3">
                                            <strong class="text-sm text-slate-900">QR Code</strong>
                                            <p class="text-xs text-slate-500">Quick payment links</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Block Configuration Section --}}
                <div id="configuration" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">5. Block Configuration</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-600">
                            Each block can be configured individually. Click on any block in the canvas to open its configuration options.
                        </p>

                        <h3 class="text-base font-semibold text-slate-900">Common Configuration Options:</h3>
                        
                        <div class="space-y-3">
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-slate-900 mb-2">Display Settings</h4>
                                <ul class="text-xs text-slate-600 space-y-1">
                                    <li>• Show/hide specific fields</li>
                                    <li>• Choose display format (inline/block)</li>
                                    <li>• Enable/disable sections</li>
                                </ul>
                            </div>
                            
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-slate-900 mb-2">Grouping Options</h4>
                                <ul class="text-xs text-slate-600 space-y-1">
                                    <li>• Group items by milestone</li>
                                    <li>• Group time entries by user/date</li>
                                    <li>• Sort order preferences</li>
                                </ul>
                            </div>
                            
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-slate-900 mb-2">Custom Text</h4>
                                <ul class="text-xs text-slate-600 space-y-1">
                                    <li>• Section titles</li>
                                    <li>• Default notes text</li>
                                    <li>• Payment instructions</li>
                                    <li>• Footer content</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400 mt-1"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-green-900">Configuration Saved</h4>
                                    <p class="text-sm text-green-700 mt-1">
                                        Configured blocks show a green "Configured" badge. Your settings are automatically saved with the template.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Template Hierarchy Section --}}
                <div id="template-hierarchy" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">6. Template Hierarchy</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-600 mb-4">
                            Templates follow a priority hierarchy. The system automatically selects the most specific template available:
                        </p>
                        
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-purple-700">1</span>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="bg-purple-50 rounded-lg px-4 py-3">
                                        <strong class="text-sm text-purple-900">Project Level</strong>
                                        <p class="text-xs text-purple-700 mt-1">Specific template assigned to a project (highest priority)</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-blue-700">2</span>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="bg-blue-50 rounded-lg px-4 py-3">
                                        <strong class="text-sm text-blue-900">Customer Level</strong>
                                        <p class="text-xs text-blue-700 mt-1">Default template for all invoices to this customer</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-green-700">3</span>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="bg-green-50 rounded-lg px-4 py-3">
                                        <strong class="text-sm text-green-900">Company Level</strong>
                                        <p class="text-xs text-green-700 mt-1">Company-wide default template</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-slate-700">4</span>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="bg-slate-50 rounded-lg px-4 py-3">
                                        <strong class="text-sm text-slate-900">System Level</strong>
                                        <p class="text-xs text-slate-700 mt-1">System default template (fallback)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Best Practices Section --}}
                <div id="best-practices" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">7. Best Practices</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <i class="fas fa-star text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-sm text-slate-900">Start Simple</strong>
                                    <p class="text-xs text-slate-600 mt-1">Begin with essential blocks and add optional ones as needed</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-star text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-sm text-slate-900">Maintain Consistency</strong>
                                    <p class="text-xs text-slate-600 mt-1">Use the same template structure across similar invoice types</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-star text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-sm text-slate-900">Test with Preview</strong>
                                    <p class="text-xs text-slate-600 mt-1">Always preview your template with sample data before using it</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-star text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-sm text-slate-900">Configure Blocks Properly</strong>
                                    <p class="text-xs text-slate-600 mt-1">Take time to configure each block for optimal display</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-star text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-sm text-slate-900">Use Template Duplication</strong>
                                    <p class="text-xs text-slate-600 mt-1">Duplicate existing templates as a starting point for variations</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-star text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <strong class="text-sm text-slate-900">Consider Your Audience</strong>
                                    <p class="text-xs text-slate-600 mt-1">Include information that your customers need to process payments</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Troubleshooting Section --}}
                <div id="troubleshooting" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">8. Troubleshooting</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="space-y-4">
                            <div class="border-l-4 border-red-400 bg-red-50 p-4">
                                <h4 class="text-sm font-semibold text-red-900">Blocks won't drag</h4>
                                <p class="text-xs text-red-700 mt-1">
                                    Ensure you're clicking and holding on the block itself, not just the icon. Try refreshing the page if the issue persists.
                                </p>
                            </div>
                            
                            <div class="border-l-4 border-orange-400 bg-orange-50 p-4">
                                <h4 class="text-sm font-semibold text-orange-900">Configuration not saving</h4>
                                <p class="text-xs text-orange-700 mt-1">
                                    Make sure to click "Save Configuration" in the modal before closing it. The green "Configured" badge confirms saving.
                                </p>
                            </div>
                            
                            <div class="border-l-4 border-yellow-400 bg-yellow-50 p-4">
                                <h4 class="text-sm font-semibold text-yellow-900">Template not appearing in selection</h4>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Check that the template is marked as "Active" and has the correct company assignment if using multi-tenant setup.
                                </p>
                            </div>
                            
                            <div class="border-l-4 border-blue-400 bg-blue-50 p-4">
                                <h4 class="text-sm font-semibold text-blue-900">Preview not loading</h4>
                                <p class="text-xs text-blue-700 mt-1">
                                    Disable pop-up blockers for this site. The preview opens in a new window for better viewing.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FAQ Section --}}
                <div id="faq" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">9. Frequently Asked Questions</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900">Can I have multiple templates per company?</h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Yes! You can create unlimited templates. Use the template hierarchy to assign them at different levels (project, customer, company).
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900">Can I change a template after invoices have been created?</h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Yes, but changes only affect new invoices. Existing invoices retain their original formatting for consistency.
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900">How do I make a template the default?</h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Check the "Set as default template" option in the template settings. Only one template can be default per company.
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900">Can I import/export templates?</h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Currently, you can duplicate templates within the system. Import/export functionality is planned for a future update.
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900">What's the difference between template types?</h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Template types (Standard, Modern, Classic, etc.) are mainly for organization. The actual layout is determined by the blocks you add.
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900">Can I add custom CSS to templates?</h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Not directly, but you can use the color scheme and font settings to customize appearance. Custom CSS may be added in future versions.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Support Section --}}
                <div class="bg-gradient-to-r from-slate-600 to-slate-700 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Need More Help?</h3>
                            <p class="text-sm text-slate-200 mt-1">
                                Contact our support team for additional assistance with invoice templates.
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <button class="px-4 py-2 bg-white text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all">
                                <i class="fas fa-envelope mr-2"></i>
                                Email Support
                            </button>
                            <button class="px-4 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-400 transition-all">
                                <i class="fas fa-book mr-2"></i>
                                Documentation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Smooth scrolling for table of contents
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

// Highlight active section in table of contents
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('[id]');
    const scrollY = window.pageYOffset;
    
    sections.forEach(section => {
        const sectionHeight = section.offsetHeight;
        const sectionTop = section.offsetTop - 100;
        const sectionId = section.getAttribute('id');
        
        if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
            document.querySelector(`a[href="#${sectionId}"]`)?.classList.add('bg-slate-100', 'text-slate-900');
        } else {
            document.querySelector(`a[href="#${sectionId}"]`)?.classList.remove('bg-slate-100', 'text-slate-900');
        }
    });
});
</script>
@endpush