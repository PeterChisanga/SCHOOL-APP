<?php

namespace App\Jobs;

use App\Services\AfricasTalkingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendResultsSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300]; // Retry after 1 min, then 5 mins

    protected string $phone;
    protected string $message;

    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle(AfricasTalkingService $smsService): void
    {
        $smsService->sendSms($this->phone, $this->message);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Failed to send results SMS to {$this->phone}: " . $exception->getMessage());
    }
}
