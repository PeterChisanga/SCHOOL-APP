@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Fee Structure</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('fees.create') }}" class="btn btn-primary mb-3">Add Fee</a>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Term</th>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fees as $fee)
                    <tr>
                        <td>{{ $fee->class->name ?? 'All classes' }}</td>
                        <td>{{ $fee->term }}</td>
                        <td>{{ $fee->type }}</td>
                        <td>K {{ number_format($fee->amount, 2) }}</td>
                        <td>{{ $fee->is_active ? 'Yes' : 'No' }}</td>
                        <td>
                            <a href="{{ route('fees.edit', $fee) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                            <form action="{{ route('fees.destroy', $fee) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Delete this fee?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No fees set up yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-muted small">
        Fees defined here are added automatically as pending fees when a pupil is registered
        for the matching class and term.
    </p>
</div>
@endsection
