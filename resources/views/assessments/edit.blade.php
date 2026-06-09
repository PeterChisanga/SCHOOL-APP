@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Assessment</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('assessments.update', $assessment->id) }}">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Assessment Title:</label>
                <input type="text" name="title" class="form-control"
                    value="{{ old('title', $assessment->title) }}" required>
            </div>
            <div class="col-md-6">
                <label>Assessment Date:</label>
                <input type="date" name="assessment_date" class="form-control"
                    value="{{ old('assessment_date', $assessment->assessment_date->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Subject:</label>
                <select name="subject_id" class="form-control" required>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}"
                            {{ old('subject_id', $assessment->subject_id) == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label>Term:</label>
                <select name="term" class="form-control" required>
                    <option value="term 1" {{ old('term', $assessment->term) == 'term 1' ? 'selected' : '' }}>Term 1</option>
                    <option value="term 2" {{ old('term', $assessment->term) == 'term 2' ? 'selected' : '' }}>Term 2</option>
                    <option value="term 3" {{ old('term', $assessment->term) == 'term 3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Pupil:</label>
            <input type="text" class="form-control" disabled
                value="{{ $assessment->pupil->first_name }} {{ $assessment->pupil->last_name }}">
        </div>

        @if(Auth::user()->isPremium())
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Mark:</label>
                    <input type="number" name="raw_mark" id="raw_mark" class="form-control"
                        value="{{ old('raw_mark', $assessment->raw_mark) }}" min="0" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label>Out Of:</label>
                    <input type="number" name="max_mark" id="max_mark" class="form-control"
                        value="{{ old('max_mark', $assessment->max_mark) }}" min="1" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label>Percentage:</label>
                    <input type="text" id="percentage_display" class="form-control" disabled
                        value="{{ number_format($assessment->percentage, 2) }}%">
                </div>
            </div>
        @else
            <div class="mb-3">
                <label>Percentage (%):</label>
                <input type="number" name="percentage" class="form-control"
                    value="{{ old('percentage', $assessment->percentage) }}"
                    min="0" max="100" step="0.01" required>
            </div>
        @endif

        <div class="mb-3">
            <label>Comments:</label>
            <select name="comments" class="form-control">
                <option value="" disabled>Select Comment</option>
                @foreach(['Excellent performance', 'Very good, keep it up', 'Good effort', 'Needs improvement', 'Poor performance', 'Absent', 'Sick'] as $comment)
                    <option value="{{ $comment }}" {{ old('comments', $assessment->comments) == $comment ? 'selected' : '' }}>
                        {{ $comment }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Assessment</button>
        <a href="{{ route('assessments.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>

@if(Auth::user()->isPremium())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rawInput = document.getElementById('raw_mark');
        const maxInput = document.getElementById('max_mark');
        const display  = document.getElementById('percentage_display');

        function update() {
            const raw = parseFloat(rawInput.value) || 0;
            const max = parseFloat(maxInput.value) || 1;
            display.value = Math.min((raw / max) * 100, 100).toFixed(2) + '%';
        }

        rawInput.addEventListener('input', update);
        maxInput.addEventListener('input', update);
    });
</script>
@endif
@endsection
