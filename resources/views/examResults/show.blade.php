@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Exam Results for {{ $examResult->pupil->first_name }} {{ $examResult->pupil->last_name }}</h1>
        <div>
            <div class="dropdown d-inline">
                <button class="btn btn-primary dropdown-toggle" type="button" id="exportPdfDropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Print Results
                </button>
                <div class="dropdown-menu" aria-labelledby="exportPdfDropdown">
                    @foreach ($terms as $term)
                        <a class="dropdown-item"
                            href="{{ route('examResults.exportPdf', ['pupil' => $examResult->pupil->id, 'term' => $term]) }}">
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
                    @if(Auth::user()->school->is_premium)
                        | Position in Class: {{ $positions[$term] ?? '-' }}
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @php
                    $resultsForTerm       = $examResult->pupil->examResults->where('term', $term);
                    $assessmentsForTerm   = $assessmentsByTerm[$term] ?? collect();
                    $assessmentsBySubject = $assessmentsForTerm->groupBy('subject_id');

                    // ── Build CA column headers across ALL subjects for this term ──
                    // Mid-term is always CA 1, then remaining CAs follow
                    // We need a unified list of CA labels for the header

                    // Collect all unique CA titles across subjects (excluding mid-term)
                    $allCaTitles = $assessmentsForTerm->pluck('title')->unique()->values();

                    // Total CA columns = 1 (mid-term) + number of unique assessment titles
                    $totalCaColumns = 1 + $allCaTitles->count();
                @endphp

                @if($resultsForTerm->isEmpty())
                    <p>No results available for {{ ucfirst($term) }}.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                {{-- Row 1: Group headers --}}
                                <tr>
                                    <th rowspan="2">Subject</th>
                                    <th colspan="{{ $totalCaColumns }}" class="text-center">
                                        Continuous Assessment
                                        <br><small class="text-warning fw-normal">(40%)</small>
                                    </th>
                                    <th rowspan="2" class="text-center">
                                        Total CA
                                        <br><small class="text-warning fw-normal">(40%)</small>
                                    </th>
                                    <th rowspan="2" class="text-center">
                                        Final Exam
                                        <br><small class="text-warning fw-normal">(60%)</small>
                                    </th>
                                    <th rowspan="2" class="text-center">Final Mark</th>
                                    <th rowspan="2" class="text-center">Grade</th>
                                    <th rowspan="2" class="text-center">Remark</th>
                                </tr>
                                {{-- Row 2: Individual CA column names --}}
                                <tr>
                                    <th class="text-center">Mid-Term Test</th>
                                    @foreach($allCaTitles as $caTitle)
                                        <th class="text-center">{{ $caTitle }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resultsForTerm as $result)
                                    @php
                                        // ── Build CA entries for this subject ──
                                        $subjectAssessments = $assessmentsBySubject[$result->subject_id] ?? collect();

                                        // Index assessments by title for column alignment
                                        $assessmentByTitle = $subjectAssessments->keyBy('title');

                                        // Mid-term percentage
                                        $midTermPct = $result->mid_term_mark;

                                        // Collect all CA percentages for average
                                        $caPercentages = collect();
                                        if ($midTermPct !== null) {
                                            $caPercentages->push($midTermPct);
                                        }
                                        foreach ($allCaTitles as $caTitle) {
                                            $ca = $assessmentByTitle[$caTitle] ?? null;
                                            if ($ca) {
                                                $caPercentages->push($ca->percentage);
                                            }
                                        }

                                        // ── CA Average & Weighted (40%) ──
                                        $caAverage  = $caPercentages->isNotEmpty()
                                            ? $caPercentages->avg()
                                            : null;
                                        $caWeighted = $caAverage !== null ? ($caAverage * 0.40) : null;

                                        // ── Final Exam & Weighted (60%) ──
                                        $examMark     = $result->end_of_term_mark;
                                        $examWeighted = $examMark !== null ? ($examMark * 0.60) : null;

                                        // ── Final Mark ──
                                        $finalMark = ($caWeighted !== null && $examWeighted !== null)
                                            ? $caWeighted + $examWeighted
                                            : null;

                                        // ── Grade & Remark ──
                                        $grade  = '-';
                                        $remark = '-';
                                        if ($finalMark !== null) {
                                            $grade = match(true) {
                                                $finalMark >= 75 => 'A',
                                                $finalMark >= 60 => 'B',
                                                $finalMark >= 50 => 'C',
                                                $finalMark >= 45 => 'D',
                                                $finalMark >= 40 => 'E',
                                                default          => 'F'
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
                                        {{-- Subject --}}
                                        <td class="fw-bold">{{ $result->subject->name }}</td>

                                        {{-- Mid-Term CA column --}}
                                        <td class="text-center">
                                            @if($midTermPct !== null)
                                                @if(auth()->user()->isPremium() && $result->mid_term_raw !== null)
                                                    {{ $result->mid_term_raw }}/{{ $result->mid_term_max }}
                                                    <br><small>({{ number_format($midTermPct, 1) }}%)</small>
                                                @else
                                                    {{ number_format($midTermPct, 1) }}%
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Other CA columns aligned by title --}}
                                        @foreach($allCaTitles as $caTitle)
                                            @php
                                                $ca = $assessmentByTitle[$caTitle] ?? null;
                                            @endphp
                                            <td class="text-center">
                                                @if($ca)
                                                    @if($ca->raw_mark !== null)
                                                        {{ $ca->raw_mark }}/{{ $ca->max_mark }}
                                                        <br><small>({{ number_format($ca->percentage, 1) }}%)</small>
                                                    @else
                                                        {{ number_format($ca->percentage, 1) }}%
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Total CA (average of all CAs) --}}
                                        <td class="text-center">
                                            <strong>
                                                {{ $caAverage !== null ? number_format($caAverage, 1).'%' : '—' }}
                                            </strong>
                                        </td>

                                        {{-- Final Exam --}}
                                        <td class="text-center">
                                            @if($examMark !== null)
                                                @if(auth()->user()->isPremium() && $result->end_term_raw !== null)
                                                    {{ $result->end_term_raw }}/{{ $result->end_term_max }}
                                                    <br><small>({{ number_format($examMark, 1) }}%)</small>
                                                @else
                                                    {{ number_format($examMark, 1) }}%
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Final Mark --}}
                                        <td class="text-center">
                                            <strong>
                                                {{ $finalMark !== null ? number_format($finalMark, 1).'%' : '—' }}
                                            </strong>
                                        </td>

                                        {{-- Grade --}}
                                        <td class="text-center">
                                            <span class="badge
                                                @if($grade == 'A') badge-success
                                                @elseif($grade == 'B') badge-primary
                                                @elseif($grade == 'C') badge-info
                                                @elseif($grade == 'D' || $grade == 'E') badge-warning
                                                @else badge-danger
                                                @endif">
                                                {{ $grade }}
                                            </span>
                                        </td>

                                        {{-- Remark --}}
                                        <td>{{ $remark }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Weight legend --}}
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Final Mark = Average of all CAs × 40% + Final Exam × 60%
                        </small>
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

