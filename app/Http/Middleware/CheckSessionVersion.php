<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class CheckSessionVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // If the session doesn't have a version, initialize it with the current DB version
            if (!$request->session()->has('session_version')) {
                $request->session()->put('session_version', $user->session_version);
            }

            // If the version in the session does not match the version in the DB, logout
            if ($request->session()->get('session_version') !== $user->session_version) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->with('error', __('Your credentials have been updated. Please log in again.'));
            }
        }

        return $next($request);
    }
}
