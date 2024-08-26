<?php

namespace App\Models\Admin;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
