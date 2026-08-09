<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\School;
use App\Models\SchoolPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReconciliationController extends Controller
{
    public function index()
    {
        $schools = School::orderBy('name')->get()->map(function ($school) {
            $collected = $this->totalCollected($school->id);
            $paidOut   = $this->totalPaidOut($school->id);

            return [
                'school'    => $school,
                'collected' => $collected,
                'paid_out'  => $paidOut,
                'held'      => $collected - $paidOut,
            ];
        });

        return view('admin.reconciliation.index', compact('schools'));
    }

    public function show(School $school)
    {
        $transactions = PaymentTransaction::with('payment.pupil')
            ->whereHas('payment', fn ($q) => $q->where('school_id', $school->id))
            ->where('payment_method', 'mobile_money')
            ->where('status', 'successful')
            ->latest('date')
            ->get();

        $payouts = SchoolPayout::where('school_id', $school->id)
            ->with('paidBy')
            ->latest('paid_at')
            ->get();

        $collected = $transactions->sum('amount');
        $paidOut   = $payouts->sum('amount');
        $held      = $collected - $paidOut;

        return view('admin.reconciliation.show', compact('school', 'transactions', 'payouts', 'collected', 'paidOut', 'held'));
    }

    public function storePayout(Request $request, School $school)
    {
        $validated = $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:150',
            'notes'     => 'nullable|string|max:255',
            'paid_at'   => 'required|date',
        ]);

        SchoolPayout::create([
            'school_id' => $school->id,
            'amount'    => $validated['amount'],
            'reference' => $validated['reference'] ?? null,
            'notes'     => $validated['notes'] ?? null,
            'paid_by'   => Auth::id(),
            'paid_at'   => $validated['paid_at'],
        ]);

        return back()->with('success', 'Payout recorded.');
    }

    private function totalCollected(int $schoolId): float
    {
        return (float) PaymentTransaction::whereHas('payment', fn ($q) => $q->where('school_id', $schoolId))
            ->where('payment_method', 'mobile_money')
            ->where('status', 'successful')
            ->sum('amount');
    }

    private function totalPaidOut(int $schoolId): float
    {
        return (float) SchoolPayout::where('school_id', $schoolId)->sum('amount');
    }
}
