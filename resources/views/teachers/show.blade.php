@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Teacher Details</h3>
    <div class="row">
        <table class="table table-bordered">
            <tr>
                <th>First Name</th>
                <td>{{ $teacher->first_name }}</td>
            </tr>
            <tr>
                <th>Middle Name</th>
                <td>{{ $teacher->middle_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Last Name</th>
                <td>{{ $teacher->last_name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $teacher->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $teacher->phone }}</td>
            </tr>
            <tr>
                <th>Gender</th>
                <td>{{ $teacher->gender }}</td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td>{{ \Carbon\Carbon::parse($teacher->date_of_birth)->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>{{ $teacher->address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Marital Status</th>
                <td>{{ $teacher->marital_status }}</td>
            </tr>
            <tr>
                <th>Admission Date</th>
                <td>{{ $teacher->admission_date ? \Carbon\Carbon::parse($teacher->admission_date)->format('Y-m-d') : 'N/A' }}</td>
                {{-- <td>{{ $teacher->admission_date ? $teacher->admission_date->format('Y-m-d') : 'N/A' }}</td> --}}
            </tr>
            <tr>
                <th>Qualification</th>
                <td>{{ $teacher->qualification ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Salary</th>
                <td>{{ $teacher->salary ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>School</th>
                <td>{{ $teacher->school->name }}</td>
            </tr>
            <tr>
                <th>Classes</th>
                <td>
                    @if ($teacher->classes->count() > 0)
                        @foreach ($teacher->classes as $class)
                            <span class="badge badge-success">{{ $class->name }}</span>
                        @endforeach
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <a href="{{ route('teachers.index') }}" class="btn btn-primary mt-3">Back to Teachers List</a>
    <a href="{{ route('teachers.edit',$teacher->id) }}" class="btn btn-warning mt-3">Edit Teacher Information</a>
</div>
@endsection
