<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarCoveService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestEFirmaConsulta extends Command
{
    protected $signature = 'efirma:test {folio} {--user=1}';
    protected $description = 'Prueba consulta COVE completa con e.firma';

    public function handle()
    {
        $folio = $this->argument('folio');
        $userId = $this->option('user');

        $this->info('🔐 PRUEBA CONSULTA COVE CON E.FIRMA');
        $this->info('==================================');
        $this->newLine();

        try {
            // Autenticación
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuario no encontrado con ID: {$userId}");
                return 1;
            }

            Auth::login($user);
            $this->info("👤 Usuario autenticado: {$user->name} (RFC: {$user->rfc})");
            
            // Verificar credenciales
            if (!$user->hasWebserviceKey()) {
                $this->error('❌ Usuario no tiene clave webservice configurada');
                return 1;
            }
            
            $this->info("🔑 Webservice Key: ***" . substr($user->getDecryptedWebserviceKey(), -4));
            $this->newLine();

            // Información del folio
            $this->info("🎯 Consultando folio: {$folio}");
            $this->info("   • Longitud: " . strlen($folio) . " dígitos");
            $this->info("   • ¿Válido? " . (strlen($folio) === 15 && ctype_digit($folio) ? "SÍ ✅" : "NO ❌"));
            $this->newLine();

            // Realizar consulta
            $this->info('🚀 Iniciando consulta con e.firma...');
            $startTime = microtime(true);

            $consultarService = new ConsultarCoveService();
            $resultado = $consultarService->consultarCove($folio);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("⏱️ Tiempo de ejecución: {$executionTime}ms");
            $this->newLine();

            // Mostrar resultado
            $this->info('📊 RESULTADO:');
            $this->info('=============');

            if ($resultado['success']) {
                $this->info('✅ CONSULTA EXITOSA');
                $this->line("   • Mensaje: {$resultado['message']}");
                if (!empty($resultado['data'])) {
                    $this->line("   • Datos: " . substr($resultado['data'], 0, 200) . "...");
                }
            } else {
                $this->error('⚠️ CONSULTA SIN RESULTADO');
                $this->line("   • Error: " . ($resultado['error'] ?? 'Sin error específico'));
                $this->line("   • Mensaje: " . ($resultado['message'] ?? 'Sin mensaje'));
            }

            $this->newLine();

            // Debug info si está disponible
            $debugInfo = $consultarService->getDebugInfo();
            if (!empty($debugInfo)) {
                $this->info('🔍 Información de debug:');
                $this->line('   • Request enviado: ' . (empty($debugInfo['last_request']) ? 'No disponible' : 'Disponible'));
                $this->line('   • Response recibido: ' . (empty($debugInfo['last_response']) ? 'No disponible' : 'Disponible'));
                
                if ($this->option('verbose')) {
                    $this->newLine();
                    $this->info('📝 Request SOAP:');
                    $this->line($debugInfo['last_request'] ?? 'No disponible');
                    $this->newLine();
                    $this->info('📥 Response SOAP:');
                    $this->line($debugInfo['last_response'] ?? 'No disponible');
                }
            }

            $this->newLine();
            $this->info('✨ Prueba completada. La e.firma está funcionando correctamente.');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error en consulta con e.firma: ' . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            
            return 1;
        }
    }
}