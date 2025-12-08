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

        <!-- Form to generate PDF report -->
        <div class="mb-4">
            <form action="{{ route('expenses.exportReport') }}" method="POST">
                @csrf
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label for="term">Select Term</label>
                        <select name="term" id="term" class="form-control" required>
                            <option value="">-- Select Term --</option>
                            <option value="Term 1">Term 1</option>
                            <option value="Term 2">Term 2</option>
                            <option value="Term 3">Term 3</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="year">Select Year</label>
                        <select name="year" id="year" class="form-control" required>
                            <option value="">-- Select Year --</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-primary">Download Report</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Total Expenses Summary -->
        @if ($totals->isNotEmpty())
            <div class="table-responsive mb-4">
                <h3>Total Expenses by Term and Year</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Term 1</th>
                            <th>Term 2</th>
                            <th>Term 3</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($totals as $year => $termTotals)
                            <tr>
                                <td>{{ $year }}</td>
                                <td>{{ isset($termTotals['Term 1']) ? number_format($termTotals['Term 1'], 2) : '-' }}</td>
                                <td>{{ isset($termTotals['Term 2']) ? number_format($termTotals['Term 2'], 2) : '-' }}</td>
                                <td>{{ isset($termTotals['Term 3']) ? number_format($termTotals['Term 3'], 2) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($expenses->isEmpty())
            <p>No expenses found.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Term</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->description }}</td>
                                <td>{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                                <td>{{ $expense->term }}</td>
                                <td>
                                    <a href="{{ route('expenses.show', $expense->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
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
            </div>
        @endif
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
