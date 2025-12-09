<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuario Administrador (Evita duplicados si ya existe)
        User::firstOrCreate(
            ['email' => 'admin@ei-comercio.com'], // Busca por email
            [
                'name' => 'Admin Sistema',
                'rfc' => 'XAXX010101000', 
                'username' => 'admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // 2. Usuario Operador Demo (Evita duplicados)
        User::firstOrCreate(
            ['email' => 'operador@ei-comercio.com'],
            [
                'name' => 'Operador Demo',
                'rfc' => 'XAXX010101001',
                'username' => 'operador',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );
    }
}