@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Subjects</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <a href="{{ route('subjects.create') }}" class="btn btn-primary mb-3">Add New Subject</a>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                    <tr>
                        <td>{{ $subject->id }}</td>
                        <td>{{ $subject->name }}</td>
                        <td>
                            <a href="{{ route('subjects.show', $subject->id) }}" class="btn btn-info btn-sm mb-1">View</a>
                            <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                            <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No subjects found.</td>
                    </tr>
                @endforelse
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
