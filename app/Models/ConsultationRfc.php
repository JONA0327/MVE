<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsultationRfc extends Model
{
    use HasFactory;

    protected $fillable = ['manifestation_id', 'rfc_consulta'];

    public function manifestation()
    {
        return $this->belongsTo(Manifestation::class);
    }
}
