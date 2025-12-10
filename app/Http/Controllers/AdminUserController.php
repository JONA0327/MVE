<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log; // Importante para loguear errores

class AdminUserController extends Controller
{
    /**
     * Listar usuarios
     */
    public function index()
    {
        // Listamos todos menos el usuario actual para evitar auto-borrado accidental
        $users = User::where('id', '!=', auth()->id())->orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        // 1. Validación con mensajes personalizados para mayor claridad
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'rfc' => ['required', 'string', 'max:13', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
        ], [
            'username.unique' => 'El nombre de usuario ya está registrado.',
            'rfc.unique' => 'El RFC ya está registrado en el sistema.',
            'email.unique' => 'El correo electrónico ya existe.'
        ]);

        try {
            // Generar contraseña aleatoria de 8 caracteres
            $autoPassword = Str::password(8);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'rfc' => strtoupper($request->rfc),
                'email' => $request->email,
                'password' => Hash::make($autoPassword),
                'is_admin' => $request->has('is_admin'),
            ]);

            // Retornamos la contraseña en sesión para mostrarla UNA VEZ al admin
            return redirect()->route('admin.users.index')
                ->with('status', 'Usuario creado correctamente.')
                ->with('generated_password', $autoPassword);

        } catch (\Exception $e) {
            // Loguear el error real para que puedas verlo en storage/logs/laravel.log
            Log::error('Error al registrar usuario: ' . $e->getMessage());

            // Regresar con un mensaje de error general
            return back()->withInput()->withErrors(['general' => 'Ocurrió un error al intentar registrar el usuario. Por favor verifica los datos o intenta más tarde.']);
        }
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No puedes eliminar a otro administrador principal.');
        }
        
        $user->delete();
        return back()->with('status', 'Usuario eliminado del sistema.');
    }
}