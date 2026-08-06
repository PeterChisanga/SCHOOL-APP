<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Jobs\SendResultsSmsJob;
use App\Services\ResultsFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultsController extends Controller
{
    public function sendResults(Request $request, ResultsFormatter $formatter)
    {
        // 1. Validate the selected term input from the modal
        $request->validate([
            'term' => 'required|string',
        ]);

        $term = $request->input('term');
        $schoolId = Auth::user()->school_id;

        // 2. Filter pupils and eager load examResults only for the selected term
        Pupil::where('school_id', $schoolId)
            ->whereHas('examResults', function ($query) use ($term) {
                $query->where('term', $term);
            })
            ->with([
                'parent',
                'examResults' => function ($query) use ($term) {
                    $query->where('term', $term)->with('subject');
                }
            ])
            ->chunk(100, function ($pupils) use ($formatter, $term) {
                foreach ($pupils as $pupil) {
                    $parent = $pupil->parent;

                    if (!$parent || empty($parent->phone)) {
                        continue;
                    }

                    // Pass the pupil (and optionally $term if your formatter accepts it)
                    $message = $formatter->formatResults($pupil, $term);

                    SendResultsSmsJob::dispatch($parent->phone, $message);
                }
            });

        $formattedTerm = ucfirst(str_replace('_', ' ', $term));

        return redirect()->back()->with('success', "All student results for {$formattedTerm} have been queued for SMS delivery.");
    }
}