<?php
// app/Services/GeoCurrency.php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

class GeoCurrency
{
    /** Decide once per request */
    protected ?string $resolvedCurrency = null;

    public function currencyForCurrentUser(): string
    {
        if ($this->resolvedCurrency) {
            return $this->resolvedCurrency;
        }

        // Always use NGN (Naira) for all users
        $this->resolvedCurrency = 'NGN';
        return $this->resolvedCurrency;
    }

    public function convertFromNgn(float $amountNgn, string $toCcy): float
    {
        // Always return the same amount since we're using NGN only
        return $amountNgn;
    }

    public function format(float $amount, ?string $ccy = null): string
    {
        $ccy = $ccy ?: $this->currencyForCurrentUser();

        return match ($ccy) {
            'NGN' => '₦' . number_format($amount, 2),
            'USD' => '$' . number_format($amount, 2), // Keep for legacy support
            default => $ccy . ' ' . number_format($amount, 2),
        };
    }

    /**
     * Priority:
     * 1) session('country_code') set by DetectCountryFromIp middleware
     * 2) auth()->user()->country_code (if present)
     * 3) cached IP lookup (fallback)
     */
    protected function countryCodeFromContext(): string
    {
        // 1) Middleware-written value (cheap & reliable within this request)
        if ($cc = strtoupper((string) Session::get('country_code'))) {
            return $cc;
        }

        // 2) User profile preference (if any)
        if (auth()->check() && ($u = auth()->user()) && !empty($u->country_code)) {
            return strtoupper($u->country_code);
        }

        // 3) Last-resort IP geo (cache by IP daily)
        $ip = Request::ip();
        if (!$ip) {
            return 'NG';
        }

        // Reuse whatever you like here; kept simple:
        return Cache::remember("geo.fallback.cc.$ip", 86400, function () {
            return 'NG';
        });
    }
}
