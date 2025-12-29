<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Importamos los controladores necesarios
use App\Http\Controllers\ManifestationController;
use App\Http\Controllers\MFileCoveController;
use App\Http\Controllers\Api\CoveController; 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ruta de prueba para verificar que el archivo API carga (Pruébala en: /api/test)
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando correctamente']);
});

// Ruta del usuario
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Grupo de rutas protegidas
Route::middleware(['auth:sanctum'])->group(function () {

    // --- RUTA QUE TE DABA PROBLEMAS (VUCEM) ---
    // La definimos explícitamente aquí al principio del grupo
    Route::get('/cove/consultar', [CoveController::class, 'showByFolio'])
        ->name('api.cove.consultar');

    // --- MANIFESTACIONES ---
    Route::post('/manifestations/step1', [ManifestationController::class, 'storeStep1']);
    Route::put('/manifestations/{uuid}/step2', [ManifestationController::class, 'updateStep2']);
    Route::put('/manifestations/{uuid}/step3', [ManifestationController::class, 'updateStep3']);
    Route::post('/manifestations/{uuid}/upload', [ManifestationController::class, 'uploadFile']);
    Route::post('/manifestations/{uuid}/sign', [ManifestationController::class, 'signManifestation']);
    Route::get('/manifestations/{uuid}', [ManifestationController::class, 'show']);

    // --- ARCHIVOS M ---
    Route::post('/mfile/procesar-cove', [MFileCoveController::class, 'procesarArchivo']);
    Route::get('/mfile/ejemplo-cove', [MFileCoveController::class, 'ejemploUso']);
});