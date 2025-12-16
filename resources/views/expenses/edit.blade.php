@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Edit Expense</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('expenses.update', $expense->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" class="form-control" value="{{ old('description', $expense->description) }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" required>
            </div>

            <div class="form-group mb-3">
                <label>Term</label>
                <select name="term" class="form-control" required>
                    <option value="">Select Term</option>
                    <option value="term 1" {{ old('term', $expense->term ?? '') == 'term 1' ? 'selected' : '' }}>Term 1</option>
                    <option value="term 2" {{ old('term', $expense->term ?? '') == 'term 2' ? 'selected' : '' }}>Term 2</option>
                    <option value="term 3" {{ old('term', $expense->term ?? '') == 'term 3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $expense->date) }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Expense</button>
        </form>
    </div>
@endsection
