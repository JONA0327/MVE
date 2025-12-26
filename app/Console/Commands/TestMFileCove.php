<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MFileParserService;

class TestMFileCove extends Command
{
    protected $signature = 'test:mfile-cove {file_path}';
    protected $description = 'Prueba el procesamiento de archivo M para obtener COVEs';

    public function handle()
    {
        $filePath = $this->argument('file_path');
        
        $this->info('=== PROBANDO PROCESAMIENTO DE ARCHIVO M PARA COVE ===');
        $this->info("Archivo: {$filePath}");
        $this->newLine();
        
        try {
            $parser = new MFileParserService();
            $resultados = $parser->processMFileForCove($filePath);
            
            if (empty($resultados)) {
                $this->warning('⚠️ No se encontraron operaciones en el archivo');
                return 1;
            }
            
            $this->info('✅ Procesamiento exitoso');
            $this->info('Total de operaciones: ' . count($resultados));
            $this->newLine();
            
            // Mostrar tabla con resultados
            $headers = [
                'Operación', 
                'Aduana', 
                'Patente', 
                'Ejercicio', 
                'Sección',
                'COVE',
                'Estado'
            ];
            
            $rows = [];
            foreach ($resultados as $operacion) {
                $rows[] = [
                    $operacion['numeroOperacion'],
                    $operacion['aduana'] ?? 'N/A',
                    $operacion['patente'] ?? 'N/A',
                    $operacion['ejercicio'] ?? 'N/A',
                    $operacion['seccion'] ?? 'N/A',
                    $operacion['folioCove'] ?? 'N/A',
                    $operacion['estatusCove']
                ];
            }
            
            $this->table($headers, $rows);
            $this->newLine();
            
            // Estadísticas
            $encontrados = count(array_filter($resultados, fn($op) => $op['estatusCove'] === 'encontrado'));
            $noEncontrados = count(array_filter($resultados, fn($op) => $op['estatusCove'] === 'no_encontrado'));
            $errores = count(array_filter($resultados, fn($op) => $op['estatusCove'] === 'error_ws'));
            
            $this->info('📊 Estadísticas:');
            $this->line("   ✅ COVE encontrados: {$encontrados}");
            $this->line("   ❌ COVE no encontrados: {$noEncontrados}");
            $this->line("   ⚠️  Errores de webservice: {$errores}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error procesando archivo M:');
            $this->error($e->getMessage());
            
            if ($this->option('verbose')) {
                $this->error('Stack trace:');
                $this->error($e->getTraceAsString());
            }
            
            return 1;
        }
    }
}