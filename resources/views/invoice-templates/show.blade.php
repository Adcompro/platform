@extends('layouts.app')

@section('title', 'Invoice Template - ' . $invoiceTemplate->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/80 backdrop-blur-md border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <div class="flex items-center space-x-2 text-sm text-slate-500">
                        <a href="{{ route('invoice-templates.index') }}" class="hover:text-slate-700">Invoice Templates</a>
                        <span>/</span>
                        <span class="text-slate-900">{{ $invoiceTemplate->name }}</span>
                    </div>
                    <h1 class="text-xl font-semibold text-slate-900 mt-1">{{ $invoiceTemplate->name }}</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $invoiceTemplate->description }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="openFullPreview()" 
                            class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-expand mr-1.5 text-xs"></i>
                        Full Preview
                    </button>
                    @if(in_array(Auth::user()->role, ['super_admin']) || 
                        (Auth::user()->role == 'admin' && $invoiceTemplate->company_id == Auth::user()->company_id))
                    <a href="{{ route('invoice-templates.edit', $invoiceTemplate) }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                        <i class="fas fa-edit mr-1.5 text-xs"></i>
                        Edit Template
                    </a>
                    @endif
                    <a href="{{ route('invoice-templates.index') }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all">
                        <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-12 gap-6">
            {{-- Left Side - Template Details --}}
            <div class="col-span-4">
                {{-- Template Information --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Template Details</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Type:</span>
                            <span class="text-sm font-medium text-slate-900">{{ ucfirst($invoiceTemplate->template_type) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Status:</span>
                            @if($invoiceTemplate->is_active)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Active</span>
                            @else
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">Inactive</span>
                            @endif
                        </div>
                        @if($invoiceTemplate->is_default)
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Default:</span>
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">Default Template</span>
                        </div>
                        @endif
                        @if($invoiceTemplate->company)
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Company:</span>
                            <span class="text-sm font-medium text-slate-900">{{ $invoiceTemplate->company->name }}</span>
                        </div>
                        @else
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Scope:</span>
                            <span class="text-sm font-medium text-slate-900">System-wide</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Design Settings --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-4">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Design Settings</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">Color Scheme:</span>
                            <div class="flex items-center space-x-2">
                                @if($invoiceTemplate->color_scheme == 'custom')
                                    <div class="flex space-x-1">
                                        <div class="w-6 h-6 rounded border border-slate-300" style="background-color: {{ $invoiceTemplate->primary_color }}"></div>
                                        <div class="w-6 h-6 rounded border border-slate-300" style="background-color: {{ $invoiceTemplate->secondary_color }}"></div>
                                        <div class="w-6 h-6 rounded border border-slate-300" style="background-color: {{ $invoiceTemplate->accent_color }}"></div>
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 bg-{{ $invoiceTemplate->color_scheme }}-100 text-{{ $invoiceTemplate->color_scheme }}-700 text-xs rounded">
                                        {{ ucfirst($invoiceTemplate->color_scheme) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Font:</span>
                            <span class="text-sm font-medium text-slate-900">{{ $invoiceTemplate->font_family }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Font Size:</span>
                            <span class="text-sm font-medium text-slate-900">{{ ucfirst($invoiceTemplate->font_size) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-slate-500">Logo Position:</span>
                            <span class="text-sm font-medium text-slate-900">{{ ucfirst($invoiceTemplate->logo_position) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Included Sections --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50">
                        <h2 class="text-base font-medium text-slate-900">Included Sections</h2>
                    </div>
                    <div class="p-4 space-y-2">
                        @php
                            $sections = [
                                'show_logo' => 'Company Logo',
                                'show_header' => 'Header Section',
                                'show_project_details' => 'Project Details',
                                'show_budget_overview' => 'Budget Overview',
                                'show_time_entry_details' => 'Time Entry Details',
                                'show_additional_costs_section' => 'Additional Costs',
                                'show_subtotals' => 'Subtotals',
                                'show_tax_details' => 'Tax Details',
                                'show_discount_section' => 'Discount Section',
                                'show_payment_terms' => 'Payment Terms',
                                'show_bank_details' => 'Bank Details',
                                'show_notes_section' => 'Notes Section',
                                'show_footer' => 'Footer',
                                'show_page_numbers' => 'Page Numbers'
                            ];
                        @endphp
                        @foreach($sections as $field => $label)
                        <div class="flex items-center justify-between py-1">
                            <span class="text-sm text-slate-600">{{ $label }}</span>
                            @if($invoiceTemplate->$field)
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                            @else
                                <i class="fas fa-times-circle text-slate-300 text-sm"></i>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right Side - Template Preview --}}
            <div class="col-span-8">
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200/50 flex justify-between items-center">
                        <h2 class="text-base font-medium text-slate-900">Template Preview</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-slate-500">Sample data preview</span>
                        </div>
                    </div>
                    
                    {{-- Preview Container --}}
                    <div class="p-4">
                        <div class="bg-white border border-slate-200 rounded-lg shadow-sm" style="min-height: 800px;">
                            <div class="p-8" style="font-family: {{ $invoiceTemplate->font_family }}, sans-serif;">
                                {{-- Header with Logo --}}
                                @if($invoiceTemplate->show_header)
                                <div class="border-b-2 border-{{ $invoiceTemplate->color_scheme }}-500 pb-4 mb-6">
                                    <div class="flex {{ $invoiceTemplate->logo_position == 'center' ? 'justify-center' : ($invoiceTemplate->logo_position == 'right' ? 'justify-end' : 'justify-between') }} items-start">
                                        @if($invoiceTemplate->show_logo && $invoiceTemplate->logo_position !== 'none')
                                        <div class="w-24 h-24 bg-slate-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-building text-slate-400 text-2xl"></i>
                                        </div>
                                        @endif
                                        @if($invoiceTemplate->logo_position == 'left')
                                        <div class="text-right">
                                            <h1 class="text-3xl font-bold text-{{ $invoiceTemplate->color_scheme }}-600">INVOICE</h1>
                                            <p class="text-sm text-slate-500 mt-1">{{ $previewData['invoice']['invoice_number'] }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    @if($invoiceTemplate->logo_position !== 'left')
                                    <h1 class="text-3xl font-bold text-{{ $invoiceTemplate->color_scheme }}-600 mt-4">INVOICE</h1>
                                    <p class="text-sm text-slate-500 mt-1">{{ $previewData['invoice']['invoice_number'] }}</p>
                                    @endif
                                </div>
                                @endif

                                {{-- Company and Customer Info --}}
                                <div class="grid grid-cols-2 gap-8 mb-6">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-700 mb-2">From:</h3>
                                        <div class="text-sm text-slate-600">
                                            <p class="font-semibold">{{ $previewData['company']['name'] }}</p>
                                            <p>{{ $previewData['company']['address'] }}</p>
                                            <p>{{ $previewData['company']['zip_code'] }} {{ $previewData['company']['city'] }}</p>
                                            <p>{{ $previewData['company']['country'] }}</p>
                                            @if($invoiceTemplate->show_bank_details)
                                            <p class="mt-2">VAT: {{ $previewData['company']['vat_number'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-700 mb-2">Bill To:</h3>
                                        <div class="text-sm text-slate-600">
                                            <p class="font-semibold">{{ $previewData['customer']['name'] }}</p>
                                            <p>{{ $previewData['customer']['company'] }}</p>
                                            <p>{{ $previewData['customer']['address'] }}</p>
                                            <p>{{ $previewData['customer']['zip_code'] }} {{ $previewData['customer']['city'] }}</p>
                                            <p>{{ $previewData['customer']['country'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Invoice Details --}}
                                <div class="bg-slate-50 rounded-lg p-4 mb-6">
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="text-slate-500">Invoice Date:</span>
                                            <p class="font-semibold">{{ date('F d, Y', strtotime($previewData['invoice']['invoice_date'])) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-slate-500">Due Date:</span>
                                            <p class="font-semibold">{{ date('F d, Y', strtotime($previewData['invoice']['due_date'])) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-slate-500">Status:</span>
                                            <p class="font-semibold text-{{ $invoiceTemplate->color_scheme }}-600">{{ ucfirst($previewData['invoice']['status']) }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Project Info --}}
                                @if($invoiceTemplate->show_project_details)
                                <div class="mb-6">
                                    <h3 class="text-sm font-semibold text-slate-700 mb-2">Project Details</h3>
                                    <div class="text-sm text-slate-600">
                                        <p class="font-medium">{{ $previewData['project']['name'] }}</p>
                                        <p class="text-xs mt-1">{{ $previewData['project']['description'] }}</p>
                                    </div>
                                </div>
                                @endif

                                {{-- Line Items --}}
                                <div class="mb-6">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b-2 border-{{ $invoiceTemplate->color_scheme }}-200">
                                                <th class="text-left py-2 font-semibold text-slate-700">Description</th>
                                                <th class="text-right py-2 font-semibold text-slate-700">Qty</th>
                                                <th class="text-right py-2 font-semibold text-slate-700">Rate</th>
                                                <th class="text-right py-2 font-semibold text-slate-700">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($previewData['line_items'] as $item)
                                            <tr class="border-b border-slate-200">
                                                <td class="py-3">{{ $item['description'] }}</td>
                                                <td class="py-3 text-right">{{ $item['quantity'] }}</td>
                                                <td class="py-3 text-right">€{{ number_format($item['unit_price'], 2) }}</td>
                                                <td class="py-3 text-right font-medium">€{{ number_format($item['total'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Totals --}}
                                <div class="flex justify-end mb-6">
                                    <div class="w-64">
                                        @if($invoiceTemplate->show_subtotals)
                                        <div class="flex justify-between py-2 text-sm">
                                            <span class="text-slate-600">Subtotal:</span>
                                            <span class="font-medium">€{{ number_format($previewData['invoice']['subtotal'], 2) }}</span>
                                        </div>
                                        @endif
                                        @if($invoiceTemplate->show_tax_details)
                                        <div class="flex justify-between py-2 text-sm border-b border-slate-200">
                                            <span class="text-slate-600">VAT (21%):</span>
                                            <span class="font-medium">€{{ number_format($previewData['invoice']['vat_amount'], 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between py-3 text-lg font-bold text-{{ $invoiceTemplate->color_scheme }}-600">
                                            <span>Total:</span>
                                            <span>€{{ number_format($previewData['invoice']['total_amount'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Terms --}}
                                @if($invoiceTemplate->show_payment_terms)
                                <div class="border-t border-slate-200 pt-4 mb-4">
                                    <h3 class="text-sm font-semibold text-slate-700 mb-2">Payment Terms</h3>
                                    <p class="text-xs text-slate-600">{{ $invoiceTemplate->payment_terms_text ?: 'Payment is due within 30 days of invoice date.' }}</p>
                                </div>
                                @endif

                                {{-- Bank Details --}}
                                @if($invoiceTemplate->show_bank_details)
                                <div class="bg-slate-50 rounded-lg p-4 mb-4">
                                    <h3 class="text-sm font-semibold text-slate-700 mb-2">Bank Details</h3>
                                    <div class="grid grid-cols-2 gap-4 text-xs text-slate-600">
                                        <div>
                                            <p><span class="font-medium">Bank:</span> {{ $previewData['company']['bank_name'] }}</p>
                                            <p><span class="font-medium">IBAN:</span> {{ $previewData['company']['iban'] }}</p>
                                        </div>
                                        <div>
                                            <p><span class="font-medium">BIC:</span> {{ $previewData['company']['bic'] }}</p>
                                            <p><span class="font-medium">Account Name:</span> {{ $previewData['company']['name'] }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Footer --}}
                                @if($invoiceTemplate->show_footer)
                                <div class="border-t border-slate-200 pt-4 mt-8 text-center text-xs text-slate-500">
                                    <p>{{ $invoiceTemplate->footer_content ?: 'Thank you for your business!' }}</p>
                                    @if($invoiceTemplate->show_page_numbers)
                                    <p class="mt-2">Page 1 of 1</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Full Screen Preview Modal --}}
<div id="fullPreviewModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-5xl w-full max-h-[90vh] overflow-auto">
            <div class="sticky top-0 bg-white border-b border-slate-200 px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-medium text-slate-900">Invoice Template Preview</h3>
                <button onclick="closeFullPreview()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="fullPreviewContent" class="p-8">
                {{-- Full preview content will be loaded here --}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openFullPreview() {
    // Clone the preview content
    const previewContent = document.querySelector('.bg-white.border.border-slate-200.rounded-lg').innerHTML;
    document.getElementById('fullPreviewContent').innerHTML = previewContent;
    document.getElementById('fullPreviewModal').classList.remove('hidden');
}

function closeFullPreview() {
    document.getElementById('fullPreviewModal').classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullPreview();
    }
});
</script>
@endpush