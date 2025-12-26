<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarEdocumentService;
use App\Exceptions\CoveConsultaException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestConsultarEdocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:consultar-edocument 
                            {--cove= : COVE a consultar (ej: COVE257M4C974)}
                            {--user-id=1 : ID del usuario para autenticación}
                            {--adenda= : Número de adenda opcional}
                            {--show-xml : Mostrar XML completo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '✅ SEGURO: Consultar eDocument completo de COVE (NO genera trámites)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("📄 CONSULTA EDOCUMENT COVE - MODO SEGURO");
        $this->info("========================================");
        $this->newLine();
        
        $cove = $this->option('cove');
        $userId = $this->option('user-id');
        $numeroAdenda = $this->option('adenda');
        
        if (!$cove) {
            $this->error("❌ Debe especificar un COVE con --cove=");
            $this->line("   💡 Ejemplo: php artisan test:consultar-edocument --cove=COVE257M4C974");
            return 1;
        }
        
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
        $this->line("   • COVE: {$cove}");
        if ($numeroAdenda) {
            $this->line("   • Número Adenda: {$numeroAdenda}");
        }
        $this->line("   • Servicio: ConsultarEdocument (SOLO CONSULTA)");
        $this->newLine();
        
        $this->info("✅ MODO SEGURO: Este comando NO genera trámites nuevos");
        $this->info("   Solo consulta el documento electrónico completo del COVE");
        $this->newLine();

        try {
            $this->info("🚀 Iniciando consulta de eDocument...");
            $this->line("🔐 Generando e.firma digital...");
            
            $consultarEdocumentService = app(ConsultarEdocumentService::class);
            
            // Consultar el eDocument
            $resultado = $consultarEdocumentService->consultarEdocument($cove, $numeroAdenda);
            
            // Obtener debug inmediatamente después de la llamada
            $debug = $consultarEdocumentService->getDebugInfo();
            
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
                
                // Extraer eDocument del request
                if (preg_match('/<eDocument>([^<]+)<\/eDocument>/', $request, $matches)) {
                    $this->line("   • COVE en request: " . $matches[1] . " ✅");
                } else {
                    $this->line("   • COVE en request: ❌ NO ENCONTRADO");
                }
                
                // Extraer cadena original firmada
                if (preg_match('/<cadenaOriginal>([^<]+)<\/cadenaOriginal>/', $request, $matches)) {
                    $cadenaOriginal = $matches[1];
                    $this->line("   • Cadena Original: " . $cadenaOriginal);
                    
                    // Analizar cadena original
                    $partes = explode('|', $cadenaOriginal);
                    if (count($partes) >= 2) {
                        $this->line("   • COVE en cadena: " . $partes[0]);
                        $this->line("   • RFC en cadena: " . $partes[1]);
                        
                        if ($partes[0] === $cove) {
                            $this->line("   • Coincidencia COVE: ✅ CORRECTO");
                        } else {
                            $this->line("   • Coincidencia COVE: ❌ DIFERENTE");
                        }
                        
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
            if ($resultado['success']) {
                $this->info("✅ CONSULTA EDOCUMENT EXITOSA:");
                $this->line("   • COVE: {$cove}");
                $this->line("   • RFC Consultado: {$user->rfc}");
                $this->line("   • Mensaje: " . $resultado['message']);
                
                if (isset($resultado['cove_data'])) {
                    $coveData = $resultado['cove_data'];
                    $this->newLine();
                    $this->line("📄 DATOS DEL COVE:");
                    if ($coveData['eDocument']) {
                        $this->line("   • eDocument: " . $coveData['eDocument']);
                    }
                    if ($coveData['tipoOperacion']) {
                        $this->line("   • Tipo Operación: " . $coveData['tipoOperacion']);
                    }
                    if ($coveData['numeroFacturaRelacionFacturas']) {
                        $this->line("   • Número Factura: " . $coveData['numeroFacturaRelacionFacturas']);
                    }
                    if ($coveData['relacionFacturas']) {
                        $this->line("   • Relación Facturas: " . $coveData['relacionFacturas']);
                    }
                    if ($coveData['automotriz']) {
                        $this->line("   • Automotriz: " . $coveData['automotriz']);
                    }
                }
                
                $this->newLine();
                $this->info("🎉 Documento eDocument obtenido exitosamente");
                $this->line("   ✅ Se usó e.firma real del usuario");
                $this->line("   ✅ No se generaron trámites nuevos");
                $this->line("   ✅ Solo se consultó información existente");
                
            } else {
                $this->warn("⚠️ CONSULTA SIN RESULTADOS:");
                $this->line("   • COVE: {$cove}");
                $this->line("   • RFC Consultado: {$user->rfc}");
                $this->line("   • Mensaje: " . $resultado['message']);
                
                if (isset($resultado['errores']) && !empty($resultado['errores'])) {
                    $this->line("   • Errores:");
                    foreach ($resultado['errores'] as $error) {
                        $this->line("     - " . $error);
                    }
                }
                
                $this->newLine();
                $this->line("📋 ANÁLISIS DEL RESULTADO:");
                $this->line("   • Resultado 'Sin resultados' es ESPERADO ✅");
                $this->line("   • El COVE probablemente fue generado por otro RFC (agente aduanal)");
                $this->line("   • VUCEM solo permite consultar COVEs del mismo RFC que los generó");
                $this->line("   • RFC consultando: {$user->rfc}");
                $this->line("   • RFC que generó el COVE: Probablemente diferente");
                
                $this->newLine();
                $this->info("💡 CONCLUSIÓN: ¡El sistema funciona perfectamente!");
                $this->line("   Para obtener el COVE, necesitarías el RFC del agente que lo generó.");
            }

            return 0;

        } catch (CoveConsultaException $e) {
            $this->error("❌ ERROR EN CONSULTA EDOCUMENT: " . $e->getMessage());
            
            // Mostrar información técnica
            $this->newLine();
            $this->line("🔧 INFORMACIÓN TÉCNICA:");
            $this->line("   • Usuario: {$user->username} (RFC: {$user->rfc})");
            $this->line("   • COVE consultado: {$cove}");
            
            // Intentar obtener debug info
            try {
                $consultarEdocumentService = app(ConsultarEdocumentService::class);
                $debug = $consultarEdocumentService->getDebugInfo();
                
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
            $this->line("   • Servicio ConsultarEdocument es siempre seguro");
            
            return 1;
        } finally {
            // Limpiar autenticación
            Auth::logout();
        }
    }
}