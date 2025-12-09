<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Manifestation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'curp_solicitante',
        'rfc_solicitante',
        // CORRECCIÓN: Nombres separados
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        
        'rfc_importador',
        'razon_social_importador',
        'registro_nacional_contribuyentes',
        'total_precio_pagado',
        'total_incrementables',
        'total_decrementables',
        'total_valor_aduana',
        'total_precio_por_pagar',
        'existe_vinculacion',
        'metodo_valoracion_global',
        'incoterm',
        'status',
        'cadena_original',
        'sello_digital',
        'path_acuse_manifestacion',
        'path_detalle_manifestacion',
    ];

    protected $casts = [
        'total_precio_pagado' => 'decimal:2',
        'total_incrementables' => 'decimal:2',
        'total_decrementables' => 'decimal:2',
        'total_valor_aduana' => 'decimal:2',
        'total_precio_por_pagar' => 'decimal:2',
        'existe_vinculacion' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // --- RELACIONES ---
    public function coves() { return $this->hasMany(ManifestationCove::class); }
    public function pedimentos() { return $this->hasMany(ManifestationPedimento::class); }
    public function adjustments() { return $this->hasMany(ManifestationAdjustment::class); }
    public function payments() { return $this->hasMany(ManifestationPayment::class); }
    public function compensations() { return $this->hasMany(ManifestationCompensation::class); }
    public function attachments() { return $this->hasMany(ManifestationAttachment::class); }
}