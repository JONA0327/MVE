<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Importador extends Model
{
    protected $table = 'importadores';

    protected $fillable = [
        'rfc',
        'razon_social',
        'registro_nacional_contribuyentes',
        'domicilio_fiscal',
    ];

    /**
     * Un importador puede tener muchas manifestaciones
     */
    public function manifestations(): HasMany
    {
        return $this->hasMany(Manifestation::class);
    }

    /**
     * Buscar o crear importador por RFC
     */
    public static function findOrCreateByRfc(string $rfc, array $data = []): self
    {
        $rfc = strtoupper(trim($rfc));
        
        $importador = self::where('rfc', $rfc)->first();
        
        if (!$importador && !empty($data)) {
            $importador = self::create(array_merge(['rfc' => $rfc], $data));
        }
        
        return $importador;
    }

    /**
     * Actualizar datos del importador si se proporcionan nuevos
     */
    public function updateIfNewer(array $data): bool
    {
        $changed = false;
        
        if (!empty($data['razon_social']) && $this->razon_social !== $data['razon_social']) {
            $this->razon_social = $data['razon_social'];
            $changed = true;
        }
        
        if (!empty($data['registro_nacional_contribuyentes']) && $this->registro_nacional_contribuyentes !== $data['registro_nacional_contribuyentes']) {
            $this->registro_nacional_contribuyentes = $data['registro_nacional_contribuyentes'];
            $changed = true;
        }
        
        if (!empty($data['domicilio_fiscal']) && $this->domicilio_fiscal !== $data['domicilio_fiscal']) {
            $this->domicilio_fiscal = $data['domicilio_fiscal'];
            $changed = true;
        }
        
        if ($changed) {
            $this->save();
        }
        
        return $changed;
    }
}
