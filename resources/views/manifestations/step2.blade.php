<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Carga de los documentos
        </h2>
    </x-slot>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="py-12" x-data="filesHandler()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- STEPPER -->
            <div class="mb-10">
                 <div class="flex items-center justify-between w-full opacity-90">
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-blue-900">PASO 1</div></div>
                    <div class="flex-auto border-t-2 border-blue-900"></div>
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-blue-900 border-2 border-blue-900 rounded-full px-2">PASO 2</div></div>
                    <div class="flex-auto border-t-2 border-slate-200"></div>
                    <div class="flex flex-col items-center w-1/3"><div class="text-xs font-bold text-slate-400">PASO 3</div></div>
                </div>
            </div>

            <div class="bg-white shadow-2xl rounded-sm overflow-hidden mb-10 border border-slate-300">
                <div class="bg-slate-100 px-8 py-6 border-b border-slate-300">
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 uppercase">2. Documentación de Soporte</h1>
                        <p class="text-xs text-slate-500">Adjunte los documentos requeridos por VUCEM para respaldar su manifestación de valor.</p>
                    </div>
                </div>

                <div class="p-10">
                    <!-- FORMULARIO DE CARGA -->
                    <div class="bg-slate-50 rounded border border-dashed border-slate-300 p-6 mb-8">
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Primera fila: Categoría y Tipo/Descripción -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Categoría del Documento</label>
                                    <select x-model="uploadForm.categoria" class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                        <option value="Principal">Documento Principal</option>
                                        <option value="Complementario">Documento Complementario</option>
                                    </select>
                                </div>
                                <div>
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
                                                <option value="Certificado de Origen">Certificado de Origen</option>
                                                <option value="Permiso COFEPRIS">Permiso COFEPRIS</option>
                                                <option value="Certificado Fitosanitario">Certificado Fitosanitario</option>
                                                <option value="Otros">Otros Documentos</option>
                                            </select>
                                        </div>
                                    </template>
                                    <template x-if="uploadForm.categoria === 'Complementario'">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">Descripción</label>
                                            <input type="text" x-model="uploadForm.descripcion" class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900" placeholder="Ej: Carta aclaratoria, Poder notarial...">
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Segunda fila: Archivo y Botón -->
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                                <div class="md:col-span-4">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1 required">Archivo</label>
                                    <input type="file" x-ref="fileInput" class="block w-full text-xs border border-slate-300 rounded-sm p-2 bg-white text-slate-600" accept=".pdf,.jpg,.jpeg,.png">
                                    <p class="text-xs text-slate-500 mt-1">PDF, JPG, PNG (máx. 3MB)</p>
                                </div>

                                <div class="md:col-span-1">
                                    <button type="button" @click="uploadFile()" 
                                        class="w-full inline-flex justify-center items-center bg-blue-900 text-white px-4 py-3 rounded-sm text-sm font-bold hover:bg-blue-800 disabled:opacity-50 transition shadow-sm uppercase tracking-wider"
                                        :disabled="uploading">
                                        <span x-show="!uploading">Adjuntar</span>
                                        <span x-show="uploading">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Subiendo...
                                        </span>
                                    </button>
                            </div>
                        </div>
                    </div>

                    <!-- LISTA DE ARCHIVOS -->
                    <div x-show="files.length > 0">
                        <h3 class="text-xs font-bold text-blue-900 uppercase border-b-2 border-blue-900 mb-4 pb-1">Documentos en el Expediente</h3>
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                            <template x-for="file in files" :key="file.id">
                                <li class="bg-white border border-slate-200 rounded p-4 flex items-center shadow-sm hover:shadow-md transition">
                                    <div class="bg-red-50 p-3 rounded mr-4">
                                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate" x-text="file.tipo_documento"></p>
                                        <p class="text-xs text-slate-500 truncate" x-text="file.file_name"></p>
                                        <span x-show="file.descripcion_complementaria" class="text-xs bg-slate-100 px-2 py-1 rounded mt-1 inline-block" x-text="'(' + file.descripcion_complementaria + ')'"></span>
                                    </div>
                                    <div class="ml-2 flex items-center gap-2">
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded font-medium">✓ OK</span>
                                        <button @click="viewFile(file.id, file.file_name)" class="text-blue-600 hover:text-blue-800 p-1 hover:bg-blue-50 rounded transition" title="Ver documento">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteFile(file.id)" class="text-red-600 hover:text-red-800 p-1 hover:bg-red-50 rounded transition" title="Eliminar documento">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                    
                    <div x-show="files.length === 0" class="text-center py-10 text-slate-400 italic bg-slate-50 border border-dashed border-slate-200 rounded mb-8">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm">No hay documentos adjuntos.</p>
                        <p class="text-xs mt-2">Utilice el formulario superior para adjuntar los documentos requeridos.</p>
                    </div>

                    <!-- INFORMACIÓN SOBRE PROCESAMIENTO AUTOMÁTICO -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900 mb-1">Procesamiento Automático de PDFs</h4>
                                <p class="text-xs text-blue-800">
                                    Los archivos PDF se verifican y convierten automáticamente al formato VUCEM (300 DPI, escala de grises) al momento de adjuntarlos. El sistema se encarga de todo el proceso de forma transparente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES DE NAVEGACIÓN -->
                    <div class="flex justify-between items-center mt-10 pt-6 border-t border-slate-200">
                        <div class="flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-red-700 font-bold text-sm px-4 py-2 mr-4 transition uppercase tracking-wider">Cancelar</a>
                            <a href="{{ route('manifestations.step1', $manifestation->uuid) }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-sm font-bold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 hover:text-slate-900 transition">
                                &larr; Anterior
                            </a>
                        </div>
                        <a href="{{ route('manifestations.step3', $manifestation->uuid) }}" class="inline-flex items-center px-8 py-3 bg-slate-900 border border-transparent rounded-sm font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-900 shadow-md transform hover:-translate-y-0.5 transition">
                            Continuar &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal de Visualización -->
        <div x-show="showModal" x-cloak @click.self="closeModal()" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="margin: 0;">
            <div @click.stop class="bg-white rounded-lg shadow-2xl max-w-6xl w-full max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-bold text-slate-800" x-text="modalFileName"></h3>
                    <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold leading-none">
                        ×
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-4 bg-slate-50">
                    <template x-if="modalFileType === 'pdf'">
                        <iframe :src="modalFileUrl" class="w-full h-full min-h-[70vh] border-0 bg-white rounded"></iframe>
                    </template>
                    <template x-if="modalFileType === 'image'">
                        <div class="flex items-center justify-center">
                            <img :src="modalFileUrl" class="max-w-full h-auto rounded shadow-lg">
                        </div>
                    </template>
                </div>
                <div class="p-4 border-t bg-slate-50 flex justify-end">
                    <button @click="closeModal()" class="px-6 py-2 bg-slate-600 text-white rounded hover:bg-slate-700 font-semibold">
                        Cerrar
                    </button>
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
                showModal: false,
                modalFileUrl: null,
                modalFileType: null,
                modalFileName: null,
                
                viewFile(fileId, fileName) {
                    const file = this.files.find(f => f.id === fileId);
                    if (!file) return;
                    
                    this.modalFileName = fileName;
                    
                    // Construir URL del archivo usando la ruta del controlador
                    this.modalFileUrl = `/manifestacion/{{ $manifestation->uuid }}/attachment/${fileId}/view`;
                    
                    // Determinar tipo de archivo por extensión
                    const ext = fileName.split('.').pop().toLowerCase();
                    if (ext === 'pdf') {
                        this.modalFileType = 'pdf';
                    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                        this.modalFileType = 'image';
                    }
                    
                    this.showModal = true;
                    document.body.style.overflow = 'hidden'; // Prevenir scroll del body
                },
                
                closeModal() {
                    this.showModal = false;
                    this.modalFileUrl = null;
                    this.modalFileType = null;
                    this.modalFileName = null;
                    document.body.style.overflow = ''; // Restaurar scroll del body
                },
                
                async deleteFile(fileId) {
                    if (!confirm('¿Está seguro de eliminar este documento?')) return;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const response = await fetch(`/manifestacion/{{ $manifestation->uuid }}/attachment/${fileId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });
                        
                        const result = await response.json();
                        
                        if (!response.ok || !result.success) {
                            throw new Error(result.error || 'Error al eliminar el archivo');
                        }
                        
                        // Remover el archivo de la lista
                        this.files = this.files.filter(f => f.id !== fileId);
                        alert('✅ Documento eliminado correctamente');
                    } catch (error) {
                        alert('❌ ' + error.message);
                    }
                },
                
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
                        const response = await fetch("{{ route('manifestations.upload', $manifestation->uuid) }}", { 
                            method: 'POST', 
                            headers: { 'X-CSRF-TOKEN': csrfToken }, 
                            body: formData 
                        });
                        
                        const result = await response.json();
                        
                        if (!response.ok || !result.success) {
                            throw new Error(result.error || 'Error al subir el archivo');
                        }
                        
                        this.files.push({ 
                            id: result.id, 
                            tipo_documento: tipoFinal, 
                            descripcion_complementaria: this.uploadForm.descripcion, 
                            file_name: fileInput.files[0].name 
                        });
                        fileInput.value = ''; 
                        this.uploadForm.descripcion = '';
                        this.uploadForm.tipo_documento = '';
                        
                        // Mostrar mensaje con información de conversión
                        if (result.converted) {
                            alert('✅ Archivo subido y convertido automáticamente\n\n' + result.message);
                        } else {
                            alert('✅ ' + result.message);
                        }
                    } catch (error) { 
                        alert('❌ ' + error.message); 
                    } finally { 
                        this.uploading = false; 
                    }
                }
            }
        }
    </script>
</x-app-layout>