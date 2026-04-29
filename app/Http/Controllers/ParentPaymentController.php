<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use App\Models\Pupil;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\LipilaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\config\services;

class ParentPaymentController extends Controller
{
    public function searchPage()
    {
        return view('parents.search');
    }

    public function searchParent(Request $request)
    {
        $request->validate(['phone' => 'required']);

        $phone     = $request->phone;
        $formatted = $this->formatPhoneNumber($phone);

        $parent = ParentModel::where('phone', $phone)
                    ->orWhere('phone', $formatted)
                    ->first();

        if (!$parent) {
            return back()->with('error', 'No account found for that phone number. Please check and try again.');
        }

        $pupil = Pupil::find($parent->pupil_id);

        if (!$pupil) {
            return back()->with('error', 'No pupil linked to this account.');
        }

        session(['current_parent' => $parent]);

        return redirect()->route('parent.payments', ['pupilId' => $pupil->id]);
    }

    public function showPayments($pupilId)
    {
        $pupil    = Pupil::findOrFail($pupilId);
        $payments = Payment::where('pupil_id', $pupilId)->get();
        $parent   = session('current_parent');

        return view('parents.payments', compact('pupil', 'payments', 'parent'));
    }

    public function processPayment(Request $request, $paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            $validated = $request->validate([
                'amount_to_pay'  => 'required|numeric|min:0.01|max:' . $payment->balance,
                'payment_phone'  => 'required|min:10',
                'email'          => 'nullable|email',
            ]);

            $phone  = $this->formatPhoneNumber($request->payment_phone);
            $pupil  = $payment->pupil;
            $parent = session('current_parent');

            // Create a pending transaction immediately so we have a record
            $transaction = PaymentTransaction::create([
                'payment_id'      => $payment->id,
                'amount'          => floatval($validated['amount_to_pay']),
                'mode_of_payment' => 'MobileMoney',
                'date'            => now(),
                'lipila_status'   => 'Pending',
            ]);

            // Call Lipila
            $lipila = new LipilaService();
            $result = $lipila->collectMobileMoney([
                'amount'        => floatval($validated['amount_to_pay']),
                'narration'     => 'Payment for ' . $payment->type . ' - ' . $payment->term
                                    . ' (' . $pupil->first_name . ' ' . $pupil->last_name . ')',
                'accountNumber' => $phone,
                'email'         => $request->email ?? $parent->email ?? null,
            ]);

            // Update transaction with Lipila response
            $transaction->update([
                'lipila_reference_id'  => $result['referenceId'] ?? null,
                'lipila_status'        => $result['status'] ?? 'Pending',
                'lipila_payment_type'  => $result['paymentType'] ?? null,
                'lipila_message'       => $result['message'] ?? null,
            ]);

            // Store in session for status page
            session([
                'lipila_reference_id' => $result['referenceId'] ?? null,
                'original_payment_id' => $payment->id,
            ]);

            return redirect()->route('parent.payment.status');

        } catch (\Exception $e) {
            Log::error('ParentPaymentController@processPayment', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function checkPaymentStatus()
    {
        $referenceId       = session('lipila_reference_id');
        $originalPaymentId = session('original_payment_id');

        if (!$referenceId || !$originalPaymentId) {
            return redirect()->route('parent.search.page')->with('error', 'Payment session expired.');
        }

        $payment = Payment::find($originalPaymentId);

        return view('parents.paymentStatus', compact('referenceId', 'payment'));
    }

    /**
     * Called by the status page via AJAX to poll Lipila for the latest status.
     */
    public function getPaymentStatus(Request $request)
    {
        $request->validate(['payment_id' => 'required']);

        try {
            $lipila = new LipilaService();
            $result = $lipila->checkStatus($request->payment_id);

            // If Lipila confirms success, update our records
            if (($result['status'] ?? '') === 'Successful') {
                $transaction = PaymentTransaction::where('lipila_reference_id', $request->payment_id)->first();

                if ($transaction && $transaction->lipila_status !== 'Successful') {
                    $transaction->update([
                        'lipila_status'       => 'Successful',
                        'lipila_payment_type' => $result['paymentType'] ?? $transaction->lipila_payment_type,
                        'lipila_message'      => $result['message'] ?? null,
                    ]);

                    $payment = Payment::find($transaction->payment_id);
                    if ($payment) {
                        $payment->amount_paid += $transaction->amount;
                        $payment->balance      = max(0, $payment->amount - $payment->amount_paid);
                        $payment->save();
                    }
                }
            }

            // If failed, mark transaction
            if (($result['status'] ?? '') === 'Failed') {
                $transaction = PaymentTransaction::where('lipila_reference_id', $request->payment_id)->first();
                $transaction?->update([
                    'lipila_status'  => 'Failed',
                    'lipila_message' => $result['message'] ?? null,
                ]);
            }

            return response()->json(['success' => true, 'data' => $result]);

        } catch (\Exception $e) {
            Log::error('getPaymentStatus', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lipila webhook — called by Lipila server when payment is finalised.
     * This is the primary/reliable update path; getPaymentStatus() is the fallback.
     */
    public function lipilaWebhook(Request $request)
    {
        $rawBody   = $request->getContent();
        $webhookId = $request->header('webhook-id');
        $timestamp = $request->header('webhook-timestamp');
        $signature = $request->header('webhook-signature');

        // Verify signature
        $lipila = new LipilaService();
        if (!$lipila->verifyWebhookSignature($webhookId, $timestamp, $rawBody, $signature)) {
            Log::warning('Lipila webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Reject stale webhooks (> 5 minutes)
        if (abs(now()->timestamp - (int) $timestamp) > 300) {
            return response()->json(['error' => 'Webhook expired'], 400);
        }

        $data        = $request->json()->all();
        $referenceId = $data['referenceId'] ?? null;
        $status      = $data['status'] ?? null;

        if (!$referenceId || !$status) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $transaction = PaymentTransaction::where('lipila_reference_id', $referenceId)->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Idempotency — skip if already finalised
        if (in_array($transaction->lipila_status, ['Successful', 'Failed'])) {
            return response()->json(['message' => 'Already processed']);
        }

        $transaction->update([
            'lipila_status'       => $status,
            'lipila_payment_type' => $data['paymentType'] ?? $transaction->lipila_payment_type,
            'lipila_message'      => $data['message'] ?? null,
        ]);

        if ($status === 'Successful') {
            $payment = Payment::find($transaction->payment_id);
            if ($payment) {
                $payment->amount_paid += $transaction->amount;
                $payment->balance      = max(0, $payment->amount - $payment->amount_paid);
                $payment->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '260' . substr($phone, 1);   // Lipila uses 260XXXXXXXXX, not +260
        }

        if (str_starts_with($phone, '+')) {
            return ltrim($phone, '+');           // strip the + sign
        }

        return $phone;
    }
}