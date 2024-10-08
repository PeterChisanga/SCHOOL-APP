@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Add New Expense</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" class="form-control" value="{{ old('description') }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ old('date') }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Expense</button>
        </form>
    </div>
@endsection
