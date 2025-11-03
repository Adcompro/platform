@extends('layouts.app')

@section('title', 'Google Calendar Setup')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Google Calendar Setup</h1>

    <p>This is a test page to verify the route works.</p>

    <a href="{{ route('calendar.providers.index') }}" class="text-blue-600 hover:underline">
        Back to Providers
    </a>
</div>
@endsection