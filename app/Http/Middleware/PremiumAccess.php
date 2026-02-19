<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PremiumAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school = Auth::user()->school;

        if (!$school || !$school->is_premium || ($school->subscription_expires_at && $school->subscription_expires_at < now())) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'This feature is available only for premium subscribers.');
        }

        return $next($request);
    }
}
