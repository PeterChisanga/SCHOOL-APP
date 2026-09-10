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
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!$class)
        <div class="card">
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    You are not set as the <strong>Class Teacher</strong> of any class yet.
                    Ask your administrator to assign your class.
                </div>
                <a href="{{ route('attendance.index') }}" class="btn btn-default mt-3">Back to attendance</a>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-clipboard-check"></i> Register — {{ $class->name }}
                </h3>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to attendance
                </a>
            </div>
            <div class="card-body">
                @if ($date > now()->toDateString())
                    <div class="alert alert-danger">Please pick today or an earlier date.</div>
                @else
                    <form method="GET" action="{{ route('attendance.register') }}" class="form-inline mb-3">
                        <label for="date" class="mr-2">Date</label>
                        <input type="date" name="date" id="date" class="form-control mr-2"
                               value="{{ $date }}" max="{{ now()->toDateString() }}" required>
                        <button type="submit" class="btn btn-outline-primary">Open register</button>
                    </form>

                    @if ($savedForDate)
                        <div class="alert alert-info">
                            A register already exists for this date — review the marks below and save to update.
                        </div>
                    @elseif (!$pupils->isEmpty())
                        <div class="alert alert-light border">
                            No register yet for this date. Mark pupils below and save.
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                {{ \Carbon\Carbon::parse($date)->format('l, j F Y') }}
                            </h3>
                            <span class="badge badge-info" id="presentCount"></span>
                        </div>
                        <div class="card-body">
                            @if ($pupils->isEmpty())
                                <div class="alert alert-info mb-0">No pupils are enrolled in this class.</div>
                            @else
                                <form method="POST" action="{{ route('attendance.store') }}">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date }}">

                                    <div class="mb-3">
                                        <button type="button" class="btn btn-sm btn-success" id="markAllPresent">
                                            <i class="fas fa-check"></i> Mark all present
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="markAllAbsent">
                                            <i class="fas fa-times"></i> Mark all absent
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">#</th>
                                                    <th>Pupil</th>
                                                    <th style="width: 220px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pupils as $i => $pupil)
                                                    @php $saved = $existing[$pupil->id] ?? 'present'; @endphp
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td class="align-middle">
                                                            <strong>{{ $pupil->first_name }} {{ $pupil->last_name }}</strong>
                                                            @if ($pupil->middle_name)
                                                                <span class="text-muted">({{ $pupil->middle_name }})</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                                <label class="btn btn-sm btn-outline-success @if ($saved === 'present') active @endif">
                                                                    <input type="radio" name="status[{{ $pupil->id }}]" value="present"
                                                                           class="att-radio att-present" autocomplete="off"
                                                                           @if ($saved === 'present') checked @endif> Present
                                                                </label>
                                                                <label class="btn btn-sm btn-outline-danger @if ($saved === 'absent') active @endif">
                                                                    <input type="radio" name="status[{{ $pupil->id }}]" value="absent"
                                                                           class="att-radio att-absent" autocomplete="off"
                                                                           @if ($saved === 'absent') checked @endif> Absent
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save register</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
    (function () {
        var present = document.querySelectorAll('.att-present');
        var absent = document.querySelectorAll('.att-absent');
        var counter = document.getElementById('presentCount');

        if (!present.length) return;

        function setAll(setPresent) {
            present.forEach(function (r) { r.checked = setPresent; });
            absent.forEach(function (r) { r.checked = !setPresent; });
            syncLabels();
            updateCount();
        }

        function syncLabels() {
            document.querySelectorAll('.btn-group-toggle').forEach(function (group) {
                group.querySelectorAll('label').forEach(function (label) {
                    var input = label.querySelector('input');
                    label.classList.toggle('active', input && input.checked);
                });
            });
        }

        function updateCount() {
            var n = document.querySelectorAll('.att-present:checked').length;
            var total = present.length;
            counter.textContent = n + ' of ' + total + ' present';
            counter.classList.remove('badge-info', 'badge-success', 'badge-danger');
            counter.classList.add(n === total ? 'badge-success' : (n === 0 ? 'badge-danger' : 'badge-info'));
        }

        document.getElementById('markAllPresent').addEventListener('click', function () { setAll(true); });
        document.getElementById('markAllAbsent').addEventListener('click', function () { setAll(false); });
        document.querySelectorAll('.att-radio').forEach(function (r) {
            r.addEventListener('change', function () { syncLabels(); updateCount(); });
        });

        updateCount();
    })();
</script>
@endsection
