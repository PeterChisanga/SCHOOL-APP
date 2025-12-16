<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>Period / Day</th>
            @foreach(['Mon','Tue','Wed','Thu','Fri'] as $day)
                <th>{{ $day }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($timeSlots as $slot)
            <tr>
                <td>{{ $slot->name }} ({{ substr($slot->start_time,0,5) }} - {{ substr($slot->end_time,0,5) }})</td>
                @foreach(['Mon','Tue','Wed','Thu','Fri'] as $day)
                    <td>
                        @php
                            $entry = $timetable[$day][$slot->id] ?? null;
                            if($entry && $entry->count()) $entry = $entry->first();
                        @endphp
                        @if($entry)
                            @if(isset($entry->subject))
                                <strong>{{ $entry->subject->name }}</strong><br>
                            @endif
                            @if(isset($entry->teacher))
                                <small>{{ $entry->teacher->first_name }} {{ $entry->teacher->last_name }}</small>
                            @elseif(isset($entry->class))
                                <small>{{ $entry->class->name }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

