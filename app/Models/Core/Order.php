<?php

namespace App\Models\Core;

use App\Models\Core\Customer;
use App\Models\Core\OrderStatus;
use App\Models\Core\OrderAdvance;
use App\Models\Core\OrderProduct;
use App\Models\Core\DeliveryOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function delivery() : BelongsTo{
        return $this->belongsTo(Delivery::class);
    }

}
