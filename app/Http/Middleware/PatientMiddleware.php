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
            $dashboardRoute = '';

            switch ($user->role) {
                case 'doctor':
                    $dashboardRoute = 'doctor.dashboard';
                    break;
                case 'dispatcher':
                    $dashboardRoute = 'dispatcher.dashboard';
                    break;
                case 'pharmacy':
                    $dashboardRoute = 'pharmacy.dashboard';
                    break;
                case 'health':
                    $dashboardRoute = 'health.dashboard';
                    break;
                case 'transport':
                    $dashboardRoute = 'transport.dashboard';
                    break;
                default:
                    Auth::logout();
                    return redirect()->route('login')->with('error', 'Unauthorized access. Please login with a valid account.');
            }
            return redirect()->route($dashboardRoute)->with('error', 'Unauthorized access to patient area.');
        }
        return $next($request);
    }
}
