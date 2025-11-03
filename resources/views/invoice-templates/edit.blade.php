@extends('layouts.app')

@section('title', 'Edit Invoice Template')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 style="font-size: calc(var(--theme-font-size) + 6px); font-weight: 500; color: var(--theme-text);">Edit Invoice Template</h1>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.125rem;">{{ $invoiceTemplate->name }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="openPreviewWindow()" 
                            class="bg-slate-500 text-white font-medium rounded-lg hover:bg-slate-600 transition-all duration-200 flex items-center"
                            style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview
                    </button>
                    <button type="submit" form="templateForm" 
                            class="bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-200"
                            style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                        Save Template
                    </button>
                    <a href="{{ route('invoice-templates.index') }}" 
                       class="bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-all"
                       style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-32">
        <form id="templateForm" action="{{ route('invoice-templates.update', $invoiceTemplate) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            {{-- Template Settings --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Template Settings</h2>
                </div>
                <div class="p-4">
                    @include('invoice-templates.partials.template-settings', ['invoiceTemplate' => $invoiceTemplate])
                </div>
            </div>

            {{-- Hidden Fields --}}
            <input type="hidden" name="layout_config" id="layout_config" value="">
            <input type="hidden" name="block_positions" id="block_positions" value="{{ old('block_positions', is_string($invoiceTemplate->block_positions) ? $invoiceTemplate->block_positions : json_encode($invoiceTemplate->block_positions ?? [])) }}">

            {{-- Visual Builder --}}
            <div id="visualBuilder" class="grid grid-cols-12 gap-6">
                {{-- Left Panel - Settings --}}
                <div class="col-span-3">
                    {{-- Template Style Settings --}}
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-4">
                        <div class="px-4 py-3 border-b border-slate-200/50">
                            <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Style Settings</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label for="color_scheme" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Color Scheme</label>
                                <select name="color_scheme" id="color_scheme" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg" style="font-size: var(--theme-font-size);">
                                    <option value="blue" {{ $invoiceTemplate->color_scheme == 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="green" {{ $invoiceTemplate->color_scheme == 'green' ? 'selected' : '' }}>Green</option>
                                    <option value="red" {{ $invoiceTemplate->color_scheme == 'red' ? 'selected' : '' }}>Red</option>
                                    <option value="purple" {{ $invoiceTemplate->color_scheme == 'purple' ? 'selected' : '' }}>Purple</option>
                                    <option value="custom" {{ $invoiceTemplate->color_scheme == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>

                            <div id="customColors" class="{{ $invoiceTemplate->color_scheme != 'custom' ? 'hidden' : '' }}">
                                <div class="space-y-3">
                                    <div>
                                        <label for="primary_color" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Primary Color</label>
                                        <input type="color" name="primary_color" id="primary_color" 
                                               value="{{ $invoiceTemplate->primary_color ?? '#3B82F6' }}" 
                                               class="w-full h-8 rounded border border-slate-200">
                                    </div>
                                    <div>
                                        <label for="secondary_color" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Secondary Color</label>
                                        <input type="color" name="secondary_color" id="secondary_color" 
                                               value="{{ $invoiceTemplate->secondary_color ?? '#64748B' }}" 
                                               class="w-full h-8 rounded border border-slate-200">
                                    </div>
                                    <div>
                                        <label for="accent_color" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Accent Color</label>
                                        <input type="color" name="accent_color" id="accent_color" 
                                               value="{{ $invoiceTemplate->accent_color ?? '#EF4444' }}" 
                                               class="w-full h-8 rounded border border-slate-200">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="font_family" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Font Family</label>
                                <select name="font_family" id="font_family" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg" style="font-size: var(--theme-font-size);">
                                    <option value="Inter" {{ $invoiceTemplate->font_family == 'Inter' ? 'selected' : '' }}>Inter</option>
                                    <option value="Arial" {{ $invoiceTemplate->font_family == 'Arial' ? 'selected' : '' }}>Arial</option>
                                    <option value="Helvetica" {{ $invoiceTemplate->font_family == 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                                    <option value="Georgia" {{ $invoiceTemplate->font_family == 'Georgia' ? 'selected' : '' }}>Georgia</option>
                                    <option value="Times New Roman" {{ $invoiceTemplate->font_family == 'Times New Roman' ? 'selected' : '' }}>Times New Roman</option>
                                    <option value="Courier" {{ $invoiceTemplate->font_family == 'Courier' ? 'selected' : '' }}>Courier</option>
                                </select>
                            </div>

                            <div>
                                <label for="font_size" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Font Size</label>
                                <select name="font_size" id="font_size" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg" style="font-size: var(--theme-font-size);">
                                    <option value="small" {{ $invoiceTemplate->font_size == 'small' ? 'selected' : '' }}>Small</option>
                                    <option value="normal" {{ $invoiceTemplate->font_size == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="large" {{ $invoiceTemplate->font_size == 'large' ? 'selected' : '' }}>Large</option>
                                </select>
                            </div>

                            <div>
                                <label for="logo_position" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Logo Position</label>
                                <select name="logo_position" id="logo_position" onchange="toggleLogoUpload()" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg" style="font-size: var(--theme-font-size);">
                                    <option value="left" {{ $invoiceTemplate->logo_position == 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="center" {{ $invoiceTemplate->logo_position == 'center' ? 'selected' : '' }}>Center</option>
                                    <option value="right" {{ $invoiceTemplate->logo_position == 'right' ? 'selected' : '' }}>Right</option>
                                    <option value="none" {{ $invoiceTemplate->logo_position == 'none' ? 'selected' : '' }}>No Logo</option>
                                </select>
                            </div>
                            
                            {{-- Logo Upload --}}
                            <div id="logoUploadSection" style="{{ $invoiceTemplate->logo_position === 'none' ? 'display: none;' : '' }}">
                                <label for="logo_file" style="display: block; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Company Logo</label>
                                <div class="space-y-2">
                                    @if($invoiceTemplate->logo_path)
                                        @php
                                            // Handle both cases: with or without 'logos/' prefix
                                            $logoPath = $invoiceTemplate->logo_path;
                                            if (strpos($logoPath, 'logos/') === 0) {
                                                $logoPath = substr($logoPath, 6); // Remove 'logos/' prefix
                                            }
                                            
                                            // Check if file exists to prevent broken image displays
                                            $fullPath = storage_path('app/public/logos/' . $logoPath);
                                            $fileExists = file_exists($fullPath);
                                        @endphp
                                        
                                        <div id="existingLogo" class="relative border border-slate-200 rounded-lg p-2 bg-white">
                                            @if($fileExists)
                                                <img src="{{ url('template-logo/' . $logoPath) }}" 
                                                     alt="Current logo" 
                                                     class="max-h-20 mx-auto"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); text-align: center; margin-top: 0.25rem;">Current logo</p>
                                                <div style="display: none; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-danger); text-align: center; margin-top: 0.25rem;">
                                                    <svg class="w-4 h-4 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Logo could not be loaded
                                                </div>
                                            @else
                                                <div class="text-center p-4 bg-amber-50 rounded" style="font-size: calc(var(--theme-font-size) - 2px); color: #d97706;">
                                                    <svg class="w-5 h-5 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <strong>Logo file missing</strong><br>
                                                    Please upload a new logo
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <input type="file" 
                                           name="logo_file" 
                                           id="logo_file"
                                           accept="image/*"
                                           onchange="previewLogo(event)"
                                           class="w-full px-3 py-1.5 border border-slate-200 rounded-lg file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                                           style="font-size: var(--theme-font-size);">
                                    
                                    {{-- Logo Preview --}}
                                    <div id="logoPreview" class="hidden">
                                        <div class="relative border border-slate-200 rounded-lg p-2 bg-white">
                                            <img id="logoPreviewImg" src="" alt="Logo preview" class="max-h-20 mx-auto">
                                            <button type="button" 
                                                    onclick="removeLogo()"
                                                    class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Upload a PNG, JPG or SVG file (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Block Configuration --}}
                    <div id="blockConfigPanel" class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl hidden">
                        <div class="px-4 py-3 border-b border-slate-200/50">
                            <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Block Configuration</h3>
                        </div>
                        <div class="p-4">
                            <div id="blockConfigContent">
                                <!-- Dynamic content will be inserted here -->
                            </div>
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <button type="button" onclick="saveBlockConfig()" 
                                        class="w-full bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all"
                                        style="font-size: var(--theme-button-font-size); padding: var(--theme-button-padding-y) var(--theme-button-padding-x);">
                                    Save Configuration
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Middle Panel - Available Blocks --}}
                <div class="col-span-3">
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
                        <div class="px-4 py-3 border-b border-slate-200/50">
                            <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Available Blocks</h3>
                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.125rem;">Drag blocks to canvas</p>
                        </div>
                        <div class="p-4">
                            <div id="availableBlocks" class="space-y-2">
                                @php
                                $blocks = [
                                    ['type' => 'header', 'label' => 'Header', 'icon' => 'fa-heading'],
                                    ['type' => 'company_info', 'label' => 'Company Info', 'icon' => 'fa-building'],
                                    ['type' => 'customer_info', 'label' => 'Customer Info', 'icon' => 'fa-user'],
                                    ['type' => 'invoice_details', 'label' => 'Invoice Details', 'icon' => 'fa-file-invoice'],
                                    ['type' => 'project_info', 'label' => 'Project Info', 'icon' => 'fa-project-diagram'],
                                    ['type' => 'line_items', 'label' => 'Line Items', 'icon' => 'fa-list'],
                                    ['type' => 'time_entries', 'label' => 'Time Entries', 'icon' => 'fa-clock'],
                                    ['type' => 'hours_summary', 'label' => 'Hours Summary', 'icon' => 'fa-hourglass-half'],
                                    ['type' => 'hours_per_user', 'label' => 'Hours per User', 'icon' => 'fa-users'],
                                    ['type' => 'hours_per_milestone', 'label' => 'Hours per Milestone', 'icon' => 'fa-tasks'],
                                    ['type' => 'hours_per_task', 'label' => 'Hours per Task', 'icon' => 'fa-check-square'],
                                    ['type' => 'weekly_hours', 'label' => 'Weekly Hours Overview', 'icon' => 'fa-calendar-week'],
                                    ['type' => 'monthly_hours', 'label' => 'Monthly Hours Overview', 'icon' => 'fa-calendar'],
                                    ['type' => 'budget_overview', 'label' => 'Budget Overview', 'icon' => 'fa-chart-pie'],
                                    ['type' => 'additional_costs', 'label' => 'Additional Costs', 'icon' => 'fa-plus-circle'],
                                    ['type' => 'subtotal', 'label' => 'Subtotal', 'icon' => 'fa-calculator'],
                                    ['type' => 'tax_section', 'label' => 'Tax Section', 'icon' => 'fa-percent'],
                                    ['type' => 'discount_section', 'label' => 'Discount', 'icon' => 'fa-tag'],
                                    ['type' => 'total_amount', 'label' => 'Total Amount', 'icon' => 'fa-dollar-sign'],
                                    ['type' => 'payment_terms', 'label' => 'Payment Terms', 'icon' => 'fa-calendar-alt'],
                                    ['type' => 'bank_details', 'label' => 'Bank Details', 'icon' => 'fa-university'],
                                    ['type' => 'notes', 'label' => 'Notes', 'icon' => 'fa-sticky-note'],
                                    ['type' => 'footer', 'label' => 'Footer', 'icon' => 'fa-align-center'],
                                    ['type' => 'qr_code', 'label' => 'QR Code', 'icon' => 'fa-qrcode'],
                                    ['type' => 'signature_section', 'label' => 'Signatures', 'icon' => 'fa-signature'],
                                ];
                                @endphp
                                
                                @foreach($blocks as $block)
                                <div class="template-block available-block bg-slate-50 border border-slate-200 rounded-lg p-3 cursor-move hover:bg-slate-100 transition-colors" 
                                     data-block-type="{{ $block['type'] }}">
                                    <div class="flex items-center">
                                        <i class="fas {{ $block['icon'] }} text-slate-400 mr-2"></i>
                                        <span style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $block['label'] }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Panel - Canvas --}}
                <div class="col-span-6">
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl">
                        <div class="px-4 py-3 border-b border-slate-200/50">
                            <h3 style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">Template Canvas</h3>
                            <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-top: 0.125rem;">Arrange your invoice layout</p>
                        </div>
                        <div class="p-4">
                            <div id="templateCanvas" class="min-h-[600px] border-2 border-dashed border-slate-200 rounded-lg p-4">
                                {{-- Existing blocks will be loaded here --}}
                                <div class="empty-state text-center py-16 text-slate-400">
                                    <i class="fas fa-layer-group text-4xl mb-3"></i>
                                    <p style="font-size: var(--theme-font-size);">Drag blocks here to build your template</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Block Configuration Modal --}}
<div id="blockConfigModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full flex flex-col" style="max-height: 90vh; height: auto;">
            {{-- Modal Header --}}
            <div class="border-b border-slate-200 flex items-center justify-between flex-shrink-0" style="padding: 32px 32px !important;">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900">Configure Block</h3>
                    <p class="text-sm text-slate-500 mt-1" id="blockConfigSubtitle">Customize the appearance and behavior of this block</p>
                </div>
                <button type="button" onclick="closeBlockConfig()" 
                        class="text-slate-400 hover:text-slate-600 transition-colors p-2 hover:bg-slate-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            {{-- Modal Body with scrollable content --}}
            <div class="flex-1 overflow-y-auto" style="max-height: calc(90vh - 180px); padding: 32px !important;">
                <div id="modalBlockConfigContent" class="space-y-6">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>
            
            {{-- Modal Footer --}}
            <div class="border-t border-slate-200 bg-slate-50/50 flex justify-end space-x-3 flex-shrink-0" style="padding: 24px 32px !important;">
                <button type="button" onclick="closeBlockConfig()" 
                        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all duration-200">
                    Cancel
                </button>
                <button type="button" onclick="saveBlockConfig()" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.css">
<style>
    /* Modal scrollbar styling */
    #blockConfigModal .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }
    
    #blockConfigModal .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    #blockConfigModal .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    
    #blockConfigModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    .sortable-ghost {
        opacity: 0.4;
        background: #f1f5f9;
    }
    .sortable-drag {
        opacity: 0.9;
    }
    .template-block {
        transition: all 0.3s ease;
    }
    /* Removed duplicate configured indicator - now handled in HTML */
    .template-block.configured {
        position: relative;
    }
    /* Show block actions on hover for cleaner look */
    .canvas-block .block-actions {
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .canvas-block:hover .block-actions {
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Logo upload functions
function toggleLogoUpload() {
    const logoPosition = document.getElementById('logo_position').value;
    const logoUploadSection = document.getElementById('logoUploadSection');
    
    if (logoPosition === 'none') {
        logoUploadSection.style.display = 'none';
    } else {
        logoUploadSection.style.display = 'block';
    }
}

function previewLogo(event) {
    const file = event.target.files[0];
    
    if (file) {
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            event.target.value = '';
            return;
        }
        
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please upload an image file (PNG, JPG, or SVG)');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Hide existing logo if present
            const existingLogo = document.getElementById('existingLogo');
            if (existingLogo) {
                existingLogo.style.display = 'none';
            }
            
            // Show preview
            const previewImg = document.getElementById('logoPreviewImg');
            const previewDiv = document.getElementById('logoPreview');
            
            if (previewImg && previewDiv) {
                previewImg.src = e.target.result;
                previewDiv.classList.remove('hidden');
            } else {
                console.error('Logo preview elements not found - please refresh the page');
            }
        };
        
        reader.readAsDataURL(file);
    }
}

function removeLogo() {
    document.getElementById('logo_file').value = '';
    document.getElementById('logoPreview').classList.add('hidden');
    document.getElementById('logoPreviewImg').src = '';
    
    // Show existing logo again if present
    const existingLogo = document.getElementById('existingLogo');
    if (existingLogo) {
        existingLogo.style.display = 'block';
    }
}

function toggleLogoOptions() {
    const showLogo = document.getElementById('config_show_logo');
    const logoPositionOption = document.getElementById('logoPositionOption');
    
    if (showLogo && logoPositionOption) {
        logoPositionOption.style.display = showLogo.checked ? 'block' : 'none';
    }
}
// Global variables for Sortable instances
let availableSortable = null;
let canvasSortable = null;
let currentEditingBlock = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag and drop
    initializeDragAndDrop();
    
    // Load existing blocks
    loadExistingBlocks();
    
    // Color scheme toggle
    document.getElementById('color_scheme').addEventListener('change', function() {
        document.getElementById('customColors').classList.toggle('hidden', this.value !== 'custom');
    });
});

function initializeDragAndDrop() {
    // Available blocks (source)
    const availableEl = document.getElementById('availableBlocks');
    availableSortable = Sortable.create(availableEl, {
        group: {
            name: 'shared',
            pull: 'clone',
            put: false
        },
        animation: 150,
        sort: false
    });

    // Template canvas (destination)
    const canvasEl = document.getElementById('templateCanvas');
    canvasSortable = Sortable.create(canvasEl, {
        group: 'shared',
        animation: 150,
        handle: '.drag-handle',
        onAdd: function(evt) {
            // Clear empty state
            const emptyState = canvasEl.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }
            
            // Transform the added block
            const item = evt.item;
            const blockType = item.dataset.blockType;
            
            // Create a unique ID for the block
            const blockId = 'block-' + Date.now();
            
            // Create the canvas version of the block
            const canvasBlock = createCanvasBlock(blockType, blockId);
            
            // Replace the cloned element with the canvas version
            item.replaceWith(canvasBlock);
            
            // Update the template structure
            updateTemplateBlocks();
        },
        onSort: function() {
            updateTemplateBlocks();
        }
    });
}

function createCanvasBlock(blockType, blockId, config = {}) {
    const block = document.createElement('div');
    block.className = 'template-block canvas-block bg-white border border-slate-200 rounded-lg p-4 mb-3 relative group';
    block.dataset.blockId = blockId;
    block.dataset.blockType = blockType;
    
    // Store configuration in data attribute
    if (Object.keys(config).length > 0) {
        block.dataset.blockConfig = JSON.stringify(config);
        block.classList.add('configured');
    }
    
    const blockLabels = {
        'header': 'Header',
        'company_info': 'Company Information',
        'customer_info': 'Customer Information',
        'invoice_details': 'Invoice Details',
        'project_info': 'Project Information',
        'line_items': 'Invoice Line Items',
        'time_entries': 'Time Entries',
        'hours_summary': 'Hours Summary',
        'hours_per_user': 'Hours per User',
        'hours_per_milestone': 'Hours per Milestone',
        'hours_per_task': 'Hours per Task',
        'weekly_hours': 'Weekly Hours Overview',
        'monthly_hours': 'Monthly Hours Overview',
        'budget_overview': 'Budget Overview',
        'additional_costs': 'Additional Costs',
        'subtotal': 'Subtotal',
        'tax_section': 'Tax / VAT',
        'discount_section': 'Discount',
        'total_amount': 'Total Amount',
        'payment_terms': 'Payment Terms',
        'bank_details': 'Bank Details',
        'notes': 'Notes / Comments',
        'footer': 'Footer',
        'qr_code': 'QR Code',
        'signature_section': 'Signature Section'
    };
    
    // Check if block is configured
    const isConfigured = Object.keys(config).length > 0;
    const configBadge = isConfigured ? '<span class="ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full">Configured</span>' : '';
    const configButtonText = isConfigured ? 'Edit Configuration' : 'Configure';
    const configButtonIcon = isConfigured ? 
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>' :
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
    
    block.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center flex-1">
                <div class="drag-handle cursor-move mr-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-slate-700">${blockLabels[blockType] || blockType}</span>
                ${configBadge}
            </div>
            <div class="block-actions flex items-center space-x-1">
                <button type="button" onclick="event.stopPropagation(); configureBlock('${blockId}');" 
                        class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                        title="${configButtonText}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${configButtonIcon}
                    </svg>
                </button>
                <button type="button" onclick="event.stopPropagation(); removeBlock('${blockId}');" 
                        class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        title="Remove from template">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    return block;
}

function loadExistingBlocks() {
    const blockPositionsInput = document.getElementById('block_positions');
    const templateCanvas = document.getElementById('templateCanvas');
    
    console.log('Loading existing blocks...');
    console.log('Block positions input:', blockPositionsInput);
    console.log('Template canvas:', templateCanvas);
    
    if (!blockPositionsInput || !templateCanvas) {
        console.error('Required elements not found');
        return;
    }
    
    try {
        const rawValue = blockPositionsInput.value || '[]';
        console.log('Raw block positions value:', rawValue);
        
        // Handle escaped JSON
        let blockPositions;
        try {
            // First try direct parse
            blockPositions = JSON.parse(rawValue);
        } catch (e) {
            // If that fails, try to unescape and parse
            const unescaped = rawValue.replace(/\\"/g, '"').replace(/\\\\/g, '\\');
            blockPositions = JSON.parse(unescaped);
        }
        
        console.log('Parsed block positions:', blockPositions);
        
        if (blockPositions.length > 0) {
            // Clear empty state
            templateCanvas.innerHTML = '';
            
            // Create blocks
            blockPositions.forEach((blockData) => {
                const blockId = blockData.id || 'block-' + Date.now() + Math.random();
                const config = blockData.config || {};
                const block = createCanvasBlock(blockData.type, blockId, config);
                if (block) {
                    templateCanvas.appendChild(block);
                } else {
                    console.error('Failed to create block for:', blockData);
                }
            });
        }
    } catch (e) {
        console.error('Error loading blocks:', e);
        console.error('Stack:', e.stack);
    }
}

function updateTemplateBlocks() {
    const canvas = document.getElementById('templateCanvas');
    const blocks = [];
    
    canvas.querySelectorAll('.canvas-block').forEach((block) => {
        const blockData = {
            id: block.dataset.blockId,
            type: block.dataset.blockType,
            config: {}
        };
        
        // Get configuration if it exists
        if (block.dataset.blockConfig) {
            try {
                blockData.config = JSON.parse(block.dataset.blockConfig);
            } catch (e) {
                console.error('Error parsing block config:', e);
            }
        }
        
        blocks.push(blockData);
    });
    
    console.log('UpdateTemplateBlocks - Found blocks:', blocks);
    
    // Update hidden input with layout config
    const layoutConfig = {
        blocks: blocks,
        settings: {
            color_scheme: document.getElementById('color_scheme').value,
            font_family: document.getElementById('font_family').value,
            font_size: document.getElementById('font_size').value,
            logo_position: document.getElementById('logo_position').value
        }
    };
    
    document.getElementById('layout_config').value = JSON.stringify(layoutConfig);
    document.getElementById('block_positions').value = JSON.stringify(blocks);
    
    console.log('UpdateTemplateBlocks - Saved to hidden inputs:', {
        layout_config: layoutConfig,
        block_positions: blocks
    });
}

function removeBlock(blockId) {
    console.log('RemoveBlock called for:', blockId);
    
    // Vraag om bevestiging
    if (!confirm('Are you sure you want to remove this block from the template?')) {
        console.log('User cancelled removal');
        return;
    }
    
    const block = document.querySelector(`[data-block-id="${blockId}"]`);
    console.log('Found block:', block);
    
    if (block) {
        // Verwijder het block uit de DOM
        block.remove();
        console.log('Block removed from DOM');
        
        // Update de template blocks in het hidden input field
        updateTemplateBlocks();
        
        // Toon empty state als er geen blocks meer zijn
        const canvas = document.getElementById('templateCanvas');
        const remainingBlocks = canvas.querySelectorAll('.canvas-block');
        console.log('Remaining blocks:', remainingBlocks.length);
        
        if (remainingBlocks.length === 0) {
            canvas.innerHTML = `
                <div class="empty-state text-center py-16 text-slate-400">
                    <svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="text-sm font-medium">No blocks in template</p>
                    <p class="text-xs mt-1">Drag blocks here to build your invoice template</p>
                </div>
            `;
        }
        
        console.log('Block successfully removed:', blockId);
    } else {
        console.error('Block not found with id:', blockId);
        alert('Error: Could not find block to remove. Please refresh the page and try again.');
    }
}

function configureBlock(blockId) {
    const block = document.querySelector(`[data-block-id="${blockId}"]`);
    if (!block) return;
    
    currentEditingBlock = block;
    const blockType = block.dataset.blockType;
    const existingConfig = block.dataset.blockConfig ? JSON.parse(block.dataset.blockConfig) : {};
    
    // Get block display name
    const blockNames = {
        'header': 'Header',
        'company_info': 'Company Information',
        'customer_info': 'Customer Information',
        'invoice_details': 'Invoice Details',
        'project_info': 'Project Information',
        'line_items': 'Invoice Line Items',
        'time_entries': 'Time Entries',
        'hours_summary': 'Hours Summary',
        'hours_per_user': 'Hours per User',
        'hours_per_milestone': 'Hours per Milestone',
        'hours_per_task': 'Hours per Task',
        'weekly_hours': 'Weekly Hours Overview',
        'monthly_hours': 'Monthly Hours Overview',
        'budget_overview': 'Budget Overview',
        'additional_costs': 'Additional Costs',
        'subtotal': 'Subtotal',
        'tax_section': 'Tax/VAT Section',
        'discount_section': 'Discount Section',
        'total_amount': 'Total Amount',
        'payment_terms': 'Payment Terms',
        'bank_details': 'Bank Details',
        'notes': 'Notes/Comments',
        'footer': 'Footer',
        'qr_code': 'QR Code',
        'signature_section': 'Signature Section'
    };
    
    // Update modal title and subtitle based on whether editing or configuring
    const modalTitle = document.querySelector('#blockConfigModal h3');
    const subtitle = document.getElementById('blockConfigSubtitle');
    const isEditing = existingConfig && Object.keys(existingConfig).length > 0;
    
    if (modalTitle) {
        modalTitle.textContent = isEditing ? 'Edit Block Configuration' : 'Configure Block';
    }
    if (subtitle) {
        subtitle.textContent = isEditing ? 
            `Update settings for the ${blockNames[blockType] || blockType} block` :
            `Configure settings for the ${blockNames[blockType] || blockType} block`;
    }
    
    // Show modal
    const modal = document.getElementById('blockConfigModal');
    modal.classList.remove('hidden');
    
    // Load configuration UI - use improved version if available
    const configContent = document.getElementById('modalBlockConfigContent');
    if (typeof getImprovedBlockConfigHTML === 'function') {
        configContent.innerHTML = getImprovedBlockConfigHTML(blockType, existingConfig);
    } else {
        configContent.innerHTML = getBlockConfigHTML(blockType, existingConfig);
    }
    
    // Add click handler to backdrop
    modal.onclick = function(e) {
        if (e.target === modal) {
            closeBlockConfig();
        }
    };
}

function closeBlockConfig() {
    document.getElementById('blockConfigModal').classList.add('hidden');
    currentEditingBlock = null;
}

function saveBlockConfig() {
    if (!currentEditingBlock) return;
    
    const blockType = currentEditingBlock.dataset.blockType;
    const config = {};
    
    // Get all configuration values based on block type
    const configElements = document.querySelectorAll('#modalBlockConfigContent input, #modalBlockConfigContent select, #modalBlockConfigContent textarea');
    configElements.forEach(element => {
        const key = element.id.replace('config_', '');
        if (element.type === 'checkbox') {
            config[key] = element.checked;
        } else {
            config[key] = element.value;
        }
    });
    
    // Store configuration
    currentEditingBlock.dataset.blockConfig = JSON.stringify(config);
    currentEditingBlock.classList.add('configured');
    
    // Update template blocks
    updateTemplateBlocks();
    
    // Close modal
    closeBlockConfig();
}

function getBlockConfigHTML(blockType, config) {
    config = config || {};
    var html = '';
    
    switch(blockType) {
        case 'header':
            html += '<div class="space-y-6">';
            
            // Text Customization
            html += '<div class="bg-slate-50 border border-slate-200 rounded-xl p-6">';
            html += '<h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center">';
            html += '<svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
            html += '</svg>';
            html += 'Text Customization</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_invoice_title" class="block text-xs font-medium text-slate-600 mb-1">Invoice Title</label>';
            html += '<input type="text" id="config_invoice_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.invoice_title || 'INVOICE') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_invoice_subtitle" class="block text-xs font-medium text-slate-600 mb-1">Invoice Subtitle (optional)</label>';
            html += '<input type="text" id="config_invoice_subtitle" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.invoice_subtitle || '') + '" placeholder="e.g., Tax Invoice, Proforma Invoice">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Style Settings
            html += '<div class="bg-slate-50 border border-slate-200 rounded-xl p-6">';
            html += '<h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center">';
            html += '<svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>';
            html += '</svg>';
            html += 'Style Settings</h4>';
            html += '<label for="config_header_style" class="block text-sm font-medium text-slate-700 mb-2">Header Style</label>';
            html += '<select id="config_header_style" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500">';
            html += '<option value="standard"' + ((config.header_style === 'standard' || !config.header_style) ? ' selected' : '') + '>Standard - Logo and Title</option>';
            html += '<option value="minimal"' + (config.header_style === 'minimal' ? ' selected' : '') + '>Minimal - Title Only</option>';
            html += '<option value="modern"' + (config.header_style === 'modern' ? ' selected' : '') + '>Modern - Large Logo</option>';
            html += '</select>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-slate-50 border border-slate-200 rounded-xl p-6">';
            html += '<h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center">';
            html += '<svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            html += '</svg>';
            html += 'Display Options</h4>';
            html += '<div class="space-y-2">';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_logo"' + (config.show_logo !== false ? ' checked' : '') + ' onchange="toggleLogoOptions()" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Company Logo</span>';
            html += '</label>';
            
            html += '<div id="logoPositionOption" style="' + (config.show_logo === false ? 'display:none' : '') + '" class="ml-6 mt-2 space-y-3">';
            html += '<div>';
            html += '<label for="config_logo_position" class="block text-xs font-medium text-slate-600 mb-1">Logo Position in Header</label>';
            html += '<select id="config_logo_position" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg">';
            html += '<option value="left"' + (config.logo_position === 'left' ? ' selected' : '') + '>Left (with company info)</option>';
            html += '<option value="center"' + (config.logo_position === 'center' ? ' selected' : '') + '>Center (above all)</option>';
            html += '<option value="right"' + (config.logo_position === 'right' ? ' selected' : '') + '>Right (with invoice number)</option>';
            html += '<option value="full-width"' + (config.logo_position === 'full-width' ? ' selected' : '') + '>Full Width (spanning entire header)</option>';
            html += '</select>';
            html += '</div>';
            
            html += '<div>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-2 -mx-2 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_logo_full_width"' + (config.logo_full_width === true ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-2 text-xs text-slate-700">Stretch logo to full width</span>';
            html += '</label>';
            html += '<p class="text-xs text-slate-500 ml-6 mt-1">When enabled, logo will stretch across the entire header width</p>';
            html += '</div>';
            html += '</div>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_use_template_logo"' + (config.use_template_logo !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Use Template Logo (if uploaded)</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_company_name"' + (config.show_company_name !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Company Name</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_invoice_number"' + (config.show_invoice_number !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Invoice Number in Header</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'company_info':
            html += '<div class="space-y-6">';
            
            // Section Settings
            html += '<div class="bg-slate-50 border border-slate-200 rounded-xl p-6">';
            html += '<h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center">';
            html += '<svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>';
            html += '</svg>';
            html += 'Section Settings</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label class="flex items-center cursor-pointer hover:bg-white p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_section_title"' + (config.show_section_title !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show section title (From:)</span>';
            html += '</label>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_company_section_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title (if shown)</label>';
            html += '<input type="text" id="config_company_section_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.company_section_title || 'From:') + '" placeholder="e.g., From:, Sender:, Our Company">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_vat_label" class="block text-xs font-medium text-slate-600 mb-1">VAT Number Label</label>';
            html += '<input type="text" id="config_vat_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.vat_label || 'VAT:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_coc_label" class="block text-xs font-medium text-slate-600 mb-1">CoC Number Label</label>';
            html += '<input type="text" id="config_coc_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.coc_label || 'CoC:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_phone_label" class="block text-xs font-medium text-slate-600 mb-1">Phone Label</label>';
            html += '<input type="text" id="config_phone_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.phone_label || 'Phone:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_email_label" class="block text-xs font-medium text-slate-600 mb-1">Email Label</label>';
            html += '<input type="text" id="config_email_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.email_label || 'Email:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_website_label" class="block text-xs font-medium text-slate-600 mb-1">Website Label</label>';
            html += '<input type="text" id="config_website_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.website_label || 'Web:') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Company Details Override
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Company Information (Override if needed)</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_company_name" class="block text-xs font-medium text-slate-600 mb-1">Company Name</label>';
            html += '<input type="text" id="config_company_name" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.company_name || '') + '" placeholder="Leave empty to use default">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_company_address" class="block text-xs font-medium text-slate-600 mb-1">Address</label>';
            html += '<textarea id="config_company_address" rows="2" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" placeholder="Leave empty to use default">' + (config.company_address || '') + '</textarea>';
            html += '</div>';
            html += '<div class="grid grid-cols-2 gap-3">';
            html += '<div>';
            html += '<label for="config_company_phone" class="block text-xs font-medium text-slate-600 mb-1">Phone</label>';
            html += '<input type="text" id="config_company_phone" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.company_phone || '') + '" placeholder="e.g., +31 20 123 4567">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_company_email" class="block text-xs font-medium text-slate-600 mb-1">Email</label>';
            html += '<input type="email" id="config_company_email" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.company_email || '') + '" placeholder="e.g., info@company.com">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_company_vat" class="block text-xs font-medium text-slate-600 mb-1">VAT Number</label>';
            html += '<input type="text" id="config_company_vat" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.company_vat || '') + '" placeholder="e.g., NL123456789B01">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_company_coc" class="block text-xs font-medium text-slate-600 mb-1">CoC Number</label>';
            html += '<input type="text" id="config_company_coc" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.company_coc || '') + '" placeholder="e.g., 12345678">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Position Settings
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_company_position" class="block text-sm font-medium text-slate-700 mb-2">Position on Invoice</label>';
            html += '<select id="config_company_position" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500">';
            html += '<option value="left"' + ((config.company_position === 'left' || !config.company_position) ? ' selected' : '') + '>Left Side (Default)</option>';
            html += '<option value="right"' + (config.company_position === 'right' ? ' selected' : '') + '>Right Side</option>';
            html += '<option value="center"' + (config.company_position === 'center' ? ' selected' : '') + '>Center</option>';
            html += '<option value="full_width"' + (config.company_position === 'full_width' ? ' selected' : '') + '>Full Width</option>';
            html += '</select>';
            html += '</div>';
            
            // Show/Hide Fields
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Show/Hide Fields</h4>';
            html += '<div class="grid grid-cols-2 gap-x-4 gap-y-2">';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_company_name"' + (config.show_company_name !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Company Name</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_company_address"' + (config.show_company_address !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Address</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_vat"' + (config.show_vat !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show VAT Number</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_coc"' + (config.show_coc !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show CoC/KVK Number</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_email"' + (config.show_email !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Email</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_phone"' + (config.show_phone !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Phone</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_website"' + (config.show_website ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">'; 
            html += '<span class="ml-3 text-sm text-slate-700">Show Website</span>';
            html += '</label>';
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'customer_info':
            html += '<div class="space-y-6">';
            
            // Section Settings
            html += '<div class="bg-slate-50 border border-slate-200 rounded-xl p-6">';
            html += '<h4 class="text-base font-semibold text-slate-800 mb-4 flex items-center">';
            html += '<svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>';
            html += '</svg>';
            html += 'Section Settings</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label class="flex items-center cursor-pointer hover:bg-white p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_section_title"' + (config.show_section_title !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show section title (To:/Bill To:)</span>';
            html += '</label>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_section_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title (if shown)</label>';
            html += '<input type="text" id="config_section_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.section_title || 'Bill To:') + '" placeholder="e.g., To:, Bill To:, Customer, Invoice To">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_attn_label" class="block text-xs font-medium text-slate-600 mb-1">Contact Person Label</label>';
            html += '<input type="text" id="config_attn_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.attn_label || 'Attn:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_email_label" class="block text-xs font-medium text-slate-600 mb-1">Email Label</label>';
            html += '<input type="text" id="config_email_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.email_label || 'Email:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_phone_label" class="block text-xs font-medium text-slate-600 mb-1">Phone Label</label>';
            html += '<input type="text" id="config_phone_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.phone_label || 'Phone:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_vat_label" class="block text-xs font-medium text-slate-600 mb-1">VAT Number Label</label>';
            html += '<input type="text" id="config_vat_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.vat_label || 'VAT:') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Position Settings
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_customer_position" class="block text-sm font-medium text-slate-700 mb-2">Position on Invoice</label>';
            html += '<select id="config_customer_position" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500">';
            html += '<option value="left"' + (config.customer_position === 'left' ? ' selected' : '') + '>Left Side</option>';
            html += '<option value="right"' + ((config.customer_position === 'right' || !config.customer_position) ? ' selected' : '') + '>Right Side (Default)</option>';
            html += '<option value="center"' + (config.customer_position === 'center' ? ' selected' : '') + '>Center</option>';
            html += '</select>';
            html += '</div>';
            
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Show/Hide Fields</h4>';
            html += '<div class="space-y-2">';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_customer_company"' + (config.show_customer_company !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Company Name</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_customer_vat"' + (config.show_customer_vat ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show VAT Number</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_customer_email"' + (config.show_customer_email !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Email</span>';
            html += '</label>';
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'line_items':
            html += '<div class="space-y-6">';
            
            // Column Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Column Labels</h4>';
            html += '<div class="grid grid-cols-2 gap-3">';
            html += '<div>';
            html += '<label for="config_description_label" class="block text-xs font-medium text-slate-600 mb-1">Description Label</label>';
            html += '<input type="text" id="config_description_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.description_label || 'Description') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_quantity_label" class="block text-xs font-medium text-slate-600 mb-1">Quantity Label</label>';
            html += '<input type="text" id="config_quantity_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.quantity_label || 'Qty') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_rate_label" class="block text-xs font-medium text-slate-600 mb-1">Rate/Price Label</label>';
            html += '<input type="text" id="config_rate_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.rate_label || 'Rate') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_amount_label" class="block text-xs font-medium text-slate-600 mb-1">Amount Label</label>';
            html += '<input type="text" id="config_amount_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.amount_label || 'Amount') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_tax_label" class="block text-xs font-medium text-slate-600 mb-1">Tax/VAT Label</label>';
            html += '<input type="text" id="config_tax_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.tax_label || 'Tax') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_item_code_label" class="block text-xs font-medium text-slate-600 mb-1">Item Code Label</label>';
            html += '<input type="text" id="config_item_code_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.item_code_label || 'Item Code') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_item_codes"' + (config.show_item_codes ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Item Codes</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_group_by_milestone"' + (config.group_by_milestone ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Group by Milestone</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_descriptions"' + (config.show_descriptions !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Task Descriptions</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_quantity"' + (config.show_quantity !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Quantity Column</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_rate"' + (config.show_rate !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Rate Column</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_tax"' + (config.show_tax ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Tax/VAT Column</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            
            // Table Style
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_table_style" class="block text-sm font-medium text-slate-700 mb-2">Table Style</label>';
            html += '<select id="config_table_style" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500">';
            html += '<option value="standard"' + ((config.table_style === 'standard' || !config.table_style) ? ' selected' : '') + '>Standard - With borders</option>';
            html += '<option value="striped"' + (config.table_style === 'striped' ? ' selected' : '') + '>Striped - Alternating rows</option>';
            html += '<option value="minimal"' + (config.table_style === 'minimal' ? ' selected' : '') + '>Minimal - Clean lines</option>';
            html += '<option value="modern"' + (config.table_style === 'modern' ? ' selected' : '') + '>Modern - Colored header</option>';
            html += '</select>';
            html += '</div>';
            
            html += '</div>';
            break;
            
        case 'invoice_details':
            html += '<div class="space-y-6">';
            
            // Text Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Field Labels</h4>';
            html += '<div class="grid grid-cols-2 gap-3">';
            html += '<div>';
            html += '<label for="config_invoice_number_label" class="block text-xs font-medium text-slate-600 mb-1">Invoice Number Label</label>';
            html += '<input type="text" id="config_invoice_number_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.invoice_number_label || 'Invoice Number') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_invoice_date_label" class="block text-xs font-medium text-slate-600 mb-1">Invoice Date Label</label>';
            html += '<input type="text" id="config_invoice_date_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.invoice_date_label || 'Invoice Date') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_due_date_label" class="block text-xs font-medium text-slate-600 mb-1">Due Date Label</label>';
            html += '<input type="text" id="config_due_date_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.due_date_label || 'Due Date') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_payment_terms_label" class="block text-xs font-medium text-slate-600 mb-1">Payment Terms Label</label>';
            html += '<input type="text" id="config_payment_terms_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.payment_terms_label || 'Payment Terms') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_po_number_label" class="block text-xs font-medium text-slate-600 mb-1">PO Number Label</label>';
            html += '<input type="text" id="config_po_number_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.po_number_label || 'PO Number') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_status_label" class="block text-xs font-medium text-slate-600 mb-1">Status Label</label>';
            html += '<input type="text" id="config_status_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.status_label || 'Status') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Format & Values
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Format & Default Values</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_invoice_number_format" class="block text-xs font-medium text-slate-600 mb-1">Invoice Number Format</label>';
            html += '<input type="text" id="config_invoice_number_format" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.invoice_number_format || 'INV-{number}') + '" placeholder="e.g., INV-{number}, {year}-{number}">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_date_format" class="block text-xs font-medium text-slate-600 mb-1">Date Format</label>';
            html += '<select id="config_date_format" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg">';
            html += '<option value="DD/MM/YYYY"' + (config.date_format === 'DD/MM/YYYY' ? ' selected' : '') + '>DD/MM/YYYY (31/12/2024)</option>';
            html += '<option value="MM/DD/YYYY"' + (config.date_format === 'MM/DD/YYYY' ? ' selected' : '') + '>MM/DD/YYYY (12/31/2024)</option>';
            html += '<option value="YYYY-MM-DD"' + (config.date_format === 'YYYY-MM-DD' ? ' selected' : '') + '>YYYY-MM-DD (2024-12-31)</option>';
            html += '<option value="DD MMM YYYY"' + (config.date_format === 'DD MMM YYYY' ? ' selected' : '') + '>DD MMM YYYY (31 Dec 2024)</option>';
            html += '<option value="MMMM DD, YYYY"' + (config.date_format === 'MMMM DD, YYYY' ? ' selected' : '') + '>MMMM DD, YYYY (December 31, 2024)</option>';
            html += '</select>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_payment_terms_text" class="block text-xs font-medium text-slate-600 mb-1">Default Payment Terms Text</label>';
            html += '<input type="text" id="config_payment_terms_text" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.payment_terms_text || 'Net 30 days') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_separator" class="block text-xs font-medium text-slate-600 mb-1">Label Separator</label>';
            html += '<select id="config_separator" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg">';
            html += '<option value=":"' + (config.separator === ':' ? ' selected' : '') + '>Colon (:)</option>';
            html += '<option value=" -"' + (config.separator === ' -' ? ' selected' : '') + '>Dash (-)</option>';
            html += '<option value=""' + (config.separator === '' ? ' selected' : '') + '>None</option>';
            html += '</select>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_invoice_number"' + (config.show_invoice_number !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Invoice Number</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_invoice_date"' + (config.show_invoice_date !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Invoice Date</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_due_date"' + (config.show_due_date !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Due Date</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_payment_terms"' + (config.show_payment_terms ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Payment Terms</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_purchase_order"' + (config.show_purchase_order ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Purchase Order Number</span>';
            html += '</label>';
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'payment_terms':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_payment_text" class="block text-sm font-medium text-slate-700 mb-2">Payment Terms Text</label>';
            html += '<textarea id="config_payment_text" rows="3" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500">' + (config.payment_text || 'Payment is due within 30 days from invoice date.') + '</textarea>';
            html += '</div>';
            
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_late_fee"' + (config.show_late_fee ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Late Payment Fee Information</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_highlight_terms"' + (config.highlight_terms ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Highlight Payment Terms</span>';
            html += '</label>';
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'bank_details':
            html += '<div class="space-y-6">';
            
            // Field Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Field Labels</h4>';
            html += '<div class="grid grid-cols-2 gap-3">';
            html += '<div>';
            html += '<label for="config_bank_name_label" class="block text-xs font-medium text-slate-600 mb-1">Bank Name Label</label>';
            html += '<input type="text" id="config_bank_name_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.bank_name_label || 'Bank:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_account_name_label" class="block text-xs font-medium text-slate-600 mb-1">Account Name Label</label>';
            html += '<input type="text" id="config_account_name_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.account_name_label || 'Account Name:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_iban_label" class="block text-xs font-medium text-slate-600 mb-1">IBAN Label</label>';
            html += '<input type="text" id="config_iban_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.iban_label || 'IBAN:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_bic_label" class="block text-xs font-medium text-slate-600 mb-1">BIC/SWIFT Label</label>';
            html += '<input type="text" id="config_bic_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.bic_label || 'BIC/SWIFT:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_reference_label" class="block text-xs font-medium text-slate-600 mb-1">Payment Reference Label</label>';
            html += '<input type="text" id="config_reference_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.reference_label || 'Reference:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_payment_instruction_label" class="block text-xs font-medium text-slate-600 mb-1">Payment Instructions Label</label>';
            html += '<input type="text" id="config_payment_instruction_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.payment_instruction_label || 'Payment Instructions:') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Bank Information Display
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Show/Hide Fields</h4>';
            html += '<div class="space-y-2">';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_bank_name"' + (config.show_bank_name !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Bank Name</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_account_name"' + (config.show_account_name !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Account Name</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_iban"' + (config.show_iban !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show IBAN</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_bic"' + (config.show_bic !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show BIC/SWIFT</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_reference"' + (config.show_reference !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Payment Reference</span>';
            html += '</label>';
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'notes':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_notes_title" class="block text-sm font-medium text-slate-700 mb-2">Section Title</label>';
            html += '<input type="text" id="config_notes_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500" value="' + (config.notes_title || 'Notes') + '">';
            html += '</div>';
            
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_default_notes" class="block text-sm font-medium text-slate-700 mb-2">Default Notes Text</label>';
            html += '<textarea id="config_default_notes" rows="4" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500">' + (config.default_notes || '') + '</textarea>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'total_amount':
            html += '<div class="space-y-6">';
            
            // Text Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Text Labels</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_subtotal_label" class="block text-xs font-medium text-slate-600 mb-1">Subtotal Label</label>';
            html += '<input type="text" id="config_subtotal_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.subtotal_label || 'Subtotal') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_tax_total_label" class="block text-xs font-medium text-slate-600 mb-1">Tax/VAT Total Label</label>';
            html += '<input type="text" id="config_tax_total_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.tax_total_label || 'VAT 21%') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_discount_label" class="block text-xs font-medium text-slate-600 mb-1">Discount Label</label>';
            html += '<input type="text" id="config_discount_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.discount_label || 'Discount') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_total_label" class="block text-xs font-medium text-slate-600 mb-1">Total Label</label>';
            html += '<input type="text" id="config_total_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.total_label || 'Total Due') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_paid_label" class="block text-xs font-medium text-slate-600 mb-1">Amount Paid Label</label>';
            html += '<input type="text" id="config_paid_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.paid_label || 'Amount Paid') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_balance_label" class="block text-xs font-medium text-slate-600 mb-1">Balance Due Label</label>';
            html += '<input type="text" id="config_balance_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.balance_label || 'Balance Due') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_subtotal"' + (config.show_subtotal !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Subtotal</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_tax_breakdown"' + (config.show_tax_breakdown !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Tax/VAT Breakdown</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_discount"' + (config.show_discount ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Discount (if applicable)</span>';
            html += '</label>';
            
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_highlight_total"' + (config.highlight_total !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">';
            html += '<span class="ml-3 text-sm text-slate-700">Highlight Total Amount</span>';
            html += '</label>';
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'project_info':
            html += '<div class="space-y-6">';
            
            // Field Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Field Labels</h4>';
            html += '<div class="grid grid-cols-2 gap-3">';
            html += '<div>';
            html += '<label for="config_project_label" class="block text-xs font-medium text-slate-600 mb-1">Project Label</label>';
            html += '<input type="text" id="config_project_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.project_label || 'Project') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_project_number_label" class="block text-xs font-medium text-slate-600 mb-1">Project Number Label</label>';
            html += '<input type="text" id="config_project_number_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.project_number_label || 'Project #') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_period_label" class="block text-xs font-medium text-slate-600 mb-1">Period Label</label>';
            html += '<input type="text" id="config_period_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.period_label || 'Period') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_manager_label" class="block text-xs font-medium text-slate-600 mb-1">Project Manager Label</label>';
            html += '<input type="text" id="config_manager_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.manager_label || 'Project Manager') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_location_label" class="block text-xs font-medium text-slate-600 mb-1">Location Label</label>';
            html += '<input type="text" id="config_location_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.location_label || 'Location') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_reference_label" class="block text-xs font-medium text-slate-600 mb-1">Reference Label</label>';
            html += '<input type="text" id="config_reference_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.reference_label || 'Reference') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_project_number"' + (config.show_project_number !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Project Number</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_period"' + (config.show_period ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Period</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_manager"' + (config.show_manager ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Project Manager</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_location"' + (config.show_location ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Location</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'qr_code':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">QR Code Settings</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_qr_type" class="block text-xs font-medium text-slate-600 mb-1">QR Code Content</label>';
            html += '<select id="config_qr_type" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg">';
            html += '<option value="payment"' + (config.qr_type === 'payment' ? ' selected' : '') + '>Payment Information</option>';
            html += '<option value="invoice_url"' + (config.qr_type === 'invoice_url' ? ' selected' : '') + '>Invoice URL</option>';
            html += '<option value="custom"' + (config.qr_type === 'custom' ? ' selected' : '') + '>Custom Text</option>';
            html += '</select>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_qr_custom_text" class="block text-xs font-medium text-slate-600 mb-1">Custom QR Text (if custom selected)</label>';
            html += '<input type="text" id="config_qr_custom_text" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.qr_custom_text || '') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_qr_size" class="block text-xs font-medium text-slate-600 mb-1">QR Code Size</label>';
            html += '<select id="config_qr_size" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg">';
            html += '<option value="small"' + (config.qr_size === 'small' ? ' selected' : '') + '>Small (100x100)</option>';
            html += '<option value="medium"' + ((config.qr_size === 'medium' || !config.qr_size) ? ' selected' : '') + '>Medium (150x150)</option>';
            html += '<option value="large"' + (config.qr_size === 'large' ? ' selected' : '') + '>Large (200x200)</option>';
            html += '</select>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_qr_label" class="block text-xs font-medium text-slate-600 mb-1">QR Code Label</label>';
            html += '<input type="text" id="config_qr_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.qr_label || 'Scan to pay') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'subtotal':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Subtotal Configuration</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_subtotal_label" class="block text-xs font-medium text-slate-600 mb-1">Subtotal Label</label>';
            html += '<input type="text" id="config_subtotal_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.subtotal_label || 'Subtotal') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_line_count" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_line_count"' + (config.show_line_count ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show number of line items</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'time_entries':
            html += '<div class="space-y-6">';
            
            // Column Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Column Labels</h4>';
            html += '<div class="grid grid-cols-2 gap-3">';
            html += '<div>';
            html += '<label for="config_date_label" class="block text-xs font-medium text-slate-600 mb-1">Date Column Label</label>';
            html += '<input type="text" id="config_date_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.date_label || 'Date') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_user_label" class="block text-xs font-medium text-slate-600 mb-1">User Column Label</label>';
            html += '<input type="text" id="config_user_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.user_label || 'User') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_description_label" class="block text-xs font-medium text-slate-600 mb-1">Description Column Label</label>';
            html += '<input type="text" id="config_description_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.description_label || 'Description') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_hours_label" class="block text-xs font-medium text-slate-600 mb-1">Hours Column Label</label>';
            html += '<input type="text" id="config_hours_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.hours_label || 'Hours') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_rate_label" class="block text-xs font-medium text-slate-600 mb-1">Rate Column Label</label>';
            html += '<input type="text" id="config_rate_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.rate_label || 'Rate') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_amount_label" class="block text-xs font-medium text-slate-600 mb-1">Amount Column Label</label>';
            html += '<input type="text" id="config_amount_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.amount_label || 'Amount') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_date"' + (config.show_date !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Date Column</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_user"' + (config.show_user !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show User Column</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_description"' + (config.show_description !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Description Column</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_rate"' + (config.show_rate ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Rate Column</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_group_by_date"' + (config.group_by_date ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Group by Date</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_group_by_user"' + (config.group_by_user ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Group by User</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'budget_overview':
            html += '<div class="space-y-6">';
            
            // Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Labels & Text</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_budget_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_budget_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.budget_title || 'Budget Overview') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_allocated_label" class="block text-xs font-medium text-slate-600 mb-1">Allocated Budget Label</label>';
            html += '<input type="text" id="config_allocated_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.allocated_label || 'Allocated Budget:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_used_label" class="block text-xs font-medium text-slate-600 mb-1">Used Budget Label</label>';
            html += '<input type="text" id="config_used_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.used_label || 'Used Budget:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_remaining_label" class="block text-xs font-medium text-slate-600 mb-1">Remaining Budget Label</label>';
            html += '<input type="text" id="config_remaining_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.remaining_label || 'Remaining Budget:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_percentage_label" class="block text-xs font-medium text-slate-600 mb-1">Percentage Label</label>';
            html += '<input type="text" id="config_percentage_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.percentage_label || 'Percentage Used:') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_allocated"' + (config.show_allocated !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Allocated Budget</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_used"' + (config.show_used !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Used Budget</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_remaining"' + (config.show_remaining !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Remaining Budget</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_percentage"' + (config.show_percentage !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Percentage</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_progress_bar"' + (config.show_progress_bar ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Progress Bar</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'additional_costs':
            html += '<div class="space-y-6">';
            
            // Labels
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Labels & Text</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_costs_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_costs_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.costs_title || 'Additional Costs') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_description_label" class="block text-xs font-medium text-slate-600 mb-1">Description Label</label>';
            html += '<input type="text" id="config_description_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.description_label || 'Description') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_amount_label" class="block text-xs font-medium text-slate-600 mb-1">Amount Label</label>';
            html += '<input type="text" id="config_amount_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.amount_label || 'Amount') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            // Display Options
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Display Options</h4>';
            html += '<div class="space-y-2">';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_date"' + (config.show_date ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Date</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_category"' + (config.show_category ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Category</span>';
            html += '</label>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_group_by_category"' + (config.group_by_category ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Group by Category</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'footer':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_footer_text" class="block text-sm font-medium text-slate-700 mb-2">Footer Text</label>';
            html += '<textarea id="config_footer_text" rows="3" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg">' + (config.footer_text || 'Thank you for your business!') + '</textarea>';
            html += '</div>';
            
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<label for="config_footer_note" class="block text-sm font-medium text-slate-700 mb-2">Additional Footer Note</label>';
            html += '<input type="text" id="config_footer_note" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.footer_note || '') + '" placeholder="e.g., Please include invoice number with payment">';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'tax_section':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Tax Settings</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_tax_rate" class="block text-xs font-medium text-slate-600 mb-1">Default Tax Rate (%)</label>';
            html += '<input type="number" id="config_tax_rate" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.tax_rate || '21') + '" min="0" max="100" step="0.01">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_tax_name" class="block text-xs font-medium text-slate-600 mb-1">Tax Name</label>';
            html += '<input type="text" id="config_tax_name" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.tax_name || 'VAT') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_tax_number_label" class="block text-xs font-medium text-slate-600 mb-1">Tax Number Label</label>';
            html += '<input type="text" id="config_tax_number_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.tax_number_label || 'VAT Registration:') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'discount_section':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Discount Settings</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_discount_type" class="block text-xs font-medium text-slate-600 mb-1">Discount Type</label>';
            html += '<select id="config_discount_type" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg">';
            html += '<option value="percentage"' + (config.discount_type === 'percentage' ? ' selected' : '') + '>Percentage (%)</option>';
            html += '<option value="fixed"' + (config.discount_type === 'fixed' ? ' selected' : '') + '>Fixed Amount</option>';
            html += '</select>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_discount_label" class="block text-xs font-medium text-slate-600 mb-1">Discount Label</label>';
            html += '<input type="text" id="config_discount_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.discount_label || 'Discount') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_discount_reason" class="block text-xs font-medium text-slate-600 mb-1">Default Discount Reason</label>';
            html += '<input type="text" id="config_discount_reason" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.discount_reason || '') + '" placeholder="e.g., Early payment discount">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'signature_section':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Signature Settings</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_signature_title" class="block text-xs font-medium text-slate-600 mb-1">Signature Title</label>';
            html += '<input type="text" id="config_signature_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.signature_title || 'Authorized Signature') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_signature_name_label" class="block text-xs font-medium text-slate-600 mb-1">Name Label</label>';
            html += '<input type="text" id="config_signature_name_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.signature_name_label || 'Name:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_signature_date_label" class="block text-xs font-medium text-slate-600 mb-1">Date Label</label>';
            html += '<input type="text" id="config_signature_date_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.signature_date_label || 'Date:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_signature_line"' + (config.show_signature_line !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Signature Line</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'hours_summary':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Hours Summary Configuration</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_summary_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_summary_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.summary_title || 'Hours Summary') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_total_hours_label" class="block text-xs font-medium text-slate-600 mb-1">Total Hours Label</label>';
            html += '<input type="text" id="config_total_hours_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.total_hours_label || 'Total Hours:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_billable_hours_label" class="block text-xs font-medium text-slate-600 mb-1">Billable Hours Label</label>';
            html += '<input type="text" id="config_billable_hours_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.billable_hours_label || 'Billable Hours:') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_non_billable_label" class="block text-xs font-medium text-slate-600 mb-1">Non-Billable Hours Label</label>';
            html += '<input type="text" id="config_non_billable_label" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.non_billable_label || 'Non-Billable Hours:') + '">';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'hours_per_user':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Hours per User Configuration</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_user_hours_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_user_hours_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.user_hours_title || 'Hours per Team Member') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_hourly_rate" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_hourly_rate"' + (config.show_hourly_rate ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Hourly Rate</span>';
            html += '</label>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_total_cost" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_total_cost"' + (config.show_total_cost ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Total Cost</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'hours_per_milestone':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Hours per Milestone Configuration</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_milestone_hours_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_milestone_hours_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.milestone_hours_title || 'Hours per Milestone') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_percentage" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_percentage"' + (config.show_percentage !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Percentage of Total</span>';
            html += '</label>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_budget" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_budget"' + (config.show_budget ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Budget vs Actual</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'hours_per_task':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Hours per Task Configuration</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_task_hours_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_task_hours_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.task_hours_title || 'Hours per Task') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_group_by_milestone" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_group_by_milestone"' + (config.group_by_milestone !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Group by Milestone</span>';
            html += '</label>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_subtasks" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_subtasks"' + (config.show_subtasks ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Include Subtasks</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'weekly_hours':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Weekly Hours Configuration</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_weekly_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_weekly_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.weekly_title || 'Weekly Hours Overview') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_weeks_to_show" class="block text-xs font-medium text-slate-600 mb-1">Number of Weeks to Show</label>';
            html += '<input type="number" id="config_weeks_to_show" min="1" max="12" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.weeks_to_show || 4) + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_chart" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_chart"' + (config.show_chart !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Chart</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        case 'monthly_hours':
            html += '<div class="space-y-6">';
            html += '<div class="bg-white border border-slate-200 rounded-lg p-4">';
            html += '<h4 class="text-sm font-medium text-slate-700 mb-3">Monthly Hours Configuration</h4>';
            html += '<div class="space-y-3">';
            html += '<div>';
            html += '<label for="config_monthly_title" class="block text-xs font-medium text-slate-600 mb-1">Section Title</label>';
            html += '<input type="text" id="config_monthly_title" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.monthly_title || 'Monthly Hours Overview') + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_months_to_show" class="block text-xs font-medium text-slate-600 mb-1">Number of Months to Show</label>';
            html += '<input type="number" id="config_months_to_show" min="1" max="12" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg" value="' + (config.months_to_show || 3) + '">';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_trend" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_trend"' + (config.show_trend !== false ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Trend Line</span>';
            html += '</label>';
            html += '</div>';
            html += '<div>';
            html += '<label for="config_show_comparison" class="flex items-center cursor-pointer hover:bg-slate-50 p-3 -mx-3 rounded-lg transition-colors">';
            html += '<input type="checkbox" id="config_show_comparison"' + (config.show_comparison ? ' checked' : '') + ' class="rounded border-slate-300 text-primary-600">';
            html += '<span class="ml-3 text-sm text-slate-700">Show Year-over-Year Comparison</span>';
            html += '</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            break;
            
        default:
            html += '<div class="bg-slate-50 rounded-lg p-8 text-center">';
            html += '<svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>';
            html += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
            html += '</svg>';
            html += '<p class="text-sm font-medium text-slate-700 mb-1">Configuration Options</p>';
            html += '<p class="text-xs text-slate-500">Settings for this block will be available soon</p>';
            html += '</div>';
            break;
    }
    
    return html;
}

function openPreviewWindow() {
    // Save current state
    updateTemplateBlocks();
    
    // Create a form to submit the current template state
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('invoice-templates.preview', $invoiceTemplate) }}';
    form.target = 'preview';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Visual builder mode - generate layout JSON
    generateLayoutJSON();
    const blockPositionsValue = document.getElementById('block_positions').value;
    console.log('Sending block_positions to preview:', blockPositionsValue);
    const blockPositionsInput = document.createElement('input');
    blockPositionsInput.type = 'hidden';
    blockPositionsInput.name = 'block_positions';
    blockPositionsInput.value = blockPositionsValue;
    form.appendChild(blockPositionsInput);
    
    // Add layout config
    const layoutConfigValue = document.getElementById('layout_config').value;
    const layoutInput = document.createElement('input');
    layoutInput.type = 'hidden';
    layoutInput.name = 'layout_config';
    layoutInput.value = layoutConfigValue;
    form.appendChild(layoutInput);
    
    // Add form fields
    const formData = new FormData(document.querySelector('form'));
    for (let [key, value] of formData.entries()) {
        if (key !== '_token' && key !== 'block_positions' && key !== 'layout_config' && !key.startsWith('_')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
    }
    
    document.body.appendChild(form);
    
    // Open preview window
    window.open('', 'preview', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    
    // Submit form to preview window
    form.submit();
    
    // Remove form
    setTimeout(() => {
        document.body.removeChild(form);
    }, 100);
}

function generateLayoutJSON() {
    updateTemplateBlocks();
}

// Make functions globally available
window.removeBlock = removeBlock;
window.configureBlock = configureBlock;
window.saveBlockConfig = saveBlockConfig;
window.closeBlockConfig = closeBlockConfig;
</script>
@endpush
