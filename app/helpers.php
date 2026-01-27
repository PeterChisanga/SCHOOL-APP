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
