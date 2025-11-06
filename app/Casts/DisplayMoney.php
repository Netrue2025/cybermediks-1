<?php

namespace App\Casts;

use App\Support\CurrencyContext;
use App\Support\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DisplayMoney implements CastsAttributes
{
    /** @var string NGN is your DB source-of-truth */
    protected string $baseCurrency;

    public function __construct(string $baseCurrency = 'NGN')
    {
        $this->baseCurrency = strtoupper($baseCurrency);
    }

    public function get($model, string $key, $value, array $attributes)
    {
        $amountNgn = (float) $value;
        $ctx = app(\App\Support\CurrencyContext::class);
        $pair = $ctx->convertFromNgn($amountNgn);
        $symbol = $ctx->symbol($pair['currency']);
        return $symbol . number_format($pair['amount'], 2, '.', ','); // <- string
    }


    public function set($model, string $key, $value, array $attributes)
    {
        // Persist raw NGN to DB no matter what the viewer sees
        // Accept Money, numeric string, or float.
        if ($value instanceof Money) {
            // If someone passes a Money in USD, you could invert; simpler: expect NGN on set.
            // For safety, just store its numeric amount (assume already NGN).
            return [$key => (float) $value->amount];
        }
        return [$key => (float) $value];
    }
}
