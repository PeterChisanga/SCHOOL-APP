@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Class Timetables @if($term)<small class="text-muted">({{ $term }})</small>@endif</h1>
        @if(Auth::user()->user_type === 'admin')
            <a href="{{ route('timetable.index', ['term' => $term]) }}" class="btn btn-sm btn-primary">Manage timetable</a>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Pupils</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                        <tr>
                            <td>{{ $class->name }}</td>
                            <td>{{ $class->pupils()->count() }}</td>
                            <td>
                                <a href="{{ route('timetable.class.view', ['class' => $class->id, 'term' => $term]) }}"
                                   class="btn btn-sm btn-warning">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No classes you can see yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
