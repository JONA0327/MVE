<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rfc' => ['required', 'string', 'between:12,13'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Como el RFC está encriptado, necesitamos buscar manualmente
        $inputRfc = $this->input('rfc');
        $inputUsername = $this->input('username');
        $inputPassword = $this->input('password');

        // Obtener todos los usuarios y buscar el que coincida con RFC y username
        $users = \App\Models\User::all();
        $user = null;

        foreach ($users as $potentialUser) {
            // Comparar RFC desencriptado y username
            if ($potentialUser->rfc === $inputRfc && $potentialUser->username === $inputUsername) {
                $user = $potentialUser;
                break;
            }
        }

        // Si encontramos el usuario, verificar la contraseña
        if ($user && Auth::attempt(['id' => $user->id, 'password' => $inputPassword], $this->boolean('remember'))) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Si no se pudo autenticar
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'rfc' => trans('auth.failed'), // Mensaje de error genérico
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
