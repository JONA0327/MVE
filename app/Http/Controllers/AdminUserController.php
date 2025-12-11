<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    /**
     * Listar usuarios según jerarquía
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            // Super Admin ve a todos los Administradores
            $users = User::where('role', 'admin')->orderBy('created_at', 'desc')->get();
        } elseif ($user->role === 'admin') {
            // Admin ve solo a sus Operadores (hijos)
            $users = User::where('parent_id', $user->id)->orderBy('created_at', 'desc')->get();
        } else {
            // Operador no debería estar aquí
            abort(403, 'No autorizado.');
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Guardar nuevo usuario con límites y roles
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // 1. Verificar Permisos y Límites
        if ($currentUser->role === 'operator') {
            abort(403, 'Los operadores no pueden crear usuarios.');
        }

        if ($currentUser->role === 'admin' && !$currentUser->canAddMoreUsers()) {
            return back()->withErrors(['limit' => 'Has alcanzado el límite de 5 operadores permitidos. Contacta a soporte para ampliar tu plan.']);
        }

        // 2. Validación
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'rfc' => ['required', 'string', 'max:13', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
        ]);

        try {
            $autoPassword = Str::password(8);
            
            // 3. Definir Rol y Padre según quién crea
            $newRole = 'operator'; // Por defecto
            $parentId = $currentUser->id;

            if ($currentUser->role === 'super_admin') {
                $newRole = 'admin'; // Super Admin crea Admins
                // El parent_id podría ser null o el super admin, depende si quieres ligarlos
                $parentId = $currentUser->id; 
            }

            // 4. Crear Usuario
            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'rfc' => strtoupper($request->rfc),
                'email' => $request->email,
                'password' => Hash::make($autoPassword),
                'role' => $newRole,
                'parent_id' => $parentId,
                'is_admin' => ($newRole === 'admin' || $newRole === 'super_admin'), // Mantener compatibilidad
            ]);

            return redirect()->route('admin.users.index')
                ->with('status', "Usuario creado correctamente con rol: " . strtoupper($newRole))
                ->with('generated_password', $autoPassword);

        } catch (\Exception $e) {
            Log::error('Error registro usuario: ' . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Error del sistema al registrar.']);
        }
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        // Solo permitir borrar si es hijo del usuario actual o si es super admin
        if ($currentUser->role !== 'super_admin' && $user->parent_id !== $currentUser->id) {
            return back()->with('error', 'No tienes permiso para eliminar este usuario.');
        }
        
        $user->delete();
        return back()->with('status', 'Usuario eliminado del sistema.');
    }
}