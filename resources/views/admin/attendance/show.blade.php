@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-clipboard-check"></i> {{ $class->name }} — {{ \Carbon\Carbon::parse($date)->format('l, j F Y') }}
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.attendance.pdf', ['class' => $class->id, 'date' => $date]) }}"
                   class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print / PDF</a>
                <a href="{{ route('admin.attendance.index', ['date' => $date]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendance.show', $class->id) }}" class="form-inline mb-3">
                <label for="date" class="mr-2">Date</label>
                <input type="date" name="date" id="date" class="form-control mr-2"
                       value="{{ $date }}" max="{{ now()->toDateString() }}" required>
                <button type="submit" class="btn btn-outline-primary">Open</button>
                <span class="ml-3 text-muted">
                    Class Teacher:
                    <strong>{{ $teacher ? ($teacher->first_name . ' ' . $teacher->last_name) : 'N/A' }}</strong>
                </span>
            </form>

            @php
                $presentCount = count(array_filter($existing, fn ($s) => $s === 'present'));
                $absentCount = count(array_filter($existing, fn ($s) => $s === 'absent'));
            @endphp

            @if (empty($existing))
                <div class="alert alert-warning">No register was taken for this date.</div>
            @else
                <div class="alert alert-info">
                    Recorded {{ count($existing) }} of {{ $pupils->count() }} pupils —
                    <strong class="text-success">{{ $presentCount }} present</strong>,
                    <strong class="text-danger">{{ $absentCount }} absent</strong>.
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Pupil</th>
                            <th style="width: 140px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pupils as $i => $pupil)
                            @php $status = $existing[$pupil->id] ?? null; @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $pupil->first_name }} {{ $pupil->last_name }}</strong>
                                    @if ($pupil->middle_name)
                                        <span class="text-muted">({{ $pupil->middle_name }})</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($status === 'present')
                                        <span class="badge badge-success">Present</span>
                                    @elseif ($status === 'absent')
                                        <span class="badge badge-danger">Absent</span>
                                    @else
                                        <span class="badge badge-secondary">Not recorded</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
