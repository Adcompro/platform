@extends('layouts.app')

@section('title', 'Deleted Projects')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    {{-- Header Section --}}
    <div class="bg-white/70 backdrop-blur-sm border-b sticky top-0 z-10" style="border-color: rgba(var(--theme-border-rgb), 0.5);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-xl font-semibold" style="color: var(--theme-text);">Deleted Projects</h1>
                    <p class="text-xs mt-0.5" style="color: var(--theme-text-muted);">Manage and restore deleted projects</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('projects.index') }}" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 flex items-center" style="background-color: rgba(var(--theme-border-rgb), 0.1); color: var(--theme-text-muted);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.2)'" onmouseout="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.1)'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Projects
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-32">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 px-3 py-2.5 rounded-lg" style="background-color: rgba(var(--theme-accent-rgb), 0.1); border: 1px solid rgba(var(--theme-accent-rgb), 0.3); color: var(--theme-accent);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-accent-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
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
            <div class="mb-4 px-3 py-2.5 rounded-lg" style="background-color: rgba(var(--theme-danger-rgb), 0.1); border: 1px solid rgba(var(--theme-danger-rgb), 0.3); color: var(--theme-danger);">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 mt-0.5" style="color: rgba(var(--theme-danger-rgb), 0.6);" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Projects Table --}}
        <div class="backdrop-blur-sm rounded-xl overflow-hidden theme-card" style="background-color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(var(--theme-border-rgb), 0.6);">
            @if($deletedProjects->isEmpty())
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-12 w-12" style="color: rgba(var(--theme-border-rgb), 0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium" style="color: var(--theme-text);">No deleted projects</h3>
                    <p class="mt-1 text-xs" style="color: var(--theme-text-muted);">All projects are currently active.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full" style="border-collapse: separate; border-spacing: 0;">
                        <thead style="background-color: rgba(var(--theme-border-rgb), 0.1);">
                            <tr>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--theme-text-muted);">Project</th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--theme-text-muted);">Customer</th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--theme-text-muted);">Company</th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--theme-text-muted);">Status</th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--theme-text-muted);">Deleted</th>
                                <th scope="col" class="relative px-4 py-2.5"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody style="background-color: rgba(255, 255, 255, 0.5);">
                            @foreach($deletedProjects as $project)
                            <tr class="transition-colors" style="border-top: 1px solid rgba(var(--theme-border-rgb), 0.3);" onmouseover="this.style.backgroundColor='rgba(var(--theme-border-rgb), 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                                <td class="px-4 py-3">
                                    <div>
                                        <div class="text-sm font-medium line-through" style="color: var(--theme-text);">{{ $project->name }}</div>
                                        @if($project->description)
                                            <div class="text-xs mt-0.5" style="color: var(--theme-text-muted);">{{ Str::limit($project->description, 50) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm" style="color: var(--theme-text);">{{ $project->customer?->name ?? 'No Customer' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm" style="color: var(--theme-text);">{{ $project->companyRelation?->name ?? 'No Company' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'active' => 'bg-green-100 text-green-800',
                                            'on_hold' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusClass = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="{{ $statusClass }} px-2 py-0.5 inline-flex text-xs font-medium rounded-lg">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs" style="color: var(--theme-text-muted);">
                                    {{ \App\Helpers\DateHelper::format($project->deleted_at) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end">
                                        <!-- Restore Button -->
                                        <form action="{{ route('projects.restore', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to restore this project?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-white text-xs font-medium rounded-lg transition-all duration-200" style="background-color: var(--theme-accent);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                                <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                Restore
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-4 py-3" style="border-top: 1px solid rgba(var(--theme-border-rgb), 0.5);">
                    {{ $deletedProjects->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
