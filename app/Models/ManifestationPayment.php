<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManifestationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifestation_id',
        'status', // 'paid' o 'payable'
        'fecha',
        'importe',
        'forma_pago',
        'moneda',
        'tipo_cambio',
        'situacion_pago' // Solo para 'payable'
    ];

    protected $casts = [
        'fecha' => 'date',
        'importe' => 'decimal:2',
        'tipo_cambio' => 'decimal:6',
    ];
}
