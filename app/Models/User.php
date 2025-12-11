<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'rfc',
        'email',
        'password',
        'role',       // Nuevo
        'parent_id',  // Nuevo
        'is_admin',   // Mantenemos por compatibilidad momentánea, pero usaremos 'role'
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
}