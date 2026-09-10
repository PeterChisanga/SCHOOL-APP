@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-chart-bar"></i> Attendance Summary
                @if ($class)
                    — {{ $class->name }}
                @endif
            </h3>
            <div class="card-tools">
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary">Take attendance</a>
                <a href="{{ route('attendance.days') }}" class="btn btn-sm btn-outline-secondary">By day</a>
            </div>
        </div>
        <div class="card-body">
            @if (!$class)
                <div class="alert alert-warning mb-0">
                    You are not set as the <strong>Class Teacher</strong> of any class yet.
                    Ask your administrator to assign your class.
                </div>
            @else
                <form method="GET" action="{{ route('attendance.stats') }}" class="form-inline">
                    <div class="form-group mr-3 mb-2">
                        <label for="from" class="mr-2">From</label>
                        <input type="date" name="from" id="from" class="form-control" value="{{ $from }}" required>
                    </div>
                    <div class="form-group mr-3 mb-2">
                        <label for="to" class="mr-2">To</label>
                        <input type="date" name="to" id="to" class="form-control" value="{{ $to }}"
                               max="{{ now()->toDateString() }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-filter"></i> Show summary</button>
                    <span class="text-muted ml-2 mb-2">Tip: set From/To to match a term, or leave as the current year.</span>
                </form>
            @endif
        </div>
    </div>

    @if ($class)
        @if ($from > $to)
            <div class="alert alert-danger">The "From" date must be before the "To" date.</div>
        @else
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $daysRecorded }}</h3>
                            <p>Days recorded</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $pupils->count() }}</h3>
                            <p>Pupils enrolled</p>
                        </div>
                    </div>
                </div>
                @php
                    $totalPresent = $stats->sum('present');
                    $totalAbsent = $stats->sum('absent');
                    $avg = ($totalPresent + $totalAbsent) > 0
                        ? round($totalPresent / ($totalPresent + $totalAbsent) * 100)
                        : null;
                @endphp
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $avg !== null ? $avg . '%' : '—' }}</h3>
                            <p>Average attendance</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $totalAbsent }}</h3>
                            <p>Absences recorded</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $class->name }} &mdash;
                        {{ \Carbon\Carbon::parse($from)->format('j M Y') }} to
                        {{ \Carbon\Carbon::parse($to)->format('j M Y') }}</h3>
                </div>
                <div class="card-body">
                    @if ($pupils->isEmpty())
                        <div class="alert alert-info mb-0">No pupils are enrolled in this class.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Pupil</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Recorded</th>
                                        <th style="width: 220px;">Attendance rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pupils as $i => $pupil)
                                        @php
                                            $row = $stats->get($pupil->id);
                                            $present = $row->present ?? 0;
                                            $absent = $row->absent ?? 0;
                                            $total = $row->total ?? 0;
                                            $pct = $total > 0 ? round($present / $total * 100) : null;
                                            $bar = $pct !== null
                                                ? ($pct >= 90 ? 'bg-success' : ($pct >= 75 ? 'bg-warning' : 'bg-danger'))
                                                : 'bg-secondary';
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>
                                                <strong>{{ $pupil->first_name }} {{ $pupil->last_name }}</strong>
                                                @if ($pupil->middle_name)
                                                    <span class="text-muted">({{ $pupil->middle_name }})</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-success"><strong>{{ $present }}</strong></td>
                                            <td class="text-center text-danger"><strong>{{ $absent }}</strong></td>
                                            <td class="text-center">{{ $total }}</td>
                                            <td>
                                                @if ($pct !== null)
                                                    <div class="progress progress-sm" style="min-width: 110px;">
                                                        <div class="progress-bar {{ $bar }}" style="width: {{ $pct }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $pct }}%</small>
                                                @else
                                                    <small class="text-muted">No records in this period</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
