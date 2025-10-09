<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIQuoteService
{
    public static function buildPrompt(array $rxItems, string $csvChunk): string
    {
        // $rxItems: [ ['drug'=>'Amoxicillin','dose'=>'500 mg','frequency'=>'', 'quantity'=>10], ... ]
        $rxJson = json_encode($rxItems, JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
You are a pharmacy inventory matcher. 
Given:
1) A prescription item list (JSON).
2) A CSV snippet of a pharmacy's inventory (comma-separated, first row headers). 

Tasks:
- For each prescription item, find the closest matching inventory row (exact if possible; otherwise fuzzy on drug name and strength).
- If multiple matches exist, pick the most reasonable (matching strength/form).
- Return strict JSON with this schema:

{
  "available": [
     { "drug":"...", "match_row": <row_index>, "unit_price": <number>, "line_total": <number>, "matched_name":"...", "reason":"..." }
  ],
  "unavailable": [
     { "drug":"...", "reason":"..." }
  ]
}

Notes:
- unit_price should be numeric (no currency symbol). If price column not present, omit the drug from "available" and explain in reason.
- line_total = unit_price * quantity (if quantity is given; assume 1 otherwise).
- Respond ONLY with JSON. No extra text.

PRESCRIPTION_JSON:
{$rxJson}

INVENTORY_CSV_SNIPPET:
{$csvChunk}
PROMPT;
    }

    public static function quote(array $rxItems, string $csvText): array
    {
        // If CSV too large, chunk it and merge best-effort results.
        $chunks = self::chunkCsv($csvText, 8000); // ~tokens-safe heuristic
        $available = [];
        $unavailable = [];

        foreach ($chunks as $chunk) {
            $prompt = self::buildPrompt($rxItems, $chunk);

            // Replace with your OpenAI call
            $res = Http::withToken(config('services.openai.key'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini', // pick your model
                    'messages' => [
                        ['role' => 'system', 'content' => 'You return strict JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ])->json();

            $text = data_get($res, 'choices.0.message.content', '');
            $json = json_decode($text, true);

            if (is_array($json)) {
                $available = array_merge($available, $json['available'] ?? []);
                $unavailable = array_merge($unavailable, $json['unavailable'] ?? []);
            }
        }

        return [
            'available' => $available,
            'unavailable' => $unavailable,
        ];
    }

    private static function chunkCsv(string $text, int $maxLen): array
    {
        $text = trim($text);
        if (strlen($text) <= $maxLen) return [$text];

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $header = array_shift($lines);
        $chunks = [];
        $current = $header . PHP_EOL;
        foreach ($lines as $line) {
            if (strlen($current) + strlen($line) + 1 > $maxLen) {
                $chunks[] = $current;
                $current = $header . PHP_EOL;
            }
            $current .= $line . PHP_EOL;
        }
        if (trim($current) !== $header) $chunks[] = $current;
        return $chunks;
    }
}
