<?php

namespace App\Services;

use Illuminate\Support\Facades\{Storage, Cache};

class InventoryReader
{
    public static function readCsvText(?string $path): ?string
    {
        if (!$path || !Storage::exists($path)) return null;
        return Storage::get($path);
    }

    // Optional caching of parsed array (name → price) for local fallback
    public static function parseCsvToArray(?string $path, int $pharmacyId): array
    {
        if (!$path || !Storage::exists($path)) return [];
        $cacheKey = "pharmacy_inventory_array_{$pharmacyId}";

        return Cache::remember($cacheKey, 3600, function () use ($path) {
            $raw = Storage::get($path);
            $rows = [];
            $lines = preg_split('/\r\n|\r|\n/', trim($raw));
            if (!$lines || count($lines) < 2) return [];

            $headers = str_getcsv(array_shift($lines));
            $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $cols = str_getcsv($line);
                $row = [];
                foreach ($cols as $i => $val) {
                    $row[$headers[$i] ?? "col{$i}"] = trim($val);
                }
                $rows[] = $row;
            }
            return $rows;
        });
    }
}
