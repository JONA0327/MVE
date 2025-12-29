<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Previsualización y Validación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
             <!-- STEPPER VISUAL -->
             <div class="mb-10">
                 <div class="flex items-center justify-between w-full opacity-90">
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-blue-900">PASO 1</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-blue-900">PASO 2</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">PASO 3</div></div>
                </div>
            </div>

            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300">
                
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300">
                    <h1 class="text-lg font-bold text-slate-900 uppercase">3. Verificación y Firma</h1>
                    <p class="text-xs text-slate-500">Revise toda la información y proceda con la firma de su manifestación de valor.</p>
                </div>

                <div class="p-10">
                    <!-- VISTA PRELIMINAR (SOLO LECTURA) -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-slate-700 mb-6 border-b-2 border-slate-300 pb-2">Vista Preliminar de la Manifestación</h2>
                        
                        <!-- INFORMACIÓN BÁSICA -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-slate-50 p-4 rounded">
                                <label class="text-xs font-bold text-slate-500 uppercase">RFC Solicitante</label>
                                <p class="text-sm text-slate-800 font-mono">{{ $manifestation->rfc_solicitante }}</p>
                            </div>
                            <div class="bg-slate-50 p-4 rounded">
                                <label class="text-xs font-bold text-slate-500 uppercase">RFC Importador</label>
                                <p class="text-sm text-slate-800 font-mono">{{ $manifestation->rfc_importador }}</p>
                            </div>
                            <div class="bg-slate-50 p-4 rounded">
                                <label class="text-xs font-bold text-slate-500 uppercase">Valor en Aduana</label>
                                <p class="text-sm text-slate-800 font-bold">${{ number_format($manifestation->total_valor_aduana, 2) }}</p>
                            </div>
                        </div>

                        <!-- PEDIMENTOS -->
                        @if($manifestation->pedimentos->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-slate-700 mb-3">Pedimentos Asociados</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm bg-white rounded border">
                                    <thead class="bg-slate-100">
                                        <tr>
                                            <th class="p-2 text-left font-bold text-xs">Número</th>
                                            <th class="p-2 text-left font-bold text-xs">Patente</th>
                                            <th class="p-2 text-left font-bold text-xs">Aduana</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($manifestation->pedimentos as $pedimento)
                                        <tr class="border-t">
                                            <td class="p-2 font-mono">{{ $pedimento->numero_pedimento }}</td>
                                            <td class="p-2">{{ $pedimento->patente }}</td>
                                            <td class="p-2">{{ $pedimento->aduana_clave }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- COVEs -->
                        @if($manifestation->coves->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-slate-700 mb-3">COVEs</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($manifestation->coves as $cove)
                                <div class="bg-white border rounded p-3">
                                    <p class="text-xs text-slate-500">eDocument</p>
                                    <p class="text-sm font-mono text-slate-800">{{ $cove->edocument }}</p>
                                    <p class="text-xs text-slate-500 mt-2">Método: {{ $cove->metodo_valoracion }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- RFCs DE CONSULTA -->
                        @if($manifestation->consultationRfcs->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-slate-700 mb-3">RFCs Autorizados para Consulta</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($manifestation->consultationRfcs as $rfc)
                                <div class="bg-white border rounded p-3">
                                    <p class="text-xs text-slate-500">RFC</p>
                                    <p class="text-sm font-mono text-slate-800">{{ $rfc->rfc_consulta }}</p>
                                    @if($rfc->razon_social)
                                    <p class="text-xs text-slate-500 mt-1">{{ $rfc->razon_social }}</p>
                                    @endif
                                    @if($rfc->tipo_figura)
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mt-1">{{ $rfc->tipo_figura }}</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- FORMULARIO DE FIRMA -->
                    <form method="POST" action="{{ route('manifestations.sign', $manifestation->uuid) }}" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 p-4 rounded border-l-4 border-red-500 text-red-700 text-sm font-bold">
                                Por favor revise los campos obligatorios marcados.
                            </div>
                        @endif

                        <!-- DECLARACIÓN ART. 81 -->
                        <div class="mb-10">
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm leading-5 font-medium text-yellow-800">
                                            Documentación Soporte (Art. 81 RLA)
                                        </h3>
                                        <div class="mt-2 text-sm leading-5 text-yellow-700">
                                            <label class="inline-flex items-start cursor-pointer">
                                                <input type="checkbox" required class="mt-1 form-checkbox h-4 w-4 text-yellow-600 transition duration-150 ease-in-out border-yellow-300 focus:ring-yellow-500">
                                                <span class="ml-2">
                                                    Manifiesto bajo protesta de decir verdad que cuento con el <strong>Documento Principal (Factura Comercial)</strong> y demás documentación anexa requerida por el Artículo 81 del Reglamento de la Ley Aduanera para sustentar el valor declarado.
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN DE FIRMA ELECTRÓNICA -->
                        <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded">
                            <h3 class="text-md font-bold text-yellow-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Firma Electrónica (e.firma)
                            </h3>
                            <p class="text-xs text-yellow-700 mb-4">
                                Para enviar la manifestación a VUCEM, debe firmarla electrónicamente con su certificado .cer y llave privada .key del SAT.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-yellow-700 uppercase mb-1">Certificado (.cer)</label>
                                    <input name="cer_file" type="file" accept=".cer" required class="w-full text-xs border border-yellow-300 rounded-sm focus:ring-yellow-500 focus:border-yellow-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-yellow-700 uppercase mb-1">Llave privada (.key)</label>
                                    <input name="key_file" type="file" accept=".key" required class="w-full text-xs border border-yellow-300 rounded-sm focus:ring-yellow-500 focus:border-yellow-500">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-yellow-700 uppercase mb-1">Contraseña de la llave privada</label>
                                <input name="password" type="password" required placeholder="Ingrese la contraseña de su llave privada" class="w-full md:w-1/2 text-xs border border-yellow-300 rounded-sm focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                        </div>

                        <!-- VISTA PREVIA FORMATO VUCEM (TEMPORAL - SOLO PRUEBA) -->
                        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-6 mb-6" x-data="{ showPreview: false }">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <h3 class="text-lg font-bold text-yellow-800">Vista Previa Formato VUCEM</h3>
                                    <span class="ml-3 px-2 py-1 bg-red-500 text-white text-xs font-bold rounded">TEMPORAL - SOLO PRUEBA</span>
                                </div>
                                <button type="button" @click="showPreview = !showPreview" class="text-yellow-600 hover:text-yellow-800 font-bold">
                                    <span x-text="showPreview ? 'Ocultar' : 'Mostrar'"></span>
                                </button>
                            </div>
                            
                            <div x-show="showPreview" x-collapse>
                                <div class="bg-white p-4 rounded border border-yellow-300">
                                    <p class="text-xs text-slate-600 mb-2 font-semibold">Cadena generada (formato pipe-separated):</p>
                                    <div class="bg-slate-900 text-green-400 p-4 rounded font-mono text-xs overflow-x-auto whitespace-pre-wrap break-all">
                                        @php
                                            $cadena = '|';
                                            $cadena .= ($manifestation->rfc_importador ?? 'N/A') . '|';
                                            if($manifestation->consultationRfcs->count() > 0) {
                                                $cadena .= ($manifestation->consultationRfcs[0]->rfc_consulta ?? '') . '|';
                                                $cadena .= ($manifestation->consultationRfcs[0]->tipo_figura ?? 'TIPFIG.OTR') . '|';
                                            } else {
                                                $cadena .= '|TIPFIG.OTR|';
                                            }
                                            if($manifestation->pedimentos->count() > 0) {
                                                $cadena .= ($manifestation->pedimentos[0]->numero_pedimento ?? '') . '|';
                                            } else {
                                                $cadena .= '|';
                                            }
                                            if($manifestation->coves->count() > 0) {
                                                $cadena .= ($manifestation->coves[0]->edocument ?? '') . '|';
                                            } else {
                                                $cadena .= '|';
                                            }
                                            $cadena .= ($manifestation->incoterm ?? 'TIPINC.FOB') . '|';
                                            $cadena .= ($manifestation->existe_vinculacion ? '1' : '0') . '|';
                                            if($manifestation->pedimentos->count() > 0) {
                                                $cadena .= ($manifestation->pedimentos[0]->patente ?? '') . '|';
                                                $cadena .= ($manifestation->pedimentos[0]->aduana_clave ?? '') . '|';
                                            } else {
                                                $cadena .= '||';
                                            }
                                            if($manifestation->payments->where('tipo_pago', 'precio_pagado')->count() > 0) {
                                                $pago = $manifestation->payments->where('tipo_pago', 'precio_pagado')->first();
                                                $cadena .= ($pago->fecha ?? '') . '|';
                                                $cadena .= ($pago->importe ?? '0') . '|';
                                                $cadena .= ($pago->forma_pago ?? 'FORPAG.EF') . '|';
                                                if($pago->forma_pago === 'FORPAG.OT') {
                                                    $cadena .= ($pago->especifique ?? '') . '|';
                                                }
                                                $cadena .= ($pago->moneda ?? 'USD') . '|';
                                                $cadena .= ($pago->tipo_cambio ?? '1') . '|';
                                            } else {
                                                $cadena .= '||FORPAG.EF||USD|1|';
                                            }
                                            $cadena .= ($manifestation->metodo_valoracion ?? 'VALADU.VTM') . '|';
                                        @endphp
                                        {{ $cadena }}
                                    </div>
                                    
                                    <div class="mt-4 grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs font-bold text-slate-700 mb-2">Campos detectados:</p>
                                            <ul class="text-xs space-y-1">
                                                <li>✓ RFC Importador: <span class="font-mono">{{ $manifestation->rfc_importador ?? 'N/A' }}</span></li>
                                                <li>✓ RFCs Consulta: <span class="font-mono">{{ $manifestation->consultationRfcs->count() }}</span></li>
                                                <li>✓ COVEs: <span class="font-mono">{{ $manifestation->coves->count() }}</span></li>
                                                <li>✓ Pedimentos: <span class="font-mono">{{ $manifestation->pedimentos->count() }}</span></li>
                                                <li>✓ INCOTERM: <span class="font-mono">{{ $manifestation->incoterm ?? 'N/A' }}</span></li>
                                                <li>✓ Método: <span class="font-mono">{{ $manifestation->metodo_valoracion ?? 'N/A' }}</span></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-700 mb-2">Ajustes:</p>
                                            <ul class="text-xs space-y-1">
                                                <li>✓ Incrementables: <span class="font-mono">{{ $manifestation->incrementables->count() }}</span></li>
                                                <li>✓ Decrementables: <span class="font-mono">{{ $manifestation->decrementables->count() }}</span></li>
                                                <li>✓ Pagos Pagados: <span class="font-mono">{{ $manifestation->payments->where('tipo_pago', 'precio_pagado')->count() }}</span></li>
                                                <li>✓ Pagos Por Pagar: <span class="font-mono">{{ $manifestation->payments->where('tipo_pago', 'precio_por_pagar')->count() }}</span></li>
                                                <li>✓ Compensaciones: <span class="font-mono">{{ $manifestation->compensations->count() }}</span></li>
                                                <li>✓ Vinculación: <span class="font-mono">{{ $manifestation->existe_vinculacion ? 'SÍ' : 'NO' }}</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN VISTA PREVIA -->

                        <!-- BOTONES DE NAVEGACIÓN -->
                        <div class="flex justify-between items-center mt-10 pt-6 border-t border-slate-200">
                            <a href="{{ route('manifestations.step2', $manifestation->uuid) }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-sm font-bold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 hover:text-slate-900 transition">
                                &larr; Anterior
                            </a>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-green-700 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-green-600 shadow-md transform hover:-translate-y-0.5 transition">
                                Firmar y Enviar &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Script simplificado para paso 3 (solo previsualización y firma)
        console.log('Step 3 - Preview and Sign loaded');
    </script>
</x-app-layout>