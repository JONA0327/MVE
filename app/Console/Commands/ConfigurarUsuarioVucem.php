<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ConfigurarUsuarioVucem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vucem:configurar-usuario {user-id : ID del usuario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar contraseña VUCEM para un usuario';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user-id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Usuario con ID {$userId} no encontrado");
            return 1;
        }
        
        $vucemPassword = env('VUCEM_WS_PASSWORD');
        if (!$vucemPassword) {
            $this->error("❌ VUCEM_WS_PASSWORD no configurado en .env");
            return 1;
        }
        
        $user->vucem_ws_password = $vucemPassword;
        $user->save();
        
        $this->info("✅ Usuario configurado exitosamente:");
        $this->line("   • ID: {$user->id}");
        $this->line("   • Username: {$user->username}");
        $this->line("   • RFC: {$user->rfc}");
        $this->line("   • Password VUCEM: ✅ Configurado");
        
        return 0;
    }
}