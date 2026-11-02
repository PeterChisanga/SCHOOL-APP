@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>My Timetable @if($term)<small class="text-muted">({{ $term }})</small>@endif</h1>
        <div>
            <a href="{{ route('timetable.print') }}" target="_blank" class="btn btn-sm btn-secondary">Print</a>
            <a href="{{ route('timetable.pdf') }}" class="btn btn-sm btn-primary">Download PDF</a>
        </div>
    </div>

    @if(isset($myClasses) && $myClasses->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">Class timetables you can see</div>
            <div class="card-body">
                @foreach($myClasses as $myClass)
                    <a href="{{ route('timetable.class.view', ['class' => $myClass->id, 'term' => $term]) }}"
                       class="btn btn-sm btn-outline-primary mr-1 mb-1">{{ $myClass->name }}</a>
                @endforeach
            </div>
        </div>
    @endif

    @if(! $term)
        <div class="alert alert-info">
            The school has not published a timetable yet. Check back once the admin confirms it.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th style="width:80px">Period</th>
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
                                @php
                                    $entry = $cells[$day][$period] ?? null;
                                    $also  = $extra[$day][$period] ?? [];
                                @endphp
                                <td class="{{ now()->dayOfWeekIso === $day ? 'table-info' : '' }}">
                                    @if($entry && $entry->subject)
                                        <strong>{{ $entry->subject->name }}</strong>
                                        <div class="text-muted small">{{ implode(' / ', array_filter(array_merge([$entry->class->name ?? ''], $also))) }}</div>
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
    @endif
</div>
@endsection
