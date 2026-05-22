<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
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
            // email
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener formato email.',
            // password
            'password.required' => 'El password es obligatorio.',
            'password.min' => 'El password debe tener al menos :min caracteres.',
            'password.max' => 'El password no puede superar los :max caracteres.',
        ];
    }
}
