<?php

namespace App\Core\States\Order\Event;

use App\Models\Core\Order;
use Illuminate\Support\Facades\Event;

class OrderReturned extends Event
{

    /**
     * @param Order $order
     * @param string $returnReason
     */
    public function __construct(\App\Models\Core\Order $order, string $returnReason)
    {
    }
}
