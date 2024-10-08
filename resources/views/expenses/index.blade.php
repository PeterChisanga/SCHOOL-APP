@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Expenses</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('expenses.create') }}" class="btn btn-primary mb-3">Add New Expense</a>

        @if ($expenses->isEmpty())
            <p>No expenses found.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->description }}</td>
                            <td>{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->date }}</td>
                            <td>
                                <a href="{{ route('expenses.show', $expense->id) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
