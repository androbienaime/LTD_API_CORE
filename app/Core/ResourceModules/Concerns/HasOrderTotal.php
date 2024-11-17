<?php

namespace App\Core\ResourceModules\Concerns;

use App\Core\Class\Coupons;
use App\Models\Core\Currency;
use App\Models\Core\Declination;
use App\Models\Core\Delivery;
use App\Models\Core\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;

trait HasOrderTotal
{
    /**
     * Calculates total order amount including products, discounts, and delivery
     */
    public static function updateTotals(Get $get, Set $set, $livewire = null): float
    {
        // Calculate product totals
        $selectedProducts = collect($get('orderProducts'))
            ->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        // Get delivery costs
        $costs = $get("delivery.costs");

        // Calculate total declination prices from repeater
        $declination_total = collect($get('orderProducts'))
            ->filter(fn($item) => !empty($item['declination_id']))
            ->map(fn($item) => Declination::find($item['declination_id'])?->price ?? 0)
            ->sum();

        // Calculate prices and discounts
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');

        $subtotal = $selectedProducts->reduce(function ($acc, $product) use ($prices, $get, $declination_total) {
            $productId = $product["product_id"];
            $productModel = Product::find($productId);
            $quantity = $product["quantity"];

            $to = Currency::find($get("currency_id"));

            $productPrice = Currency::convert($prices[$productId], $productModel->currency->iso_code, $to->iso_code);
            $productDiscount = Currency::convert(self::productDiscount($productModel), $productModel->currency->iso_code, $to->iso_code);

            // Calculate the subtotal and discount total
            $acc['total'] += ($productPrice - $productDiscount) * $quantity + $declination_total;
            $acc['discountTotal'] += $productDiscount * $quantity;
            $acc['deliveryTotal'] += self::productDeliveryCosts(Delivery::find($product["delivery_id"]));
            return $acc;
        }, ['total' => 0, 'discountTotal' => 0, 'deliveryTotal' => 0]);

        $total = $subtotal["total"] + ($subtotal["total"] * ($get('taxes') / 100));

        // Discount if coupon valid
        $code = $get("coupon");
        (!is_null($code)) ?: $code = "";

        $getCouponDiscount = (new Coupons())
            ->products($selectedProducts->all())
            ->discount(code : $code, total : $total);


        $set('total_amount_order', number_format(($total - $getCouponDiscount) + $subtotal["deliveryTotal"] + $costs, 2, '.', ''));
        $set("total_discount", number_format($subtotal["discountTotal"] + $getCouponDiscount, 2, '.', ''));

        self::updateBalance($get, $set);

        return $total;
    }

    /**
     * Updates subtotal for a single product including declinations and delivery
     * @param $get
     * @param $set
     */

    public static function updateSubTotal($get, $set): float
    {
        $total = 0;
        if(!is_null(Product::find($get('product_id')))) {
            $product = Product::find($get('product_id'));
            $discount = self::productDiscount($product);
            $delivery_price = self::productDeliveryCosts(Delivery::find($get("delivery_id")));
            $declinationPrice = Declination::find($get("declination_id"))->price ?? 0;

            $sub_totals = ($product->price + $declinationPrice + $delivery_price - $discount) * $get("quantity");
            $to = Currency::find($get("../../currency_id"));

            $set("sub_totals", Currency::convert($sub_totals, $product->currency->iso_code, $to->iso_code));
            $total = $sub_totals;
        }

        return $total;
    }

    /**
     * Updates the remaining balance after payment
     */
    private static function updateBalance(Get $get, Set $set): void
    {
        $totals = $get("total_amount_order");
        $amount = $get("order_amount");
        $set('balance', number_format($totals - $amount, 2, '.', ''));
    }
}
