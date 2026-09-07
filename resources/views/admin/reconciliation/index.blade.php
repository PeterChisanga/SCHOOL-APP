@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Platform Reconciliation</h2>
    <p class="text-muted">Money collected through the platform's shared mobile money account, by school.</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>School</th>
                    <th>Collected via Platform <span class="badge badge-info">Gateway</span></th>
                    <th>Collected In-School <span class="badge badge-secondary">Manual</span></th>
                    <th>Paid Out to School</th>
                    <th>Held in Platform</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schools as $row)
                    <tr>
                        <td>{{ $row['school']->name }}</td>
                        <td>K {{ number_format($row['collected'], 2) }}</td>
                        <td>K {{ number_format($row['manual_collected'], 2) }}</td>
                        <td>K {{ number_format($row['paid_out'], 2) }}</td>
                        <td>
                            <strong class="{{ $row['held'] > 0 ? 'text-warning' : 'text-success' }}">
                                K {{ number_format($row['held'], 2) }}
                            </strong>
                        </td>
                        <td>
                            <a href="{{ route('admin.reconciliation.show', $row['school']->id) }}" class="btn btn-info btn-sm">View / Record Payout</a>
                            <a href="{{ route('schools.enter', $row['school']->id) }}" class="btn btn-success btn-sm">View Dashboard</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No schools on record yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
