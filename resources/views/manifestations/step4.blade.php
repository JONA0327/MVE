<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Expediente Digital y Firma
        </h2>
    </x-slot>

    <div class="py-12" x-data="filesHandler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- STEPPER: Paso 4 -->
            <div class="mb-8">
                <div class="flex items-center justify-between w-full">
                    <div class="flex flex-col items-center w-1/4">
                         <div class="w-8 h-8 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">✓</div>
                        <div class="text-xs font-medium mt-2 text-indigo-700">Generales</div>
                    </div>
                    <div class="flex-auto border-t-4 border-indigo-600 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4"><div class="w-8 h-8 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">✓</div></div>
                    <div class="flex-auto border-t-4 border-indigo-600 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4"><div class="w-8 h-8 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">✓</div></div>
                    <div class="flex-auto border-t-4 border-indigo-600 transition duration-500 ease-in-out"></div>
                    <div class="flex flex-col items-center w-1/4">
                         <div class="w-10 h-10 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold shadow-lg ring-4 ring-indigo-100 z-10">4</div>
                        <div class="text-xs font-bold mt-2 text-indigo-700">Firma</div>
                    </div>
                </div>
            </div>

            <!-- 1. ANEXOS -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-8 border-t-4 border-gray-600">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">1. Expediente Digital (Anexos)</h3>
                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">Max 3MB</span>
                </div>
                
                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg border border-dashed border-gray-300 p-5 mb-6">
                        <!-- Categoría -->
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Categoría del Requisito</label>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" name="categoria" value="Principal" x-model="uploadForm.categoria" class="text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                    <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-indigo-700">Documento Principal</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="radio" name="categoria" value="Complementario" x-model="uploadForm.categoria" class="text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                    <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-indigo-700">Documento Complementario</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <!-- Inputs Dinámicos -->
                            <div class="md:col-span-4">
                                <template x-if="uploadForm.categoria === 'Principal'">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tipo de Documento</label>
                                        <select x-model="uploadForm.tipo_documento" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500">
                                            <option value="">Seleccione el tipo...</option>
                                            <option value="Factura Comercial">Factura Comercial</option>
                                            <option value="Conocimiento de Embarque">Conocimiento de Embarque</option>
                                            <option value="Certificado de Origen">Certificado de Origen</option>
                                        </select>
                                    </div>
                                </template>
                                <template x-if="uploadForm.categoria === 'Complementario'">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Descripción</label>
                                        <input type="text" x-model="uploadForm.descripcion" class="w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500" placeholder="Ej: Carta aclaratoria...">
                                    </div>
                                </template>
                            </div>
                            
                            <div class="md:col-span-5">
                                 <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Archivo</label>
                                <input type="file" x-ref="fileInput" class="block w-full text-xs border border-gray-300 rounded-md p-2 bg-white" accept=".pdf,.jpg,.jpeg">
                            </div>

                            <div class="md:col-span-3">
                                 <button type="button" @click="uploadFile()" 
                                    class="w-full bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-700 disabled:opacity-50 transition shadow-md"
                                    :disabled="uploading">
                                    <span x-show="!uploading">Adjuntar</span>
                                    <span x-show="uploading">Subiendo...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de archivos -->
                    <div class="overflow-hidden border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Documento</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre Archivo</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <template x-for="file in files" :key="file.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                            <span x-text="file.tipo_documento"></span>
                                            <span x-show="file.descripcion_complementaria" class="block text-xs text-gray-500 italic mt-0.5" x-text="file.descripcion_complementaria"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500" x-text="file.file_name"></td>
                                        <td class="px-4 py-3 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✓ Cargado</span></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. FIRMA -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-lg border-t-8 border-indigo-600 mb-12">
                <div class="p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center pb-4 border-b">
                        2. Firmado Electrónico (e.firma)
                    </h3>
                    
                    <form action="{{ route('manifestations.sign', $manifestation->uuid) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Certificado (.cer)</label>
                                <input type="file" name="cer_file" accept=".cer" required class="block w-full text-sm border p-2 rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Clave Privada (.key)</label>
                                <input type="file" name="key_file" accept=".key" required class="block w-full text-sm border p-2 rounded">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Contraseña de Clave Privada</label>
                                <input type="password" name="password" required class="block w-full border-gray-300 rounded-md p-2" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="flex justify-end pt-6 border-t">
                            <button type="submit" class="inline-flex items-center px-8 py-4 bg-green-700 border border-transparent rounded-md font-bold text-white text-lg uppercase tracking-widest hover:bg-green-600 active:bg-green-900 shadow-xl transition">
                                Firmar y Enviar al SAT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filesHandler() {
            return {
                files: @json($manifestation->attachments ?? []),
                uploading: false,
                uploadForm: { categoria: 'Principal', tipo_documento: '', descripcion: '' },
                
                async uploadFile() {
                    const fileInput = this.$refs.fileInput;
                    if (fileInput.files.length === 0) return alert('Seleccione un archivo primero.');
                    this.uploading = true;
                    const formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    
                    let tipoFinal = this.uploadForm.tipo_documento;
                    if(this.uploadForm.categoria === 'Complementario') {
                        tipoFinal = 'Documento Complementario';
                        formData.append('descripcion_complementaria', this.uploadForm.descripcion);
                    }
                    formData.append('tipo_documento', tipoFinal);

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const response = await fetch("{{ route('manifestations.upload', $manifestation->uuid) }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData });
                        if (!response.ok) throw new Error('Error');
                        const result = await response.json();
                        this.files.push({ id: result.id, tipo_documento: tipoFinal, descripcion_complementaria: this.uploadForm.descripcion, file_name: fileInput.files[0].name });
                        fileInput.value = ''; this.uploadForm.descripcion = '';
                    } catch (error) { alert('Error al subir archivo.'); } finally { this.uploading = false; }
                }
            }
        }
    </script>
</x-app-layout>