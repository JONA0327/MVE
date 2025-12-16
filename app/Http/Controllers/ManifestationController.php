<?php

namespace App\Http\Controllers;

use App\Models\Manifestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ManifestationController extends Controller
{
    /**
     * Helper para cargar catálogos desde JSON
     */
    private function getCurrencies()
    {
        $path = resource_path('data/currencies.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        // Fallback por si el archivo no existe
        return [
            ['code' => 'MXN', 'name' => 'Peso Mexicano'],
            ['code' => 'USD', 'name' => 'Dólar Americano'],
            ['code' => 'EUR', 'name' => 'Euro'],
        ];
    }

    /**
     * Helper para cargar catálogos generales (Formas pago, Incoterms, etc.)
     */
    private function getCatalogs()
    {
        $path = resource_path('data/catalogs.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return [];
    }

    public function createStep1()
    {
        return view('manifestations.step1');
    }

    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'curp_solicitante' => 'required|string|size:18',
            'rfc_solicitante' => 'required|string|between:12,13',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255', 
            'rfc_importador' => 'required|string|between:12,13',
            'razon_social_importador' => 'required|string',
            'registro_nacional_contribuyentes' => 'required|string', 
        ]);

        $manifestation = Manifestation::create($validated);

        return redirect()->route('manifestations.step2', $manifestation->uuid)
            ->with('status', 'Borrador iniciado. Continúe con los valores.');
    }

    public function editStep1($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
        return view('manifestations.step1', compact('manifestation'));
    }

    public function updateStep1(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
        $validated = $request->validate([
            'curp_solicitante' => 'required|string|size:18',
            'rfc_solicitante' => 'required|string|between:12,13',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'rfc_importador' => 'required|string|between:12,13',
            'razon_social_importador' => 'required|string',
            'registro_nacional_contribuyentes' => 'required|string', 
        ]);
        $manifestation->update($validated);
        return redirect()->route('manifestations.step2', $manifestation->uuid)
            ->with('status', 'Datos generales actualizados.');
    }

    /**
     * PASO 2: VISTA (Con Monedas y Catálogos)
     */
    public function editStep2($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->with('coves')->firstOrFail();
        $currencies = $this->getCurrencies(); // Cargar catálogo monedas
        $catalogs = $this->getCatalogs();     // Cargar resto de catálogos
        return view('manifestations.step2', compact('manifestation', 'currencies', 'catalogs'));
    }

    public function updateStep2(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'total_valor_aduana' => 'required|numeric',
            'coves' => 'array',
        ]);

        DB::transaction(function () use ($manifestation, $request) {
            $manifestation->update($request->only([
                'total_precio_pagado', 'total_incrementables',
                'total_decrementables', 'total_valor_aduana',
                'total_precio_por_pagar'
            ]));

            if ($request->has('coves')) {
                $manifestation->coves()->delete();
                $coves = collect($request->input('coves'))
                    ->filter(fn($c) => !empty($c['edocument']))
                    ->values();
                
                if($coves->isNotEmpty()) {
                    $manifestation->coves()->createMany($coves);
                }
            }
        });

        return redirect()->route('manifestations.step3', $uuid);
    }

    /**
     * PASO 3: VISTA (Con Monedas y Catálogos Completos)
     */
    public function editStep3($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)
            ->with(['pedimentos', 'payments', 'compensations', 'consultationRfcs'])
            ->firstOrFail();
        
        $incrementables = $manifestation->adjustments()->where('type', 'incrementable')->get();
        $decrementables = $manifestation->adjustments()->where('type', 'decrementable')->get();
        
        $currencies = $this->getCurrencies(); // Cargar catálogo monedas
        $catalogs = $this->getCatalogs();     // Cargar resto de catálogos

        return view('manifestations.step3', compact('manifestation', 'incrementables', 'decrementables', 'currencies', 'catalogs'));
    }

    public function updateStep3(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'metodo_valoracion_global' => 'required',
            'incoterm' => 'required',
        ]);

        DB::transaction(function () use ($manifestation, $request) {
            
            // Procesar incoterm: extraer solo el código (máx 3 caracteres)
            $incotermValue = trim($request->input('incoterm', ''));
            if (strpos($incotermValue, '.') !== false) {
                $parts = explode('.', $incotermValue);
                $incotermValue = strtoupper(substr(trim($parts[1]), 0, 3));
            } else {
                $incotermValue = strtoupper(substr($incotermValue, 0, 3));
            }
            
            $manifestation->update([
                'metodo_valoracion_global' => $request->input('metodo_valoracion_global'),
                'incoterm' => $incotermValue,
                'existe_vinculacion' => $request->input('existe_vinculacion'),
            ]);

            // Pedimentos
            if ($request->has('pedimentos')) {
                $manifestation->pedimentos()->delete();
                $peds = collect($request->input('pedimentos'))
                    ->filter(fn($p) => !empty($p['numero_pedimento']))
                    ->values()
                    ->toArray();
                if(count($peds) > 0) $manifestation->pedimentos()->createMany($peds);
            }

            // Ajustes
            $manifestation->adjustments()->delete();
            $adjustments = [];
            foreach ($request->input('incrementables', []) as $inc) {
                if(!empty($inc['concepto']) && !empty($inc['importe'])) { 
                    $inc['type'] = 'incrementable'; 
                    $adjustments[] = $inc; 
                }
            }
            foreach ($request->input('decrementables', []) as $dec) {
                if(!empty($dec['concepto']) && !empty($dec['importe'])) { 
                    $dec['type'] = 'decrementable'; 
                    $adjustments[] = $dec; 
                }
            }
            if (count($adjustments) > 0) {
                $manifestation->adjustments()->createMany($adjustments);
            }

            // Pagos
            if ($request->has('pagos')) {
                $manifestation->payments()->delete();
                $pagos = collect($request->input('pagos'))
                    ->filter(fn($p) => !empty($p['importe']))
                    ->values()
                    ->toArray();
                if(count($pagos) > 0) $manifestation->payments()->createMany($pagos);
            }
            
            // Compensaciones
            if ($request->has('compensaciones')) {
                $manifestation->compensations()->delete();
                $compensaciones = collect($request->input('compensaciones'))
                    ->filter(fn($c) => !empty($c['fecha']) && !empty($c['motivo']))
                    ->values()
                    ->toArray();
                if(count($compensaciones) > 0) $manifestation->compensations()->createMany($compensaciones);
            }

            // RFCs de Consulta
            if ($request->has('consultation_rfcs')) {
                $manifestation->consultationRfcs()->delete();
                $rfcs = collect($request->input('consultation_rfcs'))
                    ->filter(fn($r) => !empty($r['rfc_consulta']))
                    ->map(function ($r) {
                        return ['rfc_consulta' => strtoupper($r['rfc_consulta'])];
                    })
                    ->values()
                    ->toArray();
                if(count($rfcs) > 0) $manifestation->consultationRfcs()->createMany($rfcs);
            }
        });

        return redirect()->route('manifestations.step4', $uuid);
    }
    
    public function editStep4($uuid) { $manifestation = Manifestation::where('uuid', $uuid)->with('attachments')->firstOrFail(); return view('manifestations.step4', compact('manifestation')); }
    public function uploadFile(Request $request, $uuid) { $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); $request->validate(['file' => 'required|file|max:3072']); $file = $request->file('file'); $path = $file->storeAs('manifestations/' . $uuid, 'anexo_' . time() . '.' . $file->getClientOriginalExtension()); $att = $manifestation->attachments()->create([ 'tipo_documento' => $request->tipo_documento, 'descripcion_complementaria' => $request->descripcion_complementaria, 'file_path' => $path, 'file_name' => $file->getClientOriginalName(), 'file_size' => $file->getSize(), 'mime_type' => $file->getMimeType(), ]); return response()->json(['success' => true, 'id' => $att->id]); }
    public function summary($uuid) { $manifestation = Manifestation::where('uuid', $uuid)->with(['coves', 'pedimentos', 'adjustments', 'payments', 'compensations', 'attachments', 'consultationRfcs'])->firstOrFail(); return view('manifestations.summary', compact('manifestation')); }
    public function signManifestation(Request $request, $uuid) { $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); $request->validate([ 'cer_file' => 'required|file', 'key_file' => 'required|file', 'password' => 'required|string', ]); try { $pathAcuse = 'manifestations/' . $uuid . '/SAT_ACUSE_' . time() . '.pdf'; $pathDetalle = 'manifestations/' . $uuid . '/SAT_DETALLE_' . time() . '.pdf'; Storage::put($pathAcuse, '%PDF-1.4 ... (Contenido Simulado Acuse) ...'); Storage::put($pathDetalle, '%PDF-1.4 ... (Contenido Simulado Detalle) ...'); $manifestation->update([ 'status' => 'signed', 'sello_digital' => 'SELLO_SAT_SIMULADO_XYZ_' . time(), 'cadena_original' => '||CADENA|ORIGINAL|SAT||', 'path_acuse_manifestacion' => $pathAcuse, 'path_detalle_manifestacion' => $pathDetalle, ]); return redirect()->route('dashboard')->with('status', 'Manifestación enviada y firmada correctamente. Acuses recibidos.'); } catch (Exception $e) { return back()->withErrors(['password' => 'Error: ' . $e->getMessage()]); } }
    public function downloadAcuse($uuid) { $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); if (!$manifestation->path_acuse_manifestacion || !Storage::exists($manifestation->path_acuse_manifestacion)) { $data = [ 'm' => $manifestation, 'fecha_impresion' => now()->format('d/m/Y H:i:s'), 'folio_sat' => 'M-' . substr($manifestation->uuid, 0, 8), ]; $pdf = Pdf::loadView('manifestations.pdf.acuse', $data); return $pdf->download('Acuse_' . $manifestation->uuid . '.pdf'); } return Storage::download($manifestation->path_acuse_manifestacion, 'Acuse_SAT_' . $manifestation->uuid . '.pdf'); }
    public function destroy($uuid) { $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); if ($manifestation->status === 'signed') { return back()->with('error', 'No es posible eliminar una manifestación firmada.'); } Storage::deleteDirectory('manifestations/' . $uuid); $manifestation->delete(); return redirect()->route('dashboard')->with('status', 'Borrador eliminado correctamente.'); }
}