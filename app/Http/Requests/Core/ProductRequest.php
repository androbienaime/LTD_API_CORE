<?php

namespace App\Http\Requests\Core;

use App\Models\Core\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProductRequest extends FormRequest
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
        return $this->isMethod('post') ? $this->validatedProduct(): $this->validatedProduct(true);

    }

    private function validatedProduct($updateOneField=false){
        $required = ($updateOneField) ? "sometimes|" : "required|";

        $idRoute = ($updateOneField) ? ",". $this->input('product_id') : "";

        return [
            'name' => $required.'string|max:255',
            'slug' => $required.'string|unique:products,slug'.$idRoute,
            'description' => 'nullable|string',
            'price' => $required.'numeric|min:0',
            'has_unlimited_stock' => $required.'boolean',
            'stock_quantity' => 'required_if:has_unlimited_stock,false|integer|min:0',
            'article' => 'string',
            'shop_id' => 'nullable|exists:shops,id',
            'currency_iso_code' => $required.'exists:currencies,iso_code',
            
            // Discount
            'has_discount' => 'boolean',
            'discount' => 'required_if:has_discount,true|array|max:1', // Limite à un seul objet dans le tableau
            'discount.discount' => 'required_with:discount|integer|min:0', // La remise est requise et doit être un entier positif
            'discount.description' => 'nullable|string', // Description facultative
            'discount.start_date' => 'nullable|date|after:yesterday|before_or_equal:discount.end_date', // Vérifie les dates
            'discount.end_date' => 'nullable|date|after:now|after_or_equal:discount.start_date',

            
            // Relations
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id',
            'declinations' => 'array',
            'declinations.*.name' => $required.'string',
            'declinations.*.price' => $required.'numeric',
            'declinations.*.quantity' => $required.'numeric',
            'declinations.*.values' => $required.'array',
            'declinations.*.values.*' => 'exists:declination_values,id',

            //LTSPSEO

            'product_seo' => 'nullable|array', // Si envoyé, ce doit être un tableau
            'product_seo.meta_title' => 'required_with:product_seo|string|max:255',
            'product_seo.meta_description' => 'nullable|string',
            'product_seo.is_redirection' => 'nullable|boolean',
            'product_seo.category_id' => 'nullable|integer|exists:categories,id',
            'product_seo.tags' => 'nullable|array', // Tags optionnels
            'product_seo.tags.*' => 'nullable|string',

            // Images
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096'
        ];

    }


    public function prepareForValidation()
    {
        $product = Product::findBySlug("".$this->route('product'));

        if (!$product) {
            throw ValidationException::withMessages([
                'slug' => 'Le produit avec ce slug n\'existe pas.',
            ]);
        }

        // Injecter l'ID dans la requête pour gérer les règles de validation conditionnelles
        $this->merge(['product_id' => $product->id]);
    }
}
