<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManifestationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Ruta estándar para obtener info del usuario logueado (útil para el frontend)
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de la Manifestación de Valor (Protegidas)
Route::middleware(['auth:sanctum'])->group(function () {

    // --- PASO 1: Pre-registro / Borrador Inicial ---
    // Crea el registro maestro y devuelve el UUID
    Route::post('/manifestations/step1', [ManifestationController::class, 'storeStep1']);

    // --- PASO 2: Totales y COVEs ---
    // Actualiza montos y sincroniza la lista de COVEs
    Route::put('/manifestations/{uuid}/step2', [ManifestationController::class, 'updateStep2']);

    // --- PASO 3: Detalle Completo ---
    // Guarda Pedimentos, Incrementables, Decrementables, Pagos y Compensaciones
    Route::put('/manifestations/{uuid}/step3', [ManifestationController::class, 'updateStep3']);

    // --- PASO 3 (Extra): Carga de Archivos ---
    // Sube archivos individualmente (Anexos)
    Route::post('/manifestations/{uuid}/upload', [ManifestationController::class, 'uploadFile']);

    // --- PASO 4: Firmado Electrónico ---
    // Recibe .key, password y firma la cadena original
    Route::post('/manifestations/{uuid}/sign', [ManifestationController::class, 'signManifestation']);

    // Opcional: Ruta para ver el estado actual de una manifestación (para recargar la página)
    Route::get('/manifestations/{uuid}', [ManifestationController::class, 'show']);

    // --- PROCESAMIENTO DE ARCHIVOS M PARA COVE ---
    // Procesar archivo M y obtener COVEs
    Route::post('/mfile/procesar-cove', [App\Http\Controllers\MFileCoveController::class, 'procesarArchivo']);
    
    // Ejemplo de uso (para desarrollo/testing)
    Route::get('/mfile/ejemplo-cove', [App\Http\Controllers\MFileCoveController::class, 'ejemploUso']);
});