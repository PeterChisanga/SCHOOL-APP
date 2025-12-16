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

            <a href="{{ route('examResults.index') }}" class="btn btn-secondary ml-2">Back</a>
        </div>
    </div>

    @foreach ($terms as $term)
        <div class="card mb-4">
            <div class="card-header">
                <h5>
                    {{ ucfirst($term) }} Results
                    {{-- Position in class only for premium --}}
                    @if(Auth::user()->school->is_premium)
                        | Position in Class: {{ $positions[$term] ?? '-' }}
                    @endif
                </h5>
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
                                @if(auth()->user()->isPremium())
                                    {{-- Premium: Includes all freemium features + raw marks + percentages + ranking --}}
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
                                        <th>Percentage</th>
                                        <th>Mark</th>
                                        <th>Total Mark</th>
                                        <th>Percentage</th>
                                    </tr>
                                @else
                                    {{-- Free: Only basic results --}}
                                    <tr>
                                        <th>Subject</th>
                                        <th>Mid Term Mark</th>
                                        <th>End of Term Mark</th>
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

                                        $grade = '-';
                                        $remark = '-';

                                        if ($average !== null) {
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
                                        }
                                    @endphp

                                    <tr>
                                        <td>{{ $result->subject->name }}</td>
                                        @if(auth()->user()->isPremium())
                                            {{-- Premium version: raw, max, percentages + comments --}}
                                            <td>{{ $result->mid_term_raw ?? '-' }}</td>
                                            <td>{{ $result->mid_term_max ?? '-' }}</td>
                                            <td>{{ $result->mid_term_mark !== null ? number_format($result->mid_term_mark, 2).'%' : '-' }}</td>
                                            <td>{{ $result->end_term_raw ?? '-' }}</td>
                                            <td>{{ $result->end_term_max ?? '-' }}</td>
                                            <td>{{ $result->end_of_term_mark !== null ? number_format($result->end_of_term_mark, 2).'%' : '-' }}</td>
                                            <td>{{ $average !== null ? number_format($average, 2).'%' : '-' }}</td>
                                            <td>{{ $grade }}</td>
                                            <td>{{ $remark }}</td>
                                            <td>{{ $result->comments ?? '-' }}</td>
                                        @else
                                            {{-- Free version: basic marks + comments --}}
                                            <td>{{ $result->mid_term_mark !== null ? $result->mid_term_mark : '-' }}</td>
                                            <td>{{ $result->end_of_term_mark !== null ? $result->end_of_term_mark : '-' }}</td>
                                            <td>{{ $average !== null ? number_format($average, 2) : '-' }}</td>
                                            <td>{{ $grade }}</td>
                                            <td>{{ $remark }}</td>
                                            <td>{{ $result->comments ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    {{-- Upgrade call-to-action for free users --}}
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
</div>
@endsection

