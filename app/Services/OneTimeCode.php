<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class OneTimeCode
{
    /**
     * Generate & store a 6-digit code in cache with TTL (minutes).
     * $type: 'verify' | 'reset'
     */
    public static function make(string $email, string $type, int $ttlMinutes = 15): string
    {
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $key  = self::key($email, $type);
        Cache::put($key, $code, now()->addMinutes($ttlMinutes));
        return $code;
    }

    /** Validate and optionally consume the code */
    public static function check(string $email, string $type, string $code, bool $consume = true): bool
    {
        $key = self::key($email, $type);
        $cached = Cache::get($key);
        if (!$cached || $cached !== $code) return false;
        if ($consume) Cache::forget($key);
        return true;
    }

    /** Clear code */
    public static function clear(string $email, string $type): void
    {
        Cache::forget(self::key($email, $type));
    }

    private static function key(string $email, string $type): string
    {
        return "codes:{$type}:" . strtolower(trim($email));
    }
}
