@extends('layouts.app')

@section('title', 'AI Settings')

@push('styles')
<style>
    .header-btn {
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Sticky Header - Exact Copy Theme Settings --}}
    <div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
        <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
            <div class="flex justify-between items-center" style="height: 100%;">
                <div>
                    <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">AI Settings</h1>
                    <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Configure AI providers, models, and feature toggles</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($settings->ai_enabled)
                        <span class="inline-flex items-center px-3 py-1 rounded-full" style="font-size: var(--theme-font-size); font-weight: 500; background-color: rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success);">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            AI Enabled
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full" style="font-size: var(--theme-font-size); font-weight: 500; background-color: rgba(var(--theme-danger-rgb), 0.1); color: var(--theme-danger);">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            AI Disabled
                        </span>
                    @endif
                    <button type="submit" form="ai-settings-form" id="header-save-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-save mr-1.5"></i>
                        Save Settings
                    </button>
                    <button type="button" onclick="resetUsageStats()" id="header-reset-btn"
                            class="header-btn"
                            style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                        <i class="fas fa-sync-alt mr-1.5"></i>
                        Reset Usage
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content - Exact Copy Theme Settings --}}
    <div style="padding: 1.5rem 2rem;">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-success-rgb), 0.1); border-color: var(--theme-success); color: var(--theme-success); padding: var(--theme-card-padding);">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span style="font-size: var(--theme-font-size);">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 border rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border-color: var(--theme-danger); color: var(--theme-danger); padding: var(--theme-card-padding);">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span style="font-size: var(--theme-font-size);">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <form id="ai-settings-form" action="{{ route('ai-settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
            
                {{-- General Settings --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">General Settings</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Default Provider</label>
                                <select name="default_provider" class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);">
                            <option value="openai" {{ $settings->default_provider === 'openai' ? 'selected' : '' }}>OpenAI</option>
                            <option value="anthropic" {{ $settings->default_provider === 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                        </select>
                    </div>
                    
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Timeout (seconds)</label>
                                <input type="number" name="timeout_seconds" value="{{ $settings->timeout_seconds }}" 
                                       class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="5" max="300">
                    </div>
                        </div>
                        
                        <div class="mt-4 space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="ai_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                                       {{ $settings->ai_enabled ? 'checked' : '' }}>
                                <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Enable AI Features</span>
                    </label>
                    
                            <label class="flex items-center">
                                <input type="checkbox" name="log_ai_usage" class="rounded" style="border-color: var(--theme-primary);" 
                                       {{ $settings->log_ai_usage ? 'checked' : '' }}>
                                <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Log AI Usage</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" name="show_ai_costs" class="rounded" style="border-color: var(--theme-primary);" 
                                       {{ $settings->show_ai_costs ? 'checked' : '' }}>
                                <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Show AI Costs to Users</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- OpenAI Configuration --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b border-gray-200 flex justify-between items-center" style="padding: 1rem 1.5rem;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">OpenAI Configuration</h2>
                        <button type="button" onclick="testConnection('openai')" 
                                class="inline-flex items-center transition-all duration-200" 
                                style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-size: var(--theme-button-font-size); font-weight: 500; background-color: var(--theme-primary); color: white;">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Test Connection
                        </button>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">API Key</label>
                                <input type="password" name="openai_api_key" 
                                       value="{{ $settings->openai_api_key ? '••••••••' : '' }}" 
                                       placeholder="sk-..." 
                                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Leave empty to keep current key</p>
                    </div>
                    
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Model</label>
                                <select name="openai_model" class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);">
                            @foreach($availableModels['openai'] as $model => $description)
                                <option value="{{ $model }}" {{ $settings->openai_model === $model ? 'selected' : '' }}>
                                    {{ $description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Temperature (0-2)</label>
                        <input type="number" name="openai_temperature" value="{{ $settings->openai_temperature }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="0" max="2" step="0.1">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Tokens</label>
                        <input type="number" name="openai_max_tokens" value="{{ $settings->openai_max_tokens }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="1" max="128000">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Input Cost (per 1K tokens)</label>
                        <input type="number" name="openai_input_cost_per_1k" value="{{ $settings->openai_input_cost_per_1k }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="0" step="0.000001">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Output Cost (per 1K tokens)</label>
                        <input type="number" name="openai_output_cost_per_1k" value="{{ $settings->openai_output_cost_per_1k }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="0" step="0.000001">
                    </div>
                </div>
            </div>

                {{-- Anthropic Configuration --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b border-gray-200 flex justify-between items-center" style="padding: 1rem 1.5rem;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Anthropic (Claude) Configuration</h2>
                        <button type="button" onclick="testConnection('anthropic')" 
                                class="inline-flex items-center transition-all duration-200" 
                                style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-size: var(--theme-button-font-size); font-weight: 500; background-color: var(--theme-primary); color: white;">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Test Connection
                        </button>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">API Key</label>
                        <input type="password" name="anthropic_api_key" 
                               value="{{ $settings->anthropic_api_key ? '••••••••' : '' }}" 
                               placeholder="sk-ant-..." 
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Leave empty to keep current key</p>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Model</label>
                        <select name="anthropic_model" class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);">
                            @foreach($availableModels['anthropic'] as $model => $description)
                                <option value="{{ $model }}" {{ $settings->anthropic_model === $model ? 'selected' : '' }}>
                                    {{ $description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Temperature (0-2)</label>
                        <input type="number" name="anthropic_temperature" value="{{ $settings->anthropic_temperature }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="0" max="2" step="0.1">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Tokens</label>
                        <input type="number" name="anthropic_max_tokens" value="{{ $settings->anthropic_max_tokens }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="1" max="200000">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Input Cost (per 1K tokens)</label>
                        <input type="number" name="anthropic_input_cost_per_1k" value="{{ $settings->anthropic_input_cost_per_1k }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="0" step="0.000001">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Output Cost (per 1K tokens)</label>
                        <input type="number" name="anthropic_output_cost_per_1k" value="{{ $settings->anthropic_output_cost_per_1k }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="0" step="0.000001">
                    </div>
                </div>
            </div>

                {{-- Feature Toggles --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Feature Toggles</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="ai_chat_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                               {{ $settings->ai_chat_enabled ? 'checked' : '' }}>
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">AI Chat</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="ai_task_generator_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                               {{ $settings->ai_task_generator_enabled ? 'checked' : '' }}>
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">AI Task Generator</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="ai_time_predictions_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                               {{ $settings->ai_time_predictions_enabled ? 'checked' : '' }}>
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">AI Time Predictions</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="ai_invoice_generation_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                               {{ $settings->ai_invoice_generation_enabled ? 'checked' : '' }}>
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">AI Invoice Generation</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="ai_digest_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                               {{ $settings->ai_digest_enabled ? 'checked' : '' }}>
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">AI Digest</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="ai_learning_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                               {{ $settings->ai_learning_enabled ? 'checked' : '' }}>
                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">AI Learning</span>
                    </label>
                        </div>
                    </div>
                </div>

                {{-- AI Chat Configuration --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">AI Chat Configuration</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Tokens per Response</label>
                        <input type="number" name="ai_chat_max_tokens" value="{{ $settings->ai_chat_max_tokens }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="100" max="4000">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Maximum length of AI responses (100-4000)</p>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Temperature (Creativity)</label>
                        <input type="number" name="ai_chat_temperature" value="{{ $settings->ai_chat_temperature }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="0" max="1" step="0.1">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">0 = Focused, 1 = Creative</p>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Chat History Limit</label>
                        <input type="number" name="ai_chat_history_limit" value="{{ $settings->ai_chat_history_limit }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="5" max="50">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Number of messages to keep in history</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="ai_chat_show_context" class="rounded" style="border-color: var(--theme-primary);" 
                                   {{ $settings->ai_chat_show_context ? 'checked' : '' }}>
                            <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">Show User Context</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="ai_chat_allow_file_analysis" class="rounded" style="border-color: var(--theme-primary);" 
                                   {{ $settings->ai_chat_allow_file_analysis ? 'checked' : '' }}>
                            <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text-muted);">Allow File Analysis</span>
                        </label>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">System Prompt (Optional)</label>
                        <textarea name="ai_chat_system_prompt" rows="3" 
                                  class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);"
                                  placeholder="You are a helpful project management assistant...">{{ $settings->ai_chat_system_prompt }}</textarea>
                        <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Custom instructions for the AI assistant behavior</p>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Welcome Message</label>
                        <input type="text" name="ai_chat_welcome_message" 
                               value="{{ $settings->ai_chat_welcome_message }}" 
                               placeholder="Hello! How can I help you with your projects today?"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">First message shown when chat opens</p>
                    </div>
                    </div>
                </div>

                {{-- AI Time Entry Configuration --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">AI Time Entry Settings</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="ai_time_entry_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                                       {{ $settings->ai_time_entry_enabled ? 'checked' : '' }}>
                                <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Enable AI Time Entry Improvements</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            {{-- Default Rules --}}
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Default Description Rules</label>
                                <textarea name="ai_time_entry_default_rules" rows="10" 
                                          class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);"
                                          placeholder="Define default rules for time entry descriptions...">{{ $settings->ai_time_entry_default_rules ?? 
'COMMUNICATIEBUREAU TIME ENTRY REGELS:

1. PERSBERICHTEN & MEDIA:
   - Groepeer persberichten per onderwerp/campagne
   - Vermeld ALTIJD alle media waar het persbericht is verschenen
   - Format: "Persbericht [onderwerp] - verschenen in: [media1], [media2], [media3]"
   - Bij follow-up: "Media follow-up [onderwerp] - contact met [journalist/medium]"

2. JOURNALIST CONTACT:
   - Vermeld altijd naam journalist EN medium
   - Format: "Journalistencontact: [naam] ([medium]) - [onderwerp]"
   - Bij informatieverstrekking: "Info verstrekt aan [journalist] ([medium]) over [onderwerp]"

3. SCHRIJFWERK & CONTENT:
   - Specificeer type content (artikel, blog, social media, nieuwsbrief)
   - Vermeld doelgroep/kanaal
   - Format: "[Type content] geschreven voor [doel/kanaal] - [onderwerp]"

4. KLANTCONTACT:
   - Onderscheid tussen strategisch overleg en uitvoering
   - Format: "Klantoverleg [bedrijf] - [onderwerp/doel]"
   - Bij regulier contact: "Afstemming [klant] over [onderwerp]"

5. INTERN TEAMOVERLEG:
   - Specificeer type overleg (brainstorm, planning, review)
   - Format: "Team [type overleg] - [project/onderwerp]"

6. ALGEMENE RICHTLIJNEN:
   - Gebruik consistente terminologie per klant
   - Vermeld projectnaam bij lange-termijn klanten
   - Houd beschrijvingen beknopt maar volledig (max 100 karakters)
   - Begin met werkwoord in verleden tijd' }}</textarea>
                                <p style="font-size: var(--theme-font-size); margin-top: 0.25rem; color: var(--theme-text-muted);">Default rules for all projects unless overridden</p>
                            </div>

                            {{-- Default Categories --}}
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Default Task Categories</label>
                                <input type="text" name="ai_time_entry_default_categories" 
                                       value="{{ is_array($settings->ai_time_entry_default_categories) ? implode(', ', $settings->ai_time_entry_default_categories) : ($settings->ai_time_entry_default_categories ?? 'persbericht, media contact, journalistencontact, schrijfwerk, content creatie, klantoverleg, strategisch advies, campagne ontwikkeling, social media, nieuwsbrief, teamoverleg, research, mediamonitoring, crisis communicatie, event organisatie') }}" 
                                       placeholder="persbericht, media contact, schrijfwerk, klantoverleg..."
                                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                <p style="font-size: var(--theme-font-size); margin-top: 0.25rem; color: var(--theme-text-muted);">Comma-separated list of default task categories</p>
                            </div>

                            {{-- Example Patterns --}}
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Example Patterns</label>
                                <textarea name="ai_time_entry_example_patterns" rows="12" 
                                          class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);"
                                          placeholder="One example per line...">{{ is_array($settings->ai_time_entry_example_patterns) ? implode("\n", $settings->ai_time_entry_example_patterns) : ($settings->ai_time_entry_example_patterns ?? 'Persbericht productlancering - verschenen in: AD, Telegraaf, NOS, RTL Nieuws
Journalistencontact: Jan Jansen (NRC) - interview CEO over duurzaamheidsstrategie
Info verstrekt aan Maria de Vries (Volkskrant) over jaarcijfers Q3
Blogartikel geschreven voor corporate website - onderwerp: innovatie in retail
Social media content voor LinkedIn - campagne week 42
Klantoverleg Philips - kwartaalreview communicatiestrategie
Afstemming Albert Heijn over persconferentie planning
Team brainstorm - nieuwe campagne Gemeente Amsterdam
Mediamonitoring uitgevoerd - sentiment analyse productlancering
Crisis communicatie - opstellen statement voor datalek incident
Nieuwsbrief Q4 opgesteld en verstuurd naar 5000 abonnees
Event organisatie - persconferentie voorbereid voor 50 journalisten') }}</textarea>
                                <p style="font-size: var(--theme-font-size); margin-top: 0.25rem; color: var(--theme-text-muted);">Examples of good time entry descriptions (one per line)</p>
                            </div>

                            {{-- Settings Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Description Length</label>
                                    <input type="number" name="ai_time_entry_max_length" value="{{ $settings->ai_time_entry_max_length }}" 
                                           class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="50" max="500">
                                    <p style="font-size: var(--theme-font-size); margin-top: 0.25rem; color: var(--theme-text-muted);">Maximum characters for descriptions</p>
                                </div>
                                
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">History Learning Days</label>
                                    <input type="number" name="ai_time_entry_history_days" value="{{ $settings->ai_time_entry_history_days }}" 
                                           class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="7" max="365">
                                    <p style="font-size: var(--theme-font-size); margin-top: 0.25rem; color: var(--theme-text-muted);">Days of history to analyze for patterns</p>
                                </div>
                            </div>

                            {{-- Feature Toggles --}}
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="ai_time_entry_auto_improve" class="rounded" style="border-color: var(--theme-primary);" 
                                           {{ $settings->ai_time_entry_auto_improve ? 'checked' : '' }}>
                                    <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Auto-improve descriptions on save</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="ai_time_entry_learn_from_history" class="rounded" style="border-color: var(--theme-primary);" 
                                           {{ $settings->ai_time_entry_learn_from_history ? 'checked' : '' }}>
                                    <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Learn from recent time entries</span>
                                </label>
                            </div>

                            {{-- Prompt Template --}}
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">AI Prompt Template</label>
                                <textarea name="ai_time_entry_prompt_template" rows="12" 
                                          class="mt-1 block w-full rounded-lg border-gray-300 font-mono" style="font-size: var(--theme-font-size);"
                                          placeholder="Template for AI prompt...">{{ $settings->ai_time_entry_prompt_template ?? 
'Je bent een time entry assistent voor een communicatiebureau. 

Verbeter de volgende tijdregistratie volgens deze regels:
{rules}

Beschikbare categorieën: {categories}

Goede voorbeelden:
{examples}

BELANGRIJK:
- Bij persberichten: combineer gerelateerde entries EN vermeld ALLE media waar het verschenen is
- Bij journalistencontact: vermeld ALTIJD naam + medium
- Maximaal {max_length} karakters
- Gebruik Nederlandse terminologie
- Wees specifiek maar beknopt

Originele beschrijving: "{description}"' }}</textarea>
                                <p style="font-size: var(--theme-font-size); margin-top: 0.25rem; color: var(--theme-text-muted);">Variables: {rules}, {categories}, {examples}, {max_length}, {description}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AI Invoice Settings --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">AI Invoice Generation Settings</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="ai_invoice_enabled" class="rounded" style="border-color: var(--theme-primary);" 
                                       {{ $settings->ai_invoice_enabled ? 'checked' : '' }}>
                                <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Enable AI Invoice Generation</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            {{-- System Prompt --}}
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Invoice System Prompt</label>
                                <textarea name="ai_invoice_system_prompt" rows="6" 
                                          class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);"
                                          placeholder="System prompt for invoice generation...">{{ $settings->ai_invoice_system_prompt ?? 
'Je bent een communicatiebureau factuur specialist. Bij het genereren van factuuromschrijvingen:

1. PERSBERICHTEN: Groepeer alle persberichten per onderwerp/campagne en vermeld ALLE media waar ze zijn verschenen
   Format: "Persbericht [onderwerp] - media plaatsingen: [lijst van alle media]"

2. JOURNALIST CONTACT: Combineer contactmomenten met dezelfde journalist/medium
   Format: "Media contacten [journalist/medium] - [onderwerpen besproken]"

3. SCHRIJFWERK: Groepeer per type content (artikelen, blogs, social media posts)
   Format: "[Type] content ontwikkeling - [aantal items] voor [kanalen]"

4. KLANTOVERLEG: Combineer strategische sessies en reguliere afstemmingen apart
   Format: "Strategisch overleg - [onderwerpen]" of "Project afstemming - [activiteiten]"

5. FOCUS OP WAARDE: Beschrijf de geleverde waarde voor de klant, niet alleen de uitgevoerde taken' }}</textarea>
                                <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Main instruction for AI when generating invoices</p>
                            </div>

                            {{-- Consolidation Instructions --}}
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Consolidation Instructions</label>
                                <textarea name="ai_invoice_consolidation_instructions" rows="8" 
                                          class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);"
                                          placeholder="Instructions for consolidating time entries...">{{ $settings->ai_invoice_consolidation_instructions ?? 
'1. ANALYSEER alle time entries en groepeer op hoofdactiviteit
2. PERSBERICHTEN: Combineer ALLE persberichten van dezelfde campagne/onderwerp
   - Behoud ALLE media vermeldingen (AD, Telegraaf, NOS, RTL, etc.)
   - Vermeld totaal aantal media plaatsingen
3. MEDIA CONTACTEN: Groepeer per journalist/medium
   - Combineer follow-ups en informatieverstrekking
   - Vermeld alle besproken onderwerpen
4. CONTENT CREATIE: Groepeer per content type
   - Tel aantal geproduceerde items
   - Vermeld doelkanalen
5. VERGADERINGEN: Onderscheid strategisch vs operationeel
   - Combineer dagelijkse standups tot "Team coordinatie"
   - Groepeer klantoverleg per type (strategie/uitvoering)
6. BEHOUD belangrijke project-specifieke details
7. GEBRUIK professionele factuur terminologie in het Nederlands' }}</textarea>
                                <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Step-by-step instructions for consolidating entries</p>
                            </div>

                            {{-- Settings Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Output Language</label>
                                    <select name="ai_invoice_output_language" 
                                            style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                                        <option value="nl" {{ $settings->ai_invoice_output_language == 'nl' ? 'selected' : '' }}>Nederlands</option>
                                        <option value="en" {{ $settings->ai_invoice_output_language == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="auto" {{ $settings->ai_invoice_output_language == 'auto' ? 'selected' : '' }}>Auto-detect</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Description Words</label>
                                    <input type="number" name="ai_invoice_max_description_words" value="{{ $settings->ai_invoice_max_description_words ?? 100 }}" 
                                           class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="50" max="500">
                                </div>
                                
                                <div>
                                    <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Similarity Threshold</label>
                                    <input type="number" name="ai_invoice_group_similar_threshold" value="{{ $settings->ai_invoice_group_similar_threshold ?? 0.8 }}" 
                                           class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" 
                                           min="0" max="1" step="0.1">
                                    <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">0-1, higher = more grouping</p>
                                </div>
                            </div>

                            {{-- Communication Agency Specific --}}
                            <div class="mt-4">
                                <h3 style="font-size: var(--theme-font-size); font-weight: 500; margin-bottom: 0.5rem; color: var(--theme-text-muted);">Communication Agency Features</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="ai_invoice_bundle_press_releases" class="rounded" style="border-color: var(--theme-primary);" 
                                               {{ $settings->ai_invoice_bundle_press_releases ? 'checked' : '' }}>
                                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Bundle press releases by topic/campaign</span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="ai_invoice_list_all_media" class="rounded" style="border-color: var(--theme-primary);" 
                                               {{ $settings->ai_invoice_list_all_media ? 'checked' : '' }}>
                                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Always list all media where content appeared</span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="ai_invoice_group_by_activity_type" class="rounded" style="border-color: var(--theme-primary);" 
                                               {{ $settings->ai_invoice_group_by_activity_type ? 'checked' : '' }}>
                                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Group entries by activity type</span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="ai_invoice_include_technical_details" class="rounded" style="border-color: var(--theme-primary);" 
                                               {{ $settings->ai_invoice_include_technical_details ? 'checked' : '' }}>
                                        <span style="margin-left: 0.5rem; font-size: var(--theme-font-size); color: var(--theme-text);">Include technical details in descriptions</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Invoice Description Template --}}
                            <div>
                                <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Invoice Description Template</label>
                                <textarea name="ai_invoice_description_prompt" rows="6" 
                                          class="mt-1 block w-full rounded-lg border-gray-300 font-mono" style="font-size: var(--theme-font-size);"
                                          placeholder="Template for invoice descriptions...">{{ $settings->ai_invoice_description_prompt ?? 
'Genereer een professionele factuuromschrijving voor {PROJECT_NAME} - periode {PERIOD}

Op basis van de werkzaamheden:
{WORK_SUMMARY}

Formatteer als:
- Hoofdactiviteiten met duidelijke waarde-omschrijving
- Bij persberichten: vermeld aantal en alle media plaatsingen
- Bij content: vermeld type en aantallen
- Gebruik professionele Nederlandse terminologie
- Maximum {MAX_WORDS} woorden totaal' }}</textarea>
                                <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Variables: {PROJECT_NAME}, {PERIOD}, {WORK_SUMMARY}, {MAX_WORDS}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rate Limiting --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Rate Limiting & Usage Controls</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Requests/Minute</label>
                        <input type="number" name="max_requests_per_minute" value="{{ $settings->max_requests_per_minute }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="1">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Tokens/Day</label>
                        <input type="number" name="max_tokens_per_day" value="{{ $settings->max_tokens_per_day }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="1000">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Max Cost/Month ($)</label>
                        <input type="number" name="max_cost_per_month" value="{{ $settings->max_cost_per_month }}" 
                               class="mt-1 block w-full rounded-lg border-gray-300" style="font-size: var(--theme-font-size);" min="1">
                    </div>
                    </div>
                </div>

                {{-- Usage Statistics --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b border-gray-200 flex justify-between items-center" style="padding: 1rem 1.5rem;">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Usage Statistics</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('ai-settings.export-usage') }}" 
                           class="inline-flex items-center transition-all duration-200"
                           style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-size: var(--theme-button-font-size); font-weight: 500; background-color: var(--theme-success); color: white;">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export CSV
                        </a>
                        <form action="{{ route('ai-settings.reset-usage') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('Reset all usage statistics?')"
                                    class="inline-flex items-center transition-all duration-200"
                                    style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); border-radius: var(--theme-button-radius); font-size: var(--theme-button-font-size); font-weight: 500; background-color: var(--theme-danger); color: white;">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset Stats
                            </button>
                        </form>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="rounded-lg p-4" style="background-color: var(--theme-bg-secondary);">
                        <div style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Requests Today</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--theme-text);">{{ number_format($settings->total_requests_today) }}</div>
                    </div>
                    
                            <div class="rounded-lg p-4" style="background-color: var(--theme-bg-secondary);">
                        <div style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Tokens Today</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--theme-text);">{{ number_format($settings->total_tokens_today) }}</div>
                        <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">of {{ number_format($settings->max_tokens_per_day) }}</div>
                    </div>
                    
                            <div class="rounded-lg p-4" style="background-color: var(--theme-bg-secondary);">
                        <div style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Cost This Month</div>
                        <div style="font-size: 1.5rem; font-weight: 600; color: var(--theme-text);">${{ number_format($settings->total_cost_this_month, 4) }}</div>
                        <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">of ${{ number_format($settings->max_cost_per_month) }}</div>
                    </div>
                    
                            <div class="rounded-lg p-4" style="background-color: var(--theme-bg-secondary);">
                        <div style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Last Reset</div>
                        <div style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">
                            {{ $settings->last_reset_date ? $settings->last_reset_date->format('M d, Y') : 'Never' }}
                        </div>
                    </div>
                        </div>

                        @if($usageStats && count($usageStats) > 0)
                        <div class="mt-6">
                    <h3 style="font-size: var(--theme-font-size); font-weight: 500; margin-bottom: 0.5rem; color: var(--theme-text-muted);">Daily Usage (Last 30 days)</h3>
                    <div style="position: relative; height: 200px;">
                        <canvas id="usageChart"></canvas>
                    </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Advanced Settings --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
                        <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Advanced Settings</h2>
                    </div>
                    <div style="padding: var(--theme-card-padding);">
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">Proxy URL (Optional)</label>
                        <input type="url" name="proxy_url" value="{{ $settings->proxy_url }}" 
                               placeholder="https://proxy.example.com"
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); margin-top: 0.25rem; color: var(--theme-text-muted);">Use a proxy server for API requests</p>
                    </div>
                    </div>
                </div>

        </div>
    </form>
</div>

{{-- Test Connection Modal --}}
<div id="testModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Testing Connection...</h3>
        <div id="testResult" style="font-size: var(--theme-font-size);"></div>
        <button onclick="closeTestModal()" style="margin-top: 1rem; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: #475569; color: white; border-radius: var(--theme-button-radius); font-size: var(--theme-button-font-size); border: none; cursor: pointer;" onmouseover="this.style.background='#334155';" onmouseout="this.style.background='#475569';">
            Close
        </button>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-success').trim();
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-danger').trim();

    // Header buttons
    const saveBtn = document.getElementById('header-save-btn');
    if (saveBtn) {
        saveBtn.style.backgroundColor = primaryColor;
        saveBtn.style.color = 'white';
        saveBtn.style.border = 'none';
        saveBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    const resetBtn = document.getElementById('header-reset-btn');
    if (resetBtn) {
        resetBtn.style.backgroundColor = dangerColor;
        resetBtn.style.color = 'white';
        resetBtn.style.border = 'none';
        resetBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Test connection buttons
    const testBtns = document.querySelectorAll('.test-connection-btn');
    testBtns.forEach(btn => {
        btn.style.backgroundColor = primaryColor;
        btn.style.color = 'white';
        btn.style.border = 'none';
    });

    // Form checkboxes
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.style.accentColor = primaryColor;
    });
}

// Reset usage stats function
function resetUsageStats() {
    if (confirm('Are you sure you want to reset all usage statistics? This action cannot be undone.')) {
        fetch('{{ route("ai-settings.reset-usage") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
<script>
function testConnection(provider) {
    const modal = document.getElementById('testModal');
    const result = document.getElementById('testResult');
    
    modal.classList.remove('hidden');
    result.innerHTML = '<div style="color: #2563eb;">Testing ' + provider + ' connection...</div>';
    
    fetch('{{ route("ai-settings.test-connection") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ provider: provider })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = '<div style="color: #16a34a;">✓ ' + data.message + '</div>';
            if (data.details) {
                result.innerHTML += '<div style="margin-top: 0.5rem; font-size: 11px; color: #6b7280;">Model: ' + data.details.model + '<br>Response: ' + data.details.response + '</div>';
            }
        } else {
            result.innerHTML = '<div style="color: #dc2626;">✗ ' + data.message + '</div>';
        }
    })
    .catch(error => {
        result.innerHTML = '<div style="color: #dc2626;">✗ Error: ' + error.message + '</div>';
    });
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}

@if($usageStats && count($usageStats) > 0)
// Usage Chart
const ctx = document.getElementById('usageChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($usageStats->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
        datasets: [{
            label: 'Daily Cost ($)',
            data: {!! json_encode($usageStats->pluck('total_cost')) !!},
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
@endif
</script>
@endpush
@endsection