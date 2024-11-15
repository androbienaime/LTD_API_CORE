<?php

namespace App\Core\Trait\Concerns;

use App\Models\Core\Currency;
use Illuminate\Support\Facades\Cache;

trait HasConvertCurrency
{
    public static function convert(float $amount, string $from, string $to): float
    {
        $fromCurrency = self::getCurrency($from);
        $toCurrency = self::getCurrency($to);

        if (!$fromCurrency || !$toCurrency) {
            throw new \InvalidArgumentException('Invalid currency codes');
        }

        // Convert through base currency (USD)
        $inUSD = $amount / $fromCurrency->exchange_rate;
        return $inUSD * $toCurrency->exchange_rate;
    }

    protected static function getCurrency(string $iso_code): ?Currency
    {
        return Cache::remember(
            "currency_{$iso_code}",
            now()->addHour(),
            fn () => Currency::where('iso_code', $iso_code)->first()
        );
//        return Currency::where('iso_code', $iso_code)->first();
    }


    protected function clearCache(): void
    {
        Cache::tags(['currencies'])->flush();
    }

}
