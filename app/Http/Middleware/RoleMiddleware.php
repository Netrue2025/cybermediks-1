<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Not logged in
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login first.');
        }

        // Normalize roles from route parameter
        // (Laravel passes "doctor,pharmacy" as first arg when defined once)
        if (count($roles) === 1 && is_string($roles[0]) && str_contains($roles[0], ',')) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }
        $roles = array_filter($roles); // remove empties

        // If no roles specified => treat as "must be authenticated" (allow)
        if (empty($roles)) {
            return $next($request);
        }

        // If user role is allowed => continue
        if ($user->role && in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Otherwise redirect to their own dashboard (configurable)
        $redirects = config('roles.redirects', []);
        $routeName = Arr::get($redirects, $user->role);

        if ($routeName) {
            return redirect()->route($routeName)
                ->with('error', 'Unauthorized for this area.');
        }

        // Unknown role → logout for safety
        Auth::logout();
        return redirect()->route('login')
            ->with('error', 'Unauthorized access. Please login with a valid account.');
    }
}
