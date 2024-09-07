<?php

namespace App\Models\Core;

use App\Models\Core\Address;
use App\Models\Core\Delivery;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AddressDelivery extends Pivot
{
    use HasFactory;

    public function address() : BelongsTo{
        return $this->belongsTo(Address::class);
    }

    public function delivery() : BelongsTo{
        return $this->belongsTo(Delivery::class);
    }
}
