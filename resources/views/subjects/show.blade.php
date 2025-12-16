<!-- resources/views/subjects/show.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Subject Details</h1>

    <div class="mb-3">
        <strong>ID:</strong> {{ $subject->id }}
    </div>
    <div class="mb-3">
        <strong>Name:</strong> {{ $subject->name }}
    </div>

    <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-warning">Edit</a>

    <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>

    <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection
