<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAccountRequest extends FormRequest
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
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'username' => 'required|string|unique:accounts,username',
            'email' => 'required|string|email|max:255|unique:accounts',
            'password' => 'required|string|min:8',
            'date_of_birth' => 'nullable|date|before:today', // Date valide dans le passé
            'gender' => 'nullable|in:male,female,other',    // Valeurs spécifiques
            'address.*.phone' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
