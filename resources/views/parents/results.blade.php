@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">

        <!-- Title - Smaller on mobile -->
        <h1 class="h4 h3-sm mb-0 text-wrap">
            Exam Results for {{ $pupil->first_name }} {{ $pupil->last_name }}
        </h1>

        <!-- Print Button -->
        <div class="dropdown d-inline">
            <button class="btn btn-primary dropdown-toggle" type="button" id="exportPdfDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Print Results
            </button>
            <div class="dropdown-menu" aria-labelledby="exportPdfDropdown">
                @foreach ($terms as $term)
                    <a class="dropdown-item" href="{{ route('examResults.exportPdf', ['pupil' => $pupil->id, 'term' => $term]) }}">
                        Export {{ $term }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @foreach ($terms as $term)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    {{ ucfirst($term) }} Results
                    @if($pupil->school->is_premium)
                        <span class="badge bg-info ms-2">Position: {{ $positions[$term] ?? '-' }}</span>
                    @endif
                </h5>
            </div>

            <div class="card-body">
                @php
                    $resultsForTerm = $pupil->examResults->where('term', $term);
                @endphp

                @if ($resultsForTerm->isEmpty())
                    <p class="text-muted">No results available for {{ ucfirst($term) }}.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                @if($pupil->school->is_premium)
                                    <tr>
                                        <th rowspan="2">Subject</th>
                                        <th colspan="3" class="text-center">Mid-Term</th>
                                        <th colspan="3" class="text-center">End of Term</th>
                                        <th rowspan="2">Average</th>
                                        <th rowspan="2">Grade</th>
                                        <th rowspan="2">Remark</th>
                                        <th rowspan="2">Comments</th>
                                    </tr>
                                    <tr>
                                        <th>Mark</th>
                                        <th>Total Mark</th>
                                        <th>%</th>
                                        <th>Mark</th>
                                        <th>Total Mark</th>
                                        <th>%</th>
                                    </tr>
                                @else
                                    <tr>
                                        <th>Subject</th>
                                        <th>Mid Term</th>
                                        <th>End Term</th>
                                        <th>Average</th>
                                        <th>Grade</th>
                                        <th>Remark</th>
                                        <th>Comments</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @foreach ($resultsForTerm as $result)
                                    @php
                                        $average = ($result->mid_term_mark !== null && $result->end_of_term_mark !== null)
                                            ? ($result->mid_term_mark + $result->end_of_term_mark) / 2
                                            : null;

                                        $grade = $average !== null ? match(true) {
                                            $average >= 75 => 'A',
                                            $average >= 60 => 'B',
                                            $average >= 50 => 'C',
                                            $average >= 45 => 'D',
                                            $average >= 40 => 'E',
                                            default => 'F'
                                        } : '-';

                                        $remark = match($grade) {
                                            'A' => 'Excellent',
                                            'B' => 'Very Good',
                                            'C' => 'Good',
                                            'D' => 'Satisfactory',
                                            'E' => 'Pass',
                                            default => 'Fail'
                                        };
                                    @endphp

                                    <tr>
                                        <td>{{ $result->subject->name }}</td>
                                        @if($pupil->school->is_premium)
                                            <td>{{ $result->mid_term_raw ?? '-' }}</td>
                                            <td>{{ $result->mid_term_max ?? '-' }}</td>
                                            <td>{{ $result->mid_term_mark !== null ? number_format($result->mid_term_mark, 2).'%' : '-' }}</td>
                                            <td>{{ $result->end_term_raw ?? '-' }}</td>
                                            <td>{{ $result->end_term_max ?? '-' }}</td>
                                            <td>{{ $result->end_of_term_mark !== null ? number_format($result->end_of_term_mark, 2).'%' : '-' }}</td>
                                        @else
                                            <td>{{ $result->mid_term_mark ?? '-' }}</td>
                                            <td>{{ $result->end_of_term_mark ?? '-' }}</td>
                                        @endif
                                        <td>{{ $average !== null ? number_format($average, 2).'%' : '-' }}</td>
                                        <td>{{ $grade }}</td>
                                        <td>{{ $remark }}</td>
                                        <td>{{ $result->comments ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@endsection
