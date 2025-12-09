<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManifestationAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifestation_id',
        'type', // 'incrementable' o 'decrementable'
        'concepto',
        'fecha_erogacion',
        'importe',
        'moneda',
        'tipo_cambio',
        'a_cargo_importador'
    ];

    protected $casts = [
        'fecha_erogacion' => 'date',
        'importe' => 'decimal:2',
        'tipo_cambio' => 'decimal:6', // Precisión de 6 decimales para TC
        'a_cargo_importador' => 'boolean',
    ];

    // Scopes para usar: ManifestationAdjustment::incrementables()->get();
    public function scopeIncrementables($query)
    {
        return $query->where('type', 'incrementable');
    }

    public function scopeDecrementables($query)
    {
        return $query->where('type', 'decrementable');
    }
}
