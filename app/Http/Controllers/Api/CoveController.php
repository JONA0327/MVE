<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Vucem\ConsultarCoveService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CoveController extends Controller
{
    private ConsultarCoveService $consultarCoveService;

    public function __construct(ConsultarCoveService $consultarCoveService)
    {
        $this->consultarCoveService = $consultarCoveService;
    }

    /**
     * Consultar información de un COVE por su folio
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function showByFolio(Request $request): JsonResponse
    {
        try {
            // Validar entrada
            $validator = Validator::make($request->all(), [
                'cove' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                    'regex:/^[A-Za-z0-9\-_]+$/', // Solo alfanuméricos, guiones y guiones bajos
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $folioCove = trim($request->input('cove'));

            // Log de la solicitud
            Log::info('API: Consultando COVE', [
                'folio' => $folioCove,
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            // Validación previa para evitar consultas innecesarias
            if (strlen($folioCove) < 5 || strlen($folioCove) > 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'El folio de COVE debe tener entre 5 y 20 caracteres',
                    'error_type' => 'validation_error'
                ], 400);
            }

            // Llamar al servicio SOAP con timeout controlado
            $startTime = microtime(true);
            
            try {
                $result = $this->consultarCoveService->consultarCove($folioCove);
            } catch (\Exception $e) {
                $executionTime = round((microtime(true) - $startTime) * 1000);
                
                Log::error('API: Error en servicio COVE', [
                    'folio' => $folioCove,
                    'execution_time_ms' => $executionTime,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error interno del servicio VUCEM',
                    'error_type' => 'service_error'
                ], 500);
            }
            
            $executionTime = round((microtime(true) - $startTime) * 1000);

            // Log del resultado
            if ($result['success']) {
                Log::info('API: COVE encontrado exitosamente', [
                    'folio' => $folioCove,
                    'numero_factura' => $result['data']['numero_factura'] ?? 'N/A'
                ]);
            } else {
                Log::warning('API: Error consultando COVE', [
                    'folio' => $folioCove,
                    'error_type' => $result['error_type'] ?? 'unknown',
                    'message' => $result['message']
                ]);
            }

            // Preparar respuesta
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'COVE consultado exitosamente',
                    'data' => [
                        'cove' => $result['data']['folio'] ?? $folioCove,
                        'metodo_valoracion' => $result['data']['metodo_valoracion'] ?? '1',
                        'numero_factura' => $result['data']['numero_factura'] ?? 'N/A',
                        'fecha_expedicion' => $result['data']['fecha_expedicion'] ?? date('Y-m-d'),
                        'emisor' => $result['data']['emisor'] ?? 'N/A',
                        'edocument' => $result['data']['folio'] ?? $folioCove,
                        // Datos adicionales si están disponibles
                        'estatus' => $result['data']['estatus'] ?? 'Válido',
                        'fecha_emision' => $result['data']['fecha_emision'] ?? date('Y-m-d'),
                        'rfc_solicitante' => $result['data']['rfc_solicitante'] ?? ''
                    ]
                ]);
            } else {
                // Determinar código de respuesta HTTP apropiado
                $statusCode = $this->getStatusCodeFromErrorType($result['error_type'] ?? 'unknown');
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_type' => $result['error_type'] ?? 'unknown',
                    'details' => $result['details'] ?? null
                ], $statusCode);
            }

        } catch (\Exception $e) {
            // Error inesperado
            Log::error('API: Error inesperado consultando COVE', [
                'folio' => $request->input('cove', 'N/A'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Intente más tarde.',
                'error_type' => 'server_error'
            ], 500);
        }
    }

    /**
     * Obtener el código de estado HTTP apropiado según el tipo de error
     */
    private function getStatusCodeFromErrorType(string $errorType): int
    {
        return match ($errorType) {
            'validation_error' => 422, // Unprocessable Entity
            'auth_error' => 401,       // Unauthorized
            'profile_incomplete' => 422, // Unprocessable Entity
            'config_error' => 503,     // Service Unavailable
            'soap_fault' => 502,       // Bad Gateway
            'network_error' => 502,    // Bad Gateway
            'cove_not_found' => 404,   // Not Found
            'empty_response' => 502,   // Bad Gateway
            'invalid_response_format' => 502, // Bad Gateway
            'processing_error' => 502, // Bad Gateway
            default => 500             // Internal Server Error
        };
    }

    /**
     * Endpoint para pruebas de conectividad (solo en modo debug)
     */
    public function testConnection(Request $request): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no disponible en producción'
            ], 403);
        }

        try {
            // Probar con un COVE de ejemplo
            $testCove = $request->input('test_cove', '12345');
            $result = $this->consultarCoveService->consultarCove($testCove);
            
            return response()->json([
                'success' => true,
                'message' => 'Test de conexión completado',
                'result' => $result,
                'debug_info' => $this->consultarCoveService->getDebugInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en test de conexión: ' . $e->getMessage(),
                'debug_info' => $this->consultarCoveService->getDebugInfo()
            ], 500);
        }
    }

    /**
     * Verificar configuración de credenciales VUCEM
     */
    public function checkConfiguration(): JsonResponse
    {
        if (!config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no disponible en producción'
            ], 403);
        }

        $config = [
            'user_authenticated' => Auth::check(),
            'user_has_rfc' => Auth::check() && !empty(Auth::user()->rfc),
            'user_has_webservice_key' => Auth::check() && Auth::user()->hasWebserviceKey(),
            'wsdl_exists' => file_exists(base_path('wsdl/vucem/ConsultarRespuestaCove.wsdl')),
            'soap_enabled' => extension_loaded('soap')
        ];

        $allOk = array_reduce($config, fn($carry, $item) => $carry && $item, true);

        return response()->json([
            'success' => $allOk,
            'message' => $allOk ? 'Configuración correcta' : 'Hay problemas de configuración',
            'configuration' => $config
        ]);
    }
}
