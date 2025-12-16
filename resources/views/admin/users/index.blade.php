<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                @if(Auth::user()->role === 'super_admin')
                    Gestión de Administradores
                @else
                    Gestión de Operadores
                @endif
            </h2>
            
            @if(Auth::user()->role === 'admin')
                <div class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">
                    Usuarios: {{ Auth::user()->children()->count() }} / 5 Disponibles
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Mensajes de Estado y Error -->
            @if (session('status'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('status') }}
                </div>
            @endif
            
            @if (session('generated_password'))
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-md">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>¡Importante!</strong> Copie la contraseña generada para el usuario, no se volverá a mostrar:
                            </p>
                            <p class="mt-2 text-xl font-mono font-bold text-slate-800 select-all bg-white p-2 rounded border border-yellow-200 inline-block">
                                {{ session('generated_password') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulario de Creación (Ocultar si límite alcanzado) -->
            @if(Auth::user()->canAddMoreUsers())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-lg font-bold mb-4 text-slate-700">
                            @if(Auth::user()->role === 'super_admin')
                                Registrar Nuevo Administrador
                            @else
                                Registrar Nuevo Operador
                            @endif
                        </h3>
                        <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            @csrf
                            <div class="lg:col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nombre Completo</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="w-full text-sm rounded-md border-slate-300 focus:ring-blue-900 focus:border-blue-900" required>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Usuario (Login)</label>
                                <input type="text" name="username" value="{{ old('username') }}" class="w-full text-sm rounded-md border-slate-300 focus:ring-blue-900 focus:border-blue-900" required>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">RFC</label>
                                <input type="text" name="rfc" value="{{ old('rfc') }}" class="w-full text-sm rounded-md border-slate-300 focus:ring-blue-900 focus:border-blue-900 uppercase" minlength="12" maxlength="13" required>
                            </div>
                            <div class="lg:col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Correo Electrónico</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="w-full text-sm rounded-md border-slate-300 focus:ring-blue-900 focus:border-blue-900" required>
                            </div>
                            
                            <!-- Checkbox Admin Oculto (ya lo manejamos por rol automático) -->

                            <div class="lg:col-span-1">
                                <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-md transition text-sm">
                                    + Crear Usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <p class="text-sm text-yellow-700 font-bold">Has alcanzado el límite máximo de usuarios permitidos (5). Elimina un operador para crear uno nuevo.</p>
                </div>
            @endif

            <!-- Listado de Usuarios -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Usuario / RFC</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Registro</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900 font-mono">{{ $user->username }}</div>
                                        <div class="text-xs text-slate-500">{{ $user->rfc }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->role === 'admin')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Administrador</span>
                                        @elseif($user->role === 'operator')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Operador</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $user->role }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">
                                        No se han registrado usuarios bajo tu supervisión.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>