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

    <form method="GET" action="{{ route('parents.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <label for="search" class="form-label">Search Parent</label>
                <input type="text" name="search" id="search" class="form-control"
                       value="{{ request('search') }}" placeholder="Name, phone or email...">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2">Search</button>
                <a href="{{ route('parents.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>

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
