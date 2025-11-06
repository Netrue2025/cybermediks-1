<?php

use App\Services\GeoCurrency;

if (! function_exists('money_display')) {
    /**
     * money_display($amountNgn, $forceCcy = null)
     * - Accepts amounts in NGN (your base)
     * - Auto-detects user currency (USD outside Nigeria) unless $forceCcy provided
     */
    function money_display(?float $amountNgn, ?string $forceCcy = null): string
    {
        if ($amountNgn === null) return '—';

        /** @var GeoCurrency $svc */
        $svc = app(GeoCurrency::class);
        $ccy = $forceCcy ?: $svc->currencyForCurrentUser();

        $converted = $svc->convertFromNgn($amountNgn, $ccy);
        return $svc->format($converted, $ccy);
    }
}
