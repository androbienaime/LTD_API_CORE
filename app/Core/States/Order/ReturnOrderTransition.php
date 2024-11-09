<?php

namespace App\Core\States\Order;

use App\Core\States\Order\Event\OrderReturned;
use App\Models\Core\Order;
use Spatie\ModelStates\Transition;

class ReturnOrderTransition extends Transition
{
    private string $returnReason;
    private Order $order;
    public function __construct(Order $order, string $returnReason)
    {
        $this->order = $order;
        $this->returnReason = $returnReason;
    }

    public function handle(): Order
    {
        event(new OrderReturned($this->order, $this->returnReason));

        $this->order->state = new ReturnedState($this->order, $this->returnReason, now());
        $this->order->save();

        return $this->order;
    }
}
