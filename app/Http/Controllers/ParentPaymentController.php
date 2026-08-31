<?php

namespace App\Http\Controllers;

use App\Models\OtpVerification;
use App\Models\ParentModel;
use App\Models\Pupil;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\PaymentTransaction;
use App\Services\LencoService;
use App\Services\AfricasTalkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParentPaymentController extends Controller
{
    // =========================================================================
    // SEARCH parents
    // =========================================================================

    public function searchPage()
    {
        return view('parents.search');
    }

    public function searchParent(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

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

        // Invalidate any previous unused OTPs for this number
        OtpVerification::where('phone', $formatted)
            ->where('used', false)
            ->update(['used' => true]);

        // Generate & store OTP (hashed)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::create([
            'phone'      => $formatted,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
            'used'       => false,
        ]);

        try {
            (new AfricasTalkingService())->sendSms(
                $formatted,
                "Your verification code is: {$otp}. It expires in 10 minutes. Do not share it."
            );
        } catch (\Exception $e) {
            Log::error('OTP SMS send failed', ['phone' => $formatted, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send verification code. Please try again.');
        }

        session([
            'otp_phone'    => $formatted,
            'otp_pupil_id' => $pupil->id,
            'otp_verified' => false,
        ]);

        return redirect()->route('parent.otp.page');
    }

    // =========================================================================
    // OTP
    // =========================================================================

    public function otpPage()
    {
        if (!session('otp_phone')) {
            return redirect()->route('parent.search.page')
                ->with('error', 'Session expired. Please search again.');
        }

        return view('parents.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $phone = session('otp_phone');

        if (!$phone) {
            return redirect()->route('parent.search.page')
                ->with('error', 'Session expired. Please search again.');
        }

      $record = OtpVerification::where('phone', $phone)
            ->where('otp', $request->otp)
            ->where('used', false)
            ->first();

        if (!$record) {
            return back()->with('error', 'Invalid or expired code. Please try again.');
        }

        $record->update(['used' => true]);

        $parent = ParentModel::where('phone', $phone)->first();

        session([
            'current_parent'     => $parent,
            'otp_verified'       => true,
            'otp_verified_phone' => $phone,
        ]);

        return redirect()->route('parent.payments', ['pupilId' => session('otp_pupil_id')]);
    }

    public function resendOtp()
    {
        $phone = session('otp_phone');

        if (!$phone) {
            return redirect()->route('parent.search.page');
        }

        $tooSoon = OtpVerification::where('phone', $phone)
                    ->where('used', false)
                    ->where('created_at', '>=', now()->subMinute())
                    ->exists();

        if ($tooSoon) {
            return back()->with('error', 'Please wait at least 60 seconds before requesting a new code.');
        }

        OtpVerification::where('phone', $phone)
            ->where('used', false)
            ->update(['used' => true]);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::create([
            'phone'      => $phone,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
            'used'       => false,
        ]);

        try {
            (new AfricasTalkingService())->sendSms(
                $phone,
                "Your new verification code is: {$otp}. It expires in 10 minutes."
            );
        } catch (\Exception $e) {
            Log::error('OTP resend failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to resend code. Please try again.');
        }

        return back()->with('success', 'A new verification code has been sent.');
    }

    public function showResults($pupilId) {
        $pupil = Pupil::findOrFail($pupilId);
        $parent = session('current_parent');

        $terms = $pupil->examResults->pluck('term')->unique();

        // Calculate position in class for each term
        $positions = [];
        $classId = $pupil->class_id;
        foreach ($terms as $term) {
            $classResults = ExamResult::whereHas('pupil', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })->where('term', $term)->get();

            $pupilTotals = $classResults->groupBy('pupil_id')->map(function ($pupilResults) {
                $total = $pupilResults->sum(function ($result) {
                    return ($result->mid_term_mark + $result->end_of_term_mark) / 2;
                });
                return [
                    'pupil_id' => $pupilResults->first()->pupil_id,
                    'total' => $total,
                ];
            })->sortByDesc('total')->values();

            $currentPosition = 1;
            $previousTotal = null;
            $skipPositions = 0;
            foreach ($pupilTotals as $index => $pupilData) {
                if ($previousTotal !== $pupilData['total']) {
                    $currentPosition += $skipPositions;
                    $skipPositions = 1;
                } else {
                    $skipPositions++;
                }
                if ($pupilData['pupil_id'] == $pupil->id) {
                    $positions[$term] = $currentPosition;
                    break;
                }
                $previousTotal = $pupilData['total'];
            }
            if (!isset($positions[$term])) {
                $positions[$term] = '-';
            }
        }

        return view('parents.results', compact('pupil', 'terms', 'positions'));
    }

    // =========================================================================
    // PAYMENTS (overview / choice of method)
    // =========================================================================

    public function showPayments($pupilId)
    {
        if (!session('otp_verified')) {
            return redirect()->route('parent.search.page')
                ->with('error', 'Please verify your phone number first.');
        }

        $pupil    = Pupil::findOrFail($pupilId);
        $payments = Payment::where('pupil_id', $pupilId)->get();
        $parent   = session('current_parent');

        return view('parents.payments', compact('pupil', 'payments', 'parent'));
    }

    // =========================================================================
    // MOBILE MONEY (LENCO) — unchanged
    // =========================================================================

    public function processPayment(Request $request, $paymentId)
    {
        if (!session('otp_verified')) {
            return redirect()->route('parent.search.page')
                ->with('error', 'Please verify your phone number first.');
        }

        try {
            $payment = Payment::findOrFail($paymentId);

            $validated = $request->validate([
                'amount_to_pay' => 'required|numeric|min:0.01|max:' . $payment->balance,
                'payment_phone' => 'required|string',
                'operator'      => 'required|in:airtel,mtn,zamtel',
            ]);

            $parent    = session('current_parent');
            $reference = 'PAY-' . strtoupper(Str::random(12));

            // Create pending transaction
            $transaction = PaymentTransaction::create([
                'payment_id'      => $payment->id,
                'amount'          => floatval($validated['amount_to_pay']),
                'mode_of_payment' => 'Mobile Money',
                'payment_method'  => 'mobile_money',
                'status'          => 'pending',
                'date'            => now()->toDateString(),
                'receipt_number'  => $reference,
            ]);

            // Call Lenco
            $gateway = new LencoService();
            $result  = $gateway->collectMobileMoney([
                'amount'    => floatval($validated['amount_to_pay']),
                'phone'     => $this->formatPhoneNumber($validated['payment_phone']),
                'operator'  => $validated['operator'],
                'reference' => $reference,
            ]);

            Log::info('Lenco collectMobileMoney result', ['result' => $result]);

            $lencoStatus = $result['status'] ?? 'failed';

            // Hard failure
            if ($lencoStatus === 'failed') {
                $transaction->delete();
                return back()->with('error', $result['reasonForFailure'] ?? $result['message'] ?? 'Payment initiation failed. Please try again.');
            }

            session([
                'payment_reference'    => $reference,
                'original_payment_id'  => $payment->id,
                'transaction_id'       => $transaction->id,
                'lenco_otp_required'   => ($lencoStatus === 'otp-required'),
            ]);

            return redirect()->route('parent.payment.status');

        } catch (\Exception $e) {
            Log::error('processPayment error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function checkPaymentStatus()
    {
        $reference = session('payment_reference');
        $paymentId = session('original_payment_id');

        if (!$reference || !$paymentId) {
            return redirect()->route('parent.search.page')
                ->with('error', 'Payment session expired.');
        }

        $payment    = Payment::find($paymentId);
        $otpRequired = session('lenco_otp_required', false);

        return view('parents.paymentStatus', compact('reference', 'payment', 'otpRequired'));
    }


    public function tumenyWebhook(Request $request)
        {
        $payload = $request->all();

        if (!isset($payload['status'], $payload['id'])) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $transaction = PaymentTransaction::where('transaction_reference', $payload['id'])->first();

        if (!$transaction) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $payment = Payment::find($transaction->payment_id);

        if (!$payment) {
            return response()->json(['error' => 'Payment missing'], 404);
        }

        if (strtoupper($payload['status']) === 'SUCCESS') {
            $payment->amount_paid += $transaction->amount;
            $payment->balance = max(0, $payment->amount - $payment->amount_paid);
            $payment->save();

            $transaction->status = 'completed';
            $transaction->save();

        } else {
            $transaction->status = 'failed';
            $transaction->save();
        }

        return response()->json(['status' => 'ok']);
    }


    public function getPaymentStatus(Request $request)
        {
        $request->validate([
            'payment_id' => 'required'
        ]);

        $authToken = $this->getAuthToken();

        if (!$authToken) {
            return response()->json(['success' => false, 'message' => 'Auth failed'], 500);
        }

        try {
            $response = $this->httpClient()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $authToken,
                ])
                ->get($this->baseUrl . '/api/v1/payment/' . $request->payment_id);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ]);
            }

            return response()->json(['success' => false], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }


    /**
     * AJAX polling — called by the status page every few seconds.
     * Returns JSON: { status, message }
     *
     * Possible Lenco statuses: pending | successful | failed | pay-offline | otp-required
     */
    public function pollStatus(Request $request)
    {
        $reference = session('payment_reference');

        if (!$reference) {
            return response()->json(['status' => 'failed', 'message' => 'Session expired.'], 400);
        }

        $gateway = new LencoService();
        $result  = $gateway->checkStatus($reference);

        $status = $result['status'] ?? 'failed';

        if ($status === 'successful') {
            $this->applyToBalance($reference, $status);
        }

        return response()->json([
            'status'  => $status,
            'message' => $result['reasonForFailure'] ?? $this->statusLabel($status),
        ]);
    }

    public function paymentWebhook(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-Lenco-Signature');

        if (!$signature) {
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $gateway = new LencoService();

        if (!$gateway->verifyWebhookSignature($rawBody, $signature)) {
            Log::warning('Lenco webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data      = $request->json()->all();
        $status    = $data['data']['status']    ?? null;
        $reference = $data['data']['reference'] ?? null;

        if (!$reference || !$status) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $this->applyToBalance($reference, $status);

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // MANUAL PAYMENT (bank transfer / reference / proof of payment upload)
    // =========================================================================

    /**
     * Shows the pupil's school's payment details (bank + mobile money merchant info)
     * and the form for the parent to submit a reference and/or proof of payment.
     */
    public function showManualPaymentForm($paymentId)
    {
        if (!session('otp_verified')) {
            return redirect()->route('parent.search.page')
                ->with('error', 'Please verify your phone number first.');
        }

        $payment = Payment::with('pupil.school')->findOrFail($paymentId);
        $school  = $payment->pupil->school ?? null;

        if (!$school) {
            Log::error('Manual payment form: pupil has no linked school', ['payment_id' => $payment->id]);
            return back()->with('error', 'This pupil has no school on file. Please contact the school office.');
        }

        $paymentDetail = PaymentDetail::where('school_id', $school->id)->first();

        if (!$paymentDetail) {
            Log::error('Manual payment form: school has no payment details on file', ['school_id' => $school->id]);
            return back()->with('error', 'Payment details for this school are not set up yet. Please contact the school office.');
        }

        return view('parents.manualPayment', compact('payment', 'school', 'paymentDetail'));
    }

    public function submitManualPayment(Request $request, $paymentId)
    {
        if (!session('otp_verified')) {
            return redirect()->route('parent.search.page')
                ->with('error', 'Please verify your phone number first.');
        }

        try {
            $payment = Payment::with('pupil.school')->findOrFail($paymentId);
            $school  = $payment->pupil->school ?? null;

            $validated = $request->validate([
                'amount_to_pay'    => 'required|numeric|min:0.01|max:' . $payment->balance,
                'parent_reference' => 'nullable|string|max:150',
                'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
            ]);

            // Require at least one of the two so we have something to verify against
            if (empty($validated['parent_reference']) && !$request->hasFile('proof_of_payment')) {
                return back()
                    ->withErrors(['parent_reference' => 'Please provide a payment reference or upload proof of payment.'])
                    ->withInput();
            }

            $proofPath = null;
            if ($request->hasFile('proof_of_payment')) {
                // Stored on the 'public' disk — make sure `php artisan storage:link` has been run
                $proofPath = $request->file('proof_of_payment')->store('proof_of_payments', 'public');
            }

            $reference = 'MAN-' . strtoupper(Str::random(12));

            $transaction = PaymentTransaction::create([
                'payment_id'            => $payment->id,
                'school_id'             => $school->id ?? null,
                'amount'                => floatval($validated['amount_to_pay']),
                'mode_of_payment'       => 'Manual - Bank/Mobile Transfer',
                'payment_method'        => 'manual',
                'status'                => 'pending',
                'date'                  => now()->toDateString(),
                'receipt_number'        => $reference,
                'parent_reference'      => $validated['parent_reference'] ?? null,
                'proof_of_payment_path' => $proofPath,
            ]);

            Log::info('Manual payment submitted for verification', [
                'payment_id'      => $payment->id,
                'transaction_id'  => $transaction->id,
                'reference'       => $reference,
            ]);

            session(['manual_payment_reference' => $reference]);

            return redirect()->route('parent.manual.payment.submitted')
                ->with('success', 'Your payment has been submitted and is pending verification. It will be applied to your balance once reviewed.');

        } catch (\Exception $e) {
            Log::error('submitManualPayment error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Something went wrong. Please try again.')->withInput();
        }
    }

    public function manualPaymentSubmitted()
    {
        $reference = session('manual_payment_reference');

        if (!$reference) {
            return redirect()->route('parent.search.page');
        }

        return view('parents.manualPaymentSubmitted', compact('reference'));
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Applies a successful transaction's amount to the linked payment's balance.
     * Shared by the Lenco poll/webhook handlers.
     */
    private function applyToBalance(string $reference, string $status): void
    {
        $transaction = PaymentTransaction::where('receipt_number', $reference)->first();

        if (!$transaction || $transaction->status === 'successful') {
            return; // not found, or already applied — avoid double-crediting
        }

        if ($status === 'successful') {
            $payment = Payment::find($transaction->payment_id);
            if ($payment && $payment->amount_paid < $payment->amount) {
                $payment->amount_paid += $transaction->amount;
                $payment->balance      = max(0, $payment->amount - $payment->amount_paid);
                $payment->save();
            }
            $transaction->update(['status' => 'successful']);
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+260' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'      => 'Waiting for customer to authorise payment.',
            'pay-offline'  => 'Please complete the payment prompt on your phone.',
            'otp-required' => 'An OTP has been sent to your phone. Please enter it to continue.',
            'successful'   => 'Payment completed successfully.',
            'failed'       => 'Payment failed.',
            default        => ucfirst($status),
        };
    }
}
