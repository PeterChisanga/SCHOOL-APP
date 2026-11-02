<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $heading ?? 'Timetable' }} — {{ $subtitle ?? '' }}</title>
    <style>
        /* Landscape, always — a timetable in portrait is unreadable. */
        @page { size: A4 landscape; margin: 8mm 10mm; }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
        }

        /* ---------- school letterhead (same as the report form) ---------- */
        .letterhead { text-align: center; padding-bottom: 6px; border-bottom: 2px solid #222; }
        .letterhead img {
            display: block;
            margin: 0 auto 4px;
            max-height: 64px;
            max-width: 64px;
        }
        .letterhead h1 { font-size: 17px; margin: 0; letter-spacing: .3px; }
        .letterhead .motto { font-size: 10px; font-style: italic; color: #444; margin: 2px 0 0; }
        .letterhead .contact { font-size: 9.5px; color: #444; margin: 2px 0 0; }

        .doc-title { text-align: center; margin: 8px 0 2px; }
        .doc-title h2 { font-size: 13px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .doc-title .meta { font-size: 10px; color: #444; margin-top: 2px; }

        /* ---------- the grid ---------- */
        table.grid {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.grid th, table.grid td {
            border: 1px solid #666;
            padding: 5px 4px;
            vertical-align: middle;
            text-align: center;
        }
        table.grid thead th {
            background: #e9e9e9;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        table.grid th.period-col {
            width: 52px;
            background: #f4f4f4;
            font-size: 10px;
        }
        table.grid td { height: 46px; }
        table.grid td.empty { background: #fbfbfb; color: #bbb; }
        table.grid td.booked .subject { display: block; font-weight: bold; font-size: 11px; }
        table.grid td.booked .klass { display: block; font-size: 9px; color: #555; margin-top: 1px; }

        .footer {
            margin-top: 10px;
            font-size: 9px;
            color: #555;
            display: flex;
            justify-content: space-between;
        }

        .actions { margin-bottom: 10px; }
        .actions button {
            padding: 6px 14px;
            font-size: 12px;
            cursor: pointer;
        }

        @media print {
            .actions { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="actions">
        <button onclick="window.print()">Print</button>
    </div>

    {{-- School header --}}
    <div class="letterhead">
        @if($school && $school->photo)
            <img src="{{ $logoPath ?? asset('storage/' . $school->photo) }}" alt="School Logo">
        @endif
        <h1>{{ $school->name ?? 'Timetable' }}</h1>
        @if($school && $school->motto)
            <p class="motto">{{ $school->motto }}</p>
        @endif
        @if($school)
            <p class="contact">
                {{ $school->address }}
                @if($school->phone) &nbsp;|&nbsp; {{ $school->phone }} @endif
                @if($school->email) &nbsp;|&nbsp; {{ $school->email }} @endif
            </p>
        @endif
    </div>

    <div class="doc-title">
        <h2>{{ $heading ?? 'Timetable' }}</h2>
        <div class="meta">
            {{ $subtitle ?? '' }}
            @if($term) &nbsp;|&nbsp; {{ $term }} &nbsp;|&nbsp; {{ $days }} days × {{ $periods }} periods @endif
        </div>
    </div>

    @if($term)
        <table class="grid">
            <thead>
                <tr>
                    <th class="period-col">Period</th>
                    @for($day = 1; $day <= $days; $day++)
                        <th>{{ \App\Models\TimetableEntry::dayName($day) }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @for($period = 1; $period <= $periods; $period++)
                    <tr>
                        <th class="period-col">P{{ $period }}</th>
                        @for($day = 1; $day <= $days; $day++)
                            @php
                                $entry = $cells[$day][$period] ?? null;
                                $also  = $extra[$day][$period] ?? [];
                            @endphp
                            <td class="{{ $entry && $entry->subject ? 'booked' : 'empty' }}">
                                @if($entry && $entry->subject)
                                    <span class="subject">{{ $entry->subject->name }}</span>
                                    <span class="klass">
                                        @if(($heading ?? '') === 'Class Timetable')
                                            {{ implode(' / ', array_filter(array_merge(
                                                [$entry->teacher ? $entry->teacher->first_name . ' ' . $entry->teacher->last_name : ''],
                                                $also
                                            ))) }}
                                        @else
                                            {{ implode(' / ', array_filter(array_merge([$entry->class->name ?? ''], $also))) }}
                                        @endif
                                    </span>
                                @else
                                    &mdash;
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    @else
        <p style="margin-top:20px;">The school has not published a timetable yet.</p>
    @endif

    <div class="footer">
        <span>{{ $school->name ?? '' }}</span>
        <span>Printed {{ now()->format('j M Y') }}</span>
    </div>

</body>
</html>
