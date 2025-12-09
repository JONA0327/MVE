<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManifestationPedimento extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifestation_id',
        'numero_pedimento',
        'patente',
        'aduana_clave'
    ];
}