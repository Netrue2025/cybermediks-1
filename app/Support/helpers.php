<?php

use App\Services\GeoCurrency;

if (! function_exists('money_display')) {
    /**
     * money_display($amountNgn, $forceCcy = null)
     * - Accepts amounts in NGN (your base)
     * - Auto-detects user currency (USD outside Nigeria) unless $forceCcy provided
     */
    function money_display($amountNgn)
    {
        if ($amountNgn === null) return '—';
        return '₦'. $amountNgn;
    }
}
