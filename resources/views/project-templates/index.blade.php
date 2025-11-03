@extends('layouts.app')

@section('title', 'Project Templates')

@section('content')
{{-- Sticky Header --}}
<div class="bg-white border-b border-gray-200 sticky z-50" style="top: var(--theme-header-height); margin-left: -90px; width: calc(100vw - 8rem); height: var(--theme-header-height); min-height: var(--theme-header-height);">
    <div style="padding: var(--theme-view-header-padding) 2rem; margin-left: 90px; max-width: calc(100vw - 8rem); padding-right: 3rem; height: 100%;">
        <div class="flex justify-between items-center" style="height: 100%;">
            <div>
                <h1 class="font-semibold text-gray-900" style="font-size: var(--theme-view-header-title-size); line-height: 1.2;">Project Templates</h1>
                <p class="text-gray-600" style="font-size: var(--theme-view-header-description-size); margin-top: 0.25rem;">Reusable templates for quick project setup</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                <a href="{{ route('project-templates.create') }}" id="header-new-btn"
                   class="header-btn inline-flex items-center"
                   style="padding: calc(var(--theme-view-header-padding) * 0.5) var(--theme-view-header-padding); font-size: var(--theme-view-header-button-size);">
                    <i class="fas fa-plus mr-1.5"></i>
                    New Template
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
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

    {{-- Filters & Search Card --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden mb-6">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Filter Templates</h2>
        </div>
        <div style="padding: var(--theme-card-padding);">
            <form method="GET" action="{{ route('project-templates.index') }}">
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Search
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by name or description..."
                               style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                    </div>

                    <div style="min-width: 200px;">
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Category
                        </label>
                        <select name="category"
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <div style="min-width: 200px;">
                        <label style="display: block; font-size: var(--theme-font-size); font-weight: 500; color: var(--theme-text); margin-bottom: 0.5rem;">
                            Company
                        </label>
                        <select name="company_id"
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(203, 213, 225, 0.6); border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: white;">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="flex gap-2">
                        <button type="submit" id="filter-btn"
                                style="padding: 0.5rem 1rem; color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); background-color: var(--theme-primary);">
                            <i class="fas fa-search mr-1.5"></i>Filter
                        </button>
                        <a href="{{ route('project-templates.index') }}" id="clear-btn"
                           style="padding: 0.5rem 1rem; background-color: #6b7280; color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); text-decoration: none; display: inline-flex; align-items: center;">
                            <i class="fas fa-times mr-1.5"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Templates Grid Card --}}
    <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden">
        <div class="border-b" style="border-color: rgba(203, 213, 225, 0.3); padding: var(--theme-card-padding);">
            <h2 style="font-size: calc(var(--theme-font-size) + 4px); font-weight: 600; color: var(--theme-text); margin: 0;">Templates ({{ $templates->total() }})</h2>
        </div>
        <div style="padding: var(--theme-card-padding);">
            @if($templates->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($templates as $template)
                        <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
                            <div style="padding: var(--theme-card-padding);">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text);">
                                            {{ $template->name }}
                                        </h3>
                                        @if($template->category)
                                            <p style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-primary); margin-top: 0.25rem;">
                                                {{ ucfirst($template->category) }}
                                            </p>
                                        @endif
                                    </div>
                                    @if(isset($template->status))
                                        <span class="inline-flex px-2 py-1 rounded-full"
                                              style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: {{ $template->status === 'active' ? 'rgba(var(--theme-success-rgb), 0.1)' : 'rgba(203, 213, 225, 0.2)' }}; color: {{ $template->status === 'active' ? 'var(--theme-success)' : 'var(--theme-text-muted)' }};">
                                            {{ ucfirst($template->status) }}
                                        </span>
                                    @endif
                                </div>

                                @if($template->description)
                                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-bottom: 1rem;">
                                        {{ Str::limit($template->description, 100) }}
                                    </p>
                                @endif

                                <div class="flex flex-wrap gap-2 mb-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full"
                                          style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-primary-rgb), 0.1); color: var(--theme-primary);">
                                        <i class="fas fa-flag mr-1"></i>
                                        {{ $template->milestones->count() }} milestones
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full"
                                          style="font-size: calc(var(--theme-font-size) - 2px); font-weight: 600; background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                        <i class="fas fa-tasks mr-1"></i>
                                        {{ $template->milestones->sum(function($m) { return $m->tasks->count(); }) }} tasks
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 mb-3" style="font-size: calc(var(--theme-font-size) - 1px); color: var(--theme-text-muted);">
                                    <span>
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $template->calculateTotalHours() }}h
                                    </span>
                                    <span>
                                        <i class="fas fa-euro-sign mr-1"></i>
                                        €{{ number_format($template->calculateTotalValue(), 0) }}
                                    </span>
                                </div>

                                <div style="font-size: calc(var(--theme-font-size) - 2px); color: var(--theme-text-muted); margin-bottom: 1rem;">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $template->created_at->format('M j, Y') }}
                                </div>

                                <div class="border-t pt-3" style="border-color: rgba(203, 213, 225, 0.3);">
                                    <div class="flex flex-col gap-2">
                                        <a href="{{ route('project-templates.show', $template) }}"
                                           style="font-size: var(--theme-font-size); color: var(--theme-primary); text-decoration: none; display: flex; align-items: center;">
                                            <i class="fas fa-eye mr-2"></i>
                                            View Details
                                        </a>

                                        @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                                        <a href="{{ route('project-templates.edit', $template) }}"
                                           style="font-size: var(--theme-font-size); color: var(--theme-text-muted); text-decoration: none; display: flex; align-items: center;">
                                            <i class="fas fa-edit mr-2"></i>
                                            Edit Template
                                        </a>

                                        <a href="#" onclick="useTemplate({{ $template->id }}); return false;"
                                           style="font-size: var(--theme-font-size); color: var(--theme-text-muted); text-decoration: none; display: flex; align-items: center;">
                                            <i class="fas fa-copy mr-2"></i>
                                            Use Template
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $templates->appends(request()->query())->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-4" style="background-color: rgba(203, 213, 225, 0.1);">
                        <svg class="w-12 h-12" style="color: var(--theme-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: calc(var(--theme-font-size) + 2px); font-weight: 600; color: var(--theme-text); margin-bottom: 0.5rem;">No templates found</h3>
                    <p style="font-size: var(--theme-font-size); color: var(--theme-text-muted); margin-bottom: 1.5rem;">
                        @if(request()->hasAny(['search', 'category', 'company_id']))
                            No templates match your search criteria. Try adjusting your filters.
                        @else
                            Start by creating your first project template.
                        @endif
                    </p>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
                    <a href="{{ route('project-templates.create') }}"
                       style="padding: 0.5rem 1rem; background-color: var(--theme-primary); color: white; border: none; border-radius: var(--theme-border-radius); font-size: var(--theme-font-size); text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="fas fa-plus mr-2"></i>
                        New Template
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Apply comprehensive theme styling
function styleThemeElements() {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim();

    // Header new button
    const newBtn = document.getElementById('header-new-btn');
    if (newBtn) {
        newBtn.style.backgroundColor = primaryColor;
        newBtn.style.color = 'white';
        newBtn.style.border = 'none';
        newBtn.style.borderRadius = 'var(--theme-border-radius)';
    }

    // Filter button
    const filterBtn = document.getElementById('filter-btn');
    if (filterBtn) {
        filterBtn.style.backgroundColor = primaryColor;
    }

    // Clear button hover effects
    const clearBtn = document.getElementById('clear-btn');
    if (clearBtn) {
        clearBtn.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#4b5563';
        });
        clearBtn.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '#6b7280';
        });
    }
}

// Template usage function
function useTemplate(templateId) {
    alert(`Template ${templateId} usage functionality coming soon! This will create a new project based on this template.`);
}

// Initialize styling when page loads
document.addEventListener('DOMContentLoaded', function() {
    styleThemeElements();
});
</script>
@endpush
@endsection
