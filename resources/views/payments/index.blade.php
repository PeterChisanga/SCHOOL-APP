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
                    <option value="">All</option>
                    <option value="Term 1" {{ request('term') == 'Term 1' ? 'selected' : '' }}>Term 1</option>
                    <option value="Term 2" {{ request('term') == 'Term 2'? 'selected' : '' }}>Term 2</option>
                    <option value="Term 3" {{ request('term') == 'Term 3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="year">Year</label>
                <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                    <option value="">All</option>
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

    <div class="table-responsive">
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
                            <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                            @if ($payment->balance != 0 )
                            <a href="{{ route('payments.create-pay-balance', $payment->id) }}" class="btn btn-warning btn-sm">Pay Balance</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@if(!auth()->user()->isPremium())
    <div class="my-4">
        <div class="p-4 p-md-5 rounded-4 shadow-lg border border-3 bg-gradient"
            style="background: linear-gradient(135deg, #007bff, #6610f2); color:white; animation: glow 6s infinite alternate;">

            <h3 class="text-center fw-bold mb-3">
                <i class="fas fa-crown text-warning"></i> Unlock Full Power — Upgrade to Premium
            </h3>

            <p class="text-center fs-5">
                Get instant access to advanced financial tools, academic automation, and professional school management features.
            </p>

            <ul class="list-unstyled fs-5 mt-4">
                <li class="mb-2">
                    <i class="fas fa-check-circle text-warning me-2"></i>
                    Add unlimited <strong>custom incomes</strong> (donations, grants, PTA funds, etc.)
                </li>
                <li class="mb-2">
                    <i class="fas fa-check-circle text-warning me-2"></i>
                    Sending Results to Parents  <strong>via WhatsApp & SMS</strong>
                </li>
                <li class="mb-2">
                    <i class="fas fa-check-circle text-warning me-2"></i>
                    <strong>School Inventory Management </strong>
                </li>
                <li class="mb-2">
                    <i class="fas fa-check-circle text-warning me-2"></i>
                    Access complete <strong>Financial intelligence</strong> — Expense Reports, Income Reports, Summaries, trends & analysis
                </li>
                <li class="mb-2">
                    <i class="fas fa-check-circle text-warning me-2"></i>
                    Unlock all <strong>Premium academic features</strong> (ranking, raw marks, percentages, and more)
                </li>
            </ul>

            <div class="text-center mt-4">
                <a href="{{ route('subscription.upgrade') }}"
                class="btn btn-warning btn-lg px-5 shadow-lg fw-bold" style="font-size: 1.3rem;">
                    Upgrade Now for Full Access
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes glow {
            from { box-shadow: 0 0 15px rgba(255,255,255,0.1); }
            to { box-shadow: 0 0 35px rgba(255,255,255,0.4); }
        }
    </style>
@endif
@endsection
