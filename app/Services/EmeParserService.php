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
                $data['numero_pedimento'] = $this->formatPedimento($fields[2] ?? '');
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
     * Formatea número de pedimento: XX  XXX  XXXX  XXXXXXX
     */
    private function formatPedimento(string $pedimento): string
    {
        $cleaned = preg_replace('/\D/', '', $pedimento);
        $parts = [];
        
        if (strlen($cleaned) > 0) $parts[] = substr($cleaned, 0, 2);
        if (strlen($cleaned) > 2) $parts[] = substr($cleaned, 2, 3);
        if (strlen($cleaned) > 5) $parts[] = substr($cleaned, 5, 4);
        if (strlen($cleaned) > 9) $parts[] = substr($cleaned, 9);
        
        return implode('  ', $parts);
    }
}
