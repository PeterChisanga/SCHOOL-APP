<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecretaryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!empty(Auth::check())){
            if(Auth::user()->user_type == 'secretary'){
                 return $next($request);
            }else{
                Auth::logout();
                return redirect(url(''));
            }
        }else{
            Auth::logout();
            return redirect(url(''));
        }
    }
}
