@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Income Summary</h2>
        <div>
            @if(auth()->user()->isPremium())
                <a href="{{ route('incomes.create') }}" class="btn btn-success me-2">+ Add Income</a>

                <a href="{{ route('incomes.report', request()->only(['term', 'year'])) }}"
                class="btn btn-primary me-2">Income Report</a>

                <a href="{{ route('financial.report', request()->only(['term', 'year'])) }}"
                class="btn btn-secondary">Financial Report</a>
            @endif

        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-auto">
                <select name="term" class="form-control">
                    <option value="">All Terms</option>
                    @foreach(['term 1','term 2','term 3'] as $t)
                        <option value="{{ $t }}" {{ $term == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-control">
                    <option value="">All Years</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Fee Income -->
    @if($feeIncomes->count())
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong>Fee-Based Income</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach($feeIncomes as $type => $total)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $type)) }}</td>
                                <td class="text-end fw-bold">K {{ number_format($total, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="table-active">
                            <td><strong>Total Fees</strong></td>
                            <td class="text-end"><strong>K {{ number_format($feeIncomes->sum(), 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Custom Incomes — PREMIUM ONLY -->
    @if(auth()->user()->isPremium())
        @if($customIncomes->count())
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <strong>Other Incomes (Premium)</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customIncomes as $inc)
                                <tr>
                                    <td>{{ $inc->date->format('d/m/Y') }}</td>
                                    <td>{{ $inc->source }}</td>
                                    <td>{{ Str::limit($inc->description, 40) }}</td>
                                    <td>K {{ number_format($inc->amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('incomes.edit', $inc) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('incomes.destroy', $inc) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
            <!-- Grand Total -->
            <div class="alert alert-dark text-center fs-3 fw-bold">
                TOTAL INCOME: K {{ number_format($grandTotal, 2) }}
            </div>
    @else
        {{-- <div class="alert alert-info text-center">
            <strong>Upgrade to Premium</strong> to:
            <ul class="mb-2">
                <li>Add custom income (donations, grants, etc.)</li>
                <li>Edit or delete income entries</li>
                <li>Full financial control</li>
            </ul>
            <a href="{{ route('subscription.upgrade') }}" class="btn btn-success">Upgrade Now</a>
        </div> --}}

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
</div>
@endsection
