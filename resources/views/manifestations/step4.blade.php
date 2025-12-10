<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Expediente Digital
        </h2>
    </x-slot>

    <div class="py-12" x-data="filesHandler()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <!-- STEPPER VISUAL -->
            <div class="mb-10">
                <div class="flex items-center justify-between w-full opacity-90">
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Generales</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Valores</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900">Detalles</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">Archivos</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/5"><div class="text-xs font-bold text-slate-400">Resumen</div></div>
                </div>
            </div>

            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300">
                
                <!-- ENCABEZADO DE TARJETA -->
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300">
                    <h1 class="text-lg font-bold text-slate-900 uppercase">4. Adjuntar Documentación</h1>
                    <p class="text-xs text-slate-500">Suba los archivos soporte en formato PDF o Imagen.</p>
                </div>

                <div class="p-10">
                    
                    <!-- FORMULARIO DE CARGA -->
                    <div class="bg-slate-50 rounded border border-dashed border-slate-300 p-6 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-4">
                                <template x-if="uploadForm.categoria === 'Principal'">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">Tipo de Documento</label>
                                        <select x-model="uploadForm.tipo_documento" class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                            <option value="">Seleccione el tipo...</option>
                                            <option value="Factura Comercial">Factura Comercial</option>
                                            <option value="Conocimiento de Embarque">Conocimiento de Embarque / Guía</option>
                                            <option value="Documento de Transporte">Documento de Transporte</option>
                                            <option value="Pruebas de Valor">Pruebas de Valor</option>
                                            <option value="Contratos Relacionados">Contratos Relacionados</option>
                                            <option value="Comprobante de Incrementables">Comprobante de Incrementables</option>
                                            <option value="Otros">Otros Documentos</option>
                                        </select>
                                    </div>
                                </template>
                                <template x-if="uploadForm.categoria === 'Complementario'">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">Descripción</label>
                                        <input type="text" x-model="uploadForm.descripcion" class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900" placeholder="Ej: Carta aclaratoria...">
                                    </div>
                                </template>
                            </div>
                            
                            <div class="md:col-span-5">
                                 <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">Archivo</label>
                                <input type="file" x-ref="fileInput" class="block w-full text-xs border border-slate-300 rounded-sm p-2 bg-white text-slate-600" accept=".pdf,.jpg,.jpeg">
                            </div>

                            <div class="md:col-span-3">
                                 <button type="button" @click="uploadFile()" 
                                    class="w-full inline-flex justify-center items-center bg-blue-900 text-white px-4 py-2 rounded-sm text-sm font-bold hover:bg-blue-800 disabled:opacity-50 transition shadow-sm uppercase tracking-wider"
                                    :disabled="uploading">
                                    <span x-show="!uploading">Adjuntar</span>
                                    <span x-show="uploading">Subiendo...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- LISTA DE ARCHIVOS -->
                    <div x-show="files.length > 0">
                        <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-4 pb-1">Documentos en el Expediente</h3>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="file in files" :key="file.id">
                                <li class="bg-white border border-slate-200 rounded p-3 flex items-center shadow-sm">
                                    <div class="bg-red-50 p-2 rounded mr-3">
                                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate" x-text="file.tipo_documento"></p>
                                        <p class="text-xs text-slate-500 truncate" x-text="file.file_name"></p>
                                    </div>
                                    <span class="text-xs text-green-600 font-bold bg-green-50 px-2 py-1 rounded ml-2">OK</span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <div x-show="files.length === 0" class="text-center py-10 text-slate-400 italic">
                        No hay documentos adjuntos.
                    </div>

                    <!-- BOTONES DE NAVEGACIÓN -->
                    <div class="flex justify-between items-center mt-10 pt-6 border-t border-slate-200">
                        <a href="{{ route('manifestations.step3', $manifestation->uuid) }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-sm font-bold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 hover:text-slate-900 transition">
                            &larr; Anterior
                        </a>
                        
                        <a href="{{ route('manifestations.summary', $manifestation->uuid) }}" class="inline-flex items-center px-8 py-3 bg-slate-900 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-900 shadow-md transform hover:-translate-y-0.5 transition">
                            Ver Vista Preliminar y Firmar &rarr;
                        </a>
                    </div>
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
                    if (fileInput.files.length === 0) return alert('Por favor, seleccione un archivo para adjuntar.');
                    if (this.uploadForm.categoria === 'Principal' && !this.uploadForm.tipo_documento) {
                        return alert('Debe seleccionar un tipo de documento.');
                    }
                    if (this.uploadForm.categoria === 'Complementario' && !this.uploadForm.descripcion) {
                        return alert('Debe escribir una descripción para el documento complementario.');
                    }

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
                    } catch (error) { alert('Error al subir el archivo. Verifique el tamaño (max 3MB) y el formato.'); } finally { this.uploading = false; }
                }
            }
        }
    </script>
</x-app-layout>