<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Manifestaciones de Valor
        </h2>
    </x-slot>

    <div class="py-12" x-data="mvHandler()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <!-- STEPPER VISUAL -->
            <div class="mb-10">
                <div class="flex items-center justify-between w-full opacity-90">
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">PASO 1</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">PASO 2</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">PASO 3</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">PASO 4</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Resumen</div></div>
                </div>
            </div>

            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300">
                
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300 flex justify-between items-center">
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 uppercase">2. Manifestaciones de Valor</h1>
                        <p class="text-xs text-slate-500">Agregue pedimentos, incrementables y decrementables.</p>
                    </div>
                    <button type="button" @click="addMV()" class="bg-blue-900 hover:bg-blue-800 text-white text-sm px-6 py-3 rounded-sm font-bold shadow-md transition flex items-center uppercase tracking-wider">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Añadir MV
                    </button>
                </div>

                <div class="p-10">
                    <form method="POST" action="{{ route('manifestations.updateStep2', $manifestation->uuid) }}">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 p-4 rounded border-l-4 border-red-500 text-red-700 text-sm font-bold">
                                Por favor revise los campos obligatorios marcados.
                            </div>
                        @endif

                        <!-- LISTA DE MVs -->
                        <div class="space-y-8">
                            <template x-for="(mv, mvIndex) in manifestaciones" :key="mvIndex">
                                <div class="bg-slate-50 border-2 border-slate-300 rounded-lg p-6 shadow-sm">
                                    
                                    <!-- Encabezado de MV -->
                                    <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-blue-900">
                                        <h3 class="text-sm font-bold text-blue-900 uppercase flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Manifestación de Valor #<span x-text="mvIndex + 1"></span>
                                        </h3>
                                        <button type="button" @click="removeMV(mvIndex)" class="text-red-600 hover:text-red-800 font-bold text-xs px-3 py-1 border border-red-300 rounded hover:bg-red-50 transition uppercase">
                                            Eliminar MV
                                        </button>
                                    </div>

                                    <!-- 1. PEDIMENTOS -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4">
                                            <h4 class="text-xs font-bold text-blue-900 uppercase">Pedimentos</h4>
                                            <button type="button" @click="addPedimento(mvIndex)" class="text-xs bg-white border border-blue-900 text-blue-900 px-3 py-1 rounded-sm font-bold hover:bg-blue-50 transition uppercase">
                                                + Agregar Pedimento
                                            </button>
                                        </div>
                                        <div class="overflow-x-auto border border-slate-200 rounded">
                                            <table class="w-full text-sm">
                                                <thead class="bg-slate-100 text-slate-500 border-b border-slate-200">
                                                    <tr>
                                                        <th class="p-3 text-left font-bold uppercase text-xs">Número Pedimento <span class="text-red-500">*</span></th>
                                                        <th class="p-3 text-left font-bold uppercase text-xs">Patente <span class="text-red-500">*</span></th>
                                                        <th class="p-3 text-left font-bold uppercase text-xs">Aduana <span class="text-red-500">*</span></th>
                                                        <th class="p-3 text-center font-bold uppercase text-xs w-20"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(ped, pedIndex) in mv.pedimentos" :key="pedIndex">
                                                        <tr class="bg-white hover:bg-slate-50">
                                                            <td class="p-2">
                                                                <input type="text" :name="`manifestaciones[${mvIndex}][pedimentos][${pedIndex}][numero_pedimento]`" x-model="ped.numero_pedimento" required maxlength="21" placeholder="XX  XXX  XXXX  XXXXXXX" class="w-full text-sm rounded-sm border-slate-300 focus:ring-blue-900 font-mono">
                                                            </td>
                                                            <td class="p-2">
                                                                <input type="text" :name="`manifestaciones[${mvIndex}][pedimentos][${pedIndex}][patente]`" x-model="ped.patente" required class="w-full text-sm rounded-sm border-slate-300 focus:ring-blue-900">
                                                            </td>
                                                            <td class="p-2">
                                                                <select :name="`manifestaciones[${mvIndex}][pedimentos][${pedIndex}][aduana_clave]`" x-model="ped.aduana_clave" required class="w-full text-sm rounded-sm border-slate-300 focus:ring-blue-900">
                                                                    <option value="430">430 - Veracruz</option>
                                                                    <option value="240">240 - Nuevo Laredo</option>
                                                                </select>
                                                            </td>
                                                            <td class="p-2 text-center">
                                                                <button type="button" @click="mv.pedimentos.splice(pedIndex, 1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- 2. INCREMENTABLES -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4">
                                            <h4 class="text-xs font-bold text-blue-900 uppercase">Incrementables</h4>
                                            <button type="button" @click="addIncrementable(mvIndex)" class="text-xs bg-white border border-blue-900 text-blue-900 px-3 py-1 rounded-sm font-bold hover:bg-blue-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(inc, incIndex) in mv.incrementables" :key="incIndex">
                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-4 rounded border border-slate-200 shadow-sm">
                                                    <div class="md:col-span-3">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Concepto</label>
                                                        <select :name="`manifestaciones[${mvIndex}][incrementables][${incIndex}][concepto]`" x-model="inc.concepto" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione...</option>
                                                            @foreach($catalogs['incrementables'] ?? [] as $incrementable)
                                                                <option value="{{ $incrementable['clave'] }}" title="{{ $incrementable['descripcion'] }}">{{ Str::limit($incrementable['descripcion'], 40) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                        <input type="date" :name="`manifestaciones[${mvIndex}][incrementables][${incIndex}][fecha_erogacion]`" x-model="inc.fecha_erogacion" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                                        <select :name="`manifestaciones[${mvIndex}][incrementables][${incIndex}][moneda]`" x-model="inc.moneda" required class="w-full text-xs rounded-sm border-slate-300">
                                                            @foreach($currencies as $c)
                                                                <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                                        <input type="number" step="0.01" :name="`manifestaciones[${mvIndex}][incrementables][${incIndex}][importe]`" x-model="inc.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">T. Cambio</label>
                                                        <input type="number" step="0.0001" :name="`manifestaciones[${mvIndex}][incrementables][${incIndex}][tipo_cambio]`" x-model="inc.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                                    </div>
                                                    <div class="md:col-span-1 flex justify-center pb-1">
                                                        <button type="button" @click="mv.incrementables.splice(incIndex, 1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- 3. DECREMENTABLES -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4">
                                            <h4 class="text-xs font-bold text-slate-700 uppercase">Decrementables</h4>
                                            <button type="button" @click="addDecrementable(mvIndex)" class="text-xs bg-white border border-slate-400 text-slate-600 px-3 py-1 rounded-sm font-bold hover:bg-slate-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(dec, decIndex) in mv.decrementables" :key="decIndex">
                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-4 rounded border border-slate-200 shadow-sm">
                                                    <div class="md:col-span-3">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Concepto</label>
                                                        <select :name="`manifestaciones[${mvIndex}][decrementables][${decIndex}][concepto]`" x-model="dec.concepto" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione...</option>
                                                            @foreach($catalogs['decrementables'] ?? [] as $decrementable)
                                                                <option value="{{ $decrementable['clave'] }}" title="{{ $decrementable['descripcion'] }}">{{ Str::limit($decrementable['descripcion'], 40) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                        <input type="date" :name="`manifestaciones[${mvIndex}][decrementables][${decIndex}][fecha_erogacion]`" x-model="dec.fecha_erogacion" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                                        <select :name="`manifestaciones[${mvIndex}][decrementables][${decIndex}][moneda]`" x-model="dec.moneda" required class="w-full text-xs rounded-sm border-slate-300">
                                                            @foreach($currencies as $c)
                                                                <option value="{{ $c['code'] }}">{{ $c['code'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                                        <input type="number" step="0.01" :name="`manifestaciones[${mvIndex}][decrementables][${decIndex}][importe]`" x-model="dec.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">T. Cambio</label>
                                                        <input type="number" step="0.0001" :name="`manifestaciones[${mvIndex}][decrementables][${decIndex}][tipo_cambio]`" x-model="dec.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                                    </div>
                                                    <div class="md:col-span-1 flex justify-center pb-1">
                                                        <button type="button" @click="mv.decrementables.splice(decIndex, 1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                </div>
                            </template>

                            <!-- Mensaje cuando no hay MVs -->
                            <div x-show="manifestaciones.length === 0" class="text-center py-12 text-slate-400 italic bg-slate-50 border-2 border-dashed border-slate-300 rounded-lg">
                                <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-bold mb-2">No hay manifestaciones de valor agregadas</p>
                                <p class="text-sm">Haga clic en "Añadir MV" para comenzar.</p>
                            </div>
                        </div>

                        <!-- BOTONES DE NAVEGACIÓN -->
                        <div class="flex justify-between items-center mt-10 pt-6 border-t border-slate-200">
                            <div class="flex items-center">
                                <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-red-700 font-bold text-sm px-4 py-2 mr-4 transition uppercase tracking-wider">Cancelar</a>
                                <a href="{{ route('manifestations.step1', $manifestation->uuid) }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-sm font-bold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 hover:text-slate-900 transition">
                                    &larr; Anterior
                                </a>
                            </div>
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
        function mvHandler() {
            return {
                manifestaciones: [],
                
                init() {
                    // Cargar datos existentes si hay
                    const existingData = @json($manifestation->manifestaciones ?? []);
                    if (existingData && existingData.length > 0) {
                        this.manifestaciones = existingData;
                    }
                },
                
                addMV() {
                    this.manifestaciones.push({
                        pedimentos: [{ numero_pedimento: '', patente: '', aduana_clave: '430' }],
                        incrementables: [],
                        decrementables: []
                    });
                },
                
                removeMV(index) {
                    if (confirm('¿Está seguro de eliminar esta Manifestación de Valor?')) {
                        this.manifestaciones.splice(index, 1);
                    }
                },
                
                addPedimento(mvIndex) {
                    this.manifestaciones[mvIndex].pedimentos.push({ 
                        numero_pedimento: '', 
                        patente: '', 
                        aduana_clave: '430' 
                    });
                },
                
                addIncrementable(mvIndex) {
                    this.manifestaciones[mvIndex].incrementables.push({ 
                        concepto: '', 
                        importe: 0, 
                        moneda: 'USD', 
                        tipo_cambio: 20.0000, 
                        fecha_erogacion: '' 
                    });
                },
                
                addDecrementable(mvIndex) {
                    this.manifestaciones[mvIndex].decrementables.push({ 
                        concepto: '', 
                        importe: 0, 
                        moneda: 'USD', 
                        tipo_cambio: 20.0000, 
                        fecha_erogacion: '' 
                    });
                }
            }
        }
    </script>
</x-app-layout>
