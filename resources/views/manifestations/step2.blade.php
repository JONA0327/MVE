<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Valores de la Transacción
        </h2>
    </x-slot>

    <div class="py-12" x-data="covesHandler()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- STEPPER ... (mismo stepper) ... -->
            <div class="mb-10">
                 <div class="flex items-center justify-between w-full opacity-90">
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Generales</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">Valores</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Detalles</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Archivos</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Resumen</div></div>
                </div>
            </div>

            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300">
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300 flex justify-between items-center">
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 uppercase">2. Valores Declarados</h1>
                        <p class="text-xs text-slate-500">Capture los acuses de valor (COVEs) para calcular el valor en aduana.</p>
                    </div>
                    <button type="button" @click="addCove()" class="bg-white border border-slate-300 text-slate-700 text-xs px-4 py-2 rounded-sm font-bold shadow-sm hover:bg-slate-50 hover:text-blue-900 transition flex items-center uppercase">
                        <span class="text-lg mr-1 leading-none text-blue-600">+</span>
                        Agregar COVE
                    </button>
                </div>

                <div class="p-10">
                    <form method="POST" action="{{ route('manifestations.updateStep2', $manifestation->uuid) }}">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 p-4 rounded border-l-4 border-red-500 text-red-700 text-sm font-bold">
                                Por favor corrija los errores marcados antes de continuar.
                            </div>
                        @endif

                        <div class="overflow-x-auto mb-8 shadow-sm rounded border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/4">Acuse (eDocument) <span class="text-red-500">*</span></th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Método Val. <span class="text-red-500">*</span></th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"># Factura</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha Exp.</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Emisor</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <template x-for="(cove, index) in coves" :key="index">
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-3 align-top">
                                                <div class="flex rounded-sm shadow-sm">
                                                    <input type="text" :name="`coves[${index}][edocument]`" x-model="cove.edocument" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-sm text-xs border-slate-300 focus:ring-blue-900 focus:border-blue-900 font-mono uppercase text-slate-800" required placeholder="COVE..." @keydown.enter.prevent="fetchCoveData(index)">
                                                    <button type="button" @click="fetchCoveData(index)" class="inline-flex items-center px-3 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-600 text-xs rounded-r-sm hover:bg-slate-200 hover:text-blue-900 transition" title="Consultar">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                    </button>
                                                </div>
                                                <p x-show="cove.loading" class="text-xs text-blue-600 mt-1 animate-pulse font-medium">Buscando...</p>
                                            </td>
                                            <td class="p-3 align-top">
                                                 <!-- SELECT DINÁMICO DE MÉTODOS DE VALORACIÓN -->
                                                 <select :name="`coves[${index}][metodo_valoracion]`" x-model="cove.metodo_valoracion" required class="w-full text-xs border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                                    <option value="">Seleccione...</option>
                                                    @foreach($catalogs['metodos_de_valoracion'] ?? [] as $metodo)
                                                        <option value="{{ $metodo['clave'] }}">{{ $metodo['descripcion'] }}</option>
                                                    @endforeach
                                                 </select>
                                            </td>
                                            <td class="p-3 align-top"><input type="text" :name="`coves[${index}][numero_factura]`" x-model="cove.numero_factura" class="w-full text-xs border-slate-300 bg-slate-50 rounded-sm text-slate-500" required readonly></td>
                                            <td class="p-3 align-top"><input type="date" :name="`coves[${index}][fecha_expedicion]`" x-model="cove.fecha_expedicion" class="w-full text-xs border-slate-300 bg-slate-50 rounded-sm text-slate-500" readonly></td>
                                            <td class="p-3 align-top"><input type="text" :name="`coves[${index}][emisor]`" x-model="cove.emisor" class="w-full text-xs border-slate-300 bg-slate-50 rounded-sm text-slate-500" readonly></td>
                                            <td class="p-3 text-center align-top">
                                                <button type="button" @click="removeCove(index)" class="text-slate-400 hover:text-red-600 p-2 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- TOTALES -->
                        <div class="bg-slate-50 p-8 rounded border border-slate-200">
                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Resumen de Valores</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                
                                <!-- Precio Pagado (CON CATÁLOGO) -->
                                <div>
                                    <x-input-label value="Precio Pagado" class="text-xs font-bold text-slate-500 uppercase mb-1 required" />
                                    <div class="relative flex rounded-sm shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                        <input name="total_precio_pagado" x-model="totales.precio_pagado" @input="calculateTotals()" required type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-slate-300 focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                        
                                        <!-- SELECT DINÁMICO DE MONEDAS -->
                                        <select name="moneda_precio_pagado" x-model="totales.moneda_precio_pagado" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                            @foreach($currencies as $c)
                                                <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <x-input-error :messages="$errors->get('total_precio_pagado')" />
                                </div>
                                
                                <!-- Incrementables -->
                                <div>
                                    <x-input-label value="(+) Incrementables" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                    <div class="relative flex rounded-sm shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                        <input name="total_incrementables" x-model="totales.incrementables" @input="calculateTotals()" type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-slate-300 focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                        <select name="moneda_incrementables" x-model="totales.moneda_incrementables" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                            @foreach($currencies as $c)
                                                <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Decrementables -->
                                <div>
                                    <x-input-label value="(-) Decrementables" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                    <div class="relative flex rounded-sm shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                        <input name="total_decrementables" x-model="totales.decrementables" @input="calculateTotals()" type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-slate-300 focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                        <select name="moneda_decrementables" x-model="totales.moneda_decrementables" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                            @foreach($currencies as $c)
                                                <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Valor en Aduana -->
                                <div class="md:col-span-2 lg:col-span-1 bg-blue-100 p-4 rounded border border-blue-200">
                                    <x-input-label value="= VALOR EN ADUANA" class="text-xs font-black text-blue-900 uppercase mb-1 required" />
                                    <div class="relative flex rounded-sm shadow-inner bg-white">
                                        <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-blue-300 bg-blue-50 text-blue-800 text-sm font-bold">$</span>
                                        <input name="total_valor_aduana" x-model="totales.valor_aduana" type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-blue-300 bg-white text-blue-900 font-black text-xl focus:ring-blue-900 focus:border-blue-900" required />
                                        <span class="inline-flex items-center px-3 rounded-r-sm border border-l-0 border-blue-300 bg-blue-50 text-blue-900 text-xs font-bold">MXN</span>
                                    </div>
                                    <x-input-error :messages="$errors->get('total_valor_aduana')" />
                                </div>
                                
                                <!-- Precio por Pagar -->
                                <div>
                                    <x-input-label value="Precio por Pagar" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                    <div class="relative flex rounded-sm shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                        <input name="total_precio_por_pagar" x-model="totales.precio_por_pagar" type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-slate-300 focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                        <select name="moneda_precio_por_pagar" x-model="totales.moneda_precio_por_pagar" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                            @foreach($currencies as $c)
                                                <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex justify-between items-center pt-6 border-t border-slate-200">
                             <div class="flex items-center">
                                <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-red-700 font-bold text-sm px-4 py-2 mr-4 transition uppercase tracking-wider">Cancelar</a>
                                <a href="{{ route('manifestations.step1', $manifestation->uuid) }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-sm font-bold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 hover:text-slate-900 transition">
                                    &larr; Anterior
                                </a>
                            </div>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-slate-900 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-900 shadow-md transform hover:-translate-y-0.5 transition" :disabled="coves.length === 0">
                                Guardar y Siguiente &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function covesHandler() {
            return {
                coves: @json($manifestation->coves ?? []),
                totales: {
                    precio_pagado: '{{ $manifestation->total_precio_pagado ?? 0 }}',
                    incrementables: '{{ $manifestation->total_incrementables ?? 0 }}',
                    decrementables: '{{ $manifestation->total_decrementables ?? 0 }}',
                    valor_aduana: '{{ $manifestation->total_valor_aduana ?? 0 }}',
                    precio_por_pagar: '{{ $manifestation->total_precio_por_pagar ?? 0 }}',
                    moneda_precio_pagado: 'MXN',
                    moneda_incrementables: 'MXN',
                    moneda_decrementables: 'MXN',
                    moneda_precio_por_pagar: 'MXN',
                },
                calculateTotals() {
                    const pagado = parseFloat(this.totales.precio_pagado) || 0;
                    const incrementables = parseFloat(this.totales.incrementables) || 0;
                    const decrementables = parseFloat(this.totales.decrementables) || 0;
                    this.totales.valor_aduana = (pagado + incrementables - decrementables).toFixed(2);
                },
                addCove() {
                    if (this.coves.length > 0) {
                        const last = this.coves[this.coves.length - 1];
                        if (!last.edocument || last.edocument.trim() === '') {
                            return alert('Por favor complete el eDocument del renglón actual antes de agregar otro.');
                        }
                    }
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
                    }, 800);
                }
            }
        }
    </script>
</x-app-layout>