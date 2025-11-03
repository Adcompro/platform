@extends('layouts.app')

@section('title', 'Edit Project - ' . $project->name)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1>Edit Project: {{ $project->name }}</h1>
        
        <div class="mt-6">
            <h2>Milestones</h2>
            @forelse($project->milestones as $milestone)
                <div class="border p-4 mb-4">
                    <h3>{{ $milestone->name }}</h3>
                    
                    @if($milestone->tasks->count() > 0)
                        <h4>Tasks:</h4>
                        @foreach($milestone->tasks as $task)
                            <div class="ml-4 p-2 border-l">
                                {{ $task->name }}
                            </div>
                        @endforeach
                    @endif
                </div>
            @empty
                <p>No milestones yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection