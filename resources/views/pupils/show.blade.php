@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h2>Pupil Details</h2>

    <div class="row">
        <div class="col-md-6">
            <h4><strong>First Name:</strong> {{ $pupil->first_name }}</h4>
            <h4><strong>Last Name:</strong> {{ $pupil->last_name }}</h4>
            <h4><strong>Middle Name:</strong> {{ $pupil->middle_name ?  $pupil->middle_name : '-' }}</h4>
            <h4><strong>Gender:</strong> {{ $pupil->gender }}</h4>
            <h4><strong>Blood Group:</strong> {{ $pupil->blood_group ? $pupil->blood_group : '-' }}</h4>
            <h4><strong>Religion:</strong> {{ $pupil->religion }}</h4>
            <h4><strong>Date of Birth:</strong> {{ $pupil->date_of_birth ? $pupil->date_of_birth->format('d-m-Y') : '-' }}</h4>
            <h4><strong>Admission Date:</strong> {{ $pupil->admission_date ? $pupil->admission_date->format('d-m-Y') : '-' }}</h4>
            <h4><strong>Health Conditions:</strong> {{ $pupil->health_conditions ? $pupil->health_conditions : '-' }}</h4>
            <h4><strong>Class:</strong> {{ $pupil->class->name ?? 'N/A'  }}</h4>
        </div>

        <div class="col-md-6">
            <h2>Parent Information</h2>

            @if($pupil->parent)
                <h4><strong>Parent Name:</strong> {{ $pupil->parent->first_name }} {{ $pupil->parent->last_name }}</h4>
                <h4><strong>Phone:</strong> {{ $pupil->parent->phone }}</h4>
                <h4><strong>Email:</strong> {{ $pupil->parent->email ? $pupil->parent->email : '-' }}</h4>
                <h4><strong>Address:</strong> {{ $pupil->parent->address }}</h4>
                <a href="{{ route('parents.edit', $pupil->parent->id) }}" class="btn btn-warning">Edit Parent Information</a>
            @else
                <div class="alert alert-warning">
                    <p>No parent information found for this pupil.</p>
                </div>
                <a href="{{ route('parents.create', $pupil->id) }}" class="btn btn-primary">Add Parent Information</a>
            @endif
        </div>
    </div>

    <a href="{{ route('pupils.edit', $pupil->id) }}" class="btn btn-warning mt-3">Edit Pupils Details</a>
    <a href="{{ route('pupils.index') }}" class="btn btn-secondary mt-3">Back to Pupil List</a>
    <form action="{{ route('pupils.destroy', $pupil->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this pupil and all related data?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn mt-2 btn-danger">Delete Pupil</button>
    </form>
</div>
@endsection
