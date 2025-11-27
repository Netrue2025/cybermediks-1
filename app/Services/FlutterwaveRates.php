<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveRates
{
    const KEY_NGN_USD = 'fx.ngn_usd.rate';
    const TTL = 86400;

    public static function ngnToUsd(): float
    {
        return Cache::remember(self::KEY_NGN_USD, self::TTL, function () {
            $secret = config('services.flutterwave.secret');
            $base   = rtrim(config('services.flutterwave.base', 'https://api.flutterwave.com'), '/');
            $url    = "$base/v3/transfers/rates";

            // use a larger source to reduce rounding noise
            $srcAmount = 10000; // NGN
            $resp = Http::withToken($secret)->get($url, [
                'amount'               => $srcAmount,
                'source_currency'      => 'NGN',
                'destination_currency' => 'NGN',
            ]);

            $fallback = (float) config('services.flutterwave.fallback_ngn_usd', 1 / 1700); // ~0.000588

            if (!$resp->ok()) {
                return $fallback;
            }

            $data = $resp->json('data', []);
            // Prefer computing from returned breakdown if present:
            $src = (float) ($data['source']['amount'] ?? $srcAmount);
            $dst = (float) ($data['destination']['amount'] ?? 0.0);

            // Compute USD-per-NGN
            $usdPerNgn = ($src > 0 && $dst > 0) ? $dst / $src : null;

            // If API only returns a single 'rate', it might be NGN-per-USD.
            if (!$usdPerNgn && isset($data['rate']) && is_numeric($data['rate'])) {
                $rate = (float) $data['rate'];

                // Heuristic: if rate > 1, it’s almost certainly NGN per USD (e.g., 1700)
                // so invert to get USD per NGN.
                $usdPerNgn = $rate > 1 ? (1 / $rate) : $rate;
            }

            // Sanity guard: 1 NGN should be a tiny fraction of a dollar.
            // Realistic range: 0 < usdPerNgn < 0.01
            if (!$usdPerNgn || $usdPerNgn <= 0 || $usdPerNgn >= 0.01) {
                Log::warning('FW rate out of expected bounds; using fallback', [
                    'computed' => $usdPerNgn,
                    'data'     => $data,
                ]);
                return $fallback;
            }

            return $usdPerNgn;
        });
    }
}
