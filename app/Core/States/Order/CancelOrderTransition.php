<?php

namespace App\Core\States\Order;

use App\Core\States\Order\Event\OrderCancelled;
use App\Models\Core\Order;
use Spatie\ModelStates\Transition;

class CancelOrderTransition extends Transition
{
    private ?string $reason;
    private Order $order;

    public function __construct(Order $order, ?string $reason = null)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function handle(): Order
    {
        event(new OrderCancelled($this->order, $this->reason));

        $this->order->updateStateData(['return_reason' => $this->reason, "cancelledAt" => now()]);
        $this->order->state = new CancelledState($this->order);
        $this->order->save();

        return $this->order;
    }
}
