@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Expense Details</h2>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Description</h5>
                <p class="card-text">{{ $expense->description }}</p>

                <h5 class="card-title">Amount</h5>
                <p class="card-text">{{ number_format($expense->amount, 2) }}</p>

                <h5 class="card-title">Date</h5>
                <p class="card-text">{{ $expense->date }}</p>

                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Back to Expenses</a>
            </div>
        </div>
    </div>
@endsection
