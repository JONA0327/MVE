<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'rfc' => ['required', 'string', 'max:13', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
        ]);

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