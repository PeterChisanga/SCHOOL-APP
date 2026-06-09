@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h1 class="mb-4">Assessment Details</h1>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            {{ $assessment->title }}
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Pupil:</strong> {{ $assessment->pupil->first_name }} {{ $assessment->pupil->last_name }}</p>
                    <p><strong>Class:</strong> {{ $assessment->pupil->class->name ?? 'N/A' }}</p>
                    <p><strong>Subject:</strong> {{ $assessment->subject->name }}</p>
                    <p><strong>Term:</strong> {{ ucfirst($assessment->term) }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Date:</strong> {{ $assessment->assessment_date->format('d M Y') }}</p>
                    @if($assessment->raw_mark !== null)
                        <p><strong>Mark:</strong> {{ $assessment->raw_mark }} / {{ $assessment->max_mark }}</p>
                    @endif
                    <p><strong>Percentage:</strong> {{ number_format($assessment->percentage, 2) }}%</p>
                    <p><strong>Comments:</strong> {{ $assessment->comments ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('assessments.edit', $assessment->id) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('assessments.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
