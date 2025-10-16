@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Parents</h1>

    {{-- <a href="{{ route('parents.create') }}" class="btn btn-primary mb-3">Add New Parent</a> --}}

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parents as $parent)
                    <tr>
                        <td>{{ $parent->first_name }}</td>
                        <td>{{ $parent->last_name }}</td>
                        <td>{{ $parent->phone }}</td>
                        <td>{{ $parent->email }}</td>
                        <td>
                            <a href="{{ route('pupils.show', $parent->pupil_id) }}" class="btn btn-info btn-sm mb-1">Show Details</a>
                            <a href="{{ route('parents.edit', $parent->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
