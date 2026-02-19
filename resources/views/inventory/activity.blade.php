@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">
        Inventory Activity Log - {{ $inventory->item_name }}
    </h2>

    <div class="card mb-3">
        <div class="card-body">
            <strong>Current Stock:</strong>
            <span class="badge bg-primary fs-6">
                {{ $inventory->quantity }}
            </span>
        </div>
    </div>

    @if($logs->isEmpty())
        <p>No activity recorded for this item.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Change</th>
                        <th>Old Qty</th>
                        <th>New Qty</th>
                        <th>Note</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>

                            <td>
                                @if($log->change_amount > 0)
                                    <span class="badge bg-success">
                                        {{ ucfirst($log->action_type) }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        {{ ucfirst($log->action_type) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($log->change_amount > 0)
                                    +{{ $log->change_amount }}
                                @else
                                    {{ $log->change_amount }}
                                @endif
                            </td>
                            <td>{{ $log->old_quantity }}</td>
                            <td>{{ $log->new_quantity }}</td>
                            <td>{{ $log->note }}</td>
                            <td>{{ $log->user->first_name ?? 'System' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
