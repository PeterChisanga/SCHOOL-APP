<?php

namespace App\Services;

use App\Models\Pupil;

class ResultsFormatter
{
    public function formatResults(Pupil $pupil, ?string $term = null): string
    {
        // Filter collection by term if passed, or use pre-loaded results
        $results = $term 
            ? $pupil->examResults->where('term', $term) 
            : $pupil->examResults;

        if ($results->isEmpty()) {
            return "No results available.";
        }

        // Format term string for header (e.g., 'term_1' -> 'Term 1')
        $activeTerm = $term ?? $results->first()?->term ?? 'current';
        $formattedTerm = ucwords(str_replace('_', ' ', $activeTerm));

        $message = "{$pupil->last_name} {$pupil->first_name} - {$formattedTerm} Results:\n";

        foreach ($results as $result) {
            $subject = $result->subject?->name ?? "Subject";
            $mid     = round($result->mid_term_mark) ?? '—';
            $end     = round($result->mid_term_mark) ?? '—';

            $message .= "{$subject}: Mid {$mid}%, End {$end}%\n";
        }

        //$message .= "\nTranscript: https://e-schoolzambia.site/results/{$pupil->id}/{$activeTerm}";

        $position = $this->classPosition($pupil, $activeTerm);
        if ($position !== null) {
            $message .= "\nPosition in class: {$position}";
        }

        return trim($message);
    }

    public function classPosition(Pupil $pupil, string $term): ?int
    {
        $classId = $pupil->class_id;
        if (!$classId) {
            return null;
        }

        $totals = \App\Models\ExamResult::whereHas('pupil', fn($q) => $q->where('class_id', $classId))
            ->where('term', $term)
            ->get()
            ->groupBy('pupil_id')
            ->map(function ($results) {
                $ends = $results->pluck('end_of_term_mark')->filter(fn($v) => $v !== null);
                return $ends->isNotEmpty() ? (int) round($ends-avg()) : null;
            })
            ->filter(fn($v) => $v !== null)
            ->sortDesc();

        $position = 1;
        $previousTotal = null;
        $skipPositions = 0;

        foreach ($totals as $pupilId => $total) {
            if ($previousTotal !== $total) {
                $position += $skipPositions;
                $skipPositions = 1;
            } else {
                $skipPositions++;
            }

            if ((int) $pupilId === (int) $pupil->id) {
                return $position;
            }

            $previousTotal = $total;
        }

        return null;
    
    }
}
