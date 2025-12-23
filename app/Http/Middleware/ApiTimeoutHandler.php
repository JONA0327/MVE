<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiTimeoutHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo aplicar a rutas de API VUCEM
        if (!str_starts_with($request->path(), 'api/')) {
            return $next($request);
        }

        // Configurar timeouts agresivos para APIs
        ini_set('max_execution_time', 15);
        ini_set('default_socket_timeout', 8);
        set_time_limit(15);
        
        // Iniciar medición de tiempo
        $startTime = microtime(true);
        
        try {
            $response = $next($request);
            
            // Medir tiempo de ejecución
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log de rendimiento
            if ($executionTime > 5000) { // Más de 5 segundos
                Log::warning('API: Petición lenta detectada', [
                    'path' => $request->path(),
                    'execution_time_ms' => $executionTime,
                    'user_id' => auth()->id()
                ]);
            }
            
            // Agregar headers de timeout
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
            
            return $response;
            
        } catch (\Throwable $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            Log::error('API: Error con timeout', [
                'path' => $request->path(),
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            // Retornar respuesta de error rápida
            return response()->json([
                'success' => false,
                'message' => 'El servicio tardó demasiado en responder',
                'error_type' => 'timeout_error',
                'execution_time' => $executionTime . 'ms'
            ], 504); // Gateway Timeout
        }
    }
}