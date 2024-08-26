<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
