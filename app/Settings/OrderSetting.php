<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class OrderSetting extends Settings
{
    public float $percentValuesProcessingOrder = 0;
    public string $defaultOrderStatus = "Pending";
    public string $orderNumberFormat = "#,##0";
    // public string $keyReferencesMethod = "increment";
    // public int $keyReferences = 0;

    public static function group(): string
    {
        return 'order';
    }
}
