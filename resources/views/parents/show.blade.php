@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Parent Details</h1>

    <p><strong>First Name:</strong> {{ $parent->first_name }}</p>
    <p><strong>Last Name:</strong> {{ $parent->last_name }}</p>
    <p><strong>Phone:</strong> {{ $parent->phone }}</p>
    <p><strong>Email:</strong> {{ $parent->email }}</p>
    <p><strong>Address:</strong> {{ $parent->address }}</p>
    <p><strong>Pupil:</strong> {{ $parent->pupil->first_name }} {{ $parent->pupil->last_name }}</p>

    <a href="{{ route('parents.edit', $parent->id) }}" class="btn btn-warning">Edit</a>

    <form action="{{ route('parents.destroy', $parent->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</div>
@endsection
