<?php

namespace App\Services;

class MFileParserService
{
    public function parse($content)
    {
        $lines = explode("\n", $content);
        $data = [
            'rfc_importador' => null,
            'razon_social_importador' => null,
            'pedimento_clave' => null,
            'patente' => null,
            'aduana' => null,
            'tipo_operacion' => null,
            'incoterm' => null,
            'coves' => [],
            'pedimentos' => [],
            'partidas' => [],
            'fechas' => [],
        ];

        $currentPedimento = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $fields = explode('|', $line);
            $recordType = $fields[0] ?? '';

            // Registro 500: Encabezado del Pedimento
            // 500|1|3685|5003763|470||
            if ($recordType == '500' && count($fields) > 4) {
                $data['patente'] = $fields[2] ?? null;
                $data['pedimento_clave'] = $fields[3] ?? null;
                $data['aduana'] = $fields[4] ?? null;
                $currentPedimento = $fields[3] ?? null;
            }

            // Registro 501: Datos Generales del Importador
            // 501|3685|5003763|470|1|F4|470||LMT1310031H0|GOMC581110HTSRRR02|18.35070|0|0|0|0||0.229|98|98|98|9|LUMINUS MEXICO TRADING, S.A. DE C.V.|...
            if ($recordType == '501') {
                $data['rfc_importador'] = $fields[8] ?? null;
                $data['razon_social_importador'] = $fields[20] ?? null;
                $data['tipo_operacion'] = $fields[5] ?? null; // F4 = definitiva
            }

            // Registro 505: Datos de COVE y Proveedor
            // 505|5003763|24102025|COVE257U4SBT4|FCA||||||NAL121213F3A|NORTH AMERICAN LIGHTING MEXICO, S.A DE C.V.||||||
            if ($recordType == '505' && count($fields) > 10) {
                $cove = $fields[3] ?? '';
                if (!empty($cove)) {
                    $data['coves'][] = [
                        'edocument' => $cove,
                        'numero_factura' => $fields[2] ?? '',
                        'incoterm' => $fields[4] ?? '',
                        'rfc_proveedor' => $fields[10] ?? '',
                        'nombre_proveedor' => $fields[11] ?? '',
                    ];
                    
                    // Guardar incoterm global si existe
                    if (!empty($fields[4]) && empty($data['incoterm'])) {
                        $data['incoterm'] = $fields[4];
                    }
                }
            }

            // Registro 506: Fechas del pedimento
            // 506|5003763|1|01102025| (tipo 1 = fecha de entrada)
            // 506|5003763|2|21112025| (tipo 2 = fecha de pago)
            // 506|5003763|7|14112025| (tipo 7 = fecha de entrada a recinto)
            if ($recordType == '506' && count($fields) > 3) {
                $tipoFecha = $fields[2] ?? '';
                $fecha = $fields[3] ?? '';
                if (!empty($fecha)) {
                    $data['fechas'][] = [
                        'tipo' => $tipoFecha,
                        'fecha' => $fecha,
                    ];
                }
            }

            // Registro 507: Contribuciones
            // 507|5003763|SO|AA||| (tipo de contribución)
            if ($recordType == '507') {
                // Puede almacenar tipos de contribuciones si es necesario
            }

            // Registro 509: Valores
            // 509|5003763|1|445.00000|4|
            // 509|5003763|15|290.00000|2|
            if ($recordType == '509' && count($fields) > 3) {
                $tipoValor = $fields[2] ?? '';
                $monto = $fields[3] ?? '';
                // Almacenar valores si es necesario para incrementables/decrementables
            }

            // Registro 512: Pedimentos anteriores (para rectificaciones)
            // 512|5003763|3685|5003303|470|V1|14112025|39269099|1|0.22900|
            if ($recordType == '512' && count($fields) > 8) {
                $data['pedimentos'][] = [
                    'numero_pedimento' => ($fields[2] ?? '') . ' ' . ($fields[3] ?? '') . ' ' . ($fields[4] ?? ''),
                    'fecha' => $fields[6] ?? '',
                    'fraccion' => $fields[7] ?? '',
                ];
            }

            // Registro 551: Partidas (mercancías)
            // 551|5003763|39269099|1|06|EMPAQUETADURAS PARA USO AUTOMOTRIZ|1.89083|433|433|23.58|229.000|6|0.22900|1||0|1||||MEX|MEX|||||
            if ($recordType == '551' && count($fields) > 10) {
                $data['partidas'][] = [
                    'fraccion_arancelaria' => $fields[2] ?? '',
                    'descripcion' => $fields[5] ?? '',
                    'cantidad' => $fields[6] ?? '',
                    'unidad' => $fields[7] ?? '',
                    'valor_unitario' => $fields[9] ?? '',
                    'valor_aduana' => $fields[10] ?? '',
                ];
            }
        }

        return $data;
    }
}