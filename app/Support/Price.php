<?php

namespace App\Support;

use App\Services\FlutterwaveRates;

class Price
{
    /**
     * Returns ['amount' => float, 'currency' => 'NGN'|'USD']
     * $countryCode should be ISO2 (e.g., 'NG').
     */
    public static function presentForUser(float $amountNgn, ?string $countryCode): array
    {
        if (strtoupper((string) $countryCode) === 'NG') {
            return ['amount' => $amountNgn, 'currency' => 'NGN'];
        }
        $rate = FlutterwaveRates::ngnToUsdRate();     // USD per 1 NGN
        $usd  = $amountNgn * $rate;                   // convert
        return ['amount' => $usd, 'currency' => 'USD'];
    }
}
