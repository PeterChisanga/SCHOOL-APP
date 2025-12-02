<?php

namespace App\Services;

use App\Models\ExamResult;

class ResultsFormatter
{
    public function formatResults($studentId)
    {
        $results = ExamResult::with('subject')
            ->where('student_id', $studentId)
            ->get();

        if ($results->isEmpty()) {
            return "No results available.";
        }

        $message = "Results:\n";

        foreach ($results as $result) {
            $subject = $result->subject->name;

            $mid = $result->mid_term_mark . "/" . $result->mid_term_max;
            $end = $result->end_of_term_mark . "/" . $result->end_of_term_max;

            $message .= "{$subject}: Mid {$mid}, Final {$end}\n";
        }

        return $message;
    }
}
