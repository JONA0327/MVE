<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\EFirmaService;

class VerifyEFirma extends Command
{
    protected $signature = 'efirma:verify';
    protected $description = 'Verifica la configuración y archivos de e.firma';

    public function handle()
    {
        $this->info('🔐 VERIFICACIÓN DE E.FIRMA');
        $this->info('========================');
        $this->newLine();

        try {
            $efirmaService = new EFirmaService();
            $status = $efirmaService->verificarArchivos();

            // Mostrar configuración
            $this->info('📋 Configuración actual:');
            $this->line('   • Ruta e.firma: ' . config('vucem.efirma.path'));
            $this->line('   • Archivo certificado: ' . config('vucem.efirma.cert_file'));
            $this->line('   • Archivo llave: ' . config('vucem.efirma.key_file'));
            $this->line('   • Archivo contraseña: ' . config('vucem.efirma.password_file'));
            $this->newLine();

            // Verificar archivos
            $this->info('📂 Estado de archivos:');
            $this->line('   • Certificado existe: ' . ($status['cert_exists'] ? 'SÍ ✅' : 'NO ❌'));
            $this->line('   • Certificado legible: ' . ($status['cert_readable'] ? 'SÍ ✅' : 'NO ❌'));
            $this->line('   • Llave privada existe: ' . ($status['key_exists'] ? 'SÍ ✅' : 'NO ❌'));
            $this->line('   • Llave privada legible: ' . ($status['key_readable'] ? 'SÍ ✅' : 'NO ❌'));
            $this->line('   • Archivo contraseña existe: ' . ($status['password_file_exists'] ? 'SÍ ✅' : 'NO ❌'));
            $this->line('   • Archivo contraseña legible: ' . ($status['password_readable'] ? 'SÍ ✅' : 'NO ❌'));
            $this->line('   • Contraseña válida: ' . ($status['password_valid'] ? 'SÍ ✅' : 'NO ❌'));
            $this->newLine();

            // Mostrar errores si los hay
            if (!empty($status['errors'])) {
                $this->error('❌ Errores encontrados:');
                foreach ($status['errors'] as $error) {
                    $this->line('   • ' . $error);
                }
                $this->newLine();
            }

            // Verificación completa
            $allOk = $status['cert_exists'] && $status['cert_readable'] && 
                     $status['key_exists'] && $status['key_readable'] && 
                     $status['password_file_exists'] && $status['password_readable'] && 
                     $status['password_valid'];

            if ($allOk) {
                $this->info('🎉 ¡Configuración de e.firma CORRECTA!');
                
                // Hacer prueba de firma
                $this->info('🧪 Realizando prueba de firma...');
                try {
                    $firmaTest = $efirmaService->generarFirmaElectronica('123456789012345', 'XAXX010101000');
                    $this->info('   ✅ Prueba de firma exitosa');
                    $this->line('   • Certificado: ' . substr($firmaTest['certificado'], 0, 50) . '...');
                    $this->line('   • Cadena original: ' . $firmaTest['cadenaOriginal']);
                    $this->line('   • Firma: ' . substr($firmaTest['firma'], 0, 50) . '...');
                } catch (\Exception $e) {
                    $this->error('   ❌ Error en prueba de firma: ' . $e->getMessage());
                    return 1;
                }
                
            } else {
                $this->error('❌ Configuración de e.firma INCOMPLETA');
                $this->info('💡 Configure los archivos faltantes y variables de entorno.');
                return 1;
            }

            $this->newLine();
            $this->info('📋 Siguientes pasos:');
            $this->line('   1. Coloque los archivos .cer y .key en: ' . base_path(config('vucem.efirma.path')));
            $this->line('   2. Configure E_FIRMA_KEY_PASSWORD en .env');
            $this->line('   3. Ejecute: php artisan efirma:test para probar consulta completa');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error verificando e.firma: ' . $e->getMessage());
            return 1;
        }
    }
}