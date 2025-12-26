<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarCoveService;
use App\Exceptions\CoveConsultaException;

class TestConsultarCove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:consultar-cove 
                            {--numero=5000745 : Número de operación a consultar}
                            {--show-xml : Mostrar XML completo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '✅ SEGURO: Consultar COVE existente (no genera trámites nuevos)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 CONSULTA SEGURA DE COVE - CONSULTAR RESPUESTA COVE");
        $this->info("=======================================================");
        $this->newLine();
        
        $numeroOperacion = $this->option('numero');
        
        $this->line("📋 Configuración:");
        $this->line("   • RFC: " . config('vucem.rfc'));
        $this->line("   • Número de Operación: {$numeroOperacion}");
        $this->line("   • Servicio: ConsultarRespuestaCove (SOLO CONSULTA)");
        $this->newLine();
        
        $this->info("✅ MODO SEGURO: Este comando NO genera trámites nuevos");
        $this->info("   Solo consulta información existente en VUCEM");
        $this->newLine();

        try {
            // Para modo de prueba, crear un mock de ConsultarCoveService
            // que no requiera autenticación
            $this->info("🚀 Iniciando consulta de COVE...");
            
            $this->warn("⚠️ NOTA: Implementación de ConsultarCoveService pendiente");
            $this->line("   • El servicio requiere usuario autenticado");
            $this->line("   • Para pruebas completas, autenticar usuario primero");
            $this->newLine();
            
            $this->info("💡 DEMOSTRACIÓN DEL FLUJO SEGURO:");
            $this->line("   ✅ 1. Consultar número de operación: {$numeroOperacion}");
            $this->line("   ✅ 2. Verificar existencia de COVE");
            $this->line("   ✅ 3. Si existe, obtener documento");
            $this->line("   ✅ 4. NO generar trámites nuevos");
            
            $this->newLine();
            $this->info("🔧 PARA IMPLEMENTAR:");
            $this->line("   • Autenticar usuario: php artisan tinker -> User::first()");
            $this->line("   • Ejecutar consulta con usuario autenticado");
            $this->line("   • El XML se generará con e.firma del usuario");

            $this->newLine();
            $this->info("🎯 RESULTADO ESPERADO:");
            $this->line("   • Si número {$numeroOperacion} existe: Devuelve COVE");
            $this->line("   • Si no existe: Mensaje 'No encontrado'");  
            $this->line("   • En ambos casos: NO se generan trámites nuevos");

            return 0;

        } catch (CoveConsultaException $e) {
            $this->error("❌ ERROR EN CONSULTA: " . $e->getMessage());
            
            // Mostrar información técnica
            $this->newLine();
            $this->line("🔧 INFORMACIÓN TÉCNICA:");
            
            try {
                $debug = $consultarCoveService->getDebugInfo();
                if ($debug['last_request'] && $this->option('show-xml')) {
                    $this->newLine();
                    $this->line("📄 XML REQUEST:");
                    $this->line($debug['last_request']);
                }
                if ($debug['last_response'] && $this->option('show-xml')) {
                    $this->newLine();
                    $this->line("📄 XML RESPONSE:");
                    $this->line($debug['last_response']);
                }
            } catch (\Exception $debugError) {
                $this->line("   • Error obteniendo debug: " . $debugError->getMessage());
            }
            
            $this->newLine();
            $this->line("ℹ️  RECORDATORIO:");
            $this->line("   • Este es un error de consulta, no de generación");
            $this->line("   • No se han creado trámites nuevos");
            $this->line("   • Servicio ConsultarRespuestaCove es siempre seguro");
            
            return 1;
        }
    }
}