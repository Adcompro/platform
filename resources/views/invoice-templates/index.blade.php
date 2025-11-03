@extends('layouts.app')

@section('title', 'Invoice Templates')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/80 backdrop-blur-md border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 600; color: var(--theme-text);">Invoice Templates</h1>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.125rem;">Manage your invoice layouts and designs</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('invoice-templates.help') }}" 
                       class="inline-flex items-center bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-all duration-200"
                       style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                        <i class="fas fa-question-circle mr-1.5" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                        Help
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <a href="{{ route('invoice-templates.create') }}" 
                       class="inline-flex items-center bg-slate-500 text-white font-medium rounded-lg hover:bg-slate-600 transition-all duration-200"
                       style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                        <i class="fas fa-plus mr-1.5" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                        New Template
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50/50 backdrop-blur-sm border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="ml-3 font-medium" style="font-size: var(--theme-font-size);">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50/50 backdrop-blur-sm border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="ml-3 font-medium" style="font-size: var(--theme-font-size);">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Search and Filter Bar --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('invoice-templates.index') }}" class="flex gap-2">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search templates..." 
                           class="w-full px-3 py-1.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                           style="font-size: var(--theme-font-size);">
                </div>
                <select name="template_type" 
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent"
                        style="font-size: var(--theme-font-size);">
                    <option value="">All Types</option>
                    <option value="standard" {{ request('template_type') == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="modern" {{ request('template_type') == 'modern' ? 'selected' : '' }}>Modern</option>
                    <option value="classic" {{ request('template_type') == 'classic' ? 'selected' : '' }}>Classic</option>
                    <option value="minimal" {{ request('template_type') == 'minimal' ? 'selected' : '' }}>Minimal</option>
                    <option value="detailed" {{ request('template_type') == 'detailed' ? 'selected' : '' }}>Detailed</option>
                    <option value="custom" {{ request('template_type') == 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
                <button type="submit" 
                        class="bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-all"
                        style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        {{-- Templates Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($templates as $template)
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-md transition-all duration-200">
                {{-- Template Header --}}
                <div class="px-4 py-3 border-b border-slate-200/50 flex justify-between items-start">
                    <div>
                        <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">
                            {{ $template->name }}
                            @if($template->is_default)
                            <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full" style="font-size: calc(var(--theme-font-size) - 2px);">Default</span>
                            @endif
                        </h3>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.125rem;">{{ $template->description }}</p>
                    </div>
                    @if(!$template->is_active)
                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded" style="font-size: calc(var(--theme-font-size) - 2px);">Inactive</span>
                    @endif
                </div>

                {{-- Template Preview --}}
                <div class="p-4">
                    <div class="aspect-[8.5/11] bg-white rounded-lg border border-slate-200 p-3 mb-3 overflow-hidden">
                        {{-- Actual layout based on block_positions --}}
                        @php
                            $blocks = [];
                            if (isset($template->block_positions)) {
                                if (is_string($template->block_positions)) {
                                    $decoded = json_decode($template->block_positions, true);
                                    if (is_array($decoded)) {
                                        $blocks = $decoded;
                                    }
                                } elseif (is_array($template->block_positions)) {
                                    $blocks = $template->block_positions;
                                }
                            }
                        @endphp
                        
                        <div class="space-y-1.5 h-full">
                            @if(count($blocks) > 0)
                                @php
                                    $currentRow = [];
                                    $rows = [];
                                    
                                    // Group blocks into rows based on their column configuration
                                    foreach($blocks as $block) {
                                        $blockType = $block['type'] ?? '';
                                        $columns = $block['columns'] ?? 12; // Default full width
                                        $position = $block['position'] ?? 'left'; // left, right, center
                                        
                                        // Check if block should be on same row as previous
                                        if ($columns < 12 && count($currentRow) > 0) {
                                            $totalColumns = array_sum(array_column($currentRow, 'columns'));
                                            if ($totalColumns + $columns <= 12) {
                                                $currentRow[] = $block;
                                            } else {
                                                $rows[] = $currentRow;
                                                $currentRow = [$block];
                                            }
                                        } elseif ($columns < 12) {
                                            $currentRow[] = $block;
                                        } else {
                                            if (count($currentRow) > 0) {
                                                $rows[] = $currentRow;
                                            }
                                            $rows[] = [$block];
                                            $currentRow = [];
                                        }
                                    }
                                    if (count($currentRow) > 0) {
                                        $rows[] = $currentRow;
                                    }
                                @endphp
                                
                                @foreach($rows as $row)
                                    <div class="flex gap-0.5">
                                        @foreach($row as $block)
                                            @php
                                                $blockType = $block['type'] ?? '';
                                                $columns = $block['columns'] ?? 12;
                                                $widthClass = match($columns) {
                                                    6 => 'w-1/2',
                                                    4 => 'w-1/3',
                                                    8 => 'w-2/3',
                                                    3 => 'w-1/4',
                                                    9 => 'w-3/4',
                                                    default => 'w-full'
                                                };
                                                $blockHeight = match($blockType) {
                                                    'header' => 'h-6',
                                                    'logo' => 'h-8',
                                                    'company_info', 'customer_info' => 'h-5',
                                                    'invoice_details' => 'h-4',
                                                    'invoice_lines' => 'h-12',
                                                    'totals' => 'h-6',
                                                    'payment_info', 'bank_details' => 'h-5',
                                                    'notes' => 'h-4',
                                                    'footer' => 'h-3',
                                                    default => 'h-3'
                                                };
                                                $blockColor = match($blockType) {
                                                    'header' => 'bg-' . $template->color_scheme . '-100 border-' . $template->color_scheme . '-200',
                                                    'logo' => 'bg-white',
                                                    'invoice_lines' => 'bg-slate-50 border border-slate-200',
                                                    'totals' => 'bg-slate-100',
                                                    'footer' => 'bg-slate-50',
                                                    default => 'bg-slate-100'
                                                };
                                            @endphp
                                            
                                            <div class="{{ $widthClass }} {{ $blockHeight }} {{ $blockColor }} rounded-sm relative overflow-hidden">
                                        @if($blockType === 'header')
                                            <div class="p-1 text-center">
                                                <div style="font-size: 6px; font-weight: 600; color: var(--theme-text);">INVOICE</div>
                                                @if($template->header_content)
                                                    <div style="font-size: 4px; color: var(--theme-text-muted); margin-top: 1px;">{{ Str::limit(strip_tags($template->header_content), 30) }}</div>
                                                @endif
                                            </div>
                                        @elseif($blockType === 'logo' && $template->show_logo)
                                            <div class="h-full flex {{ $template->logo_position == 'center' ? 'justify-center' : ($template->logo_position == 'right' ? 'justify-end' : '') }} p-1">
                                                @if($template->logo_path && file_exists(public_path($template->logo_path)))
                                                    <img src="{{ asset($template->logo_path) }}" alt="Logo" class="h-full object-contain" style="max-width: 30px;">
                                                @else
                                                    <div class="bg-gradient-to-br from-slate-300 to-slate-400 rounded flex items-center justify-center" style="width: 20px; height: 20px;">
                                                        <span style="font-size: 6px; color: white; font-weight: bold;">LOGO</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($blockType === 'company_info')
                                            <div class="p-1">
                                                <div style="font-size: 5px; font-weight: 600; color: var(--theme-text);">{{ Str::limit($template->company->name ?? 'Company Name', 20) }}</div>
                                                <div style="font-size: 4px; color: var(--theme-text-muted);">123 Business St</div>
                                                <div style="font-size: 4px; color: var(--theme-text-muted);">City, 12345</div>
                                            </div>
                                        @elseif($blockType === 'customer_info')
                                            <div class="p-1">
                                                <div style="font-size: 4px; font-weight: 600; color: var(--theme-text-muted);">BILL TO:</div>
                                                <div style="font-size: 4px; color: var(--theme-text);">Customer Name</div>
                                                <div style="font-size: 4px; color: var(--theme-text-muted);">Customer Address</div>
                                            </div>
                                        @elseif($blockType === 'invoice_details')
                                            <div class="p-1 flex justify-between">
                                                <div>
                                                    <div style="font-size: 4px; color: var(--theme-text-muted);">Invoice #: <span style="color: var(--theme-text);">INV-001</span></div>
                                                    <div style="font-size: 4px; color: var(--theme-text-muted);">Date: <span style="color: var(--theme-text);">{{ date('d/m/Y') }}</span></div>
                                                </div>
                                                <div class="text-right">
                                                    <div style="font-size: 4px; color: var(--theme-text-muted);">Due: <span style="color: var(--theme-text);">30 days</span></div>
                                                </div>
                                            </div>
                                        @elseif($blockType === 'invoice_lines')
                                            <div class="p-1">
                                                <table class="w-full" style="font-size: 4px;">
                                                    <thead>
                                                        <tr class="border-b" style="border-color: rgba(0,0,0,0.1);">
                                                            <th class="text-left" style="padding: 1px; font-weight: 600;">Description</th>
                                                            <th class="text-center" style="padding: 1px; font-weight: 600;">Qty</th>
                                                            <th class="text-right" style="padding: 1px; font-weight: 600;">Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr style="color: var(--theme-text-muted);">
                                                            <td style="padding: 1px;">Service Item 1</td>
                                                            <td class="text-center" style="padding: 1px;">2</td>
                                                            <td class="text-right" style="padding: 1px;">€100</td>
                                                        </tr>
                                                        <tr style="color: var(--theme-text-muted);">
                                                            <td style="padding: 1px;">Service Item 2</td>
                                                            <td class="text-center" style="padding: 1px;">1</td>
                                                            <td class="text-right" style="padding: 1px;">€50</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @elseif($blockType === 'totals')
                                            <div class="p-1 flex justify-end">
                                                <div style="font-size: 4px;">
                                                    <div class="flex justify-between" style="gap: 8px;">
                                                        <span style="color: var(--theme-text-muted);">Subtotal:</span>
                                                        <span style="color: var(--theme-text);">€150</span>
                                                    </div>
                                                    <div class="flex justify-between" style="gap: 8px;">
                                                        <span style="color: var(--theme-text-muted);">Tax:</span>
                                                        <span style="color: var(--theme-text);">€31.50</span>
                                                    </div>
                                                    <div class="flex justify-between border-t" style="gap: 8px; border-color: rgba(0,0,0,0.1); padding-top: 1px; margin-top: 1px;">
                                                        <span style="font-weight: 600; color: var(--theme-text);">Total:</span>
                                                        <span style="font-weight: 600; color: var(--theme-text);">€181.50</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($blockType === 'payment_info')
                                            <div class="p-1">
                                                <div style="font-size: 4px; font-weight: 600; color: var(--theme-text-muted);">PAYMENT TERMS</div>
                                                <div style="font-size: 3px; color: var(--theme-text-muted);">Net 30 days - Bank transfer</div>
                                            </div>
                                        @elseif($blockType === 'bank_details')
                                            <div class="p-1">
                                                <div style="font-size: 4px; font-weight: 600; color: var(--theme-text-muted);">BANK DETAILS</div>
                                                <div style="font-size: 3px; color: var(--theme-text-muted);">IBAN: NL00 BANK 0000 0000 00</div>
                                                <div style="font-size: 3px; color: var(--theme-text-muted);">BIC: BANKCODE</div>
                                            </div>
                                        @elseif($blockType === 'notes')
                                            <div class="p-1">
                                                <div style="font-size: 4px; font-weight: 600; color: var(--theme-text-muted);">NOTES</div>
                                                <div style="font-size: 3px; color: var(--theme-text-muted);">Thank you for your business!</div>
                                            </div>
                                        @elseif($blockType === 'footer')
                                            <div class="p-1 text-center">
                                                @if($template->footer_content)
                                                    <div style="font-size: 3px; color: var(--theme-text-muted);">{{ Str::limit(strip_tags($template->footer_content), 50) }}</div>
                                                @else
                                                    <div style="font-size: 3px; color: var(--theme-text-muted);">© {{ date('Y') }} Company - All rights reserved</div>
                                                @endif
                                            </div>
                                        @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @else
                                {{-- Fallback to default layout if no blocks defined --}}
                                @if($template->show_header)
                                <div class="h-4 bg-{{ $template->color_scheme }}-100 rounded-sm"></div>
                                @endif
                                
                                @if($template->show_logo)
                                <div class="flex {{ $template->logo_position == 'center' ? 'justify-center' : ($template->logo_position == 'right' ? 'justify-end' : '') }}">
                                    <div class="w-6 h-6 bg-slate-300 rounded"></div>
                                </div>
                                @endif
                                
                                <div class="space-y-1">
                                    <div class="h-2 bg-slate-200 rounded w-3/4"></div>
                                    <div class="h-2 bg-slate-200 rounded w-1/2"></div>
                                </div>
                                
                                <div class="border border-slate-200 rounded p-1">
                                    <div class="h-2 bg-slate-200 rounded mb-1"></div>
                                    <div class="h-1 bg-slate-100 rounded"></div>
                                    <div class="h-1 bg-slate-100 rounded mt-0.5"></div>
                                </div>
                                
                                @if($template->show_footer)
                                <div class="h-3 bg-slate-50 rounded-sm"></div>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Template Info --}}
                    <div class="space-y-2" style="font-size: calc(var(--theme-font-size) - 2px);">
                        <div class="flex justify-between">
                            <span style="color: var(--theme-text-muted);">Type:</span>
                            <span style="font-weight: 500; color: var(--theme-text);">{{ ucfirst($template->template_type) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span style="color: var(--theme-text-muted);">Color:</span>
                            <span class="px-2 py-0.5 bg-{{ $template->color_scheme }}-100 text-{{ $template->color_scheme }}-700 rounded" style="font-size: calc(var(--theme-font-size) - 2px);">
                                {{ ucfirst($template->color_scheme) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span style="color: var(--theme-text-muted);">Font:</span>
                            <span style="font-weight: 500; color: var(--theme-text);">{{ $template->font_family }}</span>
                        </div>
                        @if($template->company)
                        <div class="flex justify-between">
                            <span style="color: var(--theme-text-muted);">Company:</span>
                            <span style="font-weight: 500; color: var(--theme-text);">{{ $template->company->name }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100">
                        <div class="flex items-center space-x-1">
                            <button onclick="openTemplatePreview({{ $template->id }})" 
                                    class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-50 rounded-lg transition-all"
                                    title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            @if(in_array(Auth::user()->role, ['super_admin']) || 
                                (Auth::user()->role == 'admin' && $template->company_id == Auth::user()->company_id))
                            <a href="{{ route('invoice-templates.edit', $template) }}" 
                               class="text-slate-400 hover:text-blue-600 p-1 hover:bg-slate-50 rounded-lg transition-all"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @endif
                        </div>
                        <div class="flex items-center space-x-1">
                            <form action="{{ route('invoice-templates.duplicate', $template) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-slate-400 hover:text-purple-600 p-1 hover:bg-slate-50 rounded-lg transition-all"
                                        title="Duplicate">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </form>
                            @if(in_array(Auth::user()->role, ['super_admin']) || 
                                (Auth::user()->role == 'admin' && $template->company_id == Auth::user()->company_id))
                            @if(!$template->is_default)
                            <form action="{{ route('invoice-templates.destroy', $template) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this template?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-slate-400 hover:text-red-600 p-1 hover:bg-slate-50 rounded-lg transition-all"
                                        title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 font-medium" style="font-size: var(--theme-font-size); color: var(--theme-text);">No templates found</h3>
                    <p class="mt-1" style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Get started by creating a new invoice template.</p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <div class="mt-4">
                        <a href="{{ route('invoice-templates.create') }}" 
                           class="inline-flex items-center bg-slate-500 text-white font-medium rounded-lg hover:bg-slate-600 transition-all duration-200"
                           style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                            <i class="fas fa-plus mr-1.5" style="font-size: calc(var(--theme-font-size) - 2px);"></i>
                            Create Template
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($templates->hasPages())
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Template Preview Modal --}}
<div id="templatePreviewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" onclick="closeTemplatePreview(event)">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="font-semibold" style="font-size: calc(var(--theme-font-size) + 4px); color: var(--theme-text);">
                    Template Preview
                </h2>
                <button onclick="closeTemplatePreview()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                <div id="templatePreviewContent">
                    {{-- Content will be loaded here via AJAX --}}
                    <div class="flex justify-center items-center py-12">
                        <div class="text-center">
                            <svg class="animate-spin h-8 w-8 mx-auto text-slate-400 mb-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Loading template preview...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Modal Footer --}}
            <div class="flex items-center justify-end px-6 py-4 border-t border-slate-200 bg-slate-50 gap-2">
                <button onclick="closeTemplatePreview()" 
                        class="font-medium rounded-lg transition-all"
                        style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);"
                        onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'"
                        onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.1)'">
                    Close
                </button>
                <a id="templateEditLink" href="#" 
                   class="font-medium rounded-lg transition-all"
                   style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background-color: var(--theme-primary); color: white;"
                   onmouseover="this.style.opacity='0.9'" 
                   onmouseout="this.style.opacity='1'">
                    Edit Template
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    let currentTemplateId = null;
    
    function openTemplatePreview(templateId) {
        currentTemplateId = templateId;
        const modal = document.getElementById('templatePreviewModal');
        const content = document.getElementById('templatePreviewContent');
        const editLink = document.getElementById('templateEditLink');
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Update edit link
        editLink.href = `/invoice-templates/${templateId}/edit`;
        
        // Show loading state
        content.innerHTML = `
            <div class="flex justify-center items-center py-12">
                <div class="text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto text-slate-400 mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Loading template preview...</p>
                </div>
            </div>
        `;
        
        // Fetch template preview
        fetch(`/invoice-templates/${templateId}/preview-ajax`)
            .then(response => response.text())
            .then(html => {
                content.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading template preview:', error);
                content.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p style="font-size: var(--theme-font-size); color: var(--theme-text);">Failed to load template preview</p>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.5rem;">Please try again or refresh the page</p>
                    </div>
                `;
            });
    }
    
    function closeTemplatePreview(event) {
        if (event && event.target !== event.currentTarget) {
            return;
        }
        const modal = document.getElementById('templatePreviewModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentTemplateId = null;
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && currentTemplateId !== null) {
            closeTemplatePreview();
        }
    });
</script>
@endsection