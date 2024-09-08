<?php

namespace App\Models\Core;

use App\Models\Core\MerchantShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Merchant extends Model
{
    use HasFactory;

    public function merchantShop() : HasMany{
        return $this->hasMany(MerchantShop::class);
    }
}
