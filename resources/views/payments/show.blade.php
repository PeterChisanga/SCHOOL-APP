@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <h2>Payment Details for {{ $payment->pupil->first_name }} {{ $payment->pupil->last_name }}</h2>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4> {{ $payment->type }}</h4>
        <a href="{{ route('payments.export-pdf',$payment) }}" class="btn btn-primary btn-sm">Print Receipt</a>
    </div>

    <table class="table table-bordered">
        <tr>
            <th>Total Amount</th>
            <td>{{ $payment->amount }}</td>
        </tr>
        <tr>
            <th>Amount Paid</th>
            <td>{{ $payment->amount_paid }}</td>
        </tr>
        <tr>
            <th>Balance</th>
            <td>{{ $payment->balance }}</td>
        </tr>
        <tr>
            <th>Term</th>
            <td>{{ $payment->term }}</td>
        </tr>
    </table>

    <h3>Transactions</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Mode of Payment</th>
                <th>Deposit Slip ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payment->paymenttransactions as $transaction)
                <tr>
                    <td>{{ $transaction->date }}</td>
                    <td>{{ $transaction->amount }}</td>
                    <td>{{ $transaction->mode_of_payment }}</td>
                    <td>{{ $transaction->deposit_slip_id ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">Back</a>
    @if ($payment->balance != 0 )
    <a href="{{ route('payments.create-pay-balance', $payment->id) }}" class="btn btn-warning btn-sm">Pay Balance</a>
    @endif
</div>
@endsection
