<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SecretaryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if (Auth::check()) {
            if (Auth::user()->user_type === 'secretary' || Auth::user()->user_type === 'admin') {
                return $next($request);
            } else {
                Auth::logout();
                return redirect('/login')->with('error', 'Unauthorized access');
            }
        }

        return redirect('/login')->with('error', 'Please log in to access this page.');
    }

}
