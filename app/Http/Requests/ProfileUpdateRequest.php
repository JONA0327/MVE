<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'webservice_key' => ['nullable', 'string', 'max:500'],
            // Campos del solicitante (opcionales)
            'rfc' => ['nullable', 'string', 'min:12', 'max:13'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'actividad_economica' => ['nullable', 'string', 'max:500'],
            'pais' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'estado' => ['nullable', 'string', 'max:100'],
            'municipio' => ['nullable', 'string', 'max:100'],
            'localidad' => ['nullable', 'string', 'max:100'],
            'colonia' => ['nullable', 'string', 'max:100'],
            'calle' => ['nullable', 'string', 'max:255'],
            'numero_exterior' => ['nullable', 'string', 'max:20'],
            'numero_interior' => ['nullable', 'string', 'max:20'],
            'lada' => ['nullable', 'string', 'max:5'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];
    }
}
