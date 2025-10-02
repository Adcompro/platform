@extends('layouts.app')

@section('title', 'Service Categories')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Service Categories</h1>
                    <p class="text-sm text-gray-600">Manage categories for your service catalog</p>
                </div>
                <div class="flex space-x-3">
                    {{-- Action buttons with role checks --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('service-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i>Create Category
                    </a>
                    @endif
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

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-tags text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Categories</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_categories'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_categories'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-cogs text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Services</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_services'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Avg Services</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['avg_services'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Search --}}
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Search & Filter</h2>
            </div>
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Categories</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or description..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>

                    @if(request()->hasAny(['search', 'status']))
                    <div class="flex items-end">
                        <a href="{{ route('service-categories.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Categories Grid --}}
        @if($categories->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $category)
                    <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h3>
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full {{ $category->status_badge_class }}">
                                            {{ ucfirst($category->status) }}
                                        </span>
                                    </div>
                                    
                                    @if($category->description)
                                        <p class="mt-2 text-sm text-gray-600">{{ Str::limit($category->description, 100) }}</p>
                                    @endif

                                    <div class="mt-4 flex items-center text-sm text-gray-500">
                                        <i class="fas fa-cogs mr-1"></i>
                                        <span>{{ $category->services_count }} services</span>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-calendar mr-1"></i>
                                        <span>{{ $category->formatted_created_at }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between items-center">
                                <div class="flex space-x-2">
                                    <a href="{{ route('service-categories.show', $category) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                                    <a href="{{ route('service-categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    @endif
                                </div>
                                
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin']) && $category->canBeDeleted())
                                <form method="POST" action="{{ route('service-categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $categories->appends(request()->query())->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-tags text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Categories Found</h3>
                    <p class="text-gray-500 mb-6">
                        @if(request()->hasAny(['search', 'status']))
                            No categories match your search criteria. Try adjusting your filters.
                        @else
                            Get started by creating your first service category.
                        @endif
                    </p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin', 'project_manager']))
                    <a href="{{ route('service-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-plus mr-2"></i>Create Category
                    </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

{{-- JavaScript Section --}}
@push('scripts')
<script>
    // Service Categories specific JavaScript
    console.log('Service Categories page loaded');
</script>
@endpush