@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    @if (!$class)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Attendance</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    You are not set as the <strong>Class Teacher</strong> of any class yet.
                    Ask your administrator to assign your class.
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-clipboard-check"></i> Attendance — {{ $class->name }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('attendance.stats') }}" class="btn btn-sm btn-outline-secondary">Summary</a>
                    <a href="{{ route('attendance.days') }}" class="btn btn-sm btn-outline-secondary">By day</a>
                </div>
            </div>
            <div class="card-body">
                <a href="{{ route('attendance.register') }}" class="btn btn-success btn-lg">
                    <i class="fas fa-plus"></i> Take New Attendance
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Previous records</h3>
            </div>
            <div class="card-body">
                @if ($recent->isEmpty())
                    <div class="alert alert-info mb-0">
                        No registers have been taken yet. Click <strong>Take New Attendance</strong> to record today's.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Absent</th>
                                    <th style="width: 120px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recent as $date => $counts)
                                    <tr>
                                        <td class="align-middle">
                                            <strong>{{ \Carbon\Carbon::parse($date)->format('l, j M Y') }}</strong>
                                        </td>
                                        <td class="text-center align-middle text-success">
                                            <strong>{{ $counts['present'] }}</strong>
                                        </td>
                                        <td class="text-center align-middle text-danger">
                                            <strong>{{ $counts['absent'] }}</strong>
                                        </td>
                                        <td class="text-right align-middle">
                                            <a href="{{ route('attendance.register', ['date' => $date]) }}"
                                               class="btn btn-sm btn-outline-primary">View / edit</a>
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
</div>
@endsection
