@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Secretaries</h2>

    <a href="{{ route('secretaries.create') }}" class="btn btn-primary mb-3">Add Secretary</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($secretaries as $secretary)
                    <tr>
                        <td>{{ $secretary->user->first_name }} {{ $secretary->user->last_name }}</td>
                        <td>{{ $secretary->user->email }}</td>
                        <td>{{ $secretary->user->phone_number }}</td>
                        <td>
                            <a href="{{ route('secretaries.show', $secretary->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                            <a href="{{ route('secretaries.edit', $secretary->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                            <form action="{{ route('secretaries.destroy', $secretary->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this secretary?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
