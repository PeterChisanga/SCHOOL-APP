@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-calendar-day"></i> Attendance by Day
                @if ($class)
                    — {{ $class->name }}
                @endif
            </h3>
            <div class="card-tools">
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary">Take attendance</a>
                <a href="{{ route('attendance.stats') }}" class="btn btn-sm btn-outline-secondary">Summary</a>
            </div>
        </div>
        <div class="card-body">
            @if (!$class)
                <div class="alert alert-warning mb-0">
                    You are not set as the <strong>Class Teacher</strong> of any class yet.
                    Ask your administrator to assign your class.
                </div>
            @else
                <form method="GET" action="{{ route('attendance.days') }}" class="form-inline">
                    <div class="form-group mr-3 mb-2">
                        <label for="from" class="mr-2">From</label>
                        <input type="date" name="from" id="from" class="form-control" value="{{ $from }}" required>
                    </div>
                    <div class="form-group mr-3 mb-2">
                        <label for="to" class="mr-2">To</label>
                        <input type="date" name="to" id="to" class="form-control" value="{{ $to }}"
                               max="{{ now()->toDateString() }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-filter"></i> Show days</button>
                </form>
            @endif
        </div>
    </div>

    @if ($class)
        @if ($from > $to)
            <div class="alert alert-danger">The "From" date must be before the "To" date.</div>
        @else
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $class->name }} &mdash;
                        {{ \Carbon\Carbon::parse($from)->format('j M Y') }} to
                        {{ \Carbon\Carbon::parse($to)->format('j M Y') }}</h3>
                </div>
                <div class="card-body">
                    @if ($days->isEmpty())
                        <div class="alert alert-info mb-0">
                            No registers were taken in this period. Once you take attendance, each day appears here.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                        <th>Absentees</th>
                                        <th style="width: 120px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($days as $date => $rows)
                                        @php
                                            $presentCount = $rows->where('status', 'present')->count();
                                            $absentees = $rows->where('status', 'absent');
                                        @endphp
                                        <tr>
                                            <td class="align-middle">
                                                <strong>{{ \Carbon\Carbon::parse($date)->format('l, j M Y') }}</strong>
                                            </td>
                                            <td class="text-center align-middle text-success">
                                                <strong>{{ $presentCount }}</strong>
                                            </td>
                                            <td class="text-center align-middle text-danger">
                                                <strong>{{ $absentees->count() }}</strong>
                                            </td>
                                            <td class="align-middle">
                                                @if ($absentees->isEmpty())
                                                    <span class="badge badge-success">Everyone present</span>
                                                @else
                                                    {{ $absentees->map(fn ($a) => trim(($a->pupil->first_name ?? '') . ' ' . ($a->pupil->last_name ?? '')))->filter()->implode(', ') }}
                                                @endif
                                            </td>
                                            <td class="text-right align-middle">
                                                <a href="{{ route('attendance.index', ['date' => $date]) }}"
                                                   class="btn btn-sm btn-outline-primary">View / edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Only days with a saved register are listed.</small>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
