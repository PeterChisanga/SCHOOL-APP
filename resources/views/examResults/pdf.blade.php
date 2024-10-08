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
        <p> {{ $pupil->school->address }}</p>
        <p><strong>Contact:</strong> {{ $pupil->school->phone }} | <strong>Email:</strong> {{ $pupil->school->email }}</p>
    </div>

    <div class="center-text" style="margin-top: 30px;">
        <h2>SCHOOL REPORT FORM</h2>
        <h3>Results for {{ $pupil->first_name }} {{ $pupil->last_name }}</h3>
    </div>

    <p><strong>Class:</strong> {{ $pupil->class->name }} | <strong>Gender:</strong> {{ $pupil->gender }}</p>

    <table>
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
            @foreach ($pupil->examResults as $result)
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

                <tr>
                    <td>{{ $result->subject->name }}</td>
                    <td>{{ $result->mid_term_mark }} %</td>
                    <td>{{ $result->end_of_term_mark }} %</td>
                    <td>{{ $average }} %</td>
                    <td>{{ $grade }}</td>
                    <td>{{ $remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

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
<p>Class Teacher's Comment : ..........................................................................Signature: ..................</p>
<br>
<p>Head Teacher's Comment : ...........................................................................Signature: ...................</p>

</body>
</html>
