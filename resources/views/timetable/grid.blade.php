@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Timetable — {{ $class->name }} ({{ $term }})</h1>
        <div>
            @if($published)
                <span class="badge badge-success mr-2">Published</span>
            @else
                <span class="badge badge-secondary mr-2">Draft — not visible to teachers</span>
            @endif
            <a href="{{ route('timetable.class.view', ['class' => $class->id, 'term' => $term]) }}"
               class="btn btn-sm btn-outline-secondary">Read-only view</a>
            <a href="{{ route('timetable.class.pdf', ['class' => $class->id, 'term' => $term]) }}"
               class="btn btn-sm btn-outline-primary">PDF</a>
            <a href="{{ route('timetable.index', ['term' => $term]) }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p class="text-muted">
        Pick a subject and teacher per cell. Save as often as you like — the admin's choice always wins,
        even if it double-books a teacher (those cells are highlighted amber).
        Set a subject to “—” to clear the cell.
    </p>

    <form method="POST" action="{{ route('timetable.save', ['class' => $class->id]) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="term" value="{{ $term }}">

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th style="width:80px">Period</th>
                        @for($day = 1; $day <= $days; $day++)
                            <th>{{ \App\Models\TimetableEntry::dayName($day) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @for($period = 1; $period <= $periods; $period++)
                        <tr>
                            <th>P{{ $period }}</th>
                            @for($day = 1; $day <= $days; $day++)
                                @php
                                    $entry = $entries[$day . '-' . $period] ?? null;
                                    $isConflict = $entry && $entry->teacher_id
                                        && isset($conflicts[$entry->teacher_id . '-' . $day . '-' . $period]);
                                @endphp
                                <td class="{{ $isConflict ? 'table-warning' : '' }}">
                                    <select name="cells[{{ $day }}][{{ $period }}][subject_id]"
                                            class="form-control form-control-sm mb-1">
                                        <option value="">—</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}"
                                                {{ (int) old("cells.$day.$period.subject_id", $entry->subject_id ?? 0) === (int) $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="cells[{{ $day }}][{{ $period }}][teacher_id]"
                                            class="form-control form-control-sm">
                                        <option value="">No teacher</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                {{ (int) old("cells.$day.$period.teacher_id", $entry->teacher_id ?? 0) === (int) $teacher->id ? 'selected' : '' }}>
                                                {{ $teacher->first_name }} {{ $teacher->last_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @if($isConflict)
                                        <small class="text-warning d-block">Teacher already booked in this slot</small>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <button class="btn btn-primary">Save timetable</button>
    </form>

    <div class="mt-3">
        <form method="POST" action="{{ route('timetable.publish') }}" class="d-inline">
            @csrf
            <input type="hidden" name="term" value="{{ $term }}">
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="action" value="publish">
            <button class="btn btn-success">Confirm &amp; publish this class</button>
        </form>

        <form method="POST" action="{{ route('timetable.clear') }}" class="d-inline"
              onsubmit="return confirm('Clear every cell for {{ $class->name }} in {{ $term }}?')">
            @csrf
            <input type="hidden" name="term" value="{{ $term }}">
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <button class="btn btn-outline-danger">Clear this class</button>
        </form>
    </div>
</div>
@endsection
