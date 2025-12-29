<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultaAcusesService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestConsultaAcusesCommand extends Command
{
    protected $signature = 'vucem:test-acuses {folio} {--debug}';
    protected $description = 'Probar servicio ConsultaAcuses de VUCEM (eDocument o COVE)';

    public function handle()
    {
        $folio = $this->argument('folio');
        $debug = $this->option('debug');

        $this->info("======================================");
        $this->info("  TEST CONSULTA ACUSES - VUCEM");
        $this->info("======================================");
        $this->info("Folio: {$folio}");
        
        // Detectar tipo de folio
        $tipo = 'eDocument';
        if (preg_match('/^COVE[A-Z0-9]+$/i', $folio)) {
            $tipo = 'COVE (Acuse de Valor)';
        }
        $this->info("Tipo detectado: {$tipo}");
        $this->info("======================================\n");

        // 1. Validar usuario
        $this->info("1️⃣  Validando usuario autenticado...");
        $user = User::first();
        
        if (!$user) {
            $this->error("❌ No hay usuarios en la base de datos");
            return 1;
        }

        Auth::login($user);
        $this->info("✅ Usuario encontrado: {$user->name} ({$user->rfc})");

        // 2. Validar credenciales
        $this->info("\n2️⃣  Validando credenciales Web Service...");
        
        // Usar webservice_user si está disponible, si no usar RFC
        $wsUser = $user->webservice_user ?? $user->rfc;
        if (empty($wsUser)) {
            $this->error("❌ No se encontró webservice_user ni RFC");
            return 1;
        }
        
        if (empty($user->webservice_key)) {
            $this->error("❌ webservice_key no configurado");
            return 1;
        }

        $webserviceKey = $user->getDecryptedWebserviceKey();
        if (empty($webserviceKey)) {
            $this->error("❌ No se pudo desencriptar webservice_key");
            return 1;
        }

        $this->info("✅ webservice_user: " . $wsUser);
        $this->info("✅ webservice_key: " . substr($webserviceKey, 0, 16) . "...");

        // 3. Validar configuración
        $this->info("\n3️⃣  Validando configuración VUCEM...");
        $endpoint = config('vucem.edocument.endpoint');
        $this->info("✅ Endpoint: {$endpoint}");

        // 4. Crear servicio y consultar
        $this->info("\n4️⃣  Consultando acuse...");
        try {
            $service = new ConsultaAcusesService();
            $result = $service->consultarAcuse($folio);

            // 5. Mostrar resultado
            $this->info("\n5️⃣  RESULTADO:");
            $this->info("======================================");

            if ($result['success']) {
                $this->info("✅ Consulta exitosa");
                $this->info("Código: " . ($result['code'] ?? 'N/A'));
                $this->info("Descripción: " . ($result['descripcion'] ?? 'N/A'));
                
                if (!empty($result['acuse_documento'])) {
                    $acuseLength = strlen($result['acuse_documento']);
                    $this->info("Acuse documento: {$acuseLength} caracteres (base64)");
                    
                    // Guardar PDF
                    $pdfPath = storage_path("app/acuses/acuse_{$folio}.pdf");
                    $dir = dirname($pdfPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    file_put_contents($pdfPath, base64_decode($result['acuse_documento']));
                    $this->info("✅ PDF guardado en: {$pdfPath}");
                }

                if (!empty($result['mensajes'])) {
                    $this->info("\nMensajes:");
                    foreach ($result['mensajes'] as $msg) {
                        $this->info("  - [{$msg['clave']}] {$msg['descripcion']}");
                    }
                }
            } else {
                $this->error("❌ Consulta fallida");
                $this->error("Código: " . ($result['code'] ?? 'N/A'));
                $this->error("Descripción: " . ($result['descripcion'] ?? 'N/A'));
                
                if (!empty($result['message'])) {
                    $this->error("Error: " . $result['message']);
                }

                if (!empty($result['mensajes_error'])) {
                    $this->error("\nMensajes de error:");
                    foreach ($result['mensajes_error'] as $msg) {
                        $this->error("  - [{$msg['clave']}] {$msg['descripcion']}");
                    }
                }
            }

            // 6. Debug
            if ($debug && !empty($result['debug'])) {
                $this->info("\n6️⃣  DEBUG:");
                $this->info("======================================");
                
                if (!empty($result['debug']['last_request_headers'])) {
                    $this->info("\n📤 REQUEST HEADERS:");
                    $this->line($result['debug']['last_request_headers']);
                }

                if (!empty($result['debug']['last_request'])) {
                    $this->info("\n📤 REQUEST XML:");
                    $this->line($this->formatXml($result['debug']['last_request']));
                }

                if (!empty($result['debug']['last_response_headers'])) {
                    $this->info("\n📥 RESPONSE HEADERS:");
                    $this->line($result['debug']['last_response_headers']);
                }

                if (!empty($result['debug']['last_response'])) {
                    $this->info("\n📥 RESPONSE XML:");
                    $this->line($this->formatXml($result['debug']['last_response']));
                }
            }

            return $result['success'] ? 0 : 1;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR FATAL:");
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function formatXml(string $xml): string
    {
        try {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            return $dom->saveXML();
        } catch (\Exception $e) {
            return $xml;
        }
    }
}
