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
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">PASO 1</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-slate-400">PASO 2</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-slate-400">PASO 3</div></div>
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
                            
                            <!-- Carga de Archivo M -->
                            <div class="mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold text-blue-900 uppercase flex items-center mb-2">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Cargar Archivo M (Opcional)
                                        </h4>
                                        <p class="text-xs text-slate-600">Precargue automáticamente los datos del importador y la manifestación desde un archivo M.</p>
                                    </div>
                                    <div class="ml-4">
                                        <label for="emeFile" class="cursor-pointer inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase rounded-sm shadow-md transition transform hover:-translate-y-0.5">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <span x-show="!isUploading">Seleccionar M</span>
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
                                                                    <option value="TIPFIG.AGE">Agente Aduanal</option>
                                                                    <option value="TIPFIG.AAD">Agencia Aduanal</option>
                                                                    <option value="TIPFIG.REP">Representante Legal</option>
                                                                    <option value="TIPFIG.OTR">Otro</option>
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
                                                <th class="px-3 py-3 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Seleccionar</th>
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
                                                    <td class="p-3 align-middle text-center">
                                                        <input type="radio" :name="`selected_cove`" :value="index" x-model="selectedCoveIndex" 
                                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                    </td>
                                                    <td class="p-3 align-top relative">
                                                        <input type="text" 
                                                            :name="`coves[${index}][edocument]`" 
                                                            x-model="cove.edocument" 
                                                            x-bind:class="cove.fromEme ? 'bg-blue-50 border-blue-300 text-blue-700 font-bold cursor-not-allowed' : 'bg-white border-slate-300'"
                                                            x-bind:readonly="cove.fromEme"
                                                            maxlength="20"
                                                            class="block w-full px-3 py-2 rounded-sm text-xs focus:ring-blue-900 focus:border-blue-900 font-mono uppercase text-slate-800 shadow-sm" 
                                                            required 
                                                            placeholder="COVE...">

                                                        <div x-show="cove.fromEme" class="absolute top-0 right-0 w-3 h-3 bg-blue-500 rounded-full border-2 border-white shadow-md" title="Del archivo M"></div>
                                                    </td>
                                                    <td class="p-3 align-top">
                                                        <select :name="`coves[${index}][metodo_valoracion]`" x-model="cove.metodo_valoracion" required class="w-full text-xs border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                                            <option value="">Seleccione...</option>
                                                            <option value="VALADU.VTM">Valor de transacción</option>
                                                            <option value="VALADU.VMI">Valor de transacción de las mercancías idénticas</option>
                                                            <option value="VALADU.VMS">Valor de transacción de las mercancías similares</option>
                                                            <option value="VALADU.VPU">Valor de precio unitario de venta</option>
                                                            <option value="VALADU.VR">Valor reconstruido de las mercancías importadas</option>
                                                            <option value="VALADU.A78">Valor determinado conforme a lo establecido en el artículo 78</option>
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
                                    <button type="button" @click="toggleMVSection()" 
                                        :disabled="coves.length === 0 || selectedCoveIndex === null"
                                        :class="(coves.length === 0 || selectedCoveIndex === null) ? 
                                            'inline-flex items-center px-6 py-3 bg-gray-400 text-gray-600 font-bold text-sm uppercase rounded-sm shadow-md cursor-not-allowed opacity-50' : 
                                            'inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm uppercase rounded-sm shadow-md transition transform hover:-translate-y-0.5'">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span x-text="showMVSection ? 'Ocultar Detalles MV' : 'Añadir MV'"></span>
                                    </button>
                                    <p x-show="coves.length === 0" class="text-xs text-red-600 mt-2">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        Debe agregar al menos un COVE antes de configurar la Manifestación de Valor
                                    </p>
                                    <p x-show="coves.length > 0 && selectedCoveIndex === null" class="text-xs text-orange-600 mt-2">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        Debe seleccionar un COVE de la tabla para asociar con la Manifestación de Valor
                                    </p>
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
                                                    <option value="VALADU.VTM">Valor de transacción</option>
                                                    <option value="VALADU.VMI">Valor de transacción de las mercancías idénticas</option>
                                                    <option value="VALADU.VMS">Valor de transacción de las mercancías similares</option>
                                                    <option value="VALADU.VPU">Valor de precio unitario de venta</option>
                                                    <option value="VALADU.VR">Valor reconstruido de las mercancías importadas</option>
                                                    <option value="VALADU.A78">Valor determinado conforme a lo establecido en el artículo 78</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">INCOTERM</label>
                                                <select name="incoterm" x-model="general.incoterm" required class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                                    <option value="">Seleccione INCOTERM...</option>
                                                    <option value="TIPINC.CFR">CFR - COSTE Y FLETE (PUERTO DE DESTINO CONVENIDO)</option>
                                                    <option value="TIPINC.CIF">CIF - COSTE, SEGURO Y FLETE (PUERTO DE DESTINO CONVENIDO)</option>
                                                    <option value="TIPINC.CPT">CPT - TRANSPORTE PAGADO HASTA (EL LUGAR DE DESTINO CONVENIDO)</option>
                                                    <option value="TIPINC.CIP">CIP - TRANSPORTE Y SEGURO PAGADOS HASTA (LUGAR DE DESTINO CONVENIDO)</option>
                                                    <option value="TIPINC.DAP">DAP - ENTREGADA EN LUGAR</option>
                                                    <option value="TIPINC.DDP">DDP - ENTREGADA DERECHOS PAGADOS (LUGAR DE DESTINO CONVENIDO)</option>
                                                    <option value="TIPINC.DPU">DPU - ENTREGADA Y DESCARGADA EN EL LUGAR ACORDADO</option>
                                                    <option value="TIPINC.EXW">EXW - EN FABRICA (LUGAR CONVENIDO)</option>
                                                    <option value="TIPINC.FCA">FCA - FRANCO TRANSPORTISTA (LUGAR DESIGNADO)</option>
                                                    <option value="TIPINC.FAS">FAS - FRANCO AL COSTADO DEL BUQUE (PUERTO DE CARGA CONVENIDO)</option>
                                                    <option value="TIPINC.FOB">FOB - FRANCO A BORDO (PUERTO DE CARGA CONVENIDO)</option>
                                                    <option value="TIPINC.DAF">DAF-ENTREGADA EN FRONTERA (LUGAR CONVENIDO)</option>
                                                    <option value="TIPINC.DAT">DAT-ENTREGADA EN TERMINAL</option>
                                                    <option value="TIPINC.DES">DES-ENTREGADA SOBRE BUQUE (PUERTO DE DESTINO CONVENIDO)</option>
                                                    <option value="TIPINC.DEQ">DEQ-ENTREGADA EN MUELLE (PUERTO DE DESTINO CONVENIDO)</option>
                                                    <option value="TIPINC.DDU">DDU-ENTREGADA DERECHOS NO PAGADOS (LUGAR DE DESTINO CONVENIDO)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- VINCULACIÓN ENTRE PARTES -->
                                    <div class="mb-8">
                                        <h3 class="text-xs font-bold text-yellow-800 uppercase border-b-2 border-yellow-800 mb-4 pb-1">Vinculación entre Partes</h3>
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-sm p-4">
                                            <p class="text-xs text-yellow-700 mb-4">¿Existe vinculación entre el importador y el vendedor/exportador? Indique si existe alguna relación comercial, familiar, de control o participación entre las partes</p>
                                            
                                            <div class="flex items-center space-x-6 mb-4">
                                                <label class="flex items-center">
                                                    <input type="radio" name="existe_vinculacion" value="1" x-model="form.existe_vinculacion" class="text-yellow-600 border-yellow-300 focus:ring-yellow-600">
                                                    <span class="ml-2 text-sm font-bold text-yellow-800">SÍ, existe vinculación</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="radio" name="existe_vinculacion" value="0" x-model="form.existe_vinculacion" class="text-yellow-600 border-yellow-300 focus:ring-yellow-600">
                                                    <span class="ml-2 text-sm font-bold text-yellow-800">NO, no existe vinculación</span>
                                                </label>
                                            </div>
                                            
                                            <!-- Campo de descripción cuando existe vinculación -->
                                            <div x-show="form.existe_vinculacion === '1'" class="mt-4">
                                                <label class="block text-xs font-bold text-yellow-800 uppercase mb-1">Describa la vinculación existente:</label>
                                                <textarea name="descripcion_vinculacion" 
                                                    x-model="form.descripcion_vinculacion"
                                                    rows="3" 
                                                    class="w-full text-sm border-yellow-300 rounded-sm focus:ring-yellow-600 focus:border-yellow-600" 
                                                    placeholder="Describa el tipo de vinculación (comercial, familiar, de control, etc.)"></textarea>
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
                                                                        <option value="{{ $aduana['aduana'] }}{{ $aduana['seccion'] }}">{{ $aduana['aduana'] }}{{ $aduana['seccion'] }} - {{ $aduana['denominacion'] }}</option>
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
                                            <h3 class="text-xs font-bold text-green-700 uppercase">Incrementables conforme al artículo 65 de la ley</h3>
                                            <button type="button" @click="addIncrementable()" class="text-xs bg-white border border-green-600 text-green-700 px-3 py-1 rounded-sm font-bold hover:bg-green-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(inc, i) in incrementables" :key="i">
                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-4 rounded border border-slate-200 shadow-sm relative">
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Concepto</label>
                                                        <select :name="`incrementables[${i}][concepto]`" x-model="inc.concepto" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione...</option>
                                                            @foreach($catalogs['incrementables'] ?? [] as $inc_cat)
                                                                <option value="{{ $inc_cat['clave'] }}">{{ $inc_cat['descripcion'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                        <input type="date" :name="`incrementables[${i}][fecha_erogacion]`" x-model="inc.fecha_erogacion" @change="updateExchangeRate('incrementables', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                                        <select :name="`incrementables[${i}][moneda]`" x-model="inc.moneda" @change="updateExchangeRate('incrementables', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione moneda...</option>
                                                            @foreach($currencies ?? [] as $currency)
                                                                <option value="{{ $currency['code'] }}">{{ $currency['code'] }} - {{ $currency['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                                        <input type="number" step="0.01" :name="`incrementables[${i}][importe]`" x-model="inc.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-1">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">T. Cambio</label>
                                                        <input type="number" step="0.0001" :name="`incrementables[${i}][tipo_cambio]`" x-model="inc.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">¿A cargo del importador?</label>
                                                        <div class="flex items-center gap-4 pt-2">
                                                            <label class="inline-flex items-center">
                                                                <input type="radio" :name="`incrementables[${i}][a_cargo_importador]`" value="1" x-model="inc.a_cargo_importador" class="text-green-600 focus:ring-green-500">
                                                                <span class="ml-2 text-xs">Sí</span>
                                                            </label>
                                                            <label class="inline-flex items-center">
                                                                <input type="radio" :name="`incrementables[${i}][a_cargo_importador]`" value="0" x-model="inc.a_cargo_importador" class="text-green-600 focus:ring-green-500">
                                                                <span class="ml-2 text-xs">No</span>
                                                            </label>
                                                        </div>
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
                                            <div>
                                                <h3 class="text-xs font-bold text-red-700 uppercase">Información que no integra el valor de transacción conforme al artículo 66 de la ley aduanera (DECREMENTABLES)</h3>
                                                <p class="text-[10px] text-red-600 mt-1">(Se considera que se distinguen del precio pagado las cantidades que se mencionan, se detallan o especifican separadamente del precio pagado en el comprobante fiscal digital o en el documento equivalente)</p>
                                            </div>
                                            <button type="button" @click="addDecrementable()" class="text-xs bg-white border border-red-600 text-red-700 px-3 py-1 rounded-sm font-bold hover:bg-red-50 transition uppercase whitespace-nowrap">
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
                                                                <option value="{{ $dec_cat['clave'] }}">{{ $dec_cat['descripcion'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                        <input type="date" :name="`decrementables[${i}][fecha_erogacion]`" x-model="dec.fecha_erogacion" @change="updateExchangeRate('decrementables', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Moneda</label>
                                                        <select :name="`decrementables[${i}][moneda]`" x-model="dec.moneda" @change="updateExchangeRate('decrementables', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione moneda...</option>
                                                            @foreach($currencies ?? [] as $currency)
                                                                <option value="{{ $currency['code'] }}">{{ $currency['code'] }} - {{ $currency['name'] }}</option>
                                                            @endforeach
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

                                    <!-- PRECIO PAGADO -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-slate-800 pb-1">
                                            <h3 class="text-xs font-bold text-slate-800 uppercase">Precio pagado</h3>
                                            <button type="button" @click="addPagoPagado()" class="text-xs bg-white border border-slate-800 text-slate-800 px-3 py-1 rounded-sm font-bold hover:bg-slate-100 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(pago, i) in pagosPagados" :key="i">
                                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-white p-4 rounded border border-slate-200 shadow-sm relative">
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                        <input type="date" :name="`pagos_pagados[${i}][fecha]`" x-model="pago.fecha" @change="updateExchangeRate('pagosPagados', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                                        <input type="number" step="0.01" :name="`pagos_pagados[${i}][importe]`" x-model="pago.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                                    </div>
                                                    <div class="md:col-span-3">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Forma de Pago</label>
                                                        <select :name="`pagos_pagados[${i}][forma_pago]`" x-model="pago.forma_pago" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione un valor</option>
                                                            @foreach($catalogs['formas_de_pago'] ?? [] as $forma)
                                                                <option value="{{ $forma['clave'] }}">{{ $forma['descripcion'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-12" x-show="pago.forma_pago === 'FORPAG.OT'">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Especifique</label>
                                                        <input type="text" :name="`pagos_pagados[${i}][especifique]`" x-model="pago.especifique" maxlength="70" class="w-full text-xs rounded-sm border-slate-300" placeholder="Especifique el tipo de pago...">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Tipo de moneda</label>
                                                        <select :name="`pagos_pagados[${i}][moneda]`" x-model="pago.moneda" @change="updateExchangeRate('pagosPagados', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                            <option value="">Seleccione un valor</option>
                                                            @foreach($currencies ?? [] as $currency)
                                                                <option value="{{ $currency['code'] }}">{{ $currency['code'] }} - {{ $currency['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Tipo de cambio</label>
                                                        <input type="number" step="0.001" :name="`pagos_pagados[${i}][tipo_cambio]`" x-model="pago.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                                    </div>
                                                    <div class="md:col-span-1 flex justify-center pb-1">
                                                        <button type="button" @click="pagosPagados.splice(i,1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- PRECIO POR PAGAR -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-blue-600 pb-1">
                                            <h3 class="text-xs font-bold text-blue-700 uppercase">Precio por pagar</h3>
                                            <button type="button" @click="addPagoPorPagar()" class="text-xs bg-white border border-blue-600 text-blue-700 px-3 py-1 rounded-sm font-bold hover:bg-blue-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(pago, i) in pagosPorPagar" :key="i">
                                                <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end mb-3">
                                                        <div class="md:col-span-2">
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                            <input type="date" :name="`pagos_por_pagar[${i}][fecha]`" x-model="pago.fecha" @change="updateExchangeRate('pagosPorPagar', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Importe</label>
                                                            <input type="number" step="0.01" :name="`pagos_por_pagar[${i}][importe]`" x-model="pago.importe" required class="w-full text-xs rounded-sm border-slate-300">
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Forma de Pago</label>
                                                            <select :name="`pagos_por_pagar[${i}][forma_pago]`" x-model="pago.forma_pago" required class="w-full text-xs rounded-sm border-slate-300">
                                                                <option value="">Seleccione un valor</option>
                                                                @foreach($catalogs['formas_de_pago'] ?? [] as $forma)
                                                                    <option value="{{ $forma['clave'] }}">{{ $forma['descripcion'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="md:col-span-12" x-show="pago.forma_pago === 'FORPAG.OT'">
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Especifique</label>
                                                            <input type="text" :name="`pagos_por_pagar[${i}][especifique]`" x-model="pago.especifique" maxlength="70" class="w-full text-xs rounded-sm border-slate-300" placeholder="Especifique el tipo de pago...">
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Tipo de moneda</label>
                                                            <select :name="`pagos_por_pagar[${i}][moneda]`" x-model="pago.moneda" @change="updateExchangeRate('pagosPorPagar', i)" required class="w-full text-xs rounded-sm border-slate-300">
                                                                <option value="">Seleccione un valor</option>
                                                                @foreach($currencies ?? [] as $currency)
                                                                    <option value="{{ $currency['code'] }}">{{ $currency['code'] }} - {{ $currency['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Tipo de cambio</label>
                                                            <input type="number" step="0.001" :name="`pagos_por_pagar[${i}][tipo_cambio]`" x-model="pago.tipo_cambio" class="w-full text-xs rounded-sm border-slate-300 bg-slate-100" readonly>
                                                        </div>
                                                        <div class="md:col-span-1 flex justify-center pb-1">
                                                            <button type="button" @click="pagosPorPagar.splice(i,1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Momento(s) o situación(es) cuando se realizará el pago:</label>
                                                        <textarea :name="`pagos_por_pagar[${i}][situacion_pago]`" x-model="pago.situacion_pago" rows="2" class="w-full text-xs rounded-sm border-slate-300" placeholder="Describa el momento o situación..."></textarea>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- COMPENSO PAGO -->
                                    <div class="mb-8">
                                        <div class="flex justify-between items-center mb-4 border-b-2 border-purple-600 pb-1">
                                            <h3 class="text-xs font-bold text-purple-700 uppercase">Compenso Pago</h3>
                                            <button type="button" @click="addCompensoPago()" class="text-xs bg-white border border-purple-600 text-purple-700 px-3 py-1 rounded-sm font-bold hover:bg-purple-50 transition uppercase">
                                                + Agregar
                                            </button>
                                        </div>
                                        <div class="space-y-4">
                                            <template x-for="(comp, i) in compensaciones" :key="i">
                                                <div class="bg-white p-4 rounded border border-slate-200 shadow-sm">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                                        <div>
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Fecha</label>
                                                            <input type="date" :name="`compensaciones[${i}][fecha]`" x-model="comp.fecha" required class="w-full text-xs rounded-sm border-slate-300">
                                                        </div>
                                                        <div>
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase required">Forma de Pago</label>
                                                            <select :name="`compensaciones[${i}][forma_pago]`" x-model="comp.forma_pago" required class="w-full text-xs rounded-sm border-slate-300">
                                                                <option value="">Seleccione un valor</option>
                                                                @foreach($catalogs['formas_de_pago'] ?? [] as $forma)
                                                                    <option value="{{ $forma['clave'] }}">{{ $forma['descripcion'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3" x-show="comp.forma_pago === 'FORPAG.OT'">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Especifique</label>
                                                        <input type="text" :name="`compensaciones[${i}][especifique]`" x-model="comp.especifique" maxlength="70" class="w-full text-xs rounded-sm border-slate-300" placeholder="Especifique el tipo de pago...">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Motivo por lo que se realizó:</label>
                                                        <textarea :name="`compensaciones[${i}][motivo]`" x-model="comp.motivo" rows="2" maxlength="1000" class="w-full text-xs rounded-sm border-slate-300" placeholder="Describa el motivo..."></textarea>
                                                    </div>
                                                    <div class="flex items-start gap-3">
                                                        <div class="flex-1">
                                                            <label class="text-xs text-slate-500 font-bold block mb-1 uppercase">Prestación de la mercancía:</label>
                                                            <textarea :name="`compensaciones[${i}][prestacion_mercancia]`" x-model="comp.prestacion_mercancia" rows="2" maxlength="1000" class="w-full text-xs rounded-sm border-slate-300" placeholder="Describa la prestación..."></textarea>
                                                        </div>
                                                        <div class="pt-6">
                                                            <button type="button" @click="compensaciones.splice(i,1)" class="text-slate-400 hover:text-red-600 p-2">✕</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
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
                                        <div x-show="isFromEme('total_precio_pagado')" class="absolute top-6 right-0 w-3 h-3 bg-blue-500 rounded-full border-2 border-white shadow-md" title="Del archivo M"></div>
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
                                        <div x-show="isFromEme('total_valor_aduana')" class="absolute top-0 right-0 w-3 h-3 bg-blue-500 rounded-full border-2 border-white shadow-md" title="Del archivo M"></div>
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
                showVucemPreview: false, // Mostrar/ocultar vista previa VUCEM
                consultationRfcs: @if(isset($manifestation) && $manifestation->consultationRfcs->count() > 0) @json($manifestation->consultationRfcs) @else [] @endif,
                coves: @if(isset($manifestation) && $manifestation->coves->count() > 0) @json($manifestation->coves) @else [] @endif,
                selectedCoveIndex: null, // Índice del COVE seleccionado
                aduanas: @json($catalogs['aduanas'] ?? []), // Catálogo de aduanas para autoselección
                // Variables para la sección MV
                showMVSection: false,
                general: {
                    metodo: '{{ old("metodo_valoracion_global", $manifestation->metodo_valoracion_global ?? "") }}',
                    incoterm: '{{ old("incoterm", $manifestation->incoterm ?? "") }}'
                },
                pedimentos: @if(isset($manifestation) && $manifestation->pedimentos && $manifestation->pedimentos->count() > 0) @json($manifestation->pedimentos) @else [{numero_pedimento: '', patente: '', aduana_clave: '430'}] @endif,
                incrementables: @if(isset($incrementables) && $incrementables->count() > 0) @json($incrementables) @else [] @endif,
                decrementables: @if(isset($decrementables) && $decrementables->count() > 0) @json($decrementables) @else [] @endif,
                pagosPagados: @if(isset($manifestation) && $manifestation->payments && $manifestation->payments->where('status', 'paid')->count() > 0) @json($manifestation->payments->where('status', 'paid')->values()) @else [] @endif,
                pagosPorPagar: @if(isset($manifestation) && $manifestation->payments && $manifestation->payments->where('status', 'payable')->count() > 0) @json($manifestation->payments->where('status', 'payable')->values()) @else [] @endif,
                compensaciones: @if(isset($manifestation) && $manifestation->compensations && $manifestation->compensations->count() > 0) @json($manifestation->compensations) @else [] @endif,
                form: {
                    rfc_solicitante: '{{ Auth::user()->rfc ?? "" }}',
                    rfc_importador: '{{ old("rfc_importador", $manifestation->rfc_importador ?? "") }}',
                    razon_social_importador: '{{ old("razon_social_importador", $manifestation->razon_social_importador ?? "") }}',
                    registro_nacional_contribuyentes: '{{ old("registro_nacional_contribuyentes", $manifestation->registro_nacional_contribuyentes ?? "") }}',
                    existe_vinculacion: '{{ old("existe_vinculacion", $manifestation->existe_vinculacion ?? "") }}',
                    descripcion_vinculacion: '{{ old("descripcion_vinculacion", $manifestation->descripcion_vinculacion ?? "") }}'
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
                        this.form.registro_nacional_contribuyentes = '';
                        this.form.existe_vinculacion = '';
                        this.form.descripcion_vinculacion = '';
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
                            // VALIDAR RFC DEL SOLICITANTE
                            const rfcSolicitante = '{{ Auth::user()->rfc }}';
                            const rfcDelArchivo = result.data.rfc_importador || 
                                                 (result.data.rfc_consultable_eme ? result.data.rfc_consultable_eme.rfc_consulta : null);
                            
                            if (rfcDelArchivo && rfcSolicitante !== rfcDelArchivo) {
                                alert('❌ Error de Validación\n\n' +
                                      'El RFC del archivo M no coincide con el RFC del solicitante:\n\n' +
                                      `✓ RFC del Solicitante: ${rfcSolicitante}\n` +
                                      `❌ RFC del Archivo M: ${rfcDelArchivo}\n\n` +
                                      'Solo puede cargar archivos M que correspondan a su RFC.');
                                return;
                            }
                            
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
                            
                            alert('✅ Archivo M procesado exitosamente\n\n' + 
                                  'Datos precargados:\n' + summary.join('\n') + 
                                  '\n\nLos campos marcados son de solo lectura.\n' +
                                  'Complete manualmente los campos restantes.');
                        } else {
                            alert('❌ Error: ' + (result.message || 'No se pudo procesar el archivo M'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('❌ Ocurrió un error al procesar el archivo M.');
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

                    // Precargar COVEs del archivo M
                    if (this.emeData.coves && this.emeData.coves.length > 0) {
                        this.coves = this.emeData.coves.map(cove => ({
                            ...cove,
                            fromEme: true,  // Marcar como proveniente del EME
                            loading: false
                        }));
                        console.log('COVEs cargados del EME:', this.coves);
                    }

                    // Precargar pedimentos del archivo M
                    if (this.emeData.pedimentos && this.emeData.pedimentos.length > 0) {
                        this.pedimentos = this.emeData.pedimentos.map(ped => {
                            const pedimento = {
                                numero_pedimento: ped.numero_pedimento || '',
                                patente: ped.patente || '',
                                aduana_clave: ped.aduana_clave || '430',
                                fromEme: true
                            };
                            
                            // Autoseleccionar aduana basada en el código del número de pedimento
                            if (pedimento.numero_pedimento) {
                                const numero = pedimento.numero_pedimento.replace(/\s/g, ''); // Quitar espacios
                                if (numero.length >= 5) {
                                    // Extraer código de aduana completo (3 dígitos: código + sección)
                                    let codigoCompleto;
                                    if (numero.length >= 16) {
                                        // Formato con año: YYAAAPPPPNNNNNNN
                                        codigoCompleto = numero.substring(2, 5); // 3 dígitos
                                    } else {
                                        // Formato sin año: AAAPPPPNNNNNNN
                                        codigoCompleto = numero.substring(0, 3); // 3 dígitos
                                    }
                                    this.autoSelectAduana(pedimento, codigoCompleto);
                                }
                            }
                            
                            return pedimento;
                        });
                        console.log('Pedimentos cargados del EME:', this.pedimentos);
                    }

                    // Precargar incrementables del archivo M
                    if (this.emeData.adjustments) {
                        const incrementablesEme = this.emeData.adjustments.filter(adj => adj.type === 'incrementable');
                        if (incrementablesEme.length > 0) {
                            this.incrementables = incrementablesEme.map(inc => ({
                                concepto: inc.concepto || '',
                                importe: inc.importe || 0,
                                moneda: inc.moneda || 'USD',
                                tipo_cambio: inc.tipo_cambio || 20.0000,
                                fecha_erogacion: inc.fecha_erogacion || '',
                                a_cargo_importador: inc.a_cargo_importador ?? 1,
                                fromEme: true,
                                loading: false
                            }));
                            console.log('Incrementables cargados del EME:', this.incrementables);
                        }

                        // Precargar decrementables del archivo M
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

                    // Precargar pagos del archivo M
                    if (this.emeData.payments && this.emeData.payments.length > 0) {
                        // Separar pagos según su status
                        this.pagosPagados = this.emeData.payments
                            .filter(pago => pago.status === 'paid')
                            .map(pago => ({
                                fecha: pago.fecha || '',
                                importe: pago.importe || 0,
                                moneda: pago.moneda || 'USD',
                                tipo_cambio: pago.tipo_cambio || 20.0000,
                                forma_pago: pago.forma_pago || '01',
                                fromEme: true,
                                loading: false
                            }));
                        
                        this.pagosPorPagar = this.emeData.payments
                            .filter(pago => pago.status === 'payable')
                            .map(pago => ({
                                fecha: pago.fecha || '',
                                importe: pago.importe || 0,
                                moneda: pago.moneda || 'USD',
                                tipo_cambio: pago.tipo_cambio || 20.0000,
                                forma_pago: pago.forma_pago || '01',
                                situacion_pago: pago.situacion_pago || '',
                                fromEme: true,
                                loading: false
                            }));
                        
                        console.log('Pagos Pagados cargados del EME:', this.pagosPagados);
                        console.log('Pagos Por Pagar cargados del EME:', this.pagosPorPagar);
                    }

                    // Precargar compensaciones del archivo M
                    if (this.emeData.compensaciones && this.emeData.compensaciones.length > 0) {
                        this.compensaciones = this.emeData.compensaciones.map(comp => ({
                            fecha: comp.fecha || '',
                            forma_pago: comp.forma_pago || '',
                            motivo: comp.motivo || '',
                            prestacion_mercancia: comp.prestacion_mercancia || '',
                            fromEme: true
                        }));
                        console.log('Compensaciones cargadas del EME:', this.compensaciones);
                    }

                    // Precargar valores de aduana del archivo M
                    if (this.emeData.total_precio_pagado) {
                        const precioPagadoInput = document.querySelector('input[name="total_precio_pagado"]');
                        if (precioPagadoInput) precioPagadoInput.value = this.emeData.total_precio_pagado;
                    }
                    if (this.emeData.total_valor_aduana) {
                        const valorAduanaInput = document.querySelector('input[name="total_valor_aduana"]');
                        if (valorAduanaInput) valorAduanaInput.value = this.emeData.total_valor_aduana;
                    }

                    // Autoseleccionar INCOTERM del archivo M (registro 505, campo 5)
                    if (this.emeData.incoterm) {
                        const incotermFromFile = this.emeData.incoterm.toUpperCase().trim();
                        
                        // Mapeo de códigos cortos a claves VUCEM
                        const incotermMap = {
                            'EXW': 'TIPINC.EXW',
                            'FCA': 'TIPINC.FCA',
                            'CPT': 'TIPINC.CPT',
                            'CIP': 'TIPINC.CIP',
                            'DAP': 'TIPINC.DAP',
                            'DPU': 'TIPINC.DPU',
                            'DDP': 'TIPINC.DDP',
                            'FAS': 'TIPINC.FAS',
                            'FOB': 'TIPINC.FOB',
                            'CFR': 'TIPINC.CFR',
                            'CIF': 'TIPINC.CIF',
                            'DAF': 'TIPINC.DAF',
                            'DAT': 'TIPINC.DAT',
                            'DES': 'TIPINC.DES',
                            'DEQ': 'TIPINC.DEQ',
                            'DDU': 'TIPINC.DDU'
                        };
                        
                        // Verificar que el incoterm del archivo sea válido
                        if (incotermMap[incotermFromFile]) {
                            this.general.incoterm = incotermMap[incotermFromFile];
                            console.log(`✅ INCOTERM autoseleccionado del archivo M: ${incotermFromFile} → ${incotermMap[incotermFromFile]}`);
                        } else {
                            console.warn(`⚠️ INCOTERM del archivo M no reconocido: ${incotermFromFile}`);
                        }
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
                    this.form.registro_nacional_contribuyentes = this.form.rfc_solicitante;
                    
                    this.mensajeAyuda = '✓ Datos del solicitante cargados correctamente';
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
                            
                            this.mensajeAyuda = '⚠️ RFC no encontrado. Complete manualmente los datos.';
                            
                            alert('ℹ️ RFC no encontrado en registros previos\n\n' +
                                  'Los campos están ahora habilitados para edición manual:\n' +
                                  '• Razón Social\n\n' +
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
                    if (!this.isFromEme('existe_vinculacion')) {
                        this.form.existe_vinculacion = '';
                        this.form.descripcion_vinculacion = '';
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
                        // loading: false,  <-- ESTO YA NO SE NECESITA
                        fromEme: false
                    });
                },
                removeCove(index) {
                    // Si se elimina el COVE seleccionado, limpiar la selección
                    if (this.selectedCoveIndex === index) {
                        this.selectedCoveIndex = null;
                        this.showMVSection = false; // Ocultar la sección MV si estaba visible
                    }
                    // Si se elimina un COVE anterior al seleccionado, ajustar el índice
                    else if (this.selectedCoveIndex !== null && index < this.selectedCoveIndex) {
                        this.selectedCoveIndex--;
                    }
                    
                    this.coves.splice(index, 1);
                },
                toggleMVSection() {
                    // Validar que hay al menos un COVE
                    if (this.coves.length === 0) {
                        alert('⚠️ Debe agregar al menos un COVE antes de configurar la Manifestación de Valor.\n\nVaya a la pestaña "COVEs" y agregue al menos un COVE.');
                        this.activeTab = 'coves';
                        return;
                    }
                    
                    // Validar que hay un COVE seleccionado
                    if (this.selectedCoveIndex === null) {
                        alert('⚠️ Debe seleccionar un COVE específico para asociar con la Manifestación de Valor.\n\nSeleccione uno de los COVEs de la tabla usando el botón de selección.');
                        this.activeTab = 'coves';
                        return;
                    }
                    
                    // Si hay COVEs y uno está seleccionado, permitir mostrar/ocultar la sección MV
                    this.showMVSection = !this.showMVSection;
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
                        a_cargo_importador: 1,
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
                addPagoPagado() {
                    this.pagosPagados.push({
                        fecha: '',
                        importe: 0,
                        moneda: 'USD',
                        tipo_cambio: 20.0000,
                        forma_pago: '',
                        especifique: '',
                        loading: false
                    });
                },
                addPagoPorPagar() {
                    this.pagosPorPagar.push({
                        fecha: '',
                        importe: 0,
                        moneda: 'USD',
                        tipo_cambio: 20.0000,
                        forma_pago: '',
                        especifique: '',
                        situacion_pago: '',
                        loading: false
                    });
                },
                addCompensoPago() {
                    this.compensaciones.push({
                        fecha: '',
                        forma_pago: '',
                        especifique: '',
                        motivo: '',
                        prestacion_mercancia: ''
                    });
                },
                addRfc() {
                    this.consultationRfcs.push({
                        rfc_consulta: ''
                    });
                },
                formatPedimento(ped) {
                    // No formatear si viene del archivo EME (ya está formateado correctamente en el backend)
                    if (ped.fromEme) {
                        return;
                    }
                    
                    let val = ped.numero_pedimento.replace(/\D/g, '');
                    let parts = [];
                    let codigoAduana = '';
                    
                    // Si el valor tiene menos de 16 dígitos (no incluye año), agregar año actual
                    if (val.length > 0 && val.length < 16) {
                        const currentYear = new Date().getFullYear().toString().slice(-2); // Últimos 2 dígitos del año actual
                        parts.push(currentYear);
                        if (val.length > 0) {
                            codigoAduana = val.substring(0, 3).padStart(3, '0'); // Aduana con ceros a la izquierda
                            parts.push(codigoAduana);
                        }
                        if (val.length > 3) parts.push(val.substring(3, 7));   // Patente  
                        if (val.length > 7) parts.push(val.substring(7));      // Folio
                    } else {
                        // Lógica original para números que ya incluyen el año
                        if (val.length > 0) parts.push(val.substring(0, 2));
                        if (val.length > 2) {
                            codigoAduana = val.substring(2, 5);
                            parts.push(codigoAduana);
                        }
                        if (val.length > 5) parts.push(val.substring(5, 9));
                        if (val.length > 9) parts.push(val.substring(9));
                    }
                    ped.numero_pedimento = parts.join('  ');
                    
                    // Autoseleccionar aduana si tenemos código de aduana (3 dígitos: código + sección)
                    if (codigoAduana) {
                        this.autoSelectAduana(ped, codigoAduana);
                    }
                },
                autoSelectAduana(pedimento, codigoCompleto) {
                    // Extraer código de aduana (2 dígitos) y sección (1 dígito)
                    // Ejemplo: "070" -> aduana="07", seccion="0"
                    const codigoAduana = codigoCompleto.substring(0, 2).replace(/^0+/, '') || '0'; // Primeros 2 dígitos sin ceros a la izquierda
                    const seccionAduana = codigoCompleto.substring(2, 3) || '0'; // Tercer dígito
                    
                    // Buscar la aduana que coincida con código Y sección
                    const aduanaEncontrada = this.aduanas.find(aduana => 
                        (aduana.aduana === codigoAduana || aduana.aduana.padStart(2, '0') === codigoAduana.padStart(2, '0')) &&
                        aduana.seccion === seccionAduana
                    );
                    
                    if (aduanaEncontrada) {
                        // Asignar el código y sección juntos: "070"
                        pedimento.aduana_clave = `${aduanaEncontrada.aduana}${aduanaEncontrada.seccion}`;
                        console.log(`✅ Aduana autoseleccionada: ${aduanaEncontrada.aduana}${aduanaEncontrada.seccion} - ${aduanaEncontrada.denominacion}`);
                    } else {
                        console.warn(`⚠️ No se encontró aduana para código: ${codigoAduana} sección: ${seccionAduana} (${codigoCompleto})`);
                    }
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

                    // Verificar COVEs (OBLIGATORIO - debe haber al menos uno y uno seleccionado)
                    if (this.coves.length === 0) {
                        alert('⚠️ Debe agregar al menos un COVE antes de continuar.\n\nVaya a la pestaña "COVEs" y agregue la información requerida.');
                        this.activeTab = 'coves';
                        return false;
                    }
                    
                    if (this.selectedCoveIndex === null) {
                        alert('⚠️ Debe seleccionar un COVE específico para asociar con la Manifestación de Valor.\n\nVaya a la pestaña "COVEs" y seleccione uno de los COVEs disponibles.');
                        this.activeTab = 'coves';
                        return false;
                    }
                    
                    // Verificar que el COVE seleccionado esté completo
                    if (this.selectedCoveIndex !== null && this.selectedCoveIndex < this.coves.length) {
                        const selectedCove = this.coves[this.selectedCoveIndex];
                        if (!selectedCove.edocument || !selectedCove.metodo_valoracion || !selectedCove.numero_factura) {
                            alert('⚠️ El COVE seleccionado debe tener todos sus campos obligatorios completos antes de continuar.');
                            this.activeTab = 'coves';
                            return false;
                        }
                    }
                    
                    // Verificar que todos los COVEs están completos (opcional, pero si existen deben estar completos)
                    for (let i = 0; i < this.coves.length; i++) {
                        const cove = this.coves[i];
                        if (cove.edocument && (!cove.metodo_valoracion || !cove.numero_factura)) {
                            alert('⚠️ Todos los COVEs que tengan información deben estar completos antes de continuar.');
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
                },
                async getExchangeRate(currency, date) {
                    if (!currency || !date) return 20.0000;
                    
                    if (currency === 'MXN') {
                        return 1.0000;
                    }
                    
                    try {
                        const response = await fetch(`{{ route('manifestations.exchangeRate') }}?currency=${currency}&date=${date}`);
                        const data = await response.json();
                        return data.rate || 20.0000;
                    } catch (error) {
                        console.warn('Error obteniendo tipo de cambio:', error);
                        return 20.0000;
                    }
                },
                async updateExchangeRate(type, index) {
                    let item;
                    if (type === 'incrementables') {
                        item = this.incrementables[index];
                    } else if (type === 'decrementables') {
                        item = this.decrementables[index];
                    } else if (type === 'pagos') {
                        item = this.pagos[index];
                    } else if (type === 'pagosPagados') {
                        item = this.pagosPagados[index];
                    } else if (type === 'pagosPorPagar') {
                        item = this.pagosPorPagar[index];
                    }
                    
                    if (!item || !item.moneda || (!item.fecha_erogacion && !item.fecha)) {
                        return;
                    }
                    
                    const fecha = item.fecha_erogacion || item.fecha;
                    const rate = await this.getExchangeRate(item.moneda, fecha);
                    item.tipo_cambio = rate;
                    
                    console.log(`ℹ️ Tipo de cambio actualizado: ${item.moneda} (${fecha}) = ${rate}`);
                },
                
                // Función de vista previa formato VUCEM (TEMPORAL - SOLO PRUEBA)
                generateVucemPreview() {
                    let cadena = '|';
                    
                    // RFC Importador
                    cadena += (this.form.rfc_importador || 'N/A') + '|';
                    
                    // RFCs de consulta (primer RFC y tipo figura)
                    if (this.consultationRfcs.length > 0) {
                        cadena += (this.consultationRfcs[0].rfc_consulta || '') + '|';
                        cadena += (this.consultationRfcs[0].tipo_figura || 'TIPFIG.OTR') + '|';
                    } else {
                        cadena += '|TIPFIG.OTR|';
                    }
                    
                    // Pedimentos (primer pedimento)
                    if (this.pedimentos.length > 0) {
                        cadena += (this.pedimentos[0].numero_pedimento || '') + '|';
                    } else {
                        cadena += '|';
                    }
                    
                    // COVEs (primer COVE)
                    if (this.coves.length > 0) {
                        cadena += (this.coves[0].edocument || '') + '|';
                    } else {
                        cadena += '|';
                    }
                    
                    // INCOTERM
                    cadena += (this.general.incoterm || 'TIPINC.FOB') + '|';
                    
                    // Vinculación
                    cadena += (this.form.existe_vinculacion == '1' ? '1' : '0') + '|';
                    
                    // Patente y Aduana (primer pedimento)
                    if (this.pedimentos.length > 0) {
                        cadena += (this.pedimentos[0].patente || '') + '|';
                        cadena += (this.pedimentos[0].aduana_clave || '') + '|';
                    } else {
                        cadena += '||';
                    }
                    
                    // PAGOS PAGADOS (primer pago)
                    if (this.pagosPagados.length > 0) {
                        const pago = this.pagosPagados[0];
                        cadena += (pago.fecha || '') + '|';
                        cadena += (pago.importe || '0') + '|';
                        cadena += (pago.forma_pago || 'FORPAG.EF') + '|';
                        if (pago.forma_pago === 'FORPAG.OT') {
                            cadena += (pago.especifique || '') + '|';
                        }
                        cadena += (pago.moneda || 'USD') + '|';
                        cadena += (pago.tipo_cambio || '1') + '|';
                    } else {
                        cadena += '||FORPAG.EF||USD|1|';
                    }
                    
                    // PAGOS POR PAGAR (primer pago)
                    if (this.pagosPorPagar.length > 0) {
                        const pago = this.pagosPorPagar[0];
                        cadena += (pago.fecha || '') + '|';
                        cadena += (pago.importe || '0') + '|';
                        cadena += (pago.situacion_pago || '') + '|';
                        cadena += (pago.forma_pago || 'FORPAG.EF') + '|';
                        if (pago.forma_pago === 'FORPAG.OT') {
                            cadena += (pago.especifique || '') + '|';
                        }
                        cadena += (pago.moneda || 'USD') + '|';
                        cadena += (pago.tipo_cambio || '1') + '|';
                    } else {
                        cadena += '||SITUACIÓN|FORPAG.EF||USD|1|';
                    }
                    
                    // COMPENSACIONES (primera compensación)
                    if (this.compensaciones.length > 0) {
                        const comp = this.compensaciones[0];
                        cadena += (comp.fecha || '') + '|';
                        cadena += (comp.motivo || '') + '|';
                        cadena += (comp.prestacion_mercancia || '') + '|';
                        cadena += (comp.forma_pago || 'FORPAG.EF') + '|';
                        if (comp.forma_pago === 'FORPAG.OT') {
                            cadena += (comp.especifique || '') + '|';
                        }
                    } else {
                        cadena += '||MOTIVO|MERCANCIA|FORPAG.EF||';
                    }
                    
                    // MÉTODO DE VALORACIÓN
                    cadena += (this.general.metodo || 'VALADU.VTM') + '|';
                    
                    // INCREMENTABLES (primer incrementable)
                    if (this.incrementables.length > 0) {
                        const inc = this.incrementables[0];
                        cadena += (inc.concepto || 'INCRE.CG') + '|';
                        cadena += (inc.fecha_erogacion || '') + '|';
                        cadena += (inc.importe || '0') + '|';
                        cadena += (inc.moneda || 'USD') + '|';
                        cadena += (inc.tipo_cambio || '1') + '|';
                        cadena += (inc.a_cargo_importador ? '1' : '0') + '|';
                    } else {
                        cadena += 'INCRE.CG||0|USD|1|0|';
                    }
                    
                    // DECREMENTABLES (primer decrementable)
                    if (this.decrementables.length > 0) {
                        const dec = this.decrementables[0];
                        cadena += (dec.concepto || 'DECRE.GR') + '|';
                        cadena += (dec.fecha_erogacion || '') + '|';
                        cadena += (dec.importe || '0') + '|';
                        cadena += (dec.moneda || 'USD') + '|';
                        cadena += (dec.tipo_cambio || '1') + '|';
                    } else {
                        cadena += 'DECRE.GR||0|USD|1|';
                    }
                    
                    // COVEs adicionales (segundo COVE si existe)
                    if (this.coves.length > 1) {
                        cadena += (this.coves[1].edocument || '') + '|';
                        cadena += (this.coves[1].metodo_valoracion || 'VALADU.VTM') + '|';
                    } else {
                        cadena += '|VALADU.VTM|';
                    }
                    
                    // Totales (ejemplo)
                    cadena += '0|0|0|0|0|';
                    
                    return cadena;
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
