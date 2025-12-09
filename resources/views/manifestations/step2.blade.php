<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Valores de la Transacción
        </h2>
    </x-slot>

    <div class="py-12" x-data="covesHandler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- STEPPER E&I -->
            <div class="mb-10">
                 <div class="flex items-center justify-between w-full">
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-900 text-white font-bold text-sm shadow-sm">✓</div>
                        <div class="text-xs font-medium mt-2 text-blue-900 uppercase">Generales</div>
                    </div>
                    <div class="flex-auto border-t-4 border-blue-900 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-900 text-white font-bold shadow-lg ring-4 ring-blue-50 z-10 transform scale-110">2</div>
                        <div class="text-xs font-bold mt-2 text-blue-900 uppercase tracking-wide">Valores</div>
                    </div>
                    <div class="flex-auto border-t-4 border-gray-200 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4 opacity-40 grayscale">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold z-10">3</div>
                        <div class="text-xs font-medium mt-2 text-gray-500">Detalles</div>
                    </div>
                    <div class="flex-auto border-t-4 border-gray-200 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4 opacity-40 grayscale">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold z-10">4</div>
                        <div class="text-xs font-medium mt-2 text-gray-500">Firma</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-t-4 border-blue-900">
                <div class="p-8">

                    <form method="POST" action="{{ route('manifestations.updateStep2', $manifestation->uuid) }}">
                        @csrf
                        @method('PUT')

                        <!-- SECCIÓN COVES -->
                        <div class="flex justify-between items-end mb-6 border-b border-slate-100 pb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Acuses de Valor (COVE)</h3>
                                <p class="text-sm text-slate-500">Ingrese el eDocument para importar los datos oficiales.</p>
                            </div>
                            <div>
                                <button type="button" @click="addCove()" class="bg-white border border-slate-300 text-slate-700 text-xs px-4 py-2 rounded-md font-bold shadow-sm hover:bg-slate-50 hover:text-blue-900 transition flex items-center">
                                    <span class="text-xl mr-1 leading-none text-blue-600">+</span>
                                    Agregar Renglón
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tabla Data Grid -->
                        <div class="overflow-visible mb-10 shadow-sm rounded-lg border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/4">Acuse (eDocument)</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Método Val.</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"># Factura</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha Exp.</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Emisor</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <template x-for="(cove, index) in coves" :key="index">
                                        <tr class="hover:bg-slate-50 transition">
                                            <!-- Columna eDocument con Botón de Búsqueda -->
                                            <td class="p-3 align-top">
                                                <div class="flex rounded-md shadow-sm">
                                                    <input type="text" 
                                                           :name="`coves[${index}][edocument]`" 
                                                           x-model="cove.edocument" 
                                                           class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md text-xs border-slate-300 focus:ring-blue-900 focus:border-blue-900 font-mono uppercase text-slate-800" 
                                                           required 
                                                           placeholder="COVE..."
                                                           @keydown.enter.prevent="fetchCoveData(index)">
                                                    <button type="button" 
                                                            @click="fetchCoveData(index)"
                                                            class="inline-flex items-center px-3 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-600 text-xs rounded-r-md hover:bg-slate-200 hover:text-blue-900 transition"
                                                            title="Consultar en SAT">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                    </button>
                                                </div>
                                                <p x-show="cove.loading" class="text-xs text-blue-600 mt-1 animate-pulse font-medium">Conectando con VUCEM...</p>
                                            </td>
                                            
                                            <td class="p-3 align-top">
                                                 <select :name="`coves[${index}][metodo_valoracion]`" x-model="cove.metodo_valoracion" class="w-full text-xs border-slate-300 rounded focus:ring-blue-900 focus:border-blue-900">
                                                    <option value="1">Valor de Transacción</option>
                                                    <option value="2">Idénticas</option>
                                                    <option value="3">Similares</option>
                                                 </select>
                                            </td>
                                            <td class="p-3 align-top">
                                                <input type="text" :name="`coves[${index}][numero_factura]`" x-model="cove.numero_factura" class="w-full text-xs border-slate-300 bg-slate-50 rounded text-slate-500" required readonly>
                                            </td>
                                            <td class="p-3 align-top">
                                                <input type="date" :name="`coves[${index}][fecha_expedicion]`" x-model="cove.fecha_expedicion" class="w-full text-xs border-slate-300 bg-slate-50 rounded text-slate-500" readonly>
                                            </td>
                                            <td class="p-3 align-top">
                                                <input type="text" :name="`coves[${index}][emisor]`" x-model="cove.emisor" class="w-full text-xs border-slate-300 bg-slate-50 rounded text-slate-500" readonly>
                                            </td>
                                            <td class="p-3 text-center align-top">
                                                <button type="button" @click="removeCove(index)" class="text-red-400 hover:text-red-600 p-2 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="coves.length === 0">
                                        <td colspan="6" class="text-center py-10 text-slate-400 bg-slate-50 border-2 border-dashed border-slate-200 rounded-lg m-4">
                                            <div class="flex flex-col items-center">
                                                <svg class="h-10 w-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span class="text-sm font-medium">Agregue un COVE para comenzar la importación.</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- TOTALES -->
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Resumen de Valores
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label value="Precio Pagado" class="text-xs uppercase text-slate-500 font-bold" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">$</span>
                                        <input name="total_precio_pagado" x-model="totales.precio_pagado" type="number" step="0.01" class="pl-7 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900 text-slate-800" />
                                    </div>
                                </div>
                                
                                <div>
                                    <x-input-label value="(+) Incrementables" class="text-xs uppercase text-slate-500 font-bold" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">$</span>
                                        <input name="total_incrementables" x-model="totales.incrementables" type="number" step="0.01" class="pl-7 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900 text-slate-800" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label value="(-) Decrementables" class="text-xs uppercase text-slate-500 font-bold" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">$</span>
                                        <input name="total_decrementables" x-model="totales.decrementables" type="number" step="0.01" class="pl-7 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900 text-slate-800" />
                                    </div>
                                </div>

                                <div class="md:col-span-2 lg:col-span-1 bg-blue-50 p-2 rounded-lg border border-blue-100">
                                    <x-input-label value="= VALOR EN ADUANA" class="text-xs font-bold text-blue-900" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-blue-800 font-bold">$</span>
                                        <input name="total_valor_aduana" 
                                              :value="(parseFloat(totales.precio_pagado || 0) + parseFloat(totales.incrementables || 0) - parseFloat(totales.decrementables || 0)).toFixed(2)"
                                              class="pl-7 block w-full bg-white border-blue-200 rounded-md shadow-inner text-blue-900 font-bold text-lg" readonly />
                                    </div>
                                </div>
                                
                                <div>
                                    <x-input-label value="Precio por Pagar" class="text-xs uppercase text-slate-500 font-bold" />
                                    <div class="relative mt-1">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">$</span>
                                        <input name="total_precio_por_pagar" x-model="totales.precio_por_pagar" type="number" step="0.01" class="pl-7 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900 text-slate-800" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between pt-6 border-t border-slate-100">
                            <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-slate-800 font-medium px-4 py-2">Cancelar</a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-transparent rounded-md font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-900 shadow-lg transform hover:-translate-y-0.5 transition">
                                Guardar y Siguiente &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script de Alpine (Sin cambios lógicos) -->
    <script>
        function covesHandler() {
            return {
                coves: @json($manifestation->coves ?? []),
                totales: {
                    precio_pagado: '{{ $manifestation->total_precio_pagado ?? 0 }}',
                    incrementables: '{{ $manifestation->total_incrementables ?? 0 }}',
                    decrementables: '{{ $manifestation->total_decrementables ?? 0 }}',
                    precio_por_pagar: '{{ $manifestation->total_precio_por_pagar ?? 0 }}',
                },
                addCove() {
                    this.coves.push({ edocument: '', metodo_valoracion: '1', numero_factura: '', fecha_expedicion: '', emisor: '', loading: false });
                },
                removeCove(index) {
                    this.coves.splice(index, 1);
                },
                fetchCoveData(index) {
                    const cove = this.coves[index];
                    if(!cove.edocument || cove.edocument.trim() === '') { return alert('Ingrese un COVE válido.'); }
                    cove.loading = true;
                    setTimeout(() => {
                        cove.numero_factura = 'FACT-' + Math.floor(Math.random() * 99999);
                        cove.fecha_expedicion = new Date().toISOString().split('T')[0];
                        cove.emisor = 'PROVEEDOR IMPORTADO S.A. DE C.V.';
                        cove.loading = false;
                        // this.totales.precio_pagado = (parseFloat(this.totales.precio_pagado) + 1000).toFixed(2);
                    }, 800);
                }
            }
        }
    </script>
</x-app-layout>