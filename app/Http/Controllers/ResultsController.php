<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Jobs\SendResultsSmsJob;
use App\Services\ResultsFormatter;

class ResultsController extends Controller
{

  public function sendResults($studentId, ResultsFormatter $formatter)
  {
    $student = Pupil::with('parents')->findOrFail($studentId);

    $message = $formatter->formatResults($studentId);

    foreach ($student->parents as $parent) {
      dispatch(new SendResultsSmsJob(
        $parent->phone,
        $message
      ));
    }

    return response()->json([
      'status'  => 'queued',
      'message' => 'Student results have been queued for SMS delivery.'
    ]);
  }
}
