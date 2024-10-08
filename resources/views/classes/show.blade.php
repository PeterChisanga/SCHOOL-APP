@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Class Details</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            Class: {{ $class->name }}
        </div>
        <div class="card-body">
            <p><strong>Class ID:</strong> {{ $class->id }}</p>
            <p><strong>School ID:</strong> {{ $class->school_id }}</p>
        </div>
    </div>

    <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-warning mt-3">Edit</a>
    <a href="{{ route('classes.index') }}" class="btn btn-secondary mt-3">Back to Classes</a>
</div>
@endsection
