<?php

namespace App\Models\Core;

use App\Models\Core\Carrier;
use App\Models\Core\DeliveryOrder;
use App\Models\Core\DeliveryProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = [
        "width",
        "heigth",
        "depth",
        "weigth",
        "costs",
        'delivery_mode',
        "address_id"
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

    public function address() : BelongsTo{
        return $this->belongsTo(Address::class);
    }
}
