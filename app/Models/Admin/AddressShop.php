<?php

namespace App\Models\Admin;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
