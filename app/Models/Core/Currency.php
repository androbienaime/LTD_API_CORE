<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        "currency",
        "symbol",
        "iso_code",
        "exchange_rate"
    ];

    protected $casts = [
        "is_active" => 'boolean'
    ];

    public function historicalRates(): HasMany
    {
        return $this->hasMany(CurrencyRate::class);
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
}
