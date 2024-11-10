<?php

namespace App\Core\States\Order;

use App\Core\States\Order\Contracts\OrderStateInterface;
use App\Core\States\Order\OrderState;

class ProcessingState extends OrderState implements Contracts\OrderStateInterface
{

    public function canShipOrder(): bool
    {
        return true;
    }

    public function label() : string{
        return "Processing";
    }

    public function color() : string{
        return "info";
    }
}
