<?php

namespace App\Http\Controllers;

use App\Services\Vucem\ConsultaAcusesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AcuseController extends Controller
{
    /**
     * Descargar acuse de eDocument o COVE
     * 
     * Descarga el acuse desde VUCEM si no existe localmente.
     * El servicio detecta automáticamente el tipo según el formato del folio:
     * - Formato eDocument (ej: 0170220LIS5D4) → Acuse de eDocument
     * - Formato COVE (ej: COVE214KNPVU4) → Acuse de COVE (Acuse de Valor)
     * 
     * @param string $folio Folio de eDocument o COVE
     */
    public function descargarAcuse(string $folio)
    {
        try {
            // Detectar tipo de acuse
            $tipo = preg_match('/^COVE[A-Z0-9]+$/i', $folio) ? 'COVE' : 'eDocument';
            $filename = "acuse_{$folio}.pdf";
            $path = "acuses/{$filename}";

            Log::info("[ACUSE] Solicitud de descarga", [
                'folio' => $folio,
                'tipo' => $tipo
            ]);

            // Verificar si ya existe el archivo
            if (Storage::exists($path)) {
                Log::info("[ACUSE] Archivo encontrado en cache", [
                    'path' => $path
                ]);
                
                return response()->file(
                    Storage::path($path),
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $filename . '"'
                    ]
                );
            }

            // No existe, consultar al servicio VUCEM
            Log::info("[ACUSE] Consultando VUCEM", [
                'folio' => $folio
            ]);

            $service = new ConsultaAcusesService();
            $result = $service->consultarAcuse($folio);

            if (!$result['success']) {
                Log::error("[ACUSE] Error al consultar", [
                    'folio' => $folio,
                    'error' => $result['message'] ?? 'Error desconocido',
                    'mensajes_error' => $result['mensajes_error'] ?? []
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener el acuse desde VUCEM',
                    'error' => $result['descripcion'] ?? $result['message'] ?? 'Error desconocido',
                    'mensajes_error' => $result['mensajes_error'] ?? []
                ], 422);
            }

            // Decodificar el PDF
            $pdfContent = base64_decode($result['acuse_documento']);
            
            if (empty($pdfContent)) {
                Log::error("[ACUSE] El contenido del PDF está vacío", [
                    'folio' => $folio
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'El acuse está vacío'
                ], 422);
            }

            // Guardar el archivo
            Storage::put($path, $pdfContent);
            
            Log::info("[ACUSE] Archivo guardado exitosamente", [
                'folio' => $folio,
                'path' => $path,
                'size' => strlen($pdfContent)
            ]);

            // Devolver el PDF
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error("[ACUSE] Excepción al descargar", [
                'folio' => $folio,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al descargar el acuse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista previa de acuses disponibles (para debug)
     */
    public function listarAcuses()
    {
        try {
            $files = Storage::files('acuses');
            
            $acuses = collect($files)->map(function ($file) {
                return [
                    'nombre' => basename($file),
                    'folio' => str_replace(['acuse_', '.pdf'], '', basename($file)),
                    'tamaño' => Storage::size($file),
                    'fecha' => Storage::lastModified($file),
                    'url' => route('acuses.descargar', ['folio' => str_replace(['acuse_', '.pdf'], '', basename($file))])
                ];
            })->sortByDesc('fecha')->values();

            return response()->json([
                'success' => true,
                'total' => $acuses->count(),
                'acuses' => $acuses
            ]);

        } catch (\Exception $e) {
            Log::error("[ACUSE] Error al listar", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al listar acuses: ' . $e->getMessage()
            ], 500);
        }
    }
}
