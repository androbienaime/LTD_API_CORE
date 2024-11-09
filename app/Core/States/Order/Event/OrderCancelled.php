<?php

namespace App\Core\States\Order\Event;

class OrderCancelled
{

    /**
     * @param $order
     * @param string|null $reason
     */
    public function __construct($order, ?string $reason)
    {
    }
}
