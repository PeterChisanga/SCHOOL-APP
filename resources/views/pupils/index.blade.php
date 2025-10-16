@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h1 class="mb-4">Pupil List</h1>

    <form method="GET" action="{{ route('pupils.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <label for="class_id" class="form-label">Filter by Class</label>
                <select name="class_id" id="class_id" class="form-control" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <a href="{{ route('pupils.create') }}" class="btn btn-primary mb-3">Add New Pupil</a>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Date of Admission</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pupils as $pupil)
                    <tr>
                        <td>{{ $pupil->first_name }}</td>
                        <td>{{ $pupil->last_name }}</td>
                        <td>{{ $pupil->class->name ?? 'N/A' }}</td>
                        <td>{{ $pupil->gender }}</td>
                        <td>{{ $pupil->date_of_birth->format('Y-m-d') }}</td>
                        <td>{{ $pupil->admission_date ? $pupil->admission_date->format('Y-m-d') : '-' }}</td>
                        <td>
                            <a href="{{ route('pupils.show', $pupil->id) }}" class="btn btn-sm btn-info mb-2">View Details</a>

                            <a href="{{ route('pupils.edit', $pupil->id) }}" class="btn btn-sm btn-warning mb-2">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No pupils found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
