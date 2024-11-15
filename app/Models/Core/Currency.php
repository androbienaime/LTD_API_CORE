<?php

namespace App\Models\Core;

use App\Core\Trait\Concerns\HasConvertCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    use HasFactory, HasConvertCurrency;

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

}
