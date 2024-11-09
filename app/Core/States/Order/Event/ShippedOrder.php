<?php

namespace App\Core\States\Order\Event;

class ShippedOrder
{

    /**
     * @param Order $order
     * @param string $trackingNumber
     */
    public function __construct(\App\Models\Core\Order $order, string $trackingNumber)
    {
    }
}
