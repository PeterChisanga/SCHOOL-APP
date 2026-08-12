<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('dashboardRoute')) {
    function dashboardRoute()
    {
        $user = Auth::user();

        return match($user->user_type) {
            'admin'     => 'admin.dashboard',
            'teacher'   => 'teacher.dashboard',
            'secretary' => 'secretary.dashboard',
            default     => 'login',
        };
    }
}

if (!function_exists('dashboardLabel')) {
    function dashboardLabel()
    {
        $user = Auth::user();

        return match($user->user_type) {
            'admin'     => 'Admin Dashboard',
            'teacher'   => 'Teacher Dashboard',
            'secretary' => 'Secretary Dashboard',
            default     => 'Dashboard',
        };
    }
}

if (!function_exists('normalizePhoneNumber')) {
    /*
     * Normalize a phone number to a consistent format.
     * Returns null for empty input.
     */
    function normalizePhoneNumber(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        // Keep only digits and a possible leading '+'
        $phone = preg_replace('/[^0-9+]/', '', trim($phone));
        $hasCountryCode = str_starts_with($phone, '+');
        $phone = str_replace('+', '', $phone); // drop any stray plus signs

        if ($hasCountryCode) {
            // Explicit code was given; kept as-is (downstream validation
            // enforces +260 for Zambia).
            return '+' . $phone;
        }

        if (str_starts_with($phone, '260') && strlen($phone) >= 11) {
            // Typed without the '+': 2609XXXXXXXX
            return '+' . $phone;
        }

        if (str_starts_with($phone, '0')) {
            // Local leading zero: 0971234567
            return '+260' . substr($phone, 1);
        }

        // Bare local number: 971234567
        return '+260' . $phone;
    }
}
