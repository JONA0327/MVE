<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Clave de Webservice') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Configure su clave de autenticación para los servicios web de VUCEM. Esta clave se almacena de forma segura y encriptada.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update.webservice') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="webservice_key" :value="__('Clave de Webservice VUCEM')" />
            <x-text-input id="webservice_key" 
                         name="webservice_key" 
                         type="text" 
                         class="mt-1 block w-full font-mono text-sm" 
                         :value="old('webservice_key', $user->webservice_key)" 
                         autocomplete="off" 
                         placeholder="Ingrese su clave de webservice VUCEM..." />
            <x-input-error class="mt-2" :messages="$errors->get('webservice_key')" />
            
            <!-- Información adicional -->
            <div class="mt-2 text-xs text-gray-500 bg-gray-50 p-2 rounded border-l-2 border-gray-300">
                <p class="font-medium text-gray-700">ℹ️ Información importante:</p>
                <ul class="mt-1 space-y-1 text-gray-600">
                    <li>• Esta clave es única para cada usuario del sistema</li>
                    <li>• Se utiliza para autenticarse con los servicios web de VUCEM</li>
                    <li>• La información se almacena encriptada en la base de datos</li>
                    <li>• Solo usted puede ver y modificar esta clave</li>
                </ul>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar Clave') }}</x-primary-button>

            @if (session('webservice-updated') === 'webservice-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-medium"
                >{{ __('Clave de webservice actualizada.') }}</p>
            @endif
        </div>
    </form>
</section>