@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Teacher</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $teacher->first_name) }}" required>
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name </label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name', $teacher->middle_name) }}">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $teacher->last_name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $teacher->email) }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender <span class="text-danger">*</span></label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="male" {{ old('gender', $teacher->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $teacher->gender) == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="marital_status" name="marital_status" value="{{ old('marital_status', $teacher->marital_status) }}" required>
                </div>

                <div class="form-group">
                    <label>Assign Classes</label>
                    <div class="row">
                        @foreach ($classes as $class)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="class_ids[]"
                                        value="{{ $class->id }}"
                                        id="class_{{ $class->id }}"
                                        {{ (is_array(old('class_ids', $teacher->classes->pluck('id')->toArray())) && in_array($class->id, old('class_ids', $teacher->classes->pluck('id')->toArray()))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="class_{{ $class->id }}">
                                        {{ $class->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="address">Address </label>
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $teacher->address) }}">
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $teacher->date_of_birth) }}" required>
                </div>

                <div class="form-group">
                    <label for="admission_date">Admission Date</label>
                    <input type="date" class="form-control" id="admission_date" name="admission_date" value="{{ old('admission_date', $teacher->admission_date) }}">
                </div>

                <div class="form-group">
                    <label for="qualification">Qualification </label>
                    <input type="text" class="form-control" id="qualification" name="qualification" value="{{ old('qualification', $teacher->qualification) }}">
                </div>

                <div class="form-group">
                    <label for="salary">Salary </label>
                    <input type="number" class="form-control" id="salary" name="salary" value="{{ old('salary', $teacher->salary) }}">
                </div>

                <div class="form-group">
                    <label for="password">New Password (Leave blank if not changing)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password (Leave blank if not changing)</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Teacher</button>
    </form>
</div>
@endsection
