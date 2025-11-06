<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TouchLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (auth()->check() && auth()->user()->role === 'doctor') {
            // only mark active for doctors; you can broaden if you like
            auth()->user()->forceFill(['last_seen_at' => now()])->saveQuietly();
        }

        return $response;
    }
}
