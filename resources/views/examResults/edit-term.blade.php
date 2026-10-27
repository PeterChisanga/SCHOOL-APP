@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-3">Edit Results — {{ $pupil->first_name }} {{ $pupil->last_name }} ({{ ucwords($term) }})</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('examResults.update-term', ['pupil' => $pupil->id, 'term' => $term]) }}">
        @csrf
        @method('PUT')

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    @if (Auth::user()->isPremium())
                        <tr>
                            <th rowspan="2">Subject</th>
                            <th colspan="3" class="text-center">Mid-Term</th>
                            <th colspan="3" class="text-center">End of Term</th>
                            <th rowspan="2">Comment</th>
                        </tr>
                        <tr>
                            <th>Mark</th>
                            <th>Total</th>
                            <th>%</th>
                            <th>Mark</th>
                            <th>Total</th>
                            <th>%</th>
                        </tr>
                    @else
                        <tr>
                            <th>Subject</th>
                            <th>Mid-Term Mark</th>
                            <th>End of Term Mark</th>
                            <th>Comment</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach($subjects as $subject)
                        @php $r = $results[$subject->id] ?? null; @endphp
                        <tr>
                            <td>{{ $subject->name }}</td>

                            @if (Auth::user()->isPremium())
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control raw-mark"
                                           data-type="mid_{{ $subject->id }}"
                                           name="results[{{ $subject->id }}][mid_term_raw]"
                                           value="{{ $r?->mid_term_raw ?? $r?->mid_term_mark ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="1" class="form-control max-mark"
                                           data-type="mid_{{ $subject->id }}"
                                           name="results[{{ $subject->id }}][mid_term_max]"
                                           value="{{ $r?->mid_term_max ?? 100 }}">
                                </td>
                                <td><span class="percentage-display" data-type="mid_{{ $subject->id }}">{{ $r && $r->mid_term_mark !== null ? number_format($r->mid_term_mark, 2).'%' : '—' }}</span></td>

                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control raw-mark"
                                           data-type="end_{{ $subject->id }}"
                                           name="results[{{ $subject->id }}][end_term_raw]"
                                           value="{{ $r?->end_term_raw ?? $r?->end_of_term_mark ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="1" class="form-control max-mark"
                                           data-type="end_{{ $subject->id }}"
                                           name="results[{{ $subject->id }}][end_term_max]"
                                           value="{{ $r?->end_term_max ?? 100 }}">
                                </td>
                                <td><span class="percentage-display" data-type="end_{{ $subject->id }}">{{ $r && $r->end_of_term_mark !== null ? number_format($r->end_of_term_mark, 2).'%' : '—' }}</span></td>
                            @else
                                <td>
                                    <input type="number" min="0" max="100" class="form-control"
                                           name="results[{{ $subject->id }}][mid_term_mark]"
                                           value="{{ $r?->mid_term_mark ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" min="0" max="100" class="form-control"
                                           name="results[{{ $subject->id }}][end_of_term_mark]"
                                           value="{{ $r?->end_of_term_mark ?? '' }}">
                                </td>
                            @endif

                            <td>
                                <select name="results[{{ $subject->id }}][comments]" class="form-control">
                                    <option value="" {{ !$r?->comments ? 'selected' : '' }}>—</option>
                                    <option value="Excellent performance" {{ $r?->comments == 'Excellent performance' ? 'selected' : '' }}>Excellent</option>
                                    <option value="Very good, keep it up" {{ $r?->comments == 'Very good, keep it up' ? 'selected' : '' }}>Very good</option>
                                    <option value="Good effort" {{ $r?->comments == 'Good effort' ? 'selected' : '' }}>Good effort</option>
                                    <option value="Needs improvement" {{ $r?->comments == 'Needs improvement' ? 'selected' : '' }}>Needs improvement</option>
                                    <option value="Poor performance" {{ $r?->comments == 'Poor performance' ? 'selected' : '' }}>Poor performance</option>
                                    <option value="Absent" {{ $r?->comments == 'Absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="Sick" {{ $r?->comments == 'Sick' ? 'selected' : '' }}>Sick</option>
                                    <option value="Changed school" {{ $r?->comments == 'Changed school' ? 'selected' : '' }}>Changed school</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-muted small">Leave a subject blank to skip or remove it. You can enter Mid-Term, End-of-Term, or both.</p>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('examResults.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    @if (Auth::user()->isPremium())
    document.addEventListener('DOMContentLoaded', function () {
        const rawMarkInputs = document.querySelectorAll('.raw-mark');
        const maxMarkInputs = document.querySelectorAll('.max-mark');

        function calculate(input) {
            const type = input.dataset.type;
            const maxInput = document.querySelector('.max-mark[data-type="' + type + '"]');
            const display = document.querySelector('.percentage-display[data-type="' + type + '"]');
            if (!maxInput || !display) return;

            const raw = parseFloat(input.value);
            const max = parseFloat(maxInput.value);

            if (isNaN(raw) || isNaN(max) || max <= 0) {
                display.textContent = '—';
                return;
            }
            display.textContent = Math.min((raw / max) * 100, 100).toFixed(2) + '%';
        }

        rawMarkInputs.forEach(function (input) {
            input.addEventListener('input', function () { calculate(input); });
        });

        maxMarkInputs.forEach(function (input) {
            input.addEventListener('input', function () {
                const type = input.dataset.type;
                const raw = document.querySelector('.raw-mark[data-type="' + type + '"]');
                if (raw) calculate(raw);
            });
        });
    });
    @endif
</script>
@endsection
