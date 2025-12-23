<?php

namespace App\Services;

/**
 * Parser de Archivo M (Anexo 22 SAT)
 * Extrae información del archivo M para prellenar Manifestación de Valor
 * SOLO USA CAMPOS EXISTENTES EN LA BASE DE DATOS
 */
class MFileParserService
{
    public function parse($content)
    {
        \Log::info('========================================');
        \Log::info('INICIANDO PARSE DE ARCHIVO EME');
        \Log::info('Longitud del contenido: ' . strlen($content));
        
        $lines = explode("\n", $content);
        \Log::info('Total de líneas: ' . count($lines));
        
        $data = [
            // Campos manifestations tabla
            'rfc_importador' => null,
            'razon_social_importador' => null,
            'registro_nacional_contribuyentes' => null,
            'domicilio_fiscal_importador' => null,
            'metodo_valoracion_global' => null,
            'incoterm' => null,
            'total_precio_pagado' => 0,
            'total_valor_aduana' => 0,
            
            // Datos adicionales para contexto
            'patente' => null,
            'aduana_clave' => null,
            'pedimento_numero' => null,
            
            // Datos del exportador/proveedor (del registro 502)
            'rfc_exportador' => null,
            'nombre_exportador' => null,
            
            // Relaciones
            'coves' => [],           // manifestation_coves
            'pedimentos' => [],      // manifestation_pedimentos
            'adjustments' => [],     // manifestation_adjustments
            'payments' => [],        // manifestation_payments
        ];

        $valoresPorTipo = []; // Para acumular valores del registro 509

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $fields = explode('|', $line);
            $recordType = $fields[0] ?? '';

            // ========================================================================
            // REGISTRO 500: Encabezado del Pedimento
            // ========================================================================
            if ($recordType == '500' && count($fields) > 4) {
                $data['patente'] = $fields[2] ?? null;
                $data['pedimento_numero'] = $fields[3] ?? null;
                $data['aduana_clave'] = $fields[4] ?? null;
            }

            // ========================================================================
            // REGISTRO 501: Datos Generales del Importador
            // ========================================================================
            // Estructura: 501|patente|pedimento|aduana|tipo|clave|aduana2|vacio|RFC|CURP|...
            // Campo 8 = RFC (13 caracteres)
            // Campo 9 = CURP (18 caracteres)
            if ($recordType == '501') {
                \Log::info('=== REGISTRO 501 DETECTADO ===');
                \Log::info('Total campos: ' . count($fields));
                \Log::info('Primeros 25 campos:');
                for ($i = 0; $i < min(25, count($fields)); $i++) {
                    \Log::info("  Campo $i: [" . ($fields[$i] ?? 'NULL') . "] (len=" . strlen($fields[$i] ?? '') . ")");
                }
                
                if (count($fields) > 21) {
                    // RFC siempre tiene 12 o 13 caracteres (persona moral o física)
                    // CURP siempre tiene 18 caracteres
                    $campo8 = trim($fields[8] ?? '');
                    $campo9 = trim($fields[9] ?? '');
                    
                    // Tomar el campo que tenga 12 o 13 caracteres (RFC) - Este va a RFCs consultables
                    $rfcConsultable = null;
                    $razonSocialConsultable = null;
                    
                    if (strlen($campo8) >= 12 && strlen($campo8) <= 13) {
                        $rfcConsultable = $campo8;
                        \Log::info('RFC consultable encontrado en campo 8: ' . $campo8);
                    } elseif (strlen($campo9) >= 12 && strlen($campo9) <= 13) {
                        $rfcConsultable = $campo9;
                        \Log::info('RFC consultable encontrado en campo 9: ' . $campo9);
                    } else {
                        \Log::warning('No se encontró RFC válido en campos 8 o 9');
                        \Log::warning('Campo 8: [' . $campo8 . '] len=' . strlen($campo8));
                        \Log::warning('Campo 9: [' . $campo9 . '] len=' . strlen($campo9));
                    }
                    
                    // La razón social del campo 21 corresponde al RFC consultable
                    if ($rfcConsultable && !empty($fields[21])) {
                        $razonSocialConsultable = $fields[21];
                        \Log::info('Razón Social consultable (campo 21): ' . $razonSocialConsultable);
                        
                        // Agregar RFC consultable del EME
                        $data['rfc_consultable_eme'] = [
                            'rfc_consulta' => $rfcConsultable,
                            'razon_social' => $razonSocialConsultable,
                            'source' => 'eme'
                        ];
                    }
                } else {
                    \Log::warning('Registro 501 tiene menos de 22 campos, no se puede extraer datos');
                }
            }

            // ========================================================================
            // REGISTRO 502: Datos del Proveedor/Exportador
            // ========================================================================
            // Estructura: 502|patente|pedimento|aduana|tipo_proveedor|id_fiscal|nombre|...
            // Campo 4 = Tipo (1=Nacional, 2=Extranjero)
            // Campo 5 = ID Fiscal (RFC o Tax ID)
            // Campo 6 = Nombre/Razón Social
            if ($recordType == '502' && count($fields) > 6) {
                \Log::info('=== REGISTRO 502 DETECTADO ===');
                \Log::info('Total campos: ' . count($fields));
                \Log::info('Campo 4 (tipo): ' . ($fields[4] ?? 'NULL'));
                \Log::info('Campo 5 (id_fiscal): ' . ($fields[5] ?? 'NULL'));
                \Log::info('Campo 6 (nombre): ' . ($fields[6] ?? 'NULL'));
                \Log::info('Línea completa: ' . substr($line, 0, 200));
                
                $idFiscal = trim($fields[5] ?? '');
                $nombreProveedor = trim($fields[6] ?? '');
                
                // Guardar RFC del exportador si existe
                if (!empty($idFiscal)) {
                    $data['rfc_exportador'] = $idFiscal;
                    \Log::info('RFC Exportador guardado: ' . $idFiscal);
                }
                
                // Guardar nombre del exportador
                if (!empty($nombreProveedor)) {
                    $data['nombre_exportador'] = $nombreProveedor;
                    \Log::info('Nombre Exportador guardado: ' . $nombreProveedor);
                }
            }

            // ========================================================================
            // REGISTRO 505: COVE y Factura
            // ========================================================================
            // Mapea a: manifestation_coves (edocument, numero_factura, fecha_expedicion, emisor)
            if ($recordType == '505' && count($fields) > 11) {
                $edocument = $fields[3] ?? '';
                if (!empty($edocument)) {
                    $data['coves'][] = [
                        'edocument' => $edocument,
                        'numero_factura' => $fields[2] ?? '',
                        'fecha_expedicion' => $this->convertDate($fields[2] ?? ''), // ddmmyyyy -> yyyy-mm-dd
                        'emisor' => $fields[11] ?? '',
                        'metodo_valoracion' => '1', // Default
                    ];
                    
                    // Guardar incoterm global (manifestations.incoterm - máx 3 caracteres)
                    if (!empty($fields[4]) && empty($data['incoterm'])) {
                        // Extraer incoterm: si contiene punto, tomar el código después del punto
                        // Ejemplo: "TIPINC.CIF" -> "CIF", "FOB" -> "FOB"
                        $incotermValue = trim($fields[4]);
                        if (strpos($incotermValue, '.') !== false) {
                            $parts = explode('.', $incotermValue);
                            $data['incoterm'] = strtoupper(substr(trim($parts[1]), 0, 3));
                        } else {
                            $data['incoterm'] = strtoupper(substr($incotermValue, 0, 3));
                        }
                    }
                }
            }

            // ========================================================================
            // REGISTRO 509: Valores y Totales
            // ========================================================================
            // Tipo 1 = Valor factura / Precio pagado
            // Tipo 15 = Valor aduana
            if ($recordType == '509' && count($fields) > 3) {
                $tipoValor = $fields[2] ?? '';
                $monto = floatval($fields[3] ?? 0);
                
                if ($tipoValor == '1') {
                    // total_precio_pagado
                    $data['total_precio_pagado'] += $monto;
                } elseif ($tipoValor == '15') {
                    // total_valor_aduana
                    $data['total_valor_aduana'] += $monto;
                }
                
                $valoresPorTipo[$tipoValor] = $monto;
            }

            // ========================================================================
            // REGISTRO 512: Pedimentos Anteriores
            // ========================================================================
            // Mapea a: manifestation_pedimentos (numero_pedimento, patente, aduana_clave)
            if ($recordType == '512' && count($fields) > 4) {
                $patente = $fields[2] ?? '';
                $pedimento = $fields[3] ?? '';
                $aduana = $fields[4] ?? '';
                
                if (!empty($pedimento)) {
                    // Formato correcto: YY  AAA  PPPP  NNNNNNN
                    // YY = año actual (2 dígitos), AAA = aduana, PPPP = patente, NNNNNNN = folio
                    $currentYear = date('y'); // Últimos 2 dígitos del año actual
                    $numeroPedimentoFormateado = sprintf(
                        '%s  %s  %s  %s',
                        str_pad($currentYear, 2, '0', STR_PAD_LEFT),     // Año
                        str_pad($aduana, 3, '0', STR_PAD_LEFT),          // Aduana (3 dígitos)
                        str_pad($patente, 4, '0', STR_PAD_LEFT),         // Patente (4 dígitos) 
                        str_pad($pedimento, 7, '0', STR_PAD_LEFT)        // Folio (7 dígitos)
                    );
                    
                    $data['pedimentos'][] = [
                        'numero_pedimento' => $numeroPedimentoFormateado,
                        'patente' => $patente,
                        'aduana_clave' => $aduana,
                    ];
                }
            }

            // ========================================================================
            // REGISTRO 551: Partidas (para método de valoración)
            // ========================================================================
            // Campo 14 = método de valoración
            if ($recordType == '551' && count($fields) > 14) {
                // Tomar el método de valoración de la primera partida
                if (empty($data['metodo_valoracion_global'])) {
                    $data['metodo_valoracion_global'] = $fields[13] ?? '1';
                }
            }
        }

        // Si no se encontraron pedimentos en 512, usar el pedimento principal del 500
        if (empty($data['pedimentos']) && !empty($data['pedimento_numero'])) {
            // Formato correcto: YY  AAA  PPPP  NNNNNNN
            $currentYear = date('y'); // Últimos 2 dígitos del año actual
            $numeroPedimentoFormateado = sprintf(
                '%s  %s  %s  %s',
                str_pad($currentYear, 2, '0', STR_PAD_LEFT),                    // Año
                str_pad($data['aduana_clave'] ?? '', 3, '0', STR_PAD_LEFT),     // Aduana (3 dígitos)
                str_pad($data['patente'] ?? '', 4, '0', STR_PAD_LEFT),          // Patente (4 dígitos)
                str_pad($data['pedimento_numero'] ?? '', 7, '0', STR_PAD_LEFT)  // Folio (7 dígitos)
            );
            
            $data['pedimentos'][] = [
                'numero_pedimento' => $numeroPedimentoFormateado,
                'patente' => $data['patente'],
                'aduana_clave' => $data['aduana_clave'],
            ];
        }

        \Log::info('=== DATOS FINALES DEL PARSER EME ===');
        \Log::info('RFC Importador: ' . ($data['rfc_importador'] ?? 'NULL'));
        \Log::info('Razón Social: ' . ($data['razon_social_importador'] ?? 'NULL'));
        \Log::info('Registro Nacional: ' . ($data['registro_nacional_contribuyentes'] ?? 'NULL'));
        \Log::info('Domicilio Fiscal: ' . ($data['domicilio_fiscal_importador'] ?? 'NULL'));
        \Log::info('RFC Exportador: ' . ($data['rfc_exportador'] ?? 'NULL'));
        \Log::info('Nombre Exportador: ' . ($data['nombre_exportador'] ?? 'NULL'));
        \Log::info('Total COVEs: ' . count($data['coves']));
        \Log::info('Total Pedimentos: ' . count($data['pedimentos']));

        return $data;
    }

    /**
     * Convierte fecha de formato ddmmyyyy a yyyy-mm-dd
     */
    private function convertDate($dateStr)
    {
        if (!$dateStr || strlen($dateStr) < 8) {
            return null;
        }
        
        // Si ya viene en formato numérico ddmmyyyy
        if (is_numeric($dateStr)) {
            $day = substr($dateStr, 0, 2);
            $month = substr($dateStr, 2, 2);
            $year = substr($dateStr, 4, 4);
            return "{$year}-{$month}-{$day}";
        }
        
        return null;
    }
}