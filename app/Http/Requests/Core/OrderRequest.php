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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /**
         * Détecter si c'est une création ou une mise à jour
         */
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [

            /*
            |--------------------------------------------------------------------------
            | Relations principales
            |--------------------------------------------------------------------------
            */

            'customer' => 'nullable|exists:customers,id',

            'currency' => [
                $isUpdate ? 'nullable' : 'required',
                'exists:currencies,iso_code',
            ],

            'account' => 'nullable|exists:accounts,id',

            'shop' => 'nullable|exists:shops,id',

            'merchant' => 'nullable|exists:merchants,id',

            'coupon' => 'nullable|exists:coupons,id',

            'payment_method' => 'nullable|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | Livraison
            |--------------------------------------------------------------------------
            */

            'delivery' => 'nullable|array',

            'delivery.delivery_date' => [
                'required_with:delivery',
                'date',
            ],

            'delivery.costs' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Montants
            |--------------------------------------------------------------------------
            */

            'order_amount' => [
                $isUpdate ? 'nullable' : 'required',
                'numeric',
                'min:0',
                'lte:total_amount_order',
            ],

            'total_amount_order' => [
                $isUpdate ? 'nullable' : 'required',
                'numeric',
                'min:0',
            ],

            'balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'total_discount' => [
                'nullable',
                'numeric',
                'min:0',
                // 'lte:total_amount_order',
            ],

            /*
            |--------------------------------------------------------------------------
            | Informations commande
            |--------------------------------------------------------------------------
            */

            'reference_order' => [
                'nullable',
                'string',
                'max:255',
            ],

            'secure_key' => [
                'nullable',
                'string',
                'max:255',
            ],
            'note' => [
                'nullable',
                'string',
            ],

            'state_data' => [
                'nullable',
                'json',
            ],

            'has_delivery' => [
                $isUpdate ? 'nullable' : 'required',
                'boolean',
            ],

            'has_advance' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Produits
            |--------------------------------------------------------------------------
            */

            'order_products' => [
                $isUpdate ? 'nullable' : 'required',
                'array',
                'min:1',
            ],

            'order_products.*.id' => [
                $isUpdate ? 'nullable' : 'required',
                'exists:products,id',
            ],

            'order_products.*.quantity' => [
                $isUpdate ? 'nullable' : 'required',
                'integer',
                'min:1',
            ],

            'order_products.*.sub_totals' => [
                $isUpdate ? 'nullable' : 'required',
                'numeric',
                'min:0',
            ],

            'order_products.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'order_products.*.declination' => [
                'nullable',
                'exists:declinations,id',
            ],

            'order_products.*.delivery' => [
                'nullable',
                'exists:deliveries,id',
            ],

            'order_products.*.offer_price' => [
                'nullable',
                'numeric',
                'min:1',
            ],
        ];
    }

    /**
     * Messages personnalisés
     */
    public function messages(): array
    {
        return [

            'order_amount.lte' =>
                'Le montant payé ne peut pas dépasser le montant total.',

            'order_products.required' =>
                'Au moins un produit est requis.',

            'order_products.min' =>
                'Au moins un produit est requis.',

            'delivery.delivery_date.required_with' =>
                'La date de livraison est obligatoire.',

        ];
    }

    /**
     * Préparer les données avant validation
     */
    protected function prepareForValidation(): void
    {
        /**
         * Convertir automatiquement certains champs
         */
        $this->merge([

            'order_amount' => $this->order_amount !== null
                ? (float) $this->order_amount
                : null,

            'total_amount_order' => $this->total_amount_order !== null
                ? (float) $this->total_amount_order
                : null,

            'balance' => $this->balance !== null
                ? (float) $this->balance
                : null,

            'total_discount' => $this->total_discount !== null
                ? (float) $this->total_discount
                : null,

            'has_delivery' => filter_var(
                $this->has_delivery,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ),

            'has_advance' => filter_var(
                $this->has_advance,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ),

        ]);
    }
}