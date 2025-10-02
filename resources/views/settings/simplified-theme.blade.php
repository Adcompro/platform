@extends('layouts.app')

@section('title', 'Theme Settings')

@push('styles')
{!! $theme->getCssVariables() !!}
<style>
    /* Apply theme settings to this page */
    body {
        font-family: var(--theme-font-family);
        
        color: var(--theme-text);
        background-color: var(--theme-background);
    }
    
    .theme-preview-section {
        border: 1px solid var(--theme-muted);
        border-radius: var(--theme-border-radius);
        padding: var(--theme-card-padding);
        background: white;
        box-shadow: var(--theme-card-shadow);
    }
    
    /* Style all cards with theme settings */
    .bg-white {
        background-color: white !important;
        border-radius: var(--theme-border-radius) !important;
        box-shadow: var(--theme-card-shadow) !important;
    }
    
    /* Style buttons with theme settings */
    .btn-primary, button[type="submit"] {
        background-color: var(--theme-primary) !important;
        color: var(--theme-button-text) !important;
        border-radius: var(--theme-button-radius) !important;
        padding: var(--theme-button-padding) !important;
        transition: all 0.2s;
    }
    
    .btn-primary:hover, button[type="submit"]:hover {
        background-color: color-mix(in srgb, var(--theme-primary) 85%, black) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    /* Style input fields */
    input[type="text"], input[type="color"], select, textarea {
        border-radius: var(--theme-border-radius) !important;
        border-color: var(--theme-muted) !important;
        font-size: var(--theme-font-size) !important;
    }
    
    input[type="text"]:focus, input[type="color"]:focus, select:focus, textarea:focus {
        border-color: var(--theme-primary) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--theme-primary) 20%, transparent) !important;
    }
    
    /* Style section headers */
    .bg-gray-50 {
        background-color: color-mix(in srgb, var(--theme-primary) 5%, white) !important;
        border-color: var(--theme-primary) !important;
    }
    
    /* Apply theme colors to text */
    h1, h2, h3, h4, h5, h6 {
        color: var(--theme-text) !important;
    }
    
    .text-gray-500, .text-gray-600, .text-gray-700 {
        color: var(--theme-muted-text) !important;
    }
    
    .preset-card {
        cursor: pointer;
        transition: all 0.2s;
        border-radius: var(--theme-border-radius);
    }
    
    .preset-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--theme-card-shadow);
        border-color: var(--theme-primary) !important;
    }
    
    .preset-card.active {
        border-color: var(--theme-primary) !important;
        border-width: 2px !important;
        background-color: color-mix(in srgb, var(--theme-primary) 5%, white) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--theme-primary) 20%, transparent) !important;
    }
    
    .preset-card.active h3 {
        color: var(--theme-primary) !important;
        font-weight: 700 !important;
    }
    
    .preset-card.active .color-preview {
        border: 1px solid var(--theme-primary);
    }

    /* Menu Preview Styles */
    .tab-preview {
        border-bottom: 3px solid transparent;
        padding-bottom: 0.5rem;
    }

    .tab-preview.active {
        border-bottom-color: var(--theme-nav-active, #14b8a6);
        color: var(--theme-text) !important;
        font-weight: 600;
    }
    
    .color-preview {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
    }
    
    .setting-group {
        background: #f9fafb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .setting-label {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    
    .live-indicator {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Theme Settings</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Customize the look and feel of your application</p>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" form="theme-form" id="header-save-btn"
                            class="font-medium transition-all"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-save mr-1.5"></i>
                        Save Changes
                    </button>
                    <button onclick="resetTheme()" id="header-reset-btn"
                            class="font-medium transition-all"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-undo mr-1.5"></i>
                        Reset to Default
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="padding: 1.5rem 2rem;">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- Live Preview Section --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <span class="live-indicator w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Live Preview
                </h2>
            </div>
            <div style="padding: 1.5rem;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Preview Header --}}
                    <div class="theme-preview-section">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Header</h3>
                        <div id="preview-header" class="bg-white border border-gray-200 rounded px-4 py-2">
                            <h1 class="text-lg font-semibold" style="color: var(--theme-text)">Page Title</h1>
                            <p class="text-sm" style="color: var(--theme-text-muted)">Page description</p>
                        </div>
                    </div>

                    {{-- Preview Buttons --}}
                    <div class="theme-preview-section">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Buttons</h3>
                        <div class="space-y-2">
                            <button id="preview-btn-primary" class="px-4 py-2 text-white font-medium rounded-md" 
                                    style="background-color: var(--theme-primary)">
                                Primary Button
                            </button>
                            <button id="preview-btn-danger" class="px-4 py-2 text-white font-medium rounded-md"
                                    style="background-color: var(--theme-danger)">
                                Danger Button
                            </button>
                        </div>
                    </div>

                    {{-- Preview Table --}}
                    <div class="theme-preview-section">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Table</h3>
                        <table id="preview-table" class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Value</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-3 py-2 text-sm">Item 1</td>
                                    <td class="px-3 py-2 text-sm">Value 1</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm">Item 2</td>
                                    <td class="px-3 py-2 text-sm">Value 2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Presets --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                <h2 class="text-lg font-semibold text-gray-900">Quick Presets</h2>
            </div>
            <div style="padding: 1.5rem;">
                <p class="text-xs text-gray-500 mb-3">Current preset: <strong>{{ $theme->preset_name ?? 'none' }}</strong> (Updated: {{ $theme->updated_at->format('H:i:s') }})</p>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3" style="overflow: visible;">
                    @foreach($presets as $key => $preset)
                    <div class="preset-card border-2 rounded-lg p-3 cursor-pointer hover:border-orange-400 transition-all @if($theme->preset_name == $key) active @else border-gray-200 @endif"
                         data-preset="{{ $key }}"
                         style="cursor: pointer; position: relative;">
                        @if($theme->preset_name == $key)
                        <div style="position: absolute; top: -8px; right: -8px; background: #22c55e; border: 2px solid white; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.25);">
                            <i class="fas fa-check" style="font-size: 12px; font-weight: bold;"></i>
                        </div>
                        @endif
                        <h3 class="text-sm font-semibold text-gray-700 mb-1">{{ $preset['name'] }} <span class="text-xs text-gray-400">({{ $key }})</span></h3>
                        <p class="text-xs text-gray-500 mb-2">{{ $preset['description'] }}</p>
                        <div class="flex space-x-1">
                            @foreach($preset['preview_colors'] as $color)
                            <div class="color-preview" style="background-color: {{ $color }}"></div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Settings Form --}}
        <form id="theme-form" action="{{ route('settings.theme.update') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Colors Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">Colors</h2>
                    </div>
                    <div class="space-y-4" style="padding: 1.5rem;">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="setting-label">Primary Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="primary_color" value="{{ $theme->primary_color }}" 
                                           class="h-10 w-20 rounded border border-gray-300"
                                           onchange="updatePreview()">
                                    <input type="text" value="{{ $theme->primary_color }}" 
                                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded"
                                           readonly>
                                </div>
                            </div>
                            
                            <div>
                                <label class="setting-label">Accent Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="accent_color" value="{{ $theme->accent_color }}" 
                                           class="h-10 w-20 rounded border border-gray-300"
                                           onchange="updatePreview()">
                                    <input type="text" value="{{ $theme->accent_color }}" 
                                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded"
                                           readonly>
                                </div>
                            </div>
                            
                            <div>
                                <label class="setting-label">Danger Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="danger_color" value="{{ $theme->danger_color }}" 
                                           class="h-10 w-20 rounded border border-gray-300"
                                           onchange="updatePreview()">
                                    <input type="text" value="{{ $theme->danger_color }}" 
                                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded"
                                           readonly>
                                </div>
                            </div>
                            
                            <div>
                                <label class="setting-label">Text Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="text_color" value="{{ $theme->text_color }}" 
                                           class="h-10 w-20 rounded border border-gray-300"
                                           onchange="updatePreview()">
                                    <input type="text" value="{{ $theme->text_color }}" 
                                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded"
                                           readonly>
                                </div>
                            </div>
                            
                            <div>
                                <label class="setting-label">Muted Text Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="muted_text_color" value="{{ $theme->muted_text_color }}" 
                                           class="h-10 w-20 rounded border border-gray-300"
                                           onchange="updatePreview()">
                                    <input type="text" value="{{ $theme->muted_text_color }}" 
                                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded"
                                           readonly>
                                </div>
                            </div>
                            
                            <div>
                                <label class="setting-label">Background Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="background_color" value="{{ $theme->background_color }}" 
                                           class="h-10 w-20 rounded border border-gray-300"
                                           onchange="updatePreview()">
                                    <input type="text" value="{{ $theme->background_color }}" 
                                           class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded"
                                           readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Typography Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">Typography</h2>
                    </div>
                    <div class="space-y-4" style="padding: 1.5rem;">
                        <div>
                            <label class="setting-label">Font Family</label>
                            <select name="font_family" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                    onchange="updatePreview()">
                                <option value="system" {{ $theme->font_family === 'system' ? 'selected' : '' }}>System Default</option>
                                <option value="inter" {{ $theme->font_family === 'inter' ? 'selected' : '' }}>Inter</option>
                                <option value="roboto" {{ $theme->font_family === 'roboto' ? 'selected' : '' }}>Roboto</option>
                                <option value="poppins" {{ $theme->font_family === 'poppins' ? 'selected' : '' }}>Poppins</option>
                                <option value="opensans" {{ $theme->font_family === 'opensans' ? 'selected' : '' }}>Open Sans</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="setting-label">Base Font Size</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['10px', '11px', '12px', '13px', '14px', '15px', '16px'] as $size)
                                <label class="flex items-center">
                                    <input type="radio" name="font_size_base" value="{{ $size }}" 
                                           {{ $theme->font_size_base === $size ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $size }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Header Size</label>
                            <div class="flex space-x-4">
                                @foreach(['small' => 'Small', 'normal' => 'Normal', 'large' => 'Large'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="header_font_size" value="{{ $key }}" 
                                           {{ $theme->header_font_size === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Line Height</label>
                            <div class="flex space-x-4">
                                @foreach(['compact' => 'Compact', 'normal' => 'Normal', 'relaxed' => 'Relaxed'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="line_height" value="{{ $key }}" 
                                           {{ $theme->line_height === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        {{-- Typography Preview --}}
                        <div class="setting-group">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Preview</p>
                            <p id="typography-preview" style="font-family: var(--theme-font-family);  line-height: var(--theme-line-height);">
                                The quick brown fox jumps over the lazy dog. This is how your text will look with the current typography settings.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Page Headers Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">Page Headers</h2>
                    </div>
                    <div class="space-y-4" style="padding: 1.5rem;">
                        <div>
                            <label class="setting-label">Title Size</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['sm' => 'Small', 'base' => 'Base', 'lg' => 'Large', 'xl' => 'XL', '2xl' => '2XL', '3xl' => '3XL', '4xl' => '4XL'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="header_title_size" value="{{ $key }}" 
                                           {{ $theme->header_title_size === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Title Weight</label>
                            <div class="flex space-x-4">
                                @foreach(['normal' => 'Normal', 'medium' => 'Medium', 'semibold' => 'Semibold', 'bold' => 'Bold', 'extrabold' => 'Extra Bold'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="header_title_weight" value="{{ $key }}" 
                                           {{ $theme->header_title_weight === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Header Padding</label>
                            <div class="flex space-x-4">
                                @foreach(['compact' => 'Compact', 'normal' => 'Normal', 'relaxed' => 'Relaxed', 'spacious' => 'Spacious'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="header_padding" value="{{ $key }}" 
                                           {{ $theme->header_padding === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Text Spacing</label>
                            <div class="flex space-x-4">
                                @foreach(['tight' => 'Tight', 'normal' => 'Normal', 'relaxed' => 'Relaxed'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="header_spacing" value="{{ $key }}" 
                                           {{ $theme->header_spacing === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        {{-- Header Preview --}}
                        <div class="setting-group">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Preview</p>
                            <div id="header-preview" style="padding: var(--theme-header-custom-padding); border: 1px solid #e5e7eb; border-radius: 6px;">
                                <h1 style="font-size: var(--theme-header-title-size); font-weight: var(--theme-header-title-weight); margin: 0 0 var(--theme-header-spacing) 0; color: var(--theme-text);">Sample Page Title</h1>
                                <p style="margin: 0; color: var(--theme-text-muted); font-size: calc(var(--theme-font-size) * 0.9);">This is how your page headers will look with the current settings</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buttons Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">Buttons</h2>
                    </div>
                    <div class="space-y-4" style="padding: 1.5rem;">
                        <div>
                            <label class="setting-label">Button Size</label>
                            <div class="flex space-x-4">
                                @foreach(['small' => 'Small', 'normal' => 'Normal', 'large' => 'Large'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="button_size" value="{{ $key }}" 
                                           {{ $theme->button_size === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Button Text Color</label>
                            <div class="flex space-x-4">
                                @foreach(['white' => 'White', 'black' => 'Black', 'auto' => 'Auto Contrast'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="button_text_color" value="{{ $key }}" 
                                           {{ $theme->button_text_color === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Button Radius</label>
                            <div class="flex space-x-3">
                                @foreach(['none' => 'None', 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'full' => 'Full'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="button_radius" value="{{ $key }}" 
                                           {{ $theme->button_radius === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Button Style</label>
                            <div class="flex space-x-4">
                                @foreach(['solid' => 'Solid', 'outline' => 'Outline', 'ghost' => 'Ghost'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="button_style" value="{{ $key }}" 
                                           {{ $theme->button_style === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        {{-- Button Preview --}}
                        <div class="setting-group">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Preview</p>
                            <div class="flex space-x-2">
                                <button type="button" id="btn-preview-small" class="btn-preview">Small</button>
                                <button type="button" id="btn-preview-normal" class="btn-preview">Normal</button>
                                <button type="button" id="btn-preview-large" class="btn-preview">Large</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tables Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">Tables</h2>
                    </div>
                    <div class="space-y-4" style="padding: 1.5rem;">
                        <div>
                            <label class="setting-label">Row Padding</label>
                            <div class="flex space-x-4">
                                @foreach(['compact' => 'Compact', 'normal' => 'Normal', 'spacious' => 'Spacious'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="table_row_padding" value="{{ $key }}" 
                                           {{ $theme->table_row_padding === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="setting-label">Header Style</label>
                            <div class="flex space-x-4">
                                @foreach(['light' => 'Light', 'dark' => 'Dark', 'colored' => 'Colored', 'bold' => 'Bold'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="table_header_style" value="{{ $key }}" 
                                           {{ $theme->table_header_style === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="table_striped" value="1" 
                                       {{ $theme->table_striped ? 'checked' : '' }}
                                       onchange="updatePreview()"
                                       class="mr-2">
                                <span class="text-sm">Striped Rows</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="setting-label">Hover Effect</label>
                            <div class="flex space-x-4">
                                @foreach(['none' => 'None', 'light' => 'Light', 'dark' => 'Dark', 'colored' => 'Colored'] as $key => $label)
                                <label class="flex items-center">
                                    <input type="radio" name="table_hover_effect" value="{{ $key }}" 
                                           {{ $theme->table_hover_effect === $key ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-1">
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Layout Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:col-span-2">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">Layout</h2>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div>
                                <label class="setting-label">Header Height</label>
                                <select name="header_height" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                        onchange="updatePreview()">
                                    <option value="compact" {{ $theme->header_height === 'compact' ? 'selected' : '' }}>Compact</option>
                                    <option value="normal" {{ $theme->header_height === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="tall" {{ $theme->header_height === 'tall' ? 'selected' : '' }}>Tall</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="setting-label">Sidebar Width</label>
                                <select name="sidebar_width" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                        onchange="updatePreview()">
                                    <option value="narrow" {{ $theme->sidebar_width === 'narrow' ? 'selected' : '' }}>Narrow (200px)</option>
                                    <option value="normal" {{ $theme->sidebar_width === 'normal' ? 'selected' : '' }}>Normal (250px)</option>
                                    <option value="wide" {{ $theme->sidebar_width === 'wide' ? 'selected' : '' }}>Wide (300px)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="setting-label">Card Padding</label>
                                <select name="card_padding" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                        onchange="updatePreview()">
                                    <option value="small" {{ $theme->card_padding === 'small' ? 'selected' : '' }}>Small</option>
                                    <option value="normal" {{ $theme->card_padding === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="large" {{ $theme->card_padding === 'large' ? 'selected' : '' }}>Large</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="setting-label">Card Shadow</label>
                                <select name="card_shadow" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                        onchange="updatePreview()">
                                    <option value="none" {{ $theme->card_shadow === 'none' ? 'selected' : '' }}>None</option>
                                    <option value="small" {{ $theme->card_shadow === 'small' ? 'selected' : '' }}>Small</option>
                                    <option value="medium" {{ $theme->card_shadow === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="large" {{ $theme->card_shadow === 'large' ? 'selected' : '' }}>Large</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="setting-label">Border Radius</label>
                                <select name="border_radius" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                        onchange="updatePreview()">
                                    <option value="none" {{ $theme->border_radius === 'none' ? 'selected' : '' }}>None</option>
                                    <option value="small" {{ $theme->border_radius === 'small' ? 'selected' : '' }}>Small</option>
                                    <option value="medium" {{ $theme->border_radius === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="large" {{ $theme->border_radius === 'large' ? 'selected' : '' }}>Large</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- View Header Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:col-span-2">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">View Headers</h2>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="setting-label">Title Size</label>
                                <select name="view_header_title_size" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                        onchange="updatePreview()">
                                    <option value="small" {{ $theme->view_header_title_size === 'small' ? 'selected' : '' }}>Small (20px)</option>
                                    <option value="medium" {{ $theme->view_header_title_size === 'medium' ? 'selected' : '' }}>Medium (24px)</option>
                                    <option value="large" {{ $theme->view_header_title_size === 'large' ? 'selected' : '' }}>Large (30px)</option>
                                </select>
                            </div>

                            <div>
                                <label class="setting-label">Padding</label>
                                <select name="view_header_padding" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                        onchange="updatePreview()">
                                    <option value="compact" {{ $theme->view_header_padding === 'compact' ? 'selected' : '' }}>Compact (8px)</option>
                                    <option value="normal" {{ $theme->view_header_padding === 'normal' ? 'selected' : '' }}>Normal (16px)</option>
                                    <option value="spacious" {{ $theme->view_header_padding === 'spacious' ? 'selected' : '' }}>Spacious (24px)</option>
                                </select>
                            </div>

                            <div>
                                <label class="setting-label">Auto Scale</label>
                                <div class="flex items-center">
                                    <input type="checkbox" name="view_header_auto_scale" value="1"
                                           {{ $theme->view_header_auto_scale ? 'checked' : '' }}
                                           onchange="updatePreview()"
                                           class="mr-2">
                                    <span class="text-sm text-gray-600">Scale with header height</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">When enabled, view headers adjust to main header height</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Menu Styling Section --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:col-span-2">
                    <div class="bg-gray-50 border-b border-gray-200" style="padding: 0.75rem 1.5rem;">
                        <h2 class="text-lg font-semibold text-gray-900">Menu Styling</h2>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Sidebar Styling --}}
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-4">Sidebar</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="setting-label">Sidebar Style</label>
                                        <div class="flex space-x-4">
                                            @foreach(['dark' => 'Dark', 'light' => 'Light', 'colored' => 'Colored'] as $key => $label)
                                            <label class="flex items-center">
                                                <input type="radio" name="sidebar_style" value="{{ $key }}"
                                                       {{ ($theme->sidebar_style ?? 'dark') === $key ? 'checked' : '' }}
                                                       onchange="updatePreview()"
                                                       class="mr-1">
                                                <span class="text-sm">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <label class="setting-label">Sidebar Background</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="color" name="sidebar_background_color"
                                                   value="{{ $theme->sidebar_background_color ?? '#1e293b' }}"
                                                   onchange="updatePreview()"
                                                   class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="{{ $theme->sidebar_background_color ?? '#1e293b' }}"
                                                   class="flex-1 px-2 py-1 text-sm text-gray-600 border border-gray-300 rounded"
                                                   onchange="this.previousElementSibling.value = this.value; updatePreview()">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="setting-label">Sidebar Text Color</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="color" name="sidebar_text_color"
                                                   value="{{ $theme->sidebar_text_color ?? '#94a3b8' }}"
                                                   onchange="updatePreview()"
                                                   class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="{{ $theme->sidebar_text_color ?? '#94a3b8' }}"
                                                   class="flex-1 px-2 py-1 text-sm text-gray-600 border border-gray-300 rounded"
                                                   onchange="this.previousElementSibling.value = this.value; updatePreview()">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="setting-label">Sidebar Active Color</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="color" name="sidebar_active_color"
                                                   value="{{ $theme->sidebar_active_color ?? '#14b8a6' }}"
                                                   onchange="updatePreview()"
                                                   class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="{{ $theme->sidebar_active_color ?? '#14b8a6' }}"
                                                   class="flex-1 px-2 py-1 text-sm text-gray-600 border border-gray-300 rounded"
                                                   onchange="this.previousElementSibling.value = this.value; updatePreview()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Top Navigation Styling --}}
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 mb-4">Top Navigation</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="setting-label">Navigation Style</label>
                                        <div class="flex space-x-4">
                                            @foreach(['tabs' => 'Tabs', 'pills' => 'Pills', 'underline' => 'Underline'] as $key => $label)
                                            <label class="flex items-center">
                                                <input type="radio" name="top_nav_style" value="{{ $key }}"
                                                       {{ ($theme->top_nav_style ?? 'tabs') === $key ? 'checked' : '' }}
                                                       onchange="updatePreview()"
                                                       class="mr-1">
                                                <span class="text-sm">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <label class="setting-label">Active Tab Color</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="color" name="top_nav_active_color"
                                                   value="{{ $theme->top_nav_active_color ?? '#14b8a6' }}"
                                                   onchange="updatePreview()"
                                                   class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="{{ $theme->top_nav_active_color ?? '#14b8a6' }}"
                                                   class="flex-1 px-2 py-1 text-sm text-gray-600 border border-gray-300 rounded"
                                                   onchange="this.previousElementSibling.value = this.value; updatePreview()">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="setting-label">Topbar Background Color</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="color" name="topbar_background_color"
                                                   value="{{ $theme->topbar_background_color ?? $theme->background_color }}"
                                                   onchange="updatePreview()"
                                                   class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                            <input type="text" value="{{ $theme->topbar_background_color ?? $theme->background_color }}"
                                                   class="flex-1 px-2 py-1 text-sm text-gray-600 border border-gray-300 rounded"
                                                   onchange="this.previousElementSibling.value = this.value; updatePreview()">
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Separate from main background color - affects header bar only</p>
                                    </div>

                                    <div>
                                        <label class="setting-label">Icon Size</label>
                                        <div class="flex space-x-4">
                                            @foreach(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $key => $label)
                                            <label class="flex items-center">
                                                <input type="radio" name="sidebar_icon_size" value="{{ $key }}"
                                                       {{ ($theme->sidebar_icon_size ?? 'medium') === $key ? 'checked' : '' }}
                                                       onchange="updatePreview()"
                                                       class="mr-1">
                                                <span class="text-sm">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <label class="setting-label">Text Size</label>
                                        <div class="flex space-x-4">
                                            @foreach(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $key => $label)
                                            <label class="flex items-center">
                                                <input type="radio" name="sidebar_text_size" value="{{ $key }}"
                                                       {{ ($theme->sidebar_text_size ?? 'small') === $key ? 'checked' : '' }}
                                                       onchange="updatePreview()"
                                                       class="mr-1">
                                                <span class="text-sm">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Menu Preview --}}
                        <div class="mt-6">
                            <h3 class="text-md font-semibold text-gray-800 mb-4">Menu Preview</h3>
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                {{-- Mock Sidebar --}}
                                <div class="flex">
                                    <div id="sidebar-preview" class="w-16 h-32 flex flex-col items-center justify-center space-y-2"
                                         style="background-color: var(--theme-sidebar-bg, #1e293b);">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs"
                                             style="background-color: var(--theme-sidebar-active, #14b8a6); color: white;">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs"
                                             style="color: var(--theme-sidebar-text, #94a3b8);">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs"
                                             style="color: var(--theme-sidebar-text, #94a3b8);">
                                            <i class="fas fa-cog"></i>
                                        </div>
                                    </div>
                                    {{-- Mock Top Navigation --}}
                                    <div class="flex-1">
                                        <div class="h-12 bg-white border-b border-gray-200 flex items-center px-4 space-x-6">
                                            <span class="text-sm font-medium tab-preview active"
                                                  style="border-bottom-color: var(--theme-nav-active, #14b8a6);">Overview</span>
                                            <span class="text-sm text-gray-500 tab-preview">Settings</span>
                                            <span class="text-sm text-gray-500 tab-preview">Reports</span>
                                        </div>
                                        <div class="h-20 bg-gray-50 flex items-center justify-center text-sm text-gray-500">
                                            Main Content Area
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">This preview shows how your menu will look with the current settings</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all">
                    <i class="fas fa-save mr-2"></i>
                    Save Theme Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Test if JavaScript is loading
    console.log('Theme settings JavaScript loaded');
    
    // Add event listeners for preset cards when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM ready, adding preset card listeners');
        
        // Add click event listeners to all preset cards
        document.querySelectorAll('.preset-card').forEach(function(card) {
            const preset = card.getAttribute('data-preset');
            if (preset) {
                console.log('Adding listener for preset:', preset);
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Preset card clicked via event listener:', preset);
                    applyPreset(preset);
                });
            }
        });
    });
    
    let updateTimeout;
    
    // Make applyPreset globally available immediately
    window.applyPreset = function(preset) {
        console.log('applyPreset called with:', preset);
        if (confirm('Apply this preset? Your current settings will be replaced.')) {
            console.log('User confirmed, sending request...');
            fetch(`/settings/theme/preset/${preset}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to apply preset: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error applying preset:', error);
                alert('Error applying preset. Please check the console for details.');
            });
        } else {
            console.log('User cancelled');
        }
    };
    
    // Update color input text displays
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('input', function() {
            this.nextElementSibling.value = this.value;
        });
    });
    
    // Live preview update
    function updatePreview() {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(() => {
            const formData = new FormData(document.getElementById('theme-form'));
            
            fetch('{{ route('settings.theme.preview') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update CSS variables
                    const styleTag = document.getElementById('theme-preview-styles');
                    if (styleTag) {
                        styleTag.innerHTML = data.css;
                    } else {
                        const newStyle = document.createElement('style');
                        newStyle.id = 'theme-preview-styles';
                        newStyle.innerHTML = data.css;
                        document.head.appendChild(newStyle);
                    }
                    
                    // Update button classes
                    updateButtonPreviews();
                    updateTablePreview();
                    updateMenuPreview();
                }
            });
        }, 300);
    }
    
    // Reset theme
    function resetTheme() {
        if (confirm('Reset theme to default? All your customizations will be lost.')) {
            fetch('{{ route('settings.theme.reset') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
            .then(() => location.reload());
        }
    }
    
    // Update button previews based on settings
    function updateButtonPreviews() {
        const size = document.querySelector('input[name="button_size"]:checked').value;
        const radius = document.querySelector('input[name="button_radius"]:checked').value;
        const style = document.querySelector('input[name="button_style"]:checked').value;
        const textColor = document.querySelector('input[name="button_text_color"]:checked').value;

        const sizeClasses = {
            'small': 'px-2 py-1 text-xs',
            'normal': 'px-4 py-2 text-sm',
            'large': 'px-6 py-3 text-base'
        };

        const radiusClasses = {
            'none': 'rounded-none',
            'small': 'rounded',
            'medium': 'rounded-md',
            'large': 'rounded-lg',
            'full': 'rounded-full'
        };

        // Update preview buttons
        ['small', 'normal', 'large'].forEach(btnSize => {
            const btn = document.getElementById(`btn-preview-${btnSize}`);
            if (btn) {
                btn.className = `${sizeClasses[btnSize]} ${radiusClasses[radius]} bg-blue-600 text-white font-medium hover:bg-blue-700 transition-all`;
            }
        });

        // Update header buttons
        updateHeaderButtons(size, radius, style, textColor);
    }

    function updateHeaderButtons(size, radius, style, textColor) {
        const sizeClasses = {
            'small': 'px-2 py-1 text-xs',
            'normal': 'px-4 py-2 text-sm',
            'large': 'px-6 py-3 text-base'
        };

        const radiusClasses = {
            'none': 'rounded-none',
            'small': 'rounded',
            'medium': 'rounded-md',
            'large': 'rounded-lg',
            'full': 'rounded-full'
        };

        // Save button - uses primary color
        const saveBtn = document.getElementById('header-save-btn');
        if (saveBtn) {
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
            const baseClasses = `font-medium transition-all ${radiusClasses[radius]}`;

            if (style === 'solid') {
                saveBtn.className = baseClasses;
                saveBtn.style.backgroundColor = primaryColor;
                saveBtn.style.color = textColor === 'white' ? 'white' : textColor === 'black' ? 'black' : 'white';
                saveBtn.style.border = 'none';
            } else if (style === 'outline') {
                saveBtn.className = baseClasses;
                saveBtn.style.backgroundColor = 'transparent';
                saveBtn.style.color = primaryColor;
                saveBtn.style.border = `2px solid ${primaryColor}`;
            } else if (style === 'ghost') {
                saveBtn.className = baseClasses;
                saveBtn.style.backgroundColor = 'transparent';
                saveBtn.style.color = primaryColor;
                saveBtn.style.border = 'none';
            }
        }

        // Reset button - always danger color
        const resetBtn = document.getElementById('header-reset-btn');
        if (resetBtn) {
            const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();
            const baseClasses = `font-medium transition-all ${radiusClasses[radius]}`;

            if (style === 'solid') {
                resetBtn.className = baseClasses;
                resetBtn.style.backgroundColor = dangerColor;
                resetBtn.style.color = 'white';
                resetBtn.style.border = 'none';
            } else if (style === 'outline') {
                resetBtn.className = baseClasses;
                resetBtn.style.backgroundColor = 'transparent';
                resetBtn.style.color = dangerColor;
                resetBtn.style.border = `2px solid ${dangerColor}`;
            } else if (style === 'ghost') {
                resetBtn.className = baseClasses;
                resetBtn.style.backgroundColor = 'transparent';
                resetBtn.style.color = dangerColor;
                resetBtn.style.border = 'none';
            }
        }

    }
    
    // Update table preview based on settings
    function updateTablePreview() {
        const padding = document.querySelector('input[name="table_row_padding"]:checked').value;
        const headerStyle = document.querySelector('input[name="table_header_style"]:checked').value;
        const striped = document.querySelector('input[name="table_striped"]').checked;
        
        const paddingClasses = {
            'compact': 'px-3 py-1',
            'normal': 'px-4 py-2',
            'spacious': 'px-6 py-4'
        };
        
        const table = document.getElementById('preview-table');
        if (table) {
            // Update cell padding
            table.querySelectorAll('th, td').forEach(cell => {
                cell.className = paddingClasses[padding] + ' text-sm';
            });
            
            // Update header style
            const thead = table.querySelector('thead tr');
            if (thead) {
                switch(headerStyle) {
                    case 'dark':
                        thead.className = 'bg-gray-800 text-white';
                        break;
                    case 'colored':
                        thead.style.backgroundColor = 'var(--theme-primary)';
                        thead.className = 'text-white';
                        break;
                    case 'bold':
                        thead.className = 'bg-gray-100 font-bold';
                        break;
                    default:
                        thead.className = 'bg-gray-50';
                }
            }
            
            // Update striped rows
            if (striped) {
                table.querySelectorAll('tbody tr:nth-child(even)').forEach(row => {
                    row.classList.add('bg-gray-50');
                });
            }
        }
    }
    
    // Auto-save on change
    let saveTimeout;
    document.getElementById('theme-form').addEventListener('change', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            const formData = new FormData(this);
            
            // Ensure checkbox is included even when unchecked
            if (!formData.has('table_striped')) {
                formData.append('table_striped', '0');
            }
            
            fetch('{{ route('settings.theme.update') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show subtle save indicator
                    const indicator = document.createElement('div');
                    indicator.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg';
                    indicator.textContent = 'Saved';
                    document.body.appendChild(indicator);
                    setTimeout(() => indicator.remove(), 2000);
                }
            })
            .catch(error => {
                console.error('Validation error:', error);
                if (error.errors) {
                    Object.keys(error.errors).forEach(key => {
                        console.error(`${key}: ${error.errors[key].join(', ')}`);
                    });
                }
            });
        }, 1000);
    });

    // Update menu preview based on settings
    function updateMenuPreview() {
        const sidebarBg = document.querySelector('input[name="sidebar_background_color"]')?.value || '#1e293b';
        const sidebarText = document.querySelector('input[name="sidebar_text_color"]')?.value || '#94a3b8';
        const sidebarActive = document.querySelector('input[name="sidebar_active_color"]')?.value || '#14b8a6';
        const navActive = document.querySelector('input[name="top_nav_active_color"]')?.value || '#14b8a6';

        // Update CSS variables for menu preview
        document.documentElement.style.setProperty('--theme-sidebar-bg', sidebarBg);
        document.documentElement.style.setProperty('--theme-sidebar-text', sidebarText);
        document.documentElement.style.setProperty('--theme-sidebar-active', sidebarActive);
        document.documentElement.style.setProperty('--theme-nav-active', navActive);

        // Update sidebar preview
        const sidebarPreview = document.getElementById('sidebar-preview');
        if (sidebarPreview) {
            sidebarPreview.style.backgroundColor = sidebarBg;
        }

        // Update active tab preview
        const activeTab = document.querySelector('.tab-preview.active');
        if (activeTab) {
            activeTab.style.borderBottomColor = navActive;
        }
    }

    // Initialize menu preview on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateMenuPreview();
        updateButtonPreviews(); // Initialize header buttons with current settings

        // Add event listeners for menu color inputs
        ['sidebar_background_color', 'sidebar_text_color', 'sidebar_active_color', 'top_nav_active_color'].forEach(inputName => {
            const input = document.querySelector(`input[name="${inputName}"]`);
            if (input) {
                input.addEventListener('input', updateMenuPreview);
            }
        });
    });
</script>
@endpush