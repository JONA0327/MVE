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
     * PASO 1: VISTA (CREAR)
     */
    public function createStep1()
    {
        return view('manifestations.step1');
    }

    /**
     * PASO 1: GUARDAR
     */
    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'curp_solicitante' => 'required|string|size:18',
            'rfc_solicitante' => 'required|string|size:13',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255', 
            
            'rfc_importador' => 'required|string|size:13',
            'razon_social_importador' => 'required|string',
            'registro_nacional_contribuyentes' => 'required|string', 
        ]);

        $manifestation = Manifestation::create($validated);

        return redirect()->route('manifestations.step2', $manifestation->uuid)
            ->with('status', 'Borrador iniciado. Continúe con los valores.');
    }

    /**
     * PASO 1: EDITAR
     */
    public function editStep1($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
        return view('manifestations.step1', compact('manifestation'));
    }

    /**
     * PASO 1: ACTUALIZAR
     */
    public function updateStep1(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'curp_solicitante' => 'required|string|size:18',
            'rfc_solicitante' => 'required|string|size:13',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'rfc_importador' => 'required|string|size:13',
            'razon_social_importador' => 'required|string',
            'registro_nacional_contribuyentes' => 'required|string', 
        ]);

        $manifestation->update($validated);

        return redirect()->route('manifestations.step2', $manifestation->uuid)
            ->with('status', 'Datos generales actualizados.');
    }

    /**
     * PASO 2: VISTA
     */
    public function editStep2($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->with('coves')->firstOrFail();
        return view('manifestations.step2', compact('manifestation'));
    }

    /**
     * PASO 2: GUARDAR
     */
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
     * PASO 3: VISTA
     */
    public function editStep3($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)
            ->with(['pedimentos', 'payments', 'compensations'])
            ->firstOrFail();
        
        $incrementables = $manifestation->adjustments()->where('type', 'incrementable')->get();
        $decrementables = $manifestation->adjustments()->where('type', 'decrementable')->get();

        return view('manifestations.step3', compact('manifestation', 'incrementables', 'decrementables'));
    }

    /**
     * PASO 3: GUARDAR
     */
    public function updateStep3(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'metodo_valoracion_global' => 'required',
            'incoterm' => 'required',
        ]);

        DB::transaction(function () use ($manifestation, $request) {
            
            $manifestation->update($request->only(['metodo_valoracion_global', 'incoterm', 'existe_vinculacion']));

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
            
            // Compensaciones (si aplica)
            if ($request->has('compensaciones')) {
                $manifestation->compensations()->delete();
                $manifestation->compensations()->createMany($request->input('compensaciones'));
            }
        });

        return redirect()->route('manifestations.step4', $uuid);
    }

    /**
     * PASO 4: VISTA (Archivos)
     */
    public function editStep4($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->with('attachments')->firstOrFail();
        return view('manifestations.step4', compact('manifestation'));
    }

    /**
     * SUBIR ARCHIVO (AJAX)
     */
    public function uploadFile(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
        $request->validate(['file' => 'required|file|max:3072']);

        $file = $request->file('file');
        $path = $file->storeAs('manifestations/' . $uuid, 'anexo_' . time() . '.' . $file->getClientOriginalExtension());

        $att = $manifestation->attachments()->create([
            'tipo_documento' => $request->tipo_documento,
            'descripcion_complementaria' => $request->descripcion_complementaria,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json(['success' => true, 'id' => $att->id]);
    }

    /**
     * PASO 5: RESUMEN Y FIRMA (NUEVO MÉTODO)
     */
    public function summary($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)
            ->with(['coves', 'pedimentos', 'adjustments', 'payments', 'compensations', 'attachments'])
            ->firstOrFail();

        return view('manifestations.summary', compact('manifestation'));
    }

    /**
     * FIRMA (Simulación SAT)
     */
    public function signManifestation(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
        
        $request->validate([
            'cer_file' => 'required|file',
            'key_file' => 'required|file',
            'password' => 'required|string',
        ]);

        try {
            // Simulación SAT
            $pathAcuse = 'manifestations/' . $uuid . '/SAT_ACUSE_' . time() . '.pdf';
            $pathDetalle = 'manifestations/' . $uuid . '/SAT_DETALLE_' . time() . '.pdf';

            // En producción: Generar PDFs reales aquí
            Storage::put($pathAcuse, '%PDF-1.4 ... (Contenido Simulado Acuse) ...');
            Storage::put($pathDetalle, '%PDF-1.4 ... (Contenido Simulado Detalle) ...');

            $manifestation->update([
                'status' => 'signed',
                'sello_digital' => 'SELLO_SAT_SIMULADO_XYZ_' . time(),
                'cadena_original' => '||CADENA|ORIGINAL|SAT||',
                'path_acuse_manifestacion' => $pathAcuse,
                'path_detalle_manifestacion' => $pathDetalle,
            ]);

            return redirect()->route('dashboard')
                ->with('status', 'Manifestación enviada y firmada correctamente. Acuses recibidos.');

        } catch (Exception $e) {
            return back()->withErrors(['password' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * DESCARGAR ACUSE
     */
    public function downloadAcuse($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();

        if (!$manifestation->path_acuse_manifestacion || !Storage::exists($manifestation->path_acuse_manifestacion)) {
             $data = [
                'm' => $manifestation,
                'fecha_impresion' => now()->format('d/m/Y H:i:s'),
                'folio_sat' => 'M-' . substr($manifestation->uuid, 0, 8),
            ];
            $pdf = Pdf::loadView('manifestations.pdf.acuse', $data);
            return $pdf->download('Acuse_' . $manifestation->uuid . '.pdf');
        }

        return Storage::download($manifestation->path_acuse_manifestacion, 'Acuse_SAT_' . $manifestation->uuid . '.pdf');
    }

    /**
     * ELIMINAR BORRADOR
     */
    public function destroy($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();

        if ($manifestation->status === 'signed') {
            return back()->with('error', 'No es posible eliminar una manifestación firmada.');
        }

        Storage::deleteDirectory('manifestations/' . $uuid);
        $manifestation->delete();

        return redirect()->route('dashboard')
            ->with('status', 'Borrador eliminado correctamente.');
    }
}