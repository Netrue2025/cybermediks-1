<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            [
                'name'  => 'Cardiology',
                'icon'  => 'fa-regular fa-heart',
                'color' => '#f472b6', // pink
            ],
            [
                'name'  => 'Dermatology',
                'icon'  => 'fa-regular fa-sun',
                'color' => '#fbbf24', // yellow
            ],
            [
                'name'  => 'Neurology',
                'icon'  => 'fa-solid fa-brain',
                'color' => '#a78bfa', // purple
            ],
            [
                'name'  => 'Orthopedics',
                'icon'  => 'fa-solid fa-bone',
                'color' => '#93c5fd', // blue
            ],
            [
                'name'  => 'Pediatrics',
                'icon'  => 'fa-solid fa-baby',
                'color' => '#fb7185', // red
            ],
            [
                'name'  => 'Psychiatry',
                'icon'  => 'fa-solid fa-user',
                'color' => '#a0aec0', // gray
            ],
            [
                'name'  => 'General Medicine',
                'icon'  => 'fa-solid fa-stethoscope',
                'color' => '#60a5fa', // blue
            ],
            [
                'name'  => 'Radiology',
                'icon'  => 'fa-solid fa-x-ray',
                'color' => '#38bdf8', // cyan
            ],
            [
                'name'  => 'Ophthalmology',
                'icon'  => 'fa-regular fa-eye',
                'color' => '#22c55e', // green
            ],
            [
                'name'  => 'Gynecology',
                'icon'  => 'fa-solid fa-venus',
                'color' => '#ec4899', // pink
            ],
        ];

        foreach ($specialties as $spec) {
            DB::table('specialties')->updateOrInsert(
                ['name' => $spec['name']],
                [
                    'slug'       => Str::slug($spec['name']),
                    'icon'       => $spec['icon'],
                    'color'      => $spec['color'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
