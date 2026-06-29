<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LencoService
{
    protected string $secretKey;
    protected string $baseUrl = 'https://api.lenco.co/access/v2';

    public function __construct()
    {
        $this->secretKey = config('services.lenco.secret_key');
    }

    // =========================================================================
    // COLLECT — MOBILE MONEY
    // =========================================================================

    /**
     * Initiate a mobile-money collection request.
     *
     * Required keys in $data:
     *   - amount        (numeric, in ZMW)
     *   - phone         (E.164, local, or MSISDN Zambian format)
     *   - operator      ('airtel' | 'mtn' | 'zamtel')
     *
     * Optional keys:
     *   - reference     (auto-generated if omitted)
     *   - country       (defaults to 'zm')
     *   - bearer        ('merchant' | 'customer' — who bears the charge; defaults to 'merchant')
     *
     * Possible status values in response:
     *   pending | successful | failed | pay-offline | otp-required
     */
    public function collectMobileMoney(array $data): array
    {
        $reference = $data['reference'] ?? ('PAY-' . strtoupper(Str::random(12)));
        $url       = "{$this->baseUrl}/collections/mobile-money";

        $payload = [
            'amount'    => $data['amount'],
            'reference' => $reference,
            'phone'     => $this->normalisePhone($data['phone']),
            'operator'  => $data['operator'],
            'country'   => $data['country'] ?? 'zm',
            'bearer'    => $data['bearer']  ?? 'merchant',
        ];

        Log::info('LENCO collectMobileMoney REQUEST', [
            'url'     => $url,
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->defaultHeaders())
                ->post($url, $payload);

            Log::info('LENCO collectMobileMoney RESPONSE', [
                'status'    => $response->status(),
                'body_raw'  => $response->body(),
                'body_json' => $response->json(),
            ]);

            if (! $response->successful()) {
                Log::error('LENCO collectMobileMoney FAILED', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    'reference' => $reference,
                    'status'    => 'failed',
                    'message'   => $response->json()['message'] ?? $response->body() ?: 'Payment initiation failed.',
                ];
            }

            $json = $response->json();

            // Lenco wraps the payload under 'data' — merge so reference is always present
            return array_merge(
                ['reference' => $reference],
                $json['data'] ?? $json
            );

        } catch (\Exception $e) {
            Log::error('LENCO collectMobileMoney EXCEPTION', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return [
                'reference' => $reference,
                'status'    => 'failed',
                'message'   => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // CHECK COLLECTION STATUS
    // =========================================================================

    /**
     * Poll the status of a collection by its Lenco reference.
     *
     * Returns the 'data' payload from Lenco, or an error array.
     *
     * Possible status values: pending | successful | failed | pay-offline | otp-required
     */
    public function checkStatus(string $reference): array
    {
        $url = "{$this->baseUrl}/collections/status/{$reference}";

        Log::info('LENCO checkStatus REQUEST', [
            'reference' => $reference,
            'url'       => $url,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->defaultHeaders())
                ->get($url);

            Log::info('LENCO checkStatus RESPONSE', [
                'status'    => $response->status(),
                'body_raw'  => $response->body(),
                'body_json' => $response->json(),
            ]);

            if (! $response->successful()) {
                Log::error('LENCO checkStatus FAILED', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    'status'  => 'failed',
                    'message' => $response->json()['message'] ?? $response->body() ?: 'Status check failed.',
                ];
            }

            $json = $response->json();

            // Return the nested data object so callers get status, reference, etc. directly
            return $json['data'] ?? $json;

        } catch (\Exception $e) {
            Log::error('LENCO checkStatus EXCEPTION', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return [
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // ENCRYPTION KEY  (used for card collections — fetched fresh every time)
    // =========================================================================

    /**
     * Fetch Lenco's current RSA public key for JWE payload encryption.
     *
     * IMPORTANT: This key can rotate at any time. Never cache or hardcode it.
     * Only needed for card collection — NOT required for mobile money.
     *
     * Returns array with keys: publicKey, keyId (and possibly others).
     */
    public function getEncryptionKey(): array
    {
        $url = "{$this->baseUrl}/encryption-key";

        Log::info('LENCO getEncryptionKey REQUEST', ['url' => $url]);

        try {
            $response = Http::timeout(30)
                ->withHeaders($this->defaultHeaders())
                ->get($url);

            Log::info('LENCO getEncryptionKey RESPONSE', [
                'status'    => $response->status(),
                'body_json' => $response->json(),
            ]);

            if (! $response->successful()) {
                Log::error('LENCO getEncryptionKey FAILED', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    'status'  => 'failed',
                    'message' => $response->json()['message'] ?? 'Failed to fetch encryption key.',
                ];
            }

            $json = $response->json();

            return $json['data'] ?? $json;

        } catch (\Exception $e) {
            Log::error('LENCO getEncryptionKey EXCEPTION', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return [
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // VERIFY WEBHOOK SIGNATURE
    // =========================================================================

    /**
     * Verify an incoming Lenco webhook.
     *
     * Lenco sends the signature in the X-Lenco-Signature header.
     *
     * Per Lenco docs, the signing key (webhook_hash_key) is NOT a separate
     * credential — it is derived by SHA256-hashing your API secret key:
     *
     *   webhook_hash_key = SHA256(API_SECRET_KEY)
     *   expected         = HMAC-SHA512(rawBody, webhook_hash_key)
     *
     * Usage in your webhook controller:
     *
     *   $rawBody  = $request->getContent();
     *   $sigHeader = $request->header('X-Lenco-Signature');
     *
     *   if (! $lenco->verifyWebhookSignature($rawBody, $sigHeader)) {
     *       abort(401, 'Invalid signature');
     *   }
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        // Derive the webhook hash key from the API secret (per Lenco docs)
        $webhookHashKey = hash('sha256', $this->secretKey);

        $expected = hash_hmac('sha512', $rawBody, $webhookHashKey);

        return hash_equals($expected, $signature);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Default request headers for all Lenco API calls.
     */
    private function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Normalise a Zambian phone number to full MSISDN (no leading +).
     *
     *   +260972643310  →  260972643310
     *    0972643310    →  260972643310
     *    260972643310  →  260972643310
     */
    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '0')) {
            return '260' . substr($digits, 1);
        }

        return $digits; // already has country code
    }
}