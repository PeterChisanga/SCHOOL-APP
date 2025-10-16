@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Secretary</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('secretaries.update', $secretary->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="first_name">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $secretary->user->first_name) }}" required>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $secretary->user->last_name) }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $secretary->user->email) }}" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $secretary->user->phone_number) }}" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (Leave blank if not changing)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm New Password (Leave blank if not changing)</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
        </div>

        <button type="submit" class="btn btn-primary">Update Secretary</button>
    </form>
</div>
@endsection
