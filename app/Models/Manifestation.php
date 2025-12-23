<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class Manifestation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        // Campos del solicitante
        'rfc_solicitante',
        'razon_social_solicitante',
        'actividad_economica_solicitante',
        'pais_solicitante',
        'codigo_postal_solicitante',
        'estado_solicitante',
        'municipio_solicitante',
        'localidad_solicitante',
        'colonia_solicitante',
        'calle_solicitante',
        'numero_exterior_solicitante',
        'numero_interior_solicitante',
        'lada_solicitante',
        'telefono_solicitante',
        'correo_solicitante',
        // Relación con importador
        'importador_id',
        // Campos legacy del importador (mantener temporalmente para compatibilidad)
        'rfc_importador',
        'razon_social_importador',
        'registro_nacional_contribuyentes',
        'total_precio_pagado',
        'total_incrementables',
        'total_decrementables',
        'total_valor_aduana',
        'total_precio_por_pagar',
        'existe_vinculacion',
        'descripcion_vinculacion',
        'metodo_valoracion_global',
        'incoterm',
        'fecha_factura',
        'fecha_entrada',
        'fecha_pago_pedimento',
        'fecha_presentacion',
        'observaciones_pedimento',
        'data_source',
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
        'fecha_factura' => 'date',
        'fecha_entrada' => 'date',
        'fecha_pago_pedimento' => 'date',
        'fecha_presentacion' => 'date',
    ];

    // Campos del solicitante que deben ser encriptados
    protected $encryptedFields = [
        'rfc_solicitante',
        'razon_social_solicitante',
        'actividad_economica_solicitante',
        'pais_solicitante',
        'codigo_postal_solicitante',
        'estado_solicitante',
        'municipio_solicitante',
        'localidad_solicitante',
        'colonia_solicitante',
        'calle_solicitante',
        'numero_exterior_solicitante',
        'numero_interior_solicitante',
        'lada_solicitante',
        'telefono_solicitante',
        'correo_solicitante',
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
    public function importador() { return $this->belongsTo(Importador::class); }
    public function coves() { return $this->hasMany(ManifestationCove::class); }
    public function pedimentos() { return $this->hasMany(ManifestationPedimento::class); }
    public function adjustments() { return $this->hasMany(ManifestationAdjustment::class); }
    public function payments() { return $this->hasMany(ManifestationPayment::class); }
    public function compensations() { return $this->hasMany(ManifestationCompensation::class); }
    public function attachments() { return $this->hasMany(ManifestationAttachment::class); }
    public function consultationRfcs() { return $this->hasMany(ConsultationRfc::class); }

    // --- MUTATORS PARA ENCRIPTACIÓN ---
    public function setRfcSolicitanteAttribute($value)
    {
        $this->attributes['rfc_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setRazonSocialSolicitanteAttribute($value)
    {
        $this->attributes['razon_social_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setActividadEconomicaSolicitanteAttribute($value)
    {
        $this->attributes['actividad_economica_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setPaisSolicitanteAttribute($value)
    {
        $this->attributes['pais_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setCodigoPostalSolicitanteAttribute($value)
    {
        $this->attributes['codigo_postal_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setEstadoSolicitanteAttribute($value)
    {
        $this->attributes['estado_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setMunicipioSolicitanteAttribute($value)
    {
        $this->attributes['municipio_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setLocalidadSolicitanteAttribute($value)
    {
        $this->attributes['localidad_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setColoniaSolicitanteAttribute($value)
    {
        $this->attributes['colonia_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setCalleSolicitanteAttribute($value)
    {
        $this->attributes['calle_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setNumeroExteriorSolicitanteAttribute($value)
    {
        $this->attributes['numero_exterior_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setNumeroInteriorSolicitanteAttribute($value)
    {
        $this->attributes['numero_interior_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setLadaSolicitanteAttribute($value)
    {
        $this->attributes['lada_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setTelefonoSolicitanteAttribute($value)
    {
        $this->attributes['telefono_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setCorreoSolicitanteAttribute($value)
    {
        $this->attributes['correo_solicitante'] = $value ? Crypt::encrypt($value) : null;
    }

    // --- ACCESSORS PARA DESENCRIPTACIÓN ---
    public function getRfcSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            // Si hay error en la desencriptación, devolver el valor original
            // (esto puede pasar con datos antiguos no encriptados)
            return $value;
        }
    }

    public function getRazonSocialSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getActividadEconomicaSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getPaisSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getCodigoPostalSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getEstadoSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getMunicipioSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getLocalidadSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getColoniaSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getCalleSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getNumeroExteriorSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getNumeroInteriorSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getLadaSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getTelefonoSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getCorreoSolicitanteAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }
}