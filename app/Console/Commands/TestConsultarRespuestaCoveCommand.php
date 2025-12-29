<?php

namespace App\Console\Commands;

use App\Services\Vucem\ConsultarRespuestaCoveService;
use Illuminate\Console\Command;
use Exception;

/**
 * Comando para probar la consulta de respuesta COVE
 * 
 * Uso: php artisan vucem:test-consultar-cove {numeroOperacion}
 * 
 * Ejemplo: php artisan vucem:test-consultar-cove 1234567890
 */
class TestConsultarRespuestaCoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vucem:test-consultar-cove 
                            {numeroOperacion : Número de operación COVE a consultar}
                            {--user=1 : ID del usuario para obtener credenciales}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el servicio de consulta de respuesta COVE de VUCEM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $numeroOperacion = $this->argument('numeroOperacion');
        $userId = $this->option('user');

        $this->info("🔍 Prueba de Consulta Respuesta COVE");
        $this->info("══════════════════════════════════════");
        $this->info("Número de Operación: {$numeroOperacion}");
        $this->info("Usuario ID: {$userId}");
        $this->newLine();

        try {
            // 1. Obtener usuario
            $this->info("📋 Paso 1: Obteniendo usuario...");
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                $this->error("❌ Usuario no encontrado con ID: {$userId}");
                return Command::FAILURE;
            }
            
            $this->info("✓ Usuario encontrado: {$user->name} ({$user->rfc})");
            $this->newLine();

            // 2. Verificar credenciales VUCEM
            $this->info("🔐 Paso 2: Verificando credenciales VUCEM...");
            if (empty($user->rfc)) {
                $this->error("❌ El usuario no tiene RFC configurado");
                return Command::FAILURE;
            }
            
            if (empty($user->webservice_key)) {
                $this->error("❌ El usuario no tiene webservice_key configurado");
                return Command::FAILURE;
            }
            
            $this->info("✓ RFC: {$user->rfc}");
            $this->info("✓ Webservice Key: " . substr($user->webservice_key, 0, 10) . "...");
            $this->newLine();

            // 3. Verificar archivos e.firma
            $this->info("📄 Paso 3: Verificando archivos e.firma...");
            $efirmaPath = config('vucem.efirma.path');
            $certFile = config('vucem.efirma.cert_file');
            $keyFile = config('vucem.efirma.key_file');
            $passwordFile = config('vucem.efirma.password_file');

            $cerPath = base_path($efirmaPath . DIRECTORY_SEPARATOR . $certFile);
            $keyPath = base_path($efirmaPath . DIRECTORY_SEPARATOR . $keyFile);
            $passwordPath = base_path($efirmaPath . DIRECTORY_SEPARATOR . $passwordFile);

            if (!file_exists($cerPath)) {
                $this->error("❌ Archivo de certificado no encontrado: {$cerPath}");
                return Command::FAILURE;
            }
            
            if (!file_exists($keyPath)) {
                $this->error("❌ Archivo de llave privada no encontrado: {$keyPath}");
                return Command::FAILURE;
            }

            if (!file_exists($passwordPath)) {
                $this->error("❌ Archivo de contraseña no encontrado: {$passwordPath}");
                return Command::FAILURE;
            }

            $password = trim(file_get_contents($passwordPath));
            if (empty($password)) {
                $this->error("❌ Contraseña de e.firma vacía");
                return Command::FAILURE;
            }

            $this->info("✓ Certificado: {$certFile}");
            $this->info("✓ Llave privada: {$keyFile}");
            $this->info("✓ Contraseña configurada");
            $this->newLine();

            // 4. Ejecutar consulta
            $this->info("🚀 Paso 4: Ejecutando consulta al Web Service...");
            $this->info("Endpoint: " . config('vucem.consultar_respuesta_cove.endpoint'));
            $this->newLine();

            $consultarService = new ConsultarRespuestaCoveService($user);
            
            $startTime = microtime(true);
            $resultado = $consultarService->consultarRespuesta($numeroOperacion);
            $endTime = microtime(true);
            
            $duracion = round(($endTime - $startTime) * 1000, 2);

            $this->newLine();
            $this->info("✅ CONSULTA EXITOSA (Tiempo: {$duracion}ms)");
            $this->info("══════════════════════════════════════");
            $this->newLine();

            // 5. Mostrar resultado
            $this->displayResultado($resultado);

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->newLine();
            $this->error("══════════════════════════════════════");
            $this->error("❌ ERROR EN LA CONSULTA");
            $this->error("══════════════════════════════════════");
            $this->error("Mensaje: " . $e->getMessage());
            $this->error("Archivo: " . $e->getFile() . ":" . $e->getLine());
            
            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->error("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }

    /**
     * Mostrar el resultado de la consulta
     */
    private function displayResultado(array $resultado)
    {
        // Información general
        $this->info("📊 INFORMACIÓN GENERAL");
        $this->info("─────────────────────────────────────");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Número de Operación', $resultado['numeroOperacion'] ?? 'N/A'],
                ['Hora de Recepción', $resultado['horaRecepcion'] ?? 'N/A'],
                ['Total de Respuestas', count($resultado['respuestasOperaciones'] ?? [])],
            ]
        );

        // Leyenda (si existe)
        if (!empty($resultado['leyenda'])) {
            $this->newLine();
            $this->warn("⚠️  LEYENDA:");
            $this->line("  " . $resultado['leyenda']);
        }

        // Respuestas de operaciones
        if (!empty($resultado['respuestasOperaciones'])) {
            $this->newLine();
            $this->info("📋 RESPUESTAS DE OPERACIONES");
            $this->info("─────────────────────────────────────");

            foreach ($resultado['respuestasOperaciones'] as $index => $operacion) {
                $this->newLine();
                $this->info("Operación #" . ($index + 1));
                
                $data = [
                    ['Factura/Relación', $operacion['numeroFacturaORelacionFacturas'] ?? 'N/A'],
                    ['Contiene Error', $operacion['contieneError'] ? '❌ SÍ' : '✅ NO'],
                ];

                if (!empty($operacion['eDocument'])) {
                    $data[] = ['eDocument', $operacion['eDocument']];
                }

                if (!empty($operacion['numeroAdenda'])) {
                    $data[] = ['Número Adenda', $operacion['numeroAdenda']];
                }

                if (!empty($operacion['cadenaOriginal'])) {
                    $cadena = $operacion['cadenaOriginal'];
                    if (strlen($cadena) > 60) {
                        $cadena = substr($cadena, 0, 60) . '...';
                    }
                    $data[] = ['Cadena Original', $cadena];
                }

                if (!empty($operacion['selloDigital'])) {
                    $sello = $operacion['selloDigital'];
                    if (strlen($sello) > 60) {
                        $sello = substr($sello, 0, 60) . '...';
                    }
                    $data[] = ['Sello Digital', $sello];
                }

                $this->table(['Campo', 'Valor'], $data);

                // Mostrar errores si existen
                if (!empty($operacion['errores'])) {
                    $this->error("  ⚠️  ERRORES:");
                    foreach ($operacion['errores'] as $error) {
                        $this->line("    • " . $error);
                    }
                }
            }
        }

        // XML Raw (solo en modo verbose)
        if ($this->output->isVerbose() && !empty($resultado['raw_response'])) {
            $this->newLine();
            $this->info("📄 XML RESPUESTA RAW");
            $this->info("─────────────────────────────────────");
            $this->line($resultado['raw_response']);
        }
    }
}
