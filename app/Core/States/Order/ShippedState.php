<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;
use App\Models\Core\Order;

class ShippedState extends OrderState
{
    private string $trackingNumber;



    public function __construct(Order $order){
        parent::__construct($order);
    }

    public function getTrackingNumber(): ?string{
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): void{
        $this->trackingNumber = $trackingNumber;
    }

    public function label() : string{
        return "Shipped";
    }

    public function color() : string
    {
        return "primary";
    }
}
