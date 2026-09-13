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
            <h3 class="card-title mb-0"><i class="fas fa-clipboard-check"></i> Attendance Oversight</h3>
            <form method="GET" action="{{ route('admin.attendance.index') }}" class="form-inline">
                <label for="date" class="mr-2">Date</label>
                <input type="date" name="date" id="date" class="form-control mr-2"
                       value="{{ $date }}" max="{{ now()->toDateString() }}" required>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Show</button>
            </form>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner"><h3>{{ $summary['classes'] }}</h3><p>Classes</p></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner"><h3>{{ $summary['taken'] }}</h3><p>Registers taken</p></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner"><h3>{{ $summary['present'] }}</h3><p>Present</p></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner"><h3>{{ $summary['absent'] }}</h3><p>Absent</p></div>
                    </div>
                </div>
            </div>

            @if ($rows->isEmpty())
                <div class="alert alert-info mb-0">No classes exist for this school yet.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Class Teacher</th>
                                <th class="text-center">Enrolled</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Register</th>
                                <th style="width: 160px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="align-middle"><strong>{{ $row['class']->name }}</strong></td>
                                    <td class="align-middle">
                                        {{ $row['teacher'] ? ($row['teacher']->first_name . ' ' . $row['teacher']->last_name) : '—' }}
                                    </td>
                                    <td class="text-center align-middle">{{ $row['enrolled'] }}</td>
                                    <td class="text-center align-middle text-success"><strong>{{ $row['taken'] ? $row['present'] : '—' }}</strong></td>
                                    <td class="text-center align-middle text-danger"><strong>{{ $row['taken'] ? $row['absent'] : '—' }}</strong></td>
                                    <td class="text-center align-middle">
                                        @if ($row['taken'])
                                            <span class="badge badge-success">Taken</span>
                                        @else
                                            <span class="badge badge-secondary">Not taken</span>
                                        @endif
                                    </td>
                                    <td class="text-right align-middle">
                                        <a href="{{ route('admin.attendance.show', ['class' => $row['class']->id, 'date' => $date]) }}"
                                           class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('admin.attendance.pdf', ['class' => $row['class']->id, 'date' => $date]) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Print / PDF">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Showing registers for {{ \Carbon\Carbon::parse($date)->format('l, j F Y') }}.</small>
            @endif
        </div>
    </div>
</div>
@endsection
