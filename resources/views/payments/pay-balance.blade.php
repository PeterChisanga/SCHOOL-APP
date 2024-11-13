@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Record Payment of the balance for {{ $payment->pupil->first_name }} {{ $payment->pupil->last_name }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payments.pay-balance', $payment) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="amount_paid">Amount Paid</label>
            <input type="number" class="form-control" id="amount_paid" name="amount_paid" value="{{ old('amount_paid') }}" required>
        </div>

        <div class="form-group">
            <label for="mode_of_payment">Mode of Payment</label>
            <select name="mode_of_payment" class="form-control" required>
                <option value="cash" {{ old('mode_of_payment') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="deposit" {{ old('mode_of_payment') == 'deposit' ? 'selected' : '' }}>Deposit</option>
            </select>
        </div>

        <div class="form-group">
            <label for="date">Payment Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ old('date') }}" required>
        </div>

        <div class="form-group">
            <label for="deposit_slip_id">Deposit Slip ID (optional)</label>
            <input type="text" class="form-control" id="deposit_slip_id" name="deposit_slip_id" value="{{ old('deposit_slip_id') }}">
        </div>

        <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-secondary btn-sm">Back</a>
        <button type="submit" class="btn btn-primary">Record Payment of the balance</button>
    </form>
</div>
@endsection
