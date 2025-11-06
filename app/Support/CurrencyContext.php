<?php

namespace App\Support;

use App\Services\FlutterwaveRates;

class CurrencyContext
{
    public function viewerCurrency(): string
    {
        $cc = strtoupper((string) session('country_code', 'NG'));
        return $cc === 'NG' ? 'NGN' : 'USD';
    }

    public function convertFromNgn(float $amountNgn): array
    {
        $cur = $this->viewerCurrency();
        if ($cur === 'NGN') {
            return ['amount' => $amountNgn, 'currency' => 'NGN'];
        }
        $rate = FlutterwaveRates::ngnToUsd(); // USD per 1 NGN
        return ['amount' => $amountNgn * $rate, 'currency' => 'USD'];
    }

    public function symbol(string $currency): string
    {
        return $currency === 'USD' ? '$' : '₦';
    }
}
