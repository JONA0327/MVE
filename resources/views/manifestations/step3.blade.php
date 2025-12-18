<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Detalle de la Manifestación
        </h2>
    </x-slot>

    <div class="py-12" x-data="detalleHandler()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
             <!-- STEPPER VISUAL -->
             <div class="mb-10">
                 <div class="flex items-center justify-between w-full opacity-90">
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">PASO 1</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">PASO 2</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">PASO 3</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">PASO 4</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Resumen</div></div>
                </div>
            </div>

            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300">
                
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300">
                    <h1 class="text-lg font-bold text-slate-900 uppercase">4. Detalles y Ajustes</h1>
                    <p class="text-xs text-slate-500">Especifique método de valoración, incrementables y pagos.</p>
                </div>

                <div class="p-10">
                    <form method="POST" action="{{ route('manifestations.updateStep3', $manifestation->uuid) }}">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 p-4 rounded border-l-4 border-red-500 text-red-700 text-sm font-bold">
                                Por favor revise los campos obligatorios marcados.
                            </div>
                        @endif

                        <!-- 1. GENERALES DEL TRÁMITE -->
                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Método e Incoterm</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label value="Método de Valoración" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <!-- CATÁLOGO: Métodos de Valoración -->
                                    <select name="metodo_valoracion_global" required class="w-full mt-1 border-slate-300 rounded-sm shadow-sm focus:border-blue-900 focus:ring-blue-900 text-slate-700 text-sm" x-model="general.metodo">
                                        <option value="">Seleccione...</option>
                                        @foreach($catalogs['metodos_de_valoracion'] ?? [] as $m)
                                            <option value="{{ $m['clave'] }}">{{ $m['descripcion'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <x-input-label value="INCOTERM" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <!-- CATÁLOGO: Incoterms (CORREGIDO: Solo descripción) -->
                                    <select name="incoterm" 
                                        required 
                                        class="w-full mt-1 border-slate-300 rounded-sm shadow-sm focus:border-blue-900 focus:ring-blue-900 text-slate-700 text-sm" 
                                        x-bind:class="isFromEme('incoterm') ? 'bg-gray-50 cursor-not-allowed' : 'bg-white'"
                                        x-bind:disabled="isFromEme('incoterm')"
                                        x-model="general.incoterm">
                                        <option value="">Seleccione...</option>
                                        @foreach($catalogs['incoterms'] ?? [] as $inc)
                                            <!-- Aquí quitamos la clave visual para que no se vea doble -->
                                            <option value="{{ $inc['clave'] }}">{{ $inc['descripcion'] }}</option>
                                        @endforeach
                                    </select>
                                    <span x-show="isFromEme('incoterm')" class="absolute top-0 right-0 bg-blue-600 text-white text-xs px-2 py-1 rounded-bl text-[10px] font-bold">📄 Del EME</span>
                                </div>
                                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                                    <span class="text-xs font-bold text-slate-500 uppercase block mb-2 required">¿Existe Vinculación?</span>
                                    <div class="flex items-center space-x-6 mt-2">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="existe_vinculacion" value="1" required x-model="general.vinculacion" class="text-blue-900 focus:ring-blue-900 h-4 w-4">
                                            <span class="ml-2 text-slate-700 text-sm font-bold">Sí</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="existe_vinculacion" value="0" required x-model="general.vinculacion" class="text-blue-900 focus:ring-blue-900 h-4 w-4">
                                            <span class="ml-2 text-slate-700 text-sm font-bold">No</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. PEDIMENTOS -->
                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Pedimento Asociado</h3>
                            <div class="overflow-x-auto border border-slate-200 rounded">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                                        <tr>
                                            <th class="p-3 text-left font-bold uppercase text-xs">Número Pedimento <span class="text-red-500">*</span></th>
                                            <th class="p-3 text-left font-bold uppercase text-xs">Patente <span class="text-red-500">*</span></th>
                                            <th class="p-3 text-left font-bold uppercase text-xs">Aduana <span class="text-red-500">*</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(ped, i) in pedimentos" :key="i">
                                            <tr class="bg-white">
                                                <td class="p-2">
                                                    <input type="text" :name="`pedimentos[${i}][numero_pedimento]`" x-model="ped.numero_pedimento" @input="formatPedimento(ped)" required maxlength="21" placeholder="XX  XXX  XXXX  XXXXXXX" class="w-full text-sm rounded-sm border-slate-300 focus:ring-blue-900 font-mono">
                                                </td>
                                                <td class="p-2"><input type="text" :name="`pedimentos[${i}][patente]`" x-model="ped.patente" required class="w-full text-sm rounded-sm border-slate-300 focus:ring-blue-900"></td>
                                                <td class="p-2">
                                                    <select :name="`pedimentos[${i}][aduana_clave]`" x-model="ped.aduana_clave" required class="w-full text-sm rounded-sm border-slate-300 focus:ring-blue-900">
                                                        <option value="430">430 - Veracruz</option>
                                                        <option value="240">240 - Nuevo Laredo</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 3. INCREMENTABLES -->
                        <div class="mb-10">
                            <div class="flex justify-between items-center mb-6 border-b-2 border-blue-900 pb-1">
                                <h3 class="text-xs font-bold text-blue-900 uppercase">Incrementables</h3>
                                <button type="button" @click="addIncrementable()" class="text-xs bg-white border border-blue-900 text-blue-900 px-3 py-1 rounded-sm font-bold hover:bg-blue-50 transition uppercase">
                                    + Agregar
                                </button>
                            </div>
                            <div class="space-y-4">
                                <template x-for="(inc, i) in incrementables" :key="i">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-slate-50 p-4 rounded border border-slate-200 shadow-sm relative">
                                        <div class="md:col-span-3">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Concepto</label>
                                            <!-- CATÁLOGO: Incrementables -->
                                            <select :name="`incrementables[${i}][concepto]`" x-model="inc.concepto" required class="w-full text-xs rounded-sm border-slate-300">
                                                <option value="">Seleccione...</option>
                                                @foreach($catalogs['incrementables'] ?? [] as $inc)
                                                    <option value="{{ $inc['clave'] }}" title="{{ $inc['descripcion'] }}">{{ Str::limit($inc['descripcion'], 40) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="md:col-span-2">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                            <input type="date" :name="`incrementables[${i}][fecha_erogacion]`" x-model="inc.fecha_erogacion" @change="updateTC(inc)" required class="w-full text-xs rounded-sm border-slate-300">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                            <select :name="`incrementables[${i}][moneda]`" x-model="inc.moneda" @change="updateTC(inc)" required class="w-full text-xs rounded-sm border-slate-300">
                                                @foreach($currencies as $c)
                                                    <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                            <input type="number" step="0.01" :name="`incrementables[${i}][importe]`" x-model="inc.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                        </div>
                                        <div class="md:col-span-2 relative">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">T. Cambio</label>
                                            <input type="number" step="0.0001" :name="`incrementables[${i}][tipo_cambio]`" x-model="inc.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                            <div x-show="inc.loading" class="absolute right-2 top-8 text-xs text-blue-600 animate-spin">↻</div>
                                        </div>
                                        <div class="md:col-span-1 flex justify-center pb-1">
                                            <button type="button" @click="incrementables.splice(i,1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                        </div>
                                        
                                        <div class="md:col-span-12 mt-2 p-2 bg-blue-50 border border-blue-100 rounded flex items-center">
                                            <input type="hidden" :name="`incrementables[${i}][a_cargo_importador]`" value="1">
                                            <svg class="w-4 h-4 text-blue-800 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            <span class="text-xs text-blue-900 font-bold uppercase">Gasto a cargo del importador</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 4. DECREMENTABLES -->
                        <div class="mb-10">
                            <div class="flex justify-between items-center mb-6 border-b-2 border-slate-400 pb-1">
                                <h3 class="text-xs font-bold text-slate-700 uppercase">Decrementables</h3>
                                <button type="button" @click="addDecrementable()" class="text-xs bg-white border border-slate-400 text-slate-600 px-3 py-1 rounded-sm font-bold hover:bg-slate-50 transition uppercase">
                                    + Agregar
                                </button>
                            </div>
                            <div class="space-y-4">
                                <template x-for="(dec, i) in decrementables" :key="i">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-slate-50 p-4 rounded border border-slate-200 shadow-sm relative">
                                        <div class="md:col-span-3">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Concepto</label>
                                            <!-- CATÁLOGO: Decrementables -->
                                            <select :name="`decrementables[${i}][concepto]`" x-model="dec.concepto" required class="w-full text-xs rounded-sm border-slate-300">
                                                <option value="">Seleccione...</option>
                                                @foreach($catalogs['decrementables'] ?? [] as $dec)
                                                    <option value="{{ $dec['clave'] }}" title="{{ $dec['descripcion'] }}">{{ Str::limit($dec['descripcion'], 40) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="md:col-span-2">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                            <input type="date" :name="`decrementables[${i}][fecha_erogacion]`" x-model="dec.fecha_erogacion" @change="updateTC(dec)" required class="w-full text-xs rounded-sm border-slate-300">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                            <select :name="`decrementables[${i}][moneda]`" x-model="dec.moneda" @change="updateTC(dec)" required class="w-full text-xs rounded-sm border-slate-300">
                                                @foreach($currencies as $c)
                                                    <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                            <input type="number" step="0.01" :name="`decrementables[${i}][importe]`" x-model="dec.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                        </div>
                                        <div class="md:col-span-2 relative">
                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">T. Cambio</label>
                                            <input type="number" step="0.0001" :name="`decrementables[${i}][tipo_cambio]`" x-model="dec.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                            <div x-show="dec.loading" class="absolute right-2 top-8 text-xs text-slate-500 animate-spin">↻</div>
                                        </div>
                                        <div class="md:col-span-1 flex justify-center pb-1">
                                            <button type="button" @click="decrementables.splice(i,1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 5. PAGOS -->
                        <div class="mb-10">
                            <div class="flex justify-between items-center mb-6 border-b-2 border-slate-800 pb-1">
                                <h3 class="text-xs font-bold text-slate-800 uppercase">Pagos</h3>
                                <button type="button" @click="addPago()" class="text-xs bg-white border border-slate-800 text-slate-800 px-3 py-1 rounded-sm font-bold hover:bg-slate-100 transition uppercase">
                                    + Agregar
                                </button>
                            </div>
                            <div class="overflow-x-auto border border-slate-200 rounded">
                                <table class="w-full text-sm border-collapse">
                                    <thead class="bg-slate-100 text-slate-500 font-bold text-xs uppercase">
                                        <tr>
                                            <th class="p-2 text-left border-b border-slate-200">Estatus <span class="text-red-500">*</span></th>
                                            <th class="p-2 text-left border-b border-slate-200 w-32">Fecha <span class="text-red-500">*</span></th>
                                            <th class="p-2 text-left border-b border-slate-200">Importe <span class="text-red-500">*</span></th>
                                            <th class="p-2 text-left border-b border-slate-200 w-24">Moneda</th>
                                            <th class="p-2 text-left border-b border-slate-200">T.C.</th>
                                            <th class="p-2 text-left border-b border-slate-200">Forma Pago <span class="text-red-500">*</span></th>
                                            <th class="p-2 text-center border-b border-slate-200"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(pago, i) in pagos" :key="i">
                                            <tr class="bg-white hover:bg-slate-50">
                                                <td class="p-2 border-b border-slate-100">
                                                    <select :name="`pagos[${i}][status]`" x-model="pago.status" required class="w-full text-xs border-slate-300 rounded-sm">
                                                        <option value="paid">Pagado</option>
                                                        <option value="payable">Por Pagar</option>
                                                    </select>
                                                </td>
                                                <td class="p-2 border-b border-slate-100"><input type="date" :name="`pagos[${i}][fecha]`" x-model="pago.fecha" @change="updateTC(pago)" required class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                <td class="p-2 border-b border-slate-100"><input type="number" step="0.01" :name="`pagos[${i}][importe]`" x-model="pago.importe" required class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                <td class="p-2 border-b border-slate-100"><select :name="`pagos[${i}][moneda]`" x-model="pago.moneda" @change="updateTC(pago)" required class="w-full text-xs border-slate-300 rounded-sm"><option value="USD">USD</option><option value="MXN">MXN</option></select></td>
                                                <td class="p-2 border-b border-slate-100"><input type="number" step="0.0001" :name="`pagos[${i}][tipo_cambio]`" x-model="pago.tipo_cambio" class="w-full text-xs border-slate-300 bg-slate-50 rounded-sm" readonly></td>
                                                <td class="p-2 border-b border-slate-100">
                                                    <!-- CATÁLOGO: Formas de Pago -->
                                                    <select :name="`pagos[${i}][forma_pago]`" x-model="pago.forma_pago" required class="w-full text-xs border-slate-300 rounded-sm">
                                                        <option value="">Seleccione...</option>
                                                        @foreach($catalogs['formas_de_pago'] ?? [] as $fp)
                                                            <option value="{{ $fp['clave'] }}">{{ $fp['descripcion'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="p-2 border-b border-slate-100 text-center"><button type="button" @click="pagos.splice(i,1)" class="text-slate-400 hover:text-red-600 font-bold">✕</button></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 6. COMPENSACIONES -->
                        <div class="mb-10">
                            <div class="flex justify-between items-center mb-6 border-b-2 border-yellow-500 pb-1">
                                <h3 class="text-xs font-bold text-slate-800 uppercase">Compenso Pago (Opcional)</h3>
                                <button type="button" @click="addCompensacion()" class="text-xs bg-white border border-yellow-500 text-yellow-700 px-3 py-1 rounded-sm font-bold hover:bg-yellow-50 transition uppercase">
                                    + Agregar
                                </button>
                            </div>
                            <div class="overflow-x-auto border border-yellow-200 rounded">
                                <table class="w-full text-sm border-collapse">
                                    <thead class="bg-yellow-50 text-yellow-800 font-bold text-xs uppercase">
                                        <tr>
                                            <th class="p-2 text-left w-32 border-b border-yellow-200">Fecha Pago</th>
                                            <th class="p-2 text-left border-b border-yellow-200">Motivo</th>
                                            <th class="p-2 text-left border-b border-yellow-200">Prestación</th>
                                            <th class="p-2 text-left border-b border-yellow-200">Forma de pago</th>
                                            <th class="p-2 text-center border-b border-yellow-200"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(comp, i) in compensaciones" :key="i">
                                            <tr class="bg-white hover:bg-yellow-50/20">
                                                <td class="p-2 border-b border-slate-100"><input type="date" :name="`compensaciones[${i}][fecha]`" x-model="comp.fecha" required class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                <td class="p-2 border-b border-slate-100"><input type="text" :name="`compensaciones[${i}][motivo]`" x-model="comp.motivo" required class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                <td class="p-2 border-b border-slate-100"><input type="text" :name="`compensaciones[${i}][prestacion_mercancia]`" x-model="comp.prestacion_mercancia" class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                <td class="p-2 border-b border-slate-100"><input type="text" :name="`compensaciones[${i}][forma_pago]`" x-model="comp.forma_pago" required class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                <td class="p-2 border-b border-slate-100 text-center"><button type="button" @click="compensaciones.splice(i,1)" class="text-slate-400 hover:text-red-600 font-bold">✕</button></td>
                                            </tr>
                                        </template>
                                        <tr x-show="compensaciones.length === 0">
                                            <td colspan="5" class="text-center py-4 text-xs text-slate-400 italic bg-slate-50">No hay compensaciones registradas.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- FECHAS INFORMATIVAS (EME) -->
                        <div class="mb-10" x-show="emeData !== null">
                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Fechas Informativas del Pedimento</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-sm p-6">
                                <p class="text-xs text-blue-700 mb-4 italic">📄 Información extraída del archivo EME (solo lectura)</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <x-input-label value="Fecha de Entrada" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                        <input type="date" 
                                            name="fecha_entrada" 
                                            x-model="fechas_informativas.fecha_entrada" 
                                            readonly 
                                            class="w-full mt-1 border-slate-300 rounded-sm shadow-sm bg-gray-50 cursor-not-allowed text-slate-700 text-sm" />
                                    </div>
                                    <div>
                                        <x-input-label value="Fecha de Pago" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                        <input type="date" 
                                            name="fecha_pago_pedimento" 
                                            x-model="fechas_informativas.fecha_pago_pedimento" 
                                            readonly 
                                            class="w-full mt-1 border-slate-300 rounded-sm shadow-sm bg-gray-50 cursor-not-allowed text-slate-700 text-sm" />
                                    </div>
                                    <div>
                                        <x-input-label value="Fecha de Presentación" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                        <input type="date" 
                                            name="fecha_presentacion" 
                                            x-model="fechas_informativas.fecha_presentacion" 
                                            readonly 
                                            class="w-full mt-1 border-slate-300 rounded-sm shadow-sm bg-gray-50 cursor-not-allowed text-slate-700 text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OBSERVACIONES DEL PEDIMENTO -->
                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Observaciones del Pedimento</h3>
                            <div class="relative">
                                <textarea name="observaciones_pedimento" 
                                    x-model="observaciones_pedimento"
                                    rows="4"
                                    class="w-full mt-1 border-slate-300 rounded-sm shadow-sm focus:border-blue-900 focus:ring-blue-900 text-slate-700 text-sm"
                                    placeholder="Capture observaciones adicionales del pedimento..."></textarea>
                                <span x-show="isFromEme('observaciones_pedimento')" class="absolute top-0 right-0 bg-blue-600 text-white text-xs px-2 py-1 rounded-bl text-[10px] font-bold">📄 Del EME</span>
                                <p class="text-xs text-slate-500 mt-2 italic">Campo editable - Si viene del EME, puede modificarse según sea necesario.</p>
                            </div>
                        </div>

                        <!-- 7. RFCs DE CONSULTA -->
                        <div class="mb-10">
                            <div class="flex justify-between items-center mb-6 border-b-2 border-green-600 pb-1">
                                <h3 class="text-xs font-bold text-slate-800 uppercase">RFCs Autorizados para Consulta (Agente Aduanal)</h3>
                                <button type="button" @click="addRfc()" class="text-xs bg-white border border-green-600 text-green-700 px-3 py-1 rounded-sm font-bold hover:bg-green-50 transition uppercase">
                                    + Agregar RFC
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="(rfc, i) in consultationRfcs" :key="i">
                                    <div class="flex items-center bg-white border border-slate-200 rounded-sm p-2 shadow-sm">
                                        <div class="bg-green-100 p-2 rounded-sm mr-2">
                                            <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <input type="text" :name="`consultation_rfcs[${i}][rfc_consulta]`" x-model="rfc.rfc_consulta" class="flex-1 text-sm border-0 focus:ring-0 uppercase font-mono bg-transparent" placeholder="RFC..." minlength="12" maxlength="13" required>
                                        <button type="button" @click="consultationRfcs.splice(i,1)" class="text-slate-400 hover:text-red-500 font-bold ml-2 p-1">✕</button>
                                    </div>
                                </template>
                            </div>
                            <div x-show="consultationRfcs.length === 0" class="text-center py-4 text-xs text-slate-400 italic bg-slate-50 border border-dashed border-slate-200 rounded">
                                No se han autorizado RFCs adicionales para consultar este documento.
                            </div>
                        </div>

                        <!-- 8. DECLARACIÓN ART. 81 -->
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

                        <!-- BOTONES DE NAVEGACIÓN -->
                        <div class="flex justify-between items-center mt-10 pt-6 border-t border-slate-200">
                            <a href="{{ route('manifestations.step2', $manifestation->uuid) }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-sm font-bold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 hover:text-slate-900 transition">
                                &larr; Anterior
                            </a>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-slate-900 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-900 shadow-md transform hover:-translate-y-0.5 transition">
                                Guardar y Siguiente &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function detalleHandler() {
            return {
                pedimentos: @json($manifestation->pedimentos ?? []) .length ? @json($manifestation->pedimentos) : [{numero_pedimento: '', patente: '', aduana_clave: '430'}],
                incrementables: @json($incrementables ?? []),
                decrementables: @json($decrementables ?? []),
                pagos: @json($manifestation->payments ?? []),
                compensaciones: @json($manifestation->compensations ?? []),
                consultationRfcs: @json($manifestation->consultationRfcs ?? []),
                emeData: null,
                general: {
                    metodo: '{{ old("metodo_valoracion_global", $manifestation->metodo_valoracion_global ?? "") }}',
                    incoterm: '{{ old("incoterm", $manifestation->incoterm ?? "") }}'
                },
                fechas_informativas: {
                    fecha_entrada: '{{ old("fecha_entrada", $manifestation->fecha_entrada ?? "") }}',
                    fecha_pago_pedimento: '{{ old("fecha_pago_pedimento", $manifestation->fecha_pago_pedimento ?? "") }}',
                    fecha_presentacion: '{{ old("fecha_presentacion", $manifestation->fecha_presentacion ?? "") }}'
                },
                observaciones_pedimento: '{{ old("observaciones_pedimento", $manifestation->observaciones_pedimento ?? "") }}',
                init() {
                    // Recuperar datos del EME de sessionStorage si existen
                    const storedEme = sessionStorage.getItem('emeData');
                    if (storedEme) {
                        this.emeData = JSON.parse(storedEme);
                        this.aplicarDatosEme();
                    }
                },
                aplicarDatosEme() {
                    if (!this.emeData) return;
                    
                    // Precargar pedimento
                    if (this.emeData.numero_pedimento && this.pedimentos.length === 1 && !this.pedimentos[0].numero_pedimento) {
                        this.pedimentos[0].numero_pedimento = this.emeData.numero_pedimento;
                        this.pedimentos[0].patente = this.emeData.patente || '';
                        this.pedimentos[0].aduana_clave = this.emeData.aduana_clave || '430';
                        this.pedimentos[0].from_eme = true;
                    }
                    
                    // Precargar incoterm
                    if (this.emeData.incoterm) {
                        this.general.incoterm = this.emeData.incoterm;
                    }
                    
                    // Precargar fechas informativas
                    if (this.emeData.fecha_entrada) {
                        this.fechas_informativas.fecha_entrada = this.emeData.fecha_entrada;
                    }
                    if (this.emeData.fecha_pago_pedimento) {
                        this.fechas_informativas.fecha_pago_pedimento = this.emeData.fecha_pago_pedimento;
                    }
                    if (this.emeData.fecha_presentacion) {
                        this.fechas_informativas.fecha_presentacion = this.emeData.fecha_presentacion;
                    }
                    
                    // Precargar observaciones
                    if (this.emeData.observaciones_pedimento) {
                        this.observaciones_pedimento = this.emeData.observaciones_pedimento;
                    }
                },
                isFromEme(field) {
                    return this.emeData && this.emeData[field];
                },



                addIncrementable() { 
                    this.incrementables.push({ concepto: '', importe: 0, moneda: 'USD', tipo_cambio: 20.0000, a_cargo_importador: true, fecha_erogacion: '', loading: false }); 
                },
                addDecrementable() { 
                    this.decrementables.push({ concepto: '', importe: 0, moneda: 'USD', tipo_cambio: 20.0000, fecha_erogacion: '', loading: false }); 
                },
                addPago() { 
                    this.pagos.push({ status: 'paid', fecha: '', importe: 0, moneda: 'USD', tipo_cambio: 20.0000, forma_pago: 'Transferencia', situacion_pago: '', loading: false }); 
                },
                addCompensacion() {
                    this.compensaciones.push({ fecha: '', motivo: '', prestacion_mercancia: '', forma_pago: '' });
                },
                addRfc() {
                    this.consultationRfcs.push({ rfc_consulta: '' });
                },

                formatPedimento(ped) {
                    let val = ped.numero_pedimento.replace(/\D/g, '');
                    let parts = [];
                    if (val.length > 0) parts.push(val.substring(0, 2));
                    if (val.length > 2) parts.push(val.substring(2, 5));
                    if (val.length > 5) parts.push(val.substring(5, 9));
                    if (val.length > 9) parts.push(val.substring(9));
                    ped.numero_pedimento = parts.join('  ');
                },
                async updateTC(row) {
                    if (row.moneda === 'MXN') { row.tipo_cambio = 1.0000; return; }
                    const fecha = row.fecha || row.fecha_erogacion;
                    if (!fecha) return; 
                    const today = new Date().toISOString().split('T')[0];
                    if (fecha > today) { alert("No hay tipo de cambio oficial para fechas futuras."); row.tipo_cambio = 0; return; }
                    row.loading = true;
                    try {
                        const response = await fetch(`https://api.frankfurter.app/${fecha}?from=${row.moneda}&to=MXN`);
                        if (!response.ok) throw new Error('Error API');
                        const data = await response.json();
                        if (data.rates && data.rates.MXN) { row.tipo_cambio = data.rates.MXN.toFixed(4); } 
                        else { alert("No se encontró cotización."); }
                    } catch (error) { console.error(error); alert("Error de red al consultar TC."); } 
                    finally { row.loading = false; }
                }
            }
        }
    </script>
</x-app-layout>