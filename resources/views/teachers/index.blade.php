@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Teachers List</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('teachers.create') }}" class="btn btn-primary">Add New Teacher</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Qualification</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teachers as $teacher)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $teacher->first_name }}</td>
                        <td>{{ $teacher->last_name }}</td>
                        <td>{{ $teacher->email }}</td>
                        <td>{{ $teacher->phone }}</td>
                        <td>{{ ucfirst($teacher->gender) }}</td>
                        <td>{{ \Carbon\Carbon::parse($teacher->date_of_birth)->format('Y-m-d') }}</td>
                        <td>{{ $teacher->qualification ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('teachers.show', $teacher->id) }}" class="btn btn-info btn-sm mb-2">View</a>
                            <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-warning btn-sm mb-2">Edit</a>
                            <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this teacher?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
