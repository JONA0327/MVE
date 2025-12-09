<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Gestión de Usuarios y Accesos
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ openModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alerta de Contraseña Generada (Solo se muestra una vez) -->
            @if (session('generated_password'))
                <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-6 rounded-r shadow-md relative">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-bold text-green-800">¡Usuario registrado con éxito!</h3>
                            <div class="mt-2 text-green-700">
                                <p>Por favor, comparta las siguientes credenciales con el usuario. <br><strong>Esta contraseña no se volverá a mostrar.</strong></p>
                                <div class="mt-4 bg-white p-3 rounded border border-green-200 inline-block">
                                    <span class="text-sm text-gray-500">Contraseña Temporal:</span>
                                    <span class="ml-2 text-xl font-mono font-bold text-green-900 select-all">{{ session('generated_password') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-t-4 border-blue-900">
                <div class="p-6 border-b border-gray-100 bg-slate-50 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Directorio de Usuarios</h3>
                        <p class="text-sm text-slate-500">Administre quién tiene acceso a la plataforma VUCEM Lite.</p>
                    </div>
                    <button @click="openModal = true" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-900 transition ease-in-out duration-150 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        Registrar Usuario
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nombre / Cargo</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Identificadores</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            @forelse ($users as $user)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-800 font-bold text-lg">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-slate-900">{{ $user->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900"><span class="font-bold text-slate-500 text-xs">RFC:</span> {{ $user->rfc }}</div>
                                        <div class="text-sm text-slate-900"><span class="font-bold text-slate-500 text-xs">USR:</span> {{ $user->username }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->is_admin)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Administrador</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Operador</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('¿Está seguro de eliminar a este usuario? Perderá el acceso inmediatamente.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-wide hover:underline">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-500 italic">
                                        No hay otros usuarios registrados en el sistema.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL DE REGISTRO (Slide-over) -->
        <div x-show="openModal" style="display: none;" class="fixed inset-0 overflow-hidden z-50">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 bg-slate-900 bg-opacity-75 transition-opacity" @click="openModal = false"></div>
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                    <div class="w-screen max-w-md bg-white shadow-xl transform transition-transform">
                        <div class="h-full flex flex-col bg-white shadow-xl overflow-y-scroll">
                            <div class="px-4 py-6 bg-blue-900 sm:px-6">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-lg font-bold text-white">Nuevo Usuario</h2>
                                    <button @click="openModal = false" class="text-blue-200 hover:text-white">
                                        <span class="sr-only">Cerrar</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-blue-200">
                                    La contraseña será generada automáticamente por el sistema.
                                </p>
                            </div>
                            <div class="relative flex-1 px-4 sm:px-6 py-6">
                                <form method="POST" action="{{ route('admin.users.store') }}">
                                    @csrf
                                    
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">Nombre Completo</label>
                                            <input type="text" name="name" required class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">RFC</label>
                                            <input type="text" name="rfc" required maxlength="13" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900 uppercase">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">Nombre de Usuario (Login)</label>
                                            <input type="text" name="username" required class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">Correo Electrónico</label>
                                            <input type="email" name="email" required class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-900 focus:border-blue-900">
                                        </div>

                                        <div class="flex items-center">
                                            <input id="is_admin" name="is_admin" type="checkbox" class="h-4 w-4 text-blue-900 focus:ring-blue-900 border-gray-300 rounded">
                                            <label for="is_admin" class="ml-2 block text-sm text-slate-700">
                                                ¿Otorgar permisos de Administrador?
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-8 pt-6 border-t border-gray-200">
                                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-slate-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-900 transition">
                                            Crear Usuario y Generar Clave
                                        </button>
                                        <button type="button" @click="openModal = false" class="mt-3 w-full flex justify-center py-3 px-4 border border-slate-300 rounded-md shadow-sm text-sm font-bold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>