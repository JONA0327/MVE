<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Ruta temporal para probar modal de error
Route::post('/test-error-modal', function (Request $request) {
    // Simular error 422 de RFC no coincide
    return response()->json([
        'success' => false,
        'error_type' => 'rfc_mismatch',
        'message' => 'El archivo EME que cargaste no coincide con el importador.',
        'rfc_eme' => 'ABC123456789',
        'rfc_solicitante' => 'NET070608EM9'
    ], 422);
})->name('test.error.modal');

// Ruta temporal para probar modal de no encontrar RFC
Route::post('/test-no-rfc', function (Request $request) {
    return response()->json([
        'success' => false,
        'error_type' => 'no_rfc_found',
        'message' => 'No se pudo extraer el RFC del importador del archivo EME'
    ], 422);
})->name('test.no.rfc');