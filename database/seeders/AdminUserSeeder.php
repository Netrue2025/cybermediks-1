<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cybermediks.test'],
            [
                'first_name' => 'System',
                'last_name'  => 'Admin',
                'password'   => bcrypt('password'), // change!
                'role'       => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
