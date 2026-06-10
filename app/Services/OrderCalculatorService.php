<?php

namespace App\Services;

use App\Core\Class\Coupons;
use App\Models\Core\Product;
use App\Models\Core\Currency;
use App\Models\Core\Delivery;
use App\Core\Trait\ProductTrait;
use App\Models\Core\Declination;

class OrderCalculatorService
{
    use ProductTrait;

    // ─── Prix de base ─────────────────────────────────────────────────────────

    /**
     * Résout le prix unitaire selon l'ordre de priorité :
     *   1. Prix d'offre validé (override_price) — jamais stocké sur Product
     *   2. Prix de déclinaison
     *   3. Prix catalogue du produit
     */
    private static function resolveBasePrice(array $params): float
    {
        /** @var Product $product */
        $product = $params['product'];

        // 1. Prix d'offre (override) — priorité absolue
        if (isset($params['override_price']) && $params['override_price'] !== null) {
            return (float) $params['override_price'];
        }

        // 2. Prix de déclinaison
        if (!empty($params['declination_id'])) {
            $declination = Declination::find($params['declination_id']);
            if ($declination) {
                return (float) $declination->price;
            }
        }

        // 3. Prix catalogue
        return (float) $product->price;
    }

    // ─── Sous-total ───────────────────────────────────────────────────────────

    /**
     * Calcule le sous-total d'une ligne produit.
     *
     * Paramètres attendus dans $productData :
     *   - product         : Product
     *   - quantity        : int
     *   - currency_id     : int
     *   - delivery_id     : int|null
     *   - declination_id  : int|null
     *   - override_price  : float|null  ← prix d'offre validé (null = prix catalogue)
     */
    public static function calculateSubtotal(array $productData): float
    {
        $product = $productData['product'];

        // Prix unitaire (offre > déclinaison > catalogue)
        $price = self::resolveBasePrice($productData);

        // Remise produit (discount catalogue — non appliqué si prix d'offre)
        $discount = isset($productData['override_price']) && $productData['override_price'] !== null
            ? 0
            : self::productDiscount($product);

        // Frais de livraison produit
        $delivery_price = self::productDeliveryCosts(
            Delivery::find($productData['delivery_id'])
        );

        // Calcul avant conversion
        $sub_totals = ($price + $delivery_price - $discount) * $productData['quantity'];

        // Conversion de devise
        $to = Currency::find($productData['currency_id']);

        return Currency::convert($sub_totals, $product->currency->iso_code, $to->iso_code);
    }

    // ─── Total commande ───────────────────────────────────────────────────────

    /**
     * Calcule le total d'une commande multi-produits.
     *
     * Chaque item de $products peut contenir :
     *   - id            : int
     *   - quantity      : int
     *   - delivery      : int|null
     *   - declination   : int|null
     *   - offer_token   : string|null  ← si présent, override_price doit être résolu
     *                                    en amont par OrderService via OfferService
     *   - override_price: float|null  ← prix d'offre déjà validé, passé par OrderService
     */
    public static function calculateTotal(
        array   $products,
        int     $currency,
        ?float  $deliveryCost = 0,
        ?string $couponCode = null
    ): float {
        $subtotals = collect($products)
            ->map(fn ($product) => self::calculateSubtotal([
                'product'        => Product::find($product['id']),
                'delivery_id'    => $product['delivery']       ?? null,
                'quantity'       => $product['quantity'],
                'declination_id' => $product['declination']    ?? null,
                'currency_id'    => $currency,
                // Transmis par OrderService après validation du token d'offre
                'override_price' => $product['override_price'] ?? null,
            ]))
            ->sum();

        // Réduction coupon globale
        $couponDiscount = 0;
        if ($couponCode) {
            $couponDiscount = (new Coupons())
                ->products($products)
                ->discount(code: $couponCode, total: $subtotals);
        }

        // Frais de livraison globaux
        return max(0, $subtotals - $couponDiscount + $deliveryCost);
    }
}