@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Simple Form Test for Project {{ $project->id }}</h1>
    
    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label class="block font-bold mb-2">Project Name:</label>
            <input type="text" name="name" value="{{ $project->name }}" class="border p-2 w-full" required>
        </div>
        
        <div class="mb-4">
            <label class="block font-bold mb-2">Customer:</label>
            <select name="customer_id" class="border p-2 w-full" required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $project->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block font-bold mb-2">Status:</label>
            <select name="status" class="border p-2 w-full" required>
                <option value="draft" {{ $project->status == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="on_hold" {{ $project->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                <option value="cancelled" {{ $project->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block font-bold mb-2">Start Date:</label>
            <input type="date" name="start_date" value="{{ $project->start_date?->format('Y-m-d') }}" class="border p-2 w-full" required>
        </div>
        
        <div class="mb-4">
            <label class="block font-bold mb-2">Billing Frequency:</label>
            <select name="billing_frequency" class="border p-2 w-full" required>
                <option value="monthly" {{ $project->billing_frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="quarterly" {{ $project->billing_frequency == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="project_completion" {{ $project->billing_frequency == 'project_completion' ? 'selected' : '' }}>Project Completion</option>
            </select>
        </div>
        
        <div class="mb-4 p-4 bg-yellow-100 border-2 border-yellow-500">
            <label class="block font-bold mb-2 text-red-600">TEAM MEMBERS TEST:</label>
            @foreach($users as $user)
                <label class="block mb-1">
                    <input type="checkbox" name="team_members[]" value="{{ $user->id }}" 
                           {{ $project->users->contains($user->id) ? 'checked' : '' }}>
                    {{ $user->name }} (ID: {{ $user->id }})
                </label>
            @endforeach
        </div>
        
        <div class="mb-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded">
                SAVE PROJECT
            </button>
        </div>
    </form>
    
    <div class="mt-8 p-4 bg-gray-100">
        <h2 class="font-bold mb-2">Current Team Members:</h2>
        <ul>
            @foreach($project->users as $member)
                <li>{{ $member->name }} (ID: {{ $member->id }})</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection