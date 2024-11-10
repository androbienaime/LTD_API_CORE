<?php

namespace App\Core\States\Order;

use App\Core\States\Order\Event\OrderReturned;
use App\Models\Core\Order;
use Carbon\Carbon;
use Spatie\ModelStates\Transition;

class ReturnOrderTransition extends Transition
{
    private string $returnReason;
    private \DateTime $returnedAt;
    private Order $order;
    public function __construct(Order $order, string $returnReason, \DateTime $returnedAt)
    {
        $this->order = $order;
        $this->returnReason = $returnReason;
        $this->returnedAt = $returnedAt ?? Carbon::now()->toDateTimeString();
        ;
    }

    public function handle(): Order
    {
        event(new OrderReturned($this->order, $this->returnReason));

        $this->order->updateStateData(["return_reason" => $this->returnReason,
                                        "returned_at" => $this->returnedAt]);
        $this->order->state = new ReturnedState($this->order);
        $this->order->save();

        return $this->order;
    }
}
