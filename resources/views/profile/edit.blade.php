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