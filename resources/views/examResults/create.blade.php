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
                    @if (Auth::user()->isPremium())
                        <tr>
                            <th colspan="3" class="text-center">Mid-Term</th>
                            <th colspan="3" class="text-center">End of Term</th>
                        </tr>
                    @endif
                    <tr>
                        <th>Pupil Name</th>
                        @if (Auth::user()->isPremium())
                            <th>Mark</th>
                            <th>Total Mark</th>
                            <th>Percentage</th>
                            <th>Mark</th>
                            <th>Total Mark</th>
                            <th>Percentage</th>
                        @else
                            <th>Mid-Term Mark</th>
                            <th>End of Term Mark</th>
                        @endif
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $selectedPupil->first_name }} {{ $selectedPupil->last_name }}</td>
                        @if (Auth::user()->isPremium())
                            <td>
                                <input type="number" name="single_pupil[mid_term_raw]" class="form-control raw-mark" min="0" step="0.01" required data-type="mid_term">
                            </td>
                            <td>
                                <input type="number" name="single_pupil[mid_term_max]" class="form-control max-mark" min="1" step="0.01" required data-type="mid_term">
                                <input type="hidden" name="single_pupil[mid_term_mark]" class="percentage-field" value="" data-type="mid_term">
                            </td>
                            <td><span class="percentage-display" data-type="mid_term">0.00%</span></td>
                            <td>
                                <input type="number" name="single_pupil[end_term_raw]" class="form-control raw-mark" min="0" step="0.01" required data-type="end_term">
                            </td>
                            <td>
                                <input type="number" name="single_pupil[end_term_max]" class="form-control max-mark" min="1" step="0.01" required data-type="end_term">
                                <input type="hidden" name="single_pupil[end_of_term_mark]" class="percentage-field" value="" data-type="end_term">
                            </td>
                            <td><span class="percentage-display" data-type="end_term">0.00%</span></td>
                        @else
                            <td>
                                <input type="number" name="single_pupil[mid_term_mark]" class="form-control" min="0" max="100" required>
                            </td>
                            <td>
                                <input type="number" name="single_pupil[end_of_term_mark]" class="form-control" min="0" max="100" required>
                            </td>
                        @endif
                        <td>
                            <select name="single_pupil[comments]" class="form-control" >
                                <option value="" selected disabled>Select Comment</option>
                                <option value="Excellent performance">Excellent </option>
                                <option value="Very good, keep it up">Very good</option>
                                <option value="Good effort">Good effort</option>
                                <option value="Needs improvement">Needs improvement</option>
                                <option value="Poor performance">Poor performance</option>
                                <option value="Absent">Absent</option>
                                <option value="Sick">Sick</option>
                                <option value="Changed school">Changed school</option>
                            </select>
                            <input type="hidden" name="single_pupil[pupil_id]" value="{{ $pupilId }}">
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <h4>Enter Results for Class</h4>
            <table class="table">
                <thead>
                    @if (Auth::user()->isPremium())
                        <tr>
                            <th colspan="3" class="text-center">Mid-Term</th>
                            <th colspan="3" class="text-center">End of Term</th>
                        </tr>
                    @endif
                    <tr>
                        <th>Pupil Name</th>
                        @if (Auth::user()->isPremium())
                            <th>Mark</th>
                            <th>Total Mark</th>
                            <th>Percentage</th>
                            <th>Mark</th>
                            <th>Total Mark</th>
                            <th>Percentage</th>
                        @else
                            <th>Mid-Term Mark</th>
                            <th>End of Term Mark</th>
                        @endif
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classPupils as $pupil)
                        <tr>
                            <td>{{ $pupil->first_name }} {{ $pupil->last_name }}</td>
                            @if (Auth::user()->isPremium())
                                <td>
                                    <input type="number" name="pupil_results[{{ $pupil->id }}][mid_term_raw]" class="form-control raw-mark" min="0" step="0.01" required data-type="mid_term_{{ $pupil->id }}">
                                </td>
                                <td>
                                    <input type="number" name="pupil_results[{{ $pupil->id }}][mid_term_max]" class="form-control max-mark" min="1" step="0.01" required data-type="mid_term_{{ $pupil->id }}">
                                    <input type="hidden" name="pupil_results[{{ $pupil->id }}][mid_term_mark]" class="percentage-field" value="" data-type="mid_term_{{ $pupil->id }}">
                                </td>
                                <td><span class="percentage-display" data-type="mid_term_{{ $pupil->id }}">0.00%</span></td>
                                <td>
                                    <input type="number" name="pupil_results[{{ $pupil->id }}][end_term_raw]" class="form-control raw-mark" min="0" step="0.01" required data-type="end_term_{{ $pupil->id }}">
                                </td>
                                <td>
                                    <input type="number" name="pupil_results[{{ $pupil->id }}][end_term_max]" class="form-control max-mark" min="1" step="0.01" required data-type="end_term_{{ $pupil->id }}">
                                    <input type="hidden" name="pupil_results[{{ $pupil->id }}][end_of_term_mark]" class="percentage-field" value="" data-type="end_term_{{ $pupil->id }}">
                                </td>
                                <td><span class="percentage-display" data-type="end_term_{{ $pupil->id }}">0.00%</span></td>
                            @else
                                <td>
                                    <input type="number" name="pupil_results[{{ $pupil->id }}][mid_term_mark]" class="form-control" min="0" max="100" required>
                                </td>
                                <td>
                                    <input type="number" name="pupil_results[{{ $pupil->id }}][end_of_term_mark]" class="form-control" min="0" max="100" required>
                                </td>
                            @endif
                            <td>
                                <select name="pupil_results[{{ $pupil->id }}][comments]" class="form-control" >
                                    <option value="" selected disabled> Comment</option>
                                    <option value="Excellent performance">Excellent </option>
                                    <option value="Very good, keep it up">Very good</option>
                                    <option value="Good effort">Good effort</option>
                                    <option value="Needs improvement">Needs improvement</option>
                                    <option value="Poor performance">Poor performance</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Sick">Sick</option>
                                    <option value="Changed school">Changed school</option>
                                </select>
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

    @if (Auth::user()->isPremium())
    document.addEventListener('DOMContentLoaded', function () {
        const maxMarkInputs = document.querySelectorAll('.max-mark');
        const rawMarkInputs = document.querySelectorAll('.raw-mark');
        const percentageDisplays = document.querySelectorAll('.percentage-display');
        const percentageFields = document.querySelectorAll('.percentage-field');

        function calculatePercentage(input) {
            const type = input.dataset.type;
            const maxMarkInput = document.querySelector(`.max-mark[data-type="${type}"]`);
            const maxMark = parseFloat(maxMarkInput.value) || 1;
            const rawMark = parseFloat(input.value) || 0;
            const percentage = Math.min((rawMark / maxMark) * 100, 100);

            const display = document.querySelector(`.percentage-display[data-type="${type}"]`);
            if (display) display.textContent = percentage.toFixed(2) + '%';

            const percentageField = document.querySelector(`.percentage-field[data-type="${type}"]`);
            if (percentageField) percentageField.value = percentage.toFixed(2);
        }

        function updateMaxMark(input) {
            const type = input.dataset.type;
            const maxMark = parseFloat(input.value) || 1;
            const rawInput = document.querySelector(`.raw-mark[data-type="${type}"]`);
            if (rawInput) {
                rawInput.max = maxMark;
                calculatePercentage(rawInput);
            }
        }

        rawMarkInputs.forEach(input => calculatePercentage(input));
        maxMarkInputs.forEach(input => input.addEventListener('input', () => updateMaxMark(input)));
        rawMarkInputs.forEach(input => input.addEventListener('input', () => calculatePercentage(input)));
    });
    @endif
</script>
@endsection
