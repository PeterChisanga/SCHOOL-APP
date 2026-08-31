<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentDetail;
use App\Models\School;
use Illuminate\Http\Request;

class PaymentDetailController extends Controller
{
    /**
     * Fetch payment details for a given school.
     * GET /admin/schools/{schoolId}/payment-details
     */
    public function show($schoolId)
    {
        $school        = School::findOrFail($schoolId);
        $paymentDetail = PaymentDetail::where('school_id', $schoolId)->first();

        return response()->json([
            'school'         => $school->only('id', 'name'),
            'payment_detail' => $paymentDetail,
        ]);
    }

    /**
     * Show the edit form (create or update) for a school's payment details.
     * GET /admin/schools/{schoolId}/payment-details/edit
     */
    public function edit($schoolId)
    {
        $school        = School::findOrFail($schoolId);
        $paymentDetail = PaymentDetail::where('school_id', $schoolId)->first();

        return view('admin.paymentDetails.edit', compact('school', 'paymentDetail'));
    }

    /**
     * Create or update (upsert) a school's payment details.
     * POST /admin/schools/{schoolId}/payment-details
     */
    public function upsert(Request $request, $schoolId)
    {
        $school = School::findOrFail($schoolId);

        $validated = $request->validate([
            'bank_name'                  => 'nullable|string|max:150',
            'bank_account_name'          => 'nullable|string|max:150',
            'bank_account_number'        => 'nullable|string|max:100',
            'bank_branch'                => 'nullable|string|max:150',
            'bank_swift_code'            => 'nullable|string|max:50',
            'mobile_money_provider'      => 'nullable|string|max:100',
            'mobile_money_number'        => 'nullable|string|max:50',
            'mobile_money_account_name'  => 'nullable|string|max:150',
            'payment_instructions'       => 'nullable|string|max:2000',
        ]);

        $paymentDetail = PaymentDetail::updateOrCreate(
            ['school_id' => $school->id],
            $validated
        );

        return back()->with('success', 'Payment details saved for ' . $school->name . '.');
    }
}