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
            ->where('status', 'successful')
            ->latest('date')
            ->get();

        $payouts = SchoolPayout::where('school_id', $schoolId)
            ->latest('paid_at')
            ->get();

        // Only real gateway collections are ever held by the platform — manual
        // (in-school) payments go straight to the school, so they're shown for
        // visibility but kept out of the "held" calculation below. See
        // PaymentTransaction::getIsGatewayCollectionAttribute() for why this
        // is based on the receipt number rather than payment_method.
        $collected       = $transactions->filter(fn ($t) => $t->is_gateway_collection)->sum('amount');
        $manualCollected = $transactions->sum('amount') - $collected;
        $paidOut         = $payouts->sum('amount');
        $held            = $collected - $paidOut;

        return view('reconciliation.index', compact('transactions', 'payouts', 'collected', 'manualCollected', 'paidOut', 'held'));
    }
}
