<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
