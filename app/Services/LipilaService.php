<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LipilaService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.lipila.dev/api/v1';
    protected string $webhookSecret;

    public function __construct()
    {
        $this->apiKey        = config('services.lipila.api_key');
        $this->webhookSecret = config('services.lipila.webhook_secret');

        Log::info('LIPILA INIT', [
            'baseUrl' => $this->baseUrl,
            'apiKey_present' => !empty($this->apiKey),
            'apiKey_preview' => substr($this->apiKey ?? '', 0, 10) . '...',
        ]);
    }

    public function collectMobileMoney(array $data): array
    {
        $referenceId = Str::uuid()->toString();
        $url = "{$this->baseUrl}/collections/mobile-money";

        $payload = [
            'referenceId'   => $referenceId,
            'amount'        => $data['amount'],
            'narration'     => $data['narration'],
            'accountNumber' => $data['accountNumber'],
            'currency'      => 'ZMW',
            'email'         => $data['email'] ?? null,
            'callbackUrl'   => route('lipila.callback'), // FIXED (moved to body)
        ];

       $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];

        Log::info('LIPILA REQUEST START', [
            'url'     => $url,
            'headers' => $headers,
            'payload' => $payload,
        ]);

        $start = microtime(true);

        try {
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($url, $payload);

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::info('LIPILA RESPONSE', [
                'status'     => $response->status(),
                'duration_ms'=> $duration,
                'headers'    => $response->headers(),
                'body_raw'   => $response->body(),
                'body_json'  => $response->json(),
            ]);

            if (!$response->successful()) {
                Log::error('LIPILA FAILED RESPONSE', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }

            return array_merge(
                ['referenceId' => $referenceId],
                $response->json() ?? []
            );

        } catch (\Exception $e) {

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::error('LIPILA EXCEPTION', [
                'message'     => $e->getMessage(),
                'code'        => $e->getCode(),
                'duration_ms' => $duration,
                'trace'       => $e->getTraceAsString(),
            ]);

            return [
                'referenceId' => $referenceId,
                'status'      => 'Failed',
                'message'     => $e->getMessage(),
            ];
        }
    }

    public function checkStatus(string $referenceId): array
    {
        $url = "{$this->baseUrl}/collections/check-status";

        Log::info('LIPILA STATUS CHECK START', [
            'referenceId' => $referenceId,
            'url' => $url
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept'    => 'application/json',
                    'x-api-key' => $this->apiKey,
                ])
                ->get($url, [
                    'referenceId' => $referenceId,
                ]);

            Log::info('LIPILA STATUS RESPONSE', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->json() ?? [];

        } catch (\Exception $e) {
            Log::error('LIPILA STATUS EXCEPTION', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return ['status' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}