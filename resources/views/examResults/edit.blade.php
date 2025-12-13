@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Exam Result</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('examResults.update', $examResult->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Pupil Name:</label>
            <input type="text" class="form-control" value="{{ $examResult->pupil->first_name }} {{ $examResult->pupil->last_name }}" disabled>
        </div>

        <div class="form-group">
            <label for="subject_id">Subject:</label>
            <select name="subject_id" id="subject_id" class="form-control" required>
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ $examResult->subject_id == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="term">Term:</label>
            <select name="term" id="term" class="form-control" required>
                <option value="">-- Select Term --</option>
                <option value="term 1" {{ $examResult->term == 'term 1' ? 'selected' : '' }}>Term 1 (First Term)</option>
                <option value="term 2" {{ $examResult->term == 'term 2' ? 'selected' : '' }}>Term 2 (Second Term)</option>
                <option value="term 3" {{ $examResult->term == 'term 3' ? 'selected' : '' }}>Term 3 (Third Term)</option>
            </select>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th colspan="3" class="text-center">Mid-Term</th>
                    <th colspan="3" class="text-center">End of Term</th>
                </tr>
                <tr>
                    @if(Auth::user()->isPremium())
                            <th>Mark</th>
                            <th>Total Mark</th>
                            <th>Percentage</th>
                            <th>Mark</th>
                            <th>Total Mark</th>
                            <th>Percentage</th>
                    @else
                        <th>Percentage</th>
                        <th>Percentage</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    @if(Auth::user()->isPremium())
                        <td>
                            <input type="number" name="mid_term_raw" id="mid_term_raw" class="form-control raw-mark" min="0" step="0.01" value="{{ $examResult->mid_term_raw ?? $examResult->mid_term_mark }}" required data-type="mid_term">
                        </td>
                        <td>
                            <input type="number" name="mid_term_max" id="mid_term_max" class="form-control max-mark" min="1" step="0.01" value="{{ $examResult->mid_term_max ?? 100 }}" required data-type="mid_term">
                            <input type="hidden" name="mid_term_mark" class="percentage-field" value="{{ $examResult->mid_term_mark }}" data-type="mid_term">
                        </td>
                        <td><span class="percentage-display" data-type="mid_term">{{ number_format($examResult->mid_term_mark ?? 0, 2) }}%</span></td>
                        <td>
                            <input type="number" name="end_term_raw" id="end_term_raw" class="form-control raw-mark" min="0" step="0.01" value="{{ $examResult->end_term_raw ?? $examResult->end_of_term_mark }}" required data-type="end_term">
                        </td>
                        <td>
                            <input type="number" name="end_term_max" id="end_term_max" class="form-control max-mark" min="1" step="0.01" value="{{ $examResult->end_term_max ?? 100 }}" required data-type="end_term">
                            <input type="hidden" name="end_of_term_mark" class="percentage-field" value="{{ $examResult->end_of_term_mark }}" data-type="end_term">
                        </td>
                        <td><span class="percentage-display" data-type="end_term">{{ number_format($examResult->end_of_term_mark ?? 0, 2) }}%</span></td>
                    @else
                        <td>
                            <input type="number" name="mid_term_mark" class="form-control" min="0" max="100" value="{{ $examResult->mid_term_mark }}" required>
                        </td>
                        <td>
                            <input type="number" name="end_of_term_mark" class="form-control" min="0" max="100" value="{{ $examResult->end_of_term_mark }}" required>
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>

        <div class="form-group">
            <label for="comments">Comments:</label>
            <select name="comments" id="comments" class="form-control" required>
                <option value="" {{ !$examResult->comments ? 'selected' : '' }} disabled>Select Comment</option>
                <option value="Excellent performance" {{ $examResult->comments == 'Excellent performance' ? 'selected' : '' }}>Excellent </option>
                <option value="Very good, keep it up" {{ $examResult->comments == 'Very good, keep it up' ? 'selected' : '' }}>Very good</option>
                <option value="Good effort" {{ $examResult->comments == 'Good effort' ? 'selected' : '' }}>Good effort</option>
                <option value="Needs improvement" {{ $examResult->comments == 'Needs improvement' ? 'selected' : '' }}>Needs improvement</option>
                <option value="Poor performance" {{ $examResult->comments == 'Poor performance' ? 'selected' : '' }}>Poor performance</option>
                <option value="Absent" {{ $examResult->comments == 'Absent' ? 'selected' : '' }}>Absent</option>
                <option value="Sick" {{ $examResult->comments == 'Sick' ? 'selected' : '' }}>Sick</option>
                <option value="Changed school" {{ $examResult->comments == 'Changed school' ? 'selected' : '' }}>Changed school</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Result</button>
        <a href="{{ route('examResults.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    @if(Auth::user()->isPremium())
    document.addEventListener('DOMContentLoaded', function () {
        const maxMarkInputs = document.querySelectorAll('.max-mark');
        const rawMarkInputs = document.querySelectorAll('.raw-mark');
        const percentageDisplays = document.querySelectorAll('.percentage-display');
        const percentageFields = document.querySelectorAll('.percentage-field');

        // Calculate and display percentage for a given raw mark input
        function calculatePercentage(input) {
            const type = input.dataset.type;
            const maxMarkInput = document.querySelector(`.max-mark[data-type="${type}"]`);
            const maxMark = parseFloat(maxMarkInput.value) || 1; // Prevent division by zero
            const rawMark = parseFloat(input.value) || 0;
            const percentage = Math.min((rawMark / maxMark) * 100, 100); // Cap at 100%

            // Update percentage display
            const display = document.querySelector(`.percentage-display[data-type="${type}"]`);
            if (display) {
                display.textContent = percentage.toFixed(2) + '%';
            }

            // Update hidden percentage field
            const percentageField = document.querySelector(`.percentage-field[data-type="${type}"]`);
            if (percentageField) {
                percentageField.value = percentage.toFixed(2);
            }
        }

        // Update max mark and recalculate percentages
        function updateMaxMark(input) {
            const type = input.dataset.type;
            const maxMark = parseFloat(input.value) || 1; // Prevent division by zero
            // Update max attribute of corresponding raw mark input
            const rawInput = document.querySelector(`.raw-mark[data-type="${type}"]`);
            if (rawInput) {
                rawInput.max = maxMark;
                calculatePercentage(rawInput);
            }
        }

        // Initialize percentages
        rawMarkInputs.forEach(input => calculatePercentage(input));

        // Event listeners
        maxMarkInputs.forEach(input => {
            input.addEventListener('input', () => updateMaxMark(input));
        });
        rawMarkInputs.forEach(input => {
            input.addEventListener('input', () => calculatePercentage(input));
        });
    });
    @endif
</script>
@endsection
