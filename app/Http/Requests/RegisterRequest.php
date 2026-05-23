<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:20',
            'nickname' => 'required|string|min:3|max:20|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:20',
        ];
    }

    public function attributes(): array
    {
        return [

        ];
    }

    public function messages(): array
    {
        return [
            // name
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser un texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            // nickname
            'nickname.required' => 'El nickname es obligatorio.',
            'nickname.string' => 'El nickname debe ser un texto.',
            'nickname.min' => 'El nickname debe tener al menos :min caracteres.',
            'nickname.max' => 'El nickname no puede superar los :max caracteres.',
            'nickname.unique' => 'El nickname ya está registrado.',
            // email
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener formato email.',
            'email.unique' => 'El email ya está registrado.',
            // password
            'password.required' => 'El password es obligatorio.',
            'password.min' => 'El password debe tener al menos :min caracteres.',
            'password.max' => 'El password no puede superar los :max caracteres.',
        ];
    }
}
