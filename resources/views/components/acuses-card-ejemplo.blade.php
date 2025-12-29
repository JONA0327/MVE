{{-- Ejemplo de uso del componente acuses-card --}}

{{-- Forma 1: Con props --}}
<x-acuses-card 
    :edocument-folio="$manifestation->edocument_folio"
    :cove-folio="$manifestation->cove_folio"
/>

{{-- Forma 2: Con valores literales --}}
<x-acuses-card 
    edocument-folio="0170220LIS5D4"
    cove-folio="COVE214KNPVU4"
/>

{{-- Forma 3: Solo eDocument --}}
<x-acuses-card 
    :edocument-folio="$manifestation->edocument_folio"
    :cove-folio="null"
/>

{{-- Forma 4: Solo COVE --}}
<x-acuses-card 
    :edocument-folio="null"
    :cove-folio="$manifestation->cove_folio"
/>

{{-- 
    Ejemplo completo en una vista de manifestación:
--}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>Manifestación #{{ $manifestation->uuid }}</h2>
            
            {{-- Información de la manifestación --}}
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>RFC Importador:</strong> {{ $manifestation->rfc_importador }}</p>
                    <p><strong>Pedimento:</strong> {{ $manifestation->pedimento }}</p>
                    <p><strong>Estado:</strong> {{ $manifestation->estado }}</p>
                </div>
            </div>

            {{-- Acuses VUCEM --}}
            <x-acuses-card 
                :edocument-folio="$manifestation->edocument_folio"
                :cove-folio="$manifestation->cove_folio"
            />
        </div>
    </div>
</div>
@endsection
