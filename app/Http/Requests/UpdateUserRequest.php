<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'name' => 'required|string|min:3|max:20',
            'nickname' => 'required|string|min:3|max:20|unique:users,nickname,' . $userId,
            'email' => 'required|email|unique:users,email,' . $userId,
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
        ];
    }
}
