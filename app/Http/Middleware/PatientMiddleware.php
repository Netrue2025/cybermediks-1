<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PatientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            // Not logged in
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if (!$user->role || $user->role !== 'patient') {
            // Not a patient
            return abort(403, 'Access denied.');
        }
        return $next($request);
    }
}
