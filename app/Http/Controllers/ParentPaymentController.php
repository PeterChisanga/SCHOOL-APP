<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use App\Models\Pupil;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ParentPaymentController extends Controller
{
    private $baseUrl = 'https://tumeny.herokuapp.com';

    /*
     * Automatically apply SSL bypass for local environment
     */
    private function httpClient()
    {
        $client = Http::timeout(30);

        if (app()->environment('local')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

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
        $pupil = Pupil::findOrFail($pupilId);
        $payments = Payment::where('pupil_id', $pupilId)->get();
        $parent = session('current_parent');

        return view('parents.payments', compact('pupil', 'payments', 'parent'));
    }

    private function getAuthToken(): ?string
    {
        try {
            $apiKey = config('services.tumeny.api_key');
            $apiSecret = config('services.tumeny.api_secret');

            if (!$apiKey || !$apiSecret) {
                Log::error('Missing Tumeny API credentials');
                return null;
            }

            return Cache::remember('tumeny_auth_token', 2700, function () use ($apiKey, $apiSecret) {

                $response = $this->httpClient()
                    ->withHeaders([
                        'apiKey' => $apiKey,
                        'apiSecret' => $apiSecret,
                    ])
                    ->post($this->baseUrl . '/api/token');

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['token'] ?? null;
                }

                Log::error("Failed getting token", ['body' => $response->body()]);
                return null;
            });

        } catch (\Exception $e) {
            Log::error('Token Exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function processPayment(Request $request, $paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            $validated = $request->validate([
                'amount_to_pay' => 'required|numeric|min:0.01|max:' . $payment->balance,
                'payment_phone' => 'required|min:10'
            ]);

            $authToken = $this->getAuthToken();

            if (!$authToken) {
                return back()->with('error', 'Could not authenticate with Tumeny.');
            }

            $phone = $this->formatPhoneNumber($request->payment_phone);
            $amount = intval(floatval($validated['amount_to_pay']) * 100);

            $pupil = $payment->pupil;
            $parent = session('current_parent');

            $firstName = $pupil->first_name ?? 'User';
            $lastName = $pupil->last_name ?? 'Unknown';

            $email = $parent->email ?? 'noreply@school.com';

            $requestData = [
                'description' => 'Payment for ' . $payment->type . ' - ' . $payment->term,
                'customerFirstName' => $firstName,
                'customerLastName' => $lastName,
                'email' => $email,
                'phoneNumber' => $phone,
                'amount' => $amount,
            ];

            $response = $this->httpClient()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $authToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/v1/payment', $requestData);

            if ($response->successful()) {
                $data = $response->json();

                if (!isset($data['payment']['id'])) {
                    return back()->with('error', 'Invalid response from Tumeny.');
                }

                $tumenyPaymentId = $data['payment']['id'];

                $transaction = PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'amount' => floatval($request->amount_to_pay),
                    'phone_number' => $phone,
                    'mode_of_payment' => 'Tumeny',
                    'status' => strtolower($data['payment']['status'] ?? 'pending'),
                    'date' => now(),
                    'transaction_reference' => $tumenyPaymentId
                ]);

                session([
                    'tumeny_payment_id' => $tumenyPaymentId,
                    'original_payment_id' => $payment->id,
                ]);

                return redirect()->route('parent.payment.status');
            }

            return back()->with('error', 'Failed to initiate payment.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function checkPaymentStatus()
    {
        $tumenyPaymentId = session('tumeny_payment_id');
        $originalPaymentId = session('original_payment_id');

        if (!$tumenyPaymentId || !$originalPaymentId) {
            return redirect()->route('parent.search')->with('error', 'Payment session expired');
        }

        $payment = Payment::find($originalPaymentId);

       return view('parents.paymentStatus', compact('tumenyPaymentId', 'payment'));
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

    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+260' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+')) {
            return '+260' . $phone;
        }

        return $phone;
    }
}
