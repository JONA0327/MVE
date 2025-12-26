<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestLogin extends Command
{
    protected $signature = 'test:login {username} {rfc} {password}';
    protected $description = 'Prueba el proceso de autenticación con RFC encriptado';

    public function handle()
    {
        $inputUsername = $this->argument('username');
        $inputRfc = strtoupper($this->argument('rfc'));
        $inputPassword = $this->argument('password');

        $this->info('=== PROBANDO AUTENTICACIÓN ===');
        $this->info("Username: {$inputUsername}");
        $this->info("RFC: {$inputRfc}");
        
        // Buscar usuario por username
        $user = User::where('username', $inputUsername)->first();
        
        if (!$user) {
            $this->error('❌ Usuario no encontrado');
            
            // Mostrar usuarios disponibles
            $this->info("\nUsuarios disponibles:");
            $users = User::select('id', 'username', 'name')->take(5)->get();
            foreach ($users as $u) {
                $decryptedRfc = $u->rfc; // Se desencripta automáticamente
                $this->line("ID: {$u->id} - Username: {$u->username} - Name: {$u->name} - RFC: {$decryptedRfc}");
            }
            return 1;
        }
        
        $this->info("✅ Usuario encontrado: {$user->name}");
        
        // Verificar RFC
        $decryptedRfc = $user->rfc; // Se desencripta automáticamente por el accessor
        $this->info("RFC en BD (desencriptado): {$decryptedRfc}");
        
        if ($decryptedRfc !== $inputRfc) {
            $this->error('❌ RFC no coincide');
            $this->error("Esperado: {$inputRfc}");
            $this->error("En BD: {$decryptedRfc}");
            return 1;
        }
        
        $this->info('✅ RFC coincide');
        
        // Verificar contraseña
        if (!Hash::check($inputPassword, $user->password)) {
            $this->error('❌ Contraseña incorrecta');
            return 1;
        }
        
        $this->info('✅ Contraseña correcta');
        $this->info('🎉 ¡Autenticación exitosa!');
        
        return 0;
    }
}