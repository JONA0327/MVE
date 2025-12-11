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
        // 1. SUPER ADMINISTRADOR (Dueño del Sistema)
        $superAdmin = User::create([
            'name' => 'Super Admin Master',
            'username' => 'SUPERADMIN',
            'rfc' => 'XAXX010101000', // RFC Genérico
            'email' => 'super@admin.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_admin' => true, // Compatibilidad
        ]);

        // 2. ADMINISTRADOR (Cliente Principal)
        // Este usuario es creado "por el sistema" o por el super admin
        $adminUser = User::create([
            'name' => 'Cliente Administrador SA de CV',
            'username' => 'ADMIN_CLIENTE',
            'rfc' => 'CUA1001010000', // RFC de ejemplo
            'email' => 'cliente@empresa.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'parent_id' => $superAdmin->id, // Opcional: El Super Admin lo creó
            'is_admin' => true, // Compatibilidad
        ]);

        // 3. OPERADOR (Empleado del Cliente)
        // Este usuario DEBE tener parent_id apuntando al Admin anterior para que la lógica funcione
        User::create([
            'name' => 'Juan Pérez (Operador)',
            'username' => 'juan.operador',
            'rfc' => 'PEPJ800101000',
            'email' => 'juan@empresa.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'parent_id' => $adminUser->id, // CRÍTICO: Pertenece al Admin de arriba
            'is_admin' => false,
        ]);
        
        // Operador 2 para pruebas de límite
        User::create([
            'name' => 'Ana Gomez (Operador 2)',
            'username' => 'ana.operador',
            'rfc' => 'GOAN900101000',
            'email' => 'ana@empresa.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'parent_id' => $adminUser->id,
            'is_admin' => false,
        ]);
    }
}