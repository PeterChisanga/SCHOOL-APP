<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;

class RegisterSchool implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(NotificationService $notifier)
    {
        if ($this->batch()?->cancelled()) return;

        // Hand the collected data over to the notification layer, which
        // arranges it and sends the emails: one to the school admin,
        // one to Kapini Technologies.
        /** $notifier->sendWelcomeEmail(
            $this->data['school_name'],
            $this->data['admin_name'],
            $this->data['admin_email'],
            $this->data['admin_phone'],
        );
        */
        $notifier->notifyKapiniOfNewSchool(
            $this->data['school_name'],
            $this->data['admin_name'],
            $this->data['admin_email'],
            $this->data['admin_phone'],
        );
    }
}
