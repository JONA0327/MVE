<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                {{ __('Panel de Control') }}
            </h2>
            <a href="{{ route('manifestations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Manifestación
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- TARJETAS DE RESUMEN (STATS) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-900 p-6 flex items-center justify-between">
                    <div>
                        <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Documentos</div>
                        <div class="text-3xl font-black text-slate-800 mt-1">{{ $manifestations->count() }}</div>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-full text-blue-900">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>

                <!-- Firmadas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500 p-6 flex items-center justify-between">
                    <div>
                        <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Enviadas / Firmadas</div>
                        <div class="text-3xl font-black text-green-600 mt-1">{{ $manifestations->where('status', 'signed')->count() }}</div>
                    </div>
                    <div class="bg-green-50 p-3 rounded-full text-green-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <!-- Borradores -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-400 p-6 flex items-center justify-between">
                    <div>
                        <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Borradores Pendientes</div>
                        <div class="text-3xl font-black text-yellow-600 mt-1">{{ $manifestations->where('status', '!=', 'signed')->count() }}</div>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-full text-yellow-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- TABLA DE GESTIÓN -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if (session('status'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('status') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Folio / Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Importador</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Valor Aduana</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Estatus</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse($manifestations as $m)
                                    <tr class="hover:bg-slate-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-slate-800">
                                                {{ substr($m->uuid, 0, 8) }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $m->updated_at->format('d/m/Y H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-slate-900">{{ $m->razon_social_importador }}</div>
                                            <div class="text-xs text-slate-500">{{ $m->rfc_importador }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-slate-700">
                                                ${{ number_format($m->total_valor_aduana, 2) }}
                                            </div>
                                            <div class="text-xs text-slate-400">MXN</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($m->status === 'signed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200">
                                                    FIRMADA
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    BORRADOR
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-3">
                                                @if($m->status === 'signed')
                                                    <!-- Acciones para Firmadas -->
                                                    <a href="{{ route('manifestations.downloadAcuse', $m->uuid) }}" class="text-blue-600 hover:text-blue-900 font-bold flex items-center text-xs bg-blue-50 px-3 py-1 rounded hover:bg-blue-100 transition" target="_blank">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                        Acuse
                                                    </a>
                                                @else
                                                    <!-- Acciones para Borradores -->
                                                    <a href="{{ route('manifestations.step1', $m->uuid) }}" class="text-yellow-600 hover:text-yellow-800 font-bold text-xs bg-yellow-50 px-3 py-1 rounded hover:bg-yellow-100 transition">
                                                        Editar
                                                    </a>
                                                    
                                                    <form action="{{ route('manifestations.destroy', $m->uuid) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este borrador?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 px-3 py-1 rounded hover:bg-red-100 transition ml-2">
                                                            ✕
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="mt-2 text-sm font-medium">No hay manifestaciones registradas.</p>
                                            <p class="text-xs">Comience creando una nueva solicitud.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>