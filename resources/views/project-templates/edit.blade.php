@extends('layouts.app')

@section('content')
<div class="min-h-screen" style="background: var(--theme-gradient);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" style="padding: 2rem 1rem;">
        {{-- Header --}}
        <div class="mb-8">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/60 rounded-xl p-6" style="padding: 1.5rem;">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 14px);">Template Bewerken</h1>
                        <p class="mt-2" style="color: var(--theme-text-muted); ">Bewerk "{{ $projectTemplate->name }}"</p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <a href="{{ route('project-templates.show', $projectTemplate) }}" class="px-4 py-2 rounded-lg transition-colors text-white" 
                           style="padding: 0.5rem 1rem; background: var(--theme-secondary); ">>
                            <i class="fas fa-arrow-left mr-2"></i>Terug naar Template
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('project-templates.update', $projectTemplate) }}" id="template-form" 
              x-data="{ reorderMode: false }"
              @submit="updateAllInputNames()">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2">
                    {{-- Template Basis Info --}}
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl mb-8" style="border: 1px solid rgba(var(--theme-border-rgb), 0.3); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <div class="px-6 py-4" style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.2);">
                            <h3 class="font-medium" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Template Informatie</h3>
                            <p class="mt-1" style="color: var(--theme-text-muted); ">Basis gegevens voor de template</p>
                        </div>
                        <div class="p-6 space-y-6" style="padding: 1.5rem;">
                            <div>
                                <label for="name" class="block font-medium" style="color: var(--theme-text); ">Template Naam*</label>
                                <input type="text" name="name" id="name" required
                                       value="{{ old('name', $projectTemplate->name) }}"
                                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                       onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                       placeholder="Bijv. Website Project Template">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block font-medium" style="color: var(--theme-text); ">Beschrijving</label>
                                <textarea name="description" id="description" rows="3"
                                          class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                          style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                          onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                          onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                          placeholder="Beschrijf waarvoor deze template gebruikt wordt...">{{ old('description', $projectTemplate->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @if(Schema::hasColumn('project_templates', 'default_hourly_rate'))
                                <div>
                                    <label for="default_hourly_rate" class="block font-medium" style="color: var(--theme-text); ">Standaard Uurtarief (€)</label>
                                    <input type="number" name="default_hourly_rate" id="default_hourly_rate" step="0.01" min="0"
                                           value="{{ old('default_hourly_rate', $projectTemplate->default_hourly_rate) }}"
                                           class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                           style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                           onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                           onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                           placeholder="75.00">
                                    @error('default_hourly_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @endif

                                @if(Schema::hasColumn('project_templates', 'estimated_duration_days'))
                                <div>
                                    <label for="estimated_duration_days" class="block font-medium" style="color: var(--theme-text); ">Geschatte Duur (dagen)</label>
                                    <input type="number" name="estimated_duration_days" id="estimated_duration_days" min="0"
                                           value="{{ old('estimated_duration_days', $projectTemplate->estimated_duration_days) }}"
                                           class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                           style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                           onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                           onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                           placeholder="30">
                                    @error('estimated_duration_days')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @endif

                                @if(Schema::hasColumn('project_templates', 'status'))
                                <div>
                                    <label for="status" class="block font-medium" style="color: var(--theme-text); ">Status</label>
                                    <select name="status" id="status" 
                                            class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                            style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                            onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                            onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
                                        <option value="active" {{ old('status', $projectTemplate->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $projectTemplate->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Milestones Builder --}}
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl mb-8" style="border: 1px solid rgba(var(--theme-border-rgb), 0.3); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <div class="px-6 py-4" style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.2);">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Template Structuur</h3>
                                    <p class="mt-1" style="color: var(--theme-text-muted); ">Voeg milestones en tasks toe</p>
                                </div>
                                <button type="button" onclick="addMilestone()" class="px-4 py-2 rounded-lg transition-colors text-white hover:opacity-80" 
                                        style="padding: 0.5rem 1rem; background: rgb(var(--theme-primary-rgb)); ">
                                    <i class="fas fa-plus mr-2"></i>Milestone Toevoegen
                                </button>
                            </div>
                        </div>
                        <div class="px-6 py-4" style="padding: 1rem 1.5rem;">
                            <div id="milestones-container" class="space-y-6" x-ignore>
                                {{-- Existing Milestones --}}
                                @foreach($projectTemplate->milestones as $milestone)
                                    <div class="milestone-item rounded-lg p-6" data-milestone-index="{{ $loop->iteration }}" data-milestone-id="milestone-{{ $milestone->id }}" 
                                         style="padding: 1.5rem; border: 1px solid rgba(var(--theme-primary-rgb), 0.3); background: linear-gradient(135deg, rgba(var(--theme-primary-rgb), 0.1) 0%, rgba(var(--theme-primary-rgb), 0.05) 100%);">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center">
                                                <div class="drag-handle cursor-move mr-3" style="color: var(--theme-text-muted);">
                                                    <i class="fas fa-grip-vertical"></i>
                                                </div>
                                                <h4 class="font-semibold" style="color: rgb(var(--theme-primary-rgb)); ">Milestone <span class="milestone-number">{{ $loop->iteration }}</span></h4>
                                            </div>
                                            <button type="button" onclick="removeMilestone(this)" class="text-red-600 hover:text-red-800 transition-colors" style="">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                            <div class="md:col-span-2">
                                                <label class="block font-medium" style="color: var(--theme-text); ">Milestone Name*</label>
                                                <input type="text" name="milestones[{{ $loop->index }}][name]" required
                                                       value="{{ $milestone->name }}"
                                                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                       onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                                       placeholder="E.g. Design Phase">
                                            </div>
                                            <div>
                                                <label class="block font-medium" style="color: var(--theme-text); ">Estimated Hours</label>
                                                <input type="number" name="milestones[{{ $loop->index }}][estimated_hours]" min="0" step="0.5"
                                                       value="{{ $milestone->estimated_hours }}"
                                                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                       onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                                       placeholder="40">
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block font-medium" style="color: var(--theme-text); ">Description</label>
                                            <textarea name="milestones[{{ $loop->index }}][description]" rows="3"
                                                      class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                      style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                      onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                                      onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                                      placeholder="Describe this milestone...">{{ $milestone->description }}</textarea>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                                            <div>
                                                <label class="block font-medium" style="color: var(--theme-text); ">Fee Type</label>
                                                <select name="milestones[{{ $loop->index }}][fee_type]"
                                                        class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                        style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                        onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                                                        onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
                                                    <option value="in_fee" {{ old('milestones.' . $loop->index . '.fee_type', $milestone->fee_type) === 'in_fee' ? 'selected' : '' }}>In Fee</option>
                                                    <option value="extended" {{ old('milestones.' . $loop->index . '.fee_type', $milestone->fee_type) === 'extended' ? 'selected' : '' }}>Extended</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Tasks Container --}}
                                        <div class="tasks-container mt-6">
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="font-medium" style="color: var(--theme-text); ">Tasks</h5>
                                                <button type="button" onclick="addTask(this)" class="px-3 py-1 rounded text-white hover:opacity-80 transition-colors" 
                                                        style="padding: 0.25rem 0.75rem; background: rgb(var(--theme-accent-rgb)); ">
                                                    <i class="fas fa-plus mr-1"></i>Add Task
                                                </button>
                                            </div>
                                            <div class="tasks-list space-y-3" x-ignore>
                                                {{-- Existing Tasks --}}
                                                @foreach($milestone->tasks as $task)
                                                    <div class="task-item rounded p-4" data-task-index="{{ $loop->iteration }}" data-task-id="task-{{ $milestone->id }}-{{ $task->id }}" 
                                                         style="padding: 1rem; background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.1) 0%, rgba(var(--theme-accent-rgb), 0.05) 100%); border: 1px solid rgba(var(--theme-accent-rgb), 0.3);">
                                                        <input type="hidden" name="milestones[{{ $loop->parent->index }}][tasks][{{ $loop->index }}][sort_order]" value="{{ $loop->iteration }}" class="task-sort-order">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <div class="flex items-center">
                                                                <div class="task-drag-handle cursor-move mr-2" style="color: var(--theme-text-muted);">
                                                                    <i class="fas fa-grip-vertical"></i>
                                                                </div>
                                                                <h6 class="font-medium" style="color: rgb(var(--theme-accent-rgb)); ">Task <span class="task-number">{{ $loop->iteration }}</span></h6>
                                                            </div>
                                                            <button type="button" onclick="removeTask(this)" class="text-red-600 hover:text-red-800 transition-colors" style="">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                                            <div class="md:col-span-2">
                                                                <label class="block font-medium" style="color: var(--theme-text); ">Task Name*</label>
                                                                <input type="text" name="milestones[{{ $milestone->sort_order - 1 }}][tasks][{{ $loop->index }}][name]" required
                                                                       value="{{ $task->name }}"
                                                                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                                       onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                                                                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                                                       placeholder="E.g. Logo Design">
                                                            </div>
                                                            <div>
                                                                <label class="block font-medium" style="color: var(--theme-text); ">Estimated Hours</label>
                                                                <input type="number" name="milestones[{{ $milestone->sort_order - 1 }}][tasks][{{ $loop->index }}][estimated_hours]" min="0" step="0.5"
                                                                       value="{{ $task->estimated_hours }}"
                                                                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                                       onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                                                                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                                                       placeholder="8">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="block font-medium" style="color: var(--theme-text); ">Description</label>
                                                            <textarea name="milestones[{{ $milestone->sort_order - 1 }}][tasks][{{ $loop->index }}][description]" rows="3"
                                                                      class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                                      style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                                      onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                                                                      onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                                                                      placeholder="Describe this task...">{{ $task->description }}</textarea>
                                                        </div>

                                                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-3">
                                                            <div>
                                                                <label class="block font-medium" style="color: var(--theme-text); ">Fee Type</label>
                                                                <select name="milestones[{{ $milestone->sort_order - 1 }}][tasks][{{ $loop->index }}][fee_type]"
                                                                        class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                                                                        style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                                        onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                                                                        onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
                                                                    <option value="in_fee" {{ ($task->fee_type ?? 'in_fee') === 'in_fee' ? 'selected' : '' }}>In Fee</option>
                                                                    <option value="extended" {{ ($task->fee_type ?? 'in_fee') === 'extended' ? 'selected' : '' }}>Extended</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Empty state if no milestones --}}
                                @if($projectTemplate->milestones->count() === 0)
                                    <div class="text-center py-8" id="empty-state" style="padding-top: 2rem; padding-bottom: 2rem; color: var(--theme-text-muted);">
                                        <i class="fas fa-tasks mb-4" style="font-size: calc(var(--theme-font-size) + 24px);"></i>
                                        <p style="">No milestones added yet</p>
                                        <p style="font-size: calc(var(--theme-font-size) - 2px);">Click "Add Milestone" to get started</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1">
                    {{-- Template Statistics --}}
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl mb-6" style="border: 1px solid rgba(var(--theme-border-rgb), 0.3); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <div class="px-6 py-4" style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(var(--theme-border-rgb), 0.2);">
                            <h3 class="font-medium" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 4px);">Template Statistieken</h3>
                        </div>
                        <div class="px-6 py-4 space-y-4" style="padding: 1rem 1.5rem;">
                            <div class="flex justify-between items-center">
                                <span style="color: var(--theme-text-muted); ">Milestones:</span>
                                <span class="font-medium" id="stats-milestones" style="color: var(--theme-text); ">{{ $projectTemplate->milestones->count() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span style="color: var(--theme-text-muted); ">Tasks:</span>
                                <span class="font-medium" id="stats-tasks" style="color: var(--theme-text); ">{{ $projectTemplate->milestones->sum(function($m) { return $m->tasks->count(); }) }}</span>
                            </div>
                            <hr style="border-color: rgba(var(--theme-border-rgb), 0.2);">
                            <div class="flex justify-between items-center">
                                <span style="color: var(--theme-text-muted); ">Totale Uren:</span>
                                <span class="font-medium" id="stats-hours" style="color: var(--theme-text); ">{{ $projectTemplate->calculateTotalHours() }}h</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span style="color: var(--theme-text-muted); ">Geschatte Waarde:</span>
                                <span class="font-medium" id="stats-value" style="color: var(--theme-text); ">€{{ number_format($projectTemplate->calculateTotalValue(), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-4">
                        <button type="submit" class="w-full text-white px-4 py-3 rounded-lg transition-colors font-medium hover:opacity-90" 
                                style="padding: 0.75rem 1rem; background: rgb(var(--theme-primary-rgb)); ">
                            <i class="fas fa-save mr-2"></i>Wijzigingen Opslaan
                        </button>
                        
                        <a href="{{ route('project-templates.show', $projectTemplate) }}" class="w-full px-4 py-3 rounded-lg transition-colors font-medium text-center block hover:opacity-80" 
                           style="padding: 0.75rem 1rem; background: var(--theme-secondary); color: var(--theme-text); ">
                            <i class="fas fa-times mr-2"></i>Annuleren
                        </a>
                        
                        <div style="border-top: 1px solid rgba(var(--theme-border-rgb), 0.2); padding-top: 1rem;">
                            <button type="button" onclick="deleteTemplate()" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg transition-colors font-medium" 
                                    style="padding: 0.75rem 1rem; ">
                                <i class="fas fa-trash mr-2"></i>Template Verwijderen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Templates for new items --}}
<template id="milestone-template">
    <div class="milestone-item rounded-lg p-6" data-milestone-index="" data-milestone-id="" 
         style="padding: 1.5rem; border: 1px solid rgba(var(--theme-primary-rgb), 0.3); background: linear-gradient(135deg, rgba(var(--theme-primary-rgb), 0.1) 0%, rgba(var(--theme-primary-rgb), 0.05) 100%);">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="drag-handle cursor-move mr-3" style="color: var(--theme-text-muted);">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <h4 class="font-semibold" style="color: rgb(var(--theme-primary-rgb)); ">Milestone <span class="milestone-number"></span></h4>
            </div>
            <button type="button" onclick="removeMilestone(this)" class="text-red-600 hover:text-red-800 transition-colors" style="">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="md:col-span-2">
                <label class="block font-medium" style="color: var(--theme-text); ">Milestone Name*</label>
                <input type="text" name="milestones[][name]" required
                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                       onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                       placeholder="E.g. Design Phase">
            </div>
            <div>
                <label class="block font-medium" style="color: var(--theme-text); ">Estimated Hours</label>
                <input type="number" name="milestones[][estimated_hours]" min="0" step="0.5"
                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                       onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                       placeholder="40">
            </div>
        </div>

        <div class="mb-4">
            <label class="block font-medium" style="color: var(--theme-text); ">Description</label>
            <textarea name="milestones[][description]" rows="3"
                      class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                      style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                      onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                      onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                      placeholder="Describe this milestone..."></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
            <div>
                <label class="block font-medium" style="color: var(--theme-text); ">Fee Type</label>
                <select name="milestones[][fee_type]"
                        class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                        style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                        onFocus="this.style.borderColor='rgb(var(--theme-primary-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-primary-rgb), 0.1)';"
                        onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
                    <option value="in_fee">In Fee</option>
                    <option value="extended">Extended</option>
                </select>
            </div>
        </div>

        <div class="tasks-container mt-6">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-medium" style="color: var(--theme-text); font-size: calc(var(--theme-font-size) + 2px);">Tasks</h5>
                <button type="button" onclick="addTask(this)" class="px-3 py-1 rounded text-white hover:opacity-80 transition-colors" 
                        style="padding: 0.25rem 0.75rem; background: rgb(var(--theme-accent-rgb)); ">
                    <i class="fas fa-plus mr-1"></i>Task Toevoegen
                </button>
            </div>
            <div class="tasks-list space-y-3" x-ignore></div>
        </div>
    </div>
</template>

<template id="task-template">
    <div class="task-item rounded p-4" data-task-index="" 
         style="padding: 1rem; background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.1) 0%, rgba(var(--theme-accent-rgb), 0.05) 100%); border: 1px solid rgba(var(--theme-accent-rgb), 0.3);">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
                <div class="task-drag-handle cursor-move mr-2" style="color: var(--theme-text-muted);">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <h6 class="font-medium" style="color: rgb(var(--theme-accent-rgb)); ">Task <span class="task-number"></span></h6>
            </div>
            <button type="button" onclick="removeTask(this)" class="text-red-600 hover:text-red-800 transition-colors" style="">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
            <div class="md:col-span-2">
                <label class="block font-medium" style="color: var(--theme-text); ">Task Name*</label>
                <input type="text" name="milestones[][tasks][][name]" required
                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                       onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                       placeholder="E.g. Logo Design">
            </div>
            <div>
                <label class="block font-medium" style="color: var(--theme-text); ">Estimated Hours</label>
                <input type="number" name="milestones[][tasks][][estimated_hours]" min="0" step="0.5"
                       class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                       style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                       onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                       onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                       placeholder="8">
            </div>
        </div>

        <div class="mb-3">
            <label class="block font-medium" style="color: var(--theme-text); ">Description</label>
            <textarea name="milestones[][tasks][][description]" rows="3"
                      class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                      style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                      onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                      onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';"
                      placeholder="Describe this task..."></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-3">
            <div>
                <label class="block font-medium" style="color: var(--theme-text); ">Fee Type</label>
                <select name="milestones[][tasks][][fee_type]"
                        class="mt-1 block w-full rounded-md px-3 py-2 transition-colors"
                        style="background: white; border: 1px solid rgba(var(--theme-border-rgb), 0.3); color: var(--theme-text);  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                        onFocus="this.style.borderColor='rgb(var(--theme-accent-rgb))'; this.style.boxShadow='0 0 0 3px rgba(var(--theme-accent-rgb), 0.1)';"
                        onBlur="this.style.borderColor='rgba(var(--theme-border-rgb), 0.3)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)';">
                    <option value="in_fee">In Fee</option>
                    <option value="extended">Extended</option>
                </select>
            </div>
        </div>
    </div>
</template>


{{-- Success/Error Messages --}}
@if(session('success'))
    <div class="fixed top-4 right-4 text-white px-6 py-4 rounded-lg shadow-lg z-50" style="padding: 1rem 1.5rem; background: rgb(var(--theme-success-rgb));">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span style="">{{ session('success') }}</span>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const successMessage = document.querySelector('.fixed.top-4.right-4');
            if (successMessage) successMessage.remove();
        }, 5000);
    </script>
@endif

@if(session('error'))
    <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50" style="padding: 1rem 1.5rem;">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span style="">{{ session('error') }}</span>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const errorMessage = document.querySelector('.fixed.top-4.right-4');
            if (errorMessage) errorMessage.remove();
        }, 5000);
    </script>
@endif

<script>
let milestoneCount = {{ $projectTemplate->milestones->count() }};

function deleteTemplate() {
    if (confirm('Weet je zeker dat je deze template wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("project-templates.destroy", $projectTemplate) }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function addMilestone() {
    const container = document.getElementById('milestones-container');
    const emptyState = document.getElementById('empty-state');
    const template = document.getElementById('milestone-template');
    
    milestoneCount++;
    
    const clone = template.content.cloneNode(true);
    const milestoneDiv = clone.querySelector('.milestone-item');
    milestoneDiv.setAttribute('data-milestone-index', milestoneCount);
    milestoneDiv.setAttribute('data-milestone-id', 'milestone-new-' + Date.now()); // Add unique ID for new milestones
    clone.querySelector('.milestone-number').textContent = milestoneCount;
    
    updateMilestoneInputNames(clone, milestoneCount - 1);
    
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    container.appendChild(clone);
    
    // Reinitialize sortable for all task lists (including new one)
    if (typeof window.initTasksSortable === 'function') {
        setTimeout(() => {
            window.initTasksSortable();
        }, 100);
    }
    
    // Reinitialize milestones sortable for new milestone
    if (typeof window.initMilestonesSortable === 'function') {
        setTimeout(() => {
            window.initMilestonesSortable();
        }, 100);
    }
    
    updateStats();
}

function removeMilestone(button) {
    if (confirm('Are you sure you want to remove this milestone?')) {
        const milestoneDiv = button.closest('.milestone-item');
        
        // Clear stored order for this milestone
        localStorage.removeItem('milestone-order');
        
        milestoneDiv.remove();
        
        updateMilestoneNumbers();
        updateStats();
        
        const container = document.getElementById('milestones-container');
        const emptyState = document.getElementById('empty-state');
        if (container.children.length === 1 && emptyState) {
            emptyState.style.display = 'block';
        }
        
        // Reinitialize milestones sortable after removal
        if (typeof window.initMilestonesSortable === 'function') {
            setTimeout(() => {
                window.initMilestonesSortable();
            }, 100);
        }
    }
}

function addTask(button) {
    const tasksContainer = button.closest('.milestone-item').querySelector('.tasks-list');
    const template = document.getElementById('task-template');
    const milestoneIndex = button.closest('.milestone-item').getAttribute('data-milestone-index') - 1;
    
    const taskCount = tasksContainer.children.length + 1;
    
    const clone = template.content.cloneNode(true);
    clone.querySelector('.task-number').textContent = taskCount;
    clone.querySelector('.task-item').setAttribute('data-task-index', taskCount);
    
    updateTaskInputNames(clone, milestoneIndex, taskCount - 1);
    
    tasksContainer.appendChild(clone);
    
    
    updateStats();
}

function removeTask(button) {
    if (confirm('Are you sure you want to remove this task?')) {
        button.closest('.task-item').remove();
        updateTaskNumbers(button.closest('.tasks-list'));
        updateStats();
    }
}


function updateMilestoneInputNames(clone, index) {
    const inputs = clone.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        let name = input.getAttribute('name');
        if (name && name.includes('milestones[]')) {
            name = name.replace('milestones[]', `milestones[${index}]`);
            input.setAttribute('name', name);
        }
    });
}

function updateTaskInputNames(clone, milestoneIndex, taskIndex) {
    const inputs = clone.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        let name = input.getAttribute('name');
        if (name && name.includes('milestones[][tasks][]')) {
            name = name.replace('milestones[][tasks][]', `milestones[${milestoneIndex}][tasks][${taskIndex}]`);
            input.setAttribute('name', name);
        }
    });
}


function updateMilestoneNumbers() {
    const milestones = document.querySelectorAll('.milestone-item');
    milestones.forEach((milestone, milestoneIndex) => {
        milestone.setAttribute('data-milestone-index', milestoneIndex + 1);
        milestone.querySelector('.milestone-number').textContent = milestoneIndex + 1;
    });
    milestoneCount = milestones.length;
    
    // Don't update input names here - do it on form submit
    console.log('Milestone visual numbers updated');
}

// Function to update ALL input names based on current DOM order
// This should be called right before form submission
function updateAllInputNames() {
    console.log('Updating all input names for form submission...');
    
    const milestones = document.querySelectorAll('.milestone-item');
    milestones.forEach((milestone, milestoneIndex) => {
        // Update milestone inputs
        milestone.querySelectorAll('input, textarea, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('milestones[')) {
                if (!name.includes('[tasks]')) {
                    // Milestone-level input
                    const fieldMatch = name.match(/milestones\[\d+\](.+)$/);
                    if (fieldMatch) {
                        const fieldName = fieldMatch[1];
                        const newName = `milestones[${milestoneIndex}]${fieldName}`;
                        input.setAttribute('name', newName);
                    }
                }
            }
        });
        
        // Update task inputs
        const tasks = milestone.querySelectorAll('.task-item');
        tasks.forEach((task, taskIndex) => {
            task.querySelectorAll('input, textarea, select').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('[tasks]')) {
                    // Task-level input
                    const fieldMatch = name.match(/\[tasks\]\[\d+\](.+)$/);
                    if (fieldMatch) {
                        const fieldName = fieldMatch[1];
                        const newName = `milestones[${milestoneIndex}][tasks][${taskIndex}]${fieldName}`;
                        input.setAttribute('name', newName);
                    }
                }
            });
            
        });
    });
    
    console.log('All input names updated for submission');
}

function updateTaskNumbers(tasksContainer) {
    if (!tasksContainer) return;
    
    console.log('Updating task numbers (visual only)...');
    const tasks = tasksContainer.querySelectorAll('.task-item');
    
    // ONLY update the visual numbers, NOT the input names
    // Input names will be updated right before form submission
    tasks.forEach((task, taskIndex) => {
        task.setAttribute('data-task-index', taskIndex + 1);
        const taskNumber = task.querySelector('.task-number');
        if (taskNumber) {
            taskNumber.textContent = taskIndex + 1;
        }
    });
    
    console.log('Task visual numbers updated');
}


function updateStats() {
    const milestones = document.querySelectorAll('.milestone-item').length;
    const tasks = document.querySelectorAll('.task-item').length;
    
    let totalHours = 0;
    const defaultRate = document.getElementById('default_hourly_rate') ? document.getElementById('default_hourly_rate').value || 75 : 75;
    
    // Calculate total hours from all inputs
    document.querySelectorAll('input[name*="estimated_hours"]').forEach(input => {
        const hours = parseFloat(input.value) || 0;
        totalHours += hours;
    });
    
    const totalValue = totalHours * parseFloat(defaultRate);
    
    document.getElementById('stats-milestones').textContent = milestones;
    document.getElementById('stats-tasks').textContent = tasks;
    document.getElementById('stats-hours').textContent = totalHours + 'h';
    document.getElementById('stats-value').textContent = '€' + totalValue.toLocaleString('nl-NL', {minimumFractionDigits: 2});
}

// Preview function removed - no longer needed

// Store Sortable instances
let sortableInstances = {
    milestones: null,
    tasks: []
};

// Event listeners for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Update stats when form fields change
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name*="estimated_hours"], #default_hourly_rate')) {
            updateStats();
        }
    });
    
    // Use whichever sortable library is available
    if (typeof Sortable !== 'undefined') {
        console.log('Using SortableJS with reload strategy...');
        
        // Initialize milestones sortable with reload strategy
        function initMilestonesSortable() {
            const milestonesContainer = document.getElementById('milestones-container');
            console.log('Initializing sortable for milestones');
            
            // Apply saved order from localStorage if exists
            try {
                const savedOrder = localStorage.getItem('milestone-order');
                if (savedOrder && savedOrder !== 'undefined' && savedOrder !== 'null') {
                    const orderArray = JSON.parse(savedOrder);
                    if (Array.isArray(orderArray) && orderArray.length > 0) {
                        const items = Array.from(milestonesContainer.children);
                        items.sort((a, b) => {
                            const aId = a.getAttribute('data-milestone-id');
                            const bId = b.getAttribute('data-milestone-id');
                            return orderArray.indexOf(aId) - orderArray.indexOf(bId);
                        });
                        milestonesContainer.innerHTML = '';
                        items.forEach(item => milestonesContainer.appendChild(item));
                    }
                }
            } catch (e) {
                console.log('Error restoring milestone order:', e);
                localStorage.removeItem('milestone-order');
            }
            
            // Initialize Sortable
            new Sortable(milestonesContainer, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function(evt) {
                    console.log('Milestone moved - saving and reloading');
                    
                    // Save the new order
                    const order = [];
                    milestonesContainer.querySelectorAll('.milestone-item').forEach(milestone => {
                        order.push(milestone.getAttribute('data-milestone-id'));
                    });
                    localStorage.setItem('milestone-order', JSON.stringify(order));
                    
                    // Update milestone numbers
                    updateMilestoneNumbers();
                    updateStats();
                    
                    // Show saving message and reload
                    showSaveMessage();
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        }
        
        // Initialize milestones
        initMilestonesSortable();
        
        // Initialize sortable for ALL tasks lists 
        function initTasksSortable() {
            document.querySelectorAll('.tasks-list').forEach((tasksList, index) => {
                console.log('Initializing sortable for tasks list', index);
                
                // Apply saved order from localStorage if exists
                try {
                    const savedOrder = localStorage.getItem('task-order-' + index);
                    if (savedOrder && savedOrder !== 'undefined' && savedOrder !== 'null') {
                        const orderArray = JSON.parse(savedOrder);
                        if (Array.isArray(orderArray) && orderArray.length > 0) {
                            const items = Array.from(tasksList.children);
                            items.sort((a, b) => {
                                const aId = a.getAttribute('data-task-id');
                                const bId = b.getAttribute('data-task-id');
                                return orderArray.indexOf(aId) - orderArray.indexOf(bId);
                            });
                            tasksList.innerHTML = '';
                            items.forEach(item => tasksList.appendChild(item));
                        }
                    }
                } catch (e) {
                    console.log('Error restoring task order:', e);
                    localStorage.removeItem('task-order-' + index);
                }
                
                // Initialize Sortable
                new Sortable(tasksList, {
                    handle: '.task-drag-handle',
                    animation: 150,
                    onEnd: function(evt) {
                        console.log('Task moved - saving and reloading');
                        
                        // Save the new order
                        const order = [];
                        tasksList.querySelectorAll('.task-item').forEach(task => {
                            order.push(task.getAttribute('data-task-id'));
                        });
                        localStorage.setItem('task-order-' + index, JSON.stringify(order));
                        
                        // Update sort order fields
                        tasksList.querySelectorAll('.task-item').forEach((task, idx) => {
                            const sortInput = task.querySelector('.task-sort-order');
                            if (sortInput) sortInput.value = idx + 1;
                        });
                        
                        // Show saving message and reload
                        showSaveMessage();
                        setTimeout(() => window.location.reload(), 500);
                    }
                });
            });
        }
        
        
        // Initialize tasks
        initTasksSortable();
        
        // Store init function globally for reuse
        window.initTasksSortable = initTasksSortable;
    }
    
    // Function to show saving message
    function showSaveMessage() {
        const message = document.createElement('div');
        message.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        message.style.padding = '0.75rem 1.5rem';
        message.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving order...';
        document.body.appendChild(message);
    }
    
    // Initial stats update
    updateStats();
    
    // Update all input names right before form submission
    const templateForm = document.getElementById('template-form');
    if (templateForm) {
        templateForm.addEventListener('submit', function(e) {
            console.log('Form is being submitted, updating all input names...');
            updateAllInputNames();
            
            // Clear localStorage after successful submit
            localStorage.removeItem('milestone-order');
            for (let i = 0; i < 10; i++) {
                localStorage.removeItem('task-order-' + i);
            }
            
            // Allow form to submit after updating names
        });
    }
    
    // Also add a button to clear the saved order
    const clearOrderBtn = document.createElement('button');
    clearOrderBtn.type = 'button';
    clearOrderBtn.className = 'fixed bottom-4 right-4 text-white px-4 py-2 rounded-lg shadow-lg z-40';
    clearOrderBtn.style.cssText = 'padding: 0.5rem 1rem; background: var(--theme-secondary); ';
    clearOrderBtn.innerHTML = '<i class="fas fa-undo mr-2"></i>Reset Order';
    clearOrderBtn.onclick = function() {
        if (confirm('Reset the drag and drop order to original?')) {
            localStorage.removeItem('milestone-order');
            for (let i = 0; i < 10; i++) {
                localStorage.removeItem('task-order-' + i);
            }
            window.location.reload();
        }
    };
    document.body.appendChild(clearOrderBtn);
});
</script>
<style>
.sortable-fallback {
    opacity: 0.8 !important;
    background-color: #e0f2fe !important;
}
</style>
@endsection