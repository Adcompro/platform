@extends('layouts.app')

@section('title', 'AI Invoice Prompt Settings')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-medium text-slate-900">AI Invoice Prompt Configuration</h1>
                    <p class="text-sm text-slate-600 mt-0.5">Customize AI prompts for invoice generation and description bundling</p>
                </div>
                <a href="{{ route('settings.index') }}" 
                   class="px-3 py-1.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                    Back to Settings
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
        @endif

        <form action="{{ route('settings.ai-invoice-prompts.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- System Prompt --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">System Prompt</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Main instruction for AI behavior when analyzing time entries</p>
                </div>
                <div class="p-4">
                    <textarea name="ai_invoice_system_prompt" 
                              rows="6" 
                              class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                              placeholder="System prompt for AI...">{{ \App\Models\Setting::get('ai_invoice_system_prompt', '') }}</textarea>
                </div>
            </div>

            {{-- Consolidation Instructions --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Consolidation Instructions</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Step-by-step instructions for consolidating time entries</p>
                </div>
                <div class="p-4">
                    <textarea name="ai_invoice_consolidation_instructions" 
                              rows="8" 
                              class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                              placeholder="Consolidation instructions...">{{ \App\Models\Setting::get('ai_invoice_consolidation_instructions') }}</textarea>
                </div>
            </div>

            {{-- Invoice Description Prompt --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Invoice Description Template</h2>
                    <p class="text-xs text-slate-600 mt-0.5">Template for generating final invoice description. Use {PROJECT_NAME}, {PERIOD}, {WORK_SUMMARY} as placeholders</p>
                </div>
                <div class="p-4">
                    <textarea name="ai_invoice_description_prompt" 
                              rows="8" 
                              class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                              placeholder="Invoice description template...">{{ \App\Models\Setting::get('ai_invoice_description_prompt') }}</textarea>
                </div>
            </div>

            {{-- Additional Settings --}}
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl mb-6">
                <div class="px-4 py-3 border-b border-slate-200/50">
                    <h2 class="text-base font-medium text-slate-900">Additional Settings</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Output Language</label>
                            <select name="ai_invoice_output_language" 
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="en" {{ \App\Models\Setting::get('ai_invoice_output_language') == 'en' ? 'selected' : '' }}>English</option>
                                <option value="nl" {{ \App\Models\Setting::get('ai_invoice_output_language') == 'nl' ? 'selected' : '' }}>Nederlands</option>
                                <option value="auto" {{ \App\Models\Setting::get('ai_invoice_output_language') == 'auto' ? 'selected' : '' }}>Auto-detect</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Max Description Words</label>
                            <input type="number" 
                                   name="ai_invoice_max_description_words" 
                                   value="{{ \App\Models\Setting::get('ai_invoice_max_description_words', 100) }}"
                                   min="50" 
                                   max="500"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Include Technical Details</label>
                            <select name="ai_invoice_include_technical_details" 
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="true" {{ \App\Models\Setting::get('ai_invoice_include_technical_details') == 'true' ? 'selected' : '' }}>Yes</option>
                                <option value="false" {{ \App\Models\Setting::get('ai_invoice_include_technical_details') == 'false' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Similarity Threshold (0-1)</label>
                            <input type="number" 
                                   name="ai_invoice_group_similar_threshold" 
                                   value="{{ \App\Models\Setting::get('ai_invoice_group_similar_threshold', 0.8) }}"
                                   min="0" 
                                   max="1" 
                                   step="0.1"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="resetToDefaults()"
                        class="px-4 py-2 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-all">
                    Reset to Defaults
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all">
                    Save Settings
                </button>
            </div>
        </form>

        {{-- Help Section --}}
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="text-sm font-medium text-blue-900 mb-2">Placeholder Variables</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li><code class="bg-blue-100 px-1 rounded">{PROJECT_NAME}</code> - Name of the project</li>
                <li><code class="bg-blue-100 px-1 rounded">{PERIOD}</code> - Invoice period (e.g., "Jan 1 - Jan 31, 2025")</li>
                <li><code class="bg-blue-100 px-1 rounded">{WORK_SUMMARY}</code> - JSON summary of work performed</li>
            </ul>
        </div>
    </div>
</div>

<script>
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all prompts to their default values?')) {
        // Reset textareas to default values
        document.querySelector('[name="ai_invoice_system_prompt"]').value = `You are an expert at analyzing time entries and creating comprehensive invoice descriptions for clients. Your task is to intelligently consolidate similar activities while preserving ALL important details. IMPORTANT: Always create descriptions in ENGLISH for international business compatibility. Focus on deliverables and value provided to the client. Keep all essential information - it's better to have multiple detailed lines than to lose important context.`;
        
        document.querySelector('[name="ai_invoice_consolidation_instructions"]').value = `1. Analyze ALL time entry descriptions and group truly similar activities
2. Create comprehensive descriptions that include ALL important work performed
3. Use multiple bullet points or lines when different types of work were done
4. Keep specific technical details, feature names, bug fixes, and deliverables
5. For repetitive tasks (like daily meetings), combine them but mention frequency
6. NEVER lose important information - when in doubt, keep it as separate items
7. Format descriptions professionally for client invoices`;
        
        document.querySelector('[name="ai_invoice_output_language"]').value = 'en';
        document.querySelector('[name="ai_invoice_max_description_words"]').value = '100';
        document.querySelector('[name="ai_invoice_include_technical_details"]').value = 'true';
        document.querySelector('[name="ai_invoice_group_similar_threshold"]').value = '0.8';
    }
}
</script>
@endsection