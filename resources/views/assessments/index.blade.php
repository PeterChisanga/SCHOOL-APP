@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h1 class="mb-4">Pupil Assessments</h1>

    {{-- Filters --}}
    <form method="GET" action="{{ route('assessments.index') }}" class="mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="class_id" class="form-control" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="subject_id" class="form-control" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="term" class="form-control" onchange="this.form.submit()">
                    <option value="">All Terms</option>
                    <option value="term 1" {{ request('term') == 'term 1' ? 'selected' : '' }}>Term 1</option>
                    <option value="term 2" {{ request('term') == 'term 2' ? 'selected' : '' }}>Term 2</option>
                    <option value="term 3" {{ request('term') == 'term 3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
            <div class="col-md-3">
                <a href="{{ route('assessments.index') }}" class="btn btn-secondary w-100">Clear Filters</a>
            </div>
        </div>
    </form>

    <a href="{{ route('assessments.create') }}" class="btn btn-primary mb-3">Enter Assessment Results</a>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Pupil</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Term</th>
                    <th>Date</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $assessment)
                    <tr>
                        <td>{{ $assessment->title }}</td>
                        <td>{{ $assessment->pupil->first_name }} {{ $assessment->pupil->last_name }}</td>
                        <td>{{ $assessment->pupil->class->name ?? 'N/A' }}</td>
                        <td>{{ $assessment->subject->name }}</td>
                        <td>{{ ucfirst($assessment->term) }}</td>
                        <td>{{ $assessment->assessment_date->format('Y-m-d') }}</td>
                        <td>
                            @if($assessment->raw_mark !== null)
                                {{ $assessment->raw_mark }}/{{ $assessment->max_mark }}
                                <small class="text-muted">({{ number_format($assessment->percentage, 1) }}%)</small>
                            @else
                                {{ number_format($assessment->percentage, 1) }}%
                            @endif
                        </td>
                        <td>
                            {{-- <a href="{{ route('assessments.show', $assessment->id) }}" class="btn btn-sm btn-info mb-1">View</a> --}}
                            <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>
                            <form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this assessment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No assessments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
