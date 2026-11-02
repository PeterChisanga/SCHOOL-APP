@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Timetable — {{ $class->name }} @if($term)<small class="text-muted">({{ $term }})</small>@endif</h1>
        <div>
            @unless($published)
                <span class="badge badge-secondary mr-2">Not published yet</span>
            @endunless
            <a href="{{ route('timetable.class.print', ['class' => $class->id, 'term' => $term]) }}"
               target="_blank" class="btn btn-sm btn-secondary">Print</a>
            <a href="{{ route('timetable.class.pdf', ['class' => $class->id, 'term' => $term]) }}"
               class="btn btn-sm btn-primary">Download PDF</a>
            @if(in_array(Auth::user()->user_type, ['admin', 'secretary'], true))
                <a href="{{ route('timetable.classes') }}" class="btn btn-sm btn-outline-secondary">All classes</a>
            @else
                <a href="{{ route('timetable.mine') }}" class="btn btn-sm btn-outline-secondary">My timetable</a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead>
                <tr>
                    <th style="width:90px">Period</th>
                    @for($day = 1; $day <= $days; $day++)
                        <th class="{{ now()->dayOfWeekIso === $day ? 'table-info' : '' }}">
                            {{ \App\Models\TimetableEntry::dayName($day) }}
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @for($period = 1; $period <= $periods; $period++)
                    <tr>
                        <th>P{{ $period }}</th>
                        @for($day = 1; $day <= $days; $day++)
                            @php $entry = $cells[$day][$period] ?? null; @endphp
                            <td class="{{ now()->dayOfWeekIso === $day ? 'table-info' : '' }}">
                                @if($entry && $entry->subject)
                                    <strong>{{ $entry->subject->name }}</strong>
                                    @if($entry->teacher)
                                        <div class="text-muted small">
                                            {{ $entry->teacher->first_name }} {{ $entry->teacher->last_name }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection
