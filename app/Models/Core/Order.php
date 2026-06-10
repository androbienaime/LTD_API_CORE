<?php

namespace App\Models\Core;

use App\Core\States\Order\CancelledState;
use App\Core\States\Order\CancelOrderTransition;
use App\Core\States\Order\DeliveredState;
use App\Core\States\Order\OrderState;
use App\Core\States\Order\PendingState;
use App\Core\States\Order\ProcessingState;
use App\Core\States\Order\ProcessOrderTransition;
use App\Core\States\Order\ReturnedState;
use App\Core\States\Order\ReturnOrderTransition;
use App\Core\States\Order\ShipOrderTransition;
use App\Core\States\Order\ShippedState;
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
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\HasUuid;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\State;
use Spatie\ModelStates\Transition;

class Order extends Model
{
    use HasFactory, AccountGlobalScopeTrait, HasStates;

    public $incrementing = false; // Désactiver l'auto-incrémentation
    protected $keyType = 'string'; // Indiquer que la clé est une chaîne

    protected static string $tableName = "orders";

    protected $fillable = [
        "order_amount",
        "user_id",
        "customer_id",
        "shop_id",
        "advance_order_id",
        "total_amount_order",
        "reference_order",
        "secure_key",
        "currency_id",
        "state",
        "state_data",
        "account_id",
        "merchant_id",
        "coupon_id",
        "delivery_id",
        "has_delivery",
        "balance",
        "total_discount",
        "account_id",
        "payment_method_id",
        "has_advance",
        "note",
    ];

    protected $casts = [
        "state" => OrderState::class,
        "state_data" => 'array',
        "has_delivery" => "boolean"
    ];

    public function currency() : BelongsTo
    {
        return $this->belongsTo(Currency::class);
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

    public function shops(){
        return $this->belongsTo(Shop::class);
    }

    public function payments() : hasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }
    public function isPaid(){
        return $this->payments()->where("status", "completed")->exist();
    }

    public function advanceOrders(): HasMany
    {
        return $this->hasMany(AdvanceOrder::class);
    }

    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class);
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

    public function ship(string $trackingNumber = null, ?string $account_shipped_id = null) : self{
        $this->state->transition(new ShipOrderTransition($this, $trackingNumber, $account_shipped_id));
        return $this;
    }

    public function cancel(string $reason = null) : self{
        $this->state->transition(new CancelOrderTransition($this, $reason));
        return $this;
    }

    public function return(string $reason = null) : self{
        $this->state->transition(new ReturnOrderTransition($this, $reason, now()));
        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableStates(): array
    {
        $currentState = $this->state;
        return self::getStates()["state"]
            ->filter(fn ($stateClass) =>  $currentState->canTransitionTo($stateClass))
            ->values()
            ->toArray();
    }

    /**
     * Méthode pour mettre à jour les données d'état dans le champ JSON
     */
    public function updateStateData(array $data): void
    {
        $this->state_data = array_merge($this->state_data ?? [], $data);
        $this->save();
    }


    public function changeStatus(string $newState, ?string $reason = null, 
        ?string $trackingNumber = null, ?string $account_shipped_id = null, 
        ?string $account_delivered_id = null, ?int $currency_id = null): void
    {
        if ($this->state->canTransitionTo($newState)) {
            switch ($newState) {
                case PendingState::class:
                        $this->state->transitionTo(new PendingState($this));
                    break;
                case ProcessingState::class:
                        $this->process()->save();
                    break;
                case ShippedState::class:
                        $this->ship($trackingNumber, $account_shipped_id)->save();
                    break;
                case DeliveredState::class:
                        if($currency_id == null){
                            throw new \Exception("Currency not found : " . $currency_id);
                        }
                        $this->completeToTotalAmount($currency_id, PaymentMethod::findOrCreatePaymentMethod('cash'), $account_delivered_id);
                        $this->updateStateData(["account_delivered_id" => $account_delivered_id]);
                        $this->state->transitionTo(new DeliveredState($this));
                    break;
                case ReturnedState::class:
                    $this->return($reason);
                    break;
                case CancelledState::class:
                    $this->cancel($reason);
                    break;
                default:
                    throw new \Exception("État non supporté");
            }
        } else {
            throw new \Exception("Transition non autorisée");
        }
    }


    /**
     * Complète order_amount pour atteindre exactement total_amount_order,
     * puis enregistre le montant ajouté dans un advance.
     */
    public function completeToTotalAmount(int $currencyId, int $paymentMethod, string $accountId): void
    {
        // Montant strictement nécessaire pour atteindre total_amount_order
        $newOrderAmount = $this->total_amount_order - $this->order_amount;

        if ($newOrderAmount > 0) {
            $this->increment('order_amount', $newOrderAmount);
            $this->decrement('balance', $newOrderAmount);

            $this->advanceOrders()->create([
                'amount_order_advance' => $newOrderAmount,
                'currency_id'          => $currencyId,
                'payment_method_id'    => $paymentMethod,
                'account_id'           => $accountId,
            ]);
        }
    }

}
