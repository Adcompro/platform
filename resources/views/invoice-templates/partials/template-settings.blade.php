<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Basic Information --}}
    <div>
        <label for="name" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Template Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $invoiceTemplate->name) }}" 
               class="w-full px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               style="font-size: var(--theme-font-size);"
               required>
    </div>

    <div>
        <label for="template_type" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Template Type</label>
        <select name="template_type" id="template_type" 
                class="w-full px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                style="font-size: var(--theme-font-size);">
            <option value="standard" {{ $invoiceTemplate->template_type == 'standard' ? 'selected' : '' }}>Standard</option>
            <option value="modern" {{ $invoiceTemplate->template_type == 'modern' ? 'selected' : '' }}>Modern</option>
            <option value="classic" {{ $invoiceTemplate->template_type == 'classic' ? 'selected' : '' }}>Classic</option>
            <option value="minimal" {{ $invoiceTemplate->template_type == 'minimal' ? 'selected' : '' }}>Minimal</option>
            <option value="detailed" {{ $invoiceTemplate->template_type == 'detailed' ? 'selected' : '' }}>Detailed</option>
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="description" style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text-muted); margin-bottom: 0.25rem;">Description</label>
        <textarea name="description" id="description" rows="3" 
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  style="font-size: var(--theme-font-size);">{{ old('description', $invoiceTemplate->description) }}</textarea>
    </div>

    {{-- Status Settings --}}
    <div class="md:col-span-2 border-t border-slate-200 pt-4">
        <div class="flex items-center space-x-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" 
                       {{ old('is_active', $invoiceTemplate->is_active) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Active Template</span>
            </label>
            
            <label class="flex items-center">
                <input type="checkbox" name="is_default" value="1" 
                       {{ old('is_default', $invoiceTemplate->is_default) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2" style="font-size: var(--theme-font-size); color: var(--theme-text);">Default Template</span>
            </label>
        </div>
    </div>
</div>