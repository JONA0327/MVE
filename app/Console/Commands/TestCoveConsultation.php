<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarCoveService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TestCoveConsultation extends Command
{
    protected $signature = 'cove:test {folio=ABC123456} {--user-id=1}';
    protected $description = 'Probar consulta COVE con un folio específico';

    public function handle()
    {
        $folio = $this->argument('folio');
        $userId = $this->option('user-id');
        $startTime = microtime(true); // Mover aquí para estar disponible en catch

        $this->info("=== PRUEBA DE CONSULTA COVE ===");
        $this->info("Folio: {$folio}");
        
        try {
            // Buscar usuario
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuario con ID {$userId} no encontrado");
                return 1;
            }

            $this->info("Usuario: {$user->name} ({$user->email})");
            
            // Verificar credenciales
            if (!$user->rfc) {
                $this->error("El usuario no tiene RFC configurado");
                return 1;
            }
            
            if (!$user->webservice_key) {
                $this->error("El usuario no tiene clave webservice configurada");
                return 1;
            }
            
            $this->info("RFC: {$user->rfc}");
            $this->info("Webservice Key: ***" . substr($user->webservice_key, -4));
            
            // Simular autenticación
            auth()->login($user);
            
            $this->info("\n=== INICIANDO CONSULTA SOAP ===");
            $soapStartTime = microtime(true);
            
            // Crear instancia del servicio
            $consultarService = new ConsultarCoveService();
            
            // Hacer la consulta
            $result = $consultarService->consultarCove($folio);
            
            $executionTime = round((microtime(true) - $soapStartTime) * 1000);
            
            $this->info("=== RESULTADO ===");
            $this->info("Tiempo de ejecución: {$executionTime}ms");
            
            if ($result['success']) {
                $this->info("✅ Consulta exitosa");
                
                if (isset($result['data'])) {
                    $this->info("Datos recibidos:");
                    $this->line(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            } else {
                $this->warn("⚠️ Consulta falló");
                $this->error("Error: " . ($result['message'] ?? 'Sin mensaje de error'));
                
                if (isset($result['soap_fault'])) {
                    $this->error("SOAP Fault: " . $result['soap_fault']);
                }
            }
            
            return $result['success'] ? 0 : 1;
            
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            $this->error("=== ERROR ===");
            $this->error("Tiempo hasta error: {$executionTime}ms");
            $this->error("Mensaje: " . $e->getMessage());
            $this->error("Archivo: " . $e->getFile() . ":" . $e->getLine());
            
            return 1;
        }
    }
}