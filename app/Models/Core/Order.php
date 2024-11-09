<?php

namespace App\Models\Core;

use App\Core\States\Order\CancelOrderTransition;
use App\Core\States\Order\OrderState;
use App\Core\States\Order\ProcessOrderTransition;
use App\Core\States\Order\ReturnOrderTransition;
use App\Core\States\Order\ShipOrderTransition;
use App\Core\Trait\Models\AccountGlobalScopeTrait;
use App\Models\Core\Customer;
use App\Models\Core\OrderAdvance;
use App\Models\Core\OrderProduct;
use App\Models\Core\DeliveryOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\ModelStates\HasStates;

class Order extends Model
{
    use HasFactory, AccountGlobalScopeTrait, HasStates;

    protected static string $tableName = "orders";
    protected $guarded;

    protected $casts = [
        "state" => OrderState::class,
    ];

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

    public function payments() : hasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }
    public function isPaid(){
        return $this->payments()->where("status", "completed")->exist();
    }

    public function process() : self
    {
        try {
            $this->state->transition(new ProcessOrderTransition($this));
            return $this;
        } catch (InvalidTransition $e) {
            throw new OrderProcessingException($e->getMessage());
        }
    }

    public function ship(string $trackingNumber) : self{
        $this->state->transition(ShipOrderTransition::class, $trackingNumber);
        return $this;
    }

    public function cancel(string $reason) : self{
        $this->state->transition(CancelOrderTransition::class, $reason);
        return $this;
    }

    public function return(string $reason) : self{
        $this->state->transition(ReturnOrderTransition::class, $reason);
        return $this;
    }

}
