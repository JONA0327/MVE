<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vucem\ConsultarEdocumentService;

class TestEdocument extends Command
{
    protected $signature = 'vucem:edocument {folio}';
    protected $description = 'Descarga info de un COVE por su folio alfanumérico';

    public function handle(ConsultarEdocumentService $service)
    {
        $folio = $this->argument('folio');
        $this->info("🚀 Consultando Edocument: $folio");
        
        // Simular login si es necesario, o confiar en el fallback del servicio
        
        $resultado = $service->consultarPorFolio($folio);
        
        dd($resultado);
    }
}