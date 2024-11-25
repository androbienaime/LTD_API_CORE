<?php

namespace App\Services;

use App\Core\Trait\ProductTrait;
use App\Models\Core\Order;
use App\Models\Core\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Core\OrderRequest;
use App\Http\Resources\Core\OrderResource;

class OrderService
{
    use ProductTrait;

    public static function createOrder(OrderRequest $request){
        try {
            DB::beginTransaction();

            // Récupération des données validées
            $validated = $request->validated();

            // Créer l'ordre (commande)
            $order = Order::create([
                'customer_id' => $validated['customer'],
                'currency_id' => $validated['currency'],
                'account_id' => $validated['account'] ?? null,
                'merchant_id' => $validated['merchant'] ?? null,
                'coupon_id' => $validated['coupon'] ?? null,
                'delivery_id' => $validated['delivery'] ?? null,
                'order_amount' => $validated['order_amount'],
                'total_amount_order' => $validated['total_amount_order'],
                'reference_order' => $validated['reference_order'],
                'secure_key' => $validated['secure_key'],
                'has_delivery' => $validated['has_delivery'],
                'balance' => $validated['balance'] ?? 0,
                'total_discount' => $validated['total_discount'] ?? 0,
            ]);

            // Associer les produits
            foreach ($validated['order_products'] as $product) {
                $productModel = Product::find($product['id']);

                // Vérifier la présence de déclinaisons et de livraisons, et valider leur existence
                self::checkDeclination($productModel, $product);
                self::checkDelivery($productModel, $product);

                // Créer l'enregistrement dans la relation
                $order->orderProducts()->create([
                    'product_id' => $product['id'],
                    'quantity' => $product['quantity'],
                    'sub_totals' => OrderCalculatorService::calculateSubtotal([
                        "product" => Product::find($product['id']),
                        "declination_id" => $product['declination'] ?? null,
                        'delivery_id' => $product['delivery'] ?? null,
                        'currency_id' => $validated['currency'],
                        "quantity" => $product['quantity'],
                    ]), 
                    'discount' => $product['discount'] ?? 0,
                    'declination_id' => $product['declination'] ?? null,
                    'delivery_id' => $product['delivery'] ?? null
                ]);

                $total = OrderCalculatorService::calculateTotal(
                    $validated['order_products'],
                    $validated['currency'],
                    $validated['delivery_cost'] ?? 0,
                    $validated['coupon_code'] ?? ""
                );
                
                if($validated["order_amount"] > $total){
                    throw new \Exception("The amount is greather than total");
                }
                // Mettre à jour le total
                $order->update(['total_amount_order' => $total, 'balance' => $total - $validated["order_amount"]]);
            }

            DB::commit();

            // Retourner une réponse de succès
            return [
                'message' => 'Commande créée avec succès.',
                'order' => new OrderResource($order),
                'code' => 200
            ];

        } catch (\Exception $e) {
            // Annuler toutes les modifications en cas d'erreur
            DB::rollBack();

            return [
                'message' => 'Une erreur est survenue lors de la création de la commande.',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public static function updateOrder(OrderRequest $request, Order $order){
        try {
            DB::beginTransaction();

            // Récupération des données validées
            $validated = $request->validated();

            // Mettre à jour les champs spécifiques de la commande
            $order->update(array_filter([
                'customer_id' => $validated['customer'] ?? null,
                'currency_id' => $validated['currency'] ?? null,
                'account_id' => $validated['account'] ?? null,
                'merchant_id' => $validated['merchant'] ?? null,
                'coupon_id' => $validated['coupon'] ?? null,
                'delivery_id' => $validated['delivery'] ?? null,
                'order_amount' => $validated['order_amount'] ?? null,
                'total_amount_order' => $validated['total_amount_order'] ?? null,
                'reference_order' => $validated['reference_order'] ?? null,
                'secure_key' => $validated['secure_key'] ?? null,
                'has_delivery' => $validated['has_delivery'] ?? null,
                'balance' => $validated['balance'] ?? null,
                'total_discount' => $validated['total_discount'] ?? null,
            ]));

            // Vérifier et mettre à jour les produits associés si fournis
            if (isset($validated['order_products'])) {
                foreach ($validated['order_products'] as $product) {
                    $productModel = Product::find($product['id']);

                    // Vérifier la présence de déclinaisons et de livraisons, et valider leur existence
                    self::checkDeclination($productModel, $product);
                    self::checkDelivery($productModel, $product);

                    // Vérifier si l'association existe déjà ou doit être créée
                    $existingOrderProduct = $order->orderProducts()->where('product_id', $product['id'])->first();

                    if ($existingOrderProduct) {
                        // Mettre à jour l'association existante
                        $existingOrderProduct->update([
                            'quantity' => $product['quantity'] ?? $existingOrderProduct->quantity,
                            'sub_totals' => OrderCalculatorService::calculateSubtotal([
                                "product" => $productModel ?? $existingOrderProduct->product,
                                "declination_id" => $product['declination'] ?? $existingOrderProduct->declination_id,
                                'delivery_id' => $product['delivery'] ?? $existingOrderProduct->delivery_id,
                                'currency_id' => $validated['currency'] ?? $order->currency->id,
                                "quantity" => $product['quantity'] ?? $existingOrderProduct->quantity,
                            ]) ?? $existingOrderProduct->sub_totals,                            
                            'discount' => $product['discount'] ?? $existingOrderProduct->discount,
                            'declination_id' => $product['declination'] ?? $existingOrderProduct->declination_id,
                            'delivery_id' => $product['delivery'] ?? $existingOrderProduct->delivery_id,
                        ]);
                    } else {
                        // Créer une nouvelle association
                        $order->orderProducts()->create([
                            'product_id' => Product::find($product['id'])->id,
                            'quantity' => $product['quantity'],
                            'sub_totals' => $product['sub_totals'] ?? OrderCalculatorService::calculateSubtotal([
                                "product" => $productModel->id,
                                "declination_id" => $product['declination'],
                                'delivery_id' => $product['delivery'],
                                'currency_id' => $validated['currency']
                            ]),  
                            'discount' => $product['discount'] ?? 0,
                            'declination_id' => $product['declination'] ?? null,
                            'delivery_id' => $product['delivery'] ?? null,
                        ]);
                    }

                    $total = OrderCalculatorService::calculateTotal(
                        $validated['order_products'],
                        $validated['currency'],
                        $validated['delivery_cost'] ?? 0,
                        $validated['coupon_code'] ?? ""
                    );
                    
                    if($validated["order_amount"] > $total){
                        throw new \Exception("The amount is greather than total");
                    }
                    // Mettre à jour le total
                    $order->update(['total_amount_order' => $total, 'balance' => $total - $validated["order_amount"]]);
                }
            }

            DB::commit();

            // Retourner une réponse de succès
            return[
                'message' => 'Commande mise à jour avec succès.',
                'order' => new OrderResource($order),
                'code' => 200
            ];
        } catch (\Exception $e) {
            // Annuler toutes les modifications en cas d'erreur
            DB::rollBack();

            return [
                'message' => 'Une erreur est survenue lors de la mise à jour de la commande.',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }


    /**
     * Vérifier les déclinaisons du produit.
     *
     * @param  $productModel
     * @param  array  $product
     * @throws \Exception
     */
    private static function checkDeclination(Product $productModel, $product)
    {
        if (self::hasDeclinations($productModel)) {
            if (empty($product['declination'])) {
                throw new \Exception("Le produit ID {$product['id']} nécessite une déclinaison, mais aucune 'declination' n'a été fournie.");
            }

            if (!$productModel->declinations->contains('id', $product['declination'])) {
                throw new \Exception("La déclinaison ID {$product['declination']} n'est pas valide pour le produit ID {$product['id']}.");
            }
        } elseif (!empty($product['declination'])) {
            throw new \Exception("Le produit ID {$product['id']} ne nécessite pas de déclinaison, mais une 'declination' a été fournie.");
        }
    }

    /**
     * Vérifier les livraisons du produit.
     *
     * @param  $productModel
     * @param  array  $product
     * @throws \Exception
     */
    private static function checkDelivery($productModel, $product)
    {
        if (self::hasDelivery($productModel)) {
            if (empty($product['delivery'])) {
                throw new \Exception("Le produit ID {$product['id']} nécessite une livraison, mais aucune livraison n'a été fournie.");
            }

            if (!$productModel->deliveryProducts()->where('id', $product['delivery'])->exists()) {
                throw new \Exception("La livraison ID {$product['delivery']} n'est pas valide pour le produit ID {$product['id']}.");
            }
        } elseif (!empty($product['delivery'])) {
            throw new \Exception("Le produit ID {$product['id']} ne nécessite pas de livraison, mais une 'delivery' a été fournie.");
        }
    }

}
