<?php

namespace App\Models\Core;

use App\Models\Core\Shop;
use App\Models\Core\Address;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AddressShop extends Pivot
{
    use HasFactory;

    public function address() : BelongsTo{
        return $this->belongsTo(Address::class);
    }

    public function shop(){
        return $this->belongsTo(Shop::class);
    }
}
