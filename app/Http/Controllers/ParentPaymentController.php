<?php

namespace App\Http\Controllers;

use App\Models\OtpVerification;
use App\Models\ParentModel;
use App\Models\Pupil;
use App\Models\Payment;
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
    // SEARCH
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
                    ->where('used', false)
                    ->where('expires_at', '>', now())
                    ->latest()
                    ->first();

     if (!$record || $request->otp !== $record->otp) {
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

    // =========================================================================
    // PAYMENTS
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
                'operator'      => 'required|in:airtel,mtn,zamtel', // ← new field in your form
            ]);

            $parent    = session('current_parent');
            $reference = 'PAY-' . strtoupper(Str::random(12));

            // Create pending transaction
            $transaction = PaymentTransaction::create([
                'payment_id'      => $payment->id,
                'amount'          => floatval($validated['amount_to_pay']),
                'mode_of_payment' => 'Mobile Money',
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

            // Lenco returns data.reference (same as what we sent) and data.status
            $lencoStatus = $result['status'] ?? 'failed';

            // Hard failure
            if ($lencoStatus === 'failed') {
                $transaction->delete();
                return back()->with('error', $result['reasonForFailure'] ?? $result['message'] ?? 'Payment initiation failed. Please try again.');
            }

            // otp-required: Lenco needs the customer to enter an OTP before proceeding.
            // Store flag so the status page can render an OTP input if needed.
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

    // =========================================================================
    // PAYMENT STATUS
    // =========================================================================

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

        // If now successful, update the payment record
        if ($status === 'successful') {
            $transaction = PaymentTransaction::where('receipt_number', $reference)->first();
            if ($transaction) {
                $payment = Payment::find($transaction->payment_id);
                if ($payment && $payment->amount_paid < $payment->amount) {
                    $payment->amount_paid += $transaction->amount;
                    $payment->balance      = max(0, $payment->amount - $payment->amount_paid);
                    $payment->save();
                }
            }
        }

        return response()->json([
            'status'  => $status,
            'message' => $result['reasonForFailure'] ?? $this->statusLabel($status),
        ]);
    }

    // =========================================================================
    // WEBHOOK
    // =========================================================================

    public function paymentWebhook(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-Lenco-Signature'); // ← Lenco's header name

        if (!$signature) {
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $gateway = new LencoService();

        if (!$gateway->verifyWebhookSignature($rawBody, $signature)) {
            Log::warning('Lenco webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data      = $request->json()->all();

        // Lenco webhook payload mirrors the collection response shape
        $status    = $data['data']['status']    ?? null;
        $reference = $data['data']['reference'] ?? null;

        if (!$reference || !$status) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $transaction = PaymentTransaction::where('receipt_number', $reference)->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($status === 'successful') {
            $payment = Payment::find($transaction->payment_id);
            if ($payment && $payment->amount_paid < $payment->amount) {
                $payment->amount_paid += $transaction->amount;
                $payment->balance      = max(0, $payment->amount - $payment->amount_paid);
                $payment->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

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