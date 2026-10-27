<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\SchoolWelcome;
use App\Mail\SchoolRegistered;
use Illuminate\Support\Facades\Log;

/**
 * All platform email goes through here.
 *
 * Every failure is caught and logged rather than thrown, so a broken SMTP
 * setup never blocks a registration or a payment webhook. The price is that a
 * failure is invisible in the UI — that is what `php artisan mail:diagnose`
 * exists for, and why each log line names the recipient, the exception class
 * and the message.
 */
class NotificationService
{
    /**
     * Send the welcome email when a new school is registered.
     */
    public function sendWelcomeEmail($schoolName, $adminName, $adminEmail, $adminPhone)
    {
        try {
            Mail::to($adminEmail)->send(new SchoolWelcome(
                $schoolName,
                $adminName,
                $adminEmail,
                $adminPhone
            ));
        } catch (\Throwable $e) {
            Log::error("Failed to send school welcome email to {$adminEmail}: " . get_class($e) . ': ' . $e->getMessage());
        }
    }

    /**
     * Alert the platform owner when a new school is registered.
     */
    public function notifyKapiniOfNewSchool($schoolName, $adminName, $adminEmail, $adminPhone)
    {
        $to = config('mail.notify_address');

        try {
            Mail::to($to)->send(new SchoolRegistered(
                $schoolName,
                $adminName,
                $adminEmail,
                $adminPhone
            ));
        } catch (\Throwable $e) {
            Log::error("Failed to send school-registration alert to {$to}: " . get_class($e) . ': ' . $e->getMessage());
        }
    }

    /**
     * Notify a school that a parent's mobile money payment succeeded.
     */
    public function notifySchoolOfPayment($schoolEmail, $schoolName, $pupilName, $amount, $reference)
    {
        try {
            Mail::raw(
                "Payment received: K{$amount} from {$pupilName} for {$schoolName}. Ref: {$reference}.",
                function ($message) use ($schoolEmail, $schoolName) {
                    $message->to($schoolEmail)
                            ->subject("Payment Received - {$schoolName}");
                }
            );
        } catch (\Throwable $e) {
            Log::error("Failed to send school payment-received alert to {$schoolEmail}: " . get_class($e) . ': ' . $e->getMessage());
        }
    }

    /**
     * Alert the platform owner when a parent's mobile money payment succeeds, for reconciliation.
     */
    public function notifyKapiniOfPayment($schoolName, $pupilName, $amount, $reference)
    {
        $to = config('mail.notify_address');

        try {
            Mail::raw(
                "Payment received: K{$amount} from {$pupilName} ({$schoolName}). Ref: {$reference}.",
                function ($message) use ($to, $schoolName) {
                    $message->to($to)
                            ->subject("Payment Received - {$schoolName}");
                }
            );
        } catch (\Throwable $e) {
            Log::error("Failed to send payment-received alert to {$to}: " . get_class($e) . ': ' . $e->getMessage());
        }
    }
}
