<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
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

    public function storeRules(){
        return [
            'name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'theme_name' => 'nullable|string|max:255',
            'theme_color' => 'nullable|string|max:7', // Exemple : #FFFFFF
            'types' => 'nullable|array',
            'shop_description' => 'nullable|string',
            'address_id' => 'nullable|exists:addresses,id',
            'ltsp_seo_id' => 'nullable',
            'slug' => 'required|string|unique:shops,slug',
            'account_id' => 'required|exists:accounts,id',
        ];
    }

    public function updateRules(){
        
        return [
            'name' => 'sometimes|string|max:255',
            'reference' => 'nullable|string|max:255',
            'theme_name' => 'nullable|string|max:255',
            'theme_color' => 'nullable|string|max:7', // Exemple : #FFFFFF
            'types' => 'nullable|array',
            'shop_description' => 'nullable|string',
            'address_id' => 'nullable|exists:addresses,id',
            'ltsp_seo_id' => 'nullable',
            'slug' => 'sometimes|string|unique:shops,slug',
            'account_id' => 'sometimes|exists:accounts,id',
        ];
    }
}
