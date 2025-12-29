<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-file-pdf"></i> Acuses VUCEM
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            {{-- Acuse de eDocument --}}
            @if($edocumentFolio)
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-primary">
                            <i class="fas fa-file-alt"></i> Acuse de eDocument
                        </h6>
                        <p class="text-muted small mb-2">
                            Folio: <strong>{{ $edocumentFolio }}</strong>
                        </p>
                        <a href="{{ route('acuses.descargar', ['folio' => $edocumentFolio]) }}" 
                           target="_blank" 
                           class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            @endif

            {{-- Acuse de COVE (Acuse de Valor) --}}
            @if($coveFolio)
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-success">
                            <i class="fas fa-certificate"></i> Acuse de Valor (COVE)
                        </h6>
                        <p class="text-muted small mb-2">
                            Folio: <strong>{{ $coveFolio }}</strong>
                        </p>
                        <a href="{{ route('acuses.descargar', ['folio' => $coveFolio]) }}" 
                           target="_blank" 
                           class="btn btn-success btn-sm w-100">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            @endif

            {{-- Sin acuses disponibles --}}
            @if(!$edocumentFolio && !$coveFolio)
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        No hay acuses disponibles aún. Los acuses se generan después de enviar la manifestación a VUCEM.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
