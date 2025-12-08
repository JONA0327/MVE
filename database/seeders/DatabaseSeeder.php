<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Admin Sistema',
            'email' => 'admin@test.com',
            'rfc' => 'XAXX010101000', // RFC Genérico
            'username' => 'admin1',
            'password' => bcrypt('password'), // O Hash::make('password')
        ]);
    }
}
