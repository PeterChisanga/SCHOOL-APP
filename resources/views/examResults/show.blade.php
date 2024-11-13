@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Exam Results for {{ $examResult->pupil->first_name }} {{ $examResult->pupil->last_name }}</h1>
        <div>
            <!-- Dropdown to export PDF based on term -->
            <div class="dropdown d-inline">
                <button class="btn btn-primary dropdown-toggle" type="button" id="exportPdfDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Print Results
                </button>
                <div class="dropdown-menu" aria-labelledby="exportPdfDropdown">
                    @foreach ($terms as $term)
                        <a class="dropdown-item" href="{{ route('examResults.exportPdf', ['pupil' => $examResult->pupil->id, 'term' => $term]) }}">
                            Export {{ $term }}
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('examResults.edit', $examResult->id) }}" class="btn btn-warning ml-2">Edit</a>
            <a href="{{ route('examResults.index') }}" class="btn btn-secondary ml-2">Back</a>
        </div>
    </div>

    @foreach ($terms as $term)
        <div class="card mb-4">
            <div class="card-header">
                <h5>{{ ucfirst($term) }} Results</h5>
            </div>
            <div class="card-body">
                @php
                    $resultsForTerm = $examResult->pupil->examResults->where('term', $term);
                @endphp

                @if ($resultsForTerm->isEmpty())
                    <p>No results available for {{ ucfirst($term) }}.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
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
                                @foreach ($resultsForTerm as $result)
                                    @php
                                        $average = ($result->mid_term_mark + $result->end_of_term_mark) / 2;
                                        $grade = match(true) {
                                            $average >= 75 => 'A',
                                            $average >= 60 => 'B',
                                            $average >= 50 => 'C',
                                            $average >= 45 => 'D',
                                            $average >= 40 => 'E',
                                            default => 'F'
                                        };
                                        $remark = match($grade) {
                                            'A' => 'Excellent',
                                            'B' => 'Very Good',
                                            'C' => 'Good',
                                            'D' => 'Satisfactory',
                                            'E' => 'Pass',
                                            'F' => 'Fail'
                                        };
                                    @endphp

                                    <tr>
                                        <td>{{ $result->subject->name }}</td>
                                        <td>{{ $result->mid_term_mark }}</td>
                                        <td>{{ $result->end_of_term_mark }}</td>
                                        <td>{{ number_format($average, 2) }}</td>
                                        <td>{{ $grade }}</td>
                                        <td>{{ $remark }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
