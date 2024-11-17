<?php

namespace App\Core\ResourceModules\Concerns;

use App\Models\Core\Currency;
use App\Models\Core\Product;

trait HasConvertToCurrency
{
    protected static int  $currency = 1;
    /**
     * @param Product $product
     * @param $amount
     * @return float
     */
    public static function convertToCurrency(Product $product, $amount): float
    {
        self::$currency = session('currency') ?? 1;

        $currency = Currency::where("id", self::$currency)->first();
        $from = $product->currency->iso_code;
        $to = $currency->iso_code;

        return Currency::convert($amount, $from, $to);
    }

}
