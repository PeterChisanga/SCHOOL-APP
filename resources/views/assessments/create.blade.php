@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Assessment</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Selection Form --}}
    <form method="GET" action="{{ route('assessments.create') }}">
        <div class="row mb-4">
            <div class="col-md-6">
                <label>Entry Type:</label>
                <select name="entry_type" id="entry_type" class="form-control" onchange="toggleInputs()">
                    <option value="bulk" {{ request('entry_type', 'bulk') == 'bulk' ? 'selected' : '' }}>Bulk (Class)</option>
                    <option value="single" {{ request('entry_type') == 'single' ? 'selected' : '' }}>Single Pupil</option>
                </select>
            </div>
            <div class="col-md-6">
                <div id="class_select" style="{{ request('entry_type', 'bulk') == 'bulk' ? '' : 'display:none;' }}">
                    <label>Select Class:</label>
                    <select name="class_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="pupil_select" style="{{ request('entry_type') == 'single' ? '' : 'display:none;' }}">
                    <label>Select Pupil:</label>
                    <select name="pupil_id" class="form-control" onchange="this.form.submit()">
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

    @if((request('entry_type', 'bulk') == 'bulk' && $classPupils->isEmpty()) || (request('entry_type') == 'single' && empty($pupilId)))
        <p>Please select a {{ request('entry_type', 'bulk') == 'bulk' ? 'class' : 'pupil' }} to continue.</p>
    @else
    <form method="POST" action="{{ route('assessments.store') }}">
        @csrf
        <input type="hidden" name="entry_type" value="{{ request('entry_type', 'bulk') }}">

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Assessment Title:</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                    placeholder="e.g. Week 3 Quiz, Monthly Test - October" required>
            </div>
            <div class="col-md-6">
                <label>Assessment Date:</label>
                <input type="date" name="assessment_date" class="form-control"
                    value="{{ old('assessment_date', now()->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Subject:</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">-- Select Subject --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label>Term:</label>
                <select name="term" class="form-control" required>
                    <option value="">-- Select Term --</option>
                    <option value="term 1" {{ old('term') == 'term 1' ? 'selected' : '' }}>Term 1</option>
                    <option value="term 2" {{ old('term') == 'term 2' ? 'selected' : '' }}>Term 2</option>
                    <option value="term 3" {{ old('term') == 'term 3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
        </div>

        {{-- Single Pupil Entry --}}
        @if(request('entry_type') == 'single' && !empty($pupilId))
            @php $selectedPupil = $pupils->find($pupilId); @endphp
            <h4>Assessment for {{ $selectedPupil->first_name }} {{ $selectedPupil->last_name }}</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pupil</th>
                        @if(Auth::user()->isPremium())
                            <th>Mark</th>
                            <th>Out Of</th>
                            <th>Percentage</th>
                        @else
                            <th>Percentage (%)</th>
                        @endif
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $selectedPupil->first_name }} {{ $selectedPupil->last_name }}</td>
                        <input type="hidden" name="single_pupil[pupil_id]" value="{{ $pupilId }}">
                        @if(Auth::user()->isPremium())
                            <td>
                                <input type="number" name="single_pupil[raw_mark]"
                                    class="form-control raw-mark" min="0" step="0.01"
                                    data-type="single" required>
                            </td>
                            <td>
                                <input type="number" name="single_pupil[max_mark]"
                                    class="form-control max-mark" min="1" step="0.01"
                                    data-type="single" required>
                            </td>
                            <td><span class="percentage-display" data-type="single">0.00%</span></td>
                        @else
                            <td>
                                <input type="number" name="single_pupil[percentage]"
                                    class="form-control" min="0" max="100" step="0.01" required>
                            </td>
                        @endif
                        <td>
                            <select name="single_pupil[comments]" class="form-control">
                                <option value="" disabled selected>Comment</option>
                                <option value="Excellent performance">Excellent</option>
                                <option value="Very good, keep it up">Very good</option>
                                <option value="Good effort">Good effort</option>
                                <option value="Needs improvement">Needs improvement</option>
                                <option value="Poor performance">Poor performance</option>
                                <option value="Absent">Absent</option>
                                <option value="Sick">Sick</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

        {{-- Bulk Entry --}}
        @else
            <h4>Assessment for Class</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pupil</th>
                        @if(Auth::user()->isPremium())
                            <th>Mark</th>
                            <th>Out Of</th>
                            <th>Percentage</th>
                        @else
                            <th>Percentage (%)</th>
                        @endif
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classPupils as $pupil)
                        <tr>
                            <td>{{ $pupil->first_name }} {{ $pupil->last_name }}</td>
                            @if(Auth::user()->isPremium())
                                <td>
                                    <input type="number"
                                        name="pupil_results[{{ $pupil->id }}][raw_mark]"
                                        class="form-control raw-mark" min="0" step="0.01"
                                        data-type="pupil_{{ $pupil->id }}" required>
                                </td>
                                <td>
                                    <input type="number"
                                        name="pupil_results[{{ $pupil->id }}][max_mark]"
                                        class="form-control max-mark" min="1" step="0.01"
                                        data-type="pupil_{{ $pupil->id }}" required>
                                </td>
                                <td>
                                    <span class="percentage-display" data-type="pupil_{{ $pupil->id }}">0.00%</span>
                                </td>
                            @else
                                <td>
                                    <input type="number"
                                        name="pupil_results[{{ $pupil->id }}][percentage]"
                                        class="form-control" min="0" max="100" step="0.01" required>
                                </td>
                            @endif
                            <td>
                                <select name="pupil_results[{{ $pupil->id }}][comments]" class="form-control">
                                    <option value="" disabled selected>Comment</option>
                                    <option value="Excellent performance">Excellent</option>
                                    <option value="Very good, keep it up">Very good</option>
                                    <option value="Good effort">Good effort</option>
                                    <option value="Needs improvement">Needs improvement</option>
                                    <option value="Poor performance">Poor performance</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Sick">Sick</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <button type="submit" class="btn btn-primary">Save Assessment</button>
        <a href="{{ route('assessments.index') }}" class="btn btn-secondary">Back</a>
    </form>
    @endif
</div>

<script>
    function toggleInputs() {
        const type = document.getElementById('entry_type').value;
        document.getElementById('class_select').style.display = type === 'bulk' ? 'block' : 'none';
        document.getElementById('pupil_select').style.display = type === 'single' ? 'block' : 'none';
    }

    @if(Auth::user()->isPremium())
    document.addEventListener('DOMContentLoaded', function () {
        function calculatePercentage(type) {
            const raw = parseFloat(document.querySelector(`.raw-mark[data-type="${type}"]`)?.value) || 0;
            const max = parseFloat(document.querySelector(`.max-mark[data-type="${type}"]`)?.value) || 1;
            const pct = Math.min((raw / max) * 100, 100);
            const display = document.querySelector(`.percentage-display[data-type="${type}"]`);
            if (display) display.textContent = pct.toFixed(2) + '%';
        }

        document.querySelectorAll('.raw-mark, .max-mark').forEach(input => {
            input.addEventListener('input', () => calculatePercentage(input.dataset.type));
        });
    });
    @endif
</script>
@endsection
