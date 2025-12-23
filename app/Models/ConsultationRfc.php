<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsultationRfc extends Model
{
    use HasFactory;

    protected $fillable = ['manifestation_id', 'rfc_consulta', 'razon_social', 'tipo_figura', 'nombre'];

    public function manifestation()
    {
        return $this->belongsTo(Manifestation::class);
    }
}
