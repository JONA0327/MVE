<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    Tablero de Control
                </h2>
                <p class="text-sm text-slate-500 mt-1">Gestión de Manifestaciones de Valor</p>
            </div>
            <a href="{{ route('manifestations.create') }}" class="inline-flex items-center px-5 py-2.5 bg-slate-900 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-900 active:bg-slate-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Manifestación
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Tarjetas de Resumen (KPIs) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-900 relative group transition hover:shadow-md">
                    <div class="absolute right-4 top-4 bg-blue-50 p-2 rounded-full group-hover:bg-blue-100 transition">
                        <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wide">Total Trámites</div>
                    <div class="text-3xl font-bold text-slate-800 mt-1">{{ $manifestations->count() }}</div>
                </div>

                <!-- Borradores -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500 relative group transition hover:shadow-md">
                    <div class="absolute right-4 top-4 bg-yellow-50 p-2 rounded-full group-hover:bg-yellow-100 transition">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wide">En Borrador</div>
                    <div class="text-3xl font-bold text-slate-800 mt-1">{{ $manifestations->where('status', '!=', 'signed')->count() }}</div>
                </div>

                <!-- Firmadas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-600 relative group transition hover:shadow-md">
                    <div class="absolute right-4 top-4 bg-green-50 p-2 rounded-full group-hover:bg-green-100 transition">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wide">Firmadas y Enviadas</div>
                    <div class="text-3xl font-bold text-slate-800 mt-1">{{ $manifestations->where('status', 'signed')->count() }}</div>
                </div>
            </div>

            <!-- Mensajes de Estado -->
            @if (session('status'))
                <div x-data="{ show: true }" x-show="show" class="bg-blue-50 border-l-4 border-blue-900 p-4 mb-6 flex justify-between items-center shadow-sm rounded-r">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-blue-900 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-blue-900 font-bold text-sm">{{ session('status') }}</span>
                    </div>
                    <button @click="show = false" class="text-blue-400 hover:text-blue-900">&times;</button>
                </div>
            @endif
            
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 flex justify-between items-center shadow-sm rounded-r">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-red-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-red-800 font-bold text-sm">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-red-400 hover:text-red-800">&times;</button>
                </div>
            @endif

            <!-- Tabla Principal -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-700 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Historial Reciente
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    @if($manifestations->isEmpty())
                        <div class="text-center py-16">
                            <div class="bg-slate-50 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">Sin registros activos</h3>
                            <p class="text-slate-500 mt-1 mb-6 text-sm">No has creado ninguna manifestación de valor todavía.</p>
                            <a href="{{ route('manifestations.create') }}" class="text-blue-800 hover:text-blue-600 font-bold text-sm hover:underline">Crear la primera ahora &rarr;</a>
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Folio</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Importador</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Valor Aduana</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach ($manifestations as $m)
                                    <tr class="hover:bg-slate-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-slate-900 font-mono">{{ substr($m->uuid, 0, 8) }}</div>
                                            <div class="text-xs text-slate-500">{{ $m->created_at->format('d M Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-900 font-bold">{{ $m->razon_social_importador }}</div>
                                            <div class="text-xs text-slate-500 font-mono">{{ $m->rfc_importador }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold text-slate-700">${{ number_format($m->total_valor_aduana, 2) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($m->status === 'signed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                                    Firmado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    Borrador
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($m->status === 'signed')
                                                <a href="{{ route('manifestations.downloadAcuse', $m->uuid) }}" class="text-blue-700 hover:text-blue-900 flex items-center justify-end group font-bold">
                                                    <span class="mr-1">Acuse PDF</span>
                                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </a>
                                            @else
                                                <div class="flex items-center justify-end space-x-4">
                                                    <a href="{{ route('manifestations.step2', $m->uuid) }}" class="text-slate-600 hover:text-blue-800 flex items-center group font-bold transition" title="Continuar Edición">
                                                        <span class="mr-1">Editar</span>
                                                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </a>
                                                    
                                                    <!-- Botón Eliminar -->
                                                    <form action="{{ route('manifestations.destroy', $m->uuid) }}" method="POST" onsubmit="return confirm('¿Confirma que desea eliminar este borrador permanentemente?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-slate-300 hover:text-red-600 flex items-center transition duration-200" title="Eliminar Borrador">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>