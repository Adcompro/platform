@extends('layouts.app')

@section('title', 'AI Weekly Digest Configuration')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/60 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">AI Weekly Digest</h1>
                    <p class="text-sm text-slate-600 mt-1">Configure automated AI-powered weekly reports and summaries</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('ai-digest.preview') }}" 
                       class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-all">
                        <i class="fas fa-eye mr-2"></i>
                        Preview Digest
                    </a>
                    <button onclick="document.getElementById('generate-form').submit()" 
                            class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-all">
                        <i class="fas fa-magic mr-2"></i>
                        Generate Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Configuration Form --}}
            <div class="lg:col-span-2">
                <form action="{{ route('ai-digest.settings') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- General Settings --}}
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200/50">
                            <h2 class="text-lg font-semibold text-slate-900">General Settings</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            {{-- Enable/Disable --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-slate-700">Enable Weekly Digest</label>
                                    <p class="text-xs text-slate-500 mt-1">Automatically generate and send weekly reports</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="digest_enabled" value="false">
                                    <input type="checkbox" 
                                           name="digest_enabled" 
                                           value="true"
                                           class="sr-only peer"
                                           {{ $settings['digest_enabled'] === 'true' ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>

                            {{-- Frequency --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Frequency</label>
                                <select name="digest_frequency" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="daily" {{ $settings['digest_frequency'] == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ $settings['digest_frequency'] == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="biweekly" {{ $settings['digest_frequency'] == 'biweekly' ? 'selected' : '' }}>Bi-weekly</option>
                                    <option value="monthly" {{ $settings['digest_frequency'] == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>

                            {{-- Day of Week --}}
                            <div id="day-selector" class="{{ in_array($settings['digest_frequency'], ['weekly', 'biweekly']) ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Day of Week</label>
                                <select name="digest_day" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="monday" {{ $settings['digest_day'] == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ $settings['digest_day'] == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday" {{ $settings['digest_day'] == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ $settings['digest_day'] == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ $settings['digest_day'] == 'friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="saturday" {{ $settings['digest_day'] == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ $settings['digest_day'] == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>

                            {{-- Time --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Send Time</label>
                                <input type="time" 
                                       name="digest_time" 
                                       value="{{ $settings['digest_time'] }}"
                                       class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                    </div>

                    {{-- Content Settings --}}
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200/50">
                            <h2 class="text-lg font-semibold text-slate-900">Content Settings</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            @php
                                $contentOptions = [
                                    'digest_include_projects' => ['label' => 'Project Statistics', 'desc' => 'New, completed, and at-risk projects'],
                                    'digest_include_time' => ['label' => 'Time Tracking', 'desc' => 'Hours logged, billable percentage, top contributors'],
                                    'digest_include_invoices' => ['label' => 'Invoice Summary', 'desc' => 'New invoices, payments, overdue amounts'],
                                    'digest_include_risks' => ['label' => 'Risk Analysis', 'desc' => 'Critical issues and potential problems'],
                                    'digest_include_recommendations' => ['label' => 'AI Recommendations', 'desc' => 'Actionable suggestions for improvement'],
                                ];
                            @endphp

                            @foreach($contentOptions as $key => $option)
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <label class="text-sm font-medium text-slate-700">{{ $option['label'] }}</label>
                                        <p class="text-xs text-slate-500">{{ $option['desc'] }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="{{ $key }}" value="false">
                                        <input type="checkbox" 
                                               name="{{ $key }}" 
                                               value="true"
                                               class="sr-only peer"
                                               {{ $settings[$key] === 'true' ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Recipients --}}
                    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200/50">
                            <h2 class="text-lg font-semibold text-slate-900">Recipients</h2>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-slate-600 mb-4">Select users who will receive the weekly digest email</p>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($users as $user)
                                    <label class="flex items-center p-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                        <input type="checkbox" 
                                               name="digest_recipients[]" 
                                               value="{{ $user->id }}"
                                               class="rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                               {{ in_array($user->id, $settings['digest_recipients']) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-slate-700">{{ $user->name }}</span>
                                            <span class="text-xs text-slate-500 ml-2">{{ $user->email }}</span>
                                            <span class="text-xs px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full ml-2">{{ ucfirst($user->role) }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-all">
                            <i class="fas fa-save mr-2"></i>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Recent Digests & Quick Actions --}}
            <div class="space-y-6">
                {{-- Quick Actions --}}
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">Quick Actions</h2>
                    </div>
                    <div class="p-4 space-y-2">
                        <form id="generate-form" action="{{ route('ai-digest.generate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="period" value="week">
                            <input type="hidden" name="send_email" value="false">
                        </form>
                        
                        <button onclick="generateAndSend()" 
                                class="w-full px-4 py-2 bg-purple-100 text-purple-700 text-sm font-medium rounded-lg hover:bg-purple-200 transition-all text-left">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Generate & Send Now
                        </button>
                        
                        <a href="{{ route('ai-digest.preview') }}" 
                           class="block w-full px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-all">
                            <i class="fas fa-eye mr-2"></i>
                            Preview Latest Digest
                        </a>
                        
                        <button onclick="testEmail()" 
                                class="w-full px-4 py-2 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-200 transition-all text-left">
                            <i class="fas fa-envelope mr-2"></i>
                            Send Test Email
                        </button>
                    </div>
                </div>

                {{-- Recent Digests --}}
                @if($recentDigests->count() > 0)
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200/50">
                        <h2 class="text-lg font-semibold text-slate-900">Recent Digests</h2>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2">
                            @foreach($recentDigests as $digest)
                                <div class="flex items-center justify-between p-2 hover:bg-slate-50 rounded-lg">
                                    <div>
                                        <span class="text-sm font-medium text-slate-700">
                                            {{ ucfirst($digest['period']) }}ly Digest
                                        </span>
                                        <span class="text-xs text-slate-500 block">
                                            {{ $digest['generated_at']->format('M d, Y H:i') }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-slate-400">
                                        {{ $digest['generated_at']->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Help Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        How it works
                    </h3>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• AI analyzes your project data weekly</li>
                        <li>• Generates insights and recommendations</li>
                        <li>• Sends formatted email to selected recipients</li>
                        <li>• Highlights risks and achievements</li>
                        <li>• Provides actionable next steps</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show/hide day selector based on frequency
    document.querySelector('select[name="digest_frequency"]').addEventListener('change', function() {
        const daySelector = document.getElementById('day-selector');
        if (this.value === 'weekly' || this.value === 'biweekly') {
            daySelector.classList.remove('hidden');
        } else {
            daySelector.classList.add('hidden');
        }
    });

    function generateAndSend() {
        if (confirm('This will generate and send the digest email to all selected recipients. Continue?')) {
            const form = document.getElementById('generate-form');
            form.querySelector('input[name="send_email"]').value = 'true';
            form.submit();
        }
    }

    function testEmail() {
        if (confirm('This will send a test digest email to your address. Continue?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("ai-digest.generate") }}';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="period" value="week">
                <input type="hidden" name="send_email" value="true">
                <input type="hidden" name="test_only" value="true">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection