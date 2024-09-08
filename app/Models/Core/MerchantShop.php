<?php

namespace App\Models\Core;

use App\Models\Core\Shop;
use App\Models\Core\MerchantShop;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantShop extends Pivot
{
    use HasFactory;

    public function merchant() : BelongsTo{
        return $this->belongsTo(MerchantShop::class);
    }

    public function shop(){
        return $this->belongsTo(Shop::class);
    }
}
