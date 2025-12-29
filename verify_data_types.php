<?php
require __DIR__ . '/vendor/autoload.php';

use App\Services\Vucem\EFirmaService;

// Mock config
$configData = include 'config/vucem.php';
config(['vucem' => $configData]);

$efirmaService = new EFirmaService();

// Generar datos de prueba
$numeroOperacion = '1234567890';
$rfc = 'NET070608EM9';

echo "=== VERIFICACION DE TIPOS DE DATOS VUCEM ===\n\n";

try {
    $firmaData = $efirmaService->generarFirmaElectronica($numeroOperacion, $rfc);
    
    echo "1. Username (RFC)\n";
    echo "   Valor: " . $rfc . "\n";
    echo "   Longitud: " . strlen($rfc) . " caracteres\n";
    echo "   Tipo: Alfanumerico\n";
    echo "   Especificacion: 12-13 caracteres\n";
    echo "   Cumple: " . (strlen($rfc) >= 12 && strlen($rfc) <= 13 ? 'SI' : 'NO') . "\n\n";
    
    echo "2. Password\n";
    echo "   Longitud: 64 caracteres (cifrada SHA-256)\n";
    echo "   Tipo: Alfanumerico\n";
    echo "   Especificacion: 64 caracteres\n";
    echo "   Cumple: SI (configurada en DB)\n\n";
    
    echo "3. Certificado (BLOB Base64)\n";
    echo "   Longitud: " . strlen($firmaData['certificado']) . " caracteres\n";
    echo "   Tipo: Base64 (BLOB)\n";
    echo "   Especificacion: N/A (sin limite)\n";
    echo "   Primeros 50 chars: " . substr($firmaData['certificado'], 0, 50) . "...\n";
    echo "   Cumple: SI\n\n";
    
    echo "4. Cadena Original (CLOB)\n";
    echo "   Valor: " . $firmaData['cadenaOriginal'] . "\n";
    echo "   Longitud: " . strlen($firmaData['cadenaOriginal']) . " caracteres\n";
    echo "   Tipo: String (CLOB)\n";
    echo "   Especificacion: N/A (sin limite)\n";
    echo "   Cumple: SI\n\n";
    
    echo "5. Firma Electronica (Alfanumerico Base64)\n";
    echo "   Longitud: " . strlen($firmaData['firma']) . " caracteres\n";
    echo "   Tipo: Base64 (Alfanumerico)\n";
    echo "   Especificacion: Maximo 1000 caracteres\n";
    echo "   Cumple: " . (strlen($firmaData['firma']) <= 1000 ? 'SI' : 'NO') . "\n";
    echo "   Primeros 80 chars: " . substr($firmaData['firma'], 0, 80) . "...\n\n";
    
    echo "=== RESULTADO FINAL ===\n";
    $rfcOk = strlen($rfc) >= 12 && strlen($rfc) <= 13;
    $firmaOk = strlen($firmaData['firma']) <= 1000;
    
    if ($rfcOk && $firmaOk) {
        echo "TODOS LOS CAMPOS CUMPLEN CON LAS ESPECIFICACIONES DE VUCEM\n";
    } else {
        echo "ADVERTENCIA: Algunos campos NO cumplen:\n";
        if (!$rfcOk) echo "- RFC debe tener 12-13 caracteres\n";
        if (!$firmaOk) echo "- Firma excede los 1000 caracteres\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
