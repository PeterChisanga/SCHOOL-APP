@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Secretary Details</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $secretary->user->first_name }} {{ $secretary->user->last_name }}</h5>
            <p class="card-text"><strong>Email:</strong> {{ $secretary->user->email }}</p>
            <p class="card-text"><strong>Phone:</strong> {{ $secretary->user->phone_number }}</p>
            <p class="card-text"><strong>School:</strong> {{ $secretary->school->name }}</p>
        </div>
    </div>

    <a href="{{ route('secretaries.index') }}" class="btn btn-secondary mt-3">Back to List</a>
</div>
@endsection
