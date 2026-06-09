@extends('layouts.app')

@section('content')
<div class="container">

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <script>
            (function(){
                const el = document.getElementById('success-alert');
                if (!el) return;
                setTimeout(function(){
                    el.classList.remove('show');
                    setTimeout(() => el.remove(), 300);
                }, 4000);   // Auto dismiss after 4 seconds
            })();
        </script>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Exam Results</h1>
        <a href="{{ route('examResults.create') }}" class="btn btn-primary">Enter Final Exam Results</a>
        <a href="{{ route('assessments.create') }}" class="btn btn-secondary">Enter Assessment Results</a>
        {{-- <a href="{{ route('results.sendSms') }}" class="btn btn-secondary">Send Resuults</a> --}}
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
    @if($groupedResults->isEmpty())
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
                        <th>Term</th>
                        <th>Subjects</th>
                        <th>CAs Done</th>
                        <th>Overall CA Avg</th>
                        <th>Overall Exam Avg</th>
                        <th>Final Mark</th>
                        <th>Overall Grade</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedResults as $key => $results)
                        @php
                            $firstResult  = $results->first();
                            $pupil        = $firstResult->pupil;
                            $term         = $firstResult->term;
                            $groupKey     = $pupil->id . '_' . $term;

                            // Assessments for this pupil + term
                            $pupilAssessments = $assessments[$groupKey] ?? collect();

                            // Per-subject final mark calculation
                            $subjectFinalMarks = collect();

                            foreach ($results as $result) {
                                $subjectCAs = $pupilAssessments
                                    ->where('subject_id', $result->subject_id);

                                $caPercentages = collect();

                                // Mid-term as CA
                                if ($result->mid_term_mark !== null) {
                                    $caPercentages->push($result->mid_term_mark);
                                }

                                // Other assessments
                                foreach ($subjectCAs as $ca) {
                                    $caPercentages->push($ca->percentage);
                                }

                                $caAverage    = $caPercentages->isNotEmpty()
                                    ? $caPercentages->avg()
                                    : null;
                                $caWeighted   = $caAverage !== null
                                    ? $caAverage * 0.40
                                    : null;
                                $examWeighted = $result->end_of_term_mark !== null
                                    ? $result->end_of_term_mark * 0.60
                                    : null;

                                if ($caWeighted !== null && $examWeighted !== null) {
                                    $subjectFinalMarks->push([
                                        'ca_avg'     => $caAverage,
                                        'exam_mark'  => $result->end_of_term_mark,
                                        'final_mark' => $caWeighted + $examWeighted,
                                    ]);
                                }
                            }

                            // Aggregates across all subjects
                            $overallCaAvg   = $subjectFinalMarks->isNotEmpty()
                                ? $subjectFinalMarks->avg('ca_avg')
                                : null;
                            $overallExamAvg = $subjectFinalMarks->isNotEmpty()
                                ? $subjectFinalMarks->avg('exam_mark')
                                : null;
                            $overallFinal   = $subjectFinalMarks->isNotEmpty()
                                ? $subjectFinalMarks->avg('final_mark')
                                : null;

                            // Overall Grade
                            $overallGrade  = '-';
                            $overallRemark = '-';
                            if ($overallFinal !== null) {
                                $overallGrade = match(true) {
                                    $overallFinal >= 75 => 'A',
                                    $overallFinal >= 60 => 'B',
                                    $overallFinal >= 50 => 'C',
                                    $overallFinal >= 45 => 'D',
                                    $overallFinal >= 40 => 'E',
                                    default             => 'F'
                                };
                                $overallRemark = match($overallGrade) {
                                    'A' => 'Excellent',
                                    'B' => 'Very Good',
                                    'C' => 'Good',
                                    'D' => 'Satisfactory',
                                    'E' => 'Pass',
                                    'F' => 'Fail'
                                };
                            }

                            // Count total CAs done (mid-terms + assessments)
                            $midTermCount = $results->whereNotNull('mid_term_mark')->count();
                            $totalCaDone  = $midTermCount + $pupilAssessments->count();
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>
                                    {{ $pupil->first_name }} {{ $pupil->last_name }}
                                </strong>
                            </td>
                            <td>{{ $pupil->class->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($term) }}</td>
                            <td>
                                <small>
                                    {{ $results->map(fn($r) => $r->subject->name)->join(', ') }}
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $totalCaDone }}</span>
                            </td>
                            <td class="text-center">
                                {{ $overallCaAvg !== null ? number_format($overallCaAvg, 1).'%' : '—' }}
                            </td>
                            <td class="text-center">
                                {{ $overallExamAvg !== null ? number_format($overallExamAvg, 1).'%' : '—' }}
                            </td>
                            <td class="text-center">
                                <strong>
                                    {{ $overallFinal !== null ? number_format($overallFinal, 1).'%' : '—' }}
                                </strong>
                            </td>
                            <td class="text-center">
                                <span class="badge
                                    @if($overallGrade == 'A') badge-success
                                    @elseif($overallGrade == 'B') badge-primary
                                    @elseif($overallGrade == 'C') badge-info
                                    @elseif($overallGrade == 'D' || $overallGrade == 'E') badge-warning
                                    @else badge-danger
                                    @endif">
                                    {{ $overallGrade }} — {{ $overallRemark }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('examResults.show', $firstResult->id) }}"
                                        class="btn btn-primary mr-1">
                                        View
                                    </a>
                                    <a href="{{ route('examResults.edit', $firstResult->id) }}"
                                        class="btn btn-warning mr-1">
                                        Edit
                                    </a>
                                    <form action="{{ route('examResults.destroy', $firstResult->id) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete all results for {{ $pupil->first_name }} {{ $pupil->last_name }} - {{ ucfirst($term) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div class="mt-2">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                Final Mark = Average of all CAs (including Mid-Term) × 40% + Final Exam × 60%.
                Each row represents one pupil's full term. Click <strong>View</strong> for subject breakdown.
            </small>
        </div>
    @endif
</div>
@endsection

