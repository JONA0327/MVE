<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarEdocumentService;
use App\Services\Vucem\EFirmaService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestConsultarEdocumentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vucem:test-edocument 
                            {edocument? : El número de eDocument a consultar}
                            {--rfc= : RFC del usuario (opcional, usa el primero si no se especifica)}
                            {--debug : Mostrar XML request/response completo}
                            {--validate-only : Solo validar configuración sin hacer llamada real}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la consulta de eDocument en VUCEM y valida la configuración';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('    🔍 TEST: ConsultarEdocument VUCEM');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // PASO 1: Validar usuario
        $this->info('📋 PASO 1: Validando Usuario');
        $this->line('─────────────────────────────────────────────────────────────');
        
        $rfcOption = $this->option('rfc');
        if ($rfcOption) {
            $user = User::where('rfc', $rfcOption)->first();
            if (!$user) {
                $this->error("❌ Usuario con RFC {$rfcOption} no encontrado");
                return 1;
            }
        } else {
            $user = User::whereNotNull('rfc')->first();
            if (!$user) {
                $this->error('❌ No hay usuarios con RFC configurado en la BD');
                return 1;
            }
        }

        Auth::login($user);

        $this->info("✅ Usuario: {$user->name}");
        $this->info("✅ RFC: {$user->rfc}");
        
        // Verificar clave webservice
        try {
            $claveWS = $user->getDecryptedWebserviceKey();
            if (empty($claveWS)) {
                $this->error('❌ Usuario no tiene clave webservice configurada');
                $this->warn('   Configúrala en el perfil del usuario');
                return 1;
            }
            $this->info('✅ Clave Webservice: ' . str_repeat('*', strlen($claveWS)) . ' (' . strlen($claveWS) . ' caracteres)');
        } catch (\Exception $e) {
            $this->error('❌ Error al obtener clave webservice: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // PASO 2: Validar e.firma
        $this->info('📋 PASO 2: Validando e.firma');
        $this->line('─────────────────────────────────────────────────────────────');
        
        try {
            $efirmaService = app(EFirmaService::class);
            $status = $efirmaService->verificarArchivos();

            $this->table(
                ['Archivo', 'Estado'],
                [
                    ['Certificado (.cer)', $status['cert_exists'] && $status['cert_readable'] ? '✅ OK' : '❌ FALTA'],
                    ['Llave Privada (.key)', $status['key_exists'] && $status['key_readable'] ? '✅ OK' : '❌ FALTA'],
                    ['Contraseña', $status['password_valid'] ? '✅ OK' : '❌ INVÁLIDA'],
                ]
            );

            if (!empty($status['errors'])) {
                $this->newLine();
                $this->error('❌ Errores en e.firma:');
                foreach ($status['errors'] as $error) {
                    $this->line('   • ' . $error);
                }
                return 1;
            }

            $this->info('✅ Archivos e.firma correctos');
        } catch (\Exception $e) {
            $this->error('❌ Error validando e.firma: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // PASO 3: Validar WSDL
        $this->info('📋 PASO 3: Validando WSDL');
        $this->line('─────────────────────────────────────────────────────────────');
        
        $wsdlPath = base_path('wsdl/vucem/COVE/edocument/ConsultarEdocument.wsdl');
        $xsdPath = base_path('wsdl/vucem/COVE/edocument/ConsultarEdocument.xsd');
        $xsdOxmlPath = base_path('wsdl/vucem/COVE/edocument/RecibirCove.xsd');
        
        $this->table(
            ['Archivo', 'Existe', 'Ruta'],
            [
                ['WSDL', file_exists($wsdlPath) ? '✅' : '❌', $wsdlPath],
                ['XSD Principal', file_exists($xsdPath) ? '✅' : '❌', $xsdPath],
                ['XSD Firma (oxml)', file_exists($xsdOxmlPath) ? '✅' : '❌', $xsdOxmlPath],
            ]
        );

        if (!file_exists($wsdlPath) || !file_exists($xsdPath)) {
            $this->error('❌ Archivos WSDL/XSD faltantes');
            return 1;
        }

        $this->info('✅ Archivos WSDL/XSD presentes');
        $this->newLine();

        // PASO 4: Validar Configuración
        $this->info('📋 PASO 4: Configuración VUCEM');
        $this->line('─────────────────────────────────────────────────────────────');
        
        $endpoint = config('vucem.edocument.endpoint');
        $soapAction = config('vucem.edocument.soap_action');
        
        $this->table(
            ['Parámetro', 'Valor'],
            [
                ['Endpoint', $endpoint],
                ['SOAPAction', $soapAction],
                ['SOAP Version', 'SOAP 1.1'],
                ['Timeout', config('vucem.edocument.connection_timeout', 30) . 's'],
            ]
        );

        // Validar endpoint
        if (str_contains($endpoint, 'prueba') || str_contains($endpoint, 'test')) {
            $this->warn('⚠️  Parece ser endpoint de PRUEBAS');
        } else {
            $this->info('✅ Endpoint de PRODUCCIÓN');
        }

        $this->newLine();

        // PASO 5: Test de firma (sin llamada real)
        $this->info('📋 PASO 5: Test de Generación de Firma');
        $this->line('─────────────────────────────────────────────────────────────');
        
        $testEdocument = $this->argument('edocument') ?? 'TEST123456789';
        
        try {
            $firma = $efirmaService->generarFirmaElectronica($testEdocument, $user->rfc);
            
            $this->info('✅ Firma generada correctamente');
            $this->line('   Cadena Original: ' . $firma['cadenaOriginal']);
            $this->line('   Certificado: ' . substr($firma['certificado'], 0, 50) . '... (' . strlen($firma['certificado']) . ' chars)');
            $this->line('   Firma: ' . substr($firma['firma'], 0, 50) . '... (' . strlen($firma['firma']) . ' chars)');
            
            // Validar formato de cadena original
            $expectedFormat = "|{$testEdocument}|{$user->rfc}|";
            if ($firma['cadenaOriginal'] === $expectedFormat) {
                $this->info('✅ Formato de cadena original correcto');
            } else {
                $this->error('❌ Formato de cadena original incorrecto');
                $this->line('   Esperado: ' . $expectedFormat);
                $this->line('   Obtenido: ' . $firma['cadenaOriginal']);
            }
        } catch (\Exception $e) {
            $this->error('❌ Error generando firma: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // Si solo es validación, terminamos aquí
        if ($this->option('validate-only')) {
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('✅ VALIDACIÓN COMPLETA - Todo configurado correctamente');
            $this->info('═══════════════════════════════════════════════════════════');
            return 0;
        }

        // PASO 6: Consulta Real
        $edocument = $this->argument('edocument');
        
        if (!$edocument) {
            $edocument = $this->ask('Ingresa el número de eDocument a consultar');
            if (!$edocument) {
                $this->error('❌ eDocument es requerido');
                return 1;
            }
        }

        $this->info('📋 PASO 6: Consulta Real a VUCEM');
        $this->line('─────────────────────────────────────────────────────────────');
        $this->info("Consultando eDocument: {$edocument}");
        $this->newLine();

        try {
            $service = app(ConsultarEdocumentService::class);
            
            $this->info('⏳ Enviando solicitud a VUCEM...');
            $result = $service->consultarEdocument($edocument);

            $this->newLine();
            $this->line('═══════════════════════════════════════════════════════════');
            
            if ($result['success']) {
                $this->info('✅ CONSULTA EXITOSA');
                $this->line('═══════════════════════════════════════════════════════════');
                $this->newLine();
                
                $this->info('Mensaje: ' . $result['message']);
                
                if (isset($result['cove_data'])) {
                    $this->newLine();
                    $this->info('📦 Datos del COVE:');
                    $this->table(
                        ['Campo', 'Valor'],
                        [
                            ['eDocument', $result['cove_data']['eDocument'] ?? 'N/A'],
                            ['Tipo Operación', $result['cove_data']['tipoOperacion'] ?? 'N/A'],
                            ['Número Factura', $result['cove_data']['numeroFacturaRelacionFacturas'] ?? 'N/A'],
                        ]
                    );
                }
            } else {
                $this->error('❌ CONSULTA FALLIDA');
                $this->line('═══════════════════════════════════════════════════════════');
                $this->newLine();
                
                $this->error('Mensaje: ' . $result['message']);
                
                if (isset($result['errores']) && !empty($result['errores'])) {
                    $this->newLine();
                    $this->error('Errores reportados:');
                    foreach ($result['errores'] as $error) {
                        $this->line('  • ' . $error);
                    }
                }

                // Sugerencias según tipo de error
                $this->newLine();
                $this->warn('💡 Sugerencias:');
                if (str_contains(strtolower($result['message']), 'no encontrado')) {
                    $this->line('  • Verifica que el eDocument existe en el portal web');
                    $this->line('  • Confirma que tu RFC tiene permisos para consultar ese COVE');
                    $this->line('  • Revisa que el número sea exacto (sin espacios)');
                } elseif (str_contains(strtolower($result['message']), 'autenticación') || 
                          str_contains(strtolower($result['message']), 'credencial')) {
                    $this->line('  • Verifica que usas la CLAVE WEBSERVICE (no la contraseña del portal)');
                    $this->line('  • Confirma que tu RFC está activo en VUCEM');
                    $this->line('  • La clave webservice podría estar vencida');
                } elseif (str_contains(strtolower($result['message']), 'firma')) {
                    $this->line('  • El certificado e.firma debe estar vigente');
                    $this->line('  • El RFC del certificado debe coincidir con el usuario');
                    $this->line('  • La contraseña de la llave privada debe ser correcta');
                }
            }

            // Mostrar debug si se solicitó
            if ($this->option('debug')) {
                $debug = $service->getDebugInfo();
                
                $this->newLine();
                $this->line('═══════════════════════════════════════════════════════════');
                $this->info('🐛 INFORMACIÓN DE DEBUG');
                $this->line('═══════════════════════════════════════════════════════════');
                
                $this->newLine();
                $this->info('📤 XML REQUEST:');
                $this->line('─────────────────────────────────────────────────────────────');
                $this->line($this->formatXml($debug['last_request'] ?? 'N/A'));
                
                $this->newLine();
                $this->info('📥 XML RESPONSE:');
                $this->line('─────────────────────────────────────────────────────────────');
                $this->line($this->formatXml($debug['last_response'] ?? 'N/A'));
                
                $this->newLine();
                $this->info('📋 REQUEST HEADERS:');
                $this->line('─────────────────────────────────────────────────────────────');
                $this->line($debug['last_request_headers'] ?? 'N/A');
                
                // Análisis automático del XML
                $this->newLine();
                $this->info('🔍 ANÁLISIS AUTOMÁTICO:');
                $this->line('─────────────────────────────────────────────────────────────');
                $this->analyzeRequest($debug['last_request'] ?? '');
            }

            $this->newLine();
            $this->line('═══════════════════════════════════════════════════════════');
            
            return $result['success'] ? 0 : 1;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('═══════════════════════════════════════════════════════════');
            $this->error('❌ ERROR CRÍTICO');
            $this->error('═══════════════════════════════════════════════════════════');
            $this->newLine();
            $this->error('Excepción: ' . get_class($e));
            $this->error('Mensaje: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Formatea XML para mejor legibilidad
     */
    private function formatXml(string $xml): string
    {
        if (empty($xml)) {
            return 'N/A';
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        
        // Suprimir warnings de XML mal formado
        @$dom->loadXML($xml);
        
        return $dom->saveXML();
    }

    /**
     * Analiza el request XML y detecta problemas comunes
     */
    private function analyzeRequest(string $xml): void
    {
        if (empty($xml)) {
            $this->warn('⚠️  No hay XML request para analizar');
            return;
        }

        $checks = [];

        // Check 1: Nodo <request> wrapper
        if (str_contains($xml, '<request>') || str_contains($xml, '<ns1:request>')) {
            $checks[] = ['✅', 'Nodo <request> wrapper presente'];
        } else {
            $checks[] = ['❌', 'Falta nodo <request> wrapper - ERROR CRÍTICO'];
        }

        // Check 2: Namespace ConsultarEdocument
        if (str_contains($xml, 'ConsultarEdocument/')) {
            $checks[] = ['✅', 'Namespace ConsultarEdocument correcto'];
        } else {
            $checks[] = ['⚠️', 'Namespace ConsultarEdocument podría estar incorrecto'];
        }

        // Check 3: Namespace oxml para firma
        if (str_contains($xml, 'oxml') || str_contains($xml, 'cove/ws/oxml')) {
            $checks[] = ['✅', 'Namespace oxml presente'];
        } else {
            $checks[] = ['⚠️', 'Namespace oxml no detectado'];
        }

        // Check 4: WS-Security header
        if (str_contains($xml, 'wsse:Security')) {
            $checks[] = ['✅', 'Header WS-Security presente'];
        } else {
            $checks[] = ['❌', 'Falta header WS-Security - ERROR'];
        }

        // Check 5: UsernameToken
        if (str_contains($xml, 'wsse:UsernameToken')) {
            $checks[] = ['✅', 'UsernameToken presente'];
        } else {
            $checks[] = ['❌', 'Falta UsernameToken - ERROR'];
        }

        // Check 6: Elementos de firma
        if (str_contains($xml, '<certificado>') && str_contains($xml, '<cadenaOriginal>') && str_contains($xml, '<firma>')) {
            $checks[] = ['✅', 'Elementos de firma electrónica presentes'];
        } else {
            $checks[] = ['❌', 'Faltan elementos de firma electrónica - ERROR'];
        }

        // Check 7: criterioBusqueda
        if (str_contains($xml, 'criterioBusqueda')) {
            $checks[] = ['✅', 'Elemento criterioBusqueda presente'];
        } else {
            $checks[] = ['❌', 'Falta criterioBusqueda - ERROR'];
        }

        // Check 8: eDocument en body
        if (str_contains($xml, '<eDocument>')) {
            $checks[] = ['✅', 'Elemento eDocument presente'];
        } else {
            $checks[] = ['❌', 'Falta elemento eDocument - ERROR'];
        }

        // Mostrar resultados
        $this->table(['', 'Validación'], $checks);

        // Contar errores
        $errores = count(array_filter($checks, fn($c) => $c[0] === '❌'));
        $warnings = count(array_filter($checks, fn($c) => $c[0] === '⚠️'));

        $this->newLine();
        if ($errores > 0) {
            $this->error("Se encontraron {$errores} error(es) crítico(s) en el XML");
        } elseif ($warnings > 0) {
            $this->warn("Se encontraron {$warnings} advertencia(s)");
        } else {
            $this->info('✅ Estructura XML parece correcta');
        }
    }
}
