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
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">PASO 1</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">PASO 2</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">PASO 3</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">PASO 4</div></div>
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

                    <!-- TABS DE NAVEGACIÓN -->
                    <div class="border-b border-slate-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button type="button" @click="activeTab = 'solicitante'" 
                                :class="activeTab === 'solicitante' ? 'border-blue-900 text-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Solicitante
                            </button>
                            <button type="button" @click="activeTab = 'manifestacion'" 
                                :class="activeTab === 'manifestacion' ? 'border-blue-900 text-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Manifestación
                            </button>
                            <button type="button" @click="activeTab = 'coves'" 
                                :class="activeTab === 'coves' ? 'border-blue-900 text-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0M15 17a2 2 0 104 0M9 17h6"></path>
                                </svg>
                                COVEs
                            </button>
                            <button type="button" @click="activeTab = 'valor'" 
                                :class="activeTab === 'valor' ? 'border-blue-900 text-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-wider transition">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                Valor Aduanal
                            </button>
                        </nav>
                    </div>

                    <!-- TAB: SOLICITANTE -->
                    <form method="POST" action="{{ isset($manifestation) ? route('manifestations.updateStep1', $manifestation->uuid) : route('manifestations.store') }}">
                        @csrf
                        @if(isset($manifestation))
                            @method('PUT')
                        @endif

                        <div x-show="activeTab === 'solicitante'" x-transition>
                                Manifestación de Valor
                            </button>
                        </nav>
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

                        <!-- TAB: SOLICITANTE -->
                        <div x-show="activeTab === 'solicitante'" x-transition>
                            
                            @if(!Auth::user()->hasCompleteSolicitorProfile())
                            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <p class="text-sm text-yellow-800 font-bold">⚠️ Complete la información del solicitante</p>
                                <p class="text-xs text-yellow-700 mt-1">Esta información es requerida para crear manifestaciones. Puede completarla aquí o en su <a href="{{ route('profile.edit') }}" class="underline font-bold">perfil de usuario</a>.</p>
                            </div>
                            @endif

                            <!-- Validación de datos completos -->
                            <div x-show="!datosCompletos" class="mb-6 bg-yellow-50 border-2 border-yellow-300 text-yellow-800 px-6 py-4 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 mr-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <div>
                                        <h4 class="font-bold text-sm">Tus datos están incompletos</h4>
                                        <p class="text-sm mt-1">Completa tus datos en el perfil para poder crear manifestaciones.</p>
                                        <a href="{{ route('profile.edit') }}" class="inline-block mt-3 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-bold py-2 px-4 rounded transition">
                                            Ir a Perfil
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Datos del Solicitante</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <x-input-label for="rfc_solicitante" value="RFC del Solicitante" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="rfc_solicitante" class="block w-full uppercase text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="rfc_solicitante" 
                                        x-model="form.rfc_solicitante" @input="copiarRfcSolicitanteAImportador()"
                                        :value="old('rfc_solicitante', $manifestation->rfc_solicitante ?? Auth::user()->rfc ?? '')" readonly required minlength="12" maxlength="13" />
                                    <x-input-error :messages="$errors->get('rfc_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="razon_social_solicitante" value="Razón Social" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="razon_social_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="razon_social_solicitante" 
                                        :value="old('razon_social_solicitante', $manifestation->razon_social_solicitante ?? Auth::user()->razon_social ?? '')" readonly required />
                                    <x-input-error :messages="$errors->get('razon_social_solicitante')" class="mt-1" />
                                </div>
                            </div>

                            <div class="mb-6">
                                <x-input-label for="actividad_economica_solicitante" value="Actividad Económica Preponderante" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                <textarea id="actividad_economica_solicitante" name="actividad_economica_solicitante" rows="2" required readonly
                                    class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm">{{ old('actividad_economica_solicitante', $manifestation->actividad_economica_solicitante ?? Auth::user()->actividad_economica ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('actividad_economica_solicitante')" class="mt-1" />
                            </div>

                            <h4 class="text-xs font-bold text-blue-900 uppercase border-b border-blue-900 mb-4 pb-1">Domicilio Fiscal del Solicitante</h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <x-input-label for="pais_solicitante" value="País" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="pais_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="pais_solicitante" 
                                        :value="old('pais_solicitante', $manifestation->pais_solicitante ?? Auth::user()->pais ?? 'ESTADOS UNIDOS MEXICANOS')" readonly required />
                                    <x-input-error :messages="$errors->get('pais_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="codigo_postal_solicitante" value="Código Postal" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="codigo_postal_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="codigo_postal_solicitante" 
                                        :value="old('codigo_postal_solicitante', $manifestation->codigo_postal_solicitante ?? Auth::user()->codigo_postal ?? '')" readonly required maxlength="10" />
                                    <x-input-error :messages="$errors->get('codigo_postal_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="estado_solicitante" value="Estado" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="estado_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="estado_solicitante" 
                                        :value="old('estado_solicitante', $manifestation->estado_solicitante ?? Auth::user()->estado ?? '')" readonly required />
                                    <x-input-error :messages="$errors->get('estado_solicitante')" class="mt-1" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <x-input-label for="municipio_solicitante" value="Municipio" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="municipio_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="municipio_solicitante" 
                                        :value="old('municipio_solicitante', $manifestation->municipio_solicitante ?? Auth::user()->municipio ?? '')" readonly required />
                                    <x-input-error :messages="$errors->get('municipio_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="localidad_solicitante" value="Localidad" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                                    <x-text-input id="localidad_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="localidad_solicitante" 
                                        :value="old('localidad_solicitante', $manifestation->localidad_solicitante ?? Auth::user()->localidad ?? '')" readonly />
                                    <x-input-error :messages="$errors->get('localidad_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="colonia_solicitante" value="Colonia" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="colonia_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="colonia_solicitante" 
                                        :value="old('colonia_solicitante', $manifestation->colonia_solicitante ?? Auth::user()->colonia ?? '')" readonly required />
                                    <x-input-error :messages="$errors->get('colonia_solicitante')" class="mt-1" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <x-input-label for="calle_solicitante" value="Calle" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="calle_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="calle_solicitante" 
                                        :value="old('calle_solicitante', $manifestation->calle_solicitante ?? Auth::user()->calle ?? '')" readonly required />
                                    <x-input-error :messages="$errors->get('calle_solicitante')" class="mt-1" />
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="numero_exterior_solicitante" value="No. Exterior" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                        <x-text-input id="numero_exterior_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="numero_exterior_solicitante" 
                                            :value="old('numero_exterior_solicitante', $manifestation->numero_exterior_solicitante ?? Auth::user()->numero_exterior ?? '')" readonly required />
                                        <x-input-error :messages="$errors->get('numero_exterior_solicitante')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label for="numero_interior_solicitante" value="No. Interior" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                                        <x-text-input id="numero_interior_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="numero_interior_solicitante" 
                                            :value="old('numero_interior_solicitante', $manifestation->numero_interior_solicitante ?? Auth::user()->numero_interior ?? '')" readonly />
                                        <x-input-error :messages="$errors->get('numero_interior_solicitante')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <h4 class="text-xs font-bold text-blue-900 uppercase border-b border-blue-900 mb-4 pb-1">Datos de Contacto</h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="lada_solicitante" value="Lada" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="lada_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="lada_solicitante" 
                                        :value="old('lada_solicitante', $manifestation->lada_solicitante ?? Auth::user()->lada ?? '')" readonly required maxlength="5" placeholder="444" />
                                    <x-input-error :messages="$errors->get('lada_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="telefono_solicitante" value="Teléfono" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="telefono_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="text" name="telefono_solicitante" 
                                        :value="old('telefono_solicitante', $manifestation->telefono_solicitante ?? Auth::user()->telefono ?? '')" readonly required maxlength="20" placeholder="4115100" />
                                    <x-input-error :messages="$errors->get('telefono_solicitante')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="correo_solicitante" value="Correo Electrónico" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <x-text-input id="correo_solicitante" class="block w-full text-sm bg-gray-50 border-gray-300 text-gray-600 cursor-not-allowed rounded-sm shadow-sm" type="email" name="correo_solicitante" 
                                        :value="old('correo_solicitante', $manifestation->correo_solicitante ?? Auth::user()->email ?? '')" readonly required />
                                    <x-input-error :messages="$errors->get('correo_solicitante')" class="mt-1" />
                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB: SOLICITANTE -->

                        <!-- TAB: MANIFESTACIÓN DE VALOR -->
                        <div x-show="activeTab === 'manifestacion'" x-transition>
                            
                            <!-- Carga de Archivo EME -->
                            <div class="mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold text-blue-900 uppercase flex items-center mb-2">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Cargar Archivo EME (Opcional)
                                        </h4>
                                        <p class="text-xs text-slate-600">Precargue automáticamente los datos del importador y la manifestación desde un archivo EME.</p>
                                    </div>
                                    <div class="ml-4">
                                        <label for="emeFile" class="cursor-pointer inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase rounded-sm shadow-md transition transform hover:-translate-y-0.5">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <span x-show="!isUploading">Seleccionar EME</span>
                                            <span x-show="isUploading" x-cloak>Procesando...</span>
                                        </label>
                                        <input type="file" id="emeFile" @change="uploadEme($event)" accept=".txt,.eme,.325" class="hidden">
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-6 pb-1">Datos del Importador</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="relative">
                                    <x-input-label for="rfc_importador" :value="__('RFC Importador')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <div class="flex gap-2">
                                        <input id="rfc_importador" 
                                            class="block w-full text-sm rounded-sm shadow-sm uppercase font-mono focus:border-blue-900 focus:ring-blue-900" 
                                            x-bind:class="form.rfc_importador && form.rfc_solicitante && form.rfc_importador !== form.rfc_solicitante ? 'border-red-300 bg-red-50 text-red-700' : 'border-gray-300 bg-white'"
                                            type="text" 
                                            name="rfc_importador" 
                                            x-model="form.rfc_importador" 
                                            @input="validarRfcImportador()"
                                            placeholder="Debe ser el mismo RFC del solicitante"
                                            required 
                                            minlength="12" 
                                            maxlength="13" />
                                        <button type="button" 
                                            @click="aceptarRfcImportador()"
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-sm shadow transition whitespace-nowrap">
                                            Aceptar
                                        </button>
                                    </div>
                                    <div x-show="form.rfc_importador && form.rfc_solicitante && form.rfc_importador === form.rfc_solicitante" class="absolute top-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white shadow-md" title="RFC correcto"></div>
                                    <div x-show="form.rfc_importador && form.rfc_solicitante && form.rfc_importador !== form.rfc_solicitante" class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-white shadow-md" title="RFC incorrecto"></div>
                                    <div x-show="form.rfc_importador && form.rfc_solicitante && form.rfc_importador !== form.rfc_solicitante" class="mt-1 text-xs text-red-600 font-semibold flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        ⚠️ Error: El RFC del importador debe ser el mismo que el del solicitante
                                    </div>
                                    <p class="text-xs mt-2 font-bold flex items-center" x-bind:class="mensajeAyuda.includes('✓') ? 'text-green-600' : 'text-blue-600'">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span x-text="mensajeAyuda"></span>
                                    </p>
                                </div>
                                <div class="relative">
                                    <x-input-label for="razon_social_importador" :value="__('Razón Social')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <input id="razon_social_importador" 
                                        class="block w-full text-sm rounded-sm shadow-sm bg-slate-100 text-slate-600 border-slate-300 font-bold cursor-not-allowed" 
                                        type="text" 
                                        name="razon_social_importador" 
                                        x-model="form.razon_social_importador" 
                                        readonly
                                        required />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="registro_nacional_contribuyentes" :value="__('Registro Nacional de Contribuyentes (o Tax ID)')" class="font-bold text-slate-500 text-xs uppercase mb-1 required" />
                                    <input id="registro_nacional_contribuyentes" class="block w-full bg-slate-100 text-slate-600 border-slate-300 rounded-sm shadow-sm font-bold text-sm cursor-not-allowed" type="text" name="registro_nacional_contribuyentes" x-model="form.registro_nacional_contribuyentes" readonly required />
                                </div>
                                <div class="md:col-span-2 relative">
                                    <x-input-label for="domicilio_fiscal_importador" :value="__('Domicilio Fiscal del Importador')" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                                    <textarea id="domicilio_fiscal_importador" 
                                        class="block w-full text-sm rounded-sm shadow-sm bg-slate-100 text-slate-600 border-slate-300 cursor-not-allowed" 
                                        name="domicilio_fiscal_importador" 
                                        x-model="form.domicilio_fiscal_importador"
                                        readonly
                                        rows="2"
                                        placeholder="Calle, número, colonia, código postal, ciudad, estado, país"></textarea>
                                </div>
                            </div>

                            <!-- RFCs Autorizados para Consulta -->
                            <div class="mt-10">
                                <div class="bg-gray-50 border border-gray-300 rounded-sm p-6">
                                    <h4 class="text-sm font-bold text-gray-700 uppercase mb-2">RFCS AUTORIZADOS PARA CONSULTA</h4>
                                    <p class="text-xs text-gray-600 mb-6">Agentes aduanales o representantes autorizados para consultar esta manifestación</p>
                                    
                                    <!-- Botón para agregar RFC en la parte superior derecha -->
                                    <div class="flex justify-end mb-4">
                                        <button type="button" @click="addRfcConsulta()" class="text-xs bg-white border-2 border-green-600 text-green-700 px-4 py-2 rounded font-bold hover:bg-green-50 transition uppercase">
                                            + AGREGAR RFC
                                        </button>
                                    </div>

                                    <!-- RFC Temporal para captura (solo aparece al presionar Agregar) -->
                                    <div x-show="showRfcForm" class="space-y-4 mb-6">
                                        <div class="bg-slate-50 border border-slate-200 rounded p-4">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">RFC a consultar:</label>
                                                    <input type="text" 
                                                        x-model="tempRfc" 
                                                        class="w-full text-sm border border-gray-300 rounded px-3 py-2 uppercase font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                                        placeholder="Ingrese RFC..." 
                                                        minlength="12" 
                                                        maxlength="13">
                                                </div>
                                                <div>
                                                    <button type="button" 
                                                        @click="aceptarRfcConsulta()" 
                                                        :disabled="!tempRfc || tempRfc.length < 12 || tempRfc.length > 13 || buscandoRfcConsulta"
                                                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white text-sm font-bold py-2 px-4 rounded transition">
                                                        <span x-text="buscandoRfcConsulta ? 'Buscando...' : 'Aceptar'"></span>
                                                    </button>
                                                </div>
                                                <div>
                                                    <button type="button" 
                                                        @click="cancelarRfcConsulta()" 
                                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white text-sm font-bold py-2 px-4 rounded transition">
                                                        Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- RFCs de consulta aceptados -->
                                    <div class="space-y-4 mb-6">
                                        <template x-for="(rfc, i) in consultationRfcs" :key="i">
                                            <div class="bg-white border border-gray-200 rounded p-4 shadow-sm">
                                                <div class="grid grid-cols-12 gap-4 items-start mb-2">
                                                    <div class="col-span-1 flex items-center">
                                                        <div class="bg-green-100 p-2 rounded">
                                                            <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                        </div>
                                                    </div>
                                                    <div class="col-span-11">
                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                            <div>
                                                                <label class="block text-xs font-semibold text-gray-700 mb-1">RFC:</label>
                                                                <input type="text" 
                                                                    :name="`consultation_rfcs[${i}][rfc_consulta]`" 
                                                                    x-model="rfc.rfc_consulta" 
                                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 uppercase font-mono bg-gray-50 cursor-not-allowed" 
                                                                    readonly>
                                                            </div>
                                                            <div class="relative">
                                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Razón Social:</label>
                                                                <input type="text" 
                                                                    :name="`consultation_rfcs[${i}][razon_social]`" 
                                                                    x-model="rfc.razon_social" 
                                                                    x-bind:class="rfc.fromDB ? 'bg-green-50 border-green-300 text-green-700 cursor-not-allowed' : 'bg-white border-gray-300'"
                                                                    x-bind:readonly="rfc.fromDB"
                                                                    class="w-full text-sm rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                                                    placeholder="Razón social..." 
                                                                    required>
                                                                <div x-show="rfc.fromDB" class="absolute top-6 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white shadow-md" title="De la base de datos"></div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tipo de figura*:</label>
                                                                <select :name="`consultation_rfcs[${i}][tipo_figura]`" 
                                                                    x-model="rfc.tipo_figura"
                                                                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                    required>
                                                                    <option value="">Seleccione un valor</option>
                                                                    <option value="Agencia Aduanal">Agencia Aduanal</option>
                                                                    <option value="Agente aduanal">Agente aduanal</option>
                                                                    <option value="Otro">Otro</option>
                                                                    <option value="Representante Legal">Representante Legal</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex justify-end mt-3">
                                                    <button type="button" 
                                                        @click="consultationRfcs.splice(i,1)" 
                                                        class="text-xs text-red-600 hover:text-red-800 font-semibold px-3 py-1 border border-red-300 rounded hover:bg-red-50 transition">
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="consultationRfcs.length === 0" class="text-center py-8 text-sm text-gray-500 bg-white border border-gray-200 rounded">
                                        No se han autorizado RFCs adicionales para consultar este documento.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB: MANIFESTACIÓN -->

                        <!-- TAB: COVEs -->
                        <div x-show="activeTab === 'coves'" x-transition>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-700 uppercase">Acuses de Valor (COVEs)</h3>
                                        <p class="text-xs text-slate-500">Capture los COVEs para calcular el valor en aduana.</p>
                                    </div>
                                    <button type="button" @click="addCove()" class="bg-white border border-slate-300 text-slate-700 text-xs px-4 py-2 rounded-sm font-bold shadow-sm hover:bg-slate-50 hover:text-blue-900 transition flex items-center uppercase">
                                        <span class="text-lg mr-1 leading-none text-blue-600">+</span>
                                        Agregar COVE
                                    </button>
                                </div>

                                <div class="overflow-x-auto shadow-sm rounded border border-slate-200">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">COVE</th>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Método</th>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider"># Factura</th>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Fecha</th>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Emisor</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-slate-200">
                                            <template x-for="(cove, index) in coves" :key="index">
                                                <tr class="hover:bg-slate-50">
                                                    <td class="p-3 align-top relative">
                                                        <div class="flex">
                                                            <input type="text" :name="`coves[${index}][edocument]`" x-model="cove.edocument" 
                                                                x-bind:class="cove.fromEme ? 'bg-blue-50 border-blue-300 text-blue-700 font-bold cursor-not-allowed' : 'bg-white border-slate-300'"
                                                                x-bind:readonly="cove.fromEme"
                                                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-sm text-xs focus:ring-blue-900 focus:border-blue-900 font-mono uppercase text-slate-800" required placeholder="COVE...">
                                                            <button type="button" @click="fetchCoveData(index)" :disabled="cove.fromEme" class="inline-flex items-center px-3 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-600 text-xs rounded-r-sm hover:bg-slate-200 hover:text-blue-900 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                            </button>
                                                        </div>
                                                        <p x-show="cove.loading" class="text-xs text-blue-600 mt-1 animate-pulse font-medium">Buscando...</p>
                                                        <div x-show="cove.fromEme" class="absolute top-0 right-0 w-3 h-3 bg-blue-500 rounded-full border-2 border-white shadow-md" title="Del archivo EME"></div>
                                                    </td>
                                                    <td class="p-3 align-top">
                                                        <select :name="`coves[${index}][metodo_valoracion]`" x-model="cove.metodo_valoracion" required class="w-full text-xs border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                                            <option value="">Seleccione...</option>
                                                            <option value="1">Valor de transacción</option>
                                                            <option value="2">Valor de transacción de mercancías idénticas</option>
                                                            <option value="3">Valor de transacción de mercancías similares</option>
                                                            <option value="4">Valor deductivo</option>
                                                            <option value="5">Valor reconstruido</option>
                                                            <option value="6">Valor de última instancia</option>
                                                        </select>
                                                    </td>
                                                    <td class="p-3 align-top"><input type="text" :name="`coves[${index}][numero_factura]`" x-model="cove.numero_factura" class="w-full text-xs border-slate-300 rounded-sm" required></td>
                                                    <td class="p-3 align-top"><input type="date" :name="`coves[${index}][fecha_expedicion]`" x-model="cove.fecha_expedicion" class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                    <td class="p-3 align-top"><input type="text" :name="`coves[${index}][emisor]`" x-model="cove.emisor" class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                    <td class="p-3 align-top text-center">
                                                        <button type="button" @click="removeCove(index)" class="text-slate-400 hover:text-red-600 p-2 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <div x-show="coves.length === 0" class="text-center py-8 text-sm text-gray-500 bg-white border border-gray-200 rounded">
                                    No se han agregado COVEs. Haga clic en "Agregar COVE" para comenzar.
                                </div>

                                <!-- BOTÓN AÑADIR MV -->
                                <div class="mt-6 text-center">
                                    <button type="button" @click="showMVSection = !showMVSection" 
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm uppercase rounded-sm shadow-md transition transform hover:-translate-y-0.5">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span x-text="showMVSection ? 'Ocultar Detalles MV' : 'Añadir MV'"></span>
                                    </button>
                                </div>

                                <!-- SECCIÓN MV EXPANDIBLE -->
                                <div x-show="showMVSection" x-transition class="mt-8 bg-slate-50 border border-slate-200 rounded-lg p-6">
                                    <div class="mb-6">
                                        <h2 class="text-lg font-bold text-blue-900 uppercase">Manifestación de Valor - Detalles</h2>
                                        <p class="text-sm text-slate-600">Configure incrementables, pedimentos, pagos y demás información requerida.</p>
                                    </div>

                                    <!-- MÉTODO E INCOTERM -->
                                    <div class="mb-8">
                                        <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-4 pb-1">Método e Incoterm</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">Método de Valoración Global</label>
                                                <select name="metodo_valoracion_global" x-model="general.metodo" required class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                                    <option value="">Seleccione método...</option>
                                                    <option value="1">Valor de transacción</option>
                                                    <option value="2">Valor de transacción de mercancías idénticas</option>
                                                    <option value="3">Valor de transacción de mercancías similares</option>
                                                    <option value="4">Valor deductivo</option>
                                                    <option value="5">Valor reconstruido</option>
                                                    <option value="6">Valor de última instancia</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">INCOTERM</label>
                                                <select name="incoterm" x-model="general.incoterm" required class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                                    <option value="">Seleccione INCOTERM...</option>
                                                    <option value="EXW">EXW - Ex Works</option>
                                                    <option value="FCA">FCA - Free Carrier</option>
                                                    <option value="CPT">CPT - Carriage Paid To</option>
                                                    <option value="CIP">CIP - Carriage and Insurance Paid To</option>
                                                    <option value="DAP">DAP - Delivered at Place</option>
                                                    <option value="DPU">DPU - Delivered at Place Unloaded</option>
                                                    <option value="DDP">DDP - Delivered Duty Paid</option>
                                                    <option value="FAS">FAS - Free Alongside Ship</option>
                                                    <option value="FOB">FOB - Free on Board</option>
                                                    <option value="CFR">CFR - Cost and Freight</option>
                                                    <option value="CIF">CIF - Cost, Insurance and Freight</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PEDIMENTOS -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-blue-900 pb-1">
                                            <h3 class="text-xs font-bold text-blue-900 uppercase">Pedimentos Anteriores</h3>
                                            <button type="button" @click="addPedimento()" class="text-xs bg-white border border-blue-900 text-blue-900 px-3 py-1 rounded-sm font-bold hover:bg-blue-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="overflow-x-auto border border-slate-200 rounded-lg shadow-sm">
                                            <table class="w-full text-sm border-collapse table-fixed">
                                                <thead class="bg-blue-50">
                                                    <tr>
                                                        <th class="w-1/4 p-3 text-left font-bold uppercase text-xs border-b border-slate-200">Número Pedimento <span class="text-red-500">*</span></th>
                                                        <th class="w-1/6 p-3 text-left font-bold uppercase text-xs border-b border-slate-200">Patente <span class="text-red-500">*</span></th>
                                                        <th class="w-1/2 p-3 text-left font-bold uppercase text-xs border-b border-slate-200">Aduana <span class="text-red-500">*</span></th>
                                                        <th class="w-1/12 p-3 text-center font-bold uppercase text-xs border-b border-slate-200">Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(ped, i) in pedimentos" :key="i">
                                                        <tr class="bg-white border-b border-slate-100 hover:bg-slate-50">
                                                            <td class="p-3">
                                                                <input type="text" :name="`pedimentos[${i}][numero_pedimento]`" x-model="ped.numero_pedimento" @input="formatPedimento(ped)" required maxlength="21" placeholder="XX  XXX  XXXX  XXXXXXX" class="w-full text-sm rounded-md border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
                                                            </td>
                                                            <td class="p-3">
                                                                <input type="text" :name="`pedimentos[${i}][patente]`" x-model="ped.patente" required class="w-full text-sm rounded-md border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                            </td>
                                                            <td class="p-3">
                                                                <select :name="`pedimentos[${i}][aduana_clave]`" x-model="ped.aduana_clave" required class="w-full text-sm rounded-md border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" style="min-width: 300px;">
                                                                    <option value="">Seleccione aduana...</option>
                                                                    @foreach($catalogs['aduanas'] ?? [] as $aduana)
                                                                        <option value="{{ $aduana['aduana'] }}">{{ $aduana['aduana'] }} - {{ $aduana['denominacion'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="p-3 text-center">
                                                                <button type="button" @click="pedimentos.splice(i,1)" class="text-slate-400 hover:text-red-600 font-bold text-lg transition-colors duration-200" title="Eliminar pedimento">✕</button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- INCREMENTABLES -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-green-600 pb-1">
                                            <h3 class="text-xs font-bold text-green-700 uppercase">Incrementables</h3>
                                            <button type="button" @click="addIncrementable()" class="text-xs bg-white border border-green-600 text-green-700 px-3 py-1 rounded-sm font-bold hover:bg-green-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(inc, i) in incrementables" :key="i">
                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-4 rounded border border-slate-200 shadow-sm relative">
                                                    <div class="md:col-span-3">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Concepto</label>
                                                        <select :name="`incrementables[${i}][concepto]`" x-model="inc.concepto" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione...</option>
                                                            @foreach($catalogs['incrementables'] ?? [] as $inc_cat)
                                                                <option value="{{ $inc_cat['clave'] }}" title="{{ $inc_cat['descripcion'] }}">{{ Str::limit($inc_cat['descripcion'], 40) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                        <input type="date" :name="`incrementables[${i}][fecha_erogacion]`" x-model="inc.fecha_erogacion" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                                        <select :name="`incrementables[${i}][moneda]`" x-model="inc.moneda" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="USD">USD</option>
                                                            <option value="MXN">MXN</option>
                                                            <option value="EUR">EUR</option>
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                                        <input type="number" step="0.01" :name="`incrementables[${i}][importe]`" x-model="inc.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">T. Cambio</label>
                                                        <input type="number" step="0.0001" :name="`incrementables[${i}][tipo_cambio]`" x-model="inc.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                                    </div>
                                                    <div class="md:col-span-1 flex justify-center pb-1">
                                                        <button type="button" @click="incrementables.splice(i,1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- DECREMENTABLES -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-red-600 pb-1">
                                            <h3 class="text-xs font-bold text-red-700 uppercase">Decrementables</h3>
                                            <button type="button" @click="addDecrementable()" class="text-xs bg-white border border-red-600 text-red-700 px-3 py-1 rounded-sm font-bold hover:bg-red-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(dec, i) in decrementables" :key="i">
                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-4 rounded border border-slate-200 shadow-sm relative">
                                                    <div class="md:col-span-3">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Concepto</label>
                                                        <select :name="`decrementables[${i}][concepto]`" x-model="dec.concepto" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione...</option>
                                                            @foreach($catalogs['decrementables'] ?? [] as $dec_cat)
                                                                <option value="{{ $dec_cat['clave'] }}" title="{{ $dec_cat['descripcion'] }}">{{ Str::limit($dec_cat['descripcion'], 40) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                        <input type="date" :name="`decrementables[${i}][fecha_erogacion]`" x-model="dec.fecha_erogacion" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                                        <select :name="`decrementables[${i}][moneda]`" x-model="dec.moneda" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="USD">USD</option>
                                                            <option value="MXN">MXN</option>
                                                            <option value="EUR">EUR</option>
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                                        <input type="number" step="0.01" :name="`decrementables[${i}][importe]`" x-model="dec.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">T. Cambio</label>
                                                        <input type="number" step="0.0001" :name="`decrementables[${i}][tipo_cambio]`" x-model="dec.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                                    </div>
                                                    <div class="md:col-span-1 flex justify-center pb-1">
                                                        <button type="button" @click="decrementables.splice(i,1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- PAGOS -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-slate-800 pb-1">
                                            <h3 class="text-xs font-bold text-slate-800 uppercase">Pagos</h3>
                                            <button type="button" @click="addPago()" class="text-xs bg-white border border-slate-800 text-slate-800 px-3 py-1 rounded-sm font-bold hover:bg-slate-100 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="overflow-x-auto border border-slate-200 rounded">
                                            <table class="w-full text-sm border-collapse">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="p-2 text-left text-xs font-bold uppercase">Estatus</th>
                                                        <th class="p-2 text-left text-xs font-bold uppercase">Fecha</th>
                                                        <th class="p-2 text-left text-xs font-bold uppercase">Importe</th>
                                                        <th class="p-2 text-left text-xs font-bold uppercase">Moneda</th>
                                                        <th class="p-2 text-left text-xs font-bold uppercase">T.C.</th>
                                                        <th class="p-2 text-left text-xs font-bold uppercase">Forma Pago</th>
                                                        <th class="p-2 text-center text-xs font-bold uppercase">Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(pago, i) in pagos" :key="i">
                                                        <tr class="bg-white hover:bg-slate-50">
                                                            <td class="p-2">
                                                                <select :name="`pagos[${i}][status]`" x-model="pago.status" required class="w-full text-xs border-slate-300 rounded-sm">
                                                                    <option value="paid">Pagado</option>
                                                                    <option value="payable">Por Pagar</option>
                                                                </select>
                                                            </td>
                                                            <td class="p-2"><input type="date" :name="`pagos[${i}][fecha]`" x-model="pago.fecha" required class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                            <td class="p-2"><input type="number" step="0.01" :name="`pagos[${i}][importe]`" x-model="pago.importe" required class="w-full text-xs border-slate-300 rounded-sm"></td>
                                                            <td class="p-2">
                                                                <select :name="`pagos[${i}][moneda]`" x-model="pago.moneda" required class="w-full text-xs border-slate-300 rounded-sm">
                                                                    <option value="USD">USD</option>
                                                                    <option value="MXN">MXN</option>
                                                                </select>
                                                            </td>
                                                            <td class="p-2"><input type="number" step="0.0001" :name="`pagos[${i}][tipo_cambio]`" x-model="pago.tipo_cambio" class="w-full text-xs border-slate-300 bg-slate-50 rounded-sm" readonly></td>
                                                            <td class="p-2">
                                                                <select :name="`pagos[${i}][forma_pago]`" x-model="pago.forma_pago" required class="w-full text-xs border-slate-300 rounded-sm">
                                                                    <option value="">Seleccione...</option>
                                                                    @foreach($catalogs['formas_de_pago'] ?? [] as $forma)
                                                                        <option value="{{ $forma['clave'] }}">{{ $forma['descripcion'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="p-2 text-center"><button type="button" @click="pagos.splice(i,1)" class="text-slate-400 hover:text-red-600 font-bold">✕</button></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- RFCs DE CONSULTA -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-green-600 pb-1">
                                            <h3 class="text-xs font-bold text-slate-800 uppercase">RFCs Autorizados para Consulta</h3>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB: COVEs -->

                        <!-- TAB: VALOR ADUANAL -->
                        <div x-show="activeTab === 'valor'" x-transition>
                            <div class="space-y-6">
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold text-slate-700 uppercase">Cálculo del Valor en Aduana</h3>
                                    <p class="text-xs text-slate-500">Complete los valores para el cálculo del valor en aduana.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Precio Pagado -->
                                    <div class="relative">
                                        <x-input-label value="Precio Pagado" class="text-xs font-bold text-slate-500 uppercase mb-1 required" />
                                        <div class="relative flex rounded-sm shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                            <input name="total_precio_pagado" type="number" step="0.01" 
                                                x-bind:class="isFromEme('total_precio_pagado') ? 'bg-blue-50 border-blue-300 text-blue-700 font-bold cursor-not-allowed' : 'bg-white border-slate-300'"
                                                x-bind:readonly="isFromEme('total_precio_pagado')"
                                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                            <select name="moneda_precio_pagado" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                                <option value="MXN">MXN</option>
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                            </select>
                                        </div>
                                        <div x-show="isFromEme('total_precio_pagado')" class="absolute top-6 right-0 w-3 h-3 bg-blue-500 rounded-full border-2 border-white shadow-md" title="Del archivo EME"></div>
                                    </div>
                                    
                                    <!-- Incrementables -->
                                    <div>
                                        <x-input-label value="(+) Incrementables" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                        <div class="relative flex rounded-sm shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                            <input name="total_incrementables" type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-slate-300 focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                            <select name="moneda_incrementables" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                                <option value="MXN">MXN</option>
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Decrementables -->
                                    <div>
                                        <x-input-label value="(-) Decrementables" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                        <div class="relative flex rounded-sm shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                            <input name="total_decrementables" type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-slate-300 focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                            <select name="moneda_decrementables" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                                <option value="MXN">MXN</option>
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Valor en Aduana -->
                                    <div class="md:col-span-2 lg:col-span-1 bg-blue-100 p-4 rounded border border-blue-200 relative">
                                        <x-input-label value="= VALOR EN ADUANA" class="text-xs font-black text-blue-900 uppercase mb-1 required" />
                                        <div class="relative flex rounded-sm shadow-inner bg-white">
                                            <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-blue-300 bg-blue-50 text-blue-800 text-sm font-bold">$</span>
                                            <input name="total_valor_aduana" type="number" step="0.01" 
                                                x-bind:class="isFromEme('total_valor_aduana') ? 'bg-blue-50 border-blue-400 text-blue-800 cursor-not-allowed' : 'bg-white border-blue-300 text-blue-900'"
                                                x-bind:readonly="isFromEme('total_valor_aduana')"
                                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none font-black text-xl focus:ring-blue-900 focus:border-blue-900" required />
                                            <span class="inline-flex items-center px-3 rounded-r-sm border border-l-0 border-blue-300 bg-blue-50 text-blue-900 text-xs font-bold">MXN</span>
                                        </div>
                                        <div x-show="isFromEme('total_valor_aduana')" class="absolute top-0 right-0 w-3 h-3 bg-blue-500 rounded-full border-2 border-white shadow-md" title="Del archivo EME"></div>
                                    </div>
                                    
                                    <!-- Precio por Pagar -->
                                    <div>
                                        <x-input-label value="Precio por Pagar" class="text-xs font-bold text-slate-500 uppercase mb-1" />
                                        <div class="relative flex rounded-sm shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-sm border border-r-0 border-slate-300 bg-white text-slate-500 text-sm">$</span>
                                            <input name="total_precio_por_pagar" type="number" step="0.01" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none border-slate-300 focus:ring-blue-900 focus:border-blue-900 text-slate-800 text-sm font-bold" />
                                            <select name="moneda_precio_por_pagar" class="inline-flex items-center px-2 py-2 border border-l-0 border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold rounded-r-sm w-24">
                                                <option value="MXN">MXN</option>
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB: VALOR ADUANAL -->

                        <div class="flex justify-between items-center mt-10 pt-6 border-t border-slate-200">
                             <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-red-700 font-bold text-sm px-4 py-2 transition flex items-center uppercase tracking-wider">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cancelar
                            </a>
                            
                            <button type="submit" @click.prevent="if(canContinueToStep2()) $el.closest('form').submit()" class="inline-flex items-center px-8 py-3 bg-slate-900 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-900 shadow-md transform hover:-translate-y-0.5 transition">
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
                activeTab: 'solicitante', // Tab activo por defecto
                isUploading: false,
                buscandoRfc: false,
                buscandoRfcConsulta: false,
                camposDesdeDB: false,
                mensajeAyuda: 'Ingrese el RFC.',
                mostrarModalError: false,
                emeData: null, // Datos cargados del EME
                tempRfc: '', // RFC temporal para consulta
                showRfcForm: false,
                consultationRfcs: @if(isset($manifestation) && $manifestation->consultationRfcs->count() > 0) @json($manifestation->consultationRfcs) @else [] @endif,
                coves: @if(isset($manifestation) && $manifestation->coves->count() > 0) @json($manifestation->coves) @else [] @endif,
                // Variables para la sección MV
                showMVSection: false,
                general: {
                    metodo: '{{ old("metodo_valoracion_global", $manifestation->metodo_valoracion_global ?? "") }}',
                    incoterm: '{{ old("incoterm", $manifestation->incoterm ?? "") }}'
                },
                pedimentos: @if(isset($manifestation) && $manifestation->pedimentos && $manifestation->pedimentos->count() > 0) @json($manifestation->pedimentos) @else [{numero_pedimento: '', patente: '', aduana_clave: '430'}] @endif,
                incrementables: @if(isset($incrementables) && $incrementables->count() > 0) @json($incrementables) @else [] @endif,
                decrementables: @if(isset($decrementables) && $decrementables->count() > 0) @json($decrementables) @else [] @endif,
                pagos: @if(isset($manifestation) && $manifestation->payments && $manifestation->payments->count() > 0) @json($manifestation->payments) @else [] @endif,
                compensaciones: @if(isset($manifestation) && $manifestation->compensations && $manifestation->compensations->count() > 0) @json($manifestation->compensations) @else [] @endif,
                form: {
                    rfc_solicitante: '{{ Auth::user()->rfc ?? "" }}',
                    rfc_importador: '{{ old("rfc_importador", $manifestation->rfc_importador ?? "") }}',
                    razon_social_importador: '{{ old("razon_social_importador", $manifestation->razon_social_importador ?? "") }}',
                    domicilio_fiscal_importador: '{{ old("domicilio_fiscal_importador", $manifestation->domicilio_fiscal_importador ?? "") }}',
                    registro_nacional_contribuyentes: '{{ old("registro_nacional_contribuyentes", $manifestation->registro_nacional_contribuyentes ?? "") }}'
                },
                datosCompletos: false,
                init() {
                    // Validar datos completos del usuario
                    this.validarDatosCompletos();
                    
                    // Copiar RFC del solicitante al importador inicialmente
                    this.copiarRfcSolicitanteAImportador();
                    
                    // Verificar si es una manifestación nueva o edición
                    const isNewManifestation = {{ isset($manifestation) ? 'false' : 'true' }};
                    
                    // Si es nueva manifestación, limpiar sessionStorage
                    if (isNewManifestation) {
                        sessionStorage.removeItem('emeData');
                        this.emeData = null;
                        this.camposDesdeDB = false;
                        // Limpiar campos del importador
                        this.form.rfc_importador = '';
                        this.form.razon_social_importador = '';
                        this.form.domicilio_fiscal_importador = '';
                        this.form.registro_nacional_contribuyentes = '';
                    } else {
                        // Si es edición, recuperar datos del EME de sessionStorage si existen
                        const storedEme = sessionStorage.getItem('emeData');
                        if (storedEme) {
                            this.emeData = JSON.parse(storedEme);
                            this.aplicarDatosEme();
                        }
                        
                        // Si hay RFC importador, actualizar registro nacional
                        if(this.form.rfc_importador) {
                            this.actualizarRegistroNacional();
                        }
                    }
                    
                    // Verificar si el perfil del usuario está completo
                    const profileCompleted = {{ Auth::user()->hasCompleteSolicitorProfile() ? 'true' : 'false' }};
                    if (!profileCompleted) {
                        // Si el perfil no está completo, quedarse en tab de solicitante
                        this.activeTab = 'solicitante';
                    }
                },
                async uploadEme(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.isUploading = true;
                    const formData = new FormData();
                    formData.append('eme_file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await fetch('{{ route("manifestations.parseEme") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();

                        if (result.success && result.data) {
                            this.emeData = result.data;
                            sessionStorage.setItem('emeData', JSON.stringify(result.data));
                            this.aplicarDatosEme();
                            
                            // Resumen de datos cargados
                            const summary = [];
                            if (result.data.rfc_consultable_eme) {
                                summary.push(`✓ RFC Consultable: ${result.data.rfc_consultable_eme.rfc_consulta}`);
                                summary.push(`✓ Razón Social: ${result.data.rfc_consultable_eme.razon_social}`);
                            }
                            if (result.data.rfc_exportador) summary.push(`✓ RFC Exportador: ${result.data.rfc_exportador}`);
                            if (result.data.nombre_exportador) summary.push(`✓ Exportador: ${result.data.nombre_exportador}`);
                            if (result.data.coves && result.data.coves.length > 0) summary.push(`✓ ${result.data.coves.length} COVE(s) detectado(s)`);
                            if (result.data.pedimentos && result.data.pedimentos.length > 0) summary.push(`✓ ${result.data.pedimentos.length} Pedimento(s) detectado(s)`);
                            
                            alert('✅ Archivo EME procesado exitosamente\n\n' + 
                                  'Datos precargados:\n' + summary.join('\n') + 
                                  '\n\nLos campos marcados son de solo lectura.\n' +
                                  'Complete manualmente los campos restantes.');
                        } else {
                            alert('❌ Error: ' + (result.message || 'No se pudo procesar el archivo EME'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('❌ Ocurrió un error al procesar el archivo EME.');
                    } finally {
                        this.isUploading = false;
                        event.target.value = '';
                    }
                },
                aplicarDatosEme() {
                    if (!this.emeData) return;
                    
                    // RFC del importador = RFC del solicitante (no del EME)
                    this.form.rfc_importador = this.form.rfc_solicitante;
                    this.form.registro_nacional_contribuyentes = this.form.rfc_solicitante;
                    
                    // RFC del EME va a consultables
                    if (this.emeData.rfc_consultable_eme) {
                        const rfcEme = this.emeData.rfc_consultable_eme;
                        
                        // Verificar que no esté duplicado
                        const exists = this.consultationRfcs.find(item => item.rfc_consulta === rfcEme.rfc_consulta);
                        if (!exists) {
                            this.consultationRfcs.push({
                                rfc_consulta: rfcEme.rfc_consulta,
                                razon_social: rfcEme.razon_social,
                                tipo_figura: '', // Se debe seleccionar manualmente
                                fromDB: false,
                                source: 'eme'
                            });
                        }
                    }

                    // Precargar COVEs del archivo EME
                    if (this.emeData.coves && this.emeData.coves.length > 0) {
                        this.coves = this.emeData.coves.map(cove => ({
                            ...cove,
                            fromEme: true,  // Marcar como proveniente del EME
                            loading: false
                        }));
                        console.log('COVEs cargados del EME:', this.coves);
                    }

                    // Precargar pedimentos del archivo EME
                    if (this.emeData.pedimentos && this.emeData.pedimentos.length > 0) {
                        this.pedimentos = this.emeData.pedimentos.map(ped => ({
                            numero_pedimento: ped.numero_pedimento || '',
                            patente: ped.patente || '',
                            aduana_clave: ped.aduana_clave || '430',
                            fromEme: true
                        }));
                        console.log('Pedimentos cargados del EME:', this.pedimentos);
                    }

                    // Precargar incrementables del archivo EME
                    if (this.emeData.adjustments) {
                        const incrementablesEme = this.emeData.adjustments.filter(adj => adj.type === 'incrementable');
                        if (incrementablesEme.length > 0) {
                            this.incrementables = incrementablesEme.map(inc => ({
                                concepto: inc.concepto || '',
                                importe: inc.importe || 0,
                                moneda: inc.moneda || 'USD',
                                tipo_cambio: inc.tipo_cambio || 20.0000,
                                fecha_erogacion: inc.fecha_erogacion || '',
                                a_cargo_importador: true,
                                fromEme: true,
                                loading: false
                            }));
                            console.log('Incrementables cargados del EME:', this.incrementables);
                        }

                        // Precargar decrementables del archivo EME
                        const decrementablesEme = this.emeData.adjustments.filter(adj => adj.type === 'decrementable');
                        if (decrementablesEme.length > 0) {
                            this.decrementables = decrementablesEme.map(dec => ({
                                concepto: dec.concepto || '',
                                importe: dec.importe || 0,
                                moneda: dec.moneda || 'USD',
                                tipo_cambio: dec.tipo_cambio || 20.0000,
                                fecha_erogacion: dec.fecha_erogacion || '',
                                fromEme: true,
                                loading: false
                            }));
                            console.log('Decrementables cargados del EME:', this.decrementables);
                        }
                    }

                    // Precargar pagos del archivo EME
                    if (this.emeData.payments && this.emeData.payments.length > 0) {
                        this.pagos = this.emeData.payments.map(pago => ({
                            status: pago.status || 'paid',
                            fecha: pago.fecha || '',
                            importe: pago.importe || 0,
                            moneda: pago.moneda || 'USD',
                            tipo_cambio: pago.tipo_cambio || 20.0000,
                            forma_pago: pago.forma_pago || '01',
                            fromEme: true,
                            loading: false
                        }));
                        console.log('Pagos cargados del EME:', this.pagos);
                    }

                    // Precargar valores de aduana del archivo EME
                    if (this.emeData.total_precio_pagado) {
                        const precioPagadoInput = document.querySelector('input[name="total_precio_pagado"]');
                        if (precioPagadoInput) precioPagadoInput.value = this.emeData.total_precio_pagado;
                    }
                    if (this.emeData.total_valor_aduana) {
                        const valorAduanaInput = document.querySelector('input[name="total_valor_aduana"]');
                        if (valorAduanaInput) valorAduanaInput.value = this.emeData.total_valor_aduana;
                    }

                    console.log('✅ Datos del EME aplicados correctamente a la sección MV');
                },
                validarDatosCompletos() {
                    // Verificar que el usuario tenga todos los datos necesarios en su perfil
                    const user = @json($userData ?? []);
                    
                    const camposRequeridos = [
                        'rfc', 'razon_social', 'actividad_economica', 'pais', 
                        'codigo_postal', 'estado', 'municipio', 'colonia', 
                        'calle', 'numero_exterior', 'lada', 'telefono', 'email'
                    ];
                    
                    this.datosCompletos = camposRequeridos.every(campo => {
                        const valor = user[campo];
                        return valor && valor.toString().trim().length > 0;
                    });
                    
                    if (!this.datosCompletos) {
                        console.log('Datos incompletos del usuario:', camposRequeridos.filter(campo => {
                            const valor = user[campo];
                            return !valor || valor.toString().trim().length === 0;
                        }));
                    }
                },
                aceptarRfcImportador() {
                    const rfcImportador = this.form.rfc_importador;
                    const rfcSolicitante = this.form.rfc_solicitante;
                    
                    // Verificar si el RFC del importador coincide con el del solicitante
                    if (rfcImportador !== rfcSolicitante) {
                        this.mostrarModalError = true;
                        return;
                    }
                    
                    // Si coincide, cargar información del solicitante en campos del importador
                    this.cargarDatosSolicitante();
                },
                cargarDatosSolicitante() {
                    // Cargar datos del perfil del usuario autenticado en los campos del importador
                    const user = @json($userData ?? []);
                    this.form.razon_social_importador = user.razon_social || '';
                    this.form.domicilio_fiscal_importador = this.construirDomicilioFiscal();
                    this.form.registro_nacional_contribuyentes = this.form.rfc_solicitante;
                    
                    this.mensajeAyuda = '✓ Datos del solicitante cargados correctamente';
                },
                construirDomicilioFiscal() {
                    const user = @json($userData ?? []);
                    
                    let domicilio = [];
                    if (user.calle) domicilio.push(user.calle);
                    if (user.numero_exterior) domicilio.push(user.numero_exterior);
                    if (user.numero_interior) domicilio.push('Int. ' + user.numero_interior);
                    if (user.colonia) domicilio.push(user.colonia);
                    if (user.codigo_postal) domicilio.push('C.P. ' + user.codigo_postal);
                    if (user.municipio) domicilio.push(user.municipio);
                    if (user.estado) domicilio.push(user.estado);
                    if (user.pais) domicilio.push(user.pais);
                    
                    return domicilio.join(', ');
                },
                aceptarRfcImportador() {
                    const rfcImportador = this.form.rfc_importador;
                    const rfcSolicitante = this.form.rfc_solicitante;
                    
                    // Verificar si el RFC del importador coincide con el del solicitante
                    if (rfcImportador !== rfcSolicitante) {
                        this.mostrarModalError = true;
                        return;
                    }
                    
                    // Si coincide, cargar información del solicitante en campos del importador
                    this.cargarDatosSolicitante();
                },
                copiarRfcSolicitanteAImportador() {
                    // El RFC del importador es el mismo que el del solicitante
                    this.form.rfc_importador = this.form.rfc_solicitante;
                    this.form.registro_nacional_contribuyentes = this.form.rfc_solicitante;
                },
                validarRfcImportador() {
                    // Actualizar registro nacional solo si los RFCs coinciden
                    if (this.form.rfc_importador === this.form.rfc_solicitante) {
                        this.form.registro_nacional_contribuyentes = this.form.rfc_importador;
                        this.mensajeAyuda = '✓ RFC correcto - El importador es el mismo solicitante';
                    } else if (this.form.rfc_importador && this.form.rfc_solicitante) {
                        this.mensajeAyuda = '❌ Error: El RFC debe ser igual al del solicitante';
                    } else {
                        this.mensajeAyuda = 'El importador debe tener el mismo RFC que el solicitante';
                    }
                },
                actualizarRegistroNacional() {
                    // Copiar RFC del solicitante al importador (mismo usuario)
                    this.form.rfc_importador = this.form.rfc_solicitante;
                    // Copiar RFC al registro nacional automáticamente
                    this.form.registro_nacional_contribuyentes = this.form.rfc_solicitante;
                    
                    const rfc = this.form.rfc_solicitante;
                    if (rfc && rfc.length >= 12 && rfc.length <= 13) {
                        this.mensajeAyuda = 'RFC copiado del solicitante. Haz clic en "Buscar" para cargar datos previos.';
                    } else {
                        this.mensajeAyuda = 'Complete el RFC del solicitante primero.';
                    }
                },
                async buscarImportadorPorRfc() {
                    const rfc = this.form.rfc_importador.toUpperCase();
                    if (rfc.length < 12 || rfc.length > 13) {
                        alert('⚠️ Por favor ingrese un RFC válido (12 o 13 caracteres)');
                        return;
                    }

                    this.buscandoRfc = true;
                    this.mensajeAyuda = '🔍 Buscando en base de datos...';

                    try {
                        const response = await fetch('{{ route("manifestations.buscarImportador") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ rfc: rfc })
                        });

                        const result = await response.json();

                        if (result.found) {
                            // Datos encontrados en BD
                            this.form.razon_social_importador = result.data.razon_social;
                            this.form.domicilio_fiscal_importador = result.data.domicilio_fiscal || '';
                            this.form.registro_nacional_contribuyentes = result.data.registro_nacional || rfc;
                            this.camposDesdeDB = true;
                            this.mensajeAyuda = '✓ Datos cargados desde registros previos';
                            
                            alert('✅ Datos del importador encontrados y cargados\n\n' +
                                  'RFC: ' + rfc + '\n' +
                                  'Razón Social: ' + result.data.razon_social + '\n\n' +
                                  'Los campos se han bloqueado. Si necesita modificarlos, recargue la página.');
                        } else {
                            // No encontrado - permitir edición manual
                            this.camposDesdeDB = false;
                            
                            // Solo limpiar si no vienen del EME
                            if (!this.isFromEme('razon_social_importador')) {
                                this.form.razon_social_importador = '';
                            }
                            if (!this.isFromEme('domicilio_fiscal_importador')) {
                                this.form.domicilio_fiscal_importador = '';
                            }
                            
                            this.mensajeAyuda = '⚠️ RFC no encontrado. Complete manualmente los datos.';
                            
                            alert('ℹ️ RFC no encontrado en registros previos\n\n' +
                                  'Los campos están ahora habilitados para edición manual:\n' +
                                  '• Razón Social\n' +
                                  '• Domicilio Fiscal\n\n' +
                                  'Estos datos se guardarán para futuras manifestaciones.');
                        }
                    } catch (error) {
                        console.error('Error al buscar importador:', error);
                        this.mensajeAyuda = '❌ Error al buscar. Intente nuevamente.';
                        alert('❌ Error al buscar en la base de datos. Por favor intente nuevamente.');
                    } finally {
                        this.buscandoRfc = false;
                    }
                },
                isFromEme(field) {
                    return this.emeData && this.emeData[field];
                },
                limpiarDatosBD() {
                    // Función para limpiar datos de BD y permitir edición manual
                    this.camposDesdeDB = false;
                    
                    // Solo limpiar campos que no vengan del EME
                    if (!this.isFromEme('razon_social_importador')) {
                        this.form.razon_social_importador = '';
                    }
                    if (!this.isFromEme('domicilio_fiscal_importador')) {
                        this.form.domicilio_fiscal_importador = '';
                    }
                    
                    this.mensajeAyuda = 'Campos habilitados para edición manual.';
                },
                addRfcConsulta() {
                    // Limpiar RFC temporal y mostrar formulario
                    this.tempRfc = '';
                    this.showRfcForm = true;
                },
                cancelarRfcConsulta() {
                    // Ocultar formulario y limpiar datos
                    this.showRfcForm = false;
                    this.tempRfc = '';
                },
                async aceptarRfcConsulta() {
                    if (!this.tempRfc || this.tempRfc.length < 12 || this.tempRfc.length > 13) {
                        alert('Por favor ingrese un RFC válido de 12 o 13 caracteres');
                        return;
                    }

                    const rfc = this.tempRfc.toUpperCase();
                    
                    // Verificar que no esté duplicado
                    const exists = this.consultationRfcs.find(item => item.rfc_consulta === rfc);
                    if (exists) {
                        alert('Este RFC ya ha sido agregado a la lista de consulta');
                        return;
                    }

                    this.buscandoRfcConsulta = true;

                    try {
                        const response = await fetch('{{ route("manifestations.buscarRfcConsulta") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ rfc: rfc })
                        });

                        const result = await response.json();

                        if (result.found) {
                            // RFC encontrado - agregar con datos de BD
                            this.consultationRfcs.push({
                                rfc_consulta: rfc,
                                razon_social: result.data.razon_social,
                                tipo_figura: result.data.tipo_figura || '',
                                fromDB: true,
                                source: result.data.source
                            });

                            let message = '✅ RFC encontrado en registros previos\n\n' +
                                          'RFC: ' + rfc + '\n' +
                                          'Razón Social: ' + result.data.razon_social + '\n' +
                                          'Fuente: ' + (result.data.source === 'importadores' ? 'Importadores' : 'Consultas previas');
                            
                            if (result.data.tipo_figura) {
                                message += '\nTipo Figura: ' + result.data.tipo_figura;
                            } else {
                                message += '\n\nSolo falta seleccionar el tipo de figura.';
                            }
                            
                            alert(message);
                        } else {
                            // RFC no encontrado - agregar para captura manual
                            this.consultationRfcs.push({
                                rfc_consulta: rfc,
                                razon_social: '',
                                tipo_figura: '',
                                fromDB: false,
                                source: 'manual'
                            });

                            alert('ℹ️ RFC no encontrado en registros previos\n\n' +
                                  'RFC: ' + rfc + '\n\n' +
                                  'Complete manualmente:\n' +
                                  '• Razón Social\n' +
                                  '• Tipo de Figura\n\n' +
                                  'Estos datos se guardarán para futuras consultas.');
                        }

                        // Limpiar RFC temporal y ocultar formulario
                        this.tempRfc = '';
                        this.showRfcForm = false;

                    } catch (error) {
                        console.error('Error al buscar RFC de consulta:', error);
                        alert('❌ Error al buscar en la base de datos. Por favor intente nuevamente.');
                    } finally {
                        this.buscandoRfcConsulta = false;
                    }
                },
                addCove() {
                    this.coves.push({
                        edocument: '',
                        metodo_valoracion: '',
                        numero_factura: '',
                        fecha_expedicion: '',
                        emisor: '',
                        loading: false,
                        fromEme: false  // Marcado como manual
                    });
                },
                removeCove(index) {
                    this.coves.splice(index, 1);
                },
                async fetchCoveData(index) {
                    const cove = this.coves[index];
                    if (!cove.edocument) return;
                    
                    cove.loading = true;
                    try {
                        // Aquí iría la lógica para buscar datos del COVE
                        // Por ahora solo simulamos la búsqueda
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        
                        // Datos de ejemplo - esto se reemplazaría con la API real
                        cove.numero_factura = 'FAC-' + Math.floor(Math.random() * 10000);
                        cove.emisor = 'Ejemplo Emisor';
                        cove.fecha_expedicion = new Date().toISOString().split('T')[0];
                        
                    } catch (error) {
                        console.error('Error al buscar COVE:', error);
                        alert('Error al consultar el COVE');
                    } finally {
                        cove.loading = false;
                    }
                },
                // Funciones para la sección MV
                addPedimento() {
                    this.pedimentos.push({
                        numero_pedimento: '',
                        patente: '',
                        aduana_clave: '430'
                    });
                },
                addIncrementable() {
                    this.incrementables.push({
                        concepto: '',
                        importe: 0,
                        moneda: 'USD',
                        tipo_cambio: 20.0000,
                        fecha_erogacion: '',
                        a_cargo_importador: true,
                        loading: false
                    });
                },
                addDecrementable() {
                    this.decrementables.push({
                        concepto: '',
                        importe: 0,
                        moneda: 'USD',
                        tipo_cambio: 20.0000,
                        fecha_erogacion: '',
                        loading: false
                    });
                },
                addPago() {
                    this.pagos.push({
                        status: 'paid',
                        fecha: '',
                        importe: 0,
                        moneda: 'USD',
                        tipo_cambio: 20.0000,
                        forma_pago: '',
                        loading: false
                    });
                },
                addRfc() {
                    this.consultationRfcs.push({
                        rfc_consulta: ''
                    });
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
                canContinueToStep2() {
                    // Verificar que el perfil del usuario esté completo
                    const profileCompleted = {{ Auth::user()->hasCompleteSolicitorProfile() ? 'true' : 'false' }};
                    if (!profileCompleted) {
                        alert('⚠️ Debe completar la información del solicitante antes de continuar. Por favor, complete su perfil o llene los campos del solicitante en esta sección.');
                        this.activeTab = 'solicitante';
                        return false;
                    }

                    // Verificar campos del importador (manifestación)
                    if (!this.form.rfc_importador || !this.form.razon_social_importador || !this.form.registro_nacional_contribuyentes) {
                        alert('⚠️ Debe completar todos los campos del importador antes de continuar.');
                        this.activeTab = 'manifestacion';
                        return false;
                    }

                    // Verificar COVEs (si hay, deben estar completos)
                    for (let i = 0; i < this.coves.length; i++) {
                        const cove = this.coves[i];
                        if (!cove.edocument || !cove.metodo_valoracion || !cove.numero_factura) {
                            alert('⚠️ Debe completar todos los campos obligatorios de los COVEs antes de continuar.');
                            this.activeTab = 'coves';
                            return false;
                        }
                    }

                    // Verificar RFCs de consulta (si hay, deben estar completos - OPCIONAL)
                    for (let i = 0; i < this.consultationRfcs.length; i++) {
                        const rfc = this.consultationRfcs[i];
                        if (rfc.rfc_consulta && !rfc.tipo_figura) {
                            alert('⚠️ Si ingresa un RFC de consulta, debe seleccionar el tipo de figura.');
                            this.activeTab = 'manifestacion';
                            return false;
                        }
                        if (!rfc.rfc_consulta && rfc.tipo_figura) {
                            alert('⚠️ Si selecciona un tipo de figura, debe ingresar el RFC correspondiente.');
                            this.activeTab = 'manifestacion';
                            return false;
                        }
                    }

                    // Verificar valor en aduana (campo requerido)
                    const valorAduana = document.querySelector('input[name="total_valor_aduana"]');
                    if (!valorAduana || !valorAduana.value) {
                        alert('⚠️ Debe ingresar el valor en aduana antes de continuar.');
                        this.activeTab = 'valor';
                        return false;
                    }

                    return true;
                }
            }
        }
    </script>

    <!-- Modal de Error RFC Incorrecto -->
    <div x-show="mostrarModalError" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" @click="mostrarModalError = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-gray-900">
                            RFC Incorrecto
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600">
                                El RFC del importador debe ser el mismo que el RFC del solicitante.
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                <strong>RFC del solicitante:</strong> <span class="font-mono text-blue-600" x-text="form.rfc_solicitante"></span><br>
                                <strong>RFC ingresado:</strong> <span class="font-mono text-red-600" x-text="form.rfc_importador"></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            @click="form.rfc_importador = form.rfc_solicitante; mostrarModalError = false"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Corregir RFC
                    </button>
                    <button type="button" 
                            @click="mostrarModalError = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
