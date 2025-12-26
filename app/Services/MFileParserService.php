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

    /**
     * Procesa un archivo M de pedimento y obtiene/consulta COVEs
     *
     * @param string $filePath Ruta del archivo M a procesar
     * @return array Array de operaciones con sus datos y COVEs
     * @throws \Exception
     */
    public function processMFileForCove(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Archivo no encontrado: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \Exception("Archivo no es legible: {$filePath}");
        }

        $operaciones = [];
        
        try {
            \Log::info("Iniciando procesamiento de archivo M para COVE: {$filePath}");
            
            // Parsear archivo y extraer datos por número de operación
            $datosArchivo = $this->parseFileMForCove($filePath);
            
            // Procesar cada operación
            foreach ($datosArchivo as $numeroOperacion => $datos) {
                $operacion = [
                    'aduana' => $datos['aduana'] ?? null,
                    'seccion' => $datos['seccion'] ?? null,
                    'patente' => $datos['patente'] ?? null,
                    'ejercicio' => $datos['ejercicio'] ?? null,
                    'numeroOperacion' => $numeroOperacion,
                    'folioCove' => null,
                    'estatusCove' => 'no_encontrado',
                ];

                // Verificar si ya tiene COVE en línea 505
                if (!empty($datos['cove_existente'])) {
                    $operacion['folioCove'] = $datos['cove_existente'];
                    $operacion['estatusCove'] = 'encontrado';
                    \Log::info("COVE ya existe para operación {$numeroOperacion}: {$datos['cove_existente']}");
                } else {
                    // Consultar COVE via web service
                    \Log::info("Consultando COVE para operación: {$numeroOperacion}");
                    $coveResult = $this->consultarCoveParaOperacion($numeroOperacion, $datos);
                    
                    if ($coveResult['success']) {
                        $operacion['folioCove'] = $coveResult['cove'];
                        $operacion['estatusCove'] = 'encontrado';
                    } else {
                        $operacion['estatusCove'] = 'error_ws';
                        \Log::warning("Error al consultar COVE para operación {$numeroOperacion}: " . $coveResult['error']);
                    }
                }
                
                $operaciones[] = $operacion;
            }
            
            \Log::info("Procesamiento COVE completado. Total operaciones: " . count($operaciones));
            
        } catch (\Exception $e) {
            \Log::error("Error procesando archivo M para COVE: " . $e->getMessage());
            throw $e;
        }
        
        return $operaciones;
    }

    /**
     * Parsea el archivo M y extrae datos agrupados por número de operación para COVE
     *
     * @param string $filePath
     * @return array
     */
    private function parseFileMForCove(string $filePath): array
    {
        $operaciones = [];
        $aduana = null;
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("No se pudo abrir el archivo: {$filePath}");
        }
        
        try {
            while (($linea = fgets($handle)) !== false) {
                $linea = trim($linea);
                if (empty($linea)) {
                    continue;
                }
                
                $campos = explode('|', $linea);
                $tipoRegistro = $campos[0] ?? '';
                
                switch ($tipoRegistro) {
                    case '500':
                        // 500|1|3429|5000745|480||
                        $patente = $campos[2] ?? null;
                        $numeroOperacion = $campos[3] ?? null;
                        $seccion = $campos[4] ?? null;
                        
                        if ($numeroOperacion) {
                            $operaciones[$numeroOperacion] = [
                                'patente' => $patente,
                                'seccion' => $seccion,
                                'aduana' => null,
                                'ejercicio' => null,
                                'cove_existente' => null,
                            ];
                        }
                        break;
                        
                    case '505':
                        // 505|5000745|05062025|COVE257M4C974|DAP|USD|...
                        $numeroOperacion = $campos[1] ?? null;
                        $fecha = $campos[2] ?? null;
                        $cove = $campos[3] ?? null;
                        
                        if ($numeroOperacion) {
                            if (!isset($operaciones[$numeroOperacion])) {
                                $operaciones[$numeroOperacion] = [
                                    'patente' => null,
                                    'seccion' => null,
                                    'aduana' => null,
                                    'ejercicio' => null,
                                    'cove_existente' => null,
                                ];
                            }
                            
                            // Extraer ejercicio de la fecha (ddMMyyyy -> yyyy)
                            if ($fecha && strlen($fecha) >= 8) {
                                $operaciones[$numeroOperacion]['ejercicio'] = (int) substr($fecha, -4);
                            }
                            
                            // Guardar COVE si existe y no es vacío
                            if ($cove && !empty(trim($cove)) && strpos($cove, 'COVE') === 0) {
                                $operaciones[$numeroOperacion]['cove_existente'] = trim($cove);
                            }
                        }
                        break;
                        
                    case '801':
                        // 801|m3429224.177|1|25|010|
                        $aduanaExtraida = $campos[3] ?? null;
                        if ($aduanaExtraida && is_numeric($aduanaExtraida)) {
                            $aduana = (int) $aduanaExtraida;
                        }
                        break;
                }
            }
            
            // Asignar aduana a todas las operaciones
            foreach ($operaciones as $numeroOperacion => &$datos) {
                $datos['aduana'] = $aduana;
            }
            
        } finally {
            fclose($handle);
        }
        
        return $operaciones;
    }

    /**
     * Consulta COVE para una operación específica
     *
     * @param string $numeroOperacion
     * @param array $datosOperacion
     * @return array ['success' => bool, 'cove' => string|null, 'error' => string|null]
     */
    private function consultarCoveParaOperacion(string $numeroOperacion, array $datosOperacion): array
    {
        try {
            // Construir folio COVE (15 dígitos)
            $folio = $this->construirFolioCove($numeroOperacion, $datosOperacion);
            
            if (!$folio) {
                return [
                    'success' => false,
                    'cove' => null,
                    'error' => 'No se pudo construir folio COVE válido'
                ];
            }
            
            \Log::info("Consultando COVE con folio: {$folio} para operación: {$numeroOperacion}");
            
            // Crear instancia del servicio ConsultarCove
            $consultarCoveService = new \App\Services\Vucem\ConsultarCoveService();
            
            // Usar el servicio existente para consultar
            $resultado = $consultarCoveService->consultarCove($folio);
            
            if ($resultado && $resultado['success']) {
                // Buscar COVE en la respuesta
                $cove = $this->extraerCoveDeRespuesta($resultado['data'] ?? '');
                
                if ($cove) {
                    return [
                        'success' => true,
                        'cove' => $cove,
                        'error' => null
                    ];
                }
            }
            
            return [
                'success' => false,
                'cove' => null,
                'error' => $resultado['error'] ?? 'No se encontró COVE en la respuesta'
            ];
            
        } catch (\Exception $e) {
            \Log::error("Error consultando COVE para operación {$numeroOperacion}: " . $e->getMessage());
            
            return [
                'success' => false,
                'cove' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Construye el folio COVE de 15 dígitos para la consulta
     *
     * @param string $numeroOperacion
     * @param array $datosOperacion
     * @return string|null
     */
    private function construirFolioCove(string $numeroOperacion, array $datosOperacion): ?string
    {
        // Formato correcto: AAPPPPAAANNNNNNN (15 dígitos)
        // AA = código aduana (2 dígitos)
        // PPPP = código patente (4 dígitos) 
        // AA = ejercicio año (últimos 2 dígitos del año)
        // NNNNNNN = número operación (7 dígitos)
        
        $aduana = $datosOperacion['aduana'] ?? null;
        $patente = $datosOperacion['patente'] ?? null;
        $ejercicio = $datosOperacion['ejercicio'] ?? null;
        
        if (!$aduana || !$patente || !$ejercicio || !$numeroOperacion) {
            \Log::warning("Datos insuficientes para construir folio COVE: aduana={$aduana}, patente={$patente}, ejercicio={$ejercicio}, numeroOperacion={$numeroOperacion}");
            return null;
        }
        
        // Formatear componentes según la fórmula correcta
        $aduanaStr = str_pad((string)$aduana, 2, '0', STR_PAD_LEFT);           // 2 dígitos
        $patenteStr = str_pad((string)$patente, 4, '0', STR_PAD_LEFT);         // 4 dígitos
        $ejercicioStr = substr((string)$ejercicio, -2);                        // últimos 2 dígitos del año
        $operacionStr = str_pad((string)$numeroOperacion, 7, '0', STR_PAD_LEFT); // 7 dígitos
        
        $folio = $aduanaStr . $patenteStr . $ejercicioStr . $operacionStr;
        
        \Log::info("Construyendo folio COVE: {$aduanaStr}(aduana) + {$patenteStr}(patente) + {$ejercicioStr}(ejercicio) + {$operacionStr}(operacion) = {$folio}");
        
        // Verificar que tenga exactamente 15 dígitos
        if (strlen($folio) !== 15 || !ctype_digit($folio)) {
            \Log::warning("Folio COVE construido no es válido: {$folio} (longitud: " . strlen($folio) . ")");
            return null;
        }
        
        return $folio;
    }

    /**
     * Extrae el COVE de la respuesta XML del web service
     *
     * @param string $xmlResponse
     * @return string|null
     */
    private function extraerCoveDeRespuesta(string $xmlResponse): ?string
    {
        if (empty($xmlResponse)) {
            return null;
        }
        
        try {
            // Buscar etiquetas que podrían contener el COVE
            $patterns = [
                '/<folioCove[^>]*>([^<]+)<\/folioCove>/i',
                '/<cove[^>]*>([^<]+)<\/cove>/i',
                '/<eDocument[^>]*>([^<]*COVE[^<]*)<\/eDocument>/i',
                '/COVE[A-Z0-9]{8,}/i'
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $xmlResponse, $matches)) {
                    $cove = trim($matches[1] ?? $matches[0]);
                    
                    // Validar que parece un COVE válido
                    if (strpos($cove, 'COVE') === 0 && strlen($cove) >= 10) {
                        return $cove;
                    }
                }
            }
            
            \Log::warning("No se pudo extraer COVE de la respuesta XML");
            return null;
            
        } catch (\Exception $e) {
            \Log::error("Error extrayendo COVE de respuesta: " . $e->getMessage());
            return null;
        }
    }
}