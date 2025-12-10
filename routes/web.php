<?php

use App\Http\Controllers\ManifestationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;
use App\Models\Manifestation;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// --- DASHBOARD ---
Route::get('/dashboard', function () {
    $userRfc = Auth::user()->rfc; 
    
    $manifestations = Manifestation::where('rfc_solicitante', $userRfc)
                        ->orWhere('rfc_importador', $userRfc)
                        ->orderBy('updated_at', 'desc')
                        ->get();

    return view('dashboard', compact('manifestations'));
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- ADMIN ---
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });

    // --- MANIFESTACIÓN DE VALOR ---

    // Paso 1: Crear (Nuevo)
    Route::get('/manifestacion/nueva', [ManifestationController::class, 'createStep1'])->name('manifestations.create');
    Route::post('/manifestacion/nueva', [ManifestationController::class, 'storeStep1'])->name('manifestations.store');

    // Paso 1: Editar (Regresar desde el paso 2)
    Route::get('/manifestacion/{uuid}/paso-1', [ManifestationController::class, 'editStep1'])->name('manifestations.step1');
    Route::put('/manifestacion/{uuid}/paso-1', [ManifestationController::class, 'updateStep1'])->name('manifestations.updateStep1');

    // Paso 2
    Route::get('/manifestacion/{uuid}/paso-2', [ManifestationController::class, 'editStep2'])->name('manifestations.step2');
    Route::put('/manifestacion/{uuid}/paso-2', [ManifestationController::class, 'updateStep2'])->name('manifestations.updateStep2');

    // Paso 3
    Route::get('/manifestacion/{uuid}/paso-3', [ManifestationController::class, 'editStep3'])->name('manifestations.step3');
    Route::put('/manifestacion/{uuid}/paso-3', [ManifestationController::class, 'updateStep3'])->name('manifestations.updateStep3');

    // Paso 4
    Route::get('/manifestacion/{uuid}/paso-4', [ManifestationController::class, 'editStep4'])->name('manifestations.step4');
    Route::post('/manifestacion/{uuid}/upload', [ManifestationController::class, 'uploadFile'])->name('manifestations.upload');
    Route::post('/manifestacion/{uuid}/firmar', [ManifestationController::class, 'signManifestation'])->name('manifestations.sign');
    
    // Finales
    Route::get('/manifestacion/{uuid}/acuse', [ManifestationController::class, 'downloadAcuse'])->name('manifestations.downloadAcuse');
    Route::delete('/manifestacion/{uuid}', [ManifestationController::class, 'destroy'])->name('manifestations.destroy');
});

require __DIR__.'/auth.php';