<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Vista Preliminar de la Manifestación de Valor
            </h2>
            <!-- Botón de Imprimir -->
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 print:hidden">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Borrador
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <!-- STEPPER VISUAL (Oculto al imprimir) -->
            <div class="mb-10 print:hidden">
                <div class="flex items-center justify-between w-full opacity-75">
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Generales</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Valores</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Detalles</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Archivos</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">Resumen</div></div>
                </div>
            </div>

            <!-- DOCUMENTO DE VISTA PRELIMINAR -->
            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300 print:shadow-none print:border-none">
                
                <!-- ENCABEZADO DEL DOCUMENTO -->
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300 flex justify-between items-start print:bg-white print:border-b-2 print:border-black">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 uppercase">Manifestación de Valor</h1>
                        <p class="text-sm text-slate-500">Borrador Informativo - Sin Validez Oficial</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-500">FOLIO INTERNO</p>
                        <p class="text-lg font-mono font-bold text-slate-800">{{ substr($manifestation->uuid, 0, 8) }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                
                <div class="p-10 space-y-8 text-sm text-slate-700 print:p-0 print:pt-6">
                    
                    <!-- 1. DATOS DEL SOLICITANTE -->
                    <section class="break-inside-avoid">
                        <div class="flex justify-between items-end border-b-2 border-blue-900 mb-4 pb-1 print:border-black">
                            <h3 class="text-xs font-bold text-blue-900 uppercase print:text-black">1. Datos del Solicitante (Razón Social)</h3>
                            <a href="{{ route('manifestations.step1', $manifestation->uuid) }}" class="text-xs text-blue-600 hover:underline print:hidden flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Nombre o Razón Social</label>
                                <p class="font-bold text-base">{{ $manifestation->nombre }} {{ $manifestation->apellido_paterno }} {{ $manifestation->apellido_materno }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">RFC / CURP</label>
                                <p>{{ $manifestation->rfc_solicitante }} / {{ $manifestation->curp_solicitante }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Domicilio Fiscal (Simulado SAT)</label>
                                <p class="uppercase">AV. REFORMA 123, PISO 5, COL. JUÁREZ, CUAUHTÉMOC, CIUDAD DE MÉXICO, C.P. 06600, MÉXICO.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Correo Electrónico</label>
                                <p>{{ Auth::user()->email ?? 'contacto@empresa.com' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Teléfono</label>
                                <p>55 1234 5678</p>
                            </div>
                        </div>
                    </section>

                    <!-- 2. VINCULACIÓN -->
                    <section class="break-inside-avoid">
                        <div class="flex justify-between items-end border-b-2 border-blue-900 mb-4 pb-1 print:border-black">
                            <h3 class="text-xs font-bold text-blue-900 uppercase print:text-black">2. Vinculación</h3>
                            <a href="{{ route('manifestations.step3', $manifestation->uuid) }}" class="text-xs text-blue-600 hover:underline print:hidden flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex justify-between border-b border-slate-100 pb-2 print:border-slate-300">
                                <span>¿Existe vinculación entre importador y vendedor?</span>
                                <span class="font-bold">{{ $manifestation->existe_vinculacion ? 'SÍ' : 'NO' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-2 print:border-slate-300">
                                <span>¿Influyó en el valor de la transacción?</span>
                                <span class="font-bold">{{ $manifestation->existe_vinculacion ? 'NO' : 'N/A' }}</span>
                            </div>
                        </div>
                    </section>

                    <!-- 3. DATOS DEL IMPORTADOR -->
                    <section class="break-inside-avoid">
                        <div class="flex justify-between items-end border-b-2 border-blue-900 mb-4 pb-1 print:border-black">
                            <h3 class="text-xs font-bold text-blue-900 uppercase print:text-black">3. Datos del Importador</h3>
                            <a href="{{ route('manifestations.step1', $manifestation->uuid) }}" class="text-xs text-blue-600 hover:underline print:hidden flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Nombre o Razón Social</label>
                                <p class="font-bold uppercase">{{ $manifestation->razon_social_importador }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">RFC (con Homoclave)</label>
                                <p class="uppercase">{{ $manifestation->rfc_importador }}</p>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Domicilio</label>
                                <p class="uppercase">CALLE INDUSTRIAL 45, PARQUE INDUSTRIAL FINS, EL MARQUÉS, QUERÉTARO, C.P. 76246.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">RNC / Tax ID</label>
                                <p>{{ $manifestation->registro_nacional_contribuyentes }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Teléfono</label>
                                <p>442 987 6543</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Correo Electrónico</label>
                                <p>importaciones@proveedor.com</p>
                            </div>
                        </div>
                    </section>

                    <!-- 4. AGENTE O APODERADO ADUANAL -->
                    <section class="break-inside-avoid">
                        <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-4 pb-1 print:text-black print:border-black">4. Agente o Apoderado Aduanal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded print:bg-white print:border print:border-slate-300">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Nombre Completo</label>
                                <p class="uppercase">JUAN PÉREZ LÓPEZ</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 print:text-black">Número de Patente o Autorización</label>
                                <p class="font-mono">1624</p>
                            </div>
                        </div>
                    </section>

                    <!-- 5. FACTURAS -->
                    <section class="break-inside-avoid">
                        <div class="flex justify-between items-end border-b-2 border-blue-900 mb-4 pb-1 print:border-black">
                            <h3 class="text-xs font-bold text-blue-900 uppercase print:text-black">5. Datos de Factura(s)</h3>
                            <a href="{{ route('manifestations.step2', $manifestation->uuid) }}" class="text-xs text-blue-600 hover:underline print:hidden flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar
                            </a>
                        </div>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-slate-500 border-b border-slate-200 print:text-black">
                                    <th class="py-2">Número de Factura</th>
                                    <th class="py-2">Fecha</th>
                                    <th class="py-2">Proveedor/Emisor</th>
                                    <th class="py-2 text-right">COVE (e-Document)</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs uppercase">
                                @forelse($manifestation->coves as $cove)
                                    <tr class="border-b border-slate-100 print:border-slate-300">
                                        <td class="py-2 font-bold">{{ $cove->numero_factura }}</td>
                                        <td class="py-2">{{ \Carbon\Carbon::parse($cove->fecha_expedicion)->format('d/m/Y') }}</td>
                                        <td class="py-2">{{ $cove->emisor }}</td>
                                        <td class="py-2 text-right font-mono">{{ $cove->edocument }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-2 text-center text-slate-400">Sin facturas registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </section>

                    <!-- 6. MÉTODO DE VALORACIÓN -->
                    <section class="break-inside-avoid">
                        <div class="flex justify-between items-end border-b-2 border-blue-900 mb-4 pb-1 print:border-black">
                            <h3 class="text-xs font-bold text-blue-900 uppercase print:text-black">6. Método de Valoración</h3>
                            <a href="{{ route('manifestations.step3', $manifestation->uuid) }}" class="text-xs text-blue-600 hover:underline print:hidden flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar
                            </a>
                        </div>
                        <div class="bg-blue-50 p-4 rounded flex items-center print:bg-white print:border print:border-slate-300">
                            <svg class="w-5 h-5 text-blue-800 mr-2 print:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span class="font-bold text-blue-900 uppercase print:text-black">
                                @switch($manifestation->metodo_valoracion_global)
                                    @case(1) Método de Valor de Transacción de las Mercancías @break
                                    @case(2) Método de Valor de Transacción de Mercancías Idénticas @break
                                    @case(3) Método de Valor de Transacción de Mercancías Similares @break
                                    @case(4) Método de Precio Unitario de Venta @break
                                    @case(5) Método de Valor Reconstruido @break
                                    @case(6) Método de Último Recurso @break
                                    @default No especificado
                                @endswitch
                            </span>
                        </div>
                    </section>

                    <!-- 7. VALOR DE TRANSACCIÓN -->
                    <section class="break-inside-avoid">
                        <div class="flex justify-between items-end border-b-2 border-blue-900 mb-4 pb-1 print:border-black">
                            <h3 class="text-xs font-bold text-blue-900 uppercase print:text-black">7. Valor de Transacción de las Mercancías</h3>
                            <a href="{{ route('manifestations.step2', $manifestation->uuid) }}" class="text-xs text-blue-600 hover:underline print:hidden flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar
                            </a>
                        </div>
                        <div class="bg-slate-50 p-6 rounded-lg border border-slate-200 print:bg-white print:border-slate-400">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-slate-500 print:text-black">Precio Pagado (Numérico)</p>
                                    <p class="text-2xl font-mono font-bold text-slate-800 print:text-black">$ {{ number_format($manifestation->total_precio_pagado, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-500 print:text-black">Moneda de Facturación</p>
                                    <p class="text-xl font-bold text-slate-800 print:text-black">MXN / USD</p>
                                </div>
                            </div>
                            <div class="border-t border-slate-200 pt-4 print:border-slate-400">
                                <p class="text-xs text-slate-500 uppercase mb-1 print:text-black">Cantidad en Letra (Precio Pagado)</p>
                                <p class="font-bold text-slate-700 uppercase italic print:text-black">
                                    @php
                                        $total = $manifestation->total_precio_pagado;
                                        if (class_exists('NumberFormatter')) {
                                            $f = new NumberFormatter("es", NumberFormatter::SPELLOUT);
                                            echo strtoupper($f->format($total)) . " PESOS 00/100 M.N.";
                                        } else {
                                            echo "$ " . number_format($total, 2) . " M.N.";
                                        }
                                    @endphp
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- 8. ANEXOS -->
                    <section class="break-inside-avoid">
                        <div class="flex justify-between items-end border-b-2 border-blue-900 mb-4 pb-1 print:border-black">
                            <h3 class="text-xs font-bold text-blue-900 uppercase print:text-black">8. Anexos</h3>
                            <a href="{{ route('manifestations.step4', $manifestation->uuid) }}" class="text-xs text-blue-600 hover:underline print:hidden flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Editar
                            </a>
                        </div>
                        <ul class="list-disc list-inside space-y-1">
                            @forelse($manifestation->attachments as $att)
                                <li>
                                    <span class="font-bold">{{ $att->tipo_documento }}</span>: 
                                    <span class="text-slate-500 italic print:text-black">{{ $att->file_name }}</span>
                                    @if($att->descripcion_complementaria)
                                        <span class="text-xs bg-slate-100 px-1 rounded print:bg-white print:border print:border-slate-300">({{ $att->descripcion_complementaria }})</span>
                                    @endif
                                </li>
                            @empty
                                <li class="text-slate-400 italic">No se adjuntaron documentos.</li>
                            @endforelse
                        </ul>
                    </section>

                </div>
            </div>

            <!-- ÁREA DE FIRMA CON MODAL (Oculto al imprimir) -->
            <!-- Inicializamos AlpineData aquí para el modal -->
            <div class="bg-slate-900 overflow-hidden shadow-2xl sm:rounded-lg mb-12 relative border border-slate-800 print:hidden" x-data="{ showConfirmModal: false, acceptedTerms: false }">
                <div class="p-8">
                    <h3 class="text-xl font-bold text-white mb-2 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Firmado Electrónico (e.firma)
                    </h3>
                    <p class="text-slate-400 text-sm mb-6">He revisado la vista preliminar y confirmo que los datos son correctos. Procederé a firmar para enviar a VUCEM.</p>
                    
                    <form x-ref="signForm" action="{{ route('manifestations.sign', $manifestation->uuid) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        @if ($errors->any())
                            <div class="mb-6 bg-red-900/50 border-l-4 border-red-500 p-4 rounded text-white font-bold text-sm">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2 required">Certificado (.cer)</label>
                                <input type="file" name="cer_file" accept=".cer" required class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 bg-slate-800 rounded border border-slate-700 p-1">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-300 mb-2 required">Clave Privada (.key)</label>
                                <input type="file" name="key_file" accept=".key" required class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 bg-slate-800 rounded border border-slate-700 p-1">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-300 mb-2 required">Contraseña de Clave Privada</label>
                                <input type="password" name="password" required class="block w-full bg-slate-800 border-slate-700 rounded-md p-3 text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" placeholder="••••••••">
                            </div>
                        </div>

                        <!-- Checkbox Legal -->
                        <div class="mb-6 p-4 bg-slate-800 rounded border border-slate-700">
                            <label class="flex items-start">
                                <input type="checkbox" x-model="acceptedTerms" class="mt-1 rounded bg-slate-700 border-slate-600 text-green-500 shadow-sm focus:ring-green-500 focus:ring-offset-slate-900">
                                <span class="ml-3 text-sm text-slate-300">
                                    Declaro bajo protesta de decir verdad que los datos asentados en la presente manifestación de valor son ciertos y coinciden con la documentación soporte.
                                </span>
                            </label>
                        </div>

                        <div class="flex justify-between items-center pt-6 border-t border-slate-700">
                             <a href="{{ route('manifestations.step4', $manifestation->uuid) }}" class="text-slate-400 hover:text-white text-sm font-medium transition flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Volver a Archivos
                            </a>
                            
                            <!-- Botón que activa el modal, deshabilitado si no aceptan términos -->
                            <button type="button" 
                                    @click="showConfirmModal = true" 
                                    :disabled="!acceptedTerms"
                                    :class="!acceptedTerms ? 'opacity-50 cursor-not-allowed bg-slate-600' : 'bg-green-600 hover:bg-green-500 active:bg-green-700 shadow-xl'"
                                    class="inline-flex items-center px-8 py-4 border border-transparent rounded-md font-bold text-white text-lg uppercase tracking-widest transition transform hover:-translate-y-0.5 focus:ring-4 focus:ring-green-500/50">
                                Firmar y Terminar
                            </button>
                        </div>
                    </form>

                    <!-- MODAL DE CONFIRMACIÓN -->
                    <div x-show="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         style="display: none;">
                        
                        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full overflow-hidden" @click.away="showConfirmModal = false">
                            <div class="p-6 text-center">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">¿Está seguro de enviar la manifestación?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">
                                        Esta acción generará el sello digital y enviará el documento a VUCEM. <strong>No podrá realizar cambios posteriores</strong>.
                                    </p>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <!-- Botón Real de Envío -->
                                <button type="button" @click="$refs.signForm.submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Sí, Firmar y Enviar
                                </button>
                                <button type="button" @click="showConfirmModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Revisar de nuevo
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>