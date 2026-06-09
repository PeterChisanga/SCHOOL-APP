<!DOCTYPE html>
<html>
<head>
    <title>Exam Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .center-text {
            text-align: center;
        }
        h1, h2, h3, h4, p {
            margin: 0;
        }
        .school-logo {
            display: block;
            margin: 0 auto;
            width: 100px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 5px 6px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-size: 11px;
        }
        td {
            font-size: 11px;
        }
        .subject-col {
            text-align: left;
            font-weight: bold;
            min-width: 90px;
        }
        .ca-group-header {
            background-color: #ddeeff;
        }
        .exam-group-header {
            background-color: #fff3cc;
        }
        .final-col {
            background-color: #e8f5e9;
            font-weight: bold;
        }
        .grade-key table {
            width: 55%;
            margin: 8px auto 0;
        }
        .section-title {
            margin-top: 15px;
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .pupil-info {
            margin-top: 15px;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .signature-line {
            margin-top: 18px;
            font-size: 12px;
        }
        .weight-note {
            font-size: 10px;
            color: #555;
            font-weight: normal;
        }
    </style>
</head>
<body>

    {{-- School Header --}}
    <div class="center-text">
        @if($school->photo)
            <img src="{{ public_path('storage/' . $school->photo) }}"
                alt="School Logo" class="school-logo">
        @endif
        <h1>{{ $school->name }}</h1>
        <p><strong>Motto:</strong> {{ $school->motto }}</p>
        <p>{{ $pupil->school->address }}</p>
        <p>
            <strong>Contact:</strong> {{ $pupil->school->phone }} &nbsp;|&nbsp;
            <strong>Email:</strong> {{ $pupil->school->email }}
        </p>
    </div>

    {{-- Report Title --}}
    <div class="center-text" style="margin-top: 14px;">
        <h2>SCHOOL REPORT FORM</h2>
        <h3>{{ $pupil->first_name }} {{ $pupil->last_name }}</h3>
    </div>

    {{-- Pupil Info --}}
    <p class="pupil-info">
        <strong>Class:</strong> {{ $pupil->class->name }} &nbsp;|&nbsp;
        <strong>Gender:</strong> {{ $pupil->gender }} &nbsp;|&nbsp;
        <strong>Term:</strong> {{ ucfirst($term) }} {{ $year }}
        @if($pupil->school->is_premium)
            &nbsp;|&nbsp; <strong>Position in Class:</strong> {{ $position ?? '-' }}
        @endif
    </p>

    @if($examResultsForTerm->isEmpty())
        <p>No results available for {{ ucfirst($term) }}.</p>
    @else
        {{-- Results Table --}}
        <table>
            <thead>
                {{-- Row 1: Group headers --}}
                <tr>
                    <th rowspan="2" class="subject-col">Subject</th>

                    {{-- CA columns: 1 (mid-term) + other assessments --}}
                    <th colspan="{{ 1 + $allCaTitles->count() }}" class="ca-group-header">
                        Continuous Assessment
                        <span class="weight-note">(40%)</span>
                    </th>

                    <th rowspan="2" class="ca-group-header">
                        Total CA
                        <br><span class="weight-note">avg</span>
                    </th>

                    <th rowspan="2" class="exam-group-header">
                        Final Exam
                        <br><span class="weight-note">(60%)</span>
                    </th>

                    <th rowspan="2" class="final-col">Final Mark</th>
                    <th rowspan="2" class="final-col">Grade</th>
                    <th rowspan="2">Comments</th>
                </tr>

                {{-- Row 2: Individual CA column names --}}
                <tr>
                    <th class="ca-group-header">Mid-Term Test</th>
                    @foreach($allCaTitles as $caTitle)
                        <th class="ca-group-header">{{ $caTitle }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($examResultsForTerm as $result)
                    @php
                        $subjectAssessments = $assessmentsBySubject[$result->subject_id] ?? collect();
                        $assessmentByTitle  = $subjectAssessments->keyBy('title');

                        // Mid-term as CA
                        $caPercentages = collect();
                        if ($result->mid_term_mark !== null) {
                            $caPercentages->push($result->mid_term_mark);
                        }

                        // Other CA titles
                        foreach ($allCaTitles as $caTitle) {
                            $ca = $assessmentByTitle[$caTitle] ?? null;
                            if ($ca) {
                                $caPercentages->push($ca->percentage);
                            }
                        }

                        // Weighted calculations
                        $caAverage    = $caPercentages->isNotEmpty() ? $caPercentages->avg() : null;
                        $caWeighted   = $caAverage !== null ? $caAverage * 0.40 : null;
                        $examMark     = $result->end_of_term_mark;
                        $examWeighted = $examMark !== null ? $examMark * 0.60 : null;
                        $finalMark    = ($caWeighted !== null && $examWeighted !== null)
                            ? $caWeighted + $examWeighted
                            : null;

                        // Grade & Remark
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
                        <td class="subject-col">{{ $result->subject->name }}</td>

                        {{-- Mid-Term CA --}}
                        <td>
                            @if($result->mid_term_mark !== null)
                                @if($pupil->school->is_premium && $result->mid_term_raw !== null)
                                    {{ number_format($result->mid_term_raw, 1) }}/{{ number_format($result->mid_term_max, 1) }}
                                    ({{ number_format($result->mid_term_mark, 1) }}%)
                                @else
                                    {{ number_format($result->mid_term_mark, 1) }}%
                                @endif
                            @else
                                —
                            @endif
                        </td>

                        {{-- Other CA columns aligned by title --}}
                        @foreach($allCaTitles as $caTitle)
                            @php $ca = $assessmentByTitle[$caTitle] ?? null; @endphp
                            <td>
                                @if($ca)
                                    @if($ca->raw_mark !== null)
                                        {{ number_format($ca->raw_mark, 1) }}/{{ number_format($ca->max_mark, 1) }}
                                        ({{ number_format($ca->percentage, 1) }}%)
                                    @else
                                        {{ number_format($ca->percentage, 1) }}%
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach

                        {{-- Total CA Average --}}
                        <td>
                            {{ $caAverage !== null ? number_format($caAverage, 1).'%' : '—' }}
                        </td>

                        {{-- Final Exam --}}
                        <td>
                            @if($examMark !== null)
                                @if($pupil->school->is_premium && $result->end_term_raw !== null)
                                    {{ number_format($result->end_term_raw, 1) }}/{{ number_format($result->end_term_max, 1) }}
                                    ({{ number_format($examMark, 1) }}%)
                                @else
                                    {{ number_format($examMark, 1) }}%
                                @endif
                            @else
                                —
                            @endif
                        </td>

                        {{-- Final Mark --}}
                        <td class="final-col">
                            {{ $finalMark !== null ? number_format($finalMark, 1).'%' : '—' }}
                        </td>

                        {{-- Grade --}}
                        <td class="final-col">{{ $grade }}</td>

                        {{-- Comments --}}
                        <td>{{ $result->comments ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Formula note --}}
        <p style="font-size:10px; color:#555; margin-top:6px;">
            * Final Mark = Average of all CAs (including Mid-Term) × 40% + Final Exam × 60%
        </p>

        {{-- Grade Key --}}
        <div class="grade-key">
            <p class="section-title">Grade Key</p>
            <table>
                <thead>
                    <tr>
                        <th>Final Mark (%)</th>
                        <th>Grade</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>75 and above</td><td>A</td><td>Excellent</td></tr>
                    <tr><td>60 – 74</td><td>B</td><td>Very Good</td></tr>
                    <tr><td>50 – 59</td><td>C</td><td>Good</td></tr>
                    <tr><td>45 – 49</td><td>D</td><td>Satisfactory</td></tr>
                    <tr><td>40 – 44</td><td>E</td><td>Pass</td></tr>
                    <tr><td>Below 40</td><td>F</td><td>Fail</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Signature Lines --}}
        <p class="signature-line">
            Class Teacher's Comment: ........................................................................
            Signature: ..................
        </p>
        <br>
        <p class="signature-line">
            Head Teacher's Comment: .........................................................................
            Signature: ..................
        </p>
    @endif

</body>
</html>
