<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            Carga de los documentos
        </h2>
    </x-slot>

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
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Categoría del Documento</label>
                                <select x-model="uploadForm.categoria" class="w-full text-sm border-slate-300 rounded-sm focus:ring-blue-900 focus:border-blue-900">
                                    <option value="Principal">Documento Principal</option>
                                    <option value="Complementario">Documento Complementario</option>
                                </select>
                            </div>
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
                            
                            <div class="md:col-span-3">
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
                                    <div class="ml-2">
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded font-medium">✓ OK</span>
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

                    <!-- HERRAMIENTAS PDF VUCEM -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                        <h3 class="text-sm font-bold text-blue-900 uppercase border-b border-blue-200 mb-4 pb-2">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Herramientas para Documentos PDF
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- VERIFICADOR PDF -->
                            <div class="bg-white border border-blue-200 rounded p-4">
                                <h4 class="font-bold text-blue-900 mb-3 text-sm">
                                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.414-4.414a2 2 0 00-2.828 0L9 12l-2.172-2.172a2 2 0 00-2.828 2.828l4 4a2 2 0 002.828 0l10-10a2 2 0 00-2.828-2.828z"></path>
                                    </svg>
                                    Verificador de PDF VUCEM
                                </h4>
                                <p class="text-xs text-slate-600 mb-3">Verifique que su PDF cumple con los requisitos VUCEM antes de subirlo.</p>
                                
                                <div x-data="{ verificando: false, resultadoVerificacion: null }">
                                    <input type="file" x-ref="verificadorFile" accept=".pdf" class="block w-full text-xs border border-slate-300 rounded p-2 mb-3">
                                    
                                    <button type="button" @click="verificarPdf()" 
                                        :disabled="verificando"
                                        class="w-full bg-green-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-green-700 disabled:opacity-50">
                                        <span x-show="!verificando">Verificar PDF</span>
                                        <span x-show="verificando">Verificando...</span>
                                    </button>
                                    
                                    <div x-show="resultadoVerificacion" x-transition class="mt-3 p-3 rounded text-xs"
                                         :class="resultadoVerificacion?.valido ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        <div x-show="resultadoVerificacion?.valido">
                                            ✅ <strong>PDF Válido:</strong> Cumple con los requisitos VUCEM
                                        </div>
                                        <div x-show="!resultadoVerificacion?.valido">
                                            ❌ <strong>PDF No Válido:</strong>
                                            <ul class="mt-2 ml-4 list-disc" x-show="resultadoVerificacion?.errores">
                                                <template x-for="error in resultadoVerificacion?.errores">
                                                    <li x-text="error"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CONVERTIDOR PDF -->
                            <div class="bg-white border border-blue-200 rounded p-4">
                                <h4 class="font-bold text-blue-900 mb-3 text-sm">
                                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Convertidor PDF VUCEM
                                </h4>
                                <p class="text-xs text-slate-600 mb-3">Convierta su PDF al formato requerido por VUCEM (300 DPI, escala de grises).</p>
                                
                                <div x-data="{ convirtiendo: false, archivoConvertido: null }">
                                    <input type="file" x-ref="convertidorFile" accept=".pdf" class="block w-full text-xs border border-slate-300 rounded p-2 mb-3">
                                    
                                    <button type="button" @click="convertirPdf()" 
                                        :disabled="convirtiendo"
                                        class="w-full bg-blue-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-blue-700 disabled:opacity-50">
                                        <span x-show="!convirtiendo">Convertir a VUCEM</span>
                                        <span x-show="convirtiendo">Convirtiendo...</span>
                                    </button>
                                    
                                    <div x-show="archivoConvertido" x-transition class="mt-3 p-3 bg-green-100 text-green-800 rounded text-xs">
                                        ✅ <strong>Conversión Completada</strong>
                                        <div class="mt-2">
                                            <a :href="archivoConvertido?.url" :download="archivoConvertido?.nombre" 
                                               class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Descargar PDF VUCEM
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
                        const response = await fetch("{{ route('manifestations.upload', $manifestation->uuid) }}", { 
                            method: 'POST', 
                            headers: { 'X-CSRF-TOKEN': csrfToken }, 
                            body: formData 
                        });
                        if (!response.ok) throw new Error('Error');
                        const result = await response.json();
                        this.files.push({ 
                            id: result.id, 
                            tipo_documento: tipoFinal, 
                            descripcion_complementaria: this.uploadForm.descripcion, 
                            file_name: fileInput.files[0].name 
                        });
                        fileInput.value = ''; 
                        this.uploadForm.descripcion = '';
                        this.uploadForm.tipo_documento = '';
                        alert('✅ Archivo adjuntado correctamente');
                    } catch (error) { 
                        alert('❌ Error al subir el archivo. Verifique el tamaño (max 3MB) y el formato.'); 
                    } finally { 
                        this.uploading = false; 
                    }
                }
            }
        }
        
        // Función global para verificar PDF
        async function verificarPdf() {
            const fileInput = this.$refs.verificadorFile;
            if (!fileInput.files.length) {
                alert('Por favor, seleccione un archivo PDF para verificar.');
                return;
            }
            
            this.verificando = true;
            this.resultadoVerificacion = null;
            
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            try {
                const response = await fetch("{{ route('pdf.verify') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                
                const result = await response.json();
                this.resultadoVerificacion = result;
            } catch (error) {
                alert('❌ Error al verificar el archivo PDF.');
                console.error(error);
            } finally {
                this.verificando = false;
            }
        }
        
        // Función global para convertir PDF
        async function convertirPdf() {
            const fileInput = this.$refs.convertidorFile;
            if (!fileInput.files.length) {
                alert('Por favor, seleccione un archivo PDF para convertir.');
                return;
            }
            
            this.convirtiendo = true;
            this.archivoConvertido = null;
            
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            try {
                const response = await fetch("{{ route('pdf.convert') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                
                if (response.ok) {
                    // Si la respuesta es un archivo (blob)
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const originalName = fileInput.files[0].name;
                    const nameWithoutExt = originalName.replace('.pdf', '');
                    
                    this.archivoConvertido = {
                        url: url,
                        nombre: `${nameWithoutExt}_VUCEM.pdf`
                    };
                } else {
                    const result = await response.json();
                    throw new Error(result.message || 'Error al convertir el archivo');
                }
            } catch (error) {
                alert('❌ Error al convertir el archivo PDF: ' + error.message);
                console.error(error);
            } finally {
                this.convirtiendo = false;
            }
        }
    </script>
</x-app-layout>