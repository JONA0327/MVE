<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManifestationCove extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifestation_id',
        'edocument',
        'metodo_valoracion',
        'numero_factura',
        'fecha_expedicion',
        'emisor',
        'destinatario'
    ];

    protected $casts = [
        'fecha_expedicion' => 'date',
    ];
}
