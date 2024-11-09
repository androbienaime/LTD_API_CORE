<?php

namespace App\Core\States\Order;

use App\Core\States\Order\Event\OrderProcessingStarted;
use App\Core\States\Order\Exception\OrderTransitionException;
use App\Models\Core\Order;
use Spatie\ModelStates\Transition;

class ProcessOrderTransition extends Transition
{
    protected  Order $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
    public function handle() : Order{
//        if(!$this->order->isPaid()){
//            throw new OrderTransitionException("Cannot process unpaid order");
//        }

        event(new OrderProcessingStarted($this->order));

        $this->order->state = new ProcessingState($this->order);
//        $this->order->saveQuietly();

        return $this->order;
    }
}
