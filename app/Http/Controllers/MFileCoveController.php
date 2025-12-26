<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MFileParserService;
use Illuminate\Support\Facades\Log;

class MFileCoveController extends Controller
{
    private $mFileParser;

    public function __construct()
    {
        $this->mFileParser = new MFileParserService();
    }

    /**
     * Procesar archivo M y obtener COVEs
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function procesarArchivo(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:txt,m|max:10240', // Max 10MB
        ]);

        try {
            // Guardar archivo temporalmente
            $archivo = $request->file('archivo');
            $rutaTemporal = $archivo->store('temp', 'local');
            $rutaCompleta = storage_path('app/' . $rutaTemporal);

            Log::info("Procesando archivo M: " . $archivo->getClientOriginalName());

            // Procesar archivo M para obtener COVEs
            $resultados = $this->mFileParser->processMFileForCove($rutaCompleta);

            // Limpiar archivo temporal
            unlink($rutaCompleta);

            // Preparar estadísticas
            $estadisticas = [
                'total_operaciones' => count($resultados),
                'coves_encontrados' => count(array_filter($resultados, fn($op) => $op['estatusCove'] === 'encontrado')),
                'coves_no_encontrados' => count(array_filter($resultados, fn($op) => $op['estatusCove'] === 'no_encontrado')),
                'errores_ws' => count(array_filter($resultados, fn($op) => $op['estatusCove'] === 'error_ws')),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Archivo procesado exitosamente',
                'data' => [
                    'operaciones' => $resultados,
                    'estadisticas' => $estadisticas,
                    'nombre_archivo' => $archivo->getClientOriginalName(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando archivo M: ' . $e->getMessage());

            // Limpiar archivo temporal si existe
            if (isset($rutaCompleta) && file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error procesando archivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ejemplo de uso directo de la función
     */
    public function ejemploUso()
    {
        try {
            // Ejemplo de uso directo
            $rutaArchivo = storage_path('app/archivo_m_ejemplo.txt');
            
            if (!file_exists($rutaArchivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo de ejemplo no encontrado'
                ]);
            }

            $resultados = $this->mFileParser->processMFileForCove($rutaArchivo);

            return response()->json([
                'success' => true,
                'message' => 'Procesamiento exitoso',
                'ejemplo_resultado' => $resultados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}