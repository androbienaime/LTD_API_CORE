<?php

namespace App\Models\Core;

use App\Models\Core\Order;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
