<?php

namespace App\Core\States\Order;

use App\Core\States\Order\Event\ShippedOrder;
use App\Core\States\Order\Exception\OrderTransitionException;
use App\Models\Core\DeliveryOrder;
use App\Models\Core\Order;
use Spatie\ModelStates\Transition;

class ShipOrderTransition extends Transition
{
    private string $trackingNumber;
    private Order $order;

    /**
     * @param Order $order
     * @param string $trackingNumber
     */
    public function __construct(Order $order, ?string $trackingNumber)
    {
        $this->order = $order;
        $this->trackingNumber = $trackingNumber;
    }

    public function handle(): Order
    {
        if (empty($this->trackingNumber)) {
            throw new OrderTransitionException("Tracking number is required");
        }

        event(new ShippedOrder($this->order, $this->trackingNumber));

        $this->order->updateStateData(["tracking_number" => $this->trackingNumber]);
        $this->order->state = new ShippedState($this->order);
        $this->order->save();

        return $this->order;
    }
}
