<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;

class DeliveredState extends OrderState
{
    public function label() : string
    {
        return "Delivered";
    }

    public function color() : string
    {
        return "success";
    }
}
