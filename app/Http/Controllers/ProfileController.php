<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        \Log::info('===== PROFILE UPDATE INICIADO =====');
        \Log::info('Todos los datos del request:', $request->all());
        \Log::info('Datos validados:', $request->validated());
        
        $user = $request->user();
        \Log::info('Usuario ANTES de actualizar:', [
            'id' => $user->id,
            'name' => $user->name,
            'rfc' => $user->rfc,
            'razon_social' => $user->razon_social,
            'pais' => $user->pais,
        ]);
        
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Verificar si el perfil de solicitante está completo
        if ($user->hasCompleteSolicitorProfile()) {
            $user->profile_completed = true;
        } else {
            $user->profile_completed = false;
        }

        $saved = $user->save();
        \Log::info('Usuario guardado: ' . ($saved ? 'SI' : 'NO'));
        
        \Log::info('Usuario DESPUÉS de actualizar:', [
            'id' => $user->id,
            'name' => $user->name,
            'rfc' => $user->rfc,
            'razon_social' => $user->razon_social,
            'pais' => $user->pais,
            'telefono' => $user->telefono,
        ]);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
