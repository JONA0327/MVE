<?php

use App\Http\Controllers\ManifestationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AcuseController;
use Illuminate\Support\Facades\Route;
use App\Models\Manifestation;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\CoveController; 


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
    Route::get('/cove/consultar', [CoveController::class, 'showByFolio'])
        ->name('cove.consultar');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // --- PARSER EME ---
    Route::post('/manifestacion/parse-eme', [ManifestationController::class, 'parseEme'])->name('manifestations.parseEme');
    
    // --- BUSCAR IMPORTADOR POR RFC ---
    Route::post('/manifestacion/buscar-importador', [ManifestationController::class, 'buscarImportadorPorRfc'])->name('manifestations.buscarImportador');
    Route::post('/manifestacion/buscar-rfc-consulta', [ManifestationController::class, 'buscarRfcConsulta'])->name('manifestations.buscarRfcConsulta');
    
    // --- TIPOS DE CAMBIO ---
    Route::get('/manifestacion/tipo-cambio', [ManifestationController::class, 'getExchangeRate'])->name('manifestations.exchangeRate');
    
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

    // Paso 1: Editar
    Route::get('/manifestacion/{uuid}/paso-1', [ManifestationController::class, 'editStep1'])->name('manifestations.step1');
    Route::put('/manifestacion/{uuid}/paso-1', [ManifestationController::class, 'updateStep1'])->name('manifestations.updateStep1');

    // Paso 2
    Route::get('/manifestacion/{uuid}/paso-2', [ManifestationController::class, 'editStep2'])->name('manifestations.step2');
    Route::put('/manifestacion/{uuid}/paso-2', [ManifestationController::class, 'updateStep2'])->name('manifestations.updateStep2');
    
    // CONVERTIDOR Y VERIFICADOR PDF
    Route::post('/convertidor/pdf', [\App\Http\Controllers\Convertidor\PdfConverterController::class, 'convert'])->name('pdf.convert');
    Route::post('/verificador/pdf', [\App\Http\Controllers\Convertidor\PdfConverterController::class, 'verify'])->name('pdf.verify');

    // Paso 3
    Route::get('/manifestacion/{uuid}/paso-3', [ManifestationController::class, 'editStep3'])->name('manifestations.step3');
    Route::put('/manifestacion/{uuid}/paso-3', [ManifestationController::class, 'updateStep3'])->name('manifestations.updateStep3');

    // Paso 4: Solo Archivos
    Route::get('/manifestacion/{uuid}/paso-4', [ManifestationController::class, 'editStep4'])->name('manifestations.step4');
    Route::post('/manifestacion/{uuid}/upload', [ManifestationController::class, 'uploadFile'])->name('manifestations.upload');
    Route::delete('/manifestacion/{uuid}/attachment/{id}', [ManifestationController::class, 'deleteAttachment'])->name('manifestations.deleteAttachment');
    Route::get('/manifestacion/{uuid}/attachment/{id}/view', [ManifestationController::class, 'viewAttachment'])->name('manifestations.viewAttachment');
    
    // Paso 5: Resumen y Firma (NUEVO)
    Route::get('/manifestacion/{uuid}/resumen', [ManifestationController::class, 'summary'])->name('manifestations.summary');
    Route::post('/manifestacion/{uuid}/firmar', [ManifestationController::class, 'signManifestation'])->name('manifestations.sign');
    
    // Finales
    Route::get('/manifestacion/{uuid}/acuse', [ManifestationController::class, 'downloadAcuse'])->name('manifestations.downloadAcuse');
    Route::delete('/manifestacion/{uuid}', [ManifestationController::class, 'destroy'])->name('manifestations.destroy');
});

// --- ACUSES VUCEM (eDocument y COVE) ---
Route::middleware('auth')->group(function () {
    // Descargar acuse (detecta automáticamente si es eDocument o COVE según el formato del folio)
    Route::get('/acuses/{folio}', [AcuseController::class, 'descargarAcuse'])->name('acuses.descargar');
    
    // Listar acuses en cache (opcional, para debug)
    Route::get('/acuses', [AcuseController::class, 'listarAcuses'])->name('acuses.listar');
});

require __DIR__.'/auth.php';