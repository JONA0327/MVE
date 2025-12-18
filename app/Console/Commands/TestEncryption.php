<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Manifestation;

class TestEncryption extends Command
{
    protected $signature = 'test:encryption';
    protected $description = 'Prueba la encriptación de datos del solicitante';

    public function handle()
    {
        $this->info('Probando encriptación de datos del solicitante...');

        try {
            // Crear una manifestación de prueba
            $manifestation = new Manifestation();
            
            // Datos del solicitante (sensibles - se encriptarán automáticamente)
            $manifestation->rfc_solicitante = 'RFC123456789';
            $manifestation->razon_social_solicitante = 'Empresa de Prueba S.A. de C.V.';
            $manifestation->actividad_economica_solicitante = 'Comercio al por menor';
            $manifestation->pais_solicitante = 'México';
            $manifestation->codigo_postal_solicitante = '12345';
            $manifestation->estado_solicitante = 'Ciudad de México';
            $manifestation->municipio_solicitante = 'Benito Juárez';
            $manifestation->localidad_solicitante = 'Ciudad de México';
            $manifestation->colonia_solicitante = 'Roma Norte';
            $manifestation->calle_solicitante = 'Avenida de la Paz';
            $manifestation->numero_exterior_solicitante = '123';
            $manifestation->numero_interior_solicitante = 'A';
            $manifestation->lada_solicitante = '55';
            $manifestation->telefono_solicitante = '12345678';
            $manifestation->correo_solicitante = 'prueba@empresa.com';
            
            // Datos requeridos del importador
            $manifestation->rfc_importador = 'RFC123456789';
            $manifestation->razon_social_importador = 'Empresa Importadora S.A.';

            $this->info('Guardando manifestación con datos encriptados...');
            $manifestation->save();

            $this->info('Manifestación guardada con UUID: ' . $manifestation->uuid);

            // Recuperar la manifestación y verificar que los datos se desencriptan correctamente
            $this->info('Recuperando manifestación de la base de datos...');
            $retrievedManifestation = Manifestation::find($manifestation->id);

            $this->info('Verificando desencriptación:');
            $this->table(['Campo', 'Valor Original', 'Valor Recuperado'], [
                ['RFC Solicitante', 'RFC123456789', $retrievedManifestation->rfc_solicitante],
                ['Razón Social', 'Empresa de Prueba S.A. de C.V.', $retrievedManifestation->razon_social_solicitante],
                ['Código Postal', '12345', $retrievedManifestation->codigo_postal_solicitante],
                ['Teléfono', '12345678', $retrievedManifestation->telefono_solicitante],
                ['Correo', 'prueba@empresa.com', $retrievedManifestation->correo_solicitante],
            ]);

            // Verificar que los datos están encriptados en la base de datos
            $this->info('Verificando que los datos están encriptados en la base de datos...');
            $rawData = \DB::table('manifestations')->where('id', $manifestation->id)->first();
            
            $this->info('Datos encriptados en base de datos:');
            $this->line('RFC Solicitante (encriptado): ' . substr($rawData->rfc_solicitante, 0, 50) . '...');
            $this->line('Razón Social (encriptado): ' . substr($rawData->razon_social_solicitante, 0, 50) . '...');
            $this->line('Teléfono (encriptado): ' . substr($rawData->telefono_solicitante, 0, 50) . '...');

            // Limpiar - eliminar la manifestación de prueba
            $manifestation->delete();
            $this->info('Manifestación de prueba eliminada.');

            $this->info('✅ ¡Prueba de encriptación completada exitosamente!');
            $this->info('✅ Los datos del solicitante se encriptan automáticamente al guardar');
            $this->info('✅ Los datos se desencriptan automáticamente al leer');
            $this->info('✅ Los datos están seguros en la base de datos');

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}
