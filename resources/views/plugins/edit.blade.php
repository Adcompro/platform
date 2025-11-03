@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('plugins.show', $plugin) }}" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Configure Plugin</h1>
                        <p class="text-sm text-slate-600 mt-1">{{ $plugin->display_name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('plugins.update', $plugin) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200/50">
                    <h2 class="text-lg font-semibold text-slate-900">Plugin Configuration</h2>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Display Name --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Display Name</label>
                        <input type="text" name="display_name" value="{{ old('display_name', $plugin->display_name) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               required>
                        @error('display_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">{{ old('description', $plugin->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Icon --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Icon (Font Awesome class)</label>
                        <div class="flex items-center space-x-3">
                            <input type="text" name="icon" value="{{ old('icon', $plugin->icon) }}"
                                   class="flex-1 px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                   placeholder="e.g., fas fa-plug">
                            @if($plugin->icon)
                                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                                    <i class="{{ $plugin->icon }} text-slate-600"></i>
                                </div>
                            @endif
                        </div>
                        @error('icon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $plugin->sort_order) }}"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                               min="0" required>
                        <p class="mt-1 text-xs text-slate-500">Lower numbers appear first</p>
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Permissions --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Access Permissions</label>
                        <p class="text-xs text-slate-500 mb-3">Select which roles can access this plugin. Leave empty for unrestricted access.</p>
                        <div class="space-y-2">
                            @foreach(['super_admin' => 'Super Admin', 'admin' => 'Admin', 'project_manager' => 'Project Manager', 'user' => 'User', 'reader' => 'Reader'] as $role => $label)
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="{{ $role }}"
                                           class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                           {{ in_array($role, old('permissions', $plugin->permissions ?? [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-slate-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Plugin-specific Settings --}}
                    @if($plugin->name === 'companies')
                        <div class="border-t border-slate-200 pt-6">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Company Management Settings</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="settings[multi_company_mode]" value="1"
                                               class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                               {{ old('settings.multi_company_mode', $plugin->getSetting('multi_company_mode')) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-slate-700">Enable multi-company mode</span>
                                    </label>
                                    <p class="text-xs text-slate-500 mt-1 ml-6">When disabled, only one company can be created</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Maximum companies (if multi-mode disabled)</label>
                                    <input type="number" name="settings[max_companies]" 
                                           value="{{ old('settings.max_companies', $plugin->getSetting('max_companies', 1)) }}"
                                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                           min="1" max="10">
                                    <p class="text-xs text-slate-500 mt-1">Only applies when multi-company mode is disabled</p>
                                </div>
                            </div>
                        </div>
                    @elseif($plugin->name === 'time_tracking')
                        <div class="border-t border-slate-200 pt-6">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Time Tracking Settings</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="settings[require_approval]" value="1"
                                               class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                               {{ old('settings.require_approval', $plugin->getSetting('require_approval')) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-slate-700">Require approval for time entries</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="settings[allow_future_entries]" value="1"
                                               class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                               {{ old('settings.allow_future_entries', $plugin->getSetting('allow_future_entries')) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-slate-700">Allow future date entries</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Minimum time increment (minutes)</label>
                                    <input type="number" name="settings[min_increment]" 
                                           value="{{ old('settings.min_increment', $plugin->getSetting('min_increment', 15)) }}"
                                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                           min="1" max="60">
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($plugin->name === 'invoices')
                        <div class="border-t border-slate-200 pt-6">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Invoice Settings</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Invoice prefix</label>
                                    <input type="text" name="settings[invoice_prefix]" 
                                           value="{{ old('settings.invoice_prefix', $plugin->getSetting('invoice_prefix', 'INV-')) }}"
                                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Default payment terms (days)</label>
                                    <input type="number" name="settings[payment_terms]" 
                                           value="{{ old('settings.payment_terms', $plugin->getSetting('payment_terms', 30)) }}"
                                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                           min="0" max="365">
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="settings[auto_generate]" value="1"
                                               class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                               {{ old('settings.auto_generate', $plugin->getSetting('auto_generate')) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-slate-700">Auto-generate monthly invoices</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($plugin->name === 'calendar')
                        <div class="border-t border-slate-200 pt-6">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">Calendar Settings</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Sync interval (minutes)</label>
                                    <input type="number" name="settings[sync_interval]" 
                                           value="{{ old('settings.sync_interval', $plugin->getSetting('sync_interval', 15)) }}"
                                           class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400"
                                           min="5" max="60">
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="settings[auto_convert]" value="1"
                                               class="h-4 w-4 text-slate-600 focus:ring-slate-500 border-slate-300 rounded"
                                               {{ old('settings.auto_convert', $plugin->getSetting('auto_convert')) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-slate-700">Auto-convert meetings to time entries</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200/50 flex justify-end space-x-2">
                    <a href="{{ route('plugins.show', $plugin) }}" 
                       class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-slate-600 transition-all">
                        <i class="fas fa-save mr-1"></i> Save Configuration
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection