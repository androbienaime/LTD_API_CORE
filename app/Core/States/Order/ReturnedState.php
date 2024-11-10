<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;
use App\Models\Core\Order;

class ReturnedState extends OrderState
{
    public function __construct(Order $order){
        parent::__construct($order);
    }

    public function label() : string
    {
        return "Returned";
    }

    public function color() : string{
        return "gray";
    }

}
