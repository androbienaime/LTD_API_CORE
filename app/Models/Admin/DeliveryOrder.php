<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DeliveryOrder extends Pivot
{
    use HasFactory;

    public function delivery() : BelongsTo{
        return $this->belongsTo(Delivery::class);
    }

    public function order() : BelongsTo{
        return $this->belongsTo(Order::class);
    }
}
