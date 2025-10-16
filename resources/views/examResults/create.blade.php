@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Enter Exam Results</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Selection Form -->
    <form method="GET" action="{{ route('examResults.create') }}">
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="entry_type">Entry Type:</label>
                <select name="entry_type" id="entry_type" class="form-control" onchange="toggleInputs()">
                    <option value="bulk" {{ request('entry_type', 'bulk') == 'bulk' ? 'selected' : '' }}>Bulk (Class)</option>
                    <option value="single" {{ request('entry_type') == 'single' ? 'selected' : '' }}>Single Pupil</option>
                </select>
            </div>
            <div class="col-md-6">
                <div id="class_select" style="{{ request('entry_type', 'bulk') == 'bulk' ? '' : 'display: none;' }}">
                    <label for="class_id">Select Class:</label>
                    <select name="class_id" id="class_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="pupil_select" style="{{ request('entry_type') == 'single' ? '' : 'display: none;' }}">
                    <label for="pupil_id">Select Pupil:</label>
                    <select name="pupil_id" id="pupil_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select Pupil --</option>
                        @foreach($pupils as $pupil)
                            <option value="{{ $pupil->id }}" {{ $pupilId == $pupil->id ? 'selected' : '' }}>
                                {{ $pupil->first_name }} {{ $pupil->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </form>

    <!-- Results Entry Form -->
    @if((request('entry_type', 'bulk') == 'bulk' && $classPupils->isEmpty()) || (request('entry_type') == 'single' && empty($pupilId)))
        <p>No pupils found for the selected {{ request('entry_type', 'bulk') == 'bulk' ? 'class' : 'pupil' }}.</p>
    @else
    <form method="POST" action="{{ route('examResults.store') }}">
        @csrf
        <div class="form-group">
            <label for="subject_id">Select Subject:</label>
            <select name="subject_id" id="subject_id" class="form-control" required>
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="term">Term:</label>
            <select name="term" id="term" class="form-control" required>
                <option value="">-- Select Term --</option>
                <option value="term 1">Term 1 (First Term)</option>
                <option value="term 2">Term 2 (Second Term)</option>
                <option value="term 3">Term 3 (Third Term)</option>
            </select>
        </div>

        @if(request('entry_type', 'bulk') == 'single' && !empty($pupilId))
            @php
                $selectedPupil = $pupils->find($pupilId);
            @endphp
            <h4>Enter Results for {{ $selectedPupil->first_name }} {{ $selectedPupil->last_name }}</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pupil Name</th>
                        <th>Mid-Term Mark</th>
                        <th>End of Term Mark</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $selectedPupil->first_name }} {{ $selectedPupil->last_name }}</td>
                        <td>
                            <input type="number" name="single_pupil[mid_term_mark]" class="form-control" min="0" max="100">
                        </td>
                        <td>
                            <input type="number" name="single_pupil[end_of_term_mark]" class="form-control" min="0" max="100">
                        </td>
                        <td>
                            <input type="text" name="single_pupil[comments]" class="form-control" maxlength="255" placeholder="e.g., Absent, Sick, Changed school">
                            <input type="hidden" name="single_pupil[pupil_id]" value="{{ $pupilId }}">
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <h4>Enter Results for Class</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pupil Name</th>
                        <th>Mid-Term Mark</th>
                        <th>End of Term Mark</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classPupils as $pupil)
                        <tr>
                            <td>{{ $pupil->first_name }} {{ $pupil->last_name }}</td>
                            <td>
                                <input type="number" name="pupil_results[{{ $pupil->id }}][mid_term_mark]" class="form-control" min="0" max="100">
                            </td>
                            <td>
                                <input type="number" name="pupil_results[{{ $pupil->id }}][end_of_term_mark]" class="form-control" min="0" max="100">
                            </td>
                            <td>
                                <input type="text" name="pupil_results[{{ $pupil->id }}][comments]" class="form-control" maxlength="255" placeholder="e.g., Absent, Sick, Changed school">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ route('examResults.index') }}" class="btn btn-secondary mt-2">Back</a>
    </form>
    @endif
</div>

<script>
    function toggleInputs() {
        const entryType = document.getElementById('entry_type').value;
        document.getElementById('class_select').style.display = entryType === 'bulk' ? 'block' : 'none';
        document.getElementById('pupil_select').style.display = entryType === 'single' ? 'block' : 'none';
    }
</script>
@endsection
