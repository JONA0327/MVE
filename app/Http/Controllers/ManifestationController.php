<?php

namespace App\Http\Controllers;

use App\Models\Manifestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\MFileParserService;
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
        $catalogs = [];
        
        if (file_exists($path)) {
            $catalogs = json_decode(file_get_contents($path), true);
        }
        
        // Cargar el catálogo de aduanas por separado
        $aduanasPath = resource_path('data/aduanas.json');
        if (file_exists($aduanasPath)) {
            $catalogs['aduanas'] = json_decode(file_get_contents($aduanasPath), true);
        }
        
        return $catalogs;
    }

    public function createStep1()
    {
        $user = auth()->user();
        $userData = [
            'rfc' => $user->rfc,
            'razon_social' => $user->razon_social,
            'actividad_economica' => $user->actividad_economica,
            'pais' => $user->pais,
            'codigo_postal' => $user->codigo_postal,
            'estado' => $user->estado,
            'municipio' => $user->municipio,
            'localidad' => $user->localidad,
            'colonia' => $user->colonia,
            'calle' => $user->calle,
            'numero_exterior' => $user->numero_exterior,
            'numero_interior' => $user->numero_interior,
            'lada' => $user->lada,
            'telefono' => $user->telefono,
            'email' => $user->email,
        ];
        
        $catalogs = $this->getCatalogs();
        $currencies = $this->getCurrencies(); // Cargar catálogo monedas
        return view('manifestations.step1', compact('userData', 'catalogs', 'currencies'));
    }

    public function storeStep1(Request $request)
    {
        // Filter out empty consultation_rfcs (solo requiere RFC)
        if ($request->has('consultation_rfcs') && is_array($request->consultation_rfcs)) {
            $filtered_rfcs = [];
            foreach ($request->consultation_rfcs as $rfc) {
                // Solo requerir RFC, permitir que razón social y tipo de figura sean opcionales
                if (isset($rfc['rfc_consulta']) && !empty(trim($rfc['rfc_consulta']))) {
                    $filtered_rfcs[] = $rfc;
                }
            }
            
            if (empty($filtered_rfcs)) {
                $request->request->remove('consultation_rfcs');
            } else {
                $request->merge(['consultation_rfcs' => $filtered_rfcs]);
            }
        }
        
        // Debug: Log what we're validating
        \Log::info('Consultation RFCs being validated:', $request->consultation_rfcs ?? []);

        $validated = $request->validate([
            // Campos del solicitante
            'rfc_solicitante' => 'required|string|between:12,13',
            'razon_social_solicitante' => 'required|string|max:255',
            'actividad_economica_solicitante' => 'required|string|max:500',
            'pais_solicitante' => 'required|string|max:100',
            'codigo_postal_solicitante' => 'required|string|max:10',
            'estado_solicitante' => 'required|string|max:100',
            'municipio_solicitante' => 'required|string|max:100',
            'localidad_solicitante' => 'nullable|string|max:100',
            'colonia_solicitante' => 'required|string|max:100',
            'calle_solicitante' => 'required|string|max:255',
            'numero_exterior_solicitante' => 'required|string|max:20',
            'numero_interior_solicitante' => 'nullable|string|max:20',
            'lada_solicitante' => 'required|string|max:5',
            'telefono_solicitante' => 'required|string|max:20',
            'correo_solicitante' => 'required|email',
            // Campos del importador
            'rfc_importador' => 'required|string|between:12,13',
            'razon_social_importador' => 'required|string',
            'registro_nacional_contribuyentes' => 'required|string',
            'domicilio_fiscal_importador' => 'nullable|string',
        ]);
        
        // Validar que el RFC del importador sea el mismo que el del solicitante
        if ($validated['rfc_importador'] !== $validated['rfc_solicitante']) {
            return back()->withErrors([
                'rfc_importador' => 'El RFC del importador debe ser el mismo que el del solicitante.'
            ])->withInput();
        }
        
        // Validate consultation_rfcs separately if present and not empty
        if ($request->has('consultation_rfcs') && !empty(array_filter($request->consultation_rfcs ?? [], function($rfc) {
            return !empty($rfc['rfc_consulta']);
        }))) {
            $request->validate([
                'consultation_rfcs' => 'array',
                'consultation_rfcs.*.rfc_consulta' => 'required|string|between:12,13',
                'consultation_rfcs.*.razon_social' => 'nullable|string',
                'consultation_rfcs.*.tipo_figura' => 'nullable|string|in:Agencia Aduanal,Agente aduanal,Otro,Representante Legal',
            ]);
        }

        // Buscar o crear importador
        $importador = \App\Models\Importador::firstOrCreate(
            ['rfc' => strtoupper($validated['rfc_importador'])],
            [
                'razon_social' => $validated['razon_social_importador'],
                'registro_nacional_contribuyentes' => $validated['registro_nacional_contribuyentes'],
                'domicilio_fiscal' => $validated['domicilio_fiscal_importador'] ?? null,
            ]
        );

        // Si el importador ya existe, actualizar datos si son diferentes
        if (!$importador->wasRecentlyCreated) {
            $importador->updateIfNewer([
                'razon_social' => $validated['razon_social_importador'],
                'registro_nacional_contribuyentes' => $validated['registro_nacional_contribuyentes'],
                'domicilio_fiscal' => $validated['domicilio_fiscal_importador'] ?? null,
            ]);
        }

        // Crear manifestación con relación al importador
        $validated['importador_id'] = $importador->id;
        $manifestation = Manifestation::create($validated);
        
        // Guardar RFCs de consulta
        if (!empty($validated['consultation_rfcs'])) {
            foreach ($validated['consultation_rfcs'] as $rfc) {
                $manifestation->consultationRfcs()->create($rfc);
            }
        }

        return redirect()->route('manifestations.step2', $manifestation->uuid)
            ->with('status', 'Borrador iniciado. Continúe con los valores.');
    }

    public function editStep1($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)
            ->with([
                'importador', 
                'coves', 
                'pedimentos', 
                'adjustments',
                'payments', 
                'compensations', 
                'consultationRfcs'
            ])
            ->firstOrFail();
        
        // Separar incrementables y decrementables
        $incrementables = $manifestation->adjustments->where('type', 'incrementable');
        $decrementables = $manifestation->adjustments->where('type', 'decrementable');
        
        $user = auth()->user();
        $userData = [
            'rfc' => $user->rfc,
            'razon_social' => $user->razon_social,
            'actividad_economica' => $user->actividad_economica,
            'pais' => $user->pais,
            'codigo_postal' => $user->codigo_postal,
            'estado' => $user->estado,
            'municipio' => $user->municipio,
            'localidad' => $user->localidad,
            'colonia' => $user->colonia,
            'calle' => $user->calle,
            'numero_exterior' => $user->numero_exterior,
            'numero_interior' => $user->numero_interior,
            'lada' => $user->lada,
            'telefono' => $user->telefono,
            'email' => $user->email,
        ];
        
        $catalogs = $this->getCatalogs();
        $currencies = $this->getCurrencies(); // Cargar catálogo monedas
        return view('manifestations.step1', compact('manifestation', 'userData', 'incrementables', 'decrementables', 'catalogs', 'currencies'));
    }

    public function updateStep1(Request $request, $uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
        
        // Filter out empty consultation_rfcs (solo requiere RFC)
        if ($request->has('consultation_rfcs') && is_array($request->consultation_rfcs)) {
            $filtered_rfcs = [];
            foreach ($request->consultation_rfcs as $rfc) {
                // Solo requerir RFC, permitir que razón social y tipo de figura sean opcionales
                if (isset($rfc['rfc_consulta']) && !empty(trim($rfc['rfc_consulta']))) {
                    $filtered_rfcs[] = $rfc;
                }
            }
            
            if (empty($filtered_rfcs)) {
                $request->request->remove('consultation_rfcs');
            } else {
                $request->merge(['consultation_rfcs' => $filtered_rfcs]);
            }
        }

        $validated = $request->validate([
            // Campos del solicitante
            'rfc_solicitante' => 'required|string|between:12,13',
            'razon_social_solicitante' => 'required|string|max:255',
            'actividad_economica_solicitante' => 'required|string|max:500',
            'pais_solicitante' => 'required|string|max:100',
            'codigo_postal_solicitante' => 'required|string|max:10',
            'estado_solicitante' => 'required|string|max:100',
            'municipio_solicitante' => 'required|string|max:100',
            'localidad_solicitante' => 'nullable|string|max:100',
            'colonia_solicitante' => 'required|string|max:100',
            'calle_solicitante' => 'required|string|max:255',
            'numero_exterior_solicitante' => 'required|string|max:20',
            'numero_interior_solicitante' => 'nullable|string|max:20',
            'lada_solicitante' => 'required|string|max:5',
            'telefono_solicitante' => 'required|string|max:20',
            'correo_solicitante' => 'required|email',
            // Campos del importador
            'rfc_importador' => 'required|string|between:12,13',
            'razon_social_importador' => 'required|string',
            'registro_nacional_contribuyentes' => 'required|string',
            'domicilio_fiscal_importador' => 'nullable|string',
            // Campos de valores aduanales
            'total_precio_pagado' => 'nullable|numeric|min:0',
            'moneda_precio_pagado' => 'nullable|string|size:3',
            'total_incrementables' => 'nullable|numeric|min:0',
            'moneda_incrementables' => 'nullable|string|size:3',
            'total_decrementables' => 'nullable|numeric|min:0',
            'moneda_decrementables' => 'nullable|string|size:3',
            'total_valor_aduana' => 'nullable|numeric|min:0',
            'total_precio_por_pagar' => 'nullable|numeric|min:0',
            'moneda_precio_por_pagar' => 'nullable|string|size:3',
            // Campos de la sección MV
            'metodo_valoracion_global' => 'nullable|string',
            'incoterm' => 'nullable|string|max:15',
            // COVEs
            'coves' => 'nullable|array',
            'coves.*.edocument' => 'nullable|string',
            'coves.*.metodo_valoracion' => 'nullable|string',
            'coves.*.numero_factura' => 'nullable|string',
            'coves.*.fecha_expedicion' => 'nullable|date',
            'coves.*.emisor' => 'nullable|string',
        ]);
        
        // Validar que el RFC del importador sea el mismo que el del solicitante
        if ($validated['rfc_importador'] !== $validated['rfc_solicitante']) {
            return back()->withErrors([
                'rfc_importador' => 'El RFC del importador debe ser el mismo que el del solicitante.'
            ])->withInput();
        }
        
        // Validate consultation_rfcs separately if present and not empty
        if ($request->has('consultation_rfcs') && !empty(array_filter($request->consultation_rfcs ?? [], function($rfc) {
            return !empty($rfc['rfc_consulta']);
        }))) {
            $request->validate([
                'consultation_rfcs' => 'array',
                'consultation_rfcs.*.rfc_consulta' => 'required|string|between:12,13',
                'consultation_rfcs.*.razon_social' => 'nullable|string',
                'consultation_rfcs.*.tipo_figura' => 'nullable|string|in:Agencia Aduanal,Agente aduanal,Otro,Representante Legal',
            ]);
        }

        // Buscar o crear importador
        $importador = \App\Models\Importador::firstOrCreate(
            ['rfc' => strtoupper($validated['rfc_importador'])],
            [
                'razon_social' => $validated['razon_social_importador'],
                'registro_nacional_contribuyentes' => $validated['registro_nacional_contribuyentes'],
                'domicilio_fiscal' => $validated['domicilio_fiscal_importador'] ?? null,
            ]
        );

        // Si el importador ya existe, actualizar datos si son diferentes
        if (!$importador->wasRecentlyCreated) {
            $importador->updateIfNewer([
                'razon_social' => $validated['razon_social_importador'],
                'registro_nacional_contribuyentes' => $validated['registro_nacional_contribuyentes'],
                'domicilio_fiscal' => $validated['domicilio_fiscal_importador'] ?? null,
            ]);
        }

        // Actualizar manifestación con relación al importador
        $validated['importador_id'] = $importador->id;
        
        // Asegurar que los campos de totales tengan valores por defecto
        $validated['total_incrementables'] = $validated['total_incrementables'] ?? 0;
        $validated['total_decrementables'] = $validated['total_decrementables'] ?? 0;
        $validated['total_precio_por_pagar'] = $validated['total_precio_por_pagar'] ?? 0;
        
        $manifestation->update($validated);
        
        // Actualizar RFCs de consulta
        $manifestation->consultationRfcs()->delete();
        if (!empty($validated['consultation_rfcs'])) {
            foreach ($validated['consultation_rfcs'] as $rfc) {
                $manifestation->consultationRfcs()->create($rfc);
            }
        }

        // Guardar todos los datos de la sección MV usando transacción
        DB::transaction(function () use ($manifestation, $request) {
            // Actualizar COVEs
            if ($request->has('coves')) {
                $manifestation->coves()->delete();
                $coves = collect($request->input('coves'))
                    ->filter(fn($c) => !empty($c['edocument']))
                    ->values();
                
                if($coves->isNotEmpty()) {
                    $manifestation->coves()->createMany($coves);
                }
            }

            // Actualizar Pedimentos
            if ($request->has('pedimentos')) {
                $manifestation->pedimentos()->delete();
                $pedimentos = collect($request->input('pedimentos'))
                    ->filter(fn($p) => !empty($p['numero_pedimento']))
                    ->values();
                
                if($pedimentos->isNotEmpty()) {
                    $manifestation->pedimentos()->createMany($pedimentos);
                }
            }

            // Actualizar Incrementables
            if ($request->has('incrementables')) {
                $manifestation->adjustments()->where('type', 'incrementable')->delete();
                $incrementables = collect($request->input('incrementables'))
                    ->filter(fn($i) => !empty($i['concepto']))
                    ->map(fn($i) => [...$i, 'type' => 'incrementable'])
                    ->values();
                
                if($incrementables->isNotEmpty()) {
                    $manifestation->adjustments()->createMany($incrementables);
                }
            }

            // Actualizar Decrementables
            if ($request->has('decrementables')) {
                $manifestation->adjustments()->where('type', 'decrementable')->delete();
                $decrementables = collect($request->input('decrementables'))
                    ->filter(fn($d) => !empty($d['concepto']))
                    ->map(fn($d) => [...$d, 'type' => 'decrementable'])
                    ->values();
                
                if($decrementables->isNotEmpty()) {
                    $manifestation->adjustments()->createMany($decrementables);
                }
            }

            // Actualizar Pagos (3 secciones: pagados, por pagar, compensaciones)
            $manifestation->payments()->delete();
            
            // Pagos Pagados
            if ($request->has('pagos_pagados')) {
                $pagosPagados = collect($request->input('pagos_pagados'))
                    ->filter(fn($p) => !empty($p['fecha']))
                    ->map(fn($p) => array_merge($p, ['status' => 'paid']))
                    ->values();
                
                if($pagosPagados->isNotEmpty()) {
                    $manifestation->payments()->createMany($pagosPagados);
                }
            }
            
            // Pagos Por Pagar
            if ($request->has('pagos_por_pagar')) {
                $pagosPorPagar = collect($request->input('pagos_por_pagar'))
                    ->filter(fn($p) => !empty($p['fecha']))
                    ->map(fn($p) => array_merge($p, ['status' => 'payable']))
                    ->values();
                
                if($pagosPorPagar->isNotEmpty()) {
                    $manifestation->payments()->createMany($pagosPorPagar);
                }
            }

            // Actualizar Compensaciones
            if ($request->has('compensaciones')) {
                $manifestation->compensations()->delete();
                $compensaciones = collect($request->input('compensaciones'))
                    ->filter(fn($c) => !empty($c['fecha']))
                    ->values();
                
                if($compensaciones->isNotEmpty()) {
                    $manifestation->compensations()->createMany($compensaciones);
                }
            }
        });
        
        return redirect()->route('manifestations.step2', $manifestation->uuid)
            ->with('status', 'Manifestación actualizada correctamente.');
    }

    /**
     * PASO 2: VISTA (Con Monedas y Catálogos)
     */
    public function editStep2($uuid)
    {
        $manifestation = Manifestation::where('uuid', $uuid)->with(['coves', 'attachments'])->firstOrFail();
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
            $data = $request->only([
                'total_precio_pagado', 'total_incrementables',
                'total_decrementables', 'total_valor_aduana',
                'total_precio_por_pagar'
            ]);
            
            // Asegurar que los campos de totales tengan valores por defecto
            $data['total_incrementables'] = $data['total_incrementables'] ?? 0;
            $data['total_decrementables'] = $data['total_decrementables'] ?? 0;
            $data['total_precio_por_pagar'] = $data['total_precio_por_pagar'] ?? 0;
            
            $manifestation->update($data);

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
            ->with(['coves', 'pedimentos', 'payments', 'compensations', 'consultationRfcs', 'adjustments', 'attachments'])
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

            // Pagos (3 secciones)
            $manifestation->payments()->delete();
            
            // Pagos Pagados
            if ($request->has('pagos_pagados')) {
                $pagosPagados = collect($request->input('pagos_pagados'))
                    ->filter(fn($p) => !empty($p['importe']))
                    ->map(fn($p) => array_merge($p, ['status' => 'paid']))
                    ->values()
                    ->toArray();
                if(count($pagosPagados) > 0) $manifestation->payments()->createMany($pagosPagados);
            }
            
            // Pagos Por Pagar
            if ($request->has('pagos_por_pagar')) {
                $pagosPorPagar = collect($request->input('pagos_por_pagar'))
                    ->filter(fn($p) => !empty($p['importe']))
                    ->map(fn($p) => array_merge($p, ['status' => 'payable']))
                    ->values()
                    ->toArray();
                if(count($pagosPorPagar) > 0) $manifestation->payments()->createMany($pagosPorPagar);
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
                        return [
                            'rfc_consulta' => strtoupper($r['rfc_consulta']),
                            'razon_social' => $r['razon_social'] ?? null,
                            'tipo_figura' => $r['tipo_figura'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();
                if(count($rfcs) > 0) $manifestation->consultationRfcs()->createMany($rfcs);
            }
        });

        return redirect()->route('manifestations.step4', $uuid);
    }

    /**
     * Buscar importador por RFC en tabla de importadores
     */
    public function buscarImportadorPorRfc(Request $request)
    {
        $request->validate([
            'rfc' => 'required|string|between:12,13'
        ]);

        $rfc = strtoupper(trim($request->input('rfc')));
        
        \Log::info('Buscando importador con RFC: ' . $rfc);

        try {
            // Buscar en la tabla de importadores
            $importador = \App\Models\Importador::where('rfc', $rfc)->first();

            if ($importador) {
                \Log::info('Importador encontrado: ' . $importador->razon_social);
                
                return response()->json([
                    'found' => true,
                    'data' => [
                        'id' => $importador->id,
                        'rfc' => $importador->rfc,
                        'razon_social' => $importador->razon_social,
                        'domicilio_fiscal' => $importador->domicilio_fiscal ?? '',
                        'registro_nacional' => $importador->registro_nacional_contribuyentes ?? $rfc,
                    ]
                ]);
            }

            \Log::info('Importador no encontrado en BD para RFC: ' . $rfc);
            
            return response()->json([
                'found' => false,
                'message' => 'RFC no encontrado en registros previos'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al buscar importador: ' . $e->getMessage());
            
            return response()->json([
                'found' => false,
                'error' => 'Error al buscar en la base de datos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar RFC en consultation_rfcs y importadores para razón social
     */
    public function buscarRfcConsulta(Request $request)
    {
        $request->validate([
            'rfc' => 'required|string|between:12,13'
        ]);

        $rfc = strtoupper(trim($request->input('rfc')));
        
        \Log::info('Buscando RFC de consulta: ' . $rfc);

        try {
            // Buscar primero en la tabla de importadores
            $importador = \App\Models\Importador::where('rfc', $rfc)->first();
            if ($importador) {
                \Log::info('RFC encontrado en importadores: ' . $importador->razon_social);
                
                return response()->json([
                    'found' => true,
                    'data' => [
                        'razon_social' => $importador->razon_social,
                        'source' => 'importadores'
                    ]
                ]);
            }
            
            // Buscar en consultation_rfcs de otras manifestaciones
            $consultationRfc = \App\Models\ConsultationRfc::where('rfc_consulta', $rfc)
                ->whereNotNull('razon_social')
                ->first();
                
            if ($consultationRfc) {
                \Log::info('RFC encontrado en consultation_rfcs: ' . $consultationRfc->razon_social);
                
                return response()->json([
                    'found' => true,
                    'data' => [
                        'razon_social' => $consultationRfc->razon_social,
                        'tipo_figura' => $consultationRfc->tipo_figura ?? null,
                        'source' => 'consultation_rfcs'
                    ]
                ]);
            }
            
            \Log::info('RFC de consulta no encontrado en BD: ' . $rfc);
            
            return response()->json([
                'found' => false,
                'message' => 'RFC no encontrado en registros previos'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al buscar RFC de consulta: ' . $e->getMessage());
            
            return response()->json([
                'found' => false,
                'error' => 'Error al buscar en la base de datos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parsea el archivo EME y extrae datos para precarga
     */
    public function parseEme(Request $request, MFileParserService $parser)
    {
        \Log::info('========================================');
        \Log::info('MÉTODO parseEme LLAMADO');
        \Log::info('Archivo recibido: ' . ($request->hasFile('eme_file') ? 'SI' : 'NO'));
        
        $request->validate([
            'eme_file' => 'required|file|mimes:txt,eme,325',
        ]);

        try {
            $file = $request->file('eme_file');
            \Log::info('Nombre del archivo: ' . $file->getClientOriginalName());
            \Log::info('Tamaño del archivo: ' . $file->getSize() . ' bytes');
            
            $content = file_get_contents($file->getRealPath());
            \Log::info('Contenido leído: ' . strlen($content) . ' caracteres');
            
            // Convertir a UTF-8 si es necesario
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1,UTF-8,ASCII');
            
            $data = $parser->parse($content);
            
            \Log::info('Datos parseados exitosamente');
            \Log::info('RFC Importador extraído: ' . ($data['rfc_importador'] ?? 'NULL'));
            \Log::info('Razón Social extraída: ' . ($data['razon_social_importador'] ?? 'NULL'));

            return response()->json([
                'success' => true,
                'data' => $data,
                'source' => 'eme'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al parsear EME: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo EME: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function editStep4($uuid) 
    { 
        $manifestation = Manifestation::where('uuid', $uuid)->with('attachments')->firstOrFail(); 
        return view('manifestations.step4', compact('manifestation')); 
    }
    
    public function uploadFile(Request $request, $uuid) 
    { 
        $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); 
        $request->validate(['file' => 'required|file|max:102400']); // Máx 100MB para input
        
        $file = $request->file('file');
        $converted = false;
        $originalSize = $file->getSize();
        $finalFilePath = null;
        
        // Si es PDF, verificar y convertir automáticamente si es necesario
        if ($file->getMimeType() === 'application/pdf') {
            try {
                $converter = app(\App\Services\VucemPdfConverter::class);
                
                // Crear directorio temp
                $tempDir = storage_path('app/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }
                
                $uniqueId = uniqid();
                $inputPath = $tempDir . DIRECTORY_SEPARATOR . $uniqueId . '_input.pdf';
                $outputPath = $tempDir . DIRECTORY_SEPARATOR . $uniqueId . '_output.pdf';
                
                // Copiar archivo a temp
                copy($file->getRealPath(), $inputPath);
                
                // Verificar PDF
                $isValid = $converter->verifyPdfCompliance($inputPath);
                
                if (!$isValid['valido']) {
                    // Convertir automáticamente al formato VUCEM
                    $converter->convertToVucem($inputPath, $outputPath);
                    
                    // Verificar que no exceda 3MB
                    if (filesize($outputPath) > 3 * 1024 * 1024) {
                        @unlink($inputPath);
                        @unlink($outputPath);
                        return response()->json([
                            'success' => false, 
                            'error' => 'El archivo convertido excede el límite de 3 MB de VUCEM. Por favor reduzca el número de páginas o la calidad de las imágenes del documento original.'
                        ], 422);
                    }
                    
                    // Usar el archivo convertido
                    $finalFilePath = $outputPath;
                    $converted = true;
                    
                    @unlink($inputPath);
                } else {
                    // Archivo válido, verificar tamaño antes de continuar
                    if (filesize($inputPath) > 3 * 1024 * 1024) {
                        @unlink($inputPath);
                        return response()->json([
                            'success' => false, 
                            'error' => 'El archivo PDF excede el límite de 3 MB de VUCEM. Por favor reduzca el tamaño del documento.'
                        ], 422);
                    }
                    $finalFilePath = $inputPath;
                }
                
            } catch (\Exception $e) {
                \Log::error('Error al procesar PDF: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error' => 'Error al procesar el archivo PDF: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Para archivos no-PDF (imágenes), validar tamaño directamente
            if ($originalSize > 3 * 1024 * 1024) {
                return response()->json([
                    'success' => false, 
                    'error' => 'El archivo excede el límite de 3 MB de VUCEM. Por favor reduzca el tamaño de la imagen.'
                ], 422);
            }
        }
        
        // Guardar archivo (original o convertido)
        $fileName = 'anexo_' . time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = 'manifestations/' . $uuid;
        
        if ($finalFilePath) {
            // Guardar desde archivo temporal (PDF procesado)
            $path = $destinationPath . '/' . $fileName;
            Storage::put($path, file_get_contents($finalFilePath));
            
            // Limpiar temporal
            @unlink($finalFilePath);
        } else {
            // Guardar archivo original (imágenes u otros)
            $path = $file->storeAs($destinationPath, $fileName);
        }
        
        $att = $manifestation->attachments()->create([
            'tipo_documento' => $request->tipo_documento,
            'descripcion_complementaria' => $request->descripcion_complementaria,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $finalFilePath ? filesize(storage_path('app/' . $path)) : $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
        
        return response()->json([
            'success' => true, 
            'id' => $att->id,
            'converted' => $converted,
            'message' => $converted ? 'Archivo convertido automáticamente al formato VUCEM (300 DPI, escala de grises)' : 'Archivo cargado correctamente'
        ]); 
    }
    
    /**
     * Eliminar un archivo adjunto
     */
    public function deleteAttachment($uuid, $id)
    {
        try {
            $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
            $attachment = $manifestation->attachments()->findOrFail($id);
            
            // Eliminar archivo físico
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
            
            // Eliminar registro de base de datos
            $attachment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Visualizar un archivo adjunto
     */
    public function viewAttachment($uuid, $id)
    {
        try {
            $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail();
            $attachment = $manifestation->attachments()->findOrFail($id);
            
            $fullPath = storage_path('app/' . $attachment->file_path);
            
            if (!file_exists($fullPath)) {
                \Log::error('Archivo no encontrado', [
                    'file_path' => $attachment->file_path,
                    'full_path' => $fullPath
                ]);
                abort(404, 'Archivo no encontrado');
            }
            
            $file = file_get_contents($fullPath);
            $mimeType = $attachment->mime_type ?? mime_content_type($fullPath);
            
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $attachment->file_name . '"');
        } catch (\Exception $e) {
            \Log::error('Error al visualizar archivo', [
                'error' => $e->getMessage(),
                'uuid' => $uuid,
                'id' => $id
            ]);
            abort(404, 'Error: ' . $e->getMessage());
        }
    }
    
    public function summary($uuid) { $manifestation = Manifestation::where('uuid', $uuid)->with(['coves', 'pedimentos', 'adjustments', 'payments', 'compensations', 'attachments', 'consultationRfcs'])->firstOrFail(); return view('manifestations.summary', compact('manifestation')); }
    public function signManifestation(Request $request, $uuid) { $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); $request->validate([ 'cer_file' => 'required|file', 'key_file' => 'required|file', 'password' => 'required|string', ]); try { $pathAcuse = 'manifestations/' . $uuid . '/SAT_ACUSE_' . time() . '.pdf'; $pathDetalle = 'manifestations/' . $uuid . '/SAT_DETALLE_' . time() . '.pdf'; Storage::put($pathAcuse, '%PDF-1.4 ... (Contenido Simulado Acuse) ...'); Storage::put($pathDetalle, '%PDF-1.4 ... (Contenido Simulado Detalle) ...'); $manifestation->update([ 'status' => 'signed', 'sello_digital' => 'SELLO_SAT_SIMULADO_XYZ_' . time(), 'cadena_original' => '||CADENA|ORIGINAL|SAT||', 'path_acuse_manifestacion' => $pathAcuse, 'path_detalle_manifestacion' => $pathDetalle, ]); return redirect()->route('dashboard')->with('status', 'Manifestación enviada y firmada correctamente. Acuses recibidos.'); } catch (Exception $e) { return back()->withErrors(['password' => 'Error: ' . $e->getMessage()]); } }
    public function downloadAcuse($uuid) { $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); if (!$manifestation->path_acuse_manifestacion || !Storage::exists($manifestation->path_acuse_manifestacion)) { $data = [ 'm' => $manifestation, 'fecha_impresion' => now()->format('d/m/Y H:i:s'), 'folio_sat' => 'M-' . substr($manifestation->uuid, 0, 8), ]; $pdf = Pdf::loadView('manifestations.pdf.acuse', $data); return $pdf->download('Acuse_' . $manifestation->uuid . '.pdf'); } return Storage::download($manifestation->path_acuse_manifestacion, 'Acuse_SAT_' . $manifestation->uuid . '.pdf'); }
    public function destroy($uuid) { $manifestation = Manifestation::where('uuid', $uuid)->firstOrFail(); if ($manifestation->status === 'signed') { return back()->with('error', 'No es posible eliminar una manifestación firmada.'); } Storage::deleteDirectory('manifestations/' . $uuid); $manifestation->delete(); return redirect()->route('dashboard')->with('status', 'Borrador eliminado correctamente.'); }

    /**
     * Obtener tipo de cambio de una fecha específica
     */
    public function getExchangeRate(Request $request)
    {
        $date = $request->input('date');
        $currency = $request->input('currency', 'USD');
        
        // Si la moneda es MXN, el tipo de cambio es 1
        if ($currency === 'MXN') {
            return response()->json(['rate' => 1.0000]);
        }
        
        try {
            // Simulamos obtener del SAT o servicio externo
            $rate = $this->getHistoricalRate($currency, $date);
            return response()->json(['rate' => $rate]);
        } catch (Exception $e) {
            // Tipo de cambio por defecto si no se puede obtener
            $defaultRates = [
                'USD' => 20.0000,
                'EUR' => 22.0000,
                'CAD' => 15.0000,
                'GBP' => 25.0000,
                'JPY' => 0.1500,
            ];
            
            $rate = $defaultRates[$currency] ?? 20.0000;
            return response()->json(['rate' => $rate]);
        }
    }
    
    /**
     * Obtener tipo de cambio histórico (simulado)
     */
    private function getHistoricalRate($currency, $date)
    {
        // Simulación de tipos de cambio históricos con variación
        $baseRates = [
            'USD' => 20.0000,
            'EUR' => 22.0000,
            'CAD' => 15.0000,
            'GBP' => 25.0000,
            'JPY' => 0.1500,
            'CHF' => 22.5000,
            'CNY' => 2.8000,
            'AUD' => 14.0000,
        ];
        
        $baseRate = $baseRates[$currency] ?? 20.0000;
        
        // Agregar variación basada en la fecha para simular fluctuaciones
        $dateObject = new \DateTime($date);
        $dayOfYear = $dateObject->format('z');
        $variation = sin($dayOfYear / 365 * 2 * M_PI) * 0.1; // Variación del ±10%
        
        return round($baseRate * (1 + $variation), 4);
    }
}
