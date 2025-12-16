@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <h1 class="mb-4">Pupil List</h1>

    <form method="GET" action="{{ route('pupils.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <label for="class_id" class="form-label">Filter by Class</label>
                <select name="class_id" id="class_id" class="form-control" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <a href="{{ route('pupils.create') }}" class="btn btn-primary mb-3">Add New Pupil</a>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Date of Admission</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pupils as $pupil)
                    <tr>
                        <td>{{ $pupil->first_name }}</td>
                        <td>{{ $pupil->last_name }}</td>
                        <td>{{ $pupil->class->name ?? 'N/A' }}</td>
                        <td>{{ $pupil->gender }}</td>
                        <td>{{ $pupil->date_of_birth->format('Y-m-d') }}</td>
                        <td>{{ $pupil->admission_date ? $pupil->admission_date->format('Y-m-d') : '-' }}</td>
                        <td>
                            <a href="{{ route('pupils.show', $pupil->id) }}" class="btn btn-sm btn-info mb-2">View Details</a>

                            <a href="{{ route('pupils.edit', $pupil->id) }}" class="btn btn-sm btn-warning mb-2">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No pupils found</td>
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
