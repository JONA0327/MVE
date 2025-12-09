<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManifestationAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifestation_id',
        'tipo_documento',
        'descripcion_complementaria',
        'file_path',
        'file_name',
        'file_size',
        'mime_type'
    ];

    // Helper para obtener URL de descarga si usas Storage::url()
    public function getDownloadUrlAttribute()
    {
        return \Illuminate\Support\Facades\Storage::url($this->file_path);
    }
}
