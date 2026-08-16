<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\SchoolWelcome;
use App\Mail\SchoolRegistered;
use Illuminate\Support\Facades\Log;

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
        } catch (\Exception $e) {
            Log::error("Failed to send school welcome email: " . $e->getMessage());
        }
    }

    /**
     * Alert Kapini Technologies when a new school is registered.
     */
    public function notifyKapiniOfNewSchool($schoolName, $adminName, $adminEmail, $adminPhone)
    {
        try {
            // >>> replace with the Kapini Technologies email once confirmed <<<
            Mail::to(config('services.eschool.email_address'))->send(new SchoolRegistered(
                $schoolName,
                $adminName,
                $adminEmail,
                $adminPhone
            ));
        } catch (\Exception $e) {
            Log::error("Failed to send Kapini school-registration alert: " . $e->getMessage());
        }
    }
}
