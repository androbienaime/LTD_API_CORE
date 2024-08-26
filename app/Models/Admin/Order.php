<?php

namespace App\Models\Admin;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $guarded;

    public function status() : BelongsTo{
        return $this->belongsTo(OrderStatus::class);
    }

    public function order_advance() : BelongsTo{
        return $this->belongsTo(OrderAdvance::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function customer() : BelongsTo{
        return $this->belongsTo(Customer::class);
    }

    public function deliveryOrders() : HasMany{
        return $this->hasMany(DeliveryOrder::class);
    }

}
