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
     /**
     * Calculer le sous-total d'un produit.
     */
    public static function calculateSubtotal(array $productData): float
    {
            $product = $productData['product'];
            $discount = self::productDiscount($product);
            $delivery_price = self::productDeliveryCosts(Delivery::find($productData['delivery_id']));
            $price = Declination::find($productData["declination_id"])->price ?? $product->price;

            $sub_totals = ($price + $delivery_price - $discount) * $productData["quantity"];
            $to = Currency::find($productData["currency_id"]);

            return Currency::convert($sub_totals, $product->currency->iso_code, $to->iso_code);
           
    }

    public static function calculateTotal(array $products, $currency, ?float $deliveryCost = 0, ?string $couponCode){

        $subtotals = collect($products)->map(fn($product) => self::calculateSubtotal([
            "product" => Product::find($product['id']),
            "delivery_id" => $product["delivery"] ?? null,
            "quantity" => $product["quantity"],
            "declination_id" => $product["declination"] ?? null,
            "currency_id" => $currency
        ]))->sum();

        $getCouponDiscount =0;

        // Appliquer les réductions globales (coupon)
        if ($couponCode) {
            $getCouponDiscount = (new Coupons())
            ->products($products)
            ->discount(code : $couponCode, total : $subtotals);
        }

        // Ajouter les frais de livraison globaux
        return max(0, $subtotals - $getCouponDiscount  + $deliveryCost);
       
    }
}
