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
            'account_ids' => 'required|array|min:1',
            'account_ids.*' => 'exists:accounts,id',
        ];
    }
}
