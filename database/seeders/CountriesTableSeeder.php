<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CountriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/countries.json');

        if (! File::exists($path)) {
            $this->command->error("countries.json not found at: {$path}");
            $this->command->warn('Create the file and rerun: php artisan db:seed --class=CountriesTableSeeder');
            return;
        }

        $json = json_decode(File::get($path), true);

        if (! is_array($json)) {
            $this->command->error('countries.json is not a valid JSON array.');
            return;
        }

        // Prepare upsert payload
        $rows = array_map(function ($c) {
            return [
                'name'          => $c['name'] ?? null,
                'iso2'          => strtoupper($c['iso2'] ?? ''),
                'short'         => strtoupper($c['iso2'] ?? ''),              // minimal alias
                'currency_code' => strtoupper($c['currency'] ?? ''),           // dr5hn uses "currency"
                'currency_symbol' => $c['currency_symbol'] ?? null,
                'phone_code'    => isset($c['phone_code']) ? (string)$c['phone_code'] : null,
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }, $json);

        // Upsert by ISO2 to avoid duplicates on re-seed
        Country::upsert($rows, ['iso2'], ['name', 'short', 'currency_code', 'currency_symbol', 'phone_code', 'is_active', 'updated_at']);

        $this->command->info('Countries seeded/updated: ' . count($rows));
    }
}
