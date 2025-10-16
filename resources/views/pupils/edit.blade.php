@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Pupil Details</h2>

    <!-- Display validation errors if any -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pupils.update', $pupil->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Left column -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $pupil->first_name) }}" required>
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name', $pupil->middle_name) }}">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $pupil->last_name) }}" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="M" {{ old('gender', $pupil->gender) == 'M' ? 'selected' : '' }}>Male</option>
                        <option value="F" {{ old('gender', $pupil->gender) == 'F' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="blood_group">Blood Group</label>
                    <input type="text" class="form-control" id="blood_group" name="blood_group" value="{{ old('blood_group', $pupil->blood_group) }}" required>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="religion">Religion</label>
                    <input type="text" class="form-control" id="religion" name="religion" value="{{ old('religion', $pupil->religion) }}" required>
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $pupil->date_of_birth ? $pupil->date_of_birth->format('Y-m-d') : '' ) }}" required>
                </div>

                <div class="form-group">
                    <label for="admission_date">Admission Date</label>
                    <input type="date" class="form-control" id="admission_date" name="admission_date" value="{{ old('admission_date', $pupil->admission_date ? $pupil->admission_date->format('Y-m-d') : '') }}">
                </div>

                <div class="form-group">
                    <label for="health_conditions">Health Conditions</label>
                    <textarea class="form-control" id="health_conditions" name="health_conditions">{{ old('health_conditions', $pupil->health_conditions) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select class="form-control" id="class_id" name="class_id" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $pupil->class_id) == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update Pupil</button>
        <a href="{{ route('pupils.show', $pupil->id) }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>
@endsection
