<section>
    <!-- Mostrar errores de validación -->
    @if ($errors->any())
        <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Hay errores en el formulario:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Mensaje de éxito -->
    @if (session('status') === 'profile-updated')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 5000)"
            class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="font-semibold text-base">¡Datos guardados con éxito!</span>
        </div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Campo name oculto requerido para la validación -->
        <input type="hidden" name="name" value="{{ $user->name }}">
        <input type="hidden" name="email" value="{{ $user->email }}">

        <!-- RFC y Razón Social -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="rfc_solicitor" value="RFC del Solicitante" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="rfc_solicitor" name="rfc" type="text" class="mt-1 block w-full uppercase" :value="old('rfc', $user->rfc)" minlength="12" maxlength="13" />
                <x-input-error class="mt-2" :messages="$errors->get('rfc')" />
            </div>

            <div>
                <x-input-label for="razon_social" value="Denominación o Razón Social" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="razon_social" name="razon_social" type="text" class="mt-1 block w-full" :value="old('razon_social', $user->razon_social)" />
                <x-input-error class="mt-2" :messages="$errors->get('razon_social')" />
            </div>
        </div>

        <!-- Actividad Económica -->
        <div>
            <x-input-label for="actividad_economica" value="Actividad Económica Preponderante" class="font-bold text-slate-500 text-xs uppercase mb-1" />
            <textarea id="actividad_economica" name="actividad_economica" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('actividad_economica', $user->actividad_economica) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('actividad_economica')" />
        </div>

        <!-- Domicilio Fiscal -->
        <h4 class="text-sm font-bold text-blue-900 uppercase border-b border-blue-900 pb-2 mt-6">Domicilio Fiscal del Solicitante</h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <x-input-label for="pais" value="País" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="pais" name="pais" type="text" class="mt-1 block w-full" :value="old('pais', $user->pais ?? 'ESTADOS UNIDOS MEXICANOS')" />
                <x-input-error class="mt-2" :messages="$errors->get('pais')" />
            </div>

            <div>
                <x-input-label for="codigo_postal" value="Código Postal" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="codigo_postal" name="codigo_postal" type="text" class="mt-1 block w-full" :value="old('codigo_postal', $user->codigo_postal)" maxlength="10" />
                <x-input-error class="mt-2" :messages="$errors->get('codigo_postal')" />
            </div>

            <div>
                <x-input-label for="estado" value="Estado" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="estado" name="estado" type="text" class="mt-1 block w-full" :value="old('estado', $user->estado)" />
                <x-input-error class="mt-2" :messages="$errors->get('estado')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <x-input-label for="municipio" value="Municipio o Alcaldía" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="municipio" name="municipio" type="text" class="mt-1 block w-full" :value="old('municipio', $user->municipio)" />
                <x-input-error class="mt-2" :messages="$errors->get('municipio')" />
            </div>

            <div>
                <x-input-label for="localidad" value="Localidad" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="localidad" name="localidad" type="text" class="mt-1 block w-full" :value="old('localidad', $user->localidad)" />
                <x-input-error class="mt-2" :messages="$errors->get('localidad')" />
            </div>

            <div>
                <x-input-label for="colonia" value="Colonia" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="colonia" name="colonia" type="text" class="mt-1 block w-full" :value="old('colonia', $user->colonia)" />
                <x-input-error class="mt-2" :messages="$errors->get('colonia')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="calle" value="Calle" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="calle" name="calle" type="text" class="mt-1 block w-full" :value="old('calle', $user->calle)" />
                <x-input-error class="mt-2" :messages="$errors->get('calle')" />
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <x-input-label for="numero_exterior" value="No. Exterior" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                    <x-text-input id="numero_exterior" name="numero_exterior" type="text" class="mt-1 block w-full" :value="old('numero_exterior', $user->numero_exterior)" />
                    <x-input-error class="mt-2" :messages="$errors->get('numero_exterior')" />
                </div>

                <div>
                    <x-input-label for="numero_interior" value="No. Interior" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                    <x-text-input id="numero_interior" name="numero_interior" type="text" class="mt-1 block w-full" :value="old('numero_interior', $user->numero_interior)" />
                    <x-input-error class="mt-2" :messages="$errors->get('numero_interior')" />
                </div>
            </div>
        </div>

        <!-- Datos de Contacto -->
        <h4 class="text-sm font-bold text-blue-900 uppercase border-b border-blue-900 pb-2 mt-6">Datos de Contacto</h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <x-input-label for="lada" value="Lada" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="lada" name="lada" type="text" class="mt-1 block w-full" :value="old('lada', $user->lada)" maxlength="5" placeholder="444" />
                <x-input-error class="mt-2" :messages="$errors->get('lada')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="telefono" value="Teléfono" class="font-bold text-slate-500 text-xs uppercase mb-1" />
                <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full" :value="old('telefono', $user->telefono)" maxlength="20" placeholder="4115100" />
                <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t mt-6 pt-6">
            <x-primary-button>{{ __('Guardar Datos del Solicitante') }}</x-primary-button>
        </div>
    </form>
</section>
