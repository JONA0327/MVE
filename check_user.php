<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::first();

if (!$user) {
    echo "No hay usuarios\n";
    exit(1);
}

echo "RFC: " . ($user->rfc ?? 'NULL') . "\n";
echo "Nombre: " . ($user->name ?? 'NULL') . "\n";
echo "webservice_user: " . ($user->webservice_user ?? 'NULL') . "\n";
echo "webservice_key: " . (empty($user->webservice_key) ? 'NULL' : 'SET (' . strlen($user->webservice_key) . ' chars)') . "\n";

try {
    $decrypted = $user->getDecryptedWebserviceKey();
    echo "Decrypted webservice_key: " . (empty($decrypted) ? 'NULL' : 'SET (' . strlen($decrypted) . ' chars)') . "\n";
} catch (Exception $e) {
    echo "Error decrypting: " . $e->getMessage() . "\n";
}
