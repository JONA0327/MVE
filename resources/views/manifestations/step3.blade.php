<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Detalle de la Manifestación
        </h2>
    </x-slot>

    <div class="py-12" x-data="detalleHandler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
             <!-- STEPPER E&I -->
             <div class="mb-10">
                 <div class="flex items-center justify-between w-full">
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-900 text-white font-bold text-sm shadow-sm">✓</div>
                        <div class="text-xs font-medium mt-2 text-blue-900 uppercase">Generales</div>
                    </div>
                    <div class="flex-auto border-t-4 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-900 text-white font-bold text-sm shadow-sm">✓</div>
                        <div class="text-xs font-medium mt-2 text-blue-900 uppercase">Valores</div>
                    </div>
                    <div class="flex-auto border-t-4 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-900 text-white font-bold shadow-lg ring-4 ring-blue-50 z-10 transform scale-110">3</div>
                        <div class="text-xs font-bold mt-2 text-blue-900 uppercase tracking-wide">Detalles</div>
                    </div>
                    <div class="flex-auto border-t-4 border-gray-200"></div>
                    <div class="flex flex-col items-center w-1/4 opacity-40 grayscale">
                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold z-10">4</div>
                        <div class="text-xs font-medium mt-2 text-gray-500">Firma</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('manifestations.updateStep3', $manifestation->uuid) }}">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded text-red-700 font-bold">
                        Por favor revise los campos obligatorios marcados.
                    </div>
                @endif

                <!-- 1. GENERALES DEL TRÁMITE -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6 border-t-4 border-blue-900">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">1. Método e Incoterm</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label value="Método de Valoración" class="font-bold text-slate-700 required" />
                                <select name="metodo_valoracion_global" required class="w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-blue-900 focus:ring-blue-900 text-slate-700" x-model="general.metodo">
                                    <option value="1">Valor de Transacción</option>
                                    <option value="2">Valor de Transacción Bienes Idénticos</option>
                                    <option value="3">Valor de Transacción Bienes Similares</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label value="INCOTERM" class="font-bold text-slate-700 required" />
                                <select name="incoterm" required class="w-full mt-1 border-slate-300 rounded-md shadow-sm focus:border-blue-900 focus:ring-blue-900 text-slate-700" x-model="general.incoterm">
                                    <option value="FOB">FOB</option>
                                    <option value="CIF">CIF</option>
                                    <option value="EXW">EXW</option>
                                    <option value="DDP">DDP</option>
                                </select>
                            </div>
                            <div class="bg-slate-50 p-3 rounded border border-slate-200">
                                <span class="text-sm font-bold text-slate-700 block mb-2 required">¿Existe Vinculación?</span>
                                <div class="flex items-center space-x-6">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="existe_vinculacion" value="1" required x-model="general.vinculacion" class="text-blue-900 focus:ring-blue-900 h-4 w-4">
                                        <span class="ml-2 text-slate-700">Sí</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="existe_vinculacion" value="0" required x-model="general.vinculacion" class="text-blue-900 focus:ring-blue-900 h-4 w-4">
                                        <span class="ml-2 text-slate-700">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PEDIMENTOS (RESTRICTED TO ONE) -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-800">2. Pedimento Asociado</h3>
                            <span class="text-xs bg-slate-100 text-slate-500 px-2 py-1 rounded">Registro Único</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg border border-slate-200 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-100 text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th class="p-3 text-left">Número Pedimento <span class="text-red-500">*</span></th>
                                        <th class="p-3 text-left">Patente <span class="text-red-500">*</span></th>
                                        <th class="p-3 text-left">Aduana <span class="text-red-500">*</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Iteramos, pero sabemos que solo habrá 1 elemento siempre -->
                                    <template x-for="(ped, i) in pedimentos" :key="i">
                                        <tr class="border-b border-slate-100 last:border-0 hover:bg-white transition">
                                            <td class="p-2">
                                                <!-- Campo con máscara de espaciado -->
                                                <input type="text" 
                                                       :name="`pedimentos[${i}][numero_pedimento]`" 
                                                       x-model="ped.numero_pedimento" 
                                                       @input="formatPedimento(ped)" 
                                                       required 
                                                       maxlength="21" 
                                                       placeholder="XX  XXX  XXXX  XXXXXXX"
                                                       class="w-full text-sm rounded border-slate-300 focus:ring-blue-900 font-mono">
                                            </td>
                                            <td class="p-2"><input type="text" :name="`pedimentos[${i}][patente]`" x-model="ped.patente" required class="w-full text-sm rounded border-slate-300 focus:ring-blue-900"></td>
                                            <td class="p-2">
                                                <select :name="`pedimentos[${i}][aduana_clave]`" x-model="ped.aduana_clave" required class="w-full text-sm rounded border-slate-300 focus:ring-blue-900">
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
                </div>

                <!-- 3. INCREMENTABLES -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6 border-l-4 border-blue-500">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-800">3. Incrementables</h3>
                            <button type="button" @click="addIncrementable()" class="text-xs bg-blue-700 hover:bg-blue-800 text-white px-3 py-2 rounded shadow transition font-bold">
                                + Agregar Concepto
                            </button>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(inc, i) in incrementables" :key="i">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-blue-50 p-3 rounded-md border border-blue-100 shadow-sm relative">
                                    <div class="md:col-span-3">
                                        <label class="text-xs text-blue-900 font-bold block mb-1 required">Concepto</label>
                                        <select :name="`incrementables[${i}][concepto]`" x-model="inc.concepto" required class="w-full text-xs rounded border-slate-300 focus:ring-blue-900 focus:border-blue-900">
                                            <option value="">Seleccione...</option>
                                            <option value="Fletes">Fletes</option>
                                            <option value="Seguros">Seguros</option>
                                            <option value="Embalajes">Embalajes</option>
                                            <option value="Otros Incrementables">Otros</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-slate-600 block mb-1 required">Fecha Erogación</label>
                                        <input type="date" :name="`incrementables[${i}][fecha_erogacion]`" x-model="inc.fecha_erogacion" @change="updateTC(inc)" required class="w-full text-xs rounded border-slate-300 focus:ring-blue-900 focus:border-blue-900">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-slate-600 block mb-1 required">Moneda</label>
                                        <select :name="`incrementables[${i}][moneda]`" x-model="inc.moneda" @change="updateTC(inc)" required class="w-full text-xs rounded border-slate-300 focus:ring-blue-900 focus:border-blue-900">
                                            <option value="MXN">MXN</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-slate-600 block mb-1 required">Importe</label>
                                        <input type="number" step="0.01" :name="`incrementables[${i}][importe]`" x-model="inc.importe" required class="w-full text-xs rounded border-slate-300 focus:ring-blue-900 focus:border-blue-900">
                                    </div>
                                    <div class="md:col-span-2 relative">
                                        <label class="text-xs text-slate-600 block mb-1">T. Cambio</label>
                                        <input type="number" step="0.0001" :name="`incrementables[${i}][tipo_cambio]`" x-model="inc.tipo_cambio" class="w-full text-xs rounded border-slate-300 focus:ring-blue-900 bg-slate-50 text-slate-500" readonly>
                                        <div x-show="inc.loading" class="absolute right-2 top-8 text-xs text-blue-600 animate-spin">↻</div>
                                    </div>
                                    <div class="md:col-span-1 flex justify-center pb-2">
                                        <button type="button" @click="incrementables.splice(i,1)" class="text-red-500 hover:text-red-700 font-bold p-1 rounded hover:bg-red-50 transition">✕</button>
                                    </div>
                                    <div class="md:col-span-12 flex items-center mt-1">
                                        <input type="checkbox" value="1" :name="`incrementables[${i}][a_cargo_importador]`" x-model="inc.a_cargo_importador" class="rounded border-slate-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                        <span class="ml-2 text-xs text-slate-700 font-medium">¿Gasto a cargo del importador?</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 4. DECREMENTABLES -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6 border-l-4 border-slate-500">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-800">4. Decrementables</h3>
                            <button type="button" @click="addDecrementable()" class="text-xs bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 rounded shadow transition font-bold">
                                + Agregar Concepto
                            </button>
                        </div>
                        <div class="space-y-4">
                            <template x-for="(dec, i) in decrementables" :key="i">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-slate-50 p-3 rounded-md border border-slate-200 shadow-sm">
                                    <div class="md:col-span-3">
                                        <label class="text-xs text-slate-800 font-bold block mb-1 required">Concepto</label>
                                        <select :name="`decrementables[${i}][concepto]`" x-model="dec.concepto" required class="w-full text-xs rounded border-slate-300 focus:ring-slate-500 focus:border-slate-500">
                                            <option value="">Seleccione...</option>
                                            <option value="Transporte posterior">Transporte posterior</option>
                                            <option value="Construccion/Instalacion">Construcción</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-slate-600 block mb-1 required">Fecha Erogación</label>
                                        <input type="date" :name="`decrementables[${i}][fecha_erogacion]`" x-model="dec.fecha_erogacion" @change="updateTC(dec)" required class="w-full text-xs rounded border-slate-300">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-slate-600 block mb-1 required">Moneda</label>
                                        <select :name="`decrementables[${i}][moneda]`" x-model="dec.moneda" @change="updateTC(dec)" required class="w-full text-xs rounded border-slate-300">
                                            <option value="MXN">MXN</option>
                                            <option value="USD">USD</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-slate-600 block mb-1 required">Importe</label>
                                        <input type="number" step="0.01" :name="`decrementables[${i}][importe]`" x-model="dec.importe" required class="w-full text-xs rounded border-slate-300">
                                    </div>
                                    <div class="md:col-span-2 relative">
                                        <label class="text-xs text-slate-600 block mb-1">T. Cambio</label>
                                        <input type="number" step="0.0001" :name="`decrementables[${i}][tipo_cambio]`" x-model="dec.tipo_cambio" class="w-full text-xs rounded border-slate-300 bg-slate-100" readonly>
                                        <div x-show="dec.loading" class="absolute right-2 top-8 text-xs text-slate-500 animate-spin">↻</div>
                                    </div>
                                    <div class="md:col-span-1 flex justify-center pb-2">
                                        <button type="button" @click="decrementables.splice(i,1)" class="text-red-500 hover:text-red-700 font-bold p-1 rounded hover:bg-slate-200 transition">✕</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 5. PAGOS -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6 border-l-4 border-slate-800">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-slate-800">5. Precio Pagado / Por Pagar</h3>
                            <button type="button" @click="addPago()" class="text-xs bg-slate-800 text-white px-3 py-2 rounded font-bold hover:bg-slate-900 transition">+ Agregar Pago</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead class="bg-slate-100 text-slate-600 font-bold">
                                    <tr>
                                        <th class="border border-slate-200 p-2 text-left">Estatus <span class="text-red-500">*</span></th>
                                        <th class="border border-slate-200 p-2 text-left w-32">Fecha Pago <span class="text-red-500">*</span></th>
                                        <th class="border border-slate-200 p-2 text-left">Importe <span class="text-red-500">*</span></th>
                                        <th class="border border-slate-200 p-2 text-left w-24">Moneda <span class="text-red-500">*</span></th>
                                        <th class="border border-slate-200 p-2 text-left">T.C.</th>
                                        <th class="border border-slate-200 p-2 text-left">Forma Pago <span class="text-red-500">*</span></th>
                                        <th class="border border-slate-200 p-2 text-center">X</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(pago, i) in pagos" :key="i">
                                        <tr>
                                            <td class="border border-slate-200 p-1">
                                                <select :name="`pagos[${i}][status]`" x-model="pago.status" required class="w-full text-xs border-transparent focus:ring-0">
                                                    <option value="paid">Pagado</option>
                                                    <option value="payable">Por Pagar</option>
                                                </select>
                                            </td>
                                            <td class="border border-slate-200 p-1">
                                                <input type="date" :name="`pagos[${i}][fecha]`" x-model="pago.fecha" @change="updateTC(pago)" required class="w-full text-xs border-slate-300 rounded">
                                            </td>
                                            <td class="border border-slate-200 p-1">
                                                <input type="number" step="0.01" :name="`pagos[${i}][importe]`" x-model="pago.importe" required class="w-full text-xs border-slate-300 rounded">
                                            </td>
                                            <td class="border border-slate-200 p-1">
                                                <select :name="`pagos[${i}][moneda]`" x-model="pago.moneda" @change="updateTC(pago)" required class="w-full text-xs border-slate-300 rounded">
                                                    <option value="USD">USD</option>
                                                    <option value="MXN">MXN</option>
                                                </select>
                                            </td>
                                            <td class="border border-slate-200 p-1">
                                                <input type="number" step="0.0001" :name="`pagos[${i}][tipo_cambio]`" x-model="pago.tipo_cambio" class="w-full text-xs border-slate-300 bg-slate-50 rounded" readonly>
                                            </td>
                                            <td class="border border-slate-200 p-1">
                                                <input type="text" :name="`pagos[${i}][forma_pago]`" x-model="pago.forma_pago" required class="w-full text-xs border-slate-300 rounded" placeholder="Transferencia...">
                                            </td>
                                            <td class="border border-slate-200 p-1 text-center">
                                                <button type="button" @click="pagos.splice(i,1)" class="text-red-500 font-bold hover:text-red-700">✕</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- BOTÓN FINAL -->
                <div class="flex justify-end mt-8 pb-12">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-slate-900 border border-transparent rounded-md font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-900 shadow-lg transform hover:-translate-y-0.5 transition">
                        Guardar y Continuar a Firma &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function detalleHandler() {
            return {
                // Obtenemos los datos, si no hay pedimentos, inicializamos con uno vacío (siempre debe haber 1)
                pedimentos: @json($manifestation->pedimentos ?? []) .length ? @json($manifestation->pedimentos) : [{numero_pedimento: '', patente: '', aduana_clave: '430'}],
                incrementables: @json($incrementables ?? []),
                decrementables: @json($decrementables ?? []),
                pagos: @json($manifestation->payments ?? []),

                addIncrementable() { 
                    if (this.incrementables.length > 0) {
                        const last = this.incrementables[this.incrementables.length - 1];
                        if (!last.concepto || !last.fecha_erogacion || parseFloat(last.importe) <= 0) {
                            return alert('Por favor complete los datos del incrementable actual antes de agregar otro.');
                        }
                    }
                    this.incrementables.push({ concepto: '', importe: 0, moneda: 'USD', tipo_cambio: 20.0000, a_cargo_importador: false, fecha_erogacion: '', loading: false }); 
                },
                
                addDecrementable() { 
                    if (this.decrementables.length > 0) {
                        const last = this.decrementables[this.decrementables.length - 1];
                        if (!last.concepto || !last.fecha_erogacion || parseFloat(last.importe) <= 0) {
                            return alert('Por favor complete los datos del decrementable actual antes de agregar otro.');
                        }
                    }
                    this.decrementables.push({ concepto: '', importe: 0, moneda: 'USD', tipo_cambio: 20.0000, fecha_erogacion: '', loading: false }); 
                },
                
                addPago() { 
                    if (this.pagos.length > 0) {
                        const last = this.pagos[this.pagos.length - 1];
                        if (!last.fecha || parseFloat(last.importe) <= 0 || !last.forma_pago) {
                            return alert('Por favor complete los datos del pago actual antes de agregar otro.');
                        }
                    }
                    this.pagos.push({ status: 'paid', fecha: '', importe: 0, moneda: 'USD', tipo_cambio: 20.0000, forma_pago: 'Transferencia', situacion_pago: '', loading: false }); 
                },

                // Lógica de formateo del pedimento (2-3-4-rest)
                formatPedimento(ped) {
                    let val = ped.numero_pedimento.replace(/\D/g, ''); // Limpiar no numéricos
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