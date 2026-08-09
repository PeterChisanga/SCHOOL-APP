<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\SchoolPayout;
use Illuminate\Support\Facades\Auth;

class ReconciliationController extends Controller
{
    public function index()
    {
        $schoolId = $this->currentSchoolId();

        $transactions = PaymentTransaction::with('payment.pupil')
            ->whereHas('payment', fn ($q) => $q->where('school_id', $schoolId))
            ->where('payment_method', 'mobile_money')
            ->where('status', 'successful')
            ->latest('date')
            ->get();

        $payouts = SchoolPayout::where('school_id', $schoolId)
            ->latest('paid_at')
            ->get();

        $collected = $transactions->sum('amount');
        $paidOut   = $payouts->sum('amount');
        $held      = $collected - $paidOut;

        return view('reconciliation.index', compact('transactions', 'payouts', 'collected', 'paidOut', 'held'));
    }
}
