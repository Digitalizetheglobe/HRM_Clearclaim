<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExtendEmployeeSession
{
    /**
     * Handle an incoming request.
     * Extend session lifetime for employees and company users to prevent automatic logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is authenticated and is an employee or company user, extend session
        if (Auth::check() && (Auth::user()->type === 'employee' || Auth::user()->type === 'company')) {
            // Set session lifetime to 10 years (5256000 minutes) for employees and company users
            config(['session.lifetime' => 5256000]);
            
            // Extend the session cookie expiration
            $sessionName = config('session.cookie');
            $sessionLifetime = 5256000; // 10 years in minutes
            
            // Get the session ID
            $sessionId = $request->session()->getId();
            
            // Extend session by updating last activity time
            $request->session()->put('last_activity', now()->timestamp);
        }

        $response = $next($request);

        // If employee or company user, ensure the session cookie has extended expiration
        if (Auth::check() && (Auth::user()->type === 'employee' || Auth::user()->type === 'company')) {
            $sessionName = config('session.cookie');
            $sessionLifetime = 5256000;
            
            $response->withCookie(cookie(
                $sessionName,
                $request->session()->getId(),
                $sessionLifetime,
                config('session.path'),
                config('session.domain'),
                config('session.secure'),
                config('session.http_only'),
                false,
                config('session.same_site')
            ));
        }

        return $response;
    }
}
