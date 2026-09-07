@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Reconciliation — {{ $school->name }}
        <a href="{{ route('schools.enter', $school->id) }}" class="btn btn-success btn-sm">View Dashboard</a>
    </h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Collected via Platform <span class="badge badge-info">Gateway</span></h6>
                    <h4>K {{ number_format($collected, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Collected In-School <span class="badge badge-secondary">Manual</span></h6>
                    <h4>K {{ number_format($manualCollected, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Paid Out to School</h6>
                    <h4>K {{ number_format($paidOut, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Held in Platform</h6>
                    <h4 class="{{ $held > 0 ? 'text-warning' : 'text-success' }}">K {{ number_format($held, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <p class="text-muted"><small>Only gateway (mobile money) payments pass through the platform's account — manual, in-school payments are shown here for visibility only and are excluded from the payout figures.</small></p>

    <h4>Record a Payout</h4>
    <form action="{{ route('admin.reconciliation.payout.store', $school->id) }}" method="POST" class="mb-4">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="amount">Amount (K)</label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
            </div>
            <div class="form-group col-md-3">
                <label for="paid_at">Date paid</label>
                <input type="date" class="form-control" id="paid_at" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" required>
            </div>
            <div class="form-group col-md-3">
                <label for="reference">Reference (optional)</label>
                <input type="text" class="form-control" id="reference" name="reference" value="{{ old('reference') }}">
            </div>
            <div class="form-group col-md-3">
                <label for="notes">Notes (optional)</label>
                <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes') }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Record Payout</button>
    </form>

    <h4>Payout History</h4>
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Notes</th>
                    <th>Recorded by</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payouts as $payout)
                    <tr>
                        <td>{{ $payout->paid_at->format('Y-m-d') }}</td>
                        <td>K {{ number_format($payout->amount, 2) }}</td>
                        <td>{{ $payout->reference ?? '—' }}</td>
                        <td>{{ $payout->notes ?? '—' }}</td>
                        <td>{{ $payout->paidBy->first_name ?? '' }} {{ $payout->paidBy->last_name ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No payouts recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h4>All Payment Transactions</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Pupil</th>
                    <th>Amount</th>
                    <th>Receipt</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->date }}</td>
                        <td>{{ $transaction->payment->pupil->first_name ?? '' }} {{ $transaction->payment->pupil->last_name ?? '' }}</td>
                        <td>K {{ number_format($transaction->amount, 2) }}</td>
                        <td>{{ $transaction->receipt_number ?? '—' }}</td>
                        <td><span class="badge {{ $transaction->source_badge_class }}">{{ $transaction->source_label }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No payments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('admin.reconciliation.index') }}" class="btn btn-secondary btn-sm">Back to all schools</a>
</div>
@endsection
