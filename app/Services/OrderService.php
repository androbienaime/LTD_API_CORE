<?php

namespace App\Services;

use Exception;
use App\Models\Core\Order;
use App\Models\Core\Product;
use Illuminate\Http\Request;
use App\Core\Trait\ProductTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Core\OrderRequest;
use App\Http\Resources\Core\OrderResource;
use App\Models\Core\Currency;
use App\Models\Core\Delivery;
use App\Models\Core\PaymentMethod;
use App\Models\Core\ProductOffer;
use App\Models\Core\Shop;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;


class OrderService
{
    use ProductTrait;

    public static function createOrder(OrderRequest $request, Shop $shop)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $deliveryId = null;
            if (isset($validated['delivery'])) {
                $delivery = Delivery::create([
                    'delivery_date' => Carbon::parse($validated['delivery']['delivery_date'])
                                            ->format('Y-m-d H:i:s'),
                    'costs'         => $validated['delivery']['costs'] ?? 0,
                ]);
                $deliveryId = $delivery->id;
            }

            $totalNet    = $validated['total_amount_order'];
            $amountPaid  = $validated['order_amount'];
            $balance     = $totalNet - $amountPaid;
            $has_advance = $amountPaid > 0 && $amountPaid < $totalNet;
            $method      = 'cash';

            $paymentMethod = self::findOrCreatePaymentMethod(
                !empty($validated['payment_method']) ? $validated['payment_method'] : $method
            );

            $currencyId = Currency::findByIsoCode($validated['currency'])->id;
            if ($currencyId === null) {
                throw new \Exception('Currency not found with iso code : ' . $validated['currency']);
            }

            $order = Order::create(array_filter([
                'customer_id'        => $validated['customer'],
                'currency_id'        => $currencyId,
                'account_id'         => $validated['account']  ?? null,
                'shop_id'            => $shop->id              ?? null,
                'merchant_id'        => $validated['merchant'] ?? null,
                'coupon_id'          => $validated['coupon']   ?? null,
                'delivery_id'        => $deliveryId            ?? null,
                'order_amount'       => $validated['order_amount'],
                'total_amount_order' => $validated['total_amount_order'],
                'reference_order'    => $validated['reference_order'],
                'secure_key'         => $validated['secure_key'],
                'has_delivery'       => $validated['has_delivery'],
                'balance'            => $balance ?? 0,
                'payment_method_id'  => $paymentMethod,
                'has_advance'        => $has_advance,
                'total_discount'     => $validated['total_discount'] ?? 0,
            ], fn ($value) => $value !== null));

            if (isset($validated['order_products'])) {
                self::syncOrderProducts($order, $validated);
            }

            if ($has_advance) {
                $order->advanceOrders()->create([
                    'amount_order_advance' => $amountPaid,
                    'currency_id'          => $currencyId,
                    'payment_method_id'    => $paymentMethod,
                    'account_id'           => $validated['account'],
                ]);
            }

            self::updateOrderTotal($order, $validated);

            $order->save();

            DB::commit();

            return [
                'message' => 'Commande créée avec succès.',
                'data'    => new OrderResource($order),
                'code'    => 200,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'message' => 'Une erreur est survenue lors de la création de la commande.',
                'error'   => $e->getMessage(),
                'code'    => 500,
            ];
        }
    }

    public static function updateOrder(OrderRequest $request, Order $order)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $deliveryId = null;
            if (isset($validated['delivery'])) {
                $delivery = Delivery::create([
                    'delivery_date' => Carbon::parse($validated['delivery']['delivery_date'])
                                            ->format('Y-m-d H:i:s'),
                    'costs'         => $validated['delivery']['costs'] ?? 0,
                ]);
                $deliveryId = $delivery->id;
            }

            $currencyId = Currency::findByIsoCode($validated['currency'])->id;
            if ($currencyId === null) {
                throw new \Exception('Currency not found with iso code : ' . $validated['currency']);
            }

            $newOrderAmount = 0;
            $method         = 'cash';
            $paymentMethod  = self::findOrCreatePaymentMethod(
                !empty($validated['payment_method']) ? $validated['payment_method'] : $method
            );

            if (isset($validated['order_amount'])) {
                $orderAmount = (float) $validated['order_amount'];
                if ($orderAmount > 0 && $orderAmount <= (float) $order->balance) {
                    $newOrderAmount = round($orderAmount, 2);
                }
            }

            $order->fill(array_filter([
                'customer_id'     => $validated['customer']         ?? null,
                'currency_id'     => $currencyId,
                'account_id'      => $validated['account']          ?? null,
                'merchant_id'     => $validated['merchant']         ?? null,
                'coupon_id'       => $validated['coupon']           ?? null,
                'delivery_id'     => $deliveryId                    ?? null,
                'reference_order' => $validated['reference_order']  ?? null,
                'secure_key'      => $validated['secure_key']       ?? null,
                'has_delivery'    => $validated['has_delivery']     ?? null,
            ]));

            if (isset($validated['order_products'])) {
                self::syncOrderProducts($order, $validated);
            }

            $order->save();

            if ($newOrderAmount > 0) {
                $order->increment('order_amount', $newOrderAmount);
                $order->decrement('balance',      $newOrderAmount);

                $order->advanceOrders()->create([
                    'amount_order_advance' => $newOrderAmount,
                    'currency_id'          => $currencyId,
                    'payment_method_id'    => $paymentMethod,
                    'account_id'           => $validated['account'],
                ]);
            }

            DB::commit();

            return [
                'message' => 'Order updated successfully',
                'data'    => new OrderResource($order),
                'code'    => 200,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'message' => 'Error updating order',
                'error'   => $e->getMessage(),
                'code'    => 500,
            ];
        }
    }

    // ─── Sync order products ──────────────────────────────────────────────────

    private static function syncOrderProducts(Order $order, array $validated): void
    {
        // BUG 1 CORRIGÉ : l'ancien return debug qui court-circuitait tout
        // return ["message" => "after sync[order_products]...", ...] ← SUPPRIMÉ

        $productIds = collect($validated['order_products'])->pluck('id');

        $order->orderProducts()
              ->whereNotIn('product_id', $productIds)
              ->delete();

        foreach ($validated['order_products'] as $productData) {
            self::processOrderProduct($order, $productData, $validated);
        }
    }

    // ─── Process single order product ────────────────────────────────────────

    private static function processOrderProduct(Order $order, array $productData, array $validated): void
    {
        $product = Product::findOrFail($productData['id']);

        self::checkDeclination($product, $productData);
        self::checkDelivery($product, $productData);
        self::validateProductAvailability($product, $productData);
        self::reduceStock($product, $productData['quantity']);

        $currencyId = Currency::findByIsoCode($validated['currency'])->id;
        if ($currencyId === null) {
            throw new \Exception('Currency not found with iso code : ' . $validated['currency']);
        }

        // ── Prix d'offre ──────────────────────────────────────────────────────
        // BUG 2 CORRIGÉ : $validated['offer_price'] → $productData['offer_price']
        // BUG 3 CORRIGÉ : l'ancien return debug qui empêchait updateOrCreate
        $overridePrice = null;

        if (!empty($productData['offer_price'])) {
            $offerPrice = (float) $productData['offer_price'];   // ← depuis productData

            if ($offerPrice > 0) {
                $offer = ProductOffer::create([
                    'product_id'    => $product->id,
                    'user_id'       => auth()->id(),
                    'offered_price' => $offerPrice,
                    'token'         => self::generateToken(),
                    'status'        => 'accepted',
                    'note'          => $productData['note'] ?? null,
                    'expires_at'    => Carbon::now()->addHours(24),
                ]);

                $overridePrice = $offer->offered_price;
            }
        }

        // return ["message" => "Override : " . $overridePrice, ...] ← SUPPRIMÉ

        $order->orderProducts()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'quantity'       => $productData['quantity'],
                'sub_totals'     => OrderCalculatorService::calculateSubtotal([
                    'product'        => $product,
                    'declination_id' => $productData['declination'] ?? null,
                    'delivery_id'    => $productData['delivery']    ?? null,
                    'currency_id'    => $currencyId,
                    'quantity'       => $productData['quantity'],
                    'override_price' => $overridePrice,            // null = prix catalogue
                ]),
                'discount'       => $productData['discount']    ?? 0,
                'declination_id' => $productData['declination'] ?? null,
                'delivery_id'    => $productData['delivery']    ?? null,
                'offer_price'    => $overridePrice,
            ]
        );
    }

    // ─── Update order total ───────────────────────────────────────────────────

    private static function updateOrderTotal(Order $order, array $validated): void
    {
        $currencyId = Currency::findByIsoCode($validated['currency'])->id;
        if ($currencyId === null) {
            throw new \Exception('Currency not found with iso code : ' . $validated['currency']);
        }

        $orderProducts = $order->orderProducts;

        $total = OrderCalculatorService::calculateTotal(
            $orderProducts->map(fn ($p) => [
                'id'           => $p->product_id,
                'quantity'     => $p->quantity,
                'declination'  => $p->declination_id,
                'delivery'     => $p->delivery_id,
                'discount'     => $p->discount,
                'override_price' => $p->offer_price,  // transmettre le prix d'offre déjà enregistré
            ])->toArray(),
            $currencyId,
            $validated['delivery_cost']  ?? 0,
            $validated['coupon_code']    ?? ''
        );

        if ($validated['order_amount'] > $total) {
            throw new \Exception(
                "Order amount exceeds total : {$total} for order amount : {$validated['order_amount']}"
            );
        }

        $order->total_amount_order = $total;
        $order->balance            = $total - $validated['order_amount'];
    }

    // ─── State management ─────────────────────────────────────────────────────

    public static function showState(Order $order): array
    {
        return [
            'states' => collect($order->getAvailableStates())
                ->map(function (string $state) use ($order) {
                    $stateInstance = new $state($order);
                    return [
                        'value' => class_basename($state),
                        'label' => $stateInstance->label(),
                    ];
                })
                ->pluck('label', 'value')
                ->toArray(),
        ];
    }

    public static function changeState(Order $order, Request $request): array
    {
        $state = $request->input('state');

        $availableStates = collect($order->getAvailableStates())
            ->map(fn ($stateClass) => class_basename($stateClass))
            ->toArray();

        if (!in_array($state, $availableStates)) {
            throw ValidationException::withMessages([
                'state' => "L'état '$state' n'est pas valide pour cette commande.",
            ]);
        }

        $rules = [
            'state'                => 'required|string|in:' . implode(',', $availableStates),
            'reason'               => 'nullable|string|max:255',
            'tracking_number'      => 'nullable|string|max:255',
            'account_delivered_id' => 'nullable|exists:accounts,id',
            'account_shipped_id'   => 'nullable|exists:accounts,id',
            'currency_id'          => 'nullable|exists:currencies,iso_code',
        ];

        if ($state === 'ShippedState') {
            $rules['tracking_number'] = 'required|string|max:255';
        }
        if (in_array($state, ['CancelledState', 'ReturnedState'])) {
            $rules['reason'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $currencyId = null;
        if (isset($validated['currency_id'])) {
            $currencyId = Currency::findByIsoCode($validated['currency_id'])->id;
            if ($currencyId === null) {
                throw new \Exception('Currency not found with iso code : ' . $validated['currency_id']);
            }
        }

        $order->changeStatus(
            'App\\Core\\States\\Order\\' . $state,
            $validated['reason']               ?? null,
            $validated['tracking_number']      ?? null,
            $validated['account_shipped_id']   ?? null,
            $validated['account_delivered_id'] ?? null,
            $currencyId
        );

        return [
            'message' => 'État de la commande mis à jour avec succès.',
            'data'    => new OrderResource($order),
        ];
    }

    // ─── Live calculators ─────────────────────────────────────────────────────

    public static function calculateSubTotalLive(Request $request): array
    {
        $validated = $request->validate([
            'product'        => 'required|exists:products,slug',
            'delivery_id'    => 'nullable|exists:deliveries,id',
            'declination_id' => 'nullable|exists:declinations,id',
            'currency_id'    => 'required|string|exists:currencies,iso_code',
            'quantity'       => 'required|integer|min:1',
        ]);

        try {
            $product    = Product::findBySlug($validated['product']);
            $currencyId = Currency::findByIsoCode($validated['currency_id'])->id;

            if ($currencyId === null) {
                throw new \Exception('Currency not found with iso code : ' . $validated['currency_id']);
            }

            self::rulesLiveTotal($product, [
                'id'             => $product->id,
                'declination_id' => $validated['declination_id'] ?? null,
                'delivery_id'    => $validated['delivery_id']    ?? null,
                'quantity'       => $validated['quantity'],
            ]);

            return [
                'success' => true,
                'data'    => OrderCalculatorService::calculateSubtotal([
                    'product'        => $product,
                    'delivery_id'    => $validated['delivery_id']    ?? null,
                    'declination_id' => $validated['declination_id'] ?? null,
                    'currency_id'    => $currencyId,
                    'quantity'       => $validated['quantity'],
                ]),
                'code'    => 202,
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'code' => 500];
        }
    }

    public static function calculateTotalLive(Request $request): array
    {
        $validated = $request->validate([
            'order_products'                    => 'required|array',
            'order_products.*.price'            => 'numeric|min:0',
            'order_products.*.product'          => 'required|exists:products,slug',
            'order_products.*.delivery_id'      => 'nullable|exists:deliveries,id',
            'order_products.*.declination_id'   => 'nullable|exists:declinations,id',
            'order_products.*.quantity'         => 'required|integer|min:1',
            'currency_id'                       => 'required|exists:currencies,iso_code',
            'delivery_id'                       => 'nullable|exists:deliveries,id',
            'coupon'                            => 'nullable|string|exists:coupons,code',
        ]);

        try {
            $currencyId = Currency::findByIsoCode($validated['currency_id'])->id;
            if ($currencyId === null) {
                throw new \Exception('Currency not found with iso code : ' . $validated['currency_id']);
            }

            foreach ($validated['order_products'] as $productData) {
                $product = Product::findBySlug($productData['product']);
                self::rulesLiveTotal($product, $productData);
            }

            $total = OrderCalculatorService::calculateTotal(
                collect($validated['order_products'])->map(function ($p) {
                    return [
                        'id'          => Product::findBySlug($p['product'])->id,
                        'quantity'    => $p['quantity'],
                        'declination' => $p['declination_id'] ?? null,
                        'delivery'    => $p['delivery_id']    ?? null,
                        'discount'    => $p['discount']       ?? null,
                    ];
                })->toArray(),
                $currencyId,
                0,
                $validated['coupon'] ?? ''
            );

            return ['success' => true, 'data' => $total, 'code' => 202];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'code' => 500];
        }
    }

    // ─── Helpers privés ───────────────────────────────────────────────────────

    private static function checkDeclination(Product $productModel, array $product): void
    {
        if (self::hasDeclinations($productModel)) {
            if (empty($product['declination'])) {
                throw new \Exception("Le produit ID {$product['id']} nécessite une déclinaison.");
            }
            if (!$productModel->declinations->contains('id', $product['declination'])) {
                throw new \Exception("La déclinaison ID {$product['declination']} n'est pas valide pour le produit ID {$product['id']}.");
            }
        } elseif (!empty($product['declination'])) {
            throw new \Exception("Le produit ID {$product['id']} ne nécessite pas de déclinaison.");
        }
    }

    private static function checkDelivery(Product $productModel, array $product): void
    {
        if (self::hasDelivery($productModel)) {
            if (empty($product['delivery'])) {
                throw new \Exception("Le produit ID {$product['id']} nécessite une livraison.");
            }
            if (!$productModel->deliveryProducts()->where('id', $product['delivery'])->exists()) {
                throw new \Exception("La livraison ID {$product['delivery']} n'est pas valide pour le produit ID {$product['id']}.");
            }
        } elseif (!empty($product['delivery'])) {
            throw new \Exception("Le produit ID {$product['id']} ne nécessite pas de livraison.");
        }
    }

    private static function validateProductAvailability(Product $product, array $productData): void
    {
        if (!$product->isAvailable()) {
            throw new \Exception("Le produit {$product->name} n'est pas disponible à la vente.");
        }
        if (!$product->has_unlimited_stock && $productData['quantity'] > $product->stock_quantity) {
            throw new \Exception("Stock insuffisant pour {$product->name}. Disponible : {$product->stock_quantity}");
        }
    }

    private static function reduceStock(Product $product, int $quantity): void
    {
        if (!$product->has_unlimited_stock) {
            $product->decrement('stock_quantity', $quantity);
            if ($product->stock_quantity === 0) {
                $product->update(['is_in_stock' => false]);
            }
        }
    }

    private static function rulesLiveTotal(Product $product, array $productData): void
    {
        if (isset($productData['declination_id'])) {
            self::checkDeclination($product, ['id' => $product->id, 'declination' => $productData['declination_id']]);
        }
        if (isset($productData['delivery_id'])) {
            self::checkDelivery($product, ['id' => $product->id, 'delivery' => $productData['delivery_id']]);
        }
        self::validateProductAvailability($product, $productData);
    }

    private static function findOrCreatePaymentMethod(string|int $identifier): string
    {
        if (is_numeric($identifier)) {
            return PaymentMethod::findOrFail($identifier)->id;
        }
        if (strlen(trim($identifier)) < 2) {
            throw new \InvalidArgumentException('Le nom du moyen de paiement doit contenir au moins 2 caractères.');
        }
        return PaymentMethod::firstOrCreate(
            ['name'        => trim($identifier)],
            ['description' => null]
        )->id;
    }

    private static function generateToken(): string
    {
        return hash('sha256', Str::uuid() . microtime());
    }
}