{{-- Invoice Template Preview Content (for AJAX modal) --}}
<div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
    {{-- Template Info Header --}}
    <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">
                    {{ $invoiceTemplate->name }}
                </h3>
                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.25rem;">
                    {{ $invoiceTemplate->description }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($invoiceTemplate->is_default)
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded" style="font-size: calc(var(--theme-font-size) - 2px);">Default Template</span>
                @endif
                @if($invoiceTemplate->is_active)
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded" style="font-size: calc(var(--theme-font-size) - 2px);">Active</span>
                @else
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded" style="font-size: calc(var(--theme-font-size) - 2px);">Inactive</span>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Preview Frame --}}
    <div class="p-6 bg-slate-50">
        <div class="bg-white rounded-lg shadow-lg mx-auto" style="max-width: 800px; min-height: 600px;">
            <div class="p-8">
                @php
                    $blocks = is_string($invoiceTemplate->block_positions) 
                        ? json_decode($invoiceTemplate->block_positions, true) 
                        : $invoiceTemplate->block_positions;
                    if (!is_array($blocks)) {
                        $blocks = [];
                    }
                @endphp
                
                @if(count($blocks) > 0)
                    @foreach($blocks as $block)
                        @php
                            $blockType = $block['type'] ?? '';
                            $enabled = $block['enabled'] ?? true;
                            if (!$enabled) continue;
                        @endphp
                        
                        <div class="mb-6">
                            @if($blockType === 'header')
                                <div class="text-center pb-4 border-b-2 border-{{ $invoiceTemplate->color_scheme }}-500">
                                    <h1 class="text-3xl font-bold text-{{ $invoiceTemplate->color_scheme }}-600">INVOICE</h1>
                                    @if($invoiceTemplate->header_content)
                                        <div class="mt-2 text-sm text-gray-600">{!! $invoiceTemplate->header_content !!}</div>
                                    @endif
                                </div>
                            
                            @elseif($blockType === 'logo' && $invoiceTemplate->show_logo)
                                <div class="flex {{ $invoiceTemplate->logo_position == 'center' ? 'justify-center' : ($invoiceTemplate->logo_position == 'right' ? 'justify-end' : '') }}">
                                    @if($invoiceTemplate->logo_path && file_exists(public_path($invoiceTemplate->logo_path)))
                                        <img src="{{ asset($invoiceTemplate->logo_path) }}" alt="Logo" class="h-16 object-contain">
                                    @else
                                        <div class="bg-gradient-to-br from-slate-300 to-slate-400 rounded-lg p-4 flex items-center justify-center">
                                            <span class="text-white font-bold text-xl">LOGO</span>
                                        </div>
                                    @endif
                                </div>
                            
                            @elseif($blockType === 'company_info')
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-2">{{ $invoiceTemplate->company->name ?? 'Your Company Name' }}</h3>
                                    <p class="text-sm text-gray-600">123 Business Street</p>
                                    <p class="text-sm text-gray-600">City, State 12345</p>
                                    <p class="text-sm text-gray-600">Phone: (555) 123-4567</p>
                                    <p class="text-sm text-gray-600">Email: info@company.com</p>
                                </div>
                            
                            @elseif($blockType === 'customer_info')
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-2">BILL TO:</h3>
                                    <p class="text-sm text-gray-900 font-medium">{{ $previewData['customer_name'] ?? 'Customer Name' }}</p>
                                    <p class="text-sm text-gray-600">456 Client Avenue</p>
                                    <p class="text-sm text-gray-600">Client City, State 54321</p>
                                </div>
                            
                            @elseif($blockType === 'invoice_details')
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Invoice Number: <span class="font-medium text-gray-900">{{ $previewData['invoice_number'] ?? 'INV-2024-001' }}</span></p>
                                            <p class="text-sm text-gray-600">Invoice Date: <span class="font-medium text-gray-900">{{ $previewData['invoice_date'] ?? date('F d, Y') }}</span></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">Due Date: <span class="font-medium text-gray-900">{{ $previewData['due_date'] ?? date('F d, Y', strtotime('+30 days')) }}</span></p>
                                            <p class="text-sm text-gray-600">Terms: <span class="font-medium text-gray-900">Net 30</span></p>
                                        </div>
                                    </div>
                                </div>
                            
                            @elseif($blockType === 'invoice_lines')
                                <div>
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b-2 border-gray-200">
                                                <th class="text-left py-2 text-sm font-semibold text-gray-700">Description</th>
                                                <th class="text-center py-2 text-sm font-semibold text-gray-700">Quantity</th>
                                                <th class="text-right py-2 text-sm font-semibold text-gray-700">Rate</th>
                                                <th class="text-right py-2 text-sm font-semibold text-gray-700">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($previewData['invoice_lines'] ?? [] as $line)
                                            <tr class="border-b border-gray-100">
                                                <td class="py-3 text-sm text-gray-900">{{ $line['description'] }}</td>
                                                <td class="py-3 text-sm text-gray-600 text-center">{{ $line['quantity'] }}</td>
                                                <td class="py-3 text-sm text-gray-600 text-right">€{{ number_format($line['rate'], 2) }}</td>
                                                <td class="py-3 text-sm text-gray-900 text-right font-medium">€{{ number_format($line['amount'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            
                            @elseif($blockType === 'totals')
                                <div class="flex justify-end">
                                    <div class="w-64">
                                        <div class="flex justify-between py-2 text-sm">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="text-gray-900">€{{ number_format($previewData['subtotal'] ?? 1500, 2) }}</span>
                                        </div>
                                        @if($invoiceTemplate->show_tax_details ?? false)
                                        <div class="flex justify-between py-2 text-sm">
                                            <span class="text-gray-600">Tax (21%):</span>
                                            <span class="text-gray-900">€{{ number_format($previewData['tax'] ?? 315, 2) }}</span>
                                        </div>
                                        @endif
                                        @if($invoiceTemplate->show_discount_section ?? false)
                                        <div class="flex justify-between py-2 text-sm">
                                            <span class="text-gray-600">Discount:</span>
                                            <span class="text-gray-900">-€{{ number_format($previewData['discount'] ?? 0, 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between py-2 text-base font-semibold border-t-2 border-gray-200 mt-2">
                                            <span class="text-gray-900">Total:</span>
                                            <span class="text-{{ $invoiceTemplate->color_scheme }}-600">€{{ number_format($previewData['total'] ?? 1815, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            
                            @elseif($blockType === 'payment_info' && $invoiceTemplate->show_payment_terms)
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-gray-900 mb-2">Payment Information</h3>
                                    <div class="text-sm text-gray-600">
                                        {!! $invoiceTemplate->payment_terms_text ?? 'Payment is due within 30 days of invoice date. Please include invoice number with payment.' !!}
                                    </div>
                                </div>
                            
                            @elseif($blockType === 'bank_details' && $invoiceTemplate->show_bank_details)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-gray-900 mb-2">Bank Details</h3>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600">Bank Name: <span class="text-gray-900">Example Bank</span></p>
                                            <p class="text-gray-600">Account Name: <span class="text-gray-900">Your Company Name</span></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600">IBAN: <span class="text-gray-900">NL00 BANK 0000 0000 00</span></p>
                                            <p class="text-gray-600">BIC/SWIFT: <span class="text-gray-900">BANKNL2A</span></p>
                                        </div>
                                    </div>
                                </div>
                            
                            @elseif($blockType === 'notes' && $invoiceTemplate->show_notes_section)
                                <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                                    <h3 class="font-semibold text-gray-900 mb-2">Notes</h3>
                                    <p class="text-sm text-gray-600">Thank you for your business! We appreciate your prompt payment.</p>
                                </div>
                            
                            @elseif($blockType === 'footer' && $invoiceTemplate->show_footer)
                                <div class="mt-8 pt-4 border-t border-gray-200 text-center">
                                    <div class="text-xs text-gray-500">
                                        {!! $invoiceTemplate->footer_content ?? '© ' . date('Y') . ' Your Company Name - All rights reserved<br>www.yourcompany.com | info@yourcompany.com' !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    {{-- Fallback to default layout --}}
                    <div class="text-center text-gray-500 py-12">
                        <p>No preview blocks configured for this template.</p>
                        <p class="text-sm mt-2">Please edit the template to configure the layout.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Template Settings Info --}}
    <div class="bg-white px-4 py-3 border-t border-slate-200">
        <div class="grid grid-cols-3 gap-4" style="font-size: calc(var(--theme-font-size) - 2px);">
            <div>
                <span style="color: var(--theme-text-muted);">Type:</span>
                <span style="font-weight: 500; color: var(--theme-text); margin-left: 0.5rem;">{{ ucfirst($invoiceTemplate->template_type ?? 'standard') }}</span>
            </div>
            <div>
                <span style="color: var(--theme-text-muted);">Color Scheme:</span>
                <span class="px-2 py-0.5 bg-{{ $invoiceTemplate->color_scheme }}-100 text-{{ $invoiceTemplate->color_scheme }}-700 rounded ml-2">
                    {{ ucfirst($invoiceTemplate->color_scheme) }}
                </span>
            </div>
            <div>
                <span style="color: var(--theme-text-muted);">Font:</span>
                <span style="font-weight: 500; color: var(--theme-text); margin-left: 0.5rem;">{{ $invoiceTemplate->font_family }}</span>
            </div>
        </div>
    </div>
</div>