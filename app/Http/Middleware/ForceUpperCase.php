<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUpperCase
{
    /**
     * Lista de campos que NO deben convertirse a mayúsculas.
     */
    protected $except = [
        'password',
        'password_confirmation',
        'current_password',
        'email',
        '_token',
        '_method',
        'file', // Los archivos binarios no se tocan
        'cer_file',
        'key_file',
        'tipo_figura', // No convertir tipo de figura a mayúsculas
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        // Recorremos todos los inputs de manera recursiva (para arrays como coves[], pedimentos[])
        array_walk_recursive($input, function (&$value, $key) {
            // Si es un string y la clave no está en la lista de excepciones
            if (is_string($value) && !in_array($key, $this->except)) {
                $value = mb_strtoupper($value, 'UTF-8');
            }
        });

        // Reemplazamos los inputs del request con los convertidos
        $request->merge($input);

        return $next($request);
    }
}