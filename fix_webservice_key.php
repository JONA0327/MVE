<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

// Obtener usuario (buscar por cualquier campo o el primero con RFC)
$user = User::whereNotNull('rfc')->first();

if (!$user) {
    echo "❌ No hay usuarios con RFC configurado\n";
    exit(1);
}

// Verificar si el RFC desencriptado coincide
if ($user->rfc !== 'NET070608EM9') {
    echo "⚠️  El usuario encontrado tiene RFC diferente: {$user->rfc}\n";
    echo "¿Continuar de todos modos? (s/n): ";
    $cont = trim(fgets(STDIN));
    if (strtolower($cont) !== 's') {
        exit(0);
    }
}

echo "Usuario: {$user->name}\n";
echo "RFC: {$user->rfc}\n";

// Ver qué tiene actualmente
$current = $user->getDecryptedWebserviceKey();
echo "\n📋 Clave Webservice Actual:\n";
echo "Valor: {$current}\n";
echo "Longitud: " . strlen($current) . " caracteres\n";

// Detectar si es una ruta
if (strpos($current, '\\') !== false || strpos($current, '/') !== false) {
    echo "\n⚠️  DETECTADO: La clave actual parece ser una ruta de archivo!\n";
    echo "Esto está MAL. Debe ser la CLAVE WEBSERVICE de VUCEM.\n\n";
} else {
    echo "\n✅ La clave parece válida (no es una ruta).\n\n";
}

// Solicitar nueva clave
echo "───────────────────────────────────────────────────────\n";
echo "Para corregir, ingresa la CLAVE WEBSERVICE correcta\n";
echo "(la que obtienes en el portal VUCEM, NO la contraseña):\n";
echo "Presiona Enter sin escribir nada para cancelar.\n";
echo "───────────────────────────────────────────────────────\n";
echo "> ";

$nuevaClave = trim(fgets(STDIN));

if (empty($nuevaClave)) {
    echo "\n❌ Operación cancelada.\n";
    exit(0);
}

// Confirmar
echo "\n¿Confirmas actualizar la clave webservice? (s/n): ";
$confirma = trim(fgets(STDIN));

if (strtolower($confirma) !== 's') {
    echo "\n❌ Operación cancelada.\n";
    exit(0);
}

// Actualizar - NO usar Crypt::encrypt porque el mutator ya lo hace
$user->webservice_key = $nuevaClave; // El mutator setWebserviceKeyAttribute() ya encripta
$user->save();

echo "\n✅ Clave webservice actualizada correctamente.\n";
echo "Nueva longitud: " . strlen($nuevaClave) . " caracteres\n";
echo "\nPrueba nuevamente con:\n";
echo "php artisan vucem:test-edocument 043825149DMT6 --debug\n\n";
