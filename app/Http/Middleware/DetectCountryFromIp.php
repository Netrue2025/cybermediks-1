<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DetectCountryFromIp
{
    public function handle(Request $request, Closure $next)
    {
        // session(['country_code' => 'US']);
        // return $next($request);
        // If user has saved country_code, prefer it
        if (auth()->check() && $cc = auth()->user()->country_code) {
            session(['country_code' => strtoupper($cc)]);
            return $next($request);
        }

        // Otherwise use IP detection (skip private/local)
        $ip = $request->ip();
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            session(['country_code' => session('country_code', 'NG')]);
            return $next($request);
        }

        $cc = Cache::remember("geoip.cc.$ip", 86400, function () use ($ip) {
            try {
                // Free providers you can swap: ipapi.co, ipwho.is, ipinfo (token), etc.
                $res = Http::timeout(4)->get("https://ipapi.co/{$ip}/json/");
                if ($res->ok()) {
                    $code = strtoupper((string) $res->json('country_code'));
                    return $code ?: 'NG';
                }
            } catch (\Throwable $e) {
            }
            return 'NG';
        });

        session(['country_code' => $cc]);
        return $next($request);
    }
}
