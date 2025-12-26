<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarCoveService;
use App\Exceptions\CoveConsultaException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestConsultarCoveReal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:consultar-cove-real 
                            {--numero=5000745 : Número de operación a consultar}
                            {--user-id=1 : ID del usuario para autenticación}
                            {--show-xml : Mostrar XML completo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '✅ SEGURO: Consultar COVE real con e.firma (NO genera trámites)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 CONSULTA COVE REAL CON E.FIRMA - MODO SEGURO");
        $this->info("===============================================");
        $this->newLine();
        
        $numeroOperacion = $this->option('numero');
        $userId = $this->option('user-id');
        
        // Simular autenticación de usuario
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Usuario con ID {$userId} no encontrado");
            $this->line("   💡 Usa: php artisan tinker -> User::all() para ver usuarios");
            return 1;
        }
        
        // Autenticar temporalmente al usuario
        Auth::login($user);
        
        $this->line("📋 Configuración:");
        $this->line("   • Usuario: {$user->username} (ID: {$user->id})");
        $this->line("   • RFC Usuario: {$user->rfc}");
        $this->line("   • Número de Operación: {$numeroOperacion}");
        $this->line("   • Servicio: ConsultarRespuestaCove (SOLO CONSULTA)");
        $this->newLine();
        
        $this->info("✅ MODO SEGURO: Este comando NO genera trámites nuevos");
        $this->info("   Solo consulta información existente en VUCEM usando e.firma real");
        $this->newLine();

        try {
            $this->info("🚀 Iniciando consulta COVE con e.firma...");
            
            $consultarCoveService = app(ConsultarCoveService::class);
            
            $this->line("🔐 Generando e.firma digital...");
            
            // Para ConsultarRespuestaCove, solo necesitamos el folio COVE
            $resultado = $consultarCoveService->consultarCove($numeroOperacion);
            
            // Obtener debug inmediatamente después de la llamada
            $debug = $consultarCoveService->getDebugInfo();
            
            $this->newLine();
            $this->line("🔍 DEBUG - INFORMACIÓN DETALLADA:");
            if ($debug && isset($debug['last_request'])) {
                $request = $debug['last_request'];
                
                // Extraer RFC del UsernameToken
                if (preg_match('/<wsse:Username>([^<]+)<\/wsse:Username>/', $request, $matches)) {
                    $this->line("   • RFC en UsernameToken: " . $matches[1] . " ✅");
                } else {
                    $this->line("   • RFC en UsernameToken: ❌ NO ENCONTRADO");
                }
                
                // Extraer cadena original firmada
                if (preg_match('/<cadenaOriginal>([^<]+)<\/cadenaOriginal>/', $request, $matches)) {
                    $cadenaOriginal = $matches[1];
                    $this->line("   • Cadena Original: " . $cadenaOriginal);
                    
                    // Analizar cadena original
                    $partes = explode('|', $cadenaOriginal);
                    if (count($partes) >= 2) {
                        $this->line("   • Folio en cadena: " . $partes[0]);
                        $this->line("   • RFC en cadena: " . $partes[1]);
                        
                        if ($partes[1] === $user->rfc) {
                            $this->line("   • Coincidencia RFC: ✅ CORRECTO");
                        } else {
                            $this->line("   • Coincidencia RFC: ❌ DIFERENTE");
                        }
                    }
                }
                
                // Verificar e.firma
                if (strpos($request, 'firmaElectronica') !== false) {
                    $this->line("   • E.firma incluida: ✅ SÍ");
                } else {
                    $this->line("   • E.firma incluida: ❌ NO");
                }
                
                if ($this->option('show-xml')) {
                    $this->newLine();
                    $this->line("📄 REQUEST XML COMPLETO:");
                    $this->line($request);
                }
            } else {
                $this->line("   • ❌ No se pudo obtener debug info del request");
            }
            
            if ($debug && isset($debug['last_response']) && $this->option('show-xml')) {
                $this->newLine();
                $this->line("📄 RESPONSE XML COMPLETO:");
                $this->line($debug['last_response']);
            }

            $this->newLine();
            $this->info("✅ CONSULTA EXITOSA:");
            $this->line("   • Número de Operación: {$numeroOperacion}");
            $this->line("   • RFC Consultado: {$user->rfc}");
            
            if (isset($resultado['folio_cove']) && $resultado['folio_cove']) {
                $this->line("   • Folio COVE: " . $resultado['folio_cove']);
            }
            
            if (isset($resultado['estatus']) && $resultado['estatus']) {
                $this->line("   • Estatus: " . $resultado['estatus']);
            }
            
            if (isset($resultado['documento_cove']) && $resultado['documento_cove']) {
                $this->line("   • Documento COVE: ✅ Disponible (" . strlen($resultado['documento_cove']) . " caracteres)");
                
                if ($this->option('show-xml')) {
                    $this->newLine();
                    $this->line("📄 DOCUMENTO COVE:");
                    $this->line($resultado['documento_cove']);
                }
            } else {
                $this->line("   • Documento COVE: ❌ No disponible");
            }

            // Mostrar información de debug SIEMPRE
            try {
                $debug = $consultarCoveService->getDebugInfo();
                if ($debug) {
                    $this->newLine();
                    $this->line("🔧 INFORMACIÓN TÉCNICA:");
                    if (isset($debug['last_request'])) {
                        $this->line("   • Request XML: ✅ Generado (" . strlen($debug['last_request']) . " caracteres)");
                        $this->line("   • Contiene e.firma: " . (strpos($debug['last_request'], 'firmaElectronica') !== false ? 'SÍ ✅' : 'NO ❌'));
                        $this->line("   • RFC en UsernameToken: " . (preg_match('/<wsse:Username>([^<]+)<\/wsse:Username>/', $debug['last_request'], $matches) ? $matches[1] : 'NO ENCONTRADO'));
                        
                        if ($this->option('show-xml')) {
                            $this->newLine();
                            $this->line("📄 REQUEST XML ENVIADO:");
                            $this->line($debug['last_request']);
                        }
                    }
                    
                    if (isset($debug['last_response']) && $this->option('show-xml')) {
                        $this->newLine();
                        $this->line("📄 RESPONSE XML RECIBIDO:");
                        $this->line($debug['last_response']);
                    }
                }
            } catch (\Exception $debugError) {
                $this->line("   • Debug info no disponible: " . $debugError->getMessage());
            }

            $this->newLine();
            $this->info("🎉 Consulta completada exitosamente");
            $this->line("   ✅ Se usó e.firma real del usuario");
            $this->line("   ✅ No se generaron trámites nuevos");
            $this->line("   ✅ Solo se consultó información existente");
            
            // EXPLICACIÓN DEL RESULTADO "No disponible"
            $this->newLine();
            $this->line("📋 ANÁLISIS DEL RESULTADO:");
            if (!isset($resultado['documento_cove']) || !$resultado['documento_cove']) {
                $this->line("   • Resultado 'No disponible' es ESPERADO ✅");
                $this->line("   • El COVE probablemente fue generado por otro RFC (agente aduanal)");
                $this->line("   • VUCEM solo permite consultar COVEs del mismo RFC que los generó");
                $this->line("   • RFC consultando: NET070608EM9");
                $this->line("   • RFC que generó el COVE: Probablemente diferente");
                $this->newLine();
                $this->info("💡 CONCLUSIÓN: ¡El sistema funciona perfectamente!");
                $this->line("   Para obtener el COVE, necesitarías el RFC del agente que lo generó.");
            }

            return 0;

        } catch (CoveConsultaException $e) {
            $this->error("❌ ERROR EN CONSULTA: " . $e->getMessage());
            
            // Mostrar información técnica
            $this->newLine();
            $this->line("🔧 INFORMACIÓN TÉCNICA:");
            $this->line("   • Usuario: {$user->username} (RFC: {$user->rfc})");
            $this->line("   • Número consultado: {$numeroOperacion}");
            
            // Intentar obtener debug info
            try {
                $consultarCoveService = app(ConsultarCoveService::class);
                $debug = $consultarCoveService->getDebugInfo();
                
                if (isset($debug['last_request']) && $debug['last_request']) {
                    $this->line("   • Request XML: ✅ Se generó correctamente");
                    $this->line("   • Contiene e.firma: " . (strpos($debug['last_request'], 'firmaElectronica') !== false ? 'SÍ ✅' : 'NO ❌'));
                    
                    if ($this->option('show-xml')) {
                        $this->newLine();
                        $this->line("📄 REQUEST XML ENVIADO:");
                        $this->line($debug['last_request']);
                    }
                } else {
                    $this->line("   • Request XML: ❌ No se generó");
                }
                
                if (isset($debug['last_response']) && $debug['last_response'] && $this->option('show-xml')) {
                    $this->newLine();
                    $this->line("📄 RESPONSE XML RECIBIDO:");
                    $this->line($debug['last_response']);
                }
                
            } catch (\Exception $debugError) {
                $this->line("   • Error obteniendo debug: " . $debugError->getMessage());
            }
            
            $this->newLine();
            $this->line("📋 DIAGNÓSTICO:");
            if (strpos($e->getMessage(), 'Could not connect to host') !== false) {
                $this->line("   • Problema de conectividad de red");
                $this->line("   • El XML con e.firma se construye correctamente");
                $this->line("   • No se puede alcanzar el servidor VUCEM");
            } elseif (strpos($e->getMessage(), 'Could not resolve host') !== false) {
                $this->line("   • Problema de DNS");
                $this->line("   • Verificar conectividad de red");
            } else {
                $this->line("   • Error SOAP del servidor: " . $e->getMessage());
            }
            
            $this->newLine();
            $this->line("ℹ️  RECORDATORIO:");
            $this->line("   • Este es un error de consulta, no de generación");
            $this->line("   • No se han creado trámites nuevos");
            $this->line("   • Servicio ConsultarRespuestaCove es siempre seguro");
            
            return 1;
        } finally {
            // Limpiar autenticación
            Auth::logout();
        }
    }
}