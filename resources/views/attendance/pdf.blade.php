<!DOCTYPE html>
<html>
<head>
    <title>Daily Attendance Sheet</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1, h2, h3, p { margin: 0; }
        .center-text { text-align: center; }
        .school-logo { display: block; margin: 0 auto; width: 90px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .status-present { color: #1a7a4a; font-weight: bold; }
        .status-absent { color: #b91c1c; font-weight: bold; }
        .meta { margin-top: 18px; width: 100%; }
        .meta td { border: none; padding: 4px 0; }
        .summary-box { margin-top: 16px; border: 1px solid #444; padding: 8px; }
        .footer { margin-top: 40px; width: 100%; }
        .footer td { border: none; padding-top: 30px; }
        .sign-line { border-top: 1px solid #444; padding-top: 4px; }
    </style>
</head>
<body>

    <div class="center-text">
        @if($school && $school->photo)
            <img src="{{ public_path('storage/' . $school->photo) }}" alt="School Logo" class="school-logo">
        @endif
    </div>

    <div class="center-text">
        <h1>{{ $school->name ?? 'School' }}</h1>
        @if($school && $school->motto)
            <p><strong>Motto:</strong> {{ $school->motto }}</p>
        @endif
        @if($school && $school->address)
            <p>{{ $school->address }}</p>
        @endif
        @if($school)
            <p>
                @if($school->phone)<strong>Phone:</strong> {{ $school->phone }}@endif
                @if($school->phone && $school->email) | @endif
                @if($school->email)<strong>Email:</strong> {{ $school->email }}@endif
            </p>
        @endif
    </div>

    <div class="center-text" style="margin-top: 24px;">
        <h2>Daily Attendance Sheet</h2>
        <h3>{{ $class->name }} &mdash; {{ \Carbon\Carbon::parse($date)->format('l, j F Y') }}</h3>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Class Teacher:</strong>
                {{ $teacher ? ($teacher->first_name . ' ' . $teacher->last_name) : 'N/A' }}
            </td>
            <td style="text-align: right;"><strong>Pupils enrolled:</strong> {{ $pupils->count() }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Pupil</th>
                <th style="width: 110px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pupils as $index => $pupil)
                @php $status = $existing[$pupil->id] ?? null; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pupil->first_name }} {{ $pupil->middle_name }} {{ $pupil->last_name }}</td>
                    <td>
                        @if ($status === 'present')
                            <span class="status-present">Present</span>
                        @elseif ($status === 'absent')
                            <span class="status-absent">Absent</span>
                        @else
                            &mdash;
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="center-text">No pupils enrolled.</td></tr>
            @endforelse
        </tbody>
    </table>

    @php
        $presentCount = count(array_filter($existing, fn ($s) => $s === 'present'));
        $absentCount = count(array_filter($existing, fn ($s) => $s === 'absent'));
        $recordedCount = count($existing);
    @endphp

    <div class="summary-box">
        <strong>Summary:</strong>
        Present: {{ $presentCount }} &nbsp;|&nbsp;
        Absent: {{ $absentCount }} &nbsp;|&nbsp;
        Recorded: {{ $recordedCount }} of {{ $pupils->count() }}
        @if ($recordedCount === 0)
            &nbsp;|&nbsp; <em>No register was taken for this date.</em>
        @endif
    </div>

    <table class="footer">
        <tr>
            <td style="width: 60%;">
                <div class="sign-line">Class Teacher's Signature</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="sign-line">Head Teacher's Signature</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right; padding-top: 20px; color: #666;">
                Generated {{ now()->format('j F Y, H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>
