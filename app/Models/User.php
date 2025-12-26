<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'rfc',
        'email',
        'password',
        'role',
        'parent_id',
        'is_admin',
        'webservice_key',
        // Campos del solicitante
        'razon_social',
        'actividad_economica',
        'pais',
        'codigo_postal',
        'estado',
        'municipio',
        'localidad',
        'colonia',
        'calle',
        'numero_exterior',
        'numero_interior',
        'lada',
        'telefono',
        'profile_completed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // --- RELACIONES JERÁRQUICAS ---

    // El "Jefe" de este usuario
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Los "Empleados/Operadores" de este usuario
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // --- HELPERS DE ROLES ---

    public function isSuperAdmin() { return $this->role === 'super_admin'; }
    public function isAdmin() { return $this->role === 'admin'; }
    public function isOperator() { return $this->role === 'operator'; }

    // Validación de límite (Solo para Admins)
    public function canAddMoreUsers()
    {
        if ($this->isSuperAdmin()) return true; // Sin límite
        if ($this->isAdmin()) {
            return $this->children()->count() < 5; // Límite de 5 operadores
        }
        return false; // Operadores no crean usuarios
    }

    // Verificar si el perfil del solicitante está completo
    public function hasCompleteSolicitorProfile()
    {
        return !empty($this->rfc) &&
               !empty($this->razon_social) &&
               !empty($this->pais) &&
               !empty($this->codigo_postal) &&
               !empty($this->estado) &&
               !empty($this->municipio) &&
               !empty($this->colonia) &&
               !empty($this->calle) &&
               !empty($this->numero_exterior) &&
               !empty($this->telefono);
    }

    // --- MUTATORS PARA ENCRIPTACIÓN ---
    public function setRfcAttribute($value)
    {
        $this->attributes['rfc'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setRazonSocialAttribute($value)
    {
        $this->attributes['razon_social'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setActividadEconomicaAttribute($value)
    {
        $this->attributes['actividad_economica'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setPaisAttribute($value)
    {
        $this->attributes['pais'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setCodigoPostalAttribute($value)
    {
        $this->attributes['codigo_postal'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setMunicipioAttribute($value)
    {
        $this->attributes['municipio'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setLocalidadAttribute($value)
    {
        $this->attributes['localidad'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setColoniaAttribute($value)
    {
        $this->attributes['colonia'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setCalleAttribute($value)
    {
        $this->attributes['calle'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setNumeroExteriorAttribute($value)
    {
        $this->attributes['numero_exterior'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setNumeroInteriorAttribute($value)
    {
        $this->attributes['numero_interior'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setLadaAttribute($value)
    {
        $this->attributes['lada'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setTelefonoAttribute($value)
    {
        $this->attributes['telefono'] = $value ? Crypt::encrypt($value) : null;
    }

    public function setWebserviceKeyAttribute($value)
    {
        $this->attributes['webservice_key'] = $value ? Crypt::encrypt($value) : null;
    }

    // --- ACCESSORS PARA DESENCRIPTACIÓN ---
    public function getRfcAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            // Si hay error en la desencriptación, devolver el valor original
            return $value;
        }
    }

    public function getRazonSocialAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getActividadEconomicaAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getPaisAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getCodigoPostalAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getEstadoAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getMunicipioAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getLocalidadAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getColoniaAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getCalleAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getNumeroExteriorAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getNumeroInteriorAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getLadaAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getTelefonoAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getWebserviceKeyAttribute($value)
    {
        try {
            return $value ? Crypt::decrypt($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Verifica si el usuario tiene clave webservice configurada
     */
    public function hasWebserviceKey(): bool
    {
        return !empty($this->webservice_key);
    }

    /**
     * Obtiene la clave webservice desencriptada
     */
    public function getDecryptedWebserviceKey(): ?string
    {
        return $this->webservice_key; // Ya se desencripta automáticamente con el accessor
    }
}