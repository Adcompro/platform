@extends('layouts.app')

@section('title', 'Services')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section - Moderne uitstraling met glassmorphism --}}
    <div class="bg-white/70 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 style="font-size: 1.25rem; font-weight: 600; color: var(--theme-text);">Service Catalog</h1>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-top: 0.125rem;">Manage your service offerings and pricing</p>
                </div>
                <div class="flex items-center space-x-2">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('services.create') }}" 
                       style="display: inline-flex; align-items: center; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: var(--theme-primary); color: var(--theme-button-text-color); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                       onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.transform='translateY(-1px)';"
                       onmouseout="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'; this.style.transform='translateY(0)';">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Create Service
                    </a>
                    @endif
                    <button onclick="openHelpModal()" 
                            style="display: inline-flex; align-items: center; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: rgba(147, 51, 234, 0.1); color: #7c3aed; font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s; border: none; cursor: pointer;"
                            onmouseover="this.style.background='rgba(147, 51, 234, 0.2)';"
                            onmouseout="this.style.background='rgba(147, 51, 234, 0.1)';">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Help
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-32">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50/50 border border-green-200/50 text-green-700 px-3 py-2.5 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-green-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500;">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50/50 border border-red-200/50 text-red-700 px-3 py-2.5 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: var(--theme-font-size); font-weight: 500;">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Stats Cards - Moderne versie met kleinere padding --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3 hover:shadow-md transition-all">
                <div class="flex items-center">
                    <div class="p-2 bg-slate-100 rounded-lg">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Total Services</p>
                        <p style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">{{ $stats['total_services'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3 hover:shadow-md transition-all">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100/60 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Active Services</p>
                        <p style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">{{ $stats['active_services'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/50 rounded-xl p-3 hover:shadow-md transition-all">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100/60 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; color: var(--theme-text-muted);">Total Value</p>
                        <p style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">€ {{ number_format($stats['total_value'], 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search and Filter Bar --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('services.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
                <div class="flex-1 max-w-lg">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search services..."
                           style="width: 100%; padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                           class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                </div>

                <select name="status" 
                        style="padding: 0.375rem 0.75rem; font-size: var(--theme-font-size); border: 1px solid #e2e8f0; border-radius: var(--theme-border-radius); transition: all 0.2s;"
                        class="focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" 
                        style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: var(--theme-primary); color: var(--theme-button-text-color); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s; border: none; cursor: pointer;"
                        onmouseover="this.style.filter='brightness(0.9)';"
                        onmouseout="this.style.filter='brightness(1)';">
                    Search
                </button>
                
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('services.index') }}" 
                       style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.5); color: var(--theme-text); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s;"
                       onmouseover="this.style.background='rgba(var(--theme-border-rgb), 0.1)'; this.style.borderColor='rgba(var(--theme-border-rgb), 0.7)';"
                       onmouseout="this.style.background='white'; this.style.borderColor='rgba(var(--theme-border-rgb), 0.5)';">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Services Grid --}}
        @if($services->isEmpty())
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-8">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 style="margin-top: 0.5rem; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">No services found</h3>
                    <p style="margin-top: 0.25rem; font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Get started by creating a new service.</p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <div class="mt-4">
                        <a href="{{ route('services.create') }}" 
                           style="display: inline-flex; align-items: center; padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: var(--theme-primary); color: var(--theme-button-text-color); font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                           onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'; this.style.transform='translateY(-1px)';"
                           onmouseout="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'; this.style.transform='translateY(0)';">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create First Service
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($services as $service)
                <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-200">
                    {{-- Service Header --}}
                    <div class="px-4 py-3 border-b border-slate-200/50 bg-gradient-to-r from-{{ $service->color ?? 'slate' }}-50 to-white">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 500; color: var(--theme-text);">{{ $service->name }}</h3>
                            </div>
                            <div class="flex items-center space-x-1">
                                <a href="{{ route('services.show', $service) }}" class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-50 rounded-lg transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                <a href="{{ route('services.edit', $service) }}" class="text-slate-400 hover:text-slate-600 p-1 hover:bg-slate-50 rounded-lg transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-600 p-1 hover:bg-red-50 rounded-lg transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Service Content --}}
                    <div class="p-4">
                        @if($service->description)
                        <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-bottom: 0.75rem;" class="line-clamp-2">{{ $service->description }}</p>
                        @endif

                        {{-- Price & Duration --}}
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Price</p>
                                <p style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text);">€ {{ number_format($service->total_price, 2, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">Est. Hours</p>
                                <p style="font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text);">{{ $service->estimated_hours ?? 0 }}h</p>
                            </div>
                        </div>

                        {{-- Service Structure --}}
                        <div class="border-t border-slate-100 pt-3">
                            <div class="flex justify-between" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted);">
                                <span>{{ $service->total_milestones }} Milestones</span>
                                <span>{{ $service->total_tasks }} Tasks</span>
                                <span>{{ $service->total_subtasks }} Subtasks</span>
                            </div>
                        </div>

                        {{-- Status Badge --}}
                        <div class="mt-3 flex justify-between items-center">
                            @if($service->is_active)
                                <span style="padding: 0.125rem 0.5rem; display: inline-flex; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; border-radius: var(--theme-border-radius); background: #dcfce7; color: #166534;">
                                    Active
                                </span>
                            @else
                                <span style="padding: 0.125rem 0.5rem; display: inline-flex; font-size: calc(var(--theme-font-size) - 2px); font-weight: 500; border-radius: var(--theme-border-radius); background: #f1f5f9; color: var(--theme-text-muted);">
                                    Inactive
                                </span>
                            @endif
                            
                            <a href="{{ route('services.show', $service) }}" style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); font-weight: 500; text-decoration: none;" onmouseover="this.style.color='var(--theme-text)';" onmouseout="this.style.color='var(--theme-text-muted)';">
                                View Details →
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Help Modal --}}
<div id="helpModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 style="font-size: 1.125rem; font-weight: 600; color: white;">Service Packages Guide</h3>
                    <button onclick="closeHelpModal()" class="text-white hover:text-purple-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                {{-- Overview Section --}}
                <div class="mb-6">
                    <h4 style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.75rem;">Overview</h4>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-bottom: 0.5rem;">
                        The Service Packages module allows you to create and manage reusable service offerings that can be imported into projects. 
                        Each service package contains a hierarchical structure of milestones, tasks, and subtasks with predefined pricing and time estimates.
                    </p>
                </div>

                {{-- Features Section --}}
                <div class="mb-6">
                    <h4 style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.75rem;">Key Features</h4>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h5 style="font-weight: 500; color: var(--theme-text); font-size: var(--theme-font-size);">Hierarchical Structure</h5>
                                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Organize services into milestones, tasks, and subtasks for detailed project planning.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h5 style="font-weight: 500; color: var(--theme-text); font-size: var(--theme-font-size);">Flexible Pricing</h5>
                                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Set fixed prices or hourly rates at any level of the hierarchy.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h5 style="font-weight: 500; color: var(--theme-text); font-size: var(--theme-font-size);">Time Estimates</h5>
                                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Define estimated hours for accurate project planning and budgeting.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h5 style="font-weight: 500; color: var(--theme-text); font-size: var(--theme-font-size);">Project Import</h5>
                                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Import service packages directly into projects with custom naming and visual indicators.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h5 style="font-weight: 500; color: var(--theme-text); font-size: var(--theme-font-size);">Activity Tracking</h5>
                                <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Complete audit trail of all changes with before/after values.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- How to Use Section --}}
                <div class="mb-6">
                    <h4 style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.75rem;">How to Use</h4>
                    <div class="space-y-3">
                        <div>
                            <h5 style="font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem; font-size: var(--theme-font-size);">1. Create a Service Package</h5>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Click "Create Service" to define a new service package with name, description, and pricing.</p>
                        </div>
                        
                        <div>
                            <h5 style="font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem; font-size: var(--theme-font-size);">2. Add Structure</h5>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Navigate to the service detail page to add milestones, tasks, and subtasks with their respective details.</p>
                        </div>
                        
                        <div>
                            <h5 style="font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem; font-size: var(--theme-font-size);">3. Set Pricing</h5>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">Configure pricing at each level - either fixed price or hourly rate based on your business model.</p>
                        </div>
                        
                        <div>
                            <h5 style="font-weight: 500; color: var(--theme-text); margin-bottom: 0.25rem; font-size: var(--theme-font-size);">4. Import to Projects</h5>
                            <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted);">From any project detail page, use the "Import Service" feature to add the package structure to your project.</p>
                        </div>
                    </div>
                </div>

                {{-- Permissions Section --}}
                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                <div class="mb-6">
                    <h4 style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.75rem;">Permissions</h4>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div style="font-size: var(--theme-font-size);">
                                <p style="font-weight: 500; color: #92400e; font-size: var(--theme-font-size);">Access Control</p>
                                <ul style="margin-top: 0.25rem; color: #a16207; font-size: var(--theme-font-size);" class="space-y-1">
                                    <li>• Super Admin: Full access to all services across companies</li>
                                    <li>• Admin: Manage services for their company</li>
                                    <li>• Project Manager: Create and edit services</li>
                                    <li>• User/Reader: View-only access</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Tips Section --}}
                <div>
                    <h4 style="font-size: 1.125rem; font-weight: 600; color: var(--theme-text); margin-bottom: 0.75rem;">Pro Tips</h4>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <ul style="font-size: var(--theme-font-size); color: #1e40af;" class="space-y-2">
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">💡</span>
                                <span>Create template services for common project types to speed up project setup.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">💡</span>
                                <span>Use descriptive names and detailed descriptions for better team understanding.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">💡</span>
                                <span>Regularly review and update time estimates based on actual project data.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">💡</span>
                                <span>Use the activity log to track changes and maintain version history.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">💡</span>
                                <span>Set services as inactive instead of deleting to preserve historical data.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-3 border-t">
                <div class="flex justify-end">
                    <button onclick="closeHelpModal()" 
                            style="padding: var(--theme-button-padding-y) var(--theme-button-padding-x); background: #7c3aed; color: white; font-size: var(--theme-button-font-size); font-weight: normal; border-radius: var(--theme-button-radius); transition: all 0.2s; border: none; cursor: pointer;"
                            onmouseover="this.style.background='#6d28d9';"
                            onmouseout="this.style.background='#7c3aed';">
                        Got it, thanks!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openHelpModal() {
    document.getElementById('helpModal').classList.remove('hidden');
}

function closeHelpModal() {
    document.getElementById('helpModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('helpModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeHelpModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHelpModal();
    }
});
</script>
@endsection