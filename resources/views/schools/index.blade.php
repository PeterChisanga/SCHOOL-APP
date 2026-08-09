@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Schools</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Premium</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schools as $school)
                    <tr>
                        <td>{{ $school->name }}</td>
                        <td>{{ $school->address }}</td>
                        <td>{{ $school->phone ?? '—' }}</td>
                        <td>{{ $school->is_premium ? 'Yes' : 'No' }}</td>
                        <td>
                            <a href="{{ route('schools.enter', $school->id) }}" class="btn btn-success btn-sm">View Dashboard</a>
                            <a href="{{ route('admin.reconciliation.show', $school->id) }}" class="btn btn-primary btn-sm">Reconciliation</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No schools on record yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
