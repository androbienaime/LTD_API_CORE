<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = [
        "width",
        "height",
        "depth",
        "weigth",
        "costs",
        'delivery_mode'
    ];

    public function addressDeliveries() : HasMany{
        return $this->hasMany(AddressDelivery::class);
    }

    public function deliveryProducts() : HasMany{
        return $this->hasMany(DeliveryProduct::class);
    }

    public function deliveryOrders() : HasMany{
        return $this->hasMany(DeliveryOrder::class);
    }
}
