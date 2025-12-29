<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestationCompensation extends Model
{
    use HasFactory;

    // Definimos explícitamente la tabla para evitar errores de pluralización automática
    protected $table = 'manifestation_compensations';

    protected $fillable = [
        'manifestation_id',
        'fecha',
        'forma_pago',
        'especifique', // Para cuando forma_pago = 'FORPAG.OT'
        'motivo',
        'prestacion_mercancia'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function manifestation()
    {
        return $this->belongsTo(Manifestation::class);
    }
}