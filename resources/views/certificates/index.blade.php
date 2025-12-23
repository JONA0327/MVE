<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Certificados FIEL') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Encabezado de Usuario -->
            <div class="bg-blue-900 rounded-lg shadow-lg p-6 text-white flex items-center justify-between">
                <div class="flex items-center">
                    <div class="h-16 w-16 rounded-full bg-white text-blue-900 flex items-center justify-center font-bold text-2xl border-4 border-blue-200">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold">Gestión de Certificados FIEL</h3>
                        <p class="text-blue-200 text-sm">Configure sus certificados para firma digital</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 rounded-full {{ $hasCertificates ? 'bg-green-600' : 'bg-red-600' }} text-xs font-bold uppercase tracking-wide">
                        {{ $hasCertificates ? 'Configurado' : 'Sin Configurar' }}
                    </span>
                </div>
            </div>

            <!-- Alertas de éxito y errores -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Selector de Modo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Modo de Firma</h3>
                    
                    <form method="POST" action="{{ route('certificates.toggle-mode') }}" class="space-y-4">
                        @csrf
                        
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer {{ $user->use_system_certificates ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" name="use_system" value="1" 
                                       class="form-radio text-blue-600" 
                                       {{ $user->use_system_certificates ? 'checked' : '' }}
                                       {{ !$hasCertificates ? 'disabled' : '' }}
                                       onchange="this.form.submit()">
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">
                                        Usar certificados del sistema
                                        @if(!$hasCertificates)
                                            <span class="text-red-600 text-sm">(No disponible - debe cargar certificados)</span>
                                        @endif
                                    </div>
                                    <div class="text-gray-600 text-sm">Los certificados se cargan una vez y se usan automáticamente para firmar</div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer {{ !$user->use_system_certificates ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" name="use_system" value="0" 
                                       class="form-radio text-blue-600" 
                                       {{ !$user->use_system_certificates ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Cargar certificados manualmente</div>
                                    <div class="text-gray-600 text-sm">Se pedirán los certificados cada vez que vaya a firmar (más seguro)</div>
                                </div>
                            </label>
                        </div>
                    </form>
                </div>
            </div>

            @if($user->use_system_certificates || !$hasCertificates)
            <!-- Sección de Carga de Certificados -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ $hasCertificates ? 'Actualizar Certificados' : 'Cargar Certificados' }}
                    </h3>
                    
                    @if($hasCertificates && $certificateInfo)
                    <!-- Información del certificado actual -->
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h4 class="font-semibold text-green-800 mb-3">Certificado Actual</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Válido desde:</span>
                                <span class="text-gray-900">{{ $certificateInfo['valid_from'] }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Válido hasta:</span>
                                <span class="text-gray-900 {{ !$certificateInfo['is_valid'] ? 'text-red-600 font-bold' : '' }}">
                                    {{ $certificateInfo['valid_to'] }}
                                    @if(!$certificateInfo['is_valid'])
                                        <span class="text-red-600">(EXPIRADO)</span>
                                    @endif
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Subido:</span>
                                <span class="text-gray-900">{{ $certificateInfo['uploaded_at'] }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Estado:</span>
                                <span class="font-semibold {{ $certificateInfo['is_valid'] ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $certificateInfo['is_valid'] ? 'VÁLIDO' : 'EXPIRADO' }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Botón para eliminar certificados -->
                        <div class="mt-4">
                            <button type="button" onclick="confirmRemove()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Eliminar Certificados del Sistema
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Formulario de carga -->
                    <form method="POST" action="{{ route('certificates.upload') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label for="certificate_file" class="block text-sm font-medium text-gray-700 mb-2">
                                Archivo del Certificado (.cer)
                            </label>
                            <input type="file" 
                                   id="certificate_file" 
                                   name="certificate_file" 
                                   accept=".cer" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                   required>
                        </div>

                        <div>
                            <label for="private_key_file" class="block text-sm font-medium text-gray-700 mb-2">
                                Archivo de la Llave Privada (.key)
                            </label>
                            <input type="file" 
                                   id="private_key_file" 
                                   name="private_key_file" 
                                   accept=".key" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                   required>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña de la Llave Privada
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   required>
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <button type="submit" 
                                    class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ $hasCertificates ? 'Actualizar Certificados' : 'Cargar Certificados' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Información de seguridad -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Información de Seguridad</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Certificados del Sistema:</strong> Sus certificados se almacenan cifrados en la base de datos. Solo se desencriptan al momento de firmar.</li>
                                <li><strong>Carga Manual:</strong> Los certificados se solicitan cada vez que firma. Más seguro pero menos conveniente.</li>
                                <li><strong>Contraseñas:</strong> Las contraseñas se almacenan cifradas y nunca se muestran en pantalla.</li>
                                <li><strong>Archivos Temporales:</strong> Los archivos temporales para firma se eliminan automáticamente después del uso.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar certificados -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2">Confirmar Eliminación</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        ¿Está seguro de que desea eliminar los certificados del sistema? 
                        Esta acción no se puede deshacer y deberá volver a cargar los certificados si desea usarlos nuevamente.
                    </p>
                </div>
                <div class="items-center px-4 py-3 space-x-2">
                    <button id="confirmBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-auto hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Eliminar
                    </button>
                    <button id="cancelBtn" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-auto hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmRemove() {
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        document.getElementById('cancelBtn').onclick = function() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        document.getElementById('confirmBtn').onclick = function() {
            // Crear y enviar formulario para eliminar
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("certificates.remove") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('confirmModal').onclick = function(event) {
            if (event.target === this) {
                this.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>