<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Stringable;

class Money implements JsonSerializable, Arrayable, Stringable
{
    public float $amount;
    public string $currency;
    public string $formatted;

    public function __construct(float $amount, string $currency, string $formatted)
    {
        $this->amount    = $amount;
        $this->currency  = $currency;
        $this->formatted = $formatted;
    }

    public function __toString(): string
    {
        return $this->formatted;
    }

    // If you want JSON to be an object with all fields:
    public function jsonSerialize(): mixed
    {
        return [
            'amount'    => $this->amount,
            'currency'  => $this->currency,
            'formatted' => $this->formatted,
        ];
    }

    // If you prefer JSON to be just the formatted string, use this instead:
    // public function jsonSerialize(): mixed
    // {
    //     return $this->formatted;
    // }

    public function toArray(): array
    {
        return [
            'amount'    => $this->amount,
            'currency'  => $this->currency,
            'formatted' => $this->formatted,
        ];
    }
}
