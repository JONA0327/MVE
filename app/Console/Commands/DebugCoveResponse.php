<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarCoveService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;

class DebugCoveResponse extends Command
{
    protected $signature = 'debug:cove {folio}';
    protected $description = 'Muestra la respuesta CRUDA de VUCEM (Versión Mejorada)';

    public function handle()
    {
        $folio = $this->argument('folio');
        
        // 1. Simular Login
        $this->info("🔍 Buscando usuario con credenciales...");
        // Buscamos un usuario que tenga RFC y webservice_key (ya que lo agregaste en BD)
        $user = User::whereNotNull('rfc')->first(); 
        
        if (!$user) {
            $this->error("❌ ERROR: No se encontró ningún usuario con RFC.");
            return;
        }

        Auth::login($user);
        $this->info("👤 Usuario: {$user->email} | RFC: {$user->rfc}");
        $this->newLine();

        try {
            // 2. Instanciar servicio
            $service = app(ConsultarCoveService::class);
            
            $this->info("🚀 Consultando Folio: $folio ...");
            $result = $service->consultarCove($folio);

            // 3. Imprimir Resultado del Servicio (Éxito o Error)
            $this->newLine();
            if ($result['success']) {
                $this->info("✅ RESPUESTA EXITOSA (Laravel):");
                $this->table(['Campo', 'Valor'], collect($result['data'] ?? [])->map(fn($v, $k) => [$k, $v]));
            } else {
                $this->error("❌ EL SERVICIO FALLÓ (Laravel):");
                $this->warn("Mensaje: " . ($result['message'] ?? 'Sin mensaje'));
                $this->warn("Tipo Error: " . ($result['error_type'] ?? 'Desconocido'));
                if (isset($result['details'])) {
                    $this->line("Detalles: " . json_encode($result['details'], JSON_PRETTY_PRINT));
                }
            }

            // 4. EXTRACCIÓN FORZADA DEL XML (Usando Reflection)
            // Esto funcionará incluso si el servicio no guardó el debug info
            $this->newLine();
            $this->warn('--- DEBUG SOAP (Extracción Directa) ---');
            
            $reflection = new ReflectionClass($service);
            
            // Acceder a la propiedad privada 'soapClient'
            if ($reflection->hasProperty('soapClient')) {
                $property = $reflection->getProperty('soapClient');
                $property->setAccessible(true);
                $soapClient = $property->getValue($service);

                if ($soapClient) {
                    $this->info("📤 REQUEST XML (Lo que enviamos):");
                    $req = $soapClient->__getLastRequest();
                    $this->line($req ? $req : "⚠️ No se generó Request (¿Falló antes de enviar?)");
                    
                    $this->newLine();
                    $this->info("📥 RESPONSE XML (Lo que contestó VUCEM):");
                    $res = $soapClient->__getLastResponse();
                    $this->line($res ? $res : "⚠️ Vacío (VUCEM no contestó o conexión falló)");
                } else {
                    $this->error("⚠️ El Cliente SOAP es null (Falló la inicialización)");
                }
            } else {
                $this->error("⚠️ No se pudo acceder a la propiedad soapClient");
            }

        } catch (\Exception $e) {
            $this->error("💥 EXCEPCIÓN NO CONTROLADA: " . $e->getMessage());
            $this->line($e->getTraceAsString());
        }
    }
}