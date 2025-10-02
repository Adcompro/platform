@extends('layouts.app')

@section('content')
<div class="min-h-screen" style="background: var(--theme-bg-gradient, linear-gradient(to bottom right, rgb(248 250 252), rgb(255 255 255), rgb(248 250 252)));">
    {{-- Header Section --}}
    <div class="backdrop-blur-sm border-b" style="background: rgba(var(--theme-surface-rgb), 0.6); border-color: rgba(var(--theme-border-rgb), 0.6);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold" style="color: var(--theme-text);">Project Templates</h1>
                    <p class="text-sm" style="color: var(--theme-text-muted);">Reusable templates for quick project setup</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('project-templates.create') }}" class="inline-flex items-center px-4 py-2 border" style="padding: 0.5rem 1rem;" border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" style="background-color: var(--theme-primary); border-color: var(--theme-primary); hover:background-color: var(--theme-primary-dark); focus:background-color: var(--theme-primary-dark); focus:ring-color: var(--theme-primary);">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="rounded-lg backdrop-blur-sm p-6" style="background: rgba(var(--theme-surface-rgb), 0.7); border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg" style="padding: 0.5rem; background: rgba(var(--theme-primary-rgb), 0.1);">
                        <i class="fas fa-copy text-xl" style="color: var(--theme-primary);"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Total Templates</p>
                        <p class="text-2xl font-semibold" style="color: var(--theme-text);">{{ $templates->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg backdrop-blur-sm p-6" style="background: rgba(var(--theme-surface-rgb), 0.7); border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg" style="padding: 0.5rem; background: rgba(var(--theme-success-rgb), 0.1);">
                        <i class="fas fa-check-circle text-xl" style="color: var(--theme-success);"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Active</p>
                        <p class="text-2xl font-semibold" style="color: var(--theme-text);">{{ $templates->where('status', 'active')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg backdrop-blur-sm p-6" style="background: rgba(var(--theme-surface-rgb), 0.7); border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg" style="padding: 0.5rem; background: rgba(var(--theme-accent-rgb), 0.1);">
                        <i class="fas fa-layer-group text-xl" style="color: var(--theme-accent);"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Categories</p>
                        <p class="text-2xl font-semibold" style="color: var(--theme-text);">{{ $categories->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg backdrop-blur-sm p-6" style="background: rgba(var(--theme-surface-rgb), 0.7); border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg" style="padding: 0.5rem; background: rgba(var(--theme-warning-rgb), 0.1);">
                        <i class="fas fa-users text-xl" style="color: var(--theme-warning);"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium" style="color: var(--theme-text-muted);">Companies</p>
                        <p class="text-2xl font-semibold" style="color: var(--theme-text);">{{ $companies->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Search --}}
        <div class="rounded-lg backdrop-blur-sm p-6 mb-6" style="background: rgba(var(--theme-surface-rgb), 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.3);">
                <h2 class="font-semibold" style=" color: var(--theme-text);">Filter Templates</h2>
            </div>
            <div>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Search Templates</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or description..." class="w-full rounded-md shadow-sm transition-colors" style="border: 1px solid rgba(var(--theme-border-rgb), 0.3); background: var(--theme-surface); color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Category</label>
                        <select name="category" class="w-full rounded-md shadow-sm transition-colors" style="border: 1px solid rgba(var(--theme-border-rgb), 0.3); background: var(--theme-surface); color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--theme-text);">Company</label>
                        <select name="company_id" class="w-full rounded-md shadow-sm transition-colors" style="border: 1px solid rgba(var(--theme-border-rgb), 0.3); background: var(--theme-surface); color: var(--theme-text);" onfocus="this.style.borderColor='var(--theme-primary)'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)'" onblur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='none'">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="text-white px-4 py-2 rounded-lg transition-colors" style="padding: 0.5rem 1rem;" style="background-color: var(--theme-primary);" onmouseover="this.style.backgroundColor='var(--theme-primary-dark)'" onmouseout="this.style.backgroundColor='var(--theme-primary)'">
                            <i class="fas fa-search mr-2"></i>FILTER
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Templates List --}}
        <div class="rounded-lg backdrop-blur-sm p-6" style="background: rgba(var(--theme-surface-rgb), 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
            <div class="mb-4 pb-3" style="border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.3);">
                <h2 class="font-semibold" style=" color: var(--theme-text);">Templates List</h2>
            </div>
            
            @if($templates->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $template)
                    <div class="rounded-lg backdrop-blur-sm transition-all duration-200" style="background: rgba(var(--theme-surface-rgb), 0.8); border: 1px solid rgba(var(--theme-border-rgb), 0.2); box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'" onmouseout="this.style.boxShadow='0 2px 4px -1px rgba(0, 0, 0, 0.1)'">
                        <div class="p-6" style="padding: 1.5rem;">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-semibold" style="color: var(--theme-text);">{{ $template->name }}</h3>
                                        @if(isset($template->status))
                                            <span class="ml-2 px-2 py-1 text-xs rounded-full" style="padding: 0.25rem 0.5rem; background: {{ $template->status === 'active' ? 'rgba(var(--theme-success-rgb), 0.1); color: var(--theme-success)' : 'rgba(var(--theme-border-rgb), 0.2); color: var(--theme-text-muted)' }};">
                                                {{ ucfirst($template->status) }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($template->category)
                                        <p class="text-sm font-medium" style="color: var(--theme-primary);">{{ ucfirst($template->category) }}</p>
                                    @endif
                                    
                                    @if($template->description)
                                        <p class="mt-2 text-sm" style="color: var(--theme-text-muted);">{{ Str::limit($template->description, 100) }}</p>
                                    @endif

                                    <div class="mt-4 flex items-center text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);">
                                            <i class="fas fa-flag mr-1"></i>
                                            {{ $template->milestones->count() }} milestones
                                        </span>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                            <i class="fas fa-tasks mr-1"></i>
                                            {{ $template->milestones->sum(function($m) { return $m->tasks->count(); }) }} tasks
                                        </span>
                                        <span class="ml-2" style="color: var(--theme-text-muted);">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $template->calculateTotalHours() }}h
                                        </span>
                                        <span class="ml-2" style="color: var(--theme-text-muted);">
                                            <i class="fas fa-euro-sign mr-1"></i>
                                            €{{ number_format($template->calculateTotalValue(), 0) }}
                                        </span>
                                    </div>

                                    <div class="mt-2 text-xs" style="color: var(--theme-text-muted); opacity: 0.7;">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $template->created_at->format('M j, Y') }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 space-y-2">
                                <a href="{{ route('project-templates.show', $template) }}" class="flex items-center transition-colors duration-200 group" style="color: var(--theme-text-muted);" onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                                    <svg class="w-4 h-4 mr-2 transition-colors" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span class="text-sm">View Details</span>
                                </a>
                                
                                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                <a href="{{ route('project-templates.edit', $template) }}" class="flex items-center transition-colors duration-200 group" style="color: var(--theme-text-muted);" onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                                    <svg class="w-4 h-4 mr-2 transition-colors" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <span class="text-sm">Edit Template</span>
                                </a>
                                
                                <a href="#" onclick="useTemplate({{ $template->id }})" class="flex items-center transition-colors duration-200 group" style="color: var(--theme-text-muted);" onmouseover="this.style.color='var(--theme-text)'" onmouseout="this.style.color='var(--theme-text-muted)'">
                                    <svg class="w-4 h-4 mr-2 transition-colors" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                    </svg>
                                    <span class="text-sm">Use Template</span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $templates->appends(request()->query())->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-4" style="background: rgba(var(--theme-border-rgb), 0.1);">
                        <svg class="w-12 h-12" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium mb-2" style="color: var(--theme-text);">No templates found</h3>
                    <p class="mb-6" style="color: var(--theme-text-muted);">Start by creating your first project template.</p>
                    <a href="{{ route('project-templates.create') }}" class="inline-flex items-center px-4 py-2 border" style="padding: 0.5rem 1rem;" border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150" style="background-color: var(--theme-primary); border-color: var(--theme-primary); hover:background-color: var(--theme-primary-dark); focus:background-color: var(--theme-primary-dark); focus:ring-color: var(--theme-primary);">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Template
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function useTemplate(templateId) {
    // Placeholder for template usage functionality
    alert(`Template ${templateId} usage functionality coming soon! This will create a new project based on this template.`);
}
</script>
@endsection