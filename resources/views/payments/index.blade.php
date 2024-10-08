@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pupils Payments</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('payments.index') }}" method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="term">Term</label>
                <select name="term" id="term" class="form-control" onchange="this.form.submit()">
                    <option value="">Select Term</option>
                    <option value="Term 1" {{ request('term') == 'Term 1' ? 'selected' : '' }}>Term 1</option>
                    <option value="Term 2" {{ request('term') == 'Term 2 '? 'selected' : '' }}>Term 2</option>
                    <option value="Term 3" {{ request('term') == 'Term 3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year">Year</label>
                <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                    <option value="">Select Year</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <a href="{{ route('payments.select-pupil') }}" class="btn btn-primary mb-3">Add New Payment</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Pupil</th>
                <th>Type</th>
                <th>Total Amount</th>
                <th>Amount Paid</th>
                <th>Balance</th>
                <th>Term</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->pupil->first_name }} {{ $payment->pupil->last_name }}</td>
                    <td>{{ $payment->type }}</td>
                    <td>K {{ $payment->amount }}</td>
                    <td>K {{ $payment->amount_paid }}</td>
                    <td>K {{ $payment->balance }}</td>
                    <td>{{ $payment->term }}</td>
                    <td>
                        <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-info btn-sm">View</a>
                        @if ($payment->balance != 0 )
                        <a href="{{ route('payments.create-pay-balance', $payment->id) }}" class="btn btn-warning btn-sm">Pay Balance</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
