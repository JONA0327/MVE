<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\CoveConsultaService;
use App\Exceptions\CoveConsultaException;

class TestCoveConsulta extends Command
{
    protected $signature = 'test:cove-consulta {numeroOperacion : Número de operación a consultar}';
    protected $description = 'Prueba la consulta COVE usando CoveConsultaService con WSDL';

    public function handle()
    {
        $numeroOperacion = (int) $this->argument('numeroOperacion');
        
        // Autenticar usuario para obtener credenciales
        $user = \App\Models\User::first();
        if (!$user) {
            $this->error("❌ No hay usuarios en la base de datos");
            return 1;
        }
        
        auth()->login($user);
        
        $this->info("🔍 PRUEBA CONSULTA COVE - NUEVA IMPLEMENTACIÓN");
        $this->info("================================================");
        $this->newLine();

        $this->line("📋 Configuración:");
        $this->line("   • Usuario: " . $user->name . " (RFC: " . $user->rfc . ")");
        $this->line("   • Webservice Key: " . ($user->webservice_key ? '***' . substr($user->webservice_key, -4) : 'NO CONFIGURADA'));
        $this->line("   • RFC Sello: " . config('vucem.rfc'));
        $this->line("   • Número Operación: {$numeroOperacion}");
        $this->line("   • WSDL: " . base_path('wsdl/vucem/COVE/ConsultarRespuestaCove.wsdl'));
        $this->newLine();

        try {
            $startTime = microtime(true);
            
            $coveService = app(CoveConsultaService::class);
            
            $this->info("🚀 Iniciando consulta...");
            $resultado = $coveService->consultarPorNumeroOperacion($numeroOperacion);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            $this->line("⏱️  Tiempo de ejecución: {$duration}ms");
            $this->newLine();
            
            $this->info("📊 RESULTADO:");
            $this->info("=============");
            
            $this->line("   • Número Operación: " . ($resultado['numeroOperacion'] ?? 'N/A'));
            $this->line("   • Hora Recepción: " . ($resultado['horaRecepcion'] ?? 'N/A'));
            $this->line("   • Leyenda: " . ($resultado['leyenda'] ?? 'N/A'));
            $this->line("   • Total Resultados: " . count($resultado['resultados']));
            
            $this->newLine();
            
            if (!empty($resultado['resultados'])) {
                $this->info("🔍 DETALLES DE RESULTADOS:");
                foreach ($resultado['resultados'] as $index => $res) {
                    $this->line("   Resultado #" . ($index + 1) . ":");
                    $this->line("     • Número Factura: " . ($res['numeroFactura'] ?? 'N/A'));
                    $this->line("     • Contiene Error: " . ($res['contieneError'] ? 'SÍ' : 'NO'));
                    
                    if ($res['eDocument']) {
                        $this->line("     • eDocument (COVE): " . $res['eDocument']);
                    }
                    
                    if (!empty($res['errores'])) {
                        $this->line("     • Errores:");
                        foreach ($res['errores'] as $error) {
                            $this->line("       - " . $error);
                        }
                    }
                    $this->newLine();
                }
            } else {
                $this->warn("⚠️  No se encontraron resultados");
            }
            
            // Mostrar debug info si se solicita
            if ($this->option('verbose')) {
                $debug = $coveService->getDebugInfo();
                $this->newLine();
                $this->info("🔧 DEBUG INFO:");
                $this->line("Request XML:");
                $this->line($debug['last_request'] ?? 'N/A');
                $this->newLine();
                $this->line("Response XML:");
                $this->line($debug['last_response'] ?? 'N/A');
            }
            
            $this->newLine();
            $this->info("✅ Consulta completada exitosamente");
            
            return 0;
            
        } catch (CoveConsultaException $e) {
            $this->error("❌ Error en consulta COVE: " . $e->getMessage());
            return 1;
            
        } catch (\Exception $e) {
            $this->error("💥 Error inesperado: " . $e->getMessage());
            $this->line("Archivo: " . $e->getFile() . ":" . $e->getLine());
            return 1;
        }
    }
}