<?php

namespace App\Http\Requests\Core;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
                // Différencie les règles pour store et update
                return $this->isMethod('post') ? $this->storeRules() : $this->updateRules();
    }

    /**
     * Règles de validation pour la création (store).
     */
    protected function storeRules()
    {
        return [
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'middle_name' => 'nullable|string|max:255',
            'identity_number_id' => 'nullable|integer|exists:identity_numbers,id',
            'date_of_birth' => 'nullable|date',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'merchant_id' => 'nullable|integer|exists:merchants,id',
            'addresses' => 'nullable|array',
            'addresses.*' => 'integer|exists:addresses,id',
        ];
    }

    /**
     * Règles de validation pour la mise à jour (update).
     */
    protected function updateRules()
    {
        return [
            'firstname' => 'sometimes|string|max:255', // Pas toujours requis pour l'update
            'lastname' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $this->route('customer')->id,
            'middle_name' => 'nullable|string|max:255',
            'identity_number_id' => 'nullable|integer|exists:identity_numbers,id',
            'date_of_birth' => 'nullable|date',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'merchant_id' => 'nullable|integer|exists:merchants,id',
            'addresses' => 'nullable|array',
            'addresses.*' => 'integer|exists:addresses,id',
        ];
    }
}
