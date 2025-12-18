<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Encabezado de Usuario -->
            <div class="bg-blue-900 rounded-lg shadow-lg p-6 text-white flex items-center justify-between">
                <div class="flex items-center">
                    <div class="h-16 w-16 rounded-full bg-white text-blue-900 flex items-center justify-center font-bold text-2xl border-4 border-blue-200">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="ml-4">
                        <h3 class="text-2xl font-bold">{{ Auth::user()->name }}</h3>
                        <p class="text-blue-200 text-sm font-mono">RFC: {{ Auth::user()->rfc }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 rounded-full bg-blue-800 text-xs font-bold uppercase tracking-wide">
                        {{ Auth::user()->is_admin ? 'Administrador' : 'Operador' }}
                    </span>
                </div>
            </div>

            <!-- Alerta si el perfil no está completo -->
            @if(!Auth::user()->hasCompleteSolicitorProfile())
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-sm shadow">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 font-bold">
                            ⚠️ Complete su información como solicitante
                        </p>
                        <p class="text-xs text-yellow-600 mt-1">
                            Necesita completar los datos del solicitante en la sección "Datos del Solicitante" para poder crear manifestaciones de valor.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Tarjeta 1: Información Básica (Usamos los partials de Breeze pero envueltos en nuestro estilo) -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-t-4 border-slate-600">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Información de la Cuenta</h3>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Tarjeta 2: Seguridad / Contraseña -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-t-4 border-blue-600">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Seguridad y Contraseña</h3>
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <!-- Nueva Sección: Datos del Solicitante -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-t-4 border-green-600">
                <div class="max-w-4xl">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Datos del Solicitante (VUCEM)</h3>
                    <p class="text-sm text-slate-600 mb-6">Complete esta información para facilitar el llenado de sus manifestaciones de valor.</p>
                    @include('profile.partials.update-solicitor-form')
                </div>
            </div>

            <!-- Tarjeta 3: Zona de Peligro (Eliminar Cuenta) -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-t-4 border-red-500">
                <div class="max-w-xl">
                    <h3 class="text-lg font-bold text-red-700 mb-4 border-b border-red-100 pb-2">Zona de Peligro</h3>
                    <p class="text-sm text-slate-500 mb-4">Si elimina su cuenta, perderá acceso a todos sus trámites históricos.</p>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>