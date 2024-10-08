@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Exam Results for {{ $examResult->pupil->first_name }} {{ $examResult->pupil->last_name }}</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Mid Term Mark</th>
                <th>End of Term Mark</th>
                <th>Average</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($examResult->pupil->examResults as $result)
                @php
                    $average = ($result->mid_term_mark + $result->end_of_term_mark) / 2;

                    // Determine the grade and remark based on the average
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

                <tr>
                    <td>{{ $result->subject->name }}</td>
                    <td>{{ $result->mid_term_mark }}</td>
                    <td>{{ $result->end_of_term_mark }}</td>
                    <td>{{ $average }}</td>
                    <td>{{ $grade }}</td>
                    <td>{{ $remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Button to Export to PDF -->
    <a href="{{ route('examResults.exportPdf', $examResult->pupil->id) }}" class="btn btn-primary">Export to PDF</a>
</div>
@endsection
