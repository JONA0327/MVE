<?php

use App\Http\Controllers\ManifestationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Manifestation;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

// --- DASHBOARD: LISTADO DE MANIFESTACIONES ---
Route::get('/dashboard', function () {
    // Obtener manifestaciones del usuario actual
    $userRfc = Auth::user()->rfc; 
    
    $manifestations = Manifestation::where('rfc_solicitante', $userRfc)
                        ->orWhere('rfc_importador', $userRfc)
                        ->orderBy('updated_at', 'desc')
                        ->get();

    return view('dashboard', compact('manifestations'));
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- MANIFESTACIÓN DE VALOR (CRUD) ---

    // 1. Crear / Paso 1
    Route::get('/manifestacion/nueva', [ManifestationController::class, 'createStep1'])->name('manifestations.create');
    Route::post('/manifestacion/nueva', [ManifestationController::class, 'storeStep1'])->name('manifestations.store');

    // 2. Paso 2 (Valores)
    Route::get('/manifestacion/{uuid}/paso-2', [ManifestationController::class, 'editStep2'])->name('manifestations.step2');
    Route::put('/manifestacion/{uuid}/paso-2', [ManifestationController::class, 'updateStep2'])->name('manifestations.updateStep2');

    // 3. Paso 3 (Detalles)
    Route::get('/manifestacion/{uuid}/paso-3', [ManifestationController::class, 'editStep3'])->name('manifestations.step3');
    Route::put('/manifestacion/{uuid}/paso-3', [ManifestationController::class, 'updateStep3'])->name('manifestations.updateStep3');

    // 4. Paso 4 (Archivos y Firma)
    Route::get('/manifestacion/{uuid}/paso-4', [ManifestationController::class, 'editStep4'])->name('manifestations.step4');
    Route::post('/manifestacion/{uuid}/upload', [ManifestationController::class, 'uploadFile'])->name('manifestations.upload');
    Route::post('/manifestacion/{uuid}/firmar', [ManifestationController::class, 'signManifestation'])->name('manifestations.sign');
    
    // Descargas
    Route::get('/manifestacion/{uuid}/acuse', [ManifestationController::class, 'downloadAcuse'])->name('manifestations.downloadAcuse');

    // Eliminar
    Route::delete('/manifestacion/{uuid}', [ManifestationController::class, 'destroy'])->name('manifestations.destroy');
});

require __DIR__.'/auth.php';