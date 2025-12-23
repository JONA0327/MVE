<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarCoveService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestCoveConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cove:test-config {--user=1 : ID del usuario para probar} {--cove=12345 : Folio de COVE para probar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la configuración del servicio de consulta COVE de VUCEM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Probando configuración del servicio COVE...');
        $this->newLine();

        // Obtener y autenticar usuario
        $userId = $this->option('user');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error('❌ Usuario no encontrado con ID: ' . $userId);
            $this->line('Usuarios disponibles:');
            $users = User::select('id', 'name', 'rfc')->take(10)->get();
            foreach ($users as $u) {
                $this->line("  ID: {$u->id} - {$u->name} - RFC: {$u->rfc}");
            }
            return 1;
        }

        // Simular autenticación del usuario
        Auth::login($user);
        $this->info("👤 Usuario autenticado: {$user->name} (RFC: {$user->rfc})");
        $this->newLine();

        // Verificar configuración del perfil del usuario
        $this->info('📋 Verificando perfil del usuario:');
        
        $hasRfc = !empty($user->rfc);
        $hasWebserviceKey = $user->hasWebserviceKey();
        $wsdlExists = file_exists(config('vucem.consultar_cove.wsdl_path'));
        $soapEnabled = extension_loaded('soap');

        $this->line("  ✓ RFC configurado: " . ($hasRfc ? "✅ SÍ ({$user->rfc})" : '❌ NO'));
        $this->line("  ✓ Clave webservice: " . ($hasWebserviceKey ? '✅ SÍ (encriptada)' : '❌ NO - Configure en Mi Perfil'));
        $this->line("  ✓ WSDL existe: " . ($wsdlExists ? '✅ SÍ' : '❌ NO'));
        $this->line("  ✓ Extensión SOAP: " . ($soapEnabled ? '✅ SÍ' : '❌ NO'));
        
        if (!$hasRfc || !$hasWebserviceKey) {
            $this->newLine();
            if (!$hasRfc) {
                $this->error('❌ RFC no configurado en el perfil del usuario');
            }
            if (!$hasWebserviceKey) {
                $this->error('❌ Clave de webservice no configurada. El usuario debe ir a Mi Perfil > Configuración de Webservice VUCEM');
            }
            return 1;
        }

        if (!$wsdlExists) {
            $this->newLine();
            $this->error('❌ Archivo WSDL no encontrado en: ' . config('vucem.consultar_cove.wsdl_path'));
            return 1;
        }

        if (!$soapEnabled) {
            $this->newLine();
            $this->error('❌ Extensión PHP SOAP no está habilitada');
            return 1;
        }

        $this->newLine();
        $this->info('🧪 Probando servicio COVE...');

        try {
            $service = new ConsultarCoveService();
            $folioCove = $this->option('cove');
            
            $this->line("Consultando COVE: {$folioCove}");
            
            $result = $service->consultarCove($folioCove);
            
            if ($result['success']) {
                $this->newLine();
                $this->info('✅ ¡Prueba exitosa!');
                $this->table(
                    ['Campo', 'Valor'],
                    [
                        ['COVE', $result['data']['cove']],
                        ['Número Factura', $result['data']['numero_factura']],
                        ['Fecha Expedición', $result['data']['fecha_expedicion']],
                        ['Emisor', $result['data']['emisor']],
                        ['eDocument', $result['data']['edocument'] ?? 'N/A'],
                    ]
                );
            } else {
                $this->newLine();
                $this->warn('⚠️  Respuesta del servicio:');
                $this->line("Tipo de error: {$result['error_type']}");
                $this->line("Mensaje: {$result['message']}");
                
                if (isset($result['details'])) {
                    $this->line("Detalles: " . json_encode($result['details'], JSON_PRETTY_PRINT));
                }
            }

            $this->newLine();
            $this->info('🔍 Información de depuración:');
            $debugInfo = $service->getDebugInfo();
            
            if (!empty($debugInfo['last_request'])) {
                $this->line('Último Request SOAP enviado:');
                $this->line($debugInfo['last_request']);
            }
            
            if (!empty($debugInfo['last_response'])) {
                $this->line('Última Response SOAP recibida:');
                $this->line($debugInfo['last_response']);
            }

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('💥 Error inesperado:');
            $this->line($e->getMessage());
            $this->line('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }

        return 0;
    }
}
