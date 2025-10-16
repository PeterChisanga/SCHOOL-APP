@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Class Details</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Class: {{ $class->name }}
        </div>
        <div class="card-body">
            <p><strong>Class ID:</strong> {{ $class->id }}</p>
            <p><strong>School ID:</strong> {{ $class->school_id }}</p>
        </div>
    </div>

    <h3>Pupils in {{ $class->name }}</h3>
    <a href="{{ route('classes.exportPdf', $class->id) }}" class="btn btn-primary mb-3">Print List of Pupils</a>

    @if ($students->isEmpty())
        <p>No pupils found in this class.</p>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td>{{ $student->gender }}</td>
                        <td>
                            <a href="{{ route('pupils.show', $student->id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('pupils.edit', $student->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-warning mt-3">Edit</a>
    <a href="{{ route('classes.index') }}" class="btn btn-secondary mt-3">Back to Classes</a>
</div>
@endsection
