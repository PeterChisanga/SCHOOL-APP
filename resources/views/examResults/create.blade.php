@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Enter Exam Results</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('examResults.create') }}">
        <div class="form-group">
            <label for="class_id">Select Class:</label>
            <select name="class_id" id="class_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Select Class --</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    @if($pupils->isEmpty())
        <p>No pupils found for the selected class.</p>
    @else
    <form method="POST" action="{{ route('examResults.store') }}">
        @csrf
        <div class="form-group">
            <label for="subject_id">Select Subject:</label>
            <select name="subject_id" id="subject_id" class="form-control">
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="term">Term:</label>
            <select name="term" id="term" class="form-control" required>
                <option value="">-- Select Term --</option>
                <option value="term 1">Term 1 (First Term)</option>
                <option value="term 2">Term 2 (Second Term)</option>
                <option value="term 3">Term 3 (Third Term)</option>
            </select>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Pupil Name</th>
                    <th>Mid-Term Mark</th>
                    <th>End-Term Mark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pupils as $pupil)
                    <tr>
                        <td>{{ $pupil->first_name }} {{ $pupil->last_name }}</td>
                        <td><input type="number" name="pupil_results[{{ $pupil->id }}][mid_term_mark]" class="form-control" min="0" max="100" required></td>
                        <td><input type="number" name="pupil_results[{{ $pupil->id }}][end_of_term_mark]" class="form-control" min="0" max="100" required></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <a href="{{ route('examResults.index') }}" class="btn btn-secondary mt-2">Back</a>
    @endif

</div>
@endsection
