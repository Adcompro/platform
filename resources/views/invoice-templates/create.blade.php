@extends('layouts.app')

@section('title', 'Create Invoice Template')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/80 backdrop-blur-md border-b border-slate-200/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Invoice Template Builder</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Create a custom invoice layout</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="previewTemplate()" 
                            class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        <i class="fas fa-eye mr-1.5 text-xs"></i>
                        Preview
                    </button>
                    <button onclick="saveTemplate()" 
                            class="inline-flex items-center px-3 py-1.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                        <i class="fas fa-save mr-1.5 text-xs"></i>
                        Save Template
                    </button>
                    <a href="{{ route('invoice-templates.index') }}" 
                       class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all">
                        <i class="fas fa-times mr-1.5 text-xs"></i>
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Builder Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <form id="templateForm" action="{{ route('invoice-templates.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-12 gap-6">
                {{-- Left Sidebar - Basic Settings --}}
                <div class="col-span-3">
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden sticky top-20">
                        <div class="px-4 py-3 border-b border-slate-200/50">
                            <h2 class="text-base font-medium text-slate-900">Template Settings</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            {{-- Template Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                                    Template Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       id="name"
                                       required
                                       class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">
                                    Description
                                </label>
                                <textarea name="description" 
                                          id="description"
                                          rows="2"
                                          class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                            </div>

                            {{-- Company (if admin) --}}
                            @if(Auth::user()->role === 'super_admin')
                            <div>
                                <label for="company_id" class="block text-sm font-medium text-slate-700 mb-1">
                                    Company
                                </label>
                                <select name="company_id" 
                                        id="company_id"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <option value="">System Template (All Companies)</option>
                                    @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                            @endif

                            {{-- Template Type --}}
                            <div>
                                <label for="template_type" class="block text-sm font-medium text-slate-700 mb-1">
                                    Template Type <span class="text-red-500">*</span>
                                </label>
                                <select name="template_type" 
                                        id="template_type"
                                        required
                                        class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <option value="custom">Custom</option>
                                    <option value="standard">Standard</option>
                                    <option value="modern">Modern</option>
                                    <option value="classic">Classic</option>
                                    <option value="minimal">Minimal</option>
                                    <option value="detailed">Detailed</option>
                                </select>
                            </div>

                            {{-- Color Scheme --}}
                            <div>
                                <label for="color_scheme" class="block text-sm font-medium text-slate-700 mb-1">
                                    Color Scheme <span class="text-red-500">*</span>
                                </label>
                                <select name="color_scheme" 
                                        id="color_scheme"
                                        required
                                        onchange="updateColorPickers()"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <option value="blue">Blue</option>
                                    <option value="green">Green</option>
                                    <option value="red">Red</option>
                                    <option value="purple">Purple</option>
                                    <option value="gray">Gray</option>
                                    <option value="indigo">Indigo</option>
                                    <option value="yellow">Yellow</option>
                                    <option value="custom">Custom Colors</option>
                                </select>
                            </div>

                            {{-- Custom Colors (shown when custom is selected) --}}
                            <div id="customColors" class="space-y-3 hidden">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Primary Color</label>
                                    <input type="color" name="primary_color" id="primary_color" class="w-full h-8 rounded">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Secondary Color</label>
                                    <input type="color" name="secondary_color" id="secondary_color" class="w-full h-8 rounded">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Accent Color</label>
                                    <input type="color" name="accent_color" id="accent_color" class="w-full h-8 rounded">
                                </div>
                            </div>

                            {{-- Logo Position --}}
                            <div>
                                <label for="logo_position" class="block text-sm font-medium text-slate-700 mb-1">
                                    Logo Position
                                </label>
                                <select name="logo_position" 
                                        id="logo_position"
                                        onchange="toggleLogoUpload()"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                    <option value="none">No Logo</option>
                                </select>
                            </div>

                            {{-- Logo Upload --}}
                            <div id="logoUploadSection">
                                <label for="logo_file" class="block text-sm font-medium text-slate-700 mb-1">
                                    Company Logo
                                </label>
                                <div class="space-y-2">
                                    <input type="file" 
                                           name="logo_file" 
                                           id="logo_file"
                                           accept="image/*"
                                           onchange="previewLogo(event)"
                                           class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                                    
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
                                    
                                    <p class="text-xs text-slate-500">
                                        Upload a PNG, JPG or SVG file (max 2MB)
                                    </p>
                                </div>
                            </div>

                            {{-- Font Settings --}}
                            <div>
                                <label for="font_family" class="block text-sm font-medium text-slate-700 mb-1">
                                    Font Family
                                </label>
                                <select name="font_family" 
                                        id="font_family"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <option value="Inter">Inter (Modern)</option>
                                    <option value="Arial">Arial</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Helvetica">Helvetica</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Roboto">Roboto</option>
                                </select>
                            </div>

                            <div>
                                <label for="font_size" class="block text-sm font-medium text-slate-700 mb-1">
                                    Font Size
                                </label>
                                <select name="font_size" 
                                        id="font_size"
                                        class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <option value="small">Small</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>

                            {{-- Default Settings --}}
                            <div class="space-y-2 pt-3 border-t border-slate-200">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_default" value="1" class="rounded border-slate-300 text-slate-500 focus:ring-slate-500">
                                    <span class="ml-2 text-sm text-slate-700">Set as default template</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Middle - Available Blocks --}}
                <div class="col-span-3">
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200/50">
                            <h2 class="text-base font-medium text-slate-900">Available Blocks</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Drag blocks to the template</p>
                        </div>
                        <div id="availableBlocks" class="p-4 space-y-2 max-h-[calc(100vh-200px)] overflow-y-auto">
                            @foreach($availableBlocks as $block)
                            <div class="block-item" 
                                 data-block-id="{{ $block['id'] }}"
                                 data-block-name="{{ $block['name'] }}"
                                 data-block-config="{{ json_encode($block['configurable']) }}"
                                 draggable="true">
                                <div class="bg-white border border-slate-200 rounded-lg p-3 cursor-move hover:shadow-md transition-all">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex-shrink-0 w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                                            <i class="{{ $block['icon'] }} text-slate-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-sm text-slate-900">{{ $block['name'] }}</div>
                                            <div class="text-xs text-slate-500">{{ $block['description'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right - Template Canvas --}}
                <div class="col-span-6">
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200/50 flex justify-between items-center">
                            <div>
                                <h2 class="text-base font-medium text-slate-900">Template Layout</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Arrange blocks in your desired order</p>
                            </div>
                            <button type="button" 
                                    onclick="clearTemplate()"
                                    class="text-xs text-red-600 hover:text-red-700">
                                Clear All
                            </button>
                        </div>
                        
                        {{-- Template Drop Zone --}}
                        <div id="templateCanvas" class="p-4 min-h-[600px]">
                            <div id="dropZone" class="border-2 border-dashed border-slate-300 rounded-lg p-8 min-h-[500px]">
                                <div id="emptyState" class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-slate-600">Drag blocks here to build your template</p>
                                    <p class="text-xs text-slate-500 mt-1">You can reorder blocks after adding them</p>
                                </div>
                                <div id="templateBlocks" class="space-y-3 hidden">
                                    {{-- Dropped blocks will appear here --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden field for layout configuration --}}
            <input type="hidden" name="layout_config" id="layout_config">
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
                    <p class="text-sm text-slate-500 mt-1">Customize the appearance and behavior of this block</p>
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
                <div id="blockConfigContent" class="space-y-6">
                    {{-- Dynamic configuration options will be loaded here --}}
                </div>
            </div>
            
            {{-- Modal Footer --}}
            <div class="border-t border-slate-200 bg-slate-50/50 flex justify-end space-x-3 flex-shrink-0" style="padding: 24px 32px !important;">
                <button type="button" 
                        onclick="closeBlockConfig()"
                        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all duration-200">
                    Cancel
                </button>
                <button type="button" 
                        onclick="saveBlockConfig()"
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
let templateBlocks = [];
let currentEditingBlock = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag and drop for available blocks
    const availableBlocks = document.querySelectorAll('.block-item');
    availableBlocks.forEach(block => {
        block.addEventListener('dragstart', handleDragStart);
        block.addEventListener('dragend', handleDragEnd);
    });

    // Initialize drop zone
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('drop', handleDrop);
    dropZone.addEventListener('dragleave', handleDragLeave);

    // Initialize Sortable for reordering
    const templateBlocksContainer = document.getElementById('templateBlocks');
    if (templateBlocksContainer) {
        new Sortable(templateBlocksContainer, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function() {
                updateTemplateBlocks();
            }
        });
    }
});

function handleDragStart(e) {
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('blockId', this.dataset.blockId);
    e.dataTransfer.setData('blockName', this.dataset.blockName);
    e.dataTransfer.setData('blockConfig', this.dataset.blockConfig);
    this.classList.add('opacity-50');
}

function handleDragEnd(e) {
    this.classList.remove('opacity-50');
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'copy';
    
    const dropZone = document.getElementById('dropZone');
    dropZone.classList.add('border-slate-500', 'bg-slate-50');
    
    return false;
}

function handleDragLeave(e) {
    const dropZone = document.getElementById('dropZone');
    dropZone.classList.remove('border-slate-500', 'bg-slate-50');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    e.preventDefault();
    
    const dropZone = document.getElementById('dropZone');
    dropZone.classList.remove('border-slate-500', 'bg-slate-50');
    
    const blockId = e.dataTransfer.getData('blockId');
    const blockName = e.dataTransfer.getData('blockName');
    const blockConfig = JSON.parse(e.dataTransfer.getData('blockConfig'));
    
    addBlockToTemplate(blockId, blockName, blockConfig);
    
    return false;
}

function addBlockToTemplate(blockId, blockName, blockConfig) {
    // Hide empty state
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('templateBlocks').classList.remove('hidden');
    
    // Create unique ID for this instance
    const instanceId = blockId + '_' + Date.now();
    
    // Add to blocks array
    const newBlock = {
        id: instanceId,
        type: blockId,
        name: blockName,
        config: {},
        order: templateBlocks.length
    };
    templateBlocks.push(newBlock);
    
    // Create block element
    const blockElement = document.createElement('div');
    blockElement.className = 'template-block bg-white border border-slate-200 rounded-lg p-3';
    blockElement.dataset.instanceId = instanceId;
    blockElement.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="drag-handle cursor-move">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-sm text-slate-900">${blockName}</div>
                    <div class="text-xs text-slate-500">Click to configure</div>
                </div>
            </div>
            <div class="flex items-center space-x-1">
                ${blockConfig.length > 0 ? `
                <button type="button" 
                        onclick="configureBlock('${instanceId}')"
                        class="text-slate-400 hover:text-blue-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
                ` : ''}
                <button type="button" 
                        onclick="removeBlock('${instanceId}')"
                        class="text-slate-400 hover:text-red-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('templateBlocks').appendChild(blockElement);
    updateTemplateBlocks();
}

function removeBlock(instanceId) {
    if (!confirm('Remove this block from the template?')) return;
    
    // Remove from array
    templateBlocks = templateBlocks.filter(b => b.id !== instanceId);
    
    // Remove from DOM
    const element = document.querySelector(`[data-instance-id="${instanceId}"]`);
    if (element) {
        element.remove();
    }
    
    // Show empty state if no blocks
    if (templateBlocks.length === 0) {
        document.getElementById('emptyState').classList.remove('hidden');
        document.getElementById('templateBlocks').classList.add('hidden');
    }
    
    updateTemplateBlocks();
}

function configureBlock(instanceId) {
    const block = templateBlocks.find(b => b.id === instanceId);
    if (!block) return;
    
    currentEditingBlock = block;
    
    // Show modal
    document.getElementById('blockConfigModal').classList.remove('hidden');
    
    // Load configuration options based on block type
    const configContent = document.getElementById('blockConfigContent');
    configContent.innerHTML = getBlockConfigHTML(block);
}

function getBlockConfigHTML(block) {
    let html = '<div class="space-y-4">';
    
    // Add block-specific configuration based on type
    switch(block.type) {
        case 'header':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Header Configuration</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_logo" ${block.config.show_logo !== false ? 'checked' : ''} class="rounded border-slate-300" onchange="toggleLogoOptions()">
                            <span class="ml-2 text-sm text-slate-700">Show Company Logo</span>
                        </label>
                    </div>
                    <div id="logoPositionOption" style="${block.config.show_logo === false ? 'display:none' : ''}" class="space-y-3">
                        <div>
                            <label for="config_logo_position" class="block text-sm font-medium text-slate-700 mb-1">Logo Position in Header</label>
                            <select id="config_logo_position" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg">
                                <option value="left" ${block.config.logo_position === 'left' ? 'selected' : ''}>Left (with company info)</option>
                                <option value="center" ${block.config.logo_position === 'center' ? 'selected' : ''}>Center (above all)</option>
                                <option value="right" ${block.config.logo_position === 'right' ? 'selected' : ''}>Right (with invoice number)</option>
                                <option value="full-width" ${block.config.logo_position === 'full-width' ? 'selected' : ''}>Full Width (spanning entire header)</option>
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="config_logo_full_width" ${block.config.logo_full_width === true ? 'checked' : ''} class="rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-700">Stretch logo to full width</span>
                            </label>
                            <p class="text-xs text-slate-500 ml-6 mt-1">When enabled, logo will stretch across the entire header width</p>
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_use_template_logo" ${block.config.use_template_logo !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Use Template Logo (if uploaded)</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_company_name" ${block.config.show_company_name !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Company Name</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_invoice_title" ${block.config.show_invoice_title !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show "INVOICE" Title</span>
                        </label>
                    </div>
                    <div>
                        <label for="config_header_text" class="block text-sm font-medium text-slate-700 mb-1">Custom Header Text</label>
                        <input type="text" id="config_header_text" value="${block.config.header_text || ''}" 
                               class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg" 
                               placeholder="Optional custom text">
                    </div>
                </div>
            `;
            break;
            
        case 'company_info':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Company Information</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_vat" ${block.config.show_vat !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show VAT Number</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_kvk" ${block.config.show_kvk !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show KVK Number</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_email" ${block.config.show_email !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Email Address</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_phone" ${block.config.show_phone !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Phone Number</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_website" ${block.config.show_website !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Website</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'customer_info':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Customer Information</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_contact_person" ${block.config.show_contact_person !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Contact Person</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_customer_email" ${block.config.show_customer_email !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Customer Email</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_customer_vat" ${block.config.show_customer_vat ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Customer VAT Number</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'invoice_details':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Invoice Details</h4>
                <div class="space-y-3">
                    <div>
                        <label for="config_date_format" class="block text-sm font-medium text-slate-700 mb-1">Date Format</label>
                        <select id="config_date_format" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg">
                            <option value="M d, Y" ${block.config.date_format === 'M d, Y' ? 'selected' : ''}>Jan 01, 2025</option>
                            <option value="d/m/Y" ${block.config.date_format === 'd/m/Y' ? 'selected' : ''}>01/01/2025</option>
                            <option value="Y-m-d" ${block.config.date_format === 'Y-m-d' ? 'selected' : ''}>2025-01-01</option>
                            <option value="d-m-Y" ${block.config.date_format === 'd-m-Y' ? 'selected' : ''}>01-01-2025</option>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_due_date" ${block.config.show_due_date !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Due Date</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_invoice_status" ${block.config.show_invoice_status !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Invoice Status</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_reference" ${block.config.show_reference ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Reference Number</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'project_info':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Project Information</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_project_description" ${block.config.show_project_description !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Project Description</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_project_period" ${block.config.show_project_period ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Project Period</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_project_manager" ${block.config.show_project_manager ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Project Manager</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'line_items':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Invoice Lines Configuration</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_group_by_milestone" ${block.config.group_by_milestone ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Group Items by Milestone</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_item_codes" ${block.config.show_item_codes ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Item Codes</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_units" ${block.config.show_units !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Units (hours, pieces, etc.)</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_line_numbers" ${block.config.show_line_numbers ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Line Numbers</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_alternating_rows" ${block.config.alternating_rows !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Alternating Row Colors</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'time_entries':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Time Entries Display</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_user" ${block.config.show_user !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show User Name</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_date" ${block.config.show_date !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Entry Date</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_group_by_task" ${block.config.group_by_task ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Group by Task</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_descriptions" ${block.config.show_descriptions !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Entry Descriptions</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'budget_overview':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Budget Overview</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_chart" ${block.config.show_chart ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Visual Chart</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_percentage" ${block.config.show_percentage !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Percentage Used</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_remaining" ${block.config.show_remaining !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Remaining Budget</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'additional_costs':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Additional Costs</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_group_by_category" ${block.config.group_by_category ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Group by Category</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_description" ${block.config.show_description !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Cost Descriptions</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_highlight_costs" ${block.config.highlight_costs !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Highlight with Background Color</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'tax_section':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Tax/VAT Configuration</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_tax_number" ${block.config.show_tax_number ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Tax Number</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_tax_inclusive" ${block.config.tax_inclusive ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Prices Include Tax</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_tax_breakdown" ${block.config.show_tax_breakdown ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Tax Breakdown by Rate</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'discount_section':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Discount Settings</h4>
                <div class="space-y-3">
                    <div>
                        <label for="config_discount_type" class="block text-sm font-medium text-slate-700 mb-1">Discount Type</label>
                        <select id="config_discount_type" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg">
                            <option value="percentage" ${block.config.discount_type === 'percentage' ? 'selected' : ''}>Percentage (%)</option>
                            <option value="amount" ${block.config.discount_type === 'amount' ? 'selected' : ''}>Fixed Amount (€)</option>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_reason" ${block.config.show_reason ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Discount Reason</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'total':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Total Amount Display</h4>
                <div class="space-y-3">
                    <div>
                        <label for="config_highlight_color" class="block text-sm font-medium text-slate-700 mb-1">Highlight Style</label>
                        <select id="config_highlight_color" class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg">
                            <option value="none" ${block.config.highlight_color === 'none' ? 'selected' : ''}>No Highlight</option>
                            <option value="primary" ${block.config.highlight_color === 'primary' ? 'selected' : ''}>Primary Color</option>
                            <option value="bold" ${block.config.highlight_color === 'bold' ? 'selected' : ''}>Bold Only</option>
                            <option value="box" ${block.config.highlight_color === 'box' ? 'selected' : ''}>Boxed</option>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_in_words" ${block.config.show_in_words ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Amount in Words</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_currency_symbol" ${block.config.show_currency_symbol !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Currency Symbol (€)</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'payment_terms':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Payment Terms</h4>
                <div class="space-y-3">
                    <div>
                        <label for="config_default_terms" class="block text-sm font-medium text-slate-700 mb-1">Default Payment Terms</label>
                        <textarea id="config_default_terms" rows="3" 
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg"
                                  placeholder="Payment is due within 30 days...">${block.config.default_terms || ''}</textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_late_fee" ${block.config.show_late_fee ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Late Payment Fee Info</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'bank_details':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Bank Information</h4>
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_iban" ${block.config.show_iban !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show IBAN</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_bic" ${block.config.show_bic !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show BIC/SWIFT</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_account_name" ${block.config.show_account_name !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Account Name</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_bank_name" ${block.config.show_bank_name !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Bank Name</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'notes':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Notes/Comments Section</h4>
                <div class="space-y-3">
                    <div>
                        <label for="config_default_text" class="block text-sm font-medium text-slate-700 mb-1">Default Note Text</label>
                        <textarea id="config_default_text" rows="3" 
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg"
                                  placeholder="Thank you for your business...">${block.config.default_text || ''}</textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_thank_you" ${block.config.show_thank_you !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Include Thank You Message</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        case 'footer':
            html += `
                <h4 class="text-sm font-semibold text-slate-700 mb-3">Footer Configuration</h4>
                <div class="space-y-3">
                    <div>
                        <label for="config_footer_content" class="block text-sm font-medium text-slate-700 mb-1">Footer Content</label>
                        <textarea id="config_footer_content" rows="2" 
                                  class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg"
                                  placeholder="Company address, contact info...">${block.config.footer_content || ''}</textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_page_numbers" ${block.config.show_page_numbers !== false ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Page Numbers</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="config_show_print_date" ${block.config.show_print_date ? 'checked' : ''} class="rounded border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Show Print Date</span>
                        </label>
                    </div>
                </div>
            `;
            break;
            
        default:
            html += `
                <p class="text-sm text-slate-500">No configuration options available for this block.</p>
            `;
    }
    
    html += '</div>';
    return html;
}

function saveBlockConfig() {
    if (!currentEditingBlock) return;
    
    const block = templateBlocks.find(b => b.id === currentEditingBlock.id);
    if (!block) return;
    
    // Save all configuration values based on block type
    const configElements = document.querySelectorAll('#blockConfigContent input, #blockConfigContent select, #blockConfigContent textarea');
    
    configElements.forEach(element => {
        const id = element.id;
        if (!id || !id.startsWith('config_')) return;
        
        const configKey = id.replace('config_', '');
        
        if (element.type === 'checkbox') {
            block.config[configKey] = element.checked;
        } else if (element.type === 'radio') {
            if (element.checked) {
                block.config[configKey] = element.value;
            }
        } else {
            block.config[configKey] = element.value;
        }
    });
    
    // Update the block's visual indicator to show it's configured
    const blockElement = document.querySelector(`[data-instance-id="${block.id}"]`);
    if (blockElement) {
        const configuredBadge = blockElement.querySelector('.configured-badge');
        if (!configuredBadge) {
            const badge = document.createElement('span');
            badge.className = 'configured-badge ml-2 px-1.5 py-0.5 bg-green-100 text-green-700 text-xs rounded';
            badge.textContent = 'Configured';
            blockElement.querySelector('.font-medium').appendChild(badge);
        }
    }
    
    closeBlockConfig();
    updateTemplateBlocks();
}

function closeBlockConfig() {
    document.getElementById('blockConfigModal').classList.add('hidden');
    currentEditingBlock = null;
}

function clearTemplate() {
    if (!confirm('Clear all blocks from the template?')) return;
    
    templateBlocks = [];
    document.getElementById('templateBlocks').innerHTML = '';
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('templateBlocks').classList.add('hidden');
    updateTemplateBlocks();
}

function updateTemplateBlocks() {
    // Update the order based on DOM position
    const blockElements = document.querySelectorAll('.template-block');
    blockElements.forEach((element, index) => {
        const instanceId = element.dataset.instanceId;
        const block = templateBlocks.find(b => b.id === instanceId);
        if (block) {
            block.order = index;
        }
    });
    
    // Sort blocks by order
    templateBlocks.sort((a, b) => a.order - b.order);
    
    // Update hidden field with JSON
    const layoutConfig = {
        blocks: templateBlocks,
        show_logo: document.getElementById('logo_position').value !== 'none',
        show_header: true,
        show_footer: true,
        show_payment_terms: templateBlocks.some(b => b.type === 'payment_terms'),
        show_bank_details: templateBlocks.some(b => b.type === 'bank_details'),
        show_budget_overview: templateBlocks.some(b => b.type === 'budget_overview'),
        show_additional_costs_section: templateBlocks.some(b => b.type === 'additional_costs'),
        show_project_details: templateBlocks.some(b => b.type === 'project_info'),
        show_time_entry_details: templateBlocks.some(b => b.type === 'time_entries'),
        show_page_numbers: true,
        show_subtotals: templateBlocks.some(b => b.type === 'subtotal'),
        show_tax_details: templateBlocks.some(b => b.type === 'tax_section'),
        show_discount_section: templateBlocks.some(b => b.type === 'discount_section'),
        show_notes_section: templateBlocks.some(b => b.type === 'notes')
    };
    
    document.getElementById('layout_config').value = JSON.stringify(layoutConfig);
}

function updateColorPickers() {
    const scheme = document.getElementById('color_scheme').value;
    const customColors = document.getElementById('customColors');
    
    if (scheme === 'custom') {
        customColors.classList.remove('hidden');
    } else {
        customColors.classList.add('hidden');
    }
}

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
            document.getElementById('logoPreviewImg').src = e.target.result;
            document.getElementById('logoPreview').classList.remove('hidden');
        };
        
        reader.readAsDataURL(file);
    }
}

function removeLogo() {
    document.getElementById('logo_file').value = '';
    document.getElementById('logoPreview').classList.add('hidden');
    document.getElementById('logoPreviewImg').src = '';
}

function toggleLogoOptions() {
    const showLogo = document.getElementById('config_show_logo');
    const logoPositionOption = document.getElementById('logoPositionOption');
    
    if (showLogo && logoPositionOption) {
        logoPositionOption.style.display = showLogo.checked ? 'block' : 'none';
    }
}

function saveTemplate() {
    // Validate required fields
    const name = document.getElementById('name').value;
    if (!name) {
        alert('Please enter a template name');
        return;
    }
    
    if (templateBlocks.length === 0) {
        if (!confirm('The template has no blocks. Do you want to save an empty template?')) {
            return;
        }
    }
    
    // Update layout config one more time
    updateTemplateBlocks();
    
    // Submit form
    document.getElementById('templateForm').submit();
}

function previewTemplate() {
    // Update layout config
    updateTemplateBlocks();
    
    // Store original form action and target
    const originalForm = document.getElementById('templateForm');
    const originalAction = originalForm.action;
    const originalTarget = originalForm.target;
    
    // Temporarily change form to submit to preview
    originalForm.action = '{{ route('invoice-templates.preview-new') }}';
    originalForm.target = '_blank';
    
    // Submit the form (this will include file uploads)
    originalForm.submit();
    
    // Restore original form settings
    setTimeout(() => {
        originalForm.action = originalAction;
        originalForm.target = originalTarget;
    }, 100);
}
</script>
@endpush