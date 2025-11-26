<?php

namespace App\Support;

use App\Services\FlutterwaveRates;

class Price
{
    /**
     * Returns ['amount' => float, 'currency' => 'NGN']
     * Always returns Naira - no conversion needed
     */
    public static function toUserCurrency(float $amountNgn): array
    {
        // Always use NGN (Naira) for all users
        return ['amount' => $amountNgn, 'currency' => 'NGN'];
    }
}
