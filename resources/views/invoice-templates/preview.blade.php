<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Preview - {{ $invoiceTemplate->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if($invoiceTemplate->font_family !== 'Inter')
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $invoiceTemplate->font_family) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: {{ $invoiceTemplate->font_family }}, sans-serif;
            background-color: white;
            color: #1f2937;
            @if($invoiceTemplate->font_size == 'small')
            font-size: 0.875rem;
            @elseif($invoiceTemplate->font_size == 'large')
            font-size: 1.125rem;
            @else
            font-size: 1rem;
            @endif
        }
        
        /* Utility classes */
        .max-w-4xl { max-width: 56rem; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .p-8 { padding: 2rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .py-0-5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        
        /* Flexbox */
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .justify-end { justify-content: flex-end; }
        .items-center { align-items: center; }
        .items-start { align-items: flex-start; }
        .space-x-2 > * + * { margin-left: 0.5rem; }
        .space-x-4 > * + * { margin-left: 1rem; }
        
        /* Typography */
        .text-2xl { font-size: 1.5rem; line-height: 2rem; }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .font-medium { font-weight: 500; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-700 { color: #374151; }
        .text-gray-900 { color: #111827; }
        .text-white { color: white; }
        
        /* Backgrounds */
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-gray-200 { background-color: #e5e7eb; }
        .bg-gray-50 { background-color: #f9fafb; }
        .bg-gray-600 { background-color: #4b5563; }
        .bg-blue-100 { background-color: #dbeafe; }
        .bg-green-50 { background-color: #f0fdf4; }
        .bg-white { background-color: white; }
        
        /* Borders */
        .border { border-width: 1px; }
        .border-2 { border-width: 2px; }
        .border-b { border-bottom-width: 1px; }
        .border-b-2 { border-bottom-width: 2px; }
        .border-gray-300 { border-color: #d1d5db; }
        .border-gray-200 { border-color: #e5e7eb; }
        .rounded { border-radius: 0.25rem; }
        .rounded-lg { border-radius: 0.5rem; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.75rem; background-color: #f9fafb; font-weight: 600; }
        td { padding: 0.75rem; border-top: 1px solid #e5e7eb; }
        
        /* Buttons */
        button {
            cursor: pointer;
            font-size: 0.875rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        button:hover {
            opacity: 0.8;
        }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .p-8 {
                padding: 1rem;
            }
        }
        
        /* Custom color scheme */
        @if($invoiceTemplate->color_scheme == 'custom')
        .primary-color { color: {{ $invoiceTemplate->primary_color }}; }
        .primary-bg { background-color: {{ $invoiceTemplate->primary_color }}; }
        .primary-border { border-color: {{ $invoiceTemplate->primary_color }}; }
        .secondary-color { color: {{ $invoiceTemplate->secondary_color }}; }
        .secondary-bg { background-color: {{ $invoiceTemplate->secondary_color }}; }
        .accent-color { color: {{ $invoiceTemplate->accent_color }}; }
        @else
            @php
                $colorMap = [
                    'blue' => '#3b82f6',
                    'green' => '#10b981',
                    'red' => '#ef4444',
                    'purple' => '#8b5cf6',
                    'gray' => '#6b7280',
                    'indigo' => '#6366f1',
                    'yellow' => '#f59e0b'
                ];
                $primaryColor = $colorMap[$invoiceTemplate->color_scheme] ?? '#3b82f6';
            @endphp
        .primary-color { color: {{ $primaryColor }}; }
        .primary-bg { background-color: {{ $primaryColor }}; }
        .primary-border { border-color: {{ $primaryColor }}; }
        @endif
        
        /* Layout specific */
        .sticky { position: sticky; }
        .top-0 { top: 0; }
        .w-32 { width: 8rem; }
        .h-32 { height: 8rem; }
        .w-16 { width: 4rem; }
        .h-16 { height: 4rem; }
        .w-80 { width: 20rem; }
        .w-full { width: 100%; }
        
        /* Grid */
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .gap-8 { gap: 2rem; }
        
        /* Additional utility classes */
        .text-blue-700 { color: #1d4ed8; }
        .pb-4 { padding-bottom: 1rem; }
        .text-right { text-align: right; }
        .border-green-500 { border-color: #10b981; }
        .border-red-500 { border-color: #ef4444; }
        .border-purple-500 { border-color: #8b5cf6; }
        .border-indigo-500 { border-color: #6366f1; }
        .border-yellow-500 { border-color: #f59e0b; }
        .border-blue-500 { border-color: #3b82f6; }
        .w-64 { width: 16rem; }
        .py-0\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
    </style>
</head>
<body class="bg-white">
    {{-- Print/Download Actions --}}
    <div class="no-print sticky top-0 bg-gray-100 border-b border-gray-300 px-4 py-2 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Preview Mode</span>
            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded">{{ $invoiceTemplate->name }}</span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                Print Invoice
            </button>
            <button onclick="window.close()" class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>

    {{-- Invoice Content --}}
    <div class="max-w-4xl mx-auto p-8">
        @php
            // Debug output (remove in production)
            if (request()->has('layout_config')) {
                $debugLayout = json_decode(request()->input('layout_config'), true);
                Log::info('Preview debug - Layout config from request:', $debugLayout);
            }
            Log::info('Preview debug - block_positions attribute:', ['value' => $invoiceTemplate->block_positions]);
            
            // Get block positions if available - decode from JSON
            $blocks = [];
            if ($invoiceTemplate->block_positions) {
                $decoded = is_string($invoiceTemplate->block_positions) 
                    ? json_decode($invoiceTemplate->block_positions, true) 
                    : $invoiceTemplate->block_positions;
                
                // Check if we have an array of blocks or if blocks are nested in a 'blocks' key
                if (is_array($decoded)) {
                    if (isset($decoded['blocks']) && is_array($decoded['blocks'])) {
                        $blocks = $decoded['blocks'];
                    } else {
                        $blocks = $decoded;
                    }
                }
                
                Log::info('Preview debug - Decoded blocks:', ['blocks' => $blocks]);
            }
            
            // Only use default blocks if we truly have no blocks
            if (empty($blocks) || !is_array($blocks)) {
                Log::info('Preview debug - Using default blocks because blocks is empty');
                $blocks = [
                    ['type' => 'header'],
                    ['type' => 'company_info'],
                    ['type' => 'customer_info'],
                    ['type' => 'invoice_details'],
                    ['type' => 'project_info'],
                    ['type' => 'line_items'],
                    ['type' => 'additional_costs'],
                    ['type' => 'subtotal'],
                    ['type' => 'tax_section'],
                    ['type' => 'total_section'],
                    ['type' => 'payment_terms'],
                    ['type' => 'bank_details'],
                    ['type' => 'notes'],
                    ['type' => 'footer']
                ];
            }
            
            Log::info('Preview debug - Final blocks to render:', ['count' => count($blocks), 'blocks' => $blocks]);
        @endphp

        @php
            // Group blocks that should appear side by side
            $processedIndexes = [];
        @endphp
        
        @foreach($blocks as $index => $block)
            @if(in_array($index, $processedIndexes))
                @continue
            @endif
            
            @php
                // Check if this block and the next one should be in the same row
                $currentBlockConfig = isset($block['config']) ? $block['config'] : [];
                $nextBlock = isset($blocks[$index + 1]) ? $blocks[$index + 1] : null;
                $nextBlockConfig = $nextBlock && isset($nextBlock['config']) ? $nextBlock['config'] : [];
                
                $shouldCreateRow = false;
                $rowBlocks = [];
                
                // Get position for any block type
                $currentPosition = null;
                if ($block['type'] == 'company_info') {
                    $currentPosition = $currentBlockConfig['company_position'] ?? 'left';
                } elseif ($block['type'] == 'customer_info') {
                    $currentPosition = $currentBlockConfig['customer_position'] ?? 'right';
                } elseif ($block['type'] == 'invoice_details') {
                    $currentPosition = $currentBlockConfig['invoice_details_position'] ?? 'full_width';
                } elseif ($block['type'] == 'project_info') {
                    $currentPosition = $currentBlockConfig['project_info_position'] ?? 'full_width';
                } elseif ($block['type'] == 'bank_details') {
                    $currentPosition = $currentBlockConfig['bank_details_position'] ?? 'full_width';
                } elseif ($block['type'] == 'payment_terms') {
                    $currentPosition = $currentBlockConfig['payment_terms_position'] ?? 'full_width';
                }
                
                // Check if next block exists and get its position
                if ($nextBlock && $currentPosition && $currentPosition != 'full_width' && $currentPosition != 'center') {
                    $nextPosition = null;
                    if ($nextBlock['type'] == 'company_info') {
                        $nextPosition = $nextBlockConfig['company_position'] ?? 'left';
                    } elseif ($nextBlock['type'] == 'customer_info') {
                        $nextPosition = $nextBlockConfig['customer_position'] ?? 'right';
                    } elseif ($nextBlock['type'] == 'invoice_details') {
                        $nextPosition = $nextBlockConfig['invoice_details_position'] ?? 'full_width';
                    } elseif ($nextBlock['type'] == 'project_info') {
                        $nextPosition = $nextBlockConfig['project_info_position'] ?? 'full_width';
                    } elseif ($nextBlock['type'] == 'bank_details') {
                        $nextPosition = $nextBlockConfig['bank_details_position'] ?? 'full_width';
                    } elseif ($nextBlock['type'] == 'payment_terms') {
                        $nextPosition = $nextBlockConfig['payment_terms_position'] ?? 'full_width';
                    }
                    
                    // Create row if one is left and other is right (and neither is full_width or center)
                    if ($nextPosition && $nextPosition != 'full_width' && $nextPosition != 'center') {
                        if (($currentPosition == 'left' && $nextPosition == 'right') || 
                            ($currentPosition == 'right' && $nextPosition == 'left')) {
                            $shouldCreateRow = true;
                            $rowBlocks = [$block, $nextBlock];
                            $processedIndexes[] = $index + 1;
                        }
                    }
                }
            @endphp
            
            @if($shouldCreateRow && count($rowBlocks) == 2)
                {{-- Render two blocks side by side --}}
                <div class="grid grid-cols-2 gap-8 mb-8">
                    @foreach($rowBlocks as $rowBlock)
                        @if($rowBlock['type'] == 'company_info')
                            @php
                                $companyConfig = isset($rowBlock['config']) ? $rowBlock['config'] : [];
                                $showSectionTitle = $companyConfig['show_section_title'] ?? true;
                                $companySectionTitle = $companyConfig['company_section_title'] ?? 'From:';
                                $companyAddressFormat = $companyConfig['address_format'] ?? 'multi_line';
                                $companyVatLabel = $companyConfig['custom_label_vat'] ?? 'VAT:';
                                $companyCocLabel = $companyConfig['custom_label_coc'] ?? 'CoC:';
                                $showCompanyVat = $companyConfig['show_vat'] ?? true;
                                $showCompanyCoc = $companyConfig['show_coc'] ?? true;
                                $showCompanyEmail = $companyConfig['show_email'] ?? true;
                                $showCompanyPhone = $companyConfig['show_phone'] ?? true;
                                $showCompanyWebsite = $companyConfig['show_website'] ?? false;
                            @endphp
                            <div>
                                @if($showSectionTitle)
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">{{ $companySectionTitle }}</h3>
                                @endif
                                <div class="text-gray-600">
                                    <p class="font-semibold text-lg text-gray-900">{{ $previewData['company']['name'] }}</p>
                                    
                                    @if($companyAddressFormat == 'single_line')
                                        <p>{{ $previewData['company']['address'] }}, {{ $previewData['company']['zip_code'] }} {{ $previewData['company']['city'] }} {{ $previewData['company']['country'] }}</p>
                                    @elseif($companyAddressFormat == 'formal')
                                        <p>{{ $previewData['company']['address'] }}</p>
                                        <p>{{ $previewData['company']['zip_code'] }} {{ $previewData['company']['city'] }}</p>
                                        <p>{{ $previewData['company']['country'] }}</p>
                                    @else
                                        <p>{{ $previewData['company']['address'] }}</p>
                                        <p>{{ $previewData['company']['city'] }}</p>
                                        <p>{{ $previewData['company']['country'] }}</p>
                                    @endif
                                    
                                    <div class="mt-3 space-y-1 text-sm">
                                        @if($showCompanyEmail)
                                        <p><span class="text-gray-500">Email:</span> {{ $previewData['company']['email'] }}</p>
                                        @endif
                                        @if($showCompanyPhone)
                                        <p><span class="text-gray-500">Phone:</span> {{ $previewData['company']['phone'] }}</p>
                                        @endif
                                        @if($showCompanyWebsite && isset($previewData['company']['website']))
                                        <p><span class="text-gray-500">Website:</span> {{ $previewData['company']['website'] }}</p>
                                        @endif
                                        @if($showCompanyVat)
                                        <p><span class="text-gray-500">{{ $companyVatLabel }}</span> {{ $previewData['company']['vat_number'] }}</p>
                                        @endif
                                        @if($showCompanyCoc)
                                        <p><span class="text-gray-500">{{ $companyCocLabel }}</span> {{ $previewData['company']['kvk_number'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($rowBlock['type'] == 'customer_info')
                            @php
                                $customerConfig = isset($rowBlock['config']) ? $rowBlock['config'] : [];
                                // Support both old field name (customer_section_title) and new field name (section_title)
                                $customerSectionTitle = $customerConfig['section_title'] ?? $customerConfig['customer_section_title'] ?? 'Bill To:';
                                $showSectionTitle = $customerConfig['show_section_title'] ?? true;
                                $customerAddressFormat = $customerConfig['address_format'] ?? 'multi_line';
                                $customerVatLabel = $customerConfig['custom_label_vat'] ?? 'VAT:';
                                $customerCocLabel = $customerConfig['custom_label_coc'] ?? 'CoC:';
                                $attnLabel = $customerConfig['attn_label'] ?? 'Attn:';
                                $attnPosition = $customerConfig['attn_position'] ?? 'bottom';
                                $showCustomerCompany = $customerConfig['show_customer_company'] ?? true;
                                $showCustomerVat = $customerConfig['show_customer_vat'] ?? false;
                                $showCustomerCoc = $customerConfig['show_customer_coc'] ?? false;
                                $showCustomerEmail = $customerConfig['show_customer_email'] ?? true;
                                $showCustomerPhone = $customerConfig['show_customer_phone'] ?? false;
                            @endphp
                            <div>
                                @if($showSectionTitle)
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">{{ $customerSectionTitle }}</h3>
                                @endif
                                <div class="text-gray-600">
                                    <p class="font-semibold text-lg text-gray-900">{{ $previewData['customer']['name'] }}</p>
                                    
                                    @if($showCustomerCompany && $previewData['customer']['company'])
                                    <p class="font-medium">{{ $previewData['customer']['company'] }}</p>
                                    @endif
                                    
                                    @if($attnPosition == 'after_company' && $previewData['customer']['contact_person'] && $attnPosition != 'hide')
                                    <p class="text-sm"><span class="text-gray-500">{{ $attnLabel }}</span> {{ $previewData['customer']['contact_person'] }}</p>
                                    @endif
                                    
                                    @if($customerAddressFormat == 'single_line')
                                        <p>{{ $previewData['customer']['address'] }}, {{ $previewData['customer']['zip_code'] }} {{ $previewData['customer']['city'] }} {{ $previewData['customer']['country'] }}</p>
                                    @elseif($customerAddressFormat == 'formal')
                                        <p>{{ $previewData['customer']['address'] }}</p>
                                        <p>{{ $previewData['customer']['zip_code'] }} {{ $previewData['customer']['city'] }}</p>
                                        <p>{{ $previewData['customer']['country'] }}</p>
                                    @else
                                        <p>{{ $previewData['customer']['address'] }}</p>
                                        <p>{{ $previewData['customer']['city'] }}</p>
                                        <p>{{ $previewData['customer']['country'] }}</p>
                                    @endif
                                    
                                    @php
                                        $showBottomSection = ($attnPosition == 'bottom' && $previewData['customer']['contact_person'] && $attnPosition != 'hide') 
                                                            || $showCustomerEmail || $showCustomerPhone || $showCustomerVat || $showCustomerCoc;
                                    @endphp
                                    
                                    @if($showBottomSection)
                                    <div class="mt-3 space-y-1 text-sm">
                                        @if($attnPosition == 'bottom' && $previewData['customer']['contact_person'] && $attnPosition != 'hide')
                                        <p><span class="text-gray-500">{{ $attnLabel }}</span> {{ $previewData['customer']['contact_person'] }}</p>
                                        @endif
                                        @if($showCustomerEmail)
                                        <p><span class="text-gray-500">Email:</span> {{ $previewData['customer']['email'] }}</p>
                                        @endif
                                        @if($showCustomerPhone && isset($previewData['customer']['phone']))
                                        <p><span class="text-gray-500">Phone:</span> {{ $previewData['customer']['phone'] }}</p>
                                        @endif
                                        @if($showCustomerVat && isset($previewData['customer']['vat_number']))
                                        <p><span class="text-gray-500">{{ $customerVatLabel }}</span> {{ $previewData['customer']['vat_number'] }}</p>
                                        @endif
                                        @if($showCustomerCoc && isset($previewData['customer']['kvk_number']))
                                        <p><span class="text-gray-500">{{ $customerCocLabel }}</span> {{ $previewData['customer']['kvk_number'] }}</p>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($rowBlock['type'] == 'invoice_details')
                            @php
                                $detailsConfig = isset($rowBlock['config']) ? $rowBlock['config'] : [];
                                $showInvoiceDate = $detailsConfig['show_invoice_date'] ?? true;
                                $showDueDate = $detailsConfig['show_due_date'] ?? true;
                                $showStatus = $detailsConfig['show_status'] ?? true;
                                $showPaymentTerms = $detailsConfig['show_payment_terms'] ?? false;
                                $showReference = $detailsConfig['show_reference'] ?? false;
                            @endphp
                            <div class="@if($invoiceTemplate->color_scheme == 'custom') bg-gray-50 @else bg-{{ $invoiceTemplate->color_scheme }}-50 @endif rounded-lg p-4">
                                <div class="space-y-3">
                                    @if($showInvoiceDate || $showDueDate)
                                    <div class="grid grid-cols-2 gap-4">
                                        @if($showInvoiceDate)
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500">Invoice Date</p>
                                            <p class="font-semibold text-gray-900">{{ date('M d, Y', strtotime($previewData['invoice']['invoice_date'])) }}</p>
                                        </div>
                                        @endif
                                        @if($showDueDate)
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500">Due Date</p>
                                            <p class="font-semibold text-gray-900">{{ date('M d, Y', strtotime($previewData['invoice']['due_date'])) }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-gray-500">Invoice Number</p>
                                        <p class="font-bold text-gray-900">{{ $previewData['invoice']['invoice_number'] }}</p>
                                    </div>
                                    
                                    @if($showStatus)
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-gray-500">Status</p>
                                        <p class="font-bold @if($invoiceTemplate->color_scheme == 'custom') primary-color @else text-{{ $invoiceTemplate->color_scheme }}-600 @endif uppercase">
                                            {{ $previewData['invoice']['status'] }}
                                        </p>
                                    </div>
                                    @endif
                                    
                                    @if($showPaymentTerms)
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-gray-500">Payment Terms</p>
                                        <p class="text-gray-900">Net 30 Days</p>
                                    </div>
                                    @endif
                                    
                                    @if($showReference && isset($previewData['invoice']['reference']))
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-gray-500">Reference</p>
                                        <p class="text-gray-900">{{ $previewData['invoice']['reference'] }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                {{-- Render single block --}}
                @switch($block['type'])
                @case('header')
                    @php
                        $blockConfig = isset($block['config']) ? $block['config'] : [];
                        $headerStyle = $blockConfig['header_style'] ?? 'standard';
                        $invoiceTitle = $blockConfig['invoice_title'] ?? 'INVOICE';
                        $headerText = $blockConfig['header_text'] ?? '';
                        $showLogo = $blockConfig['show_logo'] ?? true;
                        $useTemplateLogo = $blockConfig['use_template_logo'] ?? false;
                        $logoPosition = $blockConfig['logo_position'] ?? 'left';
                        $logoFullWidth = $blockConfig['logo_full_width'] ?? false;
                        $showInvoiceNumber = $blockConfig['show_invoice_number'] ?? true;
                        $showDate = $blockConfig['show_date'] ?? false;
                        
                        // Determine if we should actually show the logo
                        $shouldShowLogo = $showLogo && (!$useTemplateLogo || ($useTemplateLogo && $invoiceTemplate->logo_path));
                        
                        // Debug block configuration
                        echo "<!-- Debug Block Config: " . json_encode($blockConfig) . " -->";
                        echo "<!-- Debug showLogo: " . ($showLogo ? 'true' : 'false') . " -->";
                        echo "<!-- Debug useTemplateLogo: " . ($useTemplateLogo ? 'true' : 'false') . " -->";
                        echo "<!-- Debug shouldShowLogo: " . ($shouldShowLogo ? 'true' : 'false') . " -->";
                        echo "<!-- Debug logo_position: " . ($invoiceTemplate->logo_position ?? 'NOT SET') . " -->";
                        echo "<!-- Debug logo_path: " . ($invoiceTemplate->logo_path ?? 'NOT SET') . " -->";
                    @endphp
                    
                    @if($invoiceTemplate->show_header)
                    <div class="mb-8">
                        {{-- Full Width Logo Display --}}
                        @if($shouldShowLogo && ($logoPosition === 'full-width' || $logoFullWidth))
                        <div class="mb-6">
                            @if($invoiceTemplate->logo_path)
                                @php
                                    $logoPathFull = $invoiceTemplate->logo_path;
                                    if (strpos($logoPathFull, 'logos/') === 0) {
                                        $logoPathFull = substr($logoPathFull, 6);
                                    }
                                @endphp
                                <img src="{{ url('template-logo/' . $logoPathFull) }}" 
                                     alt="Company Logo" 
                                     class="{{ $logoFullWidth ? 'w-full h-auto' : 'w-full h-32 object-contain' }}"
                                     style="{{ $logoFullWidth ? '' : 'max-height: 8rem;' }}">
                            @else
                                <div class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        @endif
                        
                        @if($headerStyle == 'centered')
                        {{-- Centered Style --}}
                        <div class="text-center border-b-2 @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-500 @endif pb-4">
                            @if($shouldShowLogo && $invoiceTemplate->logo_position !== 'none' && $logoPosition !== 'full-width' && !$logoFullWidth)
                            <div class="mb-4">
                                <!-- Debug: Logo Path = {{ $invoiceTemplate->logo_path ?? 'NULL' }} -->
                                <!-- Debug: Full URL = {{ $invoiceTemplate->logo_path ? url('template-logo/' . str_replace('logos/', '', $invoiceTemplate->logo_path)) : 'NO PATH' }} -->
                                @if($invoiceTemplate->logo_path)
                                    @php
                                        // Handle both cases: with or without 'logos/' prefix
                                        $logoPath = $invoiceTemplate->logo_path;
                                        if (strpos($logoPath, 'logos/') === 0) {
                                            $logoPath = substr($logoPath, 6); // Remove 'logos/' prefix
                                        }
                                    @endphp
                                    <img src="{{ url('template-logo/' . $logoPath) }}" 
                                         alt="Company Logo" 
                                         class="h-32 mx-auto object-contain"
                                         onerror="console.error('Logo failed to load:', this.src); this.style.border='2px solid red';">
                                @else
                                    <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center mx-auto">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            @endif
                            <h1 class="text-4xl font-bold @if($invoiceTemplate->color_scheme == 'custom') primary-color @else text-{{ $invoiceTemplate->color_scheme }}-600 @endif">{{ $invoiceTitle }}</h1>
                            @if($showInvoiceNumber)
                            <p class="text-gray-500 mt-1">#{{ $previewData['invoice']['invoice_number'] }}</p>
                            @endif
                            @if($showDate)
                            <p class="text-gray-500 text-sm">{{ date('M d, Y', strtotime($previewData['invoice']['invoice_date'])) }}</p>
                            @endif
                            @if($headerText)
                            <p class="text-gray-600 mt-2">{{ $headerText }}</p>
                            @endif
                        </div>
                        
                        @elseif($headerStyle == 'minimal')
                        {{-- Minimal Style --}}
                        <div class="border-b @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-300 @endif pb-2 mb-4">
                            <div class="flex justify-between items-center">
                                <h1 class="text-2xl font-semibold text-gray-800">{{ $invoiceTitle }}</h1>
                                @if($showInvoiceNumber)
                                <span class="text-gray-600">#{{ $previewData['invoice']['invoice_number'] }}</span>
                                @endif
                            </div>
                            @if($headerText)
                            <p class="text-gray-600 text-sm mt-1">{{ $headerText }}</p>
                            @endif
                        </div>
                        
                        @elseif($headerStyle == 'modern')
                        {{-- Modern Style with Background --}}
                        <div class="@if($invoiceTemplate->color_scheme == 'custom') primary-bg @else bg-{{ $invoiceTemplate->color_scheme }}-600 @endif text-white rounded-t-lg p-6 mb-6">
                            <div class="flex justify-between items-start">
                                @if($shouldShowLogo && $invoiceTemplate->logo_position !== 'none' && $logoPosition !== 'full-width' && !$logoFullWidth)
                                    @if($invoiceTemplate->logo_path)
                                        @php
                                            $logoPath2 = $invoiceTemplate->logo_path;
                                            if (strpos($logoPath2, 'logos/') === 0) {
                                                $logoPath2 = substr($logoPath2, 6);
                                            }
                                        @endphp
                                        <img src="{{ url('template-logo/' . $logoPath2) }}" 
                                             alt="Company Logo" 
                                             class="h-24 object-contain bg-white/10 rounded-lg p-2">
                                    @else
                                        <div class="w-24 h-24 bg-white/20 rounded-lg flex items-center justify-center">
                                            <svg class="w-12 h-12 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    @endif
                                @endif
                                <div class="text-right">
                                    <h1 class="text-3xl font-bold">{{ $invoiceTitle }}</h1>
                                    @if($showInvoiceNumber)
                                    <p class="text-white/90 mt-1">#{{ $previewData['invoice']['invoice_number'] }}</p>
                                    @endif
                                    @if($showDate)
                                    <p class="text-white/80 text-sm mt-1">{{ date('M d, Y', strtotime($previewData['invoice']['invoice_date'])) }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($headerText)
                            <p class="text-white/90 mt-4">{{ $headerText }}</p>
                            @endif
                        </div>
                        
                        @else
                        {{-- Standard Style (default) --}}
                        <div class="border-b-2 @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-500 @endif pb-4">
                            <div class="flex justify-between items-start">
                                @if($shouldShowLogo && $invoiceTemplate->logo_position !== 'none' && $logoPosition !== 'full-width' && !$logoFullWidth)
                                    @if($invoiceTemplate->logo_path)
                                        @php
                                            $logoPath3 = $invoiceTemplate->logo_path;
                                            if (strpos($logoPath3, 'logos/') === 0) {
                                                $logoPath3 = substr($logoPath3, 6);
                                            }
                                        @endphp
                                        <img src="{{ url('template-logo/' . $logoPath3) }}" 
                                             alt="Company Logo" 
                                             class="h-32 object-contain">
                                    @else
                                        <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    @endif
                                @endif
                                <div class="text-right">
                                    <h1 class="text-4xl font-bold @if($invoiceTemplate->color_scheme == 'custom') primary-color @else text-{{ $invoiceTemplate->color_scheme }}-600 @endif">{{ $invoiceTitle }}</h1>
                                    @if($showInvoiceNumber)
                                    <p class="text-gray-500 mt-1">#{{ $previewData['invoice']['invoice_number'] }}</p>
                                    @endif
                                    @if($showDate)
                                    <p class="text-gray-500 text-sm">{{ date('M d, Y', strtotime($previewData['invoice']['invoice_date'])) }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($headerText)
                            <p class="text-gray-600 mt-3">{{ $headerText }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                    @break

                @case('company_info')
                    @php
                        $companyConfig = isset($block['config']) ? $block['config'] : [];
                        $companyPosition = $companyConfig['company_position'] ?? 'left';
                        $showSectionTitle = $companyConfig['show_section_title'] ?? true;
                        $companySectionTitle = $companyConfig['company_section_title'] ?? 'From:';
                        $companyAddressFormat = $companyConfig['address_format'] ?? 'multi_line';
                        $companyVatLabel = $companyConfig['custom_label_vat'] ?? 'VAT:';
                        $companyCocLabel = $companyConfig['custom_label_coc'] ?? 'CoC:';
                        $showCompanyVat = $companyConfig['show_vat'] ?? true;
                        $showCompanyCoc = $companyConfig['show_coc'] ?? true;
                        $showCompanyEmail = $companyConfig['show_email'] ?? true;
                        $showCompanyPhone = $companyConfig['show_phone'] ?? true;
                        $showCompanyWebsite = $companyConfig['show_website'] ?? false;
                        
                        // Determine container classes based on position
                        $containerClass = 'mb-8';
                        $contentClass = '';
                        
                        if ($companyPosition == 'center') {
                            $containerClass .= ' text-center';
                            $contentClass = 'mx-auto max-w-md';
                        } elseif ($companyPosition == 'right') {
                            $containerClass .= ' text-right';
                        } elseif ($companyPosition == 'full_width') {
                            $contentClass = 'w-full';
                        }
                    @endphp
                    
                    <div class="{{ $containerClass }}">
                        <div class="{{ $contentClass }}">
                            @if($showSectionTitle)
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">{{ $companySectionTitle }}</h3>
                            @endif
                            <div class="text-gray-600">
                                <p class="font-semibold text-lg text-gray-900">{{ $previewData['company']['name'] }}</p>
                                
                                @if($companyAddressFormat == 'single_line')
                                    <p>{{ $previewData['company']['address'] }}, {{ $previewData['company']['zip_code'] }} {{ $previewData['company']['city'] }} {{ $previewData['company']['country'] }}</p>
                                @elseif($companyAddressFormat == 'formal')
                                    <p>{{ $previewData['company']['address'] }}</p>
                                    <p>{{ $previewData['company']['zip_code'] }} {{ $previewData['company']['city'] }}</p>
                                    <p>{{ $previewData['company']['country'] }}</p>
                                @else
                                    {{-- multi_line (default) --}}
                                    <p>{{ $previewData['company']['address'] }}</p>
                                    <p>{{ $previewData['company']['city'] }}</p>
                                    <p>{{ $previewData['company']['country'] }}</p>
                                @endif
                                
                                <div class="mt-3 space-y-1 text-sm">
                                    @if($showCompanyEmail)
                                    <p><span class="text-gray-500">Email:</span> {{ $previewData['company']['email'] }}</p>
                                    @endif
                                    @if($showCompanyPhone)
                                    <p><span class="text-gray-500">Phone:</span> {{ $previewData['company']['phone'] }}</p>
                                    @endif
                                    @if($showCompanyWebsite && isset($previewData['company']['website']))
                                    <p><span class="text-gray-500">Website:</span> {{ $previewData['company']['website'] }}</p>
                                    @endif
                                    @if($showCompanyVat)
                                    <p><span class="text-gray-500">{{ $companyVatLabel }}</span> {{ $previewData['company']['vat_number'] }}</p>
                                    @endif
                                    @if($showCompanyCoc)
                                    <p><span class="text-gray-500">{{ $companyCocLabel }}</span> {{ $previewData['company']['kvk_number'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
                    
                @case('customer_info')
                    @php
                        $customerConfig = isset($block['config']) ? $block['config'] : [];
                        $customerPosition = $customerConfig['customer_position'] ?? 'right';
                        // Support both old field name (customer_section_title) and new field name (section_title)
                        $customerSectionTitle = $customerConfig['section_title'] ?? $customerConfig['customer_section_title'] ?? 'Bill To:';
                        $showSectionTitle = $customerConfig['show_section_title'] ?? true;
                        $customerAddressFormat = $customerConfig['address_format'] ?? 'multi_line';
                        $customerVatLabel = $customerConfig['custom_label_vat'] ?? 'VAT:';
                        $customerCocLabel = $customerConfig['custom_label_coc'] ?? 'CoC:';
                        $attnLabel = $customerConfig['attn_label'] ?? 'Attn:';
                        $attnPosition = $customerConfig['attn_position'] ?? 'bottom';
                        $showCustomerCompany = $customerConfig['show_customer_company'] ?? true;
                        $showCustomerVat = $customerConfig['show_customer_vat'] ?? false;
                        $showCustomerCoc = $customerConfig['show_customer_coc'] ?? false;
                        $showCustomerEmail = $customerConfig['show_customer_email'] ?? true;
                        $showCustomerPhone = $customerConfig['show_customer_phone'] ?? false;
                        
                        // Determine container classes based on position
                        $containerClass = 'mb-8';
                        $contentClass = '';
                        
                        if ($customerPosition == 'center') {
                            $containerClass .= ' text-center';
                            $contentClass = 'mx-auto max-w-md';
                        } elseif ($customerPosition == 'right') {
                            $containerClass .= ' text-right';
                        } elseif ($customerPosition == 'full_width') {
                            $contentClass = 'w-full';
                        }
                    @endphp
                    
                    <div class="{{ $containerClass }}">
                        <div class="{{ $contentClass }}">
                            @if($showSectionTitle)
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">{{ $customerSectionTitle }}</h3>
                            @endif
                            <div class="text-gray-600">
                                <p class="font-semibold text-lg text-gray-900">{{ $previewData['customer']['name'] }}</p>
                                
                                @if($showCustomerCompany && $previewData['customer']['company'])
                                <p class="font-medium">{{ $previewData['customer']['company'] }}</p>
                                @endif
                                
                                {{-- Show Attn after company if configured --}}
                                @if($attnPosition == 'after_company' && $previewData['customer']['contact_person'] && $attnPosition != 'hide')
                                <p class="text-sm"><span class="text-gray-500">{{ $attnLabel }}</span> {{ $previewData['customer']['contact_person'] }}</p>
                                @endif
                                
                                @if($customerAddressFormat == 'single_line')
                                    <p>{{ $previewData['customer']['address'] }}, {{ $previewData['customer']['zip_code'] }} {{ $previewData['customer']['city'] }} {{ $previewData['customer']['country'] }}</p>
                                @elseif($customerAddressFormat == 'formal')
                                    <p>{{ $previewData['customer']['address'] }}</p>
                                    <p>{{ $previewData['customer']['zip_code'] }} {{ $previewData['customer']['city'] }}</p>
                                    <p>{{ $previewData['customer']['country'] }}</p>
                                @else
                                    {{-- multi_line (default) --}}
                                    <p>{{ $previewData['customer']['address'] }}</p>
                                    <p>{{ $previewData['customer']['city'] }}</p>
                                    <p>{{ $previewData['customer']['country'] }}</p>
                                @endif
                                
                                @php
                                    $showBottomSection = ($attnPosition == 'bottom' && $previewData['customer']['contact_person'] && $attnPosition != 'hide') 
                                                        || $showCustomerEmail || $showCustomerPhone || $showCustomerVat || $showCustomerCoc;
                                @endphp
                                
                                @if($showBottomSection)
                                <div class="mt-3 space-y-1 text-sm">
                                    {{-- Show Attn at bottom if configured --}}
                                    @if($attnPosition == 'bottom' && $previewData['customer']['contact_person'] && $attnPosition != 'hide')
                                    <p><span class="text-gray-500">{{ $attnLabel }}</span> {{ $previewData['customer']['contact_person'] }}</p>
                                    @endif
                                    @if($showCustomerEmail)
                                    <p><span class="text-gray-500">Email:</span> {{ $previewData['customer']['email'] }}</p>
                                    @endif
                                    @if($showCustomerPhone && isset($previewData['customer']['phone']))
                                    <p><span class="text-gray-500">Phone:</span> {{ $previewData['customer']['phone'] }}</p>
                                    @endif
                                    @if($showCustomerVat && isset($previewData['customer']['vat_number']))
                                    <p><span class="text-gray-500">{{ $customerVatLabel }}</span> {{ $previewData['customer']['vat_number'] }}</p>
                                    @endif
                                    @if($showCustomerCoc && isset($previewData['customer']['kvk_number']))
                                    <p><span class="text-gray-500">{{ $customerCocLabel }}</span> {{ $previewData['customer']['kvk_number'] }}</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @break

                @case('invoice_details')
                    <div class="@if($invoiceTemplate->color_scheme == 'custom') bg-gray-50 @else bg-{{ $invoiceTemplate->color_scheme }}-50 @endif rounded-lg p-4 mb-8">
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-500">Invoice Number</p>
                                <p class="font-bold text-gray-900">{{ $previewData['invoice']['invoice_number'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-500">Invoice Date</p>
                                <p class="font-semibold text-gray-900">{{ date('M d, Y', strtotime($previewData['invoice']['invoice_date'])) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-500">Due Date</p>
                                <p class="font-semibold text-gray-900">{{ date('M d, Y', strtotime($previewData['invoice']['due_date'])) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-gray-500">Status</p>
                                <p class="font-bold @if($invoiceTemplate->color_scheme == 'custom') primary-color @else text-{{ $invoiceTemplate->color_scheme }}-600 @endif uppercase">
                                    {{ $previewData['invoice']['status'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @break

                @case('project_info')
                    @if($invoiceTemplate->show_project_details)
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Project Details</h3>
                        <div class="bg-gray-50 rounded p-3">
                            <p class="font-semibold text-gray-900">{{ $previewData['project']['name'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $previewData['project']['description'] }}</p>
                        </div>
                    </div>
                    @endif
                    @break

                @case('line_items')
                    @php
                        $lineConfig = isset($block['config']) ? $block['config'] : [];
                        $lineStyle = $lineConfig['line_items_style'] ?? 'detailed';
                        $groupByMilestone = $lineConfig['group_by_milestone'] ?? false;
                        $showTaskDesc = $lineConfig['show_task_descriptions'] ?? true;
                        $showSubtasks = $lineConfig['show_subtasks'] ?? false;
                        $showHours = $lineConfig['show_hours'] ?? true;
                        $showRate = $lineConfig['show_rate'] ?? true;
                        $showUnit = $lineConfig['show_unit'] ?? false;
                        $descLabel = $lineConfig['desc_label'] ?? 'Description';
                        $qtyLabel = $lineConfig['qty_label'] ?? 'Qty';
                        $rateLabel = $lineConfig['rate_label'] ?? 'Rate';
                        $amountLabel = $lineConfig['amount_label'] ?? 'Amount';
                        $hoursLabel = $lineConfig['hours_label'] ?? 'Hours';
                        $unitLabel = $lineConfig['unit_label'] ?? 'Unit';
                    @endphp
                    
                    <div class="mb-8">
                        @if($lineStyle == 'minimal')
                        {{-- Minimal Style --}}
                        <table class="w-full">
                            <thead>
                                <tr class="border-b @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-200 @endif">
                                    <th class="text-left py-2 text-xs text-gray-600">{{ $descLabel }}</th>
                                    <th class="text-right py-2 text-xs text-gray-600">{{ $amountLabel }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewData['line_items'] as $item)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 text-gray-900">{{ $item['description'] }}</td>
                                    <td class="py-2 text-right text-gray-900">€{{ number_format($item['total'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        @elseif($lineStyle == 'grouped')
                        {{-- Grouped by Category/Milestone Style --}}
                        @php
                            $groupedItems = [];
                            foreach($previewData['line_items'] as $item) {
                                $group = isset($item['category']) ? $item['category'] : 'General';
                                if (!isset($groupedItems[$group])) {
                                    $groupedItems[$group] = [];
                                }
                                $groupedItems[$group][] = $item;
                            }
                        @endphp
                        
                        @foreach($groupedItems as $groupName => $items)
                        <div class="mb-4">
                            <h4 class="text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">{{ $groupName }}</h4>
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-200 @endif">
                                        <th class="text-left py-2 text-xs text-gray-600">{{ $descLabel }}</th>
                                        @if($showHours)
                                        <th class="text-right py-2 text-xs text-gray-600">{{ $hoursLabel }}</th>
                                        @endif
                                        @if($showRate)
                                        <th class="text-right py-2 text-xs text-gray-600">{{ $rateLabel }}</th>
                                        @endif
                                        <th class="text-right py-2 text-xs text-gray-600">{{ $amountLabel }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3 text-gray-900">
                                            <div>
                                                <p class="font-medium">{{ $item['description'] }}</p>
                                                @if($showTaskDesc && isset($item['details']))
                                                <p class="text-sm text-gray-500 mt-1">{{ $item['details'] }}</p>
                                                @endif
                                                @if($showSubtasks && isset($item['subtasks']))
                                                <div class="ml-4 mt-1 text-xs text-gray-500">
                                                    @foreach($item['subtasks'] as $subtask)
                                                    <p>• {{ $subtask }}</p>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                        @if($showHours)
                                        <td class="py-3 text-right text-gray-900">{{ number_format($item['quantity'], 2) }}</td>
                                        @endif
                                        @if($showRate)
                                        <td class="py-3 text-right text-gray-900">€{{ number_format($item['unit_price'], 2) }}</td>
                                        @endif
                                        <td class="py-3 text-right font-semibold text-gray-900">€{{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endforeach
                        
                        @elseif($lineStyle == 'modern')
                        {{-- Modern Style with Colored Headers --}}
                        <div class="overflow-hidden rounded-lg border @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-200 @endif">
                            <table class="w-full">
                                <thead class="@if($invoiceTemplate->color_scheme == 'custom') primary-bg @else bg-{{ $invoiceTemplate->color_scheme }}-500 @endif text-white">
                                    <tr>
                                        <th class="text-left py-3 px-4 text-xs uppercase tracking-wider">{{ $descLabel }}</th>
                                        @if($showUnit)
                                        <th class="text-center py-3 px-4 text-xs uppercase tracking-wider">{{ $unitLabel }}</th>
                                        @endif
                                        <th class="text-right py-3 px-4 text-xs uppercase tracking-wider">{{ $qtyLabel }}</th>
                                        @if($showRate)
                                        <th class="text-right py-3 px-4 text-xs uppercase tracking-wider">{{ $rateLabel }}</th>
                                        @endif
                                        <th class="text-right py-3 px-4 text-xs uppercase tracking-wider">{{ $amountLabel }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewData['line_items'] as $index => $item)
                                    <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                                        <td class="py-3 px-4 text-gray-900">
                                            <p class="font-medium">{{ $item['description'] }}</p>
                                            @if($showTaskDesc && isset($item['details']))
                                            <p class="text-sm text-gray-500 mt-1">{{ $item['details'] }}</p>
                                            @endif
                                        </td>
                                        @if($showUnit)
                                        <td class="py-3 px-4 text-center text-gray-900">{{ isset($item['unit']) ? $item['unit'] : 'hours' }}</td>
                                        @endif
                                        <td class="py-3 px-4 text-right text-gray-900">{{ number_format($item['quantity'], 2) }}</td>
                                        @if($showRate)
                                        <td class="py-3 px-4 text-right text-gray-900">€{{ number_format($item['unit_price'], 2) }}</td>
                                        @endif
                                        <td class="py-3 px-4 text-right font-bold @if($invoiceTemplate->color_scheme == 'custom') primary-color @else text-{{ $invoiceTemplate->color_scheme }}-600 @endif">€{{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @else
                        {{-- Detailed Style (default) --}}
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-200 @endif">
                                    <th class="text-left py-3 text-xs uppercase tracking-wider text-gray-700">{{ $descLabel }}</th>
                                    @if($showUnit)
                                    <th class="text-center py-3 text-xs uppercase tracking-wider text-gray-700">{{ $unitLabel }}</th>
                                    @endif
                                    <th class="text-right py-3 text-xs uppercase tracking-wider text-gray-700">{{ $qtyLabel }}</th>
                                    @if($showRate)
                                    <th class="text-right py-3 text-xs uppercase tracking-wider text-gray-700">{{ $rateLabel }}</th>
                                    @endif
                                    <th class="text-right py-3 text-xs uppercase tracking-wider text-gray-700">{{ $amountLabel }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewData['line_items'] as $index => $item)
                                <tr class="border-b border-gray-200 {{ $index % 2 == 0 ? 'bg-gray-50/50' : '' }}">
                                    <td class="py-4 text-gray-900">
                                        <div>
                                            <p class="font-medium">{{ $item['description'] }}</p>
                                            @if($showTaskDesc && isset($item['details']))
                                            <p class="text-sm text-gray-500 mt-1">{{ $item['details'] }}</p>
                                            @endif
                                            @if($showSubtasks && isset($item['subtasks']))
                                            <div class="ml-4 mt-2 space-y-1">
                                                @foreach($item['subtasks'] as $subtask)
                                                <p class="text-xs text-gray-500">• {{ $subtask }}</p>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    @if($showUnit)
                                    <td class="py-4 text-center text-gray-900 text-sm">{{ isset($item['unit']) ? $item['unit'] : 'hours' }}</td>
                                    @endif
                                    <td class="py-4 text-right text-gray-900">{{ number_format($item['quantity'], 2) }}</td>
                                    @if($showRate)
                                    <td class="py-4 text-right text-gray-900">€{{ number_format($item['unit_price'], 2) }}</td>
                                    @endif
                                    <td class="py-4 text-right font-semibold text-gray-900">€{{ number_format($item['total'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                    @break

                @case('additional_costs')
                    @if($invoiceTemplate->show_additional_costs_section && isset($previewData['additional_costs']) && count($previewData['additional_costs']) > 0)
                    <div class="mb-8 bg-yellow-50 rounded-lg p-4">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Additional Costs</h3>
                        <table class="w-full">
                            @foreach($previewData['additional_costs'] ?? [] as $cost)
                            <tr class="border-b border-yellow-200 last:border-0">
                                <td class="py-2 text-gray-900">{{ $cost['description'] }}</td>
                                <td class="py-2 text-right font-medium text-gray-900">€{{ number_format($cost['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                    @endif
                    @break

                @case('subtotal')
                @case('tax_section')
                @case('total')
                @case('total_section')
                    <div class="flex justify-end mb-8">
                        <div class="w-80">
                            @if($invoiceTemplate->show_subtotals)
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold text-gray-900">€{{ number_format($previewData['invoice']['subtotal'], 2) }}</span>
                            </div>
                            @endif
                            
                            @if($invoiceTemplate->show_discount_section && isset($previewData['invoice']['discount']))
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-semibold text-red-600">-€{{ number_format($previewData['invoice']['discount'], 2) }}</span>
                            </div>
                            @endif
                            
                            @if($invoiceTemplate->show_tax_details)
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="text-gray-600">VAT (21%):</span>
                                <span class="font-semibold text-gray-900">€{{ number_format($previewData['invoice']['vat_amount'], 2) }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between py-3">
                                <span class="text-xl font-bold @if($invoiceTemplate->color_scheme == 'custom') primary-color @else text-{{ $invoiceTemplate->color_scheme }}-600 @endif">Total Due:</span>
                                <span class="text-xl font-bold @if($invoiceTemplate->color_scheme == 'custom') primary-color @else text-{{ $invoiceTemplate->color_scheme }}-600 @endif">€{{ number_format($previewData['invoice']['total_amount'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    @break

                @case('payment_terms')
                    @if($invoiceTemplate->show_payment_terms)
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Payment Terms</h3>
                        <div class="text-sm text-gray-600">
                            <p>{{ $invoiceTemplate->payment_terms_text ?: 'Payment is due within 30 days of invoice date. Late payments may incur additional charges.' }}</p>
                        </div>
                    </div>
                    @endif
                    @break

                @case('bank_details')
                    @if($invoiceTemplate->show_bank_details)
                    <div class="@if($invoiceTemplate->color_scheme == 'custom') bg-gray-50 @else bg-{{ $invoiceTemplate->color_scheme }}-50 @endif rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Payment Information</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Bank Name:</p>
                                <p class="font-medium text-gray-900">{{ $previewData['company']['bank_name'] }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Account Name:</p>
                                <p class="font-medium text-gray-900">{{ $previewData['company']['name'] }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">IBAN:</p>
                                <p class="font-medium text-gray-900">{{ $previewData['company']['iban'] }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">BIC/SWIFT:</p>
                                <p class="font-medium text-gray-900">{{ $previewData['company']['bic'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @break

                @case('notes')
                    @php
                        $notesConfig = isset($block['config']) ? $block['config'] : [];
                        $notesTitle = $notesConfig['notes_title'] ?? 'Notes';
                        $notesText = $notesConfig['notes_text'] ?? "Thank you for your business! We appreciate your trust in our services.\n\nFor any questions regarding this invoice, please contact our billing department.";
                        $notesStyle = $notesConfig['notes_style'] ?? 'box';
                        $showNotes = $notesConfig['show_notes'] ?? true;
                    @endphp
                    
                    @if($invoiceTemplate->show_notes_section && $showNotes)
                    <div class="mb-6">
                        @if($notesStyle == 'simple')
                        {{-- Simple Style --}}
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">{{ $notesTitle }}</h3>
                        <div class="text-sm text-gray-600">
                            {!! nl2br(e($notesText)) !!}
                        </div>
                        
                        @elseif($notesStyle == 'bordered')
                        {{-- Bordered Style --}}
                        <div class="border-l-4 @if($invoiceTemplate->color_scheme == 'custom') primary-border @else border-{{ $invoiceTemplate->color_scheme }}-500 @endif pl-4">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">{{ $notesTitle }}</h3>
                            <div class="text-sm text-gray-600">
                                {!! nl2br(e($notesText)) !!}
                            </div>
                        </div>
                        
                        @elseif($notesStyle == 'highlighted')
                        {{-- Highlighted Style --}}
                        <div class="@if($invoiceTemplate->color_scheme == 'custom') bg-yellow-50 border border-yellow-200 @else bg-{{ $invoiceTemplate->color_scheme }}-50 border border-{{ $invoiceTemplate->color_scheme }}-200 @endif rounded-lg p-4">
                            <h3 class="text-sm font-bold @if($invoiceTemplate->color_scheme == 'custom') text-yellow-800 @else text-{{ $invoiceTemplate->color_scheme }}-800 @endif uppercase tracking-wider mb-2">{{ $notesTitle }}</h3>
                            <div class="text-sm @if($invoiceTemplate->color_scheme == 'custom') text-yellow-700 @else text-{{ $invoiceTemplate->color_scheme }}-700 @endif">
                                {!! nl2br(e($notesText)) !!}
                            </div>
                        </div>
                        
                        @else
                        {{-- Box Style (default) --}}
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">{{ $notesTitle }}</h3>
                        <div class="bg-gray-50 rounded p-3 text-sm text-gray-600">
                            {!! nl2br(e($notesText)) !!}
                        </div>
                        @endif
                    </div>
                    @endif
                    @break

                @case('footer')
                    @php
                        $footerConfig = isset($block['config']) ? $block['config'] : [];
                        $footerText = $footerConfig['footer_text'] ?? null;
                        $footerStyle = $footerConfig['footer_style'] ?? 'centered';
                        $showPageNumbers = $footerConfig['show_page_numbers'] ?? $invoiceTemplate->show_page_numbers;
                        $showPrintDate = $footerConfig['show_print_date'] ?? false;
                        $showFooter = $footerConfig['show_footer'] ?? true;
                        
                        // Use custom footer text if provided, otherwise use template default or company info
                        if ($footerText) {
                            $footerContent = $footerText;
                        } elseif ($invoiceTemplate->footer_content) {
                            $footerContent = $invoiceTemplate->footer_content;
                        } else {
                            $footerContent = $previewData['company']['name'] . ' • ' . $previewData['company']['address'] . ', ' . $previewData['company']['city'] . "\n" .
                                           $previewData['company']['email'] . ' • ' . $previewData['company']['phone'] . ' • ' . $previewData['company']['website'];
                        }
                    @endphp
                    
                    @if($invoiceTemplate->show_footer && $showFooter)
                    <div class="border-t border-gray-200 pt-6 mt-12">
                        @if($footerStyle == 'left')
                        {{-- Left Aligned Footer --}}
                        <div class="text-sm text-gray-500">
                            {!! nl2br(e($footerContent)) !!}
                            <div class="flex justify-between items-end mt-4">
                                @if($showPageNumbers)
                                <p class="text-xs">Page 1 of 1</p>
                                @endif
                                @if($showPrintDate)
                                <p class="text-xs">Printed: {{ date('M d, Y') }}</p>
                                @endif
                            </div>
                        </div>
                        
                        @elseif($footerStyle == 'columns')
                        {{-- Two Column Footer --}}
                        <div class="grid grid-cols-2 gap-8 text-sm text-gray-500">
                            <div>
                                <p class="font-semibold text-gray-700 mb-1">{{ $previewData['company']['name'] }}</p>
                                <p>{{ $previewData['company']['address'] }}</p>
                                <p>{{ $previewData['company']['city'] }}, {{ $previewData['company']['country'] }}</p>
                            </div>
                            <div class="text-right">
                                <p>{{ $previewData['company']['email'] }}</p>
                                <p>{{ $previewData['company']['phone'] }}</p>
                                <p>{{ $previewData['company']['website'] }}</p>
                            </div>
                        </div>
                        @if($showPageNumbers || $showPrintDate)
                        <div class="flex justify-between items-center mt-4 text-xs text-gray-400">
                            @if($showPageNumbers)
                            <p>Page 1 of 1</p>
                            @endif
                            @if($showPrintDate)
                            <p>Printed: {{ date('M d, Y') }}</p>
                            @endif
                        </div>
                        @endif
                        
                        @elseif($footerStyle == 'minimal')
                        {{-- Minimal Footer --}}
                        <div class="text-center">
                            @if($showPageNumbers || $showPrintDate)
                            <div class="flex justify-between items-center text-xs text-gray-400">
                                @if($showPageNumbers)
                                <p>Page 1 of 1</p>
                                @else
                                <div></div>
                                @endif
                                <p class="text-gray-500">{{ $previewData['company']['name'] }}</p>
                                @if($showPrintDate)
                                <p>{{ date('M d, Y') }}</p>
                                @else
                                <div></div>
                                @endif
                            </div>
                            @endif
                        </div>
                        
                        @else
                        {{-- Centered Footer (default) --}}
                        <div class="text-center text-sm text-gray-500">
                            {!! nl2br(e($footerContent)) !!}
                            @if($showPageNumbers || $showPrintDate)
                            <div class="mt-4 text-xs space-x-4">
                                @if($showPageNumbers)
                                <span>Page 1 of 1</span>
                                @endif
                                @if($showPrintDate)
                                <span>Printed: {{ date('M d, Y') }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                    @break
                @endswitch
            @endif
        @endforeach
    </div>
</body>
</html>