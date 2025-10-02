@extends('layouts.app')

@section('title', 'Theme Settings')

@push('styles')
{!! \App\Helpers\ThemeHelper::getInlineStyles() !!}
@endpush

@section('content')
<div class="min-h-screen">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-semibold text-theme-primary">Theme Settings</h1>
                    <p class="text-[13px] text-theme-secondary mt-0.5">Customize the look and feel of your application</p>
                </div>
                <div class="flex space-x-2">
                    <button onclick="resetTheme()" 
                            class="btn-theme-danger inline-flex items-center px-4 py-2 text-[13px] font-normal rounded-lg transition-all">
                        <i class="fas fa-undo mr-1.5 text-xs"></i>
                        Reset to Default
                    </button>
                    <a href="{{ route('settings.index') }}" 
                       class="btn-theme-secondary inline-flex items-center px-4 py-2 text-[13px] font-normal rounded-lg transition-all">
                        <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
                        Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-[13px]" style="padding: 1.5rem 2rem;">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-normal">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-[13px]" style="padding: 1.5rem 2rem;">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-red-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-normal">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Theme Presets --}}
            <div class="lg:col-span-1">
                {{-- Presets Card --}}
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                    <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                        <h2 class="text-[15px] font-semibold text-theme-primary">Quick Presets</h2>
                    </div>
                    <div style="padding: 2rem;" class="space-y-3">
                        @foreach($presets as $key => $preset)
                        <div class="border {{ $theme->theme_preset === $key ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }} rounded-lg p-3 cursor-pointer hover:border-orange-300 transition-all"
                             onclick="applyPreset('{{ $key }}')">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-[13px] font-semibold text-gray-700">{{ $preset['name'] }}</h3>
                                @if($theme->theme_preset === $key)
                                <span class="text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded">Active</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-500 mb-2">{{ $preset['description'] }}</p>
                            <div class="flex space-x-2">
                                <div class="w-8 h-8 rounded" style="background-color: {{ $preset['primary_color'] }}"></div>
                                <div class="w-8 h-8 rounded" style="background-color: {{ $preset['primary_hover'] }}"></div>
                                <div class="w-8 h-8 rounded" style="background-color: {{ $preset['accent_color'] }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Branding Card --}}
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                    <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                        <h2 class="text-[15px] font-semibold text-theme-primary">Branding</h2>
                    </div>
                    <div style="padding: 2rem;" class="space-y-4">
                        <div>
                            <label class="block text-[13px] font-normal text-theme-primary mb-1">Logo</label>
                            @if($theme->logo_path)
                            <div class="mb-2">
                                <img src="{{ Storage::url($theme->logo_path) }}" alt="Logo" class="h-12">
                            </div>
                            @endif
                            <form action="{{ route('theme.upload.logo') }}" method="POST" enctype="multipart/form-data" class="flex space-x-2">
                                @csrf
                                <input type="file" name="logo" accept="image/*" class="text-[11px] text-gray-600">
                                <button type="submit" class="px-3 py-1 bg-gray-100 text-gray-600 text-[11px] rounded hover:bg-gray-200">Upload</button>
                            </form>
                        </div>

                        <div>
                            <label class="block text-[13px] font-normal text-theme-primary mb-1">Favicon</label>
                            @if($theme->favicon_path)
                            <div class="mb-2">
                                <img src="{{ Storage::url($theme->favicon_path) }}" alt="Favicon" class="h-8 w-8">
                            </div>
                            @endif
                            <form action="{{ route('theme.upload.favicon') }}" method="POST" enctype="multipart/form-data" class="flex space-x-2">
                                @csrf
                                <input type="file" name="favicon" accept=".ico,.png" class="text-[11px] text-gray-600">
                                <button type="submit" class="px-3 py-1 bg-gray-100 text-gray-600 text-[11px] rounded hover:bg-gray-200">Upload</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Detailed Settings --}}
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('theme.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- General Settings --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                            <h2 class="text-[15px] font-semibold text-theme-primary">General Settings</h2>
                        </div>
                        <div style="padding: 2rem;">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="brand_name" class="block text-[13px] font-normal text-theme-primary mb-1">Brand Name</label>
                                    <input type="text" name="brand_name" id="brand_name" value="{{ $theme->brand_name }}"
                                           class="w-full px-3 py-2 text-[13px] text-theme-primary border border-gray-300 rounded-lg focus:border-[var(--theme-accent)] focus:ring-1 focus:ring-[var(--theme-accent)]">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Color Settings --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                            <h2 class="text-[15px] font-semibold text-theme-primary">Color Settings</h2>
                        </div>
                        <div style="padding: 2rem;">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Primary Colors --}}
                                <div>
                                    <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Primary Colors</h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Primary Color</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="primary_color" value="{{ $theme->primary_color }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->primary_color }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Primary Hover</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="primary_hover" value="{{ $theme->primary_hover }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->primary_hover }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Primary Text</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="primary_text" value="{{ $theme->primary_text }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->primary_text }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Accent Colors --}}
                                <div>
                                    <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Accent Colors</h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Accent Color</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="accent_color" value="{{ $theme->accent_color }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->accent_color }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Accent Hover</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="accent_hover" value="{{ $theme->accent_hover }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->accent_hover }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status Colors --}}
                                <div>
                                    <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Status Colors</h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Success</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="success_color" value="{{ $theme->success_color }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->success_color }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Warning</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="warning_color" value="{{ $theme->warning_color }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->warning_color }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Danger</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="color" name="danger_color" value="{{ $theme->danger_color }}" 
                                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                                <input type="text" value="{{ $theme->danger_color }}" 
                                                       class="flex-1 px-2 py-1 text-[11px] text-gray-600 border border-gray-300 rounded"
                                                       onchange="this.previousElementSibling.value = this.value">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- More color settings (collapsed by default) --}}
                            <details class="mt-6">
                                <summary class="cursor-pointer text-[13px] text-orange-600 hover:text-orange-700">Show Advanced Color Settings</summary>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- Text Colors --}}
                                    <div>
                                        <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Text Colors</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Primary Text</label>
                                                <input type="color" name="text_primary" value="{{ $theme->text_primary }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Secondary Text</label>
                                                <input type="color" name="text_secondary" value="{{ $theme->text_secondary }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Muted Text</label>
                                                <input type="color" name="text_muted" value="{{ $theme->text_muted }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Secondary Colors --}}
                                    <div>
                                        <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Secondary Colors</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Secondary BG</label>
                                                <input type="color" name="secondary_color" value="{{ $theme->secondary_color }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Secondary Hover</label>
                                                <input type="color" name="secondary_hover" value="{{ $theme->secondary_hover }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Secondary Border</label>
                                                <input type="color" name="secondary_border" value="{{ $theme->secondary_border }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Table & Badge Colors --}}
                                    <div>
                                        <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Component Colors</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Badge Background</label>
                                                <input type="color" name="badge_bg_color" value="{{ $theme->badge_bg_color }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Badge Text</label>
                                                <input type="color" name="badge_text_color" value="{{ $theme->badge_text_color }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Info Color</label>
                                                <input type="color" name="info_color" value="{{ $theme->info_color }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Table Colors --}}
                                    <div>
                                        <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Table Colors</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Header Background</label>
                                                <input type="color" name="table_header_bg" value="{{ $theme->table_header_bg }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Row Hover</label>
                                                <input type="color" name="table_row_hover" value="{{ $theme->table_row_hover }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Border Color</label>
                                                <input type="color" name="table_border_color" value="{{ $theme->table_border_color }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Additional Button & Link Colors Row --}}
                                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Button Colors --}}
                                    <div>
                                        <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Button Colors</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Primary Button</label>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <input type="color" name="button_primary_bg" value="{{ $theme->button_primary_bg }}" 
                                                               class="h-8 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[9px] text-gray-400">Background</span>
                                                    </div>
                                                    <div>
                                                        <input type="color" name="button_primary_hover" value="{{ $theme->button_primary_hover }}" 
                                                               class="h-8 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[9px] text-gray-400">Hover</span>
                                                    </div>
                                                </div>
                                                <input type="color" name="button_primary_text" value="{{ $theme->button_primary_text }}" 
                                                       class="h-8 w-full border border-gray-300 rounded cursor-pointer mt-2">
                                                <span class="text-[9px] text-gray-400">Text Color</span>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Secondary Button</label>
                                                <div class="grid grid-cols-3 gap-1">
                                                    <div>
                                                        <input type="color" name="button_secondary_bg" value="{{ $theme->button_secondary_bg }}" 
                                                               class="h-6 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[8px] text-gray-400">BG</span>
                                                    </div>
                                                    <div>
                                                        <input type="color" name="button_secondary_hover" value="{{ $theme->button_secondary_hover }}" 
                                                               class="h-6 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[8px] text-gray-400">Hover</span>
                                                    </div>
                                                    <div>
                                                        <input type="color" name="button_secondary_text" value="{{ $theme->button_secondary_text }}" 
                                                               class="h-6 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[8px] text-gray-400">Text</span>
                                                    </div>
                                                </div>
                                                <input type="color" name="button_secondary_border" value="{{ $theme->button_secondary_border }}" 
                                                       class="h-6 w-full border border-gray-300 rounded cursor-pointer mt-1">
                                                <span class="text-[8px] text-gray-400">Border</span>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Action Buttons</label>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <input type="color" name="button_danger_bg" value="{{ $theme->button_danger_bg }}" 
                                                               class="h-6 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[8px] text-gray-400">Danger</span>
                                                    </div>
                                                    <div>
                                                        <input type="color" name="button_danger_hover" value="{{ $theme->button_danger_hover }}" 
                                                               class="h-6 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[8px] text-gray-400">Hover</span>
                                                    </div>
                                                    <div>
                                                        <input type="color" name="button_success_bg" value="{{ $theme->button_success_bg }}" 
                                                               class="h-6 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[8px] text-gray-400">Success</span>
                                                    </div>
                                                    <div>
                                                        <input type="color" name="button_success_hover" value="{{ $theme->button_success_hover }}" 
                                                               class="h-6 w-full border border-gray-300 rounded cursor-pointer">
                                                        <span class="text-[8px] text-gray-400">Hover</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Link Colors --}}
                                    <div>
                                        <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Link Colors</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Default Link</label>
                                                <input type="color" name="link_color" value="{{ $theme->link_color }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Link Hover</label>
                                                <input type="color" name="link_hover" value="{{ $theme->link_hover }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] text-gray-500 mb-1">Visited Link</label>
                                                <input type="color" name="link_visited" value="{{ $theme->link_visited }}" 
                                                       class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- Typography Settings --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                            <h2 class="text-[15px] font-semibold text-theme-primary">Typography & Spacing</h2>
                        </div>
                        <div style="padding: 2rem;" class="space-y-6">
                            {{-- Font Family --}}
                            <div>
                                <label class="block text-[13px] font-normal text-theme-primary mb-2">Font Family</label>
                                <select name="font_family" class="w-full px-3 py-2 text-[13px] text-theme-primary border border-gray-300 rounded-lg focus:border-[var(--theme-accent)] focus:ring-1 focus:ring-[var(--theme-accent)]">
                                    <option value="Inter, system-ui, sans-serif" {{ $theme->font_family === 'Inter, system-ui, sans-serif' ? 'selected' : '' }}>Inter (Default)</option>
                                    <option value="Roboto, system-ui, sans-serif" {{ $theme->font_family === 'Roboto, system-ui, sans-serif' ? 'selected' : '' }}>Roboto</option>
                                    <option value="Open Sans, system-ui, sans-serif" {{ $theme->font_family === 'Open Sans, system-ui, sans-serif' ? 'selected' : '' }}>Open Sans</option>
                                    <option value="Lato, system-ui, sans-serif" {{ $theme->font_family === 'Lato, system-ui, sans-serif' ? 'selected' : '' }}>Lato</option>
                                    <option value="Poppins, system-ui, sans-serif" {{ $theme->font_family === 'Poppins, system-ui, sans-serif' ? 'selected' : '' }}>Poppins</option>
                                    <option value="Source Sans Pro, system-ui, sans-serif" {{ $theme->font_family === 'Source Sans Pro, system-ui, sans-serif' ? 'selected' : '' }}>Source Sans Pro</option>
                                    <option value="system-ui, sans-serif" {{ $theme->font_family === 'system-ui, sans-serif' ? 'selected' : '' }}>System Default</option>
                                    <option value="Georgia, serif" {{ $theme->font_family === 'Georgia, serif' ? 'selected' : '' }}>Georgia (Serif)</option>
                                    <option value="Times New Roman, serif" {{ $theme->font_family === 'Times New Roman, serif' ? 'selected' : '' }}>Times New Roman</option>
                                    <option value="Monaco, monospace" {{ $theme->font_family === 'Monaco, monospace' ? 'selected' : '' }}>Monaco (Monospace)</option>
                                </select>
                            </div>
                            
                            {{-- Font Sizes --}}
                            <div>
                                <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Font Sizes</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">Small</label>
                                        <input type="text" name="font_size_small" value="{{ $theme->font_size_small }}"
                                               class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">Base</label>
                                        <input type="text" name="font_size_base" value="{{ $theme->font_size_base }}"
                                               class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">Large</label>
                                        <input type="text" name="font_size_large" value="{{ $theme->font_size_large }}"
                                               class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">XL</label>
                                        <input type="text" name="font_size_xl" value="{{ $theme->font_size_xl }}"
                                               class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Font Weights --}}
                            <div>
                                <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Font Weights</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">Normal</label>
                                        <input type="text" name="font_weight_normal" value="{{ $theme->font_weight_normal }}"
                                               placeholder="400" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">Medium</label>
                                        <input type="text" name="font_weight_medium" value="{{ $theme->font_weight_medium }}"
                                               placeholder="500" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">Semibold</label>
                                        <input type="text" name="font_weight_semibold" value="{{ $theme->font_weight_semibold }}"
                                               placeholder="600" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">Bold</label>
                                        <input type="text" name="font_weight_bold" value="{{ $theme->font_weight_bold }}"
                                               placeholder="700" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Letter Spacing & Line Height --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Letter Spacing</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Tight</label>
                                            <input type="text" name="letter_spacing_tight" value="{{ $theme->letter_spacing_tight }}"
                                                   placeholder="-0.025em" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Normal</label>
                                            <input type="text" name="letter_spacing_normal" value="{{ $theme->letter_spacing_normal }}"
                                                   placeholder="0" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Wide</label>
                                            <input type="text" name="letter_spacing_wide" value="{{ $theme->letter_spacing_wide }}"
                                                   placeholder="0.025em" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Line Height</h3>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Tight</label>
                                            <input type="text" name="line_height_tight" value="{{ $theme->line_height_tight }}"
                                                   placeholder="1.25" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Normal</label>
                                            <input type="text" name="line_height_normal" value="{{ $theme->line_height_normal }}"
                                                   placeholder="1.5" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] text-gray-500 mb-1">Loose</label>
                                            <input type="text" name="line_height_loose" value="{{ $theme->line_height_loose }}"
                                                   placeholder="1.75" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Spacing Presets --}}
                            <div>
                                <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Spacing Presets</h3>
                                <div class="grid grid-cols-3 md:grid-cols-5 gap-4">
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">XS</label>
                                        <input type="text" name="spacing_xs" value="{{ $theme->spacing_xs }}"
                                               placeholder="0.25rem" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">SM</label>
                                        <input type="text" name="spacing_sm" value="{{ $theme->spacing_sm }}"
                                               placeholder="0.5rem" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">MD</label>
                                        <input type="text" name="spacing_md" value="{{ $theme->spacing_md }}"
                                               placeholder="1rem" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">LG</label>
                                        <input type="text" name="spacing_lg" value="{{ $theme->spacing_lg }}"
                                               placeholder="1.5rem" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-gray-500 mb-1">XL</label>
                                        <input type="text" name="spacing_xl" value="{{ $theme->spacing_xl }}"
                                               placeholder="2rem" class="w-full px-2 py-1.5 text-[11px] text-gray-600 border border-gray-300 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-2">These spacing values can be used with .p-theme-* and .space-theme-* classes</p>
                            </div>
                        </div>
                    </div>

                    {{-- Button Styling Settings --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                            <h2 class="text-[15px] font-semibold text-theme-primary">Button Styling</h2>
                        </div>
                        <div style="padding: 2rem;" class="space-y-6">
                            {{-- Border Radius & Padding Controls --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[13px] font-normal text-theme-primary mb-2">Border Radius</label>
                                    <select name="border_radius" class="w-full px-3 py-2 text-[13px] text-theme-primary border border-gray-300 rounded-lg focus:border-[var(--theme-accent)] focus:ring-1 focus:ring-[var(--theme-accent)]">
                                        <option value="0" {{ $theme->border_radius === '0' ? 'selected' : '' }}>Square (0px)</option>
                                        <option value="0.125rem" {{ $theme->border_radius === '0.125rem' ? 'selected' : '' }}>Small (2px)</option>
                                        <option value="0.25rem" {{ $theme->border_radius === '0.25rem' ? 'selected' : '' }}>Default (4px)</option>
                                        <option value="0.375rem" {{ $theme->border_radius === '0.375rem' ? 'selected' : '' }}>Medium (6px)</option>
                                        <option value="0.5rem" {{ $theme->border_radius === '0.5rem' ? 'selected' : '' }}>Large (8px)</option>
                                        <option value="0.75rem" {{ $theme->border_radius === '0.75rem' ? 'selected' : '' }}>XL (12px)</option>
                                        <option value="1rem" {{ $theme->border_radius === '1rem' ? 'selected' : '' }}>2XL (16px)</option>
                                        <option value="9999px" {{ $theme->border_radius === '9999px' ? 'selected' : '' }}>Pill (Full)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-normal text-theme-primary mb-2">Button Padding</label>
                                    <select name="button_padding" class="w-full px-3 py-2 text-[13px] text-theme-primary border border-gray-300 rounded-lg focus:border-[var(--theme-accent)] focus:ring-1 focus:ring-[var(--theme-accent)]">
                                        <option value="px-2 py-1" {{ $theme->button_padding === 'px-2 py-1' ? 'selected' : '' }}>Compact</option>
                                        <option value="px-3 py-1.5" {{ $theme->button_padding === 'px-3 py-1.5' ? 'selected' : '' }}>Small</option>
                                        <option value="px-4 py-2" {{ $theme->button_padding === 'px-4 py-2' ? 'selected' : '' }}>Default</option>
                                        <option value="px-6 py-3" {{ $theme->button_padding === 'px-6 py-3' ? 'selected' : '' }}>Large</option>
                                        <option value="px-8 py-4" {{ $theme->button_padding === 'px-8 py-4' ? 'selected' : '' }}>XL</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-normal text-theme-primary mb-2">Transition Speed</label>
                                    <select name="transition_speed" class="w-full px-3 py-2 text-[13px] text-theme-primary border border-gray-300 rounded-lg focus:border-[var(--theme-accent)] focus:ring-1 focus:ring-[var(--theme-accent)]">
                                        <option value="75ms" {{ $theme->transition_speed === '75ms' ? 'selected' : '' }}>Fast (75ms)</option>
                                        <option value="150ms" {{ $theme->transition_speed === '150ms' ? 'selected' : '' }}>Normal (150ms)</option>
                                        <option value="200ms" {{ $theme->transition_speed === '200ms' ? 'selected' : '' }}>Default (200ms)</option>
                                        <option value="300ms" {{ $theme->transition_speed === '300ms' ? 'selected' : '' }}>Slow (300ms)</option>
                                        <option value="500ms" {{ $theme->transition_speed === '500ms' ? 'selected' : '' }}>Very Slow (500ms)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Button Preview Section --}}
                            <div>
                                <h3 class="text-[13px] font-semibold text-theme-primary mb-3">Button Preview</h3>
                                <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <button type="button" class="btn-theme-primary px-4 py-2 rounded-lg transition-all" style="border-radius: var(--theme-border-radius); transition-duration: var(--theme-transition-speed);">
                                            Primary Button
                                        </button>
                                        <button type="button" class="btn-theme-secondary px-4 py-2 rounded-lg transition-all" style="border-radius: var(--theme-border-radius); transition-duration: var(--theme-transition-speed);">
                                            Secondary Button
                                        </button>
                                        <button type="button" class="badge-theme-success px-4 py-2 text-white rounded-lg transition-all" style="border-radius: var(--theme-border-radius); transition-duration: var(--theme-transition-speed);">
                                            Success Button
                                        </button>
                                        <button type="button" class="badge-theme-danger px-4 py-2 text-white rounded-lg transition-all" style="border-radius: var(--theme-border-radius); transition-duration: var(--theme-transition-speed);">
                                            Danger Button
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-gray-500">Preview updates automatically based on your settings above</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Effects Settings --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                            <h2 class="text-[15px] font-semibold text-theme-primary">Effects</h2>
                        </div>
                        <div style="padding: 2rem;">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="use_shadows" value="1" {{ $theme->use_shadows ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="ml-2 text-[13px] text-gray-600">Use Shadows</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="use_gradients" value="1" {{ $theme->use_gradients ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="ml-2 text-[13px] text-gray-600">Use Gradients</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="use_animations" value="1" {{ $theme->use_animations ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="ml-2 text-[13px] text-gray-600">Use Animations</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Custom CSS --}}
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f3f4f6;">
                            <h2 class="text-[15px] font-semibold text-theme-primary">Custom CSS</h2>
                        </div>
                        <div style="padding: 2rem;">
                            <textarea name="custom_css" rows="8" 
                                      placeholder="/* Add your custom CSS here */"
                                      class="w-full px-3 py-2 text-[13px] text-theme-primary font-mono border border-gray-300 rounded-lg focus:border-[var(--theme-accent)] focus:ring-1 focus:ring-[var(--theme-accent)]">{{ $theme->custom_css }}</textarea>
                            <p class="mt-2 text-[11px] text-gray-500">Add custom CSS to override default styles. Use with caution.</p>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="btn-theme-primary inline-flex items-center px-6 py-2 text-[13px] font-normal rounded-lg transition-all">
                            <i class="fas fa-save mr-1.5 text-xs"></i>
                            Save Theme Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Apply Preset Form (Hidden) --}}
<form id="preset-form" action="{{ route('theme.preset') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="preset" id="preset-input">
</form>

{{-- Reset Form (Hidden) --}}
<form id="reset-form" action="{{ route('theme.reset') }}" method="POST" class="hidden">
    @csrf
</form>

@endsection

@push('scripts')
<script>
    // Apply preset
    function applyPreset(preset) {
        if (confirm('Apply this theme preset? Your current customizations will be overwritten.')) {
            document.getElementById('preset-input').value = preset;
            document.getElementById('preset-form').submit();
        }
    }

    // Reset theme
    function resetTheme() {
        if (confirm('Reset to default theme? All customizations will be lost.')) {
            document.getElementById('reset-form').submit();
        }
    }

    // Sync color inputs with text inputs
    document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
        colorInput.addEventListener('change', function() {
            const textInput = this.nextElementSibling;
            if (textInput && textInput.type === 'text') {
                textInput.value = this.value;
            }
        });
    });

    // Live preview for button styling
    let previewTimeout;
    
    // Update border radius preview
    document.querySelector('select[name="border_radius"]')?.addEventListener('change', function() {
        document.documentElement.style.setProperty('--theme-border-radius', this.value);
        updateButtonPreviews();
    });
    
    // Update transition speed preview
    document.querySelector('select[name="transition_speed"]')?.addEventListener('change', function() {
        document.documentElement.style.setProperty('--theme-transition-speed', this.value);
        updateButtonPreviews();
    });
    
    // Update button padding preview
    document.querySelector('select[name="button_padding"]')?.addEventListener('change', function() {
        updateButtonPreviews();
    });
    
    function updateButtonPreviews() {
        const borderRadius = document.querySelector('select[name="border_radius"]')?.value || '0.5rem';
        const transitionSpeed = document.querySelector('select[name="transition_speed"]')?.value || '200ms';
        const buttonPadding = document.querySelector('select[name="button_padding"]')?.value || 'px-4 py-2';
        
        document.querySelectorAll('.bg-gray-50 button').forEach(function(btn) {
            btn.style.borderRadius = borderRadius;
            btn.style.transitionDuration = transitionSpeed;
            // Apply padding class dynamically
            btn.className = btn.className.replace(/px-\d+ py-\d+/g, '') + ' ' + buttonPadding;
        });
    }
    
    // Color live preview
    document.querySelectorAll('input[type="color"], input[type="text"]').forEach(function(input) {
        input.addEventListener('input', function() {
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(function() {
                // Live color preview could be implemented here
                console.log('Color preview:', input.name, input.value);
            }, 300);
        });
    });
</script>
@endpush