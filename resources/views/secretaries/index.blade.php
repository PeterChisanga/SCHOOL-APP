@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Secretaries</h2>

    <a href="{{ route('secretaries.create') }}" class="btn btn-primary mb-3">Add Secretary</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($secretaries as $secretary)
                    <tr>
                        <td>{{ $secretary->user->first_name }} {{ $secretary->user->last_name }}</td>
                        <td>{{ $secretary->user->email }}</td>
                        <td>{{ $secretary->user->phone_number }}</td>
                        <td>
                            <a href="{{ route('secretaries.show', $secretary->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                            <a href="{{ route('secretaries.edit', $secretary->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                            <form action="{{ route('secretaries.destroy', $secretary->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this secretary?');">Delete</button>
                            </form>
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
