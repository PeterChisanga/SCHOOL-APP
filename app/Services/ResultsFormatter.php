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
            $mid     = $result->mid_term_mark ?? '—';
            $end     = $result->end_of_term_mark ?? '—';

            $message .= "{$subject}: Mid {$mid}%, End {$end}%\n";
        }

        //$message .= "\nTranscript: https://e-schoolzambia.site/results/{$pupil->id}/{$activeTerm}";

        return trim($message);
    }
}