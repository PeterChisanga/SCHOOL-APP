@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Platform Reconciliation</h2>
    <p class="text-muted">Fees your parents have paid through the platform's mobile money account, and what the platform has paid out to you so far.</p>

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
                    <h6 class="text-muted">Already Paid Out to You</h6>
                    <h4>K {{ number_format($paidOut, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Still Held by Platform</h6>
                    <h4 class="{{ $held > 0 ? 'text-warning' : 'text-success' }}">K {{ number_format($held, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <p class="text-muted"><small>Only gateway (mobile money) payments pass through the platform's account and count toward what's held/paid out — manual, in-school payments go straight to you and are shown here for visibility only.</small></p>

    <h4>Payouts Received</h4>
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payouts as $payout)
                    <tr>
                        <td>{{ $payout->paid_at->format('Y-m-d') }}</td>
                        <td>K {{ number_format($payout->amount, 2) }}</td>
                        <td>{{ $payout->reference ?? '—' }}</td>
                        <td>{{ $payout->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">The platform hasn't recorded any payouts to your school yet.</td>
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
</div>
@endsection
