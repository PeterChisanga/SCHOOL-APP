<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timetable - {{ $meta['name'] ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        .header { text-align:center; }
        .logo { height:60px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #333; padding:6px; text-align:center; }
        .small { font-size:10px; }
        .footer { margin-top:20px; font-size:11px; }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($meta['school_logo']))
            <img src="{{ $meta['school_logo'] }}" class="logo" alt="logo" />
        @endif
        <h2>{{ $meta['name'] ?? '' }} - {{ $meta['role'] ?? '' }} Timetable</h2>
        <div>{{ $term }} - {{ $year }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Period / Day</th>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($timeSlots as $slot)
                <tr>
                    <td class="small">{{ $slot->name }}<br><span class="small">{{ substr($slot->start_time,0,5) }} - {{ substr($slot->end_time,0,5) }}</span></td>
                    @foreach($days as $day)
                        @php
                            $entry = $timetable->first(function($it) use($day,$slot){ return $it->day===$day && $it->time_slot_id===$slot->id; });
                        @endphp
                        <td>
                            @if($entry)
                                <strong>{{ $entry->subject->name ?? 'Subject' }}</strong><br>
                                <span class="small">Teacher: {{ $entry->teacher->first_name ?? '' }} {{ $entry->teacher->last_name ?? '' }}</span><br>
                                <span class="small">Room: {{ $entry->room ?? '-' }}</span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div>Generated: {{ now()->toDateTimeString() }}</div>
        <div style="margin-top:20px;">Signature: ____________________________</div>
    </div>
</body>
</html>
