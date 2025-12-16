<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ isset($manifestation) ? __('Editar Manifestación de Valor') : __('Nueva Manifestación de Valor') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="step1Handler()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-10">
                <div class="flex items-center justify-between w-full opacity-90">
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">Generales</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Valores</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Detalles</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Archivos</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Resumen</div></div>
                </div>
            </div>

            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300">
                
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300">
                    <h1 class="text-lg font-bold text-slate-900 uppercase">1. Información General</h1>
                    <p class="text-xs text-slate-500">Ingrese los datos del solicitante y del importador.</p>
                </div>

                <div class="p-10 text-slate-800">

                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="bg-blue-100 p-2 rounded-full text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-blue-900 text-sm uppercase">Autocompletar con Archivo M</h4>
                                <p class="text-xs text-blue-700 mt-1">Si cuenta con el archivo de validación (M), cárguelo aquí para llenar los datos del importador automáticamente.</p>
                            </div>
                        </div>
                        <div>
                            <label for="m_file_input" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-blue-300 rounded shadow-sm text-xs font-bold text-blue-700 uppercase tracking-widest hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Subir Archivo
                            </label>
                            <input type="file" id="m_file_input" class="hidden" accept=".325,.txt" @change="uploadMFile($event)">
                            <div x-show="isUploading" class="text-center mt-2" style="display: none;">
                                <span class="text-xs text-blue-600 font-bold animate-pulse">Procesando...</span>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ isset($manifestation) ? route('manifestations.updateStep1', $manifestation->uuid) : route('manifestations.store') }}">
                        @csrf
                        @if(isset($manifestation))
                            @method('PUT')
                        @endif

                        @if ($errors->any())
                            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
                                <h3 class="text-sm font-bold text-red-800 uppercase mb-1">Errores detectados</h3>
                                <ul class="list-disc list-inside text-xs text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Datos del Solicitante</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <x-input-label for="curp_solicitante" :value="__('CURP')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="curp_solicitante" class="block w-full uppercase text-sm border-slate-300 focus:border-blue-900 focus:ring-blue-900 rounded-sm shadow-sm bg-slate-50" type="text" name="curp_solicitante" 
                                        :value="old('curp_solicitante', $manifestation->curp_solicitante ?? '')" required maxlength="18" />
                                    <x-input-error :messages="$errors->get('curp_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="rfc_solicitante" :value="__('RFC')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="rfc_solicitante" class="block w-full uppercase text-sm border-slate-300 focus:border-blue-900 focus:ring-blue-900 rounded-sm shadow-sm bg-slate-50" type="text" name="rfc_solicitante" 
                                        :value="old('rfc_solicitante', $manifestation->rfc_solicitante ?? '')" required maxlength="13" />
                                    <x-input-error :messages="$errors->get('rfc_solicitante')" class="mt-1" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="nombre" :value="__('Nombre(s)')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="nombre" class="block w-full text-sm border-slate-300 focus:border-blue-900 focus:ring-blue-900 rounded-sm shadow-sm" type="text" name="nombre" 
                                        :value="old('nombre', $manifestation->nombre ?? '')" required />
                                </div>
                                <div>
                                    <x-input-label for="apellido_paterno" :value="__('Apellido Paterno')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="apellido_paterno" class="block w-full text-sm border-slate-300 focus:border-blue-900 focus:ring-blue-900 rounded-sm shadow-sm" type="text" name="apellido_paterno" 
                                        :value="old('apellido_paterno', $manifestation->apellido_paterno ?? '')" required />
                                </div>
                                <div>
                                    <x-input-label for="apellido_materno" :value="__('Apellido Materno')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="apellido_materno" class="block w-full text-sm border-slate-300 focus:border-blue-900 focus:ring-blue-900 rounded-sm shadow-sm" type="text" name="apellido_materno" 
                                        :value="old('apellido_materno', $manifestation->apellido_materno ?? '')" required />
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Datos del Importador</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <x-input-label for="rfc_importador" :value="__('RFC Importador')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <input id="rfc_importador" class="block w-full text-sm border-slate-300 focus:border-blue-900 focus:ring-blue-900 rounded-sm shadow-sm bg-slate-50 uppercase" type="text" name="rfc_importador" x-model="form.rfc_importador" @input="buscarRazonSocial()" required maxlength="13" />
                                    <p class="text-xs text-blue-600 mt-2 font-bold flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span x-text="mensajeAyuda"></span>
                                    </p>
                                </div>
                                <div>
                                    <x-input-label for="razon_social_importador" :value="__('Razón Social')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <input id="razon_social_importador" class="block w-full bg-slate-100 text-slate-500 border-slate-300 rounded-sm shadow-sm font-bold text-sm cursor-not-allowed" type="text" name="razon_social_importador" x-model="form.razon_social_importador" readonly required />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="registro_nacional_contribuyentes" :value="__('Registro Nacional de Contribuyentes (o Tax ID)')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <input id="registro_nacional_contribuyentes" class="block w-full bg-slate-100 text-slate-500 border-slate-300 rounded-sm shadow-sm font-bold text-sm cursor-not-allowed" type="text" name="registro_nacional_contribuyentes" x-model="form.registro_nacional" readonly required />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-10 pt-6 border-t border-slate-200">
                             <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-red-700 font-bold text-sm px-4 py-2 transition flex items-center uppercase tracking-wider">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cancelar
                            </a>
                            
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-slate-900 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-900 shadow-md transform hover:-translate-y-0.5 transition">
                                {{ isset($manifestation) ? 'Actualizar y Continuar →' : 'Guardar y Continuar →' }}
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
                isUploading: false,
                mensajeAyuda: 'Ingrese el RFC.',
                form: {
                    rfc_importador: '{{ old("rfc_importador", $manifestation->rfc_importador ?? "") }}',
                    razon_social_importador: '{{ old("razon_social_importador", $manifestation->razon_social_importador ?? "") }}',
                    registro_nacional: '{{ old("registro_nacional_contribuyentes", $manifestation->registro_nacional_contribuyentes ?? "") }}'
                },
                init() {
                    if(this.form.rfc_importador) {
                        this.buscarRazonSocial();
                    }
                },
                buscarRazonSocial() {
                    const rfc = this.form.rfc_importador.toUpperCase();
                    if (rfc.length >= 12) {
                        this.mensajeAyuda = 'RFC válido.';
                        if (!this.form.razon_social_importador) {
                            // Solo simulamos si no viene del archivo M y no hay nada escrito
                            // Idealmente aquí conectarías con una API de validación real si la tienes
                        }
                    } else {
                        this.mensajeAyuda = 'Escribiendo...';
                    }
                },
                async uploadMFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.isUploading = true;
                    const formData = new FormData();
                    formData.append('m_file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await fetch('{{ route("manifestations.parseMFile") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Actualizar campos con los datos parseados
                            if(result.data.rfc_importador) {
                                this.form.rfc_importador = result.data.rfc_importador;
                            }
                            if(result.data.razon_social_importador) {
                                this.form.razon_social_importador = result.data.razon_social_importador;
                            }
                            
                            // Generar RNC dummy si no existe
                            if(!this.form.registro_nacional && this.form.rfc_importador) {
                                this.form.registro_nacional = "RNC-GENERICO"; 
                            }

                            // Guardar todos los datos del archivo M en sessionStorage para los siguientes pasos
                            sessionStorage.setItem('mFileData', JSON.stringify(result.data));

                            this.buscarRazonSocial();
                            
                            // Resumen de datos cargados
                            const summary = [];
                            if (result.data.rfc_importador) summary.push(`✓ RFC: ${result.data.rfc_importador}`);
                            if (result.data.razon_social_importador) summary.push(`✓ Razón Social: ${result.data.razon_social_importador}`);
                            if (result.data.coves && result.data.coves.length > 0) summary.push(`✓ ${result.data.coves.length} COVE(s)`);
                            if (result.data.pedimentos && result.data.pedimentos.length > 0) summary.push(`✓ ${result.data.pedimentos.length} Pedimento(s)`);
                            if (result.data.incoterm) summary.push(`✓ Incoterm: ${result.data.incoterm}`);
                            
                            alert('✅ Archivo M procesado exitosamente\n\nDatos importados:\n' + summary.join('\n') + '\n\nLos COVES y pedimentos se cargarán automáticamente en los siguientes pasos.');
                        } else {
                            alert('Error al leer el archivo: ' + (result.message || 'Formato desconocido'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Ocurrió un error al procesar el archivo.');
                    } finally {
                        this.isUploading = false;
                        event.target.value = '';
                    }
                }
            }
        }
    </script>
</x-app-layout>