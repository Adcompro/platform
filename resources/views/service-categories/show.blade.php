@extends('layouts.app')

@section('title', 'Service Category Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    @if($serviceCategory->icon)
                        <i class="{{ $serviceCategory->icon }} text-2xl mr-3" style="color: {{ $serviceCategory->color ?? '#6B7280' }}"></i>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $serviceCategory->name }}</h1>
                        <p class="text-sm text-gray-600">Service Category Details</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <a href="{{ route('service-categories.edit', $serviceCategory) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i>Edit Category
                    </a>
                    @endif
                    <a href="{{ route('service-categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Information --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Category Details --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Category Information</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Category Name</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $serviceCategory->name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $serviceCategory->status_badge_class }}">
                                        {{ ucfirst($serviceCategory->status) }}
                                    </span>
                                    @if(!$serviceCategory->is_active)
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Sort Order</dt>
                                <dd class="text-sm text-gray-900">{{ $serviceCategory->sort_order }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Total Services</dt>
                                <dd class="text-sm text-gray-900">{{ $serviceCategory->services_count }} services</dd>
                            </div>
                            
                            @if($serviceCategory->icon)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Icon</dt>
                                <dd class="flex items-center">
                                    <i class="{{ $serviceCategory->icon }} text-lg mr-2" style="color: {{ $serviceCategory->color ?? '#6B7280' }}"></i>
                                    <span class="text-sm text-gray-900">{{ $serviceCategory->icon }}</span>
                                </dd>
                            </div>
                            @endif
                            
                            @if($serviceCategory->color)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-1">Color</dt>
                                <dd class="flex items-center">
                                    <div class="w-4 h-4 rounded mr-2 border border-gray-300" style="background-color: {{ $serviceCategory->color }}"></div>
                                    <span class="text-sm text-gray-900">{{ $serviceCategory->color }}</span>
                                </dd>
                            </div>
                            @endif
                        </dl>
                        
                        @if($serviceCategory->description)
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500 mb-2">Description</dt>
                            <dd class="text-sm text-gray-900 leading-relaxed">{{ $serviceCategory->description }}</dd>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Services in Category --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-medium text-gray-900">Services in this Category</h2>
                            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                            <a href="#" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                <i class="fas fa-plus mr-1"></i>Add Service
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="p-6">
                        @if($serviceCategory->services->count() > 0)
                            <div class="space-y-4">
                                @foreach($serviceCategory->services as $service)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-gray-900">{{ $service->name }}</h4>
                                                @if($service->description)
                                                    <p class="mt-1 text-sm text-gray-600">{{ Str::limit($service->description, 100) }}</p>
                                                @endif
                                                <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                                    <span class="px-2 py-1 rounded-full {{ $service->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                        {{ ucfirst($service->status) }}
                                                    </span>
                                                    @if($service->pricing_type)
                                                        <span>{{ ucfirst(str_replace('_', ' ', $service->pricing_type)) }}</span>
                                                    @endif
                                                    @if($service->default_price)
                                                        <span>€{{ number_format($service->default_price, 2) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex space-x-2 ml-4">
                                                <a href="#" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                                <a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Edit</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-cogs text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-2">No services yet</h3>
                                <p class="text-sm text-gray-500 mb-4">This category doesn't contain any services yet.</p>
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                <a href="#" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                    <i class="fas fa-plus mr-2"></i>Add First Service
                                </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar Information --}}
            <div class="space-y-6">
                {{-- Quick Stats --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Quick Stats</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <i class="fas fa-cogs text-blue-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $stats['total_services'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-500">Total Services</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $stats['active_services'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-500">Active Services</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="p-2 bg-gray-100 rounded-lg">
                                        <i class="fas fa-pause-circle text-gray-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $stats['inactive_services'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-500">Inactive Services</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Category Meta Information --}}
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Category Details</h2>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            @if($serviceCategory->company)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Company</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $serviceCategory->company->name }}</dd>
                            </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $serviceCategory->created_at->format('M j, Y') }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $serviceCategory->updated_at->format('M j, Y H:i') }}</dd>
                            </div>
                            
                            @if($serviceCategory->creator)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $serviceCategory->creator->name }}</dd>
                            </div>
                            @endif
                            
                            @if($serviceCategory->updater && $serviceCategory->updater->id !== $serviceCategory->creator?->id)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $serviceCategory->updater->name }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Quick Actions --}}
                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('service-categories.edit', $serviceCategory) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                <i class="fas fa-edit mr-2"></i>Edit Category
                            </a>
                            
                            <button onclick="toggleStatus()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                <i class="fas fa-{{ $serviceCategory->status === 'active' ? 'pause' : 'play' }} mr-2"></i>
                                {{ $serviceCategory->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                            
                            @if($serviceCategory->can_be_deleted)
                            <button onclick="confirmDelete()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition ease-in-out duration-150">
                                <i class="fas fa-trash mr-2"></i>Delete Category
                            </button>
                            @else
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Cannot delete category with existing services</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Forms for Quick Actions --}}
@if(in_array(Auth::user()->role, ['super_admin', 'admin']))
<form id="toggle-status-form" method="POST" action="{{ route('service-categories.update', $serviceCategory) }}" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="name" value="{{ $serviceCategory->name }}">
    <input type="hidden" name="description" value="{{ $serviceCategory->description }}">
    <input type="hidden" name="status" value="{{ $serviceCategory->status === 'active' ? 'inactive' : 'active' }}">
    <input type="hidden" name="icon" value="{{ $serviceCategory->icon }}">
    <input type="hidden" name="color" value="{{ $serviceCategory->color }}">
    <input type="hidden" name="sort_order" value="{{ $serviceCategory->sort_order }}">
    @if($serviceCategory->is_active)
        <input type="hidden" name="is_active" value="1">
    @endif
</form>

@if($serviceCategory->can_be_deleted)
<form id="delete-form" method="POST" action="{{ route('service-categories.destroy', $serviceCategory) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endif
@endif
@endsection

{{-- JavaScript Section --}}
@push('scripts')
<script>
    // Toggle status functionaliteit
    function toggleStatus() {
        const currentStatus = '{{ $serviceCategory->status }}';
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        
        if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this category?`)) {
            document.getElementById('toggle-status-form').submit();
        }
    }

    // Delete confirmation
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this service category? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endpush