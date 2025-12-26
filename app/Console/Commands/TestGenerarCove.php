<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\RecibirCoveService;
use App\Exceptions\CoveConsultaException;

class TestGenerarCove extends Command
{
    protected $signature = 'test:generar-cove {--tipo=simple : Tipo de prueba (simple|automotriz|no-automotriz)}';
    protected $description = 'Prueba la generación de COVE usando RecibirCoveService';

    public function handle()
    {
        $tipo = $this->option('tipo');
        
        $this->info("🏭 PRUEBA GENERACIÓN DE COVE - RECIBIR COVE SERVICE");
        $this->info("===================================================");
        $this->newLine();

        // Datos de prueba según los ejemplos
        $datosFactura = $this->obtenerDatosPrueba($tipo);

        $this->line("📋 Configuración:");
        $this->line("   • RFC: " . config('vucem.rfc'));
        $this->line("   • Tipo de Operación: " . $datosFactura['tipoOperacion']);
        $this->line("   • Número Factura: " . $datosFactura['numeroFacturaOriginal']);
        $this->line("   • Fecha: " . $datosFactura['fechaExpedicion']);
        $this->newLine();

        try {
            $startTime = microtime(true);
            
            // Autenticar usuario (necesario para credenciales)
            $user = \App\Models\User::first();
            if ($user) {
                auth()->login($user);
            }
            
            $recibirCoveService = app(RecibirCoveService::class);
            
            $this->info("🚀 Iniciando generación de COVE...");
            
            // Mostrar la cadena original que se generará
            $cadenaOriginalPreview = $this->construirCadenaOriginalPreview($datosFactura);
            $this->line("🔗 Cadena Original:");
            $this->line("   " . $cadenaOriginalPreview);
            $this->newLine();
            
            $resultado = $recibirCoveService->generarCove($datosFactura);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            $this->line("⏱️  Tiempo de ejecución: {$duration}ms");
            $this->newLine();
            
            $this->info("📊 RESULTADO:");
            $this->info("=============");
            
            if ($resultado['success']) {
                $this->info("✅ COVE GENERADO EXITOSAMENTE");
                $this->line("   • Mensaje: " . $resultado['message']);
                
                if ($resultado['cove']) {
                    $this->line("   • Folio COVE: " . $resultado['cove']);
                }
            } else {
                $this->error("❌ ERROR EN GENERACIÓN");
                $this->line("   • Mensaje: " . $resultado['message']);
                
                if (!empty($resultado['errors'])) {
                    $this->line("   • Errores:");
                    foreach ($resultado['errors'] as $error) {
                        $this->line("     - " . $error);
                    }
                }
            }
            
            // SIEMPRE mostrar debug info para verificar funcionamiento
            $debug = $recibirCoveService->getDebugInfo();
            $this->newLine();
            $this->info("🔧 INFORMACIÓN DE DEBUG:");
            $this->info("========================");
            
            if ($debug['last_request']) {
                $this->info("📤 REQUEST SOAP GENERADO:");
                $this->line($debug['last_request']);
                $this->newLine();
                
                // Validar que contiene elementos clave
                $this->line("✅ Validaciones del Request:");
                $this->line("   • Contiene e.firma: " . (strpos($debug['last_request'], 'firmaElectronica') !== false ? 'SÍ ✅' : 'NO ❌'));
                $this->line("   • Contiene certificado: " . (strpos($debug['last_request'], 'certificado') !== false ? 'SÍ ✅' : 'NO ❌'));
                $this->line("   • Contiene firma digital: " . (strpos($debug['last_request'], '<firma>') !== false ? 'SÍ ✅' : 'NO ❌'));
                $this->line("   • Contiene WS-Security: " . (strpos($debug['last_request'], 'wsse:Security') !== false ? 'SÍ ✅' : 'NO ❌'));
                $this->line("   • RFC configurado: " . config('vucem.rfc') . " ✅");
                $this->newLine();
            } else {
                $this->warn("⚠️  No se generó request XML");
            }
            
            if ($debug['last_response']) {
                $this->info("📥 RESPONSE SOAP RECIBIDO:");
                $this->line($debug['last_response']);
            } else {
                $this->warn("⚠️  No se recibió response (problema de conectividad)");
            }
            
            $this->newLine();
            $this->info("✨ Prueba completada");
            
            return 0;
            
        } catch (CoveConsultaException $e) {
            $this->error("❌ ERROR DEL WEBSERVICE: " . $e->getMessage());
            
            // Mostrar información técnica
            $this->newLine();
            $this->line("🔧 INFORMACIÓN TÉCNICA:");
            $this->line("   • Endpoint configurado: " . env('VUCEM_COVE_ENDPOINT'));
            $this->line("   • WSDL: " . base_path('wsdl/vucem/COVE/IngresarCOVE/RecibirCove.wsdl'));
            
            // Mostrar XML generado para verificar que la construcción funciona
            try {
                $debug = $recibirCoveService->getDebugInfo();
                if ($debug['last_request']) {
                    $this->newLine();
                    $this->line("✅ CONSTRUCCIÓN XML:");
                    $this->line("   • XML SOAP generado correctamente");
                    $this->line("   • Contiene e.firma: " . (strpos($debug['last_request'], 'firmaElectronica') !== false ? 'SÍ' : 'NO'));
                    $this->line("   • Contiene certificado: " . (strpos($debug['last_request'], 'certificado') !== false ? 'SÍ' : 'NO'));
                    $this->line("   • Contiene WS-Security: " . (strpos($debug['last_request'], 'wsse:Security') !== false ? 'SÍ' : 'NO'));
                    
                    if ($this->option('verbose')) {
                        $this->newLine();
                        $this->line("📄 XML COMPLETO:");
                        $this->line($debug['last_request']);
                    }
                } else {
                    $this->line("   • No se generó XML (error antes del armado SOAP)");
                }
            } catch (\Exception $debugError) {
                $this->line("   • Error obteniendo debug: " . $debugError->getMessage());
            }
            
            $this->newLine();
            $this->line("📋 DIAGNÓSTICO:");
            if (strpos($e->getMessage(), 'Could not connect to host') !== false) {
                $this->line("   • Problema de conectividad de red");
                $this->line("   • El XML se construye correctamente");
                $this->line("   • No se puede alcanzar el servidor VUCEM");
            } elseif (strpos($e->getMessage(), 'Could not resolve host') !== false) {
                $this->line("   • Problema de DNS");
                $this->line("   • Verificar conectividad de red");
            } else {
                $this->line("   • Error SOAP del servidor: " . $e->getMessage());
            }
            
            return 1;
            
        } catch (\Exception $e) {
            $this->error("💥 Error inesperado: " . $e->getMessage());
            $this->line("Archivo: " . $e->getFile() . ":" . $e->getLine());
            return 1;
        }
    }

    /**
     * Construye una preview de la cadena original para mostrar antes de la prueba
     */
    private function construirCadenaOriginalPreview(array $datos): string
    {
        $elementos = [
            $datos['tipoOperacion'] ?? 'TOCE.IMP',
            $datos['numeroFacturaOriginal'] ?? '',
            $datos['certificadoOrigen'] ?? '0',
            $datos['fechaExpedicion'] ?? date('Y-m-d'),
            $datos['tipoFigura'] ?? '5',
            $datos['observaciones'] ?? 'Prueba del webservice de Cove',
        ];

        // Agregar RFCs de consulta
        if (!empty($datos['rfcsConsulta'])) {
            $elementos = array_merge($elementos, $datos['rfcsConsulta']);
        }

        // Agregar patentes aduanales
        if (!empty($datos['patentesAduanales'])) {
            $elementos = array_merge($elementos, $datos['patentesAduanales']);
        }

        // Mostrar solo los primeros elementos para preview
        $preview = '|' . implode('|', array_slice($elementos, 0, 10)) . '|...';
        
        return $preview;
    }

    /**
     * Obtiene datos de prueba según el tipo especificado
     */
    private function obtenerDatosPrueba(string $tipo): array
    {
        switch ($tipo) {
            case 'simple':
                // Basado en: |TOCE.IMP|ITUTUET66545|0|2011-11-15|5|Esta es una prueba del webservice de Cove|APH0609268C2|AFC000526BJ2|3916|3842|...
                return [
                    'tipoOperacion' => 'TOCE.IMP',
                    'numeroFacturaOriginal' => 'ITUTUET66545',
                    'certificadoOrigen' => '0',
                    'fechaExpedicion' => '2011-11-15',
                    'tipoFigura' => '5',
                    'observaciones' => 'Esta es una prueba del webservice de Cove desde MVE',
                    'rfcsConsulta' => ['APH0609268C2', 'AFC000526BJ2'],
                    'patentesAduanales' => ['3916', '3842'],
                    'correoElectronico' => 'prueba@mve.com',
                    'datosAdicionales' => [
                        '0', '1', '9984882', '1',
                        'APH0609268C2', 'MARTINEZ', 'ORTIZ', 'MARIA',
                        'leo', '23', '23', 'BOSQUES DE LAS LOMAS',
                        'MEXICO', 'ECATEPEC', 'MEXICO', 'MEX', '55567',
                        '1', 'AFC000526BJ2', 'BETANCOURT', 'MARTINEZ', 'FELIPE',
                        'CAPRICORNIO', '78', '78', 'PRADOS DE ECATEPEC',
                        'ECATEPEC', 'TULTITLAN', 'MEXICO', 'MEX', '66532',
                        'CUADERNOS', '2', '200.001', 'USD', '10.01', '2000.01',
                        '200000.0001', 'SCRIBE', 'DE987', 'C-4567', 'LA-02012011-WE'
                    ],
                    'emisor' => [
                        'tipoIdentificador' => '1',
                        'identificacion' => 'APH0609268C2',
                        'apellidoPaterno' => 'MARTINEZ',
                        'apellidoMaterno' => 'ORTIZ', 
                        'nombre' => 'MARIA',
                        'domicilio' => [
                            'calle' => 'leo',
                            'numeroExterior' => '23',
                            'numeroInterior' => '23',
                            'colonia' => 'BOSQUES DE LAS LOMAS',
                            'localidad' => 'MEXICO',
                            'municipio' => 'ECATEPEC',
                            'entidadFederativa' => 'MEXICO',
                            'pais' => 'MEX',
                            'codigoPostal' => '55567'
                        ]
                    ],
                    'destinatario' => [
                        'tipoIdentificador' => '1',
                        'identificacion' => 'AFC000526BJ2',
                        'apellidoPaterno' => 'BETANCOURT',
                        'apellidoMaterno' => 'MARTINEZ',
                        'nombre' => 'FELIPE',
                        'domicilio' => [
                            'calle' => 'CAPRICORNIO',
                            'numeroExterior' => '78',
                            'numeroInterior' => '78',
                            'colonia' => 'PRADOS DE ECATEPEC',
                            'localidad' => 'ECATEPEC',
                            'municipio' => 'TULTITLAN',
                            'entidadFederativa' => 'MEXICO',
                            'pais' => 'MEX',
                            'codigoPostal' => '66532'
                        ]
                    ],
                    'mercancias' => [
                        [
                            'descripcionGenerica' => 'CUADERNOS',
                            'claveUnidadMedida' => '2',
                            'tipoMoneda' => 'USD',
                            'cantidad' => '200.001',
                            'valorUnitario' => '10.01',
                            'valorTotal' => '2000.01',
                            'valorDolares' => '200000.0001',
                            'descripcionesEspecificas' => [
                                'marca' => 'SCRIBE',
                                'modelo' => 'DE987',
                                'subModelo' => 'C-4567',
                                'numeroSerie' => 'LA-02012011-WE'
                            ]
                        ]
                    ]
                ];

            case 'automotriz':
                // Basado en ejemplo de relación de facturas automotriz
                return [
                    'tipoOperacion' => 'TOCE.IMP',
                    'numeroFacturaOriginal' => '1000',
                    'certificadoOrigen' => '1',
                    'fechaExpedicion' => '2012-01-05',
                    'tipoFigura' => '5',
                    'observaciones' => 'PRUEBA RELACION DE FACTURAS IA',
                    'rfcsConsulta' => ['AFC000526BJ2', 'APH0609268C2'],
                    'patentesAduanales' => ['0010', '0007', '0008'],
                    'correoElectronico' => 'automotriz@mve.com'
                ];

            case 'no-automotriz':
                // Basado en ejemplo de relación de facturas NO automotriz
                return [
                    'tipoOperacion' => 'TOCE.EXP',
                    'numeroFacturaOriginal' => '7686876',
                    'certificadoOrigen' => '1',
                    'fechaExpedicion' => '2012-01-01',
                    'tipoFigura' => '5',
                    'observaciones' => 'PRUEBA RELACION DE FACTURAS NO IA',
                    'rfcsConsulta' => ['AFC000526BJ2'],
                    'patentesAduanales' => ['0007', '0008'],
                    'correoElectronico' => 'no-automotriz@mve.com'
                ];

            default:
                return $this->obtenerDatosPrueba('simple');
        }
    }
}