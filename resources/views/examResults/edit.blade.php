@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Exam Result</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('examResults.update', $examResult->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Pupil Name:</label>
            <input type="text" class="form-control" value="{{ $examResult->pupil->first_name }} {{ $examResult->pupil->last_name }}" disabled>
        </div>

        <div class="form-group">
            <label for="subject_id">Subject:</label>
            <select name="subject_id" id="subject_id" class="form-control" required>
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ $examResult->subject_id == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="term">Term:</label>
            <select name="term" id="term" class="form-control" required>
                <option value="">-- Select Term --</option>
                <option value="term 1" {{ $examResult->term == 'term 1' ? 'selected' : '' }}>Term 1 (First Term)</option>
                <option value="term 2" {{ $examResult->term == 'term 2' ? 'selected' : '' }}>Term 2 (Second Term)</option>
                <option value="term 3" {{ $examResult->term == 'term 3' ? 'selected' : '' }}>Term 3 (Third Term)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="mid_term_mark">Mid-Term Mark:</label>
            <input type="number" name="mid_term_mark" id="mid_term_mark" class="form-control" min="0" max="100" value="{{ $examResult->mid_term_mark }}" required>
        </div>

        <div class="form-group">
            <label for="end_of_term_mark">End-Term Mark:</label>
            <input type="number" name="end_of_term_mark" id="end_of_term_mark" class="form-control" min="0" max="100" value="{{ $examResult->end_of_term_mark }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Result</button>
        <a href="{{ route('examResults.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
