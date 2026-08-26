<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPaymentVerificationController extends Controller
{
    /**
     * List manual payments awaiting review. Optionally filter by ?school_id=
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::where('payment_method', 'manual')
            ->where('status', 'pending')
            ->with(['payment.pupil.school', 'school']);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $pendingTransactions = $query->latest()->get();

        return view('admin.paymentVerification.index', compact('pendingTransactions'));
    }

    public function approve($transactionId)
    {
        $transaction = PaymentTransaction::findOrFail($transactionId);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'This transaction has already been processed.');
        }

        $payment = Payment::find($transaction->payment_id);

        if ($payment && $payment->amount_paid < $payment->amount) {
            $payment->amount_paid += $transaction->amount;
            $payment->balance      = max(0, $payment->amount - $payment->amount_paid);
            $payment->save();
        }

        $transaction->update([
            'status'      => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        Log::info('Manual payment approved', ['transaction_id' => $transaction->id]);

        return back()->with('success', 'Payment approved and applied to the balance.');
    }

    public function reject(Request $request, $transactionId)
    {
        $request->validate(['rejection_reason' => 'required|string|max:255']);

        $transaction = PaymentTransaction::findOrFail($transactionId);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'This transaction has already been processed.');
        }

        $transaction->update([
            'status'           => 'rejected',
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        Log::info('Manual payment rejected', ['transaction_id' => $transaction->id]);

        return back()->with('success', 'Payment rejected.');
    }
}