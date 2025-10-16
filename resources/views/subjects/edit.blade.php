<!-- resources/views/subjects/edit.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Subject</h1>

    <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Name <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $subject->name) }}" required>
            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
