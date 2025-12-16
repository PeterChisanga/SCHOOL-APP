<?php
namespace App\Http\Controllers;

use App\Services\AfricasTalkingService;
use Illuminate\Http\Request;
class SmSController extends Controller

{
public function sendResults(Request $request, AfricasTalkingService $smsService)
{
    $parentNumber = $request->parent_number;
    $studentName  = $request->student_name;
    $result       = $request->result;

    $message = "Results for {$studentName}: {$result}";

    $response = $smsService->sendSms($parentNumber, $message);

    return response()->json($response);
}

}