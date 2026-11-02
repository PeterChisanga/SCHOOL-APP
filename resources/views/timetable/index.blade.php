@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-3">Timetable</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('timetable.index') }}" class="form-inline">
                <label for="term" class="mr-2">Term</label>
                <select name="term" id="term" class="form-control mr-2" onchange="this.form.submit()">
                    @foreach($terms as $option)
                        <option value="{{ $option }}" {{ $term === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
                <span class="text-muted">A generated week is reused for the whole term.</span>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header">Grid size</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('timetable.settings') }}">
                        @csrf
                        <div class="form-row">
                            <div class="col">
                                <label for="days_per_week">Days per week</label>
                                <input type="number" min="1" max="7" name="days_per_week" id="days_per_week"
                                       class="form-control" value="{{ $days }}" required>
                            </div>
                            <div class="col">
                                <label for="periods_per_day">Periods per day</label>
                                <input type="number" min="1" max="12" name="periods_per_day" id="periods_per_day"
                                       class="form-control" value="{{ $periods }}" required>
                            </div>
                        </div>
                        <button class="btn btn-secondary mt-3">Save grid size</button>
                        <small class="d-block text-muted mt-2">
                            Changing this only affects what the next generation fills.
                        </small>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-header">Actions for {{ $term }}</div>
                <div class="card-body">
                    @if($subjects === 0)
                        <div class="alert alert-warning mb-3">
                            No subjects yet — <a href="{{ route('subjects.index') }}">add subjects</a> before generating.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('timetable.generate') }}" class="d-inline"
                          onsubmit="return confirm('Generate a fresh week for every class in {{ $term }}? Existing cells for this term are replaced.')">
                        @csrf
                        <input type="hidden" name="term" value="{{ $term }}">
                        <button class="btn btn-primary" {{ $subjects === 0 ? 'disabled' : '' }}>Generate whole school</button>
                    </form>

                    <form method="POST" action="{{ route('timetable.publish') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="term" value="{{ $term }}">
                        <input type="hidden" name="action" value="publish">
                        <button class="btn btn-success">Confirm &amp; publish to teachers</button>
                    </form>

                    <form method="POST" action="{{ route('timetable.publish') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="term" value="{{ $term }}">
                        <input type="hidden" name="action" value="unpublish">
                        <button class="btn btn-outline-secondary">Hide from teachers</button>
                    </form>

                    <form method="POST" action="{{ route('timetable.clear') }}" class="d-inline"
                          onsubmit="return confirm('Clear the whole {{ $term }} timetable for the school? This cannot be undone.')">
                        @csrf
                        <input type="hidden" name="term" value="{{ $term }}">
                        <button class="btn btn-outline-danger">Clear {{ $term }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Classes — {{ $term }}</div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Cells filled</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                        @php $row = $counts[$class->id] ?? null; @endphp
                        <tr>
                            <td>{{ $class->name }}</td>
                            <td>{{ $row->cells ?? 0 }} / {{ $days * $periods }}</td>
                            <td>
                                @if($row && $row->published > 0)
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('timetable.grid', ['class' => $class->id, 'term' => $term]) }}"
                                   class="btn btn-sm btn-warning">Open</a>
                                <form method="POST" action="{{ route('timetable.generate') }}" class="d-inline"
                                      onsubmit="return confirm('Regenerate only {{ $class->name }} for {{ $term }}?')">
                                    @csrf
                                    <input type="hidden" name="term" value="{{ $term }}">
                                    <input type="hidden" name="class_id" value="{{ $class->id }}">
                                    <button class="btn btn-sm btn-outline-primary">Regenerate</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No classes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
