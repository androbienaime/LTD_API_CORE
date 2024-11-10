<?php

namespace App\Core\States\Order;

use App\Core\States\Order\Contracts\OrderStateInterface;
use App\Core\States\Order\OrderState;

class PendingState extends OrderState implements OrderStateInterface
{
    public function canShipOrder() : bool
    {
        return true;
    }

    public function label() : string{
        return "Pending";
    }

    public function color() : string{
        return "warning";
    }
}
