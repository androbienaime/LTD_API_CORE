<?php

namespace App\Services;

use App\Core\Trait\ProductTrait;
use App\Models\Core\Order;
use App\Models\Core\Product;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Core\OrderRequest;
use App\Http\Resources\Core\OrderResource;
use Exception;

class OrderService
{
    use ProductTrait;

    public static function createOrder(OrderRequest $request){
        try {
            DB::beginTransaction();
    
            $validated = $request->validated();
    
            // Créer l'ordre en utilisant un fill similaire à l'update
            $order = Order::create(array_filter([
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
            ]));
    
            // Utiliser la méthode syncOrderProducts de l'update
            if (isset($validated['order_products'])) {
                self::syncOrderProducts($order, $validated);
            }
    
            // Utiliser la méthode updateOrderTotal de l'update
            self::updateOrderTotal($order, $validated);
    
            $order->save();
    
            DB::commit();
    
            return [
                'message' => 'Commande créée avec succès.',
                'order' => new OrderResource($order),
                'code' => 200
            ];
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return [
                'message' => 'Une erreur est survenue lors de la création de la commande.',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public static function updateOrder(OrderRequest $request, Order $order)
    {
            try {
                DB::beginTransaction();

                $validated = $request->validated();

                // Update core order information
                $order->fill(array_filter([
                    'customer_id' => $validated['customer'] ?? null,
                    'currency_id' => $validated['currency'] ?? null,
                    'account_id' => $validated['account'] ?? null,
                    'merchant_id' => $validated['merchant'] ?? null,
                    'coupon_id' => $validated['coupon'] ?? null,
                    'delivery_id' => $validated['delivery'] ?? null,
                    'reference_order' => $validated['reference_order'] ?? null,
                    'secure_key' => $validated['secure_key'] ?? null,
                    'has_delivery' => $validated['has_delivery'] ?? null,
                ]));

                // Process order products
                if (isset($validated['order_products'])) {
                    self::syncOrderProducts($order, $validated);
                }

                // Calculate and update total order amount
                self::updateOrderTotal($order, $validated);

                $order->save();

                DB::commit();

                return [
                    'message' => 'Order updated successfully',
                    'order' => new OrderResource($order),
                    'code' => 200
                ];
            } catch (\Exception $e) {
                DB::rollBack();

                return [
                    'message' => 'Error updating order',
                    'error' => $e->getMessage(),
                    'code' => 500
                ];
            }
        }

        private static function syncOrderProducts(Order $order, array $validated)
        {
            $productIds = collect($validated['order_products'])->pluck('id');
            
            // Remove products not in the current update
            $order->orderProducts()
                ->whereNotIn('product_id', $productIds)
                ->delete();

            foreach ($validated['order_products'] as $productData) {
                self::processOrderProduct($order, $productData, $validated);
            }
        }

        private static function processOrderProduct(Order $order, array $productData, array $validated)
        {
            $product = Product::findOrFail($productData['id']);

            self::checkDeclination($product, $productData);
            self::checkDelivery($product, $productData);
            
            self::validateProductAvailability($product, $productData);
            self::reduceStock($product, $productData['quantity']);

            $orderProduct = $order->orderProducts()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => $productData['quantity'],
                    'sub_totals' => OrderCalculatorService::calculateSubtotal([
                        'product' => $product,
                        'declination_id' => $productData['declination'] ?? null,
                        'delivery_id' => $productData['delivery'] ?? null,
                        'currency_id' => $validated['currency'],
                        'quantity' => $productData['quantity']
                    ]),
                    'discount' => $productData['discount'] ?? 0,
                    'declination_id' => $productData['declination'] ?? null,
                    'delivery_id' => $productData['delivery'] ?? null,
                ]
            );
        }

        private static function updateOrderTotal(Order $order, array $validated)
        {
            $orderProducts = $order->orderProducts;

            $total = OrderCalculatorService::calculateTotal(
                $orderProducts->map(fn($product) => [
                    'id' => $product->product_id,
                    'quantity' => $product->quantity,
                    'declination' => $product->declination_id,
                    'delivery' => $product->delivery_id,
                    'discount' => $product->discount,
                ])->toArray(),
                $validated['currency'],
                $validated['delivery_cost'] ?? 0,
                $validated['coupon_code'] ?? ""
            );

            if ($validated["order_amount"] > $total) {
                throw new \Exception("Order amount exceeds total");
            }

            $order->total_amount_order = $total;
            $order->balance = $total - $validated["order_amount"];
    }

    public static function showState(Order $order){
        return $order->getAvailableStates();
    }

    public static function changeState(){
            
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
            if (!isset($product['delivery']) || empty($product['delivery'])) {
                throw new \Exception("Le produit ID {$product['id']} nécessite une livraison, mais aucune livraison n'a été fournie.");
            }

            if (!$productModel->deliveryProducts()->where('id', $product['delivery'])->exists()) {
                throw new \Exception("La livraison ID {$product['delivery']} n'est pas valide pour le produit ID {$product['id']}.");
            }
        } elseif (!empty($product['delivery'])) {
            throw new \Exception("Le produit ID {$product['id']} ne nécessite pas de livraison, mais une 'delivery' a été fournie.");
        }
    }

    private static function validateProductAvailability(Product $product, array $productData)
    {
        // Utiliser la méthode isAvailable() du modèle
        if (!$product->isAvailable()) {
            throw new \Exception("Le produit {$product->name} n'est pas disponible à la vente.");
        }

        // Vérification de la quantité commandée
        if (!$product->has_unlimited_stock) {
            // Si stock limité, vérifier que la quantité commandée ne dépasse pas le stock
            if ($productData['quantity'] > $product->stock_quantity) {
                throw new \Exception("Quantité insuffisante en stock pour le produit {$product->name}. 
                    Stock disponible : {$product->stock_quantity}");
            }
        }
    }

    private static function reduceStock(Product $product, int $quantity)
    {
        // Vérifier si le stock est géré (pas illimité)
        if (!$product->has_unlimited_stock) {
            // Réduire le stock
            $product->decrement('stock_quantity', $quantity);

            // Optionnel : Mettre à jour le statut si le stock atteint zéro
            if ($product->stock_quantity === 0) {
                $product->update([
                    'is_in_stock' => false
                ]);
            }
        }
    }

}
