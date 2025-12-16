@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Class</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('classes.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Class Name</label>
            <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('classes.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
