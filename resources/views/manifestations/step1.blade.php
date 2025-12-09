<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva Manifestación de Valor') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="step1Handler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- STEPPER -->
            <div class="mb-8">
                <div class="flex items-center justify-between w-full">
                    <div class="flex flex-col items-center w-1/4">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold shadow-lg ring-4 ring-indigo-100 z-10">1</div>
                        <div class="text-xs font-bold mt-2 text-indigo-700">Generales</div>
                    </div>
                    <div class="flex-auto border-t-4 border-gray-200 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4 opacity-50"><div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold z-10">2</div></div>
                    <div class="flex-auto border-t-4 border-gray-200 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4 opacity-50"><div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold z-10">3</div></div>
                    <div class="flex-auto border-t-4 border-gray-200 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4 opacity-50"><div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold z-10">4</div></div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-t-4 border-indigo-600">
                <div class="p-8 text-gray-900">
                    <form method="POST" action="{{ route('manifestations.store') }}">
                        @csrf

                        <!-- SECCIÓN 1: SOLICITANTE -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-100 relative">
                            <span class="absolute top-0 left-0 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-br-lg rounded-tl-lg">SOLICITANTE</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 mt-4">
                                <div>
                                    <x-input-label for="curp_solicitante" :value="__('CURP')" class="font-bold text-gray-600" />
                                    <x-text-input id="curp_solicitante" class="block mt-1 w-full uppercase" type="text" name="curp_solicitante" :value="old('curp_solicitante')" required maxlength="18" />
                                </div>
                                <div>
                                    <x-input-label for="rfc_solicitante" :value="__('RFC')" class="font-bold text-gray-600" />
                                    <x-text-input id="rfc_solicitante" class="block mt-1 w-full uppercase" type="text" name="rfc_solicitante" :value="old('rfc_solicitante')" required maxlength="13" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div><x-input-label for="nombre" :value="__('Nombre(s)')" /><x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required /></div>
                                <div><x-input-label for="apellido_paterno" :value="__('Apellido Paterno')" /><x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required /></div>
                                <div><x-input-label for="apellido_materno" :value="__('Apellido Materno')" /><x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" /></div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: IMPORTADOR -->
                        <div class="bg-blue-50 p-6 rounded-lg mb-8 border border-blue-100 relative">
                            <span class="absolute top-0 left-0 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-br-lg rounded-tl-lg">IMPORTADOR</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 mt-4">
                                <div>
                                    <x-input-label for="rfc_importador" :value="__('RFC Importador')" class="font-bold text-gray-600" />
                                    <input id="rfc_importador" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm uppercase" type="text" name="rfc_importador" x-model="form.rfc_importador" @input="buscarRazonSocial()" required maxlength="13" placeholder="XAXX010101000" />
                                    <p class="text-xs text-blue-600 mt-2 font-medium" x-text="mensajeAyuda"></p>
                                </div>
                                <div>
                                    <x-input-label for="razon_social_importador" :value="__('Razón Social')" class="font-bold text-gray-600" />
                                    <input id="razon_social_importador" class="block mt-1 w-full bg-gray-100 cursor-not-allowed border-gray-300 rounded-md shadow-sm" type="text" name="razon_social_importador" x-model="form.razon_social_importador" readonly required />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="registro_nacional_contribuyentes" :value="__('Registro Nacional de Contribuyentes')" />
                                    <input id="registro_nacional_contribuyentes" class="block mt-1 w-full bg-gray-100 cursor-not-allowed border-gray-300 rounded-md shadow-sm" type="text" name="registro_nacional_contribuyentes" x-model="form.registro_nacional" readonly />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8 border-t pt-6">
                             <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-700 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-800 transition ease-in-out duration-150 shadow-lg">
                                Guardar y Continuar &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function step1Handler() {
            return {
                mensajeAyuda: 'Ingrese el RFC para buscar la Razón Social automáticamente.',
                form: {
                    rfc_importador: '{{ old("rfc_importador") }}',
                    razon_social_importador: '{{ old("razon_social_importador") }}',
                    registro_nacional: '{{ old("registro_nacional_contribuyentes") }}'
                },
                buscarRazonSocial() {
                    const rfc = this.form.rfc_importador.toUpperCase();
                    if (rfc.length >= 12) {
                        this.mensajeAyuda = '✓ RFC encontrado en el padrón.';
                        if (!this.form.razon_social_importador) {
                            this.form.razon_social_importador = "IMPORTADORA EJEMPLO DE MEXICO S.A. DE C.V.";
                            this.form.registro_nacional = "RNC-" + Math.floor(Math.random() * 999999);
                        }
                    } else {
                        this.mensajeAyuda = 'Buscando...';
                        this.form.razon_social_importador = '';
                        this.form.registro_nacional = '';
                    }
                }
            }
        }
    </script>
</x-app-layout>