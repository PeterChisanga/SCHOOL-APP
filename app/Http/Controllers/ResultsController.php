<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Models\ParentModel;
use App\Models\ExamResult;
use App\Jobs\SendResultsSmsJob;
use App\Services\ResultsFormatter;

class ResultsController extends Controller
{
    public function sendResults(ResultsFormatter $formatter)
    {
        $studentIds = ExamResult::select('pupil_id')->groupBy('pupil_id')->pluck('pupil_id');

        foreach ($studentIds as $studentId) {
          $student = Pupil::find($studentId);

            if (!$student) {
                continue;
            }
            $parents = ParentModel::where('pupil_id', $studentId)->get();

            if ($parents->isEmpty()) {
                continue;
            }

            $message = $formatter->formatResults($studentId);

            foreach ($parents as $parent) {
                dispatch(new SendResultsSmsJob(
                    $parent->phone,
                    $message
                ));
            }
        }

        return response()->json([
            'status'  => 'queued',
            'message' => 'All student results have been queued for SMS delivery.'
        ]);
    }
}
