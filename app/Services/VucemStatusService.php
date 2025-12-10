<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VucemStatusService
{
    /**
     * Verifica si el portal de VUCEM está respondiendo.
     * Retorna true si hay problemas (caído/mantenimiento).
     */
    public function isDown(): bool
    {
        // Guardamos el estado en caché por 5 minutos
        return Cache::remember('vucem_status_down', 300, function () {
            try {
                // URL pública de VUCEM
                $targetUrl = 'https://www.ventanillaunica.gob.mx/vucem/Ingreso.html';
                
                $response = Http::withHeaders([
                    // IMPORTANTE: Nos disfrazamos de navegador real para evitar bloqueos
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                ])
                ->timeout(5) // Subimos un poco el timeout por si VUCEM está lento pero funcional
                ->connectTimeout(5)
                // Deshabilitar verificación SSL si VUCEM tiene certificados intermedios rotos (común en gobierno)
                ->withoutVerifying() 
                ->get($targetUrl);

                // 1. Si el estatus es error de servidor (500+) -> CAÍDO
                if ($response->serverError()) {
                    return true;
                }

                // 2. Análisis de contenido más estricto
                // Solo marcamos caído si dice explícitamente "Sitio en mantenimiento" o similar en el título o encabezado
                // Evitamos falsos positivos por noticias que contengan la palabra "mantenimiento"
                $body = strtolower($response->body());
                
                // Frases comunes de fallo real
                if (
                    str_contains($body, '<title>mantenimiento</title>') || 
                    str_contains($body, 'servicio no disponible') ||
                    str_contains($body, 'intermitencia en el servicio')
                ) {
                    return true;
                }

                return false; // Todo parece estar bien

            } catch (\Exception $e) {
                // Si falla la conexión por timeout o DNS, asumimos caído
                return true; 
            }
        });
    }
}