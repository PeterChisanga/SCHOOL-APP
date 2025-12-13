

<!DOCTYPE html>
<html>
<head>
    <title>Exam Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="center-text">
        @if($school->photo)
            <img src="{{ public_path('storage/' . $school->photo) }}" alt="School Logo" class="school-logo">
        @endif
    </div>

    <div class="center-text">
        <h1>{{ $school->name }}</h1>
        <p><strong>Motto:</strong> {{ $school->motto }}</p>
        <p>{{ $pupil->school->address }}</p>
        <p><strong>Contact:</strong> {{ $pupil->school->phone }} | <strong>Email:</strong> {{ $pupil->school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>SCHOOL REPORT FORM</h2>
        <h3>Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</h3>
    </div>
    <p><strong>Class:</strong> {{ $pupil->class->name }} | <strong>Gender:</strong> {{ $pupil->gender }} | <strong>Term:</strong> {{ $term }} {{ $year }}  @if(auth()->user()->isPremium()) | <strong>Position in Class:</strong> {{ $position ?? '-' }} @endif</p>

    @if($examResultsForTerm->isEmpty())
        <p>No results available for {{ $term }}.</p>
    @else
        <table style="margin: 10px auto; font-size: 12px;">
            <thead>
                <tr>
                    <th rowspan="2">Subject</th>
                    @if(auth()->user()->isPremium())
                        <th colspan="3" class="text-center">Mid-Term</th>
                        <th colspan="3" class="text-center">End of Term</th>
                    @else
                        <th rowspan="2">Mid Term Mark</th>
                        <th rowspan="2">End of Term Mark</th>
                    @endif
                    <th rowspan="2">Average</th>
                    <th rowspan="2">Grade</th>
                    <th rowspan="2">Comments</th>
                </tr>
                @if(auth()->user()->isPremium())
                    <tr>
                        <th>Mark</th>
                        <th>Total Mark</th>
                        <th>%</th>
                        <th>Mark</th>
                        <th>Total Mark</th>
                        <th>%</th>
                    </tr>
                @else
                    <br>
                @endif
            </thead>
            <tbody>
                @foreach ($examResultsForTerm as $result)
                    @php
                        $average = ($result->mid_term_mark !== null && $result->end_of_term_mark !== null)
                            ? ($result->mid_term_mark + $result->end_of_term_mark) / 2
                            : null;
                        $grade = '-';
                        if ($average !== null) {
                            $grade = match(true) {
                                $average >= 75 => 'A',
                                $average >= 60 => 'B',
                                $average >= 50 => 'C',
                                $average >= 45 => 'D',
                                $average >= 40 => 'E',
                                default => 'F'
                            };
                        }
                    @endphp
                    <tr>
                        <td>{{ $result->subject->name }}</td>
                        @if(auth()->user()->isPremium())
                            <td>{{ $result->mid_term_raw !== null ? number_format($result->mid_term_raw, 2) : '-' }}</td>
                            <td>{{ $result->mid_term_max !== null ? number_format($result->mid_term_max, 2) : '-' }}</td>
                            <td>{{ number_format($result->mid_term_mark, 2) }}%</td>
                            <td>{{ $result->end_term_raw !== null ? number_format($result->end_term_raw, 2) : '-' }}</td>
                            <td>{{ $result->end_term_max !== null ? number_format($result->end_term_max, 2) : '-' }}</td>
                            <td>{{ number_format($result->end_of_term_mark, 2) }}%</td>
                        @else
                            <td>{{ $result->mid_term_mark !== null ? number_format($result->mid_term_mark, 2) : '-' }}%</td>
                            <td>{{ $result->end_of_term_mark !== null ? number_format($result->end_of_term_mark, 2) : '-' }}%</td>
                        @endif
                        <td>{{ $average !== null ? number_format($average, 2) : '-' }}%</td>
                        <td>{{ $grade }}</td>
                        <td>{{ $result->comments ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>Grade Key</h3>
    <table border="1" cellpadding="3" cellspacing="0" width="60%" style="margin: 0 auto; font-size: 12px;">
        <thead>
            <tr>
                <th>Average (%)</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>75 and above</td>
                <td>A</td>
                <td>Excellent</td>
            </tr>
            <tr>
                <td>60 - 74</td>
                <td>B</td>
                <td>Very Good</td>
            </tr>
            <tr>
                <td>50 - 59</td>
                <td>C</td>
                <td>Good</td>
            </tr>
            <tr>
                <td>45 - 49</td>
                <td>D</td>
                <td>Satisfactory</td>
            </tr>
            <tr>
                <td>40 - 44</td>
                <td>E</td>
                <td>Pass</td>
            </tr>
            <tr>
                <td>Below 40</td>
                <td>F</td>
                <td>Fail</td>
            </tr>
        </tbody>
    </table>
    <br>
    <p>Class Teacher's Comment: ..........................................................................Signature: ..................</p>
    <br>
    <p>Head Teacher's Comment: ...........................................................................Signature: ...................</p>
</body>
</html>
