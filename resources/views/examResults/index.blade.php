@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Exam Results</h1>
        <a href="{{ route('examResults.create') }}" class="btn btn-primary">Enter New Results</a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('examResults.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
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
            <div class="col-md-4">
                <label for="subject_id" class="form-label">Filter by Subject</label>
                <select name="subject_id" id="subject_id" class="form-control" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>
    </form>

    <!-- Results Table -->
    @if($examResults->isEmpty())
        <div class="alert alert-warning">
            No exam results found for the selected criteria.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Pupil Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Mid-Term Mark</th>
                        <th>End-Term Mark</th>
                        <th>Average</th>
                        <th>Grade</th>
                        <th>Grade Remark</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($examResults as $result)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $result->pupil->first_name }} {{ $result->pupil->last_name }}</td>
                            <td>{{ $result->pupil->class->name }}</td>
                            <td>{{ $result->subject->name }}</td>
                            <td>{{ $result->mid_term_mark }} %</td>
                            <td>{{ $result->end_of_term_mark }} %</td>
                            @php
                                $average = ($result->mid_term_mark + $result->end_of_term_mark) / 2;

                                if ($average >= 75) {
                                    $grade = 'A';
                                    $remark = 'Excellent';
                                } elseif ($average >= 60) {
                                    $grade = 'B';
                                    $remark = 'Very Good';
                                } elseif ($average >= 50) {
                                    $grade = 'C';
                                    $remark = 'Good';
                                } elseif ($average >= 45) {
                                    $grade = 'D';
                                    $remark = 'Satisfactory';
                                } elseif ($average >= 40) {
                                    $grade = 'E';
                                    $remark = 'Pass';
                                } else {
                                    $grade = 'F';
                                    $remark = 'Fail';
                                }
                            @endphp

                            <td>{{ $average }} %</td>
                            <td>{{ $grade }}</td>
                            <td>{{ $remark }}</td>
                            <td>
                                <a href="{{ route('examResults.show', $result->id) }}" class="btn btn-sm btn-primary mb-1">View</a>
                                <a href="{{ route('examResults.edit', $result->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
