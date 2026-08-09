<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect('/login')->with('error', 'Unauthorized access');
        }

        $userType = Auth::user()->user_type;

        if ($userType === 'admin') {
            return $next($request);
        }

        if ($userType === 'platform_admin') {
            if (session('acting_school_id')) {
                return $next($request);
            }

            return redirect()->route('schools.index')
                ->with('error', 'Select a school to view its dashboard first.');
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Unauthorized access');
    }
}
