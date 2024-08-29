<?php

namespace App\Models\Admin;

use App\Models\Admin\Carrier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = [
        "width",
        "heigth",
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

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }
}
