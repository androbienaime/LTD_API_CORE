<?php

namespace App\Http\Requests\Core;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
        /*
        return [
            // Relations "BelongsTo"
            'customer' => 'nullable|exists:customers,id', // Assurez-vous que l'ID existe dans la table "customers"
            'currency' => 'required|exists:currencies,id',
            'account' => 'nullable|exists:accounts,id',
            'merchant' => 'nullable|exists:accounts,id',
            'coupon' => 'nullable|exists:coupons,id',
            'delivery' => 'nullable|exists:deliveries,id',

            // Champs de l'entité principale
            'order_products' => 'required|array|min:1', // Tableau de produits
            'order_products.*.id' => 'required|exists:products,id', // Chaque produit doit exister
            'order_products.*.quantity' => 'required|integer|min:1', // Quantité de chaque produit
            'order_products.*.sub_totals' => 'required|numeric|min:0', // Prix unitaire du produit
            'order_products.*.declination' => 'nullable|exists:declinations,id', // Prix unitaire du produit
            'order_products.*.delivery' => 'nullable|exists:deliveries,id', // Prix unitaire du produit

            'order_amount' => 'required|numeric|min:0',
            'total_amount_order' => 'required|numeric|min:0',
            'reference_order' => 'required|string|max:255|unique:orders,reference_order',
            'secure_key' => 'required|string|max:255',
            'has_delivery' => 'required|boolean',
            'balance' => 'nullable|numeric|min:0',
            'total_discount' => 'nullable|numeric|min:0',
        ];
        */

        $rules = [
            // Règles communes
            'customer' => 'exists:customers,id',
            'currency' => 'exists:currencies,id',
            'account' => 'nullable|exists:accounts,id',
            'merchant' => 'nullable|exists:merchants,id',
            'coupon' => 'nullable|exists:coupons,id',
            // 'delivery' => 'nullable|exists:deliveries,id',
            // Après — accepte un ID OU un objet inline
            'delivery' => 'nullable',
            'delivery.delivery_date' => 'required_if:delivery,array|date',
            'delivery.costs'         => 'sometimes|numeric|min:0',
            'order_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:total_amount_order', // total_amount_order doit être >= order_amount
            ],            
            'total_amount_order' => 'required|numeric|min:0',
            'reference_order' => 'required|string|max:255',
            'secure_key' => 'required|string|max:255',
            'state_data' => 'nullable|json',
            'has_delivery' => 'required|boolean',
            'balance' => 'nullable|numeric|min:0',
            'total_discount' => 'nullable|numeric|min:0',
        ];

        if ($this->routeIs('orders.update')) {
            // Pour update : rendre certains champs optionnels
            $rules = array_merge($rules, [
                'order_amount' =>  [
                    'nullable',
                    'numeric',
                    'min:0',
                    'lte:total_amount_order', // total_amount_order doit être >= order_amount
                ],
                'total_amount_order' => 'nullable|numeric|min:0',
                'reference_order' => 'nullable|string|max:255',
                'secure_key' => 'nullable|string|max:255',
                'has_delivery' => 'nullable|boolean',
            ]);
        }

        // Validation pour les produits associés
        $rules['order_products'] = 'array|required';
        $rules['order_products.*.id'] = 'required|exists:products,id';
        $rules['order_products.*.quantity'] = 'required|integer|min:1';
        $rules['order_products.*.sub_totals'] = 'required|numeric|min:0';
        $rules['order_products.*.discount'] = 'nullable|numeric|min:0';
        $rules['order_products.*.declination'] = 'nullable|exists:declinations,id';
        $rules['order_products.*.delivery'] = 'nullable|exists:deliveries,id';

        if ($this->routeIs('orders.update')) {
            // Pour update : rendre les produits optionnels
            $rules['order_products.*.quantity'] = 'nullable|integer|min:1';
            $rules['order_products.*.sub_totals'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }
}
