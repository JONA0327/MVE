<?php

namespace App\Services;

/**
 * Parser de Archivo EME (Formato de Pedimento Electrónico)
 * Extrae información del archivo EME para prellenar SOLO campos que existen en la Manifestación de Valor
 * NO duplica información de partidas, fracciones ni regulaciones
 */
class EmeParserService
{
    /**
     * Parse el contenido del archivo EME
     * 
     * @param string $content Contenido del archivo EME
     * @return array Datos estructurados para precarga
     */
    public function parse(string $content): array
    {
        $lines = explode("\n", $content);
        $data = [
            'rfc_importador' => null,
            'razon_social_importador' => null,
            'domicilio_fiscal_importador' => null,
            'cove' => null,
            'incoterm' => null,
            'fecha_factura' => null,
            'fecha_entrada' => null,
            'fecha_pago_pedimento' => null,
            'fecha_presentacion' => null,
            'numero_pedimento' => null,
            'numero_pedimento_raw' => null,
            'patente' => null,
            'aduana_clave' => null,
            'total_precio_pagado' => null,
            'total_valor_aduana' => null,
            'observaciones_pedimento' => '',
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $fields = explode('|', $line);
            $recordType = $fields[0] ?? '';

            // ========================================================================
            // REGISTRO 500: Datos generales del pedimento
            // ========================================================================
            if ($recordType == '500' && count($fields) > 3) {
                $data['patente'] = $fields[1] ?? null;
                $data['numero_pedimento_raw'] = $fields[2] ?? ''; // Guardar sin formatear
                $data['aduana_clave'] = substr($fields[3] ?? '', 0, 3); // Solo clave
            }

            // ========================================================================
            // REGISTRO 501: RFC y Razón Social del importador
            // ========================================================================
            if ($recordType == '501' && count($fields) > 2) {
                $rfc = trim($fields[1] ?? '');
                // Validar que sea RFC válido (12-13 caracteres)
                if (strlen($rfc) >= 12 && strlen($rfc) <= 13) {
                    $data['rfc_importador'] = strtoupper($rfc);
                }
                
                $data['razon_social_importador'] = trim($fields[2] ?? '');
                
                // Construir domicilio fiscal como texto simple
                $domicilioParts = [];
                if (!empty($fields[3])) $domicilioParts[] = trim($fields[3]); // Calle
                if (!empty($fields[4])) $domicilioParts[] = "Col. " . trim($fields[4]); // Colonia
                if (!empty($fields[5])) $domicilioParts[] = "CP " . trim($fields[5]); // CP
                if (!empty($fields[6])) $domicilioParts[] = trim($fields[6]); // Municipio
                if (!empty($fields[7])) $domicilioParts[] = trim($fields[7]); // Estado
                
                if (count($domicilioParts) > 0) {
                    $data['domicilio_fiscal_importador'] = implode(', ', $domicilioParts);
                }
            }

            // ========================================================================
            // REGISTRO 505: COVE, Incoterm y Fecha de Factura
            // ========================================================================
            if ($recordType == '505' && count($fields) > 10) {
                // COVE (e-Document)
                if (!empty($fields[3])) {
                    $data['cove'] = trim($fields[3]);
                }
                
                // Incoterm (extraer código de 3 letras)
                if (!empty($fields[4])) {
                    $incotermValue = trim($fields[4]);
                    if (strpos($incotermValue, '.') !== false) {
                        $parts = explode('.', $incotermValue);
                        $data['incoterm'] = strtoupper(substr(trim($parts[1]), 0, 3));
                    } else {
                        $data['incoterm'] = strtoupper(substr($incotermValue, 0, 3));
                    }
                }
                
                // Fecha de factura (campo 10, formato ddmmyyyy)
                if (!empty($fields[10])) {
                    $data['fecha_factura'] = $this->convertDate($fields[10]);
                }
            }

            // ========================================================================
            // REGISTRO 506: Fechas informativas
            // ========================================================================
            if ($recordType == '506' && count($fields) > 2) {
                $tipoFecha = $fields[1] ?? '';
                $fecha = $this->convertDate($fields[2] ?? '');
                
                if ($fecha) {
                    switch ($tipoFecha) {
                        case '1': // Fecha de entrada
                            $data['fecha_entrada'] = $fecha;
                            break;
                        case '2': // Fecha de pago
                            $data['fecha_pago_pedimento'] = $fecha;
                            break;
                        case '7': // Fecha de presentación
                            $data['fecha_presentacion'] = $fecha;
                            break;
                    }
                }
            }

            // ========================================================================
            // REGISTRO 509: Totales (Precio Pagado y Valor en Aduana)
            // ========================================================================
            if ($recordType == '509' && count($fields) > 2) {
                $tipo = $fields[1] ?? '';
                $importe = floatval($fields[2] ?? 0);
                
                if ($tipo == '1') { // Precio Pagado
                    $data['total_precio_pagado'] = $importe;
                }
                if ($tipo == '15') { // Valor en Aduana
                    $data['total_valor_aduana'] = $importe;
                }
            }

            // ========================================================================
            // REGISTRO 511: Observaciones del pedimento
            // ========================================================================
            if ($recordType == '511' && count($fields) > 1) {
                $observacion = trim($fields[1] ?? '');
                if (!empty($observacion)) {
                    if (!empty($data['observaciones_pedimento'])) {
                        $data['observaciones_pedimento'] .= "\n";
                    }
                    $data['observaciones_pedimento'] .= $observacion;
                }
            }
        }

        // Formatear número de pedimento al final, usando las fechas procesadas
        if (!empty($data['numero_pedimento_raw'])) {
            $data['numero_pedimento'] = $this->formatPedimento($data['numero_pedimento_raw'], $data);
        }

        return $data;
    }

    /**
     * Convierte fecha de formato ddmmyyyy a yyyy-mm-dd
     */
    private function convertDate(string $date): ?string
    {
        $cleaned = preg_replace('/\D/', '', $date);
        if (strlen($cleaned) == 8) {
            $day = substr($cleaned, 0, 2);
            $month = substr($cleaned, 2, 2);
            $year = substr($cleaned, 4, 4);
            
            if (checkdate($month, $day, $year)) {
                return "$year-$month-$day";
            }
        }
        return null;
    }

    /**
     * Formatea número de pedimento con el año correcto: YY  XXX  XXXX  XXXXXXX
     * Formato: año (2 dígitos) + aduana (3 dígitos) + patente (4 dígitos) + folio (7 dígitos)
     */
    private function formatPedimento(string $pedimento, array $data = []): string
    {
        $cleaned = preg_replace('/\D/', '', $pedimento);
        
        // Obtener año de las fechas disponibles (prioridad: pago > presentacion > entrada > factura)
        $year = null;
        if (!empty($data['fecha_pago_pedimento'])) {
            $year = substr($data['fecha_pago_pedimento'], 0, 4);
        } elseif (!empty($data['fecha_presentacion'])) {
            $year = substr($data['fecha_presentacion'], 0, 4);
        } elseif (!empty($data['fecha_entrada'])) {
            $year = substr($data['fecha_entrada'], 0, 4);
        } elseif (!empty($data['fecha_factura'])) {
            $year = substr($data['fecha_factura'], 0, 4);
        }
        
        // Si no hay fechas, usar año actual
        if (!$year) {
            $year = date('Y');
        }
        
        $yearShort = substr($year, -2); // Últimos 2 dígitos del año
        
        $parts = [];
        $parts[] = $yearShort; // Año (2 dígitos)
        
        // El número del pedimento del archivo EME ya no incluye el año, solo tiene:
        // aduana (3) + patente (4) + folio (7) = 14 dígitos
        if (strlen($cleaned) >= 3) $parts[] = substr($cleaned, 0, 3);     // Aduana (3 dígitos)
        if (strlen($cleaned) >= 7) $parts[] = substr($cleaned, 3, 4);     // Patente (4 dígitos)
        if (strlen($cleaned) >= 7) $parts[] = substr($cleaned, 7);        // Folio (resto)
        
        return implode('  ', $parts);
    }
}
