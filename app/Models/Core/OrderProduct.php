<?php

namespace App\Models\Core;

use App\Models\Core\Order;
use App\Models\Core\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderProduct extends Pivot
{
    use HasFactory;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function Declinations(): BelongsTo{
        return $this->belongsTo(Declination::class, "declination_id");
    }

    public function delivery() : hasMany{
        return $this->hasMany(Delivery::class);
    }
}
